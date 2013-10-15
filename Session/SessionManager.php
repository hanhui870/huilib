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
 * 2、ZSet:保持状态登录用户列表及维护（有AutoLogin cookie标记），根据deadline(最后存活时间)，超过1个月没活动后删除。
 *      删除后用户自动退出登录。（是否登录以是否存在session为依据）
 * 
 * @author 祝景法
 * @since 2013/10/13
 */
class SessionManager
{
	const MANAGER_DATALIST='global:session:gc:manager:datalist';
	const MANAGER_DEADLINE='global:session:gc:manager:deadline';
	
	/**
	 * 管理器缓存内部连接
	 *
	 * @var \HuiLib\Cache\Storage\Redis
	 */
	protected $redis=NULL;
	
	/**
	 * Session Connect
	 * @var \HuiLib\Session\SessionBase
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
		return $this->redis->zAdd(self::MANAGER_DATALIST, time(), $key);
	}
	
	/**
	 * 删除一个session
	 *
	 * @param string $key Hash缓存键
	 */
	public function delete($key)
	{
		return $this->redis->zDelete(self::MANAGER_DATALIST, $key);
	}
	
	/**
	 * 延长一个自动登录session的deadline
	 *
	 * @param string $key Hash缓存键
	 */
	public function updateDeadline($key)
	{
		$life=$this->connect->getLife();
		return $this->redis->zAdd(self::MANAGER_DEADLINE, time()+$life, $key);
	}
	
	/**
	 * 将一个session从AutoLogin列表中移除
	 *
	 * @param string $key Hash缓存键
	 */
	public function deleteDeadline($key)
	{
		return $this->redis->zDelete(self::MANAGER_DEADLINE, $key);
	}
	
	/**
	 * 执行一次session gc操作
	 * 
	 * 算法：
	 * 1、在线列表：将ZSet数据集中最后活跃(<time()-$life)所有垃圾销毁掉
	 * 2、保持登录：session到期后用户还没出来活动(>deadline)
	 * 
	 * 1和2不可能有相交，因为2到期内，会自动延长deadline；销毁前都需要调用session destory()函数
	 * 
	 * 原ylstu库操作条件：
	 * (deadline>0 and deadline<timeNow) or (deadline=0 and ltime=timeNow-keepOline)
	 *
	 * @param string $key Hash缓存键
	 */
	public function gc($maxlifetime)
	{
		echo 'session gc by manager';
		
		
	}
	
	/**
	 * 创建一个GC实例
	 */
	public static function create()
	{
		return new self();
	}
}