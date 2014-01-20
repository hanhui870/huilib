<?php
namespace HuiLib\Helper\Test;

/**
 * 数字库类测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class NumberTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
	    $a=1.0050015;
	    var_dump($a);
	    
	    $b=0.62351;
	    var_dump($b);
	    
	    //默认计算是可以准确显示浮点的
	    var_dump($a-$b);
	    
	    //除法的时候，位数就很多了
	    var_dump($a/$b);
	    
	    //返回string 所以及时结果是0，也判断通不过
	    var_dump(!bcmul($a, 0, 8));
	    
	    //正常数据判断
	    var_dump(!floatval(bcmul($a, 0, 8)));
	    
	    //默认千位分隔符是逗号，注意这个坑
	    echo number_format(1162.5322, 8);
	}

	protected static function className(){
		return __CLASS__;
	}
}