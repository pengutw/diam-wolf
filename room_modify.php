<?php
require_once __DIR__ . '/game_functions.php';
//require_once 'msgimg_setting.php';

//Session開始
session_start();
$session_id = session_id();

$go = empty($go) ? "" : $go;
$say = empty($say) ? "" : $say;
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
	
	if($room_status != 'waiting') {
		echo '<html><head><title>村修改</title><link rel="stylesheet" type="text/css" href="img/font.css">
				</head><body bgcolor=aliceblue><br /><br />';
		echo "　　　　遊戲開始後不能更改選項。</body></html>";
		return;
	}
	
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
	
	$gmtrip_str = strstr((string) $game_option,"gm:");
	sscanf($gmtrip_str, "gm:%s", $gmtrip);

	//自分のハンドルネーム、役割、生存を取得
	$res_user = $db->query("select uname,trip from user_entry
							where room_no = '$room_no' and (user_no > 0 or (uname = 'dummy_boy' and user_no = -1)) and uname = '$uname'");
	$user_arr = $db->fetch_array($res_user);
	$uname = $user_arr['uname'];
	$trip = $user_arr['trip'];
	$db->free_result($res_user);
	
	if($trip != $gmtrip && $uname != 'dummy_boy') {
		echo '<html><head><title>錯誤</title><link rel="stylesheet" type="text/css" href="img/font.css">
				</head><body bgcolor=aliceblue>
				<br /><br />';
		echo "　　　　村長或GM以外不能修改村莊。<br />";	
		echo "</body></html>";
		return;
	}
	
$ptitle = "首頁 - ";
HTMLHeaderOutput();

echo '<script language="javascript">
	function DummyBoyAutoLWDet() {
	 if (document.modify_room_form.game_option_dummy_boy1.checked) {
	 	 document.modify_room_form.game_option_dummy_autolw.value = "dummy_autolw";
	 } else {
	 	 document.modify_room_form.game_option_dummy_autolw.value = "";
	 }
	}
	</script>
	';


echo '<form method="POST" action="room_manager.php" name="modify_room_form">
<input type=hidden name=room_no value="'.$room_no.'">
<input name="command" type="hidden" value="MODIFY_ROOM">';

echo '<tr><td valign="top"><fieldset>';
echo '<legend><b>修改村子</b></legend>';
echo '<table>
<tr>
<td width="220">
	<label><strong>　村子名稱：</strong></label>
</td>
<td>
	<input name="room_name" type="text" size="45" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;" value="'.$room_name.'"> 村<br />
</td>
</tr>

<tr>
<td>
	<label><strong>　村子說明：</strong></label>
</td>
<td>
	<input name="room_comment" type="text" size="50" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;" value="'.$room_comment.'">
</td>
</tr>

<tr>
<td>
	<label><strong>　最大人數：</strong></label>
</td>
<td>
	<select name="max_user" style="background-color:aliceblue;">
	<optgroup label=最大人數>
		<option'.($max_user==8?' selected':'').'>8</option>
		<option'.($max_user==16?' selected':'').'>16</option>
		<option'.($max_user==22?' selected':'').'>22</option>
		<option'.($max_user==30?' selected':'').'>30</option>
	</optgroup>
</select>
</td>
</tr>

<tr>
<td>
	<label><strong>　希望角色：</strong></label>
</td>
<td>
	<input name="game_option_wish_role"  value="wish_role" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" '.(strstr((string) $game_option, 'wish_role')?'checked':'').'>
	<small>(打勾後將可選擇希望角色)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　限時時間：</strong></label>
</td>
<td>';


		//實際時間的制限時間を取得
		$real_time_str = strstr((string) $game_option,"real_time");
		sscanf($real_time_str,"real_time:%d:%d",$day_real_limit_time_min,$night_real_limit_time_min);

echo '	<input name="game_option_real_time" value="real_time" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" checked>
	<small>(限制投票實際時間　日：<input name="game_option_real_time_day" value="'.$day_real_limit_time_min.'" type="text" size=2 maxlength=2 style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">分
	夜：<input name="game_option_real_time_night" value="'.$night_real_limit_time_min.'" type="text" size=2 maxlength=2 style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">分)</small>
	';
	

echo '	
</td>
</tr>

<tr>
<td>
	<label><strong>　第一天晚上的替身君：</strong></label>
</td>
<td>
	<input name="game_option_dummy_boy" value="dummy_boy" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" '.(strstr((string) $game_option, 'dummy_boy')?'checked':'').' onclick="DummyBoyAutoLWDet();">
	<small>(第一天晚上狼只能咬替身君)</small>
</td>
</tr>
';

echo '
<tr>
<td>
	<label><strong>　替身君自訂：</strong></label>
</td>
<td>
	<input name="game_option_cust_dummy" value="cust_dummy" type="checkbox" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $game_option, 'cust_dummy')?'checked':'').'>
	<small>(若勾選可自訂替身君名稱及遺言，未勾選則系統隨機)</small>
</td>

</tr>


<tr>
<td>

	
</td>
<td>
	';

if(strstr((string) $game_option, 'dummy_boy') && strstr((string) $game_option, 'cust_dummy')) {
	$res_dummy_data = $db->query("select handle_name, last_words from user_entry where room_no = '$room_no' and user_no = '1'");
	$dummy_data = $db->fetch_array($res_dummy_data);

	$dummy_hn = $dummy_data['handle_name'];	
	$dummy_lw = $dummy_data['last_words'];
} else {
	$dummy_hn = '伊藤誠';
	$dummy_lw = '';
}
		echo '<table><tr style="background-color:#eeeeff;">';
		echo '<td align=left valign=middle style="color:black;border-top: silver 1px dashed;">';
		echo '<input name="dummy_name" type="text" size="10" value="'.$dummy_hn.'" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;"> <small>的遺言</small>';
		echo '<td valign=middle style="border-top: silver 1px dashed;">';
		echo '<table><td style="color:black;"><textarea name="dummy_lw" type="text" cols="30" rows="4" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;">'.$dummy_lw.'</textarea></span></td></table>';
		echo '</td></tr></table>';
	
echo'
	
</td>

</tr>

<tr>
<td><label><strong>　替身君有職顯示方式：</strong></label></td>
<td>
	<select name="game_option_dummy_autolw">
	<option value="" '.(strstr((string) $game_option, 'dummy_autolw')?'':'selected').'>無職業遺書</option>
	<option value="dummy_autolw" '.(strstr((string) $game_option, 'dummy_autolw')?'selected':'').'>有職業遺書</option>
	<option value="dummy_isred" '.(strstr((string) $game_option, 'dummy_isred')?'selected':'').'>純紅字遺書</option>
	</select>
	<small>(選擇顯示職業遺書方式)</small>
</td>
	
</tr>
	
<tr>
<td>
	<label><strong>　公開投票結果票數：</strong></label>
</td>
<td>
	<input name="game_option_open_vote" value="open_vote" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;"  '.(strstr((string) $game_option, 'open_vote')?'checked':'').'>
	<small>(權力者將會在投票時被發現)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　幽靈是否可以看角色：</strong></label>
</td>
<td>
	<input name="dellook" value="1" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.($game_dellook == 1?'checked':'').'>
	<small>(允許幽靈觀看角色)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　共生者夜晚對話顯示：</strong></label>
</td>
<td>
	<input name="game_option_comm_out" value="comoutl" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $game_option, 'comoutl')?'checked':'').'>
	<small>(允許晚上顯示共生者悄悄話)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用村莊遺書顯示：</strong></label>
</td>
<td>
	<input name="game_option_will" value="will" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $game_option, 'will')?'checked':'').'>
	<small>(允許保留遺書)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用白天自投功能：</strong></label>
</td>
<td>
	<input name="game_option_vote_me" value="votedme" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $game_option, 'votedme')?'checked':'').'>
	<small>(第二次投票給自己一樣會暴斃)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用強制Trip登記：</strong></label>
</td>';

$trip_count_str = strstr((string) $game_option,"istrip");
sscanf($trip_count_str,"istrip:%d",$tripinroom);

echo '
<td>
	<input name="game_option_trip" value="istrip" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $game_option, 'istrip')?'checked':'').'>
	<small>Trip最低數量:</small> 
	<input name="game_option_trip_countsum" type="text" size="3" value="'.$tripinroom.'" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"> 
	<small>(如果沒有設定Trip將無法註冊成為村民)</small>
</td>
</tr>

<!--<tr>
<td>
	<label><strong>　啟用白天投票顯示：</strong></label>
</td>
<td>
	<input name="game_option_votedisplay" value="votedisplay" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<small>(<font color=#d00000>測試期專用選項</font>，已投過票的人將會以特殊色顯示背景)</small>
</td>
</tr>-->';

echo '<tr>
<td>
	<label><strong>　啟用村長Trip：</strong></label>
</td>
<td>
	<input name="game_option_manager_trip" type="text" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;" value="'.$gmtrip.'">
	<br /><input name="game_option_manager_trip_enc" value="enctrip" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" checked><small> ※要使用加密後Trip請勾我
	<br />(請輸入<font color="red">加密前</font>原始Trip，對應之Trip將成為村長，在開始前有直接踢人與點名的權力)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用GM系統：</strong></label>
</td>
<td>
	<input name="game_option_gm" value="as_gm" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $game_option, 'as_gm')?'checked':'').'>
	<small>(村長在遊戲開始後轉為遊戲主持人，可以自訂規則主持遊戲)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用全匿名：</strong></label>
</td>
<td>
	<input name="game_option_guest" value="usr_guest" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $game_option, 'usr_guest')?'checked':'').'>
	<small>(遊戲開始後將名稱全匿名並重新排列投票清單)</small>
</td>
</tr>

<tr><td>&nbsp;';
if (strstr((string) $game_option, 'ischat')) {
	echo '<input name="game_option_chats" value="ischat" type="hidden">';
}
echo '</td></tr>
<tr><td colspan="2"><strong><font color="#0000d0">職業追加與調整：</font></strong></td></tr>

';


$have_spy = strstr((string) $option_role, 'spy')?1:0;

echo '	
	<tr>
	<td>
	<label><strong>　10人以上狂人的選項：</strong></label>
	</td>
	<td>
	<select name="option_role_spy">
	<option value="" '.($have_spy==0?'selected':'').'>狂人</option>
	<option value="spy" '.($have_spy==1?'selected':'').'>間諜</option>
	</select>
	<small>(選擇狂人類型)</small>
	</td>
	</tr>
';

$have_rlv = strstr((string) $option_role, 'r_lovers')?1:0;
$have_slv = strstr((string) $option_role, 's_lovers')?1:0;
$have_nof = strstr((string) $option_role, 'noflash')?1:0;
	
echo '	
	<tr>
	<td>
	<label><strong>　13人以上雙人的選項：</strong></label>
	</td>
	<td>
	<select name="option_role_lovers">
	<option value="" '.(($have_rlv+$have_slv+$have_nof)==0?'selected':'').'>共有者</option>
	<option value="r_lovers" '.($have_rlv==1?'selected':'').'>隨機戀</option>
	<option value="s_lovers" '.($have_slv==1?'selected':'').'>村村戀</option>
	<option value="noflash" '.($have_nof==1?'selected':'').'>無</option>';
	
	echo'</select>
	<small>(選擇共有者或戀人類型)</small>
	</td>
	</tr>

<tr>
<td>
<label><strong>　13人以上貴族與奴隸出場：</strong></label>
</td>
<td>
<input name="option_role_noble" value="noble" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $option_role, 'noble')?'checked':'').'>

<small>(沒水喝就喝牛奶的貴族，以及為貴族赴死，恨之入骨的奴隸)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　16人以上決定者出場：</strong></label>
</td>
<td>
	<input name="option_role_decide" value="decide" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;"  '.(strstr((string) $option_role, 'decide')?'checked':'').'>
	<small>(票數相同時決定者票為優先，可兼任)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　16人以上權力者出場：</strong></label>
</td>
<td>
	<input name="option_role_authority" value="authority" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;"  '.(strstr((string) $option_role, 'authority')?'checked':'').'>
	<small>(投票的票數為2票，可兼任)</small>
</td>
</tr>

<tr>
<td>
<label><strong>　16人以上說謊狂出場：</strong></label>
</td>
<td>
<input name="option_role_mytho" value="mytho" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $option_role, 'mytho')?'checked':'').'>	<small>(妄想成為人狼或占卜師的村人)</small>
</td>
</tr>
	
	
<tr>
<td>
	<label><strong>　20人以上時大狼出場：</strong></label>
</td>
<td>
	<input name="option_wfbig_poison" value="wfbig" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $option_role, 'wfbig')?'checked':'').'>
	<small>(狼群隨機一隻取代為大狼)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　20人以上無毒時追加人狼：</strong></label>
</td>
<td>
	<input name="option_more_wolf" value="morewolf" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $option_role, 'morewolf')?'checked':'').'>
	<small>(無埋毒者系職業時狼數多一)</small>
</td>
</tr>
	
	
<tr>
<td>
<label><strong>　20人以上共有＋隨機戀：</strong></label>
</td>
<td>
	<input name="option_role_comlover" value="comlover" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $option_role, 'comlover')?'checked':'').'>	<small>(<font color="#d00000">覆蓋13人時的雙人選項，以共有者+隨機戀人取代之</font>)</small>
</td>
</tr>

<tr>
<td>
<label><strong>　20人以上夜梟出場：</strong></label>
</td>
<td>
	<input name="option_role_owlman" value="owlman" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $option_role, 'owlman')?'checked':'').'>	
	<small>(帶來不幸的村人)</small>
</td>
</tr>
	
<tr>
<td>
<label><strong>　20人以上小企鵝出場：</strong></label>
</td>
<td>
<input name="option_role_pengu" value="pengu" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" '.(strstr((string) $option_role, 'pengu')?'checked':'').'>	
<small>(幫助動物的可愛企鵝)</small>
</td>
</tr>';
	
$have_cat = strstr((string) $option_role, 'cat')?1:0;
$have_poi = strstr((string) $option_role, 'poison')?1:0;

echo '<tr>
<td>
	<label><strong>　20人以上埋毒者選項：</strong></label>
</td>
<td>
	<select name="option_role_poison">
	<option value="" '.(($have_cat+$have_poi)==0?'selected':'').'>無</option>
	<option value="poison" '.($have_poi == 1?'selected':"").'>埋毒</option>
	<option value="cat" '.($have_cat == 1?'selected':"").'>貓又</option>
	</select>
	<small>(選擇埋毒者類型)</small>
</td>
</tr>
';
	
$have_fox = strstr((string) $option_role, 'foxs')?1:0;
$have_btr = strstr((string) $option_role, 'betr')?1:0;
$have_fsi = strstr((string) $option_role, 'fosi')?1:0;

echo '
<tr>
<td>
	<label><strong>　20人以上妖狐的選項：</strong></label>
</td>
<td>
<select name="option_role_foxs">
	<option value="" '.(($have_btr+$have_fox+$have_fsi) == 0?'checked':"").'>無</option>
	<option value="betr" '.($have_btr == 1?'selected':"").'>背德</option>
	<option value="foxs" '.($have_fox == 1?'selected':"").'>雙狐</option>
	<option value="fosi" '.($have_fsi == 1?'selected':"").'>子狐</option>';
	
echo'</select>
	<small>(選擇妖狐側類型)</small>
<!--<small>(使用將會取消埋毒)　　※強制加入埋毒</small> <input name="option_role_pobe" value="pobe" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
--></td>
</tr>

<tr>
<td></td>
<td align="right">';

	echo '<input type=submit value=" 修改 " style="border-width:1px;border-color:black;border-style:solid;">';

echo '</td>
</tr>

</table>';

}
else
{
	echo '<html><head><title>Session認證錯誤</title><link rel="stylesheet" type="text/css" href="img/font.css">
			</head><body bgcolor=aliceblue>
			<br /><br />';
	echo "　　　　Session認證錯誤。<br />";	
	echo "</body></html>";
	
}

//MySQLとの接続を閉じる
$db->close();
?>
