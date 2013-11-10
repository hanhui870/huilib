<?php
namespace HuiLib\Log\Storage;

/**
 * 日志模块File适配器
 * 
 * 没使用Cache接口，直接操作系统接口写入文件
 *
 * @author 祝景法
 * @since 2013/11/10
 */
class File extends \HuiLib\Log\LogBase
{
	/**
	 * 日志文件储存路径
	 * 
	 * @var string
	 */
	protected $filePath=NULL;
	
	/**
	 * 写入日志的操作句柄
	 * @var source 
	 */
	protected $fileFd=NULL;
	
	
	protected function __construct($config)
	{
		if (empty($config['path']) || !is_dir($config['path'])) {
			throw new \HuiLib\Error\Exception ( 'File log path can not aceess.' );
		}
		
		$this->filePath=$config['path'];
	}
	
	/**
	 * 获取Log的储存路径
	 */
	private function iniFileFd()
	{
		if ($this->fileFd!==NULL) {
			return TRUE;
		}
		if ($this->type===NULL) {
			throw new \HuiLib\Error\Exception ( 'Please set Log Type firstly.' );
		}
		$file=$this->filePath.date('Y').SEP.date('m-d').'.'.$this->type.'.log';
		if (file_exists($file)) {
			$this->fileFd=fopen($file, 'ab+');
		}else{
			$dirPath=dirname ( $file );
			if (! is_dir ( $dirPath )) {
				if (! mkdir ($dirPath, 0777, 1 )) {
					throw new \HuiLib\Error\Exception ( "Log File: 目录{$dirPath}程序没有写入权限" );
				}
			}
			$this->fileFd=fopen($file, 'wb+');
		}
		return TRUE;
	}
	
	/**
	 * 增加一条日志信息
	 *
	 * @param string $info
	 * @return int 写入的长度
	 */
	public function add($info)
	{
		$fileFd=$this->iniFileFd();
		
		$logInfo=array();
		$logInfo['Time']=date("[H:i:s]:");
		if ($this->package && $this->controller) {
			$logInfo['Route']="[{$this->package}/{$this->controller}/{$this->action}]:";
		}
		$logInfo['Info']="\"$info\"";
		
		$trace=self::getDebugTrace(2);//过滤两级
		$traceInfo=array('file'=>'', 'line'=>'');
		if (!empty($trace)) {//保留最近一条执行路径
			$traceInfo=array_shift($trace);
		}
		$logInfo['Trace']=" Trace::$traceInfo[file]:$traceInfo[line]\n";
		
		$log= implode('', $logInfo);
		return fwrite($this->fileFd, $log);
	}
	
	public function toString(){
		return 'file';
	}
	
	public function __destruct()
	{
		fclose($this->fileFd);
	}
}