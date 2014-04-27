<?php
namespace HuiLib\Module\Cdn\Server;

use HuiLib\Helper\Param;
use HuiLib\App\Front;
use HuiLib\Error\Exception;
use HuiLib\Module\Cdn\Utility;
use HuiLib\Module\Media\Image\Thumb;

/**
 * HuiLib CDN Uploader库
 * 
 * 单次上传只能一种类型
 * 
 * @author 祝景法
 * @since 2014/04/05
 */
class Uploader extends Base
{
    /**
     * 图片上传允许的mime类型
     * @var array
     */
    private $allowImageMime=array ('image/jpeg', 'image/pjpeg', 'image/gif', 'image/png', 'image/x-png', 'image/bmp' );
    
    public function uploadImages()
    {
        if (Param::post('type', Param::TYPE_STRING)!='image') {
            return $this->format(self::API_FAIL, Front::getInstance()->getHuiLang()->_('cdn.upload.type.error'));
        }
        
        $result=$this->preCheck();
        if (!$result['success']) {
            return $result;
        }
        
        foreach ($_FILES as $key=>$file){
            $meta=Param::post($key, Param::TYPE_ARRAY);
            if (!in_array($meta['type'], $this->allowImageMime)) {
                return $this->format(self::API_FAIL, Front::getInstance()->getHuiLang()->_('cdn.upload.image.mime.error'));
            }
        }

        try{
            $huiLang=Front::getInstance()->getHuiLang();
        
            //上传处理
            $result=array();
            //水印图片
            $water=Front::getInstance()->getAppConfig()->getByKey('cdn.water_mark');
            if (!file_exists($water)) {
                return $this->format(self::API_FAIL, Front::getInstance()->getHuiLang()->_('cdn.upload.water.file.miss'));
            }
            //水印位置
            $waterPosition=Front::getInstance()->getAppConfig()->getByKey('cdn.water_position');
            foreach ($_FILES as $key=>$file){
                $meta=Param::post($key, Param::TYPE_ARRAY);
        
                $path=$this->getNewFilePath($meta);
                
                //上传并打水印
                Thumb::create($file['tmp_name'])->setWaterImage($water)->thumbNormalUpload($path['file'], $waterPosition);
        
                $result[$key]['url']=$path['url'];
            }
        
            return $this->format(self::API_SUCCESS, $huiLang->_('cdn.upload.suceess'), array(), $result);
        }catch (Exception $e){
            return $this->format(self::API_FAIL, $e->getMessage());
        }
    }
    
    public function uploadFiles()
    {
        if (Param::post('type', Param::TYPE_STRING)!='file') {
            return $this->format(self::API_FAIL, Front::getInstance()->getHuiLang()->_('cdn.upload.type.error'));
        }
        
        $result=$this->preCheck();
        if (!$result['succss']) {
            return $result;
        }
        
        try{
            $huiLang=Front::getInstance()->getHuiLang();
        
            //上传处理
            $result=array();
            foreach ($_FILES as $key=>$file){
                $meta=Param::post($key, Param::TYPE_ARRAY);
        
                $path=$this->getNewFilePath($meta);
                move_uploaded_file($file['tmp_name'], $path['file']);
        
                $result[$key]['url']=$path['url'];
            }
        
            return $this->format(self::API_SUCCESS, $huiLang->_('cdn.upload.suceess'), array(), $result);
        }catch (Exception $e){
            return $this->format(self::API_FAIL, $e->getMessage());
        }
    }
    
    /**
     * 上传头像
     */
    public function uploadAvatar()
    {
        if (Param::post('type', Param::TYPE_STRING)!='image') {
            return $this->format(self::API_FAIL, Front::getInstance()->getHuiLang()->_('cdn.upload.type.error'));
        }
    
        $result=$this->preCheck();
        if (!$result['success']) {
            return $result;
        }
    
        foreach ($_FILES as $key=>$file){
            $meta=Param::post($key, Param::TYPE_ARRAY);
            if (!in_array($meta['type'], $this->allowImageMime)) {
                return $this->format(self::API_FAIL, Front::getInstance()->getHuiLang()->_('cdn.upload.image.mime.error'));
            }
        }
    
        try{
            $huiLang=Front::getInstance()->getHuiLang();
        
            //上传处理
            $result=array();
            foreach ($_FILES as $key=>$file){
                $meta=Param::post($key, Param::TYPE_ARRAY);
        
                $path=$this->getNewFilePath($meta);
                move_uploaded_file($file['tmp_name'], $path['file']);
        
                $result[$key]['url']=$path['url'];
            }
        
            return $this->format(self::API_SUCCESS, $huiLang->_('cdn.upload.suceess'), array(), $result);
        }catch (Exception $e){
            return $this->format(self::API_FAIL, $e->getMessage());
        }
    }
    
    protected function preCheck()
    {
        try{
            $huiLang=Front::getInstance()->getHuiLang();
        
            $appId=Param::post('app_id', Param::TYPE_STRING);
            if (!$appId) {
                return $this->format(self::API_FAIL, $huiLang->_('cdn.upload.app_id.null'));
            }
        
            if (empty($_FILES)) {
                return $this->format(self::API_FAIL, $huiLang->_('cdn.upload.files.empty'));
            }
        
            $secret=$this->getAppSecret($appId);
        
            $post=$this->remapPostArray($_POST);
        
            //字符安全解密
            if (!Utility::decrypt($post, $secret)) {
                return $this->format(self::API_FAIL, $huiLang->_('cdn.upload.decode.failed'));
            }
            
            $config=$this->getConfig();
            //上传文件校验处理 一个有错，全部终止
            foreach ($_FILES as $key=>$file){
                $meta=Param::post($key, Param::TYPE_ARRAY);
                if (empty($meta['size']) || empty($meta['type']) || empty($meta['name']) || empty($meta['sha1']) ) {
                    return $this->format(self::API_FAIL, $huiLang->_('cdn.upload.files.error'));
                }
                if ($file['error'] || $file['size']!=$meta['size'] || sha1_file($file['tmp_name'])!=$meta['sha1']) {
                    return $this->format(self::API_FAIL, $huiLang->_('cdn.upload.files.finger.print.error'));
                }
                if (!empty($config['max_filesize']) && $file['size']>$config['max_filesize']*1024*1024) {
                    return $this->format(self::API_FAIL, $huiLang->_('cdn.upload.files.maxsize.error', $config['max_filesize']));
                }
            }
            
            return $this->format(self::API_SUCCESS, 'ok');
        }catch (Exception $e){
            return $this->format(self::API_FAIL, $e->getMessage());
        }
    }
    
    /**
     * 
     * @param array $post
     */
    protected function remapPostArray($post)
    {
        $result=array();
        foreach ($post as $key=>$value){
            if (!is_array($value)) {
                $result[$key]=$value;
            }else{
                foreach ($value as $innerKey=>$innerValue){
                    $result[$key."[{$innerKey}]"]=$innerValue;
                }
            }
        }
        
        return $result;
    }
    
}