<?php
namespace HuiLib\Helper;

use HuiLib\App\Front;
use Controller\AppFront;
use HuiLib\Request\RequestBase;

/**
 * 根据路由组件反向生成链接的类
 *
 * @author 祝景法
 * @since 2014/02/22
 */
class Url
{
    private $routeInfo=array();
    
    /**
     * 生成统一格式链接
     * 
     * 若未指定的参数，使用当前页面的代替
     */
    public function getUrl($package = '', $controller = '', $action = '', $subAction = '')
    {
        $this->routeInfo=array();
        $request=Front::getInstance()->getRequest();

        if (! $package) {
            $package = $request->getRouteSegNum(RequestBase::SEG_PACKAGE);
        }
        $this->routeInfo[RequestBase::SEG_PACKAGE]=$package;
        
        if (! $controller) {
            $controller =$request->getRouteSegNum(RequestBase::SEG_CONTROLLER);
        }
        $this->routeInfo[RequestBase::SEG_CONTROLLER]=$controller;
        
        if (! $action) {
            $action = $request->getRouteSegNum(RequestBase::SEG_ACTION);
        }
        $this->routeInfo[RequestBase::SEG_ACTION]=$action;
        
        if (! $subAction) {
            $subAction = $request->getRouteSegNum(RequestBase::SEG_SUBACTION);
        }
        $this->routeInfo[RequestBase::SEG_SUBACTION]=$subAction;
        
        return $this->getRouteUrl();
    }
    
    /**
     * 获取重定向链接，比如短链跳转等
     */
    public function getRedictUrl($routeInfo)
    {
        $this->routeInfo=array();
        
    }
    

    protected function getRouteUrl()
    {
        $url=AppFront::getInstance()->getHomepage();
        
        if ($this->routeInfo[RequestBase::SEG_PACKAGE]) {
            $url.=$this->routeInfo[RequestBase::SEG_PACKAGE] . URL_SEP;
        }
        
        if ($this->routeInfo[RequestBase::SEG_CONTROLLER]) {
            $url.=$this->routeInfo[RequestBase::SEG_CONTROLLER] . URL_SEP;
        }
        
        if ($this->routeInfo[RequestBase::SEG_ACTION]) {
            $url.=$this->routeInfo[RequestBase::SEG_ACTION] . URL_SEP;
        }
        
        if ($this->routeInfo[RequestBase::SEG_SUBACTION]) {
            $url.=$this->routeInfo[RequestBase::SEG_SUBACTION] . URL_SEP;
        }
        
        return substr($url, 0, -1);
    }
    
    /**
     * @return \HuiLib\Helper\Url 
     */
    public static function create()
    {
        return new self();
    }
}