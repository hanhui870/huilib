<?php
namespace HuiLib\Request;

/**
 * HTTP Request类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
class Http extends RequestBase
{
	protected $requestUri;
	static $cookiePre=NULL;
	
	public function init(){
		$this->formatRequestURI();
	}
	
	/**
	 * 规范访问请求Url
	 */
	protected function formatRequestURI(){
		
	}
	
	
}