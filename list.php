<?php
require_once __DIR__ . '/setting.php';
error_reporting(0);
ini_set("default_socket_timeout", 5);

$ptitle = "聯合列表 - ";
include_once __DIR__ . "/header.inc.php";

echo '<div id="indexhi"><div id="indexh2"><b>'.$server_comment.'</b></div>';
echo '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td valign="top"><fieldset>';
echo '<legend><b>聯合遊戲列表</b></legend>';

echo "為了加強安全，房間顯示關閉。<br />";
$out1=[];
foreach($room_server_list as $room_server) {
// $fp = @fsockopen($room_server[0], 80, $err, $err_otp, 1);
// if($fp) {
//  echo "<tr><td><a href='http://$room_server[0]$room_server[1]'>服務中</a><td colspan='4'><a href='http://$room_server[0]$room_server[1]'>http://$room_server[0]$room_server[1]</a></tr>";
//  $fo=@file('http://'.$room_server[0].$room_server[1].'/api.php');
//  if(strlen($fo)>0) $out1=array_merge($out1,$fo);
//  fclose($fp);
// } else {
  echo "<a href='http://$room_server[0]$room_server[1]'>不明</a> <a href='http://$room_server[0]$room_server[1]'>http://$room_server[0]$room_server[1]</a><br />";
// }
}
echo "";

//$counts = count($out1);
//for($i=0;$i <= $counts;$i++) {
//	$out2 = explode("\t",$out1[$i]);
//	if ($out2[0]) {
//		echo "<tr>";
//		if ($out2[3] == 'playing') {
//			$id = explode(" ",$out2[0]);
//			echo "<td width=\"50\"><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 15px;\">遊戲中</font></a></td>".
//				"<td width=\"100\"><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 15px;\">[$out2[0]]</font></a></td>".
//				"<td width=\"250\"><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 15px;\">$out2[1]村</font></a></td>".
//				"<td><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 12px;\">$out2[2]</font></a></td>".
//				"<td width=\"50\"><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 13px;\">人數$out2[4]</font></a></td>";
//		} else {
//			$id = explode(" ",$out2[0]);
//			echo "<td width=\"50\"><b><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 15px;\">募集中</font></a></b></td>".
//				"<td width=\"100\"><b><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 15px;\">[$out2[0]]</font></a></b></td>".
//				"<td width=\"250\"><b><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 15px;\">$out2[1]村</font></a></b></td>".
//				"<td><b><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 12px;\">$out2[2]</font></a></b></td>".
//				"<td width=\"50\"><b><a href=\"$out2[5]login.php?room_no=$id[1]\"><font style=\"font-size : 13px;\">人數$out2[4]</font></a></b></td>";
//		}
//		echo "</tr>";
//	}
//}

echo '</fieldset></td></tr></table></div>';
include_once __DIR__ . "/footer.inc.php";
?>
