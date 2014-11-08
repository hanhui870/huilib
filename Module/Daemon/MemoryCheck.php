<?php
namespace HuiLib\Module\Daemon;

/**
 * 内存限额扫描
 * 
 * 提示规则：
 *     系统限定内存上限system，百分比上限percent，空闲内存下限keep，当前使用内存current
 *     
 *     如果 system*percent>=keep，则用keep判断。
 *     如果 system*percent<keep，则用percent判断。
 *     
 *     目标是打到最合理的内存控制。
 *     
 * 
 * @author 祝景法
 * @since 2014/10/24
 */
class MemoryCheck extends Base
{
    const LOG_SERVICE='memory';
    
    /**
     * 检测内存时间间隔
     *
     * 单位秒，经测试原来的60s太久了。
     *
     * @var int
     */
    const MEM_CHECK_INTERVAL=10;
    
    /**
     * 上次内存检测
     * @var timestamp
     */
    protected $lastMemoryCheck=0;
    
    /**
     * 系统设置内存限量
     */
    private $memoryLimit=NULL;
    
    /**
     * 内存百分比上限
     * 
     * 如果内存使用超过这个比例则提示内存不够
     */
    private $percentLimit=0.9;
    
    /**
     * 空闲内存保留下限 单位M
     * 
     * 如果空闲值小于设定则提示内存不够
     */
    private $spareMemoryKeep=50;
    
    /**
     * 确认。
     * 
     * 余量片段警戒线，采用剩余多少M结合百分比表示，因为百分比不科学，大的和小的相差太多。
     *
     * @return boolean true代表内存够 false内存到警戒线了
     */
    public function check()
    {
        //检测时效 单位时间内不用重复检测 第一次会检查下。
        //需用microtime
        if (microtime(1)<$this->lastMemoryCheck+self::MEM_CHECK_INTERVAL){
            return true;
        }

        $log=self::getLog();
        if ($this->lastMemoryCheck==0){
            //单独的内存检测日志
            $log->add('Memory check Service inited, PID:'.getmypid().' System LIMIT:'.ini_get('memory_limit'))->flush();
        }
        $this->lastMemoryCheck=microtime(1);

        //单独的内存检测日志
        $log->add('Memory check, PID:'.getmypid().' '.$this->service.' used:'.self::formatMemory(memory_get_usage()))->flush();
        
        //Allowed memory size of 134217728 bytes exhausted (tried to allocate 16777216 bytes)， 128M 90%还有12.8M余量，够处理
        $system=self::getMemoryLimit();
        if ($system*$this->percentLimit>=$this->getMemoryKeep()){//如果 system*percent>=keep，则用keep判断。
            if ($system-memory_get_usage()<$this->getMemoryKeep()){
                $message="Detect Out of memory: System ".ini_get('memory_limit')." used:".self::formatMemory(memory_get_usage())." spare:".self::formatMemory($system-memory_get_usage())."<keep:".$this->spareMemoryKeep."M Trigger event.";
                $log->add($message);
                $log->flush();
                return false;
            }
        }else{ //如果 system*percent<keep，则用percent判断。
            if (memory_get_peak_usage()>$system*$this->percentLimit){
                $message="Detect Out of memory: System ".self::getMemoryLimit()." used:".self::formatMemory(memory_get_usage())." pecent:".(number_format(memory_get_peak_usage()/ini_get('memory_limit'), 2, '.', '')*100)."% >limit ".($this->percentLimit*100)."% Trigger event.";
                $log->add($message);
                $log->flush();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 设置最低内存保存量
     * 
     * @param float $keep 最低内存保存量
     */
    public function setMemoryKeep($keep)
    {
        $keep=floatval($keep);
        if ($keep<=0) return false;
        
        $this->spareMemoryKeep=$keep;
        return true;
    }
    
    /**
     * 设置内存使用上限
     *
     * @param float $limit 内存使用上限 0-1之间
     */
    public function setPercentLimit($limit)
    {
        $limit=floatval($limit);
        if ($limit<=0 || $limit>=0) return false;
    
        $this->percentLimit=$limit;
        return true;
    }
    
    public function getMemoryKeep()
    {
        return $this->spareMemoryKeep*1024*1024;
    }
    
    /**
     * 获取系统内存限额
     */
    public function getMemoryLimit()
    {
        if ($this->memoryLimit===NULL){
            $memory=ini_get('memory_limit'); 
            $size=floatval($memory);
    
            $result=0;
            if(strripos($memory, 'G')){
                $result=$size*1024*1024*1024;
            }elseif(strripos($memory, 'M')){
                $result=$size*1024*1024;
            }elseif(strripos($memory, 'K')){
                $result=$size*1024;
            }elseif($size>128*1024*1024){
                $result=$size;
            }else{
                throw new \Exception('Failed to fetch memory_limit setting or invalid, please>=128M');
            }
    
            $this->memoryLimit=$result;
        }
    
        return $this->memoryLimit;
    }
    
    /**
     * 日志flush功能
     */
    public static function formatMemory($size)
    {
        if ($size>=1024*1024*1024){
            $number= ($size/(1024*1024*1024));
            $text='G';
        }elseif ($size>=1024*1024){
            $number= ($size/(1024*1024));
            $text='M';
        }elseif ($size>=1024){
            $number= ($size/(1024));
            $text='K';
        }else{
            $number= ($size);
            $text='B';
        }
    
        return number_format($number, 3, '.', '').$text;
    }
}