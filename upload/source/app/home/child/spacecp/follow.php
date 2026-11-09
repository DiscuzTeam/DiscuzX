<?php
/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$ops = ['add', 'del', 'bkname', 'checkfeed', 'relay', 'getfeed', 'delete', 'newthread'];
$op = in_array($_GET['op'], $ops) ? $_GET['op'] : '';

if(!in_array($op, ['add', 'del', 'bkname'])){
	if(!$_G['setting']['followstatus']) {
		showmessage('follow_status_off');
	}
}

if($op == 'add') {
	$_GET['handlekey'] = $_GET['handlekey'] ? $_GET['handlekey'] : 'followmod';
	$followuid = intval($_GET['fuid']);
	if($_GET['hash'] != FORMHASH || empty($followuid)) {
		exit('Access Denied');
	}
	if($_G['uid'] == $followuid) {
		showmessage('follow_not_follow_self');
	}
	if(!$_G['group']['allowfollow']) {
		showmessage('follow_not_follow_others');
	}
	$special = intval($_GET['special']) ? intval($_GET['special']) : 0;
	$followuser = getuserbyuid($followuid);
	if(empty($followuser)) {
		showmessage('space_does_not_exist');
	}
	// 允许单个用户屏蔽所有人收听 Ta
	$fields = table_common_member_field_home::t()->fetch($followuid);
	if(!$fields['allowasfollow']) {
		showmessage('follow_other_unfollow');
	}
	$mutual = 0;
	$followed = table_home_follow::t()->fetch_by_uid_followuid($followuid, $_G['uid']);
	if(!empty($followed)) {
		if($followed['status'] == '-1') {
			showmessage('follow_other_unfollow');
		}
		$mutual = 1;
		table_home_follow::t()->update_by_uid_followuid($followuid, $_G['uid'], ['mutual' => 1]);
	}
	$followed = table_home_follow::t()->fetch_by_uid_followuid($_G['uid'], $followuid);
	if(empty($followed)) {
		$followdata = [
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'followuid' => $followuid,
			'fusername' => $followuser['username'],
			'status' => 0,
			'mutual' => $mutual,
			'dateline' => TIMESTAMP
		];
		table_home_follow::t()->insert($followdata, false, true);
		table_common_member_count::t()->increase($_G['uid'], ['following' => 1]);
		table_common_member_count::t()->increase($followuid, ['follower' => 1, 'newfollower' => 1]);
		if($_G['setting']['followaddnotice']) {
			notification_add($followuid, 'follower', 'member_follow_add', ['count' => $count, 'from_id' => $_G['uid'], 'from_idtype' => 'following'], 1);
		}
	} elseif($special) {
		$status = $special == 1 ? 1 : 0;
		table_home_follow::t()->update_by_uid_followuid($_G['uid'], $followuid, ['status' => $status]);
		$special = $special == 1 ? 2 : 1;
	} else {
		showmessage('follow_followed_ta');
	}
	$type = !$special ? 'add' : 'special';
	showmessage('follow_add_succeed', dreferer(), ['fuid' => $followuid, 'type' => $type, 'special' => $special, 'from' => !empty($_GET['from']) ? $_GET['from'] : 'list'], ['closetime' => '2', 'showmsg' => '1']);
} elseif($op == 'del') {
	$_GET['handlekey'] = $_GET['handlekey'] ? $_GET['handlekey'] : 'followmod';
	$delfollowuid = intval($_GET['fuid']);
	if($_GET['hash'] != FORMHASH || empty($delfollowuid)) {
		exit('Access Denied');
	}
	$affectedrows = table_home_follow::t()->delete_by_uid_followuid($_G['uid'], $delfollowuid);
	if($affectedrows) {
		table_home_follow::t()->update_by_uid_followuid($delfollowuid, $_G['uid'], ['mutual' => 0]);
		table_common_member_count::t()->increase($_G['uid'], ['following' => -1]);
		table_common_member_count::t()->increase($delfollowuid, ['follower' => -1, 'newfollower' => -1]);
	}
	showmessage('follow_cancel_succeed', dreferer(), ['fuid' => $delfollowuid, 'type' => 'del', 'from' => !empty($_GET['from']) ? $_GET['from'] : 'list'], ['closetime' => '2', 'showmsg' => '1']);
} elseif($op == 'bkname') {
	$followuid = intval($_GET['fuid']);
	$followuser = table_home_follow::t()->fetch_by_uid_followuid($_G['uid'], $followuid);
	if(empty($followuser)) {
		showmessage('follow_not_assignation_user');
	}
	if(submitcheck('editbkname')) {
		$bkname = dhtmlspecialchars(cutstr(strip_tags($_GET['bkname']), 30, ''), ENT_QUOTES);
		table_home_follow::t()->update_by_uid_followuid($_G['uid'], $followuid, ['bkname' => $bkname]);
		showmessage('follow_remark_succeed', dreferer(), ['bkname' => $bkname, 'btnstr' => empty($bkname) ? lang('spacecp', 'follow_add_remark') : lang('spacecp', 'follow_modify_remark')], ['showdialog' => true, 'closetime' => true]);
	}
} elseif($op == 'newthread') {

	if(!helper_access::check_module('follow')) {
		showmessage('quickclear_noperm');
	}

	if(submitcheck('topicsubmit')) {

		if(empty($_GET['syncbbs'])) {
			$fid = intval($_G['setting']['followforumid']);
			if(!($fid && table_forum_forum::t()->fetch($fid))) {
				$fid = 0;
			}
			if(!$fid) {
				$gid = table_forum_forum::t()->fetch_fid_by_name(lang('spacecp', 'follow_specified_group'));
				if(!$gid) {
					$gid = table_forum_forum::t()->insert(['type' => 'group', 'name' => lang('spacecp', 'follow_specified_group'), 'status' => 0], true);
					table_forum_forumfield::t()->insert(['fid' => $gid]);
				}
				$forumarr = [
					'fup' => $gid,
					'type' => 'forum',
					'name' => lang('spacecp', 'follow_specified_forum'),
					'status' => 1,
					'allowsmilies' => 1,
					'allowbbcode' => 1,
					'allowimgcode' => 1
				];
				$fid = table_forum_forum::t()->insert($forumarr, true);
				table_forum_forumfield::t()->insert(['fid' => $fid]);
				table_common_setting::t()->update_setting('followforumid', $fid);
				require_once libfile('function/cache');
				updatecache('setting');
			}

		} else {
			$fid = intval($_GET['fid']);
		}
		loadcache(['bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes', 'domainwhitelist', 'albumcategory']);

		if(empty($_GET['syncbbs'])) {
			$_GET['subject'] = cutstr($_GET['message'], 75, '');
		}
		$_POST['replysubmit'] = true;
		$_GET['fid'] = $fid;
		$_GET['action'] = 'newthread';
		$_GET['allownoticeauthor'] = '1';
		include_once libfile('function/forum');
		require_once libfile('function/post');
		loadforum();
		$_G['forum']['picstyle'] = 0;
		$skipmsg = 1;
		include_once appfile('module/post', 'forum');
	}
} elseif($op == 'relay') {

	if(!helper_access::check_module('follow')) {
		showmessage('quickclear_noperm');
	}
	$tid = intval($_GET['tid']);
	$preview = $post = [];
	$preview = table_forum_threadpreview::t()->fetch($tid);
	if(empty($preview)) {
		$post = table_forum_post::t()->fetch_threadpost_by_tid_invisible($tid);
		if($post['anonymous']) {
			showmessage('follow_anonymous_unfollow');
		}
	}
	if(empty($post) && empty($preview)) {
		showmessage('follow_content_not_exist');
	}

	if(submitcheck('relaysubmit', 0, $seccodecheck, $secqaacheck)) {
		if(strlen($_GET['note']) > 140) {
			showmessage('follow_input_word_limit');
		}
		$count = table_home_follow_feed::t()->count_by_uid_tid($_G['uid'], $tid);
		if(!$count) {
			$count = table_home_follow_feed::t()->count_by_uid_tid($_G['uid'], $tid);
		}
		if($count && empty($_GET['addnewreply'])) {
			showmessage('follow_only_allow_the_relay_time');
		}
		if($_GET['addnewreply']) {

			$_G['setting']['seccodestatus'] = 0;
			$_G['setting']['secqaa']['status'] = 0;

			$_POST['replysubmit'] = true;
			$_GET['tid'] = $tid;
			$_GET['action'] = 'reply';
			$_GET['message'] = $_GET['note'];
			include_once libfile('function/forum');
			require_once libfile('function/post');
			loadforum();

			$inspacecpshare = 1;
			include_once appfile('module/post', 'forum');
		}
		require_once libfile('function/discuzcode');
		require_once libfile('function/followcode');
		$followfeed = [
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'tid' => $tid,
			'note' => cutstr(followcode(dhtmlspecialchars($_GET['note']), 0, 0, 0, false), 140),
			'dateline' => TIMESTAMP
		];
		table_home_follow_feed::t()->insert($followfeed);
		table_common_member_count::t()->increase($_G['uid'], ['feeds' => 1]);
		if(empty($preview)) {
			require_once libfile('function/discuzcode');
			require_once libfile('function/followcode');
			$feedcontent = [
				'tid' => $tid,
				'content' => followcode($post['message'], $post['tid'], $post['pid'], 1000),
			];
			table_forum_threadpreview::t()->insert($feedcontent);
			table_forum_thread::t()->update_status_by_tid($tid, '512');
		} else {
			table_forum_threadpreview::t()->update_relay_by_tid($tid, 1);
		}
		showmessage('relay_feed_success', dreferer(), ['tid' => $tid, 'pid' => $post['pid'], 'reply_mod' => 0], ['showdialog' => true, 'closetime' => true]);
	}
	$fastpost = $_G['setting']['fastpost'];
} elseif($op == 'checkfeed') {

	header('Content-Type: text/javascript');

	require_once libfile('function/member');
	checkfollowfeed();
	exit;
} elseif($op == 'getfeed') {
	$archiver = (bool)$_GET['archiver'];
	$uid = intval($_GET['uid']);
	$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
	if($page < 1) $page = 1;
	$perpage = 20;
	$start = ($page - 1) * $perpage;
	if($uid) {
		$list = getfollowfeed($uid, 'self', $archiver, $start, $perpage);
	} else {
		$type = in_array($_GET['viewtype'], ['special', 'follow', 'other']) ? $_GET['viewtype'] : 'follow';
		$list = getfollowfeed($type == 'other' ? 0 : $_G['uid'], $type, $archiver, $start, $perpage);
	}
	if(empty($list['feed'])) {
		$list = false;
	}
	if(!isset($_G['cache']['forums'])) {
		loadcache('forums');
	}
} elseif($op == 'delete') {
	$archiver = false;
	$feed = table_home_follow_feed::t()->fetch_by_feedid($_GET['feedid']);
	if(empty($feed)) {
		$feed = table_home_follow_feed::t()->fetch_by_feedid($_GET['feedid'], true);
		$archiver = true;
	}
	if(empty($feed)) {
		showmessage('follow_specify_follow_not_exist', '', [], ['return' => true]);
	} elseif($feed['uid'] != $_G['uid'] && $_G['adminid'] != 1) {
		showmessage('quickclear_noperm', '', [], ['return' => true]);
	}

	if(submitcheck('deletesubmit')) {
		if(table_home_follow_feed::t()->delete_by_feedid($_GET['feedid'], $archiver)) {
			table_common_member_count::t()->increase($feed['uid'], ['feeds' => -1]);
			table_forum_threadpreview::t()->update_relay_by_tid($feed['tid'], -1);
			showmessage('do_success', dreferer(), ['feedid' => $_GET['feedid']], ['showdialog' => 1, 'showmsg' => true, 'closetime' => true]);
		} else {
			showmessage('failed_to_delete_operation');
		}
	}
}
include template('home/spacecp_follow');
