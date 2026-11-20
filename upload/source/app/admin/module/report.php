<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/post');

cpheader();

$operation = $operation ? $operation : 'newreport';
if(submitcheck('delsubmit')) {
	if(!empty($_GET['reportids'])) {
		table_common_report::t()->delete($_GET['reportids']);
	}
}
if(submitcheck('resolvesubmit')) {
	if(!empty($_GET['reportids'])) {
		$curcredits = $_G['setting']['creditstransextra'][8] ? $_G['setting']['creditstransextra'][8] : $_G['setting']['creditstrans'];
		foreach($_GET['reportids'] as $id) {
			$id = intval($id);
			$_report = table_common_report::t()->fetch($id);
			if(!$_report || !empty($_report['opuid'])) {
				continue;
			}
			$creditchange = '';
			$uid = intval($_report['uid']);
			$_creditvalue = is_array($_GET['creditsvalue']) ? intval($_GET['creditsvalue'][$id] ?? 0) : 0;
			$opresult = $_creditvalue ? $curcredits."\t".$_creditvalue : 'ignore';
			$msg = !empty($_GET['msg'][$id]) ? '<br />'.dhtmlspecialchars($_GET['msg'][$id]) : '';
			if($_creditvalue) {
				$credittag = $_creditvalue > 0 ? '+' : '';
				$creditchange = '<br />'.cplang('report_your').$_G['setting']['extcredits'][$curcredits]['title'].'&nbsp;'.$credittag.$_creditvalue;
				updatemembercount($uid, [$curcredits => $_creditvalue], true, 'RPC', $id);
			}
			if($uid && $uid != $_G['uid'] && ($creditchange || $msg)) {
				notification_add($uid, 'report', 'report_change_credits', ['creditchange' => $creditchange, 'msg' => $msg], 1);
			}
			table_common_report::t()->update($id, ['opuid' => $_G['uid'], 'opname' => $_G['username'], 'optime' => TIMESTAMP, 'opresult' => $opresult]);
		}
		cpmsg('report_resolve_succeed', 'action=report', 'succeed');
	}
}
if(submitcheck('receivesubmit') && $admincp->isfounder) {
	$supmoderator = $_GET['supmoderator'];
	$adminuser = $_GET['adminuser'];
	table_common_setting::t()->update_setting('report_receive', ['adminuser' => $adminuser, 'supmoderator' => $supmoderator]);
	updatecache('setting');
	cpmsg('report_receive_succeed', 'action=report&operation=receiveuser', 'succeed');
}
shownav('topic', 'nav_report');
$lpp = empty($_GET['lpp']) ? 20 : $_GET['lpp'];
$start = ($page - 1) * $lpp;

$file = childfile('report/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}
require_once $file;

function itemview_parse($url) {
	$default_url = [
		'post' => 'forum.php?mod=redirect&goto=findpost&ptid=',
		'thread' => 'forum.php?mod=viewthread&tid=',
		'blog' => 'home.php?mod=space&do=blog&uid='
	];
	foreach($default_url as $key => $value) {
		if(str_starts_with($url, $value)) {
			$tmp = explode('?', $url);
			parse_str($tmp[1], $kvarr);
			if($key == 'post' && isset($kvarr['pid'])) {
				require_once libfile('function/forum');
				$pid = intval($kvarr['pid']);
				$post = get_post_by_pid($pid);
				return empty($post['message']) ? false : dhtmlspecialchars(messagecutstr($post['message'], 60));
			} else if($key == 'thread' && isset($kvarr['tid'])) {
				require_once libfile('function/forum');
				$tid = intval($kvarr['tid']);
				$post = table_forum_post::t()->fetch_visiblepost_by_tid('tid:'.$tid, $tid);
				return empty($post['message']) ? false : dhtmlspecialchars(messagecutstr($post['message'], 60));
			} else if($key == 'blog' && isset($kvarr['id'])) {
				$id = intval($kvarr['id']);
				$post = table_home_blogfield::t()->fetch($id);
				return empty($post['message']) ? false : dhtmlspecialchars(messagecutstr($post['message'], 60));
			}
		}
	}
	return false;
}