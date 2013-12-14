<?php
namespace HuiLib\Redis;

/**
 * Redis HashTable基础管理类
 *
 * @author 祝景法
 * @since 2013/12/14
 */
class HashTable extends RedisBase
{
	const TABLE_CLASS=NULL;
	
	/**
	 * 行数据储存
	 * @var array
	 */
	protected $data=array();
	
	/**
	 * 行默认初始化数据
	 * @var array
	*/
	protected static $initData=NULL;
	
	/**
	 * 修改数据储存
	 * @var array
	 */
	protected $editData=array();
	
	/**
	 * 主键字段
	 * @var string
	*/
	protected $primaryId=NULL;
	
	protected function __construct()
	{
		self::initRowInitData();
	}

	/**
	 * 通过主ID获取
	 */
	public function initByPrimaryId($primaryId)
	{
		$tableClass=static::TABLE_CLASS;
		//通过主键获取数据
		$data=$tableClass::create()->getRowByField($this->primaryId, $primaryId);
		if (empty($data)) {
			return FALSE;
		}
		
		foreach ($data as $key=>$value){
			if(isset(static::$initData[$key])){
				$this->data[$key]=$value;
			}
		}
		return TRUE;
	}
	
	/**
	 * 初始化表行默认初始化数据
	 * @return array
	 */
	public static function initRowInitData()
	{
		$tableClass=static::TABLE_CLASS;
		static::$initData=$tableClass::getRowInitData();
	}
	
	/**
	 * 快速创建Redis数据模型
	 * 
	 * @param string $primaryId
	 * @return \HuiLib\Redis\HashTable
	 */
	public static function create($primaryId=NULL)
	{
		$instance=new static();
		if ($primaryId!==NULL) {
			$instance->initByPrimaryId($primaryId);
		}
		
		return $instance;
	}
	
	public function __get($key)
	{
		if (isset($this->data[$key])) {
			return $this->data[$key];
		}else{
			return NULL;
		}
	}
	
	/**
	 * 执行储存到Redis
	 */
	public function save()
	{
		
	}
	
	public function __destruct()
	{
		//对象销毁自动触发保存
		 $this->save();
	}
}