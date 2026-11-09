<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/group');
loadcache('grouptype');
$fid = $_GET['fid'];
$sourcetype = table_forum_forum::t()->fetch_info_by_fid($fid);
$firstgroup = $_G['cache']['grouptype']['first'];
if($firstgroup[$fid]['secondlist']) {
	cpmsg('grouptype_delete_sub_notnull');
}
shownav('group', 'nav_group_type');
showchildmenu([['nav_group_type', 'group&operation=type'], [$sourcetype['name'].' ', '']], cplang('group_mergetype'));

if(!submitcheck('mergesubmit', 2)) {
	$groupselect = get_groupselect(0, 0, 0);
	showformheader("group&operation=mergetype&fid=$fid", 'enctype');
	showtableheader();
	showsetting('group_mergetype_selecttype', '', '', '<select name="mergefid">'.$groupselect.'</select>');
	showsubmit('mergesubmit');
	showtablefooter();
	showformfooter();
} else {
	$mergefid = $_GET['mergefid'];
	if(empty($_GET['confirm'])) {
		cpmsg('group_mergetype_confirm', 'action=group&operation=mergetype&fid='.$fid.'&mergesubmit=yes&formhash='.FORMHASH.'&confirm=1', 'form', [], '<input type="hidden" name="mergefid" value="'.$mergefid.'">');
	}
	if($mergefid == $fid) {
		cpmsg('group_mergetype_target_error', 'action=group&operation=mergetype&fid='.$fid, 'error');
	}
	table_forum_forum::t()->update_fup_by_fup($fid, $mergefid);
	table_forum_forum::t()->delete_by_fid($fid);
	table_home_favorite::t()->delete_by_id_idtype($fid, 'gid');
	table_forum_forumfield::t()->update_groupnum($mergefid, $sourcetype['groupnum']);
	updatecache('grouptype');
	cpmsg('group_mergetype_succeed', 'action=group&operation=type');
}
	