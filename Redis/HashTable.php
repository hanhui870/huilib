<?php
namespace HuiLib\Redis;

use HuiLib\Error\Exception;

/**
 * Redis HashTable基础管理类
 *
 * @author 祝景法
 * @since 2013/12/14
 */
class HashTable extends RedisBase
{
	/**
	 * 表类常量定义
	 * @var string
	 */
	const TABLE_CLASS=NULL;
	
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
	 * 主键字段
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
	public function initByprimaryIdKey($primaryIdValue)
	{
		//首先尝试Redis获取
		$data=$this->getAdapter()->hGetAll($this->getRedisKey($primaryIdValue));
		
		if (empty($data)) {
			//通过主键尝试数据表获取数据
			$tableClass=static::TABLE_CLASS;
			$data=$tableClass::create()->getRowByField(static::PRIMAY_IDKEY, $primaryIdValue);
			$this->FromDb=TRUE;
		}
		
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
	public static function getRowInitData()
	{
		$tableClass=static::TABLE_CLASS;
		return $tableClass::getRowInitData();
	}
	
	/**
	 * 初始化表行主键名
	 * @return array
	 */
	public function getRowPrimaryIdKey()
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
			$instance->initByprimaryIdKey($primaryIdValue);
		}
		
		return $instance;
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
	 * 执行储存到Redis
	 * 
	 * 默认情况下不需要编辑，因为编辑一般直接获取数据库表的Model对象。此处一般做相对增减。
	 * 
	 * 有效期问题：需要定期和数据库同步，何时新拉取，何时同步过去
	 */
	public function save()
	{
		if ($this->FromDb && isset($this->data[$this->primaryIdKey])) {
			$this->getAdapter()->hMset($this->getRedisKey($this->data[$this->primaryIdKey]), $this->data);
		}
		//更新编辑后的数据 仅允许数字增减
		if ($this->editData) {
			//TODO update
		}
	}
	
	public function __destruct()
	{
		//对象销毁自动触发保存
		 $this->save();
	}
}