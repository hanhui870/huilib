<?php
namespace HuiLib\Route;

use HuiLib\Error\Exception;
use HuiLib\App\Front;

/**
 * 定位于3级目录及以上的短链接服务
 * 
 * 实现:短链(SubDirectory)加个命名空间
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class AppName extends RouteBase
{
    //路由组件部分
    protected $segPart=NULL;
    
    private $appNameBack=NULL;
    
	public function __construct($segPart)
	{
	    $segPart=intval($segPart);
	    if ($segPart<0) {
	        throw new Exception('AppName route need setSegPart().');
	    }
	    
	    $this->setSegPart($segPart);
	}
    
	public function route()
	{
	    $request=Front::getInstance()->getRequest();
	    $appname=$request->getRouteSegNum($this->segPart);
	    $this->appNameBack=$appname;
	    
	    $appConfig=Front::getInstance()->getAppConfig();
	    $baseCalss = $appConfig->getByKey ( 'webRun.route.AppName.Base' );
	    $baseCalss::dispatch ($this->segPart);
	    
	    //重新出发路由
	    $request->reRoute ();
	}
	
	public function setSegPart($segPart)
	{
	    $this->segPart=$segPart;
	}
	
	public function getSegPart()
	{
	    return $this->segPart;
	}
	
	public function getAppName()
	{
	    return $this->appNameBack;
	}
}
