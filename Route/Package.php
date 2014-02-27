<?php
namespace HuiLib\Route;

use HuiLib\App\Front;
use HuiLib\Request\RequestBase;

/**
 * Package层短链模块
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class Package extends RouteBase
{
    //路由组件部分
    private $nameBack = NULL;

    public function route()
    {
        $request = Front::getInstance ()->getRequest ();
        $name = $request->getRouteSegNum ( RequestBase::SEG_PACKAGE );
        $this->nameBack = $name;
        
        $appConfig = Front::getInstance ()->getAppConfig ();
        $baseCalss = $appConfig->getByKey ( 'webRun.route.Package.Base' );
        $baseCalss::dispatch ();
        
        //重新出发路由
        $request->reRoute ();
    }

    public function getName()
    {
        return $this->nameBack;
    }
}
