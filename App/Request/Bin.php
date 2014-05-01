<?php
namespace HuiLib\App\Request;

use HuiLib\Helper\Param;
use HuiLib\Helper\String;
use HuiLib\App\Front;

/**
 * BIN Request类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
class Bin extends RequestBase
{
    /**
     * 路由信息
     * 
     *  [argv] => Array ([0] => run.php, [1] => index/index/index, [2]=>name=hanhui&age=23&fdas )
     */
    protected $scriptUrl=NULL;
    
    protected $httpHost=NULL;
    
    public function init(){
        if (!isset($_SERVER['argv'][1])) {
            $_SERVER['argv'][1]='';
        }
        if (isset($_SERVER['argv'][2])) {
            $_SERVER['QUERY_STRING']=$_SERVER['argv'][2];
            $_SERVER['REQUEST_URI']='/'.$_SERVER['argv'][1].'?'.$_SERVER['argv'][2];
            parse_str($_SERVER['argv'][2], $params);
            //将请求参数覆盖到变量中
            if ($params) {
                foreach ($params as $key=>$value){
                    $_GET[$key]=$value;
                }
            }
        }
        //print_r($_SERVER);die();
        
        $this->scriptUrl = '/'.$_SERVER['argv'][1];
        $this->httpHost = Front::getInstance()->getAppConfig()->getByKey('app.domain');

        //设置路由资源定位符，并初始化路由信息
        $this->routeUri=$this->httpHost.$this->scriptUrl;
        $this->initRouteInfo();
    }
}