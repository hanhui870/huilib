<?php
namespace Lib;
/**
 * @author 祝景法
 * @date 2013/08/11
 *
 * 系统初始化入口文件
 */

define ( 'SEP', DIRECTORY_SEPARATOR );

/**
 * 相关路径常量设置
 * SYS_ROOT 库根目录
 * APP_ROOT 应用根目录
 * WWW_ROOT 网页根目录
 */
define ( 'SYS_ROOT', dirname( __FILE__ ).SEP);

if (!defined('APP_ROOT') || !defined('WWW_ROOT')){
	throw new Exception("Please define Constant var APP_ROOT & WWW_ROOT  in the entry!");
}

include_once SYS_ROOT.'Loader/AutoLoad.php';
spl_autoload_register("Lib\Loader\AutoLoad::loadClass");

new \Lib\ffdasfdsa\fdfd();