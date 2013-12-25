<?php
namespace HuiLib\Redis;

use HuiLib\Cache\CacheBase;

/**
 * Redis Model基础管理类
 *
 * @author 祝景法
 * @since 2013/12/14
 */
abstract class RedisBase
{
	/**
	 * Redis键前缀
	 * @var string
	 */
	const KEY_PREFIX='model:';
	
	/**
	 * 缓存更新触发机制
	 * @var int
	 */
	const CACHE_SYNC_INTERVAL=600;

	/**
	 * Redis适配器
	 * @var \HuiLib\Cache\Storage\Redis
	 */
	protected $adapter=NULL;
	
	/**
	 * 基础APP实例
	 * @var \HuiLib\App\AppBase
	 */
	protected $appInstance;

	protected function __construct()
	{
	}
	
	/**
	 * 获取Redis适配器
	 * @return \HuiLib\Cache\Storage\Redis
	 */
	protected function getAdapter()
	{
		if ($this->adapter===NULL) {
			$this->adapter=CacheBase::getRedis();
		}
	
		return $this->adapter;
	}
	
	/**
	 * 获取应用实例
	 */
	protected function getAppInstace()
	{
		if ($this->appInstance===NULL) {
			$this->appInstance=\HuiLib\Bootstrap::getInstance()->appInstance();
		}
	
		return $this->appInstance;
	}
	
	/**
	 * 设置应用实例
	 */
	public function setAppInstance(\HuiLib\App\AppBase $appInstance=NULL)
	{
		$this->appInstance=$appInstance;
	
		return $this;
	}
	

	/**
	 * 快速创建Redis数据模型
	 * 
	 * @return \HuiLib\Redis\RedisBase
	 */
	public static function create()
	{
		$instance=new static();
	
		return $instance;
	}
}