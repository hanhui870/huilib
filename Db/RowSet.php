<?php
namespace HuiLib\Db;

use HuiLib\Error\Exception;
use HuiLib\Helper\Pagination;

/**
 * 数据行列表类
 *
 * @author 祝景法
 * @since 2013/11/30
 */
class RowSet extends \HuiLib\Model\ModelBase implements \Iterator, \ArrayAccess
{
	/**
	 * 行列表数据储存
	 * @var array
	 */
	protected $dataList = array ();
	
	/**
	 * Select对象
	 * @var \HuiLib\Db\Query\Select
	 */
	protected $select = NULL;
	
	/**
	 * 分页对象
	 * 
	 * @var Pagination
	 */
	protected $pagination = NULL;
	
	/**
	 * 数组当前指针
	 * @var int
	 */
	protected $position = 0;
	
	/**
	 * 表行类名
	 * @var string
	 */
	protected $rowClass = '\HuiLib\Db\RowAbstract';
	
	/**
	 * 对应表类
	 * @var \HuiLib\Db\TableAbstract
	 */
	protected $tableInstance = NULL;

	protected function __construct()
	{
		parent::__construct ();
	}
	
	/**
	 * 通过数组初始化
	 * 
	 * @param array $dataList
	 * @return \HuiLib\Db\RowSet
	 */
	public function initByDataList($dataList)
	{
	    $this->dataList = $dataList;
	    return $this;
	}
	
	/**
	 * 通过Select对象初始化
	 *
	 * @param \HuiLib\Db\Query\Select $select
	 * @return \HuiLib\Db\RowSet
	 */
	public function initBySelect(\HuiLib\Db\Query\Select $select)
	{
	    $this->select = $select;
	    $this->dataList=$select->query()->fetchAll();
	    
	    return $this;
	}

	/**
	 * 返回对象的数组表示
	 * @return array
	 */
	public function toArray()
	{
		return $this->dataList;
	}
	
	/**
	 * 返回对象数据是否为空
	 * 
	 * @return boolean
	 */
	public function isEmpty()
	{
	    return empty($this->dataList);
	}

	public function get($iter)
	{
		if (isset ( $this->dataList [$iter] )) {
			return $this->dataList [$iter];
		} else {
			return NULL;
		}
	}
	
	/**
	 * 获取分页对象
	 * 
	 * @return array
	 */
	public function getPagination()
	{
	    if ($this->select===NULL) {
	        throw new Exception("Row set instance is not inited by Select instance, doesn't have pagination.");
	    }
	    
	    if ($this->pagination===NULL) {
	        $this->pagination=Pagination::create();
	        $this->pagination->initBySelect($this->select);
	    }
	    
	    return $this->pagination;
	}

	/**
	 * 设置表行类实例
	 * @return array
	 */
	public function setRowClass($rowClass)
	{
		$this->rowClass = $rowClass;
		return $this;
	}

	/**
	 * 设置表类实例
	 * @return array
	 */
	public function setTable(TableAbstract $tableInstance)
	{
		$this->tableInstance = $tableInstance;
		return $this;
	}

	/**
	 * 返回行数据对象
	 *
	 * @param array $data 结果数据
	 * @return \HuiLib\Db\RowAbstract
	 */
	protected function rowObject($data)
	{
		if ($data === FALSE) {
			return NULL;
		}
		
		$rowClass = $this->rowClass;
		$rowInstance = $rowClass::create ( $data );
		$rowInstance->setTable ( $this->tableInstance );
		return $rowInstance;
	}

	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * 获取一行数据，生成对象
	 * @return \HuiLib\Db\RowAbstract
	 */
	public function current()
	{
		return $this->rowObject ( $this->dataList [$this->position] );
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		++ $this->position;
	}

	public function valid()
	{
		return isset ( $this->dataList [$this->position] );
	}

	public function offsetSet($offset, $value)
	{
		if (is_null ( $offset )) {
			$this->dataList [] = $value;
		} else {
			$this->dataList [$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset ( $this->dataList [$offset] );
	}

	public function offsetUnset($offset)
	{
		unset ( $this->dataList [$offset] );
	}

	/**
	 * 通过数组下标获取一行数据，生成对象
	 * 
	 * 注意: 必须保存return[iter]值，不然会无效，每次生成新对象
	 * 
	 * @param int $offset
	 * @return \HuiLib\Db\RowAbstract
	 */
	public function offsetGet($offset)
	{
		return isset ( $this->dataList [$offset] ) ? $this->rowObject ( $this->dataList [$offset] ) : NULL;
	}
}