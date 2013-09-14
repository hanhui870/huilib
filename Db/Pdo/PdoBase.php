<?php
namespace HuiLib\Db\Pdo;

/**
 * Pdo初始化类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class PdoBase extends \HuiLib\Db\DbBase
{	
	public function __construct($config)
	{
		try {
			$dsn=$config['driver'].":host={$config['host']};dbname={$config['name']}"; //data source name
			
			$this->connection = new \PDO($dsn, $config['user'], $config['password']);
			$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			
			//设置字符集
			if(isset($config['charset'])){
				$driverClass='\HuiLib\Db\Pdo\\'.ucfirst($config['driver']);
				$this->driver=new $driverClass();
				$this->connection->query($this->driver->charset($config['charset']));
			}

		} catch (\PDOException $exception) {
			throw new \HuiLib\Error\Exception($exception->getMessage(), $exception->getCode());
		}
	}
}