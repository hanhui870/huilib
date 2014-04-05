<?php
namespace HuiLib\Module\Runtime;

use HuiLib\Helper\Param;

/**
 * 输出控制类
 *
 * @author 祝景法
 * @since 2013/09/24
 */
class Output extends  \HuiLib\Module\ModuleBase
{
	/**
	 * 是否启用Gzip压缩
	 * 
	 * @var boolean
	 */
	protected $gzipEnable=TRUE;
	
	/**
	 * 输出缓存
	 * 
	 * @var string
	 */
	protected $outputBuffer=NULL;
	
	/**
	 * 是否已经输出
	 *
	 * @var boolean
	 */
	protected $outputed=FALSE;
	
	/**
	 * 开启输出控制
	 */
	public function obstart() {
		$encoding = Param::server('HTTP_ACCEPT_ENCODING', Param::TYPE_STRING);
		$accept = in_array ( 'gzip', explode ( ',', $encoding ) );
	
		if ($this->gzipEnable && function_exists ( 'ob_gzhandler' ) && $accept) {
			ob_start ( 'ob_gzhandler' );
		} else {
			$this->gzipEnable = FALSE;
			ob_start ();
		}
	}
	
	/*
	 * 获取缓存内容
	*/
	public function getOutputBuffer() {
		$buffer = ob_get_contents ();
	
		ob_end_clean ();
		$this->obstart();
		
		$this->outputBuffer=$buffer;
		
		return $buffer;
	}
	
	/*
	 * 输出缓存
	*/
	public function sendOutput() {
		//防止多次输出
		if($this->outputed) return FALSE;
		
		//TODO 更新session及相关资料信息

		//DEBUG
		
		if (empty ( $this->outputBuffer )) {
			$this->getOutputBuffer();
		}
		
		//Etag输出
		echo $this->content;
		
		$this->outputed=TRUE;
	}
	
	/**
	 * 启用Gzip输出压缩
	 */
	public function enableGzip()
	{
		$this->gzipEnable=TRUE;
	}
	
	/**
	 * 禁用Gzip输出压缩
	 */
	public function disableGzip()
	{
		$this->gzipEnable=FALSE;
	}
}