<?php
namespace HuiLib\App\Test;

/**
 * 函数和类的灵活参数调用机制
 *
 * @author 祝景法
 * @since 2013/11/05
 */
class AutoStaticParamTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
		echo "\nCall Staticly:\n";
		TestClass::create('first', 'second');
		
		echo "\nCall Directly:\n";
		new TestClass('first', 'second');
	}

	protected static function className(){
		return __CLASS__;
	}
}


class TestClass extends \HuiLib\App\Module
{
	public function __construct()
	{
		echo "Call TestClass::__construct  Param:\n";
		print_r(func_get_args());
	}
}