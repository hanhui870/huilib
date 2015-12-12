<?php
namespace HuiLib\Cache\Storage;

/**
 * File基础类
 *
 * @author 祝景法
 * @since 2015/12/12
 */
class File extends \HuiLib\Cache\CacheBase
{
	/**
	 * 打开的文件类型
	 */
	private $filePath=NULL;
	
	/**
	 * 内容
	 */
	private $data=NULL;
	
	protected function __construct($config)
	{
		$this->config=$config;
		if (empty( $config['namespace'] )) {
		    throw new \Exception('Need prefix namespace.');
		}
		
		$this->filePath=APP_DATA . '/Cache/File/'.$config['namespace'].'.cache';
		if (!file_exists($this->filePath)) {
		    mkdir(dirname($this->filePath), 744, 1);
		    touch($this->filePath);
		}
		
		$this->data=@json_decode(file_get_contents($this->filePath), true);
		if (empty($this->data)) {
		    $this->data=array();
		}
		if (!is_array($this->data)) {
		    throw new \Exception('File cache: error file content.');
		}
	}
	
	/**
	 * 强制添加一个缓存
	 *
	 * 修改为: 强制设置，强制过期
	 *
	 * @param string $key 缓存键
	 * @param mix $value 缓存值
	 * @param int $expire 过期时间，0永不过期
	 */
	public function add($key, $value)
	{
		$this->data[$key]=$value;
		
		return true;
	}
	
	/**
	 * 添加一个新的缓存
	 *
	 * 仅在缓存变量不存在的情况下缓存变量到数据存储中
	 *
	 * @param string $key 缓存键
	 * @param mix $value 缓存值
	 * @param int $expire 过期时间，0永不过期
	 */
	public function addnx($key, $value)
	{
		if (isset($this->data[$key])) {
		    return false;
		}
		
		return $this->add($key, $value);
	}
	
	/**
	 * 删除一个缓存
	 *
	 * @param string $key 缓存键
	 */
	public function delete($key)
	{
		unset($this->data[$key]);
		
		return true;
	}
	
	/**
	 * 获取一个缓存内容
	 *
	 * @param string $key 缓存键，支持多键
	 * @return mix or false not exist
	 */
	public function get($key)
	{
	        if (!isset($this->data[$key])){
	            return false;
	        }
		return $this->data[$key];
	}
	
	/**
	 * 给缓存值加上一个数
	 *
	 * @param string $key 缓存键
	 * @param mix $value 增加的值
	 */
	public function increase($key, $value=1){
		return $this->data[$key]=intval($this->data[$key])+$value;
	}
	
	/**
	 * 给缓存值减去一个数
	 *
	 * @param string $key 缓存键
	 * @param mix $value 减少的值
	 */
	public function decrease($key, $value=1){
	       return $this->data[$key]=intval($this->data[$key])-$value;
	}
	
	/**
	 * 清空所有数据
	 *
	 */
	public function flush(){
		return file_put_contents($this->filePath, json_encode( $this->data));
	}
	

	public function __destruct(){
	    $this->flush();
	}
	
	public function toString(){
		return 'file';
	}
	
}