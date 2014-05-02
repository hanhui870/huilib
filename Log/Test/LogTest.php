<?php
namespace HuiLib\Log\Test;

use HuiLib\Log\LogBase;

/**
 * 日志模块测试类
 *
 * @author 祝景法
 * @since 2013/11/10
 */
class LogTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->testFile();
	}
	
	private function test(){
		$log=LogBase::getMysql();
		$log->setType(LogBase::TYPE_USERERROR);
		$log->setIdentify('PHPFrameTest');
		//支持批量插入
		$log->add('sorry, db falied');
		$log->add('sorry, db falied');
		$log->add('sorry, db falied');
	}
	
	private function testFile(){
		$log=LogBase::getFile();
		$log->setType(LogBase::TYPE_DBERROR);
		$log->setIdentify('PHPFrameTest');
		$log->add('sorry, db falied');
		$log->add('sorry, db falied');
		$log->add('sorry, db falied');
		
		LogBase::getFile()->setType(LogBase::TYPE_DAEMON)->setIdentify('Up201405')->add('Find 1 thing wrong.');
	}

	protected static function className(){
		return __CLASS__;
	}
}