<?php
namespace HuiLib\Helper\Test;

use HuiLib\Helper\String;

/**
 * 参数类测试用例
 *
 * @author 祝景法
 * @since 2013/09/24
 */
class StringTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
		echo 'Default charset:'.String::getDefaultCharset()."\n";
		echo String::iconv('中国人', 'UTF-8', 'GBK')."\n"."\n";
		
		echo String::strlen('中国人')."\n";
		
		
	}

	protected static function className(){
		return __CLASS__;
	}
}