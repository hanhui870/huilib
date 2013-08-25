<?php
namespace HuiLib\Db\Test;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class PdoTest
{
	public function testMysql(){
		$re=$this->getDb()->getConnection()->query("select count(*) from user");
		var_dump($re->fetchAll());
	}


}