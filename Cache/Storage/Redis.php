<?php
namespace HuiLib\Cache\Storage;

/**
 * Redis基础类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class Redis extends \HuiLib\Cache\CacheBase
{
	/**
	 * Redis内部连接
	 *
	 * @var \Redis
	 */
	protected $connect;
	
	/**
	 * Redis内部数据库编号，避免命名冲突，默认0
	 *
	 * @var int
	 */
	private $db=0;
	
	protected function __construct($config)
	{
		if (empty( $config['host']) || empty($config['port'])) {
			throw new \HuiLib\Error\Exception ( "Redis配置信息错误" );
		}
		
		$this->config=$config;
		if (!empty( $config['db'] )) {
			$this->db= $config['db'];
		}
		
		$this->connect = new \Redis ();
		if (!$this->connect->connect ( $config['host'], $config['port'] )){
			throw new \HuiLib\Error\Exception ( "Redis连接失败，请确认已安装该拓展" );
		}
		if ($this->db) {
			$this->connect->select($this->db);
		}
	}
	
	/**
	 * 添加一个缓存
	 *
	 * 强制设置，强制过期
	 *
	 * @param string $key 缓存键
	 * @param mix $value 缓存值
	 * @param int $expire 过期时间，0永不过期
	 */
	public function add($key, $value, $expire=0)
	{
		return $this->connect->set($key, $value, $expire);
	}
	
	/**
	 * 替换一个已存在的缓存
	 *
	 * 强制设置，强制过期
	 *
	 * @param string $key 缓存键
	 * @param mix $value 缓存值
	 * @param int $expire 过期时间，0永不过期
	 */
	public function replace($key, $value, $expire=0)
	{
		return $this->connect->set($key, $value, $expire);
	}
	
	/**
	 * 删除一个缓存
	 *
	 * @param string $key 缓存键 支持数组，批量
	 */
	public function delete($key)
	{
		return $this->connect->delete($key);
	}
	
	/**
	 * 获取一个缓存内容
	 *
	 * @param string $key 缓存键，单个
	 */
	public function get($key)
	{
		return $this->connect->get($key);
	}
	
	/**
	 * 给缓存值加上一个数
	 *
	 * @param string $key 缓存键
	 * @param mix $value 增加的值
	 */
	public function increase($key, $value=1){
		return $this->connect->incrBy($key, $value);
	}
	
	/**
	 * 给缓存值减去一个数
	 *
	 * @param string $key 缓存键
	 * @param mix $value 减少的值
	 */
	public function decrease($key, $value=1){
		return $this->connect->decrBy($key, $value);
	}
	
	/**
	 * 清空所有数据
	 *
	 */
	public function flush(){
		return $this->connect->flushDB();
	}
	
	
	public function toString(){
		return 'redis';
	}
}