<?php
//ob_start('ob_gzhandler');

$room_no = empty($_GET['room_no']) ? "" : $_GET['room_no'];
$auto_reload = empty($_GET['auto_reload']) ? "" : (int)$_GET['auto_reload'];
$play_sound = empty($_GET['play_sound']) ? "" : $_GET['play_sound'];
$dead_mode = empty($_GET['dead_mode']) ? "" : $_GET['dead_mode'];
$list_down = empty($_GET['list_down']) ? "" : $_GET['list_down'];
$showtrip = empty($_GET['showtrip']) ? "" : $_GET['showtrip'];

echo '<html>
<head>';
//include_once("analyticstracking.php");
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>汝等是人是狼？＜遊戲＞</title><link rel="stylesheet" type="text/css" href="img/font.css">';

if ($dead_mode == 'on') {
	echo "<frameset rows=\"85,*,20%\" border=2 frameborder=1 framespacing=1 bordercolor=silver >";
	echo"<frame id=\"up\" name=\"up\" src=\"game_up.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=on&heaven_mode=on&showtrip=$showtrip&list_down=$list_down#game_top\">\r\n";
	echo"<frame id=\"middle\" name=\"middle\" src=\"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=on&showtrip=$showtrip&list_down=$list_down#game_top\">\r\n";
	echo"<frame id=\"bottom\" name=\"bottom\" src=\"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&heaven_mode=on#game_top\">\r\n";
} else {
	echo"<frameset rows=\"85,*\" border=1 frameborder=1 framespacing=1 >";
	echo"<frame id=\"up\" name=\"up\" src=\"game_up.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&showtrip=$showtrip&list_down=$list_down#game_top\">\r\n";
	echo"<frame id=\"bottom\" name=\"bottom\" src=\"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&showtrip=$showtrip&list_down=$list_down#game_top\">\r\n";
}

echo '<noframes>
<body>
<h3>瀏覽器不支援框架</h3>
</body></noframes></frameset>
</html>';