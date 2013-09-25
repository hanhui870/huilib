<?php
namespace HuiLib\App;

/**
 * View类
 * 
 * @author 祝景法
 * @since 2013/09/20
 */
class View extends \HuiLib\View\ViewBase
{
	public function __construct(\HuiLib\App\AppBase $appInstance)
	{
		$this->_appInstance=$appInstance;
	}
	
	/**
	 * 渲染输出
	 */
	public function render($view, $ajaxDelimiter = NULL)
	{
		$this->initEngine($view, $ajaxDelimiter);
		
		$cacheFile=$this->_engineInstance->getCacheFilePath();
		\HuiLib\Helper\Debug::mark('startRender');
		if (!file_exists($cacheFile)) {//缓存文件不存在
			$this->_engineInstance->parse()->writeCompiled();
			
		}elseif ($this->_appInstance->configInstance()->getByKey('template.refresh')){//开启模板自动扫描刷新
			$cacheStamp=filemtime($cacheFile);
			$tplStamp=filemtime($this->_engineInstance->getTplFilePath());
			$tplStampList=array($tplStamp);
			
			//主模板未更新，检测子模板
			if ($tplStamp <= $cacheStamp) {
				$subStampList=$this->_engineInstance->getSubTplStamp();
				$tplStampList=array_merge($tplStampList, $subStampList);
			}
			
			if (max ( $tplStampList ) > $cacheStamp) {
				unlink ( $cacheFile );
				$this->_engineInstance->parse()->writeCompiled();
			}
			
		}elseif ($this->_appInstance->configInstance()->getByKey('template.life')){//配置了自动过期时间
			$lifeTime=$this->_appInstance->configInstance()->getByKey('template.life');
			if ( time() - filemtime ( $cacheFile ) >= $lifeTime) {
				unlink ( $cacheFile );
				$this->_engineInstance->parse()->writeCompiled();
			}
		}
		\HuiLib\Helper\Debug::elapsed('startRender', 'endRender');
		
		include $this->_engineInstance->getCacheFilePath();
	}
}
