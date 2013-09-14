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
	const DUP_FIELDS = 'dupFields';
	const DUP_VALUES = 'dupValues';
	
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
	 * duplicate key update 存在主键时更新
	 * @var bool
	 */
	protected $duplicate=false;
	
	/**
	 * 要duplicate key update的键
	 * @var array();
	 */
	protected $dupFields;
	
	/**
	 * 要duplicate key update的值
	 * @var array();
	 */
	protected $dupValues;

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
	 * 设置要进行dupliate update的字段
	 *
	 * eg.
	 * array('field1', 'field2', 'field3', 'field4', ...)
	 *
	 * @param array $fields
	 * @return \HuiLib\Db\Query\Insert
	 */
	public function dupFields($fields)
	{
		if (!is_array($fields)) {
			$fields = array($fields);
		}
		$this->dupFields= $fields;
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
	public function values($values, $dupValues=NULL)
	{
		if (!is_array($values)) {
			$values = array($values);
		}
		$this->values [] = $values;
		
		if (is_array($dupValues) && !empty($dupValues)) {
			end($this->values);// 指向末尾
			$key=key($this->values);
			$this->dupValues($key, $dupValues);
		}
		return $this;
	}
	
	/**
	 * 设置dupliate update的值，一次一行，通过values()调用
	 *
	 * eg. 直接dupValues设置的，必须以关联数组形式，不然调用默认value数组
	 * array('field1'=>'value1', 'field2'=>'value2', 'field3'=>'value3' ...) 不需要plain模式，update需要
	 * array('num'=>array('plain'=>'num=num+1')) //遇到重复浏览量+1，注意结构
	 * 
	 * @param int $iter 当前value数组的键，保证和values数组匹配
	 * @param array $values
	 * @return \HuiLib\Db\Query\Insert
	 */
	protected function dupValues($iter, $values)
	{
		if (!is_array($values)) {
			$values = array($values);
		}
		$this->dupValues [$iter] = $values;
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
				$this->table = NULL;
				break;
			case self::FIELDS :
				$this->fields = array ();
				break;
			case self::DUP_FIELDS :
				$this->dupFields = array ();
				break;
			case self::VALUES :
				$this->values = array ();
				break;
			case self::DUP_VALUES :
				$this->dupValues = array ();
				break;
			case self::ENDS :
				$this->ends = '';
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
	public function kvInsert(array $pair, $dupValues=NULL)
	{
		if (empty($pair)) {
			throw new \HuiLib\Error\Exception ( 'kvInsert:待插入的关联数组不能为空' );
		}
		
		$keys=$values=array();
		foreach ($pair as $key=>$value){
			$keys[]=$key;
			$values[]=$value;
		}
		
		$this->fields($keys);
		$this->values($values, $dupValues);
		
		return $this;
	}
	
	/**
	 * 开启存在更新模式
	 */
	public function enableDuplicate()
	{
		$this->duplicate=true;
		
		return $this;
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
	 * 生成Fields对应的值域
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
	 * 创建duplicate update 核心set语句
	 * 
	 * @param int $iter 当前value数组的键，保证和values数组匹配
	 * @param array $value 一条待插入的字符组
	 */
	protected function kvDupReMap($iter, $valueArray)
	{
		if (empty($this->dupFields)) {
			$this->dupFields=$this->fields;
		}
		
		$normalSets=$dupSets=array();
		foreach ($valueArray as $keyIter=>$value){
			if (!isset($this->fields[$keyIter])) throw new \HuiLib\Error\Exception ( 'Insert::kvDupReMap，字段和字段值数组不匹配' );
			
			$setTemp=$this->fields[$keyIter].'='.$this->realEscape($value);
			$normalSets[]=$setTemp;
			if (in_array($this->fields[$keyIter], $this->dupFields) && empty($this->dupValues[$iter])) {
				//没有独立设置了重复更新数组
				$dupSets[]=$setTemp;
			}
		}
		
		//独立设置了重复更新数组，独立于dupField
		if (isset($this->dupValues[$iter])) {
			foreach ($this->dupValues[$iter] as $keyString=>$value){
				if (is_string($value)) {//key=>value 形式
					$dupSets[]=$keyString.'='.$this->realEscape($this->dupValues[$iter][$keyString]);
				}elseif (is_array($value) && isset($value['plain'])){
					$dupSets[]=$value['plain'];//num=num+1等，特殊形式
				}
			}
		}
		
		return implode(', ', $normalSets).' on duplicate key update '.implode(', ', $dupSets);
	}
	
	/**
	 * 生成Duplicate插入的SQL集
	 *
	 * @return string
	 */
	protected function compileDupValues()
	{
		if ($this->values===array()) {
			return '';
		}
		
		$SQLS=array();
		
		foreach ($this->values as $iter=>$unit){
			$parts = array ();
			$parts ['start'] = 'insert into';
			$parts [self::TABLE] = $this->renderTable ();
			$parts ['setSep'] = 'set';
			$parts [self::VALUES] = $this->kvDupReMap($iter, $unit);
			$parts[self::ENDS]=$this->ends;
			
			$this->parts = &$parts;
			$SQLS[] =parent::compile();
		}

		return implode('', $SQLS);
	}

	/**
	 * 编译成SQL语句
	 */
	protected function compile()
	{
		if (!$this->duplicate) {
			$parts = array ();
			$parts ['start'] = 'insert into';
			$parts [self::TABLE] = $this->renderTable ();
			$parts [self::FIELDS] = $this->renderFields ();
			$parts ['kvSep'] = 'values';
			$parts [self::VALUES] = $this->renderValues ();
			$parts[self::ENDS]=$this->ends;
			
			$this->parts = &$parts;
			return parent::compile ();
			
		}else{//Duplicate key update
			return $this->compileDupValues();
		}
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