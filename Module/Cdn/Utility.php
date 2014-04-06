<?php
namespace HuiLib\Module\Cdn;

/**
 * HuiLib CDN Uploader库
 * 
 * @author 祝景法
 * @since 2014/04/05
 */
class Utility
{
    /**
     * 完整性加密
     *
     * @param array $data
     * @param string $secret
     */
    public static function encrypt($post, $secret)
    {
        if (!is_array($post) || empty($secret)) {
            throw new Exception($this->getHuiLang()->_('cdn.encrypt.data.error'));
        }
    
        ksort($post, SORT_STRING);
        $post['app_secret']=$secret;
    
        return md5(http_build_query($post));
    }
    
    /**
     * 完整性加密
     *
     * @param array $data
     * @param string $secret
     */
    public static function decrypt($post, $secret)
    {
        if (!is_array($post) || empty($post['hash']) || empty($secret)) {
            throw new Exception($this->getHuiLang()->_('cdn.encrypt.data.error'));
        }
    
        $hash=$post['hash'];
        unset($post['hash']);
        
        ksort($post, SORT_STRING);
        $post['app_secret']=$secret;
    
        return $hash==md5(http_build_query($post));
    }
}