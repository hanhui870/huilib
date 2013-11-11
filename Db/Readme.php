<?php
//HuiLib Db库操作指南

//HuiLib Db库由连接器Adapter和查询器Query组成。Query封装了Delete, Insert, Select, Update, Where等相关概念。
//创建原始数据连接通过DbBase类。可以通过封装过的Query类实现快速查询。
//支持事务封装，提倡使用INNODB等支持事务的储存引擎。

//一、创建原始数据库连接
//1.1 以下创建数据库主库连接，返回Adapter对象：
$db=\HuiLib\Db\DbBase::createMaster();

//1.2 获取数据库连接Pdo对象或实际连接对象：
$connect=$db->getConnection();

//1.3 直接发起请求：
$connect->query("select count(*) from test.test");

//二、Query组件查询
//2.1 Select部分
//2.1.1 创建select对象
$select=\HuiLib\Db\Query::select();

//2.1.2 设置查询表格等操作
$select->table('test');

//2.1.3 Where条件设置支持三种形式：
//键值式(createPair)
$select->where(Where::createPair('id', 2));  //=> id='2'
//文本式(createPlain)
$select->where(Where::createPlain("name is null"));  //=>"name is null"
//占位式(createQuote)
$select->where(Where::createQuote('id=?', 2));  //=>"id='2'"
//同时支持绑定式
$select->where(Where::createPlain('t.id=:id'));
$re=$select->prepare()->execute(array('id'=>14));

//2.1.4 设置表、字段和别名
$select->table(array('AliasTable'=>'test'));  //数组键是别名
$select->columns(array('AliasId'=>'id', 'AliasTest'=>'test'));  //数组键是别名

//2.1.5 支持Join
$select->join(array('AliasTable'=>'name'), 't.id=n.tid', 'n.name as sname, n.sid as bbid'); //表名、ON条件、获取字段

//2.1.6 发起查询
$select->query();
//或者这样
$connect->query($select->toString()); 

//2.2 插入部分
//获取插入对象
$insert=\HuiLib\Db\Query::Insert();

//2.2.1 键数组、值数组插入模式
//insert into test (field1, field2) values ('fvalue1', 'fvalue2') ;
$insert->fields(array('field1','field2'))->values(array('fvalue1', 'fvalue2'));

//insert into test (field1, field2) values ('fvalue1', 'fvalue2'), ('fvalue11', 'fvalue22') ;
$insert->values(array('fvalue11', 'fvalue22')); //附加 前面的

//2.2.2 关联数组插入模式
//insert into test (field1, field2) values ('fvalue1', 'fvalue2'), ('fvalue11', 'fvalue22') ;
$insert->kvInsert(array('field1'=>'fvalue1', 'field2'=>'fvalue2'))->values(array('fvalue11', 'fvalue22'), array('fvalue11', 'fvalue22'));

//2.2.3 Duplicate Key Update模式
//需要注意dupFields和dupValues的关联性，弱耦合
$insert->enableDuplicate(true); //开启自动Dup更新模式
//insert into test set field1='fvalue1', field2='fvalue2' on duplicate key update field1='newfvalue1', num=num+1 ;
$insert->fields(array('field1','field2'))->dupFields(array('field1', 'num'))
		  ->values(array('fvalue1', 'fvalue2'), array('field2'=>'newfvalue1', array('plain'=>'num=num+1')));

//2.3 更新部分
//获取更新对象
//update tableTest set field1='fvalue1', num=num+1 where (id='16') ;
$update=\HuiLib\Db\Query::update('tableTest');
//支持两种更新Set模式，KV模式和Plain模式
$update->sets(array(
							'field1'=>'fvalue1',//KV模式
							'num'=>array('plain'=>'num=num+1') //Plain模式
						));
//绑定条件
$update->where(Where::createPair('id', '16'));

//2.4 删除部分
//获取删除对象
//delete from tableTest where (id='2') limit 10 ;
$delete=\HuiLib\Db\Query::delete('tableTest');
//设置删除条件
$delete->where(Where::createPair('id', '2'));
//设置删除行数
$delete->limit(10);






