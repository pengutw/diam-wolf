<?php
require_once __DIR__ . '/game_functions.php';
$room_no = (int)$_GET['room_no'];
if ($room_no === NULL) {
	exit;
}
$result = $db->query("SELECT day_night from room where room_no = '".$room_no."';");
$day_night = $db->fetch_array($result);
$daycc = "";
if ($day_night['day_night'] == "beforegame") {
	$background_color = $background_color_beforegame;
	$text_color = $text_color_beforegame;
	$a_color = 'blue';
	$a_vcolor = 'blue';
	$a_acolor = 'red';
}
if ($day_night['day_night'] == "aftergame") {
	$background_color = $background_color_aftergame;
	$text_color = $text_color_aftergame;
	$a_color = 'blue';
	$a_vcolor = 'blue';
	$a_acolor = 'red';
}
if ($day_night['day_night'] == "night") {
	$background_color = $background_color_night;
	$text_color = $text_color_night;
	$a_color = '#8080FF';
	$a_vcolor = '#8080FF';
	$a_acolor = 'red';
}
if ($day_night['day_night'] == "day") {
	$background_color = $background_color_day;
	$text_color = $text_color_day;
	$a_color = 'blue';
	$a_vcolor = 'blue';
	$a_acolor = 'red';
}
header("Content-type: text/css");
echo "<style id=\"diamcssajax\" type=\"text/css\">\n";
echo "body{background-color:$background_color;color:$text_color;}\n";
echo "A:link { color: $a_color; } A:visited { color: $a_vcolor; } A:active { color: $a_acolor; } A:hover { color: red; }\n";
if ($day_night['day_night'] != "aftergame") {
	echo ".left_real_time{ color:$text_color; background-color:$background_color;font-size:11pt;border-width:0px;border-style:solid;}\n";
}
echo "</style>";
?>
