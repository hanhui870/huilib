<?php
namespace HuiLib\Route;

/**
 * 路由模块基类
 *
 * 二级目录、短链路由、子域名、拓展域名路由共用接口
 * 采用抽象方法定义共同接口
 * 
 * @author 祝景法
 * @since 2013/09/15
 */
abstract class RouteBase
{
	
	public abstract function route()
	{
		
	}
}
