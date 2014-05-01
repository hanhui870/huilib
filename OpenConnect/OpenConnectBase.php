<?php
namespace HuiLib\OpenConnect;

use HuiLib\App\Request\RequestBase;
use HuiLib\Loader\AutoLoaderException;
use HuiLib\Helper\String;
use HuiLib\Helper\Utility;
use HuiLib\Helper\Param;

/**
 * 开放平台账号登录基础类
 * 
 * @rule 
 *      1、相同UID单个平台智能绑定一个账号；
 *      2、同个用户可以绑定不同平台的多个账号。
 *      
 * @author 祝景法
 * @date 2013/06/04
 */
abstract class OpenConnectBase extends \module\base {
	//使用的平台
	const PLATFORM='';
	const PLATFORM_NAME='';
	
	//使用的传输格式
	const FORMAT='json';
	
	//基础请求
	const BASE_URL = '';
	
	//授权地址
	const AUTHORIZE_URL = '';
	
	//获取access_token地址
	const ACCESS_TOKEN_URL = '';
	
	//获取token信息网址
	const TOKEN_INFO_URL = '';
	
	const REVOKE_TOKEN_URL = '';
	const UPGRADE_TOKEN_URL = '';
	
	//当前用户开放平台信息
	protected $access_token=NULL;
	protected $expire=NULL;
	protected $openid=NULL;

	/**
	 * 开放平台配置
	 */
	protected $config;
	
	public function getAuthReturnUrl(){
	    if (!isset($this->config['return_url'])) {
	        throw new \Exception("OAuth platform config: connect.return_url needed.");
	    }
	    return $this->config['return_url']. (strripos($this->config['return_url'], '?')===FALSE ? '?' : '&' ). 'platform='.static::PLATFORM;
	}


	/**
	 * 获取APP ID
	 */
	protected function getAppId(){
	    if (!isset($this->config[static::PLATFORM]['app_id'])) {
	        throw new \Exception("OAuth platform config: connect.".static::PLATFORM.".app_id needed.");
	    }
	    return $this->config[static::PLATFORM]['app_id'];
	}
	
	/**
	 * 获取APP Secret
	 */
	protected function getAppSecret(){
	    if (!isset($this->config[static::PLATFORM]['app_secret'])) {
	        throw new \Exception("OAuth platform config: connect.".static::PLATFORM.".app_secret needed.");
	    }
	    return $this->config[static::PLATFORM]['app_secret'];
	}
	
	/**
	 * 生成安全校验码
	 */
	protected function geneState(){
	    $_SESSION['tmpOpenConnectState']=Utility::geneRandomHash(32);
	    return $_SESSION['tmpOpenConnectState'];
	}
	
	/**
	 * 检验安全校验码
	 */
	public function checkState(){
	    $state=Param::get('state', Param::TYPE_STRING);
	   if (!isset($_SESSION['tmpOpenConnectState']) || $_SESSION['tmpOpenConnectState']!=$state) {
	       return FALSE;
	   }
	   
	   return TRUE;
	}

	/**
	 * 设置对象开放平台信息
	 * @return boolean
	 * @tip 类似百度初次返回是没有OpenID的
	 */
	public function setOpenInfo($openInfo){
		if (empty($openInfo['AccessToken']) || empty($openInfo['Expire'])) {
			return false;
		}
		$this->access_token = $openInfo ['AccessToken'];
		$this->expire = $openInfo ['Expire'];
		if (!empty($openInfo ['OpenId'])) {
			$this->openid = $openInfo ['OpenId'];
		}else{
			$this->openid = 0;
		}

		return $this;
	}
	
	/**
	 * 获取Adapter对象
	 * 
	 * @param string $platform
	 * @param array $config 全局connect配置
	 * @return OpenConnectBase
	 */
	public static function getAdapter($platform, $config)
	{
	   $class='\\HuiLib\\OpenConnect\\Platform\\'.RequestBase::mapRouteSegToClass($platform);
	   
	   try {
	       $adapter=new $class();
	       $adapter->config=$config;
	       
	       return $adapter;
	   }catch (AutoLoaderException $e){
	       return NULL;
	   }
	}
	
	/**
	 * 生成请求网址
	 * @param string $api Api接口
	 * @param array $param 请求参数，为Get时需要
	 */
	abstract protected function bulidUrl($api, $param = array());
	
	/**
	 * 获取用户资料
	 * @param mix $openid
	 */
	abstract function getUserProfile($openid);
	
	/**
	 * 通过AuthorizeCode获取AccessToken
	 * @param string $code
	 * @return 成功返回user_connect表绑定信息，失败Uid字段为0
	 */
	abstract function getAccessToken($code);
	
	/**
	 * 获取认证地址
	 */
	abstract function getAuthorizeUrl();
	
	/**
	 * 发送一个Get请求
	 * @param string $url 请求网址
	 * @param array $param 请求参数
	 */
	protected function getUrl($url, $param=array()){
	    if ($param && is_array($param)) {
	        $url=$url. ( String::exist($url, '?') ? '&' : '?' ) . http_build_query($param);
	    }
	
	    return $this->request($url);
	}
	
	/**
	 * 发送一个Post请求
	 * @param string $url 请求网址
	 * @param array $post 请求的数据
	 */
	protected function postUrl($url, $post){
	    return $this->request($url, 'post', $post);
	}
	
	/**
	 * 发送HTTP请求
	 * @param string $url 请求网址
	 * @param string $method 请求方法
	 * @param array $post 发送数据
	 * @param array $header HTTP头
	 */
	protected function request($url, $method='get', $post=array(), $header = array()){
	    // 创建一个cURL资源
	    $ch = curl_init ();
	
	    // 设置URL和相应的选项
	    $opt = array ();
	    $opt [CURLOPT_URL] = $url;
	
	    //请求方法
	    if ($method=='post') {
	        $opt [CURLOPT_POST] = true;
	    }
	
	    $urlInfo=parse_url($url);
	    if ($urlInfo['scheme']=='https') {
	        //HTTPS请求配置 必须的
	        $opt[CURLOPT_PROTOCOLS]=CURLPROTO_HTTPS;
	        $opt [CURLOPT_SSL_VERIFYPEER] = false;
	        $opt [CURLOPT_SSL_VERIFYHOST] = false;
	    }
	
	    if ($post) {
	        $opt [CURLOPT_POSTFIELDS] = http_build_query($post);
	    }
	
	    // 二进制数据
	    $opt [CURLOPT_BINARYTRANSFER] = true;
	
	    // 返回数据形式
	    $opt [CURLOPT_RETURNTRANSFER] = true;
	
	    // curl操作超时时间 秒
	    $opt [CURLOPT_TIMEOUT] = 5;
	
	    // 发起连接 秒
	    $opt [CURLOPT_CONNECTTIMEOUT] = 1;
	
	    // 重定向 qq图片中存在这个情况
	    $opt [CURLOPT_FOLLOWLOCATION] = 1;
	    $opt [CURLOPT_MAXREDIRS] = 3;
	
	    curl_setopt_array ( $ch, $opt );
	
	    // 抓取URL并把它传递给浏览器
	    $content = curl_exec ( $ch );
	
	    // 关闭cURL资源，并且释放系统资源
	    curl_close ( $ch );
	
	    return $content;
	}
}