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
class ImageBase extends \HuiLib\Module\ModuleBase {
	/**
	 * Imagick对象
	 * @var \Imagick
	 */
	protected $image;
	
	// 图像初始高度、宽度信息
	protected $originalSize = array ();
	
	protected $maxWidth = 760;
	// 最高
	protected $maxHeight = 8000;
	
	// 压缩比例
	protected $compressionquality = 80;

	/**
	 * 初始化imagick对象
	 *
	 * @param $filepath string 文件地址
	 * @param $isBinary boolean 是否是二进制内容或文件地址
	 */
	public function __construct($filepath, $isBinary) {
		parent::__construct ();
		
		$this->image = new \Imagick ();
		
		try {
		    // imagick::readImage 在win下5.3版本居然不能直接用。改为readImageBlob可用。
		    $this->image->readImageBlob ( $isBinary ? $filepath : file_get_contents ( $filepath ) );
		} catch ( \ImagickException $e ) {
		    throw new MediaException($e->getMessage());
		}
	}

	public function getImageFormat() {
		return  strtolower ( $this->image->getImageFormat () );
	}
	
	public function getOriginalSize() {
	    return  $this->image->getimagegeometry ();
	}
}