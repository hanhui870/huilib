<?php
namespace HuiLib\Log;

use HuiLib\Bootstrap;
use HuiLib\Helper\Param;
use HuiLib\App\Front;

/**
 * File基础类
 *
 * @author 祝景法
 * @since 2013/11/10
 */
abstract class LogBase
{
	/**
	 * PHP语言相关错误
	 */
	const TYPE_PHPERROR = 'PHPError';
	
	/**
	 * 运行时错误
	 */
	const TYPE_RUNTIME = 'Runtime';
	
	/**
	 * DAEMON执行的相关人物
	 */
	const TYPE_DAEMON = 'Daemon';
	
	/**
	 * 数据库相关人物
	 */
	const TYPE_DBERROR = 'DBError';
	
	/**
	 * 用户行为相关人物
	 */
	const TYPE_USERERROR = 'UserError';
	
	/**
	 * Log内部连接
	 */
	protected $driver = NULL;
	
	/**
	 * Log类型
	 */
	protected $type = NULL;
	
	/**
	 * Log 识别类型
	 */
	protected $identify = 'normal';
	
	/**
	 * 配置实例
	 * @var \HuiLib\Config\ConfigBase
	 */
	protected $configInstance = NULL;
	
	/**
	 * 当前处理包名
	 * @var String
	 */
	protected $package = '';
	
	/**
	 * 当前处理控制器名
	 * @var String
	 */
	protected $controller = '';
	
	/**
	 * 当前处理动作名
	 * @var String
	 */
	protected $action = '';
	
	/**
	 * 当前访问用户ID
	 * @var int
	 */
	protected $uid = 0;
	
	/**
	 * 当前访问的页面
	 * @var string
	 */
	protected $urlNow = '';

	protected function __construct($config, $configInstance)
	{
		//初始化公共信息部分
		$constroller = Front::getInstance()->getController();
		if ($constroller instanceof \HuiLib\App\Controller) {
			$this->package = $constroller->getPackage ();
			$this->controller = $constroller->getController ();
			$this->action = $constroller->getAction ();
		}
		if (isset ( $_SESSION ['uid'] )) {
			$this->uid = $_SESSION ['uid'];
		}
		$this->urlNow = Param::getRequestUrl ();
	}

	/**
	 * 获取系统默认缓存实例
	 */
	public static function getDefault(\HuiLib\Config\ConfigBase $configInstance = NULL)
	{
		if ($configInstance === NULL) {
			$configInstance = Front::getInstance()->getAppConfig();
		}
		
		$adapterName = $configInstance->getByKey ( 'log.defalut' );
		if (empty ( $adapterName )) {
			throw new \HuiLib\Error\Exception ( 'Log default adapter has not set.' );
		}
		
		return self::staticCreate ( $adapterName, $configInstance );
	}

	/**
	 * 获取Memcache默认缓存实例
	 */
	public static function getFile(\HuiLib\Config\ConfigBase $configInstance = NULL)
	{
		return self::staticCreate ( 'log.file', $configInstance );
	}

	/**
	 * 获取Redis默认缓存实例
	 */
	public static function getMysql(\HuiLib\Config\ConfigBase $configInstance = NULL)
	{
		return self::staticCreate ( 'log.mysql', $configInstance );
	}

	/**
	 * 获取Mongo默认缓存实例
	 */
	public static function getMongo(\HuiLib\Config\ConfigBase $configInstance = NULL)
	{
		return self::staticCreate ( 'log.mongo', $configInstance );
	}

	private static function staticCreate($adapterName, \HuiLib\Config\ConfigBase $configInstance = NULL)
	{
		if ($configInstance === NULL) {
			$configInstance = Front::getInstance()->getAppConfig();
		}
		
		$adapterConfig = $configInstance->getByKey ( $adapterName );
		if (empty ( $adapterConfig )) {
			throw new \HuiLib\Error\Exception ( $adapterName . ' adapter config has not set.' );
		}
		
		return self::create ( $adapterConfig, $configInstance );
	}

	/**
	 * 创建DB实例 DB factory方法
	 */
	public static function create($config, $configInstance)
	{
		if (empty ( $config ['adapter'] )) {
			throw new \HuiLib\Error\Exception ( 'Log adapter can not be empty!' );
		}
		
		$adapter = NULL;
		switch ($config ['adapter']) {
			case 'mysql' :
				$adapter = new \HuiLib\Log\Storage\Mysql ( $config, $configInstance );
				break;
			case 'file' :
				$adapter = new \HuiLib\Log\Storage\File ( $config, $configInstance );
				break;
			case 'mongo' :
				$adapter = new \HuiLib\Log\Storage\Mongo ( $config, $configInstance );
				break;
		}
		
		return $adapter;
	}

	/**
	 * 设置日志类型
	 * 
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
		
		return $this;
	}

	/**
	 * 设置识别符
	 *
	 * @param string $identify
	 */
	public function setIdentify($identify)
	{
		$this->identify = $identify;
		
		return $this;
	}

	/**
	 * 获取调式路径信息
	 */
	public static function getDebugTrace($filterLoop=1)
	{
		$debug = debug_backtrace ( 0 );
		if ($filterLoop<1) {
			throw new \HuiLib\Error\Exception ( 'LogBase::getDebugTrace() $filterLoop 参数错误.' );
		}
		for ($iter=1; $iter<=$filterLoop; $iter++){
			if (!empty($debug)) {
				array_shift($debug);
			}
		}
		
		$result = array ();
		foreach ( $debug as $key => $trace ) {
			if (empty ( $trace ['file'] ))
				break;
			$temp = array ();
			$temp ['file'] = str_ireplace ( array (SYS_PATH ), array (''), $trace ['file'] );
			$temp ['line'] = $trace ['line'];
			$temp ['function'] = $trace ['function'];
			$temp ['args'] = $trace ['args'];
			$result [] = $temp;
		}
		return $result;
	}

	/**
	 * 增加一条日志信息
	 *
	 * @param string $info
	 */
	abstract public function add($info);
}