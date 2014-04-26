<?php
namespace HuiLib\Redis;

use HuiLib\Error\Exception;

/**
 * Redis HashPure基础管理类
 *
 * 与数据库无任何连通，单独的Redis
 *
 * @author 祝景法
 * @since 2014/01/04
 */
abstract class HashPure extends RedisBase
{
	/**
	 * Redis键前缀，需要和父类的组合
	 * @var string
	 */
	const KEY_PREFIX='hash:pure:';
	
	/**
	 * 行数据储存
	 * @var array
	 */
	protected $data=array();
	
	/**
	 * 行默认初始化数据
	 *
	 * @var array
	*/
	protected static $initData=NULL;
	
	/**
	 * 主键值，生成Key
	 * @var string
	 */
	protected $primaryValue=NULL;
	
	/**
	 * 内部对象缓存
	 * @var array
	 */
	private static $innerInstance=array();
	
	/**
	 * 是否被删除 被删除 不回写
	 * @var boolean
	 */
	private $isDeleted=FALSE;
	
	protected function __construct($primaryValue)
	{
	    $this->primaryValue=$primaryValue;
	    
	    if (static::$initData===NULL) {
	        throw new Exception('Hash Pure static::$initData or static::HASH_SERVICE parsed NULL.');
	    }
	    
	    //首先尝试Redis获取
	    $cache=$this->getAdapter()->hGetAll($this->getRedisKey());

	    if (empty($cache)) {
	        $this->data=static::$initData;
	    }else{
	        $this->data=$cache;
	    }
	}
	
	/**
	 * 返回对象数据是否为空
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
	    return empty($this->data);
	}
	
	public function __get($key)
	{
	    if (isset($this->data[$key])) {
	        return $this->data[$key];
	    }else{
	        return NULL;
	    }
	}
	
	/**
	 * 修改数据
	 * @param string $key
	 * @param number $value
	 * @return boolean
	 */
	public function __set($key, $value)
	{
	    if (array_key_exists($key, $this->data)) {
	        $this->data[$key]=$value;
	        return TRUE;
	    }
	    return FALSE;
	}
	
	/**
	 * 动态增减某些键值
	 * @param string $key
	 * @param number $value
	 */
	public function incrValue($key, $value)
	{
	    if ( !is_numeric($value) ) {
	        throw new Exception('Number of $value is required by incrKey() method.');
	    }
	
	    if (array_key_exists($key, $this->data)){
	        $this->data[$key]+=$value;
	        return TRUE;
	    }
	    return FALSE;
	}
	
	/**
	 * 快速创建Redis数据模型
	 *
	 * @param string $primaryValue
	 * @return \HuiLib\Redis\HashPure
	 */
	public static function create($primaryValue=NULL)
	{
	    if ($primaryValue===NULL) {
	        return NULL;
	    }
	    if (isset(self::$innerInstance[static::getClass()][$primaryValue])) {
	        $instance=self::$innerInstance[static::getClass()][$primaryValue];
	        if ($instance instanceof static) {
	            return $instance;
	        }
	    }
	     
	    $instance=new static($primaryValue);
	    //缓存
	    self::$innerInstance[static::getClass()][$primaryValue]=$instance;
	
	    return $instance;
	}
	
	/**
	 * 获取redis储存键
	 *
	 * @param mix $primaryValue
	 */
	protected function getRedisKey()
	{
	    $spaceInfo=explode(NAME_SEP, static::getClass());
	    return parent::KEY_PREFIX.self::KEY_PREFIX. array_pop($spaceInfo) .':'.$this->primaryValue;
	}
	
	/**
	 * 删除这条数据
	 */
	public function delete()
	{
	    $this->isDeleted=TRUE;
	    return $this->getAdapter()->del($this->getRedisKey());
	}
	
	public function __destruct()
	{
	    //对象销毁自动触发保存到redis
	    if (!$this->isDeleted){
	        //编辑模式，仅更新编辑数据
	        $this->getAdapter()->hMset($this->getRedisKey(), $this->data);
	    }
	}
	
	/**
	 * 返回对象的数组表示
	 * @return array
	 */
	public function toArray()
	{
	    return $this->data;
	}
	
	protected static function getClass(){}
}