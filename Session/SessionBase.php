<?php 
namespace HuiLib\Session;

/**
 * Session基础类及工厂函数
 * 
 * 由于session处理函数回调都是serialize后的数据，不是元数据，因此使用Memcache、Apc之类的更具有优势。
 * Redis HASH数据结构处理起来要多个步骤。
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
	
	/**
	 * Session生存时间
	 * @var int
	 */
	protected $lifeTime=0;
	
	/**
	 * Session key prefix session键前缀，不同于缓存中的前缀
	 * @var string
	 */
	protected static $prefix='';
	
	protected function __construct($driverConfig)
	{
		$this->driver=\HuiLib\Cache\CacheBase::create($driverConfig);
		if (! $this->driver instanceof \HuiLib\Cache\CacheBase) {
			throw new \HuiLib\Error\Exception ( 'Session cache driver initialized failed' );
		}
		
		$life=intval(ini_get('session.cookie_lifetime'));
		if ($life>0) {
			$this->lifeTime=$life;
		}
	}

	/**
	 * 初始化一个Session
	 * 
	 * @see \SessionHandlerInterface::open()
	 */
	public function open ( $savePath , $name )
	{
		
	}
	
	/**
	 * 读取一个Session值
	 * 
	 * @see \SessionHandlerInterface::read()
	 */
	public function read ( $sessionId )
	{
	
	}
	
	/**
	 * 写入一个Session值
	 * 
	 * 写入的Session值全部都是通过serialize()处理过的字符串，如果单独处理某个键值，比较麻烦。从这个角度来说，Memcache性能好些。
	 * 另外，Session都是针对单用户单线程，同个用户登录的Session也不同，无需防并发。
	 * 
	 * @see \SessionHandlerInterface::write()
	 */
	public function write ( $sessionId , $sessionData )
	{
	
	}
	
	public function close ()
	{
		
	}
	
	/**
	 * 销毁一批过期的Session
	 * 
	 * @see \SessionHandlerInterface::destroy()
	 */
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
		
		//设置session键前缀
		if (!empty($config ['prefix'] )) {
			self::$prefix=$config ['prefix'];
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
