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