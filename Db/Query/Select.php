<?php
namespace HuiLib\Db\Query;

/**
 * Sql语句查询类Select操作
 *
 * @author 祝景法
 * @since 2013/09/03
 */
class Select extends \HuiLib\Db\Query
{
	const FIELD_ANY = '*';
	const SELECT = 'select';
	const COLUMNS = 'columns';
	const TABLE = 'table';
	const JOINS = 'joins';
	const WHERE = 'where';
	const GROUP = 'group';
	const ORDER = 'order';
	const INDEX= 'index';
	const LIMIT = 'limit';
	const OFFSET = 'offset';
	const UNION = 'union';
	const UNION_ALL = 'union all';
	const JOIN_INNER = 'inner';
	const JOIN_OUTER = 'outer';
	const JOIN_LEFT = 'left';
	const JOIN_RIGHT = 'right';
	const FOR_UPDATE = 'forUpdate';
	
	/**
	 * @var array
	 */
	protected $columns = array ();
	
	/**
	 * @var array
	*/
	protected $joins = array ();
	
	/**
	 * @var array
	 */
	protected $order = NULL;
	
	/**
	 * @var NULL|array
	*/
	protected $group = NULL;
	
	/**
	 * @var string|NULL
	 */
	protected $index = NULL;
	
	/**
	 * @var int|NULL
	 */
	protected $offset = NULL;
	
	/**
	 * @var array
	 */
	protected $union = array ();
	
	/**
	 * @var boolean
	 */
	protected $forUpdate = false;

	/**
	 * 设置SQL查询获取的类
	 * 
	 * array(key => value, ...)
     * 字符key会用作alias，单个语句可以多次调用。
     * 
	 * array('PrimaryID'=>'id', 'Description'=>'test')
	 * ↓↓↓↓
	 * id as PrimaryID, test as Description
     *     
	 * @param array $columns
	 * 
	 * @return \HuiLib\Db\Query\Select
	 */
	public function columns(array $columns)
	{
		foreach ($columns as $key=>$value){
			if (is_string($key)) {//字符键覆盖原先的
				$this->columns[$key]=$value;
			}else{
				$this->columns[]=$value;//非字符键则添加在后面
			}
		}
		return $this;
	}

	/**
	 * 设置Join数据
	 *
	 * @param string|array 表名称或者key/value pair
	 * @param string $on join 条件
	 * @param string|array $columns join表获取的字段
	 * @param string join类型
	 * 
	 * @throws \HuiLib\Error\Exception
	 * @return \HuiLib\Db\Query\Select
	 */
	public function join($table, $on, $columns = self::FIELD_ANY, $type = self::JOIN_INNER)
	{
		if (is_array ( $table ) && (! is_string ( key ( $table ) ) || count ( $table ) !== 1)) {
			throw new \HuiLib\Error\Exception ('join表是数组的时候，必须是关联数组，每次仅有一条');
		}
		if (! is_array ( $columns )) {
			$columns = array ($columns );
		}
		
		//加入到语句columns中
		$this->columns($columns);
		
		$this->joins [] = array ('table' => $table, 'on' => $on, 'columns' => $columns, 'type' => $type );
		return $this;
	}
	
	/**
	 * 设置联合查询
	 * 
	 * Union类型:
	 * union:默认去除重复的行
	 * union all:包括重复的行
	 * 
	 * @param \HuiLib\Db\Query\Select $select 要联合的查询实例
	 * @return \HuiLib\Db\Query\Select
	 */
	public function union(\HuiLib\Db\Query\Select $select, $type=self::UNION)
	{
		$this->union[]=array ('select' => $select, 'type' => $type );
		return $this;
	}
	
	/**
	 * 设置Group查询属性
	 * 
	 * eg. group('UserID')
	 * 
	 * @param array|string $group
	 * @return \HuiLib\Db\Query\Select
	 */
	public function group($group)
	{
		if (is_array ( $group )) {
			foreach ( $group as $groupBy ) {
				$this->group [] = $groupBy;
			}
		} else {
			$this->group [] = $group;
		}
		return $this;
	}

	/**
	 * 排序属性
	 * 
	 * eg. order('id desc')
	 * 
	 * @param array|string $order
	 * @return \HuiLib\Db\Query\Select
	 */
	public function order($order)
	{
		if (! is_array ( $order )) {
			$order = array ($order );
		}
		
		foreach ( $order as $value ) {
			$this->order [] = $value;
		}
		return $this;
	}
	
	/**
	 * 设置强制索引
	 * 
	 * @param string $index 索引名称
	 * @return \HuiLib\Db\Query\Select
	 */
	function index($index) {
		$this->index = $index;
	
		return $this;
	}

	
	/**
	 * 设置offset属性
	 *
	 * @param int $limit
	 * @throws \HuiLib\Error\Exception
	 * @return \HuiLib\Db\Query\Select
	 */
	public function offset($offset)
	{
		if (! is_numeric ( $offset )) {
			throw new \HuiLib\Error\Exception ( 'Query/Select limit值必须为数值' );
		}
		
		$this->offset = $offset;
		return $this;
	}
	
	/**
	 * 开启更新锁定事务模式
	 */
	public function enableForUpdate()
	{
		$this->forUpdate=true;
	
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
			case self::COLUMNS :
				$this->columns = array ();
				break;
			case self::JOINS :
				$this->joins = array ();
				break;
			case self::WHERE :
				$this->where = array();
				break;
			case self::GROUP :
				$this->group = NULL;
				break;
			case self::LIMIT :
				$this->limit = NULL;
				break;
			case self::OFFSET :
				$this->offset = NULL;
				break;
			case self::ORDER :
				$this->order = NULL;
				break;
			case self::INDEX :
				$this->index = NULL;
				break;
			case self::UNION :
				$this->union = array ();
				break;
			case self::FOR_UPDATE :
				$this->forUpdate = false;
				break;
			case self::ENDS :
				$this->ends = '';
				break;
		}
		return $this;
	}

	/**
	 * 获取原始状态值
	 * @param string $key
	 */
	public function getRawState($key = NULL)
	{
		$rawState = array(
				self::TABLE      => $this->table,
				self::COLUMNS    => $this->columns,
				self::JOINS      => $this->joins,
				self::WHERE      => $this->where,
				self::ORDER      => $this->order,
				self::GROUP      => $this->group,
				self::INDEX     => $this->index,
				self::LIMIT      => $this->limit,
				self::OFFSET     => $this->offset,
				self::UNION    => $this->union
		);
		return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
	}

	/**
	 * 生成获取查询域
	 * 
	 * @return string
	 */
	protected function renderColumns()
	{
		$field=array();
		foreach ($this->columns as $alias=>$column){
			if (is_string($alias)) {
				$field[]=sprintf("%s as %s", $column, $alias);
			}else{
				$field[]=$column;
			}
		}
		
		if (empty($field)) {//未设置获取全部
			$field[]=self::FIELD_ANY;
		}
		
		return implode(', ', $field);
	}

	/**
	 * 生成Union
	 *
	 * @return string
	 */
	protected function renderUnion()
	{
		if ($this->union===array()) {
			return '';
		}
		
		$union=array();
		foreach ($this->union as $unit){
			//虽然子句也可以用offset limit order group等信息，但还是限制在整个主句中。
			$unit['select']->reset(self::ORDER)->reset(self::GROUP)->reset(self::LIMIT)->reset(self::OFFSET)->reset(self::ENDS);
			$union[]=$unit['type'].' '. $unit['select']->toString();
		}
		
		return implode(' ', $union);
	}
	
	/**
	 * 生成Join
	 *
	 * @return string
	 */
	protected function renderJoin()
	{
		if ($this->joins===array()) {
			return '';
		}
		
		$join=array();
		foreach ($this->joins as $unit){
			$join[]=$unit['type'].' join '.$this->getAliasTable($unit['table']).' on '. $unit['on'];
		}
		
		return implode(' ', $join);
	}
	
	/**
	 * 生成Order
	 *
	 * @return string
	 */
	protected function renderOrder()
	{
		if ($this->order===NULL) {
			return '';
		}
		
		return 'order by '.implode(', ', $this->order);
	}
	
	/**
	 * 生成Group
	 *
	 * @return string
	 */
	protected function renderGroup()
	{
		if ($this->group===NULL) {
			return '';
		}
		return 'group by '.implode(', ', $this->group);
	}
	
	/* 生成Offset
	*
	* @return string
	*/
	protected function renderOffset()
	{
		if ($this->offset===NULL) {
			return '';
		}
		$this->setAdapter();
		return $this->adapter->getDriver()->offset($this->offset);
	}
	
	/**
	 * 生成Index
	 *
	 * @return string
	 */
	protected function renderIndex()
	{
		if ($this->index===NULL) {
			return '';
		}
		$this->setAdapter();
		return $this->adapter->getDriver()->index($this->index);
	}
	
	/**
	 * 生成ForUpdate
	 *
	 * @return string
	 */
	protected function renderForUpdate()
	{
		if (!$this->forUpdate) {
			return '';
		}
	
		return 'for update';
	}
	
	/**
	 * 编译成SQL语句
	 */
	protected function compile()
	{
		$parts=array();
		$parts['start']='select';
		$parts[self::COLUMNS]=$this->renderColumns();
		$parts['from']='from';
		$parts[self::TABLE]=$this->renderTable();
		$parts[self::JOINS]=$this->renderJoin();
		$parts[self::INDEX]=$this->renderIndex();
		$parts[self::WHERE]=$this->renderWhere();
		$parts[self::UNION]=$this->renderUnion();
		//一下不能在union子句中出现
		$parts[self::ORDER]=$this->renderOrder();
		$parts[self::GROUP]=$this->renderGroup();
		$parts[self::LIMIT]=$this->renderLimit();
		$parts[self::OFFSET]=$this->renderOffset();
		$parts[self::FOR_UPDATE]=$this->renderForUpdate();
		$parts[self::ENDS]=$this->ends;

		$this->parts=&$parts;
		return parent::compile();
	}
	
	/**
	 * 生成SQL语句
	 */
	public function toString()
	{
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