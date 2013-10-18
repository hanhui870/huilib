<?php
namespace HuiLib\Log\Storage;

/**
 * File基础类
 *
 * @author 祝景法
 * @since 2013/09/15
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