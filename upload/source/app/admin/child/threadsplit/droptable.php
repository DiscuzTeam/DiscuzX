<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(FORMHASH != $_GET['formhash']) {
	cpmsg('undefined_action', '', 'error');
}
$tableid = intval($_GET['tableid']);
$tablename = "forum_thread_$tableid";
$table_info = table_forum_thread::t()->gettablestatus($tableid);
if(!$tableid || !$table_info) {
	cpmsg('threadsplit_table_no_exists', 'action=threadsplit&operation=manage', 'error');
}
if($table_info['Rows'] > 0) {
	cpmsg('threadsplit_drop_table_no_empty_error', 'action=threadsplit&operation=manage', 'error');
}

table_forum_thread::t()->drop_table($tableid);
unset($threadtable_info[$tableid]);

update_threadtableids();

table_common_setting::t()->update_setting('threadtable_info', $threadtable_info);
savecache('threadtable_info', $threadtable_info);
updatecache('setting');
cpmsg('threadsplit_drop_table_succeed', 'action=threadsplit&operation=manage', 'succeed');
	