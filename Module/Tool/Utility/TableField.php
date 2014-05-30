<?php
namespace HuiLib\Module\Tool\Utility;

use HuiLib\Db\DbBase;
use HuiLib\Error\Exception;
use HuiLib\Helper\String;

/**
 * 工具集框架基础
 *
 * @author 祝景法
 * @since 2013/11/30
 */
class TableField extends \HuiLib\Module\ModuleBase
{
	public function run($table){
		if (!$table) {
			throw new Exception('Tool class table name has not been set.');
		}
		
		$db=DbBase::createMaster();
		$fieldList=$db->getConnection()->query('describe '.$table)->fetchAll(\Pdo::FETCH_ASSOC);
		
		return $fieldList;
	}
}