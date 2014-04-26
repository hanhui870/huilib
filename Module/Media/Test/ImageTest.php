<?php
namespace HuiLib\Module\Media\Test;

use HuiLib\Module\Media\Image\ImageBase;

/**
 * 图像测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class ImageTest extends \HuiLib\Test\TestBase
{

    public function run()
    {
        $this->test ();
    }

    private function test()
    {
        $file=dirname(__FILE__).'/test.jpg';
        $image=ImageBase::create($file, FALSE);
        
        echo $image->getImageFormat();
        
        //print_r($image->getOriginalSize());
    }

    protected static function className()
    {
        return __CLASS__;
    }
}