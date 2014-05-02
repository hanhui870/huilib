<?php
namespace HuiLib\Module\Runtime;

/**
 * TODO 运行时memory check模块，bin运行脚本时可能需要
 * 
 * 来自Dcoin项目时的开发优化经验
 * 
 * @author 祝景法
 * @since 2013/09/24
 */
class Memory extends  \HuiLib\Module\ModuleBase
{
    /**
     * 检测可用内存
     */
    public static function memoryCheck()
    {
        //非cli不用检测
        if ((php_sapi_name() != 'cli')) {
            return true;
        }
    
        //检测时效 单位时间内不用重复检测
        if (time()<self::$lastMemoryCheck+self::MEM_CHECK_INTERVAL){
            return true;
        }
    
        //单独的内存检测日志
        $service=defined('TRANSACTION_SERVICE') ? '('.TRANSACTION_SERVICE.')' : '';
        Transaction_Log_Memory::getInstance()->add('Normal memory check call, PID:'.getmypid().$service.' memory used now:'.self::formatMemory(memory_get_usage()))->flush();
    
        self::$lastMemoryCheck=time();
    
        //Allowed memory size of 134217728 bytes exhausted (tried to allocate 16777216 bytes)， 128M 90%还有12.8M余量，够处理
        $percent=0.90;
        if (self::getMemoryLimit()*$percent>memory_get_peak_usage()){//小于90%可用内存 达标
            return true;
        }
        ////////////内存超标分界线/////////////
        $message="!!!!!NOTICE!!!!!: Allow memory ".ini_get('memory_limit')." used now:".self::formatMemory(memory_get_usage())." >".($percent*100)."%， Will exit for safe reason.";
        if (self::$instance!==NULL && is_array(self::$instance)){
            foreach (self::$instance as $log){
                $log->add($message);
                $log->flush();
            }
        }else{
            $log=Transaction_Log_Error::getInstance();
            $log->add($message);
            $log->flush();
        }
    
        exit();
    }
    
    /**
     * 日志flush功能
     */
    public static function getMemoryLimit()
    {
        if (self::$memoryLimit===NULL){
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
    
            self::$memoryLimit=$result;
        }
    
        return self::$memoryLimit;
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