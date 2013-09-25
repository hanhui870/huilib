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
	 * 系统主要路由资源定位符
	 * 
	 * 类似http://iyunlin.com/thread/view/8878 => iyunlin.com/thread/view/8878
	 * 
	 * Http默认Host+ScriptUrl; Bin由参数组建
	 */
	protected $routeUri=NULL;
	
	/**
	 * 路由结果数组
	 * 
	 * @var array 组成:Host, Package, Controller, Action, SubAction五层次封装，便于以后拓展
	 */
	protected $routeInfo=NULL;

	//路由信息中的包
	protected $package=NULL;
	
	//控制器类
	protected $controller=NULL;
	
	/**
	 * 控制器实例
	 * @var \HuiLib\App\Controller
	 */
	protected $controllerInstance=NULL;
	
	protected $appInstance=NULL;
	
	protected $appConfig=NULL;
	
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
		$this->host=$this->getHostRouteSeg();
		$this->package=$this->getPackageRouteSeg();
		$this->controller=$this->getControllerRouteSeg();
		
		$controllerClass=NAME_SEP.$this->appInstance->getAppNamespace().NAME_SEP.'Controller'.NAME_SEP.ucfirst($this->package).NAME_SEP.ucfirst($this->controller);
		try {
			$this->controllerInstance=new $controllerClass($this->appInstance);
			$this->controllerInstance->setPackage($this->package);
			$this->controllerInstance->setController($this->controller);
			$this->controllerInstance->setHost($this->host);

		}catch (\Exception $exception){
			//检测包路由 不存在包路径触发
			$packageDir=APP_PATH.'Controller'.SEP.ucfirst($this->package).SEP;
			$this->appConfig->getByKey('webRun.route.SubDirectory');
			if (!is_dir($packageDir) && $this->appConfig->getByKey('webRun.route.SubDirectory')) {
				//不存在包 已设置二级目录路由
				$route=new \HuiLib\Route\SubDirectory();
				$route->route();
			}else{
				//TODO Message display
				throw new \HuiLib\Error\Exception("该页面不可访问");
			}
		}
	}
	
	/**
	 * 初始化系统关键路由信息
	 */
	protected function initRouteInfo()
	{
		if ($this->routeUri==NULL) {
			throw new \HuiLib\Error\Exception("关键路由信息ScriptUrl未初始化");
		}
		
		$routeInfo=explode(URL_SEP, $this->routeUri);
		
		$this->routeInfo=$routeInfo;
	}
	
	/**
	 * 二次路由
	 */
	public function reRoute($scriptUrl)
	{
		
	}
	
	/**
	 * 获取主机段路由信息
	 */
	public function getHostRouteSeg()
	{
		if (!empty($this->routeInfo[0])) {
			return $this->routeInfo[0];
		}else{
			return '';
		}
	}

	/**
	 * 获取包段路由信息
	 * 
	 * @return string 默认index包
	 */
	public function getPackageRouteSeg()
	{
		if (!empty($this->routeInfo[1])) {
			return $this->routeInfo[1];
		}else{
			return 'index';
		}
	}
	
	/**
	 * 获取控制器段路由信息
	 * 
	 * @return string 默认index控制器
	 */
	public function getControllerRouteSeg()
	{
		if (!empty($this->routeInfo[2])) {
			return $this->routeInfo[2];
		}else{
			return 'index';
		}
	}
	
	/**
	 * 获取动作段路由信息
	 * 
	 * @return string 默认index动作
	 */
	public function getActionRouteSeg()
	{
		if (!empty($this->routeInfo[3])) {
			return $this->routeInfo[3];
		}else{
			return 'index';
		}
	}
	
	/**
	 * 获取子动作段路由信息
	 */
	public function getSubActionRouteSeg()
	{
		if (!empty($this->routeInfo[4])) {
			return $this->routeInfo[4];
		}else{
			return '';
		}
	}
	
	/**
	 * 设置主机段路由信息
	 */
	public function setHostRouteSeg($host)
	{
		$this->routeInfo[0]=$host;
	}
	
	/**
	 * 设置包段路由信息
	 */
	public function setPackageRouteSeg($package)
	{
		$this->routeInfo[1]=$package;
	}
	
	/**
	 * 设置控制器段路由信息
	 */
	public function setControllerRouteSeg($controller)
	{
		$this->routeInfo[2]=$controller;
	}
	
	/**
	 * 设置动作段路由信息
	 */
	public function setActionRouteSeg($action)
	{
		$this->routeInfo[3]=$action;
	}
	
	/**
	 * 设置子动作段路由信息
	 */
	public function setSubActionRouteSeg($subAction)
	{
		$this->routeInfo[4]=$subAction;
	}

	/**
	 * 请求对象初始化
	 */
	abstract public function init();
	
}