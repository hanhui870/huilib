<?php
namespace HuiLib\Redis;

use HuiLib\Error\Exception;

/**
 * Redis HashTable基础管理类
 *
 * @author 祝景法
 * @since 2014/01/04
 */
class HashTable extends RedisBase
{
	/**
	 * 表类常量定义
	 * @var string
	 */
	const TABLE_CLASS=NULL;
	
	/**
	 * Redis键前缀，需要和父类的组合
	 * @var string
	 */
	const KEY_PREFIX='hash:table:';
	
	
}