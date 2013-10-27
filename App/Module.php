<?php
namespace HuiLib\App;

/**
 * Module基础类
 * 
 * @author 祝景法
 * @since 2013/09/20
 */
class Module
{
	/**
	 * 基础APP实例
	 * @var \HuiLib\App\AppBase
	 */
	protected $appInstance;
	
	/**
	 * 应用主域名
	 * @var \HuiLib\Lang\LangBase
	 */
	protected $langInstace = NULL;
	
	/**
	 * 网站全局配置对象
	 * @var \HuiLib\Config\ConfigBase
	 */
	protected $siteConfig=NULL;

	protected function __construct(\HuiLib\App\AppBase $appInstance=NULL)
	{
		if ($appInstance===NULL) {
			$appInstance=\HuiLib\Bootstrap::getInstance()->appInstance();
		}
		$this->appInstance=$appInstance;
	}
	
	/**
	 * 获取翻译实例
	 */
	protected function getLang()
	{
		if ($this->langInstace===NULL) {
			$this->langInstace=\HuiLib\Lang\LangBase::getDefault();
		}
	
		return $this->langInstace;
	}
	
	/**
	 * 初始化网站配置实例
	 */
	protected function getSiteConfig()
	{
		if ($this->siteConfig===NULL) {
			$this->siteConfig = $this->appInstance->siteConfigInstance();
		}
		return $this->siteConfig;
	}
	
	/**
	 * 快速创建一个模块实例
	 */
	static function create(){
		return new static();
	}
	
}
