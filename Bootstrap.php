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
	const DEFAULT_ENV = 'production';
	private static $instance;
	
	/**
	 * 运行环境
	 * @var string Enum
	 */
	private $runEnv;
	
	/**
	 * 运行入口
	 */
	private $runMethod;
	
	/**
	 * 应用单例
	 * @var \HuiLib\App\AppBase
	 */
	private $application;
	
	/**
	 * 期末执行绑定
	 * @var HuiLib\Runtime\ShutCall
	 */
	private $shutCall;
	
	private $allowedEnv = array ('production', 'testing', 'develop' );
	
	private function __construct()
	{
		if (! defined ( 'RUN_METHOD' ) ) {
			throw new \Exception ( "Please define Constant var RUN_METHOD  in the entry!" );
		}
		$this->runMethod=RUN_METHOD;
		
		$this->initPath ();
		$this->initEnv ();
		$this->initLoader ();
	}

	/**
	 * 定义系统路径常量
	 * 
	 * @throws \Exception
	 */
	private function initPath()
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
	private function initLoader()
	{
		include_once SYS_PATH . 'Loader/AutoLoad.php';
		$loadInstance = \HuiLib\Loader\AutoLoad::getInstance ();
		spl_autoload_register ( array ($loadInstance, 'loadClass' ) );
	}

	/**
	 * 初始化应用
	 * @return \HuiLib\App\AppBase
	 */
	public function createApp($config)
	{
		$this->application=\HuiLib\App\AppBase::factory($this->runMethod, $config);
		
		return $this->application;
	}

	/**
	 * 初始化应用当前运行环境
	 */
	private function initEnv()
	{
		if (isset ( $_SERVER ['SERVER_ENV'] ) && in_array ( $_SERVER ['SERVER_ENV'], $this->allowedEnv )) {
			define ( "APP_ENV", $_SERVER ['SERVER_ENV'] );
		} else {
			define ( "APP_ENV", self::DEFAULT_ENV );
		}
	}

	/**
	 * 获取已创建的应用
	 */
	public function appInstance(){
		return $this->application;
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
