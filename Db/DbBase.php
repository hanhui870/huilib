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
	 */
	protected $connection;

	public function getConnection()
	{
		return $this->connection;
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
		}
	
		return $adapter;
	}
	
}