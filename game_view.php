<?php
require_once __DIR__ . '/game_functions.php';

$view_mode = 'on';

//MySQLに接続
if($db->connect_error())
{
	exit;
}

set_time_limit(0);
ob_end_clean();
ob_implicit_flush();
header("X-Accel-Buffering: no");

//日付と白か夜かを取得
$res_room = $db->query("select * from room where room_no = '$room_no'");
if ($db->num_rows($res_room)) {
	$room_arr = $db->fetch_array($res_room);
} else {
	//$isold = '_old';
	$res_room = $db->query("select * from room{$isold} where room_no = '$room_no'");
	$room_arr = $db->fetch_array($res_room);
}
$room_status = $room_arr['status'];
$date = $room_arr['date'];
$day_night = $room_arr['day_night'];
$room_name = $room_arr['room_name'];
$room_comment = $room_arr['room_comment'];
$game_option = $room_arr['game_option'];
$option_role = $room_arr['option_role'];
$max_user = $room_arr['max_user'];
$game_dellook = $room_arr['dellook'];
$talkpage = $_GET['page'];
$db->free_result($res_room);

GuestHTMLHeaderOutput();       //HTMLヘッダ出力
echo "<table>\r\n";
echo "<tr>\r\n";
PlayerListOutput($game_dellook);   //Playヤーリストを出力
echo "</tr><tr>\r\n";
if($day_night == 'aftergame')
{
	VictoryOutput();
	echo "</tr><tr>\r\n";
}
ReVoteListOutput();    //再投票の時、メッセージを表示する
echo "</tr><tr>\r\n";
TalkLogOutput();          //会話ログを出力
echo "</tr><tr>\r\n";
LastWordsOutput(); //遺言を出力
echo "</tr><tr>\r\n";
DeadManOutput();         //死亡者を出力
echo "</tr><tr>\r\n";
VoteListOutput();            //投票結果出力
echo "</tr></table>\r\n";
if ($ajax != "on") {

HTMLFooterOutput();        //HTMLフッタ出力
}



//MySQLとの接続を閉じる
$db->close();

//----------------------------------------------------------
//HTMLヘッダ出力
function GuestHTMLHeaderOutput(): void
{
	global $room_no,$room_name,$room_comment,$date,$day_night,$game_option,$background_color_beforegame,$text_color_beforegame,
				$background_color_aftergame,$text_color_aftergame,$background_color_day,$text_color_day,$background_color_night,
				$text_color_night,$day_limit_time,$night_limit_time,$time_zone,$db,$isold,$auto_reload,$game_dellook,$ajax,$showtrip,$room_status,$domain_name;
	
if ($ajax != "on") {
	echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">\n";
//	include_once("analyticstracking.php");
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
	echo "<title>汝等是人是狼？[觀戰]</title>\r\n";
	echo '<meta name="description" content="必須絞盡腦汁找出人狼和妖狐進行處刑來守護村子的網頁遊戲。" />
	<meta property="og:title" content="汝等是人是狼？[觀戰] - 鑽石伺服器" />
	<meta property="og:description" content="必須絞盡腦汁找出人狼和妖狐進行處刑來守護村子的網頁遊戲。" />
	<link rel="shortcut icon" href="https://'.$domain_name.'/favicon.ico" type="image/x-icon" />
	<meta property="og:image" content="https://'.$domain_name.'/img/top_title2.webp" />'."\r\n";
	
	if($auto_reload != 0 && $auto_reload < 5) {//5秒以内な5秒に統一
		$auto_reload = 5;
	}
	
	if( ($auto_reload != 0) && ($day_night != 'aftergame') ) {
	//	echo "<meta http-equiv=refresh content=$auto_reload>\r\n";
	}
	
	echo "<style type=\"text/css\"><!--\r\n";
	$realtime_onload_js_str = empty($realtime_onload_js_str) ? "" : $realtime_onload_js_str;
	
	switch($day_night)
	{
		case 'beforegame':
		case 'aftergame':
		case 'day':
		case('night'):
		//	echo "body{background-color:$background_color_night;color:$text_color_night;}\r\n";
		//	echo "A:link { color: #8080FF; } A:visited { color: #8080FF; } A:active { color: red; } A:hover { color: red; } ";
		//	echo ".left_real_time{ color:$text_color_night; background-color:$background_color_night;font-size:11pt;border-width:0px;border-style:solid;}";
			break;
	}
	echo "--></style>\r\n";
	echo "<script src=\"img/infinite-scroll.pkgd.min.js\"></script>\n";
	echo "<script src=\"img/jquery-3.6.0.min.js\"></script>\n";
	echo "<script src=\"img/showimg.js\"></script>\n";
	echo "<SCRIPT LANGUAGE=\"JavaScript\"><!-- \r\n";
	
	 $socketinit = "";
	if ($ajax != "on" && $auto_reload > 0) {
		$socketinit = "socketinit();";
		echo '
			var SupportWebSocket = ("WebSocket" in window);
			if (!SupportWebSocket) alert("Not support WebSocket, can not use auto update mode.");'."\n";
		echo "var socket;
			function socketinit() {
				var host = \"wss://diam.ngct.net:8443/wss/".authcode("$room_no\tNOUSERID", "ENCODE")."\";
				socket = new WebSocket(host);
				//socket.onerror = disConnect;
				socket.onclose = disConnect;
				socket.onmessage = function(msg) {
					if (msg.data != 'ERROR') {\n";
				echo 'setTimeout(function() {$("#ajaxload").load("'.$_SERVER['REQUEST_URI'].'&ajax=on");},'.mt_rand(100,300).');'."\n";
				echo "\n	}
				};
			}
			var disConnect = function(){
			    setTimeout(function(){
			    	if (socket != null) {
			    		socket.close();
			    		socket=null;
			    	}
			    	socketinit();
			    },1000);
			}
			";
	}
}
	//経過時間をJavascriptでリアルタイム表示
	if(strstr((string) $game_option,"real_time") && ( ($day_night != 'beforegame') && ($day_night != 'aftergame') ) )
	{
		
		$realtime_onload_js_str = "realtime_output();";
		
		//實際時間的制限時間を取得
		$real_time_str = strstr((string) $game_option,"real_time");
		sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_minutes,$night_real_limit_minutes);
		$day_real_limit_time = $day_real_limit_minutes * 60; //秒になおす
		$night_real_limit_time = $night_real_limit_minutes * 60; //秒になおす
		
		$time = time();  //現在時刻、GMTとの時差を足す
		
		
		//最も小さな時間(場面の最初的時間)を取得
		$res_start_real_time = $db->query("select min(time) from talk{$isold} where room_no = '$room_no' and date = $date
																							and location like '$day_night%'");
		
		$start_real_time = (int)$db->result($res_start_real_time,0,0) + $time_zone;;
		
		if($start_real_time != NULL)
		{
			$pass_real_time = $time - $start_real_time; //経過した時間
		}
		else
		{
			$pass_real_time = 0;
			$start_real_time = $time;
		}
		
		
		if( $day_night == 'day') //白
		{
			$end_real_time = $start_real_time + $day_real_limit_time;
			$head_message = "　日落剩餘 ";
		}
		else //夜
		{
			$end_real_time = $start_real_time + $night_real_limit_time;
			$head_message = "　早上剩餘 ";
		}
		
		$start_date_month = (int)gmdate("m",$start_real_time);
		$start_date_str = gmdate("Y,$start_date_month,j,G,i,s",$start_real_time);
		
		$end_date_month = (int)gmdate("m",$end_real_time);
		$end_date_str = gmdate("Y,$end_date_month,j,G,i,s",$end_real_time);
		
		$istarttime = strtotime(gmdate("Y/$start_date_month/j G:i:s",$start_real_time));
		$iendtime = strtotime(gmdate("Y/$end_date_month/j G:i:s",$end_real_time));
		$diffseconds = $iendtime - time();
		
		if ($diffseconds >= 0) {
			$timeoutdplay = gmdate("剩餘時間 i 時 s 分",$diffseconds*5)." ".gmdate("(實際時間 i 分 s 秒)",$diffseconds)."";
		} else {
			$timeoutdplay = gmdate("時間超過 i 分 s 秒", time() - $end_real_time);
		}
		
		if ($ajax != "on" && $auto_reload > 0) {
			$realtime_onload_js_str = "";
		}
		
		//echo "$date_str \r\n";
		if ($ajax != "on") {
			echo "starttime = new Date($start_date_str); \r\n";
			echo "endtime = new Date($end_date_str); \r\n";
			echo "diffseconds = Math.floor((endtime - starttime)/1000); \r\n";
			echo "function realtime_output() { \r\n";
			echo " nowtime = new Date(); \r\n";
			echo " leftseconds = diffseconds - Math.floor((nowtime - starttime)/1000); \r\n";
			echo " lefttime = new Date(0,0,0,0,0,leftseconds); \r\n";
			echo " virtual_left_seconds = Math.floor(12*60*60*(leftseconds / diffseconds)); \r\n";
			echo " virtual_lefttime = new Date(0,0,0,0,0,virtual_left_seconds); \r\n";
			echo " if(leftseconds > 0){ \r\n";
			echo "  document.realtime_form.realtime_output.value = \"$head_message\" + virtual_lefttime.getHours()+\"時間\"+virtual_lefttime.getMinutes()+\"分 (實際時間 \"+lefttime.getMinutes()+\"分\"+lefttime.getSeconds()+\"秒)\"; \r\n";
			//echo "  document.realtime_form.realtime_output.value = leftseconds; \r\n";
			echo " } \r\n";
			echo " else{ \r\n";
			echo "  overseconds = Math.abs(leftseconds);\r\n";
			echo "  overtime = new Date(0,0,0,0,0,overseconds);\r\n";
			echo "  document.realtime_form.realtime_output.value = \"超過時間 \"+overtime.getMinutes()+\"分\"+overtime.getSeconds()+\"秒\"; \r\n";
			echo " } \r\n";
			echo " tid = setTimeout('realtime_output()', 1000); \r\n";
			echo "} \r\n";
		}
	}
	
	if ($ajax != "on") {
	echo " // --></SCRIPT>\r\n";
	
	echo '<style type="text/css">	.talktabletd {
		border-bottom: silver 1px dashed;
		word-break:break-all;
		}
		.talktabletd2 {
		padding:0 0 0 190px;
		}
		a { text-decoration: none }
		.table2a {
			word-wrap: break-word;
			word-break: break-all;
			width:110px;
		}
		.table2b{
			width:50px;
		}
		</style>';
	
//	echo "<style id=\"diamcss\" name=\"diamcss\"  type=\"text/css\"></style>\r\n";
//	echo diamcssout();
	echo "</head><body onLoad=\"$socketinit $realtime_onload_js_str\"><div style=\"display: none; position: absolute;\" id=\"showimg_div\"></div><div id=\"ajaxload\">\r\n";
}
	echo diamcssout();
	echo "<a name=\"#game_top\"><div id='loaddiam'></div>";
	echo "<table border=0 cellspacing=0 cellpadding=0 width=100%><tr>\r\n";
	
	$room_message = "<strong style=\"font-size:15pt;\">" . $room_name ."村</strong>　～" . $room_comment ."～[" . $room_no . "番地]<br />";
	
	
	echo "<td><span style=\"text-decoration:underline;\">$room_message</span></td>\r\n";
	echo "<td align=right>";
	/*
	echo "<a href=game_view.php?room_no=$room_no&view_mode=on>[手動更新]</a>\r\n";
	echo "<a href=game_view.php?room_no=$room_no&view_mode=on&auto=on>[自動更新]</a>\r\n";
	*/
		if ($auto_reload == 0) {
			echo "[<a href=game_view.php?room_no=$room_no&auto_reload=0&view_mode=on>手動</a>]\r\n";
		} else {
			echo "<a href=game_view.php?room_no=$room_no&auto_reload=0&view_mode=on>手動</a>\r\n";
		}
			
		if ($auto_reload == 5) {
			echo "[自動]";
		} else {
			echo " <a href=game_view.php?room_no=$room_no&auto_reload=5&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&list_down=$list_down target=_top>自動</a>\r\n";
		}
			
	//	if($auto_reload == 20) $lnkstr = "[20秒]";
	//	else $lnkstr = "20秒";
	//	echo " <a href=game_view.php?room_no=$room_no&auto_reload=20&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&list_down=$list_down target=_top>$lnkstr</a>\r\n";
		
	//	if($auto_reload == 30) $lnkstr = "[30秒]";
	//	else $lnkstr = "30秒";
	//	echo " <a href=game_view.php?room_no=$room_no&auto_reload=30&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&list_down=$list_down target=_top>$lnkstr</a>)\r\n";
	$url = $_SERVER['REQUEST_URI'];
	$url = !str_contains((string) $url,"showtrip") ? $url."&showtrip=show" : str_replace("&showtrip=show","",(string) $url);
	echo " [<a href=$url>TRIP</a>]\r\n";
	echo "[<a href=index.php>返回</a>]\r\n";
	echo "</td>";
	
	echo "<tr><td>";
	VillageOptOutput();
	echo "</td></tr>";
	
	echo "<tr><form action=\"login.php?room_no=$room_no\" method=POST>";
	echo "<td>";
	if ($auto_reload == 0) {
		echo "<strong>帳號<input type=text name=uname size=20>";
		echo "密碼</strong><input type=password name=password size=20 style=\"txt-align:rignt;ime-mode: disabled;\">";
		echo "<input type=hidden name=login_type value=manually>";
		echo "<input type=submit value=\"登入\">";
	} else {
		echo "<b>手動狀態才能登入</b>";
	}
	echo "</td></form>";
	echo "<td align=right>";
	if($day_night == 'beforegame')
		echo "<a href=\"user_manager.php?room_no=$room_no\"><strong>[住民登錄]</strong></a>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	
	
	
	//経過時間を取得
	$pass_real_time = empty($pass_real_time) ? 0 : $pass_real_time;
	$night_real_limit_time = empty($night_real_limit_time) ? 0 : $night_real_limit_time;
	if( strstr((string) $game_option,"real_time") ) //限制時間
	{
		
		if( $day_night == 'day') //白は12時間
		{
			//残り實際時間算出
			$left_time = $day_real_limit_time - $pass_real_time;
			
			if( $left_time < 0) //マイナスになったらゼロにする
				$left_time = 0;
		}
		else //夜は6時間
		{
			//残り實際時間算出
			$left_time = $night_real_limit_time - $pass_real_time;
			
			if( $left_time < 0) //マイナスになったらゼロにする
				$left_time = 0;
		}
	}
	else //会話で時間経過制
	{
		$res_spend_time = $db->query("select sum(spend_time) from talk{$isold} where room_no = '$room_no' and date = $date
																								and location like '$day_night%'");
		$sum_spend_time = (int)$db->result($res_spend_time,0,0);
		
		if( $day_night == 'day') //白は12時間
		{
			//$day_limit_timeから引いて残り時間算出
			$left_time = $day_limit_time - $sum_spend_time;

			if( $left_time < 0) //マイナスになったらゼロにする
				$left_time = 0;

			$left_minuts = 12 * 60 / $day_limit_time * $left_time; //残り分(60分以上含む)
			$left_hour = floor($left_minuts / 60); //残り時間
			$left_minuts %= 60; //残り分
		}
		else //夜は6時間
		{
			//$night_limit_timeから引いて残り時間算出
			$left_time = $night_limit_time - $sum_spend_time;

			if( $left_time < 0) //マイナスになったらゼロにする
				$left_time = 0;

			$left_minuts = 6 * 60 / $night_limit_time * $left_time; //残り分(60分以上含む)
			$left_hour = floor($left_minuts / 60); //残り時間
			$left_minuts %= 60; //残り分
		}
	}
	
	
	echo "<table border=0 cellpadding=0 cellspacing=0><tr><td width=1000>";
	
	echo "<table border=0 cellspacing=0 cellpadding=0><tr valign=middle>";
	
	if( $date != 0)
	{
		//生存者の数を取得
		$res_live_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
			 																							and user_no > '0'");
		$live_count = $db->result($res_live_count,0);
		$live_count_str = "<small>(生存者" . $live_count . "人)</small>";
		echo "<td>$date 日目 $live_count_str </td>";
	}
	
	
	if( $day_night == 'day') //白
	{
		if( strstr((string) $game_option,"real_time") ) //限制時間
		{
			echo "<td valign=top>";
			echo "<form name=realtime_form><input value=\"$timeoutdplay\" class=left_real_time type=text name=realtime_output size=50 readonly>";
			echo "</td></form>";
		}
		elseif( ($left_hour + $left_minuts) > 0 ) //発言による仮想時間
		{
			echo "<td>";
			echo "　日落剩餘 $left_hour 小時";
			
			if($left_minuts != 0)
				echo " $left_minuts 分";
			
			echo "</td>";
		}
	}
	elseif($day_night == 'night') //夜
	{
		if( strstr((string) $game_option,"real_time") ) //限制時間
		{
			echo "<td><form name=realtime_form><input value=\"$timeoutdplay\" class=left_real_time type=text name=realtime_output size=50 readonly></td></form>";
		}
		elseif( ($left_hour + $left_minuts) > 0 ) //発言による仮想時間
		{
			echo "<td>";
			echo "　早上剩餘 $left_hour 小時";
			if($left_minuts != 0)
				echo " $left_minuts 分";
			
			echo "</td>";
		}
	}
	echo "</tr></table>";
	echo "</td>";
	

	
	echo "</tr></table>";
	
	
	if( ($day_night == 'day') && ($left_time == 0) )
		echo "　<span style=\"background-color:#CC3300;color:snow;\">快要日落了。請趕快投票</span><br />";
		
	if( ($day_night == 'night') && ($left_time == 0) )
		echo "　<span style=\"background-color:#CC3300;color:snow;\">快要早上了。請趕快投票</span><br />";

}

?>
