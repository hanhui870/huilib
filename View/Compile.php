<?php
namespace HuiLib\View;

/**
 * 模板引擎解析类 
 * 
 * 用法：
 *   1、<!--ajax deli-->code<!--/ajax deli--> Ajax请求获取判断页面。
 *   2、{sub member_header} 获取子模板，简单起见，只支持一级获取。Ajax请求不支持。
 *   3、变量包含：{$var}、/ *{$var}* /(comment in javascript)、控制器复制变量保存在var中。数组支持js对象书写方式。
 *   4、{if}{/if}对应if循环；{loop}{/loop}对应foreach循环；{for}{/for}对应for循环。
 *   5、{eval} 对应php的eval函数。
 *   6、{php}在模板执行php代码。
 *   7、<$!--note--$> 会被保留的HTML注释。
 *   8、{block var}{/block} 用于代码块模板文件中前后替换。替换用{blockHolder var}
 *  updates:
 *   1、会清除HTML、JS注释
 *   2、2012.12.12 删除buffer_ajax函数，整合到buffer中，精简结构。
 *   
 * @author hanhui
 * @since 2013/08/11 来自原ylstu项目
 */
class Complie
{
	protected $v = array ();
	protected $viewFilePath = '';
	protected $viewTempate;
	protected $ajax = 0;
	//ajax匹配项目
	protected $ajax_delimiter = '';
	//模板最后更新时间
	private $templateLifeSin = array ();
	//递归解析最多允许3层级
	private $recursiveSubLimit = 3;
	private static $instance;
	
	function __construct()
	{
	}

	/**
	 * 模板解析函数
	 */
	function parse($tp, $cache)
	{
		if (! file_exists ( $tp ))
			die ( 'template:' . str_ireplace ( sys_root, '', $tp ) . ' Not Exists!' );
		if (! empty ( $this->viewTempate )) {
			$con = $this->viewTempate;
		} else {
			$con = file_get_contents ( $tp );
		}
		
		if ($this->ajax) {
			$this->parseAjax ( $con ); //引用传递
		}
		
		//{sub member_header}
		$con = preg_replace ( "/\{sub\s+([^\}]+)\}/ies", "\$this->loadsub('\\1')", $con );
		
		//删除模板注释
		$con = preg_replace ( '/\<\!\-\-.*?\-\-\>[\r\n\s]*/is', '', $con );
		
		//清除js中的模板变量注释
		$con = str_replace ( array ('/*{', '}*/' ), array ('{', '}' ), $con );
		
		//删除js注释
		$con = preg_replace ( '/\s*?\/\*.*?\*\/[\r\n\s]*/i', "\n", $con );
		
		$this->parseBlock ( $con );
		
		/*
		 * 先处理变量 按官方正则匹配
		 * $svar.config.site_name -> $_g['svar']['config']['site_name']
		 * ..................#作为数组嵌套区分...............
		 * $svar.config [$k#v] site_name -> 
		 * $_g['svar']['config [$k#v] site_name']->
		 * $_g['svar']['config '][$_g['k']['v']][' site_name']
		 */
		$con = preg_replace ( '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\.\[\]\$\#]*)/ies', "\$this->getvar('\\1')", $con );
		
		//{@MYROOT} echo buffer out | 特殊形式 $this->var 
		$con = preg_replace ( '/\{([@$][a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\]\'\-\>\$]*)\}/is', '<?php echo \1; ?>', $con );
		
		//{if $a['c']>$b} {if @CUR=='index'}  {if strexists($list['font'],'bold')}
		$con = preg_replace ( '/\{if\s+(.+?)\}/', '<?php if(\1){ ?>', $con );
		$con = preg_replace ( '/\{else\}/', '<?php }else{ ?>', $con );
		
		//{elseif $action=='column'}
		$con = preg_replace ( '/\{elseif\s+(.+?)\}/', '<?php }elseif(\1){ ?>', $con );
		$con = preg_replace ( '/\{\/if\}/', '<?php } ?>', $con );
		
		//{loop $re['matches'] $k $v}
		$con = preg_replace ( '/\{loop\s+(.+?)\s+(.+?)\s+(.+?)\}/', '<?php foreach(\1 as \2=>\3){ ?>', $con );
		$con = preg_replace ( '/\{\/loop\}/', '<?php } ?>', $con );
		
		//{eval "include parseout('searchform');"}
		$con = preg_replace ( '/\{eval\s+(.+?)\}/', '<?php eval(\1); ?>', $con );
		
		//{for $i=0;$i<=3;$i++}
		$con = preg_replace ( '/\{for\s+([^\}]*?)\}/', '<?php for(\1){ ?>', $con );
		$con = preg_replace ( '/\{\/for\}/', '<?php }?>' . "\n", $con );
		$con = preg_replace ( '/\{php\s+([^\}]*?)\}/', '<?php \\1 ?>', $con );
		
		//保留必要注释<$!--note--$>
		$con = preg_replace ( '/\<\$\!\-\-(.*?)\-\-\$\>/is', '<!--\1-->', $con );
		
		if (! empty ( $con )) {
			if (! is_dir ( dirname ( $cache ) )) {
				if (! mkdir ( dirname ( $cache ), 0777, 1 )) {
					error_output ( 'No right to write disk!' );
				}
			}
			$fp = fopen ( $cache, 'wb' );
			fwrite ( $fp, $con );
			fclose ( $fp );
			return TRUE;
		} else {
			error_output ( 'Template ' . $this->viewFilePath . ' parsed empty!' );
		}
	}

	/**
	 * 变量处理函数
	 */
	function getvar($str)
	{
		$r = "\$_g['" . str_ireplace ( '.', "']['", $str ) . "']";
		
		if (strexists ( $r, '[$' )) {
			preg_match_all ( '/\[\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\#]*)\]/is', $r, $m );
			
			if (! empty ( $m [1] )) {
				foreach ( $m [1] as $k => $v ) {
					$v = "'][\$_g['" . str_ireplace ( '#', "']['", $v ) . "']]['";
					$r = str_ireplace ( $m [0] [$k], $v, $r );
				}
				$r = str_ireplace ( "['']", '', $r );
			}
		}
		
		return $r;
	}

	/**
	 * 触发解析函数
	 * @aim 判断模板是否需要重新解析。首先判断主模板，然后通过模板缓存判断子模板。
	 * 通过是否传递$ajax_delimiter确定是否仅传输ajax部分内容
	 */
	function buffer($temp, $ajax_delimiter = '')
	{
		global $_g;
		$this->viewFilePath = sys_root . $temp . '.htm.php';
		
		if ($ajax_delimiter) {
			$this->ajax = 1;
			$this->ajax_delimiter = $ajax_delimiter;
			$cache = sys_root . "cache/{$temp}_{$ajax_delimiter}.ajax.php";
		} else {
			$cache = sys_root . "cache/$temp.cache.php";
		}
		
		if (! file_exists ( $cache )) {
			$this->parse ( $this->viewFilePath, $cache );
		} else {
			//模板自动刷新
			if ($this->v ['config'] ['cfg_viewFilePathrefresh']) {
				$tt_viewFilePath = filemtime ( $this->viewFilePath );
				$tt_cache = filemtime ( $cache );
				$this->templateLifeSin [] = $tt_viewFilePath;
				
				//主模板未更新，则检测子模板
				if ($tt_viewFilePath <= $tt_cache) {
					$this->sub_refresh ();
				}
				
				if (max ( $this->templateLifeSin ) > $tt_cache) {
					unlink ( $cache );
					return $this->buffer ( $temp, $ajax_delimiter );
				}
			} else {
				//模板间隔刷新
				if (! empty ( $this->v ['config'] ['viewFilePathcache_life'] ) && $this->v ['time'] - filemtime ( $cache ) >= $this->v ['config'] ['viewFilePathcache_life']) {
					unlink ( $cache );
					return $this->buffer ( $temp, $ajax_delimiter );
				}
			}
		}
		
		include $cache;
		
		\module\common\output::getInstance ()->outputBuffer ();
	}

	/**
	 * 递归检测子模板是否刷新
	 */
	function sub_refresh($viewFilePath = '')
	{
		if (empty ( $this->viewFilePath ))
			return false;
		$tls = array ();
		$this->viewTempate = file_get_contents ( $this->viewFilePath );
		$this->viewTempate = preg_replace ( "/\{sub\s+([^\}]+)\}/ies", "\$this->loadsub('\\1')", $this->viewTempate );
		//out($this->templateLifeSin);die();
		return $tls;
	}

	/**
	 * 加载子模板内容
	 * @date 2012.12.2 添加多层级子模板递归检测刷新模板机制
	 * @注意有递归 $level 进入时已经是1级，大于递归次数直接返回
	 */
	function loadsub($subviewFilePath, $level = 1)
	{
		if ($level > $this->recursiveSubLimit)
			return '';
		$viewFilePath = sys_root . "$subviewFilePath.htm.php";
		$this->templateLifeSin [] = filemtime ( $viewFilePath );
		$con = preg_replace ( '/' . preg_quote ( '<?php' ) . '(.*?)' . preg_quote ( "?>" ) . '[\r\n\s]*/is', '', file_get_contents ( $viewFilePath ) );
		$level ++;
		return preg_replace ( "/\{sub\s+([^\}]+)\}/ies", "\$this->loadsub('\\1', $level)", $con );
	}

	/**
	 * 解析Ajax模板
	 * @param string $con
	 */
	function parseAjax(&$con)
	{
		//无限定符 直接返回
		if (empty ( $this->ajax_delimiter ))
			return false;
			
			//ajax特定区域匹配项目
		$deli = ($this->ajax_delimiter) ? ' ' . preg_quote ( $this->ajax_delimiter ) : '';
		
		/* 存在ajax标签时按照标签，不然则全部内容
		 * <!--ajax deli-->code<!--/ajax deli-->
		*/
		preg_match_all ( "/\<\!\-\-ajax" . $deli . "\-\-\>(.*?)<\!\-\-\/ajax" . $deli . "\-\-\>/is", $con, $matches );
		
		//不能使用$matches，不是非空数组
		if (! empty ( $matches [1] )) {
			$con = "<?php\nif (! defined ( 'IN_iYunLin' )) {\n	exit ( 'Access Denied' );\n}\n?>";
			foreach ( $matches [1] as $v ) {
				$con .= $v;
			}
		}
		
		return true;
	}

	/**
	 * block模板解析
	 * @param string $con
	 * {block var}{/block} 
	 * 注：能否使用看匹配是否完整 未匹配的位置不变，清除模板符号。
	 */
	function parseBlock(&$con)
	{
		//{block var}{/block} Ajax情况下能否使用看匹配是否完整
		preg_match_all ( "/\{block\s+([^\}]*?)\}(.*?)\{\/block\}/is", $con, $blocks );
		
		//有匹配block
		if (! empty ( $blocks [0] )) {
			
			//匹配blockHolder
			preg_match_all ( "/\{blockHolder\s+([^\}]*?)\}/is", $con, $holders );
			
			if (! empty ( $holders [1] )) {
				$availHolders = array ();
				foreach ( $holders [1] as $k => $v ) {
					$availHolders [$v] = $v;
				}
				
				/**
				 * 原block内容处理
				 * 存在holder，则删除；不存在，这删除模块变量。
				 * 按照出现先后顺序合并相同的块
				 */
				$varList = array ();
				foreach ( $blocks [1] as $k => $v ) {
					if (isset ( $availHolders [$v] )) { //存在holder
						if (! isset ( $varList [$v] )) {
							$varList [$v] = $blocks [2] [$k];
						} else {
							$varList [$v] .= $blocks [2] [$k];
						}
						$con = str_replace ( $blocks [0] [$k], "", $con );
						unset ( $blocks [1] [$k] );
					}
				}
				
				//插入新的block内容
				foreach ( $varList as $k => $v ) {
				}
			}
			
			//去除没有匹配的block标志
			if (! empty ( $blocks [1] )) {
				foreach ( $blocks [1] as $k => $v ) {
					$con = str_replace ( $blocks [0] [$k], $blocks [2] [$k], $con );
				}
			}
		}
		
		//全局清楚无效{blockHolder var}变量
		$con = preg_replace ( '/\{blockHolder\s+.*?\}/is', '', $con );
	}

	/**
	 * 返回对象单例
	 * @overwrite 覆盖父类 之类也必须存在protected static $instance;的定义
	 */
	static function getInstance()
	{
		if (! (self::$instance instanceof self)) {
			self::$instance = new self ();
		}
		return self::$instance;
	}
}