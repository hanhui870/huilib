<?php
namespace HuiLib\Module\Cdn\Client;

use HuiLib\Error\Exception;
use HuiLib\App\Front;

/**
 * HuiLib CDN基础类库
 * 
 * @author 祝景法
 * @since 2014/03/25
 */
class Base extends  \HuiLib\Module\ModuleBase
{
    /**
     * 获取cdn配置
     */
    protected function getConfig()
    {
        $config=Front::getInstance()->getAppConfig()->getByKey('cdn');
        if (!isset($config['app_id']) || !isset($config['app_secret']) || !isset($config['upload']) || !isset($config['manage'])) {
            throw new Exception('App.ini cdn config section is required.');
        }
        
        return $config;
    }
}
