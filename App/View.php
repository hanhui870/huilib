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
	 * 
	 * 3种模板刷新更新机制：
	 * 1、配置webRun.view.refresh，(子)模板有修改会自动刷新，较耗资源适合开发环境
	 * 2、配置webRun.view.life，缓存操作生存期限后，自动删除重建
	 * 3、统一通过管理后台，刷新模板缓存
	 */
	public function render($view, $ajaxDelimiter = NULL)
	{
		$this->initEngine($view, $ajaxDelimiter);
		
		$cacheFile=$this->_engineInstance->getCacheFilePath();

		if (!file_exists($cacheFile)) {//缓存文件不存在
			$this->_engineInstance->parse()->writeCompiled();
			
		}elseif ($this->_appInstance->configInstance()->getByKey('webRun.view.refresh')){//开启模板自动扫描刷新
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
			
		}elseif ($this->_appInstance->configInstance()->getByKey('webRun.view.life')){//配置了自动过期时间
			$lifeTime=$this->_appInstance->configInstance()->getByKey('webRun.view.life');
			if ( time() - filemtime ( $cacheFile ) >= $lifeTime) {
				unlink ( $cacheFile );
				$this->_engineInstance->parse()->writeCompiled();
			}
		}
		
		include $this->_engineInstance->getCacheFilePath();
	}
}
