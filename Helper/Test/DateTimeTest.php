<?php
namespace HuiLib\Helper\Test;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/10/27
 */
class DateTimeTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
		echo \HuiLib\Helper\DateTime::format(time(), 1000);
		echo \HuiLib\Helper\DateTime::format(time(), 10);
	}
	
	protected static function className(){
		return __CLASS__;
	}
}