<?php
namespace HuiLib\Helper;

/**
 * Cookie辅助类
 *
 * @author 祝景法
 * @since 2013/10/14
 */
class Cookie
{
	//生存周期 默认一个月
	private $life=2592000;
	
	//是否仅用于http链接
	private $httponly=FALSE;
	
	//是否仅允许https访问 secure
	private $secure=FALSE;
	
	//cookie作用目录
	private $path='/';
	
	//cookie作用域名, 默认当前host
	private $domain='';
	
	//是否发送P3P信息
	private $sendP3P=FALSE;
	
	//cookie p3p信息，IE跨域共享Cookie需要设置
	private $p3p='P3P:CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"';
	
	private function __construct(){
		
	}
	
	/**
	 * 设置一个cookie到浏览器
	 * 
	 * @param string $key cookie名称
	 * @param string $value cookie值
	 * @return boolean
	 */
	public function setSookie($key, $value){
		if ($this->sendP3P) {
			header($this->p3p);
		}
		
		//life=0表示浏览器单次session长度
		if ($this->life>0) {
			$life=time()+$this->life;
		}else{
			$life=0;
		}
		
		return setcookie ( $key, $value, $life, $this->path, $this->domain, $this->secure, $this->httponly );
	}
	
	/**
	 * 删除一个cookie
	 * @param string $key
	 */
	public function delCookie($key){
		return setcookie ( $key, '', -1, $this->path, $this->domain, $this->secure, $this->httponly );
	}
	
	/**
	 * 设置cookie生存周期
	 * 
	 * @param int $life 生存周期长度 默认一个月
	 */
	public function setLife($life){
		if ($life>=0) {
			$this->life=$life;
		}
		
		return $this;
	}
	
	/**
	 * 获取cookie生存周期
	 */
	public function getLife(){
		return $this->life;
	}
	
	/**
	 * 启用HttpOnly
	 */
	public function enableHttpOnly(){
		$this->httponly=TRUE;
		
		return $this;
	}
	
	/**
	 * 禁用HttpOnly
	 */
	public function disableHttpOnly(){
		$this->httponly=FALSE;
		
		return $this;
	}
	
	/**
	 * 启用secure
	 */
	public function enableSecure(){
		$this->secure=TRUE;
		
		return $this;
	}
	
	/**
	 * 禁用secure
	 */
	public function disableSecure(){
		$this->secure=FALSE;
		
		return $this;
	}
	
	/**
	 * 设置cookie作用路径
	 * 
	 * 需要在相应目录下测试有效
	 */
	public function setPath($path){
		$this->path=$path;
		
		return $this;
	}
	
	/**
	 * 设置cookie作用域名，默认当前host
	 * 
	 * 需要在相应域名下测试有效
	 */
	public function setDomain($domain){
		$this->domain=$domain;
		
		return $this;
	}
	
	/**
	 * 启用P3P
	 */
	public function enableP3P(){
		$this->sendP3P=TRUE;
		
		return $this;
	}
	
	/**
	 * 禁用P3P
	 */
	public function disableP3P(){
		$this->sendP3P=FALSE;
		
		return $this;
	}
	
	/**
	 * 设置P3P
	 * @param unknown $p3p
	 */
	public function setP3P($p3p){
		$this->p3p=$p3p;
		
		return $this;
	}
	
	/**
	 * 生成一个实例
	 */
	public static function create(){
		return new self();
	}
}