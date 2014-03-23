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
	 * Redis更新触发时间戳，避免冲突
	 * @var int timestamp
	 */
	const REDIS_UPDATE_KEY='RedisUpdate_jfu1o8papfeu5yir6cf5wc5tz8xjn8qw';
	
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
	 * @return \Redis
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