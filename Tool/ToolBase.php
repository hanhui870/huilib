<?php
namespace HuiLib\Tool;

/**
 * 工具集框架基础
 *
 * @author 祝景法
 * @since 2013/11/30
 */
abstract class ToolBase
{
	//已初始化对象缓存
	private static $instanceCache;
	
	//app初始化对象
	protected $appInstance;
	
	protected function __construct(){
	
	}
	
	abstract public function run();
	
	/**
	 * 设置app初始化运行环境
	 * @param \HuiLib\App\AppBase $app
	 */
	public function setApp(\HuiLib\App\AppBase $appInstance){
		$this->appInstance=$appInstance;
	}
	
	/**
	 * 只能获取多个之类单例
	 * @return \HuiLib\Test\TestBase
	 */
	public static function getInstance(){
		//子类名称
		$subName=static::className();
		if (isset(self::$instanceCache[$subName])) {
			return isset(self::$instanceCache[$subName]);
		}
	
		self::$instanceCache[$subName]=new static();
	
		return self::$instanceCache[$subName];
	}
}