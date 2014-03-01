<?php
namespace HuiLib\View\Helper;


/**
 * 视图前台对象传递辅助类
 * 
 * 提供类似DB Row对象直接在模板中的对象访问支持，避免前台模板索引应用数据等。
 * 
 * @author 祝景法
 * @since 2014/03/01
 */
class Proxy
{
    /**
     * 数据储存
     * @var array
     */
    protected $data=array();
    
    /**
     * 快速获取数据值
     * 
     * 支持数组的递归访问
     * 
     * @param string $key 请求的key
     */
    public function __get($key)
    {
        return $this->get($key);
    }
    
    /**
     * 手动触发访问版
     * 
     * 因为在前台view中当key是个变量的时候不好处理
     * 
     * @param string $key 请求的key
     */
    public function get($key)
    {
        if (isset($this->data[$key])) {
            if (is_array($this->data[$key])) {
                $proxyTmp=self::create()->setData($this->data[$key]);
                return $proxyTmp;
            }else{
                return $this->data[$key];
            }
        }else{
            return NULL;
        }
    }
    
    /**
     * 修改数据 间接引用为强制覆盖
     * 
     * @param string $key
     * @param number $value
     * @return boolean
     */
    public function __set($key, $value)
    {
        return $this->add($key, $value);
    }
    
    /**
     * 添加一条数据 覆盖已有
     *
     * @param string $key
     * @param number $value
     * @return boolean
     */
    public function add($key, $value)
    {
        $this->data[$key]=$value;
        return TRUE;
    }
    
    /**
     * 添加一条数据 不覆盖
     *
     * @param string $key
     * @param number $value
     * @return boolean
     */
    public function addnx($key, $value)
    {
        if (isset($this->data[$key])) {
            $this->data[$key]=$value;
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * 完全覆盖添加data数据
     *
     * @param $data array
     * @return boolean
     */
    public function setData($data)
    {
        $this->data=$data;
        
        return $this;
    }
    
    /**
     * 快速创建
     *
     * @return \HuiLib\View\Helper\Proxy
     */
    public static function create()
    {
        $instance=new static();
    
        return $instance;
    }
}