<?php
namespace HuiLib\Module\Cdn\Server;

/**
 * HuiLib CDN Restful库
 * 
 * @author 祝景法
 * @since 2014/04/05
 */
class Restful extends  \HuiLib\Module\ModuleBase
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
    
    public function remove()
    {
    
    }
    
}