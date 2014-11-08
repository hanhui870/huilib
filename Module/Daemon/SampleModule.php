<?php
namespace HuiLib\Module\Daemon;

/**
 * 样例model
 * 
 * 放在应用的module中，并在控制器建立入口，执行以下调用即可：
 * App_Module_Crontab::instance()->daemonRun();
 * 
 * @author Bruce
 *
 */
class Crontab extends Base
{
    const SERVICE='crontab';
    
    /**
     * 执行Daemon任务
     */
    protected function run()
    {
        $this->getLog()->add("hello world.");
    }
    
    protected function init()
    {
        parent::init();
        
        $log=$this->getLog();
        $log->add("\n\n-----------------------Hello world service----------------------------");
        $log->flush();
    }
    
    /**
     * 发现新版本的事件
    */
    protected function onNewReleaseFind()
    {
        self::getLog()->add('Found new release: '.$this->getReleaseCheck()->getRelease().', will quit for update.');
        die();
    }
    
    /**
     * 系统运行过程检测到内存不够的事件
    */
    protected function onOutOfMemory()
    {
        self::getLog()->add('System find that daemon will be out of memory, will quit for restart.');
        die();
    } 
}