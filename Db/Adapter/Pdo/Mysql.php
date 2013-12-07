<?php
namespace HuiLib\Db\Adapter\Pdo;

/**
 * Pdo Mysql类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class Mysql
{
	protected static $allowDsnField=array('host', 'port', 'dbname', 'unix_socket');

	public function __construct()
	{
	}

	/**
	 * 返回Pdo定义语句
	 *
	 * @param array $config 数据库配置
	 */
	public function getDsn($dbConfig)
	{
		$dsn=array();
		
		foreach ($dbConfig as $key=>$value){
			if (in_array($key, self::$allowDsnField)) {
				$dsn[]=$key.'='.$value;
			}
		}
		
		return $dbConfig['driver'].':'.implode(';', $dsn);
	}
	
	/**
	 * 生成字符集语句
	 * 
	 * @param string $charset 字符集
	 */
	public function charset($charset)
	{
		return "set character_set_connection=$charset, character_set_results=$charset, character_set_client=binary ";
	}

	/**
	 * 生成limit SQL
	 */
	public function limit($limit)
	{
		return 'limit '.intval($limit);
	}

	public function offset($offset)
	{
		return 'offset '.intval($offset);
	}
	
	/**
	 * 强制使用某个索引
	 */
	public function index($index)
	{
		return 'use index (' . $index . ')';
	}
}