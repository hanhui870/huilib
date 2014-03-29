<?php
namespace HuiLib\OpenConnect\Platform;

/**
 * 腾讯QQ账号登录类
 * @author 祝景法
 * @date 2013/06/04
 */
class QQ extends \HuiLib\OpenConnect\OpenConnectBase {
	const PLATFORM = 'qq';
	const PLATFORM_NAME='QQ';
	const BASE_URL = 'https://graph.qq.com/';
	const AUTHORIZE_URL = 'https://graph.qq.com/oauth2.0/authorize';
	const ACCESS_TOKEN_URL = 'https://graph.qq.com/oauth2.0/token';
	const TOKEN_INFO_URL = 'https://graph.qq.com/oauth2.0/me';
	const REVOKE_TOKEN_URL = '';
	const UPGRADE_TOKEN_URL = '';

	/**
	 * 返回授权地址
	 */
	public function getAuthorizeUrl() {
		$param = array ();
		$param ['client_id'] = self::AppID;
		$param ['response_type'] = 'code';
		$param ['redirect_uri'] = $this->getAuthReturnUrl ();
		$param ['state'] = time ();
		return self::authorizeUrl . '?' . http_build_query ( $param );
	}

	/**
	 * 通过AuthorizeCode获取AccessToken
	 * @param string $code
	 */
	public function getAccessToken($code) {
		$param = array ();
		$param ['client_id'] = self::AppID;
		$param ['client_secret'] = self::AppSecret;
		$param ['grant_type'] = 'authorization_code';
		$param ['redirect_uri'] = $this->getAuthReturnUrl ();
		$param ['code'] = $code;
		//access_token=YOUR_ACCESS_TOKEN&expires_in=3600
		$response = $this->getUrl ( self::accessTokenUrl, $param );
		@parse_str ( $response, $result );

		if (! empty ( $result ['access_token'] ) && ! empty ( $result ['expires_in'] )) {
			$openInfo = array ();
			$openInfo ['platform'] = self::platform;
			$openInfo ['access_token'] = $result ['access_token'];
			$openInfo ['expire'] = $result ['expires_in'];
			$openInfo ['refresh_token'] = $result ['refresh_token'];
			
			$param = array ();
			$param ['access_token'] = $openInfo ['access_token'];
			$response = $this->getUrl ( self::tokenInfoUrl, $param );
			
			//callback( {"client_id":"YOUR_APPID","openid":"YOUR_OPENID"} ); 
			$response=trim(str_ireplace(array('callback(', ');'), '', $response));
			$tokenInfo=@json_decode($response, true);
			if (empty($tokenInfo['openid'])) {
				return false;;
			}
			$openInfo ['openid'] = $tokenInfo ['openid'];
			
			return $this->getLocalUser ( $openInfo );
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
		//是指AppID
		$param ['oauth_consumer_key'] = self::AppID;
		$param ['openid'] = $openid;

		$result = $this->getUrl ( $this->bulidUrl ( 'user/get_user_info', $param ) );
		$result=@json_decode($result, true);
		
		$userInfo=array('name'=>$result['nickname'], 'profile'=>$result);
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