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
		$re=$this->app->getDb()->getConnection()->query("select count(*) from user");
		var_dump($re->fetchAll());
	}
	
	private function testSelect(){
		
	}

	protected static function className(){
		return __CLASS__;
	}
}