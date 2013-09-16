<?php
namespace HuiLib\Cache;

/**
 * 缓存功能基础类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class CacheBase
{
	protected function __construct($config)
	{
	
	}
	
	/**
	 * 保存一个缓存
	 */
	public function save($key, $value)
	{
		
	}
	
	/**
	 * 删除一个缓存
	 */
	public function delete($key)
	{
	
	}
	
	/**
	 * 获取一个缓存内容
	 */
	public function get($key)
	{
	
	}
	
	/**
	 * 批量保存一个缓存
	 * 
	 * @param array $assocArray 关联数组
	 */
	public function saveBatch($assocArray)
	{
	
	}
	
	/**
	 * 批量删除一个缓存
	 * 
	 * @param array $keyArray 缓存键数组
	 */
	public function deleteBatch($keyArray)
	{
	
	}
	
	/**
	 * 批量获取一个缓存内容
	 * 
	 * @param array $keyArray 缓存键数组
	 */
	public function getBatch($keyArray)
	{
	
	}

	/**
	 * 创建Cache实例factory方法
	 */
	public static function create($config)
	{
		if (empty ( $config ['adapter'] )) {
			throw new \HuiLib\Error\Exception ( 'Cache adapter can not be empty!' );
		}
		
		switch ($config ['adapter']) {
			case 'redis' :
				$adapter = new \HuiLib\Cache\Storage\Redis ( $config );
				break;
			case 'memcache' :
				$adapter = new \HuiLib\Cache\Storage\Memcache ( $config );
				break;
			case 'apc' :
				$adapter = new \HuiLib\Cache\Storage\Apc ( $config );
				break;
			case 'file' :
				$adapter = new \HuiLib\Cache\Storage\File ( $config );
				break;
		}
		
		return $adapter;
	}
}