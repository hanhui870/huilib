<?php
namespace HuiLib\Helper;

use HuiLib\Module\Secure\XssFilter;
/**
 * 块文本内容格式化
 *
 * @author 祝景法
 * @since 2014/03/03
 */
class BlockTextFormator
{
    //是否添加<p>标签结尾
    const ADD_PTAG=TRUE;
    const NO_PTAG=FALSE;

    /*
     * 内容格式化，html版
     * 
     * xss处理
    */
    public static function format($message) {
        //替换h1-6和font
        $message=preg_replace('/\<(h\d|font)[^>]*?\>(.*?)\<(\/\1)\>/is', '<p>\2</p>', $message);
        $message=strip_tags($message, '<p><a><img><div><span><table><tbody><td><th><tr><ul><ol><li><b><em><strong>');
        
        //清除所有没加引号的属性
        //preg_match_all('/(?!\<\w+)\s+\w*\=\s*[^>\s\'"]*?\s/is', $message, $mat);print_r($mat);
        $message=preg_replace('/(?!\<\w+)\s+\w*\=\s*[^>\s\'"]*?\s/is', '', $message);
        
        //清除class和style和on事件，指定图片等宽和高的也要清空，手机不兼容
        //preg_match_all('/(?!\<\w+)\s+(?:on|style|class|color)\w*\=\s*[\'"][^\'\">]*[\'"]/is', $message, $mat);print_r($mat);
        $message=preg_replace('/(?!\<\w+)\s+(?:on|style|class|color|width|height)\w*\=\s*[\'"]?[^\'\">]*[\'"]?/is', '', $message);
        
        //检测标签匹配情况
        //$message=self::matchTags($message);
        
        return XssFilter::filter($message);
    }
    
    /**
     * 检测标签的匹配情况
     * 
     * TODO 如果要准确分析，需要进入上下文分析
     * 
     * @param string $message
     */
    public static function matchTags($message) {
        $need='<p>,<a>,<div>,<span>,<table>,<tbody>,<td>,<th>,<tr>,<ul>,<ol>,<li>,<b>,<em>,<strong>';
        $match='</p>,</a>,</div>,</span>,</table>,</tbody>,</td>,</th>,</tr>,</ul>,</ol>,</li>,</b>,</em>,</strong>';
        $listNeed=explode(',', $need);
        $listMatch=explode(',', $match);
        
        //匹配所有标签
        preg_match_all('/\<(\/?\w+)[^>]*?\>/is', $message, $resultTags);
        print_r($resultTags);die();
        
        if (empty($resultTags[0])) {
            return $message;
        }
        
        //目前没有去检测上下文，而是检测到不匹配直接在末尾补上。
        $stackCache=array();
        foreach ($resultTags[0] as $tag){
            $fulltag='<'.$tag.'>';
            if (in_array($fulltag, $listNeed)) {
                $stackCache[$fulltag][]=1;
            }
            if (in_array($fulltag, $listMatch)) {
                $stackCache[$fulltag][]=1;
            }
        }
        
        return $message;
    }
    
    /*
     * 简洁格式化版 $p是否加p
    */
    public static function simpleFormat($message, $addPTag = self::ADD_PTAG) {
        $output = '';
        
        //stripIxcel，除了一般新闻网站很少出现这个字符
        //$message = self::stripIxcel ( $message ); 
    
        // 兼容英文，仅保留一个空格 \s包括\n\r
        $message = preg_replace ( array ("/[ \f\t\v]{2,}/", "/[\r\n]+/" ), array (' ', "\n" ), trim ( $message ) );
    
        if ($addPTag) {
            $arraymessage = explode ( "\n", $message );
            foreach ( $arraymessage as $value ) {
                if (trim ( $value ) != '') {
                    $output .= "<p>" . trim ( $value ) . "</p>\n";
                }
            }
        } else {
            $output = str_replace ( "\n", '', $message );
        }
        return $output;
    }
    
    /*
     * strip_ixcel功能Utf8版 替换为正常空格 因为有些通过这个间隔 删掉很难看
     * 
     * 空字符貌似好多，今天发现226 128 131的字符序列也是空字符。
     * http://hz.house.sina.com.cn/scan/2014-04-01/00404029391.shtml
    */
    public static function stripIxcel($leachmessage) {
        $str = '';
        $length = strlen ( $leachmessage );
        $iter = 0;
        while ( $iter < $length ) {
            if (ord ( substr ( $leachmessage, $iter, 1 ) ) > 127) {
                $ord = intval ( ord ( $leachmessage [$iter] ) );
                $ord1 = intval ( ord ( $leachmessage [$iter + 1] ) );
                $ord2 = intval ( ord ( $leachmessage [$iter + 2] ) );
                if ($ord == 227 && $ord1 == 128 && $ord2 == 128) {
                    $str .= ' ';
                    $iter = $iter + 3;
                    continue;
                } else {
                    $str .= substr ( $leachmessage, $iter, 3 );
                    $iter = $iter + 3;
                }
            } else {
                $str .= substr ( $leachmessage, $iter, 1 );
                $iter = $iter + 1;
            }
        }
        return $str;
    }
}