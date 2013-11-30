<?php
namespace HuiLib\Db;

use HuiLib\Error\Exception;

/**
 * 数据行列表类
 *
 * @author 祝景法
 * @since 2013/11/30
 */
class RowSet extends \HuiLib\App\Model implements \Iterator, \ArrayAccess
{
	/**
	 * 行列表数据储存
	 * @var array
	 */
	protected $dataList = array ();
	
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

	protected function __construct(array $dataList)
	{
		parent::__construct ();
		
		$this->dataList = $dataList;
	}

	/**
	 * 返回对象的数组表示
	 * @return array
	 */
	public function toArray()
	{
		return $this->dataList;
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