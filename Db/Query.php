<?php
namespace HuiLib\Db;

/**
 * Sql语句基础类
 *
 * 安全性转义等问题:
 * 1、insert data: (Fields) values (Values1), (Values1)形式; KVinsert也转换成前种形式，支持duplicate key update; 值已经使用Query::escape转义
 * 2、update data: KVsets和plain update两种模式。前种已经自动转义，后种一般系统输入，无需转义
 * 3、where cond:KVpair、plainQuote、nameBind三种，均在编译时转义；
 *
 * @author 祝景法
 * @since 2013/09/03
 */
class Query
{
	const TABLE = 'table';
	const WHERE = 'where';
	const LIMIT = 'limit';
	const ENDS='ends';//结束分号
	
	/**
	 * 操作的表
	 */
	protected $table = NULL;
	
	/**
	 * 数据库连接适配器
	 * @var \HuiLib\Db\DbBase
	 */
	protected $adapter=NULL;
	
	/**
	 * SQL语句组成部分
	 */
	protected $parts = NULL;
	
	/**
	 * 结束分号
	 */
	protected $ends=';';
	
	/**
	 * @var \HuiLib\Db\Query\Where
	 */
	protected $where = NULL;
	
	/**
	 * @var int|NULL
	 */
	protected $limit = NULL;

	/**
	 * 构造函数受保护
	 * 
	 * 通过select、update、insert、delete等静态函数初始化
	 * 
	 * @param string $table 操作的表
	 */
	protected  function __construct($table = NULL){
		if ($table) {
			$this->table($table);
		}
	}

	/**
	 * 设置适配器，需要compile的时候必须设置
	 * 
	 * @param \HuiLib\Db\DbBase $adapter
	 * @return \HuiLib\Db\Query
	 */
	public function setAdapter(\HuiLib\Db\DbBase $adapter=NULL)
	{
		//已设置
		if ($this->adapter!==NULL) {
			return $this;
		}
		
		//未提供adapter，使用默认
		if ($adapter===NULL) {
			$adapter=\HuiLib\Db\DbBase::createMaster();
		}
		
		if (! $adapter instanceof \HuiLib\Db\DbBase) {
			throw new \HuiLib\Error\Exception ( 'Query::setAdapter:系统必须提供有效的DB adapter' );
		}

		$this->adapter = $adapter;
		
		return $this;
	}

	/**
	 * 获取适配器
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}

	/**
	 * 开启一个事务
	 */
	public function beginTransaction()
	{
		return $this->adapter->beginTransaction();
	}
	
	/**
	 * 开启一个事务
	 */
	public function commit()
	{
		return $this->adapter->commit();
	}
	
	/**
	 * 事务回滚
	 */
	public function rollback()
	{
		return $this->adapter->rollback();
	}
	
	/**
	 * 返回一个Select实例
	 * 
	 * @return \HuiLib\Db\Query\Select
	 */
	public static function select($table = NULL)
	{
		return new \HuiLib\Db\Query\Select ($table);
	}

	/**
	 * 返回一个Insert实例
	 * 
	 * @return \HuiLib\Db\Query\Insert
	 */
	public static function insert($table = NULL)
	{
		return new \HuiLib\Db\Query\Insert ($table);
	}

	/**
	 * 返回一个Update实例
	 * 
	 * @return \HuiLib\Db\Query\Update
	 */
	public static function update($table = NULL)
	{
		return new \HuiLib\Db\Query\Update ($table);
	}

	/**
	 * 返回一个Delete实例
	 * 
	 * @return \HuiLib\Db\Query\Delete
	 */
	public static function delete($table = NULL)
	{
		return new \HuiLib\Db\Query\Delete ($table);
	}
	
	/**
	 * 返回一个Query实例
	 *
	 * @return \HuiLib\Db\Query
	 */
	public static function create()
	{
	    return new self ();
	}

	/**
	 * 设置操作表
	 *
	 * @param  string|array $table
	 * @throws \HuiLib\Error\Exception
	 * @return Static
	 */
	public function table($table)
	{
		if (is_array($table) && (!is_string(key($table)) || count($table) !== 1)) {
			throw new \HuiLib\Error\Exception ('设置查询表的时候，必须是关联数组，且仅有一条');
		}
	
		$this->table = $table;
		return $this;
	}

	/**
	 * 设置当前操作表
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}
	
	/**
	 * 设置Where条件
	 * 
	 * Select/Delete/Update用到
	 *
	 * KVpair、plainQuote、nameBind三种模式，具体见Where类
	 *
	 * @param \HuiLib\Db\Query\Where $where where条件对象
	 *
	 * @return \HuiLib\Db\Query
	 */
	public function where(Query\Where $where)
	{
		$this->setAdapter();
		$this->where=$where;
		$this->where->setQuery($this);
		
		return $this;
	}
	
	/**
	 * 设置limit属性
	 * 
	 * Select/Delete用到
	 *
	 * @param int $limit
	 * @throws \HuiLib\Error\Exception
	 * @return \HuiLib\Db\Query
	 */
	public function limit($limit)
	{
		if (! is_numeric ( $limit )) {
			throw new \HuiLib\Error\Exception ( 'Query/Select limit值必须为数值' );
		}
	
		$this->limit = $limit;
		return $this;
	}
	
	/**
	 * 查询获取表
	 * @param array|string $table
	 * @throws \HuiLib\Error\Exception
	 * @return string
	 */
	protected function getAliasTable($table){
		if (is_string($table)) {
			return $table;
		}elseif (is_array($table)) {
			return  current($table). ' as ' . key($table);
		}
	
		throw new \HuiLib\Error\Exception ('查询表设置错误');
	}
	
	protected function renderTable()
	{
		return $this->getAliasTable($this->table);
	}
	
	/**
	 * 生成查询条件
	 *
	 * @return string
	 */
	protected function renderWhere()
	{
		if ($this->where===NULL) {
			return '';
		}
	
		return 'where '.$this->where->toString();
	}
	
	/**
	 * 生成Limit
	 * 
	 * @return string
	 */
	protected function renderLimit()
	{
		if ($this->limit===NULL) {
			return '';
		}
		$this->setAdapter();
		return $this->adapter->getDriver()->limit($this->limit);
	}
	
	/**
	 * 直接发起默认数据库查询请求
	 * 
	 * 注意：非类同名构造函数
	 * 
	 * @return \HuiLib\Db\Result
	 */
	public function query()
	{
		$this->setAdapter();
		$innerStatment=$this->adapter->getConnection()->query($this->toString());
		return Result::create($innerStatment);
	}
	
	/**
	 * 直接执行一条语句 
	 * 
	 * 返回影响行数
	 *
	 * @return number
	 */
	public function exec()
	{
	    $this->setAdapter();
	    $affectRows=$this->adapter->getConnection()->exec($this->toString());
	    return $affectRows;
	}
	
	/**
	 * 直接发起SQL查询请求
	 *
	 * @param string $string 查询的SQL
	 * @return \HuiLib\Db\Result
	 */
	public function querySql($sql)
	{
	    $this->setAdapter();
	    $innerStatment=$this->adapter->getConnection()->query($sql);
	    return Result::create($innerStatment);
	}
	
	/**
	 * 直接执行一条SQL语句
	 *
	 * 返回影响行数
	 *
	 * @param string $string 查询的SQL
	 * @return number
	 */
	public function execSql($sql)
	{
	    $this->setAdapter();
	    $affectRows=$this->adapter->getConnection()->exec($sql);
	    return $affectRows;
	}
	
	/**
	 * Prepare查询，先调用prepare，然后调用::execute($param)
	 * @return \HuiLib\Db\Query
	 */
	public function prepare()
	{
		$this->setAdapter();
		$innerStatment=$this->adapter->getConnection()->prepare($this->toString());
		return Result::create($innerStatment);
	}

	/**
	 * 编译成SQL语句
	 */
	protected function compile()
	{
		//清除多余空格
		foreach ($this->parts as $key=>$value){
			if (empty($value)) {
				unset($this->parts[$key]);
			}
		}
		return implode(' ', $this->parts);
	}
	
	/**
	 * 转义SQL 语句中使用的字符串中的特殊字符
	 * 
	 * 数组转换：仅支持单维数组
	 * array('fafdas', 'fd', 'eeee', 'bbbbb')
	 * ↓↓↓↓
	 * 'fafdas', 'fd', 'eeee', 'bbbbb'
	 * 
	 * @param string|int|array $value
	 * @return string
	 */
	public function escape($value)
	{
		$this->setAdapter();
		
		if (is_array($value)) {
			$inArray=array();
			foreach ($value as $item){
				$inArray[]=$this->adapter->getConnection()->quote($item);
			}
			return implode(',', $inArray);
		}else{
			return $this->adapter->getConnection()->quote($value);
		}
	}

	/**
	 * 生成SQL语句
	 */
	public function toString()
	{
		return $this->compile ();
	}
}