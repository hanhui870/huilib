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
	const CODE_200 = "HTTP/1.1 200 OK";
	const CODE_201 = "HTTP/1.1 201 Created";
	const CODE_202 = "HTTP/1.1 202 Accepted";
	const CODE_301 = "HTTP/1.1 301 Moved Permanently";
	const CODE_302 = "HTTP/1.1 302 Found";
	const CODE_304 = "HTTP/1.1 304 Not Modified";
	const CODE_400 = "HTTP/1.1 400 Bad Request";
	const CODE_403 = "HTTP/1.1 403 Forbidden";
	const CODE_404 = "HTTP/1.1 404 Not Found";
	const CODE_423 = "HTTP/1.1 423 Locked";
	const CODE_500 = "HTTP/1.1 500 Internal Server Error";
	const CODE_502 = "HTTP/1.1 502 Bad Gateway";
	const CODE_503 = "HTTP/1.1 503 Service Unavailable";
	
	const OK=200;
	const CREATED=201;
	const ACCEPTED=202;
	const MOVED_PERMANENTLY=301;
	const FOUND=302;
	const NOT_MODIFIED=304;
	const BAD_REQUEST=400;
	const FORBIDDEN=403;
	const NOT_FOUND=404;
	const LOCKED=423;
	const INTERNAL_SERVER_ERROR=500;
	const BAD_GATEWAY=502;
	const SERVICE_UNAVAILABLE=503;

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