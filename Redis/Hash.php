<?php
namespace HuiLib\Redis;

use HuiLib\Error\Exception;

/**
 * Redis HashTable基础管理类
 * 
 * 通过在变量中嵌入RedisUpdate字段值触发更新机制
 *
 * @author 祝景法
 * @since 2013/12/14
 */
class Hash extends RedisBase
{
	/**
	 * 表类常量定义
	 * @var string
	 */
	const TABLE_CLASS=NULL;
	
	/**
	 * Hash保存修改过的键
	 * @var string
	 */
	const EDIT_FIELD_KEY='RedisEdited';
	
	/**
	 * 行数据储存
	 * @var array
	 */
	protected $data=array();
	
	/**
	 * 行默认初始化数据
	 * @var array
	*/
	protected static $initData=NULL;
	
	/**
	 * 修改数据储存
	 * @var array
	 */
	protected $editData=array();
	
	/**
	 * 主键字段，通过行类获取
	 * @var string
	*/
	protected $primaryIdKey=NULL;
	
	/**
	 * 是否从迟久库新获取
	 * @var boolean
	 */
	protected $FromDb=FALSE;
	
	protected function __construct()
	{
		//经测试，第一个需要加载类情况，比较慢,5ms内。第二行不用加载了就比较快1/100ms左右。
		static::$initData=self::getRowInitData();
		$this->primaryIdKey=self::getRowPrimaryIdKey();
		
		if (static::$initData===NULL || $this->primaryIdKey===NULL) {
			throw new Exception('Row class static::$initData or primaryIdKey parsed NULL.');
		}
	}

	/**
	 * 通过主ID获取
	 */
	public function initByPrimaryIdKey($primaryIdValue)
	{
		//首先尝试Redis获取
		$data=$this->getAdapter()->hGetAll($this->getRedisKey($primaryIdValue));
		//超过缓存有效期，同步数据
		if (empty($data->RedisUpdate) || time()-$data->RedisUpdate>self::CACHE_SYNC_INTERVAL) {
			$this->flushEditedToDb($data);
			unset($data);
		}
		
		if (empty($data)) {
			$data=$this->fetchFromDb($primaryIdValue);
		}

		return $this->applyData($data);
	}
	
	/**
	 * 从数据库获取值
	 * 
	 * @param int $primaryIdValue
	 * @return array
	 */
	protected function fetchFromDb($primaryIdValue)
	{
		//通过主键尝试数据表获取数据
		$tableClass=static::TABLE_CLASS;
		$data=$tableClass::create()->getRowByField($this->primaryIdKey, $primaryIdValue);
		if (empty($data)) {
			return array();
		}
		
		//redis更新时间戳
		$data->RedisUpdate=time();
		$this->FromDb=TRUE;
		
		return $data->toArray();
	}
	
	/**
	 * 将键值数据应用到对象上
	 * @param array $data
	 */
	protected function applyData($data)
	{
		if (empty($data)) {
			return FALSE;
		}
		
		foreach ($data as $key=>$value){
			if(isset(static::$initData[$key])){
				$this->data[$key]=$value;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * 将编辑的数据推送到迟久库
	 * 
	 * 更新编辑数据到数据库 仅数字增减等非完全覆盖修改
	 * 默认情况下不需要编辑，因为编辑一般直接获取数据库表的Model对象。此处一般做相对增减。
	 *
	 * @param array $data 缓存数据
	 */
	protected function flushEditedToDb($data)
	{
		//通过主键尝试数据表获取行数据
		$tableClass=static::TABLE_CLASS;
		$rowObj=$tableClass::create()->getRowByField($this->primaryIdKey, $data[$this->primaryIdKey]);
		
		if (empty($data[self::EDIT_FIELD_KEY]) || empty($rowObj)) {
			return FALSE;
		}

		//更新影响值
		foreach ($data[self::EDIT_FIELD_KEY] as $key=>$value){
			$rowObj->$key+=$value;
		}
		
		return $rowObj->save();
	}
	
	/**
	 * 获取redis储存键
	 * 
	 * @param mix $primaryIdValue
	 */
	protected function getRedisKey($primaryIdValue)
	{
		if (static::TABLE_CLASS===NULL) {
			throw new Exception('Model table class has not been set.');
		}
		return self::KEY_PREFIX.static::TABLE_CLASS.':'.$primaryIdValue;
	}
	
	/**
	 * 初始化表行默认初始化数据
	 * @return array
	 */
	protected static function getRowInitData()
	{
		$tableClass=static::TABLE_CLASS;
		return $tableClass::getRowInitData();
	}
	
	/**
	 * 初始化表行主键名
	 * @return array
	 */
	protected function getRowPrimaryIdKey()
	{
		$tableClass=static::TABLE_CLASS;
		$rowClass=$tableClass::ROW_CLASS;
		return $rowClass::PRIMAY_IDKEY;
	}
	
	/**
	 * 快速创建Redis数据模型
	 * 
	 * @param string $primaryIdValue
	 * @return \HuiLib\Redis\HashTable
	 */
	public static function create($primaryIdValue=NULL)
	{
		$instance=new static();
		if ($primaryIdValue!==NULL) {
			$instance->initByPrimaryIdKey($primaryIdValue);
		}
		
		return $instance;
	}
	
	/**
	 * 强制刷新Redis数据缓存，同时更新编辑过的数据
	 *
	 * @param string $primaryIdValue
	 * @return \HuiLib\Redis\HashTable
	 */
	public static function refresh($primaryIdValue)
	{
		$instance=new static();

		//首先尝试Redis获取
		$data=$instance->getAdapter()->hGetAll($instance->getRedisKey($primaryIdValue));
		if (empty($data)) {//不存在不用处理
			return TRUE;
		}
		
		//写入数据库
		$instance->flushEditedToDb($data);
		
		//删除缓存，下次自动获取
		$instance->getAdapter()->del($instance->getRedisKey($primaryIdValue));
		
		return TRUE;
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
		if (isset($this->data[$key])) {
			$this->originalData[$key]=$this->data[$key];
			$this->data[$key]=$value;
			$this->editData[$key]=$this->data[$key]-$this->originalData[$key];
			return TRUE;
		}
		return FALSE;
	}
	
	protected function getFinalCacheData()
	{
		if (!empty($this->editData)) {
			$this->data[self::EDIT_FIELD_KEY]=$this->editData;
		}
		return $this->data;
	}
	
	public function __destruct()
	{
		//对象销毁自动触发保存到redis
		if ($this->FromDb || !empty($this->editData)) {
			$this->getAdapter()->hMset($this->getRedisKey($this->data[$this->primaryIdKey]), $this->getFinalCacheData());
		}
	}
}