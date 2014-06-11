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
	private $userAgent = '';
	//broswer
	private $broswer = '';
	private static $instance;
	
	const BROWSER_IE = 'IE';
	const BROWSER_CHROME = 'Chrome';
	const BROWSER_SOGOU = 'SogouBrowser';
	const BROWSER_360SAFE = '360Safe';
	const BROWSER_OPERA = 'Opera';
	const BROWSER_SAFARI = 'Safari';
	const BROWSER_FIREFOX='Firefox';

	private function __construct()
	{
		$this->userAgent = Param::server ( 'HTTP_USER_AGENT', Param::TYPE_STRING );
		$this->initBroswer ();
	}

	/**
	 * 是否是爬虫
	 */
	public function isRobot()
	{
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
	 * 处理useragent 设置当前浏览器
	 */
	private function initBroswer()
	{
		//设置UA 大小写不敏感
		if (String::exist ( $this->userAgent, '360SE' )) { //360浏览器
			$this->broswer = self::BROWSER_360SAFE;
		} elseif (String::exist ( $this->userAgent, 'MetaSr' )) { //搜狗浏览器3
			$this->broswer = self::BROWSER_SOGOU;
		} elseif (String::exist ( $this->userAgent, 'MSIE' )) { //IE浏览器
			$this->broswer = self::BROWSER_IE;
		} elseif (String::exist ( $this->userAgent, 'Chrome' )) { //Chrome浏览器
			$this->broswer = self::BROWSER_CHROME;
		} elseif (String::exist ( $this->userAgent, 'Googlebot' )) {
			$this->broswer = 'Googlebot';
		} elseif (String::exist ( $this->userAgent, 'Baiduspider' )) {
			$this->broswer = 'Baiduspider';
		} elseif (String::exist ( $this->userAgent, 'Sogou' )) {
			$this->broswer = 'Sogouspider';
		} elseif (String::exist ( $this->userAgent, 'Opera' )) { //Opera浏览器
			$this->broswer = self::BROWSER_OPERA;
		} elseif (String::exist ( $this->userAgent, 'Safari' )) { //Safari浏览器 包括iPhone
			$this->broswer = self::BROWSER_SAFARI;
		} elseif (String::exist ( $this->userAgent, 'Firefox' )) { //Firefox
			$this->broswer = self::BROWSER_FIREFOX;
		} elseif (String::exist ( $this->userAgent, 'Sosospider' )) {
			$this->broswer = 'Sosospider';
		} elseif (String::exist ( $this->userAgent, 'YoudaoBot' )) {
			$this->broswer = 'YoudaoBot';
		} elseif (String::exist ( $this->userAgent, 'bingbot' )) {
			$this->broswer = 'Bingbot';
		} else {
			$this->broswer = 'Other';
		}
		
		return true;
	}

	/**
	 * 处理useragent 设置当前浏览器
	 */
	public function getBroswer()
	{
		return $this->broswer;
	}

	/**
	 * 是否IE浏览器
	 */
	public function isIE()
	{
		return $this->broswer==self::BROWSER_IE;
	}
	
	/**
	 * 是否360浏览器
	 */
	public function is360()
	{
		return $this->broswer==self::BROWSER_360SAFE;
	}
	
	/**
	 * 是否Chrome浏览器
	 */
	public function isChrome()
	{
		return $this->broswer==self::BROWSER_CHROME;
	}

	/**
	 * 是否Safari浏览器
	 */
	public function isSafari()
	{
		return $this->broswer==self::BROWSER_SAFARI;
	}
	
	/**
	 * 是否Firefox浏览器
	 */
	public function isFirefox()
	{
		return $this->broswer==self::BROWSER_FIREFOX;
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