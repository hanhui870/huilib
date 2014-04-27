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
	
	public static function exceptionHandle($exception)
	{
	    $handle=Front::getInstance()->getAppConfig()->getByKey('webRun.error.handler');
	    if ($handle) {
	        $controller=new $handle();
	        $controller->exceptionAction($exception);
	    }else{
	        echo "The page you found is not available right now.";
	        
	        if (ini_get('display_errors')) {
	            echo '<pre>'.var_export($exception, true).'</pre>';
	        }
	    }
	    
        exit();
	}
}
