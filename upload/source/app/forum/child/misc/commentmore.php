<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function forum_misc_commentmore_callback_1($matches, $action = 0) {
	static $cic = 0;

	if($action == 1) {
		$cic = $matches;
	} else {
		return '<i class="cmstarv">'.sprintf('%1.1f', $matches[1]).'</i>'.str_repeat('<span class="fico-star fc-l fnmr"></span>', intval($matches[1])).str_repeat('<span class="fico-star fc-s fnmr"></span>', (5 - intval($matches[1]))).($cic++ % 2 ? '<br />' : '');
	}
}

if(!$_G['setting']['commentnumber'] || !$_G['inajax']) {
	showmessage('postcomment_closed');
}
require_once libfile('function/discuzcode');
$commentlimit = intval($_G['setting']['commentnumber']);
$page = max(1, $_G['page']);
$start_limit = ($page - 1) * $commentlimit;
$commentrows = table_forum_postcomment::t()->fetch_all_by_search(null, $_GET['pid'], null, null, null, null, null, $start_limit, $commentlimit);
$replytargets = table_forum_postcomment::t()->fetch_reply_targets($commentrows);
$comments = [];
foreach($commentrows as $comment) {
	$comment['avatar'] = avatar($comment['authorid'], 'small');
	$comment['dateline'] = dgmdate($comment['dateline'], 'u');
	$comment['comment'] = str_replace(['[b]', '[/b]', '[/color]'], ['<b>', '</b>', '</font>'], preg_replace('/\[color=([#\w]+?)\]/i', "<font color=\"\\1\">", $comment['comment']));
	if(!empty($comment['rpid']) && !empty($replytargets[$comment['rpid']]) && $replytargets[$comment['rpid']]['pid'] == $comment['pid']) {
		$comment['reply_author'] = $replytargets[$comment['rpid']]['author'];
		$comment['reply_authorid'] = $replytargets[$comment['rpid']]['authorid'];
	} else {
		$comment['reply_author'] = '';
		$comment['reply_authorid'] = 0;
	}
	$comments[] = $comment;
}
forum_misc_commentmore_callback_1(0, 1);
$totalcomment = table_forum_postcomment::t()->fetch_standpoint_by_pid($_GET['pid']);
$totalcomment = $totalcomment['comment'];
$totalcomment = preg_replace_callback('/<i>([\.\d]+)<\/i>/', 'forum_misc_commentmore_callback_1', $totalcomment);
$count = table_forum_postcomment::t()->count_by_search(null, $_GET['pid']);
$multi = multi($count, $commentlimit, $page, "forum.php?mod=misc&action=commentmore&tid={$_G['tid']}&pid={$_GET['pid']}");
include template('forum/comment_more');
	