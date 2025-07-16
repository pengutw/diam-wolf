<?php
require_once __DIR__ . '/game_functions.php';

//Session開始
session_start();
$session_id = session_id();

$get_date = $date;
$get_day_night = $day_night;

//MySQLに接続
if($db->connect_error())
{
	exit;
}

function LastWordsOutput2(): void {
	global $room_no,$day_night,$db,$isold,$room_arr;
	
	if ($day_night == 'beforegame' || $day_night == 'aftergame') {
		return;
	}
	
	$date = $room_arr['date'];
	
	$res_last_words = $db->query("select message,date from system_message{$isold}
								 where room_no = '$room_no' and date < $date and type = 'LAST_WORDS' order by id desc");
	$last_words_count = $db->num_rows($res_last_words);
	if ($last_words_count > 0) {
		echo "<table border=0 cellpadding=0 cellspacing=0 width=100%>\r\n";
		echo "<tr style=\"background-color:#ccddff;color:black;font-weight:bold;\">\r\n";
		echo "<td valign=middle align=left colspan=3 width=100% style=\"background-color:#ccddff;color:black;font-weight:bold;\">過去的遺書(新=>舊)</td>\r\n";
		echo "</tr></table>\r\n";
	} else {
		echo "沒有遺言";
		exit;
	}
	echo "<table border=0 cellpadding=0 cellspacing=0>";
	while($message_str = $db->fetch_array($res_last_words))
	{
	//	$message_str = $db->result($res_last_words,$i,0);
		$date = $message_str['date'];
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
		
			 $last_words_str = preg_replace([
	        "/\[b\](.*?)\[\/b\]/is",
	        "/\[u\](.*?)\[\/u\]/is",
	        "/\[d\](.*?)\[\/d\]/is"], [
	        "<b>\\1</b>",
	        "<u>\\1</u>",
	        "<del>\\1</del>"], $last_words_str);
		
		$last_words_str = str_replace("\n","<br />",$last_words_str);
		
		echo "<tr style=\"background-color:#eeeeff;\">\r\n";
		echo "<td width=140 align=left valign=middle style=\"color:black;border-top: silver 1px dashed;\">";
		echo "$last_words_handle_name <small>的遺言 (".$date."日)</small>";
		echo "<td><span style=\"margin:1px;\" align=left></span></td>";
		echo "<td valign=middle style=\"border-top: silver 1px dashed;\">";
		echo "<table><td width=1000 style=\"color:black;\">$last_words_str </span></td></table>";
		echo "</td>\r\n";
		echo "</tr>\r\n";
		
	}
	echo "</table>";
	
}

if( ($uname = SessionCheck($session_id)) != NULL )
{

	//日付と白か夜かを取得
	$res_room = $db->query("select date,day_night,room_name,room_comment,game_option,status,dellook from room where room_no = '$room_no'");
	if ($db->num_rows($res_room)) {
		$room_arr = $db->fetch_array($res_room);
	} else {
		//$isold = '_old';
		$res_room = $db->query("select date,day_night,room_name,room_comment,game_option,status,dellook from room{$isold} where room_no = '$room_no'");
		$room_arr = $db->fetch_array($res_room);
	}
	$date = $room_arr['date'];
	$day_night = $room_arr['day_night'];
	$room_name = $room_arr['room_name'];
	$room_comment = $room_arr['room_comment'];
	$game_option = $room_arr['game_option'];
	$game_dellook = $room_arr['dellook'];
	$game_status = $room_arr['status'];
	$db->free_result($res_room);

	if ($game_dellook == '0' && $game_status == 'playing' && $get_day_night != 'day' && $uname != 'dummy_boy') {
		echo '遊戲中本村關閉';
		exit;
	}

	//自分のハンドルネーム、役割、生存を取得
	$res_user = $db->query("select user_no,handle_name,sex,role,live from user_entry where room_no = '$room_no'
							and uname = '$uname' and user_no > '0'");
	$user_arr = $db->fetch_array($res_user);
	$user_no = $user_arr['user_no'];
	$handle_name = $user_arr['handle_name'];
	$sex = $user_arr['sex'];
	$role = $user_arr['role'];
	$live = $user_arr['live'];
	$db->free_result($res_user);

	if($uname == 'dummy_boy') {
		$live = 'dead';
	} 

	if($live == 'gone') $live = 'dead';

	if(($live != 'dead' && $day_night == 'aftergame' && $uname != 'dummy_boy')) {//死者かゲーム終了後だけ
		echo'<html><head><title>驗證錯誤</title><link rel="stylesheet" type="text/css" href="img/font.css">
				</head><body bgcolor=aliceblue"><br /><br />';
		echo "　　　　瀏覽權限錯誤<br />";
		echo "　　　　<a href=index.php target=_top style=\"color:blue;\">首頁</a>重新登錄</body></html>";
		return;
	} elseif (($live == '' && $is != 'lastwords' && $uname != 'dummy_boy')) {
		echo'<html><head><title>驗證錯誤</title><link rel="stylesheet" type="text/css" href="img/font.css">
				</head><body bgcolor=aliceblue"><br /><br />';
		echo "　　　　瀏覽權限錯誤<br />";
		echo "　　　　<a href=index.php target=_top style=\"color:blue;\">首頁</a>重新登錄</body></html>";
		return;
	}

	$date = $get_date;
	$day_night = $get_day_night;

	if($date === NULL) {
		$date = 0;
	}

	if ($live == 'dead' && $is != 'lastwords' && $date != "ALL") {
		if ($day_night == 'day') {
      $date_day_night_message = "<td width=1000 align=right>紀錄瀏覽 $date 日目(白)</td>";
  } elseif ($day_night == 'night') {
      $date_day_night_message = "<td width=1000 align=right>紀錄瀏覽 $date 日目(夜)</td>";
  } elseif ($day_night == 'beforegame') {
      $date_day_night_message = "<td width=1000 align=right>紀錄瀏覽 開場前</td>";
  }
	}
	if ($date == "ALL") {
		$date_day_night_message = "<td width=1000 align=right>紀錄瀏覽 ALL (白)</td>";
	}

	HTMLHeaderOutput();       //HTMLヘッダ出力
	echo "<table>\r\n";
	echo "<tr>\r\n";
	echo $date_day_night_message;
	echo "</tr><tr>\r\n";
	if ($live == 'dead' && $is == '' && $date != "ALL") {
		//PlayerListOutput();   //Playヤーリストを出力
		//echo "</tr><tr>\r\n";
		TalkLogOutput(1);          //会話ログを出力
		echo "</tr><tr>\r\n";
		if ($game_dellook == '1' || $uname == 'dummy_boy') {
			AbilityActionOutput(); //能力発揮を出力
		}
		echo "</tr><tr>\r\n";
		DeadManOutput();         //死亡者を出力
		if($day_night == 'night')
		{
			echo "</tr><tr>\r\n";
			VoteListOutput();            //投票結果出力
		}
	} elseif ($date == "ALL") {
		TalkLogOutput(1); 
		//echo 'Bug, off.';
	} elseif (($live != '' && $is == 'lastwords') || $uname == 'dummy_boy') {
		if (strstr((string) $game_option,"will")) {
			LastWordsOutput2();
		}
	}
	echo "</tr></table>\r\n";
	HTMLFooterOutput();        //HTMLフッタ出力
	
	
}
else
{
	echo '<html><head><title>Session驗證錯誤</title><link rel="stylesheet" type="text/css" href="img/font.css">
			</head><body bgcolor=aliceblue>
			<br /><br />';
	echo "　　　　Session驗證錯誤<br />";
	echo "　　　　<a href=index.php target=_top style=\"color:blue;\">首頁</a>重新登錄</body></html>";
}

//MySQLとの接続を閉じる
$db->close();



?>
