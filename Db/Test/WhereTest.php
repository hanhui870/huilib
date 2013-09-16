<?php
namespace HuiLib\Db\Test;

use HuiLib\Db\Query;
use HuiLib\Db\Query\Where;
use HuiLib\Helper\Debug;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class WhereTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testChainWhere();
	}
	
	private function test(){
		$select=Query::select();
		
		$where=Where::createPair('name', 'zhujingfa');
		$where1=Where::createPlain('name is null');
		$where2=Where::createQuote('num in (?)', array(3, 5, 16));
		$where1->andCase($where2);
		$where->orCase($where1);

		//初始化adapter后才能escape
		$select->where($where);
		
		echo $where->toString();
	}
	
	private function testChainWhere(){
		$select=Query::select('test');
				
		$where1=Where::createPair('test', 'zzzzzzzzzzzzzzzzzzzzzzz')
			->orCase(Where::createPlain('test is null'));
		$where=Where::createQuote('num in (?)', array(3, 5, 16))->andCase($where1, Where::HAND_LEFT);
	
		//初始化adapter后才能escape
		$select->where($where);
	
		$re=$select->query();
		echo $select->toString();
		
		\HuiLib\Helper\Debug::out ($re->fetchAll());
	}
	
	private function testWhereBenchMark(){
		Debug::mark('startSelect');
		$select=Query::select('test');
		Debug::mark('endSelect');
	
		Debug::mark('startWhere');
		$where1=Where::createPair('test', 'zzzzzzzzzzzzzzzzzzzzzzz')
		->orCase(Where::createPlain('test is null'));
	
		$where=Where::createQuote('num in (?)', array(3, 5, 16))->andCase($where1, Where::HAND_LEFT);
		Debug::mark('endWhere');
	
		//初始化adapter后才能escape
		$select->where($where);
	
		//echo $select->toString();
		Debug::mark('startQuery');
		$re=$select->query();
		Debug::mark('endQuery');
		\HuiLib\Helper\Debug::out ($re->fetchAll());
	
		Debug::elapsed('startSelect', 'endSelect');
		Debug::elapsed('startWhere', 'endWhere');
		Debug::elapsed('startQuery', 'endQuery');
		Debug::elapsed('startSelect', 'endQuery');
		Debug::elapsed('startSelect', 'endALL');
	}

	protected static function className(){
		return __CLASS__;
	}
}