SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `admin_manage` (
  `session_id` varchar(34) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `anivote` (
  `id` mediumint(6) UNSIGNED NOT NULL,
  `ppp` tinyint(1) UNSIGNED NOT NULL,
  `aa` int(3) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bbs` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `reid` int(8) UNSIGNED NOT NULL DEFAULT 0,
  `title` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `name` varchar(24) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `trip` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `password` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `mess` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `dige` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `toped` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `locked` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `recount` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `ip` char(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `retime` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `room` (
  `room_no` mediumint(8) UNSIGNED NOT NULL,
  `room_name` text DEFAULT NULL,
  `room_comment` text DEFAULT NULL,
  `max_user` enum('4','8','16','22','23','30','50') NOT NULL DEFAULT '22',
  `game_option` text DEFAULT NULL,
  `option_role` text DEFAULT NULL,
  `status` enum('playing','finished','waiting') DEFAULT NULL,
  `date` smallint(5) UNSIGNED DEFAULT NULL,
  `day_night` enum('aftergame','beforegame','day','night') DEFAULT 'beforegame',
  `last_updated` int(10) UNSIGNED DEFAULT NULL,
  `victory_role` varchar(16) DEFAULT NULL,
  `dellook` enum('0','1') DEFAULT '0',
  `uptime` int(10) NOT NULL DEFAULT 0,
  `checkdel` enum('0','1','2') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_message` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `room_no` mediumint(8) UNSIGNED DEFAULT NULL,
  `message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `type` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `date` smallint(5) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_message_old` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `room_no` mediumint(8) UNSIGNED DEFAULT NULL,
  `message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `type` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `date` smallint(5) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `talk` (
  `tid` int(10) UNSIGNED NOT NULL,
  `room_no` mediumint(8) UNSIGNED DEFAULT NULL,
  `date` tinyint(3) UNSIGNED DEFAULT NULL,
  `location` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `uname` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `time` int(10) UNSIGNED DEFAULT NULL,
  `sentence` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `font_type` set('strong','normal','weak','heaven','gm_to','to_gm','type_del','type_b') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'normal',
  `spend_time` int(11) DEFAULT NULL,
  `heaven` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `to_gm` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `gm_to` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `talk_old` (
  `tid` int(10) UNSIGNED NOT NULL,
  `room_no` mediumint(8) UNSIGNED DEFAULT NULL,
  `date` tinyint(3) UNSIGNED DEFAULT NULL,
  `location` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `uname` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `time` int(10) UNSIGNED DEFAULT NULL,
  `sentence` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `font_type` set('strong','normal','weak','heaven','gm_to','to_gm','type_del','type_b') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'normal',
  `spend_time` int(11) DEFAULT NULL,
  `heaven` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `to_gm` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `gm_to` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `trip_list` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `trip` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `totrip` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `count` mediumint(8) UNSIGNED NOT NULL,
  `gmco` mediumint(8) UNSIGNED NOT NULL,
  `stat` enum('0','1','2') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `trip_score` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `user` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `room` mediumint(8) UNSIGNED NOT NULL,
  `trip` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `mess` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `score` enum('0','1','2','3') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `trip_vote` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `reid` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `trip` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `isgm` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_entry` (
  `uid` int(10) UNSIGNED NOT NULL,
  `room_no` mediumint(8) UNSIGNED DEFAULT NULL,
  `user_no` tinyint(3) DEFAULT NULL,
  `uname` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `handle_name` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `trip` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `icon_no` smallint(6) DEFAULT NULL,
  `sex` enum('','famale','female','male','unknow') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `password` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `role` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `role_desc` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `g_color` char(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `live` enum('0','dead','gone','live') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `session_id` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `last_words` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ip_address` char(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `last_load_day_night` enum('','aftergame','beforegame','day','night') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `score` tinyint(2) NOT NULL DEFAULT 0,
  `death` enum('0','1','2') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `marked` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `lovers` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `noble` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `slave` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `gover` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_icon` (
  `icon_no` smallint(5) UNSIGNED NOT NULL,
  `icon_name` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `icon_filename` varchar(31) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `icon_width` tinyint(3) UNSIGNED DEFAULT NULL,
  `icon_height` tinyint(3) UNSIGNED DEFAULT NULL,
  `color` char(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `session_id` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `look` enum('0','1') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=FIXED;

CREATE TABLE `user_trip` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `trip` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `handle_name` varchar(21) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `password` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `number` int(6) UNSIGNED NOT NULL DEFAULT 0,
  `icon` char(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `size` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `ban` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=FIXED;

CREATE TABLE `vote` (
  `vid` int(10) UNSIGNED NOT NULL,
  `room_no` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `date` tinyint(3) UNSIGNED DEFAULT NULL,
  `uname` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `target_uname` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `vote_number` int(11) DEFAULT NULL,
  `vote_times` int(11) DEFAULT NULL,
  `situation` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `admin_manage`
  ADD KEY `session_id` (`session_id`);

ALTER TABLE `anivote`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `bbs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reid` (`reid`),
  ADD KEY `dige` (`dige`),
  ADD KEY `retime` (`retime`);

ALTER TABLE `room`
  ADD PRIMARY KEY (`room_no`),
  ADD UNIQUE KEY `room_no` (`room_no`,`date`,`max_user`,`status`) USING BTREE,
  ADD KEY `status` (`status`),
  ADD KEY `status_2` (`status`,`victory_role`);

ALTER TABLE `system_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_no_2` (`room_no`),
  ADD KEY `room_no` (`room_no`,`date`);

ALTER TABLE `system_message_old`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_no_2` (`room_no`),
  ADD KEY `room_no` (`room_no`,`date`);

ALTER TABLE `talk`
  ADD PRIMARY KEY (`tid`),
  ADD KEY `room_no_2` (`room_no`,`date`),
  ADD KEY `location` (`location`),
  ADD KEY `room_no` (`room_no`) USING BTREE,
  ADD KEY `uname` (`uname`);

ALTER TABLE `talk_old`
  ADD PRIMARY KEY (`tid`),
  ADD KEY `room_no` (`room_no`),
  ADD KEY `room_no_2` (`room_no`,`date`),
  ADD KEY `location` (`location`);

ALTER TABLE `trip_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`,`stat`),
  ADD KEY `stat` (`stat`);

ALTER TABLE `trip_score`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip` (`trip`,`score`),
  ADD KEY `trip_2` (`trip`);

ALTER TABLE `trip_vote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reid` (`reid`,`trip`);

ALTER TABLE `user_entry`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `room_no_2` (`room_no`,`uname`) USING BTREE,
  ADD KEY `room_no` (`room_no`),
  ADD KEY `room_no_5` (`room_no`,`user_no`) USING BTREE,
  ADD KEY `trip` (`trip`),
  ADD KEY `uname` (`uname`),
  ADD KEY `user_no` (`user_no`),
  ADD KEY `icon_no` (`icon_no`);

ALTER TABLE `user_icon`
  ADD PRIMARY KEY (`icon_no`) USING BTREE;

ALTER TABLE `user_trip`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trip` (`trip`);

ALTER TABLE `vote`
  ADD PRIMARY KEY (`vid`),
  ADD KEY `room_no` (`room_no`) USING HASH;


ALTER TABLE `anivote`
  MODIFY `id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `bbs`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `room`
  MODIFY `room_no` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `system_message`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `system_message_old`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `talk`
  MODIFY `tid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `talk_old`
  MODIFY `tid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `trip_list`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `trip_score`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `trip_vote`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_entry`
  MODIFY `uid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_icon`
  MODIFY `icon_no` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_trip`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `vote`
  MODIFY `vid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

INSERT INTO `user_entry` (`uid`, `room_no`, `user_no`, `uname`, `handle_name`, `trip`, `icon_no`, `sex`, `password`, `role`, `role_desc`, `g_color`, `live`, `session_id`, `last_words`, `ip_address`, `last_load_day_night`, `score`, `death`, `marked`, `lovers`, `noble`, `slave`, `gover`) VALUES
(1, 0, 0, 'system', '系統', 'DETfcflZU6', 1051, 'male', 'anijin', 'none', '', '', '', '', '', '', '', 0, '0', '0', '0', '0', '0', '0'),
(24, 2, 1, 'dummy_boy', '伊藤誠', 'DETfcflZU6', 1051, 'male', 'anijin', '', '', '', 'dead', '', '', '', 'beforegame', 0, '0', '0', '0', '0', '0', '0');

INSERT INTO `user_icon` (`icon_no`, `icon_name`, `icon_filename`, `icon_width`, `icon_height`, `color`, `session_id`, `look`) VALUES
(1, '明灰', '001.webp', 45, 45, '#DDDDDD', NULL, '1'),
(2, '暗灰', '002.webp', 45, 45, '#999999', NULL, '1'),
(3, '黄色', '003.webp', 45, 45, '#FFD700', NULL, '1'),
(4, '橘色', '004.webp', 45, 45, '#FF9900', NULL, '1'),
(5, '紅色', '005.webp', 45, 45, '#FF0000', NULL, '1'),
(6, '水色', '006.webp', 45, 45, '#99CCFF', NULL, '1'),
(7, '青', '007.webp', 45, 45, '#0066FF', NULL, '1'),
(8, '緑', '008.webp', 45, 45, '#00EE00', NULL, '1'),
(9, '紫', '009.webp', 45, 45, '#CC00CC', NULL, '1'),
(10, '櫻花色', '010.webp', 45, 45, '#FF9999', NULL, '1');