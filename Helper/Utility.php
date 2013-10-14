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
	
	
}