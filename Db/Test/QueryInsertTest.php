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
		$this->testDup();
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
	 * KV测试
	 */
	private function testKvInsert(){
		$insert=\HuiLib\Db\Query::Insert()->table('test')->kvInsert(array('test'=>'zhujingfa'))->values(array('after kvInsert", me\' to'));
		$insert->query();
		echo $insert->toString();
	}
	
	/**
	 * 测试
	 */
	private function testDup(){
		$insert=\HuiLib\Db\Query::Insert()->table('test')->enableDuplicate(true)->fields(array('id','test'))->dupFields(array('id','test', 'num'))
		->values(array(time(), 'fdafa\'\\dfdas1'))
		->values(array(time(), 'fdafa\'\\dfdas2'), array('test'=>'fdafa\'\\dfdas', array('plain'=>'num=num+1')))
		->values(array(time()+rand(1, 5), 'fdafa\'\\dfdas3'))
		->values(array(1379180480, 'fdafa\'\\dfdas4'), array('num'=>array('plain'=>'num=num+1')));
		$insert->query();
		echo $insert->toString();
	}

	protected static function className(){
		return __CLASS__;
	}
}