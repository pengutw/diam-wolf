<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/msgimg_setting.php';
require_once __DIR__ . '/setting.php';

//一頁顯示多少icon
$perpage = 49;

//MySQLに接続
if($db->connect_error())
{
	exit;
}
$command = empty($command) ? "" : $command;

switch($room_no)
{
	case(''):
		echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
		echo '<br /><br />';
		echo "　　　　錯誤：請確認村號碼是否正確<br />";
		echo "　　　　<a href=index.php style=\"color:blue;\">";
		echo "←返回</a></body></html>";
		break;
	default:
		if ($command == 'regist') {
      RegistUser($room_no,$uname,$handle_name,$_POST['icon_no'],
   				$password,$_POST['sex'],$role,$tripn);
  } elseif (ctype_digit((string) $_GET['iconpp'])) {
      $page = $_GET['iconpp'];
      $page = max(1, (int) $page);
      $start = ($page - 1) * $perpage;
      $sqlim = "limit $start,$perpage";
      echo iconfunc($sqlim);
  } else {
				$counts = $db->query("select count(*) from user_icon where icon_no != $dummy_boy_imgid  and look = '1'");
				$num = $db->result($counts,0);
				$realpages = @ceil($num / $perpage);
				RegistUserOutputHTML($room_no,$realpages);
			}
		break;
}

//MySQLとの接続を閉じる
$db->close();


//----------------------------------------------------------
//用戶を登錄する(´･ω･`)
function RegistUser($room_no,$uname,$handle_name,$icon_no,$password,$sex,$role,$tripn = ''): void
{
	global $regist_one_ip_address,$time_zone,$db,$isold,$dummy_boy_imgid;
	
	global $msg_ampm_image, $msg_guard_image, $msg_vote_image, $msg_kill_image, $msg_sys_image, $msg_mage_image, 
	$msg_room_image, $msg_wolf_image, $msg_human_image, $msg_fox_image, $msg_fosi_image, $msg_cat_image, 
	$msg_sudden_image, $msg_gm_image, $msg_rm_image, $msg_lover_image;
	
	global $demota;
	
	$uname = trim((string) $uname);
	$tripn = trim((string) $tripn);
	$handle_name = trim((string) $handle_name);
	$password = trim((string) $password);
	
	$uname = str_replace("◆","◇",$uname);
	$uname = explode("#", $uname);
	$uname = $uname[0];
	if ($tripn !== '' && $tripn !== '0') {
		$trip = tripping($tripn);
	}
	
	//記入漏れチェック
	if( ($uname == '') || ($handle_name == '') || ($icon_no == '') || ($password == '') || ($sex == '')
																											|| ($role == ''))
	{
		echo $icon_no.'<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
		echo "　　・有地方遺漏<br />　　・請重新輸入<br /></body></html>";
		return;
	}

	if(!preg_match("/^\\w/",$uname)) {
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">帳號只允許英文或數字的帳號';
		return;
	}
	
	if(strlen(str_replace("0",'',$uname)) <= 0) {
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">帳號不能都是0';
		return;
	}

	//禁止文字チェック
	//入力データの錯誤チェック
	if( strstr($uname,"'") || strstr($handle_name,"'") || strstr($password,"'")
			|| strstr($uname,"\\") || strstr($handle_name,"\\") || strstr($password,"\\")
			|| strstr($uname, ":;:") || strstr($handle_name, ":;:"))
	{
		echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>用戶登錄</title>
				</head><body bgcolor=aliceblue><br /><br />';
		echo "　　　　半形single quotation ( ' )<br />　　　　半形圓圈符號 ( \\ ) 無法使用其他特殊文字</body></html>";
		return;
	}
	
	/*
	if ($handle_name == '替身君') {
		$handle_name = '吊死我';
	}
	*/
	
	$handle_name = str_replace("\""," ",$handle_name);
	
	if (strlen(trim($handle_name)) <= 0) {
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">錯誤';
		return;
	}
	
	if (strlen($handle_name) > 21) {
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">名字長度限制21位元組';
		return;
	}
	//$uname = str_replace("\\","\\\\",$uname);
	$uname = str_replace("&","&amp;",$uname);
	$uname = str_replace("\t"," ",$uname);
	$uname = str_replace("<","&lt;",$uname);
	$uname = str_replace(">","&gt;",$uname);
	$uname = str_replace("\""," ",$uname);
	//$uname = str_replace("'","\\'",$uname);
	
	$handle_name = namenohtml($handle_name);
	
	
	//$password = str_replace("\\","\\\\",$password);
	//$password = str_replace("'","\\'",$password);
	
	
	//記入漏れチェック
	if( ($uname == 'dummy_boy') || ($uname == 'system') || ($handle_name == '替身君') || ($handle_name == '系統') || ($handle_name == 'system') )
	{
		echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
		echo "　　以下名稱不能使用<br />";
		echo "　　　　帳號：dummy_boy<br />　　　　帳號：system<br />";
		echo "　　　　村民的名稱：替身君<br />　　　　村民的名稱：系統<br /></body></html>";
		return;
	}
	
	foreach($demota as $demotum) {
		if(strstr($handle_name, (string) $demotum)) {
			echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
			echo "　　・不能包含已定義的表情符號作為村民名稱<br />　　・請嘗試別的名稱<br /></body></html>";
			return;		
		}
	}
	
	//項目被りチェック
	$res_exists_chk = $db->query("select count(uname) from user_entry where room_no = '$room_no'
													and (uname = '$uname' or handle_name = '$handle_name') and user_no > '0'");
	if( $db->result($res_exists_chk,0) != 0)
	{
		echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
		echo "　　・帳號或村民名稱已經被登錄<br />　　・請嘗試別的名稱<br /></body></html>";
		return;
	}
	
	//項目被りチェック２(キックされた人と同じ帳號はダメ)
	$res_exists_chk = $db->query("select count(uname) from user_entry where room_no = '$room_no'
																						and uname = '$uname'");
	if( $db->result($res_exists_chk,0) != 0)
	{
		echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
		echo "　　・這個帳號的人已經自刪過了。<br />　　・請嘗試別的名稱<br /></body></html>";
		return;
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
	
	//if($ip_address == '210.0.210.82' || $ip_address == '113.252.166.41' || $ip_address == '122.117.44.113') {
	//	echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
		//echo "　　・被禁止的IP位址<br /></body></html>";
	//	echo "　　・系統忙碌中，請稍候再試<br /></body></html>";
	//	return;
	//}
	
	if($regist_one_ip_address == true) //IPアドレスチェック
	{
		$res_ip_chk = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and ip_address = '$ip_address'");
		
		if( $db->result($res_ip_chk,0) != 0)
		{
			echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
			echo "　　・同一IP不能多重登錄<br /></body></html>";
			return;
		}
	}
	
	//if($trip == 'DRhODNiN' || strstr($uname, 'lime200')) return;
	
//	if($trip == 'WJjOTA1N') {
//		echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
//		echo "　　・被禁止的Trip<br /></body></html>";
//		return;
//	}
	
	
	$res_trip_chk = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and trip = '$trip'");

	if( $db->result($res_trip_chk,0) != 0 && $trip != '')
	{
		echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
		echo "　　・同一Trip不能多重登錄<br /></body></html>";
		return;
	}
	
	
	//改行を\nに統一

	
	if($db->begin_transaction()) {
		$all_query_ok = true;

		//錯誤フラグの初期化
		$error_flag = false;

		//クッキーの削除
		setcookie ("day_night", "", ['expires' => time() - 3600]);
		setcookie ("vote_times", "", ['expires' => time() - 3600]);
		setcookie ("objection", "", ['expires' => time() - 3600]);

		//DBから最大人数を取得
		$res_room_stat = $db->query("select day_night,status,max_user,game_option from room where room_no = '$room_no'");
		$room_stat_arr = $db->fetch_array($res_room_stat);
		$day_night = $room_stat_arr['day_night'];
		$status = $room_stat_arr['status'];
		$max_user = $room_stat_arr['max_user'];
		$game_option = $room_stat_arr['game_option'];
		$db->free_result($res_room_stat);


		//DBから用戶Noを降順に取得
		$res_user_no = $db->query("select user_no from user_entry where room_no = '$room_no' and user_no > '0'
																										order by user_no DESC");
		if($db->num_rows($res_user_no) == 0)
			$user_no = 0;
		else {
			$user_no_arr = $db->fetch_array($res_user_no);
			$user_no = (int)$user_no_arr['user_no'];

			if ($db->num_rows($res_user_no) == 1) {
       $user_no = $user_no > $max_user ? 0 : 1;
   } elseif ($user_no > $max_user) {
       // GM exists, get next one
       $user_no_arr = $db->fetch_array($res_user_no);
       $user_no = (int)$user_no_arr['user_no'];
   }
		}
		//$user_no = (int)$user_no_arr['user_no'] + 1; //最も大きいNo + 1
		$user_no += 1;
		$db->free_result($res_user_no);

		if ($icon_no != 'trip') {
			//$ricons = $db->query("select count(*) from user_icon where icon_no = '$icon_no';");
			//if ($db->result($ricons,0) != 1) {
			//	exit;
			//}
		}

		//驗證TRIP
		if (!empty($tripn)) {
			if (!(preg_match("/[a-zA-Z]/",$tripn) && preg_match("/\\d/",$tripn))) {
                $error_flag = true;
                echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
                echo "　　　　・Trip必須包含英文和數字，不支援英數以外字元。<br /></body></html>";
            }
			$query = $db->query("select * from user_trip where trip = '$trip'");
			$tripdb = $db->fetch_array($query);
			if ($tripdb['ban'] > 0) {
				$error_flag = true;
				echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
				echo "　　　　・此Trip凍結，無法使用。<br /></body></html>";
			}
			if ($tripdb['trip'] == '') {
				$error_flag = true;
				echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
				echo "　　　　・找不到此Trip 請先登記。<br /></body></html>";
			}
			if ($icon_no == 'trip' && $tripdb['size'] == '') {
				$error_flag = true;
				echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
				echo "　　　　・此Trip沒有個人頭像。<br /></body></html>";
			}
		}
		if ($icon_no == 'trip' && empty($tripn)) {
			$error_flag = true;
			echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
			echo "　　　　・此Trip沒有個人頭像。<br /></body></html>";
		}
		if (strstr((string) $game_option,"istrip")) {
			if (empty($trip)) {
				$error_flag = true;
				echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
				echo "　　　　・本村註冊需要Trip<br /></body></html>";
			}
			$trip_count_str = strstr((string) $game_option,"istrip");
			sscanf($trip_count_str,"istrip:%d",$tripinroom);
			$tripinrooms1 = $db->query("select count(*) from user_entry where trip = '$trip' and user_no > '0' and role != 'none' and gover = '1'");
			if ($db->result($tripinrooms1,0) < $tripinroom) {
				$error_flag = true;
				echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
				echo "　　　　・Trip遊戲次數少於 ".$tripinroom." 不能加入。<br /></body></html>";
			}
		}

		$gmtrip_str = strstr((string) $game_option,"gm:");
		if($gmtrip_str != '') {
			sscanf($gmtrip_str, "gm:%s", $gmtrip);
		}

		$as_gm = strstr((string) $game_option, "as_gm");

		$is_gm = false;
		if($gmtrip == $trip && $gmtrip != '' && $as_gm) { 
			// this person is GM
			$user_no = $max_user+1;
			$is_gm = true;
		}


		//定員オーバーしているとき
		if( ($user_no > $max_user && !$is_gm) || ($day_night != 'beforegame') || ($status != 'waiting') )
		{ 
			$error_flag = true;
			echo '<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>住民登錄</title></head><body bgcolor=aliceblue>';
			echo "　　　　・村民已滿或遊戲已經開始</body></html>";
		}

		//錯誤が無ければ登錄
		if($error_flag == false)
		{

			//Sessionの開始
			session_start();
			$session_id = '';

			do //DBに登錄されているSessionIDと被らないようにする
			{
				session_regenerate_id();
				$session_id = session_id();

				$result = $db->query("select count(room_no) from user_entry,admin_manage
									where user_entry.session_id = '$session_id' or admin_manage.session_id = '$session_id'");
			}while($db->result($result,0) != 0);

			//DBに用戶データ登錄
			if ($tripdb['trip'] && $tripdb['icon'] && $tripdb['size'] && $icon_no == 'trip') {
				$icon_no = $dummy_boy_imgid;
			}
			$db->query("insert into user_entry(room_no,user_no,uname,handle_name,trip,icon_no,sex,password
															,role,live,session_id,last_words,ip_address,last_load_day_night)
					values ($room_no,$user_no,'$uname','$handle_name','$trip',$icon_no,'$sex','$password','$role','live'
																			,'$session_id','','$ip_address','beforegame')") ? null : $all_query_ok = false;


			$time = time();  //現在時刻、GMTとの時差を足す

			//～さんが村にやってきました
			$user_regist_message_str = msgimg($msg_human_image)."$handle_name 來到村莊大廳";
			$db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
						values ($room_no,0,'beforegame system','system','$time','$user_regist_message_str','0')") ? null : $all_query_ok = false;

//			$db->query("commit"); //一応コミット
			//登錄が成功していて、今回の用戶が最後の用戶なら募集を終了する
			//if( $res_regist && ($user_no == $max_user) )
			//{
			//	$db->query("update room set status = 'playing' where room_no = '$room_no'");
			//}

			if ($all_query_ok) {
				$db->commit();
			} else {
				$db->rollback();
			}

			if( $all_query_ok )
			{
				$echosocket = "<script src=\"img/jquery-3.6.0.min.js\"></script>
							<script type=\"text/javascript\">
						var socket;
						function socketinit() {
							var host = \"wss://diam.ngct.net:8443/wss/".authcode("$room_no\tNOUSERID", "ENCODE")."\";
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
							window.location.href = \"game_frame.php?room_no=$room_no&auto_reload=0&play_sound=off\";
						}
						</script>";

				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
				echo "<title>村民登錄</title>";
				echo $echosocket."</head><body bgcolor=aliceblue onload=\"socketinit();\">";
				echo('<br /><br />');
				echo "　　・ $user_no 番目村民登錄完了、正在前往村子大廳";
				echo "畫面切換中<a href=\"game_frame.php?room_no=$room_no&auto_reload=0&play_sound=off\">按我繼續</a>";
				echo "</body></html>";
			}
			else
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><title>村民登錄</title>";
				echo '</head><body bgcolor=aliceblue><br /><br />';
				echo "　　・伺服器忙碌中<br />　　・請重新登錄";
				echo "</body></html>";
			}
		}

	//	ロック解除
		//$db->query("unlock tables");
	}
	else
	{
		print('<html><head><link rel="stylesheet" type="text/css" href="img/font.css"><title>村民登錄：錯誤</title></head><body bgcolor=aliceblue><br /><br />');
		echo "　　　　・伺服器忙碌中。麻煩重新登錄。</body></html>";
	}
	
}

//----------------------------------------------------------
//用戶登錄画面表示
function RegistUserOutputHTML($room_no,$realpages): void
{
	global $user_icon_dir,$db,$isold,$perpage;
	
	$res_room = $db->query("select room_name,room_comment,status,game_option,option_role from room where room_no = '$room_no'");
	
	if($db->num_rows($res_room) == 0)
	{
		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><title>村民登錄：錯誤</title></head>";
		echo "<body bgcolor=aliceblue>";
		echo "<br /><br />";
		echo "　　　　・編號No.$room_no 的村子不存在";
		return;
	}
	$room_array = $db->fetch_array($res_room);
	$room_name = $room_array['room_name'];
	$room_comment = $room_array['room_comment'];
	$status = $room_array['status'];
	$game_option = $room_array['game_option'];
	$option_role = $room_array['option_role'];
	$db->free_result($res_room);
	
	if($status != 'waiting')
	{
		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><title>村民登錄：錯誤</title></head>";
		echo "<body bgcolor=aliceblue>";
		echo "<br /><br />";
		echo "　　　　・遊戲已經開始了";
		return;
	}
	
	//用戶圖像一覽
	if (!$_GET['iconpp']) {
		$sqlim = 'limit 0,9';
	}
	if ($_GET['iconpp'] == 1) {
		$sqlim = 'limit 10,20 ';
	}
	
	//$server_name = str_replace("http://","",$_SERVER['SERVER_NAME']);
	//$server_name = str_replace("/","",$server_name);
	//$server_name = "http://" . $server_name;
	
	//$request_uri = str_replace("\\","/",dirname($_SERVER['REQUEST_URI']));
	
	$frame_bg_image = "img/user_regist_bg.webp";
	
	echo <<<END1
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>汝等是人是狼？[村民登錄]</title>
<link rel="stylesheet" type="text/css" href="img/font.css">
<script src="img/jquery-3.6.0.min.js"></script>
<script language="javascript">
function getParam(room,data, divID)
{
  var url = "user_manager.php?room_no="+room+"&iconpp="+data;
  $(divID).load(url);
}
</script>
<style type="text/css"><!-- a { text-decoration: none } --></style>
</head>
<body background="img/user_regist_bg2.webp">

<form action="user_manager.php?room_no=$room_no " method=POST>
<input type=hidden name=command value=regist>
<div align=left><a href=index.php>←返回</a></div>
<div align=center>

<table border=0 width=700px style="border-width:1px;border-color:black;border-style:solid;background-image:url($frame_bg_image);" cellpadding="0" cellspacing="0">

<tr><td align=center>

<table>
<td><img src="img/user_regist_title.webp"></td>
</table>

</td></tr>

<tr><td align=center style="background-color:#CC6600;border-top: silver 2px dotted;border-bottom: silver 2px dotted;">

<table border=0>
<tr>
<td valign=middle style="color:snow;"><strong><div align=right>$room_name 村</div></strong></td>
<td valign=middle><img src="img/user_regist_top.webp"></td>
</tr>
</table>

</td></tr>



<tr><td align=right>
<table border=0>
<tr>
<td style="font-size:8pt;">
～ $room_comment ～ [ $room_no 番地]
</td>
</tr>
</table>
</td></tr>


<tr><td align=center>

<table>
<tr>
<td align=center><img src="img/user_regist_uname.webp"></td><td><input type=text name=uname size=30 maxlength=30 style="background-color:#eeccaa;color:#774400;"></td>
<td><span style="font-size:8pt;">平時不顯示，作為重新登入用的「帳號名稱」
</span></td>

</tr>
<tr>
<td align=center><img src="img/user_regist_handle_trip.webp"></td><td><input type=text name=tripn size=30 maxlength=30 style="background-color:#eeccaa;color:#774400;"></td>
<td><span style="font-size:8pt;">在村中顯示「加密後的Trip碼」
</span></td>

</tr>
<tr>

<td align=center><img src="img/user_regist_handle_name.webp"></td><td><input type=text name=handle_name size=30 maxlength=30 style="background-color:#eeccaa;color:#774400;"></td>
<td><span style="font-size:8pt;">在村中所表示的「暱稱」
</span></td>


</tr>
<tr>

<td align=center><img src="img/user_regist_password.webp"></td><td><input type=password name=password size=30 maxlength=30 style="background-color:#eeccaa;color:#774400;"></td>
<td><span style="font-size:8pt;">重新登入所使用的密碼
</span></td>

</tr>
<tr>

<td align=center><img src="img/user_regist_sex.webp"></td><td><img src="img/user_regist_sex_male.webp"><input type=radio name=sex value=male>
<img src="img/user_regist_sex_female.webp"><input type=radio name=sex value=female></td>
<td><span style="font-size:8pt;">沒有什麼用途
</span></td>

</tr>
<tr>


END1;

	if( strstr((string) $game_option,"wish_role") )
	{
		echo "</tr><tr>\r\n";
		echo "<td align=center><img src=\"img/user_regist_role.webp\"></td><td>\r\n";
		echo "<img src=\"img/user_regist_role_none.webp\"><input type=radio name=role value=none>\r\n";
		echo "<img src=\"img/user_regist_role_human.webp\"><input type=radio name=role value=human><br />\r\n";
		echo "<img src=\"img/user_regist_role_wolf.webp\"><input type=radio name=role value=wolf>\r\n";
		echo "<img src=\"img/user_regist_role_mage.webp\"><input type=radio name=role value=mage><br />\r\n";
		echo "<img src=\"img/user_regist_role_necromancer.webp\"><input type=radio name=role value=necromancer>\r\n";
		echo "<img src=\"img/user_regist_role_mad.webp\"><input type=radio name=role value=mad><br />\r\n";
		echo "<img src=\"img/user_regist_role_guard.webp\"><input type=radio name=role value=guard>\r\n";
		if( !strstr((string) $option_role,"lovers") )
			echo "<img src=\"img/user_regist_role_common.webp\"><input type=radio name=role value=common><br />\r\n";
		echo "<img src=\"img/user_regist_role_fox.webp\"><input type=radio name=role value=fox><br />\r\n";
		echo "</td><td></td>\r\n";
	}
	else
	{
		echo "<input type=hidden name=role value=none>\r\n";
	}
	
	echo '
	
</tr>
<tr>

<td colspan=3 align=right>';

for ($i=1;$i <= $realpages;$i++) {
	echo "<a href=\"#\" onclick=\"getParam('".$_GET['room_no']."','$i', '#icon');return false;\">[$i]</a> ";
}

echo '<input type=submit value="住民登錄申請" style="border-width:2px;border-style:dotted;background-color:#eeccaa;color:#774400;"> 
</td>
</tr>

</table>

</td></tr>
<tr><td>

<fieldset style="border-color:black;border-width:1px;border-style:dotted;">
<legend><strong style="font-size:20px;font-weight:bold;"><img src=img/user_regist_icon.webp></strong></legend>';

echo "<div id=\"icon\">";
$nummax -= 1;
echo iconfunc("limit 0,$perpage");
echo "</div>";

echo '</fieldset>

</td>
</tr>

<tr><td><br /></td></tr>
</table>
</div>
</form>

</body></html>
';
}

function iconfunc($sqlim): string
{
	global $db,$isold,$user_icon_dir,$dummy_boy_imgid;
	$res_user_icon = $db->query("select icon_no,icon_name,icon_filename,icon_width,icon_height,color from user_icon
								 where icon_no != $dummy_boy_imgid  and look = '1' order by icon_no $sqlim");
//	$icon_count = $db->fetch_row($res_user_icon); //アイテムの個数を取得

	//表の出力
	$i = 0;
	$iconout = "<table border=0 style=\"font-size:10pt;\"><tr>";
	while($icon_arr = $db->fetch_array($res_user_icon))
	{
		//5個ごとに改行
		if( ($i % 5) == 0 )
		{
			$iconout .= "</tr><tr>\r\n";
		}
		$i++;
	//	$icon_arr = $db->fetch_array($res_user_icon);
		
		if ($i == 1) {
			$iconout .= "<td valign=top";
			$iconout .= "><img src=img/noico.webp width=45 height=45 border=2 style=\"border-color:#ffffff;\"><td>\r\n";
			$iconout .= "<td width=150px>Trip頭像<br />";
			$iconout .= "<font color=#ffffff>◆</font><input type=radio name=icon_no value=trip ></td>\r\n";
			$i++;
		}
		
		$icon_no = $icon_arr['icon_no'];
		$icon_name = $icon_arr['icon_name'];
		$icon_filename = $icon_arr['icon_filename'];
		$icon_width = $icon_arr['icon_width'];
		$icon_height = $icon_arr['icon_height'];
		$color = $icon_arr['color'];
		
		$icon_location = $user_icon_dir . "/" . $icon_filename;
		
		$iconout .= "<td valign=top";
		$iconout .= "><img src=$icon_location width=$icon_width height=$icon_height border=2 style=\"border-color:$color;\"><td>\r\n";
		$iconout .= "<td width=150px>$icon_name<br />";
		$iconout .= "<font color=$color>◆</font><input type=radio name=icon_no value=$icon_no ></td>\r\n";
	}
	$iconout .= "</tr></table>";
	$db->free_result($res_user_icon);
	return $iconout;
}
?>
