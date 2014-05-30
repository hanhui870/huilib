<?php
namespace HuiLib\Helper;

/**
 * 字符串辅助函数
 *
 * @author 祝景法
 * @since 2013/08/11
 */
class String
{
	/**
	 * 截取输出是否添加逗点
	 */
	const DOT_ENDING = true;
	const DOT_ENDING_NO = false;
	
	/**
	 * 截取输出是否HTML编码
	 */
	const HTML_ENCODE = true;
	const HTML_ENCODE_NO = false;
	
	/**
	 * 默认编码设置
	 */
	private static $defalutCharset = "utf-8";

	public static function substr($string, $start, $length, $addDot = self::DOT_ENDING_NO, $htmlEncode = self::HTML_ENCODE_NO)
	{
		if ($htmlEncode)
			$str = self::htmlEncode ( $string );
		$string = mb_substr ( $string, $start, $addDot ? $length - 3 : $length, self::$defalutCharset ) . ($addDot && mb_strlen ( $string, self::$defalutCharset ) > $length ? '...' : '');
		if ($htmlEncode)
			$string = self::htmlDecode ( $string );
		return $string;
	}

	/**
	 * 是否存在某个子串
	 * @param string $string 字符串
	 * @param string $find 查找的子串
	 * @return boolean
	 */
	public static function exist($string, $find)
	{
		return stripos ( $string, $find ) !== FALSE;
	}

	/**
	 * 编码敏感的字符串长度计数
	 * 另类，数字、字母、汉字都算一个字节
	 * 
	 * @param string $string 字符串
	 */
	public static function strlen($string)
	{
		return mb_strlen ( $string, self::$defalutCharset );
	}

	/**
	 * 批量移除转义
	 * @param mix $string
	 */
	public static function stripslashes($string)
	{
		if (is_array ( $string )) {
			foreach ( $string as $k => $v ) {
				$string [$k] = self::stripslashes ( $v );
			}
		} else {
			$string = stripslashes ( $string );
		}
		
		return $string;
	}

	/**
	 * 批量添加转义
	 * @param mix $string
	 */
	public static function addslashes($string)
	{
		if (is_array ( $string )) {
			foreach ( $string as $k => $v ) {
				$string [$k] = self::addslashes ( $v );
			}
		} else {
			$string = addslashes ( $string );
		}
		return $string;
	}

	/**
	 * 标准化文件大小
	 * 输入Byte 统一成KB MB GB
	 */
	public static function formatByteSize($size)
	{
		$size = intval ( $size );
		if ($size > 1024 * 1024 * 1024) {
			return number_format ( $size / (1024 * 1024 * 1024), 2 ) . 'GB';
		} elseif ($size > 1024 * 1024) {
			return number_format ( $size / (1024 * 1024), 2 ) . 'MB';
		} elseif ($size > 1024) {
			return number_format ( $size / (1024), 2 ) . 'KB';
		} else {
			return $size . 'B';
		}
	}

	/**
	 * html实体解码
	 * 会匹配到所有都被替换；若只需一次，请使用htmlspecialchars函数。
	 * 
	 * @param mix $string
	 */
	public static function htmlDecode($string)
	{
		if (is_array ( $string )) {
			foreach ( $string as $key => $iterString ) {
				$string [$key] = self::htmlDecode ( $iterString );
			}
		} else {
			$string = str_ireplace ( array ('&amp;', '&quot;', '&#039;', '&lt;', '&gt;' ), array ('&', '"', '\'', '<', '>' ), $string );
		}
		return $string;
	}

	/**
	 * Html代码过滤
	 * 多次调用导致&会被编码多次， 处理&, ", ', <, >
	 * 
	 * @param mix $string
	 */
	public static function htmlEncode($string)
	{
		if (is_array ( $string )) {
			foreach ( $string as $key => $iterString ) {
				$string [$key] = self::htmlEncode ( $iterString );
			}
		} else {
			$string = str_ireplace ( array ('&', '"', '\'', '<', '>' ), array ('&amp;', '&quot;', '&#039;', '&lt;', '&gt;' ), $string );
		}
		return $string;
	}

	/**
	 * 递归转换字符串编码
	 * 
	 * @param mix $string
	 * @param string $string 输入的编码
	 * @param string $string 输出的编码
	 */
	public static function iconv($string, $inCharset, $outCharset=NULL)
	{
		if ($outCharset===NULL) {
			$outCharset=self::$defalutCharset;
		}
		if ( is_array ( $string )) {
			foreach ( $string as $key => $iterString ) {
				if (is_array ( $iterString )) {
					unset ( $string [$key] );
					$key = @iconv ( $inCharset, $outCharset, $key );
					$iterString = self::iconv( $iterString, $inCharset, $outCharset );
					$string [$key] = $iterString;
				} else {
					unset ( $string [$key] );
					$key = @iconv ( $inCharset, $outCharset, $key );
					$string [$k] = @iconv ( $inCharset, $outCharset, $iterString );
				}
			}
		}elseif (is_string ( $string )){
			return @iconv ( $inCharset, $outCharset, $string );
		}

		return $string;
	}
	
	/**
	 * 设置默认编码
	 * @param unknown $charset
	 */
	public static function setDefaultCharset($charset)
	{
		self::$defalutCharset=$charset;
	}
	
	public static function getDefaultCharset()
	{
		return self::$defalutCharset;
	}
}