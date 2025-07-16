<?php
require_once __DIR__ . '/game_functions.php';
$BBSnumre = 10;
$BBSnum = 15;

$G_view = (int)$_GET['view'];
$G_id = (int)$_GET['id'];
$P_id = (int)$_POST['id'];

$bname = trim((string) $_POST['bname']);
$title = trim((string) $_POST['title']);

$bnamee = explode("#", $bname);
$bnamee[0] = str_replace("◆","◇",$bnamee[0]);
if ($bnamee[1] !== '' && $bnamee[1] !== '0') {
	$trip = tripping($bnamee[1]);
}

if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else{
	$ip= $_SERVER['REMOTE_ADDR'];
}

function strlenu($src): int {
	if (!extension_loaded("mbstring")) {
		return strlen((string) $src);
	} else {
		return mb_strlen((string) $src,strtolower('utf-8'));
	}
}

function StrToSQL($src) {
	//$src = str_replace("\r", "", $src);
	//$src = str_replace("\n", "\\n", $src);
	if (get_magic_quotes_gpc()) return $src;
	return addslashes((string) $src);
}

function nohtml($src): string {
	return str_replace("<", "&lt;", (string) $src);
}

function StrToHTML($src): string {
	$src = preg_replace("/\[url\](www.|https?:\/\/|ftp:\/\/|telnet:\/\/){1}([^\[\"']+?)\[\/url\]/i", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", (string) $src);
	$src = preg_replace("/\[color=([#\w]+?)\](.+?)\[\/color\]/i", "<font color=\"\\1\">\\2</font>", (string) $src);
	$src = preg_replace("/\[b\](.+?)\[\/b\]/i", "<b>\\1</b>", (string) $src);
	return str_replace("\n", "<br />", $src);
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

$ptitle = "人狼討論 - ";
include_once __DIR__ . "/header.inc.php";

echo '<div id="indexhi"><div id="indexh2">';

if (empty($G_view) && empty($_GET['go'])) {
	echo '<a href="bbs.php?go=post">發表主題</a>';
	echo ' <a href="bbs.php?go=dige">精華區</a>';
}
if ($G_view && preg_match("/^(\\d+)\$/",$G_view)) {
	echo '<a href="bbs.php?go=postre&id='.$G_view.'">回覆主題</a>';
	echo ' <a href="bbs.php">回列表</a>';
}
if ($_GET['go'] == 'dige') {
	echo '<a href="bbs.php?go=post">發表主題</a>';
	echo ' <a href="bbs.php">全部主題</a>';
}

echo '</div><fieldset><legend><b>';

if ($G_view !== 0) {
	echo '文章列表';
} elseif ($_GET['go'] == 'post') {
	echo '發表主題';
} elseif ($_GET['go'] == 'postre') {
	echo '回覆主題';
} elseif ($_GET['go'] == 'dige') {
	echo '精華列表';
} else {
	echo '主題列表';
}

echo '</b></legend>';

if (($_GET['go'] == 'post' || $_GET['go'] == 'postre') && $_POST['submit']) {
    setcookie("bname",$bname);
    setcookie("bpass",(string) $_POST['bpass']);
    if (empty($bname) || empty($_POST['bpass']) || empty($_POST['mess']) || (empty($title) && $_GET['go'] == 'post')) {
  			footer('缺少資料或是有遺漏');
  		}
    if (strlenu($title) > 50) {
  			footer('限制50字元');
  		}
    if (strlenu($bnamee[0]) > 12) {
  			footer('限制12字元');
  		}
    if (strlenu($_POST['mess']) > 5000) {
  			footer('限制5000字元');
  		}
    if ($_COOKIE['tobbsokpost'] == '') {
  			footer('主機不允許留言');
  		}
}

if ($_GET['go'] == 'post') {
	if ($_POST['submit']) {
		$aa =	'name,title,trip,mess,ip,time,retime,password';
		$bb =	"'".StrToSQL($bnamee[0])."'".
				",'".StrToSQL($title)."'".
				",'$trip'".
				",'".StrToSQL($_POST['mess'])."'".
				",'$ip'".
				",'".time()."'".
				",'".time()."'".
				",'".md5((string) $_POST['bpass'])."'";

		//新增資料
		$db->query("insert into bbs ($aa) values ($bb)");
		$bbs_id = $db->insert_id();
		bheader("Location:bbs.php?view=".$bbs_id);
	}
	echo '<form name="bbs" action="bbs.php?go=post" method="post" enctype="multipart/form-data">
		<li>暱稱 <input type="text" name="bname" size="24" value="'.$_COOKIE['bname'].'" /></li>
		<li>密碼 <input type="password" name="bpass" size="24" value="'.$_COOKIE['bpass'].'" />(管理用)</li>
		<li>標題 <input type="text" name="title" size="24" value="" /></li>
		<li>內容<br /><textarea name="mess" rows="5" cols="64"></textarea></li>
		<li><input id="submit" name="submit" type="submit" value="發表" /></li>
	</form>';
}

if ($_GET['go'] == 'postre' && (preg_match("/^(\\d+)\$/",$P_id) || preg_match("/^(\\d+)\$/",$G_id))) {
	if ($_POST['submit']) {
		$aa =	'reid,name,trip,mess,ip,time,retime,password';

		$bb =	"'".$P_id."'".
				",'".StrToSQL($bnamee[0])."'".
				",'$trip'".
				",'".StrToSQL($_POST['mess'])."'".
				",'$ip'".
				",'".time()."'".
				",'".time()."'".
				",'".md5((string) $_POST['bpass'])."'";

			//檢查文章
			$query = $db->query("select id,locked from bbs where id = '".$P_id."';");
			$rowchack = $db->fetch_array($query);
			if (!$rowchack['id'] || $rowchack['locked'] == 1) footer('本文不允許回覆');

			//新增資料
			$db->query("insert into bbs ($aa) values ($bb)");
			$db->query("UPDATE bbs SET retime = '".time()."',recount = recount+1 where id = '".$P_id."';");
			//判斷跳頁
			$query = $db->query("select recount from bbs where id = '".$P_id."';");
			$row = $db->fetch_array($query);
			$page_ren = max(ceil($row['recount'] / $BBSnumre), 1);

			bheader("Location:bbs.php?view=".$P_id."&page=".$page_ren);
	}
		echo '<form name="bbs" action="bbs.php?go=postre" method="post" enctype="multipart/form-data">
			<li>暱稱 <input type="text" name="bname" size="24" value="'.$_COOKIE['bname'].'" /></li>
			<li>密碼 <input type="password" name="bpass" size="24" value="'.$_COOKIE['bpass'].'" />(管理用)</li>
			<li>內容<br /><textarea name="mess" rows="5" cols="64"></textarea></li>
			<input type="hidden" name="id" value="'.$G_id.'">
			<li><input id="submit" name="submit" type="submit" value="回覆" /></li>
		</form>';
}

if ($G_view && preg_match("/^(\\d+)\$/",$G_view)) {
		$query = $db->query("select * from bbs where id='".$G_view."' and reid = '0'");
		$bbsvdata = $db->fetch_array($query);
		$bbsvdata['title'] = '<b>'.nohtml($bbsvdata['title']).'</b>';
		$bbsvdata['name'] = nohtml($bbsvdata['name']);
		if ($bbsvdata['toped']) {
			$bbsvdata['title'] = '[置頂] '.$bbsvdata['title'];
		} 
		if ($bbsvdata['locked']) {
			$bbsvdata['title'] = '[鎖定] '.$bbsvdata['title'];
		}
		if ($bbsvdata['trip']) {
			$bbsvdata['name'] .= '◆<a target="_blank" href="trip.php?go=trip&id='.$bbsvdata['trip'].'">'.$bbsvdata['trip'].'</a>';
		}
		if ($bbsvdata['dige']) {
			$bbsvdata['title'] .= ' (精華)';
		}
		$bbsvdata['mess'] = StrToHTML($bbsvdata['mess']);
		$bbsvdata['time'] = gmdate("Y-m-d H:i:s",$bbsvdata['time']+$time_zone);
		
		if(!$bbsvdata['id']) {
			footer('錯誤');
		}
		
		$page = (int)$_GET['page'];
		$page = max(1, $page);
		$start = ($page - 1) * $BBSnumre;
		echo '<div class="table1"><ul>';
		echo '<center>'.multi($bbsvdata['recount'], $BBSnumre, $page,"bbs.php?view=".$G_view."").'</center>';
		echo '<li><span class="view_title">'.$bbsvdata['title'].'<br />'.$bbsvdata['name'].'</span></li>
			<li><span class="view_mess">'.$bbsvdata['mess'].'</span></li>
			<li><span class="view_b"><a href = "bbs.php?go=edit&id='.$bbsvdata['id'].'">NO.'.$bbsvdata['id'].'</a> &lt;..&gt; ['.$bbsvdata['time'].']</span></li>';
		if ($bbsvdata['recount']) {
			echo "<br>\n";
			$query = $db->query("select id,name,trip,mess,ip,time from bbs where reid = '".$bbsvdata['id']."' order by retime ASC LIMIT $start,".$BBSnumre.";");
			while ($bbsp = $db->fetch_array($query)) {
				$bbsp['name'] = nohtml($bbsp['name']);
				if ($bbsp['trip']) {
					$bbsp['name'] .= '◆<a target="_blank" href="trip.php?go=trip&id='.$bbsp['trip'].'">'.$bbsp['trip'].'</a>';
				}
				$bbsp['mess'] = StrToHTML($bbsp['mess']);
				$bbsp['time'] = gmdate("Y-m-d H:i:s",$bbsp['time']+$time_zone);
				echo '<li><span class="view_title_re">'.$bbsp['name'].'</span></li>
					<li><span class="view_mess_re">'.$bbsp['mess'].'</span></li>
					<li><span class="view_b_re"><a href = "bbs.php?go=edit&id='.$bbsp['id'].'">NO.'.$bbsp['id'].'</a> &lt;..&gt; ['.$bbsp['time'].']</span></li><br>';
			}
		}
		echo '</ul></div>';
}

if ($_GET['go'] == 'edit' && preg_match("/^(\\d+)\$/",$G_id)) {
	if ($_POST['submit']) {
		if ($_POST['password'] == $system_password) {
			$query = $db->query("select id,reid,title,password,mess from bbs where id='".$G_id."'");
		} else {
			$query = $db->query("select id,reid,title,password,mess from bbs where id='".$G_id."' and password = '".md5((string) $_POST['password'])."'");
		}
		if (!$bbsedit = $db->fetch_array($query)) {
			footer('密碼錯誤?');
		}
		switch ($_POST['editis']) {
			case('edit'):
				echo '<form name="bbs" action="bbs.php?go=edit&id='.$G_id.'" method="post" enctype="multipart/form-data">';
				if ($bbsedit[\TITLE]) {
					echo '<input type="hidden" name="bbst" value="1">
						<li>標題 <input type="text" name="title" size="24" value="'.$bbsedit[\TITLE].'" /></li>';
				}
				echo '<li>內容<br /><textarea name="mess" rows="5" cols="64">'.$bbsedit[\MESS].'</textarea></li>
					<input type="hidden" name="password" value="'.$_POST['password'].'">
					<input type="hidden" name="editis" value="editok">
					<li><input id="submit" name="submit" type="submit" value="編輯" /></li>
					</form>';
				footer(' ');
				break;
			case('editok'):
				if ((empty($title) && $_POST['bbst']) || empty($_POST['mess'])) {
					footer('缺少資料或是有遺漏');
				}
				if (strlenu($title) > 50 && $_POST['bbst']) {
					footer('限制50字元');
				}
				if (strlenu($_POST['mess']) > 5000) {
					footer('限制5000字元');
				}
				$db->query("UPDATE bbs SET title = '".StrToSQL($title)."',mess = '".StrToSQL($_POST['mess'])."'
							 where id='".$bbsedit['id']."';");
				break;
			case('del'):
				if ($bbsedit['reid']) {
					$query = $db->query("select id from bbs where id='".$bbsedit['reid']."'");
					$bbsdel = $db->fetch_array($query);
					$db->query("UPDATE bbs SET recount = recount-1 where id = '".$bbsdel['id']."';");
				}
				$db->query("delete from bbs where id='".$bbsedit['id']."' LIMIT 1;");
				break;
			case('tolock'):
				if ($_POST['password'] != $system_password) {
					footer('無法使用');
				}
				$db->query("UPDATE bbs SET locked = '1' where id = '".$bbsedit['id']."';");
				break;
			case('totop'):
				if ($_POST['password'] != $system_password) {
					footer('無法使用');
				}
				$db->query("UPDATE bbs SET toped = '1' where id = '".$bbsedit['id']."';");
				break;
			case('nolock'):
				if ($_POST['password'] != $system_password) {
					footer('無法使用');
				}
				$db->query("UPDATE bbs SET locked = '0' where id = '".$bbsedit['id']."';");
				break;
			case('notop'):
				if ($_POST['password'] != $system_password) {
					footer('無法使用');
				}
				$db->query("UPDATE bbs SET toped = '0' where id = '".$bbsedit['id']."';");
				break;
			case('todige'):
				if ($_POST['password'] != $system_password) {
					footer('無法使用');
				}
				$db->query("UPDATE bbs SET dige = '1' where id = '".$bbsedit['id']."';");
				break;
			case('nodige'):
				if ($_POST['password'] != $system_password) {
					footer('無法使用');
				}
				$db->query("UPDATE bbs SET dige = '0' where id = '".$bbsedit['id']."';");
				break;
			default:
				footer('錯誤的操作');
				break;
		}
		bheader("Location:bbs.php");
	}
	echo '<form name="bbs" action="bbs.php?go=edit&id='.$G_id.'" method="post" enctype="multipart/form-data">';
	echo "您對文章編號".$G_id."進行管理，請選擇項目 <br />";
	echo '<select name="editis">
		<option value="del">刪除</option>
		<option value="edit" selected>編輯</option>
		<option value="tolock">鎖定</option>
		<option value="totop">置頂</option>
		<option value="nolock">解鎖定</option>
		<option value="notop">解置頂</option>
		<option value="todige">加精華</option>
		<option value="nodige">解精華</option>
		</select>
		密碼 <input type="password" name="password" size="24" value="" /> <br />
		<input id="submit" name="submit" type="submit" value="送出" />
		</form>';
	echo "<br />一般使用者只能刪除與編輯";
}

if ($_GET['go'] == 'dige') {
	$query = $db->query("select count(*) from bbs where reid = '0' and dige = '1'");
	$bbsnumq = $db->result($query, 0);
	$page = (int)$_GET['page'];
	$page = max(1, $page);
	$start = ($page - 1) * $BBSnum;
	echo '<center>'.multi($bbsnumq, $BBSnum, $page,'bbs.php?go=dige').'</center>';
	echo '<div class="table1" style="padding:0px 20px;">
		<ul><li>
			<span class="title" style="width:50px;"> No.</span>
			<span class="title" style="width:300px;">標題</span>
			<span class="title" style="width:150px">作者</span>
			<span class="title" style="width:40px">回覆</span>
			<span class="title" style="width:150px">最後時間</span>
		</li>';

	if ($bbsnumq) {
		$query = $db->query("select id,title,name,trip,retime,locked,recount,dige from bbs where reid = '0' and dige = '1' order by retime desc LIMIT $start,".$BBSnum.";");
		while ($list = $db->fetch_array($query)) {
				$list['retime'] = gmdate("Y-m-d H:i:s",$list['retime']+$time_zone);
				if ($list['trip']) {
					$list['name'] .= '◆<a target="_blank" href="trip.php?go=trip&id='.$list['trip'].'">'.$list['trip'].'</a>';
				}
				$list['locked'] = $list['locked'] ? ' (鎖定)' : '';
				echo "<li>\n".
					"<span class=\"dlist\" style=\"width:50px\"><a href=\"bbs.php?view=$list[id]\">$list[id]</a></span>\n".
					"<span class=\"dlist2\" style=\"width:300px\">$list[locked]<a href=\"bbs.php?view=$list[id]\">$list[title]</a>$list[locked]</span>\n".
					"<span class=\"dlist\" style=\"width:150px\">$list[name]</span>\n".
					"<span class=\"dlist\" style=\"width:40px\">$list[recount]</span>\n".
					"<span class=\"dlist\" style=\"width:150px\">$list[retime]</span>\n".
				"</li>";
		}
	} else {
		echo "<<li>><span>沒有主題</span></li>\n";
	}
	echo '</ul></div>';
}

if (empty($G_view) && empty($_GET['go']) || (!preg_match("/^(\\d+)\$/",$G_view) && empty($_GET['go']))) {
	$query = $db->query("select count(*) from bbs where reid = '0'");
	$bbsnumq = $db->result($query, 0);
	$page = (int)$_GET['page'];
	$page = max(1, $page);
	$start = ($page - 1) * $BBSnum;
	echo '<center>'.multi($bbsnumq, $BBSnum, $page,'bbs.php').'</center>';
	echo '<div class="table1" style="padding:0px 20px;">
		<ul><li>
			<span class="title" style="width:50px;"> No.</span>
			<span class="title" style="width:300px;">標題</span>
			<span class="title" style="width:150px">作者</span>
			<span class="title" style="width:40px">回覆</span>
			<span class="title" style="width:150px">最後時間</span>
		</li>';

	if ($bbsnumq) {
		$query = $db->query("select id,title,name,trip,retime,toped,locked,recount,dige from bbs where reid = '0' order by toped desc,retime desc LIMIT $start,".$BBSnum.";");
		while ($list = $db->fetch_array($query)) {
				$list['ren'] = max(ceil($list['recount'] / $BBSnumre), 1);
				if ($list['ren'] < 1) $list['page_ren'] = 1;
				
				$list['retime'] = gmdate("Y-m-d H:i:s",$list['retime']+$time_zone);
				if ($list['trip']) {
					$list['name'] .= '◆<a target="_blank" href="trip.php?go=trip&id='.$list['trip'].'">'.$list['trip'].'</a>';
				}
				$list['toped'] = $list['toped'] ? '[頂] ' : '';
				$list['locked'] = $list['locked'] ? ' (鎖)' : '';
				$list['dige'] = $list['dige'] ? ' (精)' : '';
				echo "<li>\n".
					"<span class=\"dlist\" style=\"width:50px\"><a href=\"bbs.php?view=$list[id]\">$list[id]</a></span>\n".
					"<span class=\"dlist2\" style=\"width:300px\">$list[toped]<a href=\"bbs.php?view=$list[id]&page=$list[ren]\">$list[title]</a>$list[locked]$list[dige]</span>\n".
					"<span class=\"dlist\" style=\"width:150px\">$list[name]</span>\n".
					"<span class=\"dlist\" style=\"width:40px\">$list[recount]</span>\n".
					"<span class=\"dlist\" style=\"width:150px\">$list[retime]</span>\n".
				"</li>";
		}
	} else {
		echo "<<li>><span>沒有主題</span></li>\n";
	}
	echo '</ul></div>';
}

footer();
?>
