<?php
namespace HuiLib\Module\Cdn\Client;

use HuiLib\Error\Exception;
use HuiLib\Module\Cdn\Utility;
/**
 * HuiLib CDN Uploader库
 * 
 * @author 祝景法
 * @since 2014/04/05
 */
class Uploader extends Base
{
    /**
     * 上传前端传过来的文件
     * 
     * 内容结构和$_FILES一致，多了些验证信息
     *
     * @param array $file
     */
    public function uploadFiles($type, $files)
    {
        if (!is_array($files) || empty($type)) {
            return $this->format(self::API_FAIL, $this->getHuiLang()->_('cdn.post.empty.type.need'));
        }
        
        $post=array();
        $post['type']=$type;
        $attches=array();
        foreach ($files as $field=>$file){
            if (!empty($file['error']) || $file['size']<=0) {
                return $this->format(self::API_FAIL, $this->getHuiLang()->_('cdn.post.files.error'));
            }
            $attches[$field]='@'.$file['tmp_name'];
            $post[$field.'[sha1]']=sha1_file($file['tmp_name']);
            unset($file['tmp_name']);
            
            //重建表单数组 主要是name, type, error, size字段，头像可能包含uid
            foreach ($file as $key=>$value){
                $post[$field.'['.$key.']']=$value;
            }
        }

        return $this->curl($post, $attches);
    }
    
    /**
     * 发起请求
     * 
     * data['type'] 上传的文件类型：image, file
     * data['file1']='@/var/test.jpg'
     * data['file1[name]']='hello world'
     * data['file1[type]']='image/jpeg'
     * data['file1[error]']=0
     * data['file1[size]']=13245
     * 
     * @param array $post 提交的数据
     * @param array $attches 上传的附件储存地址 不参与加密
     */
    protected function curl($post, $attches)
    {
        $config=parent::getConfig();
        $post['app_id']=$config['app_id'];
        try {
            $post['hash']=Utility::encrypt($post, $config['app_secret']);
        }catch (Exception $e){
            return $this->format(self::API_FAIL, $e->getMessage());
        }
        $post+=$attches;

        $handle=curl_init();
        
        //http://rpc.iyunlin.com/cdn/upload/type
        curl_setopt($handle, CURLOPT_URL, $config['upload'].$post['type']);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        
        $result=curl_exec($handle);

        $result= json_decode($result, TRUE);
        if (!Utility::decrypt($result, $config['app_secret'])) {
            return $this->format(self::API_FAIL, $this->getHuiLang()->_('cdn.responce.decode.failed'));
        }
        
        return $result;
    }
}