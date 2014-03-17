<?php
namespace HuiLib\Route;

/**
 * 路由模块基类
 *
 * 目录层路由、子域名、拓展域名路由共用接口
 * 采用抽象方法定义共同接口
 * 
 * 包层短链在Package层处理，在RequestBase/urlRoute触发 如
 *      /2 => user/view/index
 *      /hanhui => user/view/index
 *      /zjgs => topic/view/index
 * 控制器层短链在Controller层处理，在RequestBase/loadController触发 如
 *      注: 默认路由到view控制器
 *      /discuss/how-to-enjoy-zjgsdx => discuss/view/index?id
 *      /topic/2 => topic/view/index?id
 *      /zjgs/thread => topic/view/thread?id
 *      /hanhui/thread user/view/thread?page=1
 * 动作层短链在Action层处理，在Controller/dispatch触发，如
 *      注: 默认路由到view控制器对应方法
 *      /discuss/2/log => discuss/view/log
 *      /hanhui/thread/2 => user/view/thread?page=2
 * 
 * @author 祝景法
 * @since 2013/09/15
 */
abstract class RouteBase
{
    /**
     * 路由接口
     */
	public abstract function route();
}
