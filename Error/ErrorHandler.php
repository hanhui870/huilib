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
	
	}
	
	public static function exceptionHandle(){
		if (Front::getInstance()->getController()) {
		    Front::getInstance()->getController()->error();
		}else{
		    $view = new \HuiLib\App\View ();
		    $view->message=Front::getInstance()->getLang()->_('error.page.not.found');
		    $view->forward=NULL;
		    $view->timeout=NULL;
		    $view->app['homepage']='/';
		    
		    $view->assign(Front::getInstance()->getSiteConfig()->getByKey());
	
		    $view->render('Common/Message');
		}
	}
}
