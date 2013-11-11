<?php
namespace HuiLib\Helper;

/**
 * 验证器函数库
 *
 * @author 祝景法
 * @since 2013/10/20
 */
class Validator
{
	/**
	 * 是否为邮箱
	 * 仅.com/.cn/.net/.org/.edu/.info/.me等后缀邮箱。
	 */
	static function isEmail($string) {
		preg_match ( "/\w+?\@(?:[\w]+?\.)+(?:com|cn|net|org|edu|info|me)/i", $string, $match );
		if (empty ( $match [0] ) || $match [0] != $string) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 全部字母检测
	 */
	static function allCharacter($string) {
		if (empty ( $string ))
			return false;
	
		preg_match ( '/[a-zA-z]+/i', $string, $match );
		if (! empty ( $match [0] ) && $match [0] == $string) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 全部数字检测
	 */
	static function allNumber($string) {
		if (empty ( $string ))
			return false;
	
		preg_match ( '/[0-9]+/i', $string, $match );
		if (! empty ( $match [0] ) && $match [0] == $string) {
			return true;
		} else {
			return false;
		}
	}
}