<?php 
namespace HuiLib\Session;

/**
 * Session模型接口，推送到数据库相关业务
 * 
 * Singleton单例模式
 * 
 * @author 祝景法
 * @since 2013/12/01
 */
interface ModelInterface
{
	/**
	 * 将session数据推送到持久储存的接口
	 * @param array $session
	 * @param int $lastVisit
	 */
	public function pushToDb($session, $lastVisit);
	
	/**
	 * 同步数据库和Session中的信息
	 */
	public function syncWithDatabase();
	
	/**
	 * 获取单例静态方法
	 */
	public static function getInstance();
}