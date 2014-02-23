<?php
namespace HuiLib\App;

use HuiLib\Error\Exception;

/**
 * 库端常用对象中间储存
 * 
 * 便于获取，各种对象注册交流中心，单例模式，应用端在Controller\AppFront 
 * 
 * @author 祝景法
 * @since 2014/01/11
 */
class Front
{
	/**
	 * 自身唯一实例
	 * @var \HuiLib\App\Front 
	 */
	protected static $instance=NULL;
	
	/**
	 * 引导程序实例
	 * @var \HuiLib\Bootstrap
	 */
	protected $app=NULL;
	
	/**
	 * 引导程序实例
	 * @var \HuiLib\Bootstrap
	 */
	protected $bootstrap=NULL;
	
	/**
	 * 加载器实例
	 * @var \HuiLib\Loader\AutoLoad
	 */
	protected $loader=NULL;
	
	/**
	 * 请求对象
	 * @var \HuiLib\Request\RequestBase
	 */
	protected $request=NULL;
	
	/**
	 * 控制器实例
	 * @var \HuiLib\App\Controller
	 */
	protected $controller=NULL;
	
	/**
	 * 运行配置
	 * @var \HuiLib\Config\ConfigBase
	 */
	protected $appConfig=NULL;
	
	/**
	 * 网站全局配置 定位原来数据的setting表
	 * @var \HuiLib\Config\ConfigBase
	 */
	protected $siteConfig=NULL;
	
	/**
	 * session实例
	 *  @var \HuiLib\Session\SessionBase
	 */
	protected $session=NULL;
	
	/**
	 * 翻译国际化功能
	 *
	 *  @var \HuiLib\Lang\LangBase
	 */
	protected $lang=NULL;
	
	/**
	 * 前台视图View实例
	 *
	 *  @var \HuiLib\View\ViewBase
	 */
	protected $view=NULL;
	
	/**
	 * 二级目录短链对象
	 *
	 *  @var \HuiLib\Route\TopName 
	 */
	protected $topNameRoute=NULL;
	
	/**
	 * 3级目录及以上的短名称
	 *
	 *  @var \HuiLib\Route\AppName
	 */
	protected $appNameRoute=NULL;
	
	/**
	 * 应用运行末期注册方法呼叫
	 *
	 *  @var \HuiLib\Runtime\ShutCall
	 */
	protected $shutCall=NULL;
	
	public function setApp(\HuiLib\App\AppBase $app)
	{
		$this->app=$app;
	}
	
	public function getApp()
	{
		return $this->app;
	}
	
	public function setBootstrap(\HuiLib\Bootstrap $bootstrap)
	{
		$this->bootstrap=$bootstrap;
	}
	
	public function getBootstrap()
	{
		return $this->bootstrap;
	}
	
	public function setLoader(\HuiLib\Loader\AutoLoad $loader)
	{
		$this->loader=$loader;
	}
	
	public function getLoader()
	{
		return $this->loader;
	}
	
	public function setRequest(\HuiLib\Request\RequestBase $request)
	{
		$this->request=$request;
	}
	
	public function getRequest()
	{
		return $this->request;
	}
	
	public function setController(\HuiLib\App\Controller $controller)
	{
		$this->controller=$controller;
	}
	
	public function getController()
	{
		return $this->controller;
	}
	
	public function setAppConfig(\HuiLib\Config\ConfigBase $config)
	{
		$this->appConfig=$config;
	}
	
	public function getAppConfig()
	{
		return $this->appConfig;
	}
	
	public function setSiteConfig(\HuiLib\Config\ConfigBase $config)
	{
		$this->siteConfig=$config;
	}
	
	public function getSiteConfig()
	{
		if ($this->siteConfig===NULL) {
			if ($this->appConfig===NULL) {
				throw new Exception('appConfig instance has not been initialized.');
			}
			$siteIni=$this->appConfig->getByKey('app.global');
			$this->siteConfig = new \HuiLib\Config\ConfigBase ( $siteIni );
		}
		
		return $this->siteConfig;
	}
	
	public function setSession(\HuiLib\Session\SessionBase $session)
	{
		$this->session=$session;
	}
	
	public function getSession()
	{
		return $this->session;
	}
	
	public function setLang(\HuiLib\Lang\LangBase $lang)
	{
		$this->lang=$lang;
	}
	
	public function getLang()
	{
		if ($this->lang===NULL) {
			$this->lang=\HuiLib\Lang\LangBase::getDefault();
		}
	
		return $this->lang;
	}
	
	public function setView(\HuiLib\View\ViewBase $view)
	{
		$this->view=$view;
	}
	
	public function getView()
	{
		return $this->view;
	}
	
	public function setTopNameRoute(\HuiLib\Route\TopName $route)
	{
	    $this->topNameRoute=$route;
	}
	
	public function getTopNameRoute()
	{
	    return $this->topNameRoute;
	}
	
	public function setAppNameRoute(\HuiLib\Route\AppName $route)
	{
	    $this->appNameRoute=$route;
	}
	
	public function getAppNameRoute()
	{
	    return $this->appNameRoute;
	}
	
	public function setShutCall(\HuiLib\Runtime\ShutCall $shutCall)
	{
		$this->shutCall=$shutCall;
	}
	
	public function getShutCall()
	{
		return $this->shutCall;
	}
	
	public static function getInstance()
	{
		if (self::$instance===NULL) {
			self::$instance=new self();
		}
		
		return self::$instance;
	}
}