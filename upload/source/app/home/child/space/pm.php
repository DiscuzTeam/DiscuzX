<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['pmstatus']) {
	showmessage('pm_status_off');
}

loaducenter();

$list = [];

$plid = empty($_GET['plid']) ? 0 : intval($_GET['plid']);
$daterange = empty($_GET['daterange']) ? 0 : intval($_GET['daterange']);
$touid = empty($_GET['touid']) ? 0 : intval($_GET['touid']);
$opactives['pm'] = 'class="a"';

if(empty($_G['member']['category_num']['manage']) && !in_array($_G['adminid'], [1, 2, 3])) {
	unset($_G['notice_structure']['manage']);
}

if($_GET['subop'] == 'view') {
	$type = $_GET['type'];
	$page = empty($_GET['page']) ? 0 : intval($_GET['page']);

	$chatpmmember = intval($_GET['chatpmmember']);
	$chatpmmemberlist = [];
	if($chatpmmember) {
		$chatpmmember = uc_pm_chatpmmemberlist($_G['uid'], $plid);
		if(!empty($chatpmmember)) {
			$authorid = $founderuid = $chatpmmember['author'];
			$chatpmmemberlist = table_common_member::t()->fetch_all($chatpmmember['member']);
			foreach(table_common_member_field_home::t()->fetch_all($chatpmmember['member']) as $uid => $member) {
				$chatpmmemberlist[$uid] = array_merge($member, $chatpmmemberlist[$uid]);
			}
		}
		require_once libfile('function/friend');
		$friendgrouplist = friend_group_list();
		$actives = ['chatpmmember' => ' class="a"'];
	} else {
		if($touid) {
			$ols = [];
			if(defined('IN_MOBILE')) {
				$perpage = 5;
			} elseif(defined('IN_RESTFUL')) {
				$perpage = 10;
			} else {
				$perpage = 10;
			}
			$perpage = mob_perpage($perpage);
			if(!$daterange) {
				$member = getuserbyuid($touid);
				$tousername = $member['username'];
				unset($member);
				$count = uc_pm_view_num($_G['uid'], $touid, 0);
				if(!$page) {
					$page = ceil($count / $perpage);
				}
				$list = uc_pm_view($_G['uid'], 0, $touid, 5, ceil($count / $perpage) - $page + 1, $perpage, 0, 0);
				$multi = pmmulti($count, $perpage, $page, "home.php?mod=space&do=pm&subop=view&touid=$touid");
			} else {
				showmessage('parameters_error');
			}
		} else {
			if(defined('IN_MOBILE')) {
				$perpage = 10;
			} else {
				$perpage = 50;
			}
			$perpage = mob_perpage($perpage);
			$count = uc_pm_view_num($_G['uid'], $plid, 1);
			if(!$daterange) {
				if(!$page) {
					$page = ceil($count / $perpage);
				}
				$list = uc_pm_view($_G['uid'], 0, $plid, 5, ceil($count / $perpage) - $page + 1, $perpage, $type, 1);
				$multi = pmmulti($count, $perpage, $page, "home.php?mod=space&do=pm&subop=view&plid=$plid&type=$type");
			} else {
				$list = uc_pm_view($_G['uid'], 0, $plid, 5, ceil($count / $perpage) - $page + 1, $perpage, $type, 1);
				$chatpmmember = uc_pm_chatpmmemberlist($_G['uid'], $plid);
				if(!empty($chatpmmember)) {
					$authorid = $founderuid = $chatpmmember['author'];
					$chatpmmemberlist = table_common_member::t()->fetch_all($chatpmmember['member']);
					foreach(table_common_member_field_home::t()->fetch_all($chatpmmember['member']) as $uid => $member) {
						$chatpmmemberlist[$uid] = array_merge($member, $chatpmmemberlist[$uid]);
					}
					foreach(C::app()->session->fetch_all_by_uid($chatpmmember['member']) as $value) {
						if(!$value['invisible']) {
							$ols[$value['uid']] = $value['lastactivity'];
						}
					}
				}
				$membernum = count($chatpmmemberlist);
				$subject = $list[0]['subject'];
				$refreshtime = $_G['setting']['chatpmrefreshtime'];

			}
		}
		tousername($list);
		$founderuid = empty($list) ? 0 : $list[0]['founderuid'];
		$pmid = empty($list) ? 0 : $list[0]['pmid'];
	}
	$actives['privatepm'] = ' class="a"';

} elseif($_GET['subop'] == 'viewg') {

	$pmid = intval($_GET['pmid']);
	$grouppm = [];
	$membergpm = $pmid ? table_common_member_grouppm::t()->fetch_gpm($_G['uid'], $pmid) : [];
	if($membergpm) {
		$grouppm = array_merge((array)$membergpm, (array)table_common_grouppm::t()->fetch($pmid));
		if($grouppm) {
			$grouppm['numbers'] = max(0, (int)($grouppm['numbers'] ?? 0) - 1);
		}
		if(empty($membergpm['status'])) {
			table_common_member_grouppm::t()->update($_G['uid'], $pmid, ['status' => 1, 'dateline' => TIMESTAMP]);
		}
	}
	$actives['announcepm'] = ' class="a"';

} elseif($_GET['subop'] == 'ignore') {

	$ignorelist = uc_pm_blackls_get($_G['uid']);
	$actives = ['ignore' => ' class="a"'];

} elseif($_GET['subop'] == 'setting') {

	$actives = ['setting' => ' class="a"'];
	$acceptfriendpmstatus = $_G['member']['onlyacceptfriendpm'] ? $_G['member']['onlyacceptfriendpm'] : ($_G['setting']['onlyacceptfriendpm'] ? 1 : 2);
	$ignorelist = uc_pm_blackls_get($_G['uid']);

} else {

	$filter = in_array($_GET['filter'], ['newpm', 'privatepm', 'announcepm']) ? $_GET['filter'] : 'privatepm';

	$perpage = 15;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page']) ? 0 : intval($_GET['page']);
	if($page < 1) $page = 1;

	$grouppms = $gpmids = $gpmstatus = [];
	$newpm = $newpmcount = 0;

	if($filter == 'privatepm' && $page == 1 || $filter == 'announcepm' || $filter == 'newpm') {
		$announcepm = 0;
		foreach(table_common_member_grouppm::t()->fetch_all_by_uid($_G['uid'], $filter == 'announcepm' ? 1 : 0) as $gpmid => $gpuser) {
			$gpmstatus[$gpmid] = $gpuser['status'];
			if($gpuser['status'] == 0) {
				$announcepm++;
			}
		}
		$gpmids = array_keys($gpmstatus);
		if($gpmids) {
			foreach(table_common_grouppm::t()->fetch_all_by_id_authorid($gpmids) as $grouppm) {
				$grouppm['message'] = cutstr(strip_tags($grouppm['message']), 100, '');
				$grouppms[] = $grouppm;
			}
		}
	}

	if($filter == 'privatepm' || $filter == 'newpm') {
		$result = uc_pm_list($_G['uid'], $page, $perpage, 'inbox', $filter, 200);
		$count = $result['count'];
		$list = $result['data'];
	}

	tousername($list);

	if($filter == 'privatepm' && $page == 1 || $filter == 'newpm') {
		$newpmarr = uc_pm_checknew($_G['uid'], 1);
		$newpm = $newpmarr['newpm'];
	}
	$newpmcount = $newpm + $announcepm;
	if($_G['member']['newpm']) {
		table_common_member::t()->update($_G['uid'], ['newpm' => 0]);
		uc_pm_ignore($_G['uid']);
	}
	$multi = multi($count, $perpage, $page, "home.php?mod=space&do=pm&filter=$filter", 0, 5);
	$actives = [$filter => ' class="a"'];
}

if(!empty($list)) {
	$today = $_G['timestamp'] - ($_G['timestamp'] + $_G['setting']['timeoffset'] * 3600) % 86400;
	foreach($list as $key => $value) {
		$value['lastsummary'] = str_replace('&amp;', '&', $value['lastsummary']);
		$value['lastsummary'] = preg_replace('/&[a-z]+\;/i', '', $value['lastsummary']);
		$value['daterange'] = 5;
		if($value['lastdateline'] >= $today) {
			$value['daterange'] = 1;
		} elseif($value['lastdateline'] >= $today - 86400) {
			$value['daterange'] = 2;
		} elseif($value['lastdateline'] >= $today - 172800) {
			$value['daterange'] = 3;
		} elseif($value['lastdateline'] >= $today - 604800) {
			$value['daterange'] = 4;
		}
		$list[$key] = $value;
	}
}
include_once template('diy:home/space_pm');

function pmmulti($count, $perpage, $curpage, $mpurl) {
	$return = '';
	$lang['next'] = lang('core', 'nextpage');
	$lang['prev'] = lang('core', 'prevpage');
	$next = $curpage < ceil($count / $perpage) ? '<a href="'.$mpurl.'&amp;page='.($curpage + 1).'#last" class="nxt">'.$lang['next'].'</a>' : '';
	$prev = $curpage > 1 ? '<span class="pgb"><a href="'.$mpurl.'&amp;page='.($curpage - 1).'#last">'.$lang['prev'].'</a></span>' : '';
	if($next || $prev) {
		$return = '<div class="pg">'.$prev.$next.'</div>';
	}
	return $return;
}

