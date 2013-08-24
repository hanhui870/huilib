<?php
namespace HuiLib\App;

/**
 * 应用创建文件
 *
 * @author 祝景法
 * @since 2013/08/11
 */
abstract class AppBase
{
	/**
	 * 引导程序
	 * @var \HuiLib\Bootstrap
	 */
	protected $bootStrap;
	
	/**
	 * 请求对象
	 * @var \HuiLib\Request\Base 
	 */
	protected $request;
	
	/**
	 * 运行配置
	 * @var \HuiLib\Config\ConfigBase
	 */
	private $appConfig;
	private $configPath;

	/**
	 * 构造函数
	 * 
	 * @param string $config 配置文件路径
	 * @throws \Exception
	 */
	protected function __construct($config)
	{
		if (! is_file($config) ) {
			throw new \Exception ( "Please specify a accessable config file!" );
		}
		$this->configPath=$config;
		
		$this->bootStrap = \HuiLib\Bootstrap::getInstance ();
		$this->initRequest();
	}

	/**
	 * 执行应用入口
	 */
	public function run()
	{
	}
	
	/**
	 * 初始化启动配置
	 */
	private function initConfig()
	{
		$this->appConfig = new \HuiLib\Config\ConfigBase ( $this->configPath );
	}
	
	/**
	 * 返回配置实例
	 * @return \HuiLib\Config\ConfigBase
	 */
	public function configInstance()
	{
		return $this->appConfig;
	}

	/**
	 * 初始化请求
	 */
	abstract protected function initRequest();

	/**
	 * 初始化数据库连接
	 */
	private function initDatabse()
	{
	
	}
	
	/**
	 * 初始化缓存资源
	 */
	private function initCache()
	{
	
	}
	
	/**
	 * 初始化Session资源
	 */
	private function initSession()
	{
	
	}
	
	/**
	 * 初始化错误处理器
	 */
	private function initErrorHandle()
	{
	
	}
	
	/**
	 * 初始化异常处理器
	 */
	private function initExceptionHandle()
	{
	
	}
	
	/**
	 * 执行期末绑定
	 */
	private function initShutCall()
	{
		$this->shutCall=\HuiLib\Runtime\ShutCall::getInstance();
	}
	
	public function shutCallInstance(){
		return $this->shutCall;
	}
	
	/**
	 * 初始化性能记录器
	 */
	private function initProfile()
	{
	
	}
	
	/**
	 * 初始化应用类
	 * 
	 * @return \HuiLib\App\AppBase
	 */
	public static function factory($runMethod, $config)
	{
		$appClass = '\\HuiLib\\App\\' . $runMethod;
		$appInstance = new $appClass ($config);
		return $appInstance;
	}
}
