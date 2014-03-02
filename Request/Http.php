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
	 * 默认处理$_SERVER['REQUEST_URI']，重写基础信息; SCRIPT_URL仅Apache本地支持，Nginx不支持
	 *
	 * 访问网址：http://iyunlin/fdsafdas/fsdafdsa/fsdafsda?hello=fsdfsda
	 * [REQUEST_URI] => /fdsafdas/fsdafdsa/fsdafsda?hello=fsdfsda
	 * [QUERY_STRING] => hello=fsdfsda
	 */
	protected $scriptUrl=NULL;
	
	protected $httpHost=NULL;
	
	
	public function init(){
		$this->scriptUrl = Param::getScriptUrl();
		$this->httpHost = Param::server('HTTP_HOST', Param::TYPE_STRING);
		
		//规范访问重写请求Url
		$this->formatRequestURI();
		
		//设置路由资源定位符，并初始化路由信息
		$this->routeUri=$this->httpHost.$this->scriptUrl;
		$this->initRouteInfo();
		
		/**
		 * url路由处理 调用父方法
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
		    $queryString=Param::getQueryString();
			Header::redirect($this->scriptUrl. ($queryString ? '?'.$queryString : ''));
		}
		
		return true;
	}
}