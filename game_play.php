<?php
require_once __DIR__ . '/game_functions.php';
require_once __DIR__ . '/msgimg_setting.php';

//Session開始
session_start();
$session_id = session_id();

set_time_limit(0);
ob_end_clean();
ob_implicit_flush();
header("X-Accel-Buffering: no");

$go = empty($go) ? "" : $go;
$say = is_null($say) ? "" : $say;
$vote_times = empty($vote_times) ? "" : $vote_times;
$day_night = empty($day_night) ? "" : $day_night;
$objection = empty($objection) ? "" : $objection;
$play_sound = empty($play_sound) ? "off" : $play_sound;

//音效通知
if($play_sound == 'on')
{
	$cookie_day_night = $day_night;  //夜明けを音でしらせるため
	$cookie_vote_times = $vote_times; //再投票を音で知らせるため
	$cookie_objection = $objection; //「異議あり」を音で知らせるため
}


//載入快取檔案
//@include_once 'tmp/cache_'.$room_no.'.php';
/*
0 = user_no
1 = uname
2 = handle_name
3 = icon_no
4 = profile
5 = sex
6 = role
7 = live
8 = last_words
9 = last_load_day_night
*/


//MySQLに接続
if($db->connect_error())
{
	exit;
}

if( ($uname = SessionCheck($session_id)) != NULL )
{
	//echo "for_test:<br />";
	//echo isLoversVictoryOnly();
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
	$game_dellook = $room_arr['dellook'];
	$game_update = $room_arr['uptime'];
	$max_user = $room_arr['max_user'];
	$gm_pos = $max_user + 1;
	$db->free_result($res_room);
	
	//自分のハンドルネーム、役割、生存を取得
	$res_user = $db->query("select uid,user_no,handle_name,sex,role,live,last_load_day_night,trip from user_entry
							where room_no = '$room_no' and uname = '$uname' and  (user_no > 0 or (uname = 'dummy_boy' and user_no = -1))");
	$user_arr = $db->fetch_array($res_user);
	$user_no = $user_arr['user_no'];
	$handle_name = $user_arr['handle_name'];
	$sex = $user_arr['sex'];
	$role = $user_arr['role'];
	$live = $user_arr['live'];
	$trip = $user_arr['trip'];
	$userid = $user_arr['uid'];
	$last_load_day_night = $user_arr['last_load_day_night'];
	$db->free_result($res_user);
	
	
	
	//必要なクッキーをセットする
	$objection_arr = []; //SendCookie();で格納される・異議ありの情報
	$objection_left_count = 0; //SendCookie();で格納される・異議ありの残り回数
	SendCookie();
	
	//勝敗のチェック
	$victory_flag = false;
	//$res_victory = $db->query("select message from system_message where room_no = '$room_no' and type = 'VICTORY'");
	if ($isold) {
		$res_victory = $db->query("select victory_role from room{$isold} where room_no = '$room_no'");
	} else {
		$res_victory = $db->query("select victory_role from room where room_no = '$room_no'");
	}
	$res_victory = $db->query("select victory_role from room{$isold} where room_no = '$room_no'");
	$victory_role = $db->result($res_victory,0,0);
	
	if( $victory_role != NULL )
	{
		$victory_flag = true;
	}
	
	//特殊な文字を変換
	$say = str_replace("\\","",(string) $say);
	$say = str_replace("&","&amp;",$say);
	$say = str_replace("<","&lt;",$say);
	$say = str_replace(">","&gt;",$say);
	$say = str_replace("'","\\'",$say);
	
	$echosocket = "<script src=\"img/jquery-3.6.0.min.js\"></script>
		<script type=\"text/javascript\">
	var socket;
	function socketinit() {
		var host = \"wss://diam.ngct.net:8443/wss/".authcode("$room_no\t$userid", "ENCODE")."\";
		socket = new WebSocket(host);
		socket.onopen = function (evt) {
				socket.send(\"GO\");
		};
		socket.onmessage = function (evt) {
				socket.close();
		};
		socket.onclose = function (evt) {
			jsrefresh();
		};
	}
	function jsrefresh()
	{
		window.location.href = \"https://".$domain_name."/index.php\";
	}
	</script>";
	
	//退出
	if ($go == 'out') {
		if ($room_status == "waiting" && $uname != 'dummy_boy') {
			$res_target_no = $db->query("select user_no from user_entry where room_no = '$room_no'
											and handle_name = '$handle_name' and user_no > '0'");
			$target_no = $db->result($res_target_no,0,0);
			$db->query("update user_entry set user_no = -1 , live = 'dead' , session_id = NULL
							where uid = '$userid';");
			$db->query("delete from vote where room_no = '$room_no'");
			$db->query("update user_entry set user_no = user_no-1 where room_no = '$room_no' and user_no > $target_no and user_no < ".($max_user+1));
			$time = time();
			$db->query("update room set status = 'waiting',day_night = 'beforegame',last_updated = '$time' where room_no = '$room_no'"); 
			$time++;
			$kick_message_str = msgimg($msg_human_image)."$handle_name 離開這個村莊了";
			$res = $db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
									values ($room_no,$date,'$day_night system','system','$time','$kick_message_str','0')");
			$time++;
			$reset_vote_str = msgimg($msg_sys_image).'＜投票重新開始 請盡速重新投票＞';
			$res = $db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
									values ($room_no,$date,'$day_night system','system','$time','$reset_vote_str','0')");
		}
		session_destroy();
		session_id(1);
		session_start();
		echo "<html><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><title>OK</title>$echosocket</head><body bgcolor=aliceblue onload=\"socketinit();\">Delete ok!<br />Please wait for one second.</body></html>";
		exit;
	}
	
	//刪除房間處理
	if (($uname == 'dummy_boy' || (strstr((string) $game_option, 'gm:'.$trip)) && $trip != '') && $go == 'del' && is_numeric($id)) {
		$db->query("UPDATE room SET status = 'finished',day_night = 'aftergame' where room_no = '".$id."'");
		$db->query("DELETE from vote where room_no = '".$id."';");
		//刪除快取
		if ($cachefile) {
			unlink('tmp/messH_'.$id.'.php');
		}
		echo "<html><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><title>OK</title>$echosocket</head><body bgcolor=aliceblue onload=\"socketinit();\">Delete ok!<br />Please wait for one second.</body></html>";
		exit;
	}
	
	//発言したかリロードか(最後にリロードしたときの部屋の状態と同じだったら書き込む)
	if( ($say != '') && ( ($last_load_day_night == $day_night) || ($live == 'dead' || $live == 'gone') ) ) {
		Say($say,$font_type,$font_type_b.",".$font_type_del);
		//覆蓋查詢
		if ($isold) {
			$res_room = $db->query("select uptime from room{$isold} where room_no = '$room_no'");
		} else {
			$res_room = $db->query("select uptime from room where room_no = '$room_no'");
		}
		$room_arr = $db->fetch_array($res_room);
		$game_update = $room_arr['uptime'];
		$db->free_result($res_room);
	} else {
		SilenceCheck(); //ゲーム停滞のチェック(沈黙、突然死)
	}
	
	//最後にリロードした時の部屋の状態を更新
	$db->query("update user_entry set last_load_day_night = '$day_night' where uid = '$userid';");
//	$db->query("commit");
	//查詢快取
//	roomsysnew("user");
	
	HTMLHeaderOutput();       //HTMLヘッダ出力
	echo "<table>\r\n";
	echo "<tr>\r\n";
	if (in_array($room_status,['playing','waiting'])) {
		tosqlvoted($day_night);
	}
	GameHeaderOutput();       //部屋のタイトルと残り時間出力(勝敗が決まっていたら部屋のタイトルと勝敗出力)
	echo "</tr><tr>\r\n";
	if ($day_night == 'aftergame') {
		$game_dellook = 1;
	}
	if($heaven_mode == '')
	{
		if( $list_down != 'on' )
		{
			PlayerListOutput($game_dellook);      //Playヤー訊息を出力
			echo "</tr><tr>\r\n";
		}
		
		AbilityOutput();         //自分の役割の説明を出力
		echo "</tr><tr>\r\n";
		
		ReVoteListOutput();    //再投票の時、メッセージを表示する
		echo "</tr><tr>\r\n";
	}
	
	if( ($live == 'dead' || $live == 'gone') && ($heaven_mode == 'on')  )
	{
		HeavenTalkLogOutput();          //会話ログを出力
		echo "</tr><tr>\r\n";
	}
	else
	{
		TalkLogOutput($game_dellook);          //会話ログを出力
		echo "</tr><tr>\r\n";
	}
	
	if($heaven_mode == '')
	{
		if(($live == 'dead' || $live == 'gone') || strstr((string) $role, 'GM'))
		{
			AbilityActionOutput($game_dellook); //能力発揮を出力
			echo "</tr><tr>\r\n";
		}
		
		LastWordsOutput(); //遺言を出力
		echo "</tr><tr>\r\n";
		
		DeadManOutput();         //死亡者を出力
		echo "</tr><tr>\r\n";
		
		VoteListOutput();            //投票結果出力
		echo "</tr><tr>\r\n";
		
		if($dead_mode != 'on')
		{
			SelfLastWordsOutput();  //自己的遺言を出力
			echo "</tr><tr>\r\n";
		}
		
		if( $list_down == 'on' )
		{
			PlayerListOutput($game_dellook);   //Playヤー訊息を出力
		}
	}
	echo "</tr></table>\r\n";
	if ($ajax != "on") {
		HTMLFooterOutput();        //HTMLフッタ出力
	}

}
else
{
	echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>Session認證錯誤</title>
			</head><body bgcolor=aliceblue>
			<br /><br />';
	echo "　　　　Session認證錯誤，登入畫面移動中。<br />";
	echo "　　　　如果沒有移動<a href=\"game_view.php?room_no=$room_no\" target=\"_top\">按我繼續</a>";
	echo "<script language=\"javascript\">parent.location = \"game_view.php?room_no=$room_no\";</script>";
	
	echo "</body></html>";
	
}

//MySQLとの接続を閉じる
$db->close();

//***************************************************************
//               関数｜・∀・)・∀・)…
//***************************************************************


//----------------------------------------------------------
//発言
function Say($say,$font_type,$font_type2): void
{
	global $room_no,$uname,$role,$live,$date,$day_night,$day_limit_time,$night_limit_time,$time_zone,$db,$isold,$handle_name,$talk_target;


	$res_room = $db->query("select * from room where room_no = '$room_no'");
	$room_arr = $db->fetch_array($res_room);
	$game_option = $room_arr['game_option'];

	if ($font_type2 != "," && $font_type2 != "") {
		$font_type2 = rtrim((string) $font_type2,',');
		$font_type = $font_type.",".$font_type2;
		$font_type = str_replace(",,",",",$font_type);
	}

	//改行を\nに統一(MACは\r、Winは\r\n、Unixは\n？)
	$say = str_replace("\r\n","\n",(string) $say);
	$say = str_replace("\r","\n",$say);

	$gm_target = $talk_target;

	//遺言を残す
	if($font_type == 'last_words')
	{
		if($live == 'live')
		{
			if (mb_strlen($say, 'UTF-8') > 1024) {
				$say = mb_substr($say,0,1024,"UTF-8")."...";
			}
			$db->query("update user_entry set last_words = '$say' where room_no = '$room_no' and uname = '$uname'
					 	and user_no > '0'");
		//	$db->query("commit"); //一応コミット
			//查詢快取
		//	roomsysnew("user");
		}
		return;
	}

	$sqlheaven = strstr((string) $font_type, 'heaven') ? 1 : 0;

	$time = time();  //現在時刻、GMTとの時差を足す

	if( strstr((string) $game_option,"real_time") ) //限制時間
	{
		//實際時間的制限時間を取得
		$real_time_str = strstr((string) $game_option,"real_time");
		sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_minutes,$night_real_limit_minutes);
		$day_real_limit_time = $day_real_limit_minutes * 60; //秒になおす
		$night_real_limit_time = $night_real_limit_minutes * 60; //秒になおす


		//最も小さな時間(場面の最初的時間)を取得
		$res_start_real_time = $db->query("select min(time) from talk{$isold} where room_no = '$room_no' and date = $date
											and location like '$day_night%'");
		$start_real_time = $db->result($res_start_real_time,0,0);

		if($start_real_time != NULL)
		{
			$pass_real_time = $time - $start_real_time; //経過した時間
		}
		else
			$pass_real_time = 0;

		$spend_time = 0;//会話で時間経過制の方は無効にする
		$sum_spend_time = 0;


	}
	else //会話で時間経過制
	{
		if( strlen($say) <= 100 ) //経過時間
			$spend_time = 1;
		elseif( strlen($say) <= 200 )
			$spend_time = 2;
		elseif( strlen($say) <= 300 )
			$spend_time = 3;
		else
			$spend_time = 4;

		//経過時間的和
		$res_sum_spendtime = $db->query("select sum(spend_time) from talk{$isold} where room_no = '$room_no' and date = $date
																							and location like '$day_night%'");

		$sum_spend_time = (int)$db->result($res_sum_spendtime,0,0);

		$pass_real_time = 0; //限制時間的経過時間を無効にする
		$day_real_limit_time = 999; //適当に大きな数字を入れる
		$night_real_limit_time = 999; //適当に大きな数字を入れる
	}


	if( ($day_night == 'beforegame') || ($day_night == 'aftergame') ) //ゲーム開始前、終了後
	{
		if ($isold) {
			$db->query("insert into talk{$isold}(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'$day_night','$uname','$time','$say','$font_type','0','$sqlheaven')");
			$db->query("update room{$isold} set last_updated = '$time',uptime = '".time()."' where room_no = '$room_no'");
		} else {
			$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'$day_night','$uname','$time','$say','$font_type','0','$sqlheaven')");
			$db->query("update room set last_updated = '$time',uptime = '".time()."' where room_no = '$room_no'");
		}
	}
	elseif(strstr((string) $role, 'GM')) // GM 
	{

		if ((strstr((string) $font_type, 'wolf') || strstr((string) $font_type, 'common') || strstr((string) $font_type, 'lovers') || strstr((string) $font_type, 'fox')) && ($day_night == 'night')) {
      $locstr = 'night ' . $font_type;
      $font_type = 'normal';
      $db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
					values ($room_no,$date,'$locstr','$uname','$time','$say','$font_type','$spend_time','$sqlheaven')");
  } elseif ($font_type == 'gm_to' && ($day_night == 'night')) {
      // whisper
      $db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,gm_to,heaven)
						values ($room_no,$date,'$day_night gm_to:;:$gm_target','$uname','$time','$say','$font_type','0','1','$sqlheaven')");
  } else { // Broadcast
			//$say = str_replace("\n","\n　　　　　",$say);
			$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'$day_night gm_bc $font_type','$uname','$time','$say','$font_type','0','$sqlheaven')");
		}
		//更新時間
		$db->query("update room set uptime = '".time()."' where room_no = '$room_no'");
	}
	elseif( /*($live == 'live') && */($day_night == 'night') && ($font_type == 'to_gm') && ($sum_spend_time < $night_limit_time) &&
																			($pass_real_time < $night_real_limit_time) ) // whisper to GM 
	{
		$loc = $live == 'dead' ? '$heaven' : '';
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven,to_gm)
						values ($room_no,$date,'$day_night::to_gm $loc','$uname','$time','$say','$font_type','0','$sqlheaven','1')");

		$db->query("update room set uptime = '".time()."' where room_no = '$room_no'");
	}
	elseif( ($live == 'live') && ($day_night == 'day') && ($sum_spend_time < $day_limit_time) &&
																			($pass_real_time < $day_real_limit_time && $pass_real_time > 10) ) //お白
	{
		// TODO: 白天15秒不可發言修改處
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'day','$uname','$time','$say','$font_type','$spend_time','$sqlheaven')");

		$db->query("update room set last_updated = '$time',uptime = '".time()."' where room_no = '$room_no'");
	}
	elseif( ($live == 'live') && ($day_night == 'night') && strstr((string) $role,"wolf") && ($sum_spend_time < $night_limit_time) &&
																			($pass_real_time < $night_real_limit_time) ) //夜の狼
	{
		if (strstr((string) $role, 'wfasm')) {
      $font_type = 'strong';
  } elseif (strstr((string) $role, 'wfwtr')) {
      $font_type = 'weak';
  }

		if (chdis('wolf')) {
      if(strstr((string) $role, "lovers")) {
   				$locstr = chdis('lovers') ? 'night self_talk' : 'night lovers';
   			} else
   				$locstr = 'night self_talk';
  } elseif (strstr((string) $role, "lovers")) {
      $locstr = chdis('lovers') ? 'night wolf' : 'night wolf lovers';
  } else
				$locstr = 'night wolf';

		$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'$locstr','$uname','$time','$say','$font_type','$spend_time','$sqlheaven')");

		$db->query("update room set last_updated = '$time',uptime = '".time()."' where room_no = '$room_no'");
	}
	elseif( ($live == 'live') && ($day_night == 'night') && strstr((string) $role,"common") && ($sum_spend_time < $night_limit_time) &&
																		($pass_real_time < $night_real_limit_time) ) //夜の共有者
	{
		if (chdis('common')) {
      if(strstr((string) $role, "lovers")) {
   				$locstr = chdis('lovers') ? 'night self_talk' : 'night lovers';
   			} else
   				$locstr = 'night self_talk';
  } elseif (strstr((string) $role, "lovers")) {
      $locstr = chdis('lovers') ? 'night common' : 'night common lovers';
  } else
				$locstr = 'night common';

		$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'$locstr','$uname','$time','$say','$font_type','0','$sqlheaven')");
		//更新時間
	//	$db->query("update room set uptime = '".time()."' where room_no = '$room_no'");
	}
	elseif( ($live == 'live') && ($day_night == 'night') && strstr((string) $role,"fox") && ($sum_spend_time < $night_limit_time) &&
																		($pass_real_time < $night_real_limit_time) ) //晚上狐說話
	{
		if (chdis('fox')) {
      if(strstr((string) $role, "lovers")) {
   				$locstr = chdis('lovers') ? 'night self_talk' : 'night lovers';
   			} else
   				$locstr = 'night self_talk';
  } elseif (strstr((string) $role, "lovers")) {
      $locstr = chdis('lovers') ? 'night fox' : 'night fox lovers';
  } else
				$locstr = 'night fox';

		$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
					values ($room_no,$date,'$locstr','$uname','$time','$say','$font_type','0','$sqlheaven')");
	}
	elseif( ($live == 'live') && ($day_night == 'night') && strstr((string) $role,"lovers") && ($sum_spend_time < $night_limit_time) &&
																		($pass_real_time < $night_real_limit_time) ) //夜の恋人
	{
		$locstr = chdis('lovers') ? 'night self_talk' : 'night lovers';

		$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'$locstr','$uname','$time','$say','$font_type','0','$sqlheaven')");
		//更新時間
	//	$db->query("update room set uptime = '".time()."' where room_no = '$room_no'");
	}
	elseif( ($live == 'live') && ($day_night == 'night') && ($sum_spend_time < $night_limit_time) &&
														($pass_real_time < $night_real_limit_time) ) //夜の狼、共有者以外は独り言
	{
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'night self_talk','$uname','$time','$say','$font_type','0','$sqlheaven')");
		//更新時間
//		$db->query("update room set uptime = '".time()."' where room_no = '$room_no'");
	}
	elseif($live == 'dead') //死亡者の靈話
	{
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,font_type,spend_time,heaven)
						values ($room_no,$date,'heaven','$uname','$time','$say','$font_type','0','1')");
		//更新時間
		$db->query("update room set uptime = '".time()."' where room_no = '$room_no'");
	}
//	$db->query("commit"); //一応コミット
}

//----------------------------------------------------------
//ゲーム停滞のチェック

function SilenceCheck(): void
{
	global $room_no,$date,$game_option,$day_night,$silence_threshhold_time,$silence_pass_time,$suddendeath_threshhold_time,
			$day_limit_time,$night_limit_time,$time_zone,$revote_mp3,$dead_mode,$play_sound,$db,$isold;
	
	global $msg_ampm_image, $msg_guard_image, $msg_vote_image, $msg_kill_image, $msg_sys_image, $msg_mage_image, 
			$msg_room_image, $msg_wolf_image, $msg_human_image, $msg_fox_image, $msg_fosi_image, $msg_cat_image, 
			$msg_sudden_image, $msg_gm_image, $msg_rm_image, $msg_lover_image, $msg_mad_image;
	
	$dead_modes = empty($dead_modes) ? "" : $dead_modes;
	
//	if (($day_night == 'day' || $day_night == 'night')
//					&& $db->query("lock tables room WRITE, talk WRITE, vote WRITE, user_entry WRITE, system_message WRITE"))
	if (($day_night == 'day' || $day_night == 'night'))
	{
		$time = time();  //現在時刻、GMTとの時差を足す

		//最後に発言された時間を取得
		$res_last_updated = $db->query("select last_updated from room where room_no = '$room_no'");
		$last_updated_time = $db->result($res_last_updated,0,0);

		//経過時間を取得
		if( strstr((string) $game_option,"real_time") ) //限制時間
		{
			//實際時間的制限時間を取得
			$real_time_str = strstr((string) $game_option,"real_time");
			sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_minutes,$night_real_limit_minutes);
			$day_real_limit_time = $day_real_limit_minutes * 60; //秒になおす
			$night_real_limit_time = $night_real_limit_minutes * 60; //秒になおす

			//最も小さな時間(場面の最初的時間)を取得
			$res_start_real_time = $db->query("select min(time) from talk where room_no = '$room_no' and date = $date
																								and location like '$day_night%'");
			$start_real_time = $db->result($res_start_real_time,0,0);

			if($start_real_time != NULL)
			{
				$pass_real_time = $time - $start_real_time; //経過した時間
			}
			else
				$pass_real_time = 0;

			$sum_spend_time = 0; //会話で時間経過制の方は無効にする
		}
		else //会話で時間経過制
		{
			$res_spend_time = $db->query("select sum(spend_time) from talk where room_no = '$room_no' and date = $date
																								and location like '$day_night%'");
			$sum_spend_time = (int)$db->result($res_spend_time,0,0);

			$pass_real_time = 0; //限制時間的経過時間を無効にする
			$day_real_limit_time = 999; //適当に大きな数字を入れる
			$night_real_limit_time = 999; //適当に大きな数字を入れる
		}

		$last_update_diff_sec = $time - $last_updated_time;

		//echo "$pass_real_time <br />";
		//echo "$day_real_limit_time";

		//限制時間でなく、制限時間内で沈黙閾値を超えたならなら一時間進める(沈黙)
		if(  !strstr((string) $game_option,"real_time") && ( (($day_night == 'day') && ($sum_spend_time < $day_limit_time))
													|| (($day_night == 'night') && ($sum_spend_time < $night_limit_time)) ) )
		{
			if($last_update_diff_sec > $silence_threshhold_time)
			{
				if($day_night == 'day')
				{
					//沈黙的時間
					$silence_pass_minuts = floor(12*60/ $day_limit_time * $silence_pass_time); //沈黙の分(60分以上含)
					$silence_pass_hour = floor($silence_pass_minuts / 60); //沈黙的時間
					$silence_pass_minuts %= 60; //沈黙の分
				}
				else
				{

					$silence_pass_minuts = floor(6*60/ $night_limit_time * $silence_pass_time); //沈黙の分(60分以上含)
					$silence_pass_hour = floor($silence_pass_minuts / 60); //沈黙的時間
					$silence_pass_minuts %= 60; //沈黙の分
				}


				if($silence_pass_hour != 0)
					$silence_pass_hour_str = $silence_pass_hour . "時間";
				if($silence_pass_minuts !== 0)
					$silence_pass_minuts_str = $silence_pass_minuts . "分";

				$silence_message = "・・・・・・・・・・ 沉默持續了 $silence_pass_hour_str $silence_pass_minuts_str";

				if ($db->begin_transaction()) {
					$all_query_ok = true;
					$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
									values($room_no,$date,'$day_night system','system',$time,'$silence_message','$silence_pass_time')") ? null : $all_query_ok = false;

					$db->query("update room set last_updated = '$time' where room_no = '$room_no'") ? null : $all_query_ok = false;
					if ($all_query_ok) {
						$db->commit();
					} else {
						$db->rollback();
					}
				}
			}
		}
		elseif(  (($day_night == 'day') &&
									( ($sum_spend_time >= $day_limit_time) || ($pass_real_time >= $day_real_limit_time) ) )
			|| (($day_night == 'night') &&
									( ($sum_spend_time >= $night_limit_time) || ($pass_real_time >= $night_real_limit_time) ) )  )
		{
			//警告を出す
			$left_minuts = floor($suddendeath_threshhold_time / 60); //突然死までの制限時間(分)
			$left_seconds = $suddendeath_threshhold_time % 60; //突然死までの制限時間(秒)

			$left_time_str = $left_seconds == 0 ? $left_minuts . "分" : $left_minuts . "分" . $left_seconds . "秒";

			$suddendeath_alert_str = msgimg($msg_sys_image)."<font color=\"red\">最後" . $left_time_str . "還不投票將會暴斃</font>";

			$all_query_ok = true;
			if ($db->begin_transaction()) {
				//既に警告を出しているかチェック
				$res_alert = $db->query("select count(uname) from talk where room_no = '$room_no' and date = $date
							and location = '$day_night system' and uname = 'system' and sentence = '$suddendeath_alert_str'");

				$time++; //全会話の後に出るように
				if($db->result($res_alert,0) == 0) //警告を出していなかったら出す
				{
					$db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
									values($room_no,$date,'$day_night system','system','$time','$suddendeath_alert_str','0')") ? null : $all_query_ok = false;

					$db->query("update room set last_updated = '$time' where room_no = '$room_no'") ? null : $all_query_ok = false; //更新時間を更新

					$last_update_diff_sec = 0;
				}
				//音を鳴らす
				if ($dead_mode != 'on' && $play_sound == 'on') {
				//	echo "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,0,0\" width=1 height=1>\r\n".
				//		"<param name=movie value=\"$revote_swf\">\r\n".
				//		"<param name=quality value=high>\r\n".
				//		"<embed src=\"$revote_swf\" quality=high width=1 height=1 type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\">\r\n".
				//		"</embed>\r\n".
				//		"</object>$dead_modes\r\n";
					echo "<audio src=\"$revote_mp3\" autoplay>HTML5 audio not supported</audio>$dead_modes\r\n";
				}

				//制限時間を過ぎていたら未投票の人を突然死させる
				if($last_update_diff_sec > $suddendeath_threshhold_time)
				{
					if($day_night == 'day')
					{

						//何回目の投票なのか取得(再投票)
						$res_vote_times = $db->query("select message from system_message where room_no = '$room_no'
													 and date = $date and type = 'VOTE_TIMES'");
						$vote_times = $db->result($res_vote_times,0,0);
						/*
						//投票過了の人のテンポラリテーブルを建立
						$db->query("create temporary table tmp_sd select uname from vote
																where room_no = '$room_no' and date = $date
																	and situation = 'VOTE_KILL' and vote_times = $vote_times");
						//投票していない人を取得(投票過了の人を左結合して、「投票過了=NULL・投票していない」を取得)
						$res_novote = $db->query("select user_entry.uname,user_entry.handle_name
															from user_entry left join tmp_sd on user_entry.uname = tmp_sd.uname
														where user_entry.room_no = '$room_no' and user_entry.live = 'live'
													 						and user_entry.user_no > 0 and tmp_sd.uname is NULL");
						*/
						$res_novote = $db->query("select user_entry.user_no,user_entry.uname,user_entry.handle_name,user_entry.role,user_entry.last_words from user_entry
								 inner join system_message on system_message.room_no = user_entry.room_no
								 and system_message.date = '$date' and system_message.type = 'VOTE_TIMES'
								 left join vote on vote.room_no = user_entry.room_no and vote.uname = user_entry.uname
								 and vote.date = system_message.date and vote.vote_times = system_message.message
								 where user_entry.room_no = '$room_no' and user_entry.live = 'live' and user_entry.user_no > 0 and user_entry.role <> 'GM' and vote.situation IS NULL");

					}
					elseif($day_night == 'night')
					{
						/*
						//何回目の投票なのか取得(再投票)
						//投票過了の人のテンポラリテーブルを建立
						$db->query("create temporary table tmp_sd select uname from vote
																where room_no = '$room_no' and date = $date
											and (situation = 'VOTE_KILL' or situation = 'MAGE_DO' or situation = 'GUARD_DO') ");
						//投票していない人を取得
						$res_novote = $db->query("select user_entry.uname,user_entry.handle_name
															from user_entry left join tmp_sd on user_entry.uname = tmp_sd.uname
														where user_entry.room_no = '$room_no' and user_entry.live = 'live'
							and (user_entry.role like 'wolf%' or user_entry.role like 'mage%' or user_entry.role like 'guard%')
																 			and user_entry.user_no > 0 and tmp_sd.uname is NULL");
						*/
						if ($date > 1) {
          $res_novote = $db->query("select user_entry.user_no,user_entry.uname,user_entry.handle_name,user_entry.role from user_entry
									 left join vote on vote.room_no = user_entry.room_no and vote.uname = user_entry.uname and vote.date = '$date'
									 where user_entry.room_no = '$room_no' and user_entry.live = 'live' and user_entry.user_no > 0 and vote.situation IS NULL
									 and (user_entry.role like 'wolf%' or user_entry.role like 'mage%' or user_entry.role like 'guard%' or user_entry.role like 'fosi%'
										  or user_entry.role like 'cat%' or user_entry.role like 'owlman%')");
      } elseif ($date == 2) {
          $res_novote = $db->query("select user_entry.user_no,user_entry.uname,user_entry.handle_name,user_entry.role from user_entry
									left join vote on vote.room_no = user_entry.room_no and vote.uname = user_entry.uname and vote.date = '$date'
									where user_entry.room_no = '$room_no' and user_entry.live = 'live' and user_entry.user_no > 0 and vote.situation IS NULL
									and (user_entry.role like 'wolf%' or user_entry.role like 'mage%' or user_entry.role like 'guard%' or user_entry.role like 'fosi%'
										 or user_entry.role like 'cat%' or user_entry.role like 'owlman%' or user_entry.role like 'mytho%')");
      } else {
							$res_novote = $db->query("select user_entry.user_no,user_entry.uname,user_entry.handle_name,user_entry.role from user_entry
									left join vote on vote.room_no = user_entry.room_no and vote.uname = user_entry.uname and vote.date = '$date'
									where user_entry.room_no = '$room_no' and user_entry.live = 'live' and user_entry.user_no > 0 and vote.situation IS NULL
									and (user_entry.role like 'wolf%' or user_entry.role like 'mage%' or user_entry.role like 'fosi%')");
						}


						$res_wolfvote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = '$date' and situation = 'WOLF_EAT';");
						$res_wolfvotes = $db->result($res_wolfvote,0);
					}

					//未投票者の数
				//	$novote_count = $db->fetch_row($res_novote);

					//未投票者を全員突然死させる
					$voteii = 0;
					$executed_night_sd = 0;
					while($novote_arr = $db->fetch_assoc($res_novote)) {
						if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
							$user_no2 = str_pad((string) $novote_arr['user_no'],2,"0",STR_PAD_LEFT);
							$novote_arr['handle_name'] = "玩家".$user_no2."號";
						}
						$novote_this_uname = $novote_arr['uname'];
						$novote_this_handle_name = $novote_arr['handle_name'];
						$novote_this_role = $novote_arr['role'];
						$res2_votedel = 0;

						if ($day_night == 'day' && strstr((string) $game_option,"votedme")) {
							$res2_voteis = $db->query("select count(id) from system_message where room_no = '$room_no'
													 and message = '$novote_this_uname' and type = 'VOTE_NO';");
							$res2_votedel = $db->result($res2_voteis,0);
						}

						if ($day_night == 'day' && $res2_votedel < 1 && strstr((string) $game_option,"votedme")) {
							$db->query("insert into vote (room_no,date,uname,target_uname,vote_number,vote_times,situation)
										values($room_no,$date,'$novote_this_uname','$novote_this_uname',1,'$vote_times','VOTE_KILL')") ? null : $all_query_ok = false;
							$db->query("insert into system_message (room_no,message,type,date)
										values ($room_no,'$novote_this_uname','VOTE_NO',$date)") ? null : $all_query_ok = false;
							$voteii++;
						} else {
							//突然死実行
							$time = time(); //現在時刻、GMTとの時差を足す
							if (strstr((string) $novote_this_role,"wolf") && $day_night == 'night') {
           if ($res_wolfvotes == '0') {
   									$res1 = $db->query("update user_entry set live = 'dead',death = '2' 
														where room_no = '$room_no' and uname = '$novote_this_uname'
														and user_no > '0'") ? null : $all_query_ok = false;

   									$suddendeath_message_str = msgimg($msg_sudden_image)."$novote_this_handle_name (人狼)突然暴斃死亡";
   									$db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
												values($room_no,$date,'$day_night system','system','$time','$suddendeath_message_str','0')") ? null : $all_query_ok = false;

   									++$executed_night_sd; // 狼暴斃
   								}
       } elseif ($day_night == 'day' && strstr((string) $game_option,"votedme")) {
           $res1 = $db->query("update user_entry set live = 'dead',death = '1' 
													   where room_no = '$room_no' and uname = '$novote_this_uname'
													   and user_no > '0'") ? null : $all_query_ok = false;
           $db->query("insert into system_message (room_no,message,type,date)
												values ($room_no,'$novote_this_handle_name','VOTE_KILLME',$date)") ? null : $all_query_ok = false;
           $suddendeath_message_str = msgimg($msg_sudden_image)."$novote_this_handle_name 突然暴斃死亡(自投)";
           $db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
												values($room_no,$date,'$day_night system','system','$time','$suddendeath_message_str','0')") ? null : $all_query_ok = false;
       } else {
									if($day_night == 'night') { 
										++$executed_night_sd;
										$res1 = $db->query("update user_entry set live = 'dead',death = '2' 
														   where room_no = '$room_no' and uname = '$novote_this_uname'
														   and user_no > '0'") ? null : $all_query_ok = false;
									} else {
										$res1 = $db->query("update user_entry set live = 'dead',death = '1' 
														   where room_no = '$room_no' and uname = '$novote_this_uname'
														   and user_no > '0'") ? null : $all_query_ok = false;
									}

									$suddendeath_message_str = msgimg($msg_sudden_image)."$novote_this_handle_name 突然暴斃死亡";
									$db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
												values($room_no,$date,'$day_night system','system','$time','$suddendeath_message_str','0')") ? null : $all_query_ok = false;
								}
							if ($novote_arr['last_words']) {
								$last_words_str = $novote_this_handle_name."\t".$novote_arr['last_words'];

								$db->query("insert into system_message (room_no,message,type,date)
											values ($room_no,'".addslashes($last_words_str)."','LAST_WORDS',$date)") ? null : $all_query_ok = false;
							}
							if ($day_night == 'day') {
								if (strstr((string) $novote_this_role,"wfbig")) {
									$novote_this_role2 = 'wfbig';
								} elseif(strstr((string) $novote_this_role,"fosi")) {
									$novote_this_role2 = 'fosi';
								} elseif(strstr((string) $novote_this_role,"wolf")) {
									$novote_this_role2 = 'wolf';
								} else {
									$novote_this_role2 = 'human';
								}
								$necromancer_message_str = $novote_this_handle_name."\t".$novote_this_role2;
								$db->query("insert into system_message (room_no,message,type,date)
											values ($room_no,'$necromancer_message_str','NECROMANCER_RESULT',$date)") ? null : $all_query_ok = false;
							}
						}
					}

					if ($day_night == 'day' && $voteii && strstr((string) $game_option,"votedme")) 
					{
						$aaaaa = 'VOTE_KILL';
						$bbbbb = $vote_times;
						require_once __DIR__ . '/game_vote.php';
					} 
					elseif($day_night == 'night' && $executed_night_sd > 0) 
					{
						// 夜間暴斃發生，直接廢村
						CheckVictory('night');
					} 
					else
					{	
						//	$db->free_result($res_novote);

						//制限時間リセット
						$db->query("update room set last_updated = '$time' where room_no = '$room_no'") ? null : $all_query_ok = false;
						//投票リセット
						$db->query("delete from vote where room_no = '$room_no'") ? null : $all_query_ok = false;

						$time = time() + 1;
						$suddendeath_revote_str = msgimg($msg_sys_image).'<font color="red">＜投票結果有問題 請重新投票＞</font>';
						$db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
									values ($room_no,$date,'$day_night system','system','$time','$suddendeath_revote_str','0')") ? null : $all_query_ok = false;

						$suddendeath_alert_str = msgimg($msg_sys_image).'<font color="red">最後' . $left_time_str . "還不投票將會暴斃</font>";
						$db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
									values ($room_no,$date,'$day_night system','system','$time','$suddendeath_alert_str','0')") ? null : $all_query_ok = false;

						//	$db->query("DROP TABLE tmp_sd;");
						//查詢快取
						//	roomsysnew("user");

						//勝敗が決まったかチェック
						CheckVictory();
					}
				}

				if ($all_query_ok) {
					$db->commit();
				} else {
					$db->rollback();
				}
			}

		}

	//	$db->query("unlock tables"); //テーブルロック解除
	}
	
}


//----------------------------------------------------------
//必要なクッキーをまとめて登錄(ついでに最新の異議ありの状態を取得して配列に格納)
function SendCookie(): void
{
	global $room_no,$date,$day_night,$user_no,$live,$uname,$set_objection,$maxcount_objection,$objection_arr,$objection_left_count,$time_zone,$db,$isold;
	
	//<夜明けを音效通知用>--------------------------------------------------------------------------------
	//クッキーに格納（夜明けに音效通知で使う・有効期限一時間）
	setcookie("day_night", (string) $day_night,['expires' => time()+3600]); 
	
	//<異議ありを音效通知用>--------------------------------------------------------------------------------
	//今までに自分が異議ありをした回数取得
	if ($set_objection == 'set') {
		$res_my_objection = $db->query("select count(message) from system_message 
												where room_no = '$room_no' and type = 'OBJECTION' and message = '$user_no'");
		$cobjection = $db->result($res_my_objection,0);
	} else {
		$cobjection = 0;
	}
	
	//抗議廢村
	if ($set_objection == 'roomend' && $live == 'live' && $day_night != 'night') {
		$time = time();  //現在時刻、GMTとの時差を足す
		$db->query("insert into system_message(room_no,message,type,date) values($room_no,'$user_no','ROOMEND',$date)");
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
										values($room_no,$date,'$day_night system','$uname',$time,'ROOMEND','0')");
		
		$query = $db->query("select count(distinct(message)) from system_message WHERE
								 room_no = '$room_no' and date = '$date' and type = 'ROOMEND';");
		$rendcount = $db->result($query, 0);
		if ($rendcount >= 2) {
			$query = $db->query("select count(uname) from user_entry where room_no = '$room_no' and live = 'live'
								 and user_no > '0'");
			$livecount = floor($db->result($query,0) / 2);
			if ($rendcount > $livecount) {
				$db->query("UPDATE room SET status = 'finished',day_night = 'aftergame' where room_no = '$room_no';");
				$db->query("DELETE from vote where room_no = '$room_no';");
			}
		}
	}
	
	//生きていて(ゲーム終了後は死者でもOK)異議あり、のセット要求があればセットする(最大回数以内の場合)
	if( ($live == 'live') && ($day_night != 'night') && ($set_objection == 'set') && ($cobjection < $maxcount_objection) )
	{
		$time = time();  //現在時刻、GMTとの時差を足す
		$db->query("insert into system_message(room_no,message,type,date) values($room_no,'$user_no','OBJECTION',$date)");
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
										values($room_no,$date,'$day_night system','$uname',$time,'OBJECTION','0')");
	//	$db->query("commit");
	}
	
	//異議あり、のクッキーを構築する user_no 1～31まで
	$objection_arr = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]; //クッキーに送信するデータの建立
	//message:異議ありをした用戶No とその回数を取得
	$res_objection = $db->query("select message,count(message) as message_count from system_message
																			where room_no = '$room_no'
																					and type = 'OBJECTION' group by message");
	
//	$objection_res_count = $db->fetch_row($res_objection);
	while($objection_res_arr = $db->fetch_array($res_objection))
	{
	//	$objection_res_arr = $db->fetch_array($res_objection);
		$objection_user_no = (int)$objection_res_arr['message'];
		$objection_user_count = (int)$objection_res_arr['message_count'];
		$objection_arr[$objection_user_no -1] = $objection_user_count;
		
	}
	$db->free_result($res_objection);
	
	//クッキーに格納（有効期限一時間）
	$setcookie_objection_str = '';
	for($i=0;$i<31;$i++)
		$setcookie_objection_str .= $objection_arr[$i] . ","; //カンマ区切り
	
	setcookie("objection", $setcookie_objection_str,['expires' => time()+3600]); 
	
	//残り異議ありの回数
	$objection_left_count = $maxcount_objection - $objection_arr[$user_no -1];
	
	
	
	//<再投票を音效通知用>--------------------------------------------------------------------------------
	//再投票の回数を取得
	$res_revote = $db->query("select message from system_message where room_no = '$room_no' and date = $date and type = 'RE_VOTE'
																										order by message DESC");
	
	if($db->num_rows($res_revote) != 0)
	{
		//何回目の再投票なのか取得
		$last_vote_times = (int)$db->result($res_revote,0,0);
		
		//クッキーに格納（有効期限一時間）
		setcookie("vote_times", $last_vote_times,['expires' => time()+3600]); 
	}
	else
	{
		//クッキーから削除（有効期限一時間）
		setcookie("vote_times","",['expires' => time()-3600]); 
	}
	
}


//----------------------------------------------------------
//天国の靈話ログ出力
function HeavenTalkLogOutput(): void
{
	global $room_no,$role,$uname,$live,$date,$day_night,$heaven_mode,$game_update,$cachefile,$db,$isold,$handle_name,$dummy_boy_imgid,$dummy_boy_imgid,$time_zone;
	
	if($live != 'dead' && $live != 'gone')
		return;

	if ($cachefile) {
		//檔案名稱
		$file = "messH_$room_no.php";
		$filetime = filemtime("tmp/".$file);
	} else {
		$filetime = '-1';
	}

	if ($filetime != $game_update) {
		//会話の用戶名、ハンドル名、発言、発言のタイプを取得
		$result = $db->query("select u.uname as talk_uname,
							u.handle_name as talk_handle_name,
							u.live as talk_live,
							u.sex as talk_sex,
							i.color as talk_color,u.icon_no as iconno,
							ta.sentence as sentence,
							ta.font_type as font_type,
							ta.location as location,tr.icon as tcolor,ta.time as ttime 
							from ((select * from user_entry where room_no = '$room_no' or room_no = '0') as u,talk ta,user_icon i)
							left join user_trip tr on tr.trip = u.trip
							where ta.room_no = '$room_no' and tr.trip = u.trip
							and ( ( u.room_no = '$room_no' and u.uname = ta.uname
							and u.icon_no = i.icon_no and ta.heaven = '1')
							or ( u.uid = '1' and ta.uname = 'system'
							and u.icon_no = i.icon_no and ta.heaven = '1') 
							or ( u.room_no = '$room_no' and u.uname = ta.uname and ta.uname = '$uname'
							and u.icon_no = i.icon_no and ta.to_gm = '1')
							or ( u.room_no = '$room_no' and u.uname = ta.uname
							and u.icon_no = i.icon_no and ta.location like '%gm_to:;:$handle_name')) order by ta.tid DESC LIMIT 500");
		
		//room_no location uname sentence font_type
//		$talk_count = $db->fetch_row($result);
		
		$message = "<table border=0 cellpadding=0 cellspacing=0 style=\"font-size:12pt;table-layout:fixed;width: 100%;\">";
		
		//出力
		while($talk_log_array = $db->fetch_array($result))
		{
		//	$talk_log_array = $db->fetch_array($result);
			$talk_uname = $talk_log_array['talk_uname']; //帳號
			$talk_handle_name = $talk_log_array['talk_handle_name']; //暱稱
			$talk_live = $talk_log_array['talk_live']; //是否活著
			$talk_sex = $talk_log_array['talk_sex']; //性
			$talk_color = $talk_log_array['talk_color']; //ICON顏色
			$sentence = $talk_log_array['sentence']; //發言內容
			$font_type = $talk_log_array['font_type'];
			$location = $talk_log_array['location'];
			$ttime = "".gmdate("H:i:s",$talk_log_array['ttime'] + $time_zone);
			
			if ($talk_log_array['tcolor'] && $talk_log_array['iconno'] == $dummy_boy_imgid) {
				$talk_color = $talk_log_array['tcolor'];
			}
			
			//表情
			$sentence = messemot($sentence);
			
			$sentence = str_replace("\n","<br />",(string) $sentence); //改行を<br />タグに置換
			
			$font_type2 = "";
			if (strstr((string) $font_type, 'type_del')) {
				$font_type2 = "text-decoration:line-through;";
			}
			if (strstr((string) $font_type, 'type_b')) {
				$font_type2 .= "font-weight:bolder;";
			}
			if (!strstr((string) $font_type, 'type_b')) {
				if (strstr((string) $font_type, 'strong')) {
					$font_type2 .= "font-weight:bold;";
				}elseif (strstr((string) $font_type, 'weak')) {
					$font_type2 .= "font-weight:lighter;";
				}
			}
			
			if( strstr((string) $font_type, 'normal') ) //文字の大きさ
				$font_type_str = "<span style=\"font-size:12pt;$font_type2\">";
			elseif( strstr((string) $font_type, 'strong') )
				$font_type_str = "<span style=\"font-size:20pt;$font_type2\">";
			elseif( strstr((string) $font_type, 'weak') )
				$font_type_str = "<span style=\"font-size:8pt;$font_type2\">";
			elseif ( strstr((string) $font_type, 'heaven') )
				$font_type_str = "<span style=\"font-size:12pt;color:#d00000;$font_type2\">";
			
			$message .= "<tr>\r\n";
			
			//会話出力

			if(strstr((string) $location, 'gm_bc')) {
				$talk_dead_name = $talk_handle_name ;//. "<small>(" . $talk_uname . ")</small>";

				//$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>";
				//$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"> </td>";
				$message .= "<td class=\"talktabletd\" style=\"width:150px;\">";
				$message .= "<font color=$talk_color>◆</font><font color=red> $talk_dead_name </font></td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str <u><font color=red> $sentence </font></u></span>";
				$message .= "</td>\r\n";
				
				$message .= "</tr>\r\n";
			} elseif(strstr((string) $location, 'to_gm')) {
				$talk_dead_name = $talk_handle_name . " → GM ";//<small>(" . $talk_uname . ")</small>";

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"> </td>";
				$message .= "<td class=\"talktabletd\" style=\"width:150px;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_dead_name </td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				
				$message .= "</tr>\r\n";
			} elseif(strstr((string) $location, 'gm_to')) {
				$talk_dead_name = "GM → " . $talk_handle_name ;//. "<small>(" . $talk_uname . ")</small>";

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"> </td>";
				$message .= "<td class=\"talktabletd\" style=\"width:150px;\">";
				$message .= "<font color=$talk_color>◆</font><font color=red> $talk_dead_name </font></td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str <u><font color=red> $sentence </font></u></span>";
				$message .= "</td>\r\n";
				
				$message .= "</tr>\r\n";
			} else {
				$talk_dead_name = $talk_handle_name ;//. "<small>(" . $talk_uname . ")</small>";

				$message .= "<td class=\"talktabletd\" style=\"width:68px;\"><span style=\"text-align: right;\"><small>(".$ttime.")</small></span></td>";
				$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				//$message .= "<td class=\"talktabletd\" style=\"width:130px;\">";
				$message .= "<font color=$talk_color>◆</font>$talk_dead_name </td>";
				$message .= "<td class=\"talktabletd\" style=\"width:100%;padding:0 0 0 14px;\">";
				$message .= "$font_type_str $sentence </span>";
				$message .= "</td>\r\n";
				
				$message .= "</tr>\r\n";
			}
		}
		$message .= "</table>";
		echo $message;
		$db->free_result($result);
		
		//更新快取
		if ($cachefile) {
			roomsysnew($file,$message);
		}
	} else {
		include(__DIR__ . "/tmp/".$file);
	//	echo $message;
	}

}


//----------------------------------------------------------
//村名前、番地、何日目、日没まで～時間を出力(勝敗がついたら村的名稱と番地、勝敗を出力)
function GameHeaderOutput(): void
{
	global $room_no,$date,$day_night,$room_name,$room_comment,$game_option,$handle_name,$live,$role,$sex,$dead_mode,$heaven_mode,
					$list_down,$auto_reload,$play_sound,$cookie_day_night,$cookie_objection,$objection_arr,$objection_left_count,
					$morning_mp3,$maxcount_objection,$objection_image,$objection_male_mp3,$objection_female_mp3,$day_limit_time,
					$night_limit_time,$time_zone,$uname,$db,$room_status,$endroom_image,$trip,$max_user,$voted_list,$timeoutdplay,$showtrip;
	
	
	$room_message = "<strong style=\"font-size:15pt;\">" . $room_name ."村</strong>　～" . $room_comment ."～[" . $room_no . "番地]";

	if ($uname == 'dummy_boy' || (strstr((string) $game_option, 'gm:'.$trip) && $trip != '')) {
			$room_message .= " [<a href=\"game_play.php?go=del&id=$room_no&room_no=$room_no\" target=\"_top\" onClick=\"return(confirm('真的要進行廢村嗎？'))\">廢</a>]";
	}
	if ($room_status == "waiting" && $uname != 'dummy_boy') {
		$room_message .= " [<a href=\"game_play.php?go=out&room_no=$room_no\" target=\"_top\" onClick=\"return(confirm('真的要自刪嗎？'))\">自刪</a>]";
	} else {
		$room_message .= " [<a href=\"game_play.php?go=out&room_no=$room_no\" target=\"_top\">登出</a>]";
	}
	$room_message .= "<br />";
	
	echo "<table border=0 cellspacing=0 cellpadding=0 width=100%><tr>\r\n";
	
	if( ( (($live == 'dead' || $live == 'gone') || strstr((string) $role, 'GM')) && ($heaven_mode == 'on') ) || ($day_night == 'aftergame') ) //靈界とログ閲覧時
	{
		
		if( (($live == 'dead' || $live == 'gone') || strstr((string) $role, 'GM')) && ($heaven_mode == 'on') )
			echo "<td><<<幽靈的地方>>></td>";
		else
			echo "<td><span style=\"text-decoration:underline;\">$room_message</span></td>\r\n";
		
		
		echo "<td align=right><small>過去\r\n"; //過去の日のログ
		echo "<a href=game_log.php?room_no=$room_no&log_mode=on&date=0&day_night=beforegame#game_top target=\blank>開始前</a>\r\n";
		echo "<a href=game_log.php?room_no=$room_no&log_mode=on&date=1&day_night=night#game_top target=\blank>1(夜)</a>\r\n";
		for($i=2 ; $i < $date ; $i++)
		{
			echo "<a href=game_log.php?room_no=$room_no&log_mode=on&date=$i&day_night=day#game_top target=_blank>$i(白)</a>\r\n";
			echo "<a href=game_log.php?room_no=$room_no&log_mode=on&date=$i&day_night=night#game_top target=_blank>$i(夜)</a>\r\n";
		}
		if( ($heaven_mode == 'on') && ($day_night == 'night') )
			echo "<a href=game_log.php?room_no=$room_no&log_mode=on&date=$date&day_night=day#game_top target=_blank>$date(白)</a>\r\n";
		elseif( $day_night == 'aftergame' )
		{
			$res_last_day_night = $db->query("select count(uname) from talk where room_no = '$room_no' and date = $date
																									and location = 'day'");
			if($db->num_rows($res_last_day_night) > 0)
				echo "<a href=game_log.php?room_no=$room_no&log_mode=on&date=$date&day_night=day#game_top target=_blank>$date(白)</a>\r\n";
		}
		
		
		
		if($heaven_mode == 'on')
		{
			echo "</small></td></tr></table>";
			return;
		}
	}
	elseif( (($live == 'dead' || $live == 'gone') || strstr((string) $role, 'GM')) && ( $dead_mode == 'on') ) //死亡者の場合の、真ん中の全表示地上モード
	{
		echo "<td><span style=\"text-decoration:underline;\">$room_message</span></td>\r\n";
		echo "<td align=right width=400>";
		echo "<table border=0 cellpadding=0 cellspacing=0><tr>";
		echo "<td><form name=middle_reloadform action=\"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=on&showtrip=$showtrip&list_down=$list_down#game_top\" target=middle method=POST>";
//		echo "<input type=submit value=\"手動更新\" style=\"border-width:2px;border-style:dotted;background-color:#eeccaa;color:#774400;\">";
	}
	else //生存中
	{
		echo "<td><span style=\"text-decoration:underline;\">$room_message</span>";
		echo "</td>\r\n";
		echo "<td align=right>";
	}
	
	echo "<small>";
	if( $day_night != 'aftergame' ) //ゲーム終了後は自動更新しない
	{
		$auto_reload = empty($auto_reload) ? 0 : $auto_reload;

		$auto_reload += 0;
		if ($auto_reload == 0) {
			echo "[<a href=game_frame.php?room_no=$room_no&auto_reload=0&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=$showtrip&list_down=$list_down target=_top>手動</a>]\r\n";
		} else {
			echo "<a href=game_frame.php?room_no=$room_no&auto_reload=0&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=$showtrip&list_down=$list_down target=_top>手動</a>\r\n";
		}
		
		if ($auto_reload == 5) {
			echo "[自動]";
		} else {
			echo " <a href=game_frame.php?room_no=$room_no&auto_reload=5&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=$showtrip&list_down=$list_down target=_top>自動</a>\r\n";
		}
			
		echo " [音效通知](";
		if($play_sound == 'on')
		{
			echo "<a href=game_frame.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=off&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=$showtrip&list_down=$list_down target=_top>off</a>";
			echo " on)\r\n";
		}
		else
		{
			echo "off";
			echo " <a href=game_frame.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=on&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=$showtrip&list_down=$list_down target=_top>on</a>)\r\n";
		}
	}
	
	//Playヤー訊息をチャットログの上に表示するか下に表示するか
	if($list_down == 'on')
	{
		echo "<a href=game_frame.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=$showtrip&list_down=off target=_top>↑訊息</a>\r\n";
	}
	else
	{
		echo "<a href=game_frame.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=$showtrip&list_down=on target=_top>↓訊息</a>\r\n";
	}
	
	if($showtrip == '' || $showtrip == 'no')
	{
		echo "<a href=game_frame.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=show&list_down=$list_down target=_top>TRIP</a>\r\n";
	}
	else
	{
		echo "<a href=game_frame.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&dead_mode=$dead_mode&heaven_mode=$heaven_mode&showtrip=no&list_down=$list_down target=_top>TRIP</a>\r\n";
	}
	
	if ($day_night != 'aftergame' || $day_night != 'beforegame') {
		echo "<a href=game_log.php?room_no=$room_no&is=lastwords&log_mode=on&date=1&day_night=day#game_top target=_blank>過去遺書</a>\r\n";
	}
	echo "</td></tr>";
	if(($live == 'dead' || $live == 'gone')) echo "</table>";
	echo "<tr><td>";
	VillageOptOutput();
	echo "</td></tr>";
	
	
	//夜明けを音效通知する
	if($play_sound == 'on')
	{
		//夜明けの場合
		if( ( $cookie_day_night != $day_night) && ($day_night == 'day') )
		{
			//音を鳴らす
			//echo "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,0,0\" width=1 height=1>\r\n";
			//echo "<param name=movie value=\"$morning_swf\">\r\n";
			//echo "<param name=quality value=high>\r\n";
			//echo "<embed src=\"$morning_swf\" quality=high width=1 height=1 type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\">\r\n";
			//echo "</embed>\r\n";
			//echo "</object>\r\n";
			echo "<audio src=\"$morning_mp3\" autoplay>HTML5 audio not supported</audio>\r\n";
		}
		
		
		//異議あり、を音で知らせる
		//クッキーの値を配列に格納する
		sscanf($cookie_objection,"%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,",
							$tmp[0],$tmp[1],$tmp[2],$tmp[3],$tmp[4],$tmp[5],$tmp[6],$tmp[7],$tmp[8],$tmp[9],
							$tmp[10],$tmp[11],$tmp[12],$tmp[13],$tmp[14],$tmp[15],$tmp[16],$tmp[17],$tmp[18],$tmp[19],
							$tmp[20],$tmp[21],$tmp[22],$tmp[23],$tmp[24],$tmp[25],$tmp[26],$tmp[27],$tmp[28],$tmp[29]);
		
		$objection_sex_arr = [];
		$objection_count = 0;
		for($i=0;$i<30;$i++) //差分を計算
		{
			$diff_objection_this_count = $objection_arr[$i] - (int)$tmp[$i];
			if($diff_objection_this_count > 0) //差分があればその性別を確認、合計もカウント
			{
				$this_user_no = $i+1;
				$res_diff_objection = $db->query("select sex from user_entry
													where room_no = '$room_no' and user_no = $this_user_no");
				$diff_objection_user_arr = $db->fetch_array($res_diff_objection);

				$objection_sex_arr[] = $db->result($res_diff_objection,0,0) == 'male' ? 'male' : 'female';

				$objection_count++; //合計
				
			}
		}
		
		for($i=0 ; $i<$objection_count ; $i++)//差分があればその回数だけ音を鳴らす
		{
			//音を鳴らす
			$objection_mp3 = $objection_sex_arr[$i] == 'male' ? $objection_male_mp3 : $objection_female_mp3;
			
			//echo "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,0,0\" width=0 height=0>\r\n";
			//echo "<param name=movie value=\"$objection_swf\">\r\n";
			//echo "<param name=quality value=high>\r\n";
			//echo "<param name=LOOP value=\"true\">\r\n";
			//echo "<embed src=\"$objection_swf\" quality=high width=0 height=0 LOOP=true type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\">\r\n";
			//echo "</embed>\r\n";
			//echo "</object>\r\n";
			echo "<audio src=\"$objection_mp3\" autoplay>HTML5 audio not supported</audio>\r\n";
		}
	}
	
	if($dead_mode == 'on')
		echo "</td></tr></form></table>";
		
	echo "</small></td></tr></table>\r\n";
	
	
	if( $day_night == 'beforegame') //開始前の注意を出力
	{
		echo "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
		echo "<tr style=\"background-color:#009900;color:snow;font-weight:bold;\">\r\n";
		echo "<td valign=middle align=center width=100%>需要遊戲全體人員投'開始遊戲'才能開始遊戲";
		echo "<small>(完成投票的玩家其名單背景顏色會變粉紅)</small></td>\r\n";
		echo "</tr></table>\r\n";


		//return;
	}
	if( $day_night == 'aftergame' ) //終了後
	{
		VictoryOutput();
		return;
	}
	
	
	//経過時間を取得
	if( strstr((string) $game_option,"real_time") ) //限制時間
	{
		
		//實際時間的制限時間を取得
		$real_time_str = strstr((string) $game_option,"real_time");
		sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_minutes,$night_real_limit_minutes);
		$day_real_limit_time = $day_real_limit_minutes * 60; //秒になおす
		$night_real_limit_time = $night_real_limit_minutes * 60; //秒になおす
		
		$time = time();  //現在時刻、GMTとの時差を足す
		
		
		//最も小さな時間(場面の最初的時間)を取得
		$res_start_real_time = $db->query("select min(time) from talk where room_no = '$room_no' and date = $date
																							and location like '$day_night%'");
		
		$start_real_time = $db->result($res_start_real_time,0,0);
		
		if($start_real_time != NULL)
		{
			$pass_real_time = $time - $start_real_time; //経過した時間
		}
		else
			$pass_real_time = 0;
		
		
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
		$res_spend_time = $db->query("select sum(spend_time) from talk where room_no = '$room_no' and date = $date
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
			 																							and user_no > '0' and user_no <= $max_user");
		$live_count = $db->result($res_live_count,0);
		$live_count_str = "<small>(生存者" . $live_count . "人)</small>";
		echo "<td>$date 日目 $live_count_str </td>";
		
		$res_wolf_target_live_c = $db->query("select count(handle_name) from user_entry 
											   where room_no = '$room_no' and handle_name = '$wolf_target_uname' and live='live'");
		$wolf_target_live_c = $db->result($res_wolf_target_live_c,0);

	}
	
	
	if( ($day_night == 'beforegame') && strstr((string) $game_option,"real_time") )
	{
	//	echo "<td valign=top>"; //開始前は時間的ズレを表示
	//	echo " <form name=realtime_form><input class=left_real_time type=text name=realtime_output size=50 readonly>";
	//	echo "</td></form>";
	}
	elseif( $day_night == 'day') //白
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
			echo "　離日落還有 $left_hour 小時";
			
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
			echo "　離日出還有 $left_hour 小時";
			if($left_minuts != 0)
				echo " $left_minuts 分";
			
			echo "</td>";
		}
	}
	echo "</tr></table>";
	
	if ($room_status == 'playing' && $live == 'live' && !strstr((string) $role, 'GM')) {
		if ($day_night == 'day') {
			//$res_v_count = $db->query("select count(uname) from vote where room_no = '$room_no' and uname = '$uname'");
			//$v_count = $db->result($res_v_count,0);
			//if ($v_count == 0) {
			if(!strstr((string) $voted_list, ':;:' . $uname . ':;:')) {
				echo "<span style=\"background-color: #FF0000\"><b>系統提醒：您目前還沒有投票。</b></span>";
			} elseif ($v_count >= 1) {
				$res_vote_times = $db->query("select message from system_message where room_no = '$room_no'
										 and date = $date and type = 'VOTE_TIMES'");
				$vote_times = $db->result($res_vote_times,0,0);
				//$res_v_count = $db->query("select count(uname) from vote where room_no = '$room_no' and uname = '$uname' and vote_times = '$vote_times'");
				//$v_count = $db->result($res_v_count,0);
				//if ($v_count == 0) {
				if(!strstr((string) $voted_list, ':;:' . $uname . ':;:')) {
					echo "<span style=\"background-color: #FF0000\"><b>系統提醒：您目前還沒有投票。</b></span>";
				}
			}
		}
		//$res_v_count = $db->query("select count(uname) from vote where room_no = '$room_no' and uname = '$uname'");
  //if ($db->result($res_v_count,0) == 0) {
  if ($day_night == 'night' && (strstr((string) $role,'wolf') || strstr((string) $role,'fosi') || strstr((string) $role,'mage') || strstr((string) $role,'cat') || strstr((string) $role,'guard')) && !strstr((string) $voted_list, ':;:' . $uname . ':;:')) {
			if (strstr((string) $role,'wfwnd') && $wolf_target_live_c > 0) {
					echo "<span style=\"background-color: #FF0000\"><b>系統提醒：您目前還沒有投票。</b></span>";
				} elseif (strstr((string) $role,'wolf')) {
					echo "<span style=\"background-color: #FF0000\"><b>系統提醒：您目前還沒有投票，如果同側已經投票請忽略此訊息。</b></span>";
				} else {
					echo "<span style=\"background-color: #FF0000\"><b>系統提醒：您目前還沒有投票。</b></span>";
				}
		}
	}
	
	echo "</td><td width=150 align=right>";
	
	//異議あり、のボタン(夜と死者モード以外)
	if( ($day_night != 'night') && ($dead_mode != 'on') && ($heaven_mode != 'on') && ($left_time > 0) )
	{
		echo "<table border=0 cellpadding=0 cellspacing=0><tr>\r\n";
		
		echo "<td width=1000 align=right>";
		echo "<form action=\"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound\" method=POST>";
		echo "<input type=hidden name=set_objection value=set>";
		echo "<input type=image name=objimage src=\"$objection_image\" border=0>($objection_left_count)";
		echo "<a href=\"game_play.php?room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&set_objection=roomend\">廢</a></td></form>";
		echo "</tr></table>";
	}
	
	echo "</td></tr></table>";
	
	
	if( ($day_night == 'day') && ($left_time == 0) )
		echo "　<span style=\"background-color:#CC3300;color:snow;\">　快要日落了。請趕快投票　</span><br />";
		
	if( ($day_night == 'night') && ($left_time == 0) )
		echo "　<span style=\"background-color:#CC3300;color:snow;\">　快要日出了。請趕快投票　</span><br />";
		
	if($GM_EXECUTE && $NOT_EXEC)
		echo "　<span style=\"background-color:#CC3300;color:snow;\">　全數行動完成，等待行動執行中　</span><br />";
}

//----------------------------------------------------------
//能力の種類とその説明を出力
function AbilityOutput(): void
{
	global $room_no,$date,$day_night,$uname,$handle_name,$role,$live,$role_human_image,$role_wolf_image,$role_wolf_partner_image,
		$role_mage_image,$role_necromancer_image,$role_mad_image,$role_guard_image,$role_common_image,$role_common_partner_image,
		$role_fox_image,$role_poison_image,$role_authority_image,$role_mage_result_image,$role_necromancer_result_image,$role_cat_image,
		$role_result_human_image,$role_result_wolf_image,$role_guard_success_image,$role_fox_target_image,$role_betr_image,$role_betr_partner_image,
		$db,$role_fox_partner_image,$role_fosi_image,$role_nom_fosi_image,$role_result_fosi_image,$role_result_wfbig_image,$role_wfbig_image,
		$role_lovers_image,$role_lovers_partner_image, $role_GM_image,$role_pengu_image;
	global $role_mytho_image, $role_owlman_image, $voted_list;
	global $role_noble_image, $role_slave_image, $role_slave_partner_image;
	global $role_wfwtr_image, $role_wfasm_image, $role_wfbsk_image, $role_wfwnd_image, $role_wfxwnd_image;
	global $role_spy_image, $role_spy_wolf_partner_image, $role_wfwnd_final_image,$game_option;
	
	if( ($day_night == 'beforegame') || ($day_night == 'aftergame' ) )
		return;
	
	$date_yesterday = $date -1;
	
	if($live == 'dead') //死亡したら能力を表示しない
	{
		echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#CC0000;color:snow;\">";
		
		echo "　　　お前はもう死んでいる・・・　　　</span><br />";
		return;
	}
	if($live == 'gone')
	{
		echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#0f0f0f;color:snow;\">";
		
		echo "　　　已經脫離村莊。　　　</span><br />";
		return;
	}
	if (($youuname = strstr((string) $voted_list, ':;:' . $uname . ':;:')) && $live == 'live' && $uname == str_replace(":;:",'',$youuname)) {
     $you_vote_is = $db->query("select sentence from talk{$isold} where room_no = '$room_no' and date = $date
									 and uname = '$uname' and location = '$day_night system';");
     $you_voteis = $db->fetch_array($you_vote_is);
     $vnameis = explode("\t",(string) $you_voteis['sentence']);
     if(strstr((string) $game_option,"usr_guest") && $day_night != 'aftergame' && $day_night != 'beforegame') {
  				$vnameis[1] = "(馬賽克)";
  			}
     if (strstr((string) $you_voteis['sentence'], 'VOTE_DO')) {
  				echo "<span style=\"background-color:#FFFFFF;\">您已經選擇處死 $vnameis[1] </span><br />";
  			}
     if (strstr((string) $you_voteis['sentence'], 'MAGE_DO') || strstr((string) $you_voteis['sentence'], 'FOSI_DO')) {
  				echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇占卜 $vnameis[1] </span><br />";
  			}
     if (strstr((string) $you_voteis['sentence'], 'WOLF_EAT')) {
  				echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇咬殺 $vnameis[1] </span><br />";
  			}
     if (strstr((string) $you_voteis['sentence'], 'GUARD_DO')) {
  				echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇護衛 $vnameis[1] </span><br />";
  			}
     if (strstr((string) $you_voteis['sentence'], 'MYTHO_DO')) {
  				echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇模仿 $vnameis[1] </span><br />";
  			}
     if (strstr((string) $you_voteis['sentence'], 'CAT_DO')) {
  				if($vnameis[1] == ':;:NOP:;:') {
  					echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經放棄行動 </span><br />";
  				} else {
  					echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇復活 $vnameis[1] </span><br />";
  				}
  			}
     if (strstr((string) $you_voteis['sentence'], 'OWLMAN_DO')) {
  				if($vnameis[1] == ':;:NOP:;:') {
  					echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經放棄行動 </span><br />";
  				} else {
  					echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇詛咒 $vnameis[1] </span><br />";
  				}
  			}
     if (strstr((string) $you_voteis['sentence'], 'PENGU_DO')) {
  				echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇搔癢 $vnameis[1] </span><br />";
  			}
     if (strstr((string) $you_voteis['sentence'], 'SPY_DO')) {
  				echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇完成任務，離開村莊 </span><br />";
  			}
     if (strstr((string) $you_voteis['sentence'], 'HUG_DO')) {
  				echo "<span style=\"background-color:#FFFFFF; color:#000000\">您已經選擇鑽到 $vnameis[1] 懷裡 </span><br />";
  			}
 }
	if( strstr((string) $role,"human") )
	{
		echo "<img src=\"$role_human_image\"><br />";
	}
	if (strstr((string) $role,"wolf")) {
		if (strstr((string) $role,"wfbig")) {
      echo "<img src=\"$role_wfbig_image\"><br />";
  } elseif (strstr((string) $role,"wfwtr")) {
      echo "<img src=\"$role_wfwtr_image\"><br />";
  } elseif (strstr((string) $role,"wfasm")) {
      echo "<img src=\"$role_wfasm_image\"><br />";
  } elseif (strstr((string) $role,"wfbsk")) {
      echo "<img src=\"$role_wfbsk_image\"><br />";
  } elseif (strstr((string) $role,"wfwnd")) {
      echo "<img src=\"$role_wfwnd_image\"><br />";
  } elseif (strstr((string) $role,"wfxwnd")) {
      echo "<img src=\"$role_wfxwnd_image\"><br />";
  } else {
			echo "<img src=\"$role_wolf_image\"><br />";
		}
		
		//仲間を表示する
		$res_wolf_partner = $db->query("select user_no,handle_name,role from user_entry
										 where room_no = '$room_no' and role like 'wolf%' and uname <> '$uname'
										 and user_no > '0'");
	//	$partner_count = $db->num_rows($res_wolf_partner);
		
		echo "<table border=1 cellpadding=0 cellspacing=0><tr><td valign=middle>";
		echo "<img src=\"$role_wolf_partner_image\"></td><td valign=middle>　";
		while($res_wolf = $db->fetch_array($res_wolf_partner))
		{
			if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame') {
				if (!(strstr((string) $game_option,'gm:'.$talk_log_array['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                    $res_wolf['user_no'] =str_pad((string) $res_wolf['user_no'],2,"0",STR_PAD_LEFT);
                    $res_wolf['handle_name'] = "玩家".$res_wolf['user_no']."號";
                }
			}
			echo $res_wolf['handle_name'];
			if (strstr((string) $res_wolf['role'],"wfbig")) {
				echo "(大狼)";
			}
			if (strstr((string) $res_wolf['role'],"wfwtr")) {
				echo "(冬狼)";
			}
			if (strstr((string) $res_wolf['role'],"wfasm")) {
				echo "(明日夢)";
			}
			if (strstr((string) $res_wolf['role'],"wfbsk")) {
				echo "(狂狼)";
			}
			if (strstr((string) $res_wolf['role'],"wfwnd")) {
				echo "(捲尾巴幼狼)";
			}
			if (strstr((string) $res_wolf['role'],"wfxwnd")) {
				echo "(捲尾巴幼狼？)";
			}

			echo "　　";
		}
		echo "</td></tr></table>";
		
		$res_wolf_partner_live = $db->query("select count(*) from user_entry
										 where room_no = '$room_no' and role like 'wolf%' and uname <> '$uname' and live = 'live'
										 and user_no > '0'");

		$count_wolf_partner_live = $db->result($res_wolf_partner_live);
		if(strstr((string) $role,'wfwnd') && $count_wolf_partner_live == 0) {
			echo "<img src=\"$role_wfwnd_final_image\"><br />";
		}

		if($day_night == 'night')
		{
		
			if(strstr((string) $role,'wfwnd') && $count_wolf_partner_live > 0) {
				$res_wolf_voted = $db->query("select uname from vote where room_no = '$room_no' and situation = 'HUG_DO'");
				if($db->num_rows($res_wolf_voted) == 0)
				{
					echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#28e7ff;color:snow;\">";
					echo "　　　請選擇萌殺對象　　　</span><br />";
				}
			} else {
				$res_wolf_voted = $db->query("select uname from vote where room_no = '$room_no' and situation = 'WOLF_EAT'");
				if($db->num_rows($res_wolf_voted) == 0)
				{
					echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#CC0000;color:snow;\">";
					echo "　　　請選擇咬人對象　　　</span><br />";
				}
			}
		}
	}
	if( strstr((string) $role,"mage") )
	{
		echo "<img src=\"$role_mage_image\"><br />";
		
		$result = $db->query("select message from system_message where room_no = '$room_no' and date = $date_yesterday
			                           and type = 'MAGE_RESULT'");
		
		while($res_mage = $db->fetch_array($result)) {
			$mage_result_message = explode("\t", (string) $res_mage['message']);
			$mage_handle_name = $mage_result_message[0];
			$target_handle_name = $mage_result_message[1];
			$target_role = $mage_result_message[2];
			
			if( $handle_name == $mage_handle_name )
			{
				echo "<table border=1 cellpadding=0 cellspacing=0><tr>";
				echo "<td valign=middle><img src=\"$role_mage_result_image\" alt=\"占卜結果：\"></td>";
				if ($target_role == 'human') {
					echo "<td valign=middle>$target_handle_name</td>";
					echo "<td valign=middle><img src=\"$role_result_human_image\" alt=\" 人\"></td>";
				} else {
					echo "<td valign=middle><span style=\"color: Red;\">$target_handle_name</span></td>";
					echo "<td valign=middle><img src=\"$role_result_wolf_image\" alt=\" 狼\"></td>";
				}
				
				echo "</tr></table>";
			}
		}
		
		if($day_night == 'night')
		{
			$res_mage_voted = $db->query("select uname from vote where room_no = '$room_no' and
																uname = '$uname' and situation = 'MAGE_DO'");
			if($db->num_rows($res_mage_voted) == 0)
			{
				echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#990099;color:snow;\">";
				echo "　　　請選擇要占卜的對象　　　</span><br />";
			}

		}
	}
	if( strstr((string) $role,"necromancer") )
	{
		echo "<img src=\"$role_necromancer_image\"><br />";
		
		$result = $db->query("select message from system_message where room_no = '$room_no' and date = $date_yesterday
			                           and type = 'NECROMANCER_RESULT'");
		
		if ($db->num_rows($result)) {
			echo "<table border=1 cellpadding=0 cellspacing=0>";
			while($mecromancer_result = $db->fetch_array($result)) {
				$mecromancer_result_message = explode("\t", (string) $mecromancer_result['message']);
				$target_handle_name = $mecromancer_result_message[0];
				$target_role = $mecromancer_result_message[1];
				echo "<tr>";
				echo "<td valign=middle><img src=\"$role_necromancer_result_image\" alt=\"靈腦結果：\"></td>";
				if ($target_role == 'human') {
					echo "<td valign=middle>$target_handle_name</td>";
					echo "<td valign=middle><img src=\"$role_result_human_image\" alt=\" 人\"></td>";
				} elseif ($target_role == 'fosi') {
					echo "<td valign=middle><span style=\"color: #CC0099;\">$target_handle_name</span></td>";
					echo "<td valign=middle><img src=\"$role_result_fosi_image\" alt=\" 子狐\"></td>";
				} elseif ($target_role == 'wfbig') {
					echo "<td valign=middle><span style=\"color: Red;\">$target_handle_name</span></td>";
					echo "<td valign=middle><img src=\"$role_result_wfbig_image\" alt\" 大狼\"></td>";
				} else {
					echo "<td valign=middle><span style=\"color: Red;\">$target_handle_name</span></td>";
					echo "<td valign=middle><img src=\"$role_result_wolf_image\" alt=\" 狼\"></td>";
				}
				echo "</tr>";
			}
			
			echo "</table>";
		}
	}
	if( strstr((string) $role,"mad") )
	{
		echo "<img src=\"$role_mad_image\"><br />";
	}
	if( strstr((string) $role,"common") )
	{
		echo "<img src=\"$role_common_image\"><br />";
		
		//仲間を表示する
		$res_wolf_partner = $db->query("select handle_name from user_entry
											where room_no = '$room_no' and role like 'common%' and uname <> '$uname'
			 																								and user_no > '0'");
	//	$partner_count = $db->num_rows($res_wolf_partner);
		
		echo "<table border=1 cellpadding=0 cellspacing=0><tr><td valign=middle>";
		echo "<img src=\"$role_common_partner_image\"></td><td valign=middle>　";
		while($res_wolf = $db->fetch_array($res_wolf_partner))
		{
			echo $res_wolf['handle_name'];
			echo "　　";
		}
		echo "</td></tr></table>";
		
	}
	if( strstr((string) $role,"guard") )
	{
		echo "<img src=\"$role_guard_image\"><br />";
		
		$result = $db->query("select message from system_message where room_no = '$room_no' and date = $date_yesterday
																						and type = 'GUARD_SUCCESS'");
		while($res_guard = $db->fetch_array($result)) {
			$guard_success_message = explode("\t", (string) $res_guard['message']);
			$guard_handle_name = $guard_success_message[0];
			$guard_target_handle_name = $guard_success_message[1];
			
			if($handle_name == $guard_handle_name)
			{
				echo "<table border=1 cellpadding=0 cellspacing=0><tr>";
				echo "<td valign=middle>$guard_target_handle_name</td>";
				echo "<td valign=middle><img src=\"$role_guard_success_image\" alt=\"護衛成功！\"></td>";
				echo "</tr></table>";
			}
		}
		
		if( ($day_night == 'night') && ($date != 1) )
		{
			$res_guard_voted = $db->query("select uname from vote where room_no = '$room_no' and
																uname = '$uname' and situation = 'GUARD_DO'");
			if($db->num_rows($res_guard_voted) == 0)
			{
				echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#0099FF;color:snow;\">";
				echo "　　　請選擇護衛的人　　　</span><br />";
			}

		}
	}
	if( strstr((string) $role,"fox") )
	{
		echo "<img src=\"$role_fox_image\"><br />";

		//妖狐同伴
		$res_fox_partner = $db->query("select handle_name from user_entry
										where room_no = '$room_no' and role like 'fox%' and uname <> '$uname'
			 							and user_no > '0'");
		
		echo "<table border=1 cellpadding=0 cellspacing=0><tr><td valign=middle>";
		echo "<img src=\"$role_fox_partner_image\"></td><td valign=middle>　";
		while($res_fox = $db->fetch_array($res_fox_partner))
		{
			echo $res_fox['handle_name'];
			echo "　　";
		}
		echo "</td></tr></table>";

		$result = $db->query("select message from system_message where room_no = '$room_no' and date = $date_yesterday
			 															                          and type = 'FOX_EAT'");
		while($res_fox_eat = $db->fetch_array($result)) {
			$fox_eat_handle_name = $res_fox_eat['message'];
			
			if($handle_name == $fox_eat_handle_name)
			{
				echo "<table border=1 cellpadding=0 cellspacing=0><tr>";
				echo "<td valign=middle><img src=\"$role_fox_target_image\"></td>";
				echo "</tr></table>";
			}
		}
	}

	if (strstr((string) $role,"betr")) {
		echo "<img src=\"$role_betr_image\"><br />";
		
		$res_betr_partner = $db->query("select handle_name from user_entry
										where room_no = '$room_no' and role like 'fox%' and uname <> '$uname'
			 							and user_no > '0'");
		
		echo "<table border=1 cellpadding=0 cellspacing=0><tr><td valign=middle>";
		echo "<img src=\"$role_betr_partner_image\"></td><td valign=middle>　";
		while($res_betr = $db->fetch_array($res_betr_partner)) {
			echo $res_betr['handle_name'];
			echo "　　";
		}
		echo "</td></tr></table>";

	}

	if (strstr((string) $role,"fosi")) {
		echo "<img src=\"$role_fosi_image\"><br />"; //子狐圖片
		
		$res_fosi_partner = $db->query("select handle_name from user_entry
										where room_no = '$room_no' and role like 'fox%' and uname <> '$uname'
			 							and user_no > '0'");
		
		echo "<table border=1 cellpadding=0 cellspacing=0><tr><td valign=middle>";
		echo "<img src=\"$role_betr_partner_image\"></td><td valign=middle>　"; //子狐圖片
		while($res_fosi = $db->fetch_array($res_fosi_partner)) {
			echo $res_fosi['handle_name'];
			echo "　　";
		}
		echo "</td></tr></table>";

		$result = $db->query("select message from system_message where room_no = '$room_no' and date = $date_yesterday
							 and type = 'FOSI_RESULT'");
		
		while($res_fosi = $db->fetch_array($result)) {
			$fosi_result_message = explode("\t", (string) $res_fosi['message']);
			$fosi_handle_name = $fosi_result_message[0];
			$target_handle_name = $fosi_result_message[1];
			$target_role = $fosi_result_message[2];
			
			if($handle_name == $fosi_handle_name) {
				echo "<table border=1 cellpadding=0 cellspacing=0><tr>";
				echo "<td valign=middle><img src=\"$role_mage_result_image\" alt=\"占卜結果：\"></td>";
				if ($target_role == 'human') {
					echo "<td valign=middle>$target_handle_name</td>";
					echo "<td valign=middle><img src=\"$role_result_human_image\" alt=\" 人\"></td>";
				} elseif ($target_role == 'nofosi') {
					echo "<td valign=middle>$target_handle_name</td>";
					echo "<td valign=middle><img src=\"$role_nom_fosi_image\" alt=\" 很可疑。\"></td>";
				} else {
					echo "<td valign=middle><span style=\"color: Red;\">$target_handle_name</span></td>";
					echo "<td valign=middle><img src=\"$role_result_wolf_image\" alt=\" 狼\"></td>";
				}
				echo "</tr></table>";
			}
		}
		
		if($day_night == 'night') {
			$res_fosi_voted = $db->query("select uname from vote where room_no = '$room_no' and
										 uname = '$uname' and situation = 'FOSI_DO'");
			if($db->num_rows($res_fosi_voted) == 0) {
				echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#990099;color:snow;\">";
				echo "　　　請選擇要占卜的對象　　　</span><br />";
			}
		}
	}

	if( strstr((string) $role,"poison") )
	{
		echo "<img src=\"$role_poison_image\"><br />";
	}
	if( strstr((string) $role,"cat") )
	{
		echo "<img src=\"$role_cat_image\"><br />";
		$result = $db->query("select message from system_message where room_no = '$room_no' and date = $date_yesterday
							 and type = 'CAT_EAT'");
		while($res_cat_eat = $db->fetch_array($result)) {
			$cat_eat_handle_name = $res_cat_eat['message'];
			
			if ($handle_name == $cat_eat_handle_name) {
				echo "<table border=1 cellpadding=0 cellspacing=0><tr>";
				echo "<td valign=middle><img src=\"$role_fox_target_image\"></td>";
				echo "</tr></table>";
			}
		}
	}
	if( strstr((string) $role,"pengu") )
	{
		echo "<img src=\"$role_pengu_image\"><br />";
	}
	if( strstr((string) $role,"GM") )
	{
		echo "<img src=\"$role_GM_image\"><br />";
	}
	
	if( strstr((string) $role,"mytho") )
	{
		echo "<img src=\"$role_mytho_image\"><br />";
				
		if( ($day_night == 'night') && ($date == 2) )
		{
			$res_guard_voted = $db->query("select uname from vote where room_no = '$room_no' and
										  uname = '$uname' and situation = 'MYTHO_DO'");
			if($db->num_rows($res_guard_voted) == 0)
			{
				echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#FF8000;color:snow;\">";
				echo "　　　請選擇要模仿的人　　　</span><br />";
			}
			
		}
	}
	
	if( strstr((string) $role,"owlman") )
	{
		echo "<img src=\"$role_owlman_image\"><br />";
		
		if( ($day_night == 'night') && ($date != 1) )
		{
			$res_guard_voted = $db->query("select uname from vote where room_no = '$room_no' and
										  uname = '$uname' and situation = 'OWLMAN_DO'");
			if($db->num_rows($res_guard_voted) == 0)
			{
				echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#000080;color:snow;\">";
				echo "　　　請選擇要詛咒的人　　　</span><br />";
			}
			
		}
	}
	
	if (strstr((string) $role,"noble")) {
		echo "<img src=\"$role_noble_image\"><br />";
	}
	
	if (strstr((string) $role,"slave")) {
		echo "<img src=\"$role_slave_image\"><br />";
		
		$res_slave_partner = $db->query("select handle_name from user_entry
										where room_no = '$room_no' and role like 'noble%' and uname <> '$uname'
			 							and user_no > '0'");
		
		echo "<table border=1 cellpadding=0 cellspacing=0><tr><td valign=middle>";
		echo "<img src=\"$role_slave_partner_image\"></td><td valign=middle>　";
		while($res_slave = $db->fetch_array($res_slave_partner)) {
			echo $res_slave['handle_name'];
			echo "　　";
		}
		echo "</td></tr></table>";

	}
	
	
	if (strstr((string) $role,"spy")) {
		echo "<img src=\"$role_spy_image\"><br />";
		
		//仲間を表示する
		$res_wolf_partner = $db->query("select handle_name,role from user_entry
										 where room_no = '$room_no' and role like 'wolf%' and uname <> '$uname'
										 and user_no > '0'");
		
		echo "<table border=1 cellpadding=0 cellspacing=0><tr><td valign=middle>";
		echo "<img src=\"$role_spy_wolf_partner_image\"></td><td valign=middle>　";
		while($res_wolf = $db->fetch_array($res_wolf_partner))
		{
			echo $res_wolf['handle_name'];
			if (strstr((string) $res_wolf['role'],"wfbig")) {
				echo "(大狼)";
			}
			if (strstr((string) $res_wolf['role'],"wfwtr")) {
				echo "(冬狼)";
			}
			if (strstr((string) $res_wolf['role'],"wfasm")) {
				echo "(明日夢)";
			}
			if (strstr((string) $res_wolf['role'],"wfbsk")) {
				echo "(狂狼)";
			}
			if (strstr((string) $res_wolf['role'],"wfwnd")) {
				echo "(捲尾巴幼狼)";
			}
			if (strstr((string) $res_wolf['role'],"wfxwnd")) {
				echo "(捲尾巴幼狼？)";
			}

			echo "　　";
		}
		echo "</td></tr></table>";
		
		if($day_night == 'night')
		{
			$res_spy_voted = $db->query("select uname from vote where room_no = '$room_no' and situation = 'SPY_DO'");
			if($db->num_rows($res_spy_voted) == 0)
			{
				echo "<span style=\"font-size:14pt;font-weight:bold;background-color:#0f0f0f;color:snow;\">";
				echo "　　　請儘快選擇是否完成任務，離開村莊。　　　</span><br />";
			}
		}
	}
	if( strstr((string) $role,"authority") )
	{
		echo "<img src=\"$role_authority_image\"><br />";
	}
	if( strstr((string) $role,"decide") )
	{
		//echo "<img src=\"$role_human_image\"><br />";
	}
	if (strstr((string) $role,"lovers")) {
		echo "<br /><img src=\"$role_lovers_image\"><br />";
		
		$res_lovers_partner = $db->query("select handle_name from user_entry
										 where room_no = '$room_no' and lovers = '1' and uname <> '$uname'
										 and user_no > '0'");
		
		echo "<table border=1 cellpadding=0 cellspacing=0><tr><td valign=middle>";
		echo "<img src=\"$role_lovers_partner_image\"></td><td valign=middle>　";
		while($res_lovers = $db->fetch_array($res_lovers_partner)) {
			echo $res_lovers['handle_name'];
			echo "　　";
		}
		echo "</td></tr></table>";
	}
	
	
}



//----------------------------------------------------------
//自己的遺言を出力
function SelfLastWordsOutput(): void
{
	global $uname,$room_no,$day_night,$db,$isold;
	
	if($day_night == 'aftergame')
		return;
	
	$res_last_words = $db->query("select last_words from user_entry where room_no = '$room_no' and uname = '$uname'
		 																										and user_no > '0'");
	
	if( $db->num_rows($res_last_words) == 0) //まだ入力してなければ表示しない
		return;
	
	$last_words = $db->result($res_last_words,0,0);
	$last_words = str_replace("\n","<br />",(string) $last_words);
	
	if($last_words == '')
		return;
	
	 $last_words = preg_replace([
	        "/\[b\](.*?)\[\/b\]/is",
	        "/\[u\](.*?)\[\/u\]/is",
	        "/\[d\](.*?)\[\/d\]/is"], [
	        "<b>\\1</b>",
	        "<u>\\1</u>",
	        "<del>\\1</del>"], $last_words);
	
	echo "<table border=0 cellpadding=0 cellspacing=5 width=100%>\r\n";
	echo "<tr style=\"color:black;background-color:#ddeeff;\">\r\n";
	echo "<td valign=middle align=right width=140>自己的遺言</td>\r\n";
	echo "<td valign=top align=left> $last_words </td></tr></table>\r\n";
	
}

function chdis(string $ch): string|false 
{
	global $game_option;
	return strstr((string) $game_option, 'ch_'.$ch);
}

?>
