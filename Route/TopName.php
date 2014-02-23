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
    const SEG_PART=1;
    
    private $topNameBack=NULL;
    
	public function route()
	{
	    $request=Front::getInstance()->getRequest();
	    $topname=$request->getRouteSegNum(self::SEG_PART);
	    $this->topNameBack=$topname;

	    $appConfig=Front::getInstance()->getAppConfig();
	    $routeClass=$appConfig->getByKey('webRun.route.TopName.Model');
	   
	   $info=array('Item'=>'', 'RelatedId'=>'');
	   if (is_numeric($topname)) {
	       $info['Item']=$appConfig->getByKey('webRun.route.TopName.NumItem');
	       $info['RelatedId']=$topname;
	   }else{
	       $model=$routeClass::create();
	       $info=$model->parseUrl($topname);
	   }
	   
	   if (empty($info['Item']) || empty($info['RelatedId'])){
	       throw new \Exception('路由失败');
	   }
	   
	   //修正路由信息
	   $request->setRouteSegNum(self::SEG_PART, $info['Item']);
	   $baseCalss=$appConfig->getByKey('webRun.route.TopName.Base');
	   
	   //print_r($info);echo $request->getRouteInfo();
	   $baseCalss::dispatch($info);

	   //重新出发路由
	   $request->reRoute();
	}
	
	public function getTopName()
	{
	    return $this->topNameBack;
	}
}
