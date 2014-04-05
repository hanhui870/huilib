<?php
namespace HuiLib\Module\Runtime;

/**
 * 应用执行期末方法回调
 *
 * @author 祝景法
 * @since 2013/08/11
 */
class ShutCall extends  \HuiLib\Module\ModuleBase
{
	private $callbacks=array();
	
	private function __construct()
	{
		
	}
	
	/**
	 * 注册调用函数
	 *
	 * @param $module string 调用的module使用命名空间 例如：'\module\passport\birth';
	 * @param $method string 调用的方法
	 * @param $param array 参数
	 */
	public function add($module, $method, $param = array()) {
		if (empty ( $module ) || empty ( $method ))
			return false;
	
		$key = md5 ( $module . '/' . $method . '/' . var_export ( $param, 1 ) );
		$call = array ();
		$call ['module'] = $module;
		$call ['method'] = $method;
		$call ['param'] = $param;
		$this->callbacks[$key] = $call;
	}
	
	/**
	 * 全局关闭处理
	 */
	public function run() {
		if (! empty ( $this->callbacks )) {
			foreach ( $this->callbacks as $key => $call ) {
				$module = $call['module']::getInstance();
				if (is_object ( $module ) && method_exists ( $module, $call ['method'] )) {
					$module->$call ['method'] ( $call ['param'] );
				}
			}
		}
		
		$this->releaseResources();
	}
	
	private function flushRunLog(){
		// PHP错误处理
		$error = error_get_last ();
		$logModule=new \module\utility\log();
		if (! empty ( $error ['type'] )) {
			$logModule->setType ( 'PHPError' );
			//将常见错误类型转换为文字
			$error ['type'] = str_replace ( array (4096, 2048, 1024, 512, 256, 8, 4, 2, 1 ), array ('E_DEPRECATED', 'E_STRICT', 'E_USER_NOTICE', 'E_USER_WARNING', 'E_USER_ERROR', 'E_NOTICE', 'E_PARSE', 'E_WARNING', 'E_ERROR' ), $error ['type'] );
			$error ['file']=str_ireplace ( array(sys_root, DIRECTORY_SEPARATOR), array('', '|'), $error ['file'] );
			$logModule->flush ( 'PHPRuntime', $error );
		}
		
		$logModule->releaseResources();
	}
	
	/**
	 * 获取引导类实例
	 * @return \HuiLib\Runtime\ShutCall
	 */
	public static function create()
	{
		return new self ();
	}
}