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
	
	//路由信息中的包
	protected $package;
	//控制器
	protected $controller;
	//动作
	protected $action;
	//子操作
	protected $subAction;
	protected $safeCheck;
	
	
	public function get($key = NULL)
	{

	}
	
	public function set($key, $value)
	{
	
	}
	
	public function getRoutePath()
	{
	
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
	
	public function getServer($key = NULL)
	{
		if (NULL === $key) {
			return $_SERVER;
		}
	
		return (isset($_SERVER[$key])) ? $_SERVER[$key] : NULL;
	}
	
	public function getEnv($key = NULL)
	{
		if (NULL === $key) {
			return $_ENV;
		}
	
		return (isset($_ENV[$key])) ? $_ENV[$key] : NULL;
	}
	
}