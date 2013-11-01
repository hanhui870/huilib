<?php
namespace HuiLib\Db\Test;

use HuiLib\Db\Query\Where;

/**
 * 数据库Query测试类
 *
 * @author 祝景法
 * @since 2013/09/13
 */
class QueryDeleteTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	/**
	 * 测试
	 */
	private function test(){
		//delete from tableTest where (id='2') limit 10 ;
		$delete=\HuiLib\Db\Query::delete()->table('tableTest')->where(Where::createPair('id', '2'))->limit(10);
		//echo $delete->query();
		echo $delete->toString();
	}

	protected static function className(){
		return __CLASS__;
	}
}