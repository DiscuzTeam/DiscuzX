<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
const NOROBOT = TRUE;

cknewuser();

require_once libfile('class/credit');
require_once libfile('function/post');
require_once libfile('function/forumlist');


$pid = intval(getgpc('pid'));
$sortid = intval(getgpc('sortid'));
$typeid = intval(getgpc('typeid'));
$special = intval(getgpc('special'));

parse_str(getgpc('extra'), $_GET['extra']);
$_GET['extra'] = http_build_query($_GET['extra']);

$postinfo = ['subject' => ''];
$thread = ['readperm' => '', 'pricedisplay' => '', 'hiddenreplies' => ''];

$_G['forum_dtype'] = $_G['forum_checkoption'] = $_G['forum_optionlist'] = $tagarray = $_G['forum_typetemplate'] = [];


if($sortid) {
	require_once childfile('threadsorts');
}

if($_G['forum']['status'] == 3) {
	if(!helper_access::check_module('group')) {
		showmessage('group_status_off');
	}
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid'], 'post');
	if($status == -1) {
		showmessage('forum_not_group', 'index.php');
	} elseif($status == 1) {
		showmessage('forum_group_status_off');
	} elseif($status == 2) {
		showmessage('forum_group_noallowed', "forum.php?mod=group&fid={$_G['fid']}");
	} elseif($status == 3) {
		showmessage('forum_group_moderated');
	} elseif($status == 4) {
		if($_G['uid']) {
			showmessage('forum_group_not_groupmember', '', ['fid' => $_G['fid']], ['showmsg' => 1]);
		} else {
			showmessage('forum_group_not_groupmember_guest', '', ['fid' => $_G['fid']], ['showmsg' => 1, 'login' => 1]);
		}
	} elseif($status == 5) {
		showmessage('forum_group_moderated', '', ['fid' => $_G['fid']], ['showmsg' => 1]);
	}
}

if(empty($_GET['action'])) {
	showmessage('undefined_action', NULL);
} elseif($_GET['action'] == 'albumphoto') {
	require childfile('albumphoto', 'forum/post');;
} elseif(($_G['forum']['simple'] & 1) || $_G['forum']['redirect']) {
	showmessage('forum_disablepost');
}

require_once libfile('function/discuzcode');

$space = [];
space_merge($space, 'field_home');

if($_GET['action'] == 'reply') {
	$addfeedcheck = !empty($space['privacy']['feed']['newreply']) ? 'checked="checked"' : '';
} else {
	$addfeedcheck = !empty($space['privacy']['feed']['newthread']) ? 'checked="checked"' : '';
}


$navigation = $navtitle = '';

if(!empty($_GET['cedit'])) {
	unset($_G['inajax'], $_GET['infloat'], $_GET['ajaxtarget'], $_GET['handlekey']);
}

if($_GET['action'] == 'edit' || $_GET['action'] == 'reply') {

	$thread = table_forum_thread::t()->fetch_thread($_G['tid']);
	if(!$_G['forum_auditstatuson'] && !($thread['displayorder'] >= 0 || (in_array($thread['displayorder'], [-4, -2]) && $thread['authorid'] == $_G['uid']))) {
		$thread = [];
	}
	if(!empty($thread)) {

		if($thread['readperm'] && $thread['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $thread['authorid'] != $_G['uid']) {
			showmessage('thread_nopermission', NULL, ['readperm' => $thread['readperm']], ['login' => 1]);
		}

		$_G['fid'] = $thread['fid'];
		$special = $thread['special'];

	} else {
		showmessage('thread_nonexistence');
	}

	if($thread['closed'] == 1 && !$_G['forum']['ismoderator']) {
		showmessage('post_thread_closed');
	}
	if(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
		showmessage($post_autoclose, '', ['autoclose' => $_G['forum']['autoclose']]);
	}
}

if($_G['forum']['status'] == 3) {
	$returnurl = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_GET['extra']) ? '&action=list&'.preg_replace('/^(&)*/', '', $_GET['extra']) : '').'#groupnav';
	$nav = get_groupnav($_G['forum']);
	$navigation = ' <em>&rsaquo;</em> <a href="group.php">'.$_G['setting']['navs'][3]['navname'].'</a> '.$nav['nav'];
} else {
	loadcache('forums');
	$returnurl = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_GET['extra']) ? '&'.preg_replace('/^(&)*/', '', $_GET['extra']) : '');
	$navigation = ' <em>&rsaquo;</em> <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a>';

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

	$t_link = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].($_GET['extra'] && !IS_ROBOT ? '&'.$_GET['extra'] : '');
	$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['forum']['name']).'</a>';

	unset($t_link, $t_name);
}

periodscheck('postbanperiods');

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	showmessage('forum_passwd', "forum.php?mod=forumdisplay&fid={$_G['fid']}");
}

if(empty($_G['forum']['allowview'])) {
	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
		showmessage('group_nopermission', NULL, ['grouptitle' => $_G['group']['grouptitle']], ['login' => 1]);
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		showmessagenoperm('viewperm', $_G['fid']);
	}
} elseif($_G['forum']['allowview'] == -1) {
	showmessage('forum_access_view_disallow');
}

formulaperm($_G['forum']['formulaperm']);

if(in_array($_G['adminid'], [0, -1]) && $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60) && TIMESTAMP - getglobal('member/regdate') < $_G['setting']['newbiespan'] * 60) {
	showmessage('post_newbie_span', '', ['newbiespan' => $_G['setting']['newbiespan']]);
}

$special = $special > 0 && $special < 7 || $special == 127 ? intval($special) : 0;

$_G['forum']['allowpostattach'] = $_G['forum']['allowpostattach'] ?? '';
$_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
$_G['forum']['allowpostimage'] = $_G['forum']['allowpostimage'] ?? '';
$_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
$_G['group']['attachextensions'] = $_G['forum']['attachextensions'] ? $_G['forum']['attachextensions'] : $_G['group']['attachextensions'];
require_once libfile('function/upload');
$swfconfig = getuploadconfig($_G['uid'], $_G['fid']);
$imgexts = str_replace([';', '*.'], [', ', ''], $swfconfig['imageexts']['ext']);
$allowuploadnum = $allowuploadtoday = TRUE;
if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
	if($_G['group']['maxattachnum']) {
		$allowuploadnum = $_G['group']['maxattachnum'] - getuserprofile('todayattachs');
		$allowuploadnum = $allowuploadnum < 0 ? 0 : $allowuploadnum;
		if(!$allowuploadnum) {
			$allowuploadtoday = false;
		}
	}
	if($_G['group']['maxsizeperday']) {
		$allowuploadsize = $_G['group']['maxsizeperday'] - getuserprofile('todayattachsize');
		$allowuploadsize = $allowuploadsize < 0 ? 0 : $allowuploadsize;
		if(!$allowuploadsize) {
			$allowuploadtoday = false;
		}
		$allowuploadsize = $allowuploadsize / 1048576 >= 1 ? round(($allowuploadsize / 1048576), 1).'MB' : round(($allowuploadsize / 1024)).'KB';
	}
}
$allowpostimg = $_G['group']['allowpostimage'] && $imgexts;
$enctype = ($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) ? 'enctype="multipart/form-data"' : '';
$maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1).'MB' : round(($_G['group']['maxattachsize'] / 1024)).'KB';

$_G['group']['maxprice'] = isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]) ? $_G['group']['maxprice'] : 0;

$extra = !empty($_GET['extra']) ? rawurlencode($_GET['extra']) : '';
$notifycheck = empty($emailnotify) ? '' : 'checked="checked"';
$stickcheck = empty($sticktopic) ? '' : 'checked="checked"';
$digestcheck = empty($addtodigest) ? '' : 'checked="checked"';

$subject = isset($_GET['subject']) ? dhtmlspecialchars(censor(trim($_GET['subject']), NULL, FALSE, FALSE)) : '';
$subject = !empty($subject) ? str_replace("\t", ' ', $subject) : $subject;
$message = isset($_GET['message']) ? censor($_GET['message'], NULL, FALSE, FALSE) : '';
$content = isset($_GET['content']) ? censor($_GET['content'], NULL, FALSE, FALSE) : '';
$contentType = preg_replace('/[^a-zA-Z0-9_:.]/', '', strval($_GET['contentType'] ?? ''));
$contentType = substr($contentType, 0, 32);
if($contentType === '') {
	$contentType = 'text';
}
$contentEditor = preg_replace('/[^a-zA-Z0-9_:.]/', '', strval($_GET['contentEditor'] ?? ''));
$contentEditor = substr($contentEditor, 0, 32);
if($contentEditor === '') {
	$contentEditor = 'default';
}
if($content !== '' && !is_valid_non_empty_json($content)) {
	$content = '';
}
$polloptions = isset($polloptions) ? censor(trim($polloptions)) : '';
$readperm = isset($_GET['readperm']) ? intval($_GET['readperm']) : 0;
$price = isset($_GET['price']) ? intval($_GET['price']) : 0;
$cover_aid = isset($_GET['cover_aid']) ? intval($_GET['cover_aid']) : 0;
$original = isset($_GET['original']) ? intval($_GET['original']) : 0;
$source_title = isset($_GET['source_title']) ? dhtmlspecialchars(censor(trim($_GET['source_title']), NULL, FALSE, FALSE)) : '';
$source_url = isset($_GET['source_url']) ? censor(trim($_GET['source_url'])) : '';
if(!empty($source_title) || !empty($source_url)) {
	$source = json_encode(['title' => $source_title, 'url' => $source_url]);
}

if(empty($bbcodeoff) && !$_G['group']['allowhidecode'] && !empty($message) && preg_match('/\[hide=?d?\d*,?\d*\].*?\[\/hide\]/is', preg_replace('/(\[code\](.+?)\[\/code\])/is', ' ', $message))) {
	showmessage('post_hide_nopermission');
}


$urloffcheck = $usesigcheck = $smileyoffcheck = $codeoffcheck = $htmloncheck = $emailcheck = '';

list($seccodecheck, $secqaacheck) = seccheck('post', $_GET['action']);

$_G['group']['allowpostpoll'] = $_G['group']['allowpost'] && $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
$_G['group']['allowposttrade'] = $_G['group']['allowpost'] && $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
$_G['group']['allowpostreward'] = $_G['group']['allowpost'] && $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4);
$_G['group']['allowpostactivity'] = $_G['group']['allowpost'] && $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
$_G['group']['allowpostdebate'] = $_G['group']['allowpost'] && $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'] ? 'checked="checked"' : '';
$ordertypecheck = !empty($thread['tid']) && getstatus($thread['status'], 4) ? 'checked="checked"' : '';
$imgcontentcheck = !empty($thread['tid']) && getstatus($thread['status'], 15) ? 'checked="checked"' : '';
$specialextra = !empty($_GET['specialextra']) ? $_GET['specialextra'] : '';
$_G['forum']['threadplugin'] = dunserialize($_G['forum']['threadplugin']);

if($specialextra && $_G['group']['allowpost'] && $_G['setting']['threadplugins'] &&
	(!array_key_exists($specialextra, $_G['setting']['threadplugins']) ||
		!in_array($specialextra, is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : (is_array(dunserialize($_G['forum']['threadplugin'])) ? dunserialize($_G['forum']['threadplugin']) : [])) ||
		(is_array($_G['group']['allowthreadplugin']) && !in_array($specialextra, $_G['group']['allowthreadplugin'])))) {
	$specialextra = '';
}
if($special == 3 && !isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']])) {
	showmessage('reward_credits_closed');
}
$_G['group']['allowanonymous'] = $_G['forum']['allowanonymous'] || $_G['group']['allowanonymous'] ? 1 : 0;

if($_GET['action'] == 'newthread' && $_G['forum']['allowspecialonly'] && !$special) {
	if($_G['group']['allowpostpoll']) {
		$special = 1;
	} elseif($_G['group']['allowposttrade']) {
		$special = 2;
	} elseif($_G['group']['allowpostreward']) {
		$special = 3;
	} elseif($_G['group']['allowpostactivity']) {
		$special = 4;
	} elseif($_G['group']['allowpostdebate']) {
		$special = 5;
	} elseif($_G['group']['allowpost'] && $_G['setting']['threadplugins'] && $_G['group']['allowthreadplugin']) {
		if(empty($_GET['specialextra'])) {
			foreach($_G['forum']['threadplugin'] as $tpid) {
				if(array_key_exists($tpid, $_G['setting']['threadplugins']) && is_array($_G['group']['allowthreadplugin']) && in_array($tpid, $_G['group']['allowthreadplugin'])) {
					$specialextra = $tpid;
					break;
				}
			}
		}
		$threadpluginary = array_intersect($_G['forum']['threadplugin'], $_G['group']['allowthreadplugin']);
		$specialextra = in_array($specialextra, $threadpluginary) ? $specialextra : '';
	}

	if(!$special && !$specialextra) {
		showmessage('group_nopermission', NULL, ['grouptitle' => $_G['group']['grouptitle']], ['login' => 1]);
	}
}

if(!$sortid && !$specialextra) {
	$postspecialcheck[$special] = ' class="a"';
}

$editorid = 'e';
$_G['setting']['editoroptions'] = str_pad(decbin($_G['setting']['editoroptions']), 4, 0, STR_PAD_LEFT);
if(isset($_G['forum']['editormode']) && $_G['forum']['editormode'] != -1) {
	$editormode = $_G['forum']['editormode'];
	$_G['setting']['editormodetype'] = $editormode == 2;
} else {
	$editormode = bindec($_G['setting']['editoroptions'][0].$_G['setting']['editoroptions'][1]);
}
$allowswitcheditor = $_G['setting']['editoroptions'][2];
$editor = [
	'editormode' => $editormode,
	'allowswitcheditor' => $allowswitcheditor,
	'allowhtml' => $_G['forum']['allowhtml'],
	'allowsmilies' => $_G['forum']['allowsmilies'],
	'allowbbcode' => $_G['forum']['allowbbcode'],
	'allowimgcode' => $_G['forum']['allowimgcode'],
	'allowresize' => 1,
	'allowchecklength' => 1,
	'allowtopicreset' => 1,
	'textarea' => 'message',
	'simplemode' => !isset($_G['cookie']['editormode_'.$editorid]) ? !$_G['setting']['editoroptions'][3] : $_G['cookie']['editormode_'.$editorid],
];
if($specialextra) {
	$special = 127;
}

if($_GET['action'] == 'newthread') {
	$policykey = 'post';
} elseif($_GET['action'] == 'reply') {
	$policykey = 'reply';
} else {
	$policykey = '';
}
$policykeys = ['postattach', $policykey];
loadcache('creditrule');
foreach($policykeys as $key) {
	if($key && is_array($_G['cache']['creditrule'][$key])) {
		$data = [];
		$creditrule = $_G['cache']['creditrule'][$key];
		for($i = 1; $i <= 8; $i++) {
			if($creditrule['extcredits'.$i]) {
				$data[$i] = $creditrule['extcredits'.$i];
			}
		}
		$_G['setting']['creditspolicy'][$key] = $data;
	}
}
$postattachcredits = !empty($_G['forum']['postattachcredits']) ? $_G['forum']['postattachcredits'] : $_G['setting']['creditspolicy']['postattach'];
if($policykey) {
	$postcredits = getglobal('forum/'.$policykey.'credits') ? getglobal('forum/'.$policykey.'credits') : $_G['setting']['creditspolicy'][$policykey];
}

$albumlist = [];
if(helper_access::check_module('album') && $_G['group']['allowupload'] && $_G['uid']) {
	$query = table_home_album::t()->fetch_all_by_uid($_G['uid'], 'updatetime');
	foreach($query as $value) {
		if($value['picnum']) {
			$albumlist[] = $value;
		}
	}
}

$posturl = "action={$_GET['action']}&fid={$_G['fid']}".
	(!empty($_G['tid']) ? "&tid={$_G['tid']}" : '').
	(!empty($pid) ? "&pid=$pid" : '').
	(!empty($special) ? "&special=$special" : '').
	(!empty($sortid) ? "&sortid=$sortid" : '').
	(!empty($typeid) ? "&typeid=$typeid" : '').
	(!empty($_GET['firstpid']) ? '&firstpid='.$_GET['firstpid'] : '').
	(!empty($_GET['addtrade']) ? '&addtrade='.$_GET['addtrade'] : '');

if($_GET['action'] == 'reply') {
	check_allow_action('allowreply');
} else {
	check_allow_action('allowpost');
}

if($special == 4) {
	$_G['setting']['activityfield'] = $_G['setting']['activityfield'] ? dunserialize($_G['setting']['activityfield']) : [];
}
if(helper_access::check_module('album') && $_G['group']['allowupload'] && $_G['setting']['albumcategorystat'] && !empty($_G['cache']['albumcategory'])) {
	require_once libfile('function/portalcp');
}
$navtitle = lang('core', 'title_'.$_GET['action'].'_post');
list($modnewthreads, $modnewreplies) = threadmodstatus('');

if($_GET['action'] == 'newthread' || $_GET['action'] == 'newtrade') {
	loadcache('groupreadaccess');
	$navtitle .= ' - '.$_G['forum']['name'];
	require_once childfile('newthread', 'forum/post');
} elseif($_GET['action'] == 'reply') {
	$navtitle .= ' - '.$thread['subject'].' - '.$_G['forum']['name'];
	require_once childfile('newreply', 'forum/post');
} elseif($_GET['action'] == 'edit') {
	loadcache('groupreadaccess');
	$navtitle .= ' - '.$thread['subject'].' - '.$_G['forum']['name'];
	require_once childfile('editpost');
}

function check_allow_action($action = 'allowpost') {
	global $_G;
	if(isset($_G['forum'][$action]) && $_G['forum'][$action] == -1) {
		showmessage('forum_access_disallow');
	}
}
