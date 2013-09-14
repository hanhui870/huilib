<?php
namespace HuiLib\Db\Test;

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
		$update=\HuiLib\Db\Query::update()->table('test')->where(array('id=2'))
		->values(array('test'=>'zzzzzzzzzzzzzzzzzzzzzzz', 'num'=>array('plain'=>'num=num+1')));
		$update->query();
		echo $update->toString();
	}

	protected static function className(){
		return __CLASS__;
	}
}