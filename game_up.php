<?php
//ob_start('ob_gzhandler');
require_once __DIR__ . '/game_functions.php';

session_start();
$session_id = session_id();

//MySQLに接続
if($db->connect_error())
{
	exit;
}

if( ($uname = SessionCheck($session_id) ) == NULL ) {	
	echo '<html><head><title>Session驗證錯誤</title><link rel="stylesheet" type="text/css" href="img/font.css">
			</head><body bgcolor=aliceblue>
			<br /><br />';
	echo "　　　　Session驗證錯誤<br />";
	echo "　　　　<a href=index.php target=_top style=\"color:blue;\">首頁</a>重新登錄</body></html>";
	return;
}

$room_no = empty($_GET['room_no']) ? "" : $_GET['room_no'];
$auto_reload = empty($_GET['auto_reload']) ? "" : (int)$_GET['auto_reload'];
$play_sound = empty($_GET['play_sound']) ? "" : $_GET['play_sound'];
$list_down = empty($_GET['list_down']) ? "" : $_GET['list_down'];
$dead_mode = empty($_GET['dead_mode']) ? "" : $_GET['dead_mode'];
$heaven_mode = empty($_GET['heaven_mode']) ? "" : $_GET['heaven_mode'];

$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=$showtrip&list_down=$list_down"; //phpの引数を格納

//自分のハンドルネーム、役割、生存を取得
$res_user = $db->query("select uid,user_no,handle_name,role,live,trip from user_entry
							where room_no = '$room_no' and uname = '$uname' and user_no > '0'");
							
$user_arr = $db->fetch_array($res_user);
$user_no = $user_arr['user_no'];
$role = $user_arr['role'];
$live = $user_arr['live'];
$trip = $user_arr['trip'];
$handle_name = $user_arr['handle_name'];
$userid = $user_arr['uid'];
if($trip == '') $trip = "NO_TRIP";
$db->free_result($res_user);

$res_room_stat = $db->query("select day_night, game_option, status, max_user  from room where room_no = '$room_no'");
$room_stat = $db->fetch_assoc($res_room_stat);
$now_day_night = $room_stat['day_night'];
$game_option = $room_stat['game_option'];
$room_status = $room_stat['status'];
$max_user = $room_stat['max_user'];

echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css">';
//include_once("analyticstracking.php");
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>汝等是人是狼？＜發言＞</title>
<style type="text/css">
html,body {
 margin:0;
 padding:0;
 }
</style>
<script src="img/jquery-3.6.0.min.js"></script>
<script type="text/javascript">';
echo "
$(document).ready(function() {
	$(\"#say\").keydown(function (e) {
		if (e.keyCode == 13 && e.ctrlKey) {
			$(\"#submit\").click();
			//self.document.send.font_type.options[1].selected = true;
		}
	});
});

function reloadgame(){
	document.forms['reloadsend'].submit();
}

function reload_middleframe(){
//	parent.frames['middle'].document.forms['middle_reloadform'].submit();
}
var socket;
function socketinit() {
	var host = \"wss://diam.ngct.net:8443/wss/".authcode("$room_no\t$userid", "ENCODE")."\";
	socket = new WebSocket(host);
	//socket.onerror = disConnect;
	socket.onclose = disConnect;
	socket.onmessage = function(msg) { 
		if (msg.data != 'ERROR') {\n";
		if ($auto_reload > 0) {
			if ($dead_mode == 'on') {
				echo 'setTimeout(function() {$.ajax({method: "GET",url: "check_day.php?room_no='.$room_no.'&ajax=on",cache: false,dataType:"script"}).done(function(text) {$(window.parent.frames["up"].document).find("#diamcssajax").replaceWith(text);});},'.mt_rand(100,300).');'."\n";
				//echo 'setTimeout(function() {$.ajax({method: "GET",url: "game_play.php?room_no='.$room_no.'&auto_reload='.$auto_reload.'&play_sound='.$play_sound.'&dead_mode=on&showtrip='.$showtrip.'&list_down='.$list_down.'&ajax=on",cache: false,dataType:"html"}).done(function(response) {$(window.parent.frames["middle"].document).find("#ajaxload").html(response);});},'.mt_rand(100,300).');'."\n";
				//echo 'setTimeout(function() {$.ajax({method: "GET",url: "game_play.php?room_no='.$room_no.'&auto_reload='.$auto_reload.'&play_sound='.$play_sound.'&heaven_mode=on&ajax=on",cache: false,dataType:"html"}).done(function(response) {$(window.parent.frames["bottom"].document).find("#ajaxload").html(response);});},'.mt_rand(100,300).');'."\n";
				echo 'setTimeout(function() {$(window.parent.frames["middle"].document).find("#ajaxload").load("game_play.php?room_no='.$room_no.'&auto_reload='.$auto_reload.'&play_sound='.$play_sound.'&dead_mode=on&showtrip='.$showtrip.'&list_down='.$list_down.'&ajax=on");},'.mt_rand(100,300).');'."\n";
				echo 'setTimeout(function() {$(window.parent.frames["bottom"].document).find("#ajaxload").load("game_play.php?room_no='.$room_no.'&auto_reload='.$auto_reload.'&play_sound='.$play_sound.'&heaven_mode=on&ajax=on");},'.mt_rand(100,300).');'."\n";
			} else {
				if ($now_day_night == 'day' || $now_day_night == 'night') {
					echo 'setTimeout(function() {$.ajax({method: "GET",url: "check_day.php?room_no='.$room_no.'&ajax=on",cache: false,dataType:"script"}).done(function(text) {$(window.parent.frames["up"].document).find("#diamcssajax").replaceWith(text);});},'.mt_rand(100,300).');'."\n";
				}
				//echo 'setTimeout(function() {$.ajax({method: "GET",url: "game_play.php?room_no='.$room_no.'&auto_reload='.$auto_reload.'&play_sound='.$play_sound.'&showtrip='.$showtrip.'&list_down='.$list_down.'&ajax=on",cache: false,dataType:"html"}).done(function(response) {$(window.parent.frames["bottom"].document).find("#ajaxload").html(response);});},'.mt_rand(100,300).');'."\n";
				echo 'setTimeout(function() {$(window.parent.frames["bottom"].document).find("#ajaxload").load("game_play.php?room_no='.$room_no.'&auto_reload='.$auto_reload.'&play_sound='.$play_sound.'&showtrip='.$showtrip.'&list_down='.$list_down.'&ajax=on");},'.mt_rand(100,300).');'."\n";

			}
		}
		echo"\n}
	};
}

var disConnect = function(){
	setTimeout(function(){
		socketquit();
		socketinit();
	},".mt_rand(1000,1500).");
}
function send_data(){
	$(\"#message\").val($(\"#say\").val());
	$.ajax({
        url: \"game_play.php?$php_argv\",
        data: $('#send').serialize(),
        method:\"POST\",
        dataType:'text',
        cache: false,
        success: function(msg){
        	reloadgame();
        }
    });
    if ($(\"#say\").val() != '') {
		socketsend();
	}
}
function socketsend(){
	if ($(\"#sayauth\").val() != '') {
		socket.send($(\"#sayauth\").val());
	}
}
function socketquit(){
	if (socket != null) {
		socket.close();
		socket=null;
	}
}
function reconnect() {
	socketquit();
	socketinit();
	alert(\"Reconnect ok.\"); 
}
function reloadgame() {
	$(\"#say\").val(\"\");
	$(\"#say\").focus();
	$(\"#font_type\").get(0).selectedIndex = 1;
	$(\"#font_type_b\").prop(\"checked\", false);
	$(\"#font_type_del\").prop(\"checked\", false);
\n";
if ($auto_reload == '' || $auto_reload <= 0) {
	if ($dead_mode == 'on') {
		echo "window.parent.frames['middle'].location.href = \"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=on&showtrip=$showtrip&list_down=$list_down\";\n";
		echo "window.parent.frames['bottom'].location.href = \"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&heaven_mode=on\";\n";
	} else {
		echo "window.parent.frames['bottom'].location.href = \"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&showtrip=$showtrip&list_down=$list_down\";\n";
	}
	if ($now_day_night == 'day' || $now_day_night == 'night') {
		echo '$.ajax({method: "GET",url: "check_day.php?room_no='.$room_no.'&ajax=on",cache: false,dataType:"script"}).done(function(text) {$(window.parent.frames["up"].document).replaceWith("#diamcssajax").text(text);});'."\n";
	}
}
//}
echo "}
</script>\n";
echo diamcssout();
echo '</head>
<body onLoad="socketinit();reloadgame();">';

//靈話モードの時は発言用フレームでリロード、書き込みしたときに真ん中のフレームもリロードする
$middle_reload_str = $heaven_mode == 'on' ? "reload_middleframe();" : '';

//送信用フォーム
echo "<form id=\"send\" name=\"send\" method=\"POST\" onSubmit=\"$middle_reload_str\">\r\n";

echo '<a name="game_top">
<table border=0 cellpadding="0" cellspacing="0"><tr>
<td><textarea name="say" id="say" rows=3 cols=70 wrap=soft style="font-size:12pt;"></textarea><input name="sayauth" id="sayauth" type="hidden" value="'.authcode("$room_no&&$play_sound&&$dead_mode&&$heaven_mode&&$showtrip&&$list_down&&$userid", "ENCODE").'" /></td>
<td valign=bottom>
<table cellpadding="0" cellspacing="0" border=0>
<tr><td>
<input id="submit" type="button"  value="送出發言" onclick="send_data();">
</td><tr>
<tr><td>';

if (strstr((string) $role, 'wfasm') && $now_day_night == 'night' && $live = 'live') {
    echo '<select id=font_type name=font_type style="width:100px;">
	<option value=strong >大字
	<option value=strong selected>大字
	<option value=strong >還是大字';
} elseif (strstr((string) $role, 'wfwtr') && $now_day_night == 'night' && $live = 'live') {
    echo '<select id=font_type name=font_type style="width:100px;">
	<option value=weak >小字
	<option value=weak selected>小字
	<option value=weak >還是小字';
} else {
	echo '<select id=font_type name=font_type style="width:100px;">
	<option value=strong>強勢發言
	<option value=normal selected>普通發言
	<option value=weak>小聲發言';
}

if(strstr((string) $role, 'GM')) {
	echo '<option value=heaven>天國發言';
	if($now_day_night == 'night') {
		echo '<option value=normal>-------';	
		echo '<option value=wolf>狼頻道';	
		echo '<option value=fox>狐頻道';	
		echo '<option value=common>共有頻道';	
		echo '<option value=lovers>戀人頻道';	
	}
}

// add "whisper to gm"
if(strstr((string) $game_option, "gm:") && strstr((string) $game_option, "as_gm") && $now_day_night == 'night') {
	if(strstr((string) $role, 'GM')) {
		echo '<option value=gm_to>密語其他人</select>&nbsp;/&nbsp;發言對象';	
		
		echo '<select name=talk_target>';
		$user_row_res = $db->query("SELECT handle_name from user_entry where room_no = '$room_no' AND role <> 'GM' AND user_no > '0' ORDER BY user_no ASC");
		while( $user_row = $db->fetch_assoc($user_row_res) ) 
		{
			$this_hn = $user_row["handle_name"];
			echo "<option value=\"$this_hn\">$this_hn\r\n";
		}
		echo "</select></td>";
	
	} else {
		echo '<option value=to_gm>密語給GM';
	}
}
if(!strstr((string) $role, 'GM') && strstr((string) $game_option,"will") && $room_status != 'finished' && $live == 'live')	//  && $room_status == 'playing'
	echo '<option value=last_words>填寫遺書</select>';

echo ' <input type="checkbox" value="type_b" id=font_type_b name="font_type_b"><small>粗體</small>';
echo ' <input type="checkbox" value="type_del" id=font_type_del name="font_type_del"><small>刪除線</small>';

echo '</td></tr><tr><td>';

if($now_day_night == 'beforegame' && $uname == 'dummy_boy') {
		echo "[<a href=\"#\" onclick=\"socketsend();self.document.forms['game_recheck'].submit();\"><small>點名</small></a>]\r\n";
		echo "[<a href=\"game_vote.php?$php_argv#game_top\" name=\"vote_link\"><small><font color=\"red\">大腳踢人</font></small></a>]\r\n";
		if(!strstr((string) $game_option,"ischat")) {
			echo "[<a href=\"#\" onclick=\"socketsend();self.document.forms['game_start'].submit();\"><small>投'開始遊戲'一票</small></a>]\r\n";
		}
		echo "[<a href=\"room_modify.php?$php_argv\" target=\"_new\"><small><font color=\"red\">更改村莊選項</font></small></a>]\r\n";
} elseif($now_day_night != 'aftergame' && $live == 'live') {
	if (strstr((string) $role, 'GM')) {
			echo "[<a href=\"game_vote.php?$php_argv&aid=GM_KILL#game_top\" name=\"vote_link_kill\"><small>殺人</small></a>]\r\n";
			echo "[<a href=\"game_vote.php?$php_argv&aid=GM_RESU#game_top\" name=\"vote_link_resu\"><small>復活</small></a>]\r\n";
			echo "[<a href=\"game_vote.php?$php_argv&aid=GM_CHROLE#game_top\" name=\"vote_link_chrole\"><small>改變職業</small></a>]\r\n";
			echo "[<a href=\"game_vote.php?$php_argv&aid=GM_MARK#game_top\" name=\"vote_link_mark\"><small>標記</small></a>]\r\n";
			echo "[<a href=\"game_vote.php?$php_argv&aid=GM_DEMARK#game_top\" name=\"vote_link_demark\"><small>取消標記</small></a>]\r\n";
			echo "[<a href=\"game_vote.php?$php_argv&aid=GM_CHANNEL#game_top\" name=\"vote_link_channel\"><small>調整頻道</small></a>]\r\n";
			echo "[<a href=\"game_vote.php?$php_argv&aid=GM_DECL#game_top\" name=\"vote_link_decl\"><small>宣告勝利</small></a>]\r\n";
			echo "[<a href=\"game_vote.php?$php_argv&aid=GM_DELLOOK#game_top\" name=\"vote_link_dellook\"><small>調整靈視</small></a>]\r\n";
	} elseif($now_day_night == 'night') {
		if (strstr((string) $role,'wolf') || strstr((string) $role,'mage') || strstr((string) $role,'guard') || strstr((string) $role,'fosi') || strstr((string) $role,'cat') || strstr((string) $role, 'mytho') || strstr((string) $role, 'owlman') || strstr((string) $role, 'pengu') || strstr((string) $role, 'spy')) {
			echo "[<a href=\"game_vote.php?$php_argv#game_top\" name=\"vote_link\"><small>進行行動</small></a>]\r\n";
		}
	} elseif($now_day_night == 'day') {
		echo "[<a href=\"game_vote.php?$php_argv#game_top\" name=\"vote_link\"><small>投票處死</small></a>]\r\n";
	} elseif($now_day_night == 'beforegame') {
		if(strstr((string) $game_option, "gm:".$trip)) {
			echo "[<a href=\"#\" onclick=\"socketsend();self.document.forms['game_recheck'].submit();\"><small>點名</small></a>]\r\n";
			echo "[<a href=\"game_vote.php?$php_argv#game_top\" name=\"vote_link\"><small><font color=\"red\">大腳踢人</font></small></a>]\r\n";
			//if (!strstr($game_option,"ischat")) {
				echo "[<a href=\"#\" onclick=\"socketsend();self.document.forms['game_start'].submit();\"><small>投'開始遊戲'一票</small></a>]\r\n";
			//}
			echo "[<a href=\"room_modify.php?$php_argv\" target=\"_new\"><small><font color=\"red\">更改村莊選項</font></small></a>]\r\n";
		} else {
			echo "[<a href=\"game_vote.php?$php_argv#game_top\" name=\"vote_link\"><small>踢人</small></a>]\r\n";
			//if (!strstr($game_option,"ischat")) {
				echo "[<a href=\"#\" onclick=\"socketsend();self.document.forms['game_start'].submit();\"><small>投'開始遊戲'一票</small></a>]\r\n";
			//}
		}
		
	} elseif($dead_mode == '') {
		echo "[<a href=\"game_vote.php?$php_argv#game_top\" name=\"vote_link\"><small>投票</small></a>]\r\n";
	}
}
echo "<a href=\"index.php\" target=_top style=\"font-size:6pt\">TOP</a>\r\n".
			'</td></tr>
			</table>
		</td>
	</tr>
</table>
<input type="hidden" name="message" value="">
</form>';
//ページ読み込みときに自動でリロードするダミー送信フォーム
echo "<form name=\"reloadsend\" method=\"POST\" action=\"game_play.php?$php_argv#game_top\" target=\"bottom\"></form>\r\n";
if($now_day_night == 'beforegame') {
	echo "<form name=\"game_start\" action=\"game_vote.php?$php_argv#game_top\" target=\"up\" method=POST >\r\n";
	echo "<input type=hidden name=command value=vote>\r\n";
	echo "<input type=hidden name=situation id=situation value=\"GAMESTART\">\r\n";
	echo "</form>\r\n";
	
	if(strstr((string) $game_option, "gm:".$trip) || $uname == 'dummy_boy') {
		echo "<form name=\"game_recheck\" action=\"game_vote.php?$php_argv#game_top\" target=\"up\" method=POST >\r\n";
		echo "<input type=hidden name=command value=vote>\r\n";
		echo "<input type=hidden name=situation id=situation value=\"RECHECK\">\r\n";
		echo "</form>\r\n";
	}
}


echo '</body></html>';
?>
