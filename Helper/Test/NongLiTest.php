<?php
namespace HuiLib\Helper\Test;

use HuiLib\Helper\NongLi;
/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/10/27
 */
class NongLiTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
	    $this->assert(NongLi::getInstance()->L2S('1987-6-21'), '1987-7-16');
	    //闰六月
	    $this->assert(NongLi::getInstance()->L2S('1987-69-21'), '1987-8-15');
	}
	
	protected static function className(){
		return __CLASS__;
	}
}