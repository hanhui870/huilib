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
		
		$result=array();
		foreach ($fieldList as $field)
		{
			if (!empty($field['Default'])) {
				$result[$field['Field']]=$field['Default'];
			}else{
				if (String::exist($field['Type'], 'int')) {
					$result[$field['Field']]=0;
				}elseif (String::exist($field['Type'], 'varchar')) {
					$result[$field['Field']]='';
				}else{
					$result[$field['Field']]='';
				}
			}
		}
		
		//print_r($fieldList);
		echo "<pre>".var_export($result, TRUE)."</pre>";
	}
}