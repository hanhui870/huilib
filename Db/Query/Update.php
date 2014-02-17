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
	const SETS = 'sets';
	const LIMIT = 'limit';
	
	/**
	 * 更新的键值对
	 * @var array();
	 */
	protected $sets;
	
	/**
	 * 设置待插入的值，一次一个更新
	 *
	 * eg.
	 * array('field1'=>'value1', 'field2'=>'value2', 'field3'=>'value3' ...) 
	 * array('num'=>array('plain'=>'num=num+1')) //遇到重复浏览量+1，注意结构
	 *
	 * 注意和fields的前后对应
	 *
	 * @param array $sets
	 * @return \HuiLib\Db\Query\Update
	 */
	public function sets($sets)
	{
		if (!is_array($sets)) {
			$sets = array($sets);
		}
		$this->sets = $sets;
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
			case self::SETS :
				$this->sets = array ();
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
	protected function renderSets()
	{
		if ($this->sets===array()) {
			return '';
		}

		$sets=array();
		foreach ( $this->sets as $keyString=>$value ) {
			if (is_array($value) && isset($value['plain'])) {//key=>value 形式 不能使用is_string会把数字等略掉
				$sets[]=$value['plain'];//num=num+1等，特殊形式
			}else{
				$sets[]='`'.$keyString.'`='.$this->escape($value);
			}
		}
	
		return implode(', ', $sets);
	}
	
	/**
	 * 编译成SQL语句
	 */
	protected function compile(){
		$parts=array();
		$parts['start']='update';
		$parts[self::TABLE]=$this->renderTable();
		$parts ['setSep'] = 'set';
		$parts [self::SETS] = $this->renderSets ();
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
	
	public function where(Where $where){
		parent::where($where);
	
		return $this;
	}
	
	public function limit($limit){
		parent::limit($limit);
	
		return $this;
	}
}