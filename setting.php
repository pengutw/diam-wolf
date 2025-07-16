<?php
//ob_start('ob_gzhandler');
define('IN_JINRO', TRUE);
//gc_disable();
//error_reporting(7);
//データベースサーバのホスト名 hostname:port
//ポート番号を省略するとデフォルトポートがセットされます。(MySQL:3306)
$db_host = 'localhost';

//データベースの用戶名
$db_uname = '';

//データベースサーバのパスワード
$db_pass = '';

//データベース名
$db_name = '';

//管理者用パスワード
$system_password = '';

//サーバのコメント
$server_comment = '人狼伺服器';
$domain_name = "diam.ngct.net";

//戻り先のページ
$back_page = '';

//伺服器公告來源檔名
$announcement='announcement.txt';

//快取檔案開關 0關 1開
$cachefile = 0;

//資料庫連線類型 mysql mysqli
$database = 'mysqli';

//替身君的TRIP
$tripkey = "DETfcflZU6";

//key 16bit for 傳遞用
$authkey = '1qaz2wsx3edc4rfv';

//替身君的圖片ID
$dummy_boy_imgid = 1051;

//最低評價要求
$triptonum = 20;

$upiconok = 0;

//編號多少之前村轉移
$roomidserint = 0;

//紀錄刷新限制時間
$youserint_sep = "30";

//tripicon
$trip_icon_dir = "trip_icon";
$trip_icon_up = "trip_icon";

//***********************************************************
//聯合伺服器列表
//***********************************************************

//自己的簡易名稱, 跟完整網址連結
$room_server_me=['DIAM','https://diam.ngct.net/'];
//伺服器列表, 前面是HOST, 後面是子目錄, 注意有子目錄的話前面要加"/"
$room_server_list=[
 ['diam.ngct.net',''],
 /*Array('wolf.liouming.net',''),*/
 ['higurashi.nctucs.org',''],
 ['jigokutushin.net','/jinro/']
];

//***********************************************************
//地區設定
//***********************************************************
date_default_timezone_set("Asia/Taipei");
$time_zone=8; //時區: +8
$language='cht'; //語系

function GetLangSet(string $filename): string {
	global $language;
	if(file_exists('lang/'.$language.'/'.$filename)) {
		return 'lang/'.$language.'/'.$filename;
	} else {
		return 'lang/jpn/'.$filename; //預設語系
	}
}
require_once __DIR__ . '/lang/jpn/index.php';
require_once 'lang/'.$language.'/index.php';
$time_zone *= 3600;

//***********************************************************
//製作/管理者訊息
//***********************************************************

$admin_name="Anfauglir"; //本站管理者名稱
$admin_link="index.php"; //管理者主頁連結,或是E-mail連結, 或是指定index.php回到首頁

//***********************************************************
//系統選項
//***********************************************************

$sys_create_room_enabled=true; //允許開新村

$sys_create_room_max=3; //允許同時進行的村子的最大數目

//***********************************************************
//ココから高度な設定
//***********************************************************

$regist_one_ip_address = true; //住人登錄でひとつの部屋に同じIPアドレスで複数登錄できなくする できない:true できる:false

$die_room_threshhold_time = 600; //部屋最後の会話から廃村になるまで的時間(あまり短くすると沈黙等と競合する可能性あり)

$clear_user_session_id = 1200; //終了した部屋の用戶のSessionIDデータをクリアするまで的時間

$suddendeath_threshhold_time = 120; //日没、夜明け残り時間ゼロでこの閾値を過ぎると投票していない人は突然死します(秒)

$silence_threshhold_time = 60; //非限制時間でこの閾値を過ぎると沈黙となり、設定した時間が進みます(秒)
//(沈黙経過時間) 12時間÷$day_limit_time(白) もしくは 6時間÷$night_limit_time(夜) の$silence_pass_time倍的時間が進みます
$silence_pass_time = 4;

$day_limit_time = 48; //白の制限時間(白は12時間、spend_time=1(半形100文字以内) で 12時間÷$day_limit_time進みます)
$night_limit_time = 24; //夜の制限時間(夜は6時間、spend_time=1(半形100文字以内) で 6時間÷$night_limit_time進みます)

$day_real_limit_time_min = 4; //デフォルトの限制時間的場合の白の制限時間(分)
$night_real_limit_time_min = 3; //デフォルトの限制時間的場合の夜の制限時間(分)

$revote_draw_times = 5; //再投票何回目で平手とするか

$waiting_image = 'img/waiting.webp'; //村リストの募集中の画像
$playing_image = 'img/playing.webp'; //村リストのゲーム中の画像

$room_option_wish_role_image = 'img/room_option_wish_role.webp'; //村のオプションの希望角色の画像
$room_option_real_time_image = 'img/room_option_real_time.webp'; //村のオプションの希望角色の画像
$room_option_dummy_boy_image = 'img/room_option_dummy_boy.webp'; //村のオプションの替身君使用の画像
$room_option_open_vote_image = 'img/room_option_open_vote.webp'; //村のオプションの票数公開の画像
$room_option_decide_image = 'img/room_option_decide.webp'; //村のオプションの決定者の画像
$room_option_authority_image = 'img/room_option_authority.webp'; //村のオプションの權力者の画像
$room_option_betr_image = 'img/room_option_betr.webp'; // 背德
$room_option_fosi_image = 'img/room_option_fosi.webp'; // 子狐
$room_option_foxs_image = 'img/room_option_foxs.webp'; // 雙狐
$room_option_poison_image = 'img/room_option_poison.webp'; //村のオプションの埋毒者の画像
$room_option_rei_image = 'img/rei.webp';
$room_option_conn_image = 'img/conn_look.webp';
$room_option_wfbig_image = 'img/room_option_wfbig.webp';
$room_option_cat_image = 'img/room_option_cat.webp';
$room_option_voteme_image = 'img/room_option_voteme.webp';
$room_option_trip_image = 'img/room_option_trip.webp';
$room_option_will_image = 'img/room_option_will.webp';
$room_option_lovers_image = 'img/room_option_lovers.webp';
$room_option_gm_image = 'img/room_option_gm.webp';
$room_option_mytho_image = 'img/room_option_mytho.webp';
$room_option_owlman_image = 'img/room_option_owlman.webp';
$room_option_noconn_image = 'img/room_option_noconn.webp';
$room_option_pengu_image = 'img/room_option_pengu.webp';
$room_option_noble_image = 'img/room_option_noble.webp';
$room_option_spy_image = 'img/room_option_spy.webp';
$room_option_guest_image = 'img/room_option_guest.webp';
$room_option_ischat_image = 'img/room_option_ischat_image.webp';


/* IS_WHAT */
$role_result_human_image = 'img/role_result_human.webp'; //占い師、靈能者の結果、村民の画像
$role_result_wolf_image = 'img/role_result_wolf.webp'; //占い師、靈能者の結果、人狼の画像
$role_nom_fosi_image = 'img/role_fosi_magefailed2.webp'; //子狐占失敗
$role_result_fosi_image = 'img/role_result_fosi.webp'; //是子狐
$role_result_wfbig_image = 'img/role_result_heavywolf.webp'; //是大狼

/* Act I: 人狼現身 */
$role_human_image = 'img/role_human.webp'; //村民の説明の画像
$role_wolf_image = 'img/role_wolf.webp'; //人狼の説明の画像
$role_wolf_partner_image = 'img/role_wolf_partner.webp'; //人狼の仲間表示の画像
$role_mage_image = 'img/role_mage.webp'; //占い師の説明の画像
$role_mage_result_image = 'img/role_mage_result.webp'; //占い師の結果の画像
$role_necromancer_image = 'img/role_necromancer.webp'; //靈能者の説明の画像
$role_necromancer_result_image = 'img/role_necromancer_result.webp'; //靈能者の結果の画像
$role_mad_image = 'img/role_mad.webp'; //狂人の説明の画像
$role_guard_image = 'img/role_guard.webp'; //獵人の説明の画像
$role_guard_success_image = 'img/role_guard_success.webp'; //獵人の護衛成功の画像
$role_common_image = 'img/role_common.webp'; //共有者の説明の画像
$role_common_partner_image = 'img/role_common_partner.webp'; //共有者の仲間表示の画像
$role_fox_image = 'img/role_fox.webp'; //妖狐の説明の画像
$role_fox_partner_image = 'img/role_fox_partner.webp';

/* Act II: 妖狐信者 */
$role_betr_image = 'img/role_cult.webp';
$role_betr_partner_image = 'img/role_cult_partner.webp';
$role_fox_target_image = 'img/role_fox_targeted.webp'; //妖狐が狙われた画像像
$role_poison_image = 'img/role_poison.webp'; //妖狐の説明の画像像
$role_authority_image = 'img/role_authority.webp'; //妖狐の説明の画像

/* Act III: 狐族末裔，狼族好漢 */
$role_fosi_image = 'img/role_fosi.webp'; //子狐角色說明
$role_wfbig_image = 'img/role_heavywolf.webp';

/* Act IV: 貓的報恩 */
$role_cat_image = 'img/role_cat.webp'; //貓又說明

/* Act V: 真愛不滅 */
$role_lovers_image = 'img/role_lovers_image.webp'; 
$role_lovers_partner_image = 'img/role_lovers_partner.webp'; 

/* Act VI: 新統治者，降臨 */
$role_GM_image = 'img/role_GM.webp'; //GM說明

/* Act VII: 模仿與詛咒，都很危險 */
$role_mytho_image = 'img/role_mytho.webp';
$role_owlman_image = 'img/role_owlman.webp';

/* Act VIII: 企鵝鍋搔癢派對！ */
$role_pengu_image = 'img/role_pengu.webp'; //企鵝湯底說明

/* Act IX: 貴族與奴隸，壓迫者與被壓迫者 */
$role_noble_image = 'img/role_noble.webp';
$role_slave_image = 'img/role_slave.webp';
$role_slave_partner_image = 'img/role_slave_partner.webp';

/* Act X: 狼族的殘片，潛藏於血脈中的力量 */
$role_wfwtr_image = 'img/role_wtrwolf.webp';
$role_wfasm_image = 'img/role_asmwolf.webp';
$role_wfbsk_image = 'img/role_bskwolf.webp';
/* Act X-2: 狼族的殘片，若者後至 */
$role_wfwnd_image = 'img/role_wndwolf.webp';
$role_wfwnd_final_image = 'img/role_wndwolf_final.webp';
$role_wfxwnd_image = 'img/role_xwndwolf.webp';

/* Act XI: 夜黑風高，間諜疾行 */
$role_spy_image = 'img/role_spy.webp';
$role_spy_wolf_partner_image = 'img/role_spy_wolf_partner.webp';

/* Act XII: ？ */

$victory_role_human_image = 'img/victory_role_human.webp'; //勝利チームの画像 村民
$victory_role_wolf_image = 'img/victory_role_wolf.webp'; //勝利チームの画像 人狼
$victory_role_fox_image = 'img/victory_role_fox.webp'; //勝利チームの画像 妖狐
$victory_role_draw_image = 'img/victory_role_draw.webp'; //勝利チームの画像 平手
$victory_role_lovers_image = 'img/victory_role_lovers.webp';
$victory_role_custom_image = 'img/victory_role_custom.webp';

//$morning_swf = 'swf/sound_morning.swf'; //音でお知らせ(夜明け)
//$revote_swf = 'swf/sound_revote.swf'; //音でお知らせ(再投票)
//$objection_male_swf = 'swf/sound_objection_male.swf'; //音でお知らせ(意義あり、男)
//$objection_female_swf = 'swf/sound_objection_female.swf'; //音でお知らせ(意義あり、女)
$morning_mp3 = 'mp3/sound_morning.mp3'; //音でお知らせ(夜明け)
$revote_mp3 = 'mp3/sound_revote.mp3'; //音でお知らせ(再投票)
$objection_male_mp3 = 'mp3/sound_objection_male.mp3'; //音でお知らせ(意義あり、男)
$objection_female_mp3 = 'mp3/sound_objection_female.mp3'; //音でお知らせ(意義あり、女)

$objection_image = 'img/objection.webp'; //意義ありボタンの画像
$endroom_image = 'img/endroom.webp'; //意義ありボタンの画像
$maxcount_objection = 2; //意義ありの最大回数

$maxuser_array = [8,16,22,30]; //最大人数のリスト

//最大人数の画像リスト
$maxuser_image_array = [8 => 'img/max8.webp'  //村リストの最大人数8人の画像
							, 16 => 'img/max16.webp'  //村リストの最大人数16人の画像
							, 22 => 'img/max22.webp'
							, 30 => 'img/max30.webp'
							, 50 => 'img/max50.webp'];  //村リストの最大人数22人の画像

//開始時の役割リスト・決定者、權力者、埋毒者オプションがあるときは先頭の方から上書きされます
$role_list = [
	 8 => ['human','human','human','human','human','wolf','wolf','mage'] ,
	 9 => ['human','human','human','human','human','wolf','wolf','mage','necromancer'] ,
	10 => ['human','human','human','human','human','wolf','wolf','mage','necromancer','mad'] ,
	11 => ['human','human','human','human','human','wolf','wolf','mage','necromancer','mad','guard'] ,
	12 => ['human','human','human','human','human','human','wolf','wolf','mage','necromancer','mad','guard'] ,
	13 => ['human','human','human','human','human','wolf','wolf','mage','necromancer','mad','guard','common','common'] ,
	14 => ['human','human','human','human','human','human','wolf','wolf','mage','necromancer','mad','guard','common','common'] ,
	15 => ['human','human','human','human','human','human','wolf','wolf','mage','necromancer','mad','guard','common','common','fox'] ,
	16 => ['human','human','human','human','human','human','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common','fox'] ,
	17 => ['human','human','human','human','human','human','human','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common','fox'] ,
	18 => ['human','human','human','human','human','human','human','human','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common','fox'] ,
	19 => ['human','human','human','human','human','human','human','human','human','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common','fox'] ,
	20 => ['human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common'] ,
	21 => ['human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common'] ,
	22 => ['human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common'],
	23 => ['human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common'],
	24 => ['human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','wolf','wolf','mage','necromancer','mad','guard','common','common'],
	25 => ['human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','wolf','wolf','mage','necromancer','mad','guard','guard','common','common'],
	26 => ['human','human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','wolf','wolf','mage','necromancer','mad','guard','guard','common','common'],
	27 => ['human','human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','wolf','wolf','mage','necromancer','mad','guard','guard','common','common','common'],
	28 => ['human','human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','wolf','wolf','mage','mage','necromancer','mad','guard','guard','common','common','common'],
	29 => ['human','human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','wolf','wolf','mage','mage','necromancer','necromancer','mad','guard','guard','common','common','common'],
	30 => ['human','human','human','human','human','human','human','human','human','human','human','human','human','fox','wolf','wolf','wolf','wolf','wolf','wolf','mage','mage','necromancer','necromancer','mad','guard','guard','common','common','common']
	];



$user_icon_dir = 'user_icon'; //用戶アイコンディレクトリ

$user_emot_dir = 'user_emot'; //表情符號

$user_icon_dir2 = 'user_icon';

$dummy_boy_user_icon_image = '../img/dummy_boy_user_icon.webp'; //替身君のアイコン(user_iconディレクトリからの相対パス)
$dummy_boy_user_icon_width = '32'; //替身君のアイコン
$dummy_boy_user_icon_height = '32'; //替身君のアイコン

$dead_user_icon_image = 'img/grave.webp'; //死者のアイコン
$wolf_user_icon_image = 'img/wolf.webp'; //狼のアイコン
$spy_user_icon_image = '../img/spy_user_icon.webp'; //死者のアイコン

/*最初からあるアイコン名のリスト
$def_icon_name_array = array('明灰','暗灰','黄色','オレンジ','赤','水色','青','緑','紫','さくら色');

//最初からある用戶アイコンの色（アイコンのファイル名は必ず001～の数字にしてください）
//アイコンイメージをPHP設置時に追加する場合はここも必ず追加してください。
$def_icon_color_array = array('#DDDDDD','#999999','#FFD700','#FF9900','#FF0000',
							'#99CCFF','#0066FF','#00EE00','#CC00CC','#FF9999');

$def_icon_width_array = array(32,32,32,32,32,32,32,32,32,32); //最初からある用戶アイコンの幅(001～)
$def_icon_height_array = array(32,32,32,32,32,32,32,32,32,32); //最初からある用戶アイコンの高さ(001～)*/

$icon_name_max_length = 20; //アイコン名につけられる文字数(半形)
$icon_max_size = 3092; //アップロードできるアイコンファイルの最大容量(単位：バイト)
$icon_max_width = 45; //アップロードできるアイコンの最大幅
$icon_max_height = 55; //アップロードできるアイコンの最大高さ


$background_color_beforegame = 'seashell'; //ゲーム前の背景色
$background_color_aftergame = 'aliceblue'; //ゲーム後の背景色
$background_color_day = 'floralwhite'; //ゲーム中、白の背景色
$background_color_night = '#000030'; //ゲーム中、夜の背景色
$text_color_beforegame = 'black'; //ゲーム前のテキストの色
$text_color_aftergame = 'black'; //ゲーム後のテキストの色
$text_color_day = 'black'; //ゲーム中、白のテキストの色
$text_color_night = 'snow'; //ゲーム中、夜のテキストの色

$logview_onepage_count = 20; //過去ログ一覧で1ページでいくつの村を表示するか

$wfwtr_message = '今晚的村莊相當寒冷……';
$wfasm_message = '村莊炎上，陷入一片混亂……';

$s2eat = "<img src=\"$user_emot_dir/08.webp\" width=\"30\" height=\"30\" title=\"(嚼)\"/>";
$m2eat = "<img src=\"$user_emot_dir/08.webp\" width=\"50\" height=\"50\" title=\"(中嚼)\"/>";
$l2eat = "<img src=\"$user_emot_dir/08.webp\" width=\"72\" height=\"72\" title=\"(嚼嚼)\"/>";
$o2eat = "<img src=\"$user_emot_dir/08.webp\" title=\"(大嚼)\"/>";
$demota = [
			"(XD)",
			"(傻笑)",
			"(黑暗)",
			"(黑)",
			"(打擊)",
			"(哭)",
			"(GJ)",
			"(嚼)","(中嚼)","(嚼嚼)","(大嚼)",
			"(波浪嚼)",
			"(歪頭)",
			"(很帥)",
			"(金手指)",
			"(邪惡笑)",
			"(開心)",
			"(掰掰)",
			"(馬賽克)",
			"(驚嚇)",
			"(嚇到)",
			"(奸笑)",
			"(哼)",
			"(超開心)",
			"(touch)",
			"(空)",
			"(衰老)",
			"(卡卡)",
			"(笑笑)",
			"(大聲)",
			"(黑人問號)",
		];

$demotb = [
			"<img src=\"$user_emot_dir/01.webp\" title=\"(XD)\"/>",
			"<img src=\"$user_emot_dir/02.webp\" title=\"(傻笑)\"/>",
			"<img src=\"$user_emot_dir/03.webp\" title=\"(黑暗)\"/>",
			"<img src=\"$user_emot_dir/04.webp\" title=\"(黑)\"/>",
			"<img src=\"$user_emot_dir/05.webp\" title=\"(打擊)\"/>",
			"<img src=\"$user_emot_dir/06.webp\" title=\"(哭)\"/>",
			"<img src=\"$user_emot_dir/07.webp\" title=\"(GJ)\"/>",
			$s2eat, $m2eat, $l2eat, $o2eat,
			$s2eat.$m2eat.$l2eat.$o2eat.$l2eat.$m2eat.$s2eat,
			"<img src=\"$user_emot_dir/09.webp\" width=\"50\" height=\"50\" title=\"(歪頭)\"/>",
			"<img src=\"$user_emot_dir/10.webp\" title=\"(很帥)\"/>",
			"<img src=\"$user_emot_dir/11.webp\" title=\"(金手指)\"/>",
			"<img src=\"$user_emot_dir/12.webp\" title=\"(邪惡笑)\"/>",
			"<img src=\"$user_emot_dir/13.webp\" title=\"(開心)\"/>",
			"<img src=\"$user_emot_dir/14.webp\" title=\"(掰掰)\"/>",
			"<img src=\"$user_emot_dir/15.webp\" title=\"(馬賽克)\"/>",
			"<img src=\"$user_emot_dir/16.webp\" title=\"(驚嚇)\"/>",
			"<img src=\"$user_emot_dir/17.webp\" title=\"(嚇到)\"/>",
			"<img src=\"$user_emot_dir/18.webp\" title=\"(奸笑)\"/>",
			"<img src=\"$user_emot_dir/19.webp\" title=\"(哼)\"/>",
			"<img src=\"$user_emot_dir/20.webp\" title=\"(超開心)\"/>",
			"<img src=\"$user_emot_dir/21.webp\" title=\"[touch]\"/>",
			"<img src=\"$user_emot_dir/22.webp\" title=\"[空]\"/>",
			"<img src=\"$user_emot_dir/23.webp\" title=\"(衰老)\"/>",
			"<img src=\"$user_emot_dir/24.webp\" title=\"(卡卡)\"/>",
			"<img src=\"$user_emot_dir/25.webp\" title=\"(笑笑)\"/>",
			"<img src=\"$user_emot_dir/26.webp\" title=\"(大聲)\"/>",
			"<img src=\"$user_emot_dir/27.webp\" title=\"(黑人問號)\"/>",

		];

//-----------------------------------------------------------------------指定UTF-8
if (!headers_sent()) {
	header('Content-type: text/html; charset=UTF-8');
}
//---------------------------------------------------------------------------------
?>
