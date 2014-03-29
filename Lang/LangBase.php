<?php
namespace HuiLib\Lang;

use HuiLib\App\Front;

/**
 * 语言翻译类
 * 
 * @author 祝景法
 * @since 2013/10/23
 */
abstract class LangBase
{
	/**
	 * 默认翻译实例
	 * 
	 * 默认实例是单例默认的，方便在不同部件调用
	 * 
	 * @var LangBase
	 */
	protected static $defaultInstance = NULL;
	
	/**
	 * 默认HuiLib翻译实例
	 *
	 * @var LangBase
	 */
	protected static $huiLibInstance = NULL;
	
	/**
	 * 翻译文件存放目录
	 * @var string
	 */
	protected $localPath;
	
	/**
	 * 当前加载的Locale
	 * @var string
	 */
	protected $locale;

	/**
	 * 翻译文件名拓展
	 * @var string
	 */
	const FILE_EXT = '';
	
	/**
	 * 默认语言
	 * 
	 * wordpress:多国语言列表：
	 * http://codex.wordpress.org/zh-cn:%E5%A4%9A%E5%9B%BD%E8%AF%AD%E8%A8%80%E6%89%8B%E5%86%8C
	 * 
	 * @var string
	 */
	const DEFAULT_LOCALE='zh-cn';

	protected function __construct($config)
	{
		if (empty ( $config ['path'] ) || ! is_dir ( $config ['path'] )) {
			throw new \HuiLib\Error\Exception ( 'Lang path can not be empty' );
		}
		
		$this->localPath = $config ['path'];
	}

	/**
	 * 加载某个语言版本的翻译文件
	 * 
	 * @param string $locale 如中文是zh，英文是en
	 */
	public function loadLang($locale)
	{
		$this->locale=$locale;
	}
	
	/**
	 * 快捷翻译函数
	 *
	 * 转发到translate
	 */
	public function _()
	{
	    $params=func_get_args();
	    return call_user_func_array(array($this, 'translate'), $params);
	}

	/**
	 * 请求一个翻译结果
	 * 
	 * 翻译失败返回token内容
	 * 
	 * @param string $token 传递给之类的解析
	 * @param mix $param 支持传递更多参数
	 */
	public function translate($token)
	{
		$stringResult=$this->getTokenString($token);
		if ($stringResult!==$token) {
			$params=func_get_args();
			//第一个参数是$token，剔除
			array_shift($params);

			if (count($params)>0) {
				$stringResult=vsprintf($stringResult, $params);
			}
		
			return $stringResult;
		}else{
			return $token;
		}
	}

	/**
	 * 静态创建接口
	 * 
	 * @param array $config 配置
	 * @return \HuiLib\Lang\LangBase
	 */
	public static function create($config, $lang=NULL)
	{
		if (empty ( $config ['adapter'] )) {
			throw new \HuiLib\Error\Exception ( 'Lang adapter can not be empty' );
		}
		
		$adapter=NULL;
		switch ($config ['adapter']) {
			case 'gettext' :
				$adapter = new \HuiLib\Lang\Translator\GetText ( $config );
				break;
			case 'ini' :
				$adapter = new \HuiLib\Lang\Translator\Ini ( $config );
				break;
		}
		
		if ($lang) {
			$adapter->loadLang ( $lang );
		}else{
			$adapter->loadLang ( $config ['default'] );
		}
		
		return $adapter;
	}

	/**
	 * 获取系统默认翻译实例
	 *
	 * 直接调用创建默认实例
	 */
	public static function getDefault(\HuiLib\Config\ConfigBase $configInstance = NULL, $lang=NULL)
	{
		if (self::$defaultInstance !== NULL) {
			return self::$defaultInstance;
		}
		
		if ($configInstance === NULL) {
			$configInstance = Front::getInstance()->getAppConfig();
		}
		
		$config = $configInstance->getByKey ( 'lang' );
		if (empty ( $config )) {
			throw new \HuiLib\Error\Exception ( 'Lang default adapter has not set.' );
		}

		self::$defaultInstance = self::create ( $config, $lang );

		return self::$defaultInstance;
	}
	
	/**
	 * 获取调用HuiLib库的翻译实例
	 * 
	 * 默认存在Lang/I18N目录下
	 */
	public static function getHuiLibLang($lang=NULL)
	{
		$adapter=array('adapter'=>'gettext', 'path'=>LIB_PATH.'Lang'.SEP.'I18N'.SEP, 'default'=>self::DEFAULT_LOCALE);
		self::$huiLibInstance = self::create ( $adapter );
		if ($lang) {
			self::$huiLibInstance->loadLang ( $lang );
		}else{
			self::$huiLibInstance->loadLang ( $adapter ['default'] );
		}
		
		return self::$huiLibInstance;
	}
	
	/**
	 * 返回一个翻译字符串结构
	 *
	 * @param string $token 传递给之类的解析
	 */
	protected abstract function getTokenString($token);
}
