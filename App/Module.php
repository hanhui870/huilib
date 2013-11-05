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
	 * 快速创建一个Module实例
	 * 
	 * 原理：创建对象的灵活获取参数是从第0个开始的。一般函数是从第一个开始的。
	 * 
	 * @param mix $param 支持传递参数，最多5个，其他空字符串代替
	 */
	static function create(){
		
		$paramCount=func_num_args();
		
		if (!$paramCount) {
			return new static();
		}else{
			$params=func_get_args();
			//最多5个，其他空字符串代替
			list($param1, $param2, $param3, $param4, $param5)=array_pad($params, 5, '');
			
			return new static($param1, $param2, $param3, $param4, $param5);
		}
	}
	
}
