<?php
namespace HuiLib\Request;

/**
 * Request基础类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
class RequestBase
{
	//输入安全检测
	const CHECK_CSRF=1;
	const CHECK_SQL_INJECTION=2;
	const CHECK_XSS=4;
	
	const TYPE_INT='integer';
	const TYPE_FLOAT='float';
	const TYPE_STRING='string';
	const TYPE_BOOL='boolean';
	const TYPE_ARRAY='array';
	const TYPE_OBJECT='object';
	const TYPE_NONE=NULL;
	
	/**
	 * 重写前部分信息，不包含参数部分，不包含http部分
	 * 默认$_SERVER['SCRIPT_URL']，重写基础信息
	 * 
	 * [SCRIPT_URL] => /fdsafdas/fsdafdsa/fsdafsda
	 * [SCRIPT_URI] => http://iyunlin/fdsafdas/fsdafdsa/fsdafsda
	 * [REQUEST_URI] => /fdsafdas/fsdafdsa/fsdafsda?hello=fsdfsda
	 * [QUERY_STRING] => hello=fsdfsda
	 */
	protected $scriptUrl;
	
	//路由信息中的包
	protected $package;
	//控制器
	protected $controller;
	//动作
	protected $action;
	//子操作
	protected $subAction;
	
	protected $safeCheck;
	
	protected $appConfig;
	
	function __construct()
	{
	
	}
	
	/**
	 * 设置配置文件实例
	 * @param \HuiLib\Config\ConfigBase $config
	 */
	function setConfig(\HuiLib\Config\ConfigBase $config)
	{
		$this->appConfig=$config;	
	}
	
	/**
	 * 设置默认输入参数安全检查设置，具体获取时也可指定
	 * 
	 * 三位分别代表：Csrf, SqlInjection, Xss
	 */
	public function setSafeCheck($checkCode)
	{
		$this->safeCheck=$checkCode;
	}
	
	public static function getServer($key = NULL, $type=parent::TYPE_NONE)
	{
		if (NULL === $key) {
			return $_SERVER;
		}
	
		return isset($_SERVER[$key]) ? self::typeCheck($_SERVER[$key], $type) : parent::typeCheck(NULL, $type);
	}
	
	public static function getEnv($key = NULL, $type=parent::TYPE_NONE)
	{
		if (NULL === $key) {
			return $_ENV;
		}
	
		return isset($_ENV[$key]) ? self::typeCheck($_ENV[$key], $type) : parent::typeCheck(NULL, $type);
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
}