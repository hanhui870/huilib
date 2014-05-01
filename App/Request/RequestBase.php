<?php
namespace HuiLib\App\Request;

use HuiLib\App\Front;
use HuiLib\Helper\String;
use HuiLib\Error\RouteControllerException;
use HuiLib\Error\RoutePackageException;
use HuiLib\Loader\AutoLoaderException;

/**
 * Request基础类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
abstract class RequestBase
{
    /**
     * 路由信息的分隔符
     */
    const ROUTE_URL_SEP='-';
    
    //路由组件
    const SEG_HOST=0;
    const SEG_PACKAGE=1;
    const SEG_CONTROLLER=2;
    const SEG_ACTION=3;
    
    /**
     * 重写前部分信息
     * @var string
     */
    protected $scriptUrl=NULL;
    
	/**
	 * 系统主要路由资源定位符
	 * 
	 * 为了规范链接，资源路由信息只能包含 小写
	 * Package, Controller, Action相关名包含大写的，必须拆成-分隔的，如/discuss/api/add-discuss => api::addDiscuss()
	 * 
	 * 类似http://iyunlin.com/thread/view/8878 => iyunlin.com/thread/view/8878
	 * 
	 * Http默认Host+ScriptUrl; Bin由参数组建
	 */
	protected $routeUri=NULL;
	
	/**
	 * 路由结果数组
	 * 
	 * @var array 组成:Host, Package, Controller, Action五层次封装，便于以后拓展，存在路由信息时可能不准，索引0123
	 * 
	 */
	protected $routeInfo=NULL;
	
	protected $originalRouteInfo=NULL;
	
	//路由信息中的主机
	protected $host=NULL;

	//路由信息中的包
	protected $package=NULL;
	
	//控制器类
	protected $controller=NULL;
	
	/**
	 * 控制器实例
	 * @var \HuiLib\App\Controller
	 */
	protected $controllerInstance=NULL;
	
	protected $appConfig=NULL;
	
	function __construct()
	{
	    $this->init();
	}
	
	/**
	 * 网站URL路由控制
	 *
	 */
	public function urlRoute() {
	    $this->host=$this->getHostRouteSeg();
	    $this->package=$this->getPackageRouteSeg();
	    $this->controller=$this->getControllerRouteSeg();
	     
	    $loader=Front::getInstance()->getLoader();
	    
		try {
		    //检测Package是否有效
		    $controllerPath=$loader->getRegisteredPath('Controller').self::mapRouteSegToClass($this->package);
		    if (!is_dir($controllerPath)) {
		        throw new RoutePackageException('Bad package, go url route.');
		    }
		    
		    $this->loadController();
		    
		}catch (RoutePackageException $exception){
		    $packageRoute=new \HuiLib\App\Route\Package();
		    Front::getInstance()->setPackageRoute($packageRoute);
		    
		    //二级目录路由处理
		    $packageRoute->route();
		}
	}
	
	/**
	 * 二次路由
	 */
	public function reRoute()
	{
	    $this->host=$this->getHostRouteSeg();
	    $this->package=$this->getPackageRouteSeg();
	    $this->controller=$this->getControllerRouteSeg();
	    
	    try {
	        $this->loadController();
	        
	    }catch (RoutePackageException $exception){
	        exit("Reroute failed.");
	    }
	}
	
	/**
	 * 加载控制器
	 *
	 * 	路由原理：
	 * 1、以Controller为基础，不再支持任意指定一级目录；默认是IndexController
	 * 2、Controller不存在的，再执行一级目录路由
	 * 3、另外支持二级域名、拓展独立域名
	 * 4、Bin模式需要将参数组合成scriptUrl
	 *
	 * @throws \Exception
	 */
	protected function loadController()
	{
	    try {
	        //默认到controller级
	        $controllerClass='Controller'.NAME_SEP.self::mapRouteSegToClass($this->package).NAME_SEP.self::mapRouteSegToClass($this->controller);
	        try {
	           $this->controllerInstance=new $controllerClass();
	        }catch (AutoLoaderException $e){
	            throw new RouteControllerException('Controller class not exists.');
	        }
	
	        //大小写规范问题
	        if (strtolower($this->package) != $this->getPackageRouteSeg()
    	        || strtolower($this->controller) != $this->getControllerRouteSeg()
    	        || get_class($this->controllerInstance) != $controllerClass) {//强力规范url
	            exit("Bad url route package or controller format.");
	        }
	        
	        Front::getInstance()->setController($this->controllerInstance);
	    }catch (RouteControllerException $exception){
	        $controllerRoute=new \HuiLib\App\Route\Controller();
	        Front::getInstance()->setControllerRoute($controllerRoute);
	        
	        //Controller路由处理 /topic/2
	        $controllerRoute->route();
	    }
	}
	
	/**
	 * 初始化系统关键路由信息
	 * 
	 * 按照Host, Package, Controller, Action约定组件
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
	 * 将路由组件转换到类名称
	 * 
	 * eg. discuss-comment=>DiscussComment
	 *
	 * @param string $string路由组件
	 */
	public static function mapRouteSegToClass($string)
	{
	    if (!String::exist($string, self::ROUTE_URL_SEP)) {
	        return ucfirst($string);
	    }
	   $segInfo=explode(self::ROUTE_URL_SEP, $string);
	   $result=array();
	   foreach ($segInfo as $part){
	       $result[]=ucfirst($part);
	   }
	   return implode('', $result);
	}
	
	/**
	 * 将路由组件转换到控制器方法
	 * 
	 * eg. add-discuss=>addDiscuss
	 * 
	 * @param string $string路由组件
	 */
	public static function mapRouteSegToMethod($string)
	{
	    if (!String::exist($string, self::ROUTE_URL_SEP)) {
	        return $string;
	    }
	    $segInfo=explode(self::ROUTE_URL_SEP, $string);
	    
	    $result=array(array_shift($segInfo));//第一个首字符大写
	    foreach ($segInfo as $part){
	        $result[]=ucfirst($part);
	    }
	    return implode('', $result);
	}
	
	/**
	 * 获取主机段路由信息
	 */
	public function getHostRouteSeg()
	{
		if (!empty($this->routeInfo[self::SEG_HOST])) {
			return $this->routeInfo[self::SEG_HOST];
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
		if (!empty($this->routeInfo[self::SEG_PACKAGE])) {
		    return $this->routeInfo[self::SEG_PACKAGE];
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
		if (!empty($this->routeInfo[self::SEG_CONTROLLER])) {
			return $this->routeInfo[self::SEG_CONTROLLER];
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
		if (!empty($this->routeInfo[self::SEG_ACTION])) {
			return $this->routeInfo[self::SEG_ACTION];
		}else{
			return 'index';
		}
	}
	
	/**
	 * 通过指定位置获取路由组件
	 * 
	 * @param int $number 路由索引
	 */
	public function getRouteSegNum($number)
	{
	    if (!empty($this->routeInfo[$number])) {
	        return $this->routeInfo[$number];
	    }else{
	        return '';
	    }
	}
	
	/**
	 * 设置主机段路由信息
	 */
	public function setHostRouteSeg($host)
	{
		$this->routeInfo[self::SEG_HOST]=strtolower($host);
	}
	
	/**
	 * 设置包段路由信息
	 */
	public function setPackageRouteSeg($package)
	{
		$this->routeInfo[self::SEG_PACKAGE]=strtolower($package);
	}
	
	/**
	 * 设置控制器段路由信息
	 */
	public function setControllerRouteSeg($controller)
	{
		$this->routeInfo[self::SEG_CONTROLLER]=strtolower($controller);
	}
	
	/**
	 * 设置动作段路由信息
	 */
	public function setActionRouteSeg($action)
	{
		$this->routeInfo[self::SEG_ACTION]=strtolower($action);
	}
	
	/**
	 * 修复路由索引信息
	 *
	 * @param int $number 路由索引
	 * @param string $replaceMent 替换的内容，如果为空则删除
	 */
	public function setRouteSegNum($number, $replaceMent=NULL)
	{
	    if (isset($this->routeInfo[$number])) {
	        if ($replaceMent) {
	            $this->routeInfo[$number]=strtolower($replaceMent);
	        }else{
	            unset($this->routeInfo[$number]);
	            //需要重新索引
	            $this->routeInfo=array_values($this->routeInfo);
	        }
	    }elseif ($number>=0 && $number<=3 && $replaceMent){//package => action允许设置
	        $this->routeInfo[$number]=strtolower($replaceMent);
	    }
	}

	/**
	 * 获取路由路径信息
	 * 
	 * @return array
	 */
	public function getRouteInfo()
	{
		return $this->routeInfo;
	}
	
	/**
	 * 获取原始路由路径信息
	 *
	 * @return string
	 */
	public function getOriginalRouteInfo()
	{
	    if ($this->originalRouteInfo===NULL) {
	        $this->originalRouteInfo=explode(URL_SEP, $this->routeUri);
	    }
	    return $this->originalRouteInfo;
	}

	/**
	 * 通过指定位置获取路由组件
	 *
	 * @param int $number 路由索引
	 */
	public function getOriginalRouteSegNum($number)
	{
	    $this->getOriginalRouteInfo();
	    if (!empty($this->originalRouteInfo[$number])) {
	        return $this->originalRouteInfo[$number];
	    }else{
	        return '';
	    }
	}
	
	/**
	 * 是否通过命令行访问
	 * @return boolean
	 */
	public static function isCli()
	{
	    return php_sapi_name() == 'cli';
	}
	
	/**
	 * 请求对象初始化
	 */
	abstract public function init();
	
}