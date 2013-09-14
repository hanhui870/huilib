<?php
namespace HuiLib\Db\Query;

/**
 * Sql语句查询类Update操作
 *
 * @author 祝景法
 * @since 2013/09/03
 */
class Update extends \HuiLib\Db\Query
{
	const UPDATE = 'update';
	const TABLE = 'table';
	const WHERE = 'where';
	const VALUES = 'values';
	const LIMIT = 'limit';
	
	/**
	 * 更新的键值对
	 * @var array();
	 */
	protected $values;
	
	/**
	 * 设置待插入的值，一次一个更新
	 *
	 * eg.
	 * array('field1'=>'value1', 'field2'=>'value2', 'field3'=>'value3' ...) 
	 * array('num'=>array('plain'=>'num=num+1')) //遇到重复浏览量+1，注意结构
	 *
	 * 注意和fields的前后对应
	 *
	 * @param array $values
	 * @return \HuiLib\Db\Query\Update
	 */
	public function values($values)
	{
		if (!is_array($values)) {
			$values = array($values);
		}
		$this->values = $values;
		return $this;
	}
	
	/**
	 * 重置语句部分参数
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
			case self::WHERE :
				$this->where = array();
				break;
			case self::VALUES :
				$this->values = array ();
				break;
			case self::LIMIT :
				$this->limit = NULL;
				break;
			case self::ENDS :
				$this->ends = '';
				break;
		}
		return $this;
	}
	
	/**
	 * 直接发起默认数据库请求
	 *
	 * @return int 更新操作影响行数
	 */
	public function query()
	{
		$stmt=parent::query();
		return $stmt->rowCount();
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
		foreach ( $this->values as $keyString=>$value ) {
			if (is_string($value)) {//key=>value 形式
				$values[]=$keyString.'='.$this->realEscape($value);
			}elseif (is_array($value) && isset($value['plain'])){
				$values[]=$value['plain'];//num=num+1等，特殊形式
			}
		}
	
		return implode(', ', $values);
	}
	
	/**
	 * 编译成SQL语句
	 */
	protected function compile(){
		$parts=array();
		$parts['start']='update';
		$parts[self::TABLE]=$this->renderTable();
		$parts ['setSep'] = 'set';
		$parts [self::VALUES] = $this->renderValues ();
		$parts[self::WHERE]=$this->renderWhere();
		$parts[self::LIMIT]=$this->renderLimit();
		$parts[self::ENDS]=$this->ends;
		
		$this->parts=&$parts;
		return parent::compile();
	}
	
	/**
	 * 生成SQL语句
	 */
	public function toString(){
		return $this->compile();
	}
	
	public function table($table){
		parent::table($table);
	
		return $this;
	}
	
	public function where($where, $operator=self::WHERE_AND){
		parent::where($where, $operator);
	
		return $this;
	}
	
	public function limit($limit){
		parent::limit($limit);
	
		return $this;
	}
}