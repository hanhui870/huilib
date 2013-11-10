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
		$this->test();
	}
	
	private function test(){
		$log=LogBase::getMysql();
		$log->setType(LogBase::TYPE_USERERROR);
		$log->setIdentify('PHPFrameTest');
		echo $log->add('sorry, db falied');
	}

	protected static function className(){
		return __CLASS__;
	}
}