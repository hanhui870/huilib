<?php
namespace HuiLib\Helper;

/**
 * 调试辅助函数
 *
 * @author 祝景法
 * @since 2013/08/11
 */
class Debug
{
	private static $startTime;

	public static function benchStart()
	{
		self::$startTime = microtime ( 1 );
		
		return true;
	}

	public static function benchFinish()
	{
		$br = RUN_METHOD == "cli" ? "\n" : "<br/>";
		echo $br . 'Bench Start:' . self::$startTime;
		$endTime = microtime ( 1 );
		echo $br . 'Bench Finish:' . $endTime;
		echo $br . 'Consume Time Total:' . ( float ) ($endTime - self::$startTime);
	}
	
	/**
	 * 获取调式路径信息
	 */
	public static function getDebugTrace() {
		$debug = debug_backtrace ( 0 );
		$traceResult = array ();
		foreach ( $debug as $key => $trace ) {
			if ($trace ['function'] == 'getDebugTrace')
				continue; // 略过本函数
			if (empty ( $trace ['file'] ))
				break;
			$temp = array ();
			$temp ['file'] = str_ireplace ( array(SYS_PATH, SEP), array('', '/'), $trace ['file'] );
			$temp ['line'] = $trace ['line'];
			$temp ['function'] = $trace ['function'];
			$traceResult [] = $temp;
		}
		return $traceResult;
	}
	
	/**
	 * 变量智能输出
	 *
	 * @param $var mix
	 */
	public static function out($var) {
		if (is_array ( $var ) || is_object ( $var )) {
			print_r ( $var );
		} elseif (is_bool ( $var )) {
			var_dump ( $var );
		} else {
			echo $var;
		}
	}
}