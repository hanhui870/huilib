<?php
/**
 * TimeZone是全部以UTC区时的时间戳为基础的，然后在区时基础上做演算
 * 
 * Etc/GMT设置以及不提倡。是违反直觉的。Etc/GMT-8代表东八区。
 * 
 * @see http://www.php.net/manual/en/timezones.others.php
 * 
 * Mysql时区设置
 * 
 * 可以通过修改my.cnf, 在 [mysqld] 之下加来修改时区。
 * default-time-zone=timezone  
 * 例如：
 * default-time-zone='+8:00'
 * 修改后记得重启msyql。
 * 注意一定要在 [mysqld] 之下加 ，否则会出现错误: unknown variable ‘default-time-zone=+8:00′
 * 
 * 另外也可以通过命令：
 * SET time_zone=timezone  
 * 例如：比如北京时间（GMT+0800）  
 * SET time_zone='+8:00' 
 * 
 * 这个和php的时区设置又有点差别，比如北京时间在php中是：
 * date_default_timezone_set('Etc/GMT-8'); 
 */
echo "unix stamp:0\n";

//-8代表东八区
echo "Etc/GMT-8:\n";
//PHP现在提倡直接通过城市设置区时 而不是通过相差时间运算
//date_default_timezone_set('Asia/Shanghai');
date_default_timezone_set('Etc/GMT-8');
echo date("Y-m-d H:i:s", 0);
echo "\n\n";


echo "Etc/GMT+0:\n";
date_default_timezone_set('UTC');
echo date("Y-m-d H:i:s", 0);
echo "\n\n";

//西一区
echo "Etc/GMT+1:\n";
date_default_timezone_set('Etc/GMT-1');
echo date("Y-m-d H:i:s", 0);
echo "\n\n";

//完整代码示例
//区时设置 通过Etc/GMT时区设置是相反的
if (!empty($userObj['timezone'])){
    $reverseZone=-intval($userObj['timezone']);
    date_default_timezone_set("Etc/GMT".($reverseZone>0 ? "+$reverseZone" : "$reverseZone"));
    
    //数据库区时设置 mysql设置后php设置又是相反的
    $db=Zend_Db_Table::getDefaultAdapter();
    $zone=intval($userObj['timezone']);
    //处理=0中间值的问题 因为设置set time_zone = '0:00';是错误的，必须有正负。set time_zone = '+0:00'; set time_zone = '-0:00';都是可以的。
    if($zone >= 0){
        $db->query("set time_zone = '+{$zone}:00';");
    }else{
        $db->query("set time_zone = '{$zone}:00';");
    }
}

?>

//以下是JS HighChart时区设置 更改时区useUTC配置必须为true
timezone=-60*parseInt(timezone);
Highcharts.setOptions({
	global: {
		useUTC: true,
		timezoneOffset:timezone
	}
});

