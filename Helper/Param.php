<?php
namespace HuiLib\Helper;

/**
 * Param参数辅助方法
 *
 * @author 祝景法
 * @since 2013/09/19
 */
class Param
{
	const TYPE_INT='integer';
	const TYPE_FLOAT='float';
	const TYPE_STRING='string';
	const TYPE_BOOL='boolean';
	const TYPE_ARRAY='array';
	const TYPE_OBJECT='object';
	const TYPE_NONE=NULL;

	public static function getServer($key, $type=self::TYPE_NONE)
	{
		return isset($_SERVER[$key]) ? self::typeCheck($_SERVER[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	public static function getEnv($key, $type=self::TYPE_NONE)
	{
		return isset($_ENV[$key]) ? self::typeCheck($_ENV[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 强制转换输入参数类型
	 * @param mix $var 变量名
	 * @param string $type 变量类型
	 */
	public static function typeCheck($var, $type=self::TYPE_NONE){
		//成功时返回 TRUE， 或者在失败时返回 FALSE.
		$type && settype($var, $type);
	
		return $var;
	}
	
	/**
	 * 获取GET参数
	 *
	 * @param string $key
	 */
	public static function getInput($key, $type=self::TYPE_NONE)
	{
		return isset($_GET[$key]) ? self::typeCheck($_GET[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取POST参数
	 *
	 * @param string $key
	 */
	public static function postInput($key, $type=self::TYPE_NONE)
	{
		return isset($_POST[$key]) ?  self::typeCheck($_POST[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取Cookie参数
	 *
	 * @param string $key
	 */
	public static function cookieInput($key, $type=self::TYPE_NONE)
	{
		return isset($_COOKIE[$key]) ? self::typeCheck($_COOKIE[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取页面请假参数
	 */
	public static function getQueryString(){
		return self::getServer('QUERY_STRING', self::TYPE_STRING);
	}
}