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
		$this->testRowInstanceInsert();
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
		$insert=\HuiLib\Db\Query::Insert()->table('test')->kvInsert(array('field1'=>'fvalue1', 'field2'=>'fvalue2'))->values(array('fvalue11', 'fvalue22'));
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
	
	/**
	 * 测试多行
	 */
	private function testDupValues(){
		//insert into test set field1='fvalue1', field2='fvalue2' on duplicate key update field1='newfvalue1', num=num+1 ;
		$insert=\HuiLib\Db\Query::Insert()->table('test')->enableDuplicate(true)->fields(array('field1','field2'))->dupFields(array('field1', 'num'))
		->values(array('fvalue1', 'fvalue2'), array('field2'=>'newfvalue1', array('plain'=>'num=num+1')))->values(array('fvalue11', 'fvalue22'));
		//$insert->query();
		echo $insert->toString();
	}
	
	/**
	 * 测试多行行对象插入
	 */
	private function testRowInstanceInsert(){
		//insert into user_salt (`Uid`, `Salt`) values ('31', 'fdadsf'), ('32', 'aaadsf'), ('35', 'ddddd') ;
		$batch=array();
		$row=\Model\Table\UserSalt::create()->createRow();
		$row->Uid=31;
		$row->Salt="fdadsf";
		$batch[]=$row;
		
		$row=\Model\Table\UserSalt::create()->createRow();
		$row->Uid=32;
		$row->Salt="aaadsf";
		$batch[]=$row;
		
		$row=\Model\Table\UserSalt::create()->createRow();
		$row->Uid=35;
		$row->Salt="ddddd";
		$batch[]=$row;

		$insert=\HuiLib\Db\Query::Insert()->batchSaveRows($batch);
		echo $insert->toString();
		//$insert->query();
	}

	protected static function className(){
		return __CLASS__;
	}
}