<?php 
namespace HuiLib\Session;

/**
 * Session GC 管理类
 * 
 * 使用Redis HASH结构作为数据储存，因此必须启用Redis缓存
 * 
 * Session管理要点：
 * 1、ZSet:在线列表，最近在单位时间内活跃的用户，比如半小时（不能太长，以前2小时没意义，除了数量上，没其他意义）
 *      超过时间执行GC，更新用户资料，删除Session数据
 * 2、ZSet:保持状态登录用户列表及维护（有AutoLogin cookie标记），根据最后活跃时间，超过1个月没活动后删除。
 *      删除后用户自动推出登录。（是否登录以是否存在session为依据）
 * 
 * @author 祝景法
 * @since 2013/10/13
 */
class SessionManager
{
	const MANAGER_DATALIST='global:session:gc:manager:datalist';
	const MANAGER_AUTOLOGIN='global:session:gc:manager:autologin';
	
	/**
	 * 管理器缓存内部连接
	 *
	 * @var \HuiLib\Cache\Storage\Redis
	 */
	protected $redis=NULL;
	
	/**
	 * Session Connect
	 * @var \HuiLib\Session\SessionManager
	 */
	protected $connect=NULL;
	
	private function __construct()
	{
		$this->redis=\HuiLib\Cache\CacheBase::getRedis();
	}
	
	/**
	 * 设置Session Connect Adapter
	 *
	 * @param string $key Hash缓存键
	 */
	public function setAdapter(\HuiLib\Session\SessionBase $session)
	{
		$this->connect=$session;
	}
	
	/**
	 * 更新一个session的最后活跃时间
	 * 
	 * @param string $key Hash缓存键
	 */
	public function update($key)
	{
		
	}
	
	/**
	 * 删除一个session
	 *
	 * @param string $key Hash缓存键
	 */
	public function delete($key)
	{
		
	}
	
	/**
	 * 执行一次session gc操作
	 *
	 * @param string $key Hash缓存键
	 */
	public function gc($maxlifetime)
	{
	
	}
	
	/**
	 * 创建一个GC实例
	 */
	public static function create()
	{
		return new self();
	}
}