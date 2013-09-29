<?php
namespace HuiLib\Session\Test;

/**
 * Session测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class SessionTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testRedis();
	}
	
	private function testRedis()
	{
		\HuiLib\Session\SessionBase::create($this->appInstance->configInstance ());
		$_SESSION['gogogog']='fdsafsda';

		print_r($_SESSION);
	}
	
	protected static function className()
	{
		return __CLASS__;
	}
}