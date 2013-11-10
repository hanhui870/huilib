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

//完整示例
$log=LogBase::getMysql();
$log->setType(LogBase::TYPE_USERERROR);
$log->setIdentify('PHPFrameTest');
$log->add('sorry, db falied');