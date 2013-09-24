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
	
	/**
	 * 请求方式
	 */
	const SCHEME_HTTP  = 'http';
	const SCHEME_HTTPS = 'https';
	
	/**
	 * 原始请求数据
	 */
	private static $rawInputBody=NULL;

	/**
	 * 获取GET参数
	 *
	 * @param string $key
	 */
	public static function get($key, $type=self::TYPE_NONE)
	{
		return isset($_GET[$key]) ? self::typeCheck($_GET[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取POST参数
	 *
	 * @param string $key
	 */
	public static function post($key, $type=self::TYPE_NONE)
	{
		return isset($_POST[$key]) ?  self::typeCheck($_POST[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取Cookie参数
	 *
	 * @param string $key
	 */
	public static function cookie($key, $type=self::TYPE_NONE)
	{
		return isset($_COOKIE[$key]) ? self::typeCheck($_COOKIE[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取Server参数
	 *
	 * @param string $key
	 */
	public static function server($key, $type=self::TYPE_NONE)
	{
		return isset($_SERVER[$key]) ? self::typeCheck($_SERVER[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取Env参数
	 *
	 * @param string $key
	 */
	public static function env($key, $type=self::TYPE_NONE)
	{
		return isset($_ENV[$key]) ? self::typeCheck($_ENV[$key], $type) : self::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取页面请假参数
	 */
	public static function getQueryString(){
		return self::server('QUERY_STRING', self::TYPE_STRING);
	}
	
	/**
	 * 获取重写基准路径
	 */
	public static function getScriptUrl(){
		return self::server('SCRIPT_URL', self::TYPE_STRING);
	}
	
	/**
	 * 是否Ajax请求
	 * @return boolean
	 */
	public static function isXmlHttpRequest()
	{
		return (self::server('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}
	
	/**
	 * 获取访问客户IP
	 *
	 * @param  boolean $checkProxy 是否包括代理
	 * @return string
	 */
	public static function getClientIp($checkProxy = true)
	{
		if ($checkProxy && self::server('HTTP_CLIENT_IP', Param::TYPE_STRING) != null) {
			$ip = self::server('HTTP_CLIENT_IP', Param::TYPE_STRING);
		} else if ($checkProxy && self::server('HTTP_X_FORWARDED_FOR', self::TYPE_STRING) != null) {
			$ip = self::server('HTTP_X_FORWARDED_FOR', self::TYPE_STRING);
		} else {
			$ip = self::server('REMOTE_ADDR', self::TYPE_STRING);
		}
	
		return $ip;
	}
	
	/**
	 * 获取HTTP原始请求数据
	 */
	public static function getRawInput()
	{
		if (NULL === self::$rawInputBody) {
			$body = file_get_contents('php://input');
	
			if (strlen(trim($body)) > 0) {
				self::$rawInputBody = $body;
			} else {
				self::$rawInputBody = false;
			}
		}
		return self::$rawInputBody;
	}
	
	
	/**
	 * 强制转换输入参数类型
	 * @param mix $var 变量名
	 * @param string $type 变量类型
	 * 
	 * 	返回是类型安全的，根据具体类型
	 */
	public static function typeCheck($var, $type=self::TYPE_NONE){
		$type && settype($var, $type);
	
		return $var;
	}
	
	/**
	 * 获取请求方法
	 */
	public static function getMethod()
	{
		return  self::server('REQUEST_METHOD', self::TYPE_STRING);
	}
	
	/**
	 * 获取请求模式
	 * @return string
	 */
	public static function getScheme()
	{
		return (self::server('HTTPS', self::TYPE_STRING) == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
	}
	
	/**
	 * 是否Post方式发起的请求
	 *
	 * @return boolean
	 */
	public static function isPost()
	{
		if ('POST' == self::getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * 是否Get方式发起的请求
	 *
	 * @return boolean
	 */
	public static function isGet()
	{
		if ('GET' == self::getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * 是否Put方式发起的请求
	 *
	 * @return boolean
	 */
	public static function isPut()
	{
		if ('PUT' == self::getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * 是否是HTTPS请求
	 *
	 * @return boolean
	 */
	public function isSecure()
	{
		return (self::getScheme() === self::SCHEME_HTTPS);
	}
}