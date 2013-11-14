<?php
namespace HuiLib\View;

/**
 * HuiLib模板引擎解析类 
 * 
 * 用法：
 *   1、Ajax请求获取判断页面: <!--ajax delimiter-->code<!--/ajax delimiter--> 
 *   2、获取子模板: {sub header}，相对于模板目录
 *   3、变量包含: {$var}、/ *{$var}* /(comment in javascript)
 *   	   常量输出 {CONSTANT}(已经取消@符号)
 *   	   支持对象 $user->name => $this->user->name
 *   4、{if}{/if}对应if循环；{loop}{/loop}对应foreach循环；{for}{/for}对应for循环
 *   5、{eval} 对应php的eval函数。
 *   6、{php}在模板执行php代码。
 *   7、<$!--note--$> 会被保留的HTML注释。
 *   8、{block blockname}{/block blockname} 用于代码块模板文件中前后替换。替换用{blockHolder blockname}
 *   9、会清除HTML、JS注释
 *   
 * @author hanhui
 * @since 2013/09/21 重写自ylstu模板引擎
 */
class TemplateEngine
{
	/**
	 * 模板存放目录
	 * 
	 * @var string
	 */
	private $viewPath = NULL;
	
	/**
	 * 编译后缓存存放目录
	 *
	 * @var string
	 */
	private $cachePath = NULL;
	
	/**
	 * 当前正在解决的视图标记
	 *
	 * @var string
	 */
	private $view = NULL;
	
	/**
	 * ajax解析分隔符
	 *
	 * @var string
	 */
	private $ajaxDelimiter = NULL;
	
	/**
	 * 模板引擎解析后的源码
	 *
	 * @var string
	 */
	private $compiledSource = NULL;
	
	/**
	 * 子模板递归解析最多允许层次
	 *
	 * @var string
	 */
	private $recursiveSubLimit = 3;
	
	/**
	 * 子模板最后更新时间数组
	 * 
	 * @var array
	 */
	private $subTplStamp=array();
	
	/**
	 * 预获取的模板代码
	 *
	 * @var array
	 */
	private $preFetchSource=NULL;

	function __construct($view, $ajaxDelimiter = NULL)
	{
		//字母数字\/_
		if (preg_match("/[^\w\/\\\]+/i", $view)) {
			throw new \HuiLib\Error\Exception ( "Hack discovered!" );
		}
		
		$this->view=$view;
		$this->ajaxDelimiter = $ajaxDelimiter;
	}
	
	/**
	 * 安全获取模板源代码
	 * 
	 * @throws \HuiLib\Error\Exception
	 */
	private function getSourceCon()
	{
		if ($this->viewPath == NULL) {
			throw new \HuiLib\Error\Exception ( "TemplateEngine: 请设置模板源代码存放目录" );
		}
		
		if ($this->cachePath == NULL) {
			throw new \HuiLib\Error\Exception ( "TemplateEngine: 请设置模板编译缓存存放目录" );
		}
		
		//模板储存地址
		$sourceFile = $this->getTplFilePath ();
		if (! file_exists ( $sourceFile )) {
			throw new \HuiLib\Error\Exception ( "TemplateEngine: 模板{$sourceFile}不存在，请确认" );
		}
		
		$this->preFetchSource = file_get_contents ( $sourceFile );
	}

	/**
	 * 实际解析模板
	 */
	public function parse()
	{
		$this->getSourceCon();
		$source=&$this->preFetchSource;
		
		//处理Ajax内容
		if ($this->ajaxDelimiter) {
			$source = $this->procAjaxSource ( $source );
		}
		
		//应用模板解析规则
		$source = $this->appleTemplateRules ( $source );
		
		if (empty($source)) {
			throw new \HuiLib\Error\Exception ( "TemplateEngine: 模板".$this->getTplFilePath()."解析出来是空的" );
		}
		
		$this->compiledSource=$source;
		
		return $this;
	}

	/**
	 * 设置模板目录
	 */
	public function setViewPath($viewPath)
	{
		$this->viewPath = $viewPath;
		
		return $this;
	}

	/**
	 * 设置编译后缓存存放目录
	 */
	public function setCachePath($cachePath)
	{
		$this->cachePath = $cachePath;
		
		return $this;
	}

	/**
	 * 返回模板文件实际储存地址
	 * 
	 * @param string $view 需要解析的模板
	 * @return string
	 */
	public function getTplFilePath($view=NULL)
	{
		if ($view===NULL) {
			$view=$this->view;
		}
		
		return $this->viewPath . $view . '.phtml';
	}

	/**
	 * 返回模板编译后缓存实际储存地址
	 * @param string $view
	 * @return string
	 */
	public function getCacheFilePath($view=NULL)
	{
		if ($view===NULL) {
			$view=$this->view;
		}
		
		//Ajax文件
		if ($this->ajaxDelimiter) {
			$view.='_'.$this->ajaxDelimiter;
		}
		
		return $this->cachePath . $view . '.view';
	}
	
	/**
	 * 将解析内容写到磁盘缓存
	 */
	public function writeCompiled(){
		$cacheFile=$this->getCacheFilePath() ;
		$dirPath=dirname ( $cacheFile );
		if (! is_dir ( $dirPath )) {
			if (! mkdir ($dirPath, 0777, 1 )) {
				throw new \HuiLib\Error\Exception ( "TemplateEngine: 目录{$dirPath}程序没有写入权限" );
			}
		}
		
		return file_put_contents($cacheFile, $this->compiledSource);
	}
	
	/**
	 * 输出解析后内容
	 */
	public function debug(){
		echo $this->compiledSource;
	}

	/**
	 * 应用模板解析规则
	 * 
	 * @param string $source
	 */
	private function appleTemplateRules($source)
	{
		//{sub member_header}
		$source = preg_replace ( "/\{sub\s+([^\}]+)\}/ies", "\$this->procSubSource('\\1')", $source );
		
		//删除模板注释
		$source = preg_replace ( '/\<\!\-\-.*?\-\-\>[\r\n\s]*/is', '', $source );
		
		//清除js中的模板变量注释
		$source = str_replace ( array ('/*{', '}*/' ), array ('{', '}' ), $source );
		
		//删除js注释
		$source = preg_replace ( '/\s*?\/\*.*?\*\/[\r\n\s]*/i', "\n", $source );
		
		//代码块位移解析
		$source = $this->procBlockSource ( $source );
		
		//变量解析
		$source = preg_replace ( '/(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\.\[\]\$]*)/ies', "\$this->procVariable('\\1')", $source );
		
		//{if $a['c']>$b} {if CUR=='index'}  {if strexists($list['font'],'bold')}
		$source = preg_replace ( '/\{if\s+(.+?)\}/', '<?php if(\1){ ?>', $source );
		$source = preg_replace ( '/\{else\}/', '<?php }else{ ?>', $source );
		
		//{elseif $action=='column'}
		$source = preg_replace ( '/\{elseif\s+(.+?)\}/', '<?php }elseif(\1){ ?>', $source );
		$source = preg_replace ( '/\{\/if\}/', '<?php } ?>', $source );
		
		//{loop $re['matches'] $k $v}
		$source = preg_replace ( '/\{loop\s+(.+?)\s+(.+?)\s+(.+?)\}/', '<?php foreach(\1 as \2=>\3){ ?>', $source );
		$source = preg_replace ( '/\{\/loop\}/', '<?php } ?>', $source );
		
		//{eval "include parseout('searchform');"}
		$source = preg_replace ( '/\{eval\s+(.+?)\}/', '<?php eval(\1); ?>', $source );
		
		//{for $i=0;$i<=3;$i++}
		$source = preg_replace ( '/\{for\s+([^\}]*?)\}/', '<?php for(\1){ ?>', $source );
		$source = preg_replace ( '/\{\/for\}/', '<?php }?>' . "\n", $source );
		$source = preg_replace ( '/\{php\s+([^\}]*?)\}/', '<?php \\1 ?>', $source );
		
		//常量解析 {MYROOT} echo MYROOT 放在最后
		$source = preg_replace ( '/\{([$]?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\]\'\-\>\$]*)\}/is', '<?php echo \1; ?>', $source );
		
		//保留必要注释<$!--note--$>
		$source = preg_replace ( '/\<\$\!\-\-(.*?)\-\-\$\>/is', '<!--\1-->', $source );
		
		return $source;
	}
	
	/*
	 * 变量处理函数
	 * 
	 * 先处理变量，支持对象 按官方正则匹配
	* $svar.config.site_name => $this->svar['config']['site_name']
	* $name => $this->name
	* $user->name => $this->user->name
	* 
	* ..................[]数组嵌套区分, "."后内容自动加数组界定符...............
	* $userlist[$thread.uid] => $this->userlist[$this->thread['uid']]
	* $svar.config[$k.v].siteName => $this->svar['config'] [$this->k['v']] ['siteName']
	*/
	private function procVariable($variable)
	{
		//替换变量符号
		$variable = str_replace ( '$', '$this->', $variable );
		
		//$this->svar.config[$k.v]siteName
		$vInfo = explode ( '.', $variable );
		
		//第一段
		$vNew = array_shift ( $vInfo );
		
		foreach ( $vInfo as $iter => $segment ) {
			$startLeft = strpos ( $segment, '[' );
			$startRight = strpos ( $segment, ']' );
			$length = strlen ( $segment );
			
			//不能写成$var.[$ab]这样，.[]不能在一起
			if ($startLeft === 0 || $startRight === 0) {
				throw new \HuiLib\Error\Exception ( "TemplateEngine: 变量{$variable}书写出错，var.[var2写法不允许" );
			}
			
			if ($startLeft === false) {
				$startLeft = $length;
			}
			if ($startRight === false) {
				$startRight = $length;
			}
			//取小的点
			$startPoint = $startLeft <= $startRight ? $startLeft : $startRight;
			//插入数组界定付
			$vNew.="['".substr_replace($segment, "']", $startPoint, 0);
		}
		
		return $vNew;
	}

	/**
	 * 解析Ajax模板
	 * @param string $source
	 */
	private function procAjaxSource($source)
	{
		//无限定符 直接返回
		if (empty ( $this->ajaxDelimiter )) {
			return false;
		}
		
		//ajax特定区域匹配项目
		$delimiter = ($this->ajaxDelimiter) ? ' ' . preg_quote ( $this->ajaxDelimiter ) : '';
		
		/**
		 * 存在ajax标签时按照标签，不然则全部内容
		 * <!--ajax delimiter-->code<!--/ajax delimiter-->
		 */
		preg_match_all ( "/\<\!\-\-ajax" . $delimiter . "\-\-\>(.*?)<\!\-\-\/ajax" . $delimiter . "\-\-\>/is", $source, $matches );
		
		//不能使用$matches，不是非空数组
		if (! empty ( $matches [1] )) {
			$source = '';
			foreach ( $matches [1] as $v ) {
				$source .= $v;
			}
		}
		
		return $source;
	}
	
	/**
	 * 递归检测子模板是否刷新
	 */
	public function getSubTplStamp()
	{
		$this->getSourceCon();
		$this->preFetchSource = preg_replace ( "/\{sub\s+([^\}]+)\}/ies", "\$this->procSubSource('\\1')", $this->preFetchSource );

		return $this->subTplStamp;
	}

	/**
	 * 加载子模板内容
	 * 
	 * @date 2012.12.2 添加多层级子模板递归检测刷新模板机制
	 * @注意有递归 $level 进入时已经是1级，大于递归次数直接返回
	 */
	private function procSubSource($subview, $level = 1)
	{
		if ($level > $this->recursiveSubLimit)
			return '';
		
		$viewFilePath = $this->getTplFilePath ( $subview );
		if (!file_exists($viewFilePath)) {
			throw new \HuiLib\Error\Exception ( "TemplateEngine: 子模板{$viewFilePath}不存在，请确认" );
		}
		$this->subTplStamp[] = filemtime ( $viewFilePath );
		
		$source = preg_replace ( '/' . preg_quote ( '<?php' ) . '(.*?)' . preg_quote ( "?>" ) . '[\r\n\s]*/is', '', file_get_contents ( $viewFilePath ) );
		$level ++;
		
		return preg_replace ( "/\{sub\s+([^\}]+)\}/ies", "\$this->procSubSource('\\1', $level)", $source );
	}

	/**
	 * block模板解析
	 * 
	 * {block var}{/block var}
	 * 注：能否使用看匹配是否完整 未匹配的位置不变，清除模板符号。
	 * update:
	 * 20130921: 结尾也要加上var区分
	 * 
	 * @param string $source
	 * 
	 */
	private function procBlockSource($source)
	{
		//{block var}{/block} Ajax情况下能否使用看匹配是否完整
		preg_match_all ( "/\{block\s+([^\}]*?)\}(.*?)\{\/block\s+\\1\}/is", $source, $blocks );
		
		//有匹配block
		if (! empty ( $blocks [0] )) {
			
			//匹配blockHolder
			preg_match_all ( "/\{blockHolder\s+([^\}]*?)\}/is", $source, $holders );
			
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
				$blockList = array ();
				foreach ( $blocks [1] as $k => $v ) {
					if (isset ( $availHolders [$v] )) { //存在holder
						if (! isset ( $blockList [$v] )) {
							$blockList [$v] = $blocks [2] [$k];
						} else {
							$blockList [$v] .= $blocks [2] [$k];
						}
						$source = str_replace ( $blocks [0] [$k], "", $source );
						unset ( $blocks [1] [$k] );
					}
				}
				
				//插入新的block内容
				foreach ( $blockList as $key => $content ) {
					$source = preg_replace ('/\{blockHolder\s+'.preg_quote($key).'\}/is', $content, $source );
				}
			}
			
			//去除没有匹配的block标志
			if (! empty ( $blocks [1] )) {
				foreach ( $blocks [1] as $k => $v ) {
					$source = str_replace ( $blocks [0] [$k], $blocks [2] [$k], $source );
				}
			}
		}
		
		//全局清楚无效{blockHolder var}变量
		$source = preg_replace ( '/\{blockHolder\s+.*?\}/is', '', $source );
		
		return $source;
	}
}