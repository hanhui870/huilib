<?php
namespace HuiLib\Helper\Test;

use HuiLib\Helper\BlockTextFormator;
use HuiLib\Helper\String;

/**
 * 模板测试类
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class FormaterTest extends \HuiLib\Test\TestBase
{

    public function run()
    {
        $this->test ();
    }

    private function test()
    {
        $string=<<<bbb
<h1 class="title">6月14日【上海】开源中国 OSC 源创会第 25 期   现在报名»</h1>
<h2 class="title">6月14日【上海】开源中国 OSC 源创会第 25 期   现在报名»</h2>
                <b color=green  onclick="fdasfdsa">fdafdasf</b>
                <b style="fdsafa">fdafdasf</b>
                <b style="fdsafsda" src='fdfd' color="red">fdafdasf</b>
                <font color="red">font code</font>
                <img src="fdasfdas" onclick="fdasfdsa">
                <Script>alert(1)</Script>
<p>关于开源访谈</p>
                <P>关于开源访谈</P>
                <ul class="topic-list clearfix">
                    <li class="thumbnail">
              <a href="http://iyunlin/zjgs"><img src="/static/image/nopic/100.gif"></a>
              <div class="caption" title="浙江工商"><a href="http://iyunlin/zjgs">浙江工商</a></div>
            </li>
                            <li class="thumbnail">
              <a href="http://iyunlin/FriendSter"><img src="/static/image/nopic/100.gif"></a>
              <div class="caption" title="交友"><a href="http://iyunlin/FriendSter">交友</a></div>
            </li>
                            <li class="thumbnail">
              <a href="http://iyunlin/topic/49"><img src="/static/image/nopic/100.gif"></a>
              <div class="caption" title="新生"><a href="http://iyunlin/topic/49">新生</a></div>
            </li>
                    </ul>
<p>&nbsp;<br />【作者简介】<br />栗元峰，开源爱好者。从iOS应用开发、cocos2d-iphone游戏开发到cocos2d-x游戏开发，参与了appstore排行榜单第一的《全民英雄》的开发，和其他多款上线成功项目。目前供职于http://9miao.com，专注于开源跨平台移动应用引擎CrossApp的开发。</p>
<p><br />&nbsp;<br />【软件简介】<br />CrossApp是一款免费、开源、跨平台的移动应用开发引擎，使用C++开发，基于OpenGL&nbsp;ES&nbsp;2.0渲染，可以帮助所有开发者快速的开发出跨平台的原生移动应用，支持导出包括IOS和Android等。</p>
        <table>
<tbody>
<tr>
<td>这是表格</td>
<td>这是表格</td>
<td>这是表格</td>
<td>这是表格</td>
<td>这是表格</td>
<td>这是表格</td>
<td>这是表格</td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
</tbody>
</table>
bbb;
        echo BlockTextFormator::format($string).'<br></br>';
        echo '<pre>'.String::htmlEncode(BlockTextFormator::format($string)).'</pre>';
    }

    protected static function className()
    {
        return __CLASS__;
    }
}