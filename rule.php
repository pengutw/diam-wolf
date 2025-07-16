<?php
require_once __DIR__ . '/setting.php';


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

$ptitle = "遊戲規則 - ";
include_once __DIR__ . "/header.inc.php";
echo '<style type="text/css">
<!--
body {
background-image: url("img/rule_bg.webp");
-->
</style>';
echo '<div id="indexhi"><div id="indexh2"><b>'.$server_comment.'</b></div>';
echo '<img src="img/rule_title.webp"> <br />';
echo '<fieldset>';
echo '<legend><b>遊戲規則</b></legend>';

include __DIR__ . '/lang/jpn/rule.php';

echo '</fieldset></div>';
include_once __DIR__ . "/footer.inc.php";
?>
