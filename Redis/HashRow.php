<?php
namespace HuiLib\Redis;

use HuiLib\Error\Exception;

/**
 * Redis HashRow基础管理类
 * 
 * 如果数据从数据库提取出来即被编辑incrdata会被覆盖两遍。但是还好不会影响数据库一致性。
 * 
 * 通过在变量中嵌入RedisUpdate字段值触发更新机制。
 * 全局单个对象唯一机制，改为覆盖影响值，编辑时不回写原值。确保读写数据一致。不然可能存在脏写现象。
 *
 * @author 祝景法
 * @since 2013/12/14
 */
abstract class HashRow extends RedisBase
{
	/**
	 * 表类常量定义
	 * @var string
	 */
	const TABLE_CLASS=NULL;
	
	/**
	 * Redis键前缀，需要和父类的组合
	 * @var string
	 */
	const KEY_PREFIX='hash:row:';
	
	/**
	 * Hash保存修改过的键
	 * @var string
	 */
	const EDIT_FIELD_KEY='RedisEdited_jfu1o8papfeu5yir6cf5wc5tz8xjn8qw';
	
	/**
	 * Hash保存增减过的键
	 * @var string
	 */
	const INCR_FIELD_KEY='RedisIncred_jfu1o8papfeu5yir6cf5wc5tz8xjn8qw';
	
	/**
	 * 行数据储存
	 * @var array
	 */
	protected $data=array();
	
	/**
	 * 行默认初始化数据
	 * 
	 * 子类可以必须具体定义字段，来指定具体保存的字段，不然会窜参数。
	 * 
	 * @var array
	*/
	protected static $initData=NULL;
	
	/**
	 * 修改数据储存
	 * 
	 * RedisHash也支持直接编辑数据，但是全覆盖提交
	 * 优先处理incrData，两者字段互斥
	 * 
	 * 另外，不同于数据库行模型
	 * 
	 * @var array
	 */
	protected $editData=array();
	
	/**
	 * 通过Incr操作修改的数据
	 * 
	 * 不是直接覆盖，而是通过Update附加影响数据；支持正负。
	 * 
	 * @var array
	 */
	protected $incrData=array();
	
	/**
	 * 主键字段，通过行类获取
	 * @var string
	*/
	protected $primaryIdKey=NULL;
	
	/**
	 * 是否从迟久库新获取
	 * @var boolean
	 */
	protected $fromDb=FALSE;
	
	//数据中获取的数据
	private $dbData=NULL;
	
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
	
	protected function __construct()
	{
		//经测试，第一个需要加载类情况，比较慢,5ms内。第二行不用加载了就比较快1/100ms左右。
		if (static::$initData===NULL) {
		    static::$initData=self::getRowInitData();
		}
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
		$this->applyData($data);

		//超过缓存有效期，同步数据
		if (!empty($data)&&(empty($data[self::REDIS_UPDATE_KEY]) || time()-$data[self::REDIS_UPDATE_KEY]>static::CACHE_SYNC_INTERVAL)) {
			$this->flushEditedAndDelete();
			unset($data);
		}
		
		if (empty($data)) {
			$data=$this->fetchFromDb($primaryIdValue);
			$this->applyData($data);
		}

		return TRUE;
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
		
		//数据库数据缓存
		$this->dbData=$data->toArray();
		
		//redis更新时间戳 $data->RedisUpdate更新失败，非库中键
		$this->data[self::REDIS_UPDATE_KEY]=time();
		$this->fromDb=TRUE;
		$this->isDeleted=FALSE;
		
		return $this->dbData;
	}
	
	/**
	 * 将键值数据应用到对象上
	 * 
	 * @param array $data
	 */
	protected function applyData($data)
	{
		if (empty($data)) {
			return FALSE;
		}
		
		if (!empty($data[self::EDIT_FIELD_KEY])) {
			$this->editData=json_decode($data[self::EDIT_FIELD_KEY], TRUE);
		}
		if (!empty($data[self::INCR_FIELD_KEY])) {
			$this->incrData=json_decode($data[self::INCR_FIELD_KEY], TRUE);
		}
		
		foreach ($data as $key=>$value){
			if(isset(static::$initData[$key])){
				$this->data[$key]=$value;
			}elseif (in_array($key, array(self::REDIS_UPDATE_KEY))){
			    //redis update等元数据映射
				$this->data[$key]=$value;
			}
		}
		
		if ((!empty($this->editData) || !empty($this->incrData))) {
		    //更新增减影响值
		    foreach ($this->incrData as $key=>$value){
		        $this->data[$key]+=$value;
		    }
		    	
		    //更新影响值
		    foreach ($this->editData as $key=>$value){
		        //有增减影响，直接忽略编辑的
		        if (isset($this->incrData[$key])) continue;
		        $this->data[$key]=$value;
		    }
		}
		
		return TRUE;
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
	
	/**
	 * 将编辑的数据推送到迟久库，并删除缓存
	 * 
	 * 更新编辑数据到数据库 仅数字增减等非完全覆盖修改
	 * 默认情况下不需要编辑，因为编辑一般直接获取数据库表的Model对象。此处一般做相对增减。
	 *
	 * @param array $data 缓存数据
	 */
	public function flushEditedAndDelete()
	{
	    $primaryValue=$this->data[$this->primaryIdKey];
		//先删除缓存，避免可能在行对象保存后事件中激发refresh引发递归，只尝试保存一次
		$this->getAdapter()->del($this->getRedisKey($primaryValue));
		
		//通过主键尝试数据表获取行数据，锁定
		$tableClass=static::TABLE_CLASS;
		$rowObj=$tableClass::create()->enableForUpdate()->getRowByField($this->primaryIdKey, $primaryValue);

		if ((!empty($this->editData) || !empty($this->incrData)) && $rowObj) {
			//更新增减影响值
			foreach ($this->incrData as $key=>$value){
				$rowObj->$key+=$value;
			}
			
			//更新影响值
			foreach ($this->editData as $key=>$value){
				//有增减影响，直接忽略编辑的
				if (isset($this->incrData[$key])) continue;
				$rowObj->$key=$value;
			}
			//echo $rowObj->getSaveSql();
			if($rowObj->save()){
				$this->editData=array();
				$this->incrData=array();
				$this->data=array();
			}
		}
		
		//清空缓存
		self::$innerInstance[static::getClass()][$primaryValue]=NULL;
		$this->isDeleted=TRUE;

		return TRUE;
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
		$spaceInfo=explode(NAME_SEP, static::TABLE_CLASS);
		return parent::KEY_PREFIX.self::KEY_PREFIX.array_pop($spaceInfo).':'.$primaryIdValue;
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
	 * 获取数据库表行对象
	 * @return \HuiLib\Db\RowAbstract
	 */
	public function getDbRowInstance()
	{
		$tableClass=static::TABLE_CLASS;
		$rowClass=$tableClass::ROW_CLASS;

		//不用删除多余键，多余的设置不了
		return $rowClass::create($this->data);
	}
	
	/**
	 * 快速创建Redis数据模型
	 * 
	 * @param string $primaryIdValue
	 * @return \HuiLib\Redis\HashTable
	 */
	public static function create($primaryIdValue=NULL)
	{
	    if ($primaryIdValue===NULL) {
	        return NULL;
	    }
	    if (isset(self::$innerInstance[static::getClass()][$primaryIdValue])) {
	        $instance=self::$innerInstance[static::getClass()][$primaryIdValue];
	        if ($instance instanceof static) {
	            return $instance;
	        }
	    }
	    
		$instance=new static();
		$instance->initByPrimaryIdKey($primaryIdValue);
		//缓存
		self::$innerInstance[static::getClass()][$primaryIdValue]=$instance;

		return $instance;
	}
	
	/**
	 * 强制刷新Redis数据缓存，同时更新编辑过的数据
	 * 
	 * 有编辑的情况下请调用flushEditedAndDelete，不然修改的数据会被覆盖。
	 *
	 * @param string $primaryIdValue
	 * @return \HuiLib\Redis\HashTable
	 */
	public static function refresh($primaryIdValue)
	{
	    if ($primaryIdValue===NULL) {
	        return false;
	    }
	    
	    //是否存在缓存
	    if (isset(self::$innerInstance[static::getClass()][$primaryIdValue])) {
	        $instance=self::$innerInstance[static::getClass()][$primaryIdValue];
	        if ($instance instanceof static) {
	            return $instance->flushEditedAndDelete();
	        }
	    }

	    //首先尝试Redis获取数据库的
	    $instance=new static();
	    $data=$instance->getAdapter()->hGetAll($instance->getRedisKey($primaryIdValue));
	    if (empty($data)) {
	        return TRUE;
	    }
	    $instance->applyData($data);
	    return $instance->flushEditedAndDelete();
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
		//不支持修改主键值
		if ($key == $this->primaryIdKey) {
			throw new Exception('Redis Hash row model can not edit primary key value.');
		}
		
		if (array_key_exists($key, $this->data)) {
			$this->data[$key]=$value;
			$this->editData[$key]=$value;
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
			if (!isset($this->incrData[$key])) {
				$this->incrData[$key]=0;
			}
			$this->incrData[$key]+=$value;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 获取最终数据表示
	 */
	protected function getFinalData()
	{
		if (!empty($this->editData)) {
			$this->data[self::EDIT_FIELD_KEY]=json_encode($this->editData);
		}
		if (!empty($this->incrData)) {
			$this->data[self::INCR_FIELD_KEY]=json_encode($this->incrData);
		}
		return $this->data;
	}
	
	/**
	 * 获取初始数据表示
	 */
	protected function getInitedData()
	{
	    if (!$this->fromDb || !$this->dbData) {
	        return array();
	    }
	    
	    $data=$this->dbData;
	    if (!empty($this->editData)) {
	        $data[self::EDIT_FIELD_KEY]=json_encode($this->editData);
	    }
	    if (!empty($this->incrData)) {
	        $data[self::INCR_FIELD_KEY]=json_encode($this->incrData);
	    }
	    if (!empty($this->data[self::REDIS_UPDATE_KEY])) {
	        $data[self::REDIS_UPDATE_KEY]=$this->data[self::REDIS_UPDATE_KEY];
	    }
	    
	    return $this->data;
	}
	
	/**
	 * 获取编辑过的元数据
	 */
	protected function getEditedData()
	{
	    $data=array();
	    if (!empty($this->editData)) {
	        $data[self::EDIT_FIELD_KEY]=json_encode($this->editData);
	    }
	    if (!empty($this->incrData)) {
	        $data[self::INCR_FIELD_KEY]=json_encode($this->incrData);
	    }
	    if (!empty($this->data[self::REDIS_UPDATE_KEY])) {
	        $data[self::REDIS_UPDATE_KEY]=$this->data[self::REDIS_UPDATE_KEY];
	    }
	    return $data;
	}
	
	/**
	 * 返回对象的数组表示
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}
	
	/**
	 * 返回Redis中缓存数据
	 * 
	 * 如果无数据需要从数据库恢复，而不是直接获取
	 * 
	 * @param array $primaryIds 主键ID数组
	 * @return array
	 */
	public static function getListByIds($primaryIds)
	{
	    if (!is_array($primaryIds)) {
	        $primaryIds=array($primaryIds);
	    }
	    if (empty($primaryIds)) {
	        return array();
	    }

	    $result=array();
	    foreach ($primaryIds as $id){
	        $tempInstance=static::create($id);
	        $result[$id]=$tempInstance->toArray();
	    }
        return $result;
	}
	
	public function __destruct()
	{
		//对象销毁自动触发保存到redis
		if (!$this->isDeleted){
		  if($this->fromDb) {
		      //从数据库读取的回写所有数据，也有可能编辑
			  $this->getAdapter()->hMset($this->getRedisKey($this->data[$this->primaryIdKey]), $this->getInitedData());
		  }elseif (!empty($this->editData) || !empty($this->incrData)){
		      //编辑模式，仅更新编辑数据
		      $this->getAdapter()->hMset($this->getRedisKey($this->data[$this->primaryIdKey]), $this->getEditedData());
		  }
		}
	}
	
	protected static function getClass(){}
}