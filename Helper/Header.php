<?php
namespace HuiLib\Helper;

/**
 * Header相关对象
 *
 * @author 祝景法
 * @since 2013/09/19
 */
class Header
{
	const CODE_301 = "HTTP/1.1 301 Moved Permanently";
	const CODE_302 = "HTTP/1.1 302 Found";
	const CODE_304 = "HTTP/1.1 304 Not Modified";
	const CODE_403 = "HTTP/1.1 403 Forbidden";
	const CODE_404 = "HTTP/1.1 404 Not Found";
	const CODE_500 = "HTTP/1.1 500 Internal Server Error";

	/**
	 * 链接重定向
	 * @param string $location 新网址
	 * @param string $code 响应状态码
	 */
	public static function redirect($location, $code = self::CODE_301)
	{
		header ( $code );
		header ( "Location: $location" );
		die();
	}
}