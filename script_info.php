<?php
require_once __DIR__ . '/setting.php';

$time = time();  //現在時刻、GMTとの時差9時間を足す
$date_month = (int)gmdate("m",$time) - 1; //PC的時間ズレのチェック用
$date_str = gmdate("Y,$date_month,j,G,i,s",$time);

//非限制時間的発言で消費される時間　白
$day_say_spend_allseconds = floor(12*60*60 / $day_limit_time);
$day_say_spend_minuts = floor($day_say_spend_allseconds / 60);
$day_say_spend_seconds = $day_say_spend_allseconds % 60;
if($day_say_spend_seconds == 0)
	$day_say_spend_str = $day_say_spend_minuts . "分";
else
	$day_say_spend_str = $day_say_spend_minuts . "分" . $day_say_spend_seconds . "秒";

//非限制時間的発言で消費される時間　夜
$night_say_spend_allseconds = floor(6*60*60 / $night_limit_time);
$night_say_spend_minuts = floor($night_say_spend_allseconds / 60);
$night_say_spend_seconds = $night_say_spend_allseconds % 60;
if($night_say_spend_seconds == 0)
	$night_say_spend_str = $night_say_spend_minuts . "分";
else
	$night_say_spend_str = $night_say_spend_minuts . "分" . $night_say_spend_seconds . "秒";


//非限制時間的沈黙で経過する時間　白
$day_silence_spend_allseconds = $day_say_spend_allseconds * $silence_pass_time;
$day_silence_spend_hour = floor($day_silence_spend_allseconds / 60 / 60);
$day_silence_spend_minuts = floor( ($day_silence_spend_allseconds / 60) % 60);
if($day_silence_spend_minuts == 0)
	$day_silence_spend_str = $day_silence_spend_hour . "時間";
else
	$day_silence_spend_str = $day_silence_spend_hour . "時間" . $day_silence_spend_minuts . "分";


//非限制時間的沈黙で経過する時間　夜
$night_silence_spend_allseconds = $night_say_spend_allseconds * $silence_pass_time;
$night_silence_spend_hour = floor($night_silence_spend_allseconds / 60 / 60);
$night_silence_spend_minuts = floor( ($night_silence_spend_allseconds / 60) % 60);
if($night_silence_spend_minuts == 0)
	$night_silence_spend_str = $night_silence_spend_hour . "時間";
else
	$night_silence_spend_str = $night_silence_spend_hour . "時間" . $night_silence_spend_minuts . "分";


//非限制時間的沈黙になるまで的時間
$silence_threshhold_minuts = floor($silence_threshhold_time / 60);
$silence_threshhold_seconds = $silence_threshhold_time % 60;
if($silence_threshhold_seconds == 0)
	$silence_threshhold_str = $silence_threshhold_minuts . "分";
else
	$silence_threshhold_str = $silence_threshhold_minuts . "分" . $silence_threshhold_seconds . "秒";

//制限時間を消費後に突然死するまで的時間
$suddendeath_threshhold_minuts = floor($suddendeath_threshhold_time / 60);
$suddendeath_threshhold_seconds = $suddendeath_threshhold_time % 60;
if($suddendeath_threshhold_seconds == 0)
	$suddendeath_threshhold_str = $suddendeath_threshhold_minuts . "分";
else
	$suddendeath_threshhold_str = $suddendeath_threshhold_minuts . "分" . $suddendeath_threshhold_seconds . "秒";


//制限時間を消費後に突然死するまで的時間
$die_room_threshhold_minuts = floor($die_room_threshhold_time / 60);
$die_room_threshhold_seconds = $die_room_threshhold_time % 60;
if($suddendeath_threshhold_seconds == 0)
	$die_room_threshhold_str = $die_room_threshhold_minuts . "分";
else
	$die_room_threshhold_str = $die_room_threshhold_minuts . "分" . $die_room_threshhold_seconds . "秒";


//用戶アイコン、容量のバイトか㌔バイトか単位を決定
$icon_max_size_str = $icon_max_size > 1024 ? floor($icon_max_size/1024) . "kByte" : $icon_max_size ."Byte";


//村民登錄時のIPアドレスチェック
if($regist_one_ip_address == true)
	$regist_one_ip_address_str = $lang_regist_one_ip_address_str_enable;
else
	$regist_one_ip_address_str = $lang_regist_one_ip_address_str_disable;

$ptitle = "系統特點 - ";
include_once __DIR__ . "/header.inc.php";
echo '<style type="text/css">
<!--
body {
background-image: url("img/script_info_bg.webp");
-->
</style>';
echo '<div id="indexhi"><div id="indexh2"><b>'.$server_comment.'</b></div>';
echo '<img src="img/script_info_title.webp"> <br />';
echo '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td valign="top"><fieldset>';
echo '<legend><b>系統特點</b></legend>';

include(GetLangSet('script_info.php'));

echo '</fieldset></td></tr></table></div>';
include_once __DIR__ . "/footer.inc.php";
?>
