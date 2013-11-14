<?php
namespace HuiLib\Db;

/**
 * 表数据行类
 *
 * @author 祝景法
 * @since 2013/10/20
 */
class RowAbstract extends \HuiLib\App\Model
{
	/**
	 * 行数据储存
	 * @var array
	 */
	protected $data=array();
	
	public function __construct($data)
	{
	
	}
	
	/**
	 * 返回对象的数组表示
	 * @return multitype:
	 */
	public function toArray()
	{
		return $this->data;
	}
	
	public function __get()
	{
		
	}
}