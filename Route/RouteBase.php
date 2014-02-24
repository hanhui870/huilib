<?php
namespace HuiLib\Route;

/**
 * 路由模块基类
 *
 * 二级目录、短链路由、子域名、拓展域名路由共用接口
 * 采用抽象方法定义共同接口
 * 
 * 二级目录短链在Package层处理，在RequestBase类触发 如/zjgs
 * 三级目录短链在Controller成处理，在Controller类触发 如/discuss/how-to-enjoy-zjgsdx /topic/2
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
