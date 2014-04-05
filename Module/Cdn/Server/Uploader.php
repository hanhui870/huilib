<?php
namespace HuiLib\Module\Cdn\Server;

use HuiLib\Helper\Param;
use HuiLib\App\Front;

/**
 * HuiLib CDN Uploader库
 * 
 * @author 祝景法
 * @since 2014/04/05
 */
class Uploader extends Base
{
    public function uploadImages()
    {
        if (Param::post(['type'])!='file') {
            $this->renderJson(self::STATUS_FAIL, Front::getInstance()->getHuiLang()->_('cdn.upload.type.error'));
        }
        
        if (empty($_FILES)) {
            return $this->format(self::API_FAIL, Front::getInstance()->getHuiLang()->_('cdn.upload.files.empty'));
        }
        
        
    }
    
    public function uploadFiles()
    {
    
    }
    
}