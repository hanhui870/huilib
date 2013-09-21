<?php
namespace HuiLib\View\Test;

use HuiLib\Helper\Debug;

/**
 * 数据库测试类
 *
 * @author 祝景法
 * @since 2013/08/25
 */
class EngineTest extends \HuiLib\Test\TestBase
{
	public function run(){
		$this->test();
	}
	
	private function test(){
		Debug::mark('beginParse');
		$engine=new \HuiLib\View\TemplateEngine('Test');
		$engine->setViewPath(dirname(__FILE__).SEP.'ViewTest'.SEP)->setCachePath(dirname(__FILE__).SEP.'ViewTest'.SEP.'Output'.SEP);
		$engine->parse()->writeCompiled();
		Debug::mark('endParse');
		
		Debug::elapsed('beginParse', 'endParse');
	}

	protected static function className(){
		return __CLASS__;
	}
}