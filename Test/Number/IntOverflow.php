<?php 
/**
 * php整数运算溢出测试
 *
 * @author 祝景法
 * @since 2013/12/19
 */
class TestTemplate extends \HuiLib\Test\TestBase
{
    public function run(){
        $this->test();
    }

    private function test(){
        $amount=8004586000;
        echo $amount;
        var_dump($amount/1000000);
        var_dump(floatval($amount/1000000));
        
        echo '<br>*******没使用intval()函数反而不会溢出********************************************<br>';
        echo 5224556653255656554456666555/25;
        
        echo '<br>***************************************************<br>';
        echo intval($amount);
        var_dump(intval($amount)/1000000);
        var_dump(floatval(intval($amount)/1000000));
        
        /**
         * Output //因为超过32位，溢出了 不用intval反而不会溢出
         * 8004586000float(8004.586) float(8004.586) 2.0898226613023E+26
         ***************************************************
         -585348592float(-585.348592) float(-585.348592)
         */
    }

    protected static function className(){
        return __CLASS__;
    }
}
