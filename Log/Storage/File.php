<?php
namespace HuiLib\Log\Storage;

use HuiLib\App\Front;

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
	private function getFileFd()
	{
		if ($this->fileFd!==NULL && date('d', $this->startTime)==date('d')) {
			return $this->fileFd;
		}
		
		if ($this->type===NULL) {
			throw new \HuiLib\Error\Exception ( 'Please set Log Type firstly.' );
		}
		if (!$this->startTime || date('d', $this->startTime)!=date('d')){
		    $this->startTime=time();
		}
		
		$pathAdd=$this->identify ? '.'.$this->identify :'';
		$file=$this->filePath.date('Y-m-d', $this->startTime).$pathAdd.'.'.$this->type.'.log';

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
			chmod($file, 0666);
		}
		return $this->fileFd;
	}
	
	/**
	 * 增加一条日志信息
	 *
	 * @param string $message
	 * @param bool $trace 是否添加调试信息
	 */
	public function add($message, $isTrace=FALSE)
	{
	    //初始化储存文件
		$this->getFileFd();
		
		$logInfo=array();
		$mtime=number_format(microtime(1), 4, '.', '');
		$logInfo['Time']=date("[H:i:s").substr($mtime, strrpos($mtime, '.'));
		
		$request=Front::getInstance()->getRequest();
		if ($request->getPackageRouteSeg() && $request->getControllerRouteSeg()) {
		    $logInfo['Route']=" {$request->getPackageRouteSeg()}/{$request->getControllerRouteSeg()}/{$request->getActionRouteSeg()}";
		}
		$logInfo['Append']=']:';
		
		if (!is_string($message)) {
		    if (is_array($message)) {
		        $message=json_encode($message);
		    }else{
		        $message=var_export($message, TRUE);
		    }
		}
		$logInfo['Info']=" $message";
		
		if ($isTrace) {
		    $trace=self::getDebugTrace(2);//过滤两级
		    $traceInfo=array('file'=>'', 'line'=>'');
		    if (!empty($trace)) {//保留最近一条执行路径
		        $traceInfo=array_shift($trace);
		    }
		    $logInfo['Trace']=" Trace::$traceInfo[file]:$traceInfo[line]";
		}
		$logInfo['End']=PHP_EOL;
		
		$log= implode('', $logInfo);
		$this->buffer[]=$log;
		
		//超出缓存允许长度、超出缓存生命期输出到磁盘
		if (count($this->buffer)>self::MAX_BUFFER_NUM || time()-$this->lastFlush>self::FLUSH_INTERVAL){
		    $this->flush();
		}
		
		return $this;
	}
	
	/**
	 * 刷入磁盘
	 */
	public function flush()
	{
	    if (!$this->buffer) return FALSE;
	    
	    $this->lastFlush=time();
	    fwrite($this->fileFd, implode('', $this->buffer));
	    $rows=count($this->buffer);
	    $this->buffer=array();
	    
	    return $rows;
	}
	
	/**
	 * 清除老的日志
	 */
	public function clean()
	{
	    $files=glob($this->filePath.'*.log');
	    if (!$files) return false;
	
	    //print_r($files);
	    foreach ($files as $file){
	        $mtime=filemtime($file);
	        if (time()-$mtime>self::LOG_KEEP_DAYS*86400){//三个月
	            @unlink($file);
	        }
	    }
	    return true;
	}
	
	public function toString(){
		return 'file';
	}
	
	public function __destruct()
	{
	    //退出前输出缓存
	    $this->flush();
	
	    if ($this->fileFd){
	        fclose($this->fileFd);
	    }
	}
}