<?php
namespace HuiLib\Redis\Test;

$i=0;

/**
 * 模板测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class ClassStaticInitTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
	    global $i;
	    echo ++$i.PHP_EOL;
	    testCC();
	    echo ++$i.PHP_EOL;
	    
	    $c=testCD::create();
	    echo ++$i.PHP_EOL;
	}

	protected static function className(){
		return __CLASS__;
	}
}

function testCC()
{
    global $i;
    echo ++$i.PHP_EOL;
    $b=testCD::create();
    echo ++$i.PHP_EOL;
}

echo ++$i.PHP_EOL;
testCD::create();
echo ++$i.PHP_EOL;

/**
 * 测试单例模式下 构造和析构函数的调用
 * 
 * @author HanHui
 *
 */
class testCD
{
    private static $instance=NULL;
    
    public function __construct()
    {
        echo "constructied.".PHP_EOL;
    }
    
    public function __destruct()
    {
        echo "destructied.".PHP_EOL;
    }
    
    public static function create()
    {
        if (self::$instance===NULL) {
            self::$instance=new static();
        }
        
        return self::$instance;
    }
}