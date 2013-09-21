<?php
namespace HuiLib\Request;

use HuiLib\Helper\Param;
use HuiLib\Helper\String;
use HuiLib\Helper\Header;

/**
 * HTTP Request类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
class Http extends RequestBase
{
	/**
	 * 重写前部分信息，不包含参数部分，不包含http部分
	 * 默认$_SERVER['SCRIPT_URL']，重写基础信息
	 *
	 * 访问网址：http://iyunlin/fdsafdas/fsdafdsa/fsdafsda?hello=fsdfsda
	 * [SCRIPT_URL] => /fdsafdas/fsdafdsa/fsdafsda
	 * [SCRIPT_URI] => http://iyunlin/fdsafdas/fsdafdsa/fsdafsda
	 * [REQUEST_URI] => /fdsafdas/fsdafdsa/fsdafsda?hello=fsdfsda
	 * [QUERY_STRING] => hello=fsdfsda
	 */
	protected $scriptUrl=NULL;
	
	protected $httpHost=NULL;
	
	
	public function init(){
		$this->scriptUrl = Param::getScriptUrl();
		$this->httpHost = Param::server('HTTP_HOST', Param::TYPE_STRING);
		
		$this->formatRequestURI();
		
		/**
		 * url路由处理
		 */
		$this->urlRoute ();
	}
	
	/**
	 * 规范访问重写请求Url
	 */
	protected function formatRequestURI(){
		//访问主域名
		if ($this->scriptUrl=='/'){
			return true;
		}
		
		//双//等处理
		if (String::exist($this->scriptUrl, '//')){
			$this->scriptUrl=preg_replace('/\/+/is', '/', $this->scriptUrl);
		}
		
		// 重写请求Url以横杠结尾时处理
		if (String::substr($this->scriptUrl, - 1, 1 ) == '/') {
			$this->scriptUrl = String::substr ( $this->scriptUrl, 0, - 1 );
		}
		
		//有更改过scriptUrl，重新定位
		if ($this->scriptUrl != Param::getScriptUrl()){
			Header::redirect($this->scriptUrl.'?'.Param::getQueryString());
		}
		
		return true;
	}
	
	/**
	 * 网站URL路由控制
	 * 
	 * 路由原理：
	 * 1、以Controller为基础，不再支持任意指定一级目录；默认是IndexController
	 * 2、Controller不存在的，再执行一级目录路由
	 * 3、另外支持二级域名、拓展独立域名
	 */
	protected function urlRoute() {
		$pathInfo=explode(URL_SEP, $this->scriptUrl);
		
		if (empty($pathInfo[1])) {
			$pathInfo[1]='index';
		}
		
		if (empty($pathInfo[2])) {
			$pathInfo[2]='index';
		}
		
		$this->package=$pathInfo[1];
		$this->controller=$pathInfo[2];
		$controllerClass=NAME_SEP.$this->appInstance->getAppNamespace().NAME_SEP.'Controller'.NAME_SEP.ucfirst($this->package).NAME_SEP.ucfirst($this->controller);
		
		try {
			$this->controllerInstance=new $controllerClass($this->appInstance);
			$this->controllerInstance->setPackage($this->package);
			$this->controllerInstance->setController($this->controller);
			
		}catch (\Exception $exception){
			//TODO 二级目录路由处理
			
			var_dump($exception);
		}
	}
}