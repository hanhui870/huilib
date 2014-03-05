<?php
/**
 * TimeZone是全部以UTC区时的时间戳为基础的，然后在区时基础上做演算
 * 
 * Etc/GMT设置以及不提倡。是违反直觉的。Etc/GMT-8代表东八区。
 * 
 * @see http://www.php.net/manual/en/timezones.others.php
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
