<?php
namespace HuiLib\Db\Pdo;

/**
 * Pdo Mysql类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class Mysql
{

	public function __construct()
	{
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