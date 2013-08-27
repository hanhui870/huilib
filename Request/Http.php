<?php
namespace HuiLib\Request;

/**
 * HTTP Request类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
class Http extends RequestBase
{
	protected $requestUri;
	static $cookiePre=NULL;
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 获取GET参数
	 * 
	 * @param string $key
	 */
	public static function getInput($key = NULL, $type=parent::TYPE_NONE)
	{
		if (NULL === $key) {
			return $_GET;
		}

		return isset($_GET[$key]) ? parent::typeCheck($_GET[$key], $type) : parent::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取POST参数
	 *
	 * @param string $key
	 */
	public static function postInput($key = NULL, $type=parent::TYPE_NONE)
	{
		if (NULL === $key) {
			return $_POST;
		}
		
		return isset($_POST[$key]) ?  parent::typeCheck($_POST[$key], $type) : parent::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取Cookie参数
	 *
	 * @param string $key
	 */
	public static function cookieInput($key = NULL, $type=parent::TYPE_NONE)
	{
		if (NULL === $key) {
			return $_COOKIE;
		}

		$key=$this->getCookiePre().$key;
	
		return isset($_COOKIE[$key]) ? parent::typeCheck($_COOKIE[$key], $type) : parent::typeCheck(NULL, $type);
	}
	
	/**
	 * 获取Cookie前缀
	 */
	public function getCookiePre(){
		if (self::$cookiePre===NULL) {
			self::$cookiePre=$this->appConfig->getByKey('webRun.cookie.pre');
		}
		
		return self::$cookiePre;
	}
}