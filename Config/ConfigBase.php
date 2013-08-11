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
					//使用了数组+符号
					$configGroup [$sectionNow] = $settingTree + $configGroup [$sectionNow];
				}
				
				//处理继承的关系
				foreach ( $heritInfo as $iterSection ) {
					$iterSection = trim ( $iterSection );
					if (! isset ( $configGroup [$iterSection] )) {
						continue;
					}
					//使用了数组+符号
					$configGroup [$sectionNow] += $configGroup [$iterSection];
				}
			} else {
				//无继承分支，类似[production]
				$sectionNow = trim ( $section );
				if (! isset ( $configGroup [$sectionNow] )) {
					$configGroup [$sectionNow] = $settingTree;
				} else {
					//使用了数组+符号
					$configGroup [$sectionNow] = $settingTree + $configGroup [$sectionNow];
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
			$parent [$keyNow] += self::buildNestArray ( implode ( '.', $keyInfo ), $value, $parent [$keyNow] );
		} else {
			$parent [$key] = $value;
		}
		
		return $parent;
	}

	/**
	 * 通过键获取配置块，默认在当前运行环境下
	 * 
	 * @param string $key 要获取的配置键
	 */
	public function getByKey($key = '')
	{
		if (! $key) {
			return $this->configEnv;
		}
		
		if (isset ( $this->configEnv [$key] )) {
			return $this->configEnv [$key];
		}
		
		return NULL;
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
}
