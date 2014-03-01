<?php
namespace HuiLib\Config\Test;

use HuiLib\Helper\DateTime;
/**
 * 数据库Query测试类
 *
 * @author 祝景法
 * @since 2013/09/13
 */
class ConfigTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
		//$this->testMerge();
	}
	
	private function test(){
		$config = new \HuiLib\Config\ConfigBase ( dirname(__FILE__).'/Test.ini' );
		echo "lastupdate:".DateTime::format(0, $config->getLastUpdate())."\n";
		print_r($config->getByKey());
		
		$config->setByKey('app.domain', 'testOverwrite');
		$config->setByKey('webRun.cookie.pre', 'Asia/BeiJin');
		$config->setByKey('webRun.cookie.host.name', 'baichi');
		print_r($config->toArray());
		
	}
	
	private function testMerge(){
		$array=array('a'=>'aa', 'b'=>'bb','c'=>array('cc'=>'ccc'));
		$array1=array('a'=>'aa1', 'b1'=>'bb1','c'=>array('cc1'=>'ccc'));
		$array1=array('a'=>'aa1', 'b1'=>'bb1','c'=>array('cc1'=>array('cccc'=>'bbbbb')));
		print_r(array_merge_recursive($array,$array1));
		print_r(array_merge($array,$array1));
		print_r($array+$array1);
	}

	protected static function className(){
		return __CLASS__;
	}
}
