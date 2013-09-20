<?php
namespace HuiLib\Request;

/**
 * Request基础类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
abstract class RequestBase
{
	/**
	 * 默认$_SERVER['SCRIPT_URL']，重写基础信息
	 */
	protected $scriptUrl;

	//路由信息中的包
	protected $package;
	
	//控制器类
	protected $controller;
	
	/**
	 * 控制器实例
	 * @var \HuiLib\App\Controller
	 */
	protected $controllerInstance;
	
	protected $appInstance;
	
	protected $appConfig;
	
	function __construct(\HuiLib\App\AppBase $app)
	{
		$this->appInstance=$app;
		$this->setConfig($app->configInstance());
	}
	
	/**
	 * 设置配置文件实例
	 * @param \HuiLib\Config\ConfigBase $config
	 */
	public function setConfig(\HuiLib\Config\ConfigBase $config)
	{
		$this->appConfig=$config;	
	}
	
	/**
	 * 返回控制器实例
	 * 
	 * @return \HuiLib\App\Controller
	 */
	public function controllerInstance(){
		return $this->controller;
	}

	/**
	 * 请求对象初始化
	 */
	abstract public function init();
	
}