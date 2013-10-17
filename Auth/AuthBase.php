<?php
namespace HuiLib\Auth;

/**
 * 登录验证基础模块
 *
 * @author 祝景法
 * @since 2013/09/11
 */
class AuthBase
{
	private static $instance;
	
	private function __construct(){
		
	}
	
	/**
	 * 校验过程
	 */
	private function authorize(){
		
	}
	
	/**
	 * 是否登录
	 */
	public function isLogin(){
	
	}
	
	/**
	 * 登录操作
	 */
	public function login(){
	
	}
	
	/**
	 * 退出操作
	 */
	public function logout(){
	
	}
	
	/**
	 * 获取引导类实例
	 * @return \HuiLib\Auth\AuthBase
	 */
	public static function getInstance()
	{
		if (self::$instance == NULL) {
			self::$instance = new self ();
		}
		//检验数据
		self::$instance->authorize();
		return self::$instance;
	}
}