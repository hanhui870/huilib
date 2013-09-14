<?php
namespace HuiLib\Db\Test;

/**
 * 数据库Query测试类
 *
 * @author 祝景法
 * @since 2013/09/13
 */
class QueryTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testJoinSelect();
	}
	
	private function testUnionSelect(){
		$select=\HuiLib\Db\Query::Select()->from('test')->where(array('test=2','id=3'), \HuiLib\Db\Query\Select::WHERE_OR)->limit(10)->order('id asc');

		$select1=\HuiLib\Db\Query::Select()->from('test')->where('id=2')->limit(16)->offset(21);//从句limit offset等信息会被忽略

		$select->union($select1);

		$shmt=$this->app->getDb()->query($select);

		//整体输出
		//\HuiLib\Helper\Debug::out ($shmt->fetchAll());
		
		//Foreach style
		foreach ($shmt as $unit){
			\HuiLib\Helper\Debug::out ($unit);
		}
		
		echo $select->toString();
	}
	
	private function testJoinSelect(){
		$select=\HuiLib\Db\Query::Select()->columns(array('PrimaryID'=>'id', 'Description'=>'test'))->from(array('t'=>'test'))->join(array('n'=>'name'), 't.id=n.tid', 'n.name as sname, n.sid as bbid')->where('t.id=2')->limit(10)->offset(0);

		$re=$this->app->getDb()->query($select);
		\HuiLib\Helper\Debug::out ($re->fetchAll());
		echo $select->toString();
	}
	
	private function testAdapterSelect(){
		$select=\HuiLib\Db\Query::Select()->from('test')->where('id=2')->limit(10)->offset(1);
		
		$re=$this->app->getDb()->query($select);
		\HuiLib\Helper\Debug::out ($re->fetchAll());
		echo $select->toString();
	}

	protected static function className(){
		return __CLASS__;
	}
}