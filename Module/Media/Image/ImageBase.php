<?php
namespace HuiLib\Module\Media\Image;

use HuiLib\Error\MediaException;

/**
 * iYunLin图像处理模块
 * 
 * 缩放、裁剪、水印
 * 
 * @author HanHui
 * @since 2014/04/26
 */
class ImageBase extends \HuiLib\Module\ModuleBase
{
    /**
	 * Imagick对象
	 * @var \Imagick
	 */
    protected $image;
    
    // 图像初始高度、宽度信息
    protected $originalSize = array ();
    
    // 原图像保存文件大小
    protected $originalFileSize = array ();
    
    //最宽
    protected $maxWidth = 800;
    // 最高
    protected $maxHeight = 6000;
    
    // 压缩比例
    protected $compressionquality = 80;
    
    //水印资源
    protected $waterImage = NULL;
    
    //大于这个尺寸的图片才水印
    protected $waterStart=500;

    /**
	 * 初始化imagick对象
	 *
	 * @param $filepath string 文件地址
	 * @param $isBinary boolean 是否是二进制内容或文件地址
	 */
    public function __construct($filepath, $isBinary = FALSE)
    {
        parent::__construct ();
        
        $this->image = new \Imagick ();
        
        try {
            // imagick::readImage 在win下5.3版本居然不能直接用。改为readImageBlob可用。
            $this->image->readImageBlob ( $isBinary ? $filepath : file_get_contents ( $filepath ) );
            $this->originalFileSize = $this->image->getimagesize ();
        } catch ( \ImagickException $e ) {
            throw new MediaException ( $e->getMessage () );
        }
    }

    /**
	 * 图像缩放操作
	 *
	 * @param int $width 目标缩放宽度
	 * @param int $height 目标缩放高度
	 * @param $savepath string 文件存放路径，未提供则修改当前
	 */
    function thumb($width, $height, $savepath = NULL)
    {
        $format = $this->getImageFormat ();
        
        // 小动图加快上传
        if (! empty ( $this->originalSize ) && $this->originalSize ['width'] <= $width && $this->originalSize ['height'] <= $height) {
            // 不需要压缩、解析等操作，直接保存。imagick对象已经检测图像有效性。
            $tmpImage = clone $this->image;
        } else {
            if ($format == 'GIF') {
                $tmpImage = new \Imagick ();
                $transparent = new \ImagickPixel ( "rgba(0, 0, 0, 0)" ); // 透明色
                foreach ( $this->image as $frame ) {
                    $page = $frame->getImagePage ();
                    $tmp = new \Imagick ();
                    $tmp->newImage ( $page ['width'], $page ['height'], $transparent, $format );
                    $tmp->compositeImage ( $frame, \Imagick::COMPOSITE_DEFAULT, $page ['x'], $page ['y'] );
                    // 规格有调整的时候才缩放
                    if (empty ( $this->originalSize ) || $this->originalSize ['width'] > $width && $this->originalSize ['height'] > $height) {
                        $tmp->thumbnailImage ( $width, $height );
                    }
                    
                    $tmpImage->addImage ( $tmp );
                    $tmpImage->setImagePage ( $tmp->getImageWidth (), $tmp->getImageHeight (), 0, 0 );
                    $tmpImage->setImageDelay ( $frame->getImageDelay () );
                    $tmpImage->setImageDispose ( $frame->getImageDispose () );
                }
            } else {
                $tmpImage = new \Imagick ();
                $tmpImage->addimage ( $this->image );
                
                $tmpImage->thumbnailImage ( $width, $height );
            }
        }
        
        if ($savepath == NULL) {
            //缩放当前
            $this->image = $tmpImage;
        } else {
            //写入储存
            $tmpImage->coalesceImages ();
            $tmpImage->setImageFormat ( $format );
            $tmpImage->setImageCompressionQuality ( $this->compressionquality );
            $tmpImage->writeimages ( $savepath, 1 );
        }
        
        return $this;
    }

    /**
	 * 截取图片
	 * 
	 * @param int $x 开始坐标x
	 * @param int $y 坐标y
	 * @param int $width
	 * @param int $height
	 * @param string $savepath 文件存放路径，未提供则修改当前
	 */
    public function crop($x, $y, $width, $height, $savepath = NULL)
    {
        $format = $this->getImageFormat ();
        
        if ($format == 'GIF') {
            $transparent = new \ImagickPixel ( "rgba(0, 0, 0, 0)" ); // 透明色
            $tmpImage = new \Imagick ();
            foreach ( $this->image as $frame ) {
                $page = $frame->getImagePage ();
                $tmp = new \Imagick ();
                $tmp->newImage ( $page ['width'], $page ['height'], $transparent, $format );
                $tmp->compositeImage ( $frame, \Imagick::COMPOSITE_DEFAULT, $page ['x'], $page ['y'] );
                $tmp->cropimage ( $width, $height, $x, $y );
                
                $tmpImage->addImage ( $tmp );
                $tmpImage->setImagePage ( $tmp->getImageWidth (), $tmp->getImageHeight (), 0, 0 );
                $tmpImage->setImageDelay ( $frame->getImageDelay () );
                $tmpImage->setImageDispose ( $frame->getImageDispose () );
            }
        } else {
            $tmpImage = new \Imagick ();
            $tmpImage->addimage ( $this->image );
            
            $tmpImage->cropimage ( $width, $height, $x, $y );
        }
        
        if ($savepath == NULL) {
            //缩放当前
            $this->image = $tmpImage;
        } else {
            //写入储存
            $tmpImage->coalesceImages ();
            $tmpImage->setImageFormat ( $format );
            $tmpImage->setImageCompressionQuality ( $this->compressionquality );
            $tmpImage->writeimages ( $savepath, 1 );
        }
        
        return $this;
    }

    /**
	 * 截取图片
	 *
	 * @param int $x 开始坐标x
	 * @param int $y 坐标y
	 * @param int $width
	 * @param int $height
	 * @param string $savepath 文件存放路径，未提供则修改当前
	 */
    public function watchmark($x, $y, $savepath = NULL)
    {
        if (! $this->waterImage instanceof \Imagick) {
            throw new MediaException ( 'Watermark source is invalid.' );
        }
        
        if ($format == 'GIF') {
            $format = $this->getImageFormat ();
            $transparent = new \ImagickPixel ( "rgba(0, 0, 0, 0)" ); // 透明色
            $tmpImage = new \Imagick ();
            foreach ( $this->image as $frame ) {
                $page = $frame->getImagePage ();
                $tmp = new \Imagick ();
                $tmp->newImage ( $page ['width'], $page ['height'], $transparent, $format );
                $tmp->compositeImage ( $frame, \Imagick::COMPOSITE_DEFAULT, $page ['x'], $page ['y'] );
                if (min($this->getOriginalSize())>$this->waterStart) {
                    $tmp->compositeImage ( $this->waterImage, \Imagick::COMPOSITE_DEFAULT, $x, $y );
                }
                
                $tmpImage->addImage ( $tmp );
                $tmpImage->setImagePage ( $tmp->getImageWidth (), $tmp->getImageHeight (), 0, 0 );
                $tmpImage->setImageDelay ( $frame->getImageDelay () );
                $tmpImage->setImageDispose ( $frame->getImageDispose () );
            }
        } else {
            $tmpImage = new \Imagick ();
            $tmpImage->addimage ( $this->image );
            
            if (min($this->getOriginalSize())>$this->waterStart) {
                $tmpImage->compositeImage ( $this->waterImage, \Imagick::COMPOSITE_DEFAULT, $x, $y );
            }
        }
        
        if ($savepath == NULL) {
            //缩放当前
            $this->image = $tmpImage;
        } else {
            //写入储存
            $tmpImage->coalesceImages ();
            $tmpImage->setImageFormat ( $format );
            $tmpImage->setImageCompressionQuality ( $this->compressionquality );
            $tmpImage->writeimages ( $savepath, 1 );
        }
        
        return $this;
    }

    public function getImageFormat()
    {
        $format = $this->image->getImageFormat ();
        return $format;
    }

    /**
	 * 获取原始 宽度 高度
	 * 
	 * @return array
	 */
    public function getOriginalSize()
    {
        if ($this->getImageFormat()=='GIF') {
            $widths=$heights=array();
            foreach ($this->image as $frame ){
                $tmp=$frame->getimagegeometry ();
                array_push($widths, $tmp ['width']);
                array_push($heights, $tmp ['height']);
            }
            $result=array();
            $result ['width']=max($widths);
            $result ['height']=max($heights);
        }else{
            $result = $this->image->getimagegeometry ();
        }
        if (empty ( $result ['width'] ) || empty ( $result ['height'] )) {
            throw new MediaException ( 'Failed to getOriginalSize()' );
        }
        
        return $result;
    }
    
    /**
     * 获取水印图片的大小信息
     */
    public function getWaterSize()
    {
        if (! $this->waterImage instanceof \Imagick) {
            throw new MediaException ( 'Watermark source is invalid.' );
        }
        return $this->waterImage->getimagegeometry ();
    }

    public function setMaxWidth($width)
    {
        $this->maxWidth = $width;
        return $this;
    }

    public function setMaxHeight($height)
    {
        $this->maxHeight = $height;
        return $this;
    }

    /**
	 * 设置压缩比例
	 * 
	 * @param int $quality 压缩比例 0-100
	 * @return \HuiLib\Module\Media\Image\ImageBase
	 */
    public function setCompressQuality($quality)
    {
        if ($quality > 100 || $quality < 0) {
            throw new MediaException ( 'Invalid compressionquality param.' );
        }
        $this->compressionquality = $quality;
        return $this;
    }

    /**
	 * 设置水印图片
	 * 
	 * 仅允许静态图，不允许动图
	 *
	 * @param string $filepath
	 */
    public function setWaterImage($filepath, $isBinary = FALSE)
    {
        $this->waterImage = new \Imagick ();
        
        try {
            // imagick::readImage 在win下5.3版本居然不能直接用。改为readImageBlob可用。
            $this->waterImage->readImageBlob ( $isBinary ? $filepath : file_get_contents ( $filepath ) );
            if ($this->waterImage->getnumberimages ()>1) {
                throw new MediaException('Water image of dynamic is not allowed.');
            }
        } catch ( \ImagickException $e ) {
            throw new MediaException ( $e->getMessage () );
        }
    }
}