<?php
namespace HuiLib\App;

/**
 * 数据表模型Model基础类
 * 
 * @author 祝景法
 * @since 2013/10/20
 */
class Model
{
	/**
	 * 基础APP实例
	 * @var \HuiLib\App\AppBase
	 */
	protected $appInstance;
	
	/**
	 * 数据库连接适配器
	 * @var \HuiLib\Db\DbBase
	 */
	protected $dbAdapter=NULL;
	
	protected function __construct()
	{
	}
	
	/**
	 * 获取应用实例
	 */
	protected function getAppInstace()
	{
		if ($this->appInstance===NULL) {
			$this->appInstance=\HuiLib\Bootstrap::getInstance()->appInstance();
		}
	
		return $this->appInstance;
	}
	
	/**
	 * 设置应用实例
	 */
	public function setAppInstance(\HuiLib\App\AppBase $appInstance=NULL)
	{
		$this->appInstance=$appInstance;
	
		return $this;
	}
	

	/**
	 * 设置适配器，需要compile的时候必须设置
	 *
	 * @param \HuiLib\Db\DbBase $dbAdapter
	 */
	public function setDbAdapter(\HuiLib\Db\DbBase $dbAdapter=NULL)
	{
		$this->dbAdapter = $dbAdapter;
	
		return $this;
	}
	
	/**
	 * 快速创建一个Module实例
	 *
	 * 原理：创建对象的灵活获取参数是从第0个开始的。一般函数是从第一个开始的。
	 *
	 * @param mix $param 支持传递参数，最多5个，其他空字符串代替
	 */
	static function create(){
	
		$paramCount=func_num_args();
	
		if (!$paramCount) {
			return new static();
		}else{
			$params=func_get_args();
			//最多5个，其他空字符串代替
			list($param1, $param2, $param3, $param4, $param5)=array_pad($params, 5, '');
				
			return new static($param1, $param2, $param3, $param4, $param5);
		}
	}
}
