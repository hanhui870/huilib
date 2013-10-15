<?php
namespace HuiLib\Helper;

/**
 * 浏览器UserAgent辅助类
 *
 * @author 祝景法
 * @since 2013/10/14
 */
class UserAgent
{
	//UA副本
	private $userAgent='';
	private static $instance;

	private function __construct(){
		$this->userAgent=Param::server('HTTP_USER_AGENT', Param::TYPE_STRING);
	}
	
	/**
	 * 是否是爬虫
	 */
	public function isRobot() {
		$kw_spiders = 'Bot|Spider|Crawl|search';
		$kw_browsers = 'MSIE|360SE|Chrome|MetaSr|Safari|Opera|Mozilla';
		if (strpos ( $this->userAgent, 'http://' ) === FALSE && preg_match ( "/($kw_browsers)/i", $this->userAgent )) {
			$isRobot = 0;
		} elseif (preg_match ( "/($kw_spiders)/i", $this->userAgent )) {
			$isRobot = 1;
		} else {
			$isRobot = 0;
		}
		
		return $isRobot;
	}
	
	/**
	 * 是否IE浏览器
	 */
	public function isIE() {
		return preg_match('/MSIE/', $this->userAgent);
	}
	
	/**
	 * 获取UA单例
	 */
	public static function getInstance()
	{
		if (self::$instance == NULL) {
			self::$instance = new self ();
		}
		return self::$instance;
	}
}