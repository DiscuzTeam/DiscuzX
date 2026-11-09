<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$aid = intval($_GET['aid']);
$exif = $aid ? table_forum_attachment_exif::t()->fetch($aid) : [];
$s = $exif['exif'] ?? '';
if(!$s && $aid) {
    require_once libfile('function/attachment');
    $s = getattachexif($aid);
    if($s !== '') {
        table_forum_attachment_exif::t()->insert_exif($aid, $s);
    }
}
include template('common/header_ajax');
echo strip_tags($s, '<br>');
include template('common/footer_ajax');
exit;
