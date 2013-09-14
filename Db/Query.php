<?php
namespace HuiLib\Db;

/**
 * Sql语句基础类
 *
 * @author 祝景法
 * @since 2013/09/03
 */
class Query
{
	/**
	 * 操作的表
	 */
	protected $table = null;
	
	/**
	 * 数据库连接适配器
	 * @var \HuiLib\Db\DbBase
	 */
	protected $adapter;

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
			$adapter=\HuiLib\App\AppBase::getDefaultDbAdapter();
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
	public static function Select()
	{
		return new \HuiLib\Db\Query\Select ();
	}

	/**
	 * 返回一个Insert实例
	 * 
	 * @return \HuiLib\Db\Query\Insert
	 */
	public static function Insert()
	{
		return new \HuiLib\Db\Query\Insert ();
	}

	/**
	 * 返回一个Update实例
	 * 
	 * @return \HuiLib\Db\Query\Update
	 */
	public static function Update()
	{
		return new \HuiLib\Db\Query\Update ();
	}

	/**
	 * 返回一个Delete实例
	 * 
	 * @return \HuiLib\Db\Query\Delete
	 */
	public static function Delete()
	{
		return new \HuiLib\Db\Query\Delete ();
	}

	/**
	 * 设置当前操作表
	 */
	public function setTable($table)
	{
		$this->table = $table;
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
	 * 直接发起默认数据库请求
	 * 
	 * @return \HuiLib\Db\Query
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
	}

	/**
	 * 生成SQL语句
	 */
	public function toString()
	{
		return $this->compile ();
	}
}