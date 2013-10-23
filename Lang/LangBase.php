<?php
namespace HuiLib\Lang;

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
	 * 加载后的翻译文件缓存
	 * @var array
	 */
	protected $data= array();
	
	/**
	 * 翻译文件名拓展
	 * @var string
	 */
	const FILE_EXT = '';

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
	 * 请求一个翻译结果
	 * 
	 * TODO 传递可变参数的处理
	 * 
	 * 翻译失败返回token内容
	 * 
	 * @param string $token
	 */
	public function translate($token)
	{
		if (isset($this->data[$this->locale][$token])) {
			return $this->data[$this->locale][$token];
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
	public static function create($config)
	{
		if (empty ( $config ['adapter'] )) {
			throw new \HuiLib\Error\Exception ( 'Lang adapter can not be empty' );
		}
		
		switch ($config ['adapter']) {
			case 'gettext' :
				$adapter = new \HuiLib\Lang\Translator\GetText ( $config );
				break;
			case 'ini' :
				$adapter = new \HuiLib\Lang\Translator\Ini ( $config );
				break;
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
			$configInstance = \HuiLib\Bootstrap::getInstance ()->appInstance ()->configInstance ();
		}
		
		$config = $configInstance->getByKey ( 'lang' );
		if (empty ( $config )) {
			throw new \HuiLib\Error\Exception ( 'Lang default adapter has not set.' );
		}

		self::$defaultInstance = self::create ( $config );
		if ($lang) {
			self::$defaultInstance->loadLang ( $lang );
		}else{
			self::$defaultInstance->loadLang ( $config ['default'] );
		}
		
		return self::$defaultInstance;
	}
}
