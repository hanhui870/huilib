<?php
namespace HuiLib\Lang\Translator;

use HuiLib\Config\ConfigBase;

/**
 * Ini语言翻译类
 * 
 * @author 祝景法
 * @since 2013/22/10
 */
class Ini extends \HuiLib\Lang\LangBase  {
	/**
	 * ini对象实例
	 * @var ConfigBaseArray
	 */
	protected $iniDriver = NULL;
	
	const FILE_EXT='.ini';
	
	/**
	 * 实际加载翻译文件的接口
	 *
	 * @see \HuiLib\Lang\LangBase::loadLang()
	 */
	public function loadLang($locale){
		parent::loadLang($locale);
		$filePath=$this->localPath.$locale.self::FILE_EXT;
	
		$this->iniDriver[$locale]=new ConfigBase($filePath);

		return $this;
	}
	
	/**
	 * 返回一个翻译字符串结构
	 *
	 * @param string $token 传递给之类的解析
	 */
	protected function getTokenString($token)
	{
		//无配对时，返回的是array
		if ($this->iniDriver[$this->locale]->getByKey($token)!==array()) {
			return parent::translate($this->iniDriver[$this->locale]->getByKey($token));
		}else{
			return $token;
		}
	}
}