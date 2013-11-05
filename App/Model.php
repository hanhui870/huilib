<?php
namespace HuiLib\App;

/**
 * 数据表模型Model基础类
 * 
 * 1105: 决定将Model继承自Module，因为Model从本质上是Module，只是仅包含纯数据相关业务。
 * 
 * @author 祝景法
 * @since 2013/10/20
 */
class Model extends Module
{
	/**
	 * 数据库连接适配器
	 * @var \HuiLib\Db\DbBase
	 */
	protected $dbAdapter=NULL;
	
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
}
