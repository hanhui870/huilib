<?php
namespace HuiLib\Helper\Test;

use HuiLib\Helper\IpLocation;
/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/10/27
 */
class IpLocationTest extends \HuiLib\Test\TestBase
{
    public function run(){
        $this->test();
    }

    private function test(){
        echo IpLocation::convertipTiny('58.100.158.219');
        echo IpLocation::convertipFull('192.241.234.172');
    }

    protected static function className(){
        return __CLASS__;
    }
}