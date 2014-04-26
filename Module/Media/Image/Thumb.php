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
     * 生成略缩图
     * 根据设定的最小边，截图。
     * $dest 储存目录为空时，则缩放原图
     * $src 操作原图
     * $param[append_format] 处理中添加后缀形式
     */
    function thumb($param, &$src, $dest = '') {
        $result = false;
        if (empty ( $param ['action'] ))
            return $result;
    
        if (! $this->constructImagick ( $src )) { // 失败则直接返回
            return $result;
        }
    
        // 储存路径处理
        $format = $this->getImageFormat ();
    
        if ($format != '.gif') {
            $type = 'image/jpeg';
        } else {
            $type = 'image/gif';
        }
    
        /*
         * 是否传输目标保存地址 $dest为空，对源文件进行操作。 传输目标储存地址，这全新命名文件
        */
        if (empty ( $dest )) {
            $filePath = $src;
        } else {
            $filename = md5 ( $this->v ['start_time'] . $this->v ['ip'] . $this->v ['useragent'] );
            $filePath = $dest . $filename . $format;
        }
    
        $size = $this->originalSize = $this->image->getimagegeometry ();
    
        $extra = array ();
        $extra ['width'] = $size ['width'];
        $extra ['height'] = $size ['height'];
        $extra ['filePath'] = $filePath;
        $extra ['format'] = substr ( $format, 1 );
    
        switch ($param ['action']) {
            // 限制最小宽度 例如流线图用短边220px 长无限; 220_
            case 'minWidth' :
                $extra ['min'] = $param ['min'];
                $result = $this->thumbByMinWidth ( $extra );
                break;
                	
                // 按照最小边略缩 另一边可无限大
            case 'min' :
                $extra ['min'] = $param ['min'];
                $result = $this->thumbByMin ( $extra );
                break;
                	
                // 按照最大边略缩 另一边可无限小
            case 'max' :
                $extra ['max'] = $param ['max'];
                $result = $this->thumbByMax ( $extra );
                break;
                	
                /*
                 * 普通上传略缩 生成三份数据 最大 宽度最大760px 最高看顶部 缩放 消息用长边150px 短无限， 流线图用短边220px
                * 长无限; 略缩图50px; 原始图不保存 生成顺序有规律，从大到小，不然需要不断读入原图，影响效率。
                */
            case 'normal' :
                if (empty ( $dest ))
                    return $result; // 处理直接压缩
    
                // 生成 宽度最大760px 最高看顶部 原图不加参数
                $r1 = $this->thumbNormalUpload ( $extra );
    
                // 生成 流线图用短边220px 长无限; 220_
                $extra ['min'] = 220;
                $extra ['filePath'] = $dest . '220_' . $filename . $format;
                $r3 = $this->thumbByMinWidth ( $extra );
    
                // 生成 长边150px 短无限；150_
                $extra ['max'] = 150;
                $extra ['filePath'] = $dest . '150_' . $filename . $format;
                $r2 = $this->thumbByMax ( $extra );
    
                // 生成 50px略缩图; 50_ 长宽是相等的
                $extra ['crop'] = 50;
                $extra ['filePath'] = $dest . '50_' . $filename . $format;
                $r4 = $this->thumbByCrop ( $extra );
    
                if ($r1 && $r2 && $r3 && $r4) {
                    $result = true;
                }
                break;
        }
    
        // 失败
        if (! $result)
            return false;
    
        $r = array ();
        $r ['filetype'] = $type;
        $r ['width'] = $size ['width'];
        $r ['height'] = $size ['height'];
        $r ['filesize'] = filesize ( $filePath );
        $r ['filepath'] = empty ( $dest ) ? $src : str_ireplace ( www_root . $this->v ['config'] ['attach'], '', $dest );
        if (! empty ( $filename )) {
            $r ['filename'] = $filename;
        }
        $r ['format'] = $format;
    
        return $r;
    }
    
    /**
     * 截取中间部分 缩放
     *
     * @param $param Array
     * @tip 比要求规格小的也同样进行裁剪，会自动放大。
     */
    function thumbByCrop($param) {
        if (empty ( $param ['crop'] ) || empty ( $param ['width'] ) || empty ( $param ['height'] ) || empty ( $param ['filePath'] ))
            return false;
    
        $crop = $param ['crop'];
        $w = $param ['width'];
        $h = $param ['height'];
        $ratio = $w / $h;
    
        $extra = array ();
        $extra ['width'] = $crop;
        $extra ['height'] = $crop;
        $extra ['file'] = $param ['filePath'];
        $extra ['format'] = $param ['format'];
    
        if ($ratio > 1) {
            $h = $crop;
            $w = ceil ( $crop * $ratio );
            	
            $this->thumbAction ( $w, $h, $param ['filePath'], $param ['format'] );
            	
            // 截图 中间部分
            $extra ['x'] = ($w - $h) / 2;
            $extra ['y'] = 0;
            $this->crop_image ( $extra );
        } else {
            $w = $crop;
            $h = ceil ( $crop / $ratio );
            	
            $this->thumbAction ( $w, $h, $param ['filePath'], $param ['format'] );
            	
            // 截图 中间部分
            $extra ['x'] = 0;
            $extra ['y'] = ($h - $w) / 2;
            $this->crop_image ( $extra );
        }
    
        return true;
    }
    
    /**
     * 按最小边限定 最大边无限
     *
     * @param $param Array
     */
    function thumbByMin($param) {
        if (empty ( $param ['min'] ) || empty ( $param ['width'] ) || empty ( $param ['height'] ) || empty ( $param ['filePath'] ))
            return false;
        $min = $param ['min'];
        $w = $param ['width'];
        $h = $param ['height'];
        $ratio = $w / $h;
    
        if (min ( $w, $h ) > $min) {
            if ($ratio > 1) {
                $h = $min;
                $w = ceil ( $min * $ratio );
            } else {
                $w = $min;
                $h = ceil ( $min / $ratio );
            }
        }
    
        return $this->thumbAction ( $w, $h, $param ['filePath'], $param ['format'] );
    }
    
    /**
     * 按最大边限定 最小边无限
     *
     * @param $param Array
     */
    function thumbByMax($param) {
        if (empty ( $param ['max'] ) || empty ( $param ['width'] ) || empty ( $param ['height'] ) || empty ( $param ['filePath'] ))
            return false;
    
        $max = $param ['max'];
        $w = $param ['width'];
        $h = $param ['height'];
        $ratio = $w / $h;
    
        if (max ( $w, $h ) > $max) {
            if ($ratio > 1) {
                $w = $max;
                $h = ceil ( $max / $ratio );
            } else {
                $w = ceil ( $max * $ratio );
                $h = $max;
            }
        }
    
        return $this->thumbAction ( $w, $h, $param ['filePath'], $param ['format'] );
    }
    
    /**
     * 按固定宽 高度不限
     *
     * @param $param Array
     */
    function thumbByMinWidth($param) {
        if (empty ( $param ['min'] ) || empty ( $param ['width'] ) || empty ( $param ['height'] ) || empty ( $param ['filePath'] ))
            return false;
        $min = $param ['min'];
        $w = $param ['width'];
        $h = $param ['height'];
        $ratio = $w / $h;
    
        if ($w > $min) {
            $w = $min;
            $h = ceil ( $min / $ratio );
        }
    
        return $this->thumbAction ( $w, $h, $param ['filePath'], $param ['format'] );
    }
    
    /**
     * 大图保存
     * 宽度最大760px 高度最高 看顶部(针对消息长图)
     * 数码相机 数拍的最大高度 760px
     */
    function thumbNormalUpload($param) {
        if (empty ( $param ['width'] ) || empty ( $param ['height'] ) || empty ( $param ['filePath'] ))
            return false;
        $mw = $this->maxWidth;
        $mh = $this->maxHeight;
        $w = $param ['width'];
        $h = $param ['height'];
    
        $ratio = $w / $h;
    
        if ($ratio > 1 && $w > $this->maxWidth) { // width过长 超过760压缩。
            $w = $mw;
            $h = ceil ( $mw / $ratio );
        } elseif ($ratio < 1 && $ratio > 0.5 && $h > $this->maxWidth) { // 数码大图片压缩专用
            // 超过760压缩。
            $h = $mw;
            $w = ceil ( $h * $ratio );
        } elseif ($h > $mh) { // height过高
            $h = $mh;
            $w = ceil ( $mh * $ratio );
        }
    
        return $this->thumbAction ( $w, $h, $param ['filePath'], $param ['format'], array ('type' => 'normal' ) );
    }
    
    /**
     * 图像缩放操作
     *
     * @param $w int 目标缩放宽度
     * @param $h int 目标缩放高度
     * @param $path string 文件存放路径
     * @param $format string 图像格式
     * @param $extra array 额外参数设置
     * Gif: extra['type']:'normal'表示正常模式，默认压缩正常类型规格的动图。
     * @return boolean 是否成功
     */
    function thumbAction($w, $h, $path, $format, $extra = array()) {
        if (empty ( $this->image ))
            return false;
    
        $nums = $this->image->getnumberimages ();
    
        if ($nums > 1 || $format == 'gif') {
            $transparent = new \ImagickPixel ( "rgba(0, 0, 0, 0)" ); // 透明色
            $gif = new \Imagick ();
            	
            // 小动图加快上传
            if (! empty ( $extra ['type'] ) && $extra ['type'] == 'normal' && ! empty ( $this->originalSize ) && $this->originalSize ['width'] <= $w && $this->originalSize ['height'] <= $h) {
                // 不需要压缩、解析等操作，直接保存。imagick对象已经检测图像有效性。
                $gif = $this->image;
            } else {
                foreach ( $this->image as $frame ) {
                    $page = $frame->getImagePage ();
                    $tmp = new \Imagick ();
                    $tmp->newImage ( $page ['width'], $page ['height'], $transparent, 'gif' );
                    $tmp->compositeImage ( $frame, \Imagick::COMPOSITE_DEFAULT, $page ['x'], $page ['y'] );
                    	
                    // 规格有调整的时候才缩放
                    if (empty ( $this->originalSize ) || $this->originalSize ['width'] > $w && $this->originalSize ['height'] > $h) {
                        $tmp->thumbnailImage ( $w, $h );
                    }
                    	
                    $gif->addImage ( $tmp );
                    $gif->setImagePage ( $tmp->getImageWidth (), $tmp->getImageHeight (), 0, 0 );
                    $gif->setImageDelay ( $frame->getImageDelay () );
                    $gif->setImageDispose ( $frame->getImageDispose () );
                    // 非标准格式缩放动图仅保存一幅图片
                    if (empty ( $extra ['type'] ) || $extra ['type'] != 'normal') {
                        break;
                    }
                }
            }
            	
            // 保存头像
            $gif->coalesceImages ();
            $gif->setImageFormat ( $format );
            $gif->setcompressionquality ( $this->compressionquality );
            $gif->writeimages ( $path, 1 );
    
        } else {
            $this->image->thumbnailimage ( $w, $h ); // 去除bestfit 不要求完全比例 大小优先
            $this->image->setcompressionquality ( $this->compressionquality );
            $this->image->setImageFormat ( $format );
            $this->image->writeimage ( $path );
        }
        return true;
    }
    
}