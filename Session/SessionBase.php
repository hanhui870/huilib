<?php 
namespace HuiLib\Session;

/**
 * Session基础类及工厂函数
 * 
 * 1、数据储存使用Memcache、Apc之类的更具有优势。
 *     （由于session处理函数回调都是serialize后的数据，不是元数据；单用户单线程，无需防并发）
 * 2、Session管理使用Redis KV数据库管理元数据，如在线列表、保持登录等功能
 * 3、针对Robots的session_id特殊处理
 * 
 * 清空本访问关联session使用: $_SESSION=array();//使用''无效
 * 
 * @author 祝景法
 * @since 2013/09/27
 */
class SessionBase implements \SessionHandlerInterface  
{
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
	 * Session后端生存时间
	 * 
	 * 默认一个月，需要到期前自动延长
	 * @var int
	 */
	protected $lifeTime=2592000;
	
	/**
	 * Session key prefix session键前缀，不同于缓存中的前缀
	 * @var string
	 */
	protected static $prefix='';
	
	/**
	 * Session GC 管理器
	 * @var \HuiLib\Session\SessionManager
	 */
	protected $manager=NULL;
	
	protected function __construct($driverConfig)
	{
		$this->driver=\HuiLib\Cache\CacheBase::create($driverConfig);
		if (! $this->driver instanceof \HuiLib\Cache\CacheBase) {
			throw new \HuiLib\Error\Exception ( 'Session cache driver initialized failed' );
		}
		
		//session管理器
		$this->manager=\HuiLib\Session\SessionManager::create();
		$this->manager->setAdapter($this);
	}

	/**
	 * 初始化一个Session
	 * 
	 * 每次session访问均有open操作
	 * 1、更新用户session最后活跃时间
	 * 2、到期前7天内活跃需延长用户Passport cookie生命期，session储存键的生命期
	 * 
	 * @see \SessionHandlerInterface::open()
	 */
	public function open ( $savePath , $name )
	{
		//初始化，设置个性化40位SessionID
		if (session_id()=='') {
			//自定义规则生成session_id的方案，必须在session_open中调用有效
			session_id(\HuiLib\Helper\Utility::geneRandomHash());
		}
		
		//更新session管理器最后活跃
		$this->manager->update(session_id());
		
		return true;
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
	 * session有效期同
	 * 
	 * @see \SessionHandlerInterface::write()
	 */
	public function write ( $sessionId , $sessionData )
	{
	
	}
	
	public function close ()
	{
		return true;
	}
	
	/**
	 * 销毁当前一条Session，不同于GC
	 * 
	 * @see \SessionHandlerInterface::destroy()
	 */
	public function destroy ( $sessionId )
	{
		//清除浏览器session cookie
		$cookie=\HuiLib\Helper\Cookie::create()->delCookie(ini_get('session.name'));
		
		/**
		 * TODO 将个人资料推送到数据库中的操作 
		 * 1、因为将要删除实体session储存
		 * 2、推送操作全部以session中的数据为基础，不能依据目前用户的$_SESSION操作，因为可能是GC触发的
		 */
		
		//从管理列表中剔除一个SessionID
		return $this->manager->delete(session_id());
	}

	/**
	 * GC调用接口
	 * 
	 * Called randomly by PHP internally when a session starts or when session_start() is invoked.
	 * 
	 * @see SessionHandlerInterface::gc()
	 */
	public function gc ( $maxlifetime )
	{
		return $this->manager->gc($maxlifetime);
	}
	
	/**
	 * 获取session管理器
	 * 
	 * @return \HuiLib\Session\SessionManager
	 */
	public function getManager ( )
	{
		return $this->manager;
	}
	
	/**
	 * 获取session后端生命期
	 * 
	 * @return boolean
	 */
	public function getLife ()
	{
		return $this->lifeTime;
	}
	
	/**
	 * 设置session后端生存周期
	 *
	 * @param int $life 生存周期长度 默认一个月
	 */
	public function setLife($life){
		if ($life>=0) {
			$this->lifeTime=$life;
		}
	
		return $this;
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
