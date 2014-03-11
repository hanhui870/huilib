<?php
/**
 * HuiLib Lang库操作指南
 * 
 * @since 2013/11/10
 * 
 * Lang支持翻译器后端：GetText, Ini两个文件格式，推荐前种，二进制储存，有专门更新的工具
 * 
 * 适配器接口：
 * 		translate($token)：请求翻译一个字串
 * 		translate($token, $param1, $param2, ...)：请求翻译一个字串，并且支持sprintf格式变量解析
 * 
 *     复数翻译Zend接口
 *     $translate->plural("common.hour","common.hours",2);
 */

//快速获取一个Lang翻译器实例
$lang=\HuiLib\Lang\LangBase::getHuiLibLang();

//请求一个翻译 
//多个参数，如果少个会怎么样？
$lang->translate('HuiLib.lang.test', '芸临网', 'hanhui', 1000, 3.55000);