<?php
namespace HuiLib\Helper;

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