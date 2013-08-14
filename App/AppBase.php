<?php
namespace HuiLib\App;

/**
 * 应用创建文件
 *
 * @author 祝景法
 * @since 2013/08/11
 */
abstract class AppBase
{
	/**
	 * 引导程序
	 * @var \HuiLib\Bootstrap
	 */
	protected $bootStrap;
	
	/**
	 * 请求对象
	 * @var \HuiLib\Request\Base 
	 */
	protected $request;

	protected function __construct()
	{
		$this->bootStrap = \HuiLib\Bootstrap::getInstance ();
		$this->initRequest();
	}

	/**
	 * 执行应用入口
	 */
	public function run()
	{
	}

	/**
	 * 初始化请求
	 */
	abstract protected function initRequest();

	/**
	 * 初始化应用类
	 * 
	 * @return \HuiLib\App\AppBase
	 */
	public static function factory($runMethod)
	{
		$appClass = '\\HuiLib\\App\\' . $runMethod;
		$appInstance = new $appClass ();
		return $appInstance;
	}
}
