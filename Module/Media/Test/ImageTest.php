<?php
namespace HuiLib\Module\Media\Test;

use HuiLib\Module\Media\Image\ImageBase;
use HuiLib\Module\Media\Image\Thumb;

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
        $this->testThumbOpt ();
    }

    /**
     * 修改大小
     */
    private function testScale()
    {
        $file=dirname(__FILE__).'/test.jpg';
        $imageJpg=ImageBase::create($file, FALSE);
        echo $imageJpg->getImageFormat();
        $imageJpg->thumb(600, 900, dirname(__FILE__).'/test2.jpg');
        
        //png常常变得很大容量 保存为gif可以缩小体积
        $file=dirname(__FILE__).'/test.png';
        $imagePng=ImageBase::create($file, FALSE);
        echo $imagePng->getImageFormat();
        $imagePng->thumb(400, 300, dirname(__FILE__).'/test2.png');
        
        $file=dirname(__FILE__).'/test.gif';
        $imageGif=ImageBase::create($file, FALSE);
        echo $imageGif->getImageFormat();
        $imageGif->thumb(500, 250, dirname(__FILE__).'/test2.gif');
        
        //print_r($image->getOriginalSize());
    }
    
    /**
     * 截取图片
     */
    private function testCrop()
    {
        $file=dirname(__FILE__).'/test.jpg';
        $imageJpg=ImageBase::create($file, FALSE);
        $imageJpg->thumb(800, 1200);
        //echo $imageJpg->getImageFormat();
        $imageJpg->crop(50, 50, 400, 400, dirname(__FILE__).'/test2.jpg');

        //png常常变得很大容量 保存为gif可以缩小体积
        $file=dirname(__FILE__).'/test.png';
        $imagePng=ImageBase::create($file, FALSE);
        //echo $imagePng->getImageFormat();
        $imagePng->crop(50, 50, 400, 400, dirname(__FILE__).'/test2.png');
    
        $file=dirname(__FILE__).'/test.gif';
        $imageGif=ImageBase::create($file, FALSE);
        //echo $imageGif->getImageFormat();
        $imageGif->crop(50, 50, 400, 400, dirname(__FILE__).'/test2.gif');
    
        //print_r($image->getOriginalSize());
    }
    
    /**
     * 自动缩放截取图片
     */
    private function testThumbCrop()
    {
        $file=dirname(__FILE__).'/test.jpg';
        $imageJpg=Thumb::create($file, FALSE);
        $imageJpg->thumbByCrop(180, 180, dirname(__FILE__).'/testcrop.jpg');
        
        //png常常变得很大容量 保存为gif可以缩小体积
        $file=dirname(__FILE__).'/test.png';
        $imagePng=Thumb::create($file, FALSE);
        $imagePng->thumbByCrop(180, 180, dirname(__FILE__).'/testcrop.png');
        
        $file=dirname(__FILE__).'/test.gif';
        $imageGif=Thumb::create($file, FALSE);
        $imageGif->thumbByCrop(180, 180, dirname(__FILE__).'/testcrop.gif');
    }
    
    /**
     * 自动缩放截取图片
     */
    private function testThumbOpt()
    {
        $file=dirname(__FILE__).'/test.jpg';
        //$imageJpg=Thumb::create($file, FALSE);
        //$imageJpg->thumbByMin(600, dirname(__FILE__).'/testMin600.jpg');
        //$imageJpg->thumbByMax(800, dirname(__FILE__).'/testMax800.jpg');
        //$imageJpg->thumbByMinWidth(400, dirname(__FILE__).'/testMinWidth400.jpg');
        //$imageJpg->thumbNormalUpload(dirname(__FILE__).'/testNormalUp.jpg');
        
        $file=dirname(__FILE__).'/test.gif';
        $imageJpg=Thumb::create($file, FALSE);
        $imageJpg->thumbNormalUpload(dirname(__FILE__).'/testNormalUp.gif');
    }
    
    /**
     * 添加水印
     */
    private function testWatermark()
    {
        $file=dirname(__FILE__).'/test.jpg';
        $imageJpg=Thumb::create($file, FALSE);
        $imageJpg->thumbNormalUpload();
        $imageJpg->setWaterImage(dirname(__FILE__).'/watermark.png');
        $imageJpg->waterLeftTop(dirname(__FILE__).'/testWater.jpg');
        
        $file=dirname(__FILE__).'/test.png';
        $imagePng=Thumb::create($file, FALSE);
        $imagePng->thumbNormalUpload();
        $imagePng->setWaterImage(dirname(__FILE__).'/watermark.png');
        $imagePng->waterLeftTop(dirname(__FILE__).'/testWater.png');
        
        $file=dirname(__FILE__).'/test.gif';
        $imageGif=Thumb::create($file, FALSE);
        $imageGif->thumbNormalUpload();
        $imageGif->setWaterImage(dirname(__FILE__).'/watermark.png');
        $imageGif->waterLeftTop(dirname(__FILE__).'/testWater.gif');
    }

    protected static function className()
    {
        return __CLASS__;
    }
}