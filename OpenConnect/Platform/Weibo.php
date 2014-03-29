<?php
namespace HuiLib\OpenConnect\Platform;

/**
 * 新浪微博账号登录类
 * @author 祝景法
 * @date 2013/06/04
 */
class Weibo extends \HuiLib\OpenConnect\OpenConnectBase {
	const PLATFORM = 'weibo';
	const PLATFORM_NAME='微博';
	
	const BASE_URL = 'https://api.weibo.com/2/';
	const AUTHORIZE_URL = 'https://api.weibo.com/oauth2/authorize';
	const ACCESS_TOKEN_URL = 'https://api.weibo.com/oauth2/access_token';
	const TOKEN_INFO_URL = 'https://api.weibo.com/oauth2/get_token_info';
	const REVOKE_TOKEN_URL = 'https://api.weibo.com/oauth2/revokeoauth2';
	const UPGRADE_TOKEN_URL = 'https://api.weibo.com/oauth2/get_oauth2_token';

	/**
	 * 返回授权地址
	 */
	public function getAuthorizeUrl() {
		$param = array ();
		$param ['client_id'] = $this->getAppId();
		$param ['response_type'] = 'code';
		$param ['redirect_uri'] = $this->getAuthReturnUrl ();
		$param ['state'] = $this->geneState();
		return self::AUTHORIZE_URL . '?' . http_build_query ( $param );
	}

	/**
	 * 通过AuthorizeCode获取AccessToken
	 * @param string $code
	 */
	public function getAccessToken($code) {
		$param = array ();
		$param ['client_id'] = $this->getAppId();
		$param ['client_secret'] = $this->getAppSecret();
		$param ['grant_type'] = 'authorization_code';
		$param ['redirect_uri'] = $this->getAuthReturnUrl ();
		$param ['code'] = $code;
		$response = $this->postUrl ( self::ACCESS_TOKEN_URL, $param );
		$result = @json_decode ( $response, true );
		
		if (! empty ( $result ['access_token'] ) && ! empty ( $result ['expires_in'] ) && ! empty ( $result ['uid'] )) {
			$openInfo = array ();
			$openInfo ['Platform'] = self::PLATFORM;
			$openInfo ['AccessToken'] = $result ['access_token'];
			$openInfo ['Expire'] = $result ['expires_in'];
			$openInfo ['OpenId'] = $result ['uid'];
			
			return $openInfo;
		} else {//失败
			return false;
		}
	}

	/**
	 * 获取用户资料信息
	 */
	public function getUserProfile($openid) {
		$param = array ();
		$param ['access_token'] = $this->access_token;
		$param ['uid'] = $openid;

		$result = $this->getUrl ( $this->bulidUrl ( 'users/show', $param ) );
		$result=@json_decode($result, true);
		
		if (empty($result['name'])) {
		    return false;
		}
		
		//最后一条微博动态不要，占空间
		unset($result['status']);
		
		$userInfo=array('name'=>$result['name'], 'profile'=>$result);
		return $userInfo;
	}
	
	/**
	 * 生成请求网址
	 * @param string $api Api接口
	 * @param array $param 请求参数，为Get时需要
	 */
	protected function bulidUrl($api, $param = array()) {
		return static::BASE_URL . $api . '.' . static::FORMAT . ($param ? '?'.http_build_query ( $param ) : '');
	}
}