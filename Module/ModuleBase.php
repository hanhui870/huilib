<?php
namespace HuiLib\Module;

use HuiLib\App\Front;

/**
 * Module基础类
 * 
 * @author 祝景法
 * @since 2013/09/20
 */
class ModuleBase
{
	//Api状态返回
	const API_SUCCESS=TRUE;
	const API_FAIL=FALSE;
	
	protected function __construct()
	{
	}
	
	/**
	 * 输出JSON数据
	 *
	 * @param boolean $status
	 * @param string $message 返回代码
	 * @param int $extra array()  请求相关的额外状态数据
	 * @param mix $data 返回数据
	 */
	protected function format($status=self::API_SUCCESS, $message='', $extra=array(), $data=array())
	{
		$result=array();
	
		$result['success']=$status;
		$result['message']=$message;
		$result['extra']=$extra;
		$result['data']=$data;
	
		return $result;
	}
	
	/**
	 * 获取翻译实例
	 */
	protected function getLang()
	{
		return Front::getInstance()->getLang();
	}
	
	/**
	 * 初始化网站配置实例
	 */
	protected function getSiteConfig()
	{
		return Front::getInstance()->getSiteConfig();
	}
	
	/**
	 * 快速创建一个Module实例
	 * 
	 * 原理：创建对象的灵活获取参数是从第0个开始的。一般函数是从第一个开始的。
	 * 
	 * @param mix $param 支持传递参数，最多5个，其他空字符串代替
	 */
	static function create(){
		
		$paramCount=func_num_args();
		
		if (!$paramCount) {
			return new static();
		}else{
			$params=func_get_args();
			//最多5个，其他空字符串代替
			list($param1, $param2, $param3, $param4, $param5)=array_pad($params, 5, '');
			
			return new static($param1, $param2, $param3, $param4, $param5);
		}
	}
	
}
