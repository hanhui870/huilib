<?php
namespace HuiLib\Db\Test;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class TransactionTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testMysql();
	}
	
	/**
	 * 貌似Mysql执行过程中抛出PDOException，回退才有效
	 * 
	 * 在系统代码内部手工抛出异常并不能影响结果。
	 * @TODO 还需要进一步确认。不然就不能人工撤销了。
	 */
	private function testMysql(){
		$dbAdapter=\HuiLib\Db\DbBase::createMaster();
		try {
			$dbAdapter->beginTransaction();
			
			$dbAdapter->getConnection()->query("insert into test.test set field1=22222, field2=3333");
			$dbAdapter->getConnection()->query("insert into test.test set field1=0000, field0=00000");//Field0不存在
			
			$dbAdapter->commit();
			
		}catch (\Exception $e){
			print_r($e);
			$dbAdapter->rollback();
		}
		
	}

	protected static function className(){
		return __CLASS__;
	}
}