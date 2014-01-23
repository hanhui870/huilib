<?php
namespace HuiLib\Cache\Test;

use HuiLib\App\Front;

/**
 * 缓存测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class CacheTest extends \HuiLib\Test\TestBase
{

	public function run()
	{
		$this->testMemcache ();
	}

	private function testMemcache()
	{
		$cache = \HuiLib\Cache\CacheBase::create ( Front::getInstance()->getAppConfig()->getByKey ( 'cache.memcache' ) );
		$cache->add ( 'hanhui2', date ( 'Y-m-d H:i:s' ) );
		echo $cache->get ( 'hanhui2' );
		
		//测试数组
		$cache->add ( 'array', Front::getInstance()->getAppConfig()->getByKey ( 'cache.memcache' ) );
		\HuiLib\Helper\Debug::out ( $cache->get ( 'array' ) );
		
		$cache->add ( 'count', 0 );//行为每次改为初始化为1
		$cache->increase ( 'count' );
		$cache->increase ( 'count' );
		echo $cache->get ( 'count' );
	}

	private function testRedis()
	{
		$cache = \HuiLib\Cache\CacheBase::create ( Front::getInstance()->getAppConfig()->getByKey ( 'cache.redis' ) );
		
		//调用内部方法
		$cache->hset ( 'hanhui2', 'mm', date ( 'Y-m-d H:i:s' ) );
		\HuiLib\Helper\Debug::out ( $cache->hGetAll ( 'hanhui2' ) );
		
		//测试数组
		$cache->hMset ( 'array', Front::getInstance()->getAppConfig()->getByKey ( 'cache.redis' ) );
		\HuiLib\Helper\Debug::out ( $cache->hGetAll ( 'array' ) );
		
		$cache->replace ( 'count', 0, 0 );
		$cache->increase ( 'count' );
		$cache->increase ( 'count' );
		echo $cache->get ( 'count' );
		
		//清空数据
		//$cache->flush();
	}

	private function testApc()
	{
		$cache = \HuiLib\Cache\CacheBase::create ( Front::getInstance()->getAppConfig()->getByKey ( 'cache.apc' ) );
		$cache->add ( 'hanhui2', date ( 'Y-m-d H:i:s' ) );
		echo $cache->get ( 'hanhui2' );
		
		//测试数组
		$cache->replace ( 'array', Front::getInstance()->getAppConfig()->getByKey ( 'cache.memcache' ) );
		\HuiLib\Helper\Debug::out ( $cache->get ( 'array' ) );
		
		$cache->add ( 'count', 0 );
		$cache->increase ( 'count' );
		echo $cache->get ( 'count' );
		
		$cache->replace ( 'replace', 0 );
		$cache->increase ( 'replace' );
		$cache->increase ( 'replace' );
		$cache->increase ( 'replace' );
		echo 'replace:' . $cache->get ( 'replace' );
	}
	
	/**
	 * 测试通过静态函数获取
	 */
	private function testStaticInit()
	{
		$cache=\HuiLib\Cache\CacheBase::getDefault(Front::getInstance()->getAppConfig());
		echo $cache->toString();
		$cache->add ( 'hanhui2', date ( 'Y-m-d H:i:s' ) );
		echo $cache->get ( 'hanhui2' );
		
		//测试数组
		$cache->replace ( 'array', Front::getInstance()->getAppConfig()->getByKey ( 'cache.memcache' ) );
		\HuiLib\Helper\Debug::out ( $cache->get ( 'array' ) );
		
		$cache->add ( 'count', 0 );
		$cache->increase ( 'count' );
		echo $cache->get ( 'count' );
		
		$cache=\HuiLib\Cache\CacheBase::getRedis(Front::getInstance()->getAppConfig());
		echo $cache->toString();
		
		$cache=\HuiLib\Cache\CacheBase::getMemcache(Front::getInstance()->getAppConfig());
		echo $cache->toString();
		
		$cache=\HuiLib\Cache\CacheBase::getApc(Front::getInstance()->getAppConfig());
		echo $cache->toString();
		
		$cache=\HuiLib\Cache\CacheBase::getFile(Front::getInstance()->getAppConfig());
		echo $cache->toString();
	}

	protected static function className()
	{
		return __CLASS__;
	}
}