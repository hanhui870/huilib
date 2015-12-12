HuiLib, ZF like php framework. 
===================

目标：

> 1、消灭全局函数 
> 2、消灭数据库配置文件 
> 3、XSS、CSRF、SQL注入等安全问题全局化处理 
> 4、是否使用Pdo连接数据库？
> 5、必要的Test库 
> 6、统一命名规范：类、变量、方法等 
> 7、加入多语言处理 
> 8、统一服务器配置规范，做好Git版本记录
> 9、改进模板引擎变量机制，改为局部变量；添加对象获取支持。
> 10、清晰的Api(Cookie鉴权)、OAuth(授权鉴权)接口结构。Oauth技术结构：接口同Api相同，但是鉴权检测机制不同。
> 11、构架支持ackage/controller/action三级控制结构，清除SubAction，仅支持三级就够了.

TODO 性能优化：
由于使用了较多的类文件，因此准备在后期正式服务器使用APC脚本文件缓存功能，部署编译后OPCODE内容。

约定：
1、某些模型需要的数据从控制器等调用时传入，而不是模型、模块等主动去调用获取。这样更具有健壮性。
 

    整体程序结构：
    /---root目录
    /App ---应用目录
    /App/Config ---配置文件目录
    /App/Config/App.ini ---应用核心配置文件
    /App/Controller ---控制器集 三层控制器package/controller/action，第一层次可以作为类似zend的modules目录功能，区分web/api/member/admin模块。
    /App/Module ---应用程序逻辑处理模块，可能包含复杂逻辑。用于封装复杂的业务逻辑
    /App/Model ---应用“数据模型”层，仅处理表接口位置；单表相关简单逻辑放这
    /App/View --应用视图层
    /App/Lang ---语言处理层
    /App/Bin ---bin下脚本处理目录
    /App/Test ---应用区测试用例
    /App/Data ---默认数据存放区，要求程序可写，放置缓存等
    /App/Data/Cache ---放置缓存
    /App/Public/Attach ---上传的附件，要求程序可写
    /App/Doc ---应用端文档
    ----以下所有芸临项目共用
    /Lib ---系统公用库目录
    /Lib/Bootstrap.php 入口引导文件，单例Singleton，获取app、config等实例
    /Lib/App ---应用初始化框架
    /Lib/Db ---数据库目录
    /Lib/Cache ---缓存库 加入cache_apc
    /Lib/Cdn ---服务器间附件同步接口
    /Lib/Route ---路由成
    /Lib/Test ---系统单元测试目录
    /Lib/Tool ---系统工具集
    /Lib/Doc ---系统端文档
    /Lib/View ---视图模板引擎
    /Lib/Acl ---权限控制
    /Lib/Session ---session库
    /Lib/Mail ---Mail库
    /Lib/Config ---配置解析库
    /Lib/Error --错误、异常处理库
    /Lib/Runtime ---应用执行期库
    /Lib/Helper ---辅助静态库
    /Lib/Lang ---语言模块包
    /Lib/Log ---日志模块包
    /Lib/OpenConnect ---开放连接OpenConnect模块
    
    测试构架：
    /Lib/Test 存放的是系统测试基础框架
    每个库的测试用例代码放在相关Lib文件夹下的Test文件夹，比如/Lib/Config/Test 为Config库的测试用例库。