<?php
namespace HuiLib\Helper;

/**
 * 通用函数库
 * 
 * 其他没地方放的函数，放这里
 *
 * @author 祝景法
 * @since 2013/09/20
 */
class Utility
{
	/**
	 * 随机生成固定长度的随机串
	 * 
	 * @param int $length 随机串长度
	 */
	public static function geneRandomHash($length=40){
		//生成40位字母和数字组成的随机串
		$charList=array();
		//0-9
		for ($iter=ord('0'); $iter<=ord('9'); $iter++){
			$charList[]=chr($iter);
		}
		//a-z
		for ($iter=ord('a'); $iter<=ord('z'); $iter++){
			$charList[]=chr($iter);
		}

		srand(microtime(1));
		$result=array();
		$charCount=count($charList);
		for ($iter=0; $iter<$length; $iter++){
			$result[]=$charList[rand(0, 999)%$charCount];
		}
		
		return implode('', $result);
	}
	
	/**
	 * 将IP转换为数字按255基础换算。
	 * 
	 *  经测试，php数值可以超过2^32，这里的最大值也小于该值。
	 *  
	 * @param $ip string 四位数字组成的IP
	 * @return 某ip的对应唯一值，忽略没有意义的前缀0位
	 */
	public static function ipToNum($ip) {
		if (empty ( $ip ))
			return 0;
		$ipArray = self::splitIpString ( $ip );
	
		return $ipArray ['ip1'] * 255 * 255 * 255 + $ipArray ['ip2'] * 255 * 255 + $ipArray ['ip3'] * 255 + $ipArray ['ip4'];
	}
	
	/**
	 * 分拆IP
	 * 
	 * param $ip string 四位数字组成的IP
	 */
	public static function splitIpString($ip) {
		if (empty ( $ip ))
			return false;

		$ipNew = array ();
		list ( $ipNew ['ip1'], $ipNew ['ip2'], $ipNew ['ip3'], $ipNew ['ip4'] ) = @explode ( '.', $ip );
		$ipNew=array_map('intval', $ipNew);
		$ipArray [$ip] = $ipNew;
	
		return $ipNew;
	}
}