<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

shownav('founder', 'nav_threadsplit');
if(!submitcheck('threadsplit_update_submit')) {
	showsubmenu('nav_threadsplit', [
		['nav_threadsplit_manage', 'threadsplit&operation=manage', 1],
		['nav_threadsplit_move', 'threadsplit&operation=move', 0],
	]);
	/*search={"nav_threadsplit":"action=threadsplit","nav_threadsplit_manage":"action=threadsplit&operation=manage"}*/
	showtips('threadsplit_manage_tips');
	showformheader('threadsplit&operation=manage');
	showtableheader('threadsplit_manage_table_orig');

	$thread_table_orig = table_forum_thread::t()->gettablestatus();
	showsubtitle(['threadsplit_manage_tablename', 'threadsplit_manage_threadcount', 'threadsplit_manage_datalength', 'threadsplit_manage_indexlength', 'threadsplit_manage_table_createtime', 'threadsplit_manage_table_memo', '']);
	showtablerow('', [], [$thread_table_orig['Name'], $thread_table_orig['Rows'], $thread_table_orig['Data_length'], $thread_table_orig['Index_length'], $thread_table_orig['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[0]\" value=\"{$threadtable_info[0]['memo']}\" />", '']);

	showtableheader('threadsplit_manage_table_archive');
	showsubtitle(['threadsplit_manage_tablename', 'threadsplit_manage_dislayname', 'threadsplit_manage_threadcount', 'threadsplit_manage_datalength', 'threadsplit_manage_indexlength', 'threadsplit_manage_table_createtime', 'threadsplit_manage_table_memo', '']);
	foreach($threadtableids as $tableid) {
		if(!$tableid) {
			continue;
		}
		$tablename = "forum_thread_$tableid";
		$table_info = table_forum_thread::t()->gettablestatus($tableid);
		showtablerow('', [], [$table_info['Name'], "<input type=\"text\" class=\"txt\" name=\"displayname[$tableid]\" value=\"{$threadtable_info[$tableid]['displayname']}\" />", $table_info['Rows'], $table_info['Data_length'], $table_info['Index_length'], $table_info['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[$tableid]\" value=\"{$threadtable_info[$tableid]['memo']}\" />", "<a href=\"?action=threadsplit&operation=droptable&tableid=$tableid&formhash=".FORMHASH."\">{$lang['delete']}</a>"]);
	}
	showsubmit('threadsplit_update_submit', 'threadsplit_manage_update', '', '<a href="?action=threadsplit&operation=addnewtable" style="border-style: solid; border-width: 1px;" class="btn">'.$lang['threadsplit_manage_table_add'].'</a>&nbsp;<a href="?action=threadsplit&operation=forumarchive" style="border-style: solid; border-width: 1px;" class="btn">'.$lang['threadsplit_manage_forum_update'].'</a>');
	showtablefooter();
	showformfooter();
	/*search*/
} else {
	$threadtable_info = [];
	$_GET['memo'] = !empty($_GET['memo']) ? $_GET['memo'] : [];
	$_GET['displayname'] = !empty($_GET['displayname']) ? $_GET['displayname'] : [];
	foreach(array_keys($_GET['memo']) as $tableid) {
		$threadtable_info[$tableid]['memo'] = $_GET['memo'][$tableid];
	}
	foreach(array_keys($_GET['displayname']) as $tableid) {
		$threadtable_info[$tableid]['displayname'] = $_GET['displayname'][$tableid];
	}
	table_common_setting::t()->update_setting('threadtable_info', $threadtable_info);
	savecache('threadtable_info', $threadtable_info);
	update_threadtableids();
	updatecache('setting');
	cpmsg('threadsplit_manage_update_succeed', 'action=threadsplit&operation=manage', 'succeed');
}
	