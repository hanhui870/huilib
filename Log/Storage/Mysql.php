<?php
namespace HuiLib\Log\Storage;

use HuiLib\Db\DbBase;
use HuiLib\Helper\DateTime;
use HuiLib\Db\Query;

/**
 * 日志模块Mysql适配器
 *
 * @author 祝景法
 * @since 2013/11/10
 */
class Mysql extends \HuiLib\Log\LogBase
{
	protected $table=NULL;

	protected function __construct($config, \HuiLib\Config\ConfigBase $configInstance)
	{
		$driverConfig = empty ( $config ['driver'] ) ? '' : $configInstance->getByKey ( $config ['driver'] );
		if (empty ( $config ['driver'] ) || empty ( $driverConfig )) {
			throw new \HuiLib\Error\Exception ( 'Log mysql driver ini error' );
		}
		
		$this->driver = DbBase::create ( $driverConfig );
		if (! $this->driver instanceof \HuiLib\Db\Pdo\PdoBase) {
			throw new \HuiLib\Error\Exception ( 'Log mysql driver initialized failed' );
		}
		
		if (empty ( $config ['table'] ) ) {
			throw new \HuiLib\Error\Exception ( 'Log mysql driver table ini error' );
		}
		$this->table=$config ['table'];
		
		parent::__construct($config, $configInstance);
	}
	
	/**
	 * 增加一条日志信息
	 *
	 * @param string $info
	 */
	public function add($info)
	{
		$logInfo=array('log'=>$info);
		$trace=self::getDebugTrace(2);//过滤两级
		if (!empty($trace)) {//保留最近一条执行路径
			$logInfo['trace']=array_shift($trace);
		}
		
		$logArray=array();
		$logArray['UrlNow']=$this->urlNow;
		$logArray['Type']=$this->type;
		$logArray['Identify']=$this->identify;
		$logArray['Info']=json_encode($logInfo);
		$logArray['Package']=$this->package;
		$logArray['Control']=$this->controller;
		$logArray['Action']=$this->action;
		$logArray['Uid']=$this->uid;
		$logArray['CreateTime']=DateTime::format();

		return Query::insert($this->table)->kvInsert($logArray)->query();
	}

	public function toString()
	{
		return 'mysql';
	}
}