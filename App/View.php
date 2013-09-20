<?php
namespace HuiLib\App;

/**
 * View类
 * 
 * @author 祝景法
 * @since 2013/09/20
 */
class View extends \HuiLib\View\ViewBase
{
	protected function __construct(\HuiLib\App\AppBase $appInstance)
	{
		$this->_appInstance=$appInstance;
	}
	
	/**
	 * 渲染输出
	 */
	public function render()
	{
		
	}
}
