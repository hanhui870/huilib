<?php
namespace HuiLib\Db;

use HuiLib\Error\Exception;
use HuiLib\Db\TableAbstract;
use HuiLib\Db\Query\Where;

/**
 * 数据行类
 *
 * @author 祝景法
 * @since 2013/10/20
 */
class RowAbstract extends \HuiLib\App\Model
{
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
	 * 主键字段
	 * @var string
	 */
	protected $primaryId=NULL;
	
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
		if ($this->primaryId===NULL) {
			throw new Exception('RowAbstract primaryId has not been set.');
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
		$query=$this->getQuery();
		
		if ($this->newRow) {
			return $this->data[$this->primaryId]=$query->query();
		}else{
			return $query->query();
		}
	}
	
	/**
	 * 获取修改的SQL语句
	 *
	 * @return boolean
	 */
	public function getSaveSql()
	{
		return $this->getQuery()->toString();
	}
	
	/**
	 * 获取Query更新对象
	 * 
	 * @throws Exception
	 * @return Query
	 */
	protected function getQuery()
	{
		$tableInstance=$this->tableInstance;
		
		if ($tableInstance===NULL || $tableInstance::TABLE===NULL) {
			throw new Exception('Table class constant TABLE has not been set.');
		}
		
		$table=$tableInstance::TABLE;
		if ($this->newRow) {//新行
			//可以设置为默认值0，自动增长的也会自动更新；不然有些非自动增长的会有问题
			//unset($this->data[$this->primaryId]);
			$insert=Query::insert($table);
			if ($this->dbAdapter!==NULL) {
				$insert->setAdapter($this->dbAdapter);
			}
			if ($this->duplicateCreate) {
				$insert->enableDuplicate();
			}
			return $insert->kvInsert($this->data);
				
		}else{
			if (!$this->editData){
				throw new Exception('Table row editData has not been set.');
			}
			$primaryValue=$this->oldPrimaryIdValue===NULL ? $this->data[$this->primaryId] : $this->oldPrimaryIdValue;
			$update=Query::update($table);
			if ($this->dbAdapter!==NULL) {
				$update->setAdapter($this->dbAdapter);
			}
			return $update->sets($this->editData)->where(Where::createPair($this->primaryId, $primaryValue));
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
	
	public function __get($key)
	{
		if (isset($this->data[$key])) {
			return $this->data[$key];
		}else{
			return NULL;
		}
	}
	
	public function __set($key, $value)
	{
		if (isset($this->data[$key])) {
			$this->originalData[$key]=$this->data[$key];
			$this->data[$key]=$value;
			$this->editData[$key]=$value;
			
			//修改主键值，支持但不建议修改
			if ($key == $this->primaryId) {
				$this->oldPrimaryIdValue=$this->originalData[$key];
			}
			return TRUE;
		}
		return FALSE;
	}
}