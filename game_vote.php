<?php
require_once 'game_functions.php';

if (!$room_no) {
	//exit;
}

if ($aaaaa) {
	VoteTotalCheck($aaaaa,$bbbbb);
} else {

//Session開始
session_start();
$session_id = session_id();
$command = empty($command) ? "" : $command;
$type = empty($type) ? "" : $type;
$target_no = empty($target_no) ? "" : $target_no;
$target_handle_name = empty($target_handle_name) ? "" : namenohtml($target_handle_name);
$atype = empty($atype) ? "" : $atype;


//ページの色設定を取得
$background_color = empty($bg) ? "" : $bg;
$text_color = empty($fg) ? "" : $fg;
$a_color = empty($a) ? "" : $a;
$a_vcolor = empty($av) ? "" : $av;
$a_acolor = empty($aa) ? "" : $aa;

//ページの色設定を取得(<s>をシャープにデコード)
$background_color_dec = str_replace("<s>","#",(string) $background_color);
$text_color_dec = str_replace("<s>","#",(string) $text_color);
$a_color_dec = str_replace("<s>","#",(string) $a_color);
$a_vcolor_dec = str_replace("<s>","#",(string) $a_vcolor);
$a_acolor_dec = str_replace("<s>","#",(string) $a_acolor);

//phpの引数を格納
$php_argv = "room_no=$room_no&auto_reload=$auto_reload&play_sound=$play_sound&list_down=$list_down&bg=$background_color&fg=$text_color&a=$a_color&av=$a_vcolor&aa=$a_acolor";

//スタイルシート
$page_style= diamcssout()."<style type=\"text/css\"><!-- .table_votelist1{border-top: $text_color_dec 1px dotted;border-left: $text_color_dec 1px dotted;border-bottom: $text_color_dec 1px dotted;} .table_votelist2{font-size:10pt;border-top: $text_color_dec 1px dotted;border-bottom: $text_color_dec 1px dotted;border-right: $text_color_dec 1px dotted;} --></style> \r\n";

//MySQLに接続
if($db->connect_error())
	exit;

if( ($uname = SessionCheck($session_id) ) != NULL )
{

	//日付と白か夜か、ゲーム終了後かどうかを取得
	$res_room = $db->query("select date,day_night,status,max_user,game_option,option_role,dellook from room where room_no = '$room_no'");
	if ($db->num_rows($res_room)) {
		$room_arr = $db->fetch_array($res_room);
	} else {
		//$isold = '_old';
		$res_room = $db->query("select date,day_night,status from room{$isold} where room_no = '$room_no'");
		$room_arr = $db->fetch_array($res_room);
	}
	$date = $room_arr['date'];
	$day_night = $room_arr['day_night'];
	$status = $room_arr['status'];
	$max_user = $room_arr['max_user'];
	$game_option = $room_arr['game_option'];
	$option_role = $room_arr['option_role'];
	$dellook = $room_arr['dellook'];
	$db->free_result($res_room);

	//自分のハンドルネーム、役割、生存状態を取得
		$res_user = $db->query("select uid,handle_name,role,live,trip from user_entry where room_no = '$room_no' and user_no > '0' and uname = '$uname'");
	$user_arr = $db->fetch_array($res_user);
	$handle_name = $user_arr['handle_name'];
	$role = $user_arr['role'];
	$live = $user_arr['live'];
	$trip = $user_arr['trip'];
	$userid = $user_arr['uid'];
	$db->free_result($res_user);

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
	{\n";
		if ($dead_mode == 'on') {
			$echosocket .= "window.parent.frames['middle'].location.reload();\n";
			$echosocket .= "window.parent.frames['bottom'].location.reload();\n";
		} else {
			$echosocket .=  "window.parent.frames['bottom'].location.reload();\n";
		}
		$echosocket .= "window.location.href = \"game_up.php?$php_argv#game_top\";
	}
	</script>"; //投票の分類（Kick、処刑、占い、狼など）

	if($status == 'finished' || $live == 'dead')
	{
		VoteRedirect();		
	}
	elseif($command == 'vote')
	{
		//投票処理
		$target_no = (int)$target_no;

		if($date == 0)
		{
			//開始する、Kickする投票処理
			BeforeGameVote();
		}
		elseif( $target_no == 0 && $atype != '放棄行動' && $atype != '完成任務')
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票錯誤</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>・請指定投票對象<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
		}
		elseif( $day_night == 'day' )
		{
			if(strstr((string) $role, "GM")) {
				GMVote();
			} else {
				//お白の処刑投票処理
				DayVote();
			}
		}
		elseif( $day_night == 'night' )
		{
			if(strstr((string) $role, "GM")) {
				GMVote();
			} else {
				//夜の投票処理
				NightVote();
			}
		}
		else
		{
			//既に投票されております
		}
	}
	elseif($date == 0)
	{
		//開始する、Kickするページ出力
		BeforeGameVotePageOutput();
	}
	elseif( $day_night == 'day' )
	{
		if(strstr((string) $role, "GM"))
			GMVotePageOutput();
		else
			//お白の処刑投票ページ出力
			DayVotePageOutput();
	}
	elseif( $day_night == 'night' )
	{
		if(strstr((string) $role, "GM"))
			GMVotePageOutput();
		else
			//夜の投票ページ出力
			NightVotePageOutput();
	}
	else
	{
		VoteRedirect();
	}
}
else
{
	echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>session認證錯誤</title></head> $page_style ";
	echo '<br /><br />';
	echo "　　　　session認證錯誤<br />";
	echo "　　　　請由<a href=index.php target=_top style=\"color:blue;\">首頁</a>重新登入</body></html>";
}

//MySQLとの接続を閉じる
$db->close();

//過濾
}

//***********************************************************
//                       以下関数
//***********************************************************

//----------------------------------------------------------
//開始前の投票の処理
function BeforeGameVote(): void
{
	global $room_no,$day_night,$uname,$handle_name,$target_no,$target_handle_name,$situation,$php_argv,$page_style,$time_zone,$db,$isold,$game_option,$trip,$date;

	global $msg_rm_image, $msg_sys_image;

	//ゲームスタートの処理
	if( $situation == 'GAMESTART' )
	{
		$result = $db->query("select uname from vote where room_no = '$room_no' and date = '0' and uname = '$uname' and situation = 'GAMESTART'");

		if( $db->num_rows($result) == 0 )
		{
			//テーブルを排他的ロック
			if($db->begin_transaction())
			{
				$all_query_ok = true;
				//投票処理
				$db->query("insert into vote (room_no,date,uname,situation) values ($room_no,0,'$uname','GAMESTART')") ? null : $all_query_ok = false;

				//投票しました通知
				//$time = time();  //現在時刻、GMTとの時差を足す
				//$res2 = $db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
				//										values($room_no,0,'$day_night system','$uname',$time,'GAMESTART_DO','0')");

		//		$res3 = $db->query("commit"); //一応コミット

				if ($all_query_ok) {
					$db->commit();
				} else {
					$db->rollback();
				}

				//登錄成功
				if($all_query_ok)
				{
					//票が集まっていたら集計する
					VoteTotalCheck();
					VoteRedirect();
				}
				else
				{
					echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
					echo "<a name=\"#game_top\">";
					echo "<div align=center>・資料庫錯誤<br />可能有問題<br />";
					echo "<a href=\"game_up.php?$php_argv#game_top\">";
					echo "←上一頁&amp;重新整理</a></div></body></html>";
				}



			}
			else //ロックできなかったとき
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>・伺服器忙碌。<br />請重新投票。<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
			}
		}
		else
		{
			VoteRedirect();
		}
	}
	elseif( $situation == 'KICK_DO') //Kickの処理
	{
		global $room_no,$day_night,$uname,$handle_name,$target_no,$target_handle_name,$situation,$php_argv,$page_style,$time_zone,$db,$isold,$game_option,$trip;

		//ターゲットが無い場合は中止
		if($target_handle_name == '')
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>Kick：請指定投票處<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}

		$res_target_n = $db->query("select user_no from user_entry
								where user_entry.room_no = '$room_no' and user_entry.user_no > 0 and user_entry.uid = '$target_handle_name'");

		$vote_target_no = $db->result($res_target_n,0);

		if(strstr((string) $game_option, 'dummy_boy') && $vote_target_no == 1)
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>Kick：不可以投票給'替身君'。O皿O<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}

		//自分が相手に既に投票過了かどうか
		$res_target = $db->query("select count(vote.uname) from user_entry,vote 
								where user_entry.room_no = '$room_no' and user_entry.user_no > 0 and user_entry.uid = '$target_handle_name'
										and vote.room_no = '$room_no' and vote.uname = '$uname' and vote.date = 0
										and vote.situation LIKE '%KICK_DO'
										and user_entry.uname = vote.target_uname");

		if( $db->result($res_target,0) == 0)
		{
			//自分に投票できません
			$res_vote_me = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0'
											and uname = '$uname' and uid ='$target_handle_name'");
			if( $db->result($res_vote_me,0) != 0)
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>Kick：不可以投票給自己<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}

			$gmtrip_str = strstr((string) $game_option,"gm:");
			if($gmtrip_str != '')
				sscanf($gmtrip_str, "gm:%s", $gmtrip);

			$fkick = ($gmtrip == $trip && $gmtrip != '') || ($uname == 'dummy_boy');	 // ROOM MANAGER KICK

			//テーブルを排他的ロック
			if($db->begin_transaction())
			{
				$all_query_ok = true;
				//ゲームは既に開始されていないかチェック
				$res_room_stat = $db->query("select day_night from room where room_no = '$room_no'");
				$now_day_night = $db->result($res_room_stat,0,0);

				//ターゲットの用戶名を取得
				$result = $db->query("select uname,handle_name from user_entry where room_no = '$room_no' and user_no > '0'
															and uid = '$target_handle_name'");
				$target_arr = $db->fetch_array($result);
				$target_uname = $target_arr['uname'];
				$target_handle_name2 = $target_arr['handle_name'];

				if( ($now_day_night == 'beforegame') && ($target_uname != '') )
				{
					//投票処理

					//投票
					if($fkick)
						$db->query("insert into vote (room_no,date,uname,target_uname,situation)
																values ($room_no,0,'$uname','$target_uname','FKICK_DO')") ? null : $all_query_ok = false;
					else
						$db->query("insert into vote (room_no,date,uname,target_uname,situation)
																values ($room_no,0,'$uname','$target_uname','KICK_DO')") ? null : $all_query_ok = false;


					//投票しました通知
					$time = time();  //現在時刻、GMTとの時差を足す
					if($fkick)
						$vote_do_message_str = "FKICK_DO\t" . $target_handle_name2;
					else
						$vote_do_message_str = "KICK_DO\t" . $target_handle_name2;
					$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
											values($room_no,0,'$day_night system','$uname',$time,'$vote_do_message_str','0')") ? null : $all_query_ok = false;

			//		$res3 = $db->query("commit"); //一応コミット

					if ($all_query_ok) {
						$db->commit();
					} else {
						$db->rollback();
					}

					//登錄成功
					if($all_query_ok)
					{
						//票が集まっていたら集計する
						VoteTotalCheck();
						VoteRedirect();
						/*
						//投票完了
						echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
						echo "<a name=\"#game_top\">";
						echo "<div align=center>・投票完成(若要Kick玩家需要5人以上投票)<br />";
						echo "<a href=\"game_up.php?$php_argv#game_top\">";
						echo "←上一頁&amp;重新整理</a></div></body></html>";
						*/
					}
					else
					{
						echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
						echo "<a name=\"#game_top\">";
						echo "<div align=center>・資料庫錯誤<br />可能有問題・・<br />";
						echo "<a href=\"game_up.php?$php_argv#game_top\">";
						echo "←上一頁&amp;重新整理</a></div></body></html>";
					}
				}
				else
				{
					VoteRedirect();
				}


			}
			else //ロックできなかったとき
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>・伺服器忙碌。<br />請重新投票。<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
			}


		}
		else //投票過了
		{
			VoteRedirect();
		}
	} elseif( $situation == 'RECHECK') {
		global $room_no,$day_night,$uname,$handle_name,$target_no,$target_handle_name,$situation,$php_argv,$page_style,$time_zone,$db,$isold,$game_option,$trip;

		if($trip == '') $rctrip = 'NO_TRIP';
		else $rctrip = $trip;

		if(!strstr((string) $game_option, 'gm:'.$rctrip) && $uname != 'dummy_boy') {
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>只有村長或gm可以進行點名<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		} 

		$db->query("delete from vote where room_no = '$room_no'"); //今までの投票を全部削除

		$time = time();  //現在時刻、GMTとの時差を足す
		//$db->query("update room set last_updated = '$time' where room_no = '$room_no'"); //最終書き込みを更新
		$db->query("update room set status = 'waiting',day_night = 'beforegame',last_updated = '$time' where room_no = '$room_no'");

		$time++; 

		$kick_message_str = msgimg($msg_rm_image)."$handle_name 村長 進行點名。";
		$res = $db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
					values ($room_no,$date,'$day_night system','system','$time','$kick_message_str','0')");

		$time++; //出て行ったメッセージより後に表示されるように
		$reset_vote_str = msgimg($msg_sys_image).'＜投票重新開始 請盡速重新投票＞';
		$res = $db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
					values ($room_no,$date,'$day_night system','system','$time','$reset_vote_str','0')");

		VoteRedirect();
	}
}

//----------------------------------------------------------
//白の投票処理
function DayVote(): void
{
	global $room_no,$date,$uname,$handle_name,$target_no,$vote_times,$situation,$vote_lockfile,$php_argv,$page_style,$time_zone,$db,$isold;

	if( $situation != 'VOTE_KILL')
	{
		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
		echo "<a name=\"#game_top\">";
		echo "<div align=center>處刑：投票錯誤<br />";
		echo "<a href=\"game_up.php?$php_argv#game_top\">";
		echo "←上一頁&amp;重新整理</a></div></body></html>";
		return;
	}

	//自分が既に投票過了かどうか
	$res_already_vote = $db->query("select count(uname) from vote 
							where room_no = '$room_no' and uname = '$uname' and date = $date
									and situation = '$situation' and vote_times = '$vote_times'");

	if( $db->result($res_already_vote,0) == 0)
	{
		//投票相手の用戶情報取得
		$res_target = $db->query("select uname,handle_name,live from user_entry where room_no = '$room_no' and user_no = $target_no");
		$target_arr = $db->fetch_array($res_target);
		$target_uname = $target_arr['uname'];
		$target_handle_name = $target_arr['handle_name'];
		$target_live = $target_arr['live'];
		$db->free_result($res_target);

		//自分宛、死者宛、相手が居ない場合は無効
		if( (($target_live == 'dead') && !strstr((string) $role,"cat")) || ($target_uname === $uname) || ($target_uname == '') )
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>處刑：投票給了一個無效的目標(死者,自己,或是不在的人)<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}

		//テーブルを排他的ロック
		if($db->begin_transaction())
		{
			$all_query_ok = true;
			//投票処理

			//自分の役割を取得
			$res_role = $db->query("select role from user_entry where room_no = '$room_no' and uname = '$uname'
				 																							and user_no > '0'");
			$role = $db->result($res_role,0,0);

			//權力者なら投票数が２
			if( strstr((string) $role,"authority") )
				$vote_number = 2;
			else
				$vote_number = 1;

			//投票
			$res_already_vote = $db->query("SELECT count(room_no) from vote where room_no = '$room_no' AND date = '$date' AND uname = '$uname'
											AND vote_times = '$vote_times' AND situation = '$situation'");
			if( $db->result($res_already_vote,0) > 0)
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>處刑：投票錯誤<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$db->query("insert into vote (room_no,date,uname,target_uname,vote_number,vote_times,situation)
								values ($room_no,$date,'$uname','$target_uname',$vote_number,'$vote_times','$situation')") ? null : $all_query_ok = false;

			//投票しました通知
			$time = time();  //現在時刻、GMTとの時差を足す
			$vote_do_message_str = "VOTE_DO\t" . $target_handle_name;
			$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
										values($room_no,$date,'day system','$uname',$time,'$vote_do_message_str','0')") ? null : $all_query_ok = false;

		//	$res3 = $db->query("commit"); //一応コミット

			if ($all_query_ok) {
				$db->commit();
			} else {
				$db->rollback();
			}

			//登錄成功
			if($all_query_ok)
			{
				//票が集まっていたら集計する
				VoteTotalCheck();
				VoteRedirect();
			}
			else
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>・資料庫錯誤<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
			}


		}
		else //ロックできなかったとき
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>・伺服器忙碌。<br />請重新投票。<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
		}

	}
	else
	{
		VoteRedirect();
	}
}

//----------------------------------------------------------
//夜の投票処理
function NightVote(): void
{

	global $room_no,$date,$uname,$handle_name,$role,$target_no,$situation,$vote_lockfile,$php_argv,$page_style,$time_zone,$db,$isold,$game_option,$atype;


	switch($situation)
	{
		case('WOLF_EAT'):
			if( !str_contains((string) $role,"wolf") )
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：人狼以外不能咬人<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select uname from vote where room_no = '$room_no' and date = $date 
																				and situation = '$situation' group by situation");
			$already_vote = $db->num_rows($res_already_vote);
			break;
		case('HUG_DO'):
			if( !str_contains((string) $role,"wfwnd") )
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：幼狼以外不能萌殺人<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select uname from vote where room_no = '$room_no' and date = $date 
																				and situation = '$situation' group by situation");
			$already_vote = $db->num_rows($res_already_vote);
			break;
		case('MAGE_DO'):
			if( !str_contains((string) $role,"mage") )
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：占卜師以外不能占卜<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
																	and uname = '$uname' and situation = '$situation'");
			$already_vote = $db->result($res_already_vote,0);
			break;
		case('FOSI_DO'):
			if(!str_contains((string) $role,"fosi")) {
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：子狐以外不能子狐占卜<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
																	and uname = '$uname' and situation = '$situation'");
			$already_vote = $db->result($res_already_vote,0);
			break;
		case('CAT_DO'):
			if(!str_contains((string) $role,"cat")) {
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：貓又以外不能復活<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
																	and uname = '$uname' and situation = '$situation'");
			$already_vote = $db->result($res_already_vote,0);
			break;
		case('GUARD_DO'):
			if( !str_contains((string) $role,"guard") )
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：獵人以外不能護衛<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
										   and uname = '$uname' and situation = '$situation'");
			$already_vote = $db->result($res_already_vote,0);
			break;
		case('MYTHO_DO'):
			if( !str_contains((string) $role,'mytho') )
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：說謊狂以外不能模仿<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
										   and uname = '$uname' and situation = '$situation'");
			$already_vote = $db->result($res_already_vote,0);
			break;
		case('OWLMAN_DO'):
			if( !str_contains((string) $role,'owlman') )
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：夜梟以外不能詛咒<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
										   and uname = '$uname' and situation = '$situation'");
			$already_vote = $db->result($res_already_vote,0);
			break;
		case('PENGU_DO'):
			if( !str_contains((string) $role,'pengu') )
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：小企鵝以外不能搔癢<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
										   and uname = '$uname' and situation = '$situation'");
			$already_vote = $db->result($res_already_vote,0);
			break;
		case('SPY_DO'):
			if( !str_contains((string) $role,"spy") )
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：間諜以外不能完成任務<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
										   and uname = '$uname' and situation = '$situation'");
			$already_vote = $db->result($res_already_vote,0);
			break;
		default:
			VoteRedirect();
			break;

	}

	if( $already_vote == 0)
	{
		if($atype != '放棄行動' && $atype != '完成任務') {
			//投票相手の用戶情報取得
			$res_target = $db->query("select uname,handle_name,role,live from user_entry
									 where room_no = '$room_no' and user_no = $target_no");
			$target_arr = $db->fetch_array($res_target);
			$target_uname = $target_arr['uname'];
			$target_handle_name = $target_arr['handle_name'];
			$target_role = $target_arr['role'];
			$target_live = $target_arr['live'];
			$db->free_result($res_target);
		} else {
			$target_role = $target_live = '';
			$target_uname = $target_handle_name = ":;:NOP:;:";
		}

		//自分宛、死者宛、狼同士の投票は無効
		if( ($target_live == 'dead') || ($target_uname === $uname) || ( strstr((string) $role,"wolf") && !strstr((string) $role, 'wfbsk') && strstr((string) $target_role,"wolf") ) )
		{
			if (!strstr((string) $role,"cat")) {
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>夜：投票給了一個無效的目標<br />";
				echo "你不能投票給死者，自己，或是狼同伴們<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
		}

		if( ($situation == 'WOLF_EAT') && strstr((string) $game_option,"dummy_boy") && ($target_uname != 'dummy_boy') && ($date == 1) )
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>夜：投票給了一個無效的目標<br />";
			echo "使用'替身君'的場合，不能投給替身君以外的目標<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}

		//テーブルを排他的ロック
		if($db->begin_transaction())
		{
			$all_query_ok = true;
			//投票
			$res_already_vote = $db->query("SELECT count(room_no) from vote where room_no = '$room_no' AND date = '$date' AND uname = '$uname'
											AND situation = '$situation'");
			if( $db->result($res_already_vote,0) > 0)
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>投票錯誤<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
				return;
			}
			$db->query("insert into vote (room_no,date,uname,target_uname,vote_number,situation)
											values ($room_no,$date,'$uname','$target_uname',1,'$situation')") ? null : $all_query_ok = false;
			//系統メッセージ
			$system_message = $handle_name . "\t" .$target_handle_name;
			$db->query("insert into system_message (room_no,message,type,date)
											values ($room_no,'$system_message','$situation',$date)") ? null : $all_query_ok = false;

			//投票しました通知
			$time = time();  //現在時刻、GMTとの時差を足す
			$vote_do_message_str = $situation . "\t" . $target_handle_name;
			$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
										values($room_no,$date,'night system','$uname',$time,'$vote_do_message_str','0')") ? null : $all_query_ok = false;

			//	$res4 = $db->query("commit"); //一応コミット

			if ($all_query_ok) {
				$db->commit();
			} else {
				$db->rollback();
			}

			//登錄成功
			if($all_query_ok)
			{
				//票が集まっていたら集計する
				VoteTotalCheck();
				VoteRedirect();
			}
			else
			{
				echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
				echo "<a name=\"#game_top\">";
				echo "<div align=center>・資料庫錯誤<br />可能有問題<br />";
				echo "<a href=\"game_up.php?$php_argv#game_top\">";
				echo "←上一頁&amp;重新整理</a></div></body></html>";
			}

		}
		else //ロックできなかったとき
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>・伺服器忙碌。<br />請重新投票。<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
		}

	}
	else
	{
		VoteRedirect();
	}
}

//----------------------------------------------------------
//投票が終了したかどうかチェック
//票数がそろっていたら集計と処理
function VoteTotalCheck($aaaaa = '',$bbbbb = ''): void
{
	global $room_no,$uname,$handle_name,$date,$day_night,$target_no,$target_handle_name,$vote_times,$situation,$role_list,$time_zone,$db,$isold,$role,$max_user,$game_option;

	global $msg_ampm_image, $msg_guard_image, $msg_vote_image, $msg_kill_image, $msg_sys_image, $msg_mage_image, 
	$msg_room_image, $msg_wolf_image, $msg_human_image, $msg_fox_image, $msg_fosi_image, $msg_cat_image, 
	$msg_sudden_image, $msg_gm_image, $msg_rm_image, $msg_lover_image, $msg_mad_image;

	global $wfwtr_message,$wfasm_message;


	if ($aaaaa && $bbbbb) {
		$situation = $aaaaa;
		$vote_times = $bbbbb;
	}

	if($situation == 'GAMESTART') //ゲーム開始の票
	{
		//聊天模式阻止開始
		if (strstr((string) $game_option,"ischat")) {
			return;
		}
		//投票総数を取得
		$res_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = '0' and situation = '$situation'");
		$vote_count = $db->result($res_vote,0);

		if( strstr((string) $game_option,"dummy_boy") ) //替身君使用なら替身君の分を加算
			$vote_count++;

		//用戶総数を取得
		$res_user = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and user_no <= $max_user");
		$user_count = $db->result($res_user,0);

		$res_gm = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no = " . ($max_user+1));
		$have_gm = $db->result($res_gm,0);

		//全員が投票していた場合
		if($vote_count >= ($user_count + $have_gm))
		{
			//投票者が８人に達していない場合、処理を止める(荒らし対策)
			if(($vote_count + $have_gm) < 8)
				return;

			//ゲーム開始
			$db->query("update room set status = 'playing' , date = '1' , day_night = 'night' where room_no = '$room_no'");
			$db->query("delete from vote where room_no = '$room_no'"); //今までの投票を全部削除

			//役割振り分け
			$now_role_list = $role_list[$user_count]; //人数による役割リストの配列
			//echo $user_count;

			//決定者、權力者、埋毒者のオプション役割(他と兼任できるもの)を決定
			$res_option_role = $db->query("select option_role from room where room_no = '$room_no'");
			$option_role = $db->result($res_option_role,0,0);

			//20人以上時埋毒者
			//オプション役割を役割の先頭の方(村民)から上書きする
			$option_role_count = 0;
			$outwfbig = 0;
			if (strstr((string) $option_role,"cat"))
				$poisonis = 'cat';
			else if (strstr((string) $option_role,"poison"))
				$poisonis = 'poison';
			else
				$poisonis = '';

			//13人以上共有者/戀人
			$hybrid_flash_enabled = ($user_count >= 20 && strstr((string) $option_role, 'comlover'));
			if (strstr((string) $option_role, 'r_lovers') || $hybrid_flash_enabled)
				$flashis = 'r_lovers';
			else if (strstr((string) $option_role, 's_lovers'))
				$flashis = 's_lovers';
			else if (strstr((string) $option_role, 'noflash'))
				$flashis = 'none';
			else
				$flashis = 'common';

			if (strstr((string) $option_role,'mytho') && $user_count >= 16)
			{
			   $now_role_list[$option_role_count++] = 'mytho';
			}

			if (strstr((string) $option_role,'owlman') && $user_count >= 20) 
			{
			   $now_role_list[$option_role_count++] = 'owlman';
			}	  


			if(strstr((string) $option_role, 'noble') && ($user_count >= 13) ) {
				$now_role_list[$option_role_count++] = 'noble';
				$now_role_list[$option_role_count++] = 'slave';
			}


			if((strstr((string) $option_role,$poisonis) || strstr((string) $option_role,"betr") || strstr((string) $option_role,"foxs") ||
			 strstr((string) $option_role,"wfbig") || strstr((string) $option_role,"fosi")|| strstr((string) $option_role, "morewolf")) && ($user_count >= 20)) //埋毒者は20人以上時登場(その際、村民2人→毒、狼にする)
			{
				if (strstr((string) $option_role,"betr")) {
					$now_role_list[$option_role_count] = "betr";
					$option_role_count ++;
					if (strstr((string) $option_role,$poisonis)) {
						$now_role_list[$option_role_count++] = "wolf";
						$now_role_list[$option_role_count++] = $poisonis;
					}
				} elseif (strstr((string) $option_role,"foxs")) {
					$now_role_list[$option_role_count] = "fox";
					$option_role_count ++;
					if (strstr((string) $option_role,$poisonis)) {
						$now_role_list[$option_role_count++] = "wolf";
						$now_role_list[$option_role_count++] = $poisonis;
					}
				} elseif (strstr((string) $option_role,"fosi")) {
					$now_role_list[$option_role_count] = "fosi";
					$option_role_count ++;
					if (strstr((string) $option_role,$poisonis)) {
						$now_role_list[$option_role_count] = "wolf";
						$option_role_count ++;
						$now_role_list[$option_role_count] = $poisonis;
						$option_role_count ++;
					}
				} else {
					if (strstr((string) $option_role,$poisonis)) {
						$now_role_list[$option_role_count] = "wolf";
						$option_role_count ++;
						$now_role_list[$option_role_count] = $poisonis;
						$option_role_count ++;
					} 
				}
				if (strstr((string) $option_role,"wfbig"))
					$outwfbig = 1;

				if(strstr((string) $option_role,"morewolf") && $poisonis === NULL) {
					$now_role_list[$option_role_count] = "wolf";
					$option_role_count ++;
				}


			}

			if(strstr((string) $option_role, 'pengu') && $user_count > 20) {
				//$now_role_list[$option_role_count] = "wolf";
				//$option_role_count ++;
				$now_role_list[$option_role_count] = "pengu";
				$option_role_count ++;
			}

			// 1.3.71前：把共取代成村
			// 1.3.72後：
			//   共有取代重點： (前提： 開了戀)
			//   1. <20
			//   2. >20，而且沒開共戀Override
			if (!$hybrid_flash_enabled && $flashis != 'common' || $flashis == 'none') {
				for($i=0; $i<$user_count; $i++) {
					if($now_role_list[$i] == 'common') {
						if($flashis == 's_lovers') 
							$now_role_list[$i]='human lovers';
						else
							$now_role_list[$i]='human';
					}
				}
			}

			if (strstr((string) $option_role,'spy') && $user_count >= 10)
			{
				for($i=0; $i<$user_count; $i++) {
					if($now_role_list[$i] == 'mad')
			   			$now_role_list[$i] = 'spy';
			   	}
			}

			//用戶リストをランダムに取得
			$res_user_list = $db->query("select uname,role,MD5(RAND()*NOW()) as MyRand
												from user_entry where room_no = '$room_no' and user_no > '0' and user_no <= $max_user order by MyRand");

			$uname_arr = []; //役割の決定した用戶名を格納する
			$role_arr = []; //用戶名に対応する役割
			$re_uname_arr = []; //希望の役割になれなかった用戶名を一時的に格納


			while($user_list_arr = $db->fetch_array($res_user_list)) //希望の役割を選別
			{
			//	$user_list_arr = $db->fetch_array($res_user_list); //ランダムな用戶情報を取得
				$this_uname = $user_list_arr['uname'];

				if( strstr((string) $game_option,"wish_role") ) //希望角色の場合、希望を取得(´･ω･`)
					$this_role = $user_list_arr['role'];
				else
					$this_role = 'none';

				if( ($this_index = array_search($this_role,$now_role_list) ) != false ) //希望どおり
				{
					array_push($uname_arr,$this_uname);
					array_push($role_arr,$this_role);					
					array_splice($now_role_list,$this_index,1);//割り振った役割は削除する

				}
				else //希望の役割がない
				{
					array_push($re_uname_arr,$this_uname);
				}
			}
			$db->free_result($res_user_list);

			$re_count = count($re_uname_arr); //役割が決まらなかった人の数

			for($i=0 ; $i < $re_count ; $i++) //余った役割を割り当てる
			{
				array_push($uname_arr,$re_uname_arr[$i]);
				array_push($role_arr,$now_role_list[$i]);
			}

			//兼任となる役割の設定
			$rand_keys = array_randme($role_arr,$user_count); //ランダムキーを取得

			//兼任となるオプション役割(決定者、權力者)
			$option_subrole_count = [];
			while (count($option_subrole_count) < $user_count) {
				$option_subrole_count[count($option_subrole_count)] = randme(0,$user_count-1);
				$option_subrole_count = array_unique($option_subrole_count);
			}

			$decide_count = 0;
			$authority_count = 0;
			if( strstr((string) $option_role,"decide") && ($user_count >= 16) )
			{
				$role_arr[$rand_keys[$option_subrole_count[0]]] .= " decide";
				$decide_count++;
			}
			if( strstr((string) $option_role,"authority") && ($user_count >= 16) )
			{
				$role_arr[$rand_keys[$option_subrole_count[1]]] .= " authority";
				$authority_count++;
			}

			$lover_pos = 3;
			if( $flashis == 'r_lovers' && ($user_count >= 13) )	
			{
				/*
				while(DetermineRole($role_arr[$rand_keys[$option_subrole_count[$lover_pos]]]) == DetermineRole($role_arr[$rand_keys[$option_subrole_count[2]]])) {
					$lover_pos++;
				}
				*/
				$role_arr[$rand_keys[$option_subrole_count[2]]] .= " lovers";
				$role_arr[$rand_keys[$option_subrole_count[$lover_pos]]] .= " lovers";
				$lovers_count+=2;
			}			

		//	$dummy_boy_index = array_search("dummy_boy",$uname_arr); //替身君の配列インデックスを取得

			/*
			//替身君使用の場合、替身君は狼、狐、埋毒者以外にする
			if( strstr($game_option,"dummy_boy") && ($dummy_boy_index != false) )
			{
				//狼、または狐、埋毒者だった場合
				if( strstr($role_arr[$dummy_boy_index],"wolf") || strstr($role_arr[$dummy_boy_index],"fox")
				 														|| strstr($role_arr[$dummy_boy_index],"poison") )
				{
					for($i=0 ; $i < $user_count ; $i++)
					{
						//狼、狐、埋毒者以外が見つかったら入れ替える
						if(!strstr($role_arr[$i],"wolf") && !strstr($role_arr[$i],"fox") && !strstr($role_arr[$i],"poison")) {
							$tmp_role = $role_arr[$dummy_boy_index];
							$role_arr[$dummy_boy_index] = $role_arr[$i];
							$role_arr[$i] = $tmp_role;
							break;
						}
					}
				}
			}*/


			//役割をDBに更新
			$role_count_list = [];
			$role_count_list['human'] = $role_count_list['wolf'] = $role_count_list['mage'] = $role_count_list['necromancer'] = 0;
			$role_count_list['mad'] = $role_count_list['guard'] = $role_count_list['common'] = $role_count_list['fox'] = 0;
			$role_count_list['betr'] = $role_count_list['fosi'] = $role_count_list['poison'] = $role_count_list['decide'] = 0;
			$role_count_list['authority'] = $role_count_list['cat'] = $role_count_list['lovers'] = 0;
			$role_count_list['mytho'] = $role_count_list['owlman'] = 0;

			// NS_PATCH
			$role_count_list['noble'] = $role_count_list['slave'] = 0;
			$role_count_list['pengu'] = 0;

			$wftrans = $wftransx = 0;

			if($user_count >= 20) {
				$outwfwtr = $outwfasm = $outwfbsk = $outwfwnd = 1;
			}
			if($user_count >= 16) {
				$outwfxwnd = 1;
			}

			for($i=0 ; $i < $user_count ; $i++)
			{
				$regist_uname = $uname_arr[$i];
				$regist_role = $role_arr[$i];

				if($user_count >= 20) {

					if ($regist_role == "wolf" && $outwfbig == 1) {
						$regist_role = "wolf wfbig";
						$outwfbig = 0;
					} 
					else if ($regist_role == "wolf" && randme(1001,2000) > 1998 && $outwfwtr == 1) {
						$regist_role = "wolf wfwtr";
						$outwfwtr = 0;
						++$wftrans;
					}
					else if ($regist_role == "wolf" && randme(1001,2000) > 1998 && $outwfasm == 1) {
						$regist_role = "wolf wfasm";
						$outwfasm = 0;
						++$wftrans;
					} 
					else if ($regist_role == "wolf" && randme(1001,2000) > 1998 && $outwfbsk == 1) {
						$regist_role = "wolf wfbsk";
						$outwfbsk = 0;
						++$wftrans;
					} 
					else if ($regist_role == "wolf" && randme(1001,2000) > 1998 && $outwfwnd == 1) {
						$regist_role = "wolf wfwnd";
						$outwfwnd = 0;
						++$wftrans;
					}
				}

				if($user_count >= 16) {
					if ($regist_role == "wolf" && randme(1001,2000) > 1995 && $outwfxwnd == 1) {
						$regist_role = "wolf wfxwnd";
						$outwfxwnd = 0;
						++$wfxtrans;
					}
				}

				$db->query("update user_entry set role = '$regist_role' where room_no = '$room_no' and user_no > '0' and uname = '$regist_uname'");

				if( strstr((string) $regist_role,"human") )
					$role_count_list['human']++;
				if( strstr((string) $regist_role,"wolf") )
					$role_count_list['wolf']++;
				if( strstr((string) $regist_role,"mage") )
					$role_count_list['mage']++;
				if( strstr((string) $regist_role,"necromancer") )
					$role_count_list['necromancer']++;
				if( strstr((string) $regist_role,"mad") )
					$role_count_list['mad']++;
				if( strstr((string) $regist_role,"guard") )
					$role_count_list['guard']++;
				if( strstr((string) $regist_role,"common") )
					$role_count_list['common']++;
				if( strstr((string) $regist_role,"fox") )
					$role_count_list['fox']++;
				if( strstr((string) $regist_role,"betr") )
					$role_count_list['betr']++;
				if( strstr((string) $regist_role,"fosi") )
					$role_count_list['fosi']++;
				if( strstr((string) $regist_role,"poison") )
					$role_count_list['poison']++;
				if( strstr((string) $regist_role,"cat") )
					$role_count_list['cat']++;
				if( strstr((string) $regist_role,"decide") )
					$role_count_list['decide']++;
				if( strstr((string) $regist_role,"authority") )
					$role_count_list['authority']++;
				if( strstr((string) $regist_role,"lovers") )
					$role_count_list['lovers']++;
				if( strstr((string) $regist_role,"wfbig") )
					$role_count_list['wfbig']++;

				if( strstr((string) $regist_role,"wfwtr") || strstr((string) $regist_role,"wfasm") || strstr((string) $regist_role,"wfbsk")  || strstr((string) $regist_role,"wfwnd") )
					$role_count_list['wfxxx']++;

				if( strstr((string) $regist_role,"mytho") )
					$role_count_list['mytho']++;
				if( strstr((string) $regist_role,"owlman") )
					$role_count_list['owlman']++;

				if( strstr((string) $regist_role,"pengu") )
					$role_count_list['pengu']++;

				if( strstr((string) $regist_role,"noble") )
					$role_count_list['noble']++;
				if( strstr((string) $regist_role,"slave") )
					$role_count_list['slave']++;

				if( strstr((string) $regist_role,"spy") )
					$role_count_list['spy']++;

			}

			$db->query("update user_entry set role = 'GM' where room_no = '$room_no' and user_no = ".($max_user + 1));

			$poisonis = 'poison';

			//更新職業資料
			REstartDataROLE();

			//檢查人渣的職業並重新排列
			// 會有渣毒喔。 
			// or role LIKE '".$poisonis."%'  
			//  and role NOT LIKE '%".$poisonis."%' 
			$dummy_role = $db->query("select uname,role,lovers,noble from user_entry where room_no = '$room_no' and user_no > '0' and uname = 'dummy_boy' and
									 (role LIKE 'wolf%' or role LIKE 'fox%' or lovers = '1' or noble = '1') limit 1;"); //  
			$dummy_arr = $db->fetch_array($dummy_role);
			if ($dummy_arr['uname'] == 'dummy_boy') {
				$user_role = $db->query("select uname,role,lovers,noble from user_entry where room_no = '$room_no' and user_no > '0' and uname != 'dummy_boy' and role <> 'GM' and 
									 role NOT LIKE '%wolf%' and role NOT LIKE '%fox%' and role NOT LIKE '%lovers%' and lovers = '0' and noble = '0'
									 order by rand() limit 1;");//  
				$user_arr = $db->fetch_array($user_role);
				//人渣
				$db->query("update user_entry set role = '".$user_arr['role']."',lovers = '".$user_arr['lovers']."',noble = '".$user_arr['noble']."' where room_no = '$room_no' and user_no > '0' and uname = 'dummy_boy';");
				//玩家
				$db->query("update user_entry set role = '".$dummy_arr['role']."',lovers = '".$dummy_arr['lovers']."',noble = '".$dummy_arr['noble']."' where room_no = '$room_no' and user_no > '0' and uname = '".$user_arr['uname']."';");
			}

			//如果要匿名模式處理
			if (strstr((string) $game_option,"usr_guest")) {
				$rand_room = $db->query("select uid  from user_entry where room_no = '$room_no' and user_no > 0 and user_no <= '$max_user' and live = 'live' and uname != 'dummy_boy' order by rand();");
				if (strstr((string) $game_option,"dummy_boy")) {
					$room_user_no = 2;
				} else {
					$room_user_no = 1;
				}
				while ($room_arr = $db->fetch_array($rand_room)) {
					$db->query("UPDATE user_entry set user_no = '$room_user_no' where uid = '".$room_arr['uid']."';");
					$room_user_no++;
				}
			}

			//それぞれの役割が何人ずつなのか系統メッセージ
			//$role_count_list = array_count_values($role_arr);

			$role_list_message = "村民".$role_count_list['human']."　人狼".$role_count_list['wolf']."　占卜師".$role_count_list['mage'];
			if ($role_count_list['necromancer']) {
				$role_list_message .="　靈能者".$role_count_list['necromancer'];
			}
			if ($role_count_list['mad']) {
				$role_list_message .="　狂人".$role_count_list['mad'];
			}
			if ($role_count_list['spy']) {
				$role_list_message .="　間諜".$role_count_list['spy'];
			}
			if ($role_count_list['guard']) {
				$role_list_message .="　獵人".$role_count_list['guard'];
			}
			if ($role_count_list['common']) {
				$role_list_message .="　共有者".$role_count_list['common'];
			}
			if ($role_count_list['fox']) {
				$role_list_message .="　妖狐".$role_count_list['fox'];
			}
			if ($role_count_list['betr']) {
				$role_list_message .="　背德".$role_count_list['betr'];
			}
			if ($role_count_list['fosi']) {
				$role_list_message .="　子狐".$role_count_list['fosi'];
			}
			if ($role_count_list['poison']) {
				$role_list_message .="　埋毒者".$role_count_list['poison'];
			}
			if ($role_count_list['cat']) {
				$role_list_message .="　貓又".$role_count_list['cat'];
			}
			if ($role_count_list['mytho']) {
				$role_list_message .="　說謊狂".$role_count_list['mytho'];
			}
			if ($role_count_list['owlman']) {
				$role_list_message .="　夜梟".$role_count_list['owlman'];
			}
			if ($role_count_list['pengu']) {
				$role_list_message .="　小企鵝".$role_count_list['pengu'];
			}
			if ($role_count_list['noble']) {
				$role_list_message .="　貴族".$role_count_list['noble'];
			}
			if ($role_count_list['slave']) {
				$role_list_message .="　奴隸".$role_count_list['slave'];
			}

			if ($role_count_list['decide']) {
				$role_list_message .="　(決定者".$role_count_list['decide'].")";
			}
			if ($role_count_list['authority']) {
				$role_list_message .="　(權力者".$role_count_list['authority'].")";
			}
			if ($role_count_list['lovers']) {
				$role_list_message .="　(戀人".$role_count_list['lovers'].")";
			}

			if ($role_count_list['wfbig']) {
				$role_list_message .="　(大狼".$role_count_list['wfbig'].")";
			}

			if ($role_count_list['wfxxx']) {
				$role_list_message .="　(？狼".$role_count_list['wfxxx'].")";
			}


			$time = time();  //現在時刻、GMTとの時差を足す

			//役割リスト通知
			$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
										values($room_no,1,'night system','system',$time,'$role_list_message','0')");

			//殘片發生
			if($wftrans > 0) {
				++$time;
				$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
						   values($room_no,1,'night system','system',$time,'似乎傳來了特別淒厲的狼嗥聲……。','0')");
			}

			//偽殘片發生
			if($wfxtrans > 0) {
				++$time;
				$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
						   values($room_no,1,'night system','system',$time,'似乎傳來了特別淒厲的……不對，這大概是聽錯了。','0')");
			}


			$db->query("update room set last_updated = '$time' where room_no = '$room_no'"); //最終書き込みを更新


			//初日の処刑投票のカウントを1に初期化(再投票で増える)
			$db->query("insert into system_message (room_no,message,type,date)
				values ($room_no , '1' , 'VOTE_TIMES' ,1)");

			if (strstr((string) $game_option,"dummy_autolw")) {
				UpdateDummyLastWords("dummy_autolw");
			} else if (strstr((string) $game_option,"dummy_isred")) {
				UpdateDummyLastWords("dummy_isred");
			}

		}
		else //全員が投票されてないとき
			return;
	}
	elseif($situation == 'KICK_DO') //ゲーム開始前のキック
	{
		//今回投票した相手へ何人投票しているか
		$res_vote = $db->query("select count(vote.uname) from user_entry,vote where user_entry.room_no = '$room_no'
												and vote.room_no = '$room_no' and user_entry.user_no > 0 and vote.date = '0' and vote.situation = '$situation'
									and vote.target_uname = user_entry.uname and user_entry.uid = '$target_handle_name'");
		$vote_count = $db->result($res_vote,0); //投票総数を取得

		$f_res_vote = $db->query("select count(vote.uname) from user_entry,vote where user_entry.room_no = '$room_no'
												and vote.room_no = '$room_no' and user_entry.user_no > 0 and vote.date = '0' and vote.situation = 'FKICK_DO'
									and vote.target_uname = user_entry.uname and user_entry.uid = '$target_handle_name'");

		$vote_count += $db->result($f_res_vote,0)*10;
		//if( strstr($game_option,"dummy_boy") ) //替身君使用なら替身君の分を加算
		//	$vote_count++;

		//$target_handle_name = $db->result($res_vote,0,0); //投票給のハンドルネームを取得

		//投票者が5人に達していない場合、処理を止める(Kick荒らし対策)
		if($vote_count < 4)
			return;

		//投票された人意外の投票した用戶数を取得
		//$res_user = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no <> $target_no");
		//$user_count = $db->result($res_user,0,0);


		//echo "$vote_count <br />";
		//print($user_count);

		//5人以上の投票が合った場合に処理
		if($vote_count > 4)
		{
			//Kickする人の用戶Noを取得
			$res_target_no = $db->query("select user_no,handle_name from user_entry where room_no = '$room_no'
																and uid = '$target_handle_name' and user_no > '0'");
			$target_arr = $db->fetch_array($res_target_no);
			$target_no = $target_arr['user_no'];
			$target_handle_name2 = $target_arr['handle_name'];

			if($target_no == 1 && strstr((string) $game_option, 'dummy_boy')) return;

			//Kickする人のデータ削除
			//$db->query("delete from user_entry where room_no = '$room_no' and handle_name = '$target_handle_name'");

			//Kickされた人は死亡、user_noを-1、SessionIDを削除する
			$db->query("update user_entry set user_no = -1 , live = 'dead' , session_id = NULL
									where room_no = '$room_no' and user_no > '0' and uid = '$target_handle_name'");

		//	$db->query("update room set status = 'waiting' where room_no = '$room_no'"); //満員の場合、募集中に戻す
		//	$db->query("update room set day_night = 'beforegame' where room_no = '$room_no'"); //満員の場合、募集中に戻す

			$db->query("delete from vote where room_no = '$room_no'"); //今までの投票を全部削除

			//キックした人の空いた場所に詰める
/*			for($i = $target_no ; $i < $user_count+1 ; $i++)
			{
				$next = $i + 1;
				$db->query("update user_entry set user_no = $i where user_no = $next");
			}*/
			$db->query("update user_entry set user_no = user_no-1 where room_no = '$room_no' and user_no > $target_no and user_no < ".($max_user+1));

			$time = time();  //現在時刻、GMTとの時差を足す
		//	$db->query("update room set last_updated = '$time' where room_no = '$room_no'"); //最終書き込みを更新
			$db->query("update room set status = 'waiting',day_night = 'beforegame',last_updated = '$time' where room_no = '$room_no'");

			$time++; //投票メッセージより後に表示されるように
			//出て行ったメッセージ
			if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
				$target_handle_name2 = "玩家".$target_no."號";
			}
			$kick_message_str = msgimg($msg_human_image)."$target_handle_name2 人間蒸發、被轉學了";
			$res = $db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
						values ($room_no,$date,'$day_night system','system','$time','$kick_message_str','0')");

			$time++; //出て行ったメッセージより後に表示されるように
			$reset_vote_str = msgimg($msg_sys_image).'＜投票重新開始 請盡速重新投票＞';
			$res = $db->query("insert into talk (room_no,date,location,uname,time,sentence,spend_time)
						values ($room_no,$date,'$day_night system','system','$time','$reset_vote_str','0')");
		}
		else
			return; //票がそろってないとき
	}
	elseif($situation == 'VOTE_KILL') //お白の投票での処刑
	{
		//投票総数を取得
		$res_vote = $db->query("select count(uname) from vote where room_no = '$room_no'
														and date = $date and situation = '$situation'
														and vote_times = '$vote_times'");
		$vote_count = $db->result($res_vote,0);

		//生きている用戶数を取得
		$res_user = $db->query("select uname,handle_name,role from user_entry
												where room_no = '$room_no' and user_no > '0' and live = 'live' and role <> 'GM' order by user_no");
		$user_count = $db->num_rows($res_user);

		//echo "$vote_count vote_kill<br />";
		//print($user_count);

		//全員が投票していた場合
		if($vote_count >= $user_count)
		{
			$max_voted_number = 0; //最も票を入れられた人の票数
			$vote_number_list = []; //投票された人と受けた総票数のリスト（user1に３票入っていた ：$vote_number_list['user1'] => 3）
			$vote_role_list = []; //投票された人の役割リスト
			$live_handle_name_list = []; //生きている人のハンドルネームリスト

			//一人ずつ自分に投票された数を調べて処刑すべき人を決定する
			while($this_user_arr = $db->fetch_array($res_user))
			{
				//用戶Noの若い順から処理
			//	$this_user_arr = $db->fetch_array($res_user);
				$this_uname = $this_user_arr['uname'];
				$this_handle_name = $this_user_arr['handle_name'];
				$this_role = $this_user_arr['role'];

				//自分に投票された総評数
				$res_voted_number = $db->query("select sum(vote_number) from vote where room_no = '$room_no' and date = $date
													and situation = '$situation' and vote_times = '$vote_times'
													and target_uname = '$this_uname'");
				//投票された総票数
				$this_voted_number = (int)$db->result($res_voted_number,0,0);

				//自分が投票した票数
				$res_vote_number =$db->query("select vote_number from vote where room_no = '$room_no' and date = $date
													and situation = '$situation' and vote_times = '$vote_times'
													and uname = '$this_uname'");
				$this_vote_number = (int)$db->result($res_vote_number,0,0);


				//自分が投票した人のハンドルネームを取得
				$res_vote_target = $db->query("select user_entry.handle_name as handle_name from user_entry,vote 
													where user_entry.room_no = '$room_no' and user_entry.user_no > 0 and vote.room_no = '$room_no' 
														and vote.date = $date
														and vote.situation = '$situation' and vote_times = '$vote_times'
														and vote.uname = '$this_uname' and user_entry.uname = vote.target_uname");
				$this_vote_target = $db->result($res_vote_target,0,0);


				//投票結果をタブ区切りで出力 ( 誰が [TAB] 誰に [TAB] 自分への投票数 [TAB] 自分の投票数 [TAB] vote_times)
				$system_message = $this_handle_name . "\t" .$this_vote_target . "\t" . $this_voted_number
																."\t".$this_vote_number . "\t" . (int)$vote_times ;

				//投票情報を系統メッセージに登錄
				$db->query("insert into system_message (room_no,message,type,date)
														values ($room_no,'$system_message','$situation',$date)");

				//最大票数を更新
				if($this_voted_number > $max_voted_number)
					$max_voted_number = $this_voted_number;

				//投票された人と受けた総票数のリスト（user1に３票入っていた ：$vote_HN_number_list['user1_handle_name'] => 3）
				$vote_HN_number_list[$this_handle_name] = $this_voted_number;
				$vote_uname_number_list[$this_uname] = $this_voted_number; //$vote_uname_number_list['user1_uname'] => 3）

				$vote_role_list[$this_handle_name] = $this_role; //$vote_role_list['user1'] => 'human'
				array_push($live_handle_name_list,$this_handle_name); //生きている人のリスト
			}
			$db->free_result($res_user);

			//最大票数を集めた人の数を取得
			$max_voted_num_arr = array_count_values($vote_HN_number_list); // $max_voted_num_arr[票数] = その票数は何個あったか
			$max_voted_num = $max_voted_num_arr[$max_voted_number]; //$max_voted_num_arr[最大票数]の人の人数

			//最大票数の人のハンドルネームのリストを取得
			//$max_voted_HN_arr[0,1,2･･･] = 最大票数の人のハンドルネーム
			$max_voted_HN_arr = array_keys($vote_HN_number_list,$max_voted_number); 

			//$max_voted_HN_arr[0,1,2･･･] = 最大票数の人のハンドルネーム
			$max_voted_uname_arr = array_keys($vote_uname_number_list,$max_voted_number); 


			//echo "<pre>";
			//print_r($max_voted_num_arr);

			if( $max_voted_num == 1) //一人だけの場合、処刑して夜にする
			{
				$max_voted_handle_name = $max_voted_HN_arr[0];
				//処刑される人の役割
				$max_voted_role = $vote_role_list[$max_voted_handle_name];

				//処刑
				VoteKill($max_voted_handle_name,$max_voted_role,$live_handle_name_list);


				//echo "処刑その１";
			}
			else //複数いたばあい、決定者が居なければ再投票
			{
				$re_voting_flag = true; //再投票フラグ初期化

				for($i=0 ; $i < $max_voted_num ; $i++)
				{
					$max_vote_uname = $max_voted_uname_arr[$i]; //投票された人の用戶名取得
					$max_voted_handle_name = $max_voted_HN_arr[$i]; //投票された人のハンドルネーム取得
					$max_voted_role = $vote_role_list[$max_voted_handle_name]; //投票された人の役割取得

					//投票者の役割取得
					$res_max_voter_role = $db->query("select user_entry.role from user_entry,vote 
																where user_entry.room_no = '$room_no' and user_entry.user_no > 0
																and vote.room_no = '$room_no' and vote.date = $date
																and vote.situation = '$situation'
																and vote.vote_times = '$vote_times'
																and vote.uname = user_entry.uname
																and vote.target_uname = '$max_vote_uname'");

				//	$max_voter_count = $db->num_rows($res_max_voter_role);

					while($max_voter_role = $db->fetch_array($res_max_voter_role))
					{
					//	$max_voter_role = $db->result($res_max_voter_role,$j,0);

						//echo "$max_voter_role \r\n";

						if( strstr((string) $max_voter_role['role'],"decide") ) //投票者が決定者なら処刑
						{
							$re_voting_flag = false;
							break;
						}
					}

					if($re_voting_flag == false)
						break;
				}

				if($re_voting_flag == true) //再投票
				{
					//投票回数を増やす
					$next_vote_times = $vote_times +1 ;

					$db->query("update system_message set message = $next_vote_times where room_no = '$room_no'
									and date = $date and type = 'VOTE_TIMES'");


					//系統メッセージ
					$db->query("insert into system_message (room_no,message,type,date)
																values ($room_no,'$vote_times','RE_VOTE',$date)");


					$time = time();  //現在時刻、GMTとの時差を足す
					$time++;

					$revote_message = "請重新投票( $vote_times 回目)";
					$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
												values($room_no,$date,'$day_night system','system',$time,'$revote_message','0')");


					$db->query("update room set last_updated = '$time' where room_no = '$room_no'"); //最終書き込みを更新

					//echo "再投票です";
				}
				else //処刑して夜にする
				{
					VoteKill($max_voted_handle_name,$max_voted_role,$live_handle_name_list);

					//echo "処刑その２";
				}
			}
			//echo "処刑しました";

			//勝敗のチェック
			CheckVictory();
		}
		else //全員投票されてない時
			return;
	//晚上的狼 獵人 占 子狐 貓又
	} elseif($situation == 'WOLF_EAT' || $situation == 'MAGE_DO' || $situation == 'GUARD_DO' || $situation == 'FOSI_DO' || $situation == 'CAT_DO' || 
			 $situation == 'MYTHO_DO' || $situation == 'OWLMAN_DO' || $situation == 'PENGU_DO') 
	{
		//夜の投票数を取得
		$res_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date
								and (situation = 'WOLF_EAT' or situation = 'MAGE_DO' or situation = 'GUARD_DO' or situation = 'FOSI_DO' or situation = 'CAT_DO' 
								   or situation = 'MYTHO_DO' or situation = 'OWLMAN_DO' or situation = 'PENGU_DO')");
		$vote_count = $db->result($res_vote,0);

		//生きている狼の数を取得
		$wolf_count = 1; //狼は全員で一人分

		//生きている占い師の数を取得
		$res_mage_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and live = 'live'
																					and role like 'mage%'");

		$mage_count = $db->result($res_mage_count,0);

		//子狐人數取得
		$res_fosi_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and live = 'live'
									 and role like 'fosi%'");

		$fosi_count = $db->result($res_fosi_count,0);

		//生きている獵人の数を取得
		if( $date == 1 ) //初日は護衛できない
		{
			$guard_count = 0;
		}
		else
		{
			$res_guard_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and live = 'live'
																					and role like 'guard%'");
			$guard_count = $db->result($res_guard_count,0);
		}

		//生きている猫又+夜梟+小企鵝の数を取得
		if( $date == 1 ) //初日は復活できない
		{
			$cat_count = 0;
			$owl_count = 0;
			$pengu_count = 0;
		}
		else
		{
			$res_cat_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and live = 'live'
																					and role like 'cat%'");
			$cat_count = $db->result($res_cat_count,0);

			$res_owl_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and live = 'live'
										and role like 'owlman%'");
			$owl_count = $db->result($res_owl_count,0);

			$res_pengu_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and live = 'live'
										  and role like 'pengu%'");
			$pengu_count = $db->result($res_pengu_count,0);
		}

		if(($date == 1) && strstr((string) $game_option,"dummy_boy") )
		{
			//初日、替身君の役割が占い師の場合占い師の数に入れない
			$res_dummy_boy = $db->query("select role from user_entry where room_no = '$room_no' and user_no > '0' and uname = 'dummy_boy'");
			$dummy_boy_role = $db->result($res_dummy_boy,0,0);

			if( strstr((string) $dummy_boy_role,"mage") )
			{
				$mage_count--;
			}
			if( strstr((string) $dummy_boy_role,"fosi") )
			{
				$fosi_count--;
			}
		}

		// 取得活著的說謊狂人數
		if($date != 2)
		{
			$mytho_count = 0;
		} 
		else
		{
			$res_mytho_count = $db->query("select count(uname) from user_entry where room_no = '$room_no' and user_no > '0' and live = 'live'
										and role like 'mytho%'");
			$mytho_count = $db->result($res_mytho_count,0);

		}



		//全部の人数の和
		// CAUTION: SPY跟幼狼不含在此範圍內
		$user_count = $wolf_count + $mage_count + $guard_count + $fosi_count + $cat_count + $owl_count + $mytho_count + $pengu_count;
		//echo "$vote_count <br />";
		//print($user_count);

		//投票すべき人が全員投票していた場合
		if($vote_count >= $user_count)
		{

			// 企鵝最先動
			$pengu_query = $db->query("SELECT * from vote where room_no = '$room_no' AND date = $date AND situation = 'PENGU_DO'");
			while($pengu_arr = $db->fetch_array($pengu_query))
			{
				$res_pengu_count = $db->query("select count(*) from system_message where room_no = '$room_no' and type = 'PENGU_OK';"); // PENGU_DO
				if ($db->result($res_pengu_count,0) <= 2) {
					$pengu_tovote = $db->query("SELECT * from vote where room_no = '$room_no' AND date = $date AND uname = '".$pengu_arr['target_uname']."'");
					if ($pengu_toarr = $db->fetch_array($pengu_tovote)) {
						if (in_array($pengu_toarr['situation'],['MAGE_DO','GUARD_DO','CAT_DO','OWLMAN_DO'])) {

							$pengu_toname = $db->query("SELECT handle_name from user_entry where room_no = '$room_no' AND uname = '".$pengu_arr['target_uname']."'");
							$pengu_handlearr = $db->fetch_array($pengu_toname);

							$system_message = $pengu_handlearr['handle_name'];
							$db->query("insert into system_message (room_no,message,type,date)
										values($room_no,'$system_message','PENGU_OK',$date)");
														//exit;
							$db->query("DELETE from vote where room_no = '$room_no' AND date = $date AND uname = '".$pengu_arr['target_uname']."' LIMIT 1;");
						}
					}
				}
			}

			$res_wtr = $db->query("select count(type) from system_message where room_no = '$room_no' and date = $date and type = 'WOLF_CHILL'");
			$is_wtr = $db->result($res_wtr,0);

			$res_asm = $db->query("select count(type) from system_message where room_no = '$room_no' and date = $date and type = 'WOLF_FLAME'");
			$is_asm = $db->result($res_asm,0);

			// 極寒處理
			if($is_wtr) {
				$res_all_actions = $db->query("select vote.uname, user_entry.handle_name from vote, user_entry
																			where vote.room_no = '$room_no'
																					and user_entry.room_no = '$room_no' and user_entry.user_no > 0
																					and vote.date = $date
																					and (vote.situation <> 'WOLF_EAT' and 
                                                                                         vote.situation <> 'MYTHO_DO' and 
                                                                                         vote.situation <> 'PENGU_DO' and
                                                                                         vote.situation <> 'SPY_DO')
																					and vote.uname = user_entry.uname");
				$wtrout = 0;
				while($act_arr = $db->fetch_array($res_all_actions)) {			
					$vote_uname = $act_arr['uname'];
					$vote_hname = $act_arr['handle_name'];
					if (randme(1001,1100) < 1085) {
						$db->query("DELETE from vote where room_no = '$room_no' AND date = $date AND uname = '$vote_uname' LIMIT 1;");

						if($wtrout == 0) {
							$db->query("insert into system_message (room_no,message,type,date) values($room_no,'WOLF_CHILL','WCHILL_OK',$date)");
							$wtrout = 1;
						}
					}
				}
			}

			// 炎上處理
			if($is_asm) {
				$res_guard = $db->query("select count(vote.uname) from vote, user_entry
																			where vote.room_no = '$room_no'
																					and user_entry.room_no = '$room_no' and user_entry.user_no > 0
																					and vote.date = $date
																					and vote.situation = 'GUARD_DO'
																					and vote.uname = user_entry.uname");

				$guard_c = $db->result($res_asm,0);

				if($guard_c > 0) {
					$db->query("delete from vote where vote.room_no = '$room_no' and vote.date = $date and vote.situation = 'GUARD_DO'");
					$db->query("insert into system_message (room_no,message,type,date) values($room_no,'WOLF_FLAME','WFLAME_OK',$date)");
				}
			}

			// 幼狼處理（無視獵人）
			$res_wndwolf_vote = $db->query("select vote.target_uname,user_entry.role,user_entry.handle_name from vote,user_entry
																			where vote.room_no = '$room_no'
																					and user_entry.room_no = '$room_no' and user_entry.user_no > 0
																					and vote.date = $date
																					and vote.situation = 'HUG_DO'
																					and vote.target_uname = user_entry.uname");

			while($wndwolf_target_arr = $db->fetch_array($res_wndwolf_vote))
			{																		

				$wndwolf_target_uname = $wndwolf_target_arr['target_uname'];
				$wndwolf_target_role = $wndwolf_target_arr['role'];
				$wndwolf_target_handle_name = $wndwolf_target_arr['handle_name'];
				$db->free_result($res_wndwolf_vote);

				// 30%機率萌殺
				if ( randme(101,200) < 130 ) 
				{				

					//貓又處理
					$iswdel = randme(101,200);
					if (strstr((string) $wolf_target_role,"cat") && $iswdel >= 190 && !$is_asm && $wndwolf_target_uname != 'dummy_boy') {
						/*
						//貓又不死
						$db->query("insert into system_message (room_no,message,type,date)
									values($room_no,'$wolf_target_handle_name','CAT_EAT',$date)");
						*/
						$catgoon = 1;
					} 
					else 
					{
						$wndwolf_target_role = KillPlayer($wndwolf_target_handle_name, 'HUG_KILLED');
						// 咬戀死戀
						if (strstr((string) $wndwolf_target_role,"lovers")) {
							$res_lovers = $db->query("select handle_name,role from user_entry where
													 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
							if ($another_lover = $db->fetch_array($res_lovers)) {
								KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');

								// 咬戀死戀狐拖背 
								$res_fox_count = $db->query("select count(*) from user_entry where
																room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
								$fox_count = $db->result($res_fox_count,0);
								if ($fox_count == 0) {
									$res_betr = $db->query("select handle_name, role from user_entry where
															 room_no = '$room_no' and user_no > '0' and role LIKE 'betr%' and live = 'live'");

									if ($lf_betr_arr = $db->fetch_array($res_betr))
										KillPlayer($lf_betr_arr['handle_name'], 'BETR_DEAD_night');
								}							

							}
						}

						/* 死狐 - SPECIAL CASE*/
						if( strstr((string) $wndwolf_target_role,"fox") )
						{
							//計算妖狐數量
							$res_fox_count = $db->query("select count(*) from user_entry where
															room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
							$fox_count = $db->result($res_fox_count,0);
							if ($fox_count == 0) {
								//背德自殺
								$res_betr = $db->query("select handle_name, role from user_entry where
														 room_no = '$room_no' and user_no > '0' and role LIKE 'betr%' and live = 'live'");
								if ($mage_betr_arr = $db->fetch_array($res_betr)) {
									KillPlayer($mage_betr_arr['handle_name'], 'BETR_DEAD_night');

									// 咬死狐拖戀背死戀
									if(strstr((string) $mage_betr_arr['role'], "lovers")) {
										$res_lovers = $db->query("select handle_name from user_entry where
																 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
										if ($another_lover = $db->fetch_array($res_lovers))
											KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');
									}
								}
							}						
						}

						// 目前設計上萌殺不噴
						/*
						//食べられた人が埋毒者の場合
						if ( strstr($wndwolf_target_role,"poison") || strstr($wndwolf_target_role,"cat")) {
							//生きている狼を取得
							$res__wndwolf_list = $db->query("select handle_name, role from user_entry
														 where room_no = '$room_no' and role like 'wolf wfwnd%' and live = 'live'
														 and user_no > '0'");

							$wndwolf_list_arr = array();
							while($poison_wndwolf = $db->fetch_array($res__wndwolf_list)) {
								$wndwolf_list_arr[] = $poison_wndwolf['handle_name'];
								$wndwolf_list_role_arr[] = $poison_wndwolf['role'];
							}

							$rand_key = array_randme($wndwolf_list_arr,1);
							$poison_dead_wndwolf_handle_name = $wndwolf_list_arr[$rand_key];

							$poison_wndwolf_role = KillPlayer($poison_dead_wndwolf_handle_name, 'POISON_DEAD_night');

							//咬毒噴戀狼死戀 (Failsafe)
							if (strstr($poison_wndwolf_role,"lovers")) {
								$res_lovers = $db->query("select handle_name from user_entry where
														 room_no = '$room_no' and lovers = '1' and user_no > '0' and live = 'live'");

								if ($another_lover = $db->fetch_array($res_lovers))
									KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');
							}
						}
						*/
					}
				}
			}

			//狼と獵人は同時に処理
			//獵人の投票給用戶名、獵人のハンドルネームを取得
			$res_guard_vote = $db->query("select vote.target_uname,user_entry.handle_name from vote,user_entry
																			where vote.room_no = '$room_no'
																					and user_entry.room_no = '$room_no' and user_entry.user_no > 0
																					and vote.date = $date
																					and vote.situation = 'GUARD_DO'
																					and vote.uname = user_entry.uname");

			//狼の投票給用戶名とその役割を取得
			$res_wolf_vote = $db->query("select vote.target_uname,user_entry.role,user_entry.handle_name from vote,user_entry
																			where vote.room_no = '$room_no'
																					and user_entry.room_no = '$room_no' and user_entry.user_no > 0
																					and vote.date = $date
																					and vote.situation = 'WOLF_EAT'
																					and vote.target_uname = user_entry.uname");
			$wolf_target_arr = $db->fetch_array($res_wolf_vote);
			$wolf_target_uname = $wolf_target_arr['target_uname'];
			$wolf_target_role = $wolf_target_arr['role'];
			$wolf_target_handle_name = $wolf_target_arr['handle_name'];
			$db->free_result($res_wolf_vote);

			$res_wolf_target_live_c = $db->query("select count(handle_name) from user_entry 
											   where room_no = '$room_no' and uname = '$wolf_target_uname' and live='live'");
			$wolf_target_live_c = $db->result($res_wolf_target_live_c,0);

			$guard_success_flag = false;

			// 目標還活著才繼續判定
			if($wolf_target_live_c > 0) {
				while($guard_arr = $db->fetch_array($res_guard_vote)) //護衛成功かチェック
				{
				//	$guard_arr = $db->fetch_array($res_guard_vote);
					$guard_handle_name = $guard_arr['handle_name'];
					$guard_target_uname = $guard_arr['target_uname'];

					if($guard_target_uname === $wolf_target_uname) //護衛成功
					{
						//護衛成功のメッセージ
						$system_message = $guard_handle_name . "\t" . $wolf_target_handle_name;
						$db->query("insert into system_message (room_no,message,type,date)
												values($room_no,'$system_message','GUARD_SUCCESS',$date)");
						$guard_success_flag = true;
					}
				}
				$db->free_result($res_guard_vote);

				if ( $guard_success_flag == true) {
					//護衛成功
				}
				elseif( strstr((string) $wolf_target_role,"fox") && !$is_asm) //食べる先が狐の場合食べれない
				{
					$db->query("insert into system_message (room_no,message,type,date)
											values($room_no,'$wolf_target_handle_name','FOX_EAT',$date)");
				} 
				else {//護衛されてなければ食べる

					//貓又處理
					$iswdel = randme(101,200);
					if (strstr((string) $wolf_target_role,"cat") && $iswdel >= 190 && !$is_asm && $wolf_target_uname != 'dummy_boy') {
						//貓又不死
						$db->query("insert into system_message (room_no,message,type,date)
									values($room_no,'$wolf_target_handle_name','CAT_EAT',$date)");
						$catgoon = 1;
					} else {

						$res_wolf_target_role = $db->query("select role from user_entry 
														   where room_no = '$room_no'  and user_no > '0' and handle_name = '$wolf_target_handle_name'");
						$wolf_target_role = $db->result($res_wolf_target_role,0);

						// 奴隸擋咬
						if(strstr((string) $wolf_target_role,"noble"))  
						{
							$res_slave_c = $db->query("select count(handle_name) from user_entry 
															   where room_no = '$room_no'  and user_no > '0' and slave = '1' and live='live'");
							$slave_c = $db->result($res_slave_c,0);

							if($slave_c > 0) {
								$res_wolf_target_handle_name = $db->query("select handle_name from user_entry 
															   where room_no = '$room_no'  and user_no > '0' and slave = '1' and live='live'");

								$wolf_target_handle_name = $db->result($res_wolf_target_handle_name,0);
							}
						} 

						$wolf_target_role = KillPlayer($wolf_target_handle_name, 'WOLF_KILLED');
						// 咬戀死戀
						if (strstr((string) $wolf_target_role,"lovers")) {
							$res_lovers = $db->query("select handle_name,role from user_entry where
													 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
							if ($another_lover = $db->fetch_array($res_lovers)) {
								KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');

								// 咬戀死戀狐拖背 
								$res_fox_count = $db->query("select count(*) from user_entry where
																room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
								$fox_count = $db->result($res_fox_count,0);
								if ($fox_count == 0) {
									$res_betr = $db->query("select handle_name, role from user_entry where
															 room_no = '$room_no' and user_no > '0' and role LIKE 'betr%' and live = 'live'");

									if ($lf_betr_arr = $db->fetch_array($res_betr))
										KillPlayer($lf_betr_arr['handle_name'], 'BETR_DEAD_night');
								}							

							}
						}

						/* 咬死狐 - SPECIAL CASE*/
						if( strstr((string) $wolf_target_role,"fox") )
						{
							//計算妖狐數量
							$res_fox_count = $db->query("select count(*) from user_entry where
															room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
							$fox_count = $db->result($res_fox_count,0);
							if ($fox_count == 0) {
								//背德自殺
								$res_betr = $db->query("select handle_name, role from user_entry where
														 room_no = '$room_no' and user_no > '0' and role LIKE 'betr%' and live = 'live'");
								if ($mage_betr_arr = $db->fetch_array($res_betr)) {
									KillPlayer($mage_betr_arr['handle_name'], 'BETR_DEAD_night');

									// 咬死狐拖戀背死戀
									if(strstr((string) $mage_betr_arr['role'], "lovers")) {
										$res_lovers = $db->query("select handle_name from user_entry where
																 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
										if ($another_lover = $db->fetch_array($res_lovers))
											KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');
									}
								}
							}						
						}
					}

					//食べられた人が埋毒者の場合
					if ( strstr((string) $wolf_target_role,"poison") || strstr((string) $wolf_target_role,"cat")) {
						//生きている狼を取得
						$res__wolf_list = $db->query("select handle_name, role from user_entry
													 where room_no = '$room_no' and user_no > '0' and role like 'wolf%' and live = 'live'");

						$wolf_list_arr = [];
						while($poison_wolf = $db->fetch_array($res__wolf_list)) {
							$wolf_list_arr[] = $poison_wolf['handle_name'];
							$wolf_list_role_arr[] = $poison_wolf['role'];
						}


						// 優先噴狼夢。（？）
						$poison_dead_wolf_handle_name = '';
						foreach($wolf_list_role_arr as $k => $role) {
							if(strstr((string) $role, 'wfasm')) {
								$poison_dead_wolf_handle_name = $wolf_list_arr[$k];
								break;
							}
						}

						// 噴不到就噴其他人
						if($poison_dead_wolf_handle_name == '') {
							$rand_key = array_randme($wolf_list_arr,1);
							$poison_dead_wolf_handle_name = $wolf_list_arr[$rand_key];
						}

						$poison_wolf_role = KillPlayer($poison_dead_wolf_handle_name, 'POISON_DEAD_night');

						//咬毒噴戀狼死戀
						if (strstr((string) $poison_wolf_role,"lovers")) {
							$res_lovers = $db->query("select handle_name from user_entry where
													 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");

							if ($another_lover = $db->fetch_array($res_lovers))
								KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');
						}
					}
				}
			}

			//占い師の用戶名、ハンドルネームと、占い師の生存、占い師が占った用戶名取得
			$res_mage_vote = $db->query("select user_entry.uname,user_entry.handle_name,user_entry.live,vote.target_uname
																			from vote,user_entry
																			where vote.room_no = '$room_no'
																					and user_entry.room_no = '$room_no' and user_entry.user_no > 0
																					and vote.date = $date
																					and vote.situation = 'MAGE_DO'
																					and vote.uname = user_entry.uname");

			//占い師の人数分、処理
			while($mage_arr = $db->fetch_array($res_mage_vote))
			{
			//	$mage_arr = $db->fetch_array($res_mage_vote);
				$mage_uname = $mage_arr['uname'];
				$mage_handle_name = $mage_arr['handle_name'];
				$mage_live = $mage_arr['live'];
				$mage_target_uname = $mage_arr['target_uname'];

				if($mage_live == 'dead') //直前に狼に食べられていたらこの占いは無効
					continue;

				//占い師に占われた人のハンドルネームと生存、役割を取得
				$res_mage_voted = $db->query("select handle_name,role,live from user_entry where room_no = '$room_no' and user_no > '0'
															and uname = '$mage_target_uname'");

				$mage_target_arr = $db->fetch_array($res_mage_voted);
				$mage_target_handle_name = $mage_target_arr['handle_name']; //投票給のハンドルネーム取得
				$mage_target_role = $mage_target_arr['role']; //投票給の役割取得
				$mage_target_live = $mage_target_arr['live']; //投票給の役割取得



				if( strstr((string) $mage_target_role,"fox") && ($mage_target_live == 'live') ) //狐が占われたら死亡
				{
					KillPlayer($mage_target_handle_name, 'FOX_DEAD');

					//計算妖狐數量
					$res_fox_count = $db->query("select count(*) from user_entry where
													room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
					$fox_count = $db->result($res_fox_count,0);
					if ($fox_count == 0) {
						//背德自殺
						$res_betr = $db->query("select handle_name, role from user_entry where
												 room_no = '$room_no' and user_no > '0' and role LIKE 'betr%' and live = 'live'");
						if ($mage_betr_arr = $db->fetch_array($res_betr)) {
							KillPlayer($mage_betr_arr['handle_name'], 'BETR_DEAD_night');

							// 占狐拖戀背死戀
							if(strstr((string) $mage_betr_arr['role'], "lovers")) {
								$res_lovers = $db->query("select handle_name from user_entry where
														 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
								if ($another_lover = $db->fetch_array($res_lovers))
									KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');
							}
						}
					}

					// 占戀狐死戀
					//戀人自殺
					if (strstr((string) $mage_target_role,"lovers")) {
						$res_lovers = $db->query("select handle_name from user_entry where
												 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
						if ($another_lover = $db->fetch_array($res_lovers))
							KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');
					}
				}

				//占い結果を出力
				if (strstr((string) $mage_target_role,"wolf")) { //占い先が狼の場合
					$iswfbig = randme(101,200);
					if (strstr((string) $mage_target_role,"wfbig") && $iswfbig >= 130)
						$system_message = $mage_handle_name . "\t" . $mage_target_handle_name . "\t" . "human";
					else
						$system_message = $mage_handle_name . "\t" . $mage_target_handle_name . "\t" . "wolf";
				}
				else //占い先が狼以外の場合
					$system_message = $mage_handle_name . "\t" . $mage_target_handle_name . "\t" . "human";
				$db->query("insert into system_message (room_no,message,type,date)
											values($room_no,'$system_message','MAGE_RESULT',$date)");

			}

			//子狐資料處理
			$res_fosi_vote = $db->query("select user_entry.uname,user_entry.handle_name,user_entry.live,vote.target_uname
										from vote,user_entry
										where vote.room_no = '$room_no'
										and user_entry.room_no = '$room_no' and user_entry.user_no > 0
										and vote.date = $date
										and vote.situation = 'FOSI_DO'
										and vote.uname = user_entry.uname");

			//子狐處理
			while($fosi_arr = $db->fetch_array($res_fosi_vote)) {
				$fosi_uname = $fosi_arr['uname'];
				$fosi_handle_name = $fosi_arr['handle_name'];
				$fosi_live = $fosi_arr['live'];
				$fosi_target_uname = $fosi_arr['target_uname'];

				if($fosi_live == 'dead') {//被狼咬死無效
					continue;
				}

				//子狐狀態取得
				$res_fosi_voted = $db->query("select handle_name,role,live from user_entry where room_no = '$room_no' and user_no > '0'
											 and uname = '$fosi_target_uname'");

				$fosi_target_arr = $db->fetch_array($res_fosi_voted);
				$fosi_target_handle_name = $fosi_target_arr['handle_name']; //投票給のハンドルネーム取得
				$fosi_target_role = $fosi_target_arr['role']; //投票給の役割取得
				$fosi_target_live = $fosi_target_arr['live']; //投票給の役割取得

				//占い結果を出力
				$fosiok = randme(101,200);
				if(strstr((string) $fosi_target_role,"wolf")) { //占到狼
					$iswfbig = randme(101,200);
					if (strstr((string) $fosi_target_role,"wfbig") && $iswfbig >= 105)
						$system_message = $fosi_handle_name . "\t" . $fosi_target_handle_name . "\t" . "human";
					else
						$system_message = $fosi_handle_name . "\t" . $fosi_target_handle_name . "\t" . "wolf";
				} else //其它
					$system_message = $fosi_handle_name . "\t" . $fosi_target_handle_name . "\t" . "human";

				if ($fosiok >= 140)
					$system_message = $fosi_handle_name . "\t" . $fosi_target_handle_name . "\t" . "nofosi";
				$db->query("insert into system_message (room_no,message,type,date)
							values($room_no,'$system_message','FOSI_RESULT',$date)");
			}
			$db->free_result($res_fosi_vote);

			//貓又處理
			$res_cat_vote = $db->query("select user_entry.uname,user_entry.handle_name,user_entry.live,vote.target_uname
										 from vote,user_entry
										 where vote.room_no = '$room_no'
										 and user_entry.room_no = '$room_no' and user_entry.user_no > 0
										 and vote.date = $date
										 and vote.situation = 'CAT_DO'
										 and vote.uname = user_entry.uname");

			//貓又處理
			while ($cat_arr = $db->fetch_array($res_cat_vote)) {
				$cat_uname = $cat_arr['uname'];
				$cat_handle_name = $cat_arr['handle_name'];
				$cat_live = $cat_arr['live'];
				$cat_target_uname = $cat_arr['target_uname'];
				//貓又死亡無效
				if ($cat_live == 'dead' || $catgoon)
					continue;

				// 放棄行動
				if($cat_target_uname == ':;:NOP:;:') continue;

				$caturn = randme(101,200);
				// orig. val = 190
				//$caturn >= 180
				if ($caturn >= 190) {
					$res_cat_voted = $db->query("select uname,handle_name,live from user_entry where room_no = '$room_no' and user_no > '0'
												 and uname = '$cat_target_uname'");
					$res_cat_arr = $db->fetch_assoc($res_cat_voted);
					//$cat_target_uname
					if($res_cat_arr['live'] == 'dead') 
						ResuPlayer($res_cat_arr['handle_name'], 'CAT_RESU_night');
				}
			}
			$db->free_result($res_cat_vote);

			//夜梟處理
			$res_owlman_vote = $db->query("select user_entry.uname,user_entry.handle_name,user_entry.live,user_entry.role,vote.target_uname
										 from vote,user_entry
										 where vote.room_no = '$room_no'
										 and user_entry.room_no = '$room_no' and user_entry.user_no > 0
										 and vote.date = $date
										 and vote.situation = 'OWLMAN_DO'
										 and vote.uname = user_entry.uname");

			while ($owlman_arr = $db->fetch_array($res_owlman_vote)) {
				$owlman_uname = $owlman_arr['uname'];
				$owlman_role = $owlman_arr['role'];
				$owlman_handle_name = $owlman_arr['handle_name'];
				$owlman_live = $owlman_arr['live'];
				$owlman_target_uname = $owlman_arr['target_uname'];
				//死亡詛咒無效
				if ($owlman_live == 'dead')
					continue;

				// 放棄行動
				if($owlman_target_uname == ':;:NOP:;:') continue;

				$res_owlman_voted = $db->query("select role,handle_name,live from user_entry where room_no = '$room_no' and user_no > '0'
											and uname = '$owlman_target_uname'");
				$res_owlman_arr = $db->fetch_assoc($res_owlman_voted);
				$owlman_target_role = $res_owlman_arr['role'];
				$owlman_target_handle_name = $res_owlman_arr['handle_name'];
				$owlman_target_live = $res_owlman_arr['live'];

				// 狐狼抗詛咒
				if(strstr((string) $owlman_target_role, 'wolf') || strstr((string) $owlman_target_role, 'fox')) continue;

				// 目標死不處理
				if($owlman_target_live == 'dead') continue;

				//夜梟詛咒處理
				KillPlayer($owlman_target_handle_name, 'OWLMAN_KILLED');

				// 殺戀死戀
				if (strstr((string) $owlman_target_role,"lovers")) {
					$res_lovers = $db->query("select handle_name,role from user_entry where
											 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
					if ($another_lover = $db->fetch_array($res_lovers)) {
						KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_night');

						// 殺戀死戀狐拖背 
						$res_fox_count = $db->query("select count(*) from user_entry where
													room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
						$fox_count = $db->result($res_fox_count,0);
						if ($fox_count == 0) {
							$res_betr = $db->query("select handle_name, role from user_entry where
												   room_no = '$room_no' and user_no > '0' and role LIKE 'betr%' and live = 'live'");

							if ($lf_betr_arr = $db->fetch_array($res_betr))
								KillPlayer($lf_betr_arr['handle_name'], 'BETR_DEAD_night');
						}							

					}
				}

			}
			$db->free_result($res_owlman_vote);

			//說謊狂處理
			$res_mytho_vote = $db->query("select user_entry.uname,user_entry.handle_name,user_entry.live,user_entry.role,vote.target_uname
									   from vote,user_entry
									   where vote.room_no = '$room_no'
									   and user_entry.room_no = '$room_no' and user_entry.user_no > 0
									   and vote.date = $date
									   and vote.situation = 'MYTHO_DO'
									   and vote.uname = user_entry.uname");

			while ($mytho_arr = $db->fetch_array($res_mytho_vote)) {
				$mytho_uname = $mytho_arr['uname'];
				$mytho_role = $mytho_arr['role'];
				$mytho_handle_name = $mytho_arr['handle_name'];
				$mytho_live = $mytho_arr['live'];
				$mytho_target_uname = $mytho_arr['target_uname'];
				//死亡模仿無效
				if ($mytho_live == 'dead')
					continue;

				$res_mytho_voted = $db->query("select role, role_desc from user_entry where room_no = '$room_no' and user_no > '0'
											   and uname = '$mytho_target_uname'");
				$res_mytho_arr = $db->fetch_assoc($res_mytho_voted);
				$mytho_target_role = $res_mytho_arr['role'];

				if (strstr((string) $mytho_target_role, 'mage'))
					$mytho_changed_role = 'mage';
				else if(strstr((string) $mytho_target_role, 'wolf'))
					$mytho_changed_role = 'wolf';
				else
					$mytho_changed_role = 'human';

				$mytho_role = str_replace('mytho', $mytho_changed_role, (string) $mytho_role);
				// 更新職業
				$db->query("update user_entry set role = '$mytho_role', role_desc = 'mytho_tr' where room_no = '$room_no' and uname = '$mytho_uname'");


				// 更新占卜結果
				$mage_target_role = $mytho_role;
				$mage_target_handle_name = $mytho_handle_name;
				$res_mag_res = $db->query("select message from system_message where room_no = '$room_no' and date = $date
										  and type = 'MAGE_RESULT' and message like '%$mytho_name%'");
				while($mag_res_arr = $db->fetch_array($res_mag_res)) 
				{
					$message_arr = explode("\t", (string) $mag_res_arr['message']);
					$mage_handle_name = $message_arr[0];

					// 有被占到時
					if($mage_target_handle_name  === $mytho_handle_name) {
						if (strstr($mage_target_role,"wolf")) { //占い先が狼の場合
							$iswfbig = randme(101,200);
							if (strstr($mage_target_role,"wfbig") && $iswfbig >= 130)
								$system_message = $mage_handle_name . "\t" . $mage_target_handle_name . "\t" . "human";
							else
								$system_message = $mage_handle_name . "\t" . $mage_target_handle_name . "\t" . "wolf";
						}
						else { //占い先が狼以外の場合
							$system_message = $mage_handle_name . "\t" . $mage_target_handle_name . "\t" . "human";
						}

						$db->query("update system_message set message = '$system_message' 
								    where room_no = '$room_no' and date = $date and type = 'MAGE_RESULT' and message like '$mage_handle_name\t$mage_target_handle_name%'");
					}

				}
			}
			$db->free_result($res_mytho_vote);


			// SPY
			$res_spy_vote = $db->query("select user_entry.uname,user_entry.handle_name,user_entry.live,vote.target_uname
										 from vote,user_entry
										 where vote.room_no = '$room_no'
										 and user_entry.room_no = '$room_no' and user_entry.user_no > 0
										 and vote.date = $date
										 and vote.situation = 'SPY_DO'
										 and vote.uname = user_entry.uname");

			// SPY處理
			while ($spy_arr = $db->fetch_array($res_spy_vote)) 
			{
				$spy_uname = $spy_arr['uname'];
				$spy_handle_name = $spy_arr['handle_name'];
				$spy_live = $spy_arr['live'];

				// SPY死亡，任務失敗
				if ($spy_live == 'dead')
					continue;

				LeavePlayer($spy_handle_name);				

			}
			$db->free_result($res_spy_vote);


			//次の日にする
			$next_date = $date +1;
		//	$db->query("update room set date = $next_date , day_night = 'day' where room_no = '$room_no'");

			$db->query("delete from vote where room_no = '$room_no'"); //今までの投票を全部削除


			$time = time();  //現在時刻、GMTとの時差を足す
			$db->query("update room set date = $next_date , day_night = 'day',last_updated = '$time' where room_no = '$room_no'"); //最終書き込みを更新

			//次の日の処刑投票のカウントを1に初期化(再投票で増える)
			$db->query("insert into system_message (room_no,message,type,date)
													values ($room_no , '1' , 'VOTE_TIMES' ,$next_date)");

			//夜が明けた通知
			//$next_day_message = "< < 朝日が昇り $next_date 日目の朝がやってきました > >";
			//$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			//							values($room_no,$next_date,'day system','system',$time,'$next_day_message','0')");
			$next_day_message = "MORNING\t" . $next_date;
			$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
										values($room_no,$next_date,'day system','system',$time,'$next_day_message','0')");


			//勝敗のチェック
			CheckVictory();
		}
		else //全員投票されてない時
			return;
	}

//	$db->query("commit"); //一応コミット
}

//----------------------------------------------------------
//投票で処刑する
function VoteKill(string $max_voted_handle_name,$max_voted_role,$live_handle_name_list): void
{
	global $room_no,$date,$time_zone,$db,$isold,$wfwtr_message,$wfasm_message;


	//処刑
	/*
	$db->query("update user_entry set live = 'dead' where room_no = '$room_no' and handle_name = '$max_voted_handle_name'
																												and user_no > '0'");
	*/
	if ($db->begin_transaction()) {
		KillPlayer($max_voted_handle_name,'VOTE_KILLED');

		//埋毒或貓又被吊處理
		if( strstr((string) $max_voted_role,"poison") || strstr((string) $max_voted_role,"cat") )
		{
			//他の人からランダムに一人選ぶ
			$diff_arr = ["$max_voted_handle_name"];
			$poison_dead_arr = array_diff($live_handle_name_list,$diff_arr);
			$rand_key = array_randme($poison_dead_arr, 1);
			$poison_dead_handle_name = $poison_dead_arr[$rand_key];

			$poison_last_role = KillPlayer($poison_dead_handle_name, 'POISON_DEAD_day');

			//狐死掉
			if (strstr((string) $poison_last_role,"fox")) {
				//計算妖狐數量
				$res_fox_count = $db->query("select count(*) from user_entry where
												room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
				$fox_count = $db->result($res_fox_count,0);
				if ($fox_count == 0) {
					$res_betr = $db->query("select handle_name,role from user_entry where
											 room_no = '$room_no' and user_no > '0' and role LIKE 'betr%' and live = 'live'");
					if ($mage_betr_arr = $db->fetch_array($res_betr)) {
						KillPlayer($mage_betr_arr['handle_name'], 'BETR_DEAD_day');

						// 吊毒噴狐噴戀背死戀(毒噴狐 (背德自殺=>戀人自殺) )
						if(strstr((string) $mage_betr_arr['role'], "lovers")) {
							$res_lovers = $db->query("select handle_name from user_entry where
													 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
							if ($another_lover = $db->fetch_array($res_lovers))
								KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_day');
						}
					}
				}
			}
			//戀人死掉
			if (strstr((string) $poison_last_role,"lovers")) {
				$res_lovers = $db->query("select handle_name from user_entry where
										 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
				if ($another_lover = $db->fetch_array($res_lovers)) 
					KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_day');
			}

		}

		//狐被吊 (背德自殺)
		if (strstr((string) $max_voted_role,"fox")) {
			//計算妖狐數量
			$res_fox_count = $db->query("select count(*) from user_entry where
											room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
			$fox_count = $db->result($res_fox_count,0);
			if ($fox_count == 0) {
				//背德自殺
				$res_betr = $db->query("select handle_name, role from user_entry where
										 room_no = '$room_no' and user_no > '0' and role LIKE 'betr%'and live = 'live'");
				if ($mage_betr_arr = $db->fetch_array($res_betr))
					KillPlayer($mage_betr_arr['handle_name'], 'BETR_DEAD_day');

				// 吊狐噴戀背死戀
				if(strstr((string) $mage_betr_arr['role'], "lovers")) {
					$res_lovers = $db->query("select handle_name from user_entry where
											 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
					if ($another_lover = $db->fetch_array($res_lovers)) 
						KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_day');
				}
			}
		}

		//戀人自殺(戀人被吊)
		if (strstr((string) $max_voted_role,"lovers")) {
			$res_lovers = $db->query("select handle_name from user_entry where
									 room_no = '$room_no' and user_no > '0' and lovers = '1' and live = 'live'");
			if ($another_lover = $db->fetch_array($res_lovers)) {
				$lover_role = KillPlayer($another_lover['handle_name'], 'LOVER_DEAD_day');

				// 吊戀噴戀狐死背
				if(strstr((string) $lover_role, 'fox')) {
					//計算妖狐數量
					$res_fox_count = $db->query("select count(*) from user_entry where
													room_no = '$room_no' and user_no > '0' and role LIKE 'fox%' and live = 'live'");
					$fox_count = $db->result($res_fox_count,0);
					if ($fox_count == 0) {
						//背德自殺
						$res_betr = $db->query("select handle_name, role from user_entry where
												 room_no = '$room_no' and user_no > '0' and role LIKE 'betr%' and live = 'live'");
						if ($mage_betr_arr = $db->fetch_array($res_betr))
							KillPlayer($mage_betr_arr['handle_name'], 'BETR_DEAD_day');
					}
				}
			}
		}

		//靈能者の結果(系統メッセージ)
		if (strstr((string) $max_voted_role,"wfbig")) {
			$necro_max_voted_role = 'wfbig';
		} elseif(strstr((string) $max_voted_role,"fosi")) {
			$necro_max_voted_role = 'fosi';
		} elseif(strstr((string) $max_voted_role,"wolf")) {
			$necro_max_voted_role = 'wolf';
		} else {
			$necro_max_voted_role = 'human';
		}

	//echo $necro_max_voted_role;

		$necromancer_result_message = $max_voted_handle_name . "\t" .$necro_max_voted_role;
		$db->query("insert into system_message (room_no,message,type,date)
							values ($room_no,'$necromancer_result_message','NECROMANCER_RESULT',$date)");

	//echo $necromancer_result_message;


		/*
		//処刑されたメッセージ
		$db->query("insert into system_message (room_no,message,type,date)
							values ($room_no,'$max_voted_handle_name','VOTE_KILLED',$date)");

		//処刑された人の遺言を取得
		$res_votekilled_last_words = $db->query("select last_words from user_entry where room_no = '$room_no'
																	and handle_name = '$max_voted_handle_name' and user_no > '0'");

		$votekilled_last_words = $db->result($res_votekilled_last_words,0,0);
		//処刑された人の遺言を残す
		if($votekilled_last_words != '')
		{
			$last_words_str = $max_voted_handle_name . "\t" .$votekilled_last_words;
			$db->query("insert into system_message (room_no,message,type,date)
								values ($room_no,'".addslashes($last_words_str)."','LAST_WORDS',$date)");
		}
		*/

		$time = time();  //現在時刻、GMTとの時差を足す
		$db->query("update room set last_updated = '$time',day_night = 'night' where room_no = '$room_no'"); //最終書き込みを更新

	//	$db->query("update room set day_night = 'night' where room_no = '$room_no'"); //夜にする
		$db->query("delete from vote where room_no = '$room_no'"); //今までの投票を全部削除

		//夜がきた通知
		$time++;
		//$into_night_message = "< < 日が落ち、暗く静かな夜がやってきました > >";
		//$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
		//							values($room_no,$date,'night system','system',$time,'$into_night_message','0')");

		$night_message = "NIGHT";
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
									values($room_no,$date,'night system','system',$time,'$night_message','0')");

		$time++;
		if(strstr((string) $max_voted_role, 'wfwtr')) {
			$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
									values($room_no,$date,'night system','system',$time,'$wfwtr_message','0')");


			$db->query("insert into system_message (room_no,message,type,date)
							values ($room_no,'','WOLF_CHILL',$date)");

		}
		if(strstr((string) $max_voted_role, 'wfasm')) {
			$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
									values($room_no,$date,'night system','system',$time,'$wfasm_message','0')");	

			$db->query("insert into system_message (room_no,message,type,date)
							values ($room_no,'','WOLF_FLAME',$date)");

		}

	//	$db->query("commit"); //一応コミット
		$db->commit();
	}

}


//----------------------------------------------------------
//開始前の投票ページ出力
function BeforeGameVotePageOutput(): void
{
	global $room_no,$uname,$user_icon_dir,$php_argv,$page_style,$db,$isold,$trip_icon_dir,$dummy_boy_imgid,$game_option,$day_night;



	//自分以外の用戶の情報を取得
	$result = $db->query("select u.uid,u.handle_name as handle_name,u.user_no
						 ,u.uname as uname,i.icon_filename as icon_filename,i.color as color,u.icon_no as iconno,u.trip
						 ,i.icon_width as icon_width,i.icon_height as icon_height,tr.icon as ticon,tr.size as tsize,tr.id as tid
						 from user_entry u
						 left join user_icon i on i.icon_no = u.icon_no
						 left join user_trip tr on tr.trip = u.trip
						 where u.room_no = '$room_no' and u.user_no > 0 order by u.user_no ASC");
														// and user_entry.uname <> '$uname'


//	$result_count = $db->fetch_row($result);

	echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
	echo "<a name=\"#game_top\">";
	echo "<div align=center>";
	echo "<form onsubmit=\"return diamCheckNum(target_handle_name);\" action=\"game_vote.php?$php_argv#game_top\" method=POST>";
	echo "<input type=hidden name=command value=vote>";
	echo "<input type=\"hidden\" name=\"target_handle_name\" value=\"\" />";
	echo "<input type=hidden name=situation id=situation value=\"KICK_DO\">";

	echo "<table id=\"VBOX\" border=0 cellpadding=0 cellspacing=2><tr>";

	$i = 0;
	while($result_arr = $db->fetch_array($result))
	{
	//	$result_arr = $db->fetch_array($result);

		$this_uid = $result_arr['uid'];
		$this_handle_name = $result_arr['handle_name'];
		$this_uname = $result_arr['uname'];
		$this_icon_filename = $result_arr['icon_filename'];
		$this_color = $result_arr['color'];
		$this_icon_width = $result_arr['icon_width'];
		$this_icon_height = $result_arr['icon_height'];

		if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
			if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                $user_no2 =str_pad((string) $result_arr['user_no'],2,"0",STR_PAD_LEFT);
                $this_handle_name = "玩家".$user_no2."號";
            }
		}

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
		} else {
			$user_icon_dir2 = $user_icon_dir;
		}

		//5個ごとに改行
		if( ($i % 5) == 0 )
		{
			echo "</tr><tr>\r\n";
		}
		$i++;


		$icon_location = $user_icon_dir2 . "/" . $this_icon_filename;

		if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
			if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                $iconii =str_pad((string) $result_arr['user_no'],2,"0",STR_PAD_LEFT);
                $icon_location = "guest_icon/".$iconii.".webp";
                $icon_width_str = '';
                $icon_height_str = '';
                $this_color = '#FFFFFF';
            }
		}

		//HTML出力
		$diamChgBgColorHTML = "style=\"word-wrap: break-word;word-break: break-all;\"";
		if ($this_uname != 'dummy_boy' && $this_uname != $uname) {
			$diamChgBgColorHTML = "style=\"word-wrap: break-word;word-break: break-all;\" onclick=\"diamChgBgColor(this);\"";
		}
		echo "<td id=\"$this_uid\" name=\"$this_uid\" $diamChgBgColorHTML class=table_votelist1 valign=top>";
		echo "<img src=$icon_location width=$this_icon_width height=$this_icon_height border=2 style=\"border-color:$this_color;\">";
		echo "</td>\r\n";

		echo "<td id=\"$this_uid\" name=\"$this_uid\" $diamChgBgColorHTML class=table_votelist2 width=150px>$this_handle_name<br /><font color=$this_color>◆</font>";

		if($this_uname != 'dummy_boy' && $this_uname != $uname)
			echo "(投票)";
			//echo "<input type=radio name=target_handle_name value=\"$this_handle_name\">";

		echo "</td>\r\n";
	}
	$db->free_result($result);

	echo "</tr><tr>";
	echo "<td colspan=10>";

	echo "<table border=0><tr><td valign=top width=600 style=\"font-size:8pt;\">*若要Kick一個玩家，需要5人以上投票踢該員</td>";
	echo "<td width=1000 align=right>";

	echo "<table border=0>";
	echo "<td align=right valign=middle><a href=\"game_up.php?$php_argv#game_top\" style=\"font-size:14pt;\">←上一頁&amp;重新整理</a></td>";
	echo "<td arign=right><input type=submit value=\"投Kick(踢除)該員一票\"></td></form>";
	echo "</tr>";
	/*
	echo "<tr>";
	echo "<form action=\"game_vote.php?$php_argv#game_top\" method=POST>";
	echo "<input type=hidden name=command value=vote>";
	echo "<input type=hidden name=situation id=situation value=\"GAMESTART\">";
	echo "<td colspan=3 align=right><input type=submit value=\"投'開始遊戲'一票\"></td></form>";
	echo "</tr>";
	*/
	echo "</table>";

	echo "</td></tr></table>";

	echo "</td></tr></table></div>";

	echo "</body></html>";

}

//----------------------------------------------------------
//お白の投票ページを出力する
function DayVotePageOutput(): void
{

	global $room_no,$uname,$day_night,$date,$user_icon_dir,$php_argv,$page_style,$dead_user_icon_image,$db,$isold,$max_user,$trip_icon_dir,$dummy_boy_imgid,$game_option;

	//投票する状況があっているかチェック
	$res_last_load_day_night = $db->query("select last_load_day_night from user_entry where room_no = '$room_no'
																						and uname = '$uname' and user_no > '0'");
	$last_load_day_night = $db->result($res_last_load_day_night,0,0);
	if($last_load_day_night != $day_night)
	{
		VoteRedirect();
		/*
		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
		echo "<a name=\"#game_top\">";
		echo "<div align=center>請返回並重新整理<br />";
		echo "<a href=\"game_up.php?$php_argv#game_top\">";
		echo "←上一頁&amp;重新整理</a></div></body></html>";
		return;
		*/
	}

	//今何回目の投票なのか取得(再投票ならvote_timesは増える)
	$res_vote_times = $db->query("select message from system_message where room_no = '$room_no'
										and type = 'VOTE_TIMES' and date = $date");
	$vote_times = (int)$db->result($res_vote_times,0,0);


	//投票過了かどうか
	$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and uname = '$uname' and date = $date
																	and vote_times = '$vote_times' and situation = 'VOTE_KILL'");

	if( $db->result($res_already_vote,0) )
	{
		VoteRedirect();
		/*
		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
		echo "<a name=\"#game_top\">";
		echo "<div align=center>處刑：投票過了<br />";
		echo "<a href=\"game_up.php?$php_argv#game_top\">";
		echo "←上一頁&amp;重新整理</a></div></body></html>";
		return;
		*/
	}



	//用戶一覧とアイコンのデータ取得
	$res_user = $db->query("select u.user_no as user_no,u.handle_name as handle_name,tr.icon as ticon,tr.size as tsize,tr.id as tid
							,u.uname as uname,u.live as live,i.icon_filename as icon_filename,i.color as color,u.icon_no as iconno
							,i.icon_width as icon_width,i.icon_height as icon_height,u.trip
							from user_entry u
							left join user_icon i on i.icon_no = u.icon_no
							left join user_trip tr on tr.trip = u.trip
							where u.room_no = '$room_no' and u.user_no <= $max_user and u.user_no > 0 order by u.user_no ASC");
														// and user_entry.uname <> '$uname'


	$db->num_rows($res_user); //用戶数


	echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
	echo "<a name=\"#game_top\">";
	echo "<div align=center><form onsubmit=\"return diamCheckNum(target_no);\" action=\"game_vote.php?$php_argv#game_top\" method=POST><input type=hidden name=command value=vote>";
	echo "<input type=hidden name=situation id=situation value=\"VOTE_KILL\">";
	echo "<input type=hidden name=vote_times value=$vote_times >";
	echo "<input type=\"hidden\" name=\"target_no\" value=\"\" />";

	echo "<table id=\"VBOX\" border=0 cellpadding=0 cellspacing=2><tr>";

	$i = 0;
	while($user_arr = $db->fetch_array($res_user))
	{
	//	$user_arr = $db->fetch_array($res_user);

		$this_user_no = $user_arr['user_no'];
		$this_handle_name = $user_arr['handle_name'];
		$this_uname = $user_arr['uname'];
		$this_live = $user_arr['live'];

		$this_icon_filename = $user_arr['icon_filename'];
		$this_color = $user_arr['color'];
		$this_icon_width = $user_arr['icon_width'];
		$this_icon_height = $user_arr['icon_height'];

		if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
			if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                $this_user_no2 =str_pad((string) $this_user_no,2,"0",STR_PAD_LEFT);
                $this_handle_name = "玩家".$this_user_no2."號";
            }
		}

		if ($this_uname != 'dummy_boy' && $user_arr['ticon'] && $user_arr['tsize'] && $user_arr['iconno'] == $dummy_boy_imgid) {
			$user_arr['tsize']  = explode(":",(string) $user_arr['tsize']);
			if ($user_arr['tsize'][2] == '') {
				$user_arr['tsize'][2] = "webp";
			}
			$this_icon_filename = "icon_" . $user_arr['tid'].".".$user_arr['tsize'][2];
			$this_icon_width = $user_arr['tsize'][0];
			$this_icon_height = $user_arr['tsize'][1];
			$this_color = $user_arr['ticon'];
			$user_icon_dir2 = $trip_icon_dir;
		} else {
			$user_icon_dir2 = $user_icon_dir;
		}

		//5個ごとに改行
		if( ($i % 5) == 0 )
		{
			echo "</tr><tr>\r\n";
		}
		$i++;


		if( $this_live == 'live') //生きていれば用戶アイコン
		{
			$icon_location = $user_icon_dir2 . "/" . $this_icon_filename;
			$icon_width_str = "width=" . $this_icon_width;
			$icon_height_str = "height=" . $this_icon_height;
			//$live_str = "(生存中)";
		}
		else //死んでれば死亡アイコン
		{
			$icon_location = $dead_user_icon_image;
			$icon_width_str = '';
			$icon_height_str = '';
			//$live_str = "(死亡)";
		}

		//$icon_location = $user_icon_dir . "/" . $this_icon_filename;

		if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame' && $this_live == 'live') {
			if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                $iconii =str_pad((string) $this_user_no,2,"0",STR_PAD_LEFT);
                $icon_location = "guest_icon/".$iconii.".webp";
                $icon_width_str = '';
                $icon_height_str = '';
                $this_color = '#FFFFFF';
            }
		}

		//HTML出力
		$diamChgBgColorHTML = "style=\"word-wrap: break-word;word-break: break-all;\"";
		if ($this_live == 'live' && $this_uname != $uname) {
			$diamChgBgColorHTML = "style=\"word-wrap: break-word;word-break: break-all;\" onclick=\"diamChgBgColor(this);\"";
		}
		echo "<td id=\"$this_user_no\" name=\"$this_user_no\" $diamChgBgColorHTML class=table_votelist1 valign=top>";
		echo "<img src=$icon_location $icon_width_str $icon_height_str border=2 style=\"border-color:$this_color;\">";
		echo "</td>\r\n";

		echo "<td id=\"$this_user_no\" name=\"$this_user_no\" $diamChgBgColorHTML class=table_votelist2 width=150px>$this_handle_name<br /><font color=$this_color>◆</font>";

		if($this_live == 'live' && $this_uname != $uname)
			echo "(投票)\r\n";
			//echo "<input type=radio name=target_no value=\"$this_user_no\">\r\n";

		echo "</td> \r\n";
	}
	$db->free_result($res_user);


	echo "</tr><tr>";
	echo "<td colspan=10>";

	echo "<table border=0><tr><td valign=top width=600 style=\"font-size:8pt;\">*你不能更改你的投票結果，請慎重</td>";
	echo "<td width=1000 align=right>";

	echo "<table border=0>";
	echo "<td align=right valign=middle><a href=\"game_up.php?$php_argv#game_top\" style=\"font-size:14pt;\">←上一頁&amp;重新整理</a></td>";
	echo "<td arign=right><input type=submit value=\"投將該員'處刑'一票\"></td></form>";
	echo "</tr></table>";

	echo "</td></tr></table>";

	echo "</td></tr></table></div>";

	echo "</body></html>";
}

//----------------------------------------------------------
//夜の投票ページを出力する
function NightVotePageOutput(): void
{

	global $room_no,$uname,$role,$day_night,$date,$user_icon_dir,$php_argv,$page_style,$dead_user_icon_image,$wolf_user_icon_image,$db,$isold,$max_user,$game_option,$trip_icon_dir,$dummy_boy_imgid;

	$role = empty($role) ? "" : $role;

	//投票する状況があっているかチェック
	$res_last_load_day_night = $db->query("select last_load_day_night from user_entry where room_no = '$room_no'
																				and uname = '$uname' and user_no > '0'");
	$last_load_day_night = $db->result($res_last_load_day_night,0,0);
	if($last_load_day_night != $day_night)
	{
		VoteRedirect();
	}

	$res_wolf_partner_live = $db->query("select count(*) from user_entry
										 where room_no = '$room_no' and user_no > '0' and role like 'wolf%' and uname <> '$uname' and live = 'live'");

	$count_wolf_partner_live = $db->result($res_wolf_partner_live);

	if ((!str_contains((string) $role,'wolf')) && (!str_contains((string) $role,'mage')) && (!str_contains((string) $role,'guard')) 
		&& (!str_contains((string) $role,'fosi')) && (!str_contains((string) $role,'cat')) && !strstr((string) $role, 'owlman') && !strstr((string) $role, 'mytho') && !strstr((string) $role, 'pengu')
		&& !strstr((string) $role, 'spy'))
	{
		VoteRedirect();
	}
	elseif( strstr((string) $role,'wfwnd') ) //投票過了かどうか
	{
		if($count_wolf_partner_live > 0) {
			$res_already_vote = $db->query("select uname from vote where room_no = '$room_no' and date = $date 
																			and situation = 'HUG_DO' group by situation");
			$already_vote = $db->num_rows($res_already_vote);
		} else {
			$res_already_vote = $db->query("select uname from vote where room_no = '$room_no' and date = $date 
																			and situation = 'WOLF_EAT' group by situation");
			$already_vote = $db->num_rows($res_already_vote);
		}
	}
	elseif( strstr((string) $role,'wolf') ) //投票過了かどうか
	{
		$res_already_vote = $db->query("select uname from vote where room_no = '$room_no' and date = $date 
																			and situation = 'WOLF_EAT' group by situation");
		$already_vote = $db->num_rows($res_already_vote);
	}
	elseif( strstr((string) $role,'mage') ) //投票過了かどうか
	{
		$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
																and uname = '$uname' and situation = 'MAGE_DO'");
		$already_vote = $db->result($res_already_vote,0);
	}
	elseif(strstr((string) $role,'fosi')) //投票過了かどうか
	{
		$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
																and uname = '$uname' and situation = 'FOSI_DO'");
		$already_vote = $db->result($res_already_vote,0);
	}
	elseif(strstr((string) $role,'cat')) //投票過了かどうか
	{
		if($date == 1)
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>夜：無法在第一天進行復活<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}
		$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
																and uname = '$uname' and situation = 'CAT_DO'");
		$already_vote = $db->result($res_already_vote,0);
	}
	elseif( strstr((string) $role,'guard') )
	{
		if($date == 1)
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>夜：無法在第一天進行護衛<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}
		$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
																and uname = '$uname' and situation = 'GUARD_DO'");
		$already_vote = $db->result($res_already_vote,0);
	}
	elseif(strstr((string) $role, 'mytho'))
	{
		if($date != 2)
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>夜：無法在第二夜以外進行模仿<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}
		$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
									   and uname = '$uname' and situation = 'MYTHO_DO'");
		$already_vote = $db->result($res_already_vote,0);

	}
	elseif(strstr((string) $role, 'owlman'))
	{
		if($date == 1)
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>夜：無法在第一天進行詛咒<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}
		$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
									   and uname = '$uname' and situation = 'OWLMAN_DO'");
		$already_vote = $db->result($res_already_vote,0);

	}
	elseif(strstr((string) $role, 'pengu'))
	{
		if($date == 1)
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>夜：無法在第一天進行搔癢<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}
		$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
									   and uname = '$uname' and situation = 'PENGU_DO'");
		$already_vote = $db->result($res_already_vote,0);

	}
	elseif( strstr((string) $role,'spy') )
	{
		if($date == 1)
		{
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
			echo "<div align=center>夜：無法在第一天完成任務<br />";
			echo "<a href=\"game_up.php?$php_argv#game_top\">";
			echo "←上一頁&amp;重新整理</a></div></body></html>";
			return;
		}
		$res_already_vote = $db->query("select count(uname) from vote where room_no = '$room_no' and date = $date 
																and uname = '$uname' and situation = 'SPY_DO'");
		$already_vote = $db->result($res_already_vote,0);
	}


	if( $already_vote != 0 )
	{
		VoteRedirect();
	}

	if( strstr((string) $role,'wolf') ) 
	{ 


		if ( strstr((string) $game_option,"dummy_boy") && $date == 1 ) {//替身君の時は替身君だけしか選べない
			//替身君の用戶情報
			$result = $db->query("select u.user_no as user_no
								,u.handle_name as handle_name
								,u.uname as uname
								,u.live as live
								,u.trip
								,i.icon_filename as icon_filename
								,i.color as color,u.icon_no as iconno
								,i.icon_width as icon_width
								,i.icon_height as icon_height,tr.icon as ticon,tr.size as tsize,tr.id as tid
								from user_entry u left join user_icon i on i.icon_no = u.icon_no
								left join user_trip tr on tr.trip = u.trip
								where u.room_no = '$room_no'
								and u.user_no <= $max_user
								and u.uname = 'dummy_boy'
								and u.live = 'live'");
		} else {
			//自分以外の用戶情報
			$result = $db->query("select u.user_no as user_no
								,u.handle_name as handle_name
								,u.uname as uname
								,u.live as live
								,u.trip
								,u.role as role
								,i.icon_filename as icon_filename
								,i.color as color,u.icon_no as iconno
								,i.icon_width as icon_width
								,i.icon_height as icon_height,tr.icon as ticon,tr.size as tsize,tr.id as tid
								from user_entry u left join user_icon i on i.icon_no = u.icon_no
								left join user_trip tr on tr.trip = u.trip
								where u.room_no = '$room_no'
								and u.user_no <= $max_user
								and u.user_no > 0
								order by u.user_no");
								// and user_entry.uname <> '$uname'
		}
	} else {
		//自分以外の用戶情報
		$result = $db->query("select u.user_no as user_no
							,u.handle_name as handle_name
							,u.uname as uname
							,u.live as live
							,u.trip
							,i.icon_filename as icon_filename
							,i.color as color,u.icon_no as iconno
							,i.icon_width as icon_width
							,i.icon_height as icon_height,tr.icon as ticon,tr.size as tsize,tr.id as tid
							from user_entry u left join user_icon i on i.icon_no = u.icon_no
							left join user_trip tr on tr.trip = u.trip
							where u.room_no = '$room_no'
							and u.user_no <= $max_user
							and u.user_no > 0
							order by u.user_no");
							//and user_entry.uname <> '$uname'
	}

//	$result_count = $db->fetch_row($result);

	if(!strstr((string) $role,'spy')) {

		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>投票</title></head> $page_style ";
			echo "<a name=\"#game_top\">";
		echo "<div align=center><form name=\"nvform\" onsubmit=\"return diamCheckNum(target_no);\" action=\"game_vote.php?$php_argv#game_top\" method=POST>";
		echo "<input type=hidden name=command value=vote>";
		echo "<input type=\"hidden\" name=\"target_no\" value=\"\" />";

		echo "<table id=\"VBOX\" border=0 cellpadding=0 cellspacing=2><tr>";

		$i = 0;
		while($result_arr = $db->fetch_array($result))
		{
		//	$result_arr = $db->fetch_array($result);

			$this_user_no = $result_arr['user_no'];
			$this_handle_name = $result_arr['handle_name'];
			$this_uname = $result_arr['uname'];
			$this_live = $result_arr['live'];
			$this_role = empty($result_arr['role']) ? "" : $result_arr['role'];

			$this_icon_filename = $result_arr['icon_filename'];
			$this_color = $result_arr['color'];
			$this_icon_width = $result_arr['icon_width'];
			$this_icon_height = $result_arr['icon_height'];

			if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame') {
				if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                    $user_no2 =str_pad((string) $result_arr['user_no'],2,"0",STR_PAD_LEFT);
                    $this_handle_name = "玩家".$user_no2."號";
                }
			}

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
			} else {
				$user_icon_dir2 = $user_icon_dir;
			}

			//5個ごとに改行
			if( ($i % 5) == 0 )
			{
				echo "</tr><tr>\r\n";
			}
			$i++;

			if( ($this_live == 'live') && strstr((string) $role,'wolf') && !strstr((string) $role, 'wfbsk') && strstr((string) $this_role,"wolf") ) //狼同士なら狼アイコン
			{
				$icon_location = $wolf_user_icon_image;
				$icon_width_str = '';
				$icon_height_str = '';
			}
			elseif( $this_live == 'live') //生きていれば用戶アイコン
			{
				$icon_location = $user_icon_dir2 . "/" . $this_icon_filename;
				$icon_width_str = "width=" . $this_icon_width;
				$icon_height_str = "height=" . $this_icon_height;
				//$live_str = "(生存中)";
			}
			else //死んでれば死亡アイコン
			{
				$icon_location = $dead_user_icon_image;
				$icon_width_str = '';
				$icon_height_str = '';
				//$live_str = "(死亡)";
			}
			//$icon_location = $user_icon_dir . "/" . $this_icon_filename;

			if (strstr((string) $game_option,'usr_guest') && $day_night != 'aftergame' && $day_night != 'beforegame' && $this_live == 'live') {
				if (!(strstr((string) $game_option,'gm:'.$result_arr['trip']) && strstr((string) $game_option,'as_gm') || $uname == 'dummy_boy')) {
                    $iconii =str_pad((string) $result_arr['user_no'],2,"0",STR_PAD_LEFT);
                    $icon_location = "guest_icon/".$iconii.".webp";
                    $icon_width_str = '';
                    $icon_height_str = '';
                    $this_color = '#FFFFFF';
                }
			}

			//HTML出力
			$diamChgBgColorHTML = "style=\"word-wrap: break-word;word-break: break-all;\"";
			if ($this_live == 'live' && !(strstr((string) $role,'wolf') && strstr((string) $this_role,"wolf")) && !strstr((string) $role,'cat') && $uname != $this_uname) {
				$diamChgBgColorHTML = "style=\"word-wrap: break-word;word-break: break-all;\" onclick=\"diamChgBgColor(this);\"";
			}
			// wfbsk
			if ($this_live == 'live' && strstr((string) $role,'wfbsk') && strstr((string) $this_role,"wolf") && $uname != $this_uname) {
				$diamChgBgColorHTML = "style=\"word-wrap: break-word;word-break: break-all;\" onclick=\"diamChgBgColor(this);\"";
			}
			if ($this_live == 'dead' && strstr((string) $role,'cat') && $uname != $this_uname) {
				$diamChgBgColorHTML = "style=\"word-wrap: break-word;word-break: break-all;\" onclick=\"diamChgBgColor(this);\"";
			}


				echo "<td id=\"$this_user_no\" name=\"$this_user_no\" $diamChgBgColorHTML class=table_votelist1 valign=top>";
				echo "<img src=$icon_location $icon_width_str $icon_height_str border=2 style=\"border-color:$this_color;\">";
				echo "</td>\r\n";

			if(strstr((string) $role,'wfbsk') && strstr((string) $this_role,"wolf")) {		
				echo "<td id=\"$this_user_no\" name=\"$this_user_no\" $diamChgBgColorHTML class=table_votelist2 width=150px><font color=red>$this_handle_name</font><br /><font color=$this_color>◆</font>";
			} else {
				echo "<td id=\"$this_user_no\" name=\"$this_user_no\" $diamChgBgColorHTML class=table_votelist2 width=150px>$this_handle_name<br /><font color=$this_color>◆</font>";
			}

			if ($this_live == 'live' && !(strstr((string) $role,'wolf') && strstr((string) $this_role,"wolf")) && !strstr((string) $role,'cat') && $uname != $this_uname) {
				echo "(投票)\r\n";
				//echo "<input type=radio name=target_no value=\"$this_user_no\"> \r\n";
			}
			// wfbsk
			if ($this_live == 'live' && strstr((string) $role,'wfbsk') && strstr((string) $this_role,"wolf") && $uname != $this_uname) {
				echo "(投票)\r\n";
				//echo "<input type=radio name=target_no value=\"$this_user_no\"> \r\n";
			}
			if (($this_live == 'dead' || $this_live == 'gone') && strstr((string) $role,'cat') && $uname != $this_uname) {
				echo "(投票)\r\n";
				//echo "<input type=radio name=target_no value=\"$this_user_no\"> \r\n";
			}

			echo "</td>\r\n";

		}
		$db->free_result($result);

		echo "</tr><tr>";
		echo "<td colspan=10>";

		echo "<table border=0><tr><td valign=top width=600 style=\"font-size:8pt;\">*投票對象不能變更。請慎選</td>";
		echo "<td width=1000 align=right>";

		echo "<table border=0>";
		echo "<td align=right valign=middle><a href=\"game_up.php?$php_argv#game_top\" style=\"font-size:14pt;\">←上一頁&amp;重新整理</a></td>";
	} else {
		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>投票</title></head> $page_style ";
		echo "<a name=\"#game_top\">";
		echo "<div align=center><form name=\"nvform\" onsubmit=\"return diamCheckNum(target_no);\" action=\"game_vote.php?$php_argv#game_top\" method=POST>";
		echo "<input type=hidden name=command value=vote>";
		echo "<input type=\"hidden\" name=\"target_no\" value=\"\" />";
		echo "<tr><td>";

	}



	if( strstr((string) $role,'wfwnd') )
	{
		if($count_wolf_partner_live > 0) {
			echo "<input type=hidden name=situation id=situation value=\"HUG_DO\">";
			echo "<td arign=right><input type=submit value=\"鑽進懷裡\"></td></form>";
		} else {
			echo "<input type=hidden name=situation id=situation value=\"WOLF_EAT\">";
			echo "<td arign=right><input type=submit value=\"咬下去\"></td></form>";
		}
	}
	elseif( strstr((string) $role,'wolf') )
	{
		echo "<input type=hidden name=situation id=situation value=\"WOLF_EAT\">";
		echo "<td arign=right><input type=submit value=\"咬下去\"></td></form>";
	}
	elseif( strstr((string) $role,'mage') )
	{
		echo "<input type=hidden name=situation id=situation value=\"MAGE_DO\">";
		echo "<td arign=right><input type=submit value=\"占卜對象\"></td></form>";
	}
	elseif( strstr((string) $role,'fosi') )
	{
		echo "<input type=hidden name=situation id=situation value=\"FOSI_DO\">";
		echo "<td arign=right><input type=submit value=\"占卜對象\"></td></form>";
	}
	elseif( strstr((string) $role,'cat') )
	{
		echo "<input type=hidden name=situation id=situation value=\"CAT_DO\">";
		echo "<td arign=right><input type=submit name=atype value=\"放棄行動\" onClick=\"return(confirm('真的要放棄行動嗎？'))\"><input name=atype type=submit value=\"復活對象\"></td></form>";
	}
	elseif( strstr((string) $role,'guard') )
	{
		echo "<input type=hidden name=situation id=situation value=\"GUARD_DO\">";
		echo "<td arign=right><input type=submit value=\"護衛對象\"></td></form>";
	}
	elseif( strstr((string) $role,'mytho') )
	{
		echo "<input type=hidden name=situation id=situation value=\"MYTHO_DO\">";
		echo "<td arign=right><input type=submit value=\"模仿對象\"></td></form>";
	}
	elseif( strstr((string) $role,'owlman') )
	{
		echo "<input type=hidden name=situation id=situation value=\"OWLMAN_DO\">";
		echo "<td arign=right><input type=submit name=atype value=\"放棄行動\" onClick=\"return(confirm('真的要放棄行動嗎？'))\"><input name=atype type=submit value=\"詛咒對象\"></td></form>";
	}
	elseif( strstr((string) $role,'pengu') )
	{
		echo "<input type=hidden name=situation id=situation value=\"PENGU_DO\">";
		echo "<td arign=right><input type=submit name=atype value=\"放棄行動\" onClick=\"return(confirm('真的要放棄行動嗎？'))\"><input name=atype type=submit value=\"搔癢對象\"></td></form>";
	}
	elseif( strstr((string) $role,'spy') )
	{
		echo "<input type=hidden name=situation id=situation value=\"SPY_DO\">";
		echo "<td arign=right><input type=submit name=atype value=\"完成任務\" onClick=\"return(confirm('真的要完成任務嗎？'))\"></td></form>";

		echo "<table border=0>";
		echo "<td align=right valign=middle><a href=\"game_up.php?$php_argv#game_top\" style=\"font-size:14pt;\">←上一頁&amp;重新整理</a></td>";
	}


	echo "</tr></table>";

	echo "</td></tr></table>";

	echo "</td></tr></table></div>";

	echo "</body></html>";


}


function GMVote(): void
{
	global $room_no,$date,$uname,$handle_name,$target_no,$vote_times,$situation,$vote_lockfile,$php_argv,$page_style,$time_zone,$db,$isold,$role,$day_night,$game_option;

	$actid = $_POST['actid'];

	if($actid == '' || !strstr((string) $role, "GM")) {
		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
		echo "<a name=\"#game_top\">";
		echo "<div align=center>GM:投票錯誤<br />";
		echo "<a href=\"game_up.php?$php_argv#game_top\">";
		echo "←上一頁&amp;重新整理</a></div></body></html>";
		return;
	}

	//投票相手の用戶情報取得
	$res_target = $db->query("select uname,handle_name,live from user_entry where room_no = '$room_no' and user_no = $target_no");
	$target_arr = $db->fetch_array($res_target);
	$target_uname = $target_arr['uname'];
	$target_handle_name = $target_arr['handle_name'];
	$db->free_result($res_target);

	//自分宛、相手が居ない場合は無効
	if( ($target_uname === $uname) || ($target_uname == '') )
	{
		echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>投票結果</title></head> $page_style ";
		echo "<a name=\"#game_top\">";
		echo "<div align=center>GM：選擇了一個無效的目標(自己,或是不在的人)<br />";
		echo "<a href=\"game_up.php?$php_argv#game_top\">";
		echo "←上一頁&amp;重新整理</a></div></body></html>";
		return;
	}

	if(strstr((string) $day_night,'day'))
		$nstr = 'day system';
	elseif(strstr((string) $day_night,'night'))
		$nstr = 'night system';

	$silent = $_POST["silent_mode"];

	$time = time();  //現在時刻、GMTとの時差を足す
	if($actid == "GM_KILL")
	{	
		if(!$silent)
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">$target_handle_name 突然暴斃死亡。</font>','0')");

		$db->query("update room set last_updated = '$time' where room_no = '$room_no'");			
		$time = time()+1;

		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">投票重新開始，請盡速重新投票</font>','0')");

		$db->query("delete from vote where room_no = '$room_no'"); //今までの投票を全部削除

		KillPlayer($target_handle_name, 'GM_KILL');
	}
	elseif($actid == "GM_RESU")
	{
		if(!$silent)
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">$target_handle_name 神奇地復活了。</font>','0')");

		ResuPlayer($target_handle_name, 'GM_RESU');
	}
	elseif($actid == "GM_CHROLE")
	{
		// SILENT?
		if(!$silent)
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">$target_handle_name 的職業改變了！</font>','0')");

		$new_role = $_POST['new_role'];
		if(!strstr((string) $_POST['new_role'], 'wfbig') && $_POST['new_subrole'] != '')
			$new_role .= ' '.$_POST['new_subrole'];

		//RefreshDellookStatus(true);
		ChangePlayerRole($target_handle_name, $new_role);

		if (strstr((string) $game_option,"dummy_autolw")) {
			UpdateDummyLastWords("dummy_autolw");
		} else if (strstr((string) $game_option,"dummy_isred")) {
			UpdateDummyLastWords("dummy_isred");
		}
	}
	elseif($actid == "GM_MARK")
	{
		// SILENT
		if(!$silent)
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">$target_handle_name 被上了標記</font>','0')");
		ChangePlayerMark($target_handle_name, true);
	}
	elseif($actid == "GM_DEMARK")
	{
		// SILENT
		if(!$silent)
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">$target_handle_name 被解除標記</font>','0')");
		ChangePlayerMark($target_handle_name, false);
	}
	elseif($actid == "GM_DECL")
	{
		$victory_role = $_POST['victory_role'];
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
			values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">宣告了陣營勝利！</font>','0')");

		$db->query("delete from vote where room_no = '$room_no'"); //今までの投票を全部削除
		$db->query("update room{$isold} set status = 'finished',day_night = 'aftergame',victory_role = '$victory_role' where room_no = '$room_no'");
		tosqlgover();

	}
	elseif($actid == "GM_CHANNEL")
	{
		$new_game_option = "";
		$arr_game_opt = explode(' ', (string) $game_option);
		foreach($arr_game_opt as $opt_entry) 
		{
			if(strstr($opt_entry, 'chdis:'))
				$opt_entry = 'chdis:'.$_POST["ch_wolf"].':'.$_POST["ch_common"].':'.$_POST["ch_lovers"].':'.$_POST["ch_fox"];
			$new_game_option .= ' '.$opt_entry;
		}


		if(!strstr($new_game_option, "chdis:"))
			$new_game_option .= ' '.'chdis:'.$_POST["ch_wolf"].':'.$_POST["ch_common"].':'.$_POST["ch_lovers"].':'.$_POST["ch_fox"];

		$chg_msg = "(人狼".($_POST["ch_wolf"]==''?'O':'X').", 共有者".($_POST["ch_common"]==''?'O':'X').", 戀人".($_POST["ch_lovers"]==''?'O':'X').", 妖狐".($_POST["ch_fox"]==''?'O':'X').")";
		//if($silent)
		$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
				   values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">頻道設定已經變更 $chg_msg </font>','0')");

		$db->query("update room set game_option = '$new_game_option' where room_no = '$room_no'");
	}
	elseif($actid == "GM_DELLOOK")
	{
		$new_dellook = $_POST['dellook_status'];

		// SILENT
		if(!$silent) {
			if($new_dellook == 1) {
				$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
					   values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">靈視已經開啟</font>','0')");
			} else {
				$db->query("insert into talk(room_no,date,location,uname,time,sentence,spend_time)
						values($room_no,$date,'$nstr','system',$time,'<font color=\"#d00000\">靈視已經關閉</font>','0')");
			}
		}
		$db->query("update room set dellook = '$new_dellook' where room_no = '$room_no'");		
	}
	else
	{
		// ERROR
	}

	$db->query("update room set last_updated = '$time' where room_no = '$room_no'"); //最終書き込みを更新
	VoteRedirect();

	if($actid != "GM_DECL")
		CheckVictory();
}


function GMVotePageOutput(): void 
{	
	global $room_no,$uname,$day_night,$date,$user_icon_dir,$php_argv,$page_style,$dead_user_icon_image,$db,$isold,$game_option,$dellook,$trip_icon_dir,$dummy_boy_imgid;

	$actid = $_GET['aid'];
	if($actid == '')
		VoteRedirect();
	/*
	//投票する状況があっているかチェック
	$res_last_load_day_night = $db->query("select last_load_day_night from user_entry where room_no = '$room_no'
																						and uname = '$uname' and user_no > '0'");
	$last_load_day_night = $db->result($res_last_load_day_night,0,0);
	if($last_load_day_night != $day_night)
		VoteRedirect();
	*/	

	echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type=\"text/javascript\" src=\"img/vote.js\"></script><title>GM行動</title></head> $page_style ";
	echo "<a name=\"#game_top\">";
	echo "<div align=center><form action=\"game_vote.php?$php_argv&actid=$actid#game_top\" method=POST>";
	echo "<input type=hidden name=actid value=\"$actid\">";
	echo "<input type=hidden name=situation id=situation value=\"$actid\">";
	echo "<input type=hidden name=command value=vote>";

	if($actid == 'GM_DECL') {
		echo '宣告陣營勝利： <select name="victory_role">
		<option value="human">村人
		<option value="wolf">人狼
		<option value="fox">妖狐
		<option value="lovers">戀人
		<option value="draw">和局
		<option value="custo">自行宣判
		<option value="night">嚼嚼兔
		</select>';

		echo "<table border=0>";
		echo "<td align=right valign=middle><a href=\"game_up.php?$php_argv#game_top\" style=\"font-size:14pt;\">←上一頁&amp;重新整理</a></td>";
		echo "<input type=hidden name=target_no value=1>";
		echo "<td arign=right><input type=submit value=\"宣告勝利\"></td></form>";
		echo "</tr></table>";
		echo "</div></body></html>";

		return;
	}
	elseif($actid == 'GM_CHANNEL') {
		$chdis = strstr((string) $game_option, "chdis:");

		echo "關閉頻道：\r\n";
		echo "<input type=\"checkbox\" name=\"ch_wolf\" value=\"ch_wolf\" ".(strstr($chdis, "ch_wolf")?"checked":"")."> 人狼\r\n";
		echo "<input type=\"checkbox\" name=\"ch_common\" value=\"ch_common\" ".(strstr($chdis, "ch_common")?"checked":"")."> 共有\r\n";
		echo "<input type=\"checkbox\" name=\"ch_lovers\" value=\"ch_lovers\" ".(strstr($chdis, "ch_lovers")?"checked":"")."> 戀人\r\n";
		echo "<input type=\"checkbox\" name=\"ch_fox\" value=\"ch_fox\" ".(strstr($chdis, "ch_fox")?"checked":"")."> 妖狐<br />\r\n";

		echo "<table border=0>";
		echo "<td align=right valign=middle><a href=\"game_up.php?$php_argv#game_top\" style=\"font-size:14pt;\">←上一頁&amp;重新整理</a></td>";
		echo "<input type=hidden name=target_no value=1>";
		echo "<td arign=right><input type=submit value=\"變更頻道設定\"></td></form>";
		echo "</tr></table>";
		echo "</div></body></html>";
		return;
	}
	elseif($actid == 'GM_DELLOOK') {
		echo '改變靈視狀態： (目前為'.($dellook==0?'關閉':'開啟').')<br /><select name="dellook_status">
		<option value="1">開啟
		<option value="0">關閉
		</select>';

		echo "<table border=0>";
		echo "<td align=right valign=middle><a href=\"game_up.php?$php_argv#game_top\" style=\"font-size:14pt;\">←上一頁&amp;重新整理</a></td>";
		echo "<input type=hidden name=target_no value=1>";
		echo "<td arign=right>";
		echo "<input type=\"checkbox\" name=\"silent_mode\" checked∂>不顯示訊息";
		echo "<input type=submit value=\"改變靈視狀態\"></td></form>";
		echo "</tr></table>";
		echo "</div></body></html>";

		return;
	}


	//用戶一覧とアイコンのデータ取得
	$res_user = $db->query("select u.user_no as user_no
									,u.handle_name as handle_name
									,u.uname as uname
									,u.live as live
									,u.marked as marked
									,i.icon_filename as icon_filename
									,i.color as color,u.icon_no as iconno
									,i.icon_width as icon_width
									,i.icon_height as icon_height,tr.icon as ticon,tr.size as tsize,tr.id as tid
										from user_entry u left join user_icon i on i.icon_no = u.icon_no
										left join user_trip tr on tr.trip = u.trip
											where u.room_no = '$room_no' and user_no > '0' and u.role <> 'GM' order by u.user_no");
														// and user_entry.uname <> '$uname'


	$db->num_rows($res_user); //用戶数


	echo "<input type=\"hidden\" name=\"pNums\" value=\"\" />";
	echo "<table id=\"VBOX\" border=0 cellpadding=0 cellspacing=2><tr>";

	$i = 0;
	while($user_arr = $db->fetch_array($res_user))
	{
	//	$user_arr = $db->fetch_array($res_user);

		$this_user_no = $user_arr['user_no'];
		$this_handle_name = $user_arr['handle_name'];
		$this_uname = $user_arr['uname'];
		$this_live = $user_arr['live'];

		$this_icon_filename = $user_arr['icon_filename'];
		$this_color = $user_arr['color'];
		$this_icon_width = $user_arr['icon_width'];
		$this_icon_height = $user_arr['icon_height'];

		if ($this_uname != 'dummy_boy' && $user_arr['ticon'] && $user_arr['tsize'] && $user_arr['iconno'] == $dummy_boy_imgid) {
			$user_arr['tsize']  = explode(":",(string) $user_arr['tsize']);
			if ($user_arr['tsize'][2] == '') {
				$user_arr['tsize'][2] = "webp";
			}
			$this_icon_filename = "icon_" . $user_arr['tid'].".".$user_arr['tsize'][2];
			$this_icon_width = $user_arr['tsize'][0];
			$this_icon_height = $user_arr['tsize'][1];
			$this_color = $user_arr['ticon'];
			$user_icon_dir2 = $trip_icon_dir;
		} else {
			$user_icon_dir2 = $user_icon_dir;
		}

		$this_marked = $user_arr['marked'];

		if($this_marked)
			$this_marked = " (*)";
		else
			$this_marked = "";


		//5個ごとに改行
		if( ($i % 5) == 0 )
		{
			echo "</tr><tr>\r\n";
		}
		$i++;


		if( $this_live == 'live') //生きていれば用戶アイコン
		{	
			$icon_location = $user_icon_dir2 . "/" . $this_icon_filename;
			$icon_width_str = "width=" . $this_icon_width;
			$icon_height_str = "height=" . $this_icon_height;
		}
		else //死んでれば死亡アイコン
		{
			$icon_location = $dead_user_icon_image;
			$icon_width_str = '';
			$icon_height_str = '';
		}

		//HTML出力
		echo "<td class=table_votelist1 valign=top>";
		echo "<img src=$icon_location $icon_width_str $icon_height_str border=2 style=\"border-color:$this_color;\">";
		echo "</td>\r\n";

		echo "<td class=table_votelist2 width=150px>$this_handle_name<br /><font color=$this_color>◆</font>";

		switch($actid) {
		case('GM_KILL'):
			$can_select = ($this_live == 'live');
			break;
		case('GM_RESU'):
			$can_select = ($this_live != 'live');
			break;
		case('GM_CHROLE'):
			$can_select = true;
			break;
		case('GM_MARK'):
			$can_select = !$this_marked;;
			break;
		case('GM_DEMARK'):
			$can_select = $this_marked;
			break;
		}

		if($can_select)
			echo "<input type=radio name=target_no value=\"$this_user_no\">\r\n";

		echo "</td> \r\n";
	}
	$db->free_result($res_user);


	echo "</tr><tr>";
	echo "<td colspan=10>";

	echo "<table border=0><tr><td valign=top width=600 style=\"font-size:8pt;\">&nbsp;</td>";
	echo "<td width=1000 align=right>";

	switch($actid) {
		case('GM_KILL'):
			$action_text = "殺很大，殺不用錢";
			$sil_default = false;
			break;
		case('GM_RESU'):
			$action_text = "小黑魂，人！";
			$sil_default = false;
			break;
		case('GM_CHROLE'):
			$action_text = "轉換職業";
			$sil_default = true;
			break;
		case('GM_MARK'):
			$action_text = "上標記";
			$sil_default = true;
			break;
		case('GM_DEMARK'):
			$action_text = "解除標記";
			$sil_default = true;
			break;
	}

	echo "<table border=0>";
	echo "<td align=right valign=middle><a href=\"game_up.php?$php_argv#game_top\" style=\"font-size:14pt;\">←上一頁&amp;重新整理</a></td>";

if($actid == 'GM_CHROLE') {
	echo "更改為：<select name=\"new_role\"><option value=\"human\">村民
<option value=\"wolf\">人狼
<option value=\"wolf wfbig\">大狼
<option value=\"mage\">占卜師
<option value=\"necromancer\">靈能者
<option value=\"mad\">狂人
<option value=\"spy\">間諜
<option value=\"guard\">獵人
<option value=\"common\">共有者
<option value=\"fox\">妖狐
<option value=\"fosi\">子狐
<option value=\"betr\">背德
<option value=\"poison\">埋毒者
<option value=\"cat\">貓又(請先關閉靈視)
<option value=\"mytho\">說謊狂(務必在二夜前指定)
<option value=\"owlman\">夜梟
<option value=\"pengu\">小企鵝
<option value=\"noble\">貴族
<option value=\"slave\">奴隸
</select>";


	echo "<select name=\"new_subrole\">
	<option value=\"\"> 無副職
	<option value=\"authority\">權力者
<option value=\"decide\">決定者
<option value=\"lovers\">戀人
</select>";
}
	echo "<input type=\"checkbox\" name=\"silent_mode\" ".($sil_default?"checked":"")." >不顯示訊息</input>";
	echo "<td arign=right><input type=submit value=\"$action_text\"></td></form>";
	echo "</tr></table>";

	echo "</td></tr></table>";

	echo "</td></tr></table></div>";

	echo "</body></html>";
}


function KillPlayer(string $handle_name, $reason) 
{
	global $db,$room_no,$date;
	// update player status
	$db->query("update user_entry set live = 'dead' where room_no = '$room_no' and user_no > '0' and handle_name = '$handle_name'");
	// add killing message
	$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'$handle_name','$reason',$date)");
	// get last words
	$res_lw = $db->query("select last_words, role from user_entry where room_no = '$room_no' and user_no > '0' and handle_name = '$handle_name'");
	$lw = $db->fetch_assoc($res_lw);
	if ($lw['last_words'] != '') {
		$lwstr = $handle_name."\t".$lw['last_words'];
		$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'".addslashes($lwstr)."','LAST_WORDS',$date)");
	}

	RefreshDellookStatus();	

	return $lw['role'];
}

function LeavePlayer(string $handle_name) 
{
	global $db,$room_no,$date;
	// update player status
	$db->query("update user_entry set live = 'gone' where room_no = '$room_no' and user_no > '0' and handle_name = '$handle_name'");
	// add killing message

	$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'$handle_name','SPY_ESCAPE',$date)");

	// get last words
	$res_lw = $db->query("select last_words, role from user_entry where room_no = '$room_no' and user_no > '0' and handle_name = '$handle_name'");
	$lw = $db->fetch_assoc($res_lw);
	if ($lw['last_words'] != '') {
		$lwstr = $handle_name."\t".$lw['last_words'];
		$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'".addslashes($lwstr)."','LAST_WORDS',$date)");
	}

	return $lw['role'];
}

function ResuPlayer($handle_name, $reason): void 
{
	global $db,$room_no,$date,$day_night;
	// update status
	$db->query("update user_entry set live = 'live' where room_no = '$room_no' and user_no > '0' and handle_name = '$handle_name'");
	// add resurrect message
	$db->query("insert into system_message (room_no,message,type,date) values ($room_no,'$handle_name','$reason',$date)");
}

function ChangePlayerRole($handle_name, $role): void 
{
	global $db, $room_no;
	if (strstr((string) $role, "lovers")) {
		$lovers = 1;
	} else {
		$lovers = 0;
	}
	if (strstr((string) $role, "noble")) {
		$noble = 1;
	} else {
		$noble = 0;
	}
	if (strstr((string) $role, "slave")) {
		$slave = 1;
	} else {
		$slave = 0;
	}
	$db->query("UPDATE user_entry SET role = '$role',lovers = '$lovers',noble = '$noble',slave = '$slave'
				WHERE room_no = '$room_no' AND handle_name = '$handle_name'");
	REstartDataROLE();
}

function ChangePlayerMark($handle_name, $set): void 
{
	global $db, $room_no;
	$db->query("UPDATE user_entry SET marked = ".($set?1:0)." where room_no = '$room_no' AND handle_name = '$handle_name'");
}

//更新職業資料
function REstartDataROLE(): void
{
	global $db, $room_no;
		$db->query("update user_entry set lovers = '1' where room_no = '$room_no' and role LIKE '%lovers%';");
		$db->query("update user_entry set noble = '1' where room_no = '$room_no' and role LIKE '%noble%';");
		$db->query("update user_entry set slave = '1' where room_no = '$room_no' and role LIKE '%slave%';");
}

function VoteRedirect(): void
{	
	global $php_argv,$echosocket;
	echo "<html><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><title>OK</title>$echosocket</head><body bgcolor=aliceblue onload=\"socketinit();\">Vote ok!<br />Please wait for one second.</body></html>";
}

function RefreshDellookStatus(): void {
	global $db, $room_no, $game_option, $option_role;

	if(strstr((string) $game_option, 'as_gm')) return;

	$res_cat_count = $db->query("SELECT count(*) from user_entry WHERE
								room_no = '$room_no' AND user_no > '0' AND role LIKE 'cat%' AND live = 'live'");

	$cat_count = $db->result($res_cat_count,0);

	if(strstr((string) $option_role, "cat")) {
		if($cat_count == 0)
			$db->query("UPDATE room SET dellook = '1' where room_no = '$room_no'");
	}
}

function UpdateDummyLastWords($selectis): void {
	global $db, $room_no;

	$last_word_str = "NULL";
	$dummy_res = $db->query("select uname,role,live from user_entry 
							where room_no = '$room_no' and user_no > '0' and uname = 'dummy_boy' limit 1;"); 
	$dummy_arr = $db->fetch_array($dummy_res);
	$dummy_role = $dummy_arr['role'];

	if($dummy_arr['live'] == 'live') {
		if ($selectis == "dummy_autolw") { //dummy_autolw
			require 'dummy.php';

			if (strstr((string) $dummy_role, 'mage')) {
				$last_word_str = $mlastws;
			} 
			else if(strstr((string) $dummy_role, 'necromancer')) {
				$last_word_str = $nlastws;
			} 
			else if(strstr((string) $dummy_role, 'slave')) {
				$last_word_str = "請幫我吊死我家的主人啊……（斷氣）";
			} 
			else if(strstr((string) $dummy_role, 'common')) {
				$last_word_str = "共有，同伴請替我呻吟五聲～";
			} 
			else if(strstr((string) $dummy_role, 'mytho')) {
				$last_word_str = "村人請加油，我現在還沒性轉換（疑）";
			} 				
			else if(strstr((string) $dummy_role, 'owlman')) {
				$last_word_str = "我是晚上出現的夜梟，不是賣毒品的毒梟，囧";
			} 		
			else if(strstr((string) $dummy_role, 'poison') || strstr((string) $dummy_role, 'cat')) {
				$last_word_str = "毒……不然就是活不到人的貓吧？：～";
			} 		
			else if(strstr((string) $dummy_role, 'mad') || strstr((string) $dummy_role, 'spy') || strstr((string) $dummy_role, 'betr') || strstr((string) $dummy_role, 'fosi')) {
				$ismagelw = randme(101,200) > 130;
				if($ismagelw) {
					$last_word_str = $mlastws;
				} else {
					$last_word_str = $nlastws;
				}
			}

			if($last_word_str != "NULL") {
				$db->query("UPDATE user_entry SET last_words = '<font color=\"red\"> $last_word_str </font>' where room_no = '$room_no' AND user_no > '0' AND uname = 'dummy_boy'");
			}
		} else if ($selectis == "dummy_isred") { //dummy_isred
			if (strstr((string) $dummy_role, 'mage') || strstr((string) $dummy_role, 'necromancer') || strstr((string) $dummy_role, 'slave') || strstr((string) $dummy_role, 'common') || strstr((string) $dummy_role, 'mytho')
			 || strstr((string) $dummy_role, 'owlman') || strstr((string) $dummy_role, 'poison') || strstr((string) $dummy_role, 'cat') || strstr((string) $dummy_role, 'mad') || strstr((string) $dummy_role, 'spy') || strstr((string) $dummy_role, 'betr') || strstr((string) $dummy_role, 'fosi')) {
				$db->query("UPDATE user_entry SET last_words = '<font color=\"red\"> .....(模糊的遺書無法得知職業) </font>' where room_no = '$room_no' AND user_no > '0' AND uname = 'dummy_boy'");
			}
		}
	}
}

//----------------------------------------------------------
?>
