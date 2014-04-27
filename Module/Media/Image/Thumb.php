<?php
namespace HuiLib\Module\Media\Image;

/**
 * iYunLin图像缩放处理模块
 * 
 * @author HanHui
 * @since 2014/04/26
 */
class Thumb extends ImageBase
{
    /**
     * 截取中间部分缩放
     *
     * 比要求规格小的也同样进行裁剪，会自动放大。取两个参数中合适的一个值
     * 
     * @param int $width 目标宽度 
	 * @param int $height 目标高度
	 * @param string $savepath 文件存放路径，未提供则修改当前
     */
    function thumbByCrop($width, $height, $savepath=NULL) {
        $ratio=$width/$height;
        $original=$this->getOriginalSize();
        $oriRatio=$original['width']/$original['height'];
    
        $x=$y=0;
        if ($ratio > $oriRatio) {//宽度大于长度
            $idealWidth = $width;
            $idealHeight = ceil ( $width / $oriRatio );
            
            if ($height<$idealHeight) {
                $y=($idealHeight-$height)/2;
            }
        } else {//宽度小于长度
            $idealHeight=$height;
            $idealWidth = ceil ( $height * $oriRatio );
            
            if ($width<$idealWidth) {
                $x=($idealWidth-$width)/2;
            }
        }

        $this->thumb( $idealWidth, $idealHeight);
        $this->crop($x, $y, $width, $height, $savepath);
    
        return true;
    }
    
    /**
     * 按最小边缩放 最大边无限
     *
     * @param int $min 最小
     * @param string $savepath 储存路径，未提供则修改当前
     */
    function thumbByMin($min, $savepath=NULL) {
        $original=$this->getOriginalSize();
        $ratio=$original['width']/$original['height'];
    
        if (min ( $original ) > $min) {
            if ($ratio > 1) {
                $height = $min;
                $width = ceil ( $min * $ratio );
            } else {
                $width = $min;
                $height = ceil ( $min / $ratio );
            }
        }else{
            $width = $original['width'];
            $height = $original['height'];
        }
    
        return $this->thumb ( $width, $height, $savepath );
    }
    
    /**
     * 按最大边缩放 最小边无限
     *
     * @param int $max 最大
     * @param string $savepath 储存路径，未提供则修改当前
     */
    function thumbByMax($max, $savepath=NULL) {
        $original=$this->getOriginalSize();
        $ratio=$original['width']/$original['height'];
        
        if (max ( $original ) > $max) {
            if ($ratio > 1) {
                $width = $max;
                $height = ceil ( $max / $ratio );
            } else {
                $height = $max;
                $width = ceil ( $max * $ratio );
            }
        }else{
            $width = $original['width'];
            $height = $original['height'];
        }
        
        return $this->thumb ( $width, $height, $savepath );
    }
    
    /**
     * 按固定宽缩放，高度不限
     *
     * @param int $minWidth 最小宽度
     * @param string $savepath 储存路径，未提供则修改当前
     */
    function thumbByMinWidth($minWidth, $savepath=NULL) {
        $original=$this->getOriginalSize();
        $ratio=$original['width']/$original['height'];
        $width = $minWidth;
        $height = ceil ( $width / $ratio );
    
        return $this->thumb ( $width, $height, $savepath );
    }
    
    /**
     * 普通文件上传
     * 
     * @param $savepath string 文件存放路径，未提供则修改当前
     */
    function thumbNormalUpload($savepath=NULL) {
        $original=$this->getOriginalSize();
        $ratio=$original['width']/$original['height'];
    
        $width=$original['width'];
        $height=$original['height'];
        if ($ratio >= 1 && $original['width'] > $this->maxWidth) { // width过长 超过maxWidth压缩。
            $width = $this->maxWidth;
            $height = ceil ( $width / $ratio );
        } elseif ($ratio > 0.333 && $ratio < 1 && $original['height'] > $this->maxWidth) { // 数码大图片压缩专用
            $height = $this->maxWidth;
            $width = ceil ( $height * $ratio );
        } elseif ($ratio <= 0.333) { // height过高 微博长图片压缩
            //宽度处理
            if ($original['width'] > $this->maxWidth) {
                $width=$this->maxWidth;
                $height = ceil ( $width / $ratio );
            }
            //长度处理
            if ($original['height'] > $this->maxHeight) {
                $height = $this->maxHeight;
                $width = ceil ( $height * $ratio );
            }
        }

        $this->thumb ( $width, $height, $savepath);
        //取缩略图
        if (!empty($savepath) && file_exists($savepath)) {
            $this->thumbByCrop(100, 100, self::getThumbPath($savepath));
        }
        return $this;
    }
    
    /**
     * 获取略缩图路径
     * 
     * 因为不直接去处理路径，所以网络地址和本地地址通用
     */
    public static function getThumbPath($path)
    {
        $dotpos=strrpos($path, '.');
        $ext=substr($path, $dotpos);
        return substr($path, 0, $dotpos).'.thumb'.$ext;
    }
    
    /**
     * 左上水印
     *
     * @param string $savepath 文件存放路径
     */
    public function waterLeftTop($savepath=NULL)
    {
        return $this->watchmark(10, 10, $savepath);
    }
    
    /**
     * 左下水印
     *
     * @param string $savepath 文件存放路径
     */
    public function waterLeftBottom($savepath=NULL)
    {
        $size=$this->getOriginalSize();
        $water=$this->getWaterSize();
        
        return $this->watchmark(10, ($size['height']-$water['height']-10), $savepath);
    }
    
    /**
     * 右上水印
     *
     * @param string $savepath 文件存放路径
     */
    public function waterRightTop($savepath=NULL)
    {
        $size=$this->getOriginalSize();
        $water=$this->getWaterSize();
    
        return $this->watchmark(($size['width']-$water['width']-10), 10, $savepath);
    }
    
    /**
     * 右下水印
     *
     * @param string $savepath 文件存放路径
     */
    public function waterRightBottom($savepath=NULL)
    {
        $size=$this->getOriginalSize();
        $water=$this->getWaterSize();
    
        return $this->watchmark(($size['width']-$water['width']-10), ($size['height']-$water['height']-10), $savepath);
    }
    
    /**
     * 中间水印
     *
     * @param string $savepath 文件存放路径
     */
    public function waterCenter($savepath=NULL)
    {
        $size=$this->getOriginalSize();
        $water=$this->getWaterSize();
    
        return $this->watchmark(($size['width']-$water['width'])/2, ($size['height']-$water['height'])/2, $savepath);
    }
}