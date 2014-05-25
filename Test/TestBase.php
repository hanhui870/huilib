<?php
namespace HuiLib\Test;

/**
 * 测试框架基础
 *
 * @author 祝景法
 * @since 2013/09/03
 */
abstract class TestBase
{
	//已初始化对象缓存
	private static $instanceCache;
	
	protected function __construct(){
		
	}
	
	abstract public function run();
	
	/**
	 * 执行测试
	 * @param 测试执行命名空间 $param
	 */
	public static function exec($param){
		
	}
	
	/**
	 * 执行断言
	 * 
	 * @param mix $runResult 实际运行结果
	 * @param mix $expected 期望结果
	 */
	public function assert($runResult, $expected=FALSE){
	    $debug=debug_backtrace();
	    if (!empty($debug[1])) {
	        $info=$debug[1];
	        echo 'Exec: '.$info['class'].'::'.$info['function'].' line:'.$debug[0]['line'].PHP_EOL;
	    }
		if ($runResult===$expected) {
			echo 'True'.PHP_EOL;
		}else{
			echo 'Expected:'.var_export($expected, 1).', RunResult:'.var_export($runResult, 1).PHP_EOL;
		}
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