<?php
namespace HuiLib\Request;

/**
 * Request基础类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
abstract class RequestBase
{
	/**
	 * 重写前部分信息，不包含参数部分，不包含http部分
	 * 默认$_SERVER['SCRIPT_URL']，重写基础信息
	 * 
	 * [SCRIPT_URL] => /fdsafdas/fsdafdsa/fsdafsda
	 * [SCRIPT_URI] => http://iyunlin/fdsafdas/fsdafdsa/fsdafsda
	 * [REQUEST_URI] => /fdsafdas/fsdafdsa/fsdafsda?hello=fsdfsda
	 * [QUERY_STRING] => hello=fsdfsda
	 */
	protected $scriptUrl;
	
	//路由信息中的包
	protected $package;
	//控制器
	protected $controller;
	//动作
	protected $action;
	//子操作
	protected $subAction;
	
	protected $appConfig;
	
	function __construct(\HuiLib\Config\ConfigBase $config)
	{
		$this->setConfig($config);
		$this->init();
	}
	
	/**
	 * 设置配置文件实例
	 * @param \HuiLib\Config\ConfigBase $config
	 */
	function setConfig(\HuiLib\Config\ConfigBase $config)
	{
		$this->appConfig=$config;	
	}

	/**
	 * 请求对象初始化
	 */
	abstract protected function init();
	
}