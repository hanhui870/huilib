<?php
namespace HuiLib\Route;

use HuiLib\App\Front;

/**
 * 定位于二级目录的短链模块
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class TopName extends RouteBase
{
    //路由组件部分
    const SEG_PART = 1;
    private $topNameBack = NULL;

    public function route()
    {
        $request = Front::getInstance ()->getRequest ();
        $topname = $request->getRouteSegNum ( self::SEG_PART );
        $this->topNameBack = $topname;
        
        $appConfig = Front::getInstance ()->getAppConfig ();
        $baseCalss = $appConfig->getByKey ( 'webRun.route.TopName.Base' );
        $baseCalss::dispatch ();
        
        //重新出发路由
        $request->reRoute ();
    }

    public function getTopName()
    {
        return $this->topNameBack;
    }
}
