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
	const lineSep="\n";
	
	/**
	 * 时间标签
	 *
	 * @var array
	 */
	private static $marker = array();

	/**
	 * 做出一个标记
	 * @param string $label 标记名称
	 * @return boolean
	 */
	public static function mark($label)
	{
		self::$marker[$label] = microtime ( 1 );
		
		return self::$marker[$label];
	}

	/**
	 * 计算两个标记点之间的消逝时间
	 * 
	 * @param float $start 开始标记
	 * @param float $end 结束标记
	 */
	public static function elapsed($start, $end)
	{
		if (!isset(self::$marker[$start])) {
			throw new \HuiLib\Error\Exception ( "BenchMark开始标记{$start}未设置" );
		}
		if (!isset(self::$marker[$end])) {
			self::$marker[$end] = microtime ( 1 );
		}
		echo self::lineSep . "Bench Start {$start}:" .self::$marker[$start];
		echo self::lineSep . "Bench Finish {$end}:" . self::$marker[$end];
		echo self::lineSep . 'Consume Time Total:' . ( float ) (self::$marker[$end] - self::$marker[$start]);
		echo self::lineSep;
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
			$temp ['file'] = str_ireplace ( array(LIB_PATH, SEP), array('', '/'), $trace ['file'] );
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