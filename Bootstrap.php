<?php
namespace HuiLib;
use HuiLib\Loader\AutoLoad;

/**
 * 系统初始化引导文件
 * 
 * @author 祝景法
 * @since 2013/08/11
 */
class Bootstrap
{
	private static $instance;
	private $appConfig;

	private function __construct()
	{
		$this->setPath ();
		$this->setLoader ();
		$this->setConfig ();
	}

	/**
	 * 定义系统路径常量
	 * @throws \Exception
	 */
	private function setPath()
	{
		define ( 'SEP', DIRECTORY_SEPARATOR );
		
		/**
		 * 相关路径常量设置
		 * SYS_PATH 库根目录
		 * APP_PATH 应用根目录
		 * WWW_PATH 网页根目录
		*/
		define ( 'SYS_PATH', dirname ( __FILE__ ) . SEP );
		
		if (! defined ( 'APP_PATH' ) || ! defined ( 'WWW_PATH' ) || ! defined ( 'APP_CONFIG' )) {
			throw new \Exception ( "Please define Constant var APP_PATH & WWW_PATH  in the entry!" );
		}
	}

	/**
	 * 引入注册自动加载类
	 */
	private function setLoader()
	{
		include_once SYS_PATH . 'Loader/AutoLoad.php';
		$loadInstance = \HuiLib\Loader\AutoLoad::getInstance ();
		spl_autoload_register ( array ($loadInstance, 'loadClass' ) );
	}

	/**
	 * 引入注册自动加载类
	 */
	private function setConfig()
	{
		$this->appConfig=new \HuiLib\Config\ConfigBase (APP_CONFIG);
	}

	/**
	 * 初始化应用
	 */
	public function createApp()
	{
		die ();
	}

	/**
	 * 获取引导类实例
	 * @return \HuiLib\Bootstrap
	 */
	public static function getInstance()
	{
		if (self::$instance == NULL) {
			self::$instance = new self ();
		}
		return self::$instance;
	}
}
