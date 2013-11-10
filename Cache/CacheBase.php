<?php
namespace HuiLib\Cache;

/**
 * 缓存功能基础类
 * 
 * Cache模块接口行为：add强制覆盖添加；addnx不存在才添加
 *
 * @author 祝景法
 * @since 2013/09/15
 */
abstract class CacheBase
{
	/**
	 * 缓存内部连接
	 *
	 * @var CacheBase
	 */
	protected $connect=NULL;
	
	/**
	 * 缓存初始化配置
	 * @var array
	 */
	protected $config=NULL;
	
	protected function __construct()
	{
	}
	
	/**
	 * 创建Cache实例factory方法
	 */
	public static function create($config)
	{
		if (empty ( $config ['adapter'] )) {
			throw new \HuiLib\Error\Exception ( 'Cache adapter can not be empty' );
		}
	
		$adapter=NULL;
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
	
	/**
	 * 获取系统默认缓存实例
	 */
	public static function getDefault(\HuiLib\Config\ConfigBase $configInstance=NULL){
		if ($configInstance===NULL) {
			$configInstance=\HuiLib\Bootstrap::getInstance()->appInstance()->configInstance();
		}
	
		$adapterName=$configInstance->getByKey('cache.defalut');
		if (empty ($adapterName)) {
			throw new \HuiLib\Error\Exception ( 'Cache default adapter has not set.' );
		}
	
		return self::staticCreate($adapterName, $configInstance);
	}
	
	/**
	 * 获取Memcache默认缓存实例
	 */
	public static function getMemcache(\HuiLib\Config\ConfigBase $configInstance=NULL){
		return self::staticCreate('cache.memcache', $configInstance);
	}
	
	/**
	 * 获取Redis默认缓存实例
	 */
	public static function getRedis(\HuiLib\Config\ConfigBase $configInstance=NULL){
		return self::staticCreate('cache.redis', $configInstance);
	}
	
	/**
	 * 获取APC默认缓存实例
	 */
	public static function getApc(\HuiLib\Config\ConfigBase $configInstance=NULL){
		return self::staticCreate('cache.apc', $configInstance);
	}
	
	/**
	 * 获取专门储存Config资源的APC实例
	 *
	 * 因为最早config还没初始化，所以不能获取配置文件中的
	 */
	public static function getApcDirectly(){
		$config=array('adapter'=>'apc', 'prefix'=>'global:');
	
		return self::create($config);
	}
	
	/**
	 * 获取File默认缓存实例
	 */
	public static function getFile(\HuiLib\Config\ConfigBase $configInstance=NULL){
		return self::staticCreate('cache.file', $configInstance);
	}
	
	private static function staticCreate($adapterName, \HuiLib\Config\ConfigBase $configInstance=NULL){
		if ($configInstance===NULL) {
			$configInstance=\HuiLib\Bootstrap::getInstance()->appInstance()->configInstance();
		}
	
		$adapterConfig=$configInstance->getByKey($adapterName);
		if (empty ( $adapterConfig )) {
			throw new \HuiLib\Error\Exception ( $adapterName.' adapter config has not set.' );
		}
	
		return self::create($adapterConfig);
	}
	
	/**
	 * 保存一个缓存
	 * 
	 * 强制设置，强制过期
	 * 
	 * @param string $key 缓存键
	 * @param mix $value 缓存值
	 */
	public abstract function add($key, $value);
	
	/**
	 * 添加一个新的缓存
	 *
	 * 如果这个key已经存在返回FALSE
	 *
	 * @param string $key 缓存键
	 * @param mix $value 缓存值
	 */
	public abstract function addnx($key, $value);
	
	/**
	 * 删除一个缓存
	 * 
	 * @param string $key 缓存键
	 */
	public abstract function delete($key);
	
	/**
	 * 获取一个缓存内容
	 * 
	 * @param string $key 缓存键
	 */
	public abstract function get($key);
	
	/**
	 * 清空所有数据
	 *
	 */
	public function flush(){
		
	}
	
	/**
	 * 给缓存值加上一个数
	 *
	 * @param string $key 缓存键
	 * @param mix $value 增加的值
	 */
	public function increase($key, $value=1){
	
	}
	
	/**
	 * 给缓存值减去一个数
	 *
	 * @param string $key 缓存键
	 * @param mix $value 减少的值
	 */
	public function decrease($key, $value=1){
	
	}
	
	/**
	 * 批量保存一个缓存
	 * 
	 * @param array $assocArray 关联数组
	 */
	public function saveBatch($assocArray)
	{
		foreach ($assocArray as $key=>$value){
			$this->add($key, $value);
		}
	}
	
	/**
	 * 批量删除一个缓存
	 * 
	 * @param array $keyArray 缓存键数组
	 */
	public function deleteBatch($keyArray)
	{
		foreach ($keyArray as $key){
			$this->delete($key);
		}
	}
	
	/**
	 * 批量获取一个缓存内容
	 * 
	 * @param array $keyArray 缓存键数组
	 */
	public function getBatch($keyArray)
	{
		$result=array();
		foreach ($keyArray as $key){
			$result[$key]=$this->get($key);
		}
		
		return $result;
	}
	
	/**
	 * 将方法重新定位到Redis对象
	 */
	function __call($method, $arguments){
		if (method_exists($this->connect, $method)){
			//需要返回结果
			return call_user_func_array(array($this->connect, $method), $arguments);
		}else{
			throw new \HuiLib\Error\Exception ( "出错了，提交了一个该缓存服务端暂不支持的方法:{$method}" );
		}
	}
}