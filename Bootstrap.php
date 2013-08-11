<?php
namespace HuiLib;
use HuiLib\Loader\AutoLoad;

/**
 * 系统初始化引导文件
 * 
 * SYS_PATH 库根目录
 * APP_PATH 应用根目录
 * WWW_PATH 网页根目录
 * APP_ENV 当前应用执行环境，匹配相关配置
 * RUN_METHOD 应用执行方式web || bin
 * 
 * @author 祝景法
 * @since 2013/08/11
 */
class Bootstrap
{
	private static $instance;
	
	/**
	 * 运行配置
	 * @var array
	 */
	private $appConfig;
	
	/**
	 * 运行环境
	 * @var string Enum
	 */
	private $runEnv;
	private $allowedEnv = array ('production', 'testing', 'develop' );
	const DEFAULT_ENV = 'production';

	private function __construct()
	{
		if (! defined ( 'RUN_METHOD' ) ) {
			throw new \Exception ( "Please define Constant var RUN_METHOD  in the entry!" );
		}
		
		$this->setPath ();
		$this->setEnv ();
		$this->setLoader ();
		$this->setConfig ();
	}

	/**
	 * 定义系统路径常量
	 * 
	 * @throws \Exception
	 */
	private function setPath()
	{
		define ( 'SEP', DIRECTORY_SEPARATOR );
		define ( 'SYS_PATH', dirname ( __FILE__ ) . SEP );
		
		if (! defined ( 'APP_PATH' ) || ! defined ( 'WWW_PATH' )) {
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
	 * 设置启动配置
	 */
	private function setConfig()
	{
		if (! defined ( 'APP_CONFIG' )) {
			throw new \Exception ( "Please define Constant var APP_CONFIG  in the entry!" );
		}
		
		$this->appConfig = new \HuiLib\Config\ConfigBase ( APP_CONFIG );
		\HuiLib\Helper\Debug::out ( $this->getConfig()->getBySection());
	}

	/**
	 * 返回配置实例
	 * @return \HuiLib\Config\ConfigBase
	 */
	public function getConfig()
	{
		return $this->appConfig;
	}

	/**
	 * 初始化应用
	 */
	public function createApp()
	{
		die ();
	}

	/**
	 * 设置应用当前运行环境
	 */
	private function setEnv()
	{
		if (isset ( $_SERVER ['SERVER_ENV'] ) && in_array ( $_SERVER ['SERVER_ENV'], $this->allowedEnv )) {
			define ( "APP_ENV", $_SERVER ['SERVER_ENV'] );
		} else {
			define ( "APP_ENV", self::DEFAULT_ENV );
		}
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
