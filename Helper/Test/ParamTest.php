<?php
namespace HuiLib\Helper\Test;

/**
 * 参数类测试用例
 *
 * @author 祝景法
 * @since 2013/09/24
 */
class ParamTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
		echo $encoding = \HuiLib\Helper\Param::server('fdasfdsa', \HuiLib\Helper\Param::TYPE_BOOL);
		var_dump($encoding);
		
		echo $encoding = \HuiLib\Helper\Param::server('fdasfdsa', \HuiLib\Helper\Param::TYPE_STRING);
		var_dump($encoding);
	}

	protected static function className(){
		return __CLASS__;
	}
}