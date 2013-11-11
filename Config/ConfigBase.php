<?php
namespace HuiLib\Config;
use \HuiLib\Helper\String;

/**
 * ini文件配置信息解析类
 *
 * @author 祝景法
 * @since 2013/08/11
 */
class ConfigBase
{
	private $filePath;
	
	/**
	 * 配置源代码
	 */
	private $configSource;
	
	/**
	 * 配置解析结果
	 */
	private $configFinal;
	
	/**
	 * 当前运行环境配置
	 */
	private $configEnv;
	
	/**
	 * 缓存接口 默认Apc cache
	 * 
	 * @var \HuiLib\Cache\CacheBase
	 */
	private $cacheAdapter = NULL;
	const PARSE_SECTION = true;
	//ini文件，块解析分隔符
	const INI_SECTION_SEP = ':';
	//ini文件，键解析分隔符
	const KEY_SEP = '.';
	const CACHE_PREFIX = 'cache:';

	function __construct($configFile)
	{
		$this->filePath = $configFile;
		
		//由于此时
		$this->cacheAdapter = \HuiLib\Cache\CacheBase::getApcDirectly ();
		$cacheContent = $this->cacheAdapter->get ( $this->getCacheKey () );
		//print_r($cacheContent);die();
		
		if ($cacheContent === FALSE) {//不存在
			//实际解析文件
			$this->parse ();
			
		} elseif (empty ( $cacheContent ['stamp'] ) || $cacheContent ['stamp']<filemtime($this->filePath)) {//解析错误或配置文件已更新
			$this->cacheAdapter->delete ( $this->getCacheKey () );
			$this->parse ();
			
		} else {
			$this->configFinal=$cacheContent ['data'];
			if ($cacheContent ['section']) {//存在section标记
				if (isset ( $this->configFinal [APP_ENV] )) {
					$this->configEnv = &$this->configFinal [APP_ENV];
				} else {
					$this->configEnv = array ();
				}
			}else {
				$this->configEnv = &$this->configFinal;
			}
		}
	}

	/**
	 * 返回Cache储存键
	 */
	private function getCacheKey()
	{
		return self::CACHE_PREFIX . md5 ( $this->filePath );
	}

	/**
	 * 解析应用配置ini文件
	 * @throws \HuiLib\Error\Exception
	 */
	private function parse()
	{
		if (! is_file ( $this->filePath )) {
			throw new \HuiLib\Error\Exception ( "Config file: {$this->filePath} Not exists!" );
		}
		
		$this->configSource = parse_ini_file ( $this->filePath, self::PARSE_SECTION );
		if (! is_array ( $this->configSource )) {
			throw new \HuiLib\Error\Exception ( "Config ini file parsed Exception!" );
		}
		
		//检测是否存在服务器环境标签
		$allowEnvTag = \HuiLib\Bootstrap::getInstance ()->getAllowEnv ();
		$existSection = FALSE;
		foreach ( $allowEnvTag as $envTag ) {
			if (isset ( $this->configSource [$envTag] )) {
				$existSection = TRUE;
			}
		}
		
		/**
		 * 解析块元素
		 *
		 * 1、存在[production]等标签，根据继承合并块
		 * 2、不包括环境标签的直接解析数组
		 */
		if ($existSection) {
			$this->configFinal = $this->mergeSection ();
			if (isset ( $this->configFinal [APP_ENV] )) {
				$this->configEnv = &$this->configFinal [APP_ENV];
			} else {
				$this->configEnv = array ();
			}
		} else {
			$this->configFinal = $this->getSettingFromBlock ( $this->configSource );
			$this->configEnv = &$this->configFinal;
		}
		
		//缓存到缓存服务器
		$cache = array ();
		$cache ['data'] = $this->configFinal;
		$cache ['section'] = $existSection;
		$cache ['stamp'] = filemtime ( $this->filePath );
		
		$this->cacheAdapter->add ( $this->getCacheKey (), $cache );
	}

	/**
	 * 解析获取应用当前执行环境的配置
	 * 
	 * 此处数组相加不能使用array_merge_recursive，会导致某键多个值而覆盖不了
	 * 此处不能使用数组+符号，+符号运算仅限于第一级数组，二级不支持。array_replace_recursive更合适。
	 */
	private function mergeSection()
	{
		$configGroup = array ();
		foreach ( $this->configSource as $section => $blockSetting ) {
			$settingTree = $this->getSettingFromBlock ( $blockSetting );
			
			//继承分支，类似[develop : production]
			if (String::exist ( $section, self::INI_SECTION_SEP )) {
				$heritInfo = explode ( self::INI_SECTION_SEP, $section );
				
				//赋值最前面的当前section
				$sectionNow = trim ( array_shift ( $heritInfo ) );
				if (! isset ( $configGroup [$sectionNow] )) {
					$configGroup [$sectionNow] = $settingTree;
				} else {
					$configGroup [$sectionNow] = array_replace_recursive ( $configGroup [$sectionNow], $settingTree );
				}
				
				//处理继承的关系
				foreach ( $heritInfo as $iterSection ) {
					$iterSection = trim ( $iterSection );
					if (! isset ( $configGroup [$iterSection] )) {
						continue;
					}
					$configGroup [$sectionNow] = array_replace_recursive ( $configGroup [$iterSection], $configGroup [$sectionNow] );
				}
			} else {
				//无继承分支，类似[production]
				$sectionNow = trim ( $section );
				if (! isset ( $configGroup [$sectionNow] )) {
					$configGroup [$sectionNow] = $settingTree;
				} else {
					$configGroup [$sectionNow] = array_replace_recursive ( $configGroup [$sectionNow], $settingTree );
				}
			}
		} //foreach
		
		return $configGroup;
	}

	/**
	 * 块配置
	 * 
	 * array_merge_recursive:如果输入的数组中有相同的字符串键名，则这些值会被合并到一个数组中去，这将递归下去，
	 * 因此如果一个值本身是一个数组，本函数将按照相应的条目把它合并为另一个数组。然而，如果数组具有相同的数组键名，
	 * 后一个值将不会覆盖原来的值，而是附加到后面。 
	 * 
	 * @param array $blockSetting
	 * @return array
	 */
	private function getSettingFromBlock(array $blockSetting)
	{
		$settingTree = array ();
		
		foreach ( $blockSetting as $key => $value ) {
			$key = trim ( $key );
			$settingTree = array_merge_recursive ( $settingTree, $this->buildNestArray ( $key, $value ) );
		}
		
		return $settingTree;
	}

	/**
	 * 把键值转换成嵌套数组值
	 * 
	 * 数组 + 运算符附加右边数组元素，但是不会覆盖重复的键值。
	 * 
	 * @param string $key
	 * @param string $value
	 */
	private function buildNestArray($key, $value, $parent = array())
	{
		if (String::exist ( $key, self::KEY_SEP )) {
			$keyInfo = explode ( self::KEY_SEP, $key );
			
			$valueArray = array ();
			$keyNow = array_shift ( $keyInfo );
			
			if (! isset ( $parent [$keyNow] )) {
				$parent [$keyNow] = array ();
			}
			//递归从第一级创建，加法运算有效
			$parent [$keyNow] += self::buildNestArray ( implode ( '.', $keyInfo ), $value, $parent [$keyNow] );
		} else {
			$parent [$key] = $value;
		}
		
		return $parent;
	}

	/**
	 * 通过键获取配置块，默认在当前运行环境下
	 * 
	 * 支持获取方法，支持不同粒度：
	 * getByKey('webRun')
	 * getByKey('webRun.cookie')
	 * getByKey('webRun.cookie.pre')
	 * 
	 * @param string $key 要获取的配置键
	 * @return mix 空值返回array(),便于用于返回值遍历
	 */
	public function getByKey($key = '')
	{
		if (! $key) {
			return $this->configEnv;
		}
		
		$keyPath = explode ( self::KEY_SEP, $key );
		
		$valueIter = $this->configEnv;
		foreach ( $keyPath as $keyIter ) {
			if (isset ( $valueIter [$keyIter] )) {
				$valueIter = $valueIter [$keyIter];
			} else {
				$valueIter = array ();
				break;
			}
		}
		
		return $valueIter;
	}
	
	/**
	 * 通过键获取配置块，默认在当前运行环境下
	 *
	 * 支持获取方法，支持不同粒度：
	 * setByKey('webRun')
	 * setByKey('webRun.cookie')
	 * setByKey('webRun.cookie.pre')
	 *
	 * 设置仅针对当前环境的临时改动，并不会保存到APC缓存。
	 * 允许递归设置引用，如果全部地址处理确实可以。
	 *
	 * @param string $key 要获取的配置键
	 * @param string $value 要设置的值
	 * 
	 * @return mix 空值返回array(),便于用于返回值遍历
	 */
	public function setByKey($key, $value)
	{
		$keyPath = explode ( self::KEY_SEP, $key );
		
		$valueIter = &$this->configEnv;
		foreach ( $keyPath as $keyIter ) {
			if (!isset ( $valueIter [$keyIter] )) {
				$valueIter [$keyIter] = array ();
			}
			$valueIter = &$valueIter [$keyIter];
		}
		
		$valueIter=$value;
	
		return $this;
	}

	/**
	 * 把树形配置重新组合成分隔符间隔的
	 *
	 * 配置解析的例外情况就是PHP设置初始化参数
	 * 例如: session.save_handler; date.timezone等
	 *
	 * 配置禁用数组形式的值，支持递归合并
	 * 
	 * 算法提示：同解析，需要从根向叶遍历，逆向递归关系判断会脱节
	 */
	public function mergeKey($keyConfig)
	{
		$mergedConfig = $keyConfig;
		foreach ( $keyConfig as $childKey => $childConfig ) {
			$this->foreachKeyRecurse ( $childConfig, $childKey, $mergedConfig );
		}
		
		return $mergedConfig;
	}

	/**
	 * 逆向合并递归解析
	 * 
	 * @param string $childConfig
	 * @param string $parentKey 父键
	 * @param array $parentResult 递归解析父类
	 */
	private function foreachKeyRecurse($childConfig, $parentKey, &$parentResult = array())
	{
		if (is_array ( $childConfig )) {
			foreach ( $childConfig as $secKey => $secConfig ) {
				unset ( $parentResult [$parentKey] );
				$parentResult [$parentKey . self::KEY_SEP . $secKey] = $secConfig;
			}
			foreach ( $parentResult as $mergedKey => $secConfig ) {
				$this->foreachKeyRecurse ( $secConfig, $mergedKey, $parentResult );
			}
		} else {
			$parentResult [$parentKey] = $childConfig;
		}
	}

	/**
	 * 通过Section获取配置块
	 * 
	 * @param string $section 要获取的配置块
	 */
	public function getBySection($section = '')
	{
		if (! $section) {
			return $this->configFinal;
		}
		
		if (isset ( $this->configFinal [$section] )) {
			return $this->configFinal [$section];
		}
		
		return NULL;
	}

	public function toArray()
	{
		return $this->getBySection ();
	}
}
