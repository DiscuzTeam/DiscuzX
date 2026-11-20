<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$forumupdate = $listupdate = false;

$op = !in_array($op, ['editforum', 'recommend', 'member']) ? 'editforum' : $op;

if(empty($_G['fid'])) {
	if(!empty($_G['cookie']['modcpfid'])) {
		$fid = $_G['cookie']['modcpfid'];
	} else {
		list($fid) = array_keys($modforums['list']);
	}
	dheader("Location: {$cpscript}?mod=modcp&action={$_GET['action']}&op=$op&fid=$fid");
}

if($_G['fid'] && $_G['forum']['ismoderator']) {

	if($op == 'editforum') {

		require_once libfile('function/editor');

		$alloweditrules = $_G['adminid'] == 1 || $_G['forum']['alloweditrules'];

		if(!submitcheck('editsubmit')) {
			$_G['forum']['rules'] = html2bbcode($_G['forum']['rules']);
		} else {

			require_once libfile('function/discuzcode');
			$forumupdate = true;
			$rulesnew = $alloweditrules ? preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode($_GET['rulesnew'], 1, 0, 0, 0, 1, 1, 0, 0, 1)) : $_G['forum']['rules'];
			table_forum_forumfield::t()->update($_G['fid'], ['rules' => $rulesnew]);

			$_G['forum']['description'] = html2bbcode($descnew);
			$_G['forum']['rules'] = html2bbcode($rulesnew);

		}

	} elseif($op == 'recommend') {

		$useradd = 0;

		if($_G['adminid'] == 3) {
			$useradd = $_G['uid'];
		}
		$ordernew = !empty($_GET['ordernew']) && is_array($_GET['ordernew']) ? $_GET['ordernew'] : [];

		if(submitcheck('editsubmit') && $_G['forum']['modrecommend']['sort'] != 1) {
			$validids = [];
			foreach(table_forum_forumrecommend::t()->fetch_all_by_fid($_G['fid'], false, $useradd) as $validrow) {
				$validids[$validrow['tid']] = $validrow['tid'];
			}
			$threads = [];
			foreach($_GET['order'] as $id => $position) {
				$threads[$id]['order'] = $position;
			}
			foreach($_GET['subject'] as $id => $title) {
				$threads[$id]['subject'] = $title;
			}
			foreach($_GET['expirationrecommend'] as $id => $expiration) {
				$expiration = trim($expiration);
				if(!empty($expiration)) {
					if(!preg_match('/^\d{4}-\d{1,2}-\d{1,2} +\d{1,2}:\d{1,2}$/', $expiration)) {
						showmessage('recommend_expiration_invalid');
					}
					list($expiration_date, $expiration_time) = explode(' ', $expiration);
					list($expiration_year, $expiration_month, $expiration_day) = explode('-', $expiration_date);
					list($expiration_hour, $expiration_min) = explode(':', $expiration_time);
					$expiration_sec = 0;

					$expiration_timestamp = mktime($expiration_hour, $expiration_min, $expiration_sec, $expiration_month, $expiration_day, $expiration_year);
				} else {
					$expiration_timestamp = 0;
				}
				$threads[$id]['expiration'] = $expiration_timestamp;
			}
			if($_GET['delete']) {
				$listupdate = true;
				$deleteids = [];
				foreach((array)$_GET['delete'] as $deleteid) {
					$deleteid = dintval($deleteid);
					if($deleteid && isset($validids[$deleteid])) {
						$deleteids[] = $deleteid;
					}
				}
				if($deleteids) {
					table_forum_forumrecommend::t()->delete($deleteids);
				}
			}
			if(!empty($_GET['delete']) && is_array($_GET['delete'])) {
				foreach($_GET['delete'] as $id) {
					$threads[$id]['delete'] = true;
					unset($threads[$id]);
				}
			}
			foreach($threads as $id => $item) {
				if(!isset($validids[$id])) {
					continue;
				}
				$item['displayorder'] = intval($item['order']);
				$item['subject'] = dhtmlspecialchars($item['subject']);
				table_forum_forumrecommend::t()->update($id, [
					'subject' => $item['subject'],
					'displayorder' => $item['displayorder'],
					'expiration' => $item['expiration']
				]);
			}
			$listupdate = true;
		}

		$page = max(1, intval($_G['page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$threadcount = table_forum_forumrecommend::t()->count_by_fid($_G['fid']);
		$multipage = multi($threadcount, $_G['tpp'], $page, "$cpscript?action={$_GET['action']}&fid={$_G['fid']}&page=$page");

		$threadlist = $moderatormembers = [];
		$moderatorids = [];
		foreach(table_forum_forumrecommend::t()->fetch_all_by_fid($_G['fid'], false, $useradd, $start_limit, $_G['tpp']) as $thread) {
			if($thread['moderatorid']) {
				$moderatorids[$thread['moderatorid']] = $thread['moderatorid'];
			}
			$thread['authorlink'] = $thread['authorid'] ? "<a href=\"home.php?mod=space&uid={$thread['authorid']}\" target=\"_blank\">{$thread['author']}</a>" : 'Guest';
			$thread['expiration'] = $thread['expiration'] ? dgmdate($thread['expiration']) : '';
			$threadlist[] = $thread;
		}
		if($moderatorids) {
			$moderatormembers = table_common_member::t()->fetch_all($moderatorids, false, 0);
		}

	} elseif($op == 'member') {

		$do = !empty($_GET['do']) ? $_GET['do'] : ($_G['forum']['jointype'] == 2 ? 'mod' : '');
		$url = "{$cpscript}?mod=modcp&action={$_GET['action']}&op=$op&fid=$fid";

		if($do == 'mod') {
			if(!empty($_GET['formhash']) && $_GET['formhash'] == formhash()) {
				if(!empty($_GET['uid'])) {
					$checkusers = [$_GET['uid']];
					$checktype = intval($_GET['checktype']);
				} elseif(getgpc('checkall') == 1 || getgpc('checkall') == 2) {
					$checktype = $_GET['checkall'];
					$query = table_forum_groupuser::t()->fetch_all_by_fid($_G['fid'], 1);
					foreach($query as $row) {
						$checkusers[] = $row['uid'];
					}
				}
			}

			if($checkusers) {
				foreach($checkusers as $uid) {
					$notification = $checktype == 1 ? 'forum_member_check' : 'forum_member_check_failed';
					notification_add($uid, 'mod_member', $notification, ['fid' => $_G['fid'], 'forumname' => $_G['forum']['name'], 'url' => $_G['siteurl'].'forum.php?mod=forumdisplay&fid='.$_G['fid']], 1);
				}
				if($checktype == 1) {
					table_forum_groupuser::t()->update_for_user($checkusers, $_G['fid'], null, null, 4);
					table_forum_forumfield::t()->update_membernum($_G['fid'], count($checkusers));
				} elseif($checktype == 2) {
					table_forum_groupuser::t()->delete_by_fid($_G['fid'], $checkusers);
				}
				if($checktype == 1) {
					showmessage('forum_member_moderate_succeed', $url);
				} else {
					showmessage('forum_member_moderate_failed', $url);
				}
			}

			$checknum = table_forum_groupuser::t()->fetch_count_by_fid($_G['fid'], 1);
			$page = intval(getgpc('page')) ? intval($_GET['page']) : 1;
			$perpage = 50;
			$start = ($page - 1) * $perpage;
			$multipage = multi($checknum, $perpage, $page, $url);

			$checkusers = table_forum_groupuser::t()->groupuserlist($_G['fid'], 'joindateline', $perpage, $start, ['level' => 0]);

		} elseif($do == 'list') {
			if(submitcheck('delsubmit')) {
				foreach($_GET['uid'] as $uid) {
					table_forum_groupuser::t()->delete_by_fid($_G['fid'], $uid);
				}
			}

			$membernum = table_forum_groupuser::t()->fetch_count_by_fid($_G['fid']);
			$page = intval(getgpc('page')) ? intval($_GET['page']) : 1;
			$perpage = 50;
			$start = ($page - 1) * $perpage;
			$multipage = multi($membernum, $perpage, $page, $url);

			$alluserlist = table_forum_groupuser::t()->groupuserlist($_G['fid'], '', $perpage, $start, "AND level>'0'");
		}
	}
}

