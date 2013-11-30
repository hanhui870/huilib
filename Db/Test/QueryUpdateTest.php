<?php
namespace HuiLib\Db\Test;

use HuiLib\Db\Query\Where;

/**
 * 数据库Query测试类
 *
 * @author 祝景法
 * @since 2013/09/13
 */
class QueryUpdateTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	/**
	 * 测试
	 */
	private function test(){
		//update tableTest set field1='fvalue1', num=num+1 where (id='16') ;
		$update=\HuiLib\Db\Query::update()->table('tableTest')->where(Where::createPair('id', '16'));
		$update->sets(array(
				'field1'=>'fvalue1',//KV模式
				'field2'=>'fvalue2',//KV模式
				'num'=>array('plain'=>'num=num+1') //Plain模式
		));
		//$update->query();
		echo $update->toString();
	}

	protected static function className(){
		return __CLASS__;
	}
}