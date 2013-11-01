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
		//insert into test (field1, field2) values ('fvalue1', 'fvalue2') ;
		$insert=\HuiLib\Db\Query::Insert()->table('test.test')->fields(array('field1','field2'))->values(array('fvalue1', 'fvalue2'));
		
		//insert into test (field1, field2) values ('fvalue1', 'fvalue2'), ('fvalue11', 'fvalue22') ;
		$insert->values(array('fvalue11', 'fvalue22'));
		
		$insert->query();
		echo $insert->affectedRow();
		echo $insert->toString();
	}
	
	/**
	 * KV测试
	 */
	private function testKvInsert(){
		//insert into test (field1, field2) values ('fvalue1', 'fvalue2'), ('fvalue11', 'fvalue22') ;
		$insert=\HuiLib\Db\Query::Insert()->table('test')->kvInsert(array('field1'=>'fvalue1', 'field2'=>'fvalue2'))->values(array('fvalue11', 'fvalue22'), array('fvalue11', 'fvalue22'));
		//$insert->query();
		echo $insert->toString();
	}
	
	/**
	 * 测试
	 */
	private function testDup(){
		//insert into test set field1='fvalue1', field2='fvalue2' on duplicate key update field1='newfvalue1', num=num+1 ;
		$insert=\HuiLib\Db\Query::Insert()->table('test')->enableDuplicate(true)->fields(array('field1','field2'))->dupFields(array('field1', 'num'))
		->values(array('fvalue1', 'fvalue2'), array('field2'=>'newfvalue1', array('plain'=>'num=num+1')));
		//$insert->query();
		echo $insert->toString();
	}

	protected static function className(){
		return __CLASS__;
	}
}