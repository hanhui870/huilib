<?php
namespace HuiLib\Cache\Test;

/**
 * 缓存测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class CacheTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testMemcache();
	}
	
	private function testMemcache(){
		$cache=\HuiLib\Cache\CacheBase::create($this->appInstance->configInstance()->getByKey('cache.memcache'));
		$cache->add('hanhui2', date('Y-m-d H:i:s'));
		echo $cache->get('hanhui2');
		
		//测试数组
		$cache->replace('array', $this->appInstance->configInstance()->getByKey('cache.memcache'));
		\HuiLib\Helper\Debug::out ( $cache->get('array') );
		
		$cache->add('count', 0);
		$cache->increase('count');
		echo $cache->get('count');
	}

	protected static function className(){
		return __CLASS__;
	}
}