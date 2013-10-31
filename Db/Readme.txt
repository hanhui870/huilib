HuiLib Db库操作指南

HuiLib Db库由连接器Adapter和查询器Query组成。Query封装了Delete, Insert, Select, Update, Where等相关概念。
创建原始数据连接通过DbBase类。可以通过封装过的Query类实现快速查询。

一、创建原始数据库连接
1.1 以下创建数据库主库连接，返回Adapter对象：
$db=\HuiLib\Db\DbBase::createMaster()

1.2 获取数据库连接Pdo对象或实际连接对象：
$connect=$db->getConnection();

1.3 直接发起请求：
$connect->query("select count(*) from test.test");

二、Query组件查询
2.1 Select部分
2.1.1 创建select对象
$select=\HuiLib\Db\Query::select()

2.1.2 设置查询表格等操作
$select->table('test')；

2.1.3 Where条件设置支持三种形式：
键值式(createPair)
$select->where(Where::createPair('id', 2));  //=> id='2'
文本式(createPlain)
$select->where(Where::createPlain("name is null"));  //=>"name is null"
占位式(createQuote)
$select->where(Where::createQuote('id=?', 2));  //=>"id='2'"
同时支持绑定式
$select->where(Where::createPlain('t.id=:id'));
$re=$select->prepare()->execute(array('id'=>14));

2.1.4 设置表、字段和别名
$select->table(array('AliasTable'=>'test'));  //数组键是别名
$select->columns(array('AliasId'=>'id', 'AliasTest'=>'test');  //数组键是别名

2.1.5 发起查询
$select->query();
或者这样
$connect->query($select->toString()); 

2.2 插入部分
获取插入对象
$insert=\HuiLib\Db\Query::Insert()




