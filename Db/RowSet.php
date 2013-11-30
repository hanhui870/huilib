<?php
namespace HuiLib\Db;

use HuiLib\Error\Exception;

/**
 * 数据行列表类
 *
 * @author 祝景法
 * @since 2013/11/30
 */
class RowSet extends \HuiLib\App\Model
{
	/**
	 * 行列表数据储存
	 * @var array
	 */
	protected $dataList=array();
	
	/**
	 * 返回对象的数组表示
	 * @return array
	 */
	public function toArray()
	{
		return $this->$dataList;
	}
	
	public function get($key)
	{
		if (isset($this->data[$key])) {
			return $this->data[$key];
		}else{
			return NULL;
		}
	}
	
}