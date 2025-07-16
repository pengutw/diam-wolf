<?php
if(!defined('IN_JINRO')) exit('Access Denied');

echo '
		<table border="0" cellpadding="0" cellspacing="5">
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="/" onclick="showUser(\'/\'); return false;">首頁</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="list.php" onclick="showUser(\'list.php\'); return false;"><b>聯合列表</b></a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="script_info.php">'.$lang_menu_script_info.'</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="rule.php">'.$lang_menu_rule.'</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="old_log.php">'.$lang_menu_old_log.'</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="stats.php">勝率分析</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="emot.php">表情符號</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="http://nedftp.com/wiki/index.php"><font color="red">人狼維基(必看)</font></a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="bbs.php" onclick="showUser(\'bbs.php\'); return false;">人狼討論</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="trip.php">身份登錄</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="trip_vote.php">凍結系統</a></td>
			</tr>
			<tr>
			<td valign="top"><small><font color="#666666">・</font></small></td>
			<td><a href="version.php">'.$lang_menu_version.'</a></td>
			</tr>
		</table>
';
?>