<?php
namespace HuiLib\Db;

use HuiLib\Error\Exception;
use HuiLib\Db\TableAbstract;
use HuiLib\Db\Query\Where;

/**
 * 数据行类
 * 
 * 行对象通过调用不存在的属性调用方法，属性必须在$calculated注册，并且存在getProperty()方法。
 * 样例可以参照用户类。
 *
 * @author 祝景法
 * @since 2013/10/20
 */
class RowAbstract extends \HuiLib\App\Model
{
	/**
	 * 主键字段键名
	 * @var string
	 */
	const PRIMAY_IDKEY=NULL;
	
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
	 * 修改数据字段储存
	 * @var array
	 */
	protected $editData=array();
	
	/**
	 * 修改数据后原值储存
	 * @var array
	 */
	protected $originalData=array();
	
	/**
	 * 主键原值字段 如果修改过主键
	 * @var string
	 */
	protected $oldPrimaryIdValue=NULL;
	
	/**
	 * 对应表类
	 * @var \HuiLib\Db\TableAbstract 
	 */
	protected $tableInstance=NULL;
	
	/**
	 * 需要计算的字段，通过公开方法获取
	 * 
	 * @var array
	 */
	protected $calculated=NULL;
	
	/**
	 * 是否是新行
	 * @var boolean
	 */
	protected $newRow=FALSE;
	
	/**
	 * 存在主键冲突是否覆盖
	 * @var boolean
	 */
	protected $duplicateCreate=FALSE;
	
	protected function __construct(array $data)
	{
		parent::__construct();
		
		$this->data=$data;
		if (static::PRIMAY_IDKEY===NULL) {
			throw new Exception('RowAbstract const PRIMAY_IDKEY has not been set.');
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
	
	/**
	 * 保存修改后的值
	 * 
	 * @return int
	 */
	public function save()
	{
		$query=$this->getSaveQuery();
		
		$this->onBeforeSave();
		if ($this->newRow) {
			$result=$this->data[static::PRIMAY_IDKEY]=$query->query();
		}else{
			$result=$query->query();
		}
		$this->onAfterSave();
		
		return $result;
	}

	/**
	 * 获取修改的SQL语句
	 *
	 * @return boolean
	 */
	public function getSaveSql()
	{
		return $this->getSaveQuery()->toString();
	}
	
	/**
	 * 保存前事件绑定
	 */
	protected function onBeforeSave()
	{
	}
	
	/**
	 * 保存后事件绑定
	 */
	protected function onAfterSave()
	{
	}
	
	
	/**
	 * 获取Query更新对象
	 * 
	 * @throws Exception
	 * @return Query
	 */
	protected function getSaveQuery()
	{
		$tableInstance=$this->tableInstance;
		
		if ($tableInstance===NULL || $tableInstance::TABLE===NULL) {
			throw new Exception('Table class constant TABLE has not been set.');
		}
		
		$table=$tableInstance::TABLE;
		if ($this->newRow) {//新行
			// PRIMAY_IDKEY 可以设置为默认值0，自动增长的也会自动更新；不然有些非自动增长的会有问题
			$insert=Query::insert($table);
			if ($this->dbAdapter!==NULL) {
				$insert->setAdapter($this->dbAdapter);
			}
			
			if ($this->duplicateCreate) {
				$insert->enableDuplicate();
				//dup的时候要注意去除主键为0的情况
				$duplicate=$this->data;
				unset($duplicate[static::PRIMAY_IDKEY]);
				$insert->dupFields(array_keys($duplicate));
			}
			
			return $insert->kvInsert($this->data);
				
		}else{
			if (!$this->editData){
				throw new Exception('Table row editData has not been set or the field hasn\'t changed.');
			}
			$primaryValue=$this->oldPrimaryIdValue===NULL ? $this->data[static::PRIMAY_IDKEY] : $this->oldPrimaryIdValue;
			$update=Query::update($table);
			if ($this->dbAdapter!==NULL) {
				$update->setAdapter($this->dbAdapter);
			}
			return $update->sets($this->editData)->where(Where::createPair(static::PRIMAY_IDKEY, $primaryValue))->limit(1);
		}
	}
	
	/**
	 * 设置表类实例
	 * @return array
	 */
	public function setTable(TableAbstract $tableInstance)
	{
		$this->tableInstance=$tableInstance;
		return $this;
	}
	
	/**
	 * 存在主键冲突时覆盖创建新行
	 * 
	 * @return array
	 */
	public function enableDupliateCreate()
	{
		$this->duplicateCreate=TRUE;
		return $this;
	}
	
	/**
	 * 使用默认数据创建全新的一行
	 * 
	 * @return RowAbstract
	 */
	public static function createNewRow()
	{
		if (static::$initData===NULL) {
			throw new Exception('Row class default value has not been set, can now create directly.');
		}
		
		$rowNew=new static(static::$initData);
		$rowNew->newRow=TRUE;
		return $rowNew;
	}
	
	/**
	 * 删除一个值
	 *
	 * @return int
	 */
	public function delete()
	{
		$tableInstance=$this->tableInstance;
	
		if ($tableInstance===NULL || $tableInstance::TABLE===NULL) {
			throw new Exception('Table class constant TABLE has not been set.');
		}
	
		$delete=Query::delete($tableInstance::TABLE);
		if ($this->dbAdapter!==NULL) {
			$delete->setAdapter($this->dbAdapter);
		}
	
		$delete->where(Where::createPair(static::PRIMAY_IDKEY, $this->data[static::PRIMAY_IDKEY]));

		$this->onBeforeDelete();
		$result=$delete->query();
		$this->onAfterDelete();
		
		return $result;
	}
	
	/**
	 * 通过关联数组设置行对象
	 * 
	 * 带默认值初始化
	 *
	 * @return array $data Key/Value关联行对象设置数组
	 */
	public function data($data)
	{
		if (static::$initData===NULL || !is_array(static::$initData)) {
			throw new Exception('Row class static var $initData has not been set.');
		}
		
		foreach (static::$initData as $key=>$value){
			if (isset($data[$key])) {//data值初始化
				$this->data[$key]=$data[$key];
				
			}elseif (!isset($this->data[$key])){//默认值初始化
				$this->data[$key]=$value;
			}
		}
		
		return $this;
	}
	
	/**
	 * 删除前事件绑定
	 */
	protected function onBeforeDelete()
	{
	}
	
	/**
	 * 获取表行默认初始化数据
	 * @return array
	 */
	public static function getInitData()
	{
		return static::$initData;
	}
	
	/**
	 * 删除后事件绑定
	 */
	protected function onAfterDelete()
	{
	}
	
	/**
	 * 直接通过对象属性获取
	 * 
	 * 注意:以下哪怕直接获取是有值的，但还是判断失败的
	 * 对一个重载的属性使用empty时,重载魔术方法将不会被调用。 
	 * var_dump(isset($result->Email));
	 * var_dump(!empty($result->Email));
	 */
	public function __get($key)
	{
		if (isset($this->data[$key])) {
			return $this->data[$key];
		
		//NULL是默认值，使用isset将判断失败
		}elseif (array_key_exists($key, $this->calculated)){
			$method='get'.$key;
			
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}
		
		return NULL;
	}
	
	public function __set($key, $value)
	{
		if (isset($this->data[$key]) && $this->data[$key]!=$value) {
			$this->originalData[$key]=$this->data[$key];
			$this->data[$key]=$value;
			$this->editData[$key]=$value;
			
			//修改主键值，支持但不建议修改
			if ($key == static::PRIMAY_IDKEY) {
				$this->oldPrimaryIdValue=$this->originalData[$key];
			}
			return TRUE;
		}
		return FALSE;
	}
}