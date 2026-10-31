<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['postappend']) {
	showmessage('postappend_not_open');
}

$post = table_forum_post::t()->fetch_post('tid:'.$_G['tid'], $_GET['pid']);
if($post['authorid'] != $_G['uid']) {
	showmessage('postappend_only_yourself');
}
if(submitcheck('postappendsubmit')) {
	$message = censor($_GET['postappendmessage']);
	$sppos = 0;
	if($post['first'] && strexists($post['message'], chr(0).chr(0).chr(0))) {
		$sppos = strpos($post['message'], chr(0).chr(0).chr(0));
		$specialextra = substr($post['message'], $sppos + 3);
		$post['message'] = substr($post['message'], 0, $sppos);
	}
	$message = $post['message']."\n\n[postappend][b]".lang('forum/misc', 'postappend_content').' ('.dgmdate(TIMESTAMP)."):[/b]\n$message"."[/postappend]";
	if($sppos) {
		$message .= chr(0).chr(0).chr(0).$specialextra;
	}
	require_once libfile('function/post');
	$bbcodeoff = checkbbcodes($message, 0);
	table_forum_post::t()->update_post('tid:'.$_G['tid'], $_GET['pid'], [
		'message' => $message,
		'bbcodeoff' => $bbcodeoff,
		'port' => $_G['remoteport']
	]);
	showmessage('postappend_add_succeed', "forum.php?mod=viewthread&tid={$post['tid']}&pid={$post['pid']}&page={$_GET['page']}&extra={$_GET['extra']}#pid{$post['pid']}", ['tid' => $post['tid'], 'pid' => $post['pid']]);
} else {
	include template('forum/postappend');
}
	