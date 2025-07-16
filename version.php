<?php
require_once __DIR__ . '/game_functions.php';

$ptitle = "版本記錄 - ";
include_once __DIR__ . "/header.inc.php";

echo '<div id="indexhi"><div id="indexh2"><b>'.$server_comment.'</b></div>';
echo '<fieldset>';
echo '<legend><b>版本記錄</b></legend>';

if(file_exists('lang/'.$language.'/version.htm')) {
	include 'lang/'.$language.'/version.htm';
} else {
	return 'lang/cht/version.htm';
}

echo '</fieldset></div>';
include_once __DIR__ . "/footer.inc.php";
?>
