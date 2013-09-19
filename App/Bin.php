<?php
namespace HuiLib\App;

/**
 * Bin运行应用初始化
 * 
 * @author 祝景法
 * @since 2013/08/11
 */
class Bin extends AppBase
{
	const RUN_METHOD='Bin';
	
	protected function __construct($config)
	{
		parent::__construct($config);
	}
	
	/**
	 * 初始化请求
	 */
	protected function initRequest(){
	
	}
}
