<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowdelpost'] || empty($_GET['topiclist'])) {
	showmessage('no_privilege_delcomment');
}

if(!submitcheck('modsubmit')) {

	$commentid = intval($_GET['topiclist'][0]);
	$comment = table_forum_postcomment::t()->fetch($commentid);
	if(!$comment || $comment['tid'] != $_G['tid'] || !$comment['pid']) {
		showmessage('postcomment_not_found');
	}
	$pid = $comment['pid'];
	$deleteid = '<input type="hidden" name="topiclist" value="'.$commentid.'" />';

	include template('forum/topicadmin_action');

} else {

	$reason = checkreasonpm();
	$modaction = 'DCM';

	$commentid = intval($_GET['topiclist']);
	$postcomment = table_forum_postcomment::t()->fetch($commentid);
	if(!$postcomment || $postcomment['tid'] != $_G['tid']) {
		showmessage('postcomment_not_found');
	}
	table_forum_postcomment::t()->delete($commentid);
	$result = table_forum_postcomment::t()->count_by_pid($postcomment['pid']);
	if(!$result) {
		table_forum_post::t()->update_post($_G['thread']['posttableid'], $postcomment['pid'], ['comment' => 0]);
	}
	if($thread['comments']) {
		table_forum_thread::t()->update($_G['tid'], ['comments' => $thread['comments'] - 1]);
	}
	if(!$postcomment['rpid']) {
		updatepostcredits('-', $postcomment['authorid'], 'reply', $_G['fid']);
	}

	$totalcomment = [];
	foreach(table_forum_postcomment::t()->fetch_all_by_pid_score($postcomment['pid'], 1) as $comment) {
		if(strexists($comment['comment'], '<br />')) {
			if(preg_match_all('/([^:]+?):\s<i>(\d+)<\/i>/', $comment['comment'], $a)) {
				foreach($a[1] as $k => $itemk) {
					$totalcomment[trim($itemk)][] = $a[2][$k];
				}
			}
		}
	}
	$totalv = '';
	foreach($totalcomment as $itemk => $itemv) {
		$totalv .= strip_tags(trim($itemk)).': <i>'.(sprintf('%1.1f', array_sum($itemv) / count($itemv))).'</i> ';
	}

	if($totalv) {
		table_forum_postcomment::t()->update_by_pid($postcomment['pid'], ['comment' => $totalv, 'dateline' => TIMESTAMP + 1], false, false, 0);
	} else {
		table_forum_postcomment::t()->delete_by_pid($postcomment['pid'], false, 0);
	}
	table_forum_postcache::t()->delete($postcomment['pid']);

	$resultarray = [
		'redirect' => "forum.php?mod=viewthread&tid={$_G['tid']}&page=$page",
		'reasonpm' => ($sendreasonpm ? ['data' => [$postcomment], 'var' => 'post', 'item' => 'reason_delete_comment', 'notictype' => 'pcomment'] : []),
		'reasonvar' => ['tid' => $thread['tid'], 'pid' => $postcomment['pid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason],
		'modtids' => 0,
		'modlog' => $thread
	];

}

