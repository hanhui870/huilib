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
	protected $table;
	
	/**
	 * 数据库连接适配器
	 * @var \HuiLib\Db\DbBase
	 */
	protected $adapter;
	
	protected  function __construct(){
		
	}
	
	public function setAdapter(\HuiLib\Db\DbBase $adapter)
	{
		$this->adapter=$adapter;
	}

	/**
	 * 返回一个Select实例
	 */
	public static function Select(){
		
	}
	
	/**
	 * 返回一个Insert实例
	 */
	public static function Insert(){
	
	}
	
	/**
	 * 返回一个Update实例
	 */
	public static function Update(){
	
	}
	
	/**
	 * 返回一个Delete实例
	 */
	public static function Delete(){
	
	}
	
}