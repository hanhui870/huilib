<?php
/**
 * HuiLib Cache库操作指南
 * 
 * @since 2013/11/10
 * 
 * Cache支持储存后端：Apc, Memcache, Redis
 * 
 * 适配器接口：
 * 		add($key, $value)：强制添加一个缓存
 * 		addnx($key, $value)：添加一个缓存，如果已经存在返回false
 * 		delete($key)：删除一个键
 * 		get($key)：获取一个键的值
 * 		increase($key, $value=1)：给指定键增加一个值
 * 		decrease($key, $value=1)：给缓存键减少一个值
 * 
 * 2013/11/10 取消支持File储存，因为本Cache模块主要定位KV储存
 * File一般是直接写入，跟上面的接口格格不入。如果需要，可以直接file_put_contents。
 */

//快速获取一个Apc缓存实例
$cache=\HuiLib\Cache\CacheBase::getApc();

//快速获取一个Memcache缓存实例
$cache=\HuiLib\Cache\CacheBase::getMemcache();

//快速获取一个Redis缓存实例
$cache=\HuiLib\Cache\CacheBase::getRedis();