<?php
require_once __DIR__ . '/setting.php';
require_once __DIR__ . '/functions.php';

//留言板防止BOT用
setcookie("tobbsokpost","tobbsokpost");

$ptitle = "首頁 - ";
include_once __DIR__ . "/header.inc.php";
echo '<div id="indexhi"><div id="indexh2"><b>'.$server_comment.'</b></div>';
echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
echo '<tr><td valign="top"><fieldset>';
echo '<legend><b>首頁公告</b></legend>';

if ($back_page != '') {
	echo "<a href=\"$back_page\">←返回</a>";
}

echo $server_comment;

echo '<noscript>
<span style="font-size:15pt;color:red;font-weight:bold">＜＜ 請啟用JavaScript ＞＞</span>
</noscript>';

if ($announcement_file = file($announcement)) {
	foreach ($announcement_file as $announcement_line) {
		echo $announcement_line."<br />";
	}
}

echo '</fieldset><br /></td></tr>';


$result = $db->query("select count(*) from room where room_no > 1 and (status='waiting' OR status='playing')");
$room_count = $db->result($result,0); //取得募集中或是遊玩中的房間數

global $sys_create_room_max;



echo '<tr><td valign="top"><fieldset>';
echo '<legend><b>村莊列表 （村莊數：'.$room_count.' / '.$sys_create_room_max.'+1）</b></legend><b>';
include __DIR__ . '/room_manager.php';
echo '</b></fieldset><br /></td></tr>';

echo '<form method="POST" action="room_manager.php" name="create_room_form">
<input name="command" type="hidden" value="CREATE_ROOM">';

echo '<tr><td valign="top"><fieldset>';
echo '<legend><b>建立村子</b></legend>';

echo '<table>
<tr>
<td width="220">
	<label><strong>　村子名稱：</strong></label>
</td>
<td>
	<input name="room_name" type="text" size="45" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;"> 村<br />
</td>
</tr>

<tr>
<td>
	<label><strong>　村子說明：</strong></label>
</td>
<td>
	<input name="room_comment" type="text" size="50" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;">
</td>
</tr>

<tr>
<td>
	<label><strong>　最大人數：</strong></label>
</td>
<td>
	<select name="max_user" style="background-color:aliceblue;">
	<optgroup label=最大人數>
		<option>8</option>
		<option>16</option>
		<option selected>22</option>
		<option>30</option>
	</optgroup>
</select>
</td>
</tr>

<tr>
<td>
	<label><strong>　希望角色：</strong></label>
</td>
<td>
	<input name="game_option_wish_role" value="wish_role" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;">
	<small>(打勾後將可選擇希望角色)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　限時時間：</strong></label>
</td>
<td>';



echo '	<input name="game_option_real_time" value="real_time" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" checked>
	<small>(限制投票實際時間　日：<input name="game_option_real_time_day" value="'.$day_real_limit_time_min.'" type="text" size=2 maxlength=2 style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">分
	夜：<input name="game_option_real_time_night" value="'.$night_real_limit_time_min.'" type="text" size=2 maxlength=2 style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">分)</small>
	';
	
	/*
echo '	<input name="game_option_real_time" value="real_time" type="hidden" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;">
	<small>日：<input name="game_option_real_time_day" value="'.$day_real_limit_time_min.'" type="text" size=2 maxlength=2 style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;">分
	夜：<input name="game_option_real_time_night" value="'.$night_real_limit_time_min.'" type="text" size=2 maxlength=2 style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;">分</small>
	';*/
echo '	
</td>
</tr>

<tr>
<td>
	<label><strong>　第一天晚上的替身君：</strong></label>
</td>
<td>
	<input name="game_option_dummy_boy" value="dummy_boy" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" onclick="DummyBoyAutoLWDet();">
	<small>(第一天晚上狼只能咬替身君)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　替身君自訂：</strong></label>
</td>
<td>
	<input name="game_option_cust_dummy" value="cust_dummy" type="checkbox" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<small>(若勾選可自訂替身君名稱及遺言，未勾選則系統隨機)</small>
</td>

</tr>


<tr>
<td>

	
</td>
<td>
	';

		echo '<table><tr style="background-color:#eeeeff;">';
		echo '<td align=left valign=middle style="color:black;border-top: silver 1px dashed;">';
		echo '<input name="dummy_name" type="text" size="10" value="伊藤誠" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;"> <small>的遺言</small>';
		echo '<td valign=middle style="border-top: silver 1px dashed;">';
		echo '<table><td style="color:black;"><textarea name="dummy_lw" type="text" cols="30" rows="4" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;"></textarea></span></td></table>';
		echo '</td></tr></table>';
	
echo'
	
</td>

</tr>

<tr>
<td><label><strong>　替身君有職顯示方式：</strong></label></td>
<td>
	<select name="game_option_dummy_autolw">
	<option value="" selected>無職業遺書</option>
	<option value="dummy_autolw">有職業遺書</option>
	<option value="dummy_isred">純紅字遺書</option>
	</select>
	<small>(選擇顯示職業遺書方式)</small>
</td>
	
</tr>
	
<tr>
<td>
	<label><strong>　公開投票結果票數：</strong></label>
</td>
<td>
	<input name="game_option_open_vote" value="open_vote" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" checked>
	<small>(權力者將會在投票時被發現)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　幽靈是否可以看角色：</strong></label>
</td>
<td>
	<input name="dellook" value="1" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" checked>
	<small>(允許幽靈觀看角色)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　共生者夜晚對話顯示：</strong></label>
</td>
<td>
	<input name="game_option_comm_out" value="comoutl" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" checked>
	<small>(允許晚上顯示共生者悄悄話)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用村莊遺書顯示：</strong></label>
</td>
<td>
	<input name="game_option_will" value="will" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" checked>
	<small>(允許保留遺書)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用白天自投功能：</strong></label>
</td>
<td>
	<input name="game_option_vote_me" value="votedme" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" checked>
	<small>(第二次投票給自己一樣會暴斃)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用強制Trip登記：</strong></label>
</td>
<td>
	<input name="game_option_trip" value="istrip" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<small>Trip最低數量:</small> 
	<input name="game_option_trip_countsum" type="text" size="3" value="0" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"> 
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
</tr>-->

<tr>
<td>
	<label><strong>　啟用村長Trip：</strong></label>
</td>
<td>
	<input name="game_option_manager_trip" type="text" style="border-width:1px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<br /><input name="game_option_manager_trip_enc" value="enctrip" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;"><small> ※要使用加密後Trip請勾我
	<br />(請輸入<font color="red">加密前</font>原始Trip，對應之Trip將成為村長，在開始前有直接踢人與點名的權力)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用GM系統：</strong></label>
</td>
<td>
	<input name="game_option_gm" value="as_gm" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<small>(村長在遊戲開始後轉為遊戲主持人，可以自訂規則主持遊戲)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　啟用全匿名：</strong></label>
</td>
<td>
	<input name="game_option_guest" value="usr_guest" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<small>(遊戲開始後將名稱全匿名並重新排列投票清單) 尚未開放</small>
</td>
</tr>
<tr>
<td>
	<label><strong>　啟用限於聊天：</strong></label>
</td>
<td>
	<input name="game_option_chats" value="ischat" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<small>(指定為聊天村但不能遊戲開始，上限自動為50人)</small>
</td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan="2"><strong><font color="#0000d0">職業追加與調整：</font></strong></td></tr>

	
	<tr>
	<td>
	<label><strong>　10人以上狂人的選項：</strong></label>
	</td>
	<td>
	<select name="option_role_spy">
	<option value="" selected>狂人</option>
	<option value="spy">間諜</option>
	</select>
	<small>(選擇狂人類型)</small>
	</td>
	</tr>
	
	
	<tr>
	<td>
	<label><strong>　13人以上雙人的選項：</strong></label>
	</td>
	<td>
	<select name="option_role_lovers">
	<option value="" selected>共有者</option>
	<option value="r_lovers">隨機戀</option>
	<option value="s_lovers">村村戀</option>
	<option value="noflash">無</option>';
	
	echo'</select>
	<small>(選擇共有者或戀人類型)</small>
	</td>
	</tr>

<tr>
<td>
<label><strong>　13人以上貴族與奴隸出場：</strong></label>
</td>
<td>
<input name="option_role_noble" value="noble" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">

<small>(沒水喝就喝牛奶的貴族，以及為貴族赴死，恨之入骨的奴隸)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　16人以上決定者出場：</strong></label>
</td>
<td>
	<input name="option_role_decide" value="decide" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;" checked>
	<small>(票數相同時決定者票為優先，可兼任)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　16人以上權力者出場：</strong></label>
</td>
<td>
	<input name="option_role_authority" value="authority" type="checkbox" style="border-width:0px;border-style:solid;border-color:black;background-color:aliceblue;" checked>
	<small>(投票的票數為2票，可兼任)</small>
</td>
</tr>

<tr>
<td>
<label><strong>　16人以上說謊狂出場：</strong></label>
</td>
<td>
<input name="option_role_mytho" value="mytho" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">	<small>(妄想成為人狼或占卜師的村人)</small>
</td>
</tr>
	
	
<tr>
<td>
	<label><strong>　20人以上時大狼出場：</strong></label>
</td>
<td>
	<input name="option_wfbig_poison" value="wfbig" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<small>(狼群隨機一隻取代為大狼)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　20人以上無毒時追加人狼：</strong></label>
</td>
<td>
	<input name="option_more_wolf" value="morewolf" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
	<small>(無埋毒者系職業時狼數多一)</small>
</td>
</tr>
	
	
<tr>
<td>
<label><strong>　20人以上共有＋隨機戀：</strong></label>
</td>
<td>
	<input name="option_role_comlover" value="comlover" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">	<small>(<font color="#d00000">覆蓋13人時的雙人選項，以共有者+隨機戀人取代之</font>)</small>
</td>
</tr>

<tr>
<td>
<label><strong>　20人以上夜梟出場：</strong></label>
</td>
<td>
	<input name="option_role_owlman" value="owlman" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">	
	<small>(帶來不幸的村人)</small>
</td>
</tr>
	
<tr>
<td>
<label><strong>　20人以上小企鵝出場：</strong></label>
</td>
<td>
<input name="option_role_pengu" value="pengu" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">	
<small>(幫助動物的可愛企鵝)</small>
</td>
</tr>
	
	
<tr>
<td>
	<label><strong>　20人以上埋毒者選項：</strong></label>
</td>
<td>
	<select name="option_role_poison">
	<option value="">無</option>
	<option value="poison" selected>埋毒</option>
	<option value="cat">貓又</option>
	</select>
	<small>(選擇埋毒者類型)</small>
</td>
</tr>

<tr>
<td>
	<label><strong>　20人以上妖狐的選項：</strong></label>
</td>
<td>
<select name="option_role_foxs">
	<option value="" selected>無</option>
	<option value="betr">背德</option>
	<option value="foxs">雙狐</option>
	<option value="fosi">子狐</option>';
	
echo'</select>
	<small>(選擇妖狐側類型)</small>
<!--<small>(使用將會取消埋毒)　　※強制加入埋毒</small> <input name="option_role_pobe" value="pobe" type="checkbox" style="border-width:0px;border-style:solid;border-color:silver;background-color:aliceblue;">
--></td>
</tr>

<tr>
<td></td>
<td align="right">';

if (!$sys_create_room_enabled) { 
	echo $lang_create_room_disable;
} else {
	echo '<input type="hidden" value="" name="recaptcha_response" id="recaptchaResponse"><input type=submit value=" 建立 " style="border-width:1px;border-color:black;border-style:solid;">';
}

//echo '新村莊的建立似乎因為神奇的力量而無法進行';

echo '</td>
</tr>

</table>';

echo '</fieldset><br /></td></tr>';

echo '</table></form>';
include_once __DIR__ . "/footer.inc.php";
?>
