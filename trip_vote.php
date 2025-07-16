<?php
require_once __DIR__ . '/game_functions.php';
$Snum = 20;
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
	include_once __DIR__ . "/footer.inc.php";
	if ($e) {
		exit;
	}
}

$ptitle = "凍結系統 - ";
include_once __DIR__ . "/header.inc.php";

echo '<div id="indexhi"><div id="indexh2">';

if (empty($_GET['go'])) {
	echo '<a href="trip_vote.php?go=post">發起投票</a> ';
	echo '<a href="trip_vote.php?go=old">過去紀錄</a> ';
	echo '<a href="trip_vote.php?go=list">凍結清單</a> ';
}

echo '</div><fieldset><legend><b>';

if ($_GET['go'] == 'post') {
	echo '發起投票';
} elseif ($_GET['go'] == 'list') {
	echo '凍結清單';
} elseif ($_GET['go'] == 'view') {
	echo '詳細資料';
} elseif ($_GET['go'] == 're') {
	echo '我要投票';
} elseif ($_GET['go'] == 'old') {
	echo '過去紀錄';
} else {
	echo '投票列表';
}
echo '</b></legend>';

if ($_GET['go'] == '') {
	echo "<center>Trip凍結可在此處發起投票，以下是正在進行的投票。 <br />";
	echo "注意！為了確保隱私性，不公佈參與者清單。 <br /></center>";

	$query = $db->query("select count(*) from trip_list where stat = '0'");
	$Snumq = $db->result($query, 0);
	$page = $_GET['page'];
	$page = max(1, (int) $page);
	$start = ($page - 1) * $Snum;
	//$url = "?go=list";
	echo '<center>'.multi($Snumq, $Snum, $page,'trip_vote.php'.$url).'</center>';
	echo '<div class="table1"  style="padding:0px 90px;">
		<ul><li>
			<span class="title" style="width:50px;">ID</span>
			<span class="title" style="width:100px;">發起者</span>
			<span class="title" style="width:100px;">被告</span>
			<span class="title" style="width:160px;">發起時間</span>
			<span class="title" style="width:60px;">贊成數</span>
			<span class="title" style="width:50px;"> </span>
		</li>';

	if ($Snum !== 0) {
		$query = $db->query("select * from trip_list where stat = '0' ORDER BY id ASC LIMIT $start,".$Snum.";");
		$timeee = 86400 * 2;
		while ($list = $db->fetch_array($query)) {
			if (time() > $list[\TIME] + $timeee) {
				//更新
				$db->query("UPDATE trip_list SET stat = '1' where id = '".$list['id']."' and stat = '0';");
				if ($list[\COUNT] >= 20 && $list[\GMCO] >= 3) {
					$db->query("UPDATE trip_list SET stat = '2' where id = '".$list['id']."' and stat = '1';");
					$db->query("UPDATE user_trip SET ban = '".$list['id']."' where trip = '".$list['totrip']."' and ban = -1;");
				} else {
					$db->query("UPDATE user_trip SET ban = '0' where trip = '".$list['totrip']."' and ban = -1;");
				}
			}
			echo "<li>\n".
				"<span class=\"dlist\" style=\"width:50px\">$list[id]</span>\n".
				"<span class=\"dlist\" style=\"width:100px\"><a href=\"trip.php?go=trip&id=$list[trip]\">$list[trip]</a></span>\n".
				"<span class=\"dlist\" style=\"width:100px\"><a href=\"trip.php?go=trip&id=$list[totrip]\">$list[totrip]</a></span>\n";
			echo "<span class=\"dlist\" style=\"width:160px\">".gmdate("Y-m-d H:i:s",$list[\TIME] + $time_zone)."</span>\n".
				 "<span class=\"dlist\" style=\"width:60px\">$list[count]</span>\n".
				 "<span class=\"dlist\" style=\"width:50px\"><a href=\"trip_vote.php?go=re&id=$list[id]\">投票</a></span>\n".
				 "</li>";
		}
	} else {
		echo "<<li>><span>沒有資料</span></li>\n";
	}
	echo '</ul></div>';
}

if ($_GET['go'] == 'view') {
	$query = $db->query("select count(*) from trip_list where id = '".$id."' and stat > 0;");
	if (!$db->result($query, 0)) {
		footer('投票不存在或正在進行中。');
	}
	
	$query = $db->query("select * from trip_list where id = '".$id."';");
	$list = $db->fetch_array($query);
	echo "<li>發起者: $list[trip]</li>";
	echo "<li>被告: $list[totrip]</li>";
	echo "<li>贊成數: $list[count]</li>";
	echo "<li>GM數: $list[gmco]</li>";
	echo "<li>發起時間: ".gmdate("Y-m-d H:i:s",$list[\TIME] + $time_zone)."</li>";
}

if ($_GET['go'] == 'post') {
	if ($_POST['submit']) {
		if (empty($_POST['name']) || empty($_POST['password']) || empty($_POST['totrip'])) {
			footer('缺少資料或是有遺漏');
		}
		$password = md5((string) $_POST['password']);
		//$trip = $_POST['name'];
		
		$query = $db->query("select count(*) from user_trip where trip = '$trip' and password = '$password' and ban = '0';");
		if (!$db->result($query, 0)) {
			footer('找不到Trip或密碼錯誤，也有可能Trip已經被凍結。');
		}
		
		$query = $db->query("select count(*) from user_entry where trip = '$trip' and user_no > '0' and role != 'none' and gover = '1'");
		if ($db->result($query,0) < ($triptonum *10)) 
		{
			footer('您不是GM。');
		}
		
		$query = $db->query("select count(*) from user_entry u left join room r on r.room_no = u.room_no where u.trip = '$trip' and u.user_no > 0 and u.role != 'none' and u.gover = '1' and r.max_user >= '22'");
		if ($db->result($query,0) < 200) 
		{
			footer('22人場次(含)以上至少參與200場。');
		}
		
		$aa =	"trip,totrip,time";
		$bb =	"'$trip','$totrip','".time()."'";

		//新增資料
		$db->query("insert into trip_list ($aa) values ($bb)");
		$db->query("UPDATE user_trip SET ban = '-1' where trip = '$totrip' and ban = '0';");
		
		bheader("Location:trip_vote.php");
	}
	echo '<form name="trip" action="trip_vote.php?go=post" method="post" enctype="multipart/form-data">
		<li>頭票發起者Trip請輸入加密前Trip並且輸入密碼。</li>
		<li>投票被告請輸入加密後的Trip。</li>
		<li>發起者須GM資格，且22人場次(含)以上至少參與200場。</li>
		<li>發起者Trip <input type="text" name="name" size="24" value="" /></li>
		<li>密碼 <input type="password" name="password" size="24" value="" /></li>
		<li>被告Trip <input type="text" name="totrip" size="24" value="" /></li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 're') {
	if ($_POST['submit']) {
		if (empty($_POST['name']) || empty($_POST['password']) || empty($_GET['id'])) {
			footer('缺少資料或是有遺漏');
		}
		$password = md5((string) $_POST['password']);
		//$trip = $_POST['name'];
		$isgm = 1;
		
		$query = $db->query("select count(*) from trip_list where id = '".$id."' and stat = '0';");
		if (!$db->result($query, 0)) {
			footer('投票不存在或是已經結束。');
		}
		
		$query = $db->query("select count(*) from trip_vote where reid = '".$id."' and trip = '$trip';");
		if ($db->result($query, 0)) {
			footer('您投票過了。');
		}
		
		$query = $db->query("select count(*) from user_trip where trip = '$trip' and password = '$password' and ban = '0';");
		if (!$db->result($query, 0)) {
			footer('找不到Trip或密碼錯誤，也有可能Trip已經被凍結。');
		}
		
		$query = $db->query("select count(*) from user_entry where trip = '$trip' and user_no > '0' and role != 'none' and gover = '1'");
		if ($db->result($query,0) < ($triptonum *10)) 
		{
			$isgm = 0;
		}
		
		$query = $db->query("select count(*) from user_entry u left join room r on r.room_no = u.room_no where u.trip = '$trip' and u.user_no > 0 and u.role != 'none' and u.gover = '1' and r.max_user >= '22'");
		if ($db->result($query,0) < 50) 
		{
			footer('22人場次(含)以上至少參與50場。');
		}

		$aa =	"reid,trip,isgm,time";
		$bb =	"'$id','$trip','$isgm','".time()."'";
		//新增資料
		$db->query("insert into trip_vote ($aa) values ($bb)");

		//更新
		$db->query("UPDATE trip_list SET count = count+1,gmco = gmco+$isgm where id = '".$id."' and stat = '0';");
		
		bheader("Location:trip_vote.php");
	}
	echo '<form name="trip" action="trip_vote.php?go=re&id='.$id.'" method="post" enctype="multipart/form-data">
		<li>頭票參與者Trip請輸入加密前Trip並且輸入密碼。</li>
		<li>投票資格需22人場次(含)以上至少參與50場。</li>
		<li>參與者Trip <input type="text" name="name" size="24" value="" /></li>
		<li>密碼 <input type="password" name="password" size="24" value="" /></li>
		<li><input id="submit" name="submit" type="submit" value="送出" /></li>
	</form>';
}

if ($_GET['go'] == 'list') {
	$query = $db->query("select count(*) from user_trip where ban > 0");
	$Snumq = $db->result($query, 0);
	$page = $_GET['page'];
	$page = max(1, (int) $page);
	$start = ($page - 1) * $Snum;
	$url = "?go=list";
	echo '<center>'.multi($Snumq, $Snum, $page,'trip_vote.php'.$url).'</center>';
	echo '<div class="table1"  style="padding:0px 270px;">
	<ul><li>
		<span class="title" style="width:50px;">ID</span>
		<span class="title" style="width:100px;">Trip</span>
		<span class="title" style="width:50px;">狀態</span>
	</li>';

	if ($Snum !== 0) {
		$query = $db->query("select * from user_trip where ban > 0 ORDER BY id ASC LIMIT $start,".$Snum.";");
		while ($list = $db->fetch_array($query)) {
			echo "<li>\n".
				"<span class=\"dlist\" style=\"width:50px\">$list[id]</span>\n".
				"<span class=\"dlist\" style=\"width:100px\"><a href=\"trip.php?go=trip&id=$list[trip]\">$list[trip]</a></span>\n".
				"<span class=\"dlist\" style=\"width:50px\"><a href = \"trip_vote.php?go=view&id=$list[ban]\">凍結</a></span>\n";
		}
	} else {
		echo "<<li>><span>沒有資料</span></li>\n";
	}
	echo '</ul></div>';
}

if ($_GET['go'] == 'old') {
	$query = $db->query("select count(*) from trip_list where stat > 0");
	$Snumq = $db->result($query, 0);
	$page = $_GET['page'];
	$page = max(1, (int) $page);
	$start = ($page - 1) * $Snum;
	$url = "?go=old";
	echo '<center>'.multi($Snumq, $Snum, $page,'trip_vote.php'.$url).'</center>';
	echo '<div class="table1"  style="padding:0px 80px;">
	<ul><li>
		<span class="title" style="width:50px;">ID</span>
		<span class="title" style="width:100px;">發起者</span>
		<span class="title" style="width:100px;">被告</span>
		<span class="title" style="width:160px;">發起時間</span>
		<span class="title" style="width:60px;">贊成數</span>
		<span class="title" style="width:50px;">狀態</span>
	</li>';

	if ($Snum !== 0) {
		$query = $db->query("select * from trip_list where stat > 0 ORDER BY id ASC LIMIT $start,".$Snum.";");
		$timeee = 86400 * 2;
		while ($list = $db->fetch_array($query)) {
			echo "<li>\n".
				"<span class=\"dlist\" style=\"width:50px;\">$list[id]</span>\n".
				"<span class=\"dlist\" style=\"width:100px;\"><a href=\"trip.php?go=trip&id=$list[trip]\">$list[trip]</a></span>\n".
				"<span class=\"dlist\" style=\"width:100px;\"><a href=\"trip.php?go=trip&id=$list[totrip]\">$list[totrip]</a></span>\n";
			echo "<span class=\"dlist\" style=\"width:160px;\">".gmdate("Y-m-d H:i:s",$list[\TIME] + $time_zone)."</span>\n".
				 "<span class=\"dlist\" style=\"width:60px;\">$list[count]</span>\n";
			if ($list[\STAT] == 1) {
				echo "<span class=\"dlist\" style=\"width:50px;\"><a href = \"trip_vote.php?go=view&id=$list[id]\">失效</a></span>\n";
			} else {
				echo "<span class=\"dlist\" style=\"width:50px;\"><a href = \"trip_vote.php?go=view&id=$list[id]\">凍結</a></span>\n";
			}
			echo "</li>";
		}
	} else {
		echo "<<li>><span>沒有資料</span></li>\n";
	}
	echo '</ul></div>';
}

footer();
?>
