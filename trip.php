<?php
require_once 'game_functions.php';
$Snum = 15;
$isold = '';

$name = trim((string) $_POST['name']);
$trip = tripping($name);

function nohtml($src): string {
	return str_replace("<", "&lt;", (string) $src);
}

function bheader($src): never {
	header($src);
	exit;
}

function footer($e = ''): void {
	GLOBAL $admin_name,$admin_link;
	if ($e) {
		echo $e;
	}
	echo '</fieldset></div>';
	include_once "footer.inc.php";
	if ($e) {
		exit;
	}
}

$ptitle = "身分登錄 - ";
include_once "header.inc.php";

echo '<div id="indexhi"><div id="indexh2">';

if (empty($_GET['go'])) {
	echo '<a href="trip.php?go=post">身份登錄</a>';
//	echo ' <a href="trip.php?go=edit2">修改紀錄</a>';
	echo ' <a href="trip.php?go=editpass">修改密碼</a>';
//	echo ' <a href="trip.php?go=accadd">認領紀錄</a>';
	echo ' <a href="trip.php?go=edit">修改Trip</a>';
//	echo ' <a href="trip.php?go=out">排除紀錄</a>';
	echo ' <a href="trip.php?go=icon">上傳頭像</a>';
}

echo '</div><fieldset><legend><b>';

if ($_GET['go'] == 'post') {
	echo '登錄選項';
} elseif ($_GET['go'] == 'room') {
	echo '詳細列表';
} elseif ($_GET['go'] == 'trip') {
	echo '帳號列表';
} elseif ($_GET['go'] == 'search') {
	echo '搜尋結果';
} elseif ($_GET['go'] == 'accadd') {
	exit;
} elseif ($_GET['go'] == 'edit2') {
	exit;
} elseif ($_GET['go'] == 'edit') {
	echo '修改Trip';
} elseif ($_GET['go'] == 'out') {
	exit;
} elseif ($_GET['go'] == 'sce') {
	echo '評分';
} elseif ($_GET['go'] == 'smess') {
	echo '評語';
} elseif ($_GET['go'] == 'icon') {
	echo '上傳頭像';
} elseif ($_GET['go'] == 'editpass') {
	echo '修改密碼';
} elseif ($_GET['go'] == 'smess2') {
	echo '負評列表';
} else {
	echo '登錄列表';
}

echo '</b></legend>';

if ($_GET['go'] == 'post' || $_GET['go'] == 'edit' || $_GET['go'] == 'accadd') {
	if ($_POST['submit']) {
		if (empty($name) || empty($_POST['password'])) {
			footer('缺少資料或是有遺漏');
		}
	}
}

if ($_GET['go'] == 'post') {
	if ($_POST['submit']) {
		if (!(preg_match("/[a-zA-Z]/",$name) && preg_match("/[0-9]/",$name))) {
            footer('Trip必須包含英文和數字');
        }
		if (empty($_POST['email'])) {
			footer('請輸入E-MAIL');
		}
		$query = $db->query("select count(*) from user_trip where trip = '$trip';");
		if ($db->result($query, 0)) {
			footer('已有此Trip');
		}
		$password = md5((string) $_POST['password']);
		$aa =	"trip,password,email";
		$bb =	"'$trip','$password','".$_POST['email']."'";

		//新增資料
		$db->query("insert into user_trip ($aa) values ($bb)");
		bheader("Location:trip.php");
	}
	echo '<form name="trip" action="trip.php?go=post" method="post" enctype="multipart/form-data">
		<li>TRIP <input type="text" name="name" size="24" value="" /></li>
		<li>E-MAIL <input type="text" name="email" size="24" value="" /></li>
		<li>密碼 <input type="password" name="password" size="24" value="" /></li>
		<li>Trip請輸入喜好之驗證碼(自訂)，請勿過於簡單遭到破解。</li>
		<li>本伺服器E-MAIL隱私保證，僅供密碼重設使用。</li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'edit') {
	if ($_POST['submit']) {
		$nname = trim((string) $_POST['nname']);
		$ntrip = tripping($nname);
		if (!(preg_match("/[a-zA-Z]/",$nname) && preg_match("/[0-9]/",$nname))) {
            footer('Trip必須包含英文和數字');
        }
		
		$password = md5((string) $_POST['password']);
		
		$query = $db->query("select count(*) from user_trip where trip = '$trip' and password = '$password' and ban = '0';");
		if (!$db->result($query, 0)) {
			footer('找不到Trip或密碼錯誤，也有可能Trip已經被凍結。');
		}
		
		$query = $db->query("select count(*) from user_trip where trip = '$ntrip';");
		if ($db->result($query, 0)) {
			footer('已有此Trip');
		}
		$query = $db->query("UPDATE trip_score SET user = '$ntrip' where user = '$trip';");
		$query = $db->query("UPDATE trip_score SET trip = '$ntrip' where trip = '$trip';");
		$query = $db->query("UPDATE user_trip SET trip = '$ntrip' where trip = '$trip';");
		$query = $db->query("UPDATE user_trip SET trip = '$ntrip' where trip = '$trip';");
		$query = $db->query("UPDATE user_entry SET trip = '$ntrip' where trip = '$trip';");
		$query = $db->query("UPDATE bbs SET trip = '$ntrip' where trip = '$trip';");
		bheader("Location:trip.php");
	}
	echo '<form name="trip" action="trip.php?go=edit" method="post" enctype="multipart/form-data">
		<li>舊的TRIP <input type="text" name="name" size="24" value="" /></li>
		<li>管理密碼 <input type="password" name="password" size="24" value="" /></li>
		<li>新的TRIP <input type="text" name="nname" size="24" value="" /></li>
		<li>Trip請輸入喜好之驗證碼(自訂)，請勿過於簡單遭到破解。</li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'edit2') {
	if ($_POST['submit']) {
		if (empty($_POST['lname'])) {
			footer('缺少資料或是有遺漏');
		}
		$lname = trim((string) $_POST['lname']);
		$ltrip = tripping($lname);

		$password = md5((string) $_POST['password']);
		$lpassword = md5((string) $_POST['lpassword']);
		$query = $db->query("select count(*) from user_trip where trip = '$trip' and password = '$password' and ban = '0';");
		if (!$db->result($query, 0)) {
			footer('找不到Trip或密碼錯誤，也有可能Trip已經被凍結。');
		}
		$query = $db->query("select count(*) from user_trip where trip = '$ltrip' and password = '$lpassword' and ban = '0';");
		if (!$db->result($query, 0)) {
			footer('找不到Trip或密碼錯誤，也有可能Trip已經被凍結。');
		}
		$query = $db->query("UPDATE user_entry SET trip = '$trip' where trip = '$ltrip';");
		$query = $db->query("UPDATE trip_score SET user = '$trip' where user = '$ltrip';");
		$query = $db->query("UPDATE trip_score SET trip = '$trip' where trip = '$ltrip';");
		
		bheader("Location:trip.php");
	}
	echo '<form name="trip" action="trip.php?go=edit2" method="post" enctype="multipart/form-data">
		<li>請輸入加密前的Trip與舊Trip。</li>
		<li>過去紀錄相同的Trip將會取代。</li>
		<li>Trip <input type="text" name="name" size="24" value="" /></li>
		<li>密碼 <input type="password" name="password" size="24" value="" /></li>
		<li>舊Trip <input type="text" name="lname" size="24" value="" /></li>
		<li>舊Trip密碼 <input type="password" name="lpassword" size="24" value="" /></li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'editpass') {
	if ($_POST['submit']) {
		if (!(preg_match("/[a-zA-Z]/",$name) && preg_match("/[0-9]/",$name))) {
            footer('Trip必須包含英文和數字');
        }
		if (empty($_POST['email'])) {
			footer('請輸入E-MAIL');
		}
		
		$npassword = md5((string) $_POST['npassword']);
		$email = $_POST['email'];
		$query = $db->query("select count(*) from user_trip where trip = '$trip' and email = '$email' and ban = '0';");
		if (!$db->result($query, 0)) {
			footer('找不到Trip符合的，也有可能Trip已經被凍結。');
		}

		$query = $db->query("UPDATE user_trip SET password = '$npassword' where trip = '$trip' and email = '$email' and ban = '0';");
		bheader("Location:trip.php");
	}
	echo '<form name="trip" action="trip.php?go=editpass" method="post" enctype="multipart/form-data">
		<li>請輸入登記之Trip，不是加密後的Trip。</li>
		<li>Trip <input type="text" name="name" size="24" value="" /></li>
		<li>E-MAIL <input type="text" name="email" size="24" value="" /></li>
		<li>新密碼 <input type="password" name="npassword" size="24" value="" /></li>
		<li>Trip密碼不要再忘了。</li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'accadd') {
	if ($_POST['submit']) {
		if (empty($_POST['aname']) || empty($_POST['apassword'])) {
			footer('缺少資料或是有遺漏');
		}
		$password = md5((string) $_POST['password']);
		$query = $db->query("select count(*) from user_trip where trip = '$trip' and password = '$password' and ban = '0';");
		if (!$db->result($query, 0)) {
			footer('找不到Trip或密碼錯誤，也有可能Trip已經被凍結。');
		}
		
		$lname = trim((string) $_POST['aname']);
		$ltrip = tripping($lname);
		
		$query = $db->query("UPDATE user_entry SET trip = '$trip' where trip = '$ltrip' and password = '".$_POST['apassword']."';");
		
		bheader("Location:trip.php");
	}
	echo '<form name="trip" action="trip.php?go=accadd" method="post" enctype="multipart/form-data">
		<li>請輸入登記之Trip，不是加密後的Trip。</li>
		<li>認領TRIP請輸入加密後Trip。</li>
		<li>認領密碼請輸入過去遊戲所輸入的帳號密碼，不是Trip的密碼。</li>
		<li>條件皆符合者將自動取代新Trip。</li>
		<li>TRIP <input type="text" name="name" size="24" value="" /></li>
		<li>密碼 <input type="password" name="password" size="24" value="" /></li>
		<li>認領TRIP <input type="text" name="aname" size="24" value="" /></li>
		<li>認領密碼 <input type="password" name="apassword" size="24" value="" /></li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'out') {
	if ($_POST['submit']) {
		if (empty($_POST['aname'])) {
			footer('缺少資料或是有遺漏');
		}
		$password = md5((string) $_POST['password']);
		$query = $db->query("select count(*) from user_trip where trip = '$trip' and password = '$password' and ban = '0';");
		if (!$db->result($query, 0)) {
			footer('找不到Trip或密碼錯誤，也有可能Trip已經被凍結。');
		}
		$query = $db->query("UPDATE user_entry SET trip = ''
							 where handle_name LIKE '".$_POST['aname']."%' and trip = '$trip';");
		
		bheader("Location:trip.php");
	}
	echo '<form name="trip" action="trip.php?go=out" method="post" enctype="multipart/form-data">
		<li>請輸入登記之Trip，不是加密後的Trip。</li>
		<li>排除紀錄請輸入過去紀錄之玩家暱稱，將會排除符合的紀錄。</li>
		<li>TRIP <input type="text" name="name" size="24" value="" /></li>
		<li>密碼 <input type="password" name="password" size="24" value="" /></li>
		<li>暱稱 <input type="text" name="aname" size="24" value="" /></li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'trip' && !empty($_GET['id'])) {
	$id = $_GET['id'];
	$query = $db->query("select * from user_trip where trip = '$id';",'UNBUFFERED');
	$trip = $db->fetch_array($query);
	if ($trip['trip'] == '') {
		footer('此Trip尚未登記');
	}
	
	if ($id == "DETfcflZU6") {
		$query2 = $db->query("select room_no from room where room_no > 1 AND status='playing' AND game_option NOT LIKE '%ischat%'");
		if ($db->num_rows($query2) > 0) {
			footer("有村莊正在進行或等待遊戲，暫停存取紀錄。");
		}
	}
	
	$query = $db->query("select handle_name,room_no from user_entry where trip = '$id' and user_no > '0' and role != 'none' and gover = '1' group by handle_name ORDER BY room_no DESC LIMIT 500;");
	if ($db->num_rows($query) == 0) {
		footer('此Trip已登記但無遊戲資料<br />遊戲尚未開始不列入計算');
	}

	$scoq = $db->query("select score from trip_score where trip = '$id';");
	$scoqa = 0;
	$scoqb = 0;
	$scoqc = 0;
	while ($list = $db->fetch_array($scoq)) {
		if ($list['score'] == '1') {
			$scoqa++;
		}
		if ($list['score'] == '2') {
			$scoqb++;
		}
		if ($list['score'] == '3') {
			$scoqc++;
		}
	}

	$query2 = $db->query("select count(room_no) from user_entry where trip = '$id' and user_no > '0' and role != 'none' and gover = '1';");
	$num = $db->result($query2,0);
	echo "<center>該Trip使用 $num 次，已知使用暱稱如下(排除重複)";
	if ($trip['ban'] > 0) {
		echo "<br /><font color=\"Red\">此Trip已經凍結，<a href=\"trip_vote.php?go=view&id=".$trip['ban']."\">請參照紀錄</a>。</font><br />";
	}
	if ($trip['ban'] < 0) {
		echo "<br /><font color=\"Red\">此Trip正在凍結投票中</font><br />";
	}
	if ($trip['icon'] && $trip['size']) {
		$trip['size']  = explode(":",(string) $trip['size']);
		if ($trip['size'][2] == '') {
			$trip['size'][2] = "webp";
		}
		echo "<br /><img src=\"trip_icon/icon_".$trip['id'].".".$trip['size'][2]."\" width=\"".$trip['size'][0]."\" height=\"".$trip['size'][1]."\" border=2 style=\"border-color:".$trip['icon'].";\">";
	}
	echo "<br /><a href=\"trip.php?go=room&id=".$_GET['id']."\">詳細參與資料</a> (正:$scoqa/普:$scoqc/<a href=\"trip.php?go=smess2&id=$id\">負:$scoqb</a>)<a href=\"trip.php?go=smess&id=$id\">詳細</a></center>";
	echo '<div class="table1" style="padding:0px 260px;">
		<ul><li>
			<span class="title" style="width:50px;">ID</span>
			<span class="title" style="width:180px;">暱稱</span>
		</li>';
	$i = 0;
	while ($list = $db->fetch_array($query)) {
		$i++;
		echo "<li>\n".
			"<span class=\"dlist\" style=\"width:50px\">$i</span>\n".
			"<span class=\"dlist\" style=\"width:180px\">$list[handle_name]</span>\n".
		"</li>";
	}
	echo '</ul></div>';
	echo "<br />";
}

if ($_GET['go'] == 'sce' && !empty($_GET['room']) && !empty($_GET['trip'])) {
	session_start();
	$session_id = session_id();
	$room_no = $_GET['room'];
	$usertrip = $_GET['trip'];
	$uname = SessionCheck($session_id);
	$query1 = $db->query("select trip from user_entry where room_no = '$room_no' and
						 uname = '$uname' and user_no > '0' and role != 'none' and trip > ''");
	if ($db->num_rows($query1) == 0) {
		footer('遊戲結束後才能評價或是沒有TRIP');
	}
	$query2 = $db->query("select t.trip from user_entry u left join user_trip t on t.trip = u.trip
						 where u.room_no = '$room_no' and u.trip = '$usertrip' and u.user_no > 0 and role != 'none'");
	if ($db->num_rows($query2) == 0) {
		footer('遊戲結束後才能評價或是TRIP錯誤');
	}
	$query3 = $db->query("select count(*) from room where room_no = '$room_no' and
						 status = 'finished' and date > 0 and day_night = 'aftergame' AND victory_role IS NOT NULL");
	if ($db->result($query3,0) == 0) {
		footer('遊戲結束後才能評價');
	}
	
	$user = $db->fetch_array($query1);
	if ($usertrip == $user['trip']) {
		footer('不能評價自己');
	}
	if (empty($user['trip'])) {
		footer('沒有Trip不能評價');
	}
	
	$query = $db->query("select count(*) from user_entry where trip = '".$user['trip']."' and user_no > '0' and role != 'none' and gover = '1';");
	if ($db->result($query,0) < $triptonum) {
		footer('遊戲次數低於'.$triptonum.'不得評分');
	}
	if ($_POST['submit']) {
		if (empty($_POST['sceis'])) {
			footer('請選擇評價');
		}
		$mess = nohtml($_POST['mess']);
		if (empty($mess)) {
			footer('請輸入評語');
		}
		$query = $db->query("select count(*) from trip_score where user = '".$user['trip']."'
							 and room = '$room_no' and trip = '$usertrip';");
		if ($db->result($query, 0)) {
			footer('此人評價過了');
		}
		$query = $db->query("select count(*) from trip_score where user = '".$user['trip']."'
							 and room = '$room_no';");
		if ($db->result($query, 0) >= 5) {
			footer('同一場只能評價5次');
		}
		
		$db->query("INSERT INTO trip_score (user,room,trip,mess,score)VALUES('".$user['trip']."', '$room_no', '$usertrip', '$mess','".$_POST['sceis']."');");
		if ($_POST['sceis'] == '1') {
			$db->query("UPDATE user_entry SET score = score+1 where room_no = '$room_no' and trip = '$usertrip'");
		}
		if ($_POST['sceis'] == '2') {
			$db->query("UPDATE user_entry SET score = score-1 where room_no = '$room_no' and trip = '$usertrip'");
		}
		bheader("Location:trip.php?go=trip&id=$usertrip");
	}
	echo "<form name=\"trip\" action=\"trip.php?go=sce&room=$room_no&trip=$usertrip\" method=\"post\" enctype=\"multipart/form-data\">";
	echo '<li>請選擇正評價或負評價，一旦送出將不可恢復。</li>
		<li>正評 <input type="radio" name="sceis" value="1"></li>
		<li>普通 <input type="radio" name="sceis" value="3"></li>
		<li>負評 <input type="radio" name="sceis" value="2"></li>
		<li>意見 <input type="text" name="mess" size="30" value="" /></li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'smess' && !empty($_GET['id'])) {
	$id = $_GET['id'];
	$page = $_GET['page'];
	$page = max(1, intval($page));
	$start = ($page - 1) * $Snum;
	$query = $db->query("select count(*) from trip_score where trip = '$id';");
	$Snumq = $db->result($query, 0);
	if ($Snumq == 0) {
		footer('沒有資料');
	}
	$query = $db->query("select t.*,ut.handle_name from trip_score t inner join (select id from trip_score where trip = '$id' ORDER BY id DESC LIMIT $start,".$Snum.") tt on t.id=tt.id left join user_trip ut on ut.trip = t.user ORDER BY t.id DESC;");
	echo '<center>'.multi($Snumq, $Snum, $page,'trip.php?go=smess&id='.$id).'</center>';
	echo '<div class="table1" style="padding:0px 50px;">
		<ul><li>
			<span class="title" style="width:70px;">村莊ID</span>
			<span class="title" style="width:100px;">評論者</span>
			<span class="title" style="width:50px;">評價</span>
			<span class="title" style="width:400px;">評語</span>
		</li>';
	while ($list = $db->fetch_array($query)) {
	//	$query2 = $db->query("select distinct(handle_name) from user_entry where trip = '".$list['user']."' and user_no > '0' and role != 'none' and gover = '1' ORDER BY room_no DESC LIMIT 1;");
	//	$list['user2'] = $list['user'];
	//	if ($db->num_rows($query2) > 0) {
	//		$list['user2'] = $db->result($query2, 0);
	//	}
		
		if ($list['score'] == '1') {
			$list['score'] = '正';
		}
		if ($list['score'] == '2') {
			$list['score'] = '負';
		}
		if ($list['score'] == '3') {
			$list['score'] = '普';
		}
		if (empty($list['user'])) {
			$list['user'] = '--';
		}
		if (empty($list['mess'])) {
			$list['mess'] = '無評語';
		}
		if ($list['handle_name'] === NULL) {
			$list['handle_name'] = $list['user'];
		}
		echo "<li>\n".
			"<span class=\"dlist\" style=\"width:70px\"><a href=\"$oldhttp"."old_log.php?log_mode=on&room_no=$list[room]\">$list[room]</a></span>\n".
			"<span class=\"dlist\" style=\"width:100px\"><a href=\"trip.php?go=trip&id=$list[user]\" title=\"$list[user]\">$list[handle_name]</a></span>\n".
			"<span class=\"dlist\" style=\"width:50px\">$list[score]</span>\n".
			"<span class=\"dlist\" style=\"width:400px\">$list[mess]</span>\n".
		"</li>";
	}
	echo '</ul></div>';
}

if ($_GET['go'] == 'room' && !empty($_GET['id'])) {
	$id = $_GET['id'];
	if ($page <= 1) {
		echo '<center>
		<a href="trip.php?go=room&id='.$_GET['id'].'">全部</a> 
		<a href="trip.php?go=room&id='.$_GET['id'].'&play=8">8</a> 
		<a href="trip.php?go=room&id='.$_GET['id'].'&play=16">16</a> 
		<a href="trip.php?go=room&id='.$_GET['id'].'&play=22">22</a> 
		<a href="trip.php?go=room&id='.$_GET['id'].'&play=30">30</a></center>';
	}
	if ($_GET['play']) {
		$url = '&play='.$_GET['play'];
		if ($_GET['play'] == 8) {
			$sql = "and r.max_user = '8'";
		} elseif ($_GET['play'] == 16) {
			$sql = "and r.max_user = '16'";
		} elseif ($_GET['play'] == 22) {
			$sql = "and r.max_user = '22'";
		} elseif ($_GET['play'] == 30) {
			$sql = "and r.max_user = '30'";
		} else {
			$sql = '';
		}
	}
	
	if ($id == "DETfcflZU6") {
		$query2 = $db->query("select room_no from room where room_no > 1 AND status='playing' AND game_option NOT LIKE '%ischat%'");
		if ($db->num_rows($query2) > 0) {
			footer("有村莊正在進行或等待遊戲，暫停存取紀錄。");
		}
	}
	
	$query = $db->query("select count(u.room_no) from user_entry u left join room r on r.room_no = u.room_no
						 where u.trip = '$id' and u.user_no > 0 and u.role != 'none' and u.gover = '1' and r.date > 0 $sql and r.status = 'finished' and u.role <> 'GM';");
	$Snumq = $db->result($query, 0);
	if ($Snumq == 0) {
		footer('玩家尚未登記或無資料');
	}
	$page = $_GET['page'];
	$page = max(1, intval($page));
	$start = ($page - 1) * $Snum;
	echo '<center>'.multi($Snumq, $Snum, $page,'trip.php?go=room&id='.$id.$url).'</center>';
	echo '<div class="table1" style="padding:0px 110px;">
		<ul><li>
			<span class="title" style="width:70px;">村莊ID</span>
			<span class="title" style="width:180px;">暱稱</span>
			<span class="title" style="width:60px;">職業</span>
			<span class="title" style="width:50px;">狀態</span>
			<span class="title" style="width:50px;">勝利</span>
			<span class="title" style="width:50px;">暴斃</span>
		</li>';
	$query = $db->query("select u.room_no,u.handle_name,u.room_no,u.role,u.live,u.death,r.victory_role from user_entry u
						 left join room r on r.room_no = u.room_no
						 where u.trip = '$id' and u.user_no > 0 and u.role != 'none' and u.gover = '1' and r.date > 0 $sql and r.status = 'finished'
						 ORDER BY r.last_updated DESC LIMIT $start,".$Snum.";");
	while ($list = $db->fetch_array($query)) {
		if (strstr((string) $list['role'],"wolf")) {
			$list['role'] = '人狼';
		} elseif (strstr((string) $list['role'],"wfbig")) {
			$list['role'] = '大狼';
		} elseif (strstr((string) $list['role'],"mage")) {
			$list['role'] = '占卜師';
		} elseif (strstr((string) $list['role'],"necromancer")) {
			$list['role'] = '靈能者';
		} elseif (strstr((string) $list['role'],"mad")) {
			$list['role'] = '狂人';
		} elseif (strstr((string) $list['role'],"spy")) {
			$list['role'] = '間諜';
		} elseif (strstr((string) $list['role'],"guard")) {
			$list['role'] = '獵人';
		} elseif (strstr((string) $list['role'],"common")) {
			$list['role'] = '共有者';
		} elseif (strstr((string) $list['role'],"fox")) {
			$list['role'] = '妖狐';
		} elseif (strstr((string) $list['role'],"betr")) {
			$list['role'] = '背德';
		} elseif (strstr((string) $list['role'],"fosi")) {
			$list['role'] = '子狐';
		} elseif (strstr((string) $list['role'],"poison")) {
			$list['role'] = '埋毒者';
		} elseif (strstr((string) $list['role'],"cat")) {
			$list['role'] = '貓又';
		} elseif (strstr((string) $list['role'],"owlman")) {
			$list['role'] = '夜梟';
		} elseif (strstr((string) $list['role'],"pengu")) {
			$list['role'] = '小企鵝';
		} elseif (strstr((string) $list['role'],"human")) {
			$list['role'] = '村民';
		} elseif (strstr((string) $list['role'],"GM")) {
			$list['role'] = 'GM';
		} elseif (strstr((string) $list['role'],"mytho")) {
			$list['role'] = '說謊狂';
		} elseif (strstr((string) $list['role'],"noble")) {
			$list['role'] = '貴族';
		} elseif (strstr((string) $list['role'],"slave")) {
			$list['role'] = '奴隸';
		} else {
			$list['role'] = '???';
		}
		
		if ($list['live'] == 'live') {
			$list['live'] = '生存';
		} elseif ($list['live'] == 'gone') {
			$list['live'] = '脫離';
		} else {
			$list['live'] = '死亡';
		}
		
		if ($list['death']) {
			$list['death'] = '是';
			$list['death2'] = 'color:red;';
		} else {
			$list['death'] = '無';
			$list['death2'] = '';
		}
		
		$victory = match ($list['victory_role']) {
            'human' => "<img src=\"$victory_role_human_image\">",
            'wolf' => "<img src=\"$victory_role_wolf_image\">",
            'fox', 'fox1', 'fox2' => "<img src=\"$victory_role_fox_image\">",
            'draw' => "<img src=\"$victory_role_draw_image\">",
            'lover' => "<img src=\"$victory_role_lovers_image\">",
            default => "-",
        };
		
		$oldhttp = '';
		if ($list['room_no'] < $roomidserint) {
			$oldhttp = $oldurl;
		}
		
		echo "<li>\n".
			"<span class=\"dlist\" style=\"width:70px;$list[death2]\"><a href=\"$oldhttp"."old_log.php?log_mode=on&room_no=$list[room_no]\" target=\"_blank\">$list[room_no]</a></span>\n".
			"<span class=\"dlist\" style=\"width:180px;$list[death2]\">$list[handle_name]</span>\n".
			"<span class=\"dlist\" style=\"width:60px;$list[death2]\">$list[role]</span>\n".
			"<span class=\"dlist\" style=\"width:50px;$list[death2]\">$list[live]</span>\n".
			"<span class=\"dlist\" style=\"width:50px;$list[death2]\">$victory</span>\n".
			"<span class=\"dlist\" style=\"width:50px;$list[death2]\">$list[death]</span>\n".
		"</li>";
	}
	echo '</ul></div>';
	if ($page == 1) {
		$query = $db->query("select u.role,u.live,u.death,u.score from user_entry u
							 left join room r on r.room_no = u.room_no
							 where u.trip = '$id' and u.user_no > 0 and u.role != 'none' and u.gover = '1' and r.date > 0 $sql and r.status = 'finished'
							 $sql;");
		$cwolf = 0;
		$cfox = 0;
		$clive = 0;
		$cdeath = 0;
		$score = 0;
		while ($list = $db->fetch_array($query)) {
			if (strstr((string) $list['role'],"wolf") || strstr((string) $list['role'],"wfbig") || strstr((string) $list['role'],"mad")) {
				$cwolf++;
			}
			if (strstr((string) $list['role'],"fox") || strstr((string) $list['role'],"betr") || strstr((string) $list['role'],"fosi")) {
				$cfox++;
			}
			if ($list['live'] == 'live' && !strstr((string) $list['role'], 'GM')) {
				$clive++;
			}
			if ($list['death']) {
				$cdeath++;
			}
			$score = $score + $list['score'];
		}
		$cwolf = round($cwolf / $Snumq * 100,2);
		echo "<center>狼側率 $cwolf % ";
		$cfox = round($cfox / $Snumq * 100,2);
		echo "狐側率 $cfox % ";
		$chuman = 100 - ($cfox + $cwolf);
		echo "人側率 $chuman % <br />";
		
		$clive = round($clive / $Snumq * 100,2);
		
		$cdeath = round($cdeath / $Snumq * 100,2);
		echo "生存率 $clive % 暴斃率 $cdeath % 評價:$score";
		echo "</center>";
	}
}

if (empty($_GET['go']) || $_GET['go'] == 'search') {
	if ($_GET['go'] == 'search') {
		$sqla = "where trip LIKE '".$_GET['sname']."%'";
		$url = '?go=search&sname='.$_GET['sname'];
		$urlb = '&go=search&sname='.$_GET['sname'];
	} else {
		$sqla = "where trip > ''";
		$url = '?';
		$urlb = '';
	}
	$query = $db->query("select count(*) from user_trip $sqla");
	$Snumq = $db->result($query, 0);
	$page = $_GET['page'];
	$page = max(1, intval($page));
	$start = ($page - 1) * $Snum;
	if ($by == 1) {
		$url .= '&by=1';
		$sql = 'order by id desc';
	} elseif ($by == 2) {
		$url .= '&by=2';
		$sql = 'order by trip asc';
	} else {
		$sql = 'order by id desc';
	}
	if ($_GET['go'] != 'search') {
		echo "<center>已登記Trip總數: ".$Snumq." 筆<br />不定時清理0遊戲數Trip，故總數可能會減少。<br />";
	} else {
		echo "<center>";
	}
	echo multi($Snumq, $Snum, $page,'trip.php'.$url).'</center>';
	echo '<div class="table1" style="padding:0px 260px;">
		<ul><li>
			<span class="title" style="width:50px;"><a href="trip.php?by=1'.$urlb.'">ID</a></span>
			<span class="title" style="width:100px;"><a href="trip.php?by=2'.$urlb.'">Trip</a></span>
			<span class="title" style="width:50px;">狀態</span>
		</li>';

	if ($Snum) {
		$query = $db->query("select * from user_trip $sqla $sql LIMIT $start,".$Snum.";");
		while ($list = $db->fetch_array($query)) {
			echo "<li>\n".
				"<span class=\"dlist\" style=\"width:50px\">$list[id]</span>\n".
				"<span class=\"dlist\" style=\"width:100px\"><a href=\"trip.php?go=trip&id=$list[trip]\">$list[trip]</a></span>\n";
				if ($list['ban'] == 0) {
					echo "<span class=\"dlist\" style=\"width:50px\">正常</span>\n";
				} else {
					echo "<span class=\"dlist\" style=\"width:50px\"><a href = \"trip_vote.php?id=$list[ban]\">凍結</a></span>\n";
				}
		}
	} else {
		echo "<<li>><span>沒有資料</span></li>\n";
	}
	echo '</ul></div>';
	echo "<br /><center>按下Trip可查詢該Trip遊戲中使用過暱稱<br />";
	echo '<form name="trip" action="trip.php" method="get" enctype="multipart/form-data">
		搜尋Trip <input type="text" name="sname" size="9" value="" /> 
		<input id="submit" name="go" type="submit" value="search" /></form>';
	echo "</center>";
}

if ($_GET['go'] == 'icon') {
	if ($_POST['submit']) {
		if (empty($_POST['name']) || empty($_POST['color']) || empty($_FILES['icon_file']['name'])) {
			footer('缺少資料或是有遺漏');
		}

		$Imgfname = explode(".", (string) $_FILES['icon_file']['name']);
		if ($_FILES['icon_file']['name'] && !in_array(strtolower($Imgfname[1]), ['jpg','webp','png','gif'])) {
			footer('只接受jpg,webp,png,gif');
		}

		if (strlen((string) $_POST['color']) != 7 || !preg_match("/^#[0123456789abcdefABCDEF]{6}/", (string) $_POST['color'])) {
			footer('顏色錯誤');
		}

		if($trip == "4Tbklt1m4U" && $_POST['password'] == "test") {
			//MakeSmallPicture($icon_max_width, $_FILES['icon_file']['tmp_name'], $trip_icon_up."/test123");
			exit;
		} else {
			$password = md5((string) $_POST['password']);
			$query = $db->query("select * from user_trip where trip = '$trip' and password = '$password' and ban = '0';");
			$tripdb = $db->fetch_array($query);
			if ($tripdb['trip'] == '') {
				footer('找不到Trip或密碼錯誤，也有可能Trip已經被凍結。');
			}

			$query = $db->query("select count(u.room_no) from user_entry u left join room r on r.room_no = u.room_no
								 where u.trip = '$trip' and u.user_no > 0 and u.role != 'none' and u.gover = '1' and r.date > 0 and r.status = 'finished' and u.role <> 'GM';");
			if ($db->result($query, 0)  < 50) {
				footer('使用個人頭像Trip至少要玩 50 場才能傳');
			}

			if ((filesize($_FILES['icon_file']['tmp_name']) /1024) > 300) {
				footer('檔案不能大於 300KB');
			}

			if ($tripdb['size']) {
				$imgtype = explode(":",(string) $tripdb['size']);
				@unlink($trip_icon_up."/icon_".$tripdb['id'].".jpg");
				@unlink($trip_icon_up."/icon_".$tripdb['id'].".gif");
				@unlink($trip_icon_up."/icon_".$tripdb['id'].".webp");
				@unlink($trip_icon_up."/icon_".$tripdb['id'].".png");
				//echo $imgtype[2];
			}

			MakeSmallPicture($icon_max_width, $_FILES['icon_file']['tmp_name'], $trip_icon_up."/icon_".$tripdb['id']);
		}
		$imgtype = "webp";
		/*
		$bi = @imagecreatefromjpeg("trip_icon/icon_".$tripdb['id'].".jpg");
		if ($bi) {
			$bix = imagesx($bi);
			$biy = imagesy($bi);
		} else {
			$bi = @imagecreatefromgif("trip_icon/icon_".$tripdb['id'].".gif");
			if ($bi) {
				$imgtype = "gif";
				$bix = imagesx($bi);
				$biy = imagesy($bi);
			}
		}
		*/

		[$bix, $biy, $btype, $battr] = getimagesize("trip_icon/icon_".$tripdb['id'].".webp");

		if ($bix > 0 && $biy > 0) {
			$db->query("UPDATE user_trip SET icon = '".$_POST['color']."',size = '$bix:$biy:$imgtype' where trip = '$trip';");
			echo "<b>上傳完成</b>";
		} else {
			$db->query("UPDATE user_trip SET icon = '0',size = '' where trip = '$trip';");
			echo "<b>清除頭像</b>";
		}

		//bheader("Location:trip.php");
	}
	echo '<form name="trip" action="trip.php?go=icon" method="post" enctype="multipart/form-data">
		
		<li>請輸入目前Trip (沒有加密過)和密碼，選擇檔案即可上傳個人頭像</li>
		<li>不管新上傳還是要修改頭像，一律都是這上傳</li>
		<li>使用個人頭像Trip至少要玩 50 場才能傳</li>
		<li>上傳檔案副檔名請小寫，請勿先自行縮圖不然會上傳失敗(系統會幫你縮)</li>
		<li>TRIP <input type="text" name="name" size="24" value="" /></li>
		<li>密碼 <input type="password" name="password" size="24" value="" /></li>
		<li>顏色 <input type="text" name="color" size="24" value="#FFFFFF" /> (不知道是什麼不用改)</li>
		<li>上傳 <input name="icon_file" type="file" size="24" accept=".jpg, .png, .webp, .gif"></li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'smess2' && !empty($_GET['id'])) {
	$id = $_GET['id'];
	$page = $_GET['page'];
	$page = max(1, intval($page));
	$start = ($page - 1) * $Snum;
	$query = $db->query("select count(*) from trip_score where trip = '$id' and score = '2';");
	$Snumq = $db->result($query, 0);
	if ($Snumq == 0) {
		footer('沒有資料');
	}
	$query = $db->query("select t.*,u.death,ut.handle_name from trip_score t inner join (select id from trip_score where trip = '$id' and score = '2' ORDER BY id DESC LIMIT $start,".$Snum.") tt on t.id=tt.id left join user_entry u on u.room_no = t.room and u.trip = t.trip and u.user_no > 0
						 left join user_trip ut on ut.trip = t.user ORDER BY t.id;");
	echo '<center>'.multi($Snumq, $Snum, $page,'trip.php?go=smess2&id='.$id).'</center>';
	echo '<div class="table1" style="padding:0px 40px;">
		<ul><li>
			<span class="title" style="width:70px;">村莊ID</span>
			<span class="title" style="width:100px;">評論者</span>
			<span class="title" style="width:80px;">該場狀態</span>
			<span class="title" style="width:400px;">評語</span>
		</li>';
	while ($list = $db->fetch_array($query)) {
		//$query2 = $db->query("select distinct(handle_name) from user_entry where trip = '".$list['user']."' and user_no > '0' and role != 'none' and gover = '1' ORDER BY room_no DESC LIMIT 1;");
		//$list['user2'] = $list['user'];
		//if ($db->num_rows($query2) > 0) {
		//	$list['user2'] = $db->result($query2, 0);
		//}
		if ($list['death'] == '0') {
			$list['death'] = '正常';
			$list['death2'] = '';
		}else{
			$list['death'] = '暴斃';
			$list['death2'] = "color:red;";
		}
		if (empty($list['user'])) {
			$list['user'] = '--';
		}
		if (empty($list['mess'])) {
			$list['mess'] = '無評語';
		}
		if ($list['handle_name'] === NULL) {
			$list['handle_name'] = $list['user'];
		}
		echo "<li>\n".
			"<span class=\"dlist\" style=\"width:70px;$list[death2]\"><a href=\"$oldhttp"."old_log.php?log_mode=on&room_no=$list[room]\">$list[room]</a></span>\n".
			"<span class=\"dlist\" style=\"width:100px;$list[death2]\"><a href=\"trip.php?go=trip&id=$list[user]\" title=\"$list[user]\">$list[handle_name]</a></span>\n".
			"<span class=\"dlist\" style=\"width:80px;$list[death2]\">$list[death]</span>\n".
			"<span class=\"dlist\" style=\"width:400px;$list[death2]\">$list[mess]</span>\n".
		"</li>";
	}
	echo '</ul></div>';
}

footer();
?>
