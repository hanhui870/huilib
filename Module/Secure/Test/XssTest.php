<?php
namespace HuiLib\Module\Secure\Test;

use HuiLib\Module\Secure\XssFilter;

/**
 * 模板测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class XssTest extends \HuiLib\Test\TestBase
{

    public function run()
    {
        $this->test ();
    }

    private function test()
    {
        $string=<<<bbb
<p>关于开源访谈</p>
<p>&nbsp;<br />【作者简介】<br />栗元峰，开源爱好者。从iOS应用开发、cocos2d-iphone游戏开发到cocos2d-x游戏开发，参与了appstore排行榜单第一的《全民英雄》的开发，和其他多款上线成功项目。目前供职于http://9miao.com，专注于开源跨平台移动应用引擎CrossApp的开发。</p>
<p><br />&nbsp;<br />【软件简介】<br />CrossApp是一款免费、开源、跨平台的移动应用开发引擎，使用C++开发，基于OpenGL&nbsp;ES&nbsp;2.0渲染，可以帮助所有开发者快速的开发出跨平台的原生移动应用，支持导出包括IOS和Android等。</p>
        
bbb;
        echo XssFilter::filter($string);
    }

    protected static function className()
    {
        return __CLASS__;
    }
}