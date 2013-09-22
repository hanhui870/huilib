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
		return $this->controllerInstance;
	}
	
	/**
	 * 网站URL路由控制
	 *
	 * 路由原理：
	 * 1、以Controller为基础，不再支持任意指定一级目录；默认是IndexController
	 * 2、Controller不存在的，再执行一级目录路由
	 * 3、另外支持二级域名、拓展独立域名
	 * 4、Bin模式需要将参数组合成scriptUrl
	 * 
	 */
	protected function urlRoute() {
		$pathInfo=explode(URL_SEP, $this->scriptUrl);
	
		if (empty($pathInfo[1])) {
			$pathInfo[1]='index';
		}
	
		if (empty($pathInfo[2])) {
			$pathInfo[2]='index';
		}
	
		$this->package=$pathInfo[1];
		$this->controller=$pathInfo[2];
		$controllerClass=NAME_SEP.$this->appInstance->getAppNamespace().NAME_SEP.'Controller'.NAME_SEP.ucfirst($this->package).NAME_SEP.ucfirst($this->controller);
	
		try {
			$this->controllerInstance=new $controllerClass($this->appInstance);
			$this->controllerInstance->setPackage($this->package);
			$this->controllerInstance->setController($this->controller);
				
		}catch (\Exception $exception){
			//TODO 二级目录路由处理
				
			var_dump($exception);
		}
	}

	/**
	 * 请求对象初始化
	 */
	abstract public function init();
	
}