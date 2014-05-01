<?php
namespace HuiLib\App;

use HuiLib\App\Front;

/**
 * 应用创建文件
 *
 * @author 祝景法
 * @since 2013/08/11
 */
abstract class AppBase
{
	/**
	 * 请求对象
	 * @var \HuiLib\App\Request\RequestBase
	 */
	protected $requestInstance;
	
	/**
	 * 响应内容
	 */
	protected $responce;
	
	/**
	 * 运行配置
	 * @var \HuiLib\Config\ConfigBase
	 */
	protected $appConfig;
	protected $configPath;
	
	/**
	 * session实例
	 *  @var \HuiLib\Session\SessionBase
	 */
	protected $sessionInstance=NULL;

	/**
	 * 构造函数
	 * 
	 * @param string $config 配置文件路径
	 * @throws \Exception
	 */
	protected function __construct($config)
	{
		if (! is_file ( $config )) {
			throw new \Exception ( "Please specify a accessable config file!" );
		}
		$this->configPath = $config;
		
		//初始化应用配置
		$this->initConfig ();
		
		//php运行配置
		$this->initPhpSetting ();
		
		//自动加载配置
		$this->initAutoLoad ();
		
		//初始化请求对象 具体可能在子类初始化
		$this->initRequest ();
		Front::getInstance()->setRequest($this->requestInstance);
		
		$this->initErrorHandle();
		$this->initExceptionHandle();
	}

	/**
	 * 应用执行入口
	 */
	public function run()
	{
		//初始化Session Test需要独立初始化
		$this->initSession();
		
		/**
		 * url路由处理
		 */
		$this->requestInstance->urlRoute();

		if (method_exists(Front::getInstance()->getController(), 'dispatch')) {
			Front::getInstance()->getController()->dispatch();
		}else{
			throw new \HuiLib\Error\Exception ( "AppBase::run controller dispatch failed!" );
		}
		
		Front::getInstance()->getController()->output();
	}

	/**
	 * 测试执行入口
	 */
	public function runTest()
	{
	    if (!\HuiLib\App\Request\RequestBase::isCli() && (APP_ENV=='production' || APP_ENV=='staging')) {
	        exit('not support.');
	    }

	    //bin运行
	    if (RUN_METHOD==\HuiLib\Bootstrap::RUN_BIN) {
	        if (!empty($_SERVER['argv'][1])) {
	            $class=trim($_SERVER['argv'][1]);
	        }else{
	            exit('empty bin param.');
	        }      
	        
	        echo 'RUN_EVN:'. APP_ENV.PHP_EOL;
	    }else{
	        //web运行
	        $queryString = \HuiLib\Helper\Param::getQueryString ();
	        parse_str($queryString, $info);
	        if (empty($info)) {
	            exit('empty web param.');
	        }
	        //获取类名
	        $class=key($info);
	    }

		//初始化测试库
		$instance = $class::getInstance ();
		
		//执行
		$instance->run ();
	}

	/**
	 * 初始化启动配置
	 */
	protected function initConfig()
	{
		$this->appConfig = new \HuiLib\Config\ConfigBase ( $this->configPath );
		Front::getInstance()->setAppConfig($this->appConfig);
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
	 * 
	 * 先在之类初始化请求，然后父类初始化配置
	 */
	protected abstract function initRequest();

	/**
	 * 获取应用所在的命名空间
	 */
	public function getAppNamespace()
	{
		$appNamespace=$this->appConfig->getByKey ( 'app.namespace' );
		
		if (empty($appNamespace) || !is_string($appNamespace)) {
			throw new \HuiLib\Error\Exception ( "配置文件应用命名空间{app.namespace}配置错误" );
		}
		
		return $appNamespace;
	}

	/**
	 * 初始化Session资源
	 */
	protected function initSession()
	{
		$this->sessionInstance=\HuiLib\Session\SessionBase::create($this->configInstance ());
		Front::getInstance()->setSession($this->sessionInstance);
	}

	/**
	 * 初始化错误处理器
	 */
	protected function initErrorHandle()
	{
	    set_error_handler(array('HuiLib\Error\ErrorHandler', 'errorHandle'));
	}

	/**
	 * 初始化异常处理器
	 */
	protected function initExceptionHandle()
	{
	    set_exception_handler(array('HuiLib\Error\ErrorHandler', 'exceptionHandle'));
	}

	/**
	 * 初始化错误处理器
	 */
	final protected function initPhpSetting()
	{
		$settings = $this->appConfig->mergeKey ( $this->appConfig->getByKey ( 'phpSettings' ) );
		
		foreach ( $settings as $key => $value ) {
			ini_set ( $key, $value );
		}
		
		return true;
	}

	/**
	 * 将配置文件中的autoLoad项目注册到load空间
	 */
	protected function initAutoLoad()
	{
		$loads = $this->appConfig->getByKey ( 'autoLoad' );
		
		$loader = Front::getInstance()->getLoader ();
		foreach ( $loads as $name => $path ) {
			$loader->addSpace ( $name, $path );
		}
		
		return true;
	}

	/**
	 * 执行期末绑定
	 */
	protected function initShutCall()
	{
		$this->shutCall = \HuiLib\Runtime\ShutCall::create ();
		Front::getInstance()->setShutCall($this->shutCall );
	}

	/**
	 * 初始化性能记录器
	 */
	protected function initProfile()
	{
	}

	/**
	 * 初始化应用类
	 * 
	 * @return \HuiLib\App\AppBase
	 */
	public static function factory($runMethod, $config)
	{
		$appClass = '\\HuiLib\\App\\Entry\\' . ucfirst ( $runMethod );
		$appInstance = new $appClass ( $config );
		return $appInstance;
	}
}
