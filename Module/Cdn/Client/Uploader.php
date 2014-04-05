<?php
namespace HuiLib\Module\Cdn\Client;

/**
 * HuiLib CDN Uploader库
 * 
 * @author 祝景法
 * @since 2014/04/05
 */
class Uploader extends Base
{
    /**
     * 上传一个文件
     *
     * @param array $file
     */
    public function upload($file)
    {
        //if (!file_exists($file)) {
        // return $this->format(parent::API_FAIL, 'File not exsits.');
        //}
    
        return Uploader::create()->transfer($file);
    }
    
    /**
     * 上传前端传过来的文件
     * 
     * 内容取自$_FILES
     *
     * @param array $file
     */
    public function uploadFiles($files)
    {
        if (!is_array($files)) {
            return $this->format(self::API_FAIL, $this->getHuiLang()->_('post.files.empty'));
        }
        
        $post=array();
        $post['type']='image';
        foreach ($files as $field=>$file){
            if (!empty($file['error'])) {
                return $this->format(self::API_FAIL, $this->getHuiLang()->_('post.files.error'));
            }
            $post[$field]='@'.$file['tmp_name'];
            $post[$field.'[name]']=$file['name'];
            $post[$field.'[type]']=$file['type'];
            $post[$field.'[error]']=$file['error'];
            $post[$field.'[size]']=$file['size'];
        }

        return $this->curl($post);
    }
    
    /**
     * data['type'] 上传的文件类型：image, file , static
     * data['file1']='@/var/test.jpg'
     * data['file1[name]']='hello world'
     * data['file1[type]']='image/jpeg'
     * data['file1[error]']=0
     * data['file1[size]']=13245
     * 
     * @param array $data
     */
    protected function curl($data)
    {
        
        $config=parent::getConfig();
        
        $handle=curl_init();
        
        curl_setopt($handle, CURLOPT_URL, $config['upload'].$data['type']);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        
        $result=curl_exec($handle);
        
        print_r($result);
        
        
    }
}