<?php
namespace HuiLib\Module\Daemon;

use HuiLib\Log\LogBase;

/**
 * Daemon库基类
 * 
 * @author 祝景法
 * @since 2014/10/24
 */
class Base
{
    const LOG_SERVICE=NULL;
    
    /**
     * 运行的服务
     *
     * @var string
     */
    protected $service=NULL;
    
    private $logInstance=NULL;
    
    public function __construct($service)
    {
        if (!$service || preg_match('/[^\w]/is', $service)) {
            throw new \Exception("\$service 参数无效。");
        }
        
        $this->service=$service;
    }
    
    /**
     * 获取日志对象
     */
    protected function getLog()
    {
        if ($this->logInstance!==NULL) {
            return $this->logInstance;
        }
        
        if (!static::LOG_SERVICE || preg_match('/[^\w]/is', static::LOG_SERVICE)) {
            throw new \Exception("static::LOG_SERVICE 无效。");
        }
        
        $this->logInstance=LogBase::getFile()->setIdentify(static::LOG_SERVICE)->setType(LogBase::TYPE_DAEMON);

        return $this->logInstance;
    }
}