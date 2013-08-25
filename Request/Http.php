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
	public function getInput($key = NULL)
	{
		if (NULL === $key) {
			return $_GET;
		}
	
		return (isset($_GET[$key])) ? $_GET[$key] : NULL;
	}
	
	/**
	 * 获取POST参数
	 *
	 * @param string $key
	 */
	public function postInput($key = NULL)
	{
		if (NULL === $key) {
			return $_POST;
		}
		
		return (isset($_POST[$key])) ? $_POST[$key] : NULL;
	}
	
	/**
	 * 获取Cookie参数
	 *
	 * @param string $key
	 */
	public function cookieInput($key = NULL)
	{
		if (NULL === $key) {
			return $_COOKIE;
		}

		$key=$this->getCookiePre().$key;
	
		return (isset($_COOKIE[$key])) ? $_POST[$_COOKIE] : NULL;
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