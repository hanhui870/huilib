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

	protected function __construct(\HuiLib\App\AppBase $appInstance=NULL)
	{
		if ($appInstance===NULL) {
			$appInstance=\HuiLib\Bootstrap::getInstance()->appInstance();
		}
		$this->appInstance=$appInstance;
	}
	
	/**
	 * 快速创建一个模块实例
	 */
	static function create(){
		return new static();
	}
	
}
