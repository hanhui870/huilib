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
	public function __construct(\HuiLib\App\AppBase $appInstance)
	{
		$this->_appInstance=$appInstance;
	}
	
	/**
	 * 渲染输出
	 */
	public function render($view, $ajaxDelimiter = NULL)
	{
		$this->initEngine($view, $ajaxDelimiter);
		$this->_engineInstance->parse()->writeCompiled();
		
		include $this->_engineInstance->getCachePath();
	}
}
