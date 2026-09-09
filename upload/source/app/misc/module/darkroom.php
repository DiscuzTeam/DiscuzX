<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/member');
if($_G['setting']['darkroom']) {
	if(getgpc('username')) {
		$username = trim(dhtmlspecialchars(getgpc('username')));
		$user = table_common_member::t()->fetch_by_username($username);
		$crimelist = [];
		$search_no_result = 0;

		if($user && ($user['groupid'] == 4 || $user['groupid'] == 5)) {
			$uid = $user['uid'];
			foreach(table_common_member_crime::t()->fetch_all_by_uid_action($uid, [4, 5]) as $crime) {
				$crime['action'] = lang('forum/template', crime_action_ctl::$actions[$crime['action']]);
				$crime['dateline'] = dgmdate($crime['dateline'], 'u');
				$crime['username'] = $user['username'];
				$crime['groupexpiry'] = $user['groupexpiry'] ? dgmdate($user['groupexpiry'], 'u') : lang('forum/misc', 'never_expired');
				$crimelist[] = $crime;
			}
		}

		if(empty($crimelist)) {
			$search_no_result = 1;
		}

		// 加载模板并显示结果
		include_once template('misc/darkroom');
		exit;
	}
	$limit = $_G['tpp'];
	$cid = getgpc('cid') ? dintval($_GET['cid']) : 0;
	$crimelist = [];
	$i = 0;
	foreach(table_common_member_crime::t()->fetch_all_by_cid($cid, [4, 5], $limit) as $crime) {
		$i++;
		$cid = $crime['cid'];
		if(isset($crimelist[$crime['uid']])) {
			continue;
		}
		$crime['action'] = lang('forum/template', crime_action_ctl::$actions[$crime['action']]);
		$crime['dateline'] = dgmdate($crime['dateline'], 'u');
		$crimelist[$crime['uid']] = $crime;
	}
	if($crimelist && $i == $limit) {
		$dataexist = 1;
	} else {
		$dataexist = 0;
	}
	foreach(table_common_member::t()->fetch_all(array_keys($crimelist)) as $uid => $user) {
		if($user['groupid'] == 4 || $user['groupid'] == 5) {
			$crimelist[$uid]['username'] = $user['username'];
			$crimelist[$uid]['groupexpiry'] = $user['groupexpiry'] ? dgmdate($user['groupexpiry'], 'u') : lang('forum/misc', 'never_expired');
		} else {
			unset($crimelist[$uid]);
		}
	}
	if(getgpc('ajaxdata') === 'json') {
		showmessage($dataexist.'|'.$cid, '', array_values($crimelist));
	} else {
		include_once template('misc/darkroom');
	}
	exit;
}
showmessage('undefined_action');