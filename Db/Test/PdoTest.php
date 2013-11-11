<?php
namespace HuiLib\Db\Test;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class PdoTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testMysql();
	}
	
	private function testMysql(){
		$re=\HuiLib\Db\DbBase::createMaster()->getConnection()->query("select count(*) from test.test");
		var_dump($re->fetchAll());
	}

	protected static function className(){
		return __CLASS__;
	}
}