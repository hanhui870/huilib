<?php
namespace HuiLib\App;

/**
 * 控制器基础类
 * 
 * @author 祝景法
 * @since 2013/09/20
 */
class Controller
{
	/**
	 * 当前主机名
	 * @var String
	 */
	protected $host;
	
	/**
	 * 当前处理包名
	 * @var String
	 */
	protected $package;
	
	/**
	 * 当前处理控制器名
	 * @var String
	 */
	protected $controller;
	
	/**
	 * 当前处理动作名
	 * @var String
	 */
	protected $action;
	
	/**
	 * 当前处理子动作名
	 * @var String
	 */
	protected $subAction;
	
	/**
	 * 基础APP实例
	 * @var \HuiLib\App\AppBase
	 */
	protected $appInstance;
	
	/**
	 * 网站配置实例
	 * 
	 * @var \HuiLib\Config\ConfigBase
	 */
	protected $appConfig;
	
	/**
	 * 网站全局配置对象
	 * @var \HuiLib\Config\ConfigBase
	 */
	protected $siteConfig=NULL;
	
	/**
	 * 请求对象
	 * @var \HuiLib\Request\RequestBase
	 */
	protected $request;
	
	/**
	 * 响应内容
	 */
	protected $responce;
	
	/**
	 * 请求对象
	 * @var \HuiLib\View\ViewBase
	 */
	protected $view = NULL;
	
	/**
	 * 是否使用View输出
	 * @var boolean
	 */
	protected $useView = TRUE;
	
	/**
	 * 是否自动渲染并输出
	 * @var boolean
	 */
	protected $autoRender = TRUE;
	
	/**
	 * 是否Ajax请求
	 * 
	 * @var boolean
	 */
	protected $ajax = FALSE;
	
	/**
	 * 应用主域名
	 * @var \HuiLib\Lang\LangBase
	 */
	protected $langInstace = NULL;
	
	//Ajax状态返回
	const STATUS_SUCCESS=TRUE;
	const STATUS_FAIL=FALSE;

	public function __construct(\HuiLib\App\AppBase $appInstance)
	{
		$this->appInstance = $appInstance;
		$this->appConfig = $appInstance->configInstance ();
		$this->request = $appInstance->requestInstance ();
		
		//是否Ajax请求
		$this->ajax=\HuiLib\Helper\Param::isXmlHttpRequest();
		
		//控制器子类初始化接口
		$this->init();
	}
	
	/**
	 * 控制器初始化接口，在dispatch前调用
	 */
	protected function init()
	{
	}

	/**
	 * 任务分发
	 */
	public function dispatch()
	{
		$this->preDispatch ();
		
		if ($this->useView) {
			$this->initView ();
		}
		
		//路由方法 附加操作后缀
		$this->action=$this->request->getActionRouteSeg();
		$action=$this->action.'Action';
		$this->$action();
		
		$this->postDispatch ();
		
		//渲染模板，自动触发或方法中调用
		if ($this->useView && $this->autoRender) {
			$this->renderView ();
		}
	}
	
	/**
	 * 控制器内二级以上路由方法
	 * 
	 * 由__call()转发而来
	 * 比如:/thread/2，/thread/2/reply路由到相应方法，不存在相应方法的前提下
	 * 两个层次：控制器层级(/thread/2)、类方法层级(thread/log/2 落到thread::log方法)
	 * 
	 * 可使用Redis Hash实现
	 * 
	 * @param string $key 路由标志 比如user, thread
     * @param string $shortName 待路由的字符串 比如hanhui
	 */
	protected function shortNameRoute($methodName, $arguments)
	{
		$key=$this->host.URL_SEP.$this->package.URL_SEP.$this->controller.URL_SEP.$this->action;
		
		//不存在包 已设置二级目录路由
		$route=new \HuiLib\Route\ShortName();
		$route->route();
		die();
	}

	/**
	 * 请求派发前事件
	 */
	protected function preDispatch()
	{
	}

	/**
	 * 请求派发后事件
	 */
	protected function postDispatch()
	{
	}

	/**
	 * 渲染
	 * 
	 * 在autoRender关闭的情况下
	 */
	protected function renderView($view = NULL, $ajaxDelimiter = NULL)
	{
		//关闭自动渲染情况下可能未初始化
		$this->initView();
		
		$this->preRenderView();
		
		if ($view === NULL) {
			$view = ucfirst ( $this->package ) . SEP . ucfirst ( $this->controller ) . SEP . ucfirst ( $this->action );
		}
		
		$this->view->render ( $view, $ajaxDelimiter );
		
		$this->postRenderView();
	}
	
	/**
	 * 渲染前事件
	 */
	protected function preRenderView()
	{
		//有View类型的才像前台赋值配置数据
		$this->initSiteConfig();
		if (!empty($this->siteConfig)) {
			$this->view->assign($this->siteConfig->getByKey());
		}
	}
	
	/**
	 * 渲染后事件
	 */
	protected function postRenderView()
	{
	}
	
	/**
	 * 输出JSON数据
	 * 
	 * JSON数据结构:{"data":{},"success":true,"message":"","code":200}
	 * 
	 * @param boolean $status
	 * @param string $message 返回代码
	 * @param int $code 返回代码 0代表一切正常
	 * @param mix $data 返回数据
	 */
	protected function renderJson($status=self::STATUS_SUCCESS, $message='', $code=0, $data=array())
	{
		$result=array();
		
		$result['success']=$status;
		$result['message']=$message;
		$result['code']=$code;
		$result['data']=$data;
		$json=json_encode ( $result );
		
		$callback=\HuiLib\Helper\Param::get('callback', \HuiLib\Helper\Param::TYPE_STRING);
		if ($callback) {
			$json=$callback."($json)";
		}
		
		die($json);
	}
	
	/**
	 * 直接将结果集作为数组输出
	 * 
	 * @param array $result
	 */
	protected function renderJsonResult($result)
	{
		 $code=0;
		 $status=self::STATUS_SUCCESS;
		 $message='';
		 
		 if (isset($result['code'])) {
		 	$code=$result['code'];
		 	unset($result['code']);
		 }
		 if (isset($result['success'])) {
		 	$status=$result['success'];
		 	unset($result['success']);
		 }
		 if (isset($result['message'])) {
		 	$message=$result['message'];
		 	unset($result['message']);
		 }
		 
		 $this->renderJson($status, $message, $code, $result);
	}

	/**
	 * 输出
	 */
	public function output()
	{
	}

	/**
	 * 向前端赋值一个变量
	 * 
	 * 仅是桥接方法到: \HuiLib\View\ViewBase::assign($key, $value) 
	 */
	protected function assign($key, $value = NULL)
	{
		$this->view->assign ( $key, $value );
		
		return $this;
	}

	/**
	 * 初始化视图对象
	 */
	protected function initView()
	{
		if ($this->view===NULL) {
			$this->view = new \HuiLib\App\View ( $this->appInstance );
		}
	}
	
	/**
	 * 初始化网站配置实例
	 */
	protected function initSiteConfig()
	{
		if ($this->siteConfig===NULL) {
			$this->siteConfig = $this->appInstance->siteConfigInstance();
		}
	}

	/**
	 * 取消使用view
	 */
	protected function disableView()
	{
		$this->useView = FALSE;
	}

	/**
	 * 开启view功能
	 */
	protected function enableView()
	{
		$this->useView = TRUE;
	}

	/**
	 * 取消自动触发模板渲染
	 */
	protected function disableAutoRender()
	{
		$this->autoRender = FALSE;
	}
	
	public function setHost($host)
	{
		$this->host = $host;
	}

	public function setPackage($package)
	{
		$this->package = $package;
	}

	public function setController($controller)
	{
		$this->controller = $controller;
	}

	public function setAction($action)
	{
		$this->action = $action;
	}
	
	public function setSubAction($subAction)
	{
		$this->subAction = $subAction;
	}
	
	public function getPackage()
	{
		return $this->package;
	}
	
	public function getController()
	{
		return $this->controller;
	}
	
	public function getAction()
	{
		return $this->action;
	}
	
	public function getSubAction()
	{
		return $this->subAction;
	}
	
	/**
	 * 获取翻译实例
	 */
	protected function getLang()
	{
		if ($this->langInstace===NULL) {
			$this->langInstace=\HuiLib\Lang\LangBase::getDefault();
		}
	
		return $this->langInstace;
	}
	
	public function __call($name, $arguments)
	{
		$this->shortNameRoute($name, $arguments);
	}
}
