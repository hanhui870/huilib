<?php
namespace HuiLib\Request;

/**
 * BIN Request类
 *
 * @author 祝景法
 * @since 2013/08/14
 */
class Bin extends RequestBase
{
	protected $scriptUrl=NULL;
	
	public function init(){
		//TODO 需要根据启动参数获取路由参数
		$this->scriptUrl = Param::getScriptUrl();

		/**
		 * url路由处理
		*/
		$this->urlRoute ();
	}
}