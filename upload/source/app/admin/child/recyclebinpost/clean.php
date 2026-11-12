<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('cleanrbsubmit', 2)) {

	shownav('topic', 'nav_recyclebinpost');
	showsubmenu('nav_recyclebinpost', [
		['recyclebinpost_list', 'recyclebinpost', 0],
		['search', 'recyclebinpost&operation=search', 0],
		['clean', 'recyclebinpost&operation=clean', 1]
	]);
	/*search={"nav_recyclebinpost":"action=recyclebinpost","clean":"action=recyclebinpost&operation=clean"}*/
	showformheader('recyclebinpost&operation=clean');
	showtableheader('recyclebinpost_clean');
	showsetting('recyclebinpost_clean_days', 'days', '30', 'text');
	showsubmit('cleanrbsubmit');
	showtablefooter();
	showformfooter();
	/*search*/

} else {

	$deletetids = [];
	$pernum = 200;
	$postsdel = intval($_GET['postsdel']);
	$days = intval($_GET['days']);
	$timestamp = TIMESTAMP - max(0, $days * 86400);

	$postlist = [];
	loadcache('posttableids');
	$posttables = !empty($_G['cache']['posttableids']) ? $_G['cache']['posttableids'] : [0];
	foreach($posttables as $ptid) {
		foreach(table_forum_post::t()->fetch_all_pid_by_invisible_dateline($ptid, -5, $timestamp, 0, $pernum) as $post) {
			$postlist[$ptid][] = $post['pid'];
		}
	}
	$postsundel = 0;
	if($postlist) {
		foreach($postlist as $ptid => $deletepids) {
			$postsdel += recyclebinpostdelete($deletepids, $ptid);
		}
		$startlimit += $pernum;
		cpmsg('recyclebinpost_clean_next', 'action=recyclebinpost&operation=clean&cleanrbsubmit=1&formhash='.FORMHASH.'&days='.$days.'&postsdel='.$postsdel, 'succeed', ['postsdel' => $postsdel]);
	} else {
		cpmsg('recyclebinpost_succeed', 'action=recyclebinpost&operation=clean', 'succeed', ['postsdel' => $postsdel, 'postsundel' => $postsundel]);
	}
}
	