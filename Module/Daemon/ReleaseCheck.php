<?php
namespace HuiLib\Module\Daemon;

/**
 * 新版本扫描
 * 
 * @author 祝景法
 * @since 2014/10/24
 */
class ReleaseCheck extends Base
{
    const LOG_SERVICE='release';
    
    /**
     * 新版本检测时间间隔
     *
     * 单位秒 默认1分钟
     *
     * @var int
     */
    const RELEASE_CHECK_INTERVAL=60;

    /**
     * 上次版本检测
     * @var timestamp
     */
    protected $lastReleaseCheck=0;

    /**
     * 上次版本
     * @var string
     */
    protected $lastRelease=NULL;
    
    /**
     * 储存路径
     */
    private $filePath=NULL;
    
    /**
     * 文件名称
     */
    private $fileName=NULL;
    
    public function __construct($service)
    {
        parent::__construct($service);
    
        //日志保存目录
        $this->filePath=APP_DATA.'/Release/';
    
        if (!is_dir($this->filePath)) {
            mkdir($this->filePath, 0777, TRUE);
        }
    }
    
    /**
     * 确认。
     *
     * @return boolean true未发现新版本 false发现新版本
     */
    public function check()
    {
        //检测时效 单位时间内不用重复检测
        if (microtime(1)<$this->lastReleaseCheck+self::RELEASE_CHECK_INTERVAL){
            return true;
        }
        $this->lastReleaseCheck=microtime(1);
        
        $log=self::getLog();
        
        $release=trim(self::getRelease());
        if ($this->lastRelease===NULL){
            //第一次是初始化上次版本，然后直接返回
            $this->lastRelease=$release;
            $log->add("New release check, version inited to: ".$this->lastRelease)->flush();
            return TRUE;
        }

        $log->add("New release check.");
        
        //可以活性设置生效时间
        if (!empty($release) && strtotime($release)!=strtotime($this->lastRelease) && strtotime($release)<time()){
            ////////////发现新版本，触发事件/////////////
            $message="A new release:".$release." found, trigger event.";
            $log->add($message);
            $log->flush();
            return false;
        }
        
        return TRUE;
    }
    
    private function getFileName()
    {
        if ($this->fileName!==NULL){
            return $this->fileName;
        }
    
        $this->fileName=$this->filePath.$this->service.'.txt';
        return $this->fileName;
    }
    
    
    public function getRelease()
    {
        if (file_exists($this->getFileName())){
            return file_get_contents($this->getFileName());
        }else{
            echo "版本文件：".$this->getFileName().' 不存在。';
            die();
        }
    }
}