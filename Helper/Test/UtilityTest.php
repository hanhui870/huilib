<?php
namespace HuiLib\Helper\Test;

use HuiLib\Helper\Utility;
/**
 * 参数类测试用例
 *
 * @author 祝景法
 * @since 2013/09/24
 */
class UtilityTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
	    echo Utility::geneRandomHash().PHP_EOL;
		echo Utility::genUuid();
	}

	protected static function className(){
		return __CLASS__;
	}
}