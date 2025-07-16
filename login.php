<?php
require_once __DIR__ . '/setting.php';
require_once __DIR__ . '/game_functions.php';


	//MySQLに接続
if($db->connect_error())
{
	exit;
}

//Sessionの開始
session_start();
$session_id = session_id();
$login_type = empty($login_type) ? "" : $login_type;


//登錄処理 単に呼ばれただけなら観戦ページに移動させる
switch($login_type)
{
	//用戶名とパスワードで手動登錄
	case('manually'):
		if( LoginManually($room_no) )
		{
			//HTML出力
			echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">
					<title>登錄完成</title>
					<meta http-equiv=refresh content=\"1;URL=game_frame.php?room_no=$room_no\">
					</head><body bgcolor=aliceblue>
					<br /><br />";
			echo "　　　　・登錄完成。";
			echo "畫面切換中<a href=\"game_frame.php?room_no=$room_no\">按我繼續</a></body></html>";
		}
		else
		{
			//HTML出力
			echo '<html><head><title>登錄失敗</title><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">
					</head><body bgcolor=aliceblue>
					<br /><br />';
			echo "　　　　・帳號名稱與密碼錯誤<br />";
			echo "</body></html>";
		}
		break;
	
	//SessionIDから自動登錄
	default:
		if( SessionCheck($session_id) != NULL )
		{
			//HTML出力
			echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\">
					<title>登錄完成</title>
					<meta http-equiv=refresh content=\"1;URL=game_frame.php?room_no=$room_no\">
					</head><body bgcolor=aliceblue>
					<br /><br />";
			echo "　　　　・登錄完成。";
			echo "畫面切換中<a href=\"game_frame.php?room_no=$room_no\">按我繼續</a></body></html>";
		}
		else
		{
			//HTML出力
			header("Location:game_view.php?room_no=$room_no");
			//echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
			//		<title>觀戰跳頁</title>
			//		<meta http-equiv=refresh content=\"1;URL=game_view.php?room_no=$room_no\">
			//		</head><body bgcolor=aliceblue>
			//		<br /><br />";
			//echo "　　　　・觀戰畫面移動中。";
			//echo "如果沒有移動<a href=\"game_view.php?room_no=$room_no\">按我繼續</a></body></html>";
		}
}
//MySQLとの接続を閉じる
$db->close();



//***************************************************************
//               関数｜・∀・)・∀・)…
//***************************************************************
//----------------------------------------------------------
//用戶名とパスワードで登錄 返り血：登錄できた true できなかった false
function LoginManually($room_no): bool
{
	global $uname,$password,$db,$isold,$system_password,$tripkey,$dummy_boy_imgid;
	
	
	$uname = str_replace("\\","\\\\",(string) $uname);
	$uname = str_replace("&","&amp;",$uname);
	$uname = str_replace("<","&lt;",$uname);
	$uname = str_replace(">","&gt;",$uname);
	//$uname = str_replace("'","\\'",$uname);
	
	$password = str_replace("\\","\\\\",(string) $password);
	//$password = str_replace("'","\\'",$password);
	
	//IPアドレス取得
	if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
     $ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
 } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
     $ip_address = $_SERVER['HTTP_CLIENT_IP'];
 } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
     $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
 } else{
		$ip_address= $_SERVER['REMOTE_ADDR'];
	}
	
	if( ($uname == '') || ($password == '') ) //入力錯誤
	{
		return false;
	}
	
	
	//該当する用戶名とパスワードがあるか確認
	$result = $db->query("select uname from user_entry
										where room_no = '$room_no' and uname = '$uname' and password = '$password' and (user_no > 0 or (uname = 'dummy_boy' and user_no = -1))");
	//一致した場合
	if( $db->num_rows($result) == 1 || ($password == $system_password && $uname == 'dummy_boy'))
	{
		if($db->num_rows($result) == 1) {
			$login_arr = $db->fetch_array($result);
			$regist_uname = $login_arr['uname'];

			//登錄成功

			//SessionIDの再登錄
			do //DBに登錄されているSessionIDと被らないようにする
			{
				session_regenerate_id();
				$session_id = session_id();

				$result = $db->query("select count(room_no) from user_entry,admin_manage
									where user_entry.session_id = '$session_id' or admin_manage.session_id = '$session_id'");
			}while($db->result($result,0) != 0);

			//DBのSessionIDを更新
			$db->query("update user_entry set session_id = '$session_id' where room_no = '$room_no' and uname = '$uname'
																											and (user_no > 0 or (uname = 'dummy_boy' and user_no = -1))");
	//		$db->query("commit"); //一応コミット
		} else {
			// !!! special login
			$regist_uname = $uname;
			//製造幽靈人口 (?)
			$db->query("INSERT INTO user_entry (room_no, user_no, uname, handle_name, trip, icon_no, sex, password, role, live) 
					   VALUES($room_no, -1, '$uname', '替身君（路過）', '$tripkey', $dummy_boy_imgid, 'male', '$password', 'wolf wfwtr', 'dead')");
			//SessionIDの再登錄
			do //DBに登錄されているSessionIDと被らないようにする
			{
				session_regenerate_id();
				$session_id = session_id();

				$result = $db->query("select count(room_no) from user_entry,admin_manage
									 where user_entry.session_id = '$session_id' or admin_manage.session_id = '$session_id'");
			}while($db->result($result,0) != 0);

			//DBのSessionIDを更新
			$db->query("update user_entry set session_id = '$session_id' where room_no = '$room_no' and uname = '$uname' and (user_no > 0 or (uname = 'dummy_boy' and user_no = -1))");
			// !!! end seg.
		}
		return true;
	}
	
	return false;
}

?>
