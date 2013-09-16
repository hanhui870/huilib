<?php
namespace HuiLib\Db\Query;

/**
 * Sql语句Where结构集，支持嵌套
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class Where
{
	const WHERE_AND='and';
	const WHERE_OR='or';
	
	//三种where模式
	const PAIR='pair';
	const PLAIN='plain';
	const QUOTE='quote';
	
	//OR AND语句左右结合性
	const HAND_LEFT='left';
	const HAND_RIGHT='right';
	
	//当前对象where种类
	private $type=NULL;
	
	/**
	 * 右边AND对象
	 * @var \HuiLib\Db\Query\Where
	 */
	private $andObject=NULL;
	private $andHand=NULL;
	
	/**
	 * 右边OR对象
	 * @var \HuiLib\Db\Query\Where
	 */
	private $orObject=NULL;
	private $orHand=NULL;
	
	/**
	 * 数据库连接适配器
	 * 
	 * 因为where可能是嵌套的，要保证所有对象都能获取，仅初始化一次
	 * 
	 * @var \HuiLib\Db\Query
	 */
	private static $query=NULL;
	
	private $key=NULL;
	private $value=NULL;
	private $plainWhere=NULL;
	private $placeHolder=NULL;
	private $placeBind=NULL;
	
	private function __construct(){
		
	}
	
	/**
	 * 设置适配器，需要compile的时候必须设置
	 *
	 * @param \HuiLib\Db\Query $query
	 * @return \HuiLib\Db\Where
	 */
	public function setQuery(\HuiLib\Db\Query $query=NULL)
	{
		//已设置
		if (self::$query!==NULL) {
			return $this;
		}
	
		if (! $query instanceof \HuiLib\Db\Query) {
			throw new \HuiLib\Error\Exception ( 'Where::setAdapter:系统必须提供有效的DB adapter' );
		}
	
		self::$query = $query;
	
		return $this;
	}
	
	/**
	 * 跟另外一个条件组成and条件
	 */
	public function andCase(Where $where, $hand=self::HAND_RIGHT)
	{
		if ($this->orObject!==NULL) {
			throw new \HuiLib\Error\Exception ('AND, OR连接单实例仅支持一个');
		}
		$this->andObject=$where;
		$this->andHand=$hand;
		
		return $this;
	}
	
	/**
	 * 跟另外一个条件组成or条件
	 */
	public function orCase(Where $where, $hand=self::HAND_RIGHT)
	{
		if ($this->andObject!==NULL) {
			throw new \HuiLib\Error\Exception ('AND, OR连接单实例仅支持一个');
		}
		$this->orObject=$where;
		$this->orHand=$hand;
		
		return $this;
	}
	
	/**
	 * 渲染Pair语句
	 */
	private function renderPair(){
		return $this->key.'='.self::$query->escape($this->value);
	}
	
	/**
	 * 渲染Plain语句
	 */
	private function renderPlain(){
		return $this->plainWhere;
	}
	
	/**
	 * 渲染Quote语句
	 */
	private function renderQuote(){
		return str_ireplace('?', self::$query->escape($this->placeBind), $this->placeHolder);
	}
	
	/**
	 * 生成文字表达的SQL语句
	 */
	public function toString()
	{
		//获取自身对象代表的语句
		$method='render'.ucfirst($this->type);
		$whereString='('.$this->$method().')';
		
		if ($this->orObject) {
			if ($this->orHand==self::HAND_RIGHT) {
				$whereString='('.$whereString.' '.self::WHERE_OR. ' '. $this->orObject->toString().')';
			}else{
				$whereString='('.$this->orObject->toString().' '.self::WHERE_OR. ' '. $whereString.')';
			}
		}
		
		if ($this->andObject) {
			if ($this->andHand==self::HAND_RIGHT) {
				$whereString='('.$whereString.' '.self::WHERE_AND. ' '. $this->andObject->toString().')';
			}else{
				$whereString='('.$this->andObject->toString().' '.self::WHERE_AND. ' '. $whereString.')';
			}
		}
		
		return $whereString;
	}
	
	/**
	 * 创建结果集对象KV方法
	 * 
	 * eg.
	 * create('name', 'hanhui') => name='hanhui'
	 * 
	 * @param string $key 条件的键
	 * @param mix $value 条件的值
	 * 
	 * @return \HuiLib\Db\Query\Where
	 */
	public static function createPair($key, $value){
		$where=new self();
		$where->type=self::PAIR;
		$where->key=$key;
		$where->value=$value;
		
		return $where;
	}
	
	/**
	 * 创建结果集对象Plain方法
	 * 
	 * @param string $plainWhere 源条件语句，如"name is null"
	 * 
	 * @return \HuiLib\Db\Query\Where
	 */
	public static function createPlain($plainWhere){
		$where=new self();
		$where->type=self::PLAIN;
		$where->plainWhere=$plainWhere;
		
		return $where;
	}
	
	/**
	 * 创建结果集对象Quote方法
	 * 
	 * 只支持单个占位符或者或者数组占位
	 * 
	 * @param string $placeHolder 带占位符的语句
	 * @param mix $value 需要嵌入的值
	 * 
	 * @return \HuiLib\Db\Query\Where
	 */
	public static function createQuote($placeHolder, $placeBind){
		$where=new self();
		$where->type=self::QUOTE;
		$where->placeHolder=$placeHolder;
		$where->placeBind=$placeBind;
		
		return $where;
	}
}