<?php
namespace HuiLib\Log\Storage;

/**
 * 日志模块Mongo适配器
 *
 * @author 祝景法
 * @since 2013/11/10
 */
class Mongo extends \HuiLib\Log\LogBase
{
	protected function __construct($config)
	{
		var_dump($config);
	}
	
	public function add($info)
	{
	    
	}
	
	public function toString(){
		return 'mongo';
	}
}