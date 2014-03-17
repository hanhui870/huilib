<?php
namespace HuiLib\Redis;

use HuiLib\Error\Exception;
use HuiLib\Db\Query\Where;

/**
 * Redis HashTable基础管理类
 * 
 * 更新时间戳，嵌套在HashKey中。
 * 从数据库更新时是通过主键获取的，主键需要是整型的。
 * 
 * HashTable只支持单个键组成的Hash，不允许符合键。因为符合键可以通过拆解实现，减少复杂度。
 *
 * @author 祝景法
 * @since 2014/01/04
 */
abstract class HashTable extends RedisBase
{
	/**
	 * 表类常量定义
	 * @var string
	 */
	const TABLE_CLASS=NULL;
	
	/**
	 * Redis键前缀，需要和父类的组合
	 * @var string
	 */
	const KEY_PREFIX='hash:table:';
	
	/**
	 * 每次获取列表的数量
	 * 
	 * 一般仅获取个别字段，可以多点
	 */
	const FETCH_PER_ACTION=10000;
	
	/**
	 * HashTable的键字段，关联数据库字段
	 * @var string
	 */
	protected $hashKeyField=NULL;
	
	/**
	 * HashTable的值字段，可由多个值拼接，用于数据获取
	 * eg. "Name, Uid, Password, Shorturl"
	 * 
	 * @var string
	 */
	protected $hashValueField=NULL;
	
	protected function __construct()
	{
	}
	
	/**
	 * 通过Hash键列表获取值
	 * 
	 * 取出来的也是关联数组，不存在的是空值
	 * Array(
	*	    [浙商大] => 24
	 *	    [浙江工商大学] => 
	 *	)
	 * 
	 * @param array $keyList 单条为值，多条array，内容是HashKey
	 */
	public function getList($keyList)
	{
		if (!is_array($keyList)) {
			$keyList=array($keyList);
		}
		
		$keyList[]=self::REDIS_UPDATE_KEY;
		$resultList=$this->getAdapter()->hMget($this->getRedisKey(), $keyList);
		//print_r($resultList);die();

		//检测到未设置数据 导入数据，惰性更新
		if (empty($resultList[self::REDIS_UPDATE_KEY])) {
			$this->importFromDb();
			$resultList=$this->getAdapter()->hMget($this->getRedisKey(), $keyList);
		}
		unset($resultList[self::REDIS_UPDATE_KEY]);
		
		//解析Hash值数据
		if (method_exists($this, 'parseValueString')){
			$parsedList=array();
			foreach ($resultList as $key=>$value){
				$parsedList[$key]=$this->parseValueString($value);
			}
			$resultList=$parsedList;
		}
		
		return $resultList;
	}
	
	/**
	 * 从Redis HashTable从删除数据
	 * 
	 * @param array $hashKeys 单条为值，多条array，内容是HashKey
	 */
	public function delete($hashKeys)
	{
		if (empty($hashKeys)) {
			return FALSE;
		}
		if (!is_array($hashKeys)) {
			$hashKeys=array($hashKeys);
		}
		
		$multi=$this->getAdapter()->multi();
		foreach ($hashKeys as $key){
			$multi->hDel($this->getRedisKey(), $key);
		}
		
		return $multi->exec();
	}
	
	/**
	 * 手工添加一条
	 * 
	 * @param array $valueUnit 单条信息数组
	 */
	public function addOne($valueUnit)
	{
		$result=array();
		$result[$valueUnit[$this->hashKeyField]]=$this->getValueString($valueUnit);
	
		//埋Redis更新时间戳
		$result[self::REDIS_UPDATE_KEY]=time();
		return $this->getAdapter()->hMset($this->getRedisKey(), $result);
	}
	
	/**
	 * 通过主键批量添加
	 * 
	 * @param array $primaryIds 信息主键数组
	 */
	public function addByPrimaryIds($primaryIds)
	{
		$tableClass=static::TABLE_CLASS;
		$primaryIdKey=$this->getRowPrimaryIdKey();
		$dataList=$tableClass::create()->getListByIds($primaryIdKey, $primaryIds);

		if ($dataList) {
			$dataList=$dataList->toArray();
			$result=array();
			foreach ($dataList as $iter=>$valueUnit){
				$result[$valueUnit[$this->hashKeyField]]=$this->getValueString($valueUnit);
			}
			
			//埋Redis更新时间戳
			$result[self::REDIS_UPDATE_KEY]=time();
			$this->getAdapter()->hMset($this->getRedisKey(), $result);
		}
		return TRUE;
	}
	
	/**
	 * 从数据库获取数据，重建列表
	 */
	protected function importFromDb()
	{
		//通过主键尝试数据表获取数据
		$tableClass=static::TABLE_CLASS;
	
		$primaryIdKey=$this->getRowPrimaryIdKey();
		$primaryId=0;
	
		$fields=explode(',', $this->hashValueField);
		array_push($fields, $this->hashKeyField, $primaryIdKey);
		$fields=array_unique($fields);
	
		//删除旧数据
		$this->getAdapter()->delete($this->getRedisKey());
	
		do{
			$select=$tableClass::create()->select()->columns($fields);
			$select->where(Where::createQuote($primaryIdKey.' >?', $primaryId))->limit(self::FETCH_PER_ACTION)->order($primaryIdKey. ' asc');
			//echo $select->toString()."\n";
				
			$dataList=$select->query()->fetchAll();
			if ($dataList) {
				$result=array();
				foreach ($dataList as $iter=>$valueUnit){
				    //包含无限循环的要尽量限制严格些
				    if (!isset($valueUnit[$this->hashKeyField])) {
				        throw new \Exception('Field fetch error.');
				    }
					$result[$valueUnit[$this->hashKeyField]]=$this->getValueString($valueUnit);
				}

				//包含无限循环的要尽量限制严格些
				if (!isset($valueUnit[$primaryIdKey])) {
				    throw new \Exception('Value of primary key fetch error.');
				}
				$primaryId=$valueUnit[$primaryIdKey];
	
				$this->getAdapter()->hMset($this->getRedisKey(), $result);
			}
		}while (!empty($dataList));
	
		//埋Redis更新时间戳
		$this->getAdapter()->hSet($this->getRedisKey(), self::REDIS_UPDATE_KEY, time());
		return TRUE;
	}
	
	/**
	 * 获取redis储存键
	 */
	protected function getRedisKey()
	{
		if (static::TABLE_CLASS===NULL) {
			throw new Exception('Model table class has not been set.');
		}
		$spaceInfo=explode(NAME_SEP, static::TABLE_CLASS);
		return parent::KEY_PREFIX.self::KEY_PREFIX.array_pop($spaceInfo);
	}
	
	/**
	 * 返回储存在Redis Hash中的值字段
	 * 
	 * @param array $valueUnit 标准的一行数据
	 */
	protected abstract function getValueString(&$valueUnit);
	
	/**
	 * 解析Hash值的函数 未定义则不调用
	 */
	//protected abstract function parseValueString(&$valueString);
	
	/**
	 * 初始化表行主键名
	 * @return array
	 */
	protected function getRowPrimaryIdKey()
	{
		$tableClass=static::TABLE_CLASS;
		$rowClass=$tableClass::ROW_CLASS;
		return $rowClass::PRIMAY_IDKEY;
	}
	
}