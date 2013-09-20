<?php
namespace HuiLib\Loader;

/**
 * 类自动加载库
 * 
 * @author 祝景法
 * @since 2013/08/11
 */
class AutoLoad
{
	private static $instance;
	private $allowedSpace = array ();

	private function __construct()
	{
		$this->addSpace ( "HuiLib", SYS_PATH );
	}

	/**
	 * 添加一个命名空间到自动加载类
	 * @param string $namespace
	 * @param string $path
	 */
	public function addSpace($namespace, $path)
	{
		if (isset ( $this->allowedSpace [$namespace] )) {
			return false;
		}
		$this->allowedSpace [$namespace] = $path;
	}

	/**
	 * 从自动加载类移除一个命名空间
	 * @param string $namespace
	 */
	public function removeSpace($namespace)
	{
		if (! isset ( $this->allowedSpace [$namespace] )) {
			return false;
		}
		unset ( $this->allowedSpace [$namespace] );
	}

	/**
	 * 自动加载类
	 * @tip 转发过来的请求形式module\common\core，没有最前面的斜线。
	 */
	public function loadClass($name)
	{
		//\符号是命名空间分隔符
		$nameInfo=explode('\\', $name);
		
		//命名空间首标识符已经剔除，直接包含在路径信息中，便于自由放置
		$spaceName=array_shift($nameInfo);

		//字母数字\_
		if (preg_match("/[^\w\\\]+/i", $name, $m)) {
			throw new \Exception ( "Hack discovered!" );
		}
		
		$spacePath=$this->getRegisteredPath($spaceName);
		if (!is_dir($spacePath)) 
		{
			throw new \Exception ( "the SpaceName:{$spaceName} is not registered or not accessable!" );
		}

		$name = $spacePath . implode(SEP, $nameInfo) . '.php';
		if (file_exists ( $name )) {
			include_once $name;
		} else {
			throw new \Exception ( "file $name doesn't exists, please check!" );
		}
	}
	
	private function getRegisteredPath($spaceName){
		if (!isset($this->allowedSpace[$spaceName]))
		{
			return false;
		}
		
		return $this->allowedSpace[$spaceName];
	}

	/**
	 * 获取自动加载类实例
	 * @return \HuiLib\Loader\AutoLoad
	 */
	public static function getInstance()
	{
		if (self::$instance == NULL) {
			self::$instance = new self ();
		}
		return self::$instance;
	}
}