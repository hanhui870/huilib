<?php
namespace HuiLib\Module\Daemon;

/**
 * 单进程守护
 * 
 * ps检测进程仅在类unix、cygwin中能测试通过，mingw不支持ps -p命令。
 * 
 * @author Bruce
 * @since 2014/10/24
 */
class SingleProcessGuard extends Base
{
    const LOG_SERVICE='guard';
    
    /**
     * 储存路径
     */
    private $filePath=NULL;
    
    /**
     * 文件名称
     */
    private $fileName=NULL;
    
    /**
     * 文件描述符
     */
    private $fileFd=NULL;
    
    public function __construct($service)
    {
        parent::__construct($service);
        
        //日志保存目录
        $this->filePath=APP_DATA.'/Pid/';
        
        if (!is_dir($this->filePath)) {
            mkdir($this->filePath, 0777, TRUE);
        }
    }
    
    /**
     * 确认。
     * 
     * @return boolean true代表正常，单进程 false不正常，直接退出
     */
    public function check()
    {
        $log=self::getLog();
        if (!file_exists($this->getFileName())) {
            $this->writePid();
            $log->add('Service '.$this->service.' guard无并发进程， 确认成功');
            $log->release();
            return true;
        }
        
        $pid=file_get_contents($this->getFileName());
        //var_dump($pid);
        $pid=intval($pid);
        if ($pid<1) {
            $message='Service '.$this->service.' 获取pid失败，被其他进程锁住。';
            $log->add($message);
            die();
        }
        
        $status=exec('ps -p '.$pid, $output);
        /**
         * 正常情况下包括1个，如果没有启动任务 正常情况下一般都只有一条，因为被锁住是获取不到的
         * Array
         *   (
         *        [0] =>       PID    PPID    PGID     WINPID   TTY     UID    STIME COMMAND
         *   )
        */
        if (count($output)<=1){
            $this->writePid();
            $log->add("Service ".$this->service.' guard存在旧pid文件，无并发进程，确认成功');
            $log->release();

            return true;
        }else{
            $message= 'Service '.$this->service."已存在运行进程，启动失败。";
            $log->add($message);
        }

        die();
    }
    
    private function getFileName()
    {
        if ($this->fileName!==NULL){
            return $this->fileName;
        }
    
        $this->fileName=$this->filePath.$this->service.'.pid';
        return $this->fileName;
    }
    
    private function writePid()
    {
        $handle=fopen($this->getFileName(), 'wb+');
        fwrite($handle, getmypid());
        flock($handle, LOCK_EX);
        
        $this->fileFd=$handle;
        return true;
    }
}