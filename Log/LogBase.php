<?php
namespace HuiLib\Log;

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
	 * DAEMON执行的相关日志
	 */
	const TYPE_DAEMON = 'Daemon';
	
	/**
	 * 数据库相关日志
	 */
	const TYPE_DBERROR = 'DBError';
	
	/**
	 * 用户行为相关日志
	 */
	const TYPE_USERERROR = 'UserError';
	
	/**
	 * 最多缓存条数
	 * @var int
	 */
	const MAX_BUFFER_NUM=50;
	
	/**
	 * 多少时间同步一次
	 * 
	 * 单位秒
	 */
	const FLUSH_INTERVAL=30;
	
	/**
	 * 日志保存时间
	 *
	 * 单位天，默认三个月
	 *
	 * @var
	 */
	const LOG_KEEP_DAYS=90;
	
	/**
	 * 日志缓存
	 * @var array
	 */
	protected $buffer=array();
	
	/**
	 * Log对象创建时间
	 * @var int
	 */
	protected $startTime=NULL;
	
	/**
	 * 上次刷新时间
	 * @var int
	 */
	protected $lastFlush=NULL;

	/**
	 * Log类型
	 */
	protected $type = NULL;
	
	/**
	 * Log 识别类型
	 */
	protected $identify = 'normal';

	protected function __construct($config)
	{
	}
	
	/**
	 * 获取系统默认缓存实例
	 */
	public static function getDefault()
	{
	    $configInstance = Front::getInstance()->getAppConfig();
		
		$adapterName = $configInstance->getByKey ( 'log.defalut' );
		if (empty ( $adapterName )) {
			throw new \HuiLib\Error\Exception ( 'Log default adapter has not set.' );
		}
		
		return self::staticCreate ( $adapterName );
	}

	/**
	 * 获取Memcache默认缓存实例
	 * 
	 * @return \HuiLib\Log\Storage\File 
	 */
	public static function getFile()
	{
		return self::staticCreate ( 'log.file' );
	}

	/**
	 * 获取Redis默认缓存实例
	 * 
	 * @return \HuiLib\Log\Storage\Mysql 
	 */
	public static function getMysql()
	{
		return self::staticCreate ( 'log.mysql' );
	}

	/**
	 * 获取Mongo默认缓存实例
	 * 
	 * @return \HuiLib\Log\Storage\Mongo
	 */
	public static function getMongo()
	{
		return self::staticCreate ( 'log.mongo' );
	}

	private static function staticCreate($adapterName)
	{
	    $configInstance = Front::getInstance()->getAppConfig();
		
		$adapterConfig = $configInstance->getByKey ( $adapterName );
		if (empty ( $adapterConfig )) {
			throw new \HuiLib\Error\Exception ( $adapterName . ' adapter config has not set.' );
		}
		
		return self::create ( $adapterConfig );
	}

	/**
	 * 创建DB实例 DB factory方法
	 * 
	 * @return \HuiLib\Log\LogBase 
	 */
	public static function create($config)
	{
		if (empty ( $config ['adapter'] )) {
			throw new \HuiLib\Error\Exception ( 'Log adapter can not be empty!' );
		}
		
		$adapter = NULL;
		switch ($config ['adapter']) {
			case 'mysql' :
				$adapter = new \HuiLib\Log\Storage\Mysql ( $config );
				break;
			case 'file' :
				$adapter = new \HuiLib\Log\Storage\File ( $config );
				break;
			case 'mongo' :
				$adapter = new \HuiLib\Log\Storage\Mongo ( $config );
				break;
		}
		
		//设置对象创建时间
		$adapter->startTime=time();
		//初始化上次刷入
		$adapter->lastFlush=time();
		//清除老日志，默认不启用
		$adapter->clean();

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
	 * File:会放置到文件名中作为区分
	 * Mysql:会专门插入到表的一个字段
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
	 * 当前访问用户ID
	 */
	protected function getLoginUid()
	{
	    if (isset ( $_SESSION ['uid'] )) {
	        return $_SESSION ['uid'];
	    }
	    return 0;
	}
	
	/**
	 * 增加一条日志信息
	 *
	 * @param string $message
	 * @param bool $trace 是否添加调试信息
	 */
	abstract public function add($message, $isTrace=TRUE);
	
	/**
	 * 将日志写入到磁盘
	 */
	abstract public function flush();
	
	/**
	 * 清除过期日志
	 */
	abstract public function clean();
}