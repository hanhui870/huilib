<?php
namespace HuiLib\App;

/**
 * 数据表模型Model基础类
 * 
 * @author 祝景法
 * @since 2013/10/20
 */
class Model
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
