<?php
require_once __DIR__ . '/game_functions.php';
$goid = 1;

//$result = $db->query("SELECT option_role,victory_role,max_user from room where room_no >= $goid AND max_user >= '16' AND status = 'finished' AND day_night = 'aftergame' AND victory_role != '';");
$result = $db->query("SELECT option_role,victory_role,max_user from room where max_user >= '16' AND status = 'finished' AND day_night = 'aftergame' AND victory_role > '' ORDER BY room_no DESC LIMIT 10000;");
//初始
$numall2 = 0;
//無毒+背德+子狐
$nobepish2 = $nobepisw2 = $nobepisf2 = $nobepiso2 = $nobepisl2 = 0;
//純毒
$pish2 = $pisw2 = $pisf2 = $piso2 = $pisl2 = 0;
//純背
$besh2 = $besw2 = $besf2 = $beso2 = $besl2 = 0;
//子狐
$fosh2 = $fosw2 = $fosf2 = $foso2 = $fosl2 = 0;
//毒+背德
$bepish2 = $bepisw2 = $bepisf2 = $bepiso2 = $bepisl2 = 0;
//毒+子狐
$fopish2 = $fopisw2 = $fopisf2 = $fopiso2 = $fopisl2 = 0;
//初始
$numall3 = 0;
//無毒+背德+子狐
$nobepish3 = $nobepisw3 = $nobepisf3 = $nobepiso3 = $nobepisl3 = 0;
//純毒
$pish3 = $pisw3 = $pisf3 = $piso3 = $pisl3 = 0;
//純背
$besh3 = $besw3 = $besf3 = $beso3 = $besl3 = 0;
//子狐
$fosh3 = $fosw3 = $fosf3 = $foso3 = $fosl3 = 0;
//毒+背德
$bepish3 = $bepisw3 = $bepisf3 = $bepiso3 = $bepisl3 = 0;
//毒+子狐
$fopish3 = $fopisw3 = $fopisf3 = $fopiso3 = $fopisl3 = 0;
//16初始
$numall4 = 0;
//無毒+背德+子狐
$nobepish4 = $nobepisw4 = $nobepisf4 = $nobepiso4 = $nobepisl4 = 0;
//純毒
$pish4 = $pisw4 = $pisf4 = $piso4 = $pisl4 = 0;
//純背
$besh4 = $besw4 = $besf4 = $beso4 = $besl4 = 0;
//子狐
$fosh4 = $fosw4 = $fosf4 = $foso4 = $fosl4 = 0;
//毒+背德
$bepish4 = $bepisw4 = $bepisf4 = $bepiso4 = $bepisl4 = 0;
//毒+子狐
$fopish4 = $fopisw4 = $fopisf4 = $fopiso4 = $fopisl4 = 0;

while($row = $db->fetch_array($result)) {
	if ($row['max_user'] == '16') {
		$numall4++;
		if ($row['victory_role'] == 'human') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepish4++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pish4++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besh4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosh4++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepish4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopish4++;
			}
		}
		if ($row['victory_role'] == 'wolf') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisw4++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisw4++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besw4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosw4++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisw4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisw4++;
			}
		}
		if (strstr((string) $row['victory_role'],"fox")) {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisf4++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisf4++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besf4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosf4++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisf4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisf4++;
			}
		}
		if (strstr((string) $row['victory_role'],"lover")) {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisl4++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisl4++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besl4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosl4++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisl4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisl4++;
			}
		}
		if ($row['victory_role'] == 'draw') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepiso4++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$piso4++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$beso4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$foso4++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepiso4++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopiso4++;
			}
		}
	}
	if ($row['max_user'] == '22') {
		$numall2++;
		if ($row['victory_role'] == 'human') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepish2++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pish2++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besh2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosh2++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepish2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopish2++;
			}
		}
		if ($row['victory_role'] == 'wolf') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisw2++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisw2++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besw2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosw2++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisw2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisw2++;
			}
		}
		if (strstr((string) $row['victory_role'],"fox")) {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisf2++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisf2++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besf2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosf2++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisf2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisf2++;
			}
		}
		if (strstr((string) $row['victory_role'],"lover")) {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisl2++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisl2++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besl2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosl2++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisl2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisl2++;
			}
		}
		if ($row['victory_role'] == 'draw') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepiso2++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$piso2++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$beso2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$foso2++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepiso2++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopiso2++;
			}
		}
	}

	if ($row['max_user'] == '30') {
		$numall3++;
		if ($row['victory_role'] == 'human') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepish3++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pish3++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besh3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosh3++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepish3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopish3++;
			}
		}
		if ($row['victory_role'] == 'wolf') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisw3++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisw3++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besw3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosw3++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisw3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisw3++;
			}
		}
		if (strstr((string) $row['victory_role'],"fox")) {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisf3++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisf3++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besf3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosf3++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisf3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisf3++;
			}
		}
		if (strstr((string) $row['victory_role'],"lover")) {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepisl3++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$pisl3++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$besl3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$fosl3++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepisl3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopisl3++;
			}
		}
		if ($row['victory_role'] == 'draw') {
			if (!strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$nobepiso3++;
			}
			if (!strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison") && !strstr((string) $row['option_role'],"fosi")) {
				$piso3++;
			}
			if (strstr((string) $row['option_role'],"betr") && !strstr((string) $row['option_role'],"poison")) {
				$beso3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && !strstr((string) $row['option_role'],"poison")) {
				$foso3++;
			}
			if (strstr((string) $row['option_role'],"betr") && strstr((string) $row['option_role'],"poison")) {
				$bepiso3++;
			}
			if (strstr((string) $row['option_role'],"fosi") && strstr((string) $row['option_role'],"poison")) {
				$fopiso3++;
			}
		}
	}
};

$ptitle = "勝率分析 - ";
include_once __DIR__ . "/header.inc.php";

echo '<div id="indexhi"><div id="indexh2"><b>'.$server_comment.'</b></div>';
echo '<fieldset>';
echo '<legend><b>勝率分析</b></legend>';

//人
$numh = $nobepish4+$pish4+$besh4+$fosh4+$bepish4+$fopish4;
$hh = round($numh / $numall4 * 100,2);
//狼
$numw = $nobepisw4+$pisw4+$besw4+$fosw4+$bepisw4+$fopisw4;
$ww = round($numw / $numall4 * 100,2);
//狐
$numf = $nobepisf4+$pisf4+$besf4+$fosf4+$bepisf4+$fopisf4;
$ff = round($numf / $numall4 * 100,2);
//戀
$numl = $nobepisl4+$pisl4+$besl4+$fosl4+$bepisl4+$fopisl4;
$ll = round($numl / $numall4 * 100,2);
//合
$numo = $nobepiso4+$piso4+$beso4+$foso4+$bepiso4+$fopiso4;
$oo = round($numo / $numall4 * 100,2);
echo "以下統計是過去 10000 場的場勝利分析，不包含廢村。<br />分別為16與22與30人場統計<br /><br />";
echo "【16人場】<br />
－人勝－（$numh / $numall4 ：勝率 $hh %）<br />
無毒背子：$nobepish4 <br />
  純毒：$pish4 <br />
  純背：$besh4 <br />
  純子：$fosh4 <br />
  毒背：$bepish4 <br />
  毒子：$fopish4 <br />
<br />
－狼勝－（$numw / $numall4 ：勝率 $ww %）<br />
無毒背子：$nobepisw4 <br />
  純毒：$pisw4 <br />
  純背：$besw4 <br />
  純子：$fosw4 <br />
  毒背：$bepisw4 <br />
  毒子：$fopisw4 <br />
<br />
－狐勝－（$numf / $numall4 ：勝率 $ff %）<br />
無毒背子：$nobepisf4 <br />
  純毒：$pisf4 <br />
  純背：$besf4 <br />
  純子：$fosf4 <br />
  毒背：$bepisf4 <br />
  毒子：$fopisf4 <br />
<br />
－戀勝－（$numl / $numall4 ：勝率 $ll %）<br />
無毒背子：$nobepisl4 <br />
  純毒：$pisl4 <br />
  純背：$besl4 <br />
  純子：$fosl4 <br />
  毒背：$bepisl4 <br />
  毒子：$fopisl4 <br />
<br />
－和－（$numo / $numall4 ：勝率 $oo %）<br />
無毒背子：$nobepiso4 <br />
  純毒：$piso4 <br />
  純背：$beso4 <br />
  純子：$foso4 <br />
  毒背：$bepiso4 <br />
  毒子：$fopiso4 <br />";

//人
$numh = $nobepish2+$pish2+$besh2+$fosh2+$bepish2+$fopish2;
$hh = round($numh / $numall2 * 100,2);
//狼
$numw = $nobepisw2+$pisw2+$besw2+$fosw2+$bepisw2+$fopisw2;
$ww = round($numw / $numall2 * 100,2);
//狐
$numf = $nobepisf2+$pisf2+$besf2+$fosf2+$bepisf2+$fopisf2;
$ff = round($numf / $numall2 * 100,2);
//戀
$numl = $nobepisl2+$pisl2+$besl2+$fosl2+$bepisl2+$fopisl2;
$ll = round($numl / $numall2 * 100,2);
//合
$numo = $nobepiso2+$piso2+$beso2+$foso2+$bepiso2+$fopiso2;
$oo = round($numo / $numall2 * 100,2);
echo "<br /><br />";
echo "【22人場】<br />
－人勝－（$numh / $numall2 ：勝率 $hh %）<br />
無毒背子：$nobepish2 <br />
  純毒：$pish2 <br />
  純背：$besh2 <br />
  純子：$fosh2 <br />
  毒背：$bepish2 <br />
  毒子：$fopish2 <br />
<br />
－狼勝－（$numw / $numall2 ：勝率 $ww %）<br />
無毒背子：$nobepisw2 <br />
  純毒：$pisw2 <br />
  純背：$besw2 <br />
  純子：$fosw2 <br />
  毒背：$bepisw2 <br />
  毒子：$fopisw2 <br />
<br />
－狐勝－（$numf / $numall2 ：勝率 $ff %）<br />
無毒背子：$nobepisf2 <br />
  純毒：$pisf2 <br />
  純背：$besf2 <br />
  純子：$fosf2 <br />
  毒背：$bepisf2 <br />
  毒子：$fopisf2 <br />
<br />
－戀勝－（$numl / $numall2 ：勝率 $ll %）<br />
無毒背子：$nobepisl2 <br />
  純毒：$pisl2 <br />
  純背：$besl2 <br />
  純子：$fosl2 <br />
  毒背：$bepisl2 <br />
  毒子：$fopisl2 <br />
<br />
－和－（$numo / $numall2 ：勝率 $oo %）<br />
無毒背子：$nobepiso2 <br />
  純毒：$piso2 <br />
  純背：$beso2 <br />
  純子：$foso2 <br />
  毒背：$bepiso2 <br />
  毒子：$fopiso2 <br />";
//人
$numh = $nobepish3+$pish3+$besh3+$fosh3+$bepish3+$fopish3;
$hh = round($numh / $numall3 * 100,2);
//狼
$numw = $nobepisw3+$pisw3+$besw3+$fosw3+$bepisw3+$fopisw3;
$ww = round($numw / $numall3 * 100,2);
//狐
$numf = $nobepisf3+$pisf3+$besf3+$fosf3+$bepisf3+$fopisf3;
$ff = round($numf / $numall3 * 100,2);
//戀
$numl = $nobepisl3+$pisl3+$besl3+$fosl3+$bepisl3+$fopisl3;
$ll = round($numl / $numall3 * 100,2);
//合
$numo = $nobepiso3+$piso3+$beso3+$foso3+$bepiso3+$fopiso3;
$oo = round($numo / $numall3 * 100,2);
echo "<br /><br />";
echo "【30人場】<br />
－人勝－（$numh / $numall3 ：勝率 $hh %）<br />
無毒背子：$nobepish3 <br />
  純毒：$pish3 <br />
  純背：$besh3 <br />
  純子：$fosh3 <br />
  毒背：$bepish3 <br />
  毒子：$fopish3 <br />
<br />
－狼勝－（$numw / $numall3 ：勝率 $ww %）<br />
無毒背子：$nobepisw3 <br />
  純毒：$pisw3 <br />
  純背：$besw3 <br />
  純子：$fosw3 <br />
  毒背：$bepisw3 <br />
  毒子：$fopisw3 <br />
<br />
－狐勝－（$numf / $numall3 ：勝率 $ff %）<br />
無毒背子：$nobepisf3 <br />
  純毒：$pisf3 <br />
  純背：$besf3 <br />
  純子：$fosf3 <br />
  毒背：$bepisf3 <br />
  毒子：$fopisf3 <br />
<br />
－戀勝－（$numl / $numall3 ：勝率 $ll %）<br />
無毒背子：$nobepisl3 <br />
  純毒：$pisl3 <br />
  純背：$besl3 <br />
  純子：$fosl3 <br />
  毒背：$bepisl3 <br />
  毒子：$fopisl3 <br />
<br />
－和－（$numo / $numall3 ：勝率 $oo %）<br />
無毒背子：$nobepiso3 <br />
  純毒：$piso3 <br />
  純背：$beso3 <br />
  純子：$foso3 <br />
  毒背：$bepiso3 <br />
  毒子：$fopiso3 <br />";

echo '</fieldset></div>';
include_once __DIR__ . "/footer.inc.php";
?>
