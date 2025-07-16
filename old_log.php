<?php
require_once __DIR__ . '/game_functions.php';

session_start();

set_time_limit(0);
ob_end_clean();
ob_implicit_flush();
header("X-Accel-Buffering: no");

function ShowHtmlFail($err_string): void
{
	echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><title>Error</title></head><body bgcolor=aliceblue>
	<center><table border=0><tr><td><fieldset style='background:#fcbccc;border-color:#33336;width:400px;'>
	<legend><strong>Error</strong></legend>
	<label><ul>$err_string<li><a href='javascript:window.history.back()'>返回</a></ul></label></fieldset></td></tr></table></center></body></html>";
}

/*
if ($_GET['room_no']) {
	if (isset($_SESSION["youserint"]))
	{
		if (time() - $_SESSION["youserint"] < $youserint_sep)
		{
			ShowHtmlFail("Please wait ".$youserint_sep." sec");
			exit;
		} else { 
			$_SESSION["youserint"] = time();
		}
	} else {
		$_SESSION["youserint"] = time() - $youserint_sep - 1;
		header("Refresh: 1; url=https://".$domain_name."/old_log.php?".$_SERVER['QUERY_STRING']);
		exit;
	}
}
*/

$max_user = 0;
$game_option = $option_role = '';

$page = empty($page) ? "" : $page;
$log_mode = empty($log_mode) ? "" : $log_mode;
//MySQLに接続
if($db->connect_error())
{
	exit;
}

$filei = 0;
if (($reverse_log == "" || $reverse_log == "off") && ($heaven_talk == "" || $heaven_talk == "off") && ($heaven_only == "" || $heaven_only == "off")) {
	$filei = 0;
}
if ($reverse_log == "on") {
	$filei = 1;
}
if ($heaven_talk == "on") {
	$filei = 2;
}
if ($reverse_log == "on" && $heaven_talk == "on") {
	$filei = 3;
}
if ($heaven_only == "on") {
	$filei = 4;
}
if ($reverse_log == "on" && $heaven_only == "on") {
	$filei = 5;
}

if (!is_file("old_tmp/".$room_no."_".$filei.".tmp") && $room_no) {
    $result = $db->query("select count(*) from room where room_no > 1 AND status='playing' AND game_option NOT LIKE '%ischat%'");
    $room_count = $db->result($result,0);
    //取得募集中或是遊玩中的房間數
    if ($room_count > 0) {
  		//	ShowHtmlFail("有村莊正在進行遊戲，暫停存取紀錄。");
  		//	exit;
  		}
}



$outputhtml = "<html><head>";
//include_once("analyticstracking.php");
$outputhtml .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
$outputhtml .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"img/font.css\"><title>汝等是人是狼？[過去紀錄]</title> \r\n";
$outputhtml .= "<style type=\"text/css\">";
$outputhtml .= "<!--\r\n";

$background_color = $background_color_aftergame;
$text_color = $text_color_aftergame;
$a_color = 'blue';
$a_vcolor = 'blue';
$a_acolor = 'red';

if($log_mode != 'on')
{
	$outputhtml .= "body{background-color:white;background-image: url(\"img/old_log_bg.webp\");background-repeat: no-repeat;";
	$outputhtml .= "background-position: 100% 100%;background-attachment: fixed }";
	$outputhtml .= "table{filter:alpha(opacity=80,enabled=80)}";
}
else
	$outputhtml .= "body{background-color:$background_color;color:$text_color;}\r\n";

$outputhtml .= "A:link{ color: $a_color; } A:visited{ color: $a_vcolor; } A:active{ color: $a_acolor; } A:hover{ color: red; } \r\n";
$outputhtml .= ".day{  background-color:$background_color_day; color : $text_color_day;} \r\n";
$outputhtml .= ".night{background-color:$background_color_night; color: $text_color_night;} \r\n";
$outputhtml .= ".beforegame{background-color:$background_color_beforegame; color : $text_color_beforegame;} \r\n";
$outputhtml .= ".aftergame{ background-color:$background_color_aftergame; color : $text_color_aftergame;} \r\n";
$outputhtml .= ".heaven{ background-color:#cccccc; color : black;} \r\n";
$outputhtml .= ".column { MARGIN: 0px; BORDER-LEFT:#ffffff 1px solid; PADDING-LEFT:6px; BORDER-TOP:#ffffff 1px solid; PADDING-TOP:3px;
	BORDER-RIGHT:  #ffffff 0px solid; PADDING-RIGHT:  3px; BORDER-BOTTOM: #ffffff 0px solid; PADDING-BOTTOM: 3px; 
	COLOR: #ffffff; BACKGROUND-COLOR: #526CD6; } \r\n";
	
$outputhtml .= ".row { MARGIN: 0px; BORDER-LEFT:#ffffff 1px solid; PADDING-LEFT:6px; BORDER-TOP:#ffffff 1px solid; PADDING-TOP:3px; 
	BORDER-RIGHT:  #ffffff 0px solid; PADDING-RIGHT:  3px; BORDER-BOTTOM: #ffffff 0px solid; PADDING-BOTTOM: 3px; 
	COLOR: #333333; BACKGROUND-COLOR: #F2EACE; } \r\n-->";
$outputhtml .= "</style>\r\n";
$outputhtml .= "<style type=\"text/css\"><!-- a { text-decoration: none } --></style>";
$outputhtml .= "</head><body>\r\n";


switch($log_mode)
{
	case('on'):
		if($room_no != "") {
			if (!is_file("old_tmp/".$room_no."_".$filei.".tmp")) {
				$outputhtml .= OldLogOutput($room_no);

				$lastt = time() - 120;
				$result = $db->query("select count(*) from room where room_no = '".(int)$room_no."' AND last_updated > $lastt");
				$room_count = $db->result($result,0);
				if ($room_count == 0) {
					//file_put_contents("old_tmp/".$room_no."_".$filei.".tmp",$outputhtml);
				}
			} else {
				$result = $db->query("select last_updated from room where room_no = '".$room_no."'");
				$room_time = $db->result($result,0);
				$file_time = filemtime("old_tmp/".$room_no."_".$filei.".tmp");
				if ($room_time > $file_time) {
					$outputhtml .= OldLogOutput($room_no);
					//file_put_contents("old_tmp/".$room_no."_".$filei.".tmp",$outputhtml);
				} else {
					$outputhtml = file_get_contents("old_tmp/".$room_no."_".$filei.".tmp");
				}
			}
		}
		break;
	default:
		$outputhtml .= OldLogListOutput($page);
		break;
}


$bodyhtml = "</body></html> \r\n";

echo $outputhtml.$bodyhtml;

//MySQLとの接続を閉じる
$db->close();


//**************かんすー

//----------------------------------------------------------
//過去ログ一覧表示
function OldLogListOutput($page): string
{

	global $game_option, $option_role, $max_user,$playing_image,$waiting_image,$maxuser_image_array,$room_option_wish_role_image,$room_option_dummy_boy_image,
			$room_option_open_vote_image,$room_option_decide_image,$room_option_authority_image,$room_option_poison_image,
			$room_option_real_time_image,$room_option_betr_image,$room_option_rei_image,$room_option_conn_image,$db,$isold,
			$room_option_fosi_image,$room_option_foxs_image,$room_option_wfbig_image,$room_option_cat_image,$room_option_voteme_image,
			$room_option_trip_image,$room_option_will_image,$room_option_lovers_image,$room_option_gm_image, $game_dellook,$room_option_guest_image;
		global $room_option_mytho_image, $room_option_owlman_image, $room_option_noconn_image,$room_option_pengu_image,$room_option_noble_image;
		
	global $maxuser_image_array,$room_option_wish_role_image,$room_option_real_time_image,$room_option_dummy_boy_image,
			$room_option_open_vote_image,$room_option_decide_image,$room_option_authority_image,$room_option_poison_image,
			$logview_onepage_count,$victory_role_human_image,$victory_role_wolf_image,$victory_role_fox_image,$victory_role_lovers_image,
			$room_option_conn_image,$victory_role_draw_image,$room_option_betr_image,$room_option_rei_image,$db,$isold,
			$role_fosi_image,$role_fosi_partner_image,$room_option_fosi_image,$room_option_foxs_image,$time_zone,
			$room_option_wfbig_image,$room_option_cat_image,$room_option_lovers_image,$victory_role_custom_image,$roomidserint;
	
	
	
	//if($page == '')
	//	$page = 1;
	if (!$_GET['all']) {
		$issql = "AND victory_role != 'night' AND victory_role > ''";
	} else {
		$isurl = '?all=1';
	}
	if ($_GET['search']) {
		$issql = "AND room_name LIKE '%".$_GET['search']."%'";
		$isurl = '?search='.$_GET['search'];
	}
	
	$counts = $db->query("select count(*) from room where status = 'finished' $issql");
	$num = $db->result($counts,0);
	
	
//	$finished_room_count = $db->fetch_row($res_oldlog_list); //終了した村の数
	
	//全部表示の場合、一頁數で全部表示する。それ以外は設定した数ごと表示
	/*
	if($page === 'all')
		$_onepage_count = $finished_room_count;
	else
		$_onepage_count = $logview_onepage_count;
		*/

	//分頁處理
	$perpage = 25;
	$url = "old_log.php".$isurl;
	$page = max(1, (int) $page);
	$start = ($page - 1) * $perpage;
	$multipage = multi($num, $perpage,$page,$url);

	$res_oldlog_list = $db->query("select room_no,room_name,last_updated,date,game_option,option_role,max_user,victory_role,dellook
																			from room where status = 'finished' $issql ORDER BY room_no DESC LIMIT $start,$perpage;");

	
	$oldoutputhtml = '<a href="index.php">←返回</a> <a href="old_log.php?all=1">[全部顯示]</a><br /> 
';
	$oldoutputhtml .= "<img src=\"img/old_log_title.webp\"><br /> \r\n";
	
	if($num == 0)
	{
		return $oldoutputhtml . "　　　　沒有遊戲紀錄";
	}
	
	$oldoutputhtml .= "<div align=center>";
	
	$oldoutputhtml .= "<table border=0 cellpadding=0 cellspacing=0><tr>";
	$oldoutputhtml .= "<td align=right>";
	$oldoutputhtml .= '<form name="old_log" action="old_log.php" method="get" enctype="multipart/form-data">
		搜尋<input type="text" name="search" size="10" value="" /> 
		<input id="submit" type="submit" value="送出" />
	</form>';
	$oldoutputhtml .= $multipage;
	$oldoutputhtml .= "</tr></td>";
	$oldoutputhtml .= "<tr><td>";
	$oldoutputhtml .= "<table border=1 align=center cellspacing=1 bgcolor=\"#CCCCCC\"> \r\n";
	
	
	
	
	$oldoutputhtml .= "<tr><th class=column>村No</th><th class=column>村名</th><th class=column>結束時間</th><th class=column>人數</th><th class=column>勝</th><th colspan=12 class=column>選項</th></tr> \r\n";
	
	//表示する行の最初に移動
//	mysql_data_seek($res_oldlog_list,$begin_no-1);
	
	while($oldlog_list_arr = $db->fetch_array($res_oldlog_list))
	{
	//	$oldlog_list_arr = $db->fetch_array($res_oldlog_list);
		$log_room_no = $oldlog_list_arr['room_no'];
		$log_room_name = $oldlog_list_arr['room_name'];
		$last_updated = $oldlog_list_arr['last_updated'];
		$log_room_date = $oldlog_list_arr['date'];
		$log_room_game_option = $oldlog_list_arr['game_option'];
		$log_room_option_role = $oldlog_list_arr['option_role'];
		$log_room_max_user = $oldlog_list_arr['max_user'];
		$log_room_victory_role = $oldlog_list_arr['victory_role'];
		$log_room_dellook_role = $oldlog_list_arr['dellook'];
		
		
		/*
		if( strstr($log_room_game_option,"wish_role") )
			$log_wish_role_str = "<img src=\"$room_option_wish_role_image\" width=16px height=16px alt=\"希望角色\" title=\"希望角色\">";
		else
			$log_wish_role_str = "<br />";
		
		if( strstr($log_room_game_option,"real_time") )
		{
			if( strstr($log_room_game_option,"real_time:") )
			{
				//実時間的制限時間を取得
				$real_time_str = strstr($log_room_game_option,"real_time");
				sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_minutes,$night_real_limit_minutes);
				$real_time_alt_str = "限制時間　白： $day_real_limit_minutes 分　夜： $night_real_limit_minutes 分";
			}
			else
				$real_time_alt_str = "限制時間";
			
			
			$log_real_time_str = "<img src=\"$room_option_real_time_image\" width=16px height=16px alt=\" $real_time_alt_str \" title=\" $real_time_alt_str \">";
		}
		else
			$log_real_time_str = "<br />";
		
		if( strstr($log_room_game_option,"dummy_boy") )
			$log_dummy_boy_str = "<img src=\"$room_option_dummy_boy_image\" width=16px height=16px alt=\"第一天晚上的替身君\" title=\"第一天晚上的替身君\">";
		else
			$log_dummy_boy_str = "<br />";
		
		if( strstr($log_room_game_option,"open_vote") )
			$log_open_vote_str = "<img src=\"$room_option_open_vote_image\" width=16px height=16px alt=\"公開投票結果票數\" title=\"公開投票結果票數\">";
		else
			$log_open_vote_str = "<br />";
		
		if($log_room_dellook_role)
			$log_dellook_str = "<img src=\"$room_option_rei_image\" width=16px height=16px alt=\"允許幽靈觀看角色\" title=\"允許幽靈觀看角色\">";
		else
			$log_dellook_str = "<br />";
		if( strstr($log_room_game_option,"comoutl") )
			$log_comoutl_str = "<img src=\"$room_option_conn_image\" width=16px height=16px alt=\"晚上共生對話允許顯示\" title=\"晚上共生對話允許顯示\">";
		else
			$log_comoutl_str = "<br />";
		if(strstr($log_room_option_role,"lovers")) {
			$log_lovers_str = "<img src=\"$room_option_lovers_image\" width=16px height=16px alt=\"相戀的兩人\" title=\"相戀的兩人\">";
		} else {
			$log_lovers_str = "<br />";
		}
		*/
		
		
			$log_option_str = ''; //ゲームオプションの画像
	if(strstr((string) $log_room_game_option,"wish_role"))
	{
		$log_option_str .= "<img src=\"$room_option_wish_role_image\" border=0 width=16px height=16px alt=\"希望角色\" title=\"希望角色\" >";
	}
	if(strstr((string) $log_room_game_option,"real_time"))
	{

		$real_time_str = strstr((string) $log_room_game_option,"real_time");
		sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_minutes,$night_real_limit_minutes);
		$real_time_alt_str = "限制時間　白： $day_real_limit_minutes 分　夜： $night_real_limit_minutes 分";

		$log_option_str .= "<img src=\"$room_option_real_time_image\" border=0 width=16px height=16px alt=\" $real_time_alt_str \" title=\" $real_time_alt_str \">";
		//$log_option_str .= "[".$day_real_limit_minutes.":".$night_real_limit_minutes."]";
	}
	if(strstr((string) $log_room_game_option,"dummy_boy"))
	{
		$log_option_str .= "<img src=\"$room_option_dummy_boy_image\" border=0 width=16px height=16px alt=\"第一天晚上的替身君\" title=\"第一天晚上的替身君\" >";
	}
	if(strstr((string) $log_room_game_option,"dummy_isred"))
	{
		$log_option_str .= "<img src=\"$room_option_dummy_boy_image\" border=0 width=16px height=16px alt=\"替身君部分職業自動遺書，不知道職業\" title=\"替身君部分職業自動遺書，不知道職業\" >";
	}
	if(strstr((string) $log_room_game_option,"dummy_autolw"))
	{
		$log_option_str .= "<img src=\"$room_option_dummy_boy_image\" border=0 width=16px height=16px alt=\"替身君部分職業自動遺書，知道職業\" title=\"替身君部分職業自動遺書，知道職業\" >";
	}
	if(strstr((string) $log_room_game_option,"open_vote"))
	{
		$log_option_str .= "<img src=\"$room_option_open_vote_image\" border=0 width=16px height=16px alt=\"公開投票結果票數\" title=\"公開投票結果票數\" >";
	}
	if($log_room_dellook_role && !strstr((string) $log_room_option_role, "cat") && !strstr((string) $log_room_game_option,"as_gm"))
	{
		$log_option_str .= "<img src=\"$room_option_rei_image\" border=0 width=16px height=16px title=\"幽靈可以看到角色\">";
	}			
	if(strstr((string) $log_room_game_option,"votedme"))
	{
		$log_option_str .= "<img src=\"$room_option_voteme_image\" border=0 width=16px height=16px title=\"允許白天自投(一次)\">";
	}
	if(strstr((string) $log_room_game_option,"istrip"))
	{
		$log_option_str .= "<img src=\"$room_option_trip_image\" border=0 width=16px height=16px title=\"強制Trip註冊\">";
	}
	if(strstr((string) $log_room_game_option,"will"))
	{
		$log_option_str .= "<img src=\"$room_option_will_image\" border=0 width=16px height=16px title=\"允許遺書顯示\">";
	}
	if(strstr((string) $log_room_game_option,"usr_guest"))
	{
		$log_option_str .= "<img src=\"$room_option_guest_image\" border=0 width=16px height=16px title=\"匿名遊戲\">";
	}
	
	if(strstr((string) $log_room_game_option,"as_gm"))
	{
		$gm_str = strstr((string) $log_room_game_option,"gm:");
		sscanf($gm_str, "gm:%s", $gmtrip);
		$log_option_str .= "<img src=\"$room_option_gm_image\" border=0 width=16px height=16px title=\"GM制，Trip: $gmtrip\">";
	}

	$log_option_str .= '</td><td align=left valign=center class=row>';
	
	if($log_room_max_user >= 16) 
	{
		
		if(strstr((string) $log_room_option_role,"decide"))
		{
			$log_option_str .= "<img src=\"$room_option_decide_image\" border=0 width=16px height=16px alt=\"16人以上時決定者登場\" title=\"16人以上時決定者登場\">";
		}
		if(strstr((string) $log_room_option_role,"authority"))
		{
			$log_option_str .= "<img src=\"$room_option_authority_image\" border=0 width=16px height=16px alt=\"16人以上時權力者登場\" title=\"16人以上時權力者登場\">";
		}
		if(strstr((string) $log_room_option_role,"noble"))
		{
			$log_option_str .= "<img src=\"$room_option_noble_image\" border=0 width=16px height=16px alt=\"13人以上時貴族與奴隸登場\" title=\"13人以上時貴族與奴隸登場\">";
		}
		
		// comm
		if($log_room_max_user >= 20 && strstr((string) $log_room_option_role,"comlover"))
		{
			if(strstr((string) $log_room_game_option,"comoutl"))
			{
				$log_option_str .= "<img src=\"$room_option_conn_image\" border=0 width=16px height=16px title=\"晚上共生對話允許顯示\">";
			}				
			$log_option_str .= "<img src=\"$room_option_conn_image\" border=0 width=16px height=16px title=\"20人時出現共有者與隨機戀人\">";
			$log_option_str .= "<img src=\"$room_option_lovers_image\" border=0 width=16px height=16px title=\"相戀的兩人，隨機版\">";			} 
		else 
		{
			if(!(strstr((string) $log_room_option_role,"noflash") || strstr((string) $log_room_option_role,"r_lovers") || strstr((string) $log_room_option_role,"s_lovers")) && strstr((string) $log_room_game_option,"comoutl"))
			{
				$log_option_str .= "<img src=\"$room_option_conn_image\" border=0 width=16px height=16px title=\"晚上共生對話允許顯示\">";
			}								
			if(strstr((string) $log_room_option_role,"noflash"))
			{
				$log_option_str .= "<img src=\"$room_option_noconn_image\" border=0 width=16px height=16px title=\"13人時無共有者或任何兩人規則設定\">";
			}				
			if(strstr((string) $log_room_option_role,"r_lovers"))
			{
				$log_option_str .= "<img src=\"$room_option_lovers_image\" border=0 width=16px height=16px title=\"13人時出現相戀的兩人，隨機版\">";
			}
			if(strstr((string) $log_room_option_role,"s_lovers"))
			{
				$log_option_str .= "<img src=\"$room_option_lovers_image\" border=0 width=16px height=16px title=\"13人時出現相戀的兩人，村村戀版本\">";
			}
		}
		
		if(strstr((string) $log_room_option_role, 'mytho'))
		{
			$log_option_str .= "<img src=\"$room_option_mytho_image\" border=0 width=16px height=16px title=\"妄想成為人狼或占卜師的「生物」\">";
		}
	}
	
	if($log_room_max_user >= 20)
	{
		// poison
		if(strstr((string) $log_room_option_role,"poison"))
		{
			$log_option_str .= "<img src=\"$room_option_poison_image\" border=0 width=16px height=16px alt=\"20人以上時埋毒者登場\" title=\"20人以上時埋毒者登場\">";
		}
		if(strstr((string) $log_room_option_role,"cat"))
		{
			$log_option_str .= "<img src=\"$room_option_cat_image\" border=0 width=16px height=16px alt=\"20人以上時貓又登場\" title=\"20人以上時貓又登場\">";
		}
		
		// wolf
		if(strstr((string) $log_room_option_role,"wfbig"))
		{
			$log_option_str .= "<img src=\"$room_option_wfbig_image\" border=0 width=16px height=16px alt=\"20人以上時大狼登場\" title=\"20人以上時大狼登場\">";
		}
		if(strstr((string) $log_room_option_role,"morewolf") && !strstr((string) $log_room_option_role,"poison") && !strstr((string) $log_room_option_role,"cat"))
		{
			$log_option_str .= "<img src=\"$room_option_wfbig_image\" border=0 width=16px height=16px alt=\"20人以上無毒時追加人狼\" title=\"20人以上無毒時追加人狼\">";
		}
		
		// fox
		if(strstr((string) $log_room_option_role,"betr"))
		{
			$log_option_str .= "<img src=\"$room_option_betr_image\" border=0 width=16px height=16px alt=\"妖狐的同伴\" title=\"妖狐的同伴\">";
		}
		if(strstr((string) $log_room_option_role,"foxs"))
		{
			$log_option_str .= "<img src=\"$room_option_foxs_image\" border=0 width=16px height=16px alt=\"兩隻妖狐\" title=\"兩隻妖狐\">";
		}
		if(strstr((string) $log_room_option_role,"fosi"))
		{
			$log_option_str .= "<img src=\"$room_option_fosi_image\" border=0 width=16px height=16px alt=\"妖狐的占\" title=\"妖狐的占\">";
		}
		
		if(strstr((string) $log_room_option_role, 'owlman')) 
		{
			$log_option_str .= "<img src=\"$room_option_owlman_image\" border=0 width=16px height=16px title=\"帶來不幸的村人\">";
		}
		
		if(strstr((string) $log_room_option_role,"pengu"))
		{
			$log_option_str .= "<img src=\"$room_option_pengu_image\" border=0 width=16px height=16px title=\"小企鵝客串登場\">";
		}
		
		
	}
	
		$voctory_role_str = match ($log_room_victory_role) {
            'human' => "<img src=\"$victory_role_human_image\" width=15px height=15px alt=\"村民勝利\" title=\"村民勝利\">",
            'wolf' => "<img src=\"$victory_role_wolf_image\" width=15px height=15px alt=\"人狼勝利\" title=\"人狼勝利\">",
            'fox', 'fox1', 'fox2' => "<img src=\"$victory_role_fox_image\" width=15px height=15px alt=\"妖狐勝利\" title=\"妖狐勝利\">",
            'draw' => "<img src=\"$victory_role_draw_image\" width=15px height=15px alt=\"平手\" title=\"平手\">",
            'lover' => "<img src=\"$victory_role_lovers_image\" width=15px height=15px alt=\"戀人勝利\" title=\"戀人勝利\">",
            'custo' => "<img src=\"$victory_role_custom_image\" width=15px height=15px alt=\"自定\" title=\"由GM宣告勝利方\">",
            default => "-",
        };
		
		$dead_room_color = $log_room_victory_role == NULL ? ' style="color:silver"' : '';
		
		$oldoutputhtml .= "<tr> \r\n";
		
		$oldoutputhtml .= "<td align=right valign=middle class=row>$log_room_no</td> \r\n";
		$oldoutputhtml .= "<td align=right valign=middle class=row> \r\n";
		$oldoutputhtml .= "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no\" $dead_room_color >$log_room_name 村</a>\r\n";
		$oldoutputhtml .= "<small>(";/*"<a href=\"old_log.php?log_mode=on&room_no=$log_room_no\" $dead_room_color >正</a>\r\n";*/
		$oldoutputhtml .= "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on\" $dead_room_color >逆</a>\r\n";
		$oldoutputhtml .= "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&heaven_talk=on\" $dead_room_color >靈</a>\r\n";
		$oldoutputhtml .= "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on&heaven_talk=on\" $dead_room_color >逆&amp;靈</a>\r\n";
		$oldoutputhtml .= "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&heaven_only=on\" $dead_room_color ><small>逝</small></a>\r\n";
		$oldoutputhtml .= "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on&heaven_only=on\" $dead_room_color ><small>逆&amp;逝</small></a>\r\n";
/*
		echo ")</small>";
		echo "&nbsp;<small>([職]&nbsp;<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&add_role=on\" $dead_room_color >正</a>\r\n";
		echo "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&add_role=on&reverse_log=on\" $dead_room_color >逆</a>\r\n";
		echo "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&add_role=on&heaven_talk=on\" $dead_room_color >靈</a>\r\n";
		echo "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&add_role=on&reverse_log=on&heaven_talk=on\" $dead_room_color >逆&amp;靈</a>\r\n";
		echo "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&add_role=on&heaven_only=on\" $dead_room_color ><small>逝</small></a>\r\n";
		echo "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&add_role=on&reverse_log=on&heaven_only=on\" $dead_room_color ><small>逆&amp;逝</small></a>\r\n";
*/
		$oldoutputhtml .= ")</small></td> \r\n";
		
		$oldoutputhtml .= "<td align=right valign=middle class=row><small>".gmdate("Y-m-d H:i:s",$last_updated + $time_zone)."</small></td> \r\n";
		$oldoutputhtml .= "<td align=center valign=middle class=row><img src=\"$maxuser_image_array[$log_room_max_user]\"></td> \r\n";
		$oldoutputhtml .= "<td align=center valign=middle class=row>$voctory_role_str</td> \r\n";
		/*
		echo "<td valign=middle width=16 class=row>$log_wish_role_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_real_time_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_dummy_boy_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_open_vote_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_decide_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_authority_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_poison_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_wfbig_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_betr_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_dellook_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_comoutl_str </td> \r\n";
		echo "<td valign=middle width=16 class=row>$log_lovers_str </td> \r\n";
		*/
		$oldoutputhtml .= "<td valign=middle align=left class=row>$log_option_str </td> \r\n";
	}
	$db->free_result($res_oldlog_list);
	$oldoutputhtml .= "</table> \r\n";
	
	$oldoutputhtml .= "</td></tr>";
	$oldoutputhtml .= "</table>";
	return $oldoutputhtml . "</div>";
}

//----------------------------------------------------------
//指定の部屋Noのログを出力する
function OldLogOutput(?string $room_no): string
{
	global $live,$reverse_log,$heaven_only,$db,$isold,$max_user,$game_option,$option_role;
	
	$oldoutputhtml = "";
	if($room_no == NULL)
	{
		$oldoutputhtml .= "　　　・村を指定してください<br /> \r\n";
		return $oldoutputhtml . "　　　<a href=\"old_log.php\">←返回</a><br /> \r\n";
	}
	
	//日付と白か夜かを取得
	$res_room = $db->query("select date,room_name,room_comment,status,day_night,max_user,game_option,option_role,checkdel from room where room_no = '$room_no'");
	$room_arr = $db->fetch_array($res_room);
	$game_option = $room_arr['game_option'];
	$option_role = $room_arr['option_role'];
	$max_user = $room_arr['max_user'];
	$last_date = $room_arr['date'];
	$room_name = $room_arr['room_name'];
	$room_comment = $room_arr['room_comment'];
	
	$status = $room_arr['status'];
	$day_night = $room_arr['day_night'];
	$db->free_result($res_room);
	
	if( !( ($status == 'finished') && ($day_night == 'aftergame') ) )
	{
		$oldoutputhtml .= "　　　・這個紀錄還無法瀏覽<br /> \r\n";
		return $oldoutputhtml . "　　　<a href=\"old_log.php\">←返回</a><br /> \r\n";
	}
	
	if ($room_arr['checkdel'] == 2) {
		$isold = "_old";
	}
	
	
	$live = 'dead'; //他の関数に影響、すべて表示するため
	
	$room_message = "<strong style=\"font-size:15pt;\">" . $room_name ."村</strong>　～" . $room_comment ."～[" . $room_no . "番地]<br />";
	
	
	//返回先を前の頁數にする
	$_SERVER['HTTP_REFERER'] = empty($_SERVER['HTTP_REFERER']) ? "" : $_SERVER['HTTP_REFERER'];
	$referer_page_str = strstr((string) $_SERVER['HTTP_REFERER'],"page");
	sscanf($referer_page_str,"page=%s",$referer_page);
	$oldoutputhtml .= "<a href=\"old_log.php?page=$referer_page\">←返回</a><br /> \r\n";
	$oldoutputhtml .= "<table>\r\n";
	$oldoutputhtml .= "<tr>\r\n";
	$oldoutputhtml .= "<td> $room_message </td> \r\n";
	$oldoutputhtml .= "</tr>";
	
	$oldoutputhtml .= "<tr><td>";
	$oldoutputhtml .= VillageOptOutput(true);
	$oldoutputhtml .= "</td></tr>";
	
	$oldoutputhtml .= "<tr>\r\n";
	$oldoutputhtml .= PlayerListOutput(1,1,true);   //Playヤーリストを出力
	$oldoutputhtml .= "</tr><tr>\r\n";
	
	if ($reverse_log == 'on') {
     //逆順表示、一日目から最終日まで
     if($heaven_only == 'on')
   		{
   			for($i=1 ; $i<=$last_date ;$i++)
   			{
   				$oldoutputhtml .= DateTalkLogOutput($i,'heaven_only',$reverse_log);
   				$oldoutputhtml .= "</tr><tr>\r\n";
   			
   			}
   		}
   		else
   		{
   			$date = empty($date) ? "" : $date;
   			$oldoutputhtml .= DateTalkLogOutput($date,'beforegame',$reverse_log);
   			$oldoutputhtml .= "</tr><tr>\r\n";
   			
   			for($i=1 ; $i<=$last_date ;$i++)
   			{
   				$oldoutputhtml .= DateTalkLogOutput($i,'',$reverse_log);
   				$oldoutputhtml .= "</tr><tr>\r\n";
   			
   			}
   			
   			$oldoutputhtml .= VictoryOutput(true);
   			$oldoutputhtml .= "</tr><tr>\r\n";
   			
   			$oldoutputhtml .= DateTalkLogOutput($date,'aftergame',$reverse_log);
   			$oldoutputhtml .= "</tr><tr>\r\n";
   		}
 } elseif ($heaven_only == 'on') {
     for($i=$last_date ; $i>0 ;$i--)
  			{
  				$oldoutputhtml .= DateTalkLogOutput($i,'heaven_only',$reverse_log);
  				$oldoutputhtml .= "</tr><tr>\r\n";
  			
  			}
 } else
		{
			$date = empty($date) ? "" : $date;
			$oldoutputhtml .= DateTalkLogOutput($date,'aftergame',$reverse_log);
			$oldoutputhtml .= "</tr><tr>\r\n";

			$oldoutputhtml .= VictoryOutput(true);
			$oldoutputhtml .= "</tr><tr>\r\n";

			for($i=$last_date ; $i>0 ;$i--)
			{
				$oldoutputhtml .= DateTalkLogOutput($i,'',$reverse_log);
				$oldoutputhtml .= "</tr><tr>\r\n";
			}

			$oldoutputhtml .= DateTalkLogOutput($date,'beforegame',$reverse_log);
			$oldoutputhtml .= "</tr><tr>\r\n";
		}
	return $oldoutputhtml . "</tr></table>\r\n";
}

//----------------------------------------------------------
//指定の日付の会話ログを出力
function DateTalkLogOutput($set_date,$set_location,$reverse_log): string
{
	global $room_no,$last_date,$date,$day_night,$heaven_talk,$heaven_only,$db,$isold,$time_zone,$dummy_boy_imgid;
	
	if($reverse_log == 'on') {//逆順、初日から最終日まで
		$select_order1 = 'order by tid';
		$select_order2 = 'order by ta.tid';
}	else {//最終日から初日まで
		$select_order1 = 'order by tid DESC';
		$select_order2 = 'order by ta.tid DESC';
}
	
	/*
	select u.uname as talk_uname,
										u.handle_name as talk_handle_name,
										u.sex as talk_sex,u.role as talk_role,
										i.color as talk_color,u.icon_no as iconno,
										ta.sentence as sentence,ta.time as ttime,
										ta.font_type as font_type,
										ta.location as location,tr.icon as tcolor,u.trip
										from (user_entry u,talk{$isold} ta,user_icon i)
inner join (select tid from talk{$isold} where room_no = '$room_no' $select_order1) as t on ta.tid=t.tid
left join user_trip tr on u.trip = tr.trip
where ta.date = $set_date and ((ta.heaven = '1') or (ta.uname = 'system')) 
and ta.date = $set_date
and ((u.room_no = '$room_no' and u.uname = ta.uname and u.icon_no = i.icon_no)
or (u.uid = 1 and ta.uname = 'system' and u.icon_no = i.icon_no))
$select_order2
*/
	
	
	if($set_location == 'heaven_only')
	{
		//会話の用戶名、ハンドル名、発言、発言のタイプを取得
		$result = $db->query("SELECT 
    u.uname AS talk_uname,
    u.handle_name AS talk_handle_name,
    u.sex AS talk_sex,
    u.role AS talk_role,
    i.color AS talk_color,
    u.icon_no AS iconno,
    ta.sentence AS sentence,
    ta.time AS ttime,
    ta.font_type AS font_type,
    ta.location AS location,
    tr.icon AS tcolor,
    u.trip
FROM 
    talk{$isold} ta
    JOIN (
        SELECT tid 
        FROM talk{$isold} 
        WHERE room_no = '$room_no' 
        $select_order1
    ) t ON ta.tid = t.tid
    JOIN user_entry u 
        ON (
            (u.room_no = '$room_no' AND u.uname = ta.uname AND u.icon_no = i.icon_no)
            OR (u.uid = 1 AND ta.uname = 'system' AND u.icon_no = i.icon_no)
        )
    JOIN user_icon i ON u.icon_no = i.icon_no
    LEFT JOIN user_trip tr ON u.trip = tr.trip
WHERE 
    ta.date = $set_date
    AND (ta.heaven = '1' OR ta.uname = 'system')
$select_order2",'UNBUFFERED');
		$table_class = ($reverse_log == 'on') && ($set_date != 1) ? "day" : "night";
	}
	elseif( ($set_location == 'beforegame') || (($set_location == 'aftergame')) )
	{
		//会話の用戶名、ハンドル名、発言、発言のタイプを取得
$result = $db->query("select u.uname as talk_uname,
										u.handle_name as talk_handle_name,
										u.sex as talk_sex,u.role as talk_role,
										i.color as talk_color,u.icon_no as iconno,
										ta.sentence as sentence,ta.time as ttime,
										ta.font_type as font_type,
										ta.location as location,tr.icon as tcolor,u.trip
										from (user_entry u,talk{$isold} ta,user_icon i)
inner join (select tid from talk{$isold} where room_no = '$room_no' $select_order1) as t on ta.tid=t.tid
left join user_trip tr on u.trip = tr.trip
where ta.location like '$set_location%'
and ((u.room_no = '$room_no' and u.uname = ta.uname and u.icon_no = i.icon_no)
or (u.uid = 1 and ta.uname = 'system' and u.icon_no = i.icon_no))
$select_order2",'UNBUFFERED');
			$table_class = $set_location;
	}
	else
	{
		//会話の用戶名、ハンドル名、発言、発言のタイプを取得
		$result = $db->query("select u.uname as talk_uname,
										u.handle_name as talk_handle_name,
										u.sex as talk_sex,u.role as talk_role,
										i.color as talk_color,u.icon_no as iconno,
										ta.sentence as sentence,ta.time as ttime,
										ta.font_type as font_type,
										ta.location as location,tr.icon as tcolor,u.trip
										from (user_entry u,talk{$isold} ta,user_icon i)
inner join (select tid from talk{$isold} where room_no = '$room_no' $select_order1) as t on ta.tid=t.tid
										left join user_trip tr on u.trip = tr.trip
where ta.date = $set_date and ta.location <> 'aftergame' and ta.location <> 'beforegame'
and ((u.room_no = '$room_no' and u.uname = ta.uname and u.icon_no = i.icon_no)
or (u.uid = 1 and ta.uname = 'system' and u.icon_no = i.icon_no))
$select_order2",'UNBUFFERED');
		
		$table_class = ($reverse_log == 'on') && ($set_date != 1) ? "day" : "night";
	}
	
	
	
//	$talk_count = $db->fetch_row($result);
	
	//print($db->result($result,1,6));
	
	$oldoutputhtml = "<table class=$table_class border=0 cellpadding=0 cellspacing=0 style=\"font-size:12pt;\">";
	
	
	if( ($set_location != 'beforegame') && ($set_location != 'aftergame') && ($set_date != $last_date) && ($reverse_log != 'on')
																									&& ($heaven_only != 'on'))
	{
		
		$day_night = "day";
		$date = $set_date+1;
		
		$oldoutputhtml .= "<tr><td colspan=3 width=1000>";
		$oldoutputhtml .= LastWordsOutput(true); //遺言を出力
		$oldoutputhtml .= "</td></tr>\r\n";
		
		$oldoutputhtml .= "<tr><td colspan=3 width=1000>";
		$oldoutputhtml .= DeadManOutput(true);         //死亡者を出力
		$oldoutputhtml .= "</td></tr>";
	}
	
	$day_night = ($reverse_log == 'on') && ($set_date != 1) ? "day" : "night";
	
	//出力
	if (!$result) {
		$oldoutputhtml .= "請重新整理";
		exit;
	}
	while($talk_log_array = $db->fetch_array($result))
	{
	//	$talk_log_array = $db->fetch_array($result);
		$talk_uname = $talk_log_array['talk_uname'];
		$talk_handle_name = $talk_log_array['talk_handle_name'];
		$talk_sex = $talk_log_array['talk_sex'];
		$talk_color = $talk_log_array['talk_color'];
		$talk_role = $talk_log_array['talk_role'];
		$sentence = $talk_log_array['sentence'];
		$font_type = $talk_log_array['font_type'];
		$location = $talk_log_array['location'];
		$ttime = gmdate("H:i:s",$talk_log_array['ttime'] + $time_zone);
		
		$sentence = str_replace("\n","<br />",(string) $sentence); //改行を<br />タグに置換
		if ($talk_log_array['tcolor'] && $talk_log_array['iconno'] == $dummy_boy_imgid) {
			$talk_color = $talk_log_array['tcolor'];
		}
		
		if( strstr((string) $location,"day") && ($day_night == 'night') && ($reverse_log != 'on') )
		{
			
			$oldoutputhtml .= "</table>";
			
			if($heaven_only != 'on')
			{
				$oldoutputhtml .= "<table border=0 cellpadding=0 cellspacing=0>";
				
				$oldoutputhtml .= "<tr><td colspan=3 width=1000>";
				
				$date = $set_date;
				$oldoutputhtml .= DeadManOutput(true);         //死亡者を出力
				$oldoutputhtml .= "</td></tr>";
				
				$oldoutputhtml .= "<tr><td colspan=3 width=1000>";
				$date = $set_date;
				$oldoutputhtml .= VoteListOutput(true);            //投票結果出力
				$oldoutputhtml .= "</td></tr>";
				$oldoutputhtml .= "</table>";
			}
			
			$day_night = "day";
			$table_class = "day";
			
			$oldoutputhtml .= "<table class=$table_class border=0 cellpadding=0 cellspacing=0 style=\"font-size:12pt;\">";
		}
		elseif( strstr((string) $location,"night") && ($day_night == 'day') && ($reverse_log == 'on') )
		{
			
			$oldoutputhtml .= "</table>";
			
			if($heaven_only != 'on')
			{
				$oldoutputhtml .= "<table border=0 cellpadding=0 cellspacing=0>";
				$oldoutputhtml .= "<tr><td colspan=3 width=1000>";
				$oldoutputhtml .= VoteListOutput(true);            //投票結果出力
				$oldoutputhtml .= "</td></tr>";
				$oldoutputhtml .= "<tr><td colspan=3 width=1000>";
				$day_night = "night";
				$date = $set_date;
				$oldoutputhtml .= DeadManOutput(true);         //死亡者を出力
				$oldoutputhtml .= "</td></tr>";
				
				
				$oldoutputhtml .= "</table>";
			}
			
			
			
			$day_night = "night";
			$table_class = "night";
			
			$oldoutputhtml .= "<table class=$table_class border=0 cellpadding=0 cellspacing=0 style=\"font-size:12pt;\">";
		}
		
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
		
		//会話出力-----------------------------------------------------------------
		if( strstr((string) $location,'system') && ($sentence == 'OBJECTION') ) //異議あり
		{
			$this_bgcolor = $talk_sex == 'male' ? '#336699' : '#FF0099';

			$oldoutputhtml .= "<tr>\r\n";

			$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:$this_bgcolor;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name 表示抗議</td>\r\n";

			$oldoutputhtml .= "</tr>\r\n";
		//廢村
		} elseif (strstr((string) $location,'system') && $sentence == 'ROOMEND') {
			$oldoutputhtml .= "<tr>\r\n";
			$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:red;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name 要求廢村</td>\r\n";
			$oldoutputhtml .= "</tr>\r\n";
		} elseif( strstr((string) $location,'system') && ($sentence == 'GAMESTART_DO') ) //ゲーム開始に投票
		{/*
			echo "<tr>\r\n";

			echo "<td width=1000 colspan=3 align=left style=\"background-color:#999900;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name はゲーム開始に投票しました</td>\r\n";

			print("</tr>\r\n");*/
		}
		elseif( strstr((string) $location,'system') && strstr($sentence,'KICK_DO') ) //キックに投票
		{
			$sentence_enc = explode("\t", $sentence);
			$target_handle_name = $sentence_enc[1];
			/*
			$sentence_enc = str_replace(" ","\\space;",$sentence);
			sscanf($sentence_enc,"KICK_DO\t%s",$target_handle_name);
			$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
			*/
			
			$oldoutputhtml .= "<tr>\r\n";
			
			$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#aaaa33;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name 對 $target_handle_name 投票踢出</td>\r\n";
			
			$oldoutputhtml .= "</tr>\r\n";
		}
		elseif( strstr($sentence,'NIGHT') )
		{
			$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#efefef;color:black;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　< < 日落、黑暗的夜晚來臨 > > </td>\r\n";
		}
		elseif( $talk_uname == 'system' ) //用戶名:system は$sentenceをそのまま出力(系統メッセージ)
		{
			$oldoutputhtml .= "<tr>\r\n";
			
			if( strstr($sentence,'MORNING') )
			{
				sscanf($sentence,"MORNING\t%d",$morning_date);
				$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#efefef;color:black;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　< < 早晨來臨 $morning_date 日目的早上開始 > > </td>\r\n";
			}
			elseif( $heaven_only != 'on')
			{
				$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#efefef;color:black;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　$sentence </td>\r\n";
			}
			
			$oldoutputhtml .= "</tr>\r\n";
		} elseif (strstr((string) $location,'system') && strstr($sentence,'VOTE_DO')) {
      //処刑に投票
      $oldoutputhtml .= "<tr>\r\n";
      $sentence_enc = explode("\t", $sentence);
      $target_handle_name = $sentence_enc[1];
      /*
      $sentence_enc = str_replace(" ","\\space;",$sentence);
      sscanf($sentence_enc,"VOTE_DO\t%s",$target_handle_name);
      $target_handle_name = str_replace("\\space;"," ",$target_handle_name);
      */
      $oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#999900;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name 對 $target_handle_name 投票處死</td>\r\n";
      $oldoutputhtml .= "</tr>\r\n";
  } elseif( strstr((string) $location,'system') && strstr($sentence,'WOLF_EAT') ) //狼の投票
			{
				$oldoutputhtml .= "<tr>\r\n";
				
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"WOLF_EAT\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/
				
				$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#CC3300;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name 人狼對 $target_handle_name 為鎖定目標</td>\r\n";
				
				$oldoutputhtml .= "</tr>\r\n";
			} elseif( strstr((string) $location,'system') && strstr($sentence,'FOSI_DO') ) //子狐投票
			{
				$oldoutputhtml .= "<tr>\r\n";
				
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"FOSI_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/
				
				$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#990099;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name 子狐對 $target_handle_name 占卜</td>\r\n";
				
				$oldoutputhtml .= "</tr>\r\n";
			} elseif( strstr((string) $location,'system') && strstr($sentence,'MAGE_DO') ) //占い師の投票
			{
				$oldoutputhtml .= "<tr>\r\n";
				
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"MAGE_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/
				
				$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#990099;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name 對 $target_handle_name 占卜</td>\r\n";
				
				$oldoutputhtml .= "</tr>\r\n";
			} elseif( strstr((string) $location,'system') && strstr($sentence,'GUARD_DO') ) //獵人の投票
			{
				$oldoutputhtml .= "<tr>\r\n";
				
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"GUARD_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/
				
				$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#0099FF;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　$talk_handle_name 對 $target_handle_name 進行護衛</td>\r\n";
				
				$oldoutputhtml .= "</tr>\r\n";
			} elseif (strstr((string) $location,'system') && strstr($sentence,'CAT_DO')) {//貓又投票
				$oldoutputhtml .= "<tr>\r\n";
				
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				$sentence_enc = str_replace(" ","\\space;",$sentence);
				sscanf($sentence_enc,"CAT_DO\t%s",$target_handle_name);
				$target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				*/
				if($target_handle_name == ':;:NOP:;:')
					$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#006633;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　".msgimg($msg_cat_image)."$talk_handle_name 貓又 放棄行動</td>\r\n";
				else
					$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#006633;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　".msgimg($msg_cat_image)."$talk_handle_name 貓又對 $target_handle_name 進行復活</td>\r\n";
				
				$oldoutputhtml .= "</tr>\r\n";
			} elseif (strstr((string) $location,'system') && strstr($sentence,'MYTHO_DO')) {//謊投票
				$oldoutputhtml .= "<tr>\r\n";
				
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				 $sentence_enc = str_replace(" ","\\space;",$sentence);
				 sscanf($sentence_enc,"CAT_DO\t%s",$target_handle_name);
				 $target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				 */
				$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#FF8000;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　".msgimg($msg_mad_image)."$talk_handle_name 說謊狂對 $target_handle_name 進行模仿</td>\r\n";
				
				$oldoutputhtml .= "</tr>\r\n";
			} elseif (strstr((string) $location,'system') && strstr($sentence,'OWLMAN_DO')) {//夜梟投票
				$oldoutputhtml .= "<tr>\r\n";
				
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];
				/*
				 $sentence_enc = str_replace(" ","\\space;",$sentence);
				 sscanf($sentence_enc,"CAT_DO\t%s",$target_handle_name);
				 $target_handle_name = str_replace("\\space;"," ",$target_handle_name);
				 */
				if($target_handle_name == ':;:NOP:;:')
					$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#000080;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　".msgimg($msg_gm_image)."$talk_handle_name 夜梟 放棄行動</td>\r\n";
				else
					$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#000080;color:snow;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　".msgimg($msg_gm_image)."$talk_handle_name 夜梟對 $target_handle_name 進行詛咒</td>\r\n";
				
				
				$oldoutputhtml .= "</tr>\r\n";
			} elseif (strstr((string) $location,'system') && strstr($sentence,'PENGU_DO')) {
				$oldoutputhtml .= "<tr>\r\n";
				
				$sentence_enc = explode("\t", $sentence);
				$target_handle_name = $sentence_enc[1];

				if($target_handle_name == ':;:NOP:;:')
					$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#FFFF00;color:black;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　".msgimg($msg_mad_image)."$talk_handle_name 小企鵝 放棄行動</td>\r\n";
				else
					$oldoutputhtml .= "<td width=1000 colspan=3 align=left style=\"background-color:#FFFF00;color:black;font-weight:bold;border-top: silver 1px dashed;\">　　　　　　　　　　　　".msgimg($msg_mad_image)."$talk_handle_name 小企鵝對 $target_handle_name 進行搔癢</td>\r\n";
				
				$oldoutputhtml .= "</tr>\r\n";
			} elseif( strstr((string) $location,'heaven') ) //靈話
			{
				if( ($heaven_talk == 'on') || ($heaven_only == 'on') )
				{
					$oldoutputhtml .= "<tr>\r\n";
					
					
					if(strstr((string) $location, 'gm_bc')) {
						$sentence = '<font color="red">' . $sentence . '</font>';
						$talk_dead_name = "<font color=\"red\">".$talk_handle_name . MakeRoleString($talk_role) . "<small>(GM)(" .$talk_uname. ")</small></font>";
					} elseif(strstr((string) $location,'to_gm')) {
						$talk_dead_name = $talk_handle_name . MakeRoleString($talk_role) . "<small>(" .$talk_uname. ")</small>" . '→ GM';
					} elseif (strstr((string) $location, 'gm_to')) {
        $locarr = explode(':;:', (string) $location);
        $targ_name = $locarr[1];
        $sentence = '<font color="red">' . $sentence . '</font>';
        $talk_dead_name = 'GM('.$talk_uname.') → '.$targ_name;
    } else {
						$talk_dead_name = $talk_handle_name . MakeRoleString($talk_role) . "<small>(" .$talk_uname. ")</small>";
					}
						
					
					
					$oldoutputhtml .= "<td class=heaven width=200 align=left valign=middle style=\"border-bottom: silver 1px dashed;\">";
					$oldoutputhtml .= "<table class=heaven><td width=200 align=left valign=middle >";
					$oldoutputhtml .= "<font color=$talk_color>◆</font>$talk_dead_name ($ttime)</td></table></td>";
					$oldoutputhtml .= "<td><span style=\"margin:1px;\" align=left></span></td>";
					$oldoutputhtml .= "<td class=heaven valign=middle style=\"border-bottom: silver 1px dashed;\">";
					$oldoutputhtml .= "<table class=heaven ><td width=1000> $font_type_str $sentence </span></td></table>";
					$oldoutputhtml .= "</td>\r\n";
					
					$oldoutputhtml .= "</tr>\r\n";
				}
			}
		else //その他の全てを表示
			{
				if($location == 'night self_talk')
				{
					$talk_handle_name_str = $talk_handle_name . MakeRoleString($talk_role) . "<small>的自言自語</small>";
					$talk_text_color = 'snow';
				}
				elseif(StartsWith($location, 'night wolf'))
				{
					$talk_handle_name_str = $talk_handle_name . MakeWolfRoleString($talk_role) . "<small>(人狼)</small>";
					$talk_text_color = '#ffccff';
				}
				elseif($location == 'night common')
				{
					$talk_handle_name_str = $talk_handle_name . "<small>(共有者)</small>";
					$talk_text_color = '#ccffcc';
				}
				elseif($location == 'night lovers')
				{
					$talk_handle_name_str = $talk_handle_name . MakeLoverRoleString($talk_role) . "<small>(戀人)</small>";
					$talk_text_color = '#ff80ff';
				}
				elseif($location == 'night wolf lovers')
				{
					$talk_handle_name_str = $talk_handle_name . '[戀]' . "<small>(人狼/戀人)</small>";
					$talk_text_color = '#ff80ff';
				} elseif (strstr((string) $location, 'gm_bc')) {
       $talk_handle_name_str = $talk_handle_name . ' <small>(GM)</small>';
       $talk_text_color = '#ff0000';
   } elseif(strstr((string) $location,'to_gm')) {
					$talk_handle_name_str = $talk_handle_name . MakeRoleString($talk_role) . '→ GM';
					$talk_text_color = '';
				} elseif (strstr((string) $location, 'gm_to')) {
       $locarr = explode(':;:', (string) $location);
       $targ_name = $locarr[1];
       $talk_text_color = 'red';
       $talk_handle_name_str = 'GM → '.$targ_name;
   } elseif ($location == 'beforegame') {
       $talk_handle_name_str = $talk_handle_name;
       $talk_text_color = '';
   }
				else
				{
					$talk_handle_name_str = $talk_handle_name. MakeRoleString($talk_role) ;
					$talk_text_color = '';
				}
				//($location)
				
				$oldoutputhtml .= "<tr>\r\n";
				
				$oldoutputhtml .= "<td width=250 align=left valign=middle style=\"border-top: silver 1px dashed;color:$talk_text_color;\">";
				$oldoutputhtml .= "<font color=$talk_color>◆</font>$talk_handle_name_str <small>($ttime)</small></td>";
				$oldoutputhtml .= "<td><span style=\"margin:1px;\" align=left></span></td>";
				$oldoutputhtml .= "<td valign=middle style=\"border-top: silver 1px dashed;\">";
				$oldoutputhtml .= "<table class=$table_class ><td width=1000 style=\"color:$talk_text_color;\"> $font_type_str  $sentence </span></td></table>";
				$oldoutputhtml .= "</td>\r\n";
				
				$oldoutputhtml .= "</tr>\r\n";
			}
		
	}
	$db->free_result($result);
	
	if( ($set_location != 'beforegame') && ($set_location != 'aftergame') && ($set_date != $last_date) && ($reverse_log == 'on')
																										&& ($heaven_only != 'on') )
	{
		
		$day_night = "day";
		$oldoutputhtml .= "<tr><td colspan=3 width=1000> \r\n";
		
		$date = $set_date+1;
		$oldoutputhtml .= DeadManOutput(true);         //死亡者を出力
		$oldoutputhtml .= "</td></tr> \r\n";
		
		$oldoutputhtml .= "<tr><td colspan=3 width=1000>";
		$oldoutputhtml .= LastWordsOutput(true); //遺言を出力
		$oldoutputhtml .= "</td></tr>\r\n";
	}
	return $oldoutputhtml . "</table> \r\n";
}

function MakeRoleString($this_role): string {
	$role_str = '<small><small>[';

	if( strstr((string) $this_role,"human") )
	{
		$role_str .= "村";
	}
	if( strstr((string) $this_role,"wolf") )
	{
		$role_str .= "<font color=red>狼</font>";
	}
	if( strstr((string) $this_role,"mage") )
	{
		$role_str .= "<font color=#9933FF>占</font>";
	}
	if( strstr((string) $this_role,"necromancer") )
	{
		$role_str .= "<font color=#009900>靈</font>";
	}
	if( strstr((string) $this_role,"mad") )
	{
		$role_str .= "<font color=red>狂</font>";
	}
	if( strstr((string) $this_role,"common") )
	{
		$role_str .= "<font color=#cc9966>共</font>";
	}
	if( strstr((string) $this_role,"guard") )
	{
		$role_str .= "<font color=#3399ff>獵</font>";
	}
	if( strstr((string) $this_role,"fox") )
	{
		$role_str .= "<font color=#CC0099>狐</font>";
	}
	if( strstr((string) $this_role,"fosi") )
	{
		$role_str .= "<font color=#CC0099>子</font>";
	}
	if( strstr((string) $this_role,"betr") )
	{
		$role_str .= "<font color=#CC0099>背</font>";
	}
	if( strstr((string) $this_role,"poison") )
	{
		$role_str .= "<font color=#006633>毒</font>";
	}
	if( strstr((string) $this_role,"cat") )
	{
		$role_str .= "<font color=#006633>貓</font>";
	}

	if( strstr((string) $this_role,'mytho') )
	{
		$role_str .= "<font color=#FF8000>謊</font>";
	}
	if( strstr((string) $this_role,'owlman') )
	{
		$role_str .= "<font color=#000080 style=\"background-color: #ffffff\">梟</font>";
	}


	if( strstr((string) $this_role_desc,'mytho_tr') )
	{
		$role_str .= "<font color=#FF8000>謊</font>";
	}

	if( strstr((string) $this_role,'pengu') )
	{
		$role_str .= "<font color=#ff9933>鵝</font>";
	}

	// role-sensitive sub-roles
	// sub-wolf
	if( strstr((string) $this_role,"wfbig") )
	{
		$role_str .= "<font color=#ff0000>大</font>";
	}
	if( strstr((string) $this_role,"wfwtr") )
	{
		$role_str .= "<font color=#ccffff style=\"background-color: #000000\">冬</font>";
	}
	if( strstr((string) $this_role,"wfasm") )
	{
		$role_str .= "<font color=#ff3000>夢</font>";
	}
	if( strstr((string) $this_role,"wfbsk") )
	{
		$role_str .= "<font color=#d00000>+</font>";
	}

	if( strstr((string) $this_role,"lovers") )
	{
		$role_str .= "<font color=#ff80ff>戀</font>";
	}

	// role-insensitive sub-roles
	if( strstr((string) $this_role,"noble") )
	{
		$role_str .= "<font color=#999999>貴</font>";
	}
	if( strstr((string) $this_role,"slave") )
	{
		$role_str .= "<font color=#999999>奴</font>";
	}


	if( strstr((string) $this_role,"authority") )
	{
		$role_str .= "<font color=#999999>權</font>";
	}
	if( strstr((string) $this_role,"decide") )
	{
		$role_str .= "<font color=#999999>決</font>";
	}

	if( strstr((string) $this_role,"GM") )
	{
		$role_str .= "<font color=#ff8000>GM</font>";
	}
	
	return $role_str . ']</small></small>';
	}

	function MakeWolfRoleString($this_role): string {
		$role_str = '<small><small>[';

		if( strstr((string) $this_role_desc,'mytho_tr') )
		{
			$role_str .= "<font color=#FF8000>謊</font>";
		}

		// role-sensitive sub-roles
		// sub-wolf
		if( strstr((string) $this_role,"wfbig") )
		{
			$role_str .= "<font color=#ff0000>大</font>";
		}
		if( strstr((string) $this_role,"wfwtr") )
		{
			$role_str .= "<font color=#ccffff style=\"background-color: #000000\">冬</font>";
		}
		if( strstr((string) $this_role,"wfasm") )
		{
			$role_str .= "<font color=#ff3000>夢</font>";
		}
		if( strstr((string) $this_role,"wfbsk") )
		{
			$role_str .= "<font color=#d00000>+</font>";
		}

		if( strstr((string) $this_role,"lovers") )
		{
			$role_str .= "<font color=#ff80ff>戀</font>";
		}

		// role-insensitive sub-roles
		if( strstr((string) $this_role,"noble") )
		{
			$role_str .= "<font color=#999999>貴</font>";
		}
		if( strstr((string) $this_role,"slave") )
		{
			$role_str .= "<font color=#999999>奴</font>";
		}


		if( strstr((string) $this_role,"authority") )
		{
			$role_str .= "<font color=#999999>權</font>";
		}
		if( strstr((string) $this_role,"decide") )
		{
			$role_str .= "<font color=#999999>決</font>";
		}

		if($role_str == '<small><small>[') return '';

		return $role_str . ']</small></small>';
		}
		
	function MakeLoverRoleString($this_role): string {
		$role_str = '<small><small>[';

		if( strstr((string) $this_role,"human") )
		{
			$role_str .= "村";
		}
		if( strstr((string) $this_role,"wolf") )
		{
			$role_str .= "<font color=red>狼</font>";
		}
		if( strstr((string) $this_role,"mage") )
		{
			$role_str .= "<font color=#9933FF>占</font>";
		}
		if( strstr((string) $this_role,"necromancer") )
		{
			$role_str .= "<font color=#009900>靈</font>";
		}
		if( strstr((string) $this_role,"mad") )
		{
			$role_str .= "<font color=red>狂</font>";
		}
		if( strstr((string) $this_role,"common") )
		{
			$role_str .= "<font color=#cc9966>共</font>";
		}
		if( strstr((string) $this_role,"guard") )
		{
			$role_str .= "<font color=#3399ff>獵</font>";
		}
		if( strstr((string) $this_role,"fox") )
		{
			$role_str .= "<font color=#CC0099>狐</font>";
		}
		if( strstr((string) $this_role,"fosi") )
		{
			$role_str .= "<font color=#CC0099>子</font>";
		}
		if( strstr((string) $this_role,"betr") )
		{
			$role_str .= "<font color=#CC0099>背</font>";
		}
		if( strstr((string) $this_role,"poison") )
		{
			$role_str .= "<font color=#006633>毒</font>";
		}
		if( strstr((string) $this_role,"cat") )
		{
			$role_str .= "<font color=#006633>貓</font>";
		}

		if( strstr((string) $this_role,'mytho') )
		{
			$role_str .= "<font color=#FF8000>謊</font>";
		}
		if( strstr((string) $this_role,'owlman') )
		{
			$role_str .= "<font color=#000080 style=\"background-color: #ffffff\">梟</font>";
		}


		if( strstr((string) $this_role_desc,'mytho_tr') )
		{
			$role_str .= "<font color=#FF8000>謊</font>";
		}

		if( strstr((string) $this_role,'pengu') )
		{
			$role_str .= "<font color=#ff9933>鵝</font>";
		}

		// role-sensitive sub-roles
		// sub-wolf
		if( strstr((string) $this_role,"wfbig") )
		{
			$role_str .= "<font color=#ff0000>大</font>";
		}
		if( strstr((string) $this_role,"wfwtr") )
		{
			$role_str .= "<font color=#ccffff style=\"background-color: #000000\">冬</font>";
		}
		if( strstr((string) $this_role,"wfasm") )
		{
			$role_str .= "<font color=#ff3000>夢</font>";
		}
		if( strstr((string) $this_role,"wfbsk") )
		{
			$role_str .= "<font color=#d00000>+</font>";
		}

		// role-insensitive sub-roles
		if( strstr((string) $this_role,"noble") )
		{
			$role_str .= "<font color=#999999>貴</font>";
		}
		if( strstr((string) $this_role,"slave") )
		{
			$role_str .= "<font color=#999999>奴</font>";
		}


		if( strstr((string) $this_role,"authority") )
		{
			$role_str .= "<font color=#999999>權</font>";
		}
		if( strstr((string) $this_role,"decide") )
		{
			$role_str .= "<font color=#999999>決</font>";
		}

		return $role_str . ']</small></small>';
	}
	
	
?>
