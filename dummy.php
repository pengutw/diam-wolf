<?php

$m_lastws = [
	"糟糕，我好像是占耶XD\nD1 我自己 人\nD2 被速咬了",
];

$n_lastws = [
	"渣靈，科科\nD1 沒人被吊，可喜可樂",
];

$lastws = [
	"您所扮演的角色是村民\n小卒一個XD",
	"(沾滿血的信紙寫著)\n當海貓鳴泣之時，工於心計的人類將會死在自己同伴的手上,吾等睿智的狼將會稱王",
	"(日記的一頁)\n戰人這傢伙晚上突然說有事找我，回來再把日記補齊",
	"(日記的一頁)\n醫生晚上突然說有事找我，回來再把日記補齊",
	"(日記的一頁)\n教授晚上突然說有事找我，回來再把日記補齊",
	"我隨風而來，隨風而去…\n所以又死了"
];
				
$clastws = count($lastws) -1;
$rlastws = mt_rand(0,$clastws);
$lastws = $lastws[$rlastws];

$cmlastws = count($m_lastws) -1;
$rmlastws = mt_rand(0,$cmlastws);
$mlastws = $m_lastws[$rmlastws];

$cnlastws = count($n_lastws) -1;
$rnlastws = mt_rand(0,$cnlastws);
$nlastws = $n_lastws[$rnlastws];

?>
