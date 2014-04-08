<?php
namespace HuiLib\Redis\Test;

/**
 * 模板测试类
 * 
 * 主要测试UserExtraRow对象的构建和析构调用，在不同作用域不会反复调用，只会在最早一次和最后一次调用。
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class HashRowTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
	    echo 333;
	    $user=\Model\Redis\UserExtraRow::create(1);
	    $user->actionFinish();
	    print_r($user);
	    echo 444;
	}

	protected static function className(){
		return __CLASS__;
	}
}
echo 111;
$user=\Model\Redis\UserExtraRow::create(1);
print_r($user);
echo 222;
