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
	const PARSE_SECTION = true;
	//ini文件，块解析分隔符
	const INI_SECTION_SEP = ':';
	//ini文件，键解析分隔符
	const KEY_SEP = '.';

	function __construct($configFile)
	{
		$this->filePath = $configFile;
		$this->parse ();
		$this->mergeConfig ();
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
	}

	/**
	 * 解析获取应用当前执行环境的配置
	 * 
	 * 此处数组相加不能使用array_merge_recursive，会导致某键多个值而覆盖不了
	 * 此处不能使用数组+符号，+符号运算仅限于第一级数组，二级不支持。array_replace_recursive更合适。
	 */
	private function mergeConfig()
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
					$configGroup [$sectionNow]=array_replace_recursive($configGroup[$sectionNow], $settingTree);
				}
				
				//处理继承的关系
				foreach ( $heritInfo as $iterSection ) {
					$iterSection = trim ( $iterSection );
					if (! isset ( $configGroup [$iterSection] )) {
						continue;
					}
					$configGroup [$sectionNow]=array_replace_recursive($configGroup [$iterSection], $configGroup [$sectionNow]);
				}
			} else {
				//无继承分支，类似[production]
				$sectionNow = trim ( $section );
				if (! isset ( $configGroup [$sectionNow] )) {
					$configGroup [$sectionNow] = $settingTree;
				} else {
					$configGroup [$sectionNow]=array_replace_recursive($configGroup[$sectionNow], $settingTree);
				}
			}
		} //foreach
		

		$this->configFinal = $configGroup;
		if (isset ( $configGroup [APP_ENV] )) {
			$this->configEnv = $configGroup [APP_ENV];
		} else {
			$this->configEnv = array ();
		}
	}

	/**
	 * 块配置
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
		$mergedConfig=$keyConfig;
		foreach ( $keyConfig as $childKey => $childConfig ) {
			$this->foreachKeyRecurse($childConfig, $childKey, $mergedConfig);
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
	private function foreachKeyRecurse($childConfig,  $parentKey, &$parentResult = array())
	{
		if (is_array ( $childConfig )) {
			foreach ($childConfig as  $secKey => $secConfig){
				unset($parentResult[$parentKey]);
				$parentResult[$parentKey.self::KEY_SEP.$secKey]=$secConfig;
			}
			foreach ($parentResult as $mergedKey => $secConfig){
				$this->foreachKeyRecurse($secConfig, $mergedKey, $parentResult);
			}
			
		} else {
			$parentResult[$parentKey]=$childConfig;
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
	
	public function toArray(){
		return $this->getBySection();
	}
}
