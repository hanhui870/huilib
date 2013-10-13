<?php
namespace HuiLib\Cache\Storage;

/**
 * Memcache基础类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class Memcache extends \HuiLib\Cache\CacheBase
{
	/**
	 * Memcache内部连接
	 * 
	 * @var \Memcache
	 */
	protected $connect;
	
	/**
	 * Memcache库键前缀 防止多应用实例名称冲突
	 * 
	 * 通过prefix隔离命名空间
	 * 
	 * @var string
	 */
	private $prefix='';
	
	protected function __construct($config)
	{
		if (empty( $config['host']) || empty($config['port'])) {
			throw new \HuiLib\Error\Exception ( "Memcache配置信息错误" );
		}
		
		$this->config=$config;
		if (empty( $config['prefix'] )) {
			$this->prefix= $config['prefix'];
		}
		
		$this->connect = new \Memcache ();
		if (!$this->connect->connect ( $config['host'], $config['port'] )){
			throw new \HuiLib\Error\Exception ( "Memcache连接失败，请确认已安装该拓展" );
		}
	}
	
	/**
	 * 添加一个缓存
	 * 
	 * 修改为: 强制设置，强制过期
	 * 
	 * @param string $key 缓存键
	 * @param mix $value 缓存值
	 * @param string $flag 是否压缩 MEMCACHE_COMPRESSED(2)使用zlib压缩
	 * @param int $expire 过期时间，0永不过期，最大30天(2592000) 或unix时间戳
	 */
	public function add($key, $value, $flag=FALSE, $expire=0)
	{
		if (!$this->connect->add($this->prefix.$key, $value, $flag, $expire)) {//只能新添加一个值，重复刷新不会覆盖
			return $this->connect->replace($this->prefix.$key, $value, $flag, $expire);//必须是已存在的，不存在的会导致设置失败
		}
		return true;
	}
	
	/**
	 * 替换一个已存在的缓存
	 *
	 * 修改为: 强制设置，强制过期
	 * 
	 * @param string $key 缓存键
	 * @param mix $value 缓存值
	 * @param string $flag 是否压缩 MEMCACHE_COMPRESSED(2)使用zlib压缩
	 * @param int $expire 过期时间，0永不过期，最大30天(2592000) 或unix时间戳
	 */
	public function replace($key, $value, $flag=FALSE, $expire=0)
	{
		return $this->add($key, $value, $flag, $expire);
	}
	
	/**
	 * 删除一个缓存
	 * 
	 * @param string $key 缓存键
	 * @param int $timeout 超时时间，多久后删除
	 */
	public function delete($key, $timeout = 0)
	{
		return $this->connect->delete($this->prefix.$key, $timeout);
	}
	
	/**
	 * 获取一个缓存内容
	 * 
	 * @param string $key 缓存键，支持多键
	 * @param string $flags 引用，二进制，1位是否序列化，2位是否压缩
	 */
	public function get($key, $flags=NULL)
	{
		return $this->connect->get($this->prefix.$key, $flags);
	}
	
	/**
	 * 给缓存值加上一个数
	 *
	 * @param string $key 缓存键
	 * @param mix $value 增加的值
	 */
	public function increase($key, $value=1){
		return $this->connect->increment($this->prefix.$key, $value);
	}
	
	/**
	 * 给缓存值减去一个数
	 *
	 * @param string $key 缓存键
	 * @param mix $value 减少的值
	 */
	public function decrease($key, $value=1){
		return $this->connect->decrement($this->prefix.$key, $value);
	}
	
	/**
	 * 清空所有数据
	 *
	 */
	public function flush(){
		return $this->connect->flush();
	}
	
	public function toString(){
		return 'memcache';
	}
	
}