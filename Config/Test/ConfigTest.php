<?php
namespace HuiLib\Config\Test;

/**
 * 数据库Query测试类
 *
 * @author 祝景法
 * @since 2013/09/13
 */
class ConfigTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
		$config = new \HuiLib\Config\ConfigBase ( dirname(__FILE__).'/Test.ini' );
		
		print_r($config->toArray());
	}

	protected static function className(){
		return __CLASS__;
	}
}
