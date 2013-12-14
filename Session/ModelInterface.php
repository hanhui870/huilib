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
	 * Session初始化后事件调用
	 */
	public function onSessionStart();
	
	/**
	 * 关闭session事件接口（关闭前）
	 */
	public function onSessionClose();
	
	/**
	 * 销毁session事件接口（销毁前）
	 */
	public function onSessionDestroy();
	
	/**
	 * 同步数据库和Session中的信息
	 */
	public function syncWithDatabase();
	
	/**
	 * 获取单例静态方法
	 */
	public static function getInstance();
}