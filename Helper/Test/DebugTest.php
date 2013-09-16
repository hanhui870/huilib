<?php
namespace HuiLib\Helper\Test;

use HuiLib\Db\Query;
use HuiLib\Db\Query\Where;
use HuiLib\Helper\Debug;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class DebugTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testMark();
	}
	
	private function testMark(){
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