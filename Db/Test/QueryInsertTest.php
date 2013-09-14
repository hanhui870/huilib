<?php
namespace HuiLib\Db\Test;

/**
 * 数据库Query测试类
 *
 * @author 祝景法
 * @since 2013/09/13
 */
class QueryInsertTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	/**
	 * 测试
	 */
	private function test(){
		$insert=\HuiLib\Db\Query::Insert()->table('test')->fields(array('id','test'))->values(array(time(), 'fdafa\'\\dfdas'));
		$insert->query();
		echo $insert->toString();
	}
	
	/**
	 * 测试
	 */
	private function testKvInsert(){
		$insert=\HuiLib\Db\Query::Insert()->table('test')->kvInsert(array('test'=>'zhujingfa'))->values(array('after kvInsert", me\' to'));;
		$insert->query();
		echo $insert->toString();
	}

	protected static function className(){
		return __CLASS__;
	}
}