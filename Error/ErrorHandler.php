<?php
namespace HuiLib\Error;

use HuiLib\App\Front;

/**
 * 系统内部Exception
 *
 * @author 祝景法
 * @since 2013/08/11
 */
class ErrorHandler
{
	public static function errorHandle(){
	    //do not  need display, just log
	}
	
	public static function exceptionHandle(){
		if (Front::getInstance()->getController()) {
		    Front::getInstance()->getController()->exception();
		}else{
		    $view = new \HuiLib\App\View ();
		    $view->message=Front::getInstance()->getLang()->_('error.page.not.found');
		    $view->forward=NULL;
		    $view->timeout=NULL;
		    
		    /**
		     * 应用信息赋值到前台
		     */
		    $app = array ();
		    //向前台赋值当前程序版本
		    $version = Front::getInstance()->getAppConfig()->getByKey ( 'app.version' );
		    $app ['version'] = $version;
		    
		    //网站首页
		    $app ['homepage'] = '/';
		    $app ['https'] = '';
		    
		    //模块路由信息
		    $request=Front::getInstance()->getRequest();
		    $app ['package'] = $request->getPackageRouteSeg();
		    $app ['controller'] = $request->getControllerRouteSeg();
		    $app ['action'] = $request->getActionRouteSeg();
		    
		    $view->assign ( 'app', $app );
		    $view->assign(Front::getInstance()->getSiteConfig()->getByKey());
	
		    $view->render('Common/Message');
		}
	}
}
