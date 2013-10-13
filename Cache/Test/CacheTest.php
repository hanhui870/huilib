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

	public function run()
	{
		$this->testMemcache ();
	}

	private function testMemcache()
	{
		$cache = \HuiLib\Cache\CacheBase::create ( $this->appInstance->configInstance ()->getByKey ( 'cache.memcache' ) );
		$cache->add ( 'hanhui2', date ( 'Y-m-d H:i:s' ) );
		echo $cache->get ( 'hanhui2' );
		
		//测试数组
		$cache->replace ( 'array', $this->appInstance->configInstance ()->getByKey ( 'cache.memcache' ) );
		\HuiLib\Helper\Debug::out ( $cache->get ( 'array' ) );
		
		$cache->add ( 'count', 0 );//行为每次改为初始化为1
		$cache->increase ( 'count' );
		$cache->increase ( 'count' );
		echo $cache->get ( 'count' );
	}

	private function testRedis()
	{
		$cache = \HuiLib\Cache\CacheBase::create ( $this->appInstance->configInstance ()->getByKey ( 'cache.redis' ) );
		
		//调用内部方法
		$cache->hset ( 'hanhui2', 'mm', date ( 'Y-m-d H:i:s' ) );
		\HuiLib\Helper\Debug::out ( $cache->hGetAll ( 'hanhui2' ) );
		
		//测试数组
		$cache->hMset ( 'array', $this->appInstance->configInstance ()->getByKey ( 'cache.redis' ) );
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
		$cache = \HuiLib\Cache\CacheBase::create ( $this->appInstance->configInstance ()->getByKey ( 'cache.apc' ) );
		$cache->add ( 'hanhui2', date ( 'Y-m-d H:i:s' ) );
		echo $cache->get ( 'hanhui2' );
		
		//测试数组
		$cache->replace ( 'array', $this->appInstance->configInstance ()->getByKey ( 'cache.memcache' ) );
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
		$cache=\HuiLib\Cache\CacheBase::getDefault($this->appInstance->configInstance ());
		echo $cache->toString();
		$cache->add ( 'hanhui2', date ( 'Y-m-d H:i:s' ) );
		echo $cache->get ( 'hanhui2' );
		
		//测试数组
		$cache->replace ( 'array', $this->appInstance->configInstance ()->getByKey ( 'cache.memcache' ) );
		\HuiLib\Helper\Debug::out ( $cache->get ( 'array' ) );
		
		$cache->add ( 'count', 0 );
		$cache->increase ( 'count' );
		echo $cache->get ( 'count' );
		
		$cache=\HuiLib\Cache\CacheBase::getRedis($this->appInstance->configInstance ());
		echo $cache->toString();
		
		$cache=\HuiLib\Cache\CacheBase::getMemcache($this->appInstance->configInstance ());
		echo $cache->toString();
		
		$cache=\HuiLib\Cache\CacheBase::getApc($this->appInstance->configInstance ());
		echo $cache->toString();
		
		$cache=\HuiLib\Cache\CacheBase::getFile($this->appInstance->configInstance ());
		echo $cache->toString();
	}

	protected static function className()
	{
		return __CLASS__;
	}
}