<?php
namespace HuiLib\Db;

/**
 * 数据库基础类
 * 
 * 包括适配器、工厂功能，因为包含太多文件实在影响性能和维护，尽量保持简洁构架
 *
 * @author 祝景法
 * @since 2013/09/03
 */
abstract class DbBase
{
	/**
	 * 数据库连接
	 * 
	 * @var \PDO
	 */
	protected $connection;
	
	/**
	 * 数据库驱动 如mysql
	 */
	protected $driver;

	/**
	 * 获取数据库连接，便于直接查询
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * 获取具体配置驱动实例
	 */
	public function getDriver()
	{
		return $this->driver;
	}
	
	/**
	 * 执行数据库查询
	 * 
	 * 默认获取关联查询数据
	 * 
	 * @param \HuiLib\Db\Query $query SQL语句对象
	 * @return \PDOStatement
	 */
	public function query($query, $fetchStyle=\PDO::FETCH_ASSOC)
	{
		$query->setAdapter($this);

		//echo $query->toString();
		return $this->connection->query($query->toString(), $fetchStyle);
	}
	
	/**
	 * 创建DB实例 DB factory方法
	 */
	public static function create($config)
	{
		if (empty($config['adapter'])) {
			throw new \HuiLib\Error\Exception('Db adapter can not be empty!');
		}
	
		switch ($config['adapter']){
			case 'pdo':
				$adapter=new \HuiLib\Db\Pdo\PdoBase($config);
				break;
			case 'mongo':
				
				break;
		}
	
		return $adapter;
	}
}