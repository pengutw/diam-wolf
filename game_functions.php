<?php
require_once 'functions.php';
require_once 'msgimg_setting.php';

if ($room_no <= $roomidserint) {
	$isold = '';
}

$voted_list = ":;:";
$timeoutdplay = "";
$room_no = is_null($room_no) ? '' : (int)$room_no;
$page = is_null($page) ? '' : (int)$page;

function randme($aa,$bb): int {
	mt_srand((double)microtime() * 1000000 * getmypid());
	return mt_rand($aa,$bb);
}

function array_randme($aa,$bb): int|string|array {
	mt_srand((double)microtime() * 1101011 * getmypid());
	return array_rand($aa,$bb);
}

function messemot($message): string|array {
	global $user_emot_dir;
	global $demota,$demotb;
	
	$urlPattern = '/(https?:\/\/[^\s<]+)/i';

    $message = preg_replace_callback($urlPattern, function ($matches): string {
        $url = $matches[1];
        return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>';
    }, (string) $message);
//	$message = preg_replace("/(\[url\])(.[^\[\"\n\r\t]*?)(\[\/url\])/i", "<a href=\"\${2}\" target=\"_blank\">\${2}</a>", $message);
	return str_replace($demota,$demotb,(string) $message);
}

//***************************************************************
//               関数｜・∀・)・∀・)…
//***************************************************************
//----------------------------------------------------------
//Session認証 返り値 ok:用戶名 NG:NULL
function SessionCheck($session_id)
{
	global $room_no,$db,$isold;

	if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
		$ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
	}else if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	}else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip_address= $_SERVER['REMOTE_ADDR'];
	}

	//SessionIDによる認証
	$result = $db->query("select uname from user_entry where room_no = '$room_no' and session_id ='$session_id'
						 and (user_no > 0 or (uname = 'dummy_boy' and user_no = -1))");
	if ($db->num_rows($result)) {
		$uname_array = $db->fetch_array($result);
	} else {
		$result = $db->query("select uname from user_entry where room_no = '$room_no' and session_id ='$session_id'
							 and (user_no > 0 or (uname = 'dummy_boy' and user_no = -1))");
		$uname_array = $db->fetch_array($result);
	}
//	$db->free_result($result);
	/*echo "右邊這些是內部測試的資料, 請別理他: ".$room_no."   ".$session_id."   ".$db->fetch_row($result)."    ".print_r(session_get_cookie_params());*/

	if($db->num_rows($result) == 1)
	{
		return $uname_array['uname'];
	}
	else
	{
		//認証失敗
		return NULL;
	}

}

//----------------------------------------------------------
//HTMLヘッダ出力
function HTMLHeaderOutput(): void
{
	global $live,$role,$dead_mode,$heaven_mode,$day_night,$auto_reload,$play_sound,$room_no,$date,$day_night,
				$background_color_beforegame,$background_color_aftergame,$background_color_day,$background_color_night,
				$text_color_beforegame,$text_color_aftergame,$text_color_day,$text_color_night,$log_mode,$view_mode,$list_down,
				$game_option,$time_zone,$db,$isold,$timeoutdplay,$room_status;
	$ajax = $_GET['ajax'];
	if($auto_reload != 0 && $auto_reload < 5) {//5秒以内な5秒に統一
		$auto_reload = 5;
	}
	$realtime_beforegame_output_str = empty($realtime_beforegame_output_str) ? "" : $realtime_beforegame_output_str;
	$realtime_output_str = empty($realtime_output_str) ? "" : $realtime_output_str;
	
	//引数を格納
	$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&list_down=$list_down";
	   	
	//MACかどうか判別
	/*if( preg_match("/Mac OS/i",$_SERVER['HTTP_USER_AGENT']) || preg_match("/Mac_PowerPC/i",$_SERVER['HTTP_USER_AGENT']) ||
																		preg_match("/Macintosh/i",$_SERVER['HTTP_USER_AGENT']) )
		$browser_MAC = true;
	else
		$browser_MAC = false;
	*/
	$browser_MAC = false;
	
	if( ($dead_mode == 'on') && ($day_night == 'aftergame') ) //ゲームが終了して靈話から返回とき
	{
		if($browser_MAC) //MACはJavaScriptで錯誤？
		{
			$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&list_down=$list_down";
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">\r\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
			echo "<title>汝等是人是狼？[Play]</title>";
			echo "</head><body>\r\n";
			echo "切換到遊戲結束畫面。";
			echo "<center>";
			echo "<a href=\"game_frame.php?$php_argv\" target=_top><big><strong>沒有切換請按這裡</strong></big></a>";
			echo "</center>";
			return;
		}
		else
		{
			$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&list_down=$list_down";
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">\r\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
			echo "<title>汝等是人是狼？[Play]</title>";
			echo "<meta http-equiv=\"refresh\" content=\"1;URL=game_frame.php?$php_argv\">\r\n";
			echo "<script type=\"text/javascript\">if(top != self){ top.location.href = self.location.href; }</script>";
			echo "</head><body>\r\n";
			echo "切換到遊戲結束畫面。";
			echo "畫面切換中<a href=\"game_frame.php?$php_argv\" target=_top>按我繼續</a>";
			exit;
		}
	}
	//ゲーム中、死んで靈話モードに行くとき
	elseif( (($live == 'dead' || $live == 'gone') && (($dead_mode != 'on') && ($day_night != 'aftergame') && ($day_night != 'beforegame') && (($log_mode != 'on')&& ($view_mode != 'on')
		&& ($heaven_mode != 'on')))))
	{
		if($browser_MAC) //MACはJavaScriptで錯誤？
		{
			$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=on&list_down=$list_down";
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">\r\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
			echo "<title>汝等是人是狼？[Play]</title>";
			echo "</head><body>\r\n";
			echo "切換到天國模式。<br />";
			echo "<center>";
			echo "<a href=\"game_frame.php?$php_argv\" target=_top><big><strong>沒有切換請按這裡</strong></big></a>";
			echo "</center>";
			return;
		}
		else
		{
			$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=on&list_down=$list_down";
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">\r\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
			echo "<title>汝等是人是狼？[Play]</title>";
			echo "<meta http-equiv=\"refresh\" content=\"1;URL=game_frame.php?$php_argv\">\r\n";
			echo "<script type=\"text/javascript\">if(top != self){ top.location.href = self.location.href; }</script>";
			echo "</head><body>\r\n";
			echo "切換到天國模式。";
			echo "畫面切換中<a href=\"game_frame.php?$php_argv\" target=_top>按我繼續</a>";
			exit;
		}
	}
	elseif(($live == 'live') && ($heaven_mode == 'on' || $dead_mode == 'on'))
	{
		
		if($browser_MAC) //MACはJavaScriptで錯誤？
		{
			$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&list_down=$list_down";
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">\r\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
			echo "<title>汝等是人是狼？[Play]</title>";
			echo "</head><body>\r\n";
			echo "切換到遊戲畫面。";
			echo "<center>";
			echo "<a href=\"game_frame.php?$php_argv\" target=_top><big><strong>沒有切換請按這裡</strong></big></a>";
			echo "</center>";
			return;
		}
		else
		{
			$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&list_down=$list_down";
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">\r\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
			echo "<title>汝等是人是狼？[Play]</title>";
			echo "<meta http-equiv=\"refresh\" content=\"1;URL=game_frame.php?$php_argv\">\r\n";
			echo "<script type=\"text/javascript\">if(top != self){ top.location.href = self.location.href; }</script>";
			echo "</head><body>\r\n";
			echo "切換到遊戲畫面。";
			echo "畫面切換中<a href=\"game_frame.php?$php_argv\" target=_top>按我繼續</a>";
			exit;
		}
	}
	
	if ($ajax != "on") {
	echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">\r\n";
//	include_once("analyticstracking.php");
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
	//手動更新以外は
	if( ($auto_reload != 0) && ($day_night != 'aftergame') )
	//	echo "<meta http-equiv=\"refresh\" content=\"$auto_reload\">\r\n";
	//;url='game_frame.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&list_down=$list_down
	echo "<title>汝等是人是狼？[Play]</title>";
	echo "<style type=\"text/css\"><!--\r\n";
	
	switch($day_night)
	{
		case('beforegame'):
			$background_color = $background_color_beforegame;
			$text_color = $text_color_beforegame;
			$a_color = 'blue';
			$a_vcolor = 'blue';
			$a_acolor = 'red';
		//	echo "body{background-color:$background_color;color:$text_color;}\r\n";
		//	echo "A:link { color: $a_color; } A:visited { color: $a_vcolor; } A:active { color: $a_acolor; } A:hover { color: red; } ";
		//	echo ".left_real_time{ color:$text_color; background-color:$background_color;font-size:11pt;border-width:0px;border-style:solid;}";
			break;
		case('aftergame'):
			$background_color = $background_color_aftergame;
			$text_color = $text_color_aftergame;
			$a_color = 'blue';
			$a_vcolor = 'blue';
			$a_acolor = 'red';
		//	echo "body{background-color:$background_color;color:$text_color;}\r\n";
		//	echo "A:link { color: $a_color; } A:visited { color: $a_vcolor; } A:active { color: $a_acolor; } A:hover { color: red; } \r\n";
			break;
		case('day'):
			$background_color = $background_color_day;
			$text_color = $text_color_day;
			$a_color = 'blue';
			$a_vcolor = 'blue';
			$a_acolor = 'red';
		//	echo "body{background-color:$background_color;color:$text_color;}\r\n";
		//	echo "A:link { color: $a_color; } A:visited { color: $a_vcolor; } A:active { color: $a_acolor; } A:hover { color: red; } \r\n";
		//	echo ".left_real_time{ color:$text_color; background-color:$background_color;font-size:11pt;border-width:0px;border-style:solid;}";
			break;
		case('night'):
			$background_color = $background_color_night;
			$text_color = $text_color_night;
			$a_color = '#8080FF';
			$a_vcolor = '#8080FF';
			$a_acolor = 'red';
		//	echo "body{background-color:$background_color;color:$text_color;}\r\n";
		//	echo "A:link { color: $a_color; } A:visited { color: $a_vcolor; } A:active { color: $a_acolor; } A:hover { color: red; } \r\n";
	//		echo ".left_real_time{ color:$text_color; background-color:$background_color;font-size:11pt;border-width:0px;border-style:solid;}";
			break;
	}
	echo "--></style>\r\n";
	echo "<script src=\"img/jquery-3.6.0.min.js\"></script>\n";
	echo "<script src=\"img/showimg.js\"></script>\n";
	echo '<style type="text/css">html,body {margin-top:0;padding:0;}</style>';
	
	//ここからJavaScript出力
	echo "<SCRIPT LANGUAGE=\"JavaScript\"><!-- \r\n";
	
	//上フレームの背景等の色をセット
	echo "function setupbgcolor() {";
	//echo " parent.frames['up'].document.bgColor = '$background_color'; \r\n";
	echo " parent.frames['up'].document.fgColor = '$text_color'; \r\n";
	echo " parent.frames['up'].document.linkColor = '$a_color'; \r\n";
	echo " parent.frames['up'].document.vlinkColor = '$a_vcolor'; \r\n";
	echo " parent.frames['up'].document.alinkColor = '$a_acolor'; \r\n";
	echo "}\r\n";
	
	
	//シャープ(%23)がGETの中にあるとラベルと競合してIEで正常に処理できないので<s>にエンコード
	$background_color_e = str_replace("#","<s>",(string) $background_color);
	$text_color_e = str_replace("#","<s>",(string) $text_color);
	$a_color_e = str_replace("#","<s>",(string) $a_color);
	$a_vcolor_e = str_replace("#","<s>",(string) $a_vcolor);
	$a_acolor_e = str_replace("#","<s>",$a_acolor);
	
	$game_vote_newlink = "game_vote.php?" . $php_argv . 
										"&bg=$background_color_e&fg=$text_color_e&a=$a_color_e&av=$a_vcolor_e&aa=$a_acolor_e";
	//上フレームの投票給URLをセット
	echo "function setvotelink() { \r\n";
	echo " for(i=0;i<parent.frames['up'].document.links.length;i++) { \r\n";
	echo "  if(parent.frames['up'].document.links[i].name == 'vote_link'){ \r\n";
	echo "   parent.frames['up'].document.links[i].href = '$game_vote_newlink';  \r\n";
	echo "   parent.frames['up'].document.links[i].hash ='game_top'  \r\n";
	echo "  } \r\n";
	echo " } \r\n";
	echo "} \r\n";
	
	}
	
	//経過時間をJavascriptでリアルタイム表示
	if( strstr((string) $game_option,"real_time") && ( ($day_night != 'beforegame') && ($day_night != 'aftergame') ) )
	{
		//JavaScriptの関数名
		$realtime_output_str = "realtime_output();";
		
		//實際時間的制限時間を取得
		$real_time_str = strstr((string) $game_option,"real_time");
		sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_minutes,$night_real_limit_minutes);
		$day_real_limit_time = $day_real_limit_minutes * 60; //秒になおす
		$night_real_limit_time = $night_real_limit_minutes * 60; //秒になおす
		
		$time = time();  //現在時刻、GMTとの時差を足す
		
		
		//最も小さな時間(場面の最初的時間)を取得
		$res_start_real_time = $db->query("select min(time) from talk{$isold} where room_no = '$room_no' and date = $date
																							and location like '$day_night%'");
		
		$start_real_time = (int)$db->result($res_start_real_time,0,0) + $time_zone;
		
		if($start_real_time != NULL)
		{
			$pass_real_time = $time - $start_real_time; //経過した時間
		}
		else
			//$pass_real_time = 0;
			$start_real_time = $time;
		
		
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
		
		$start_date_month = (int)gmdate("m",$start_real_time); //JavaScriptの場合、月は1月→0月とズレる
		$start_date_str = gmdate("Y,$start_date_month,j,G,i,s",$start_real_time);
		
		$end_date_month = (int)gmdate("m",$end_real_time);
		$end_date_str = gmdate("Y,$end_date_month,j,G,i,s",$end_real_time);
		
		$istarttime = strtotime(gmdate("Y/$start_date_month/j G:i:s",$start_real_time));
		$iendtime = strtotime(gmdate("Y/$end_date_month/j G:i:s",$end_real_time));
		$diffseconds = $iendtime - time();
		
		if ($diffseconds >= 0) {
			$timeoutdplay = gmdate("剩餘時間 i 時 s 分",$diffseconds * 5)." ".gmdate("(實際時間 i 分 s 秒)",$diffseconds)."";
		} else {
			$timeoutdplay = gmdate("時間超過 i 分 s 秒", time() - $end_real_time);
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
		echo "  document.realtime_form.realtime_output.value = \"$head_message\" + virtual_lefttime.getHours()+\"小時\"+virtual_lefttime.getMinutes()+\"分 (實際時間 \"+lefttime.getMinutes()+\"分\"+lefttime.getSeconds()+\"秒)\"; \r\n";
		//echo "  document.realtime_form.realtime_output.value = leftseconds; \r\n";
		echo " } \r\n";
		echo " else{ \r\n";
		echo "  overseconds = Math.abs(leftseconds);\r\n";
		echo "  overtime = new Date(0,0,0,0,0,overseconds);\r\n";
		echo "  document.realtime_form.realtime_output.value = \"　超過時間 \"+overtime.getMinutes()+\"分\"+overtime.getSeconds()+\"秒\"; \r\n";
		echo " } \r\n";
		echo " tid = setTimeout('realtime_output()', 1000); \r\n";
		echo "} \r\n";
		}
	}
	elseif( strstr((string) $game_option,"real_time") && ($day_night == 'beforegame') ) //開始前、サーバと的時間ズレを表示
	{
		$realtime_beforegame_output_str = "realtime_before_output();";
		
		$time = time() + $time_zone;  //現在時刻、GMTとの時差を足す
		
		$date_month = (int)gmdate("m",$time) - 1;
		$date_str = gmdate("Y,$date_month,j,G,i,s",$time);
		
		if ($ajax != "on") {
		echo "function realtime_before_output() \r\n";
		echo "{ \r\n";
		echo " php_now = new Date($date_str); \r\n";
		echo " local_now = new Date(); \r\n";
		echo " diff_sec = Math.floor( (local_now - php_now) / 1000); \r\n";
		echo " document.realtime_form.realtime_output.value = \"伺服器與本地時間差（包含網路延遲）：\" + diff_sec + \"秒\"; \r\n";
		echo "} \r\n";
		}
	}
	
	if ($ajax != "on") {
		if ($ajax != "on" && $auto_reload > 0) {
			$realtime_output_str = "";
			$realtime_beforegame_output_str = "";
		}
		
		echo " // --></SCRIPT>\r\n";
			
	//	echo "<style id=\"diamcss\" type=\"text/css\"></style>\r\n";
		//echo "</head><body onLoad=\"setupbgcolor();setvotelink();$realtime_output_str $realtime_beforegame_output_str \" >\r\n";
		echo '<style type="text/css">	.talktabletd {
			border-bottom: silver 1px dashed;
			word-break:break-all;
			}
			.talktabletd2 {
			padding:0 0 0 190px;
			}
			.table2a {
				word-wrap: break-word;
				word-break: break-all;
				width:110px;
			}
			.table2b{
				width:50px;
			}
			</style>';
		echo "</head>\n";
	}
	if ($ajax != "on") {
		echo "<body onLoad=\"setvotelink();$realtime_output_str $realtime_beforegame_output_str\" ><div style=\"display: none; position: absolute;\" id=\"showimg_div\"></div><div id=\"ajaxload\">\r\n";
	}
	echo diamcssout();
	echo "<a name=\"#game_top\"><div id='loaddiam'></div>\r\n";
}

//----------------------------------------------------------
//HTMLフッタ出力
function HTMLFooterOutput(): void
{
	echo "</div></body></html>";
}

//----------------------------------------------------------
//会話ログ出力
function TalkLogOutput($game_dellook = 1): void {
	global $room_no,$role,$uname,$live,$date,$day_night,$game_option,$db,$isold,$user_emot_dir,$handle_name,$dummy_boy_imgid,$play_uid,$talkpage;
	
	global $msg_ampm_image, $msg_guard_image, $msg_vote_image, $msg_kill_image, $msg_sys_image, $msg_mage_image, 
	$msg_room_image, $msg_wolf_image, $msg_human_image, $msg_fox_image, $msg_fosi_image, $msg_cat_image, 
	$msg_sudden_image, $msg_gm_image, $msg_rm_image, $msg_lover_image, $msg_mad_image,$time_zone,$msg_spy_image;
	
	
	if(!strstr((string) $role,'GM')) {
		$criteria = "ta.location like '$day_night%'";
	} else {
		$criteria = "ta.location != ''";
	}

	if($day_night == 'day' || $day_night == 'night')
	if(strstr((string) $role,'GM') && $c_res = $db->query("SELECT time from talk{$isold} 
													WHERE room_no = '$room_no' 
														AND date = '$date' 
														AND ( sentence LIKE 'MORNING%' OR sentence LIKE 'NIGHT%' )
														ORDER BY tid DESC LIMIT 1")) 
	{
		$c_res_time = $db->fetch_assoc($c_res);
		if($c_res_time) {
			$time_limit = $c_res_time["time"];
			$criteria = "ta.time > $time_limit";
		}
	}
	
	if ($play_uid != "" && is_numeric($play_uid) && !strstr((string) $role,'GM')) {
		$criteria = "ta.location = 'day'";
	}
	
	$datesql = " and date >= '0'";
	if ($date != "" && is_numeric($date)) {
		$datesql = " and date = '$date'";
	}
	
	//$TALKnum = 50;
	//$result = $db->query("select count(tid) from talk{$isold} where room_no = '$room_no' $datesql");
	//$talknumq = $db->result($result, 0);
	//$talkpage = max(1, intval($talkpage));
	//$start = ($talkpage - 1) * $TALKnum;
	
	// LIMIT $start,".$TALKnum.";");
	if (!$isold) {
		//$slimit = "LIMIT 3000";
	}
	
	//会話の用戶名、ハンドル名、発言、発言のタイプを取得
	
	/*$result = $db->query("select u.uid as talk_uid,
						u.uname as talk_uname,
						u.handle_name as talk_handle_name,
						u.live as talk_live,
						u.sex as talk_sex,
						u.user_no as user_no,
						u.trip as trip,
						i.color as talk_color,u.icon_no as iconno,
						ta.sentence as sentence,
						ta.font_type as font_type,
						ta.date as t_date,
						ta.location as location,tr.icon as tcolor,ta.time as ttime,u.trip
						from ((select * from user_entry where room_no = '$room_no' or room_no = '0') as u,(select * from talk{$isold} where room_no = '$room_no' $datesql order by tid DESC $slimit) as ta,user_icon i)
						left join user_trip tr on u.trip = tr.trip
						where
						$criteria
						and ((u.room_no = '$room_no' and u.uname = ta.uname
						and u.icon_no = i.icon_no)
						or (u.uid = '1' and ta.uname = 'system'
						and u.icon_no = i.icon_no))
						order by ta.tid DESC",'UNBUFFERED');*/

	$result = $db->query("SELECT
    u.uid AS talk_uid,
    u.uname AS talk_uname,
    u.handle_name AS talk_handle_name,
    u.live AS talk_live,
    u.sex AS talk_sex,
    u.user_no AS user_no,
    u.trip AS trip,
    i.color AS talk_color,
    u.icon_no AS iconno,
    ta.sentence AS sentence,
    ta.font_type AS font_type,
    ta.date AS t_date,
    ta.location AS location,
    tr.icon AS tcolor,
    ta.time AS ttime,
    u.trip
FROM
    user_entry u
    JOIN talk{$isold} ta
        ON u.uname = ta.uname
        AND (u.room_no = '$room_no' OR u.room_no = '0')
        AND ta.room_no = '$room_no'
    JOIN user_icon i
        ON u.icon_no = i.icon_no
    LEFT JOIN user_trip tr
        ON u.trip = tr.trip
WHERE
    $criteria
    $datesql
    AND (
        (u.room_no = '$room_no' AND u.uname = ta.uname AND u.icon_no = i.icon_no)
        OR (u.uid = '1' AND ta.uname = 'system' AND u.icon_no = i.icon_no)
    )
ORDER BY
    ta.tid DESC
$slimit
",'UNBUFFERED');
	
//	$talk_count = $db->fetch_row($result);
	
	$message = "<table border=0 cellpadding=0 cellspacing=0 style=\"font-size:12pt;table-layout:fixed;width: 100%;\">";
	
	//出力
	$ii = 1;
	while($talk_log_array = $db->fetch_array($result)) {
		if ($play_uid != "" && is_numeric($play_uid)) {
			if ($talk_log_array['talk_uid'] != $play_uid) {
				continue;
			}
		}
	//	$talk_log_array = $db->fetch_array($result);
		$talk_uname = $talk_log_array['talk_uname'];
		$talk_handle_name = $talk_log_array['talk_handle_name'];
		$talk_sex = $talk_log_array['talk_sex'];
		$talk_color = $talk_log_array['talk_color'];
		$sentence = $talk_log_array['sentence'];
		$font_type = $talk_log_array['font_type'];
		$location = $talk_log_array['location'];
		$tdate = $talk_log_array['t_date'];
		$talk_trip = $talk_log_array['trip'];
		if ($play_uid != "" && is_numeric($play_uid)) {
			$ttime = "Day$tdate";
		} else {
			$ttime = "".gmdate("H:i:s",$talk_log_array['ttime'] + $time_zone);
		}
		//echo $location."<br />";
		
		if ($talk_log_array['tcolor'] && $talk_log_array['iconno'] == $dummy_boy_imgid) {
			$talk_color = $talk_log_array['tcolor'];
		}
		
		if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame') {
			if (!(strstr((string) $game_option,'gm:'.$talk_log_array['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                if ($day_night != 'aftergame' && $day_night != 'beforegame') {
					$user_no2 =str_pad((string) $talk_log_array['user_no'],2,"0",STR_PAD_LEFT);
					$talk_handle_name = "玩家".$user_no2."號";
					$talk_color = "#000000";
					if ($talk_log_array['user_no'] < 0) {
						$talk_handle_name = "玩家 (已離開)";
					}
				}
            }
		}
		
		//改行を<br />タグに置換
		$sentence = str_replace("\n","<br />",(string) $sentence);
		
		$font_type2 = "";
		if (strstr((string) $font_type, 'type_del')) {
			$font_type2 = "text-decoration:line-through;";
		}
		if (strstr((string) $font_type, 'type_b')) {
			$font_type2 = $font_type2."font-weight:bolder;";
		}
		if (!strstr((string) $font_type, 'type_b')) {
			if (strstr((string) $font_type, 'strong')) {
				$font_type2 = $font_type2."font-weight:bold;";
			}elseif (strstr((string) $font_type, 'weak')) {
				$font_type2 = $font_type2."font-weight:lighter;";
			}
		}
		
		if ( strstr((string) $font_type, 'normal') || strstr((string) $font_type, 'to_gm')) {
			//文字の大きさ
			$font_type_str = "<span style=\"font-size:12pt;line-height:160%;$font_type2\">";
		} elseif ( strstr((string) $font_type, 'gm_to') ) {
			$font_type_str = "<span style=\"font-size:12pt;color:red;line-height:160%;$font_type2\">";
		} elseif ( strstr((string) $font_type, 'strong') ) {
			$font_type_str = "<span style=\"font-size:20pt;line-height:160%;$font_type2\">";
		} elseif( strstr((string) $font_type, 'weak') ) {
			$font_type_str = "<span style=\"font-size:8pt;line-height:160%;$font_type2\">";
		}
		
		//会話出力-----------------------------------------------------------------
		//抗輸出
		//echo $location."<br />";
		if (strstr((string) $location,'system') && $sentence == 'OBJECTION') {

			if ($talk_sex == 'male') {
				$this_bgcolor = '#336699';
			} else {
				$this_bgcolor = '#FF0099';
			}


			$message .= "<tr>\r\n";
			$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
			$message .= "<td class=\"talktabletd\" style=\"background-color:$this_bgcolor;color:snow;font-weight:bold;\">".msgimg($msg_mad_image)."$talk_handle_name 表示抗議</td>\r\n";
			$message .= "</tr>\r\n";
		//廢村
		} elseif (strstr((string) $location,'system') && $sentence == 'ROOMEND') {
			$message .= "<tr>\r\n";
			$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
			$message .= "<td class=\"talktabletd\" style=\"background-color:red;color:snow;font-weight:bold;\">".msgimg($msg_mad_image)."$talk_handle_name 要求廢村</td>\r\n";
			$message .= "</tr>\r\n";
		//遊戲開始投票
		} elseif (strstr((string) $location,'system') && $sentence == 'GAMESTART_DO') {
			/*
			echo "<tr>\r\n";

			echo "<td colspan=3 class=\"talktabletd\" style=\"background-color:#999900;color:snow;font-weight:bold;\">$talk_handle_name はゲーム開始に投票しました</td>\r\n";

			print("</tr>\r\n");*/
		//踢人投票
		} elseif (strstr((string) $location,'system') && strstr($sentence,'KICK_DO')) {
			$fkick = strstr($sentence, 'FKICK_DO');
			$sentence_enc = explode("\t", $sentence);
			$target_handle_name = $sentence_enc[1];
			if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
					if (!(strstr((string) $game_option,'gm:'.$talk_log_array['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy' || strstr((string) $role, 'GM'))) {
                        $target_handle_name = "(馬賽克)";
                    }
				}
			/*
			$sentence_enc = str_replace(" ","\\space;",$sentence);
			sscanf($sentence_enc,"KICK_DO\t%s",$target_handle_name);

			$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
			*/
			$message .= "<tr>\r\n";
			if($fkick) {
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"background-color:#aaaa33;color:snow;font-weight:bold;\">".msgimg($msg_rm_image)."$talk_handle_name 村長對 $target_handle_name 強制踢出</td>\r\n";
			}	else{
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"background-color:#aaaa33;color:snow;font-weight:bold;\">".msgimg($msg_human_image)."$talk_handle_name 對 $target_handle_name 投票踢出</td>\r\n";
			}
			$message .= "</tr>\r\n";		
		//用戶名:system は$sentenceをそのまま出力(系統メッセージ)
		} elseif ($talk_uname == 'system') {
			$message .= "<tr>\r\n";
			if (strstr($sentence,'MORNING') && $location == 'day system') {
				sscanf($sentence,"MORNING\t%d",$morning_date);

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"background-color:#efefef;color:black;font-weight:bold;\">".msgimg($msg_ampm_image)."< < 早晨來臨 $morning_date 日目的早上開始 > > </td>\r\n";
			} elseif (strstr($sentence,'NIGHT') && $location == 'night system') {
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"background-color:#efefef;color:black;font-weight:bold;\">".msgimg($msg_ampm_image)."< < 日落、黑暗的夜晚來臨 > > </td>\r\n";
			} else {
				if (!strstr((string) $font_type, 'normal')){
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
					$message .= "<td class=\"talktabletd\" style=\"background-color:#efefef;color:red;\">$font_type_str $sentence</span></td>\r\n";
				} else{
					if ($day_night != 'aftergame' && $day_night != 'beforegame') {
						if (strstr((string) $game_option,"usr_guest") && $location == 'beforegame system') {
							if (strstr($sentence,"來到村莊")) {
								$sentence2 = explode("&nbsp;", $sentence);
								$sentence = $sentence2[0]."&nbsp;玩家 來到村莊大廳";
							}
							if (strstr($sentence,"離開這個")) {
								$sentence2 = explode("&nbsp;", $sentence);
								$sentence = $sentence2[0]."&nbsp;玩家 離開這個村莊了";
							}
							if (strstr($sentence,"進行點名")) {
								$sentence2 = explode(" ", $sentence);
								$sentence3 = explode("&nbsp;", $sentence);
								$sentence = $sentence3[0]."&nbsp;".$sentence2[2]." ".$sentence2[3];
							}
						}
					}
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
					$message .= "<td class=\"talktabletd\" style=\"background-color:#efefef;color:black;font-weight:bold;\">$sentence</td>\r\n";
				}
			}
			$message .= "</tr>\r\n";
		//開始前と終了後
		} elseif ($day_night == 'beforegame' || $day_night == 'aftergame') {
			$message .= "<tr>\r\n";
			$sentence = messemot($sentence);
			$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
			$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
			$message .= "<font color=$talk_color>◆</font><a target=\"_blank\" href=\"trip.php?go=trip&id=$talk_trip\" style=\"text-decoration: none;color:#000000;\" onMouseOver=\"showimage('user_".$talk_log_array['user_no']."',1)\" onMouseOut='hideimage()'>$talk_handle_name</a></td>";
			$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
			$message .= "$font_type_str $sentence </span>";
			$message .= "</td>\r\n";
			$message .= "</tr>\r\n";
		} elseif ( strstr($sentence,'VOTE_KILL')) {
		//ゲーム中、生きている人のお白
		} elseif ($live == 'live' && $day_night == 'day' && $location == 'day') {
			$message .= "<tr>\r\n";
			$sentence = messemot($sentence);
			$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
			$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
			$message .= "<font color=$talk_color>◆</font><a target=\"_blank\" href=\"trip.php?go=trip&id=$talk_trip\" style=\"text-decoration: none;color:#000000;\" onMouseOver=\"showimage('user_".$talk_log_array['user_no']."',1)\" onMouseOut='hideimage()'>$talk_handle_name</a></td>";
			$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
			$message .= "$font_type_str $sentence </span>";
			$message .= "</td>\r\n";
			$message .= "</tr>\r\n";
		// GM	Broadcast
		} elseif (strstr((string) $location, 'gm_bc')) {
			$locarr = explode(' ', (string) $location);
			$targ_loc = $locarr[2];
			$sentence = messemot($sentence);

			if($targ_loc == 'heaven' && (strstr((string) $role,'GM') || $live == 'dead')) {
				$message .= "<tr>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;background-color:#cccccc; color : black;\">";
				$message .= "<font color=$talk_color>◆</font><font color=red>$talk_handle_name <small>(天國)</small></font></td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#cccccc; color : black;\">";
				$message .= "$font_type_str <u><font color=red> $sentence </font></u></span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			} elseif($targ_loc != 'heaven') {
				$message .= "<tr>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font><font color=red>$talk_handle_name </font></td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str <u><font color=red> $sentence </font></u></span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			}
		// to GM
		} elseif ($day_night == 'night' && strstr((string) $location, 'to_gm')) {
			$sentence = messemot($sentence);

			if((strstr((string) $location, 'heaven') && strstr((string) $role, 'GM')) || $uname == 'dummy_boy') {
				//$talk_handle_name 
				$message .= "<tr>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;background-color:#cccccc; color : black;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_handle_name → GM </td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#cccccc; color : black;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";

			} elseif(($talk_handle_name == $handle_name && $live == 'live') || strstr((string) $role, 'GM')) {
				//$talk_handle_name 
				$message .= "<tr>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_handle_name → GM </td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			}
		// GM to
		} elseif ($day_night == 'night' && strstr((string) $location, 'gm_to')) {
			$locarr = explode(':;:', (string) $location);
			//echo $location."<br />";
			$targ_name = $locarr[1];
			$sentence = messemot($sentence);
			if(strstr((string) $role, 'GM') || $uname == 'dummy_boy') {

					//<font color=$talk_color>◆</font>$talk_handle_name 
					$message .= "<tr>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
					$message .= "<font color=$talk_color>◆</font>GM → $targ_name </td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
					$message .= "$font_type_str $sentence </span>";
					$message .= "</td>\r\n";
					$message .= "</tr>\r\n";
					/*
				} else {
					$message .= "<tr>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:130px;background-color:#cccccc; color : black;\">";
					$message .= "<font color=$talk_color>◆</font>GM → $targ_name </td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#cccccc; color : black;\">";
					$message .= "$font_type_str $sentence </span>";
					$message .= "</td>\r\n";
					$message .= "</tr>\r\n";
				}
				*/
			} elseif($handle_name == $targ_name && $live == 'live') {	
				$message .= "<tr>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font>GM → $targ_name </td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			}
		//夜晚狼人對話輸出
		} elseif ($live == 'live' && $day_night == 'night' && strstr((string) $location, 'night') && strstr((string) $location, 'wolf')) {
			if ((strstr((string) $role,"wolf")) || ((strstr((string) $role,"lovers") && strstr((string) $location,"lovers")) || strstr((string) $role, 'GM') )) {
				$message .= "<tr>\r\n";
				$sentence = messemot($sentence);
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_handle_name ".(strstr((string) $role, 'GM')?"<small>(人狼)</small>":"")."</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			} else {
				if (($ii % 5) == 0) {
					$message .= "<tr>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
					$message .= "狼的叫聲 </td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
					$message .= "<span style=\"font-size:12pt;line-height:160%;\">似乎在遠處聽見了狼叫聲･･･ </span>";
					$message .= "</td>\r\n";
					$message .= "</tr>\r\n";
				}
			}
		//夜晚共有對話輸出
		} elseif ($live == 'live' && $day_night == 'night' && strstr((string) $location,'night') && strstr((string) $location, 'common')) {
			if (strstr((string) $role,"common") || ((strstr((string) $role, 'lovers') && strstr((string) $location,'lovers'))) || strstr((string) $role, 'GM')) {
				$message .= "<tr>\r\n";
				$sentence = messemot($sentence);
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_handle_name ".(strstr((string) $role, 'GM')?"<small>(共有者)</small>":"")."</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			} else {
				if (strstr((string) $game_option,'comoutl')) {
					$message .= "<tr>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
					$message .= "<span style=\"font-size:8pt;\">共有者的聲音</span></td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
					$message .= "<span style=\"font-size:6pt;\"> 悄悄話･･･ </span>";
					$message .= "</td>\r\n";
					$message .= "</tr>\r\n";
				}
			}
		//夜晚妖狐對話輸出
		} elseif ($live == 'live' && $day_night == 'night' && strstr((string) $location,'night') && strstr((string) $location, 'fox')) {
			if ((strstr((string) $role,"fox")) || ((strstr((string) $role,"lovers") && strstr((string) $location,"lovers"))) || strstr((string) $role, 'GM')) {
				$message .= "<tr>\r\n";
				$sentence = messemot($sentence);
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_handle_name ".(strstr((string) $role, 'GM')?"<small>(妖狐)</small>":"")."</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			}
		//夜晚戀人對話輸出 (人X人)
		} elseif ($live == 'live' && $day_night == 'night' && strstr((string) $location,'night') && strstr((string) $location, 'lovers')) {
			if (strstr((string) $role,"lovers") || strstr((string) $role, 'GM')){
				$message .= "<tr>\r\n";
				$sentence = messemot($sentence);
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_handle_name ".(strstr((string) $role, 'GM')?"<small>(戀人)</small>":"")."</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			}
		//晚上其餘人的自言自語
		} elseif ($live == 'live' && $day_night == 'night' && $location == 'night self_talk') {
			if ($talk_uname === $uname || strstr((string) $role, 'GM')) {
				$message .= "<tr>\r\n";
				$sentence = messemot($sentence);
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_handle_name <small>的自言自語</small></td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			}
		//GM專用觀看天國
		} elseif ($location == 'heaven' && strstr((string) $role, 'GM')) {
			$message .= "<tr>\r\n";
			$sentence = messemot($sentence);
			$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
			$message .= "<td class=\"talktabletd\" style=\"width:130px;background-color:#cccccc; color : black;\">";
			$message .= "<font color=$talk_color>◆</font>$talk_handle_name <small>($talk_uname)</small></td>";
			$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#cccccc; color : black;\">";
			$message .= "$font_type_str $sentence </span>";
			$message .= "</td>\r\n";
			$message .= "</tr>\r\n";

		//	echo "<tr>\r\n";
		//死者看到的東西輸出
		} elseif (($live == 'dead' || $live == 'gone') || strstr((string) $role, 'GM')) {
			$message .= "<tr>\r\n";
			//處刑投票
			if (strstr((string) $location,'system') && strstr($sentence,'VOTE_DO')) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
					if (!(strstr((string) $game_option,'gm:'.$talk_log_array['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy' || strstr((string) $role, 'GM'))) {
                        $target_handle_name = "(馬賽克)";
                    }
				}
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"VOTE_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#999900;color:snow;font-weight:bold;\">".msgimg($msg_sys_image)."$talk_handle_name 將 $target_handle_name 投票處死</td>\r\n";
			//狼的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'WOLF_EAT') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"WOLF_EAT\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#CC3300;color:snow;font-weight:bold;\">".msgimg($msg_wolf_image)."$talk_handle_name 人狼對 $target_handle_name 鎖定為目標</td>\r\n";
			//占的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'MAGE_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"MAGE_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#990099;color:snow;font-weight:bold;\">".msgimg($msg_mage_image)."$talk_handle_name 對 $target_handle_name 進行占卜</td>\r\n";
			//子狐的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'FOSI_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"FOSI_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#990099;color:snow;font-weight:bold;\">".msgimg($msg_fosi_image)."$talk_handle_name 子狐對 $target_handle_name 進行占卜</td>\r\n";
			//貓又的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'CAT_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"CAT_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/
				if($target_handle_name == ':;:NOP:;:') {
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#006633;color:snow;font-weight:bold;\">".msgimg($msg_cat_image)."$talk_handle_name 貓又 放棄行動</td>\r\n";
				}	else{
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#006633;color:snow;font-weight:bold;\">".msgimg($msg_cat_image)."$talk_handle_name 貓又對 $target_handle_name 進行復活</td>\r\n";
				}
			//獵人的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'GUARD_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"GUARD_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#0099FF;color:snow;font-weight:bold;\">".msgimg($msg_guard_image)."$talk_handle_name 對 $target_handle_name 進行護衛</td>\r\n";
			//說謊狂的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'MYTHO_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				 $sentence_enc = str_replace(" ","\\space;",$sentence);
				 sscanf($sentence_enc,"CAT_DO\t%s",$target_handle_name);
				 $target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				 */
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#FF8000;color:snow;font-weight:bold;\">".msgimg($msg_mad_image)."$talk_handle_name 說謊狂對 $target_handle_name 進行模仿</td>\r\n";

			//夜梟的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'OWLMAN_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				 $sentence_enc = str_replace(" ","\\space;",$sentence);
				 sscanf($sentence_enc,"CAT_DO\t%s",$target_handle_name);
				 $target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				 */
				if($target_handle_name == ':;:NOP:;:') {
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#000080;color:snow;font-weight:bold;\">".msgimg($msg_gm_image)."$talk_handle_name 夜梟 放棄行動</td>\r\n";
				}	else{
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#000080;color:snow;font-weight:bold;\">".msgimg($msg_gm_image)."$talk_handle_name 夜梟對 $target_handle_name 進行詛咒</td>\r\n";
				}

			// 小企鵝的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'PENGU_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				 $sentence_enc = str_replace(" ","\\space;",$sentence);
				 sscanf($sentence_enc,"CAT_DO\t%s",$target_handle_name);
				 $target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				 */
				if($target_handle_name == ':;:NOP:;:'){
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#FFFF00;color:black;font-weight:bold;\">".msgimg($msg_mad_image)."$talk_handle_name 小企鵝 放棄行動</td>\r\n";
				}else{
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#FFFF00;color:black;font-weight:bold;\">".msgimg($msg_mad_image)."$talk_handle_name 小企鵝對 $target_handle_name 進行搔癢</td>\r\n";
				}

			//SPY的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'SPY_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#0f0f0f;color:snow;font-weight:bold;\">".msgimg($msg_spy_image)."$talk_handle_name 間諜 試圖完成任務，逃離村莊 </td>\r\n";
			//幼狼的投票
			} elseif (strstr((string) $location,'system') && strstr($sentence,'HUG_DO') && ($game_dellook || (strstr((string) $role, 'GM') || $uname == 'dummy_boy'))) {
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><small>(".$ttime.")</small></td><td class=\"talktabletd\" style=\"width:130px;\">&nbsp;</td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;background-color:#28e7ff;color:snow;font-weight:bold;\">".msgimg($msg_wolf_image)."$talk_handle_name 幼狼 對 $target_handle_name 進行萌殺 </td>\r\n";

			//其它顯示(死亡場合)
			} else {
				//自言自語
				if ($location == 'night self_talk') {
					$talk_handle_name_str = $talk_handle_name . "<small>的自言自語</small>";
					$talk_text_color = 'snow';
					if ($game_dellook == 0 && $uname != 'dummy_boy') {
						$talk_handle_name_str = '村民 ';
						$sentence = '碎碎念…';
						$talk_color = '#FFFFFF';
					}
				//人狼
				} elseif (strstr((string) $location,'night') && strstr((string) $location, 'wolf')) {
					$talk_handle_name_str = $talk_handle_name . "<small>(人狼)</small>";
					if ($game_dellook == 0 && $uname != 'dummy_boy') {
						$talk_handle_name_str = '狼的叫聲 ';
						$sentence = '似乎在遠處聽見了狼叫聲…';
						$talk_color = '#FFFFFF';
					}
					$talk_text_color = '#ffccff';
				//共有
				} elseif (strstr((string) $location,'night') && strstr((string) $location, 'common')) {
					$talk_handle_name_str = $talk_handle_name . "<small>(共有者)</small>";
					if ($game_dellook == 0 && $uname != 'dummy_boy') {
						$talk_handle_name_str = '共有者的聲音';
						$sentence = '悄悄話';
						$talk_color = '#FFFFFF';
					}
					$talk_text_color = '#ccffcc';
				//妖狐
				} elseif (strstr((string) $location,'night') && strstr((string) $location, 'fox')) {
					$talk_handle_name_str = $talk_handle_name . "<small>(妖狐)</small>";
					if ($game_dellook == 0 && $uname != 'dummy_boy') {
						$talk_handle_name_str = '村民';
						$sentence = '碎碎念…';
						$talk_color = '#FFFFFF';
						$talk_text_color = 'snow';
					} else {
						$talk_text_color = '#ccffcc';
					}
				//恋人
				} elseif (strstr((string) $location,'night') && strstr((string) $location, 'lovers')) {
					$talk_handle_name_str = $talk_handle_name . "<small>(戀人)</small>";
					if ($game_dellook == 0 && $uname != 'dummy_boy') {
						//$talk_handle_name_str = '戀人的獨白';
						//$sentence = '>//////<';
						$talk_handle_name_str = '村民';
						$sentence = '碎碎念…';
						$talk_color = '#FFFFFF';
						$talk_text_color = 'snow';
					} else {
						$talk_text_color = '#ccffcc';
					}
				} elseif (strstr((string) $location, 'gm_bc')) {
					$talk_handle_name = "<font color=red>$talk_handle_name</font>";
					$talk_color = $talk_text_color = 'red';
				} elseif (strstr((string) $location, 'gm_to')) {
					$locarr = explode(':;:', (string) $location);
					$targ_name = $locarr[1];
					$talk_text_color = 'red';
					$sentence = messemot($sentence);
					$talk_handle_name = "GM → $targ_name";

					if ($game_dellook == 0 && $uname != 'dummy_boy') {
						$talk_handle_name = "GM → ???";
						$sentence = '悄悄話…';
					}
				} elseif (strstr((string) $location, 'to_gm')) {
					$talk_text_color = '#FFFFFF';
					$talk_handle_name = "$talk_handle_name → GM";

					if ($game_dellook == 0 && $uname != 'dummy_boy') {
						$talk_handle_name = "??? → GM";
						$sentence = '悄悄話…';
					}
				//其它
				} else {
					$talk_handle_name_str = $talk_handle_name;
					$talk_text_color = '';
					if ($day_night == 'night' && $game_dellook == 0 && $uname != 'dummy_boy') {
						$talk_handle_name_str = '村民';
						$sentence = '碎碎念…';
						$talk_color = '#FFFFFF';
					}
				}
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;color:$talk_text_color;\">";
				$message .= "<font color=$talk_color>◆</font><span onMouseOver=\"showimage('user_".$talk_log_array['user_no']."',1)\" onMouseOut='hideimage()'>$talk_handle_name_str</span></td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
			}
			$message .= "</tr>\r\n";
		//吊人投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'VOTE_DO')) {

		//獵人投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'GUARD_DO')) {

		//占投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'MAGE_DO')) {

		//子狐投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'FOSI_DO')) {

		//貓又投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'CAT_DO')) {

		//狼投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'WOLF_EAT')) {
		//說謊狂投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'MYTHO_DO')) {
		//夜梟投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'OWLMAN_DO')) {
		//夜梟投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'PENGU_DO')) {
		//SPY投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'SPY_DO')) {
		//幼狼投票
		} elseif ($live == 'live' && strstr((string) $location,'system') && strstr($sentence,'HUG_DO')) {


		//觀戰者
		} else {
			if ($day_night == 'night' && strstr((string) $location,'night') && strstr((string) $location, 'wolf')) {
				if (($ii % 5) == 0) {
					$message .= "<tr>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
					$message .= "狼的叫聲 </td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
					$message .= "$font_type_str 似乎在遠處聽見了狼叫聲･･･ </span>";
					$message .= "</td>\r\n";
					$message .= "</tr>\r\n";
				}
			} elseif ($day_night == 'night' && strstr((string) $location,'night') && strstr((string) $location, 'common')) {
				if (strstr((string) $game_option,'comoutl')) {
					$message .= "<tr>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
					$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
					$message .= "<span style=\"font-size:8pt;\">共有者的聲音</span></td>";
					$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
					$message .= "<span style=\"font-size:6pt;\"> 悄悄話･･･ </span>";
					$message .= "</td>\r\n";
					$message .= "</tr>\r\n";
				}
			} elseif ($day_night == 'night' && $location == 'night lovers') {

			} elseif ($day_night == 'night' && StartsWith($location,'night fox')) {
				
			} elseif ($day_night == 'night' && $location == 'night self_talk') {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'VOTE_DO')) {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'WOLF_EAT')) {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'MAGE_DO')) {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'FOSI_DO')) {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'CAT_DO')) {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'GUARD_DO')) {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'MYTHO_DO')) {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'OWLMAN_DO')) {
				
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'PENGU_DO')) {
			
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'SPY_DO')) {
			} elseif ( strstr((string) $location,'system') && strstr($sentence,'HUG_DO')) {
			
			} else {
				$sentence = messemot($sentence);
				$message .= "<tr>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>\r\n";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font><a target=\"_blank\" href=\"trip.php?go=trip&id=$talk_trip\" style=\"text-decoration: none;color:#000000;\" onMouseOver=\"showimage('user_".$talk_log_array['user_no']."',1)\" onMouseOut='hideimage()'>$talk_handle_name</a></td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				$message .= "</tr>\r\n";
			}
		}
		$ii++;
	}
	$db->free_result($result);
	$message .= "</table>";
	echo $message;
}

//----------------------------------------------------------
//勝敗の出力
function VictoryOutput($outhtm = FALSE)
{
	global $room_no,$role,$view_mode,$log_mode,$cachefile,$db,$isold,$option_role,$live;

	global $msg_ampm_image, $msg_guard_image, $msg_vote_image, $msg_kill_image, $msg_sys_image, $msg_mage_image, 
	$msg_room_image, $msg_wolf_image, $msg_human_image, $msg_fox_image, $msg_fosi_image, $msg_cat_image, 
	$msg_sudden_image, $msg_gm_image, $msg_rm_image, $msg_lover_image, $msg_mad_image, $msg_spy_image;

	global $m2eat;
	
	//$res_victory_role = $db->query("select message from system_message where room_no = '$room_no' and type = 'VICTORY'");
	$res_victory_role = $db->query("select victory_role from room where room_no = '$room_no'");
	
	$outhtml = "";
	$outhtml .= "<table border=0 cellpadding=10 cellspacing=15 width=100%>\r\n";
	$outhtml .= "<tr>\r\n";
	
	$victory_role = $db->result($res_victory_role,0,0);

		$res_noble_live_c = $db->query("select count(*) from user_entry where room_no = '$room_no' and live = 'live' and role like 'noble%' and user_no > '0'");
		$noble_live_c = $db->result($res_noble_live_c,0,0);

	
	if($victory_role != NULL) //勝敗を出力
	{
		
		if($victory_role == 'wolf')
		{
			$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:#CC0000;color:snow;font-weight:bold;\">";
			$outhtml .= msgimg($msg_wolf_image)."[人狼・狂人勝利] 咬殺最後一人後，人狼們往下一個村莊去尋找獵物了</td>\r\n";
			
			if( ($view_mode == 'on') || ($log_mode == 'on') );//観戦モード、ログ閲覧モードでは非表示
			elseif( ((strstr((string) $role, 'spy') && $live == 'gone') || (!strstr((string) $role, 'spy') && strstr((string) DetermineRole($role),'wolf') ))
			  	&& ((strstr((string) $role, "lovers") && !isLoversVictoryOnly()) || !strstr((string) $role, "lovers")))
			{
				$outhtml .= "</tr>";
				$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:#CC0000;color:snow;font-weight:bold;\">";
				$outhtml .= "您戰勝了</td>\r\n";
				$outhtml .= "</td>";			
			}
			elseif( DetermineRole($role) != 'neutral')
			{
				$outhtml .= "</tr>";
				$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:black;color:snow;font-weight:bold;\">";
				$outhtml .= "您已經輸了</td>\r\n";
				$outhtml .= "</td>";
			}
		}
		elseif( strstr((string) $victory_role,"fox") )
		{
			$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:#993399;color:snow;font-weight:bold;\">";
			
			if( $victory_role == 'fox1')
				$outhtml .= msgimg($msg_fox_image).msgimg($msg_fox_image)."[妖狐勝利] 現在人狼已經滅絕，已經沒有我的敵人了</td>\r\n";
			else
				$outhtml .= msgimg($msg_fox_image).msgimg($msg_wolf_image)."[妖狐勝利] 欺騙愚蠢的人狼們是多麼容易的事情啊</td>\r\n";
			
			if( ($view_mode == 'on') || ($log_mode == 'on') ); //観戦モード、ログ閲覧モードでは非表示
			elseif( strstr((string) DetermineRole($role),'fox') && ((strstr((string) $role, "lovers") && !isLoversVictoryOnly()) || !strstr((string) $role, "lovers")))
			{
				$outhtml .= "</tr>";
				$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:#993399;color:snow;font-weight:bold;\">";
				$outhtml .= "您戰勝了</td>\r\n";
				$outhtml .= "</td>";
			}
			elseif(DetermineRole($role) != 'neutral')
			{
				$outhtml .= "</tr>";
				$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:black;color:snow;font-weight:bold;\">";
				$outhtml .= "您已經輸了</td>\r\n";
				$outhtml .= "</td>";
			}
		}
		elseif($victory_role == 'human')
		{
			$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:snow;color:black;font-weight:bold;\">";
			$outhtml .= msgimg($msg_human_image)."[村民勝利] 人狼的血脈成功的被根除</td>\r\n";
			
			if( ($view_mode == 'on') || ($log_mode == 'on') ); //観戦モード、ログ閲覧モードでは非表示
			elseif( ((!strstr((string) $role, 'slave') && DetermineRole($role) == 'human') || (strstr((string) $role, 'slave') && $noble_live_c == 0)) && 
					((strstr((string) $role, "lovers") && !isLoversVictoryOnly()) || !strstr((string) $role, "lovers"))
				)
			{
				$outhtml .= "</tr>";
				$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:#FFFF99;color:black;font-weight:bold;\">";
				$outhtml .= "您獲勝了</td>\r\n";
				$outhtml .= "</td>";
			}
			elseif(DetermineRole($role) != 'neutral')
			{
				$outhtml .= "</tr>";
				$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:black;color:snow;font-weight:bold;\">";
				$outhtml .= "您已經輸了</td>\r\n";
				$outhtml .= "</td>";
			}
		}
		elseif($victory_role == 'draw')
		{
			$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:black;color:snow;font-weight:bold;\">";
			$outhtml .= msgimg($msg_mad_image)."[和局] 變成平手的局面了</td>\r\n";
		}
		elseif($victory_role == 'custo')
		{
			$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:#2d7a56;color:snow;font-weight:bold;\">";
			$outhtml .= msgimg($msg_gm_image)."[GM] 由主持人宣判勝利方</td>\r\n";
		}
		elseif($victory_role == 'night') 
		{
			$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:#000030;color:snow;font-weight:bold;\">";
			$outhtml .= $m2eat." [夜間毀滅] 在某天晚上，村莊被一隻嚼嚼兔毀滅了</td>\r\n";
		}		
		elseif($victory_role == 'lover')
		{
			$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:ff80ff;color:snow;font-weight:bold;\">";
			$outhtml .= msgimg($msg_lover_image)."[戀人勝利] <font color=\"red\">♥</font> 這村子結束以後，我們就要回老家結婚了 <font color=\"red\">♥</font></td>\r\n";		
			

			if( ($view_mode == 'on') || ($log_mode == 'on') ); //観戦モード、ログ閲覧モードでは非表示			
			elseif( strstr((string) $role,"lovers") )
			{
				$outhtml .= "</tr>";
				$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:#FFFF99;color:black;font-weight:bold;\">";
				$outhtml .= "您跟您的愛人戰勝了</td>\r\n";
				$outhtml .= "</td>";
			}
			elseif( DetermineRole($role) != 'neutral')
			{
				$outhtml .= "</tr>";
				$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:black;color:snow;font-weight:bold;\">";
				$outhtml .= "您已經輸了</td>\r\n";
				$outhtml .= "</td>";
			}
		}
		//刪除快取
		if ($cachefile) {
			unlink('tmp/messH_'.$room_no.'.php');
		}
	} 
	else //廃村
		$outhtml .= "<td valign=middle align=center width=100% style=\"background-color:black;color:snow;font-weight:bold;\">".msgimg($msg_room_image)."[廢村] 這座村莊已經廢掉，人們離開了</td>\r\n";
	
	$outhtml .= "</tr></table>\r\n";
	if($outhtm) {
		return $outhtml;
	} else {
		echo $outhtml;
	}
}

//----------------------------------------------------------
//前の日の 狼が食べた、狐が占われて死亡、投票結果で死亡のメッセージ
function DeadManOutput($outhtm = FALSE)
{
	global $room_no,$date,$day_night,$log_mode,$db,$isold;
	
	if( ($day_night == 'beforegame') || ($day_night == 'aftergame' ) )
		return;
	
	$date_yesterday = $date -1;
	
	
	//一つ前の死亡者メッセージ出力
	if($day_night == 'day') //白
	{
		//前の日の夜に起こった死亡メッセージを取得(ランダム)
		$res_previous = $db->query("select message,type
								from system_message{$isold} where room_no = '$room_no' and date = $date_yesterday
								and ( type = 'WOLF_KILLED' or type = 'FOX_DEAD' or type = 'BETR_DEAD_night' or type = 'POISON_DEAD_night' or type = 'CAT_RESU_night' or type = 'LOVER_DEAD_night' or type = 'OWLMAN_KILLED' or type = 'PENGU_OK' or type = 'WCHILL_OK' or type = 'WFLAME_OK' or type = 'HUG_KILLED' or type = 'SPY_ESCAPE')
								order by rand();");
	}
	else //夜
	{
		//今日起こった処刑メッセージ、毒死メッセージ(白)を取得(ランダム)
		$res_previous = $db->query("select message,type
								from system_message{$isold} where room_no = '$room_no' and date = $date
								and (type = 'VOTE_KILLED' or type = 'VOTE_KILLME' or type = 'POISON_DEAD_day' or type = 'BETR_DEAD_day' or type = 'CAT_RESU_day' or type = 'LOVER_DEAD_day') order by rand();");
	}
	
//	$previous_count = $db->fetch_row($res_previous); //死亡者の人数

	$outhtml = "";
	
	while($previous_arr = $db->fetch_array($res_previous))
	{
	//	$previous_arr = $db->fetch_array($res_previous);
		
		$dead_man_handle_name = $previous_arr['message']; //死者のハンドルネーム
		$type = $previous_arr['type'];
		
		if ($dead_man_handle_name != "") { 
			$outhtml .= DeadManTypeOutput($dead_man_handle_name,$type,$outhtm);
		}
		
	}
	$db->free_result($res_previous);
	
	//ログ閲覧モード以外なら二つ前も死亡者メッセージ表示
	if($log_mode != 'on')
	{
		if($day_night == 'day') //白
		{
			//前の日の白に起こった処刑メッセージ、毒死(白)メッセージを取得(ランダム)
			$res_previous2 = $db->query("select message,type
									from system_message{$isold} where room_no = '$room_no' and date = $date_yesterday
									and ( type = 'VOTE_KILLED' or type = 'VOTE_KILLME' or type = 'POISON_DEAD_day' or type = 'BETR_DEAD_day' or type = 'CAT_RESU_day' or type = 'LOVER_DEAD_day') order by rand();");
		}
		else //夜
		{
			//前の日の夜に起こった死亡メッセージを取得(ランダム)
			$res_previous2 = $db->query("select message,type
									from system_message{$isold} where room_no = '$room_no' and date = $date_yesterday
									and ( type = 'WOLF_KILLED' or type = 'FOX_DEAD' or type = 'BETR_DEAD_night' or type = 'POISON_DEAD_night' or type = 'CAT_RESU_night' or type = 'LOVER_DEAD_night' or type = 'OWLMAN_KILLED'  or type = 'HUG_KILLED' or type = 'SPY_ESCAPE')
									order by rand();");
		}
	//	$previous2_count = $db->fetch_row($res_previous2); //死亡者の人数
		
		while($previous2_arr = $db->fetch_array($res_previous2))
		{
		//	$previous2_arr = $db->fetch_array($res_previous2);
			
			$dead_man_handle_name = $previous2_arr['message']; //死者のハンドルネーム
			$type = $previous2_arr['type'];
			
			if ($dead_man_handle_name != "") { 
				$outhtml .= DeadManTypeOutput($dead_man_handle_name,$type,$outhtm);
			}
			
		}
		$db->free_result($res_previous2);
	}
	if ($outhtm) {
		return $outhtml;
	} else {
		echo $outhtml;
	}
}
//----------------------------------------------------------
//死者のタイプ別に死亡メッセージを出力
function DeadManTypeOutput($dead_man_handle_name,$type,$outhtm = FALSE)
{
	global $live,$game_dellook,$role,$game_option,$day_night;
	
	global $msg_ampm_image, $msg_guard_image, $msg_vote_image, $msg_kill_image, $msg_sys_image, $msg_mage_image, 
	$msg_room_image, $msg_wolf_image, $msg_human_image, $msg_fox_image, $msg_fosi_image, $msg_cat_image, 
	$msg_sudden_image, $msg_gm_image, $msg_rm_image, $msg_lover_image, $msg_spy_image;
	
	if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
		$dead_man_handle_name = "(馬賽克)";
	}

	$outhtml = "";
	switch($type)
	{
		case('WOLF_KILLED'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#990000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_kill_image)."$dead_man_handle_name 悽慘的死狀被發現</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
		
			if(($live == 'dead' || $live == 'gone') && $game_dellook) //死者からは死亡の原因を表示
			{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr style=\"background-color:snow;color:black;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">($dead_man_handle_name 成為狼的食物)</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			}
			break;
		case('HUG_KILLED'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#990000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_kill_image)."$dead_man_handle_name 悽慘的死狀被發現</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
		
			if(($live == 'dead' || $live == 'gone') && $game_dellook) //死者からは死亡の原因を表示
			{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr style=\"background-color:snow;color:black;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">($dead_man_handle_name 被幼狼抱枕萌殺)</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			}
			break;
		case('FOX_DEAD'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#990000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_kill_image)."$dead_man_handle_name 悽慘的死狀被發現</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
		
			if(($live == 'dead' || $live == 'gone') && $game_dellook) //死者からは死亡の原因を表示
			{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr style=\"background-color:snow;color:black;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">($dead_man_handle_name (狐)似乎被占卜師咒殺而死)</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			}
			break;
		case('BETR_DEAD_day'):
		case('BETR_DEAD_night'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#990000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_kill_image)."$dead_man_handle_name 悽慘的死狀被發現</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
		
			if(($live == 'dead' || $live == 'gone') && $game_dellook) //死者からは死亡の原因を表示
			{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr style=\"background-color:snow;color:black;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">($dead_man_handle_name 背德似乎自殺了)</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			}
			break;
		case('LOVER_DEAD_day'):
		case('LOVER_DEAD_night'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#990000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_kill_image)."$dead_man_handle_name 悽慘的死狀被發現</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
		
			if(($live == 'dead' || $live == 'gone') && $game_dellook) //死者からは死亡の原因を表示
			{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr style=\"background-color:snow;color:black;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">($dead_man_handle_name 戀人似乎自殺了)</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			}
			break;
		case('CAT_RESU_day'):
		case('CAT_RESU_night'):
			//貓又處理
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#000099;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_cat_image)."$dead_man_handle_name 奇蹟的生還</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
			break;
		case('POISON_DEAD_day'):
		case('POISON_DEAD_night'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#990000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_kill_image)."$dead_man_handle_name 悽慘的死狀被發現</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
		
			if(($live == 'dead' || $live == 'gone') && $game_dellook) //死者からは死亡の原因を表示
			{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr style=\"background-color:snow;color:black;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">($dead_man_handle_name 似乎被毒死了)</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			}
			break;
		case('VOTE_KILLED'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#666600;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_vote_image)."$dead_man_handle_name 被表決處死</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
			break;
		case('VOTE_KILLME'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#666600;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_sudden_image)."$dead_man_handle_name 自投死亡(投票不計)</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
			break;
		case('GM_KILL'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#d00000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_gm_image)."$dead_man_handle_name 突然暴斃死亡</font></td>\r\n";
			$outhtml .= "</tr></table>\r\n";
			break;
		case('GM_RESU'):
			//貓又處理
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#0000d0;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_gm_image)."$dead_man_handle_name 奇蹟的生還</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
			break;
		case('PENGU_OK'):
			//if($live == 'dead' || strstr($role, 'GM') || strstr($role, 'fox') || strstr($role, 'pengu') || strstr($role, 'wolf')) //死者からは死亡の原因を表示
			//{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr height=35 style=\"background-color:snow;color:#0000d0;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">昨天晚上似乎聽到爽爽笑聲</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			//}
			break;
		case('WCHILL_OK'):
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr height=35 style=\"background-color:snow;color:#0000d0;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">昨天晚上有些人手凍僵了，行動受阻。</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			break;
			
		case('WFLAME_OK'):
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr height=35 style=\"background-color:snow;color:#0000d0;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">昨天晚上村莊相當混亂。</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			break;


		case('OWLMAN_KILLED'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#990000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_kill_image)."$dead_man_handle_name 悽慘的死狀被發現</td>\r\n";
			$outhtml .= "</tr></table>\r\n";
			
			if(($live == 'dead' || $live == 'gone') && $game_dellook) //死者からは死亡の原因を表示
			{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr style=\"background-color:snow;color:black;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">($dead_man_handle_name 似乎被夜梟詛咒而死)</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			}
			break;
		case('SPY_ESCAPE'):
			$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			$outhtml .= "<tr height=35 style=\"background-color:snow;color:#990000;font-weight:bold;\">\r\n";
			$outhtml .= "<td class=\"talktabletd2\">".msgimg($msg_kill_image)."$dead_man_handle_name 悽慘的死狀被發現</td>\r\n";
			$outhtml .= "</tr></table>\r\n";

			if(($live == 'dead' || $live == 'gone') && $game_dellook) //死者からは死亡の原因を表示
			{
				$outhtml .= "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
				$outhtml .= "<tr style=\"background-color:snow;color:0000d0;font-weight:bold;\">\r\n";
				$outhtml .= "<td class=\"talktabletd2\">($dead_man_handle_name 趁夜晚逃離村莊，實際上還活著)</td>\r\n";
				$outhtml .= "</tr></table>\r\n";
			}
			
			/*
			echo "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
			echo "<tr height=35 style=\"background-color:snow;color:#0000d0;font-weight:bold;\">\r\n";
			echo "<td class=\"talktabletd2\">".msgimg($msg_spy_image)."$dead_man_handle_name 趁夜晚逃離村莊</td>\r\n";
			echo "</tr></table>\r\n";
			*/
			
			break;
	}
	
	if ($outhtm) {
		return $outhtml;
	} else {
		echo $outhtml;
	}
}
//----------------------------------------------------------
//占う、狼が狙う、護衛する等、能力を使うメッセージ
function AbilityActionOutput($game_dellook = 1): void
{
	global $room_no,$date,$day_night,$db,$isold,$role;
	
	global $msg_ampm_image, $msg_guard_image, $msg_vote_image, $msg_kill_image, $msg_sys_image, $msg_mage_image, 
	$msg_room_image, $msg_wolf_image, $msg_human_image, $msg_fox_image, $msg_fosi_image, $msg_cat_image, 
	$msg_sudden_image, $msg_gm_image, $msg_rm_image, $msg_lover_image, $msg_mad_image, $msg_spy_image;

	if($day_night != 'day') //白のみ表示
		return;
	
	$date_yesterday = $date -1;
	
	$result = $db->query("select message,type from system_message{$isold} where room_no = '$room_no' and date = $date_yesterday
							and ( type = 'MAGE_DO' or type = 'WOLF_EAT' or type = 'GUARD_DO' or type = 'FOSI_DO' or type = 'CAT_DO' or type = 'MYTHO_DO' or type = 'OWLMAN_DO' or type = 'PENGU_DO' or type = 'HUG_DO' or type = 'SPY_ESCAPE')");
	
//	$res_count = $db->fetch_row($result);
	
	if($game_dellook || strstr((string) $role, 'GM')) {
	
		while($res_arr = $db->fetch_array($result))
		{
		//	$res_arr = $db->fetch_array($result);
			$message = $res_arr['message'];
			$type = $res_arr['type'];
			
			$sentence_enc = explode("\t", (string) $message);
			$this_handle_name = $sentence_enc[0];
			$this_target_name = $sentence_enc[1];
			/*
			$message_enc = str_replace(" ","\\space;",$message);
			sscanf($message_enc,"%s\t%s",$this_handle_name,$this_target_name);
			$this_handle_name = str_replace("\\space;"," ",$this_handle_name);
			$this_target_name = str_replace("\\space;"," ",$this_target_name);
			*/
			
			switch($type)
			{
				case('MAGE_DO'):
					echo msgimg($msg_mage_image)."<strong>昨天晚上、占卜師 $this_handle_name 對 $this_target_name 占卜</strong><br />";
					break;
				case('FOSI_DO'):
					echo msgimg($msg_fosi_image)."<strong>昨天晚上、子狐 $this_handle_name 對 $this_target_name 占卜</strong><br />";
					break;
				case('CAT_DO'):
					if($this_target_name == ':;:NOP:;:') 
						echo msgimg($msg_cat_image)."<strong>昨天晚上、貓又 $this_handle_name 放棄行動</strong><br />";
					else
						echo msgimg($msg_cat_image)."<strong>昨天晚上、貓又 $this_handle_name 對 $this_target_name 復活</strong><br />";
					break;
				case('WOLF_EAT'):
					echo msgimg($msg_wolf_image)."<strong>昨天晚上、$this_handle_name 人狼鎖定 $this_target_name 為目標</strong><br />";
					break;
				case('GUARD_DO'):
					echo msgimg($msg_guard_image)."<strong>昨天晚上、獵人 $this_handle_name 對 $this_target_name 護衛</strong><br />";
					break;
				case('MYTHO_DO'):
					echo msgimg($msg_mad_image)."<strong>昨天晚上、說謊狂 $this_handle_name 對 $this_target_name 模仿</strong><br />";
					break;
				case('OWLMAN_DO'):
					if($this_target_name == ':;:NOP:;:') 
						echo msgimg($msg_gm_image)."<strong>昨天晚上、夜梟 $this_handle_name 放棄行動</strong><br />";
					else
						echo msgimg($msg_gm_image)."<strong>昨天晚上、夜梟 $this_handle_name 對 $this_target_name 詛咒</strong><br />";
					break;
				case('PENGU_DO'):
					if($this_target_name == ':;:NOP:;:') 
						echo msgimg($msg_mad_image)."<strong>昨天晚上、小企鵝 $this_handle_name 放棄行動</strong><br />";
					else
						echo msgimg($msg_mad_image)."<strong>昨天晚上、小企鵝 $this_handle_name 對 $this_target_name 搔癢</strong><br />";
					break;
				case('HUG_DO'):
					echo msgimg($msg_wolf_image)."<strong>昨天晚上、$this_handle_name 幼狼鑽進 $this_target_name 懷裡</strong><br />";
					break;
				case('SPY_ESCAPE'):
					echo msgimg($msg_spy_image)."<strong>昨天晚上、$this_handle_name 間諜試圖完成任務，脫離村莊</strong><br />";
					break;

			}
		}
		$db->free_result($result);
	}
}




//----------------------------------------------------------
//投票の集計出力
function VoteListOutput($outhtm = FALSE)
{
	global $room_no,$date,$day_night,$log_mode,$isold;
	$date_yesterday = $date -1;
	
	
	if( ($day_night == 'beforegame') || ($day_night == 'aftergame' ) )
		return;
	
	$outhtml = "";
	if( ($day_night == 'day') && ($log_mode != 'on') ) //白だったら前の日の集計を取得
		$outhtml .= VoteListDayOutput($date_yesterday);
	else //夜だったら今日の集計を取得
		$outhtml .= VoteListDayOutput($date);

	if ($outhtm) {
		return $outhtml;
	} else {
		echo $outhtml;
	}
}

//----------------------------------------------------------
//指定した日付の投票結果を出力する
function VoteListDayOutput($set_date)
{
	global $room_no,$game_option,$live,$reverse_log,$view_mode,$db,$isold;
	
	$outhtml = "";
	//指定された日付の投票結果を取得
	$res_this_vote_message = $db->query("select message from system_message{$isold} where room_no = '$room_no' and date = $set_date
										 and type = 'VOTE_KILL' ORDER BY id ASC");
	if($db->num_rows($res_this_vote_message) == 0)
		return;
	$this_vote_message_arr = []; //投票結果を格納する
	$this_vote_latest_times = -1; //出力する投票回数を記録
	$this_vote_count = $db->num_rows($res_this_vote_message); //投票総数
	
	$this_vote_times = 0; //表の個数
	while($vote_arr = $db->fetch_array($res_this_vote_message)) //いったん配列に格納する
	{
	//	$vote_arr = $db->fetch_array($res_this_vote_message);
		$vote_message = $vote_arr['message'];
		
		//タブ区切りのデータを分割する
	//	$vote_message_enc = str_replace(" ","\\space;",$vote_message);
		$vote_message_enc = explode("\t", (string) $vote_message);
		$this_handle_name = $vote_message_enc[0];
		$target_handle_name = $vote_message_enc[1];
		$voted_number = $vote_message_enc[2];
		$vote_number = $vote_message_enc[3];
		$vote_times = $vote_message_enc[4];
	//	sscanf($vote_message_enc,"%s\t%s\t%d\t%d\t%d",$this_handle_name,$target_handle_name,$voted_number,$vote_number,$vote_times);
	//	$this_handle_name = str_replace("\\space;"," ",$this_handle_name);
	//	$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
		
		$vote_times = (int)$vote_times; 
		if($this_vote_latest_times != $vote_times) //投票回数が違うデータだと別テーブルにする
		{
			if($this_vote_latest_times != -1)
				array_push($this_vote_message_arr[$this_vote_latest_times],"</table>\r\n");
			
			$this_vote_latest_times = $vote_times;
			$this_vote_message_arr[$vote_times] = [];
			array_push($this_vote_message_arr[$vote_times],"<table border=1 cellspacing=0 cellpadding=2 style=\"font-size:12pt;\">\r\n");
			$tabletdcolspan = 4;
			array_push($this_vote_message_arr[$vote_times],"<td colspan=$tabletdcolspan align=center>$set_date 日目 ( $vote_times 回目)</td>\r\n");
			
			$this_vote_times++;
		}
		
		if( (strstr((string) $game_option,"open_vote") || ($live == 'dead' || $live == 'gone') ) && ($view_mode != 'on') )
		{
			$vote_number_str = "投票給 " . $vote_number . " 票 →";
		}
		else
		{
			$vote_number_str = "投票給→";
		}
		
		if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
			$this_handle_name = "(馬賽克)";
			$target_handle_name = "(馬賽克)";
		}
		
		//表示されるメッセージ
		$this_vote_message = "<tr><td align=left><strong>" . $this_handle_name . "</strong></td><td>"
									. $voted_number . " 票</td><td>" . $vote_number_str
									. "</td><td><strong> " . $target_handle_name . " </strong></td></tr>\r\n";
		
		array_push($this_vote_message_arr[$vote_times],$this_vote_message);
	}
	$db->free_result($res_this_vote_message);
	array_push($this_vote_message_arr[$this_vote_latest_times],"</table>\r\n");
	
	if($reverse_log == 'on') //逆順表示
	{
		//配列に格納されたデータを出力
		for($i=1; $i<=$this_vote_times; $i++)
		{
			if (!isset($this_vote_message_arr[$i])) continue;
			$this_vote_count = count($this_vote_message_arr[$i]);
			for($j=0 ; $j < $this_vote_count ;$j++)
			{
				$outhtml .= $this_vote_message_arr[$i][$j];
			}
		}
	}
	else
	{
		$this_vote_times = empty($this_vote_times) ? 0 : $this_vote_times;
		//配列に格納されたデータを出力
		for($i=$this_vote_times; $i>0; $i--)
		{
			if (!isset($this_vote_message_arr[$i])) continue;
			$this_vote_count = count($this_vote_message_arr[$i]);
			for($j=0 ; $j < $this_vote_count ;$j++)
			{
				$outhtml .= $this_vote_message_arr[$i][$j];
			}
		}
	}
	return $outhtml;
}
//----------------------------------------------------------
//Playヤー一覧出力
function PlayerListOutput($game_dellook = 1, $is_logmode = 0,$outhtm = FALSE)
{
	global $uname,$room_no,$day_night,$live,$user_icon_dir,$dead_user_icon_image,$spy_user_icon_image,$game_option,$db,$isold,$role,$trip_icon_dir,$voted_list,$showtrip,$dummy_boy_imgid;
	
	if($is_logmode) {
		$l_day_night = 'aftergame';
		$l_live = 'dead';
	} else {
		$l_day_night = $day_night;
		$l_live = $live;
	}
	
	//WindowsのMSIEを確認(画像のAlt,Title属性に改行を含めるかどうか、IEだけ改行できる)
	if( preg_match("/MSIE/i",(string) $_SERVER['HTTP_USER_AGENT']) )
		$browser_MSIE = true;
	else
		$browser_MSIE = false;
	
	$result = $db->query("select u.uname as uname
						,u.handle_name as handle_name
						,u.trip as trip
						,u.live as live
						,u.role as role
						,u.marked as marked
						,u.death as death
						,u.uid as uid
						,u.user_no as user_no
						,i.icon_filename as icon_filename
						,i.color as color,u.icon_no as iconno
						,i.icon_width as icon_width
						,i.icon_height as icon_height
						,tr.icon as ticon,tr.size as tsize,tr.id as tid
						,tr.handle_name as handle_name2
						from (user_entry u , user_icon i)
						left join user_trip tr on tr.trip = u.trip
						where u.room_no = '$room_no'
						and u.icon_no = i.icon_no
						and u.user_no > 0 order by u.user_no ASC");
//	$result_count = $db->fetch_row($result);
	
	$outhtml = "";
	$outhtml .= "<table width=800 border=0 cellpadding=0 cellspacing=0><tr><td>";
	
	$outhtml .= "<table class=\"table2\" border=0 cellpadding=0 cellspacing=5 style=\"font-size:10pt;border-width:1px;border-color:black;border-style:dotted;\"><tr>";

	$i = 0;

	while($result_arr = $db->fetch_array($result)) {
	
		
	//	$result_arr = $db->fetch_array($result);
		
		$this_uname = $result_arr['uname'];
		$this_live = $result_arr['live'];
		$this_role = $result_arr['role'];
		$this_role_desc = $result_arr['role_desc'];
		$this_marked = $result_arr['marked'];
		$this_death = $result_arr['death'];
		$this_tripcolor = "";
		$this_namecolor = "";
	
			if ($this_live == 'live') {
				if ($l_day_night == 'night') {
					$this_namecolor = "color:#FFFFFF;";
				} else {
					$this_namecolor = "color:#000000;";
				}
			} else {
				$this_tripcolor = "color:gray;";
				$this_namecolor = $this_tripcolor;
			}
		
		if ($result_arr['trip'] != "" && strstr((string) $game_option,'gm:'.$result_arr['trip'])) {
			if (strstr((string) $game_option,'as_gm')) {
				$this_tripcolor = "";
				$this_namecolor = "color:Red;";
			} 
			//else {
		//		if($l_day_night == 'night')
			//		$this_handle_name = '<font color="#ffffd0">'.$this_handle_name.'</font>';
		//		else 
		//			$this_handle_name = '<font color="#000080">'.$this_handle_name.'</font>';
		//	}
		}
		
		$this_handle_name = '<a target="_blank" style="text-decoration: none;'.$this_namecolor.'" href="game_log.php?room_no='.$room_no.'&log_mode=on&date=ALL&play_uid='.$result_arr['uid'].'#game_top">'.$result_arr['handle_name']."</a>";
		
		if (strstr((string) $game_option,'usr_guest') && $l_day_night != 'aftergame' && $day_night != 'aftergame' && $day_night != 'beforegame') {
			if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                $user_no2 = str_pad((string) $result_arr['user_no'],2,"0",STR_PAD_LEFT);
                $this_handle_name = "玩家".$user_no2."號";
            }
		}
		
		$isusr_guest = false;
		if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
			$isusr_guest = true;
		}
		if($isusr_guest == false || $uname == 'dummy_boy' || $l_day_night == 'aftergame') {
			if ($result_arr['trip']) {
				if ($showtrip == "show" && $result_arr['handle_name2'] != "") {
					$this_handle_name = $this_handle_name.'<br />◆<a target="_blank" style="text-decoration: none;'.$this_tripcolor.'" href="trip.php?go=trip&id='.$result_arr['trip'].'" title="'.$result_arr['trip'].'">'.$result_arr['handle_name2'].'</a>';
				} else {
					$this_handle_name = $this_handle_name.'<br />◆<a target="_blank" style="text-decoration: none;'.$this_tripcolor.'" href="trip.php?go=trip&id='.$result_arr['trip'].'">'.$result_arr['trip'].'</a>';
				}
			}
		}
		
		$this_icon_filename = $result_arr['icon_filename'];
		$this_color = $result_arr['color'];
		$this_icon_width = $result_arr['icon_width'];
		$this_icon_height = $result_arr['icon_height'];
		
		if ($this_uname != 'dummy_boy' && $result_arr['ticon'] && $result_arr['tsize'] && $result_arr['iconno'] == $dummy_boy_imgid) {
			$result_arr['tsize']  = explode(":",(string) $result_arr['tsize']);
			if ($result_arr['tsize'][2] == '') {
				$result_arr['tsize'][2] = "webp";
			}
			$this_icon_filename = "icon_" . $result_arr['tid'].".".$result_arr['tsize'][2];
			$this_icon_width = $result_arr['tsize'][0];
			$this_icon_height = $result_arr['tsize'][1];
			$this_color = $result_arr['ticon'];
			$user_icon_dir2 = $trip_icon_dir;
		} 
		else {
			$user_icon_dir2 = $user_icon_dir;
		}
		
		
		
		//アイコン
		$this_icon_location = $user_icon_dir2 . "/" . $this_icon_filename;
		
		if( $this_live == 'live') //生きていれば用戶アイコン
		{
			$icon_location = $user_icon_dir2 . "/" . $this_icon_filename;
			$icon_width_str = "width=" . $this_icon_width;
			$icon_height_str = "height=" . $this_icon_height;
			//$this_handle_name = '<font color="green">'.$this_handle_name.'</font>';
			//$l_live_str = "<font color='green'>(生存中)</font>";
		}
		// 只有已死+靈視或者結束遊戲，或者是GM/替身君才能看到真相
		elseif( $this_live == 'gone')
		{
		
			if($l_day_night == 'aftergame' || 
					(($l_live == 'dead' || $l_live == 'gone') && $game_dellook == '1') || 
					strstr((string) $role, 'GM') || 
					$uname == 'dummy_boy'
				) 
			{
				$icon_location = $spy_user_icon_image;
				$icon_width_str = '';
				$icon_height_str = '';
				$l_live_str = "(已脫離)";
			}
			else
			{
				$icon_location = $dead_user_icon_image;
				$icon_width_str = '';
				$icon_height_str = '';
				//$l_live_str = "<font color='red'>(死亡)</font>";
				//$this_handle_name = '<font color="gray">'.$this_handle_name.'</font>';
			}
		}
		elseif( $this_live == 'dead') //死んでれば死亡アイコン
		{
			$icon_location = $dead_user_icon_image;
			$icon_width_str = '';
			$icon_height_str = '';
			//$l_live_str = "<font color='red'>(死亡)</font>";
			//$this_handle_name = '<font color="gray">'.$this_handle_name.'</font>';
		}
		else //死んでれば死亡アイコン
		{
			$icon_location = $dead_user_icon_image;
			$icon_width_str = '';
			$icon_height_str = '';
			//$l_live_str = "<font color='red'>(死亡)</font>";
			//$this_handle_name = '<font color="gray">'.$this_handle_name.'</font>';
		}
		
		if (strstr((string) $game_option,'usr_guest') && $l_day_night != 'aftergame' && $day_night != 'aftergame' && $day_night != 'beforegame') {
			if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                $iconii =str_pad((string) $result_arr['user_no'],2,"0",STR_PAD_LEFT);
                if ($this_live != 'dead') {
					$icon_location = "guest_icon/".$iconii.".webp";
				}
                if ($this_live != 'dead') {
					$this_icon_location = $icon_location;
				} else {
					$this_icon_location = "guest_icon/".$iconii.".webp";
				}
                $icon_width_str = '';
                $icon_height_str = '';
                $this_color = '#FFFFFF';
            }
		}
		
		$image_swap_javascript_str = " onMouseover=\"this.src='$this_icon_location'\" onMouseout=\"this.src='$icon_location'\" ";
		
		//5個ごとに段落改行
		if( ($i % 5) == 0 )
		{
			$outhtml .= "</tr><tr>\r\n";
		}
		$i++;
		
		// TEST CODE
		//$game_dellook == '1';
		//$l_day_night == 'aftergame';
		
		if( $this_live == 'live' )
			$l_live_color = "";
		else {
			if($l_day_night == 'day')
				$l_live_color = " bgcolor=#999999";
			else
				$l_live_color = " bgcolor=#303030";
		}
			
			
		if( $this_marked ) 
			$this_marked = "★";
		else
			$this_marked = "";
		
		if($l_day_night == 'beforegame') //ゲーム前
		{
		
			//ゲームスタートに投票しているか、していれば色を変える
			//$res_already_voted = $db->query("select count(uname) from vote where room_no = '$room_no' and situation = 'GAMESTART'
			//																						and  uname = '$this_uname'");
			//$already_voted = $db->result($res_already_voted,0);
		
			//if( strstr($voted_list, ':;:' . $this_uname . ':;:') || ($this_uname == "dummy_boy") )
			//if( $already_voted )
			if(strstr((string) $voted_list, ':;:' . $this_uname . ':;:'))
				$already_voted_color = " bgcolor=#FF50FF";
			else
				$already_voted_color = "";
			
			$outhtml .= "<td class=\"table2b\" valign=top $already_voted_color>";
			$outhtml .= "<img src=\"$icon_location\" $icon_width_str $icon_height_str border=2 style=\"border-color:$this_color;\" id=\"user_".$result_arr['user_no']."\"></td>\r\n";
			$outhtml .= "<td class=\"table2a\" $already_voted_color><font color=$this_color>◆</font>$this_handle_name";
			//echo "<br />".$l_live_str;
			$outhtml .= "</td>\r\n";
		}
		elseif($l_day_night == 'aftergame' || (($l_live == 'dead' || $l_live == 'gone') && $game_dellook == '1') || (strstr((string) $role, 'GM')|| $uname == 'dummy_boy')) //ゲーム終了後、死亡後は用戶ネームも表示
		//if($l_day_night != 'beforegame')
		{
			$role_str = '';
			
			if( strstr((string) $this_role,"human") )
			{
				$role_str = "[村民]";
			}
			if( strstr((string) $this_role,"wolf") )
			{
				$role_str = "<font color=red>[人狼]</font>";
			}
			if( strstr((string) $this_role,"mage") )
			{
				$role_str = "<font color=#9933FF>[占卜師]</font>";
			}
			if( strstr((string) $this_role,"necromancer") )
			{
				$role_str = "<font color=#009900>[靈能者]</font>";
			}
			if( strstr((string) $this_role,"mad") )
			{
				$role_str = "<font color=red>[狂人]</font>";
			}
			if( strstr((string) $this_role,"common") )
			{
				$role_str = "<font color=#cc9966>[共有者]</font>";
			}
			if( strstr((string) $this_role,"guard") )
			{
				$role_str = "<font color=#3399ff>[獵人]</font>";
			}
			if( strstr((string) $this_role,"fox") )
			{
				$role_str = "<font color=#CC0099>[妖狐]</font>";
			}
			if( strstr((string) $this_role,"fosi") )
			{
				$role_str = "<font color=#CC0099>[子狐]</font>";
			}
			if( strstr((string) $this_role,"betr") )
			{
				$role_str = "<font color=#CC0099>[背德]</font>";
			}
			if( strstr((string) $this_role,"poison") )
			{
				$role_str .= "<font color=#006633>[埋毒者]</font>";
			}
			if( strstr((string) $this_role,"cat") )
			{
				$role_str .= "<font color=#006633>[貓又]</font>";
			}
			
			if( strstr((string) $this_role,'mytho') )
			{
				$role_str .= "<font color=#FF8000>[說謊狂]</font>";
			}
			if( strstr((string) $this_role,'owlman') )
			{
				$role_str .= "<font color=#000080 style=\"background-color: #ffffff\">[夜梟]</font>";
			}

			if( strstr((string) $this_role,"spy") )
			{
				$role_str = "<font color=red>[間諜]</font>";
			}


			if( strstr((string) $this_role,"noble") )
			{
				$role_str .= "[貴族]";
			}
			if( strstr((string) $this_role,"slave") )
			{
				$role_str .= "[奴隸]";
			}

			
			
			if( strstr((string) $this_role_desc,'mytho_tr') )
			{
				$role_str .= "<font color=#FF8000>[謊]</font>";
			}

			if( strstr((string) $this_role,'pengu') )
			{
				$role_str .= "<font color=#ff9933>[小企鵝]</font>";
			}
			
			// role-sensitive sub-roles
			// sub-wolf
			if( strstr((string) $this_role,"wfbig") )
			{
				$role_str .= "<br /><font color=#ff0000>[大狼]</font>";
			}
			if( strstr((string) $this_role,"wfwtr") )
			{
				$role_str .= "<br /><font color=#ccffff style=\"background-color: #000000\">[冬狼]</font>";
			}
			if( strstr((string) $this_role,"wfasm") )
			{
				$role_str .= "<br /><font color=#ff3000>[明日夢]</font>";
			}
			if( strstr((string) $this_role,"wfbsk") )
			{
				$role_str .= "<br /><font color=#d00000>[狂狼]</font>";
			}
			if( strstr((string) $this_role,"wfwnd") )
			{
				$role_str .= "<br /><font color=#83b0e8>[捲尾巴幼狼]</font>";
			}
			if( strstr((string) $this_role,"wfxwnd") )
			{
				$role_str .= "<br /><font color=#83b0e8>[捲尾巴幼狼？]</font>";
			}
			
			if( strstr((string) $this_role,"lovers") )
			{
				$role_str .= "<br /><font color=#ff80ff>[戀人]</font>";
			}
						
			if( strstr((string) $this_role,"authority") )
			{
				$role_str .= "<br /><font color=#999999>[權力者]</font>";
			}
			if( strstr((string) $this_role,"decide") )
			{
				$role_str .= "<br /><font color=#999999>[決定者]</font>";
			}
			
			if( strstr((string) $this_role,"GM") )
			{
				$role_str .= "<font color=#ff8000>[遊戲主持人]</font>";
			}
		
			
			
			if ($l_day_night != 'aftergame' && $this_uname != "dummy_boy") {
				if(strstr((string) $voted_list, ':;:' . $this_uname . ':;:')) {
					if ($l_day_night == 'day') {
						$l_live_color = " bgcolor=#d0FFFF";
					} else {
						$l_live_color = " bgcolor=#004000";
					}
				} else {
					$l_live_color = "";
				}
			} else {
				$l_live_color = "";
			}

			if ($l_day_night == 'aftergame' && $this_death == 1) {
				$l_live_color = ' bgcolor="#8080FF"';
			}
			
			
			if ($l_day_night == 'aftergame' && $this_death == 2) {
				$l_live_color = ' bgcolor="#FF8080"';
			}
			
			$outhtml .= "<td class=\"table2b\" valign=top $l_live_color>";
			$tripsce_url = "#";
			if($result_arr['trip'] != "") {
				$tripsce_url = "trip.php?go=sce&room=$room_no&trip=".$result_arr['trip'];
				if (strstr((string) $game_option,'usr_guest') && $l_day_night != 'aftergame' && $day_night != 'aftergame' && $day_night != 'beforegame') {
					if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                        $tripsce_url = "#";
                    }
				}
			}
			$outhtml .= "<a href = \"$tripsce_url\" target=\"_blank\"><img src=$icon_location $icon_width_str $icon_height_str border=2 title=\"$this_uname\" $image_swap_javascript_str style=\"border-color:$this_color;\" id=\"user_".$result_arr['user_no']."\"></a></td>\r\n";
			$outhtml .= "<td class=\"table2a\" $l_live_color><font color=$this_color>◆</font>$this_handle_name<br /> <strong>$role_str</strong><br />$l_live_str $this_marked</td>\r\n";

		} 
		else {//生きていてゲーム中
			if (strstr((string) $game_option, 'votedisplay') && $this_live == 'live' && $l_day_night == 'day' && $this_uname != "dummy_boy") {
				//$res_already_voted = $db->query("select count(uname) from vote where room_no = '$room_no' and uname = '$this_uname'");
				//$already_voted = $db->result($res_already_voted,0);
			
				//if($already_voted) {
				if(strstr((string) $voted_list, ':;:' . $this_uname . ':;:')) {
					$l_live_color = " bgcolor=#d0FFFF";
				} else {
					$l_live_color = "";
				}
			} else {
				$l_live_color = "";
			}
			
			$outhtml .= "<td class=\"table2b\" valign=top>";
			$tripsce_url = "#";
			if($result_arr['trip'] != "") {
				$tripsce_url = "trip.php?go=sce&room=$room_no&trip=".$result_arr['trip'];
				if (strstr((string) $game_option,'usr_guest') && $l_day_night != 'aftergame' && $day_night != 'aftergame' && $day_night != 'beforegame') {
					if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                        $tripsce_url = "#";
                    }
				}
			}
			$outhtml .= "<a href = \"$tripsce_url\" target=\"_blank\"><img src=\"$icon_location\" $icon_width_str $icon_height_str border=2 $image_swap_javascript_str style=\"border-color:$this_color;\" id=\"user_".$result_arr['user_no']."\"></a></td>\r\n";
			$outhtml .= "<td class=\"table2a\" $l_live_color><font color=$this_color>◆</font>$this_handle_name<br />$l_live_str</td>\r\n";
		}
	}
	$db->free_result($result);
	$outhtml .= "</td></tr></table>";
	$outhtml .= "</tr></table>";
	if ($outhtm) {
		return $outhtml;
	} else {
		echo $outhtml;
	}
}

//----------------------------------------------------------
//死亡者の遺言を出力
function LastWordsOutput($outhtm = FALSE)
{
	global $room_no,$date,$day_night,$game_option,$room_status,$db,$isold;
	
	if( ($day_night == 'beforegame') || ($day_night == 'aftergame') )
		return;
	
	$date_yesterday = $date -1;
	
	$ws_alt = ['',
					'(遺書被風吹走了)',
					'(遺書被C4炸爛了)',
					'(遺書被山羊當作食物吃掉了)',
					'(小企鵝我愛你!!!)',
					'(遺書被暴斃王偷走了)',
					'(你甚麼都沒看到，只看到斑斑血跡跟一隻沾滿鮮血的手臂)',
					'(遺書滿滿的閃光，根本看不到字)',
					'(遺書這樣寫著:師父~~~救我~~~~~~)',
					'(遺書不小心被當金紙燒掉了)',
					'(威望必須大於等於 1 才能瀏覽)',
					'(以下附件必須跟帖回文才可下載)',
					'(急著要上廁所，遺書拿去當衛生紙用…)',
					'(好冷，先拿遺書取暖先…變成灰了)',
					'(事情發生的太突然，來不及寫遺書)',
					'(鍵盤突然不能打字，遺書打不出來)',
					'(打到一半突然瀏覽器當掉，遺書空白一片)',
					'(此遺書需要小企鵝才能看的到)',
					'(用便宜墨水，遺書退色看不到字)',
					''
					];
//	$n_ws_alt = count($ws_alt) -1;
	
	//前日の死亡者遺言を出力
	$res_last_words = $db->query("select message from system_message{$isold}
											where room_no = '$room_no' and date = $date_yesterday and  type = 'LAST_WORDS'
																											order by rand();");
	$last_words_count = $db->num_rows($res_last_words);
	$outhtml = "";
	if($last_words_count > 0)
	{
		$outhtml .= "<table border=0 cellpadding=0 cellspacing=0 width=100%>\r\n";
		$outhtml .= "<tr style=\"background-color:#ccddff;color:black;font-weight:bold;\">\r\n";
		$outhtml .= "<td valign=middle align=left colspan=3 width=100% style=\"background-color:#ccddff;color:black;font-weight:bold;\">　　　　　　　　　　　・早上發現死者的遺書</td>\r\n";
		$outhtml .= "</tr></table>\r\n";
	}
	$outhtml .= "<table border=0 cellpadding=0 cellspacing=0 width=100%>";
	while($message_str = $db->fetch_array($res_last_words)) {
	//	$message_str = $db->result($res_last_words,$i,0);
		
		$message_str = $message_str['message'];
		$message_str = explode ("\t", (string) $message_str);
		$last_words_handle_name = $message_str[0];
		$messages = count($message_str) + 1;
		$last_words_str = '';
		for ($i = 1;$i <= $messages;$i++) {
			$last_words_str .= $message_str[$i];
			if ($i > 1) {
				$last_words_str .= "\t";
			}
		}
		if (strstr((string) $game_option,"will") || $room_status != 'playing') {
			$last_words_str = str_replace("\n","<br />",$last_words_str);
		} else {
		//	$r_ws_alt = randme(0,$n_ws_alt);
			$last_words_str = $ws_alt[$date];
		}
		
		/*
		$message_str_enc = str_replace(" ","\\space;",$message_str_enc);
		sscanf($message_str_enc,"%s\t%s",$last_words_handle_name,$last_words_str);
		$last_words_handle_name = str_replace("\\space;"," ",$last_words_handle_name);
		$last_words_str = str_replace("\\space;"," ",$last_words_str);
		*/
		
			 $last_words_str = preg_replace([
	        "/\[b\](.*?)\[\/b\]/is",
	        "/\[u\](.*?)\[\/u\]/is",
	        "/\[d\](.*?)\[\/d\]/is"], [
	        "<b>\\1</b>",
	        "<u>\\1</u>",
	        "<del>\\1</del>"], $last_words_str);
		
		if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
			$last_words_handle_name = "(馬賽克)";
		}
		$outhtml .= "<tr style=\"background-color:#eeeeff;\">\r\n";
		$outhtml .= "<td width=140 align=left valign=middle style=\"color:black;border-top: silver 1px dashed;\">";
		$outhtml .= "$last_words_handle_name <small>的遺言</small>";
		$outhtml .= "<td><span style=\"margin:1px;\" align=left></span></td>";
		$outhtml .= "<td valign=middle style=\"border-top: silver 1px dashed;\">";
		$outhtml .= "<table><td style=\"color:black;\">$last_words_str </span></td></table>";
		$outhtml .= "</td>\r\n";
		$outhtml .= "</tr>\r\n";
		
	}
	$outhtml .= "</table>";
	if ($outhtm) {
		return $outhtml;
	} else {
		echo $outhtml;
	}
}

//----------------------------------------------------------
//勝敗が決まったかどうかチェック
function CheckVictory($manual_set = ''): void
{
	global $room_no,$date,$vote_times,$revote_draw_times,$db,$isold,$max_user;

	if($manual_set != '') {
		$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = '$manual_set'
				   where room_no = '$room_no'");
		return;
	}

	//全人数を取得
	$res_total_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live' and user_no > '0' and user_no <= $max_user");
	$total_count = (int)$db->result($res_total_count,0);

	//恋人の数を取得
	$res_lovers_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
								 and lovers = '1' and user_no > '0'");
	$lovers_count = (int)$db->result($res_lovers_count,0);

	//狼の数を取得
	$res_wolf_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
								 and role like 'wolf%' and user_no > '0'");
	$wolf_count = (int)$db->result($res_wolf_count,0);

	//狼、狐以外の数を取得
	$res_human_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
								 and !( role like 'wolf%') and !(role like 'fox%') and !(role like 'fosi%') and role <> 'GM' and user_no > '0'");
	$human_count = (int)$db->result($res_human_count,0);

	//狐の数を取得
	$res_fox_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
								 and (role like 'fox%' or role like 'fosi%') and user_no > '0'");
	$fox_count = (int)$db->result($res_fox_count,0);

	//企鵝の数を取得
	$res_pengu_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
								 and role like 'pengu%' and user_no > '0'");
	$pengu_count = (int)$db->result($res_pengu_count,0);
	//企鵝不算人頭，扣除
	$human_count = $human_count - $pengu_count;
	if ($human_count < 0) {
		$human_count = 0;
	}

	//突然死で全滅
	//TODO: 加上特殊case的判例
	if( ($wolf_count == 0) && ($human_count == 0) && ($fox_count == 0) )
	{
		//ゲーム終了
		$db->query("update room set status = 'finished',day_night = 'aftergame' where room_no = '$room_no'");
	}
	elseif ($total_count <= 4 && $lovers_count == 2 && isLoversVictoryOnly()) {
		$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'lover'
						 where room_no = '$room_no'");
		tosqlgover(); //更新是否結束
	}
	elseif( $wolf_count == 0 )
	{
		if ($total_count <= 4 && $lovers_count == 2 && isLoversVictoryOnly()) {
			$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'lover'
						 where room_no = '$room_no'");
		}
		elseif( $fox_count == 0) //村民勝利
		{
			//系統メッセージ
			//$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'human','VICTORY',$date)");
			$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'human'
						 where room_no = '$room_no'");
		}
		else //狐勝利
		{
			//系統メッセージ
			//$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'fox1','VICTORY',$date)");
			$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'fox1'
						 where room_no = '$room_no'");
		}
		//ゲーム終了
	//	$db->query("update room set status = 'finished',day_night = 'aftergame' where room_no = '$room_no'");
		tosqlgover(); //更新是否結束
	}
	elseif( ($wolf_count >= $human_count))
	{
		if ($total_count <= 4 && $lovers_count == 2 && isLoversVictoryOnly()) {
			$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'lover'
						 where room_no = '$room_no'");
		}
		else 
		{			
			//大狼數量取得
			$res_wolfb_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
										 and role = 'wolf wfbig' and user_no > '0'");
			$wfbig_count = $db->result($res_wolfb_count,0);

			$res_fosi_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
										 and role like 'fosi%' and user_no > '0'");

			$fosi_count = $db->result($res_fosi_count,0);
			$fox_count -= $fosi_count;


			if($fox_count > 0) {
				//系統メッセージ
				//$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'fox2','VICTORY',$date)");
				$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'fox2'
						 where room_no = '$room_no'");
			} else if($fosi_count > 0) {
				if($wfbig_count > 0) {
					//系統メッセージ
					//$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'wolf','VICTORY',$date)");
					$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'wolf'
								 where room_no = '$room_no'");
				} else {
					//系統メッセージ
					//$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'fox2','VICTORY',$date)");
					$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'fox2'
								where room_no = '$room_no'");
				}
			} else {
				//系統メッセージ
				//$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'wolf','VICTORY',$date)");
				$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'wolf'
								 where room_no = '$room_no'");
			}
			/* 
			if(FOX_EXISTS) fox_victory;
			else if(FOSI_EXISTS) {
				if(WFBIG_EXISTS) {
					wolf_victory;
				} else {
					fox_victory;
				}
			} else wolf_victory;
			*/

		}
		//ゲーム終了
	//	$db->query("update room set status = 'finished',day_night = 'aftergame' where room_no = '$room_no'");
		tosqlgover(); //更新是否結束
	}

	//平手のチェック
	if($vote_times == $revote_draw_times)
	{
		//系統メッセージ
		//$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'draw','VICTORY',$date)");

		//ゲーム終了
		$db->query("update room set status = 'finished',day_night = 'aftergame',victory_role = 'draw' where room_no = '$room_no'");
		tosqlgover(); //更新是否結束
	}

	if (!($wolf_count == 0 && $human_count == 0 && $fox_count == 0)) {
        $query = $db->query("SELECT * from user_entry where room_no = '$room_no' and user_no > '0' and role != 'none'");
        while ($list = $db->fetch_array($query)) {
			if ($list['trip'] != "") {
				$db->query("UPDATE user_trip SET handle_name = '".$list['handle_name']."' where trip = '".$list['trip']."';");
			}
		}
    }

//	$db->query("commit"); //一応コミット
}


//----------------------------------------------------------
//再投票の時、メッセージを表示
function ReVoteListOutput(): void
{
	global $room_no,$date,$day_night,$uname,$play_sound,$cookie_vote_times,$revote_mp3,$revote_draw_times,$view_mode,$db,$isold;
	
	if($day_night != 'day') //再投票は白のみ
		return;
	
	//再投票の回数を取得
	$res_revote = $db->query("select message from system_message{$isold} where room_no = '$room_no' and date = $date and type = 'RE_VOTE'
																										order by message DESC");
	
	if($db->num_rows($res_revote) == 0)
		return;
	
	//何回目の再投票なのか取得
	$last_vote_times = (int)$db->result($res_revote,0,0);
	
	if( ($play_sound == 'on') && ($view_mode != 'on') )
	{
		if($last_vote_times > $cookie_vote_times)
		{
			//音を鳴らす
			//echo "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,0,0\" width=1 height=1>\r\n";
			//echo "<param name=movie value=\"$revote_swf\">\r\n";
			//echo "<param name=quality value=high>\r\n";
			//echo "<embed src=\"$revote_swf\" quality=high width=1 height=1 type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\">\r\n";
			//echo "</embed>\r\n";
			//echo "</object>\r\n";
			echo "<audio src=\"$revote_mp3\" autoplay>HTML5 audio not supported</audio>\r\n";
		}
	}
	
	$this_vote_times = $last_vote_times + 1;
	$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date
																and vote_times = $this_vote_times and uname = '$uname'");
	$already_vote = $db->result($res_already_vote,0);
	
	if($already_vote == 0)
	{
		$revote_message = "　　　投票重新開始 (" . $revote_draw_times . "回重新投票的話將會判定遊戲和局)　　　";
		echo "<span style=\"font-size:14pt;font-weight:bold;background-color:red;color:snow;\">$revote_message</span><br />";
	}
	
	echo VoteListDayOutput($date);
}

//快取檔案輸出
function roomsysnew(string $file,$is = ''): void {
	global $room_no;
	//寫入快取
	if($fp = fopen('tmp/'.$file, 'wb')) {
		fwrite($fp, "\x3C?php\nif(!defined('IN_JINRO')) exit('Access Denied');\n//cache file, DO NOT modify me!\n?\x3E\n"."$is"
		);
		fclose($fp);
	}
}

//分頁
function multi($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $autogoto = TRUE, $simple = FALSE): string {
	global $maxpage;

	$multipage = '';
	$mpurl .= strpos((string) $mpurl, '?') ? '&amp;' : '?';
	$realpages = 1;
	if($num > $perpage) {
		$offset = 2;

		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1">[1] ...</a> ' : '').
			($curpage > 1 && !$simple ? '<a href="'.$mpurl.'page='.($curpage - 1).'">&lsaquo;&lsaquo;</a>' : '');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? ' <span style="color: Red;">['.$i.']</span> ' :
				' <a href="'.$mpurl.'page='.$i.'">['.$i.']</a> ';
		}

		$multipage .= ($curpage < $pages && !$simple ? '<a href="'.$mpurl.'page='.($curpage + 1).'">&rsaquo;&rsaquo;</a>' : '').
			($to < $pages ? ' <a href="'.$mpurl.'page='.$pages.'">... ['.$realpages.']</a>' : '');

		$multipage = $multipage ?: '';
	}
	$maxpage = $realpages;
	return $multipage;
}

function StartsWith($h, $n): bool{
    return str_starts_with((string) $h, (string) $n);
}

function DetermineRole($role): string {
	global $uname;

	if($uname == 'dummy_boy') return 'neutral';

	if(strstr((string) $role,'pengu')) {
		return 'wolf fox';
	} elseif (strstr((string) $role,"wolf") || strstr((string) $role,"mad") || strstr((string) $role,"spy")) {
		return 'wolf';
	} elseif (strstr((string) $role,"fox") || strstr((string) $role,"betr") || strstr((string) $role,"fosi")) {
		return 'fox';
	} elseif (strstr((string) $role,"GM") || strstr((string) $role,"mytho")) {
		return 'neutral';
	} else {
		return 'human';
	}
}

function isLoversVictoryOnly(): bool {
	global $db,$room_no,$isold;
	$lovers_main = [];
	$i = 0;
	
	$res_lovers = $db->query("select role from user_entry where room_no = '$room_no' and lovers = '1' and user_no > '0'");
//	echo $db->num_rows($res_lovers)."<br />";
	
	while ($role = $db->fetch_array($res_lovers)) {
		$lovers_main[$i] = DetermineRole($role['role']);
		$i++;
	}
	if ($db->num_rows($res_lovers) == 2 && $lovers_main[0] != $lovers_main[1]) {
		return true;
	} else {
		return false;
	}
	
}

function VillageOptOutput($outhtm = FALSE) 
{
	//global ;
	global $game_option, $option_role, $max_user,$playing_image,$waiting_image,$maxuser_image_array,$room_option_wish_role_image,$room_option_dummy_boy_image,
			$room_option_open_vote_image,$room_option_decide_image,$room_option_authority_image,$room_option_poison_image,$day_night,
			$room_option_real_time_image,$room_option_betr_image,$room_option_rei_image,$room_option_conn_image,$db,$isold,
			$room_option_fosi_image,$room_option_foxs_image,$room_option_wfbig_image,$room_option_cat_image,$room_option_voteme_image,
			$room_option_trip_image,$room_option_will_image,$room_option_lovers_image,$room_option_gm_image, $game_dellook,$room_option_guest_image;
		global $room_option_mytho_image, $room_option_owlman_image, $room_option_noconn_image,$room_option_pengu_image,$room_option_noble_image,$room_option_spy_image,$room_option_ischat_image;
		
		$dellook = $game_dellook;
	
	$option_img_str = '<small>村莊選項：'; //ゲームオプションの画像
	if(strstr((string) $game_option,"wish_role"))
	{
		$option_img_str .= "<img src=\"$room_option_wish_role_image\" border=0 width=16px height=16px alt=\"希望角色\" title=\"希望角色\" >";
	}
	if(strstr((string) $game_option,"real_time"))
	{

		$real_time_str = strstr((string) $game_option,"real_time");
		sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_minutes,$night_real_limit_minutes);
		$real_time_alt_str = "限制時間　白： $day_real_limit_minutes 分　夜： $night_real_limit_minutes 分";

		$option_img_str .= "<img src=\"$room_option_real_time_image\" border=0 width=16px height=16px alt=\" $real_time_alt_str \" title=\" $real_time_alt_str \">";
		//$option_img_str .= "[".$day_real_limit_minutes.":".$night_real_limit_minutes."]";
	}
	if(strstr((string) $game_option,"dummy_boy"))
	{
		$option_img_str .= "<img src=\"$room_option_dummy_boy_image\" border=0 width=16px height=16px alt=\"第一天晚上的替身君\" title=\"第一天晚上的替身君\" >";
	}
	if(!strstr((string) $game_option,"ischat")) {
		if(strstr((string) $game_option,"dummy_isred"))
		{
			$option_img_str .= "<img src=\"$room_option_dummy_boy_image\" border=0 width=16px height=16px alt=\"替身君部分職業自動遺書，不知道職業\" title=\"替身君部分職業自動遺書，不知道職業\" >";
		}
		if(strstr((string) $game_option,"dummy_autolw"))
		{
			$option_img_str .= "<img src=\"$room_option_dummy_boy_image\" border=0 width=16px height=16px alt=\"替身君部分職業自動遺書，知道職業\" title=\"替身君部分職業自動遺書，知道職業\" >";
		}
		if(strstr((string) $game_option,"open_vote"))
		{
			$option_img_str .= "<img src=\"$room_option_open_vote_image\" border=0 width=16px height=16px alt=\"公開投票結果票數\" title=\"公開投票結果票數\" >";
		}
		if($dellook && !strstr((string) $option_role, "cat") && !strstr((string) $game_option,"as_gm"))
		{
			$option_img_str .= "<img src=\"$room_option_rei_image\" border=0 width=16px height=16px title=\"幽靈可以看到角色\">";
		}			
		if(strstr((string) $game_option,"votedme"))
		{
			$option_img_str .= "<img src=\"$room_option_voteme_image\" border=0 width=16px height=16px title=\"允許白天自投(一次)\">";
		}
	}
	if(strstr((string) $game_option,"istrip"))
	{
			$trip_count_str = strstr((string) $game_option,"istrip");
			sscanf($trip_count_str,"istrip:%d",$trip_limit);
			$option_img_str .= "<img src=\"$room_option_trip_image\" border=0 width=16px height=16px title=\"強制Trip註冊，最低要求:".$trip_limit."\">";
	}
	if(!strstr((string) $game_option,"ischat")) {
		if(strstr((string) $game_option,"will"))
		{
			$option_img_str .= "<img src=\"$room_option_will_image\" border=0 width=16px height=16px title=\"允許遺書顯示\">";
		}
	}
	if(strstr((string) $game_option,"usr_guest"))
	{
		$option_img_str .= "<img src=\"$room_option_guest_image\" border=0 width=16px height=16px title=\"匿名遊戲\">";
	}
	
	if(!strstr((string) $game_option,"ischat") && $max_user >= 16) 
	{
		
		if(strstr((string) $option_role,"decide"))
		{
			$option_img_str .= "<img src=\"$room_option_decide_image\" border=0 width=16px height=16px alt=\"16人以上時決定者登場\" title=\"16人以上時決定者登場\">";
		}
		if(strstr((string) $option_role,"authority"))
		{
			$option_img_str .= "<img src=\"$room_option_authority_image\" border=0 width=16px height=16px alt=\"16人以上時權力者登場\" title=\"16人以上時權力者登場\">";
		}
		
	}
	
	
		if(!strstr((string) $game_option,"ischat") && $max_user >= 10) {
			if(strstr((string) $option_role, 'spy')) 
			{
				$option_img_str .= "<img src=\"$room_option_spy_image\" border=0 width=16px height=16px title=\"隔壁村莊的間諜\">";
			}
		}

	if(!strstr((string) $game_option,"ischat") && $max_user >= 13) 
	{
		if(strstr((string) $option_role,"noble"))
		{
			$option_img_str .= "<img src=\"$room_option_noble_image\" border=0 width=16px height=16px alt=\"13人以上時貴族與奴隸登場\" title=\"13人以上時貴族與奴隸登場\">";
		}
		
		// comm
		if($max_user >= 20 && strstr((string) $option_role,"comlover"))
		{
			if(strstr((string) $game_option,"comoutl"))
			{
				$option_img_str .= "<img src=\"$room_option_conn_image\" border=0 width=16px height=16px title=\"晚上共生對話允許顯示\">";
			}				
			$option_img_str .= "<img src=\"$room_option_conn_image\" border=0 width=16px height=16px title=\"20人時出現共有者與隨機戀人\">";
			$option_img_str .= "<img src=\"$room_option_lovers_image\" border=0 width=16px height=16px title=\"相戀的兩人，隨機版\">";			} 
		else 
		{
			if(!(strstr((string) $option_role,"noflash") || strstr((string) $option_role,"r_lovers") || strstr((string) $option_role,"s_lovers")) && strstr((string) $game_option,"comoutl"))
			{
				$option_img_str .= "<img src=\"$room_option_conn_image\" border=0 width=16px height=16px title=\"晚上共生對話允許顯示\">";
			}								
			if(strstr((string) $option_role,"noflash"))
			{
				$option_img_str .= "<img src=\"$room_option_noconn_image\" border=0 width=16px height=16px title=\"13人時無共有者或任何兩人規則設定\">";
			}				
			if(strstr((string) $option_role,"r_lovers"))
			{
				$option_img_str .= "<img src=\"$room_option_lovers_image\" border=0 width=16px height=16px title=\"13人時出現相戀的兩人，隨機版\">";
			}
			if(strstr((string) $option_role,"s_lovers"))
			{
				$option_img_str .= "<img src=\"$room_option_lovers_image\" border=0 width=16px height=16px title=\"13人時出現相戀的兩人，村村戀版本\">";
			}
		}
		
		if(strstr((string) $option_role, 'mytho'))
		{
			$option_img_str .= "<img src=\"$room_option_mytho_image\" border=0 width=16px height=16px title=\"妄想成為人狼或占卜師的「生物」\">";
		}
	}
	
	if(!strstr((string) $game_option,"ischat") && $max_user >= 20)
	{
		// poison
		if(strstr((string) $option_role,"poison"))
		{
			$option_img_str .= "<img src=\"$room_option_poison_image\" border=0 width=16px height=16px alt=\"20人以上時埋毒者登場\" title=\"20人以上時埋毒者登場\">";
		}
		if(strstr((string) $option_role,"cat"))
		{
			$option_img_str .= "<img src=\"$room_option_cat_image\" border=0 width=16px height=16px alt=\"20人以上時貓又登場\" title=\"20人以上時貓又登場\">";
		}
		
		// wolf
		if(strstr((string) $option_role,"wfbig"))
		{
			$option_img_str .= "<img src=\"$room_option_wfbig_image\" border=0 width=16px height=16px alt=\"20人以上時大狼登場\" title=\"20人以上時大狼登場\">";
		}
		if(strstr((string) $option_role,"morewolf") && !strstr((string) $option_role,"poison") && !strstr((string) $option_role,"cat"))
		{
			$option_img_str .= "<img src=\"$room_option_wfbig_image\" border=0 width=16px height=16px alt=\"20人以上無毒時追加人狼\" title=\"20人以上無毒時追加人狼\">";
		}
		
		// fox
		if(strstr((string) $option_role,"betr"))
		{
			$option_img_str .= "<img src=\"$room_option_betr_image\" border=0 width=16px height=16px alt=\"妖狐的同伴\" title=\"妖狐的同伴\">";
		}
		if(strstr((string) $option_role,"foxs"))
		{
			$option_img_str .= "<img src=\"$room_option_foxs_image\" border=0 width=16px height=16px alt=\"兩隻妖狐\" title=\"兩隻妖狐\">";
		}
		if(strstr((string) $option_role,"fosi"))
		{
			$option_img_str .= "<img src=\"$room_option_fosi_image\" border=0 width=16px height=16px alt=\"妖狐的占\" title=\"妖狐的占\">";
		}
		
		if(strstr((string) $option_role, 'owlman')) 
		{
			$option_img_str .= "<img src=\"$room_option_owlman_image\" border=0 width=16px height=16px title=\"帶來不幸的村人\">";
		}
		
		if(strstr((string) $option_role,'pengu'))
		{
			$option_img_str .= "<img src=\"$room_option_pengu_image\" border=0 width=16px height=16px title=\"小企鵝客串登場\">";
		}
	}
	
	if(strstr((string) $game_option,"ischat")) 
	{
		$option_img_str .= "<img src=\"$room_option_ischat_image\" border=0 width=16px height=16px title=\"限定聊天\">";
	}
	
	if(strstr((string) $game_option,"as_gm"))
	{
		$gm_str = strstr((string) $game_option,"gm:");
		sscanf($gm_str, "gm:%s", $gmtrip);
		if($gmtrip != '')
				$option_img_str .= "<img src=\"$room_option_gm_image\" border=0 width=16px height=16px title=\"GM制，Trip: $gmtrip\">";

	}
	
	
	//最大人数
	$max_user_img = $maxuser_image_array[$max_user];
	
	
	$isusr_guest = false;
	if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
		$isusr_guest = true;
	}
	if(strstr((string) $game_option,"gm:") && !$isusr_guest)
	{
		$gm_str = strstr((string) $game_option,"gm:");
		sscanf($gm_str, "gm:%s", $gmtrip);
		$max_user_str = '<a href="trip.php?go=trip&id='.$gmtrip.'" target="_new"><img src="'.$max_user_img.'" title="村長Trip: '.$gmtrip.'" border=0></a>';	
	} else 
		$max_user_str = "<img src=\"$max_user_img\" border=0>";

	$outhtml = "";
	$outhtml .=  $option_img_str."&nbsp;".$max_user_str." </small>\r\n";
	$outhtml .=  " <small><div style=\"display: inline-block;\" id=\"saylog\"></div></small>";
	if ($outhtm) {
		return $outhtml;
	} else {
		echo $outhtml;
	}
}

//更新是否結束
function tosqlgover(): void {
	global $db,$room_no,$isold;
	$db->query("update user_entry set gover = '1' where room_no = '$room_no' and user_no > '0' and role != 'none'");
}

//縮圖處理
function MakeSmallPicture($w, $sf, $df): void {
	$df = $df.".webp";
    try {
        $imagick = new Imagick();
        $imagick->readImage($sf);

        // 檢查是否是動畫圖片（多幀）
        $isAnimated = $imagick->getNumberImages() > 1;

        if ($isAnimated) {
            $imagick = $imagick->coalesceImages();

            foreach ($imagick as $frame) {
                $frame->scaleImage($w, $w, true); // 等比縮圖
                $frame->setImageFormat('webp');
            }

            $imagick = $imagick->deconstructImages();
            $imagick->writeImages($df, true); // true 保留動畫
        } else {
            $imagick->scaleImage($w, $w, true);
            $imagick->setImageFormat('webp');
            $imagick->writeImage($df);
        }
    } catch (Exception) {
        exit;
    }
}

//更新是否結束
function tosqlvoted($daynight = ''): void {
	global $db,$room_no,$isold,$voted_list;
	$sqls = '';
	if ($daynight == 'beforegame' ) {
		$sqls = "and situation = 'GAMESTART'";
	}
	$res_already_voted = $db->query("select uname from vote where room_no = '$room_no' $sqls;");
	while($res_vote = $db->fetch_array($res_already_voted)) {
		$voted_list = $voted_list . $res_vote['uname'] . ':;:';
	};
}

function diamcssout(): string {
	global $room_no,$day_night,$background_color_beforegame,$text_color_beforegame,$background_color_aftergame,$text_color_aftergame;
	global $background_color_night,$text_color_night,$background_color_day,$text_color_day;
	$daycc = "";
	if ($day_night == "beforegame") {
		$background_color = $background_color_beforegame;
		$text_color = $text_color_beforegame;
		$a_color = 'blue';
		$a_vcolor = 'blue';
		$a_acolor = 'red';
	}
	if ($day_night == "aftergame") {
		$background_color = $background_color_aftergame;
		$text_color = $text_color_aftergame;
		$a_color = 'blue';
		$a_vcolor = 'blue';
		$a_acolor = 'red';
	}
	if ($day_night == "night") {
		$background_color = $background_color_night;
		$text_color = $text_color_night;
		$a_color = '#8080FF';
		$a_vcolor = '#8080FF';
		$a_acolor = 'red';
	}
	if ($day_night == "day") {
		$background_color = $background_color_day;
		$text_color = $text_color_day;
		$a_color = 'blue';
		$a_vcolor = 'blue';
		$a_acolor = 'red';
	}
	$daycc .= "<style id=\"diamcssajax\" type=\"text/css\">\n";
	$daycc .= "body{background-color:$background_color;color:$text_color;} \n";
	$daycc .= "A:link { color: $a_color; } A:visited { color: $a_vcolor; } A:active { color: $a_acolor; } A:hover { color: red; } \n";
	if ($day_night != "aftergame") {
		$daycc .= ".left_real_time{ color:$text_color; background-color:$background_color;font-size:11pt;border-width:0px;border-style:solid;}\n";
	}
	return $daycc . "</style>\n";
}
?>
