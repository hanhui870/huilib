<?php
namespace Lib;

/**
 * @author 祝景法
 * @date 2013/08/11
 *
 * 系统初始化引导文件
 */
class Bootstrap
{
	private static $instance;
	
	private function __construct()
	{
		
	}
	
	public function run()
	{
		define ( 'SEP', DIRECTORY_SEPARATOR );
		
		/**
		 * 相关路径常量设置
		 * SYS_ROOT 库根目录
		 * APP_ROOT 应用根目录
		 * WWW_ROOT 网页根目录
		*/
		define ( 'SYS_ROOT', dirname ( __FILE__ ) . SEP );

		if (! defined ( 'APP_ROOT' ) || ! defined ( 'WWW_ROOT' ))
		{
			throw new Exception ( "Please define Constant var APP_ROOT & WWW_ROOT  in the entry!" );
		}
		
		include_once SYS_ROOT . 'Loader/AutoLoad.php';
		spl_autoload_register ( "Lib\Loader\AutoLoad::loadClass" );
	}
	
	/**
	 * 获取引导类实例
	 * @return \Lib\Bootstrap
	 */
	public static function getInstance()
	{
		if ( self::$instance == NULL ) {
			self::$instance=new self();
		}
		return self::$instance;
	}
}

Bootstrap::getInstance()->run();