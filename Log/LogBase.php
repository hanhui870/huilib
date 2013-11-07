<?php
namespace HuiLib\Log;

/**
 * File基础类
 *
 * @author 祝景法
 * @since 2013/10/18
 */
class LogBase
{
	/**
	 * PHP语言相关错误
	 */
	const TYPE_PHPERROR='PHPError';
	
	/**
	 * 运行时错误
	 */
	const TYPE_RUNTIME='Runtime';
	
	/**
	 * DAEMON执行的相关人物
	 */
	const TYPE_DAEMON='Daemon';
	
	/**
	 * 数据库相关人物
	 */
	const TYPE_DBERROR='DBError';
	
	/**
	 * 用户行为相关人物
	 */
	const TYPE_USERERROR='UserError';
	
	protected function __construct($config)
	{
	
	}
	
	
}