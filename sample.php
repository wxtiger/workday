<?php

require 'vendor/autoload.php';
//use Vvtiger\Workday\Workday2;
//require 'vvtiger\workday\workday.php';
//use \vvtiger;
$workday = new \vvtiger\Workday();

//检查指定的日期是否是工作日
$curDate = time();
$curDate = '2021-01-07';
echo $workday->checkWorkday($curDate),"\n";


//获得两个日期之间的内容
$sDateFrom= '2021-09-30';
$sDateTo = '2021-10-10';
$sType='StockWork';
$arrTemp = $workday->getDay($sDateFrom, $sDateTo,$sType);
var_dump($arrTemp);
echo "\n";

echo "\n";
//获得N天以后工作日的日期，
$arrTemp = $workday->addDay('',300,$sType='Work');

echo date("Y-m-d",$arrTemp);
echo "\n";

echo 'OK';
return;
