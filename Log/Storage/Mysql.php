<?php
namespace HuiLib\Log\Storage;

use HuiLib\Db\DbBase;
use HuiLib\Helper\DateTime;
use HuiLib\Db\Query;
use HuiLib\App\Front;
use HuiLib\Helper\Param;
use Model\Table\SystemLog;

/**
 * 日志模块Mysql适配器
 *
 * @author 祝景法
 * @since 2013/11/10
 */
class Mysql extends \HuiLib\Log\LogBase
{
    /**
     * Log内部连接
     */
    protected $driver = NULL;
    
	protected $table=NULL;

	protected function __construct($config)
	{
		$driverConfig = empty ( $config ['driver'] ) ? '' : Front::getInstance()->getAppConfig()->getByKey ( $config ['driver'] );
		if (empty ( $config ['driver'] ) || empty ( $driverConfig )) {
			throw new \HuiLib\Error\Exception ( 'Log mysql driver ini error' );
		}
		
		$this->driver = DbBase::create ( $driverConfig );
		if (! $this->driver instanceof \HuiLib\Db\Adapter\Pdo\PdoBase) {
			throw new \HuiLib\Error\Exception ( 'Log mysql driver initialized failed' );
		}
		
		if (empty ( $config ['table'] ) ) {
			throw new \HuiLib\Error\Exception ( 'Log mysql driver table ini error' );
		}
		$this->table=$config ['table'];
		
		parent::__construct($config);
	}
	
	/**
	 * 增加一条日志信息
	 *
	 * @param string $message
	 */
	public function add($message)
	{
		$logInfo=array('log'=>$message);
		$trace=self::getDebugTrace(2);//过滤两级
		if (!empty($trace)) {//保留最近一条执行路径
			$logInfo['trace']=array_shift($trace);
		}

		$logInstance=SystemLog::create()->createRow();
		$logInstance['UrlNow']=Param::getRequestUrl ();;
		$logInstance['Type']=$this->type;
		$logInstance['Identify']=$this->identify;
		$logInstance['Info']=json_encode($logInfo);
		
		$request=Front::getInstance()->getRequest();
		$logInstance['Package']=$request->getPackageRouteSeg();
		$logInstance['Control']=$request->getControllerRouteSeg();
		$logInstance['Action']=$request->getActionRouteSeg();
		
		$logInstance['Uid']=$this->getLoginUid();
		$logInstance['CreateTime']=DateTime::format();

		$this->buffer[]=$logInstance;
		
		//超出缓存允许长度、超出缓存生命期输出到磁盘
		if (count($this->buffer)>self::MAX_BUFFER_NUM || time()-$this->lastFlush>self::FLUSH_INTERVAL){
		    $this->flush();
		}
		
		return $this;
	}
	
	public function flush()
	{
	    if (!$this->buffer) return FALSE;
	    
	    $this->lastFlush=time();
	    return Query::insert($this->table)->batchSaveRows($this->buffer)->exec();
	}
	
	/**
	 * 清除老的日志
	 */
	public function clean()
	{
	    return SystemLog::create()->clean(self::LOG_KEEP_DAYS);
	}

	public function toString()
	{
		return 'mysql';
	}

	public function __destruct()
	{
	    //退出前输出缓存
	    $this->flush();
	}
}