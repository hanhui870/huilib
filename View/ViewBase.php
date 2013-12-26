<?php
namespace HuiLib\View;

/**
 * 视图基础类
 * 
 * 视图类内部使用变量全部加下划线，赋值到前台的变量直接写
 * 
 * 命名规则：
 * app. 程序相关信息
 * site.命名空间 网站配置相关
 * session.登录会话相关
 * my. 当前登录用户
 * 
 * @author 祝景法
 * @since 2013/09/20
 */
abstract class ViewBase
{
	/**
	 * 渲染引擎类
	 * @var \HuiLib\View\TemplateEngine 
	 */
	protected $_engineInstance;
	
	/**
	 * 基础APP实例
	 * @var \HuiLib\App\AppBase
	 */
	protected $_appInstance;

	/**
	 * 初始化渲染引擎
	 */
	protected function initEngine($view, $ajaxDelimiter = NULL)
	{
		$this->_engineInstance = new \HuiLib\View\TemplateEngine($view, $ajaxDelimiter);
		
		$viewConfig=$this->_appInstance->configInstance()->getByKey('webRun.view');
		
		if (empty($viewConfig['viewPath']) || empty($viewConfig['cachePath'])) {
			throw new \HuiLib\Error\Exception ( "请在网站配置中同时添加webRun.view.viewPath和webRun.view.cachePath路径" );
		}
		
		$this->_engineInstance->setViewPath($viewConfig['viewPath']);
		$this->_engineInstance->setCachePath($viewConfig['cachePath']);
	}

	/**
	 * 向前端赋值一个变量
	 */
	public function assign($key, $value = NULL)
	{
		if (is_string ( $key )) {
			// 根据字符串名称赋值
			if ('_' == substr ( $key, 0, 1 )) {
				throw new \HuiLib\Error\Exception ( "赋值给前台的变量不能以下划线开始" );
			}
			$this->$key = $value;
		} elseif (is_array ( $key )) {
			// 通过关联字符串赋值
			$error = false;
			foreach ( $key as $item => $val ) {
				if ('_' == substr ( $item, 0, 1 )) {
					$error = true;
					break;
				}
				$this->$item = $val;
			}
			if ($error) {
				throw new \HuiLib\Error\Exception ( "赋值给前台的变量不能以下划线开始" );
			}
		} else {
			throw new \HuiLib\Error\Exception ( 'View::assign, 仅接受字符串或数据形式key，received ' . gettype ( $key ) );
		}
		
		return $this;
	}

	abstract function render($view, $ajaxDelimiter = NULL);
}