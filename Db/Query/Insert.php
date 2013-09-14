<?php
namespace HuiLib\Db\Query;

/**
 * Sql语句查询类Insert操作
 *
 * @author 祝景法
 * @since 2013/09/03
 */
class Insert extends \HuiLib\Db\Query
{
	const INSERT = 'insert';
	const TABLE = 'table';
	const FIELDS = 'fields';
	const VALUES = 'values';
	
	/**
	 * 插入的键
	 * @var array();
	 */
	protected $fields;
	
	/**
	 * 插入的值
	 * @var array();
	 */
	protected $values;

	/**
	 * 设置待插入的字段
	 * 
	 * eg.
	 * array('field1', 'field2', 'field3', 'field4', ...)
	 * 
	 * @param array $fields
	 * @return \HuiLib\Db\Query\Insert
	 */
	public function fields($fields)
	{
		if (!is_array($fields)) {
			$fields = array($fields);
		}
		$this->fields= $fields;
		return $this;
	}

	/**
	 * 设置待插入的值，一次一行
	 *
	 * eg.
	 * array('fieldValue1', 'fieldValue2', 'fieldValue3', 'fieldValue4', ...)
	 * 
	 * 注意和fields的前后对应
	 *
	 * @param array $values
	 * @return \HuiLib\Db\Query\Insert
	 */
	public function values($values)
	{
		if (!is_array($values)) {
			$values = array($values);
		}
		$this->values [] = $values;
		return $this;
	}

	/**
	 * 重置条件某部分
	 *
	 * @param array $part
	 * @return \HuiLib\Db\Query\Select
	 */
	public function reset($part)
	{
		switch ($part) {
			case self::TABLE :
				$this->table = null;
				break;
			case self::FIELDS :
				$this->fields = array ();
				break;
			case self::VALUES :
				$this->values = array ();
				break;
		}
		return $this;
	}

	/**
	 * 直接以关联字符组形式插入
	 * 
	 * eg.
	 * array('field1'=>'value1', 'field2'=>'value2', 'field3'=>'value3' ...)
	 */
	public function kvInsert($pair)
	{
		
	}
	
	/**
	 * 生成Fields域
	 *
	 * @return string
	 */
	protected function renderFields()
	{
		if ($this->fields===array()) {
			return '';
		}
		return '('.implode(', ', $this->fields).')';
	}
	
	/**
	 * 生成Fields域
	 *
	 * @return string
	 */
	protected function renderValues()
	{
		if ($this->values===array()) {
			return '';
		}
		
		$values=array();
		foreach ( $this->values as $valueArray ) {
			$valueArray=array_map(array($this, 'realEscape'), $valueArray);
			$values[] = '('.implode(', ', $valueArray).')';
		}
		
		return implode(', ', $values);
	}

	/**
	 * 编译成SQL语句
	 */
	protected function compile()
	{
		$parts = array ();
		$parts ['start'] = 'insert into';
		$parts [self::TABLE] = $this->renderTable ();
		$parts [self::FIELDS] = $this->renderFields ();
		$parts ['kvSep'] = 'values';
		$parts [self::VALUES] = $this->renderValues ();
		
		$this->parts = &$parts;
		return parent::compile ();
	}

	/**
	 * 生成SQL语句
	 */
	public function toString()
	{
		return $this->compile ();
	}
	
	public function table($table){
		parent::table($table);
	
		return $this;
	}
}