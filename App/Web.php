<?php
namespace HuiLib\App;

/**
 * Web运行应用初始化
 * 
 * @author 祝景法
 * @since 2013/08/11
 */
class Web extends AppBase
{
	const RUN_METHOD='Web';
	
	protected function __construct($config)
	{
		parent::__construct($config);
		
		
	}
	
	/**
	 * 初始化请求
	 */
	protected function initRequest()
	{
		$this->requestInstance=new \HuiLib\Request\Http();
		
		return $this->requestInstance;
	}
}
