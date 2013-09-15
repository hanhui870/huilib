<?php
namespace HuiLib\Db\Test;

/**
 * 数据库Query测试类
 *
 * @author 祝景法
 * @since 2013/09/13
 */
class QuerySelectTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testJoinSelect();
	}
	
	/**
	 * Select Union测试
	 */
	private function testUnionSelect(){
		
		$select=\HuiLib\Db\Query::select()->table('test')->where(array('test=2','id=3'), \HuiLib\Db\Query\Select::WHERE_OR)->limit(10)->order('id asc');

		$select1=\HuiLib\Db\Query::select()->table('test')->where('id=2')->limit(16)->offset(21);//从句limit offset等信息会被忽略

		//array quote
		//echo $select->escape(array('fafdas', 'fd', 'eeee', 'bbbbb'));
		
		$select->union($select1);

		$shmt=$select->query();

		//整体输出
		\HuiLib\Helper\Debug::out ($shmt->fetchAll());
		
		//Foreach style
		foreach ($shmt->getStatement() as $unit){
			\HuiLib\Helper\Debug::out ($unit);
		}
		
		echo $select->toString();
	}
	
	/**
	 * Select Bind测试
	 */
	private function testBindSelect(){
		//as name测试
		$select=\HuiLib\Db\Query::select()->columns(array('PrimaryID'=>'id', 'Description'=>'test'))->table(array('t'=>'test'))
		->join(array('n'=>'name'), 't.id=n.tid', 'n.name as sname, n.sid as bbid')->where('t.id=:id')->limit(10)->offset(0);
	
		$re=$select->prepare()->execute(array('id'=>2));
		\HuiLib\Helper\Debug::out ($re->fetchAll());
		echo $select->toString();
	}
	
	/**
	 * Select Join测试
	 */
	private function testJoinSelect(){
		//as name测试
		$select=\HuiLib\Db\Query::select()->columns(array('PrimaryID'=>'id', 'Description'=>'test'))->table(array('t'=>'test'))->join(array('n'=>'name'), 't.id=n.tid', 'n.name as sname, n.sid as bbid')->where('t.id=2')->limit(10)->offset(0);

		$re=$select->query();
		\HuiLib\Helper\Debug::out ($re->fetchAll());
		echo $select->toString();
	}
	
	/**
	 * Select 普通测试
	 */
	private function testAdapterSelect(){
		$select=\HuiLib\Db\Query::select()->table('test')->where('id=2')->limit(10)->offset(0)->enableForUpdate();
		
		$re=$select->query();
		\HuiLib\Helper\Debug::out ($re->fetchAll());
		echo $select->toString();
	}

	protected static function className(){
		return __CLASS__;
	}
}