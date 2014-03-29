<?php
namespace HuiLib\Db;

use HuiLib\App\Front;

/**
 * 数据库基础类
 * 
 * 包括适配器、工厂功能，因为包含太多文件实在影响性能和维护，尽量保持简洁构架
 *
 * @author 祝景法
 * @since 2013/09/03
 */
abstract class DbBase
{
	/**
	 * 数据库连接
	 */
	protected $connection=NULL;
	
	/**
	 * 数据库驱动 如mysql
	 */
	protected $driver=NULL;
	
	/**
	 * 数据库主从配置
	 */
	private static $config=NULL;
	
	/**
	 * 数据库连接缓存
	 * @var array 
	 */
	private static $dbConnectPool=array();

	/**
	 * 创建DB Master实例
	 * 
	 * 可以直接调用创建默认主库连接
	 * 
	 * @return \HuiLib\Db\DbBase 
	 */
	public static function createMaster()
	{
	    if (isset(self::$dbConnectPool['master']) && self::$dbConnectPool['master'] instanceof DbBase) {
	        return self::$dbConnectPool['master'];
	    }
	    
		self::initConfig();
		
		if (empty(self::$config['master'])) {
			throw new \HuiLib\Error\Exception('Db master config can not be empty!');
		}

		self::$dbConnectPool['master']=self::create(self::$config['master']);
		return self::$dbConnectPool['master'];
	}
	
	/**
	 * 创建DB Slave实例
	 * 
	 * 可以直接调用创建默认从库连接
	 * 
	 * @return \HuiLib\Db\DbBase 
	 */
	public static function createSlave($slaveNode=NULL)
	{
		self::initConfig();
		
		if (empty(self::$config['slave'])) {
			throw new \HuiLib\Error\Exception('Empty slave config!');
		}
		
		$slaveConfig=self::$config['slave'];
		if (empty($slaveNode)) {
		    $slaveNode=array_rand($slaveConfig);
			$dbConfig=$slaveConfig[$slaveNode];
		}elseif (isset(self::$slaveConfig[$slaveNode])){
			$dbConfig=self::$slaveConfig[$slaveNode];
		}else{
			throw new \HuiLib\Error\Exception('Specified slave config is empty!');
		}
		
		if (isset(self::$dbConnectPool[$slaveNode]) && self::$dbConnectPool[$slaveNode] instanceof DbBase) {
		    return self::$dbConnectPool[$slaveNode];
		}
		
		self::$dbConnectPool[$slaveNode]=self::create($dbConfig);
		return self::$dbConnectPool[$slaveNode];
	}
	
	/**
	 * 创建DB实例 DB factory方法
	 */
	public static function create($dbConfig){
		if (empty($dbConfig['adapter'])) {
			throw new \HuiLib\Error\Exception('Db adapter can not be empty!');
		}

		$adapter=NULL;
		switch ($dbConfig['adapter']){
			case 'pdo':
				$adapter=new \HuiLib\Db\Adapter\Pdo\PdoBase($dbConfig);
				break;
			case 'mongo':
		
				break;
		}
		
		return $adapter;
	} 
	
	/**
	 * 获取数据库连接，便于直接查询
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * 获取具体配置驱动实例
	 */
	public function getDriver()
	{
		return $this->driver;
	}
	
	/**
	 * 设置数据库配置
	 *
	 * @param array $config
	 */
	public static function setConfig($config){
		self::$config=$config;
	}
	
	/**
	 * 设置数据库配置
	 * @param array $config
	 */
	protected static function initConfig(){
		if (self::$config===NULL) {
			$configInstance=Front::getInstance()->getAppConfig();
			self::$config=$configInstance->getByKey('db');
		}
	}
	
	/**
	 * 开启一个事务
	 */
	abstract public function beginTransaction();
	
	/**
	 * 开启一个事务
	 */
	abstract public function commit();
	
	/**
	 * 事务回滚
	 */
	abstract public function rollback();
}