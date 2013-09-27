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
		session_regenerate_id();
		//$_SESSION['hanhui']='hello baby';
		
		//$_SESSION['dddd']=$_SESSION['hanhui'];
	}
	
	protected static function className()
	{
		return __CLASS__;
	}
}