<?php
namespace HuiLib\App;

/**
 * Module基础类
 * 
 * @author 祝景法
 * @since 2013/09/20
 */
class Module
{
	/**
	 * 基础APP实例
	 * @var \HuiLib\App\AppBase
	 */
	protected $appInstance;

	protected function __construct(\HuiLib\App\AppBase $appInstance)
	{
		$this->appInstance=$appInstance;
	}
	
	
	
}
