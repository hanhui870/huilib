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
	//默认结果集获取方式
	const DEFAULT_FETCH_STYLE=\PDO::FETCH_ASSOC;
	
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
	public function fetchAll($fetchStyle=self::DEFAULT_FETCH_STYLE)
	{
		if ($this->innerStatment==NULL) {
			throw new \HuiLib\Error\Exception ('execute查询，必须先调用Query::prepare');
		}
	
		return $this->innerStatment->fetchAll($fetchStyle);
	}
	
	/**
	 * 获取单条数据
	 * @param string $fetchStyle
	 * @throws \HuiLib\Error\Exception
	 * @return array|object
	 */
	public function fetch($fetchStyle=self::DEFAULT_FETCH_STYLE)
	{
		if ($this->innerStatment==NULL) {
			throw new \HuiLib\Error\Exception ('execute查询，必须先调用Query::prepare');
		}
	
		return $this->innerStatment->fetch($fetchStyle);
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