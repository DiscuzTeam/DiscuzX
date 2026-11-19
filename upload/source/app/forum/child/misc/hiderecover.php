<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['formhash'] != FORMHASH) {
	showmessage('undefined_action', NULL);
}
$_hrthread = table_forum_thread::t()->fetch_thread($_G['tid']);
if(empty($_G['uid']) || empty($_hrthread['authorid']) || $_hrthread['authorid'] != $_G['uid']) {
	showmessage('no_privilege');
}
$seccodecheck = true;
if(submitcheck('hiderecoversubmit')) {
	table_forum_threadhidelog::t()->delete_by_tid($_G['tid']);
	showmessage('thread_hiderecover_success', dreferer());
} else {
	include template('forum/hiderecover');
}
	