<?php
namespace Lib\Loader;

/**
 * @author 祝景法
 * @date 2013/08/11
 *
 * 类自动加载库
 */

class AutoLoad
{
	/**
	 * 自动加载类
	 * @tip 转发过来的请求形式module\common\core，没有最前面的斜线。
	 */
	public static function loadClass($name)
	{
		echo $name;
		$name=SYS_ROOT.str_replace('\\', SEP, $name).'.php';
	
		if (file_exists($name)){
			include_once $name;
		}else{
			throw new \Exception("file $name doesn't exists, please check!");
		}
	}
	
}