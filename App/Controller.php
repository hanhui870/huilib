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
	 * 基础APP实例
	 * @var \HuiLib\App\AppBase
	 */
	protected $appInstance;
	
	protected $appConfig;
	
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
	protected $view;
	
	/**
	 * 是否自动渲染并输出
	 * @var boolean
	 */
	protected $autoRender=TRUE;

	public function __construct(\HuiLib\App\AppBase $appInstance)
	{
		$this->appInstance=$appInstance;
		$this->appConfig=$appInstance->configInstance();
		$this->request=$appInstance->requestInstance();
	}
	
	/**
	 * 任务分发
	 */
	public function dispatch(){
		$this->preDispatch();
		
		$this->indexAction();
		
		$this->postDispatch();
	}
	
	/**
	 * 请求派发前事件
	 */
	public function preDispatch()
	{
	}
	
	/**
	 * 请求派发后事件
	 */
	public function postDispatch()
	{
	}
	
	/**
	 * 初始化视图对象
	 */
	protected function initView()
	{
		$this->view=new \HuiLib\App\View($this->appInstance);
	}
	
	/**
	 * 向前端赋值一个变量
	 * 
	 * 仅是桥接方法到: \HuiLib\View\ViewBase::assign($key, $value) 
	 */
	protected function assign($key, $value = NULL)
	{
		$this->view->assign($key, $value);
		
		return $this;
	}
}
