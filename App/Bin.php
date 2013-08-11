<?php
namespace HuiLib\App;

/**
 * Bin运行应用初始化
 * 
 * @author 祝景法
 * @since 2013/08/11
 */
class Bin extends Base
{
	private static $instance;
	
	private function __construct()
	{
		
	}
	
	/**
	 * 获取应用程序类实例
	 * @return \HuiLib\Bootstrap
	 */
	public static function getInstance()
	{
		if (self::$instance == NULL) {
			self::$instance = new self ();
		}
		return self::$instance;
	}
}
