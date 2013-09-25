<?php
namespace HuiLib\Cache\Storage;

/**
 * Apc基础类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class Apc extends \HuiLib\Cache\CacheBase
{
	protected function __construct($config)
	{
		
	}
	
	/**
	 * 保存一个缓存
	 */
	public function add($key, $value)
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
	
	public function toString(){
		return 'apc';
	}
}