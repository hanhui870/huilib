<?php
namespace HuiLib\OpenConnect\Platform;

/**
 * 百度账号登录类
 * 
 * @author 祝景法
 * @date 2013/06/16
 */
class Baidu extends \HuiLib\OpenConnect\OpenConnectBase {
	const PLATFORM = 'baidu';
	const PLATFORM_NAME='百度';
	const BASE_URL = 'https://openapi.baidu.com/rest/2.0/';
	const AUTHORIZE_URL = 'https://openapi.baidu.com/oauth/2.0/authorize';
	const ACCESS_TOKEN_URL = 'https://openapi.baidu.com/oauth/2.0/token';
	const TOKEN_INFO_URL = 'https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser';
	const REVOKE_TOKEN_URL = 'https://openapi.baidu.com/oauth/2.0/token';
	const UPGRADE_TOKEN_URL = '';

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
		//access_token=YOUR_ACCESS_TOKEN&expires_in=3600
		$response = $this->getUrl ( self::ACCESS_TOKEN_URL, $param );
		$result = @json_decode ( $response, true );
		
		if (! empty ( $result ['access_token'] ) && ! empty ( $result ['expires_in'] )) {
			$openInfo = array ();
			$openInfo ['Platform'] = self::PLATFORM;
			$openInfo ['AccessToken'] = $result ['access_token'];
			$openInfo ['Expire'] = $result ['expires_in'];
			$openInfo ['RefreshToken'] = $result ['refresh_token'];

			//获取当前登录用户信息
			$this->setOpenInfo($openInfo);
			$response = $this->getUserProfile();

			if (empty($response['uid'])) {
				return false;;
			}
			$openInfo ['OpenId'] = $response ['uid'];
			
			return $openInfo;
		} else {//失败
			return false;
		}
	}

	/**
	 * 获取用户资料信息
	 * @param int $openid 为空时表示当前登录用户
	 */
	public function getUserProfile($openid=0) {
		$param = array ();
		$param ['access_token'] = $this->access_token;
		if ($openid) {
			$param ['uid'] = $openid;
		}

		$result = $this->getUrl ( $this->bulidUrl ( 'passport/users/getInfo', $param ) );
		$result=@json_decode($result, true);
		
		if (empty($result['userid'])) {
			return false;
		}
		
		$userInfo=array();
		$userInfo['name']=!empty($result['username']) ? $result['username']: $result['realname'];
		$userInfo['uid']=$result['userid'];
		$userInfo['profile']=$result;
		return $userInfo;
	}
	
	/**
	 * 生成请求网址
	 * @param string $api Api接口
	 * @param array $param 请求参数，为Get时需要
	 */
	protected function bulidUrl($api, $param = array()) {
		return static::BASE_URL . $api . ($param ? '?'.http_build_query ( $param ) : '');
	}
}