<?php 
namespace HuiLib\Session;

/**
 * Session基础类及工厂函数
 * 
 * @author 祝景法
 * @since 2013/09/27
 */
class SessionBase implements \SessionHandlerInterface  {
	/**
	 * Session内部连接
	 *
	 * @var \HuiLib\Cache\CacheBase
	 */
	protected $driver=NULL;
	
	/**
	 * Session初始化配置
	 * @var array
	 */
	protected $config=NULL;
	
	protected function __construct($driverConfig)
	{
		
	}

	public function open ( $savePath , $name )
	{
	
	}
	
	public function read ( $sessionId )
	{
	
	}
	
	public function write ( $sessionId , $sessionData )
	{
	
	}
	
	public function close ()
	{
		
	}

	public function destroy ( $sessionId )
	{
		
	}

	public function gc ( $maxlifetime )
	{
		
	}
	
	/**
	 * 创建一个Session适配器
	 * 
	 * 支持redis、memcache、dbtable等三种适配器
	 */
	public static function create(\HuiLib\Config\ConfigBase $configInstance)
	{
		$config=$configInstance->getByKey('session');
		if (empty ( $config ['adapter'] ) || empty ( $config ['driver'] )) {
			throw new \HuiLib\Error\Exception ( 'Session adapter & driver can not be empty' );
		}
		
		$driverConfig=$configInstance->getByKey($config ['driver']);
		if (empty ( $driverConfig )) {
			throw new \HuiLib\Error\Exception ( 'Session driver config can not be empty' );
		}
		
		switch ($config ['adapter']) {
			case 'redis' :
				$driver = new \HuiLib\Session\Storage\Redis ( $driverConfig );
				break;
			case 'memcache' :
				$driver = new \HuiLib\Session\Storage\Memcache ( $driverConfig );
				break;
			case 'dbtable' :
				$driver = new \HuiLib\Session\Storage\DbTable ( $driverConfig );
				break;
		}
		
		//注册Session处理函数
		session_set_save_handler($driver, TRUE);
		session_start();
		
		return $driver;
	}
}
