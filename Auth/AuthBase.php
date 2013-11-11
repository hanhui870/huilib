<?php
namespace HuiLib\Auth;

/**
 * 登录验证基础模块
 *
 * @author 祝景法
 * @since 2013/10/18
 */
class AuthBase
{
	private static $instance;
	
	/**
	 * 权限验证码
	 * 
	 * @param string $authcode 键名配置再App.ini
	 */
	protected $authcode;
	
	/**
	 * 根据cookie解析出来的用户信息
	 * 
	 * @var array
	 */
	protected $cookieUser=array('uid'=>0, 'username'=>'', 'password'=>'');
	
	/**
	 * 登录用户权限校验码
	 */
	protected $authCookie='';
	
	/**
	 * 后端和session验证码生命期
	 * @var int
	 */
	protected $sessionLife=0;
	
	/**
	 * 是否在登录状态
	 * @var boolean 
	 */
	protected $isLogin=FALSE;
	
	private function __construct(){
		$configInstance=\HuiLib\Bootstrap::getInstance()->appInstance()->configInstance();
		$this->authCookie=$configInstance->getByKey('app.session.auth');
		$this->sessionLife=$configInstance->getByKey('app.session.authLife');
		
		$this->authcode=\HuiLib\Helper\Param::cookie($authCookie, \HuiLib\Helper\Param::TYPE_STRING);
		$this->authorize();
	}
	
	/**
	 * 校验过程
	 */
	private function authorize(){
		if (condition) {
			@list ( $this->cookieUser ['uid'], $this->cookieUser ['username'], $this->cookieUser ['password'] ) = explode ( "\t", \HuiLib\Helper\Utility::authcode($this->authcode, 'DECODE' ) );
		}
		
		if ($_SESSION ['uid'] == $this->cookieUser ['uid'] && $_SESSION ['username'] == $this->cookieUser ['username'] && $_SESSION ['password'] == $this->cookieUser ['password']) {
			$this->isLogin = TRUE;
		}
		
		/**
		 * Cookie账户信息和Session不匹配则退出登录
		 */
		if (!$this->isLogin && $this->cookieUser ['uid']) {
			$this->logout();
		}
	}
	
	/**
	 * 是否登录
	 */
	public function isLogin(){
		return $this->isLogin;
	}
	
	/**
	 * 登录操作
	 * 
	 * 发到客户端的加密密码是临时生成的
	 * @param int $uid 登录用户ID
	 * @param int $username 登录用户名
	 * @param boolean $autoLogin 是否保持登录
	 */
	public function login($uid, $username, $autoLogin=0){
		$password=\HuiLib\Helper\Utility::geneRandomHash(32);
		
		$_SESSION['uid']=$uid;
		$_SESSION['username']=$username;
		$_SESSION['password']=$password;
		
		if (!$autoLogin) {
			//非保持登录session生命期为即时过期
			$this->sessionLife=0;
			$_SESSION['autoLogin']=0;
		}else{
			$_SESSION['autoLogin']=1;
		}
		
		//设置cookie
		$cookie=\HuiLib\Helper\Cookie::create()->setLife($this->sessionLife)->enableHttpOnly();
		$cookie->setSookie($this->authCookie, 
				\HuiLib\Helper\Utility::authcode("$_SESSION[uid]\t$_SESSION[username]\t$_SESSION[password]", 'ENCODE', $this->sessionLife ));
		
		//TODO session用户信息初始化
	}
	
	/**
	 * 退出操作
	 */
	public function logout(){
		//TODO 更新用户信息到数据库
		
		$_SESSION['uid']=0;
		$_SESSION['username']='';
		$_SESSION['password']='';
		\HuiLib\Helper\Cookie::create()->delCookie($this->authCookie);
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