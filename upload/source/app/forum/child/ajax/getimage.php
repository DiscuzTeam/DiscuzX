<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_GET['aid'] = intval($_GET['aid']);
$image = table_forum_attachment_n::t()->fetch_attachment('aid:'.$_GET['aid'], $_GET['aid'], 1);
if($image['uid'] != $_G['uid']) {
	dexit();
}
include template('common/header_ajax');
if($image['aid']) {
	echo '<img src="'.getforumimg($image['aid'], 1, 300, 300, 'fixnone').'" id="image_'.$image['aid'].'" onclick="insertAttachimgTag(\''.$image['aid'].'\')" width="'.($image['width'] < 110 ? $image['width'] : 110).'" cwidth="'.($image['width'] < 300 ? $image['width'] : 300).'" />';
}
include template('common/footer_ajax');
dexit();
	