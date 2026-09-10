<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$post = table_forum_post::t()->fetch_post('tid:'.$_G['tid'], $_GET['pid']);
if($post['authorid'] != $_G['uid']) {
	showmessage('postdelete_only_yourself');
}
if(!$_G['setting']['editperdel']) {
	showmessage('post_edit_thread_ban_del');
}
if(submitcheck('postdeletesubmit')) {
	$url_forward = 'forum.php?mod=viewthread&tid=' .$post['tid'];
	require_once libfile('function/delete');

	if($post['first']) {
		if($_G['forum']['recyclebin']) {
			deletethread([$post['tid']], true, true, true);
			manage_addnotify('verifyrecycle', 1);
		} else {
			deletethread([$post['tid']], true, true);
			deletepost([$post['tid']], 'tid', true);
		}
		updateforumcount($post['fid']);
		updatethreadcount($post['tid']);
		$url_forward = 'forum.php?mod=forumdisplay&fid=' .$post['fid'];
	} else {
		if($_G['forum']['recyclebin']) {
			deletepost([$post['pid']], 'pid', true, false, true);
			manage_addnotify('verifyrecyclepost', 1);
		} else {
			deletepost([$post['pid']], 'pid', true);
		}
		updatethreadcount($post['tid']);
	}

	if(!empty($_G['inajax'])) {
		showmessage('postdelete_succeed', $url_forward, [], ['location' => true]);
	} else {
		showmessage('postdelete_succeed', $url_forward);
	}
} else {
	include template('forum/postdelete');
}
	