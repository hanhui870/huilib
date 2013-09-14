<?php
namespace HuiLib\Db\Query;

/**
 * Sql语句查询类Update操作
 *
 * @author 祝景法
 * @since 2013/09/03
 */
class Update extends \HuiLib\Db\Query
{

	/**
	 * 编译成SQL语句
	 */
	protected function compile(){
	
	}
	
	/**
	 * 生成SQL语句
	 */
	public function toString(){
		return $this->compile();
	}
}