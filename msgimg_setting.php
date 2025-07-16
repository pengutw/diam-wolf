<?php
	
	// require:
	// require_once 'msgimg_setting.php';
	
	$msg_image_prefix = 'img/msgimg/';
	
	// enables message image 
	$use_msg_image = true;
	
	$msg_ampm_image = $msg_image_prefix . 'ampm.webp';
	$msg_guard_image = $msg_image_prefix . 'bgd.webp';
	$msg_vote_image = $msg_image_prefix . 'dead1.webp';
	$msg_kill_image = $msg_image_prefix . 'dead2.webp';
	$msg_sys_image = $msg_image_prefix . 'msg.webp';
	$msg_mage_image = $msg_image_prefix . 'ura.webp';
	$msg_room_image = $msg_image_prefix . 'village.webp';
	$msg_wolf_image = $msg_image_prefix . 'wlf.webp';
	$msg_human_image = $msg_image_prefix . 'hum.webp';
	$msg_fox_image = $msg_image_prefix . 'fox.webp';
	$msg_spy_image = $msg_image_prefix . 'spy.webp';
	
	$msg_r_bat_image = $msg_image_prefix . 'bat.webp';
	$msg_r_clock_image = $msg_image_prefix . 'clock.webp';
	$msg_r_common_image = $msg_image_prefix . 'fre.webp';
	$msg_r_grave_image = $msg_image_prefix . 'grave.webp';
	$msg_r_mad_image = $msg_image_prefix . 'mad.webp';
	$msg_r_magic_image = $msg_image_prefix . 'mag.webp';
	$msg_r_necro_image = $msg_image_prefix . 'nec.webp';
	$msg_r_noble_image = $msg_image_prefix . 'nob.webp';
	$msg_r_slave_image = $msg_image_prefix . 'sla.webp';

	$msg_fosi_image = $msg_fox_image;
	$msg_cat_image = $msg_r_necro_image;
	$msg_sudden_image = $msg_r_mad_image;
	$msg_gm_image = $msg_r_magic_image;
	$msg_rm_image = $msg_r_noble_image;
	$msg_lover_image = $msg_r_common_image;
	$msg_mad_image = $msg_r_mad_image;
	
	function msgimg($image_url): string {
		global $use_msg_image;
		
		if($use_msg_image && $image_url != '') 
			return "<img src=\"$image_url\">&nbsp;";
		else
			return '';
	}
	
?>