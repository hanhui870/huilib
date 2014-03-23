<?php
namespace HuiLib\Db\Test;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class PdoTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testTransactionAdb();
	}
	
	private function testMysql(){
		$re=\HuiLib\Db\DbBase::createMaster()->getConnection()->query("select count(*) from test.test");
		var_dump($re->fetchAll());
	}
	
	private function testTransaction()
	{
	    $db=\HuiLib\Db\DbBase::createMaster()->getConnection();
	    
	    $time=time();
	    var_dump($time%2);
	    
	    try {
	        $db->beginTransaction();
	       
	        $db->query("update test.test set field1='$time' where id=2 ");
	        
	        //! 优先级高于 %
	        if (!($time%2)) {
	            throw new \Exception('error');
	        }
	        
	        $db->commit();
	    }catch (\Exception $e){
	        $db->rollBack();
	        
	        echo 'error exception, time:'.$time .' e:'.$e->getMessage();
	    }
	}
	
	private function testTransactionAdb()
	{
	    $db=\HuiLib\Db\DbBase::createMaster()->getConnection();
	     
	    $time=time();
	    var_dump($time%2);
	     
	    try {
	        \HuiLib\Db\DbBase::createMaster()->beginTransaction();
	
	        $db->query("update test.test set field1='$time' where id=2 ");
	         
	        //! 优先级高于 %
	        if (!($time%2)) {
	            throw new \Exception('error');
	        }
	         
	        \HuiLib\Db\DbBase::createMaster()->commit();
	    }catch (\Exception $e){
	        \HuiLib\Db\DbBase::createMaster()->rollBack();
	         
	        echo 'error exception, time:'.$time .' e:'.$e->getMessage();
	    }
	}
	
	private function testSlave()
	{
	    $salve=\HuiLib\Db\DbBase::createSlave();
	    $db=\HuiLib\Db\DbBase::createSlave()->getConnection();
	
	    $time=time();
	    var_dump($time%2);
	
	    try {
	        $salve->beginTransaction();
	
	        $db->query("update test.test set field1='$time' where id=2 ");
	
	        //! 优先级高于 %
	        if (!($time%2)) {
	            throw new \Exception('error');
	        }
	
	        $salve->commit();
	    }catch (\Exception $e){
	        $salve->rollBack();
	
	        echo 'error exception, time:'.$time .' e:'.$e->getMessage();
	    }
	}

	protected static function className(){
		return __CLASS__;
	}
}