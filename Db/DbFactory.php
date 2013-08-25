<?php
namespace HuiLib\Db;

/**
 * DB factory类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class DbFactory
{	
	private function __construct()
	{
	}
	
	/**
	 * 创建DB实例
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