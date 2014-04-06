<?php
namespace HuiLib\Module\Cdn\Server;

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
    protected $appList=NULL;
    
    /**
     * 获取cdn配置
     */
    protected function getConfig()
    {
        $config=Front::getInstance()->getAppConfig()->getByKey('cdn');
        if (!isset($config['save_path']) || !isset($config['hash_length']) || !isset($config['directory_depth']) || !isset($config['app_list'])) {
            throw new Exception('App.ini cdn config section is required.');
        }
        
        return $config;
    }
    
    public function getAppList()
    {
        if ($this->appList===NULL) {
            $config=$this->getConfig();
            $this->appList = new \HuiLib\Config\ConfigBase ( $config['app_list'] );
        }
    
        return $this->appList;
    }
    
    /**
     * 获取AppId的密钥
     */
    protected function getAppSecret($appId)
    {
        if (empty($appId)) {
            throw new Exception('AppId can not be null.');
        }

        $app=$this->getAppList()->getByKey('app.'. $appId);

        if (empty($app['secret'])) {
            throw new Exception('AppList.ini set secret is required.');
        }
        
        return $app['secret'];
    }
    
    /**
     * 生成一个新文件的储存路径
     */
    protected function getNewFilePath($meta)
    {
        if (empty($meta['size']) || empty($meta['type']) || empty($meta['name'])) {
            throw new Exception(Front::getInstance()->getHuiLang()->_('cdn.upload.files.error'));
        }
        
        $config=$this->getConfig();
        
        return $config['save_path'].\HuiLib\Helper\Utility::geneRandomHash(32).'.jpg';
    }
}
