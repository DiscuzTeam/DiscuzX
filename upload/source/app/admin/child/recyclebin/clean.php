<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('rbsubmit', 2)) {

	shownav('topic', 'nav_recyclebin');
	showsubmenu('nav_recyclebin', [
		['recyclebin_list', 'recyclebin', 0],
		['search', 'recyclebin&operation=search', 0],
		['clean', 'recyclebin&operation=clean', 1]
	]);
	/*search={"nav_recyclebin":"action=recyclebin","clean":"action=recyclebin&operation=clean"}*/
	showformheader('recyclebin&operation=clean');
	showtableheader('recyclebin_clean');
	showsetting('recyclebin_clean_days', 'days', '30', 'text');
	showsubmit('rbsubmit');
	showtablefooter();
	showformfooter();
	/*search*/

} else {

	$deletetids = [];
	$timestamp = TIMESTAMP;
	$pernum = 20;
	$threadsdel = intval($_GET['threadsdel']);
	$days = intval($_GET['days']);
	foreach(table_forum_thread::t()->fetch_all_recyclebin_by_dateline($timestamp - ($days * 86400), 0, $pernum) as $thread) {
		$deletetids[] = $thread['tid'];
	}
	if($deletetids) {
		require_once libfile('function/delete');
		$delcount = deletethread($deletetids);
		$threadsdel += $delcount;
		$startlimit += $pernum;
		cpmsg('recyclebin_clean_next', 'action=recyclebin&operation=clean&rbsubmit=1&formhash='.FORMHASH.'&threadsdel='.$threadsdel.'&days='.$days, 'loading', ['threadsdel' => $threadsdel]);
	} else {
		cpmsg('recyclebin_succeed', 'action=recyclebin&operation=clean', 'succeed', ['threadsdel' => $threadsdel, 'threadsundel' => 0]);
	}
}
	