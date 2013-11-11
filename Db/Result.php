<?php
namespace HuiLib\Db;

/**
 * Sql语句查询类结构集
 *
 * @author 祝景法
 * @since 2013/09/15
 */
class Result
{
	//结果集获取方式
	const ARRAY_FETCH=\PDO::FETCH_ASSOC;
	const OBJECT_FETCH=\PDO::FETCH_OBJ;
	
	/**
	 * 当前对象正在执行的获取风格
	 */
	protected $fetchStyle=self::ARRAY_FETCH;
	
	/**
	 * prepare->execute bind 查询
	 * @var \PDOStatement
	 */
	protected $innerStatment = NULL;
	
	/**
	 * 上次execute查询绑定的的参数
	 * @var array
	 */
	protected $lastBindParam = NULL;
	
	protected function __construct(\PDOStatement $statement){
		$this->innerStatment=$statement;
	}
	
	/**
	 * 执行可传递参数查询
	 *
	 * @param array $param bind键值
	 * @throws \HuiLib\Error\Exception
	 * @return \HuiLib\Db\Result
	 */
	public function execute($param=array())
	{
		if ($this->innerStatment==NULL) {
			throw new \HuiLib\Error\Exception ('execute查询，必须先调用Query::prepare');
		}
		$this->innerStatment->execute($param);
		$this->lastBindParam=$param;
		
		return $this;
	}
	
	/**
	 * 获取上次execute查询绑定的的参数
	 * 
	 * @return array:
	 */
	public function getLastBindParam()
	{
		return $this->lastBindParam;
	}
	

	/**
	 * 获取所有
	 * @param string $fetchStyle
	 * @throws \HuiLib\Error\Exception
	 * @return array|object
	 */
	public function fetchAll()
	{
		if ($this->innerStatment==NULL) {
			throw new \HuiLib\Error\Exception ('fetchAll必须先调用Query::query');
		}
	
		return $this->innerStatment->fetchAll($this->fetchStyle);
	}
	
	/**
	 * 获取单条数据
	 * @param string $fetchStyle
	 * @throws \HuiLib\Error\Exception
	 * @return array|object
	 */
	public function fetch()
	{
		if ($this->innerStatment==NULL) {
			throw new \HuiLib\Error\Exception ('fetch必须先调用Query::query');
		}
	
		return $this->innerStatment->fetch($this->fetchStyle);
	}
	
	/**
	 * 通过字段编号获取单条数据的单个字段值
	 * 
	 * 默认获取的是第一个字段
	 * 
	 * @param int $columnNumber
	 * @throws \HuiLib\Error\Exception
	 * @return array|object
	 */
	public function fetchColumn($columnNumber=0)
	{
		if ($this->innerStatment==NULL) {
			throw new \HuiLib\Error\Exception ('fetchColumn须先调用Query::query');
		}
	
		return $this->innerStatment->fetchColumn($columnNumber);
	}
	
	public function setObjectFetchStyle(){
		$this->fetchStyle=self::OBJECT_FETCH;
		
		return $this;
	}
	
	public function setArrayFetchStyle(){
		$this->fetchStyle=self::ARRAY_FETCH;
		
		return $this;
	}
	
	/**
	 * 获取语句执行结果对象
	 * @return \PDOStatement
	 */
	public function getStatement()
	{
		return $this->innerStatment;
	}
	
	/**
	 * 方法转发到statement
	 * @param int $name 调用方法
	 * @param array $arguments 调用参数
	 * @return mixed
	 */
	public function __call($name, $arguments){
		if ($this->innerStatment==NULL) {
			throw new \HuiLib\Error\Exception ('执行查询后，才能执行\PDOStatement调用');
		}
		
		return call_user_func_array(array($this->innerStatment, $name), $arguments);
	}
	
	/**
	 * 创建结果集对象工厂方法
	 */
	public static function create(\PDOStatement $statement){
		return new self($statement);
	}
}