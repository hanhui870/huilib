<?php
namespace HuiLib\Db;

/**
 * 数据库适配器基础类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
abstract class Adapter
{
	/**
	 * 数据库连接
	 */
	protected $connection;

	public function getConnection()
	{
		return $this->connection;
	}
	
	
}