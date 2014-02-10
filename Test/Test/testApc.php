<?php
/**
 * 测试Apc在Cli情况下的储值情况
 */

//var_dump(apc_store('hello',  'hanhui:'.date("y-m-d h:i:s")));

//经测试Apc在Cli环境下不能跨启动保存缓存，因为每次运行完毕全部缓存清空了。和预测一致。
var_dump(apc_fetch('hello')) ;

