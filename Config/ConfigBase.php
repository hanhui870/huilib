<?php
namespace HuiLib\Config;

/**
 * ini文件配置信息解析类
 *
 * @author 祝景法
 * @since 2013/08/11
 */
class ConfigBase
{
	private $filePath;
	private $config;
	
	const PARSE_SECTION=true;

	function __construct($configFile)
	{
		$this->filePath = $configFile;
		$this->parse ();
	}

	private function parse()
	{
		if (!is_file($this->filePath)) {
			throw new \HuiLib\Error\Exception("Config file: {$this->filePath} Not exists!");
		}
		
		$this->config=parse_ini_file($this->filePath, self::PARSE_SECTION);
		print_r($this->config);
	}
}
