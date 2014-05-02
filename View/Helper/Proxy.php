<?php
namespace HuiLib\View\Helper;


/**
 * 视图前台对象传递辅助类
 * 
 * 提供类似DB Row对象直接在模板中的对象访问支持，避免前台模板索引应用数据等。
 * 这种执行Iterator接口的数据必须是0,1,2这样开始的自然索引数组才能被foreach，但是可以正常数组获取。RowSet原理一样。
 * 
 * @author 祝景法
 * @since 2014/03/01
 */
class Proxy implements \Iterator, \ArrayAccess
{
    /**
     * 数据储存
     * @var array
     */
    protected $data=array();
    
    /**
     * 数组当前指针
     * @var int
     */
    protected $position = 0;
    
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
     * 返回对象数据是否为空
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->data);
    }
    
    /**
     * 返回数据的一级对象个数
     *
     * @return int
     */
    public function size()
    {
        return count($this->data);
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
    
    public function rewind()
    {
        $this->position = 0;
    }
    
    /**
     * 获取一行数据，生成对象
     * @return \HuiLib\Db\RowAbstract
     */
    public function current()
    {
        return $this->get($this->position);
    }
    
    public function key()
    {
        return $this->position;
    }
    
    public function next()
    {
        ++ $this->position;
    }
    
    public function valid()
    {
        return isset ( $this->data [$this->position] );
    }
    
    public function offsetSet($offset, $value)
    {
        if (is_null ( $offset )) {
            $this->data [] = $value;
        } else {
            $this->data [$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        return isset ( $this->data [$offset] );
    }
    
    public function offsetUnset($offset)
    {
        unset ( $this->data [$offset] );
    }
    
    /**
     * 通过数组下标获取一行数据
     *
     * @param int $offset
     * @return \HuiLib\Db\RowAbstract
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
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