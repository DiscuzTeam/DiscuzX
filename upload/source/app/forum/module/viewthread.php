<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');
require_once libfile('function/discuzcode');
require_once libfile('function/post');

$thread = &$_G['forum_thread'];
$forum = &$_G['forum'];
$_G['forum']['extra'] = empty($_G['forum']['extra']) ? [] : dunserialize($_G['forum']['extra']);

if(!empty($_GET['checkrush']) && preg_match('/[^0-9_]/', $_GET['checkrush'])) {
	$_GET['checkrush'] = '';
}

if(!$_G['forum_thread'] || !$_G['forum']) {
	header('HTTP/1.1 404 Not Found');
	showmessage('thread_nonexistence');
}

$page = max(1, $_G['page']);
$_GET['stand'] = isset($_GET['stand']) && in_array($_GET['stand'], ['0', '1', '2']) ? $_GET['stand'] : null;

if($page === 1 && !empty($_G['setting']['antitheft']['allow']) && empty($_G['setting']['antitheft']['disable']['thread']) && empty($_G['forum']['noantitheft'])) {
	helper_antitheft::check($_G['forum_thread']['tid'], 'tid');
}

if($_G['setting']['cachethreadlife'] && $_G['forum']['threadcaches'] && !$_G['uid'] && $page == 1 && !$_G['forum']['special'] && empty($_GET['ordertype']) && empty($_GET['authorid']) && empty($_GET['action']) && empty($_GET['do']) && empty($_GET['from']) && empty($_GET['threadindex']) && !defined('IN_ARCHIVER') && !defined('IN_MOBILE') && !IS_ROBOT) {
	viewthread_loadcache();
}

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : [];
$threadtable_info = !empty($_G['cache']['threadtable_info']) ? $_G['cache']['threadtable_info'] : [];

$archiveid = $thread['threadtableid'];
$thread['is_archived'] = (bool)$archiveid;
$thread['archiveid'] = $archiveid;
$forum['threadtableid'] = $archiveid;
$threadtable = $thread['threadtable'];
$posttableid = $thread['posttableid'];
$posttable = $thread['posttable'];


$_G['action']['fid'] = $_G['fid'];
$_G['action']['tid'] = $_G['tid'];
if($_G['fid'] == getglobal('setting/followforumid') && $_G['adminid'] != 1) {
	dheader('Location: home.php?mod=follow');
}

$st_p = $_G['uid'].'|'.TIMESTAMP;
dsetcookie('st_p', $st_p.'|'.md5($st_p.$_G['config']['security']['authkey']));

$close_leftinfo = intval(getglobal('setting/close_leftinfo'));
if(getglobal('setting/close_leftinfo_userctrl')) {
	if($_G['cookie']['close_leftinfo'] == 1) {
		$close_leftinfo = 1;
	} elseif($_G['cookie']['close_leftinfo'] == 2) {
		$close_leftinfo = 0;
	}
}
$_GET['authorid'] = !empty($_GET['authorid']) ? intval($_GET['authorid']) : 0;
$_GET['ordertype'] = !empty($_GET['ordertype']) ? intval($_GET['ordertype']) : 0;
$_GET['from'] = in_array(getgpc('from'), ['preview', 'portal', 'album']) ? getgpc('from') : '';
if($_GET['from'] == 'portal' && !$_G['setting']['portalstatus']) {
	$_GET['from'] = '';
} elseif($_GET['from'] == 'preview' && !$_G['inajax']) {
	$_GET['from'] = '';
} elseif($_GET['from'] == 'album' && ($_G['setting']['guestviewthumb']['flag'] && !$_G['uid'] && (!defined('IN_MOBILE') || constant('IN_MOBILE') != 2) || !$_G['group']['allowgetimage'])) {
	$_GET['from'] = '';
}

$fromuid = $_G['setting']['creditspolicy']['promotion_visit'] && $_G['uid'] ? '&amp;fromuid='.$_G['uid'] : '';
$feeduid = $_G['forum_thread']['authorid'] ? $_G['forum_thread']['authorid'] : 0;
$feedpostnum = $_G['forum_thread']['replies'] > $_G['ppp'] ? $_G['ppp'] : ($_G['forum_thread']['replies'] ? $_G['forum_thread']['replies'] : 1);

if(!empty($_GET['extra'])) {
	parse_str($_GET['extra'], $extra);
	$_GET['extra'] = [];
	foreach($extra as $_k => $_v) {
		if(preg_match('/^\w+$/', $_k)) {
			if(!is_array($_v)) {
				$_GET['extra'][] = $_k.'='.rawurlencode($_v);
			} else {
				$_GET['extra'][] = http_build_query([$_k => $_v]);
			}
		}
	}
	$_GET['extra'] = implode('&', $_GET['extra']);
}

$_G['forum_threadindex'] = '';
$skipaids = $aimgs = $_G['forum_posthtml'] = [];

$thread['subjectenc'] = rawurlencode($_G['forum_thread']['subject']);
$thread['short_subject'] = cutstr($_G['forum_thread']['subject'], 52);

$navigation = '';
if($_GET['from'] == 'portal') {
	if($_G['forum']['status'] == 3) {
		_checkviewgroup();
	}
	$_G['setting']['ratelogon'] = 1;
	$navigation = ' <em>&rsaquo;</em> <a href="portal.php">'.lang('core', 'portal').'</a>';
	$navsubject = $_G['forum_thread']['subject'];
	$navtitle = $_G['forum_thread']['subject'];

} elseif($_GET['from'] == 'preview') {

	$_G['setting']['ratelogon'] = 1;

} elseif($_G['forum']['status'] == 3) {
	_checkviewgroup();
	$nav = get_groupnav($_G['forum']);
	$navigation = ' <em>&rsaquo;</em> <a href="group.php">'.$_G['setting']['navs'][3]['navname'].'</a> '.$nav['nav'];
	$upnavlink = 'forum.php?mod=forumdisplay&amp;fid='.$_G['fid'].($_GET['extra'] && !IS_ROBOT ? '&amp;'.$_GET['extra'] : '');
	$_G['grouptypeid'] = $_G['forum']['fup'];

} else {
	$navigation = '';
	$upnavlink = 'forum.php?mod=forumdisplay&amp;fid='.$_G['fid'].(getgpc('extra') && !IS_ROBOT ? '&amp;'.$_GET['extra'] : '');

	if($_G['forum']['type'] == 'sub') {
		$fup = $_G['cache']['forums'][$_G['forum']['fup']]['fup'];
		$t_link = $_G['cache']['forums'][$fup]['type'] == 'group' ? 'forum.php?gid='.$fup : 'forum.php?mod=forumdisplay&fid='.$fup;
		$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['cache']['forums'][$fup]['name']).'</a>';
	}

	if($_G['forum']['fup']) {
		$fup = $_G['forum']['fup'];
		$t_link = $_G['cache']['forums'][$fup]['type'] == 'group' ? 'forum.php?gid='.$fup : 'forum.php?mod=forumdisplay&fid='.$fup;
		$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['cache']['forums'][$fup]['name']).'</a>';
	}

	$t_link = 'forum.php?mod=forumdisplay&amp;fid='.$_G['fid'].(getgpc('extra') && !IS_ROBOT ? '&amp;'.$_GET['extra'] : '');
	$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['forum']['name']).'</a>';

	if($archiveid) {
		if($threadtable_info[$archiveid]['displayname']) {
			$t_name = dhtmlspecialchars($threadtable_info[$archiveid]['displayname']);
		} else {
			$t_name = lang('core', 'archive').' '.$archiveid;
		}
		$navigation .= ' <em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&archiveid='.$archiveid.'">'.$t_name.'</a>';
	}

	unset($t_link, $t_name);
}


$_GET['extra'] = getgpc('extra') ? rawurlencode($_GET['extra']) : '';

if(rewriterulecheck('forum_viewthread')) {
	$canonical = rewriteoutput('forum_viewthread', 1, '', $_G['tid'], 1, '', '');
} else {
	$canonical = 'forum.php?mod=viewthread&tid='.$_G['tid'];
}
$_G['setting']['seohead'] .= '<link href="'.$_G['siteurl'].$canonical.'" rel="canonical" />';

$_G['forum_tagscript'] = '';

$threadsort = $thread['sortid'] && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ? 1 : 0;
if($threadsort) {
	require_once childfile('threadsorts');
}

if(empty($_G['forum']['allowview'])) {

	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
		showmessage('group_nopermission', NULL, ['grouptitle' => $_G['group']['grouptitle']], ['login' => 1]);
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		showmessagenoperm('viewperm', $_G['fid'], $_G['forum']['formulaperm']);
	}

} elseif($_G['forum']['allowview'] == -1) {
	showmessage('forum_access_view_disallow');
}

if($_G['forum']['formulaperm']) {
	formulaperm($_G['forum']['formulaperm']);
}

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	dheader("Location: {$_G['siteurl']}forum.php?mod=forumdisplay&fid={$_G['fid']}");
}

if($_G['forum']['price'] && !$_G['forum']['ismoderator']) {
	$membercredits = table_common_member_forum_buylog::t()->get_credits($_G['uid'], $_G['fid']);
	$paycredits = $_G['forum']['price'] - $membercredits;
	if($paycredits > 0) {
		dheader("Location: {$_G['siteurl']}forum.php?mod=forumdisplay&fid={$_G['fid']}");
	}
}

if($_G['forum_thread']['readperm'] && $_G['forum_thread']['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $_G['forum_thread']['authorid'] != $_G['uid']) {
	showmessage('thread_nopermission', NULL, ['readperm' => $_G['forum_thread']['readperm']], ['login' => 1]);
}

$usemagic = ['user' => [], 'thread' => []];

$replynotice = getstatus($_G['forum_thread']['status'], 6);

$hiddenreplies = getstatus($_G['forum_thread']['status'], 2);

$rushreply = getstatus($_G['forum_thread']['status'], 3);

$savepostposition = getstatus($_G['forum_thread']['status'], 1);

$incollection = getstatus($_G['forum_thread']['status'], 9);

$_G['forum_threadpay'] = FALSE;
if($_G['forum_thread']['price'] > 0 && $_G['forum_thread']['special'] == 0) {
	if($_G['setting']['maxchargespan'] && TIMESTAMP - $_G['forum_thread']['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
		table_forum_thread::t()->update($_G['tid'], ['price' => 0], false, false, $archiveid);
		$_G['forum_thread']['price'] = 0;
	} else {
		$exemptvalue = $_G['forum']['ismoderator'] ? 128 : 16;
		if(!($_G['group']['exempt'] & $exemptvalue) && $_G['forum_thread']['authorid'] != $_G['uid']) {
			if(!(table_common_credit_log::t()->count_by_uid_operation_relatedid($_G['uid'], 'BTC', $_G['tid']))) {
				require_once childfile('pay', 'forum/thread');
				$_G['forum_threadpay'] = TRUE;
			}
		}
	}
}

if($rushreply) {
	$rewardfloor = '';
	$rushresult = $rewardfloorarr = $rewardfloorarray = [];
	$rushresult = table_forum_threadrush::t()->fetch($_G['tid']);
	if($rushresult['creditlimit'] == -996) {
		$rushresult['creditlimit'] = '';
	}
	if((TIMESTAMP < $rushresult['starttimefrom'] || ($rushresult['starttimeto'] && TIMESTAMP > $rushresult['starttimeto']) || ($rushresult['stopfloor'] && $_G['forum_thread']['replies'] + 1 >= $rushresult['stopfloor'])) && $_G['forum_thread']['closed'] == 0) {
		table_forum_thread::t()->update($_G['tid'], ['closed' => 1]);
	} elseif(($rushresult['starttimefrom'] && TIMESTAMP > $rushresult['starttimefrom']) && $_G['forum_thread']['closed'] == 1) {
		if(($rushresult['starttimeto'] && TIMESTAMP < $rushresult['starttimeto'] || !$rushresult['starttimeto']) && ($rushresult['stopfloor'] && $_G['forum_thread']['replies'] + 1 < $rushresult['stopfloor'] || !$rushresult['stopfloor'])) {
			table_forum_thread::t()->update($_G['tid'], ['closed' => 0]);
		}
	}
	if($rushresult['starttimefrom'] > TIMESTAMP) {
		$rushresult['timer'] = $rushresult['starttimefrom'] - TIMESTAMP;
		$rushresult['timertype'] = 'start';
	} elseif($rushresult['starttimeto'] > TIMESTAMP) {
		$rushresult['timer'] = $rushresult['starttimeto'] - TIMESTAMP;
		$rushresult['timertype'] = 'end';
	}
	$rushresult['starttimefrom'] = $rushresult['starttimefrom'] ? dgmdate($rushresult['starttimefrom']) : '';
	$rushresult['starttimeto'] = $rushresult['starttimeto'] ? dgmdate($rushresult['starttimeto']) : '';
	$rushresult['creditlimit_title'] = $_G['setting']['creditstransextra'][11] ? $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][11]]['title'] : lang('forum/misc', 'credit_total');
}

if($_G['forum_thread']['replycredit'] > 0) {
	$_G['forum_thread']['replycredit_rule'] = table_forum_replycredit::t()->fetch($thread['tid']);
	$_G['forum_thread']['replycredit_rule']['remaining'] = $_G['forum_thread']['replycredit'] / $_G['forum_thread']['replycredit_rule']['extcredits'];
	$_G['forum_thread']['replycredit_rule']['extcreditstype'] = $_G['forum_thread']['replycredit_rule']['extcreditstype'] ? $_G['forum_thread']['replycredit_rule']['extcreditstype'] : $_G['setting']['creditstransextra'][10];
} else {
	$_G['forum_thread']['replycredit_rule']['extcreditstype'] = $_G['setting']['creditstransextra'][10];
}
$_G['group']['raterange'] = $_G['setting']['modratelimit'] && $adminid == 3 && !$_G['forum']['ismoderator'] ? [] : $_G['group']['raterange'];

$_G['group']['allowgetattach'] = (!empty($_G['forum']['allowgetattach'])) ? $_G['forum']['allowgetattach'] == 1 : ($_G['forum']['getattachperm'] ? forumperm($_G['forum']['getattachperm']) : $_G['group']['allowgetattach']);
$_G['group']['allowgetimage'] = (!empty($_G['forum']['allowgetimage'])) ? $_G['forum']['allowgetimage'] == 1 : ($_G['forum']['getattachperm'] ? forumperm($_G['forum']['getattachperm']) : $_G['group']['allowgetimage']);
$_G['getattachcredits'] = '';
if($_G['forum_thread']['attachment']) {
	$exemptvalue = $_G['forum']['ismoderator'] ? 32 : 4;
	if(!($_G['group']['exempt'] & $exemptvalue)) {
		$creditlog = updatecreditbyaction('getattach', $_G['uid'], [], '', 1, 0, $_G['forum_thread']['fid']);
		$p = '';
		if($creditlog['updatecredit']) for($i = 1; $i <= 8; $i++) {
			if($policy = $creditlog['extcredits'.$i]) {
				$_G['getattachcredits'] .= $p.$_G['setting']['extcredits'][$i]['title'].' '.$policy.' '.$_G['setting']['extcredits'][$i]['unit'];
				$p = ', ';
			}
		}
	}
}

$exemptvalue = $_G['forum']['ismoderator'] ? 64 : 8;
$_G['forum_attachmentdown'] = $_G['group']['exempt'] & $exemptvalue;

list($seccodecheck, $secqaacheck) = seccheck('post', 'reply');
$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'];

$postlist = $_G['forum_attachtags'] = $attachlist = $_G['forum_threadstamp'] = [];
$aimgcount = 0;
$_G['forum_attachpids'] = [];

if(!empty($_GET['action']) && $_GET['action'] == 'printable' && $_G['tid']) {
	require_once childfile('printable', 'forum/thread');
	dexit();
}

if($_G['forum_thread']['stamp'] >= 0) {
	$_G['forum_threadstamp'] = $_G['cache']['stamps'][$_G['forum_thread']['stamp']];
}

$lastmod = viewthread_lastmod($_G['forum_thread']);

$showsettings = str_pad(decbin($_G['setting']['showsettings']), 3, '0', STR_PAD_LEFT);

$showsignatures = $showsettings[0];
$showavatars = $showsettings[1];
$_G['setting']['showimages'] = $showsettings[2];

$highlightstatus = isset($_GET['highlight']) && str_replace('+', '', $_GET['highlight']) ? 1 : 0;

$_G['forum']['allowreply'] = $_G['forum']['allowreply'] ?? '';
$_G['forum']['allowpost'] = $_G['forum']['allowpost'] ?? '';

$allowpostreply = ($_G['forum']['allowreply'] != -1) && (($_G['forum_thread']['isgroup'] || (!$_G['forum_thread']['closed'] && !checkautoclose($_G['forum_thread']))) || $_G['forum']['ismoderator']) && ((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])) || $_G['forum']['allowreply']);
$fastpost = $_G['setting']['fastpost'] && !$_G['forum_thread']['archiveid'] && ($_G['forum']['status'] != 3 || $_G['isgroupuser']);
$allowfastpost = $_G['setting']['fastpost'] && $allowpostreply;
if(!$_G['uid'] && (getglobal('setting/need_avatar') || getglobal('setting/need_secmobile') || getglobal('setting/need_email') || getglobal('setting/need_friendnum')) || in_array($_G['adminid'], [0, -1]) && (!cknewuser(1) || $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60) && TIMESTAMP - getglobal('member/regdate') < $_G['setting']['newbiespan'] * 60)) {
	$allowfastpost = false;
}
$_G['group']['allowpost'] = $_G['forum']['allowpost'] != -1 && ((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])) || $_G['forum']['allowpost']);

$_G['forum']['allowpostattach'] = $_G['forum']['allowpostattach'] ?? '';
$allowpostattach = $allowpostreply && ($_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm']))));

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4) && isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]);
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
} else {
	$_G['group']['allowpostpoll'] = $_G['group']['allowposttrade'] = $_G['group']['allowpostreward'] = $_G['group']['allowpostactivity'] = $_G['group']['allowpostdebate'] = FALSE;
}

$_G['forum']['threadplugin'] = $_G['group']['allowpost'] && $_G['setting']['threadplugins'] ? is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : dunserialize($_G['forum']['threadplugin']) : [];

$_G['setting']['visitedforums'] = $_G['setting']['visitedforums'] && $_G['forum']['status'] != 3 ? visitedforums() : '';


$relatedthreadlist = [];
$relatedthreadupdate = $tagupdate = FALSE;
$relatedkeywords = $tradekeywords = $_G['forum_firstpid'] = '';

if(!isset($_G['cookie']['collapse']) || !str_contains($_G['cookie']['collapse'], 'modarea_c')) {
	$collapseimg['modarea_c'] = 'collapsed_no';
	$collapse['modarea_c'] = '';
} else {
	$collapseimg['modarea_c'] = 'collapsed_yes';
	$collapse['modarea_c'] = 'display: none';
}

$threadtag = [];
viewthread_updateviews($archiveid);

$postfieldsadd = $specialadd1 = $specialadd2 = $specialextra = '';
$tpids = [];
if($_G['forum_thread']['special'] == 2) {
	if(!empty($_GET['do']) && $_GET['do'] == 'tradeinfo') {
		require_once childfile('trade', 'forum/thread');
	}
	$query = table_forum_trade::t()->fetch_all_thread_goods($_G['tid']);
	foreach($query as $trade) {
		$tpids[] = $trade['pid'];
	}
	$specialadd2 = 1;

} elseif($_G['forum_thread']['special'] == 5) {
	if(isset($_GET['stand'])) {
		$specialadd2 = 1;
		$specialextra = "&amp;stand={$_GET['stand']}";
	}
}

$onlyauthoradd = $threadplughtml = '';
$postarr = [];
$maxposition = 0;
if(empty($_GET['viewpid'])) {
	if(!in_array($_G['forum_thread']['special'], [2, 3, 5])) {
		$disablepos = !$rushreply && table_forum_threaddisablepos::t()->fetch($_G['tid']) ? 1 : 0;
		if(!$disablepos) {
			if($_G['forum_thread']['maxposition']) {
				$maxposition = $_G['forum_thread']['maxposition'];
			} else {
				$maxposition = table_forum_post::t()->fetch_maxposition_by_tid($posttableid, $_G['tid']);
				table_forum_thread::t()->update($_G['tid'], ['maxposition' => $maxposition]);
			}
		}
	}

	$ordertype = empty($_GET['ordertype']) && getstatus($_G['forum_thread']['status'], 4) ? 1 : $_GET['ordertype'];
	if($_GET['from'] == 'album') {
		$ordertype = 1;
	}
	$sticklist = [];
	if($_G['page'] === 1 && $_G['forum_thread']['stickreply'] && empty($_GET['authorid'])) {
		$poststick = table_forum_poststick::t()->fetch_all_by_tid($_G['tid']);
		foreach(table_forum_post::t()->fetch_all_post($posttableid, array_keys($poststick)) as $post) {
			if($post['invisible'] != 0) {
				continue;
			}
			$post['position'] = $poststick[$post['pid']]['position'];
			$post['avatar'] = avatar($post['authorid'], 'small');
			$post['isstick'] = true;
			$sticklist[$post['pid']] = $post;
		}
		$stickcount = count($sticklist);
	}
	if($rushreply) {
		$rushids = $rushpids = $rushpositionlist = $preg = $arr = [];
		$str = ',,';
		$preg_str = rushreply_rule($rushresult);
		if($_GET['checkrush']) {
			$maxposition = 0;
			for($i = 1; $i <= $_G['forum_thread']['replies'] + 1; $i++) {
				$str = $str.$i.',,';
			}
			preg_match_all($preg_str, $str, $arr);
			$arr = $arr[0];
			foreach($arr as $var) {
				$var = str_replace(',', '', $var);
				$rushids[$var] = $var;
			}
			$temp_reply = $_G['forum_thread']['replies'];
			$_G['forum_thread']['replies'] = $countrushpost = max(0, count($rushids) - 1);
			$countrushpost = max(0, count($rushids));
			$rushids = array_slice($rushids, ($page - 1) * $_G['ppp'], $_G['ppp']);
			foreach(table_forum_post::t()->fetch_all_by_tid_position($posttableid, $_G['tid'], $rushids) as $post) {
				$postarr[$post['position']] = $post;
			}
		} else {
			for($i = ($page - 1) * $_G['ppp'] + 1; $i <= $page * $_G['ppp']; $i++) {
				$str = $str.$i.',,';
			}
			preg_match_all($preg_str, $str, $arr);
			$arr = $arr[0];
			foreach($arr as $var) {
				$var = str_replace(',', '', $var);
				$rushids[$var] = $var;
			}
			$_G['forum_thread']['replies'] = $_G['forum_thread']['replies'] - 1;
		}
	}

	if(!empty($_GET['authorid'])) {
		$maxposition = 0;
		$_G['forum_thread']['replies'] = table_forum_post::t()->count_by_tid_invisible_authorid($_G['tid'], $_GET['authorid']);
		$_G['forum_thread']['replies']--;
		if($_G['forum_thread']['replies'] < 0) {
			showmessage('undefined_action');
		}
		$onlyauthoradd = 1;
	} elseif($_G['forum_thread']['special'] == 5) {
		if(isset($_GET['stand']) && $_GET['stand'] >= 0 && $_GET['stand'] < 3) {
			$_G['forum_thread']['replies'] = table_forum_debatepost::t()->count_by_tid_stand($_G['tid'], $_GET['stand']);
		} else {
			$_G['forum_thread']['replies'] = table_forum_post::t()->count_visiblepost_by_tid($_G['tid']);
			$_G['forum_thread']['replies'] > 0 && $_G['forum_thread']['replies']--;
		}
	} elseif($_G['forum_thread']['special'] == 2) {
		$tradenum = table_forum_trade::t()->fetch_counter_thread_goods($_G['tid']);
		$_G['forum_thread']['replies'] -= $tradenum;
	}

	if($maxposition) {
		$_G['forum_thread']['replies'] = $maxposition - 1;
	}
	$_G['ppp'] = $_G['forum']['threadcaches'] && !$_G['uid'] ? $_G['setting']['postperpage'] : $_G['ppp'];
	$totalpage = ceil(($_G['forum_thread']['replies'] + 1) / $_G['ppp']);
	$page > $totalpage && $page = $totalpage;
	$_G['forum_pagebydesc'] = !$maxposition && $page > 2 && $page > ($totalpage / 2);

	if($_G['forum_pagebydesc']) {
		$firstpagesize = ($_G['forum_thread']['replies'] + 1) % $_G['ppp'];
		$_G['forum_ppp3'] = $_G['forum_ppp2'] = $page == $totalpage && $firstpagesize ? $firstpagesize : $_G['ppp'];
		$realpage = $totalpage - $page + 1;
		if($firstpagesize == 0) {
			$firstpagesize = $_G['ppp'];
		}
		$start_limit = max(0, ($realpage - 2) * $_G['ppp'] + $firstpagesize);
		$_G['forum_numpost'] = ($page - 1) * $_G['ppp'];
		if($ordertype != 1) {
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page == $totalpage ? 1 : 0);
		}
	} else {
		$start_limit = $_G['forum_numpost'] = max(0, ($page - 1) * $_G['ppp']);
		if($start_limit > $_G['forum_thread']['replies']) {
			$start_limit = $_G['forum_numpost'] = 0;
			$page = 1;
		}
		if($ordertype != 1) {
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'];
		}
	}
	$multipageparam = ($_G['forum_thread']['is_archived'] ? '&archive='.$_G['forum_thread']['archiveid'] : '').
		'&extra='.$_GET['extra'].
		($ordertype && $ordertype != getstatus($_G['forum_thread']['status'], 4) ? '&ordertype='.$ordertype : '').
		(isset($_GET['highlight']) ? '&highlight='.rawurlencode($_GET['highlight']) : '').
		(!empty($_GET['authorid']) ? '&authorid='.$_GET['authorid'] : '').
		(!empty($_GET['from']) ? '&from='.$_GET['from'] : '').
		(!empty($_GET['checkrush']) ? '&checkrush='.$_GET['checkrush'] : '').
		(!empty($_GET['modthreadkey']) ? '&modthreadkey='.rawurlencode($_GET['modthreadkey']) : '').
		$specialextra;
	$multipage = multi($_G['forum_thread']['replies'] + ($ordertype != 1 ? 1 : 0), $_G['ppp'], $page, 'forum.php?mod=viewthread&tid='.$_G['tid'].$multipageparam);
} else {
	$_GET['viewpid'] = intval($_GET['viewpid']);
	$pageadd = "AND p.pid='{$_GET['viewpid']}'";
}

$_G['forum_newpostanchor'] = $_G['forum_postcount'] = 0;

$_G['forum_onlineauthors'] = $_G['forum_cachepid'] = $_G['blockedpids'] = [];


$isdel_post = $cachepids = $postusers = $skipaids = [];

if($_G['forum_auditstatuson'] || in_array($_G['forum_thread']['displayorder'], [-2, -3, -4]) && $_G['forum_thread']['authorid'] == $_G['uid']) {
	$visibleallflag = 1;
}

require_once childfile('postarr');

foreach($postarr as $post) {
	if(($onlyauthoradd && empty($post['anonymous']) || !$onlyauthoradd) && !isset($postlist[$post['pid']])) {
		$post['authorself'] = false;

		if(isset($hotpostarr[$post['pid']])) {
			$post['existinfirstpage'] = true;
		}

		$post['incurpage'] = in_array($post['pid'], $curpagepids);
		$postusers[$post['authorid']] = [];

		$post['postappend'] = '';
		if(strpos($post['message'], '[postappend]') !== false) {
			preg_match_all('/\[postappend\](.*?)\[\/postappend\]/s', $post['message'], $m);
			$post['postappend'] = implode("\n\n", $m[1]);
			$post['message'] = str_replace(['[postappend]', '[/postappend]'], '', $post['message']);
		}

		// 开始解析json编辑器内容
		if(is_valid_non_empty_json($post['content'], true)) {
			$content = json_decode($post['content'], true);
			if($content['type'] == 'json' && $content['editor'] == 'jsonEditor' && !empty($content['content'])) {
				list($parserData, $styleData) = editor::parser($content['content']);
				if($_G['setting']['editor_global_css']) {
					$styleData .= $_G['setting']['editor_global_css'];
				}
				$sppos = strpos($post['message'], chr(0).chr(0).chr(0));
				$specialextra = substr($post['message'], $sppos + 3);
				if(!defined('IN_RESTFUL')) {
					$post['message'] = $parserData.$styleData.($sppos !== false ? chr(0).chr(0).chr(0).$specialextra : '');
				} else {
					$post['message'] = $parserData.($sppos !== false ? chr(0).chr(0).chr(0).$specialextra : '');
					if($_REQUEST['removestyle']) {
						$pattern = '/\<style(\s+.*?)?\>/s';
						$styleData = preg_replace($pattern, '', $styleData);
						$pattern = '/\<\/style(\s+.*?)?\>/s';
						$styleData = preg_replace($pattern, '', $styleData);
					}
					$styleData = str_replace(["\r", "\n", "\t"], '', $styleData);
					$post['style'] = $styleData;
				}
				if(!empty($content['extend']['quote_default'])) {
					$post['message'] = discuzcode($content['extend']['quote_default']).$post['message'];
				}
				if(!empty($post['postappend'])) {
					$post['message'] .= "<br/><br/>".discuzcode($post['postappend']);
				}
			}
		}
		// 结束解析json编辑器内容
		if($post['first']) {
			if($ordertype == 1 && $page != 1) {
				continue;
			}
			$_G['forum_firstpid'] = $post['pid'];

			if($_G['forum_thread']['price']) {
				$summary = str_replace(["\r", "\n"], '', messagecutstr(strip_tags($thread['freemessage']), 160));
			} else {
				$summary = str_replace(["\r", "\n"], '', messagecutstr(strip_tags($post['message']), 160));
			}
			$tagarray_all = $posttag_array = [];
			$tagarray_all = explode("\t", $post['tags']);
			if($tagarray_all) {
				foreach($tagarray_all as $var) {
					if($var) {
						$tag = explode(',', $var);
						$posttag_array[] = $tag;
						$tagnames[] = $tag[1];
					}
				}
			}
			$post['tags'] = $posttag_array;
			if($post['tags']) {
				$post['relateitem'] = getrelateitem($post['tags'], $post['tid'], $_G['setting']['relatenum'], $_G['setting']['relatetime']);
			}
			if(!$_G['forum']['disablecollect']) {
				if($incollection) {
					$post['relatecollection'] = getrelatecollection($post['tid'], false, $post['releatcollectionnum'], $post['releatcollectionmore']);
					if($_G['group']['allowcommentcollection'] && $_GET['ctid']) {
						$ctid = dintval($_GET['ctid']);
						$post['sourcecollection'] = table_forum_collection::t()->fetch($ctid);
					}
				} else {
					$post['releatcollectionnum'] = 0;
				}
			}
		}
		if($thread['authorid'] && $post['authorid'] == $thread['authorid'] && $post['first'] !== '1') {
			$post['authorself'] = true;
		}
		$postlist[$post['pid']] = $post;
	}
}
unset($hotpostarr);
$seodata = ['forum' => $_G['forum']['name'], 'fup' => $_G['cache']['forums'][$fup]['name'], 'subject' => $_G['forum_thread']['subject'], 'summary' => $summary, 'tags' => @implode(',', $tagnames), 'page' => intval(getgpc('page'))];
if($_G['forum']['status'] != 3) {
	$seotype = 'viewthread';
} else {
	$seotype = 'viewthread_group';
	$seodata['first'] = $nav['first']['name'];
	$seodata['second'] = $nav['second']['name'];
	$seodata['gdes'] = $_G['forum']['description'];
}

list($navtitle, $metadescription, $metakeywords) = get_seosetting($seotype, $seodata);
if(!$navtitle) {
	$navtitle = helper_seo::get_title_page($_G['forum_thread']['subject'], $_G['page']).' - '.strip_tags($_G['forum']['name']);
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metakeywords) {
	$metakeywords = strip_tags($thread['subject']);
}
if(!$metadescription) {
	$metadescription = $summary.' '.strip_tags($_G['forum_thread']['subject']);
}

$_G['allblocked'] = true;
$postno = &$_G['cache']['custominfo']['postno'];
$postnostick = str_replace(['<sup>', '</sup>'], '', $postno[0]);

require_once childfile('postlist');

if($_G['forum_thread']['special'] > 0 && (empty($_GET['viewpid']) || $_GET['viewpid'] == $_G['forum_firstpid'])) {
	require_once childfile('special');
}
if(empty($_GET['authorid']) && empty($postlist)) {
	if($rushreply) {
		dheader("Location: forum.php?mod=redirect&tid={$_G['tid']}&goto=lastpost");
	} else {
		$replies = table_forum_post::t()->count_visiblepost_by_tid($_G['tid']);
		$replies = intval($replies) - 1;
		if($_G['forum_thread']['replies'] != $replies && $replies > 0) {
			table_forum_thread::t()->update($_G['tid'], ['replies' => $replies], false, false, $archiveid);
			dheader("Location: forum.php?mod=redirect&tid={$_G['tid']}&goto=lastpost");
		}
	}
}

if($_G['forum_pagebydesc'] && (!$savepostposition || $_GET['ordertype'] == 1)) {
	$postlist = array_reverse($postlist, TRUE);
}

if(!empty($_G['setting']['sessionclose'])) {
	$_G['setting']['vtonlinestatus'] = 1;
}

if($_G['setting']['vtonlinestatus'] == 2 && $_G['forum_onlineauthors']) {
	foreach(C::app()->session->fetch_all_by_uid(array_keys($_G['forum_onlineauthors'])) as $author) {
		if(!$author['invisible']) {
			$_G['forum_onlineauthors'][$author['uid']] = 1;
		}
	}
} else {
	$_G['forum_onlineauthors'] = [];
}
$ratelogs = $comments = $commentcount = $totalcomment = [];
if($_G['forum_cachepid']) {
	foreach(table_forum_postcache::t()->fetch_all($_G['forum_cachepid']) as $postcache) {
		if($postcache['rate']) {
			$postcache['rate'] = dunserialize($postcache['rate']);
			$postlist[$postcache['pid']]['ratelog'] = dhtmlspecialchars($postcache['rate']['ratelogs']);
			$postlist[$postcache['pid']]['ratelogextcredits'] = $postcache['rate']['extcredits'];
			$postlist[$postcache['pid']]['totalrate'] = $postcache['rate']['totalrate'];
		}
		if($postcache['comment']) {
			$postcache['comment'] = dunserialize($postcache['comment']);
			$commentcount[$postcache['pid']] = $postcache['comment']['count'];
			$comments[$postcache['pid']] = $postcache['comment']['data'];
			$totalcomment[$postcache['pid']] = $postcache['comment']['totalcomment'];
		}
		unset($_G['forum_cachepid'][$postcache['pid']]);
	}
	$postcache = $ratelogs = [];
	if($_G['forum_cachepid']) {
		list($ratelogs, $postlist, $postcache) = table_forum_ratelog::t()->fetch_postrate_by_pid($_G['forum_cachepid'], $postlist, $postcache, $_G['setting']['ratelogrecord']);
	}
	foreach($postlist as $key => $val) {
		if(!empty($val['ratelogextcredits'])) {
			ksort($postlist[$key]['ratelogextcredits']);
		}
	}

	if($_G['forum_cachepid'] && $_G['setting']['commentnumber']) {
		list($commentsnew, $postcache, $commentcount, $totalcomment) = table_forum_postcomment::t()->fetch_postcomment_by_pid($_G['forum_cachepid'], $postcache, $commentcount, $totalcomment, $_G['setting']['commentnumber']);
		$comments = $commentsnew + $comments;
	}

	foreach($postcache as $pid => $data) {
		table_forum_postcache::t()->insert(['pid' => $pid, 'rate' => serialize($data['rate']), 'comment' => serialize($data['comment']), 'dateline' => TIMESTAMP], false, true);
	}
}

if($_G['forum_attachpids'] && !defined('IN_ARCHIVER')) {
	require_once libfile('function/attachment');
	if(is_array($threadsortshow) && !empty($threadsortshow['sortaids'])) {
		$skipaids = $threadsortshow['sortaids'];
	}
	parseattach($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, $skipaids);
}
// 开始将json编辑器中的图片从未使用列表中移除
foreach($postlist as $pid => $post) {
	if(!empty($post) && is_valid_non_empty_json($post['content'], true)) {
		$content = json_decode($post['content'], true);
		if($content['type'] == 'json' && $content['editor'] == 'jsonEditor' && !empty($content['content'])) {
			$post['imagelist'] = [];
			$post['imagelistcount'] = 0;
			$post['attachlist'] = [];
			$postlist[$pid] = $post;
		}
	}
	//支持IP省级地理信息
	if($_G['setting']['showiplocation']) {
		$post['iplocation'] = ip::convert($post['useip'], true);
		$postlist[$pid] = $post;
	}
}
// 结束将json编辑器中的图片从未使用列表中移除

if(empty($postlist)) {
	if($thread['closed'] > 1 && $thread['isgroup'] != 1) {
		if(rewriterulecheck('forum_viewthread')) {
			$canonical = rewriteoutput('forum_viewthread', 1, '', $thread['closed'], 1, '', '');
		} else {
			$canonical = 'forum.php?mod=viewthread&tid='.$thread['closed'];
		}
		dheader('Location:'.$_G['siteurl'].$canonical);
	}
	showmessage('post_not_found');
} elseif(!defined('IN_MOBILE_API')) {
	foreach($postlist as $pid => $post) {
		$postlist[$pid]['message'] = preg_replace('/\[attach\]\d+\[\/attach\]/i', '', $postlist[$pid]['message']);
	}
}

if(defined('IN_ARCHIVER')) {
	include loadarchiver('forum/viewthread');
	exit();
}

$_G['forum_thread']['heatlevel'] = $_G['forum_thread']['recommendlevel'] = 0;
if($_G['setting']['heatthread']['iconlevels']) {
	foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
		if($_G['forum_thread']['heats'] > $i) {
			$_G['forum_thread']['heatlevel'] = $k + 1;
			break;
		}
	}
}

if(!empty($_G['setting']['recommendthread']['status']) && $_G['forum_thread']['recommends']) {
	foreach($_G['setting']['recommendthread']['iconlevels'] as $k => $i) {
		if($_G['forum_thread']['recommends'] > $i) {
			$_G['forum_thread']['recommendlevel'] = $k + 1;
			break;
		}
	}
}

$allowblockrecommend = getglobal('group/allowdiy') || getstatus(getglobal('member/allowadmincp'), 4) || getstatus(getglobal('member/allowadmincp'), 5) || getstatus(getglobal('member/allowadmincp'), 6);
if($_G['setting']['portalstatus']) {
	$allowpostarticle = $_G['group']['allowmanagearticle'] || $_G['group']['allowpostarticle'] || getstatus($_G['member']['allowadmincp'], 2) || getstatus($_G['member']['allowadmincp'], 3);
	$allowpusharticle = empty($_G['forum_thread']['special']) && empty($_G['forum_thread']['sortid']) && !$_G['forum_thread']['pushedaid'];
} else {
	$allowpostarticle = $allowpusharticle = false;
}
if($_G['forum_thread']['displayorder'] != -4) {
	$modmenu = [
		'thread' => $_G['forum']['ismoderator'] || $allowblockrecommend || $allowpusharticle && $allowpostarticle,
		'post' => $_G['forum']['ismoderator'] && ($_G['group']['allowwarnpost'] || $_G['group']['allowbanpost'] || $_G['group']['allowdelpost'] || $_G['group']['allowstickreply']) || $_G['forum_thread']['pushedaid'] && $allowpostarticle || $_G['forum_thread']['authorid'] == $_G['uid']
	];
} else {
	$modmenu = [];
}

if($_G['forum']['alloweditpost'] && $_G['uid']) {
	$alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $_G['forum_thread']['special'] + 1);
	if(!$alloweditpost_status) {
		$edittimelimit = $_G['group']['edittimelimit'] * 60;
	}
}

if($_G['forum_thread']['replies'] > $_G['forum_thread']['views']) {
	$_G['forum_thread']['views'] = $_G['forum_thread']['replies'];
}

require_once libfile('function/upload');
$swfconfig = getuploadconfig($_G['uid'], $_G['fid']);
$_G['forum_thread']['relay'] = 0;

if(getstatus($_G['forum_thread']['status'], 10)) {
	$preview = table_forum_threadpreview::t()->fetch($_G['tid']);
	$_G['forum_thread']['relay'] = $preview['relay'];
}

if(!empty($_G['forum']['threadsorts']['suptypeid']) && !empty($_G['cache']['threadsort_template_'.$_G['forum']['threadsorts']['suptypeid']]['super']['viewthread'])) {
	[$pluginid, $tpl] = explode(':', $_G['cache']['threadsort_template_'.$_G['forum']['threadsorts']['suptypeid']]['super']['viewthread']);
	if(!$tpl) {
		$tpl = $pluginid;
		$tpldir = '';
	} else {
		$tpldir = DISCUZ_PLUGIN($pluginid).'/template';
	}
	include template('diy:'.$tpl.':'.$_G['fid'], 0, $tpldir);
	exit;
}

if(empty($_GET['viewpid'])) {
	$sufix = '';
	if($_GET['from'] == 'portal') {
		$_G['disabledwidthauto'] = 1;
		$_G['widthauto'] = 0;
		$sufix = '_portal';
		$post = &$postlist[$_G['forum_firstpid']];
	} elseif($_GET['from'] == 'preview') {
		$_G['disabledwidthauto'] = 1;
		$_G['widthauto'] = 0;
		$sufix = '_preview';
	} elseif($_GET['from'] == 'album') {
		$_G['disabledwidthauto'] = 1;
		$_G['widthauto'] = 0;
		$sufix = '_album';
		$post = &$postlist[$_G['forum_firstpid']];
		// 暂不确认对于 JS 类内容是否都需要添加 ignore_js_op 标签供 _relatedlinks 和只看大图模式正文摘要过滤用
		$post['message'] = cutstr(strip_tags(preg_replace('/(<ignore_js_op>.*<\/ignore_js_op>)/is', '', $post['message'])), 200);
		require_once childfile('album', 'forum/thread');;
	}
	include template('diy:forum/viewthread'.$sufix.':'.$_G['fid']);
} else {
	$_G['setting']['admode'] = 0;
	$post = $postlist[$_GET['viewpid']];
	if($maxposition) {
		$post['number'] = $post['position'];
	} else {
		$post['number'] = table_forum_post::t()->count_by_tid_dateline($posttableid, $post['tid'], $post['dbdateline']);
	}
	if($rushreply) {
		$post['number'] = $post['position'];
		$preg_str = rushreply_rule($rewardfloorarr);
		preg_match_all($preg_str, ',,'.$post['number'].',,', $arr);
		if($post['number'] == str_replace(',', '', $arr['0']['0'])) {
			$post['rewardfloor'] = 1;
		}
	}
	include template('common/header_ajax');
	hookscriptoutput('viewthread');
	$postcount = 0;
	if($_GET['from']) {
		include template($_GET['from'] == 'preview' ? 'forum/viewthread_preview_node' : 'forum/viewthread_from_node');
	} else {
		include template('forum/viewthread_node');
	}
	include template('common/footer_ajax');
}


function viewthread_updateviews($tableid) {
	global $_G;

	if(!$_G['setting']['preventrefresh'] || getcookie('viewid') != 'tid_'.$_G['tid']) {
		if(!$tableid && getglobal('setting/optimizeviews')) {
			if(isset($_G['forum_thread']['addviews'])) {
				if($_G['forum_thread']['addviews'] < 100) {
					table_forum_threadaddviews::t()->update_by_tid($_G['tid']);
				} else {
					if(!discuz_process::islocked('update_thread_view')) {
						$row = table_forum_threadaddviews::t()->fetch($_G['tid']);
						table_forum_threadaddviews::t()->update($_G['tid'], ['addviews' => 0]);
						table_forum_thread::t()->increase($_G['tid'], ['views' => $row['addviews'] + 1], true);
						discuz_process::unlock('update_thread_view');
					}
				}
			} else {
				table_forum_threadaddviews::t()->insert(['tid' => $_G['tid'], 'addviews' => 1], false, true);
			}
		} else {
			table_forum_thread::t()->increase($_G['tid'], ['views' => 1], true, $tableid);
		}
	}
	dsetcookie('viewid', 'tid_'.$_G['tid']);
}

function viewthread_procpost($post, $lastvisit, $ordertype, $maxposition = 0) {
	global $_G, $rushreply, $hiddenreplies;

	if(!$_G['forum_newpostanchor'] && $post['dateline'] > $lastvisit) {
		$post['newpostanchor'] = '<a name="newpost"></a>';
		$_G['forum_newpostanchor'] = 1;
	} else {
		$post['newpostanchor'] = '';
	}

	$post['lastpostanchor'] = ($ordertype != 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies']) || ($ordertype == 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies'] + 2 && (!$_G['forum_thread']['replies'] || !$post['first'])) ? '<a name="lastpost"></a>' : '';

	if(empty($post['hotrecommended']) && $post['incurpage']) {
		if($_G['forum_pagebydesc']) {
			if($ordertype != 1) {
				$post['number'] = $_G['forum_numpost'] + $_G['forum_ppp2']--;
			} else {
				$post['number'] = $post['first'] == 1 ? 1 : $_G['forum_numpost'] - $_G['forum_ppp2']--;
			}
		} else {
			if($ordertype != 1) {
				$post['number'] = ++$_G['forum_numpost'];
			} else {
				$post['number'] = $post['first'] == 1 ? 1 : --$_G['forum_numpost'];
			}
		}
	}

	if(!empty($post['existinfirstpage']) && $post['incurpage']) {
		if($_G['forum_pagebydesc']) {
			$_G['forum_ppp2']--;
		} else {
			if($ordertype != 1) {
				++$_G['forum_numpost'];
			} else {
				--$_G['forum_numpost'];
			}
		}
	}

	if($maxposition) {
		$post['number'] = $post['position'];
	}

	if(!empty($post['hotrecommended'])) {
		$post['number'] = -1;
	}

	if(!$_G['forum_thread']['special'] && !$rushreply && empty($hiddenreplies) && $_G['setting']['threadfilternum'] && getstatus($post['status'], 11)) {
		$post['isWater'] = true;
		if($_G['setting']['hidefilteredpost'] && !$_G['forum']['noforumhidewater']) {
			$post['inblacklist'] = true;
		}
	} else {
		$_G['allblocked'] = false;
	}

	if($post['inblacklist']) {
		$_G['blockedpids'][] = $post['pid'];
	}

	$_G['forum_postcount']++;

	$post['dbdateline'] = $post['dateline'];
	$post['dateline'] = dgmdate($post['dateline'], 'u', '9999', getglobal('setting/dateformat').' H:i:s');
	$post['groupid'] = $_G['cache']['usergroups'][$post['groupid']] ? $post['groupid'] : 7;

	if($post['username']) {

		$_G['forum_onlineauthors'][$post['authorid']] = 0;
		$post['usernameenc'] = rawurlencode($post['username']);
		$post['readaccess'] = $_G['cache']['usergroups'][$post['groupid']]['readaccess'];
		if($_G['cache']['usergroups'][$post['groupid']]['userstatusby'] == 1) {
			$post['authortitle'] = $_G['cache']['usergroups'][$post['groupid']]['grouptitle'];
			$post['stars'] = $_G['cache']['usergroups'][$post['groupid']]['stars'];
		}
		$post['upgradecredit'] = false;
		if($_G['cache']['usergroups'][$post['groupid']]['type'] == 'member' && $_G['cache']['usergroups'][$post['groupid']]['creditslower'] != 999999999) {
			$post['upgradecredit'] = $_G['cache']['usergroups'][$post['groupid']]['creditslower'] - $post['credits'];
			$post['upgradeprogress'] = 100 - ceil($post['upgradecredit'] / ($_G['cache']['usergroups'][$post['groupid']]['creditslower'] - $_G['cache']['usergroups'][$post['groupid']]['creditshigher']) * 100);
			$post['upgradeprogress'] = min(max($post['upgradeprogress'], 2), 100);
		}

		$post['taobaoas'] = addslashes($post['taobao']);
		$post['regdate'] = dgmdate($post['regdate'], 'd');
		$post['lastdate'] = dgmdate($post['lastvisit'], 'd');

		$post['authoras'] = !$post['anonymous'] ? ' '.addslashes($post['author']) : '';

		if($post['medals']) {
			loadcache('medals');
			foreach($post['medals'] = explode("\t", $post['medals']) as $key => $medalid) {
				list($medalid, $medalexpiration) = explode('|', $medalid);
				if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
					$post['medals'][$key] = $_G['cache']['medals'][$medalid];
					$post['medals'][$key]['medalid'] = $medalid;
					$_G['medal_list'][$medalid] = $_G['cache']['medals'][$medalid];
				} else {
					unset($post['medals'][$key]);
				}
			}
		}

		$post['avatar'] = avatar($post['authorid']);
		$post['groupicon'] = $post['avatar'] ? g_icon($post['groupid'], 1) : '';
		$post['banned'] = $post['status'] & 1;
		$post['warned'] = ($post['status'] & 2) >> 1;

	} else {
		if(!$post['authorid']) {
			$post['useip'] = substr($post['useip'], 0, strrpos($post['useip'], '.')).'.x';
		}
	}
	$post['attachments'] = [];
	$post['imagelist'] = $post['attachlist'] = [];

	if($post['attachment']) {
		if((!empty($_G['setting']['guestviewthumb']['flag']) && !$_G['uid']) || $_G['group']['allowgetattach'] || $_G['group']['allowgetimage']) {
			$_G['forum_attachpids'][] = $post['pid'];
			$post['attachment'] = 0;
			if(preg_match_all('/\[attach\](\d+)\[\/attach\]/i', $post['message'], $matchaids)) {
				$_G['forum_attachtags'][$post['pid']] = $matchaids[1];
			}
		} else {
			$post['message'] = preg_replace('/\[attach\](\d+)\[\/attach\]/i', '', $post['message']);
		}
	}

	if($_G['setting']['ratelogrecord'] && $post['ratetimes']) {
		$_G['forum_cachepid'][$post['pid']] = $post['pid'];
	}
	if($_G['setting']['commentnumber'] && ($post['first'] && $_G['setting']['commentfirstpost'] || !$post['first']) && $post['comment']) {
		$_G['forum_cachepid'][$post['pid']] = $post['pid'];
	}
	$post['allowcomment'] = $_G['setting']['commentnumber'] && is_array($_G['setting']['allowpostcomment']) && in_array(1, $_G['setting']['allowpostcomment']) && ($_G['setting']['commentpostself'] || $post['authorid'] != $_G['uid']) &&
		($post['first'] && $_G['setting']['commentfirstpost'] && in_array($_G['group']['allowcommentpost'], [1, 3]) ||
			(!$post['first'] && in_array($_G['group']['allowcommentpost'], [2, 3])));
	$forum_allowbbcode = $_G['forum']['allowbbcode'] ? -$post['groupid'] : 0;
	$post['signature'] = $post['usesig'] ? ($_G['setting']['sigviewcond'] ? (strlen($post['message']) > $_G['setting']['sigviewcond'] ? $post['signature'] : '') : $post['signature']) : '';
	$imgcontent = $post['first'] ? getstatus($_G['forum_thread']['status'], 15) : 0;
	if(!defined('IN_ARCHIVER')) {
		if($post['first']) {
			if(!defined('IN_MOBILE')) {
				$messageindex = false;
				if(str_contains($post['message'], '[/index]')) {
					$post['message'] = preg_replace_callback(
						'/\s?\[index\](.+?)\[\/index\]\s?/is',
						function($matches) use ($post) {
							return parseindex($matches[1], intval($post['pid']));
						},
						$post['message']
					);
					$messageindex = true;
					unset($_GET['threadindex']);
				}
				if(str_contains($post['message'], '[page]')) {
					if($_GET['cp'] != 'all') {
						$postbg = '';
						if(str_contains($post['message'], '[/postbg]')) {
							preg_match("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/is", $post['message'], $r);
							$postbg = $r[0];
						}
						$messagearray = explode('[page]', $post['message']);
						$cp = max(intval($_GET['cp']), 1);
						$post['message'] = $messagearray[$cp - 1];
						if($postbg && !str_contains($post['message'], '[/postbg]')) {
							$post['message'] = $postbg.$post['message'];
						}
						unset($postbg);
					} else {
						$cp = 0;
						$post['message'] = preg_replace('/\s?\[page\]\s?/is', '', $post['message']);
					}
					if($_GET['cp'] != 'all' && !str_contains($post['message'], '[/index]') && empty($_GET['threadindex']) && !$messageindex) {
						$_G['forum_posthtml']['footer'][$post['pid']] .= '<div id="threadpage"></div><script type="text/javascript" reload="1">show_threadpage('.$post['pid'].', '.$cp.', '.count($messagearray).', '.($_GET['from'] == 'preview' ? '1' : '0').', \''.(isset($_GET['modthreadkey']) ? $_GET['modthreadkey'] : '').'\');</script>';
					}
				}
			}
		}
		if(!empty($_GET['threadindex'])) {
			$_G['forum_posthtml']['header'][$post['pid']] .= '<div id="threadindex"></div><script type="text/javascript" reload="1">show_threadindex(0, '.($_GET['from'] == 'preview' ? '1' : '0').');</script>';
		}
		if(!$imgcontent) {
			// 开始json编辑器的处理
			$htmlon_jsonContent = false;
			if(is_valid_non_empty_json($post['content'], true)) {
				$content = json_decode($post['content'], true);
				if($content['type'] == 'json' && $content['editor'] == 'jsonEditor' && !empty($content['content'])) {
					$htmlon_jsonContent = true;
				}
			}
			// 结束json编辑器的处理
			$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], ($post['htmlon'] || $htmlon_jsonContent) & 1, $_G['forum']['allowsmilies'], $forum_allowbbcode, ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'] || $htmlon_jsonContent, ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], $_G['cache']['usergroups'][$post['groupid']]['allowmediacode'] && $_G['forum']['allowmediacode'], $post['pid'], getglobal('setting/lazyload'), $post['dbdateline'], $post['first'], (!empty($post['content']) && $post['content'] != '{}') & 1);
			if($post['first']) {
				$_G['relatedlinks'] = '';
				$relatedtype = !$_G['forum_thread']['isgroup'] ? 'forum' : 'group';
				if(!getglobal('setting/relatedlinkstatus')) {
					$_G['relatedlinks'] = get_related_link($relatedtype);
				} else {
					$post['message'] = parse_related_link($post['message'], $relatedtype);
				}
				if(str_contains($post['message'], '[/begin]')) {
					$post['message'] = preg_replace_callback(
						"/\[begin(=\s*([^\[\<\r\n]*?)\s*,(\d*),(\d*),(\d*),(\d*))?\]\s*([^\[\<\r\n]+?)\s*\[\/begin\]/is",
						function($matches) {
							if(!intval($_G['cache']['usergroups'][$post['groupid']]['allowbegincode'])) {
								return '';
							}
							return parsebegin($matches[2], $matches[7], $matches[3], $matches[4], $matches[5], $matches[6]);
						},
						$post['message']
					);
				}
			}
		}
	}
	if(defined('IN_ARCHIVER') || defined('IN_MOBILE') || !$post['first']) {
		if(str_contains($post['message'], '[page]')) {
			$post['message'] = preg_replace('/\s?\[page\]\s?/is', '', $post['message']);
		}
		if(str_contains($post['message'], '[/index]')) {
			$post['message'] = preg_replace('/\s?\[index\](.+?)\[\/index\]\s?/is', '', $post['message']);
		}
		if(str_contains($post['message'], '[/begin]')) {
			$post['message'] = preg_replace("/\[begin(=\s*([^\[\<\r\n]*?)\s*,(\d*),(\d*),(\d*),(\d*))?\]\s*([^\[\<\r\n]+?)\s*\[\/begin\]/is", '', $post['message']);
		}
	}
	if($imgcontent) {
		$post['message'] = '<img id="threadimgcontent" src="./'.stringtopic('', $post['tid']).'">';
	}
	$_G['forum_firstpid'] = intval($_G['forum_firstpid']);
	$post['numbercard'] = viewthread_numbercard($post);
	$post['mobiletype'] = getstatus($post['status'], 4) ? base_convert(getstatus($post['status'], 10).getstatus($post['status'], 9).getstatus($post['status'], 8), 2, 10) : 0;
	return $post;
}

function viewthread_loadcache() {
	global $_G;
	$_G['thread']['livedays'] = ceil((TIMESTAMP - $_G['thread']['dateline']) / 86400);        // 本贴子存在了多少天，最少是1天
	$_G['thread']['lastpostdays'] = ceil((TIMESTAMP - $_G['thread']['lastpost']) / 86400);        // 最后发帖天数，最少1天

	$threadcachemark = 100 - (
			$_G['thread']['digest'] * 20 +                                                        // 精华，占20分
			min($_G['thread']['views'] / max($_G['thread']['livedays'], 10) * 2, 50) +        // 阅读数与天数关系，占50分。阅读越多分越高，天数越久分越低
			max(-10, (15 - $_G['thread']['lastpostdays'])) +                                // 最后回复时间，占15分，超过15天开始倒扣分，最多扣10分
			min($_G['thread']['replies'] / $_G['setting']['postperpage'] * 1.5, 15));        // 帖子页数，占15分，10页以上就是满分
	if($threadcachemark < $_G['forum']['threadcaches']) {

		$threadcache = getcacheinfo($_G['tid']);

		if(TIMESTAMP - $threadcache['filemtime'] > $_G['setting']['cachethreadlife']) {
			@unlink($threadcache['filename']);
			define('CACHE_FILE', $threadcache['filename']);
		} else {
			$start_time = microtime(TRUE);
			$filemtime = $threadcache['filemtime'];
			ob_start(function($input) use (&$filemtime) {
				return replace_formhash($filemtime, $input);
			});
			readfile($threadcache['filename']);
			viewthread_updateviews($_G['forum_thread']['threadtableid']);
			$updatetime = dgmdate($filemtime, 'Y-m-d H:i:s');
			$debuginfo = ", Updated at $updatetime";
			if(getglobal('setting/debug')) {
				$gzip = $_G['gzipcompress'] ? ', Gzip On' : '';
				$debuginfo .= ', Processed in '.sprintf('%0.6f', microtime(TRUE) - $start_time).' second(s)'.$gzip;
			}
			echo '<script type="text/javascript">$("debuginfo") ? $("debuginfo").innerHTML = "'.$debuginfo.'." : "";</script></body></html>';
			ob_end_flush();
			exit();
		}
	}
}

function viewthread_lastmod(&$thread) {
	global $_G;
	if(!$thread['moderated']) {
		return [];
	}
	$lastmod = [];
	$lastlog = table_forum_threadmod::t()->fetch_by_tid($thread['tid']);
	if($lastlog) {
		$lastmod = [
			'moduid' => $lastlog['uid'],
			'modusername' => $lastlog['username'],
			'moddateline' => $lastlog['dateline'],
			'modaction' => $lastlog['action'],
			'magicid' => $lastlog['magicid'],
			'stamp' => $lastlog['stamp'],
			'reason' => $lastlog['reason']
		];
	}
	if($lastmod) {
		$modactioncode = lang('forum/modaction');
		$lastmod['moduid'] = $_G['setting']['moduser_public'] ? $lastmod['moduid'] : 0;
		$lastmod['modusername'] = $lastmod['modusername'] ? ($_G['setting']['moduser_public'] ? $lastmod['modusername'] : lang('forum/template', 'thread_moderations_team')) : lang('forum/template', 'thread_moderations_cron');
		$lastmod['moddateline'] = dgmdate($lastmod['moddateline'], 'u');
		$lastmod['modactiontype'] = $lastmod['modaction'];
		if($modactioncode[$lastmod['modaction']]) {
			$lastmod['modaction'] = $modactioncode[$lastmod['modaction']].($lastmod['modaction'] != 'SPA' ? '' : ' '.$_G['cache']['stamps'][$lastmod['stamp']]['text']);
		} elseif(str_starts_with($lastmod['modaction'], 'L') && preg_match('/L(\d\d)/', $lastmod['modaction'], $a)) {
			$lastmod['modaction'] = $modactioncode['SLA'].' '.$_G['cache']['stamps'][intval($a[1])]['text'];
		} else {
			$lastmod['modaction'] = '';
		}
		if($lastmod['magicid']) {
			loadcache('magics');
			$lastmod['magicname'] = $_G['cache']['magics'][$lastmod['magicid']]['name'];
		}
	} else {
		table_forum_thread::t()->update($thread['tid'], ['moderated' => 0], false, false, $thread['threadtableid']);
		$thread['moderated'] = 0;
	}
	return $lastmod;
}

function viewthread_baseinfo($post, $extra) {
	global $_G;
	list($key, $type) = $extra;
	$v = '';
	if(str_starts_with($key, 'extcredits')) {
		$i = substr($key, 10);
		$extcredit = $_G['setting']['extcredits'][$i];
		if($extcredit) {
			$v = $type ? ($extcredit['img'] ? $extcredit['img'].' ' : '').$extcredit['title'] : $post['extcredits'.$i].' '.$extcredit['unit'];
		}
	} elseif(str_starts_with($key, 'field_')) {
		$field = substr($key, 6);
		if(!empty($post['privacy']['profile'][$field])) {
			return '';
		}
		require_once libfile('function/profile');
		if($field != 'qq') {
			$v = profile_show($field, $post);
		} elseif(!empty($post['qq'])) {
			$v = '<a href="//wpa.qq.com/msgrd?v=3&uin='.$post['qq'].'&site='.$_G['setting']['bbname'].'&menu=yes&from=discuz" target="_blank" title="'.lang('spacecp', 'qq_dialog').'"><img src="'.STATICURL.'image/common/qq_big.gif" alt="QQ" style="margin:0px;"/></a>';
		}
		if($v) {
			if(!isset($_G['cache']['profilesetting'])) {
				loadcache('profilesetting');
			}
			$v = $type ? $_G['cache']['profilesetting'][$field]['title'] : $v;
		}
	} elseif($key == 'eccredit_seller') {
		$v = $type ? lang('space', 'viewthread_userinfo_sellercredit') : '<a href="home.php?mod=space&uid='.$post['uid'].'&do=trade&view=eccredit#buyercredit" target="_blank" class="vm"><img src="'.STATICURL.'image/traderank/seller/'.countlevel($post['buyercredit']).'.gif" /></a>';
	} elseif($key == 'eccredit_buyer') {
		$v = $type ? lang('space', 'viewthread_userinfo_buyercredit') : '<a href="home.php?mod=space&uid='.$post['uid'].'&do=trade&view=eccredit#sellercredit" target="_blank" class="vm"><img src="'.STATICURL.'image/traderank/seller/'.countlevel($post['sellercredit']).'.gif" /></a>';
	} else {
		$v = getLinkByKey($key, $post);
		if($v !== '') {
			$v = $type ? lang('space', 'viewthread_userinfo_'.$key) : $v;
		}
	}
	return $v;
}

function viewthread_profile_nodeparse($param) {
	list($name, $s, $e, $extra, $post) = $param;
	if(!str_contains($name, ':')) {
		if(function_exists('profile_node_'.$name)) {
			return call_user_func('profile_node_'.$name, $post, $s, $e, explode(',', $extra));
		} else {
			return '';
		}
	} else {
		list($plugin, $pluginid) = explode(':', $name);
		if($plugin == 'plugin') {
			global $_G;
			static $pluginclasses = [];
			if(isset($_G['setting']['plugins']['profile_node'][$pluginid])) {
				@include_once DISCUZ_PLUGIN($_G['setting']['plugins']['profile_node'][$pluginid]).'.class.php';
				$classkey = 'plugin_'.$pluginid;
				if(!class_exists($classkey, false)) {
					return '';
				}
				if(!isset($pluginclasses[$classkey])) {
					$pluginclasses[$classkey] = new $classkey;
				}
				return call_user_func([$pluginclasses[$classkey], 'profile_node'], $post, $s, $e, explode(',', $extra));
			}
		}
	}
}

function viewthread_profile_node($type, $post) {
	global $_G;
	$tpid = false;
	if(!empty($post['verifyicon'])) {
		$tpid = $_G['setting']['profilenode']['groupid'][-$post['verifyicon'][0]] ?? false;
	}
	if($tpid === false) {
		$tpid = $_G['setting']['profilenode']['groupid'][$post['groupid']] ?? 0;
	}
	$template = $_G['setting']['profilenode']['template'][$tpid][$type];
	$code = $_G['setting']['profilenode']['code'][$tpid][$type];
	include_once template('forum/viewthread_profile_node');
	foreach($code as $k => $p) {
		$p[] = $post;
		$template = str_replace($k, call_user_func('viewthread_profile_nodeparse', $p), $template);
	}
	echo $template;
}

function viewthread_numbercard($post) {
	global $_G;
	if(!is_array($_G['setting']['numbercard'])) {
		$_G['setting']['numbercard'] = dunserialize($_G['setting']['numbercard']);
	}

	$numbercard = [];
	foreach($_G['setting']['numbercard']['row'] as $key) {
		if(str_starts_with($key, 'extcredits')) {
			$numbercard[] = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=profile', 'value' => $post[$key], 'lang' => $_G['setting']['extcredits'][substr($key, 10)]['title']];
		} else {
			$getLink = getLinkByKey($key, $post, 1);
			$numbercard[] = ['link' => $getLink['link'], 'value' => $getLink['value'], 'lang' => lang('space', 'viewthread_userinfo_'.$key)];
		}
	}
	return $numbercard;
}

function getLinkByKey($key, $post, $returnarray = 0) {
	switch($key) {
		case 'uid':
			$v = ['link' => '?'.$post['uid'], 'value' => $post['uid']];
			break;
		case 'posts':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=thread&type=reply&view=me&from=space', 'value' => $post['posts'] - $post['threads']];
			break;
		case 'threads':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=thread&type=thread&view=me&from=space', 'value' => $post['threads']];
			break;
		case 'digestposts':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=thread&type=thread&view=me&from=space', 'value' => $post['digestposts']];
			break;
		case 'feeds':
			$v = ['link' => 'home.php?mod=follow&uid='.$post['uid'].'&do=view', 'value' => $post['feeds']];
			break;
		case 'doings':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=doing&view=me&from=space', 'value' => $post['doings']];
			break;
		case 'blogs':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=blog&view=me&from=space', 'value' => $post['blogs']];
			break;
		case 'albums':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=album&view=me&from=space', 'value' => $post['albums']];
			break;
		case 'sharings':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=share&view=me&from=space', 'value' => $post['sharings']];
			break;
		case 'friends':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=friend&view=me&from=space', 'value' => $post['friends']];
			break;
		case 'follower':
			$v = ['link' => 'home.php?mod=follow&do=follower&uid='.$post['uid'], 'value' => $post['follower']];
			break;
		case 'following':
			$v = ['link' => 'home.php?mod=follow&do=following&uid='.$post['uid'], 'value' => $post['following']];
			break;
		case 'credits':
			$v = ['link' => 'home.php?mod=space&uid='.$post['uid'].'&do=profile', 'value' => $post['credits']];
			break;
		case 'digest':
			$v = ['value' => $post['digestposts']];
			break;
		case 'readperm':
			$v = ['value' => $post['readaccess']];
			break;
		case 'regtime':
			$v = ['value' => $post['regdate']];
			break;
		case 'lastdate':
			$v = ['value' => $post['lastdate']];
			break;
		case 'oltime':
			$v = ['value' => $post['oltime'].' '.lang('space', 'viewthread_userinfo_hour')];
			break;
	}
	if(!$returnarray) {
		if($v['link']) {
			$v = '<a href="'.$v['link'].'" target="_blank" class="xi2">'.$v['value'].'</a>';
		} else {
			$v = $v['value'];
		}
	}
	return $v;
}

function countlevel($usercredit) {
	global $_G;

	$rank = 0;
	if($usercredit) {
		foreach($_G['setting']['ec_credit']['rank'] as $level => $credit) {
			if($usercredit <= $credit) {
				$rank = $level;
				break;
			}
		}
	}
	return $rank;
}

function remaintime($time) {
	$days = intval($time / 86400);
	$time -= $days * 86400;
	$hours = intval($time / 3600);
	$time -= $hours * 3600;
	$minutes = intval($time / 60);
	$time -= $minutes * 60;
	$seconds = $time;
	return [(int)$days, (int)$hours, (int)$minutes, (int)$seconds];
}

function getrelateitem($tagarray, $tid, $relatenum, $relatetime, $relatecache = '', $type = 'tid') {
	$tagidarray = $relatearray = $relateitem = [];
	$updatecache = 0;
	$limit = $relatenum;
	if(!$limit) {
		return '';
	}
	foreach($tagarray as $var) {
		$tagidarray[] = $var['0'];
	}
	if(!$tagidarray) {
		return '';
	}
	if(empty($relatecache)) {
		$thread = table_forum_thread::t()->fetch_thread($tid);
		$relatecache = $thread['relatebytag'];
	}
	if($relatecache) {
		$relatecache = explode("\t", $relatecache);
		if(TIMESTAMP > $relatecache[0] + $relatetime * 60) {
			$updatecache = 1;
		} else {
			if(!empty($relatecache[1])) {
				$relatearray = explode(',', $relatecache[1]);
			}
		}
	} else {
		$updatecache = 1;
	}
	if($updatecache) {
		$query = table_common_tagitem::t()->select($tagidarray, $tid, $type, 'itemid', 'DESC', $limit, 0, '<>');
		foreach($query as $result) {
			if($result['itemid']) {
				$relatearray[] = $result['itemid'];
			}
		}
		if($relatearray) {
			$relatebytag = implode(',', $relatearray);
		}
		table_forum_thread::t()->update($tid, ['relatebytag' => TIMESTAMP."\t".$relatebytag]);
	}


	if(!empty($relatearray)) {
		rsort($relatearray);
		foreach(table_forum_thread::t()->fetch_all_by_tid($relatearray) as $result) {
			if($result['displayorder'] >= 0) {
				$relateitem[] = $result;
			}
		}
	}
	return $relateitem;
}

function rushreply_rule() {
	global $rushresult;
	if(!empty($rushresult['rewardfloor'])) {
		$rushresult['rewardfloor'] = preg_replace('/\*+/', '*', $rushresult['rewardfloor']);
		$rewardfloorarr = explode(',', $rushresult['rewardfloor']);
		if($rewardfloorarr) {
			foreach($rewardfloorarr as $var) {
				$var = trim($var);
				if(strlen($var) > 1) {
					$var = str_replace('*', '[^,]?[\d]*', $var);
				} else {
					$var = str_replace('*', '\d+', $var);
				}
				$preg[] = "(,$var,)";
			}
			$preg = is_array($preg) ? $preg : [$preg];
			$preg_str = '/'.implode('|', $preg).'/';
		}
	}
	return $preg_str;
}

function checkrushreply($post) {
	global $_G, $rushids;
	if($_GET['authorid']) {
		return $post;
	}
	if(in_array($post['number'], $rushids)) {
		$post['rewardfloor'] = 1;
	}
	return $post;
}

function parseindex($nodes, $pid) {
	global $_G;
	$nodes = dhtmlspecialchars($nodes);
	$nodes = preg_replace('/(\**?)\[#(\d+)\](.+?)[\r\n]/', "<a page=\"\\2\" sub=\"\\1\">\\3</a>", $nodes);
	$nodes = preg_replace('/(\**?)\[#(\d+),(\d+)\](.+?)[\r\n]/', "<a tid=\"\\2\" pid=\"\\3\" sub=\"\\1\">\\4</a>", $nodes);
	$_G['forum_posthtml']['header'][$pid] .= '<div id="threadindex">'.$nodes.'</div><script type="text/javascript" reload="1">show_threadindex('.$pid.', '.($_GET['from'] == 'preview' ? '1' : '0').')</script>';
	return '';
}

function parsebegin($linkaddr, $imgflashurl, $w = 0, $h = 0, $type = 0, $s = 0) {
	static $begincontent;
	if($begincontent || $_GET['from'] == 'preview') {
		return '';
	}
	preg_match("/((https?){1}:\/\/|www\.)[^\[\"']+/i", $imgflashurl, $matches);
	$imgflashurl = $matches[0];
	$fileext = fileext($imgflashurl);
	preg_match("/((https?){1}:\/\/|www\.)[^\[\"']+/i", $linkaddr, $matches);
	$linkaddr = $matches[0];
	$randomid = 'swf_'.random(3);
	$w = ($w >= 400 && $w <= 1024) ? $w : 900;
	$h = ($h >= 300 && $h <= 640) ? $h : 500;
	$s = $s ? $s * 1000 : 5000;
	$content = match ($fileext) {
		'jpg', 'jpeg', 'gif', 'png' => '<img style="position:absolute;width:'.$w.'px;height:'.$h.'px;" src="'.$imgflashurl.'" />',
		'swf' => '<span id="'.$randomid.'" style="position:absolute;"></span>'.
			'<script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML='.
			'AC_FL_RunContent(\'width\', \''.$w.'\', \'height\', \''.$h.'\', '.
			'\'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', '.
			'\'src\', encodeURI(\''.$imgflashurl.'\'), \'quality\', \'high\', \'bgcolor\', \'#ffffff\', '.
			'\'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>',
		default => '',
	};
	if($content) {
		if($type == 1) {
			$content = '<div id="threadbeginid" style="display:none;">'.
				'<div class="flb beginidin"><span><div id="begincloseid" class="flbc" title="'.lang('core', 'close').'">'.lang('core', 'close').'</div></span></div>'.
				$content.'<div class="beginidimg" style=" width:'.$w.'px;height:'.$h.'px;">'.
				'<a href="'.$linkaddr.'" target="_blank" style="display: block; width:'.$w.'px; height:'.$h.'px;"></a></div></div>'.
				'<script type="text/javascript">threadbegindisplay(1, '.$w.', '.$h.', '.$s.');</script>';
		} else {
			$content = '<div id="threadbeginid">'.
				'<div class="flb beginidin">
					<span><div id="begincloseid" class="flbc" title="'.lang('core', 'close').'">'.lang('core', 'close').'</div></span>
				</div>'.
				$content.'<div class="beginidimg" style=" width:'.$w.'px; height:'.$h.'px;">'.
				'<a href="'.$linkaddr.'" target="_blank" style="display: block; width:'.$w.'px; height:'.$h.'px;"></a></div>
				</div>'.
				'<script type="text/javascript">threadbegindisplay('.$type.', '.$w.', '.$h.', '.$s.');</script>';
		}
	}
	$begincontent = $content;
	return $content;
}

function _checkviewgroup() {
	global $_G;
	$_G['action']['action'] = 3;
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid']);
	if($status == 1) {
		showmessage('forum_group_status_off');
	} elseif($status == 2) {
		showmessage('forum_group_noallowed', 'forum.php?mod=group&fid='.$_G['fid']);
	} elseif($status == 3) {
		showmessage('forum_group_moderated', 'forum.php?mod=group&fid='.$_G['fid']);
	}
}

