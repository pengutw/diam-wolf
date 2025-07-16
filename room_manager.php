<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/msgimg_setting.php';
	
//MySQLに接続
if ($db->connect_error()) {
    exit;
} elseif (CheckTable()==false) {
    echo "<font color=red>$lang_room_check_table_fail</font>";
    exit;
}
$command = empty($command) ? "" : $command;

//一定時間放置されていれば廃村にする
CheckDieRoom();

switch($command)
{
	case('CREATE_ROOM'):
		if( in_array($max_user,$maxuser_array) )
		{
			CreateRoom($room_name,$room_comment,$max_user,$dellook);
		}
		break;
	case('MODIFY_ROOM'):
		if( in_array($max_user,$maxuser_array) )
		{
			ModifyRoom($room_name,$room_comment,$max_user,$dellook);
		}
		break;
	default:
		ViewRoomList();
		break;
}

//MySQLとの接続を閉じる
$db->close();

//--------------------------------------------------------------------------------------------------
//必要なテーブルがあるか確認する
function CheckTable(): bool
{
	global $db_name,$db,$isold;
	//取得已建立的table表
	$result = $db->query("SHOW TABLES from $db_name");
	$exists_table=[];
	while($row=$db->fetch_row($result)) {
		$exists_table[] = $row[0];
	}
	if(!in_array('admin_manage',$exists_table) ||
		!in_array('room',$exists_table) ||
		!in_array('system_message',$exists_table) ||
		!in_array('talk',$exists_table) ||
		!in_array('user_entry',$exists_table) ||
		!in_array('user_icon',$exists_table) ||
		!in_array('vote',$exists_table)) {
		return false;
	} else {
		return true;
	}
}
/*function CheckTable()
{
	global $db_name,$user_icon_dir,$def_icon_name_array,$def_icon_color_array,$def_icon_width_array,$def_icon_height_array,
				$dummy_boy_user_icon_image,$dummy_boy_user_icon_width,$dummy_boy_user_icon_height,$system_password;	
		
		//初期のアイコンのファイル名と色データをDBに登錄する
		$icon_no = 1;
		
		//ディレクトリ内のファイル一覧を取得
		if ($handle = opendir($user_icon_dir)) {
			while (false !== ($icon_file = readdir($handle))) { 
				if ($icon_file != "." && $icon_file != "..") {
					
					//初期データの読み込み
					$icon_name = $def_icon_name_array[$icon_no -1];
					$color = $def_icon_color_array[$icon_no -1];
					$icon_width = $def_icon_width_array[$icon_no -1];
					$icon_height = $def_icon_height_array[$icon_no -1];
					
					$db->query("insert into user_icon(icon_no,icon_name,icon_filename,icon_width,icon_height,color)
									values ($icon_no,'$icon_name','$icon_file',$icon_width,$icon_height,'$color') ");
					$icon_no++;
					echo "　用戶アイコン($icon_file $icon_name $icon_width x $icon_height $color)を登錄しました<br />";
				} 
			}
			closedir($handle); 
		
		}
		//替身君のアイコンを登錄(アイコンNo：0)
		$db->query("insert into user_icon(icon_no,icon_name,icon_filename,icon_width,icon_height,color)
		values (0,'替身君用','$dummy_boy_user_icon_image',$dummy_boy_user_icon_width,$dummy_boy_user_icon_height,'#000000') ");
		
	}	
}
*/
//--------------------------------------------------------------------------------------------------
//HTML輸出
function ShowHtmlOut($txtout): void
{	
	global $lang_def_page_back;
	$lang_room_htmltitle = empty($lang_room_htmltitle) ? "" : $lang_room_htmltitle;
	echo "<html><head><title>$lang_room_htmltitle</title><meta http-equiv=refresh content='5;URL=index.php'><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"></head><body bgcolor=aliceblue>
	$txtout
	<br /><br /><a href='index.php'>$lang_def_page_back</a></body></html>";
}

function ShowHtmlFail($err_string): void
{
	global $lang_room_fail,$lang_def_page_back,$lang_room_tryback;
	$lang_room_htmltitle = empty($lang_room_htmltitle) ? "" : $lang_room_htmltitle;
	echo "<html><head><title>$lang_room_htmltitle</title><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"></head><body bgcolor=aliceblue>
	<center><table border=0><tr><td><fieldset style='background:#fcbccc;border-color:#33336;width:350px;'>
	<legend><strong>$lang_room_fail</strong></legend>
	<label><ul>$err_string<li><a href='javascript:window.history.back()'>$lang_room_tryback</a></ul></label></fieldset></td></tr></table></center></body></html>";
}

//--------------------------------------------------------------------------------------------------
//村(room)の建立

function CreateRoom($room_name,$room_comment,$max_user,$dellook = 0): void
{
	global $system_password,$time_zone,$game_option_real_time,$game_option_real_time_day,$game_option_real_time_night,$option_role_pobe,$option_role_foxs,$option_role_decide,$option_role_authority,$option_role_poison,$game_option_wish_role,$game_option_dummy_boy,$game_option_open_vote,$game_option_comm_out,$db,$isold,$dummy_boy_imgid;
	global $sys_create_room_max,$lang_create_room_disable,$sys_create_room_enabled,$option_wfbig_poison,$option_more_wolf,$game_option_vote_me,$game_option_trip,$tripkey,$game_option_will,$game_option_gm,$game_option_chats,$game_option_guest,$game_option_manager_trip,$game_option_manager_trip_enc,$option_role_lovers,$option_role_comlover,$game_option_trip_countsum;
	global $option_role_mytho, $option_role_owlman;
	global $triptonum,$game_option_votedisplay,$game_option_cust_dummy,$dummy_lw,$dummy_name,$game_option_dummy_autolw, $option_role_noble, $option_role_pengu;

	//global $msgimg;
	global $msg_room_image;
	global $option_role_spy;
	global $recaptcha_response;
	
	if (!$sys_create_room_enabled) { 
		echo $lang_create_room_disable;
		exit;
	}

	//用戶のIPアドレスを取得
	if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
     $ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
 } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
     $ip_address = $_SERVER['HTTP_CLIENT_IP'];
 } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
     $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
 } else{
		$ip_address= $_SERVER['REMOTE_ADDR'];
	}

	$ipv6 = preg_match("/^[0-9a-f]{1,4}:([0-9a-f]{0,4}:){1,6}[0-9a-f]{1,4}$/", (string) $ip_address);
	$ipv4 = preg_match("/^(\\d{1,3}\\.){3}\\d{1,3}\$/", (string) $ip_address);
	if ( $ipv6 != 0 ) {
	   $ip_address = substr((string) $ip_address, 0, strrpos((string) $ip_address, ":")+1).'xxx (IPv6)';
	} elseif ( $ipv4 != 0 ) {
	   $ip_address = substr((string) $ip_address, 0, strrpos((string) $ip_address, ".")+1).'xxx (IPv4)';
	}else {
	   echo $ip_address = "unknown";
	}
	
	//入力データの錯誤チェック
	if( ($room_name == '') || ($room_comment == '') || !ctype_digit((string) $max_user) )
	{
		global $lang_room_fail_input;
		ShowHtmlFail($lang_room_fail_input);
		return;
	}
	
	//超過允許的房間數上限
	$result = $db->query("select count(*) from room where room_no > 1 AND (status='waiting' OR status='playing') AND game_option NOT LIKE '%ischat%'");
	$room_count = $db->result($result,0); //取得募集中或是遊玩中的房間數
	global $sys_create_room_max;
	if($room_count['0']>=$sys_create_room_max) {
		global $lang_create_room_disable_maxroom_f,$lang_create_room_disable_maxroom_b;
		ShowHtmlFail("<li>$lang_create_room_disable_maxroom_f$sys_create_room_max$lang_create_room_disable_maxroom_b");
		return;
	}
	$db->free_result($result);
	
	//聊天村只能一個
	$result = $db->query("select count(*) from room where room_no > 1 AND (status='waiting' OR status='playing') AND game_option LIKE '%ischat%'");
	$room_count = $db->result($result,0); //取得募集中或是遊玩中的聊天村
	global $sys_create_room_max;
	if($game_option_chats == 'ischat' && $room_count['0'] >= 1) {
		ShowHtmlFail("<li>正在進行的聊天村只能一個</li>");
		return;
	}
	$db->free_result($result);

	//禁止文字チェック
	//入力データの錯誤チェック
	if( strstr((string) $room_name,"'") || strstr((string) $room_comment,"'") || strstr((string) $room_name,"\\") || strstr((string) $room_comment,"\\") )
	{
		echo '<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css">
				</head><body bgcolor=aliceblue><br /><br />';
		echo "　　　　半形single quotation ( ' )<br />　　　　半形圓圈符號 ( \\ ) 無法使用其他特殊文字</body></html>";
		return;
	}
	
	//禁止宣傳
	if(strstr((string) $room_name,"jigokutushin.net") || strstr((string) $room_comment,"jigokutushin.net"))
	{
		echo '<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css">
				</head><body bgcolor=aliceblue><br /><br />';
		echo "失敗</body></html>";
		return;
	}
	
	//聊天模式
	if ($game_option_chats == 'ischat') {
		$max_user = '50';
	}
	
	if($game_option_real_time == 'real_time')
	{
		//夜の制限時間
		
		//制限時間が0から99以内の数字かチェック
		if( ($game_option_real_time_day != '') && ( $game_option_real_time_night != '') &&
												!preg_match("/[^0-9]/",(string) $game_option_real_time_day) &&
												!preg_match("/[^0-9]/",(string) $game_option_real_time_night) &&
												($game_option_real_time_day > 0) && ($game_option_real_time_day < 99) &&
												($game_option_real_time_night > 0) && ($game_option_real_time_night < 99) )
		{
			$real_time_set_str = "real_time:" . $game_option_real_time_day . ":" . $game_option_real_time_night;
		}
		else
		{
			echo '<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue><br /><br />';
			echo '<center><table border=0><tr><td><fieldset style="background:#fcbccc;border-color:#33336;width:500px;">
						<legend><strong>發生了錯誤</strong></legend>';
			echo "<label>請確認以下項目<br /> 　・限制時間的白、夜的時間是否正確<br /></label></fieldset>
						　</td></tr></table></center></body></html>";
			return;
		}
	}

	//game_option_gm
	if($game_option_manager_trip != '') {
		if($game_option_manager_trip_enc != 'enctrip') {
			$game_option_manager_trip = tripping($game_option_manager_trip);
		}
		
		$trip_n1 = $db->query("select count(*) from user_entry where trip = '$game_option_manager_trip' and user_no > '0' and role != 'none' and gover = '1'");
		$trip_count = $db->result($trip_n1,0);
		if($trip_count) {
			if ($game_option_gm != '') {
       // AS_GM
       if ($trip_count < ($triptonum *10)) 
   				{
   					echo '<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
   					echo "　　　　・Trip遊戲次數少於 ".($triptonum *10)." 不能成為GM。</body></html>";
   					return;
   				}
   } elseif ($trip_count < ($triptonum *5)) {
       echo '<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
       echo "　　　　・Trip遊戲次數少於 ".($triptonum *5)." 不能成為村長。</body></html>";
       return;
   }
		} else {
			echo '<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
			echo "　　　　・Trip資料查詢出錯</body></html>";
			return;
		}
	}
	
	if ($option_role_poison == 'cat') {
		$dellook = 0;
	}
	
	if ($game_option_trip) {
		if (!ctype_digit((string) $game_option_trip_countsum) || !$game_option_trip_countsum || $game_option_trip_countsum > 99) {
			$game_option_trip_countsum = 0;
		}
		$game_option_trip = $game_option_trip.":".$game_option_trip_countsum;
	}

	$option_role = $option_role_decide ." ". $option_role_authority ." ". $option_role_poison ." ". $option_role_foxs ." ".$option_wfbig_poison." ".$option_role_lovers." ".$option_more_wolf.' '.$option_role_comlover.' '.$option_role_mytho.' '.$option_role_owlman.' '.$option_role_noble.' '.$option_role_pengu.' '.$option_role_spy;
	$game_option = $game_option_wish_role . " ".$game_option_dummy_boy ." ". $game_option_open_vote
					 ." ". $real_time_set_str." ". $game_option_comm_out." ". $game_option_vote_me." ". $game_option_trip." ".$game_option_will.' '.($game_option_manager_trip==""?"":"gm:".$game_option_manager_trip).' '.$game_option_gm.' '.$game_option_guest.' '.$game_option_chats.' '.$game_option_votedisplay.' '.$game_option_cust_dummy.' '.$game_option_dummy_autolw;


	/*
	echo '<html><head><body>test:';
	echo $room_create_message_str;
	echo '</body></html>';
	return;
	*/
	
	
	if( $db->begin_transaction() ) //テーブルをロック
	{
		$all_query_ok = true;
	//	$result = $db->query('select room_no from room order by room_no DESC'); //降順にルームNoを取得
	//	$room_no_array = $db->fetch_array($result); //一行目(最も大きなNo)を取得
	//	$room_no = '$room_no'_array['room_no'] + 1;       //ルームNoの一番大きな値に1を足す


		//$room_name = str_replace("\\","\\\\",$room_name);
		$room_name = str_replace("&","&amp;",(string) $room_name);
		$room_name = str_replace("<","&lt;",$room_name);
		$room_name = str_replace(">","&gt;",$room_name);
		//$room_name = str_replace("'","\\'",$room_name);

		//$room_comment = str_replace("\\","\\\\",$room_comment);
		$room_comment = str_replace("&","&amp;",(string) $room_comment);
		$room_comment = str_replace("<","&lt;",$room_comment);
		$room_comment = str_replace(">","&gt;",$room_comment);
		//$room_comment = str_replace("'","\\'",$room_comment);


		$time = time();  //現在時刻、GMTとの時差を足す

		if($dellook === NULL) {
			//$dellook = 1;
		}

		//登錄
		$db->query("insert into room (room_name,room_comment,game_option,option_role,max_user,status,
																						date,day_night,last_updated,dellook)
					values ('$room_name','$room_comment','$game_option','$option_role','$max_user','waiting',
																								0,'beforegame','$time','$dellook')") ? null : $all_query_ok = false;
		$room_no = $db->insert_id();

		//$opr_tz = $time_zone;
		$opr_tz = 8; // 寫死
		//$time = time() + $opr_tz*3600; 
		$time = time(); 
		$create_date = date('Y/m/d H:i:s', $time);
		$room_create_message_str = msgimg($msg_room_image).'村莊建立於'.$create_date.'，來自'.$ip_address;
		$db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time) values ($room_no,0,'beforegame system','system','$time','$room_create_message_str','0')") ? null : $all_query_ok = false;

		//替身君使用
		if( strstr($game_option,"dummy_boy") )
		{
			if(strstr($game_option, "cust_dummy")) {
				//$dummy_lw = str_replace("\\","",$dummy_lw);
				//$dummy_lw = str_replace("&","&amp;",$dummy_lw);
				//$dummy_lw = str_replace("<","&lt;",$dummy_lw);
				//$dummy_lw = str_replace(">","&gt;",$dummy_lw);
				//$dummy_lw = str_replace("'","\\'",$dummy_lw);

				$dummy_name = htmlspecialchars((string) $dummy_name);
				$dummy_name = str_replace("'","&#39;",$dummy_name);
				$dummy_lw = htmlspecialchars((string) $dummy_lw);
				$dummy_lw = str_replace("'","&#39;",$dummy_lw);

				$lastws = $dummy_lw;
				if (mb_strlen($lastws, 'UTF-8') > 1024) {
					$lastws = mb_substr($lastws,0,1024,"UTF-8")."...";
				}
			} else {
				$dummy_name = '伊藤誠';
				require_once __DIR__ . '/dummy.php';
			}


			$db->query("insert into user_entry(room_no,user_no,uname,handle_name,trip,icon_no,sex,password,live,last_words,ip_address)
						values($room_no,1,'dummy_boy','$dummy_name','$tripkey','$dummy_boy_imgid','male','$system_password','live','$lastws','')") ? null : $all_query_ok = false;


			//投票しました通知だけ(投票自体はせず、集計時に自動的に+1する)
			//$re = $db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			//										values($room_no,0,'beforegame system','dummy_boy','$time','GAMESTART_DO','0')");
		}

//		$res2 = $db->query("commit"); //一応コミット

		if ($all_query_ok) {
			$db->commit();
		} else {
			$db->rollback();
		}

		if($all_query_ok) //建立成功
		{
			//HTML出力
			//header("Location:game_view.php?room_no=$room_no");
			echo '<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css"><meta http-equiv=refresh content="1;URL=game_view.php?room_no='.$room_no.'">
					</head><body bgcolor=aliceblue><br /><br />';
			echo "　　　　・$room_name 村建立完成，繼續進入，";
			echo "畫面切換中<a href=\"game_view.php?room_no=$room_no\">按我繼續</a></body></html>";
		}
		else
		{
			//HTML出力
			echo '<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css">
					</head><body bgcolor=aliceblue><br /><br />';
			echo "　　　　・伺服器忙碌中。請重新登錄。</body></html>";
		}
		//ファイルロック解除
		//$db->query("unlock tables");
	}
	else
	{
		print('<html><head><title>村建立</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue><br /><br />');
		echo "　　　　・伺服器忙碌中。麻煩重新登錄。</body></html>";
	}

}

//--------------------------------------------------------------------------------------------------
//村(room)のwaitingとplayingのリストを出力する
function ViewRoomList(): void
{
	global $playing_image,$waiting_image,$maxuser_image_array,$room_option_wish_role_image,$room_option_dummy_boy_image,
			$room_option_open_vote_image,$room_option_decide_image,$room_option_authority_image,$room_option_poison_image,
			$room_option_real_time_image,$room_option_betr_image,$room_option_rei_image,$room_option_conn_image,$db,$isold,
			$room_option_fosi_image,$room_option_foxs_image,$room_option_wfbig_image,$room_option_cat_image,$room_option_voteme_image,
			$room_option_trip_image,$room_option_will_image,$room_option_lovers_image,$room_option_gm_image,$game_option_chats,$room_option_guest_image;
	global $room_option_mytho_image, $room_option_owlman_image, $room_option_noconn_image,$room_option_pengu_image,$room_option_noble_image,$room_option_spy_image,$room_option_ischat_image;
		
	//ルームNo、ルーム名、コメント、最大人数、状態を取得
	$result = $db->query("select room_no,room_name,room_comment,game_option,option_role,max_user,status,dellook from room
										where status <> 'finished' order by room_no DESC ");
	if($result == NULL)
		return;
	
	
	while($row_data = $db->fetch_array($result) )
	{
		$room_no = $row_data['room_no'];
		$room_name = $row_data['room_name'];
		$room_comment = $row_data['room_comment'];
		$game_option = $row_data['game_option'];
		$option_role = $row_data['option_role'];
		$max_user = $row_data['max_user'];
		$status = $row_data['status'];
		$dellook = $row_data['dellook'];
		
		if($room_no == 1) {
			echo '</b></fieldset><br /></td></tr>';
			
			echo '<tr><td valign="top"><fieldset>';
			echo '<legend><b>電動櫻太郎專賣店（會員制）（？）</b></legend><b>';
		}
		
		switch($status) //状態による分岐
		{
			case('waiting'):
				$status_img = $waiting_image;
				break;
			case('playing'):
				$status_img = $playing_image;
				break;
		}
		
		$option_img_str = ''; //ゲームオプションの画像
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
		if (!strstr((string) $game_option,"ischat")) {
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
		if (!strstr((string) $game_option,"ischat")) {
			if(strstr((string) $game_option,"will"))
			{
				$option_img_str .= "<img src=\"$room_option_will_image\" border=0 width=16px height=16px title=\"允許遺書顯示\">";
			}
			if($max_user >= 10 && strstr((string) $option_role, 'spy')) {
				$option_img_str .= "<img src=\"$room_option_spy_image\" border=0 width=16px height=16px title=\"隔壁村莊的間諜\">";
			}
		}
		if(strstr((string) $game_option,"usr_guest"))
		{
			$option_img_str .= "<img src=\"$room_option_guest_image\" border=0 width=16px height=16px title=\"匿名遊戲\">";
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
				$option_img_str .= "<img src=\"$room_option_lovers_image\" border=0 width=16px height=16px title=\"相戀的兩人，隨機版\">";	

			} 
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
			
			if(strstr((string) $option_role, 'pengu')) 
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
		
		if(strstr((string) $game_option,"gm:"))
		{
			$gm_str = strstr((string) $game_option,"gm:");
			sscanf($gm_str, "gm:%s", $gmtrip);
			$max_user_str = '<a href="trip.php?go=trip&id='.$gmtrip.'" target="_new"><img src="'.$max_user_img.'" title="村長Trip: '.$gmtrip.'" border=0></a>';	
		} else 
			$max_user_str = "<img src=\"$max_user_img\" border=0>";
	
	
		//echo $option_img_str."&nbsp;".$max_user_str." </small>\r\n";


		//最大人数
		$max_user_img = $maxuser_image_array[$max_user];
		
		$room_no_str = "[" . $room_no . "番地]";
		$room_name_str = $room_name . "村";
		$room_comment = "～" . $room_comment . "～</a><br />" . $option_img_str;
		
		echo "<a href=\"login.php?room_no=$room_no\" style=\"color:#CC3300;\"> \r\n";
		echo "<img src=\"$status_img\" border=0><small> $room_no_str</small> $room_name_str<br /> \r\n";
		echo "<small style=\"margin-left:100px;\"><div align=right>$room_comment $max_user_str \r\n";
		echo "</div></small><br />";
	}
	$db->free_result($result);
}

//----------------------------------------------------------
//一定時間更新の無い村は廃村にする
function CheckDieRoom(): void
{
	global $room,$die_room_threshhold_time,$time_zone,$cachefile,$db,$isold;
	
	$res_no_finish_room = $db->query("select room_no,game_option,last_updated from room where status <> 'finished'");
	
//	$no_finish_room_count = $db->fetch_row($res_no_finish_room);
	
	$time = time();  //現在時刻、GMTとの時差を足す
	
	$roomisoff = false;
	while($no_finish_arr = $db->fetch_array($res_no_finish_room))
	{
	//	$no_finish_arr = $db->fetch_array($res_no_finish_room);
		
		$no_finish_room_no = $no_finish_arr['room_no'];
		$no_finish_last_updated = $no_finish_arr['last_updated'];
		$no_finish_game_option = $no_finish_arr['game_option'];
		
		$diff_time = $time - $no_finish_last_updated; //どれくらい的時間が経っているか
		$roomisoff = false;
		
		if (strstr((string) $no_finish_game_option,'gm:')) {
      $die_room_threshhold_time_add = 1;
      $trip_array = explode("gm:",(string) $no_finish_game_option);
      if ($trip_array[1] != "") {
   				$trip_array = explode(" ",$trip_array[1]);
   				if ($trip_array[0] != "") {
   					$res_no_finish_room2 = $db->query("select count(*) from user_entry where room_no = '$no_finish_room_no' AND user_no > '0' AND trip LIKE '".$trip_array[0]."' AND live = 'live';");
   					if (strstr((string) $no_finish_game_option,'ischat') && $db->result($res_no_finish_room2,0) > 0) {
   						$die_room_threshhold_time_add = 72;
   					} elseif ($db->result($res_no_finish_room2,0) > 0) {
   						$die_room_threshhold_time_add = 3;
   					}
   				}
   			}
      if( $diff_time > ($die_room_threshhold_time * $die_room_threshhold_time_add) ) {//閾値を超えていたら廃村にする
   				$roomisoff = true;
   			}
  } elseif ($diff_time > $die_room_threshhold_time) {
      //閾値を超えていたら廃村にする
      $roomisoff = true;
  }
		
		if ($roomisoff) {
			$db->query("update room set status = 'finished' , day_night = 'aftergame' where room_no = $no_finish_room_no");
			$db->query("DELETE from vote where room_no = '$no_finish_room_no';");
			//刪除快取
			if ($cachefile) {
				unlink('tmp/messH_'.$no_finish_room_no.'.php');
			}
		}

	}
	$db->free_result($res_no_finish_room);
}

//----------------------------------------------------------
//終了した部屋のSessionIDのデータをクリアする
function ClearFinishedRoomSessionID(): void
{
	global $room,$clear_user_session_id,$time_zone,$db,$isold;
	
	$res_finish_room = $db->query("select room.room_no,last_updated from room,user_entry
								where room.room_no = user_entry.room_no and user_entry.session_id IS NOT NULL group by room_no");
	
//	$finish_room_count = $db->fetch_row($res_finish_room);
	
	$time = time();  //現在時刻、GMTとの時差を足す
	
	while($finish_arr = $db->fetch_array($res_finish_room))
	{
	//	$finish_arr = $db->fetch_array($res_finish_room);
		
		$finish_room_no = $finish_arr['room_no'];
		$finish_last_updated = $finish_arr['last_updated'];
		
		$diff_time = $time - $finish_last_updated; //どれくらい的時間が経っているか
		
		
		if( $diff_time > $clear_user_session_id ) //閾値を超えていたらSessionIDをクリアする
			$db->query("update user_entry set session_id = NULL where room_no = $finish_room_no");

	}
	$db->free_result($res_finish_room);
}

//村(room)の修改

function ModifyRoom($room_name,$room_comment,$max_user,$dellook = 0): void
{
	global $system_password,$time_zone,$game_option_real_time,$game_option_real_time_day,$game_option_real_time_night,$option_role_pobe,$option_role_foxs,$option_role_decide,$option_role_authority,$option_role_poison,$game_option_wish_role,$game_option_dummy_boy,$game_option_open_vote,$game_option_comm_out,$db,$isold,$dummy_boy_imgid;
	global $sys_create_room_max,$lang_create_room_disable,$sys_create_room_enabled,$option_wfbig_poison,$option_more_wolf,$game_option_vote_me,$game_option_trip,$tripkey,$game_option_will,$game_option_gm,$game_option_guest,$game_option_manager_trip,$game_option_manager_trip_enc,$option_role_lovers,$option_role_comlover,$game_option_trip_countsum, $room_no;
	global $option_role_mytho, $option_role_owlman,$game_option_chats;
	global $triptonum,$game_option_votedisplay,$game_option_cust_dummy,$dummy_lw,$dummy_name,$game_option_dummy_autolw, $option_role_noble, $option_role_pengu;
	
	//global $msgimg;
	global $msg_room_image;
	global $option_role_spy;
		
	//入力データの錯誤チェック
	if( ($room_name == '') || ($room_comment == '') || !ctype_digit((string) $max_user) )
	{
		global $lang_room_fail_input;
		ShowHtmlFail($lang_room_fail_input);
		return;
	}

	//禁止文字チェック
	//入力データの錯誤チェック
	if( strstr((string) $room_name,"'") || strstr((string) $room_comment,"'") || strstr((string) $room_name,"\\") || strstr((string) $room_comment,"\\") )
	{
		echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css">
				</head><body bgcolor=aliceblue><br /><br />';
		echo "　　　　半形single quotation ( ' )<br />　　　　半形圓圈符號 ( \\ ) 無法使用其他特殊文字</body></html>";
		return;
	}
	
	//禁止宣傳
	if(strstr((string) $room_name,"jigokutushin.net") || strstr((string) $room_comment,"jigokutushin.net"))
	{
		echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css">
				</head><body bgcolor=aliceblue><br /><br />';
		echo "失敗</body></html>";
		return;
	}
	
	//聊天模式
	if ($game_option_chats == 'ischat') {
		$max_user = '50';
	}
	
	//聊天村只能一個
	$result = $db->query("select count(*) from room where (status='waiting' OR status='playing') AND room_no > 1 AND game_option LIKE '%ischat%' AND room_no != '$room_no'");
	$room_count = $db->result($result,0); //取得募集中或是遊玩中的聊天村
	global $sys_create_room_max;
	if($game_option_chats == 'ischat' && $room_count['0'] >= 1) {
		ShowHtmlFail("<li>正在進行的聊天村只能一個</li>");
		return;
	}
	$db->free_result($result);
	
	if($game_option_real_time == 'real_time')
	{
		//夜の制限時間
		
		//制限時間が0から99以内の数字かチェック
		if( ($game_option_real_time_day != '') && ( $game_option_real_time_night != '') &&
												!preg_match("/[^0-9]/",(string) $game_option_real_time_day) &&
												!preg_match("/[^0-9]/",(string) $game_option_real_time_night) &&
												($game_option_real_time_day > 0) && ($game_option_real_time_day < 99) &&
												($game_option_real_time_night > 0) && ($game_option_real_time_night < 99) )
		{
			$real_time_set_str = "real_time:" . $game_option_real_time_day . ":" . $game_option_real_time_night;
		}
		else
		{
			echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue><br /><br />';
			echo '<center><table border=0><tr><td><fieldset style="background:#fcbccc;border-color:#33336;width:500px;">
						<legend><strong>發生了錯誤</strong></legend>';
			echo "<label>請確認以下項目<br /> 　・限制時間的白、夜的時間是否正確<br /></label></fieldset>
						　</td></tr></table></center></body></html>";
			return;
		}
	}

	//game_option_gm
	if($game_option_manager_trip != '') {
		if($game_option_manager_trip_enc != 'enctrip') {
			$game_option_manager_trip = tripping($game_option_manager_trip);
		}
		
		$trip_n1 = $db->query("select count(*) from user_entry where trip = '$game_option_manager_trip' and user_no > '0' and role != 'none' and gover = '1'");
		$trip_count = $db->result($trip_n1,0);
		if($trip_count) {
			if ($game_option_gm != '') {
       // AS_GM
       if ($trip_count < ($triptonum *10)) 
   				{
   					echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
   					echo "　　　　・Trip遊戲次數少於 ".($triptonum *10)." 不能成為GM。</body></html>";
   					return;
   				}
   } elseif ($trip_count < ($triptonum *5)) {
       echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
       echo "　　　　・Trip遊戲次數少於 ".($triptonum *5)." 不能成為村長。</body></html>";
       return;
   }
		} else {
			echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
			echo "　　　　・Trip資料查詢出錯</body></html>";
			return;
		}
	}
	
	$res_max_user_orig = $db->query("select max_user, game_option from room where room_no = '$room_no';");
	$max_user_orig = $db->result($res_max_user_orig,0);
	$game_option_orig = $db->result($res_max_user_orig,0,1);
	
	$res_user = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and user_no <= $max_user_orig");
	$user_count = $db->result($res_user,0);
	
	$res_gm = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no = " . ($max_user_orig+1));
	$have_gm = $db->result($res_gm,0);

	$res_rm = $db->query("select count(uname) from user_entry where trip = '$game_option_manager_trip' and room_no = '$room_no' and user_no > '0'");
	$have_rm = $db->result($res_rm,0);


	
	if (($user_count-$have_gm) > $max_user) {
			echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
			echo "　　　　・村莊人數不能大於欲修改的人數</body></html>";
			return;
	}
		
	// =====================
	if ($option_role_poison == 'cat') {
		$dellook = 0;
	}
	
	if ($game_option_trip) {
		if (!ctype_digit((string) $game_option_trip_countsum) || !$game_option_trip_countsum || $game_option_trip_countsum > 99) {
			$game_option_trip_countsum = 0;
		}
		$game_option_trip = $game_option_trip.":".$game_option_trip_countsum;
	}

	$option_role = $option_role_decide ." ". $option_role_authority ." ". $option_role_poison ." ". $option_role_foxs ." ".$option_wfbig_poison." ".$option_role_lovers." ".$option_more_wolf.' '.$option_role_comlover.' '.$option_role_mytho.' '.$option_role_owlman.' '.$option_role_noble.' '.$option_role_pengu.' '.$option_role_spy;
	$game_option = $game_option_wish_role . " ".$game_option_dummy_boy ." ". $game_option_open_vote
					 ." ". $real_time_set_str." ". $game_option_comm_out." ". $game_option_vote_me." ". $game_option_trip." ".$game_option_will.' '.($game_option_manager_trip==""?"":"gm:".$game_option_manager_trip).' '.$game_option_gm.' '.$game_option_guest.' '.$game_option_chats.' '.$game_option_votedisplay.' '.$game_option_cust_dummy.' '.$game_option_dummy_autolw;


	if( $db->begin_transaction() ) //テーブルをロック
	{		
		$all_query_ok = true;
		$room_name = str_replace("&","&amp;",(string) $room_name);
		$room_name = str_replace("<","&lt;",$room_name);
		$room_name = str_replace(">","&gt;",$room_name);

		$room_comment = str_replace("&","&amp;",(string) $room_comment);
		$room_comment = str_replace("<","&lt;",$room_comment);
		$room_comment = str_replace(">","&gt;",$room_comment);


		$time = time();  //現在時刻、GMTとの時差を足す

		if($dellook === NULL) {
			//$dellook = 1;
		}

		// 調出GM
		// 把GM調整到正常的位子上
		if (strstr($game_option, 'as_gm')) {
      // 若本來有，更新到新的人數分
      // 沒有，則扔到GM位上
      if (strstr((string) $game_option_orig, 'as_gm')) {
          $db->query("update user_entry set user_no = '" . ($max_user+1) . "' where user_no = '". ($max_user_orig+1)."' and room_no = '$room_no'") ? null : $all_query_ok = false;
      } elseif ($have_rm) {
          $res_gm_orig_pos = $db->query("select user_no from user_entry where trip = '$game_option_manager_trip' and room_no = '$room_no' and user_no > '0'");
          $gm_orig_pos = $db->result($res_gm_orig_pos,0);
          // 把所有本來GM位後面的往前挪
          $db->query("update user_entry set user_no = user_no - 1 where user_no > $gm_orig_pos and room_no = '$room_no' and user_no > '0'") ? null : $all_query_ok = false;
          $db->query("update user_entry set user_no = '" . ($max_user+1) . "' where trip = '". $game_option_manager_trip . "' and room_no = '$room_no'") ? null : $all_query_ok = false;
      }
  } elseif (strstr((string) $game_option_orig, 'as_gm')) {
      if($user_count+$have_gm > $max_user) {
   				echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
   				echo "　　　　・村莊滿員時，不能將GM拔除職位</body></html>";
   				return;
   			} else {
   				$db->query("update user_entry set user_no = " . ($user_count+1) . " where trip = '". $game_option_manager_trip . "' and room_no = '$room_no'") ? null : $all_query_ok = false;
   			}
  }

		$db->query("update room set room_name = '$room_name', room_comment = '$room_comment',
											game_option = '$game_option',option_role = '$option_role',
											max_user = '$max_user', last_updated = '$time', dellook = '$dellook'
										where room_no = '$room_no'") ? null : $all_query_ok = false;

		//$opr_tz = $time_zone;
		$opr_tz = 8; // 寫死
		//$time = time() + $opr_tz*3600; 
		$time = time(); 
		$create_date = date('Y/m/d H:i:s', $time);

		$res_dun = $db->query("select uname from user_entry where room_no = '$room_no' AND user_no = '1';");
		$dun = $db->result($res_dun,0);

		//替身君使用
		if (strstr($game_option,"dummy_boy")) {
      if(strstr($game_option, "cust_dummy")) 
   			{				
   				$dummy_name = htmlspecialchars((string) $dummy_name);
   				$dummy_name = str_replace("'","&#39;",$dummy_name);
   				$dummy_lw = htmlspecialchars((string) $dummy_lw);
   				$dummy_lw = str_replace("'","&#39;",$dummy_lw);

   				$lastws = $dummy_lw;
   				if (mb_strlen($lastws, 'UTF-8') > 1024) {
   					$lastws = mb_substr($lastws,0,1021,"UTF-8")."...";
   				}
   			} else {
   				$dummy_name = '伊藤誠';
   				require_once __DIR__ . '/dummy.php';
   			}
      // 本來沒渣的話就把渣擠進去，有渣的話更新資料
      if($dun != 'dummy_boy') {
   				if($user_count >= $max_user) {
   					echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue>';
   					echo "　　　　・村莊滿員時，不能加入替身君</body></html>";
   					return;

   				}
   				else {
   					// 多人就踢滑壘
   					// gm在場時不踢gm BUT 村長照踢
   					if(strstr($game_option, 'as_gm'))
   						$db->query("update user_entry set user_no = -1, live='dead', session_id='' where room_no = '$room_no' and user_no > ".($max_user-1)." and trip <> '$game_option_manager_trip'") ? null : $all_query_ok = false;
   					else
   						$db->query("update user_entry set user_no = -1, live='dead', session_id='' where room_no = '$room_no' and user_no > ".($max_user-1)) ? null : $all_query_ok = false;

   					// 剩下的除GM都+1		
   					$db->query("update user_entry set user_no = user_no +1 where room_no = '$room_no' and user_no > '0' and user_no <= $max_user;") ? null : $all_query_ok = false;

   					// 放渣
   					$db->query("insert into user_entry(room_no,user_no,uname,handle_name,trip,icon_no,sex,password,live,last_words,ip_address)
							values($room_no,1,'dummy_boy','$dummy_name','$tripkey','$dummy_boy_imgid','male','$system_password','live','$lastws','')") ? null : $all_query_ok = false;
   				}
   			} else {
   				$db->query("update user_entry set handle_name = '$dummy_name', last_words = '$lastws' where room_no = '$room_no' and user_no = '1';") ? null : $all_query_ok = false;
   			}
  } elseif ($dun == 'dummy_boy') {
      // 砍掉渣
      $db->query("delete from user_entry where room_no = '$room_no' and user_no = '1';") ? null : $all_query_ok = false;
      // 剩下的除GM都-1
      $db->query("update user_entry set user_no = user_no -1 where room_no = '$room_no' and user_no > 1 and user_no <= $max_user;") ? null : $all_query_ok = false;
  }

		if($all_query_ok) //修改成功
		{
			$room_create_message_str = msgimg($msg_room_image).'村莊已修改於'.$create_date;
			$db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time) values ($room_no,0,'beforegame system','system','$time','$room_create_message_str','0')") ? null : $all_query_ok = false;

			$db->query("delete from vote where room_no = '$room_no'") ? null : $all_query_ok = false; //今までの投票を全部削除

			if ($all_query_ok) {
				$db->commit();
			} else {
				$db->rollback();
			}

			$time++; //出て行ったメッセージより後に表示されるように
			$reset_vote_str = msgimg($msg_sys_image).'＜投票重新開始 請盡速重新投票＞';
			$res = $db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
						values ($room_no,0,'$day_night system','system','$time','$reset_vote_str','0')") ? null : $all_query_ok = false;


			echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"><meta http-equiv=refresh content="1;URL="javascript:window.close();"">
					</head><body bgcolor=aliceblue><br /><br />';
			echo "　　　　・$room_name 村修改完成<script language='javascript'>window.close();</script></body></html>";
			return;

		}
		else
		{
			$db->rollback();

			//HTML出力
			echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css">
					</head><body bgcolor=aliceblue><br /><br />';
			echo "　　　　・伺服器忙碌中。請重新登錄。</body></html>";
		}
		//ファイルロック解除
		//$db->query("unlock tables");
	}
	else
	{
		print('<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css"></head><body bgcolor=aliceblue><br /><br />');
		echo "　　　　・伺服器忙碌中。麻煩重新登錄。</body></html>";
	}

}


?>
