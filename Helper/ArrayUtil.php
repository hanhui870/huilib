<?php
namespace HuiLib\Helper;

use HuiLib\Error\Exception;
/**
 * 数组辅助函数
 *
 * @author 祝景法
 * @since 2014/03/15
 */
class ArrayUtil
{
    /**
     * 将某数组转变为以值为键的数组
     * 
     * array(0=>43, 1=>46) => array(43=>43, 46=>46)
     *
     * @param array $array
     * @param string $type
     */
    public static function bulidFromValue(array $array, $type=NULL)
    {
        $result=array();
        foreach ($array as $value){
            if ($type) {
                settype($value, $type);
            }
            $result[$value]=$value;
        }
        return $result;
    }
    
    /**
     * 将某数组转变为以某主键为键的数组
     * 
     * 譬如将数据库取出的列表格式化为主键为键的数组
     *
     * @param array $array
     * @param string $primaryKey 主键
     * @param string $type
     */
    public static function bulidFromPrimaryKey(array $array, $primaryKey, $type=NULL)
    {
        $result=array();
        foreach ($array as $value){
            if (!isset($value[$primaryKey])) {
                throw new Exception('bulidFromPrimaryKey: primary key doesn\'t exist.');
            }
            if ($type) {
                settype($value[$primaryKey], $type);
            }
            $result[$value[$primaryKey]]=$value;
        }
        return $result;
    }
}