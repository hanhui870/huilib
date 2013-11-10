<?php
namespace HuiLib\Log\Storage;

/**
 * 日志模块File适配器
 *
 * @author 祝景法
 * @since 2013/11/10
 */
class File extends \HuiLib\Log\LogBase
{
	protected function __construct($config)
	{
	
	}
	
	public function toString(){
		return 'file';
	}
}