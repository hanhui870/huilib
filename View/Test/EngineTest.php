<?php
namespace HuiLib\View\Test;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class EngineTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
		echo 'hello world';
	}

	protected static function className(){
		return __CLASS__;
	}
}