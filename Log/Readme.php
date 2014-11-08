<?php
/**
 * HuiLib Log库操作指南
 * 
 * @since 2013/11/10
 * 
 * Log支持储存后端：File, Mysql, Mongo
 * 
 * 适配器接口：
 * 		add($info)：添加一条日志信息
 */
use HuiLib\Log\LogBase;

//快速获取一个Apc Log实例
$cache=LogBase::getMysql();

//快速获取一个File Log缓存实例
$cache=LogBase::getFile();

//快速获取一个Memcache Log缓存实例
$cache=LogBase::getMongo();

//File完整示例 支持批量保存多条，但必须在同个log实例
$log=LogBase::getFile();
$log->setType(LogBase::TYPE_USERERROR);
$log->setIdentify('PHPFrameTest');//会放置到文件名中作为区分
$log->add('sorry, db falied');
$log->add('sorry, db falied');
$log->add('sorry, db falied');

//Mysql完整示例 支持批量保存多条，但必须在同个log实例
//2014-05-25.Up201405.Runtime.log
$log=LogBase::getMysql();
$log->setType(LogBase::TYPE_USERERROR);
$log->setIdentify('PHPFrameTest');
$log->add('sorry, db falied');
$log->add('sorry, db falied');
$log->add('sorry, db falied');