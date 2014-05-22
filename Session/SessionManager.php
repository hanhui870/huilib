<?php 
namespace HuiLib\Session;

use HuiLib\Error\Exception;

/**
 * Session GC 管理类
 * 
 * 使用Redis ZSet结构作为数据储存，因此必须启用Redis缓存
 * 
 * Session管理要点：
 * 1、ZSet:在线列表，最近在单位时间内活跃的用户，比如半小时（不能太长，以前2小时没意义，除了数量上，没其他意义）
 *      超过时间执行GC，删除Session数据
 * 2、ZSet:保持状态登录用户列表及维护（有AutoLogin cookie标记），根据deadline(最后存活时间)，超过1个月没活动后删除。
 *      删除后用户自动退出登录。（是否登录以是否存在session为依据）
 *      
 * 注释：GC或者销毁，Session数据不回写到数据库，有同步Model
 *      
 * 假设：session储存是永久的，因为像Memcache，重启、关机可能导致登录会话丢失
 * 
 * @author 祝景法
 * @since 2013/10/13
 */
class SessionManager
{
	//ZSet:在线列表，最近在单位时间内活跃的用户
	const MANAGER_DATALIST='global:session:gc:manager:datalist:zset';
	
	//ZSet:保持状态登录用户列表及维护
	const MANAGER_DEADLINE='global:session:gc:manager:deadline:zset';
	
	/**
	 * 管理器缓存内部连接
	 *
	 * @var \HuiLib\Cache\Storage\Redis
	 */
	protected $redis=NULL;
	
	/**
	 * SessionBase
	 * @var \HuiLib\Session\SessionBase
	 */
	protected $sessionBase=NULL;
	
	/**
	 * SessionBase
	 * @var \HuiLib\Session\ModelInterface
	 */
	protected $sessionModel=NULL;
	
	/**
	 * 每次GC最大执行数
	 */
	const MAX_GC_PER_ACTION=200;
	
	private function __construct()
	{
		$this->redis=\HuiLib\Cache\CacheBase::getRedis();
	}
	
	/**
	 * 设置SessionBase Adapter
	 *
	 * @param string $sessionId session缓存键
	 */
	public function setAdapter(\HuiLib\Session\SessionBase $session)
	{
		$this->sessionBase=$session;
	}
	
	/**
	 * 更新一个session的最后活跃时间
	 * 
	 * @param string $sessionId session缓存键
	 */
	public function update($sessionId)
	{
		return $this->redis->zAdd(self::MANAGER_DATALIST, time(), $sessionId);
	}
	
	/**
	 * 删除一个session
	 *
	 * @param string $sessionId session缓存键
	 */
	public function delete($sessionId)
	{
		return $this->redis->zDelete(self::MANAGER_DATALIST, $sessionId);
	}
	
	/**
	 * 获取一个session的最近一次操作时间
	 */
	public function getLastVisit($sessionId){
		return $this->redis->zScore(self::MANAGER_DATALIST, $sessionId);
	}
	
	/**
	 * 延长一个自动登录session的deadline
	 *
	 * @param string $sessionId session缓存键
	 */
	public function updateDeadline($sessionId)
	{
		//Session后端生存时间 默认一个月
		$life=$this->sessionBase->getLife();
		return $this->redis->zAdd(self::MANAGER_DEADLINE, time()+$life, $sessionId);
	}
	
	/**
	 * 将一个session从AutoLogin列表中移除
	 *
	 * @param string $sessionId session缓存键
	 */
	public function deleteDeadline($sessionId)
	{
		return $this->redis->zDelete(self::MANAGER_DEADLINE, $sessionId);
	}
	
	/**
	 * 获取一个session的超时时间
	 * 
	 * 无数据表示非保持登录用户
	 */
	public function getDeadline($sessionId){
		return $this->redis->zScore(self::MANAGER_DEADLINE, $sessionId);
	}
	
	/**
	 * 执行一次session gc操作
	 * 
	 * 算法：
	 * 1、在线列表：将ZSet数据集中最后活跃(<time()-$life)所有垃圾销毁掉
	 * 2、保持登录：session到期后用户还没出来活动(>deadline)
	 * 
	 * 1和2可能相交，保持登录用户超时后清除在线列表；销毁前调用session delete()函数
	 * 
	 * 原ylstu库操作条件：
	 * (deadline>0 and deadline<timeNow) or (deadline=0 and ltime=timeNow-keepOline)
	 *
	 * @param string $sessionId session缓存键
	 */
	public function gc($maxlifetime)
	{
		$gcTime=time();
		
		/**
		 * 获取超过在线时间允许的列表并清理之 limit => array($offset, $count)
		 * 
		 * 取出来数据格式 array($sessionID=>score):
		 * Array([yunk5y093qzlcoij5nhbz49ospyay4rp8yilbxli] => 1379425023)
		 */
		$endStamp=$gcTime-$this->sessionBase->getLife();
		$kickOnline=$this->redis->zRangeByScore(self::MANAGER_DATALIST, 0, $endStamp, array('withscores'=>TRUE, 'limit' => array(0, self::MAX_GC_PER_ACTION)));
		//print_r($kickOnline);die();
		
		if (!empty($kickOnline)) {
			//这里不能使用redis multi，因为读session可能使用redis
			foreach ($kickOnline as $sessionId=>$lastVisit ){
				//删除在线列表中数据
				$this->redis->zDelete(self::MANAGER_DATALIST, $sessionId);
				
				//删除实体session数据 保持登录的除外
				if (!intval($this->getDeadline($sessionId))) {
					$this->sessionBase->delete($sessionId);
				}
			}
		}
		
		/**
		 * 处理保持登录过期列表 
		 * 
		 * 超过一个月无活动的删除session
		 */
		$kickAutoLogin=$this->redis->zRangeByScore(self::MANAGER_DEADLINE, 0, $gcTime, array('withscores'=>TRUE, 'limit' => array(0, self::MAX_GC_PER_ACTION)));
		//print_r($kickAutoLogin);die();
		
		if (!empty($kickAutoLogin)) {
			$redisMulti=$this->redis->multi();
			foreach ($kickOnline as $sessionId=>$lastVisit ){
				//铁定不在线列表中  从保持登录列表删除
				$redisMulti->zDelete(self::MANAGER_DEADLINE, $sessionId);
				
				//删除实体session数据，直接删除，从在线列表剔除时更新资料过
				$this->sessionBase->delete($sessionId);
			}
			$redisMulti->exec();
		}
	}
	
	/**
	 * 返回应用端(App)交互接口Model
	 * 
	 * @return \HuiLib\Session\ModelInterface
	 */
	public function getModel()
	{
		if ($this->sessionModel===NULL) {
			$config=$this->sessionBase->getConfig();
			if (!isset($config['model']) || !class_exists($config['model'], FALSE)) {
				$this->sessionModel=FALSE;
				return $this->sessionModel;
			}
			
			$modelClass=$config['model'];
			$this->sessionModel=$modelClass::getInstance();
		}
		
		return $this->sessionModel;
	}

	/**
	 * 创建一个GC实例
	 */
	public static function create()
	{
		return new self();
	}
}