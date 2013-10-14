<?php
namespace HuiLib\Helper\Test;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class CookieTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
		$cookie=\HuiLib\Helper\Cookie::create();
		$cookie->enableHttpOnly()->enableP3P();
		//$cookie->enableSecure();//得在https条件下测试
		//$cookie->setPath('/index/'); //目录需要在相应目录下测试
		
		//超长生命期测试
		$cookie->disableHttpOnly()->setLife(99999999)->setSookie('bbs', time());
	}
	
	private function testDel(){
		$cookie=\HuiLib\Helper\Cookie::create();
		$cookie->delCookie('bbs');
	}

	protected static function className(){
		return __CLASS__;
	}
}