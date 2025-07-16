<?php
require_once __DIR__ . '/game_functions.php';

$ptitle = "表情符號 - ";
include_once __DIR__ . "/header.inc.php";

echo '<div id="indexhi"><div id="indexh2"><b>'.$server_comment.'</b></div>';
echo '<fieldset>';
echo '<legend><b>表情符號</b></legend>';
echo '<div class="table1" style="padding:0px 100px;">
	<ul><li>
		<span class="title" style="width:50px;">編號</span>
		<span class="title" style="width:420px;">圖形</span>
		<span class="title" style="width:70px">代號</span>
	</li>';

$Snum = count($demota);

if ($Snum !== 0) {
	for ($i = 1;$i <= $Snum;$i++) {
		echo "<li>\n".
			 "<span class=\"dlist\" style=\"width:50px\">$i</span>\n".
			 "<span class=\"dlist\" style=\"width:420px\">".$demotb[$i-1]."</span>\n";
		echo "<span class=\"dlist\" style=\"width:70px\">".$demota[$i-1]."</span>\n";
	}
} else {
	echo "<<li>><span>沒有資料</span></li>\n";
}
echo '</ul></div>';

echo '</fieldset></div>';
include_once __DIR__ . "/footer.inc.php";
?>
