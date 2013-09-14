<?php
namespace HuiLib\Db;

/**
 * Sql语句基础类
 *
 * TODO  生成where等条件语句的安全性quote调用问题
 *
 * @author 祝景法
 * @since 2013/09/03
 */
class Query
{
	const TABLE = 'table';
	const WHERE = 'where';
	const LIMIT = 'limit';
	const WHERE_AND='and';
	const WHERE_OR='or';
	const ENDS='ends';//结束分号
	
	/**
	 * 操作的表
	 */
	protected $table = NULL;
	
	/**
	 * 数据库连接适配器
	 * @var \HuiLib\Db\DbBase
	 */
	protected $adapter;
	
	/**
	 * SQL语句组成部分
	 */
	protected $parts = NULL;
	
	/**
	 * 结束分号
	 */
	protected $ends=';';
	
	/**
	 * @var array
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
	 */
	protected function __construct()
	{
	}

	/**
	 * 设置适配器，需要compile的时候必须设置
	 * 
	 * 一般在调用query前set
	 * 
	 * @param \HuiLib\Db\DbBase $adapter
	 * @return \HuiLib\Db\Query
	 */
	public function setAdapter(\HuiLib\Db\DbBase $adapter=NULL)
	{
		if ($adapter===NULL) {
			$adapter=\HuiLib\Bootstrap::getInstance()->appInstance()->getDb();
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
	 * 返回一个Select实例
	 * 
	 * @return \HuiLib\Db\Query\Select
	 */
	public static function select()
	{
		return new \HuiLib\Db\Query\Select ();
	}

	/**
	 * 返回一个Insert实例
	 * 
	 * @return \HuiLib\Db\Query\Insert
	 */
	public static function insert()
	{
		return new \HuiLib\Db\Query\Insert ();
	}

	/**
	 * 返回一个Update实例
	 * 
	 * @return \HuiLib\Db\Query\Update
	 */
	public static function update()
	{
		return new \HuiLib\Db\Query\Update ();
	}

	/**
	 * 返回一个Delete实例
	 * 
	 * @return \HuiLib\Db\Query\Delete
	 */
	public static function delete()
	{
		return new \HuiLib\Db\Query\Delete ();
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
	 * eg where:
	 * array('a=1', 'b is NULL')
	 *
	 * 支持一级，其他层次，直接写在子句中，OR查询需要同等级条件一起输入。提倡简单的sql。
	 *
	 * @param array|string $array 条件关联数组
	 * @param string $operator 查询条件类型
	 *
	 * @return Select
	 */
	public function where($where, $operator=self::WHERE_AND)
	{
		if (!is_array($where)) {
			$where=array($where);
		}
		$this->where [] = array ('where' => $where, 'operator' => $operator );
		return $this;
	}
	
	/**
	 * 设置limit属性
	 * 
	 * Select/Delete用到
	 *
	 * @param int $limit
	 * @throws \HuiLib\Error\Exception
	 * @return \HuiLib\Db\Query\Select
	 */
	public function limit($limit)
	{
		if (! is_numeric ( $limit )) {
			throw new \HuiLib\Error\Exception ( 'Query/Select limit值必须为数值' );
		}
	
		$this->limit = $limit;
		return $this;
	}
	
	
	protected function renderTable()
	{
		return $this->table;
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
	
		$where=array();
		foreach ($this->where as $unit){
			$where[]='('.implode(') '.$unit['operator'].'( ', $unit['where']).')';
		}
	
		if (count($where)==1) {
			return 'where '.implode(self::WHERE_AND, $where);
		}else{
			return 'where ('.implode(self::WHERE_AND, $where).')';
		}
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
		if ($this->adapter==NULL) {
			throw new \HuiLib\Error\Exception ( 'renderLimit:需要先设置Adapter对象' );
		}
		return $this->adapter->getDriver()->limit($this->limit);
	}
	
	/**
	 * 直接发起默认数据库请求
	 * 
	 * @return \PDOStatement
	 */
	public function query()
	{
		$this->setAdapter();
	
		return $this->adapter->getConnection()->query($this->toString());
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
	 * @param unknown $value
	 * @return string
	 */
	public function realEscape($value)
	{
		return $this->adapter->getConnection()->quote($value);
	}

	/**
	 * 生成SQL语句
	 */
	public function toString()
	{
		return $this->compile ();
	}
}