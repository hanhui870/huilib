<?php
namespace HuiLib\Cache\Storage;

/**
 * File基础类
 * 
 * TODO File级缓存
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class File extends \HuiLib\Cache\CacheBase
{
	protected function __construct($config)
	{
	
	}
	
	public function toString(){
		return 'file';
	}
}