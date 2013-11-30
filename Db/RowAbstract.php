<?php
namespace HuiLib\Db;

use HuiLib\Error\Exception;

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
	 * 是否是新行
	 * @var boolean
	 */
	protected $newRow=FALSE;
	
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
	 * @return boolean
	 */
	public function save()
	{
		
		
		return true;
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
			$this->data[$key]=$value;
			return TRUE;
		}
		return FALSE;
	}
}