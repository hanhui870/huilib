<?php
namespace HuiLib\Db\Adapter\Pdo;

/**
 * Pdo初始化类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class PdoBase extends \HuiLib\Db\DbBase
{	
	protected function __construct($dbConfig)
	{
		try {
			$dsn=$dbConfig['driver'].":host={$dbConfig['host']};dbname={$dbConfig['name']}"; //data source name
			$this->connection = new \PDO($dsn, $dbConfig['user'], $dbConfig['password']);
			
			//设置Pdo错误模式为异常
			$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			
			//设置字符集
			if(isset($dbConfig['charset'])){
				$driverClass='\HuiLib\Db\Adapter\Pdo\\'.ucfirst($dbConfig['driver']);
				$this->driver=new $driverClass();
				$this->connection->query($this->driver->charset($dbConfig['charset']));
			}

		} catch (\PDOException $exception) {
			throw new \HuiLib\Error\Exception($exception->getMessage(), $exception->getCode());
		}
	}
	
	/**
	 * 开启一个事务
	 */
	public function beginTransaction()
	{
		return $this->connection->beginTransaction();
	}
	
	/**
	 * 开启一个事务
	 */
	public function commit()
	{
		return $this->connection->commit();
	}
	
	/**
	 * 事务回滚
	 */
	public function rollback()
	{
		return $this->connection->rollBack();
	}
}