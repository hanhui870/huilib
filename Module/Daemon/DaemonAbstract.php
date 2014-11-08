<?php
namespace HuiLib\Module\Daemon;

use HuiLib\Log\LogBase;

/**
 * Daemon服务
 * 
 * 专门用于不间歇执行脚本，不是那种定期执行的任务。用于解决crontab时间粒度最小一分钟的不足。
 * 
 * @author 祝景法
 * @since 2014/10/24
 */
abstract class DaemonAbstract
{
    //任务执行间歇性休息时间 默认200ms 单位微秒
    const CRON_SLEEP=200000;
    //子类可以通过覆盖，快速指定服务
    const SERVICE=NULL;
    //日志多少秒强制刷新一次
    const LOG_FORCEFLUSH_INTERVAL=10;
    
    /**
     * 运行的服务
     * 
     * @var string
     */
    private $service=NULL;
    
    private $logInstance=NULL;
    
    protected $sleepCron=NULL;
    
    /**
     * 单例实例
     *
     * @var DM_Module_Daemon
     */
    private static $instance=NULL;
    
    /**
     * 单进程防并发守护
     * 
     * @var SingleProcessGuard
     */
    private $singleGuard=NULL;
    
    /**
     * 新版本检测
     * 
     * 使用方法：在App/Data/Release创建相应的{service}.txt文件即可。
     * {service}为服务名称。检测到内容变动，机会自动触发新版本事件onNewReleaseFind，可退出或其他操作。
     *
     * @var ReleaseCheck
     */
    private $releaseCheck=NULL;
    
    /**
     * 内存限额检测机制
     *
     * @var MemoryCheck
     */
    private $memoryCheck=NULL;
    
    /**
     * 上次刷新日志
     * 
     * @var float
     */
    private $lastFlushLog=0;
    
    protected function __construct()
    {
        
    }
    
    /**
     * Daemon服务初始化
     * 
     * 子类可以调用，但是必须调用parent::init();
     */
    protected function init()
    {
        //单进程并发确认
        $this->getSingleGuard()->check();
    }
    
    /**
     * 执行Daemon任务
     */
    public final function daemonRun()
    {
        try {
            /**
             * 死循环部分
             */
            while (TRUE){
                //脚本进程日常监测
                //新版本检测
                $this->releaseCheck();
                
                //内存检测等
                $this->memoryCheck();
                
                //调用方法
                $this->run();

                //日志强刷
                if (microtime(1)-$this->lastFlushLog>static::LOG_FORCEFLUSH_INTERVAL){
                    //第一次不推
                    if ($this->lastFlushLog!=0){
                        LogBase::daemonFlush();
                    }
                    $this->lastFlushLog=microtime(1);
                }
                
                usleep(self::CRON_SLEEP);
            }
            
        }catch (\Exception $e){
            self::getLog()->add('Find DM_Module_Daemon::daemonRun Exception:'.$e->getMessage().PHP_EOL.$e->getTraceAsString());
            die();
        }
    }
    
    /**
     * 执行Daemon任务要调用的方法
     */
    protected abstract function run();
    
    /**
     * 发现新版本的事件
     */
    protected function onNewReleaseFind()
    {
        
    }
    
    /**
     * 系统运行过程检测到内存不够的事件
     */
    protected function onOutOfMemory()
    {
        
    }
    
    /**
     * 系统运行过程检测到内存不够的事件
     */
    protected function onShutDown()
    {
        
    }
    
    /**
     * 内存检查 激发内存不够事件
     * 
     * @return boolean
     */
    private function memoryCheck()
    {
        if ($this->getMemoryCheck()->check()){//内存够
            return true;
        }else{
            $this->onOutOfMemory();//内存不够事件
            return false;
        }
    }
    
    private function releaseCheck()
    {
        if ($this->getReleaseCheck()->check()){//无新版本
            return true;
        }else{
            $this->onNewReleaseFind();//发现新版本
            return false;
        }
    }
    
    protected function getSingleGuard()
    {
        if ($this->singleGuard===NULL){
            $this->singleGuard=new SingleProcessGuard($this->service);
        }
        
        return $this->singleGuard;
    }
    
    protected function getReleaseCheck()
    {
        if ($this->releaseCheck===NULL){
            $this->releaseCheck=new ReleaseCheck($this->service);
        }
        
        return $this->releaseCheck;
    }
    
    protected function getMemoryCheck()
    {
        if ($this->memoryCheck===NULL){
            $this->memoryCheck=new MemoryCheck($this->service);
        }
        
        return $this->memoryCheck;
    }
    
    /**
     * 获取服务执行间歇
     * @return NULL
     */
    public function getCronInterval()
    {
        if ($this->sleepCron===NULL){
            $this->sleepCron=self::CRON_SLEEP;
        }else{
            return $this->sleepCron;
        }
    }
    
    /**
     * 获取实例
     * 
     * @param string $service
     * @return DaemonAbstract
     */
    public static function instance($service=NULL)
    {
        if (empty($service) || preg_match('/[^\w]/is', $service)) {
            if (static::SERVICE || preg_match('/[^\w]/is', static::SERVICE)){
                $service=static::SERVICE;
            }else{
                throw new \Exception('$service 参数无效，仅字母、数字、下划线。');
            }
        }
        
        if (!isset(self::$instance[$service]) || self::$instance[$service]===NULL){
            self::$instance[$service]=new static($service);
            self::$instance[$service]->service=$service;
            
            try {
                //必须移到这里，因为构造函数service还没初始化
                self::$instance[$service]->init();
            }catch (\Exception $e){
                self::getLog()->add('Find DM_Module_Daemon::instance Exception:'.$e->getMessage());
                //print_r($e);
                die();
            }
        }

        return self::$instance[$service];
    }
    
    /**
     * 获取日志对象
     */
    protected function getLog()
    {
        if ($this->logInstance!==NULL) {
            return $this->logInstance;
        }

        $this->logInstance=LogBase::getFile()->setIdentify($this->service)->setType(LogBase::TYPE_DAEMON);
    
        return $this->logInstance;
    }
    
}