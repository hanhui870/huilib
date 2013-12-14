<?php
namespace HuiLib\Redis;

use HuiLib\Cache\CacheBase;

/**
 * Redis HashTable基础管理类
 *
 * @author 祝景法
 * @since 2013/12/14
 */
class HashTable
{
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
	
	/**
	 * Redis适配器
	 * @var \HuiLib\Cache\Storage\Redis
	 */
	protected $adapter=NULL;
	
	protected function __construct()
	{
		
	}
	
	/**
	 * 获取Redis适配器
	 * @return \HuiLib\Cache\Storage\Redis
	 */
	protected function getAdapter()
	{
		if ($this->adapter===NULL) {
			$this->adapter=CacheBase::getRedis();
		}
		
		return $this->adapter;
	}
	
	/**
	 * 通过主ID获取
	 */
	public function getByPrimaryId($primaryId)
	{
		
	}
	
	public static function create()
	{
		return new static();
	}
	
}