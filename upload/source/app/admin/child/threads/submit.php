<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if($optype != '' && (empty($_GET['formhash']) || FORMHASH != $_GET['formhash'])) {
	js_cpmsg('undefined_action');
}

$tidsarray = isset($_GET['tids']) ? explode(',', $_GET['tids']) : $_GET['tidarray'];
$tidsadd = 'tid IN ('.dimplode($tidsarray).')';
if($optype == 'moveforum') {
	if(!table_forum_forum::t()->check_forum_exists($_GET['toforum'])) {
		js_cpmsg('threads_move_invalid');
	}
	table_forum_thread::t()->update($tidsarray, ['fid' => $_GET['toforum'], 'typeid' => $_GET['threadtypeid'], 'isgroup' => 0]);
	loadcache('posttableids');
	$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : ['0'];
	foreach($posttableids as $id) {
		table_forum_post::t()->update_by_tid($id, $tidsarray, ['fid' => $_GET['toforum']]);
	}

	foreach(explode(',', $_GET['fids'].','.$_GET['toforum']) as $fid) {
		updateforumcount(intval($fid));
	}

	$cpmsg = cplang('threads_succeed');

} elseif($optype == 'movesort') {

	if($_GET['tosort'] != 0) {
		if(!table_forum_threadtype::t()->fetch($_GET['tosort'])) {
			js_cpmsg('threads_move_invalid');
		}
	}

	table_forum_thread::t()->update($tidsarray, ['sortid' => $_GET['tosort']]);
	$cpmsg = cplang('threads_succeed');

} elseif($optype == 'delete') {

	require_once libfile('function/delete');
	deletethread($tidsarray, !$_GET['donotupdatemember'], !$_GET['donotupdatemember']);

	if($_G['setting']['globalstick']) {
		updatecache('globalstick');
	}

	foreach(explode(',', $_GET['fids']) as $fid) {
		updateforumcount(intval($fid));
	}

	$cpmsg = cplang('threads_succeed');

} elseif($optype == 'deleteattach') {

	require_once libfile('function/delete');
	deleteattach($tidsarray, 'tid');
	table_forum_thread::t()->update($tidsarray, ['attachment' => 0]);
	loadcache('posttableids');
	$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : ['0'];
	foreach($posttableids as $id) {
		table_forum_post::t()->update_by_tid($id, $tidsarray, ['attachment' => '0']);
	}

	$cpmsg = cplang('threads_succeed');

} elseif($optype == 'stick') {

	table_forum_thread::t()->update($tidsarray, ['displayorder' => $_GET['stick_level']]);
	$my_act = $_GET['stick_level'] ? 'sticky' : 'update';

	if($_G['setting']['globalstick']) {
		updatecache('globalstick');
	}

	$cpmsg = cplang('threads_succeed');

} elseif($optype == 'adddigest') {

	foreach(table_forum_thread::t()->fetch_all_by_tid($tidsarray) as $thread) {
		if($_GET['digest_level'] == $thread['digest']) continue;
		$extsql = [];
		if($_GET['digest_level'] > 0 && $thread['digest'] == 0) {
			$extsql = ['digestposts' => 1];
		}
		if($_GET['digest_level'] == 0 && $thread['digest'] > 0) {
			$extsql = ['digestposts' => -1];
		}
		updatecreditbyaction('digest', $thread['authorid'], $extsql, '', $_GET['digest_level'] - $thread['digest'], 1, $thread['fid']);
	}
	table_forum_thread::t()->update($tidsarray, ['digest' => $_GET['digest_level']]);
	$my_act = $_GET['digest_level'] ? 'digest' : 'update';

	$cpmsg = cplang('threads_succeed');

} elseif($optype == 'addstatus') {

	table_forum_thread::t()->update($tidsarray, ['closed' => $_GET['status']]);
	$my_opt = $_GET['status'] ? 'close' : 'open';

	$cpmsg = cplang('threads_succeed');

} elseif($operation == 'forumstick') {
	shownav('topic', 'threads_forumstick');
	loadcache(['forums', 'grouptype']);
	$forumstickthreads = table_common_setting::t()->fetch_setting('forumstickthreads', true);
	if(!submitcheck('forumsticksubmit')) {
		if(!$do) {
			showsubmenu('threads_forumstick', [
				['admin', 'threads&operation=forumstick', !$do],
				['add', 'threads&operation=forumstick&do=add', $do == 'add'],
			]);
			showtips('threads_forumstick_tips');
			showformheader('threads&operation=forumstick');
			showtableheader('admin', 'fixpadding');
			showsubtitle(['', 'subject', 'threads_forumstick_forum', 'threads_forumstick_group', 'edit']);
			if(is_array($forumstickthreads)) {
				foreach($forumstickthreads as $k => $v) {
					$forumnames = [];
					foreach($v['forums'] as $forum_id) {
						if($_G['cache']['forums'][$forum_id]['name']) {
							$forumnames[] = $name = $_G['cache']['forums'][$forum_id]['name'];
						} elseif($_G['cache']['grouptype']['first'][$forum_id]['name']) {
							$grouptypes[] = $name = $_G['cache']['grouptype']['first'][$forum_id]['name'];
						} elseif($_G['cache']['grouptype']['second'][$forum_id]['name']) {
							$grouptypes[] = $name = $_G['cache']['grouptype']['second'][$forum_id]['name'];
						}
					}
					showtablerow('', ['class="td25"'], [
						"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$k\">",
						"<a href=\"forum.php?mod=viewthread&tid={$v['tid']}\" target=\"_blank\">{$v['subject']}</a>",
						(is_array($forumnames) ? implode(', ', $forumnames) : (string)$forumnames),
						(is_array($grouptypes) ? implode(', ', $grouptypes) : (string)$grouptypes),
						"<a href=\"".ADMINSCRIPT."?action=threads&operation=forumstick&do=edit&id=$k\">{$lang['threads_forumstick_targets_change']}</a>",
					]);
				}
			}
			showsubmit('forumsticksubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();
		} elseif($do == 'add') {
			showsubmenu('threads_forumstick', [
				['admin', 'threads&operation=forumstick', !$do],
				['add', 'threads&operation=forumstick&do=add', $do == 'add'],
			]);
			require_once libfile('function/forumlist');
			showformheader('threads&operation=forumstick&do=add');
			showtableheader('add', 'fixpadding');
			showsetting('threads_forumstick_threadurl', 'forumstick_url', '', 'text');
			$targetsselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
			require_once libfile('function/group');
			$groupselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.get_groupselect(0, 0, 0).'</select>';
			showsetting('threads_forumstick_targets', '', '', $targetsselect);
			showsetting('threads_forumstick_targetgroups', '', '', $groupselect);
			echo '<input type="hidden" value="add" name="do" />';
			showsubmit('forumsticksubmit', 'submit');
			showtablefooter();
			showformfooter();
		} elseif($do == 'edit') {
			showchildmenu([['threads_forumstick', 'threads&operation=forumstick'], ['id:'.$_GET['id'], '']], cplang('edit'));
			require_once libfile('function/forumlist');
			showformheader("threads&operation=forumstick&do=edit&id={$_GET['id']}");
			showtableheader('edit', 'fixpadding');
			$targetsselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
			require_once libfile('function/group');
			$groupselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.get_groupselect(0, 0, 0).'</select>';
			foreach($forumstickthreads[$_GET['id']]['forums'] as $target) {
				$targetsselect = preg_replace("/(\<option value=\"$target\")([^\>]*)(\>)/", "\\1 \\2 selected=\"selected\" \\3", $targetsselect);
				$groupselect = preg_replace("/(\<option value=\"$target\")([^\>]*)(\>)/", "\\1 \\2 selected=\"selected\" \\3", $groupselect);
			}
			showsetting('threads_forumstick_targets', '', '', $targetsselect);
			showsetting('threads_forumstick_targetgroups', '', '', $groupselect);
			echo '<input type="hidden" value="edit" name="do" />';
			echo "<input type=\"hidden\" value=\"{$_GET['id']}\" name=\"id\" />";
			showsubmit('forumsticksubmit', 'submit');
			showtablefooter();
			showformfooter();
		}
	} else {
		if(!$do) {
			$do = 'del';
		}
		if($do == 'del') {
			if(!empty($_GET['delete']) && is_array($_GET['delete'])) {
				$del_tids = [];
				foreach($_GET['delete'] as $del_tid) {
					unset($forumstickthreads[$del_tid]);
					$del_tids[] = $del_tid;
				}
				if($del_tids) {
					table_forum_thread::t()->update($del_tids, ['displayorder' => 0]);
				}
			} else {
				cpmsg('threads_forumstick_del_nochoice', '', 'error');
			}
		} elseif($do == 'add') {
			$_GET['forumstick_url'] = rawurldecode($_GET['forumstick_url']);
			if(preg_match('/tid=(\d+)/i', $_GET['forumstick_url'], $matches)) {
				$forumstick_tid = $matches[1];
			} elseif(is_array($_G['setting']['rewritestatus']) && in_array('forum_viewthread', $_G['setting']['rewritestatus']) && $_G['setting']['rewriterule']['forum_viewthread']) {
				preg_match_all('/(\{tid\})|(\{page\})|(\{prevpage\})/', $_G['setting']['rewriterule']['forum_viewthread'], $matches);
				$matches = $matches[0];

				$tidpos = array_search('{tid}', $matches);
				if($tidpos === false) {
					cpmsg('threads_forumstick_url_invalid', 'action=threads&operation=forumstick&do=add', 'error');
				}
				$tidpos = $tidpos + 1;
				$rewriterule = str_replace(
					['\\', '(', ')', '[', ']', '.', '*', '?', '+'],
					['\\\\', '\(', '\)', '\[', '\]', '\.', '\*', '\?', '\+'],
					$_G['setting']['rewriterule']['forum_viewthread']
				);

				$rewriterule = str_replace(['{tid}', '{page}', '{prevpage}'], '(\d+?)', $rewriterule);
				$rewriterule = str_replace(['{', '}'], ['\{', '\}'], $rewriterule);
				preg_match("/$rewriterule/i", $_GET['forumstick_url'], $match_result);
				$forumstick_tid = $match_result[$tidpos];
			} elseif(is_array($_G['setting']['rewritestatus']) && in_array('all_script', $_G['setting']['rewritestatus']) && $_G['setting']['rewriterule']['all_script']) {
				preg_match_all('/(\{script\})|(\{param\})/', $_G['setting']['rewriterule']['all_script'], $matches);
				$matches = $matches[0];
				$parampos = array_search('{param}', $matches);
				if($parampos === false) {
					cpmsg('threads_forumstick_url_invalid', 'action=threads&operation=forumstick&do=add', 'error');
				}
				$parampos = $parampos + 1;
				$rewriterule = str_replace(
					['\\', '(', ')', '[', ']', '.', '*', '?', '+'],
					['\\\\', '\(', '\)', '\[', '\]', '\.', '\*', '\?', '\+'],
					$_G['setting']['rewriterule']['all_script']
				);
				$rewriterule = str_replace(['{script}', '{param}'], '([\w\d\-=]+?)', $rewriterule);
				$rewriterule = str_replace(['{', '}'], ['\{', '\}'], $rewriterule);
				$rewriterule = "/\\/$rewriterule/i";
				preg_match($rewriterule, $_GET['forumstick_url'], $match_result);
				$param = $match_result[$parampos];

				if(preg_match('/viewthread-tid-(\d+)/i', $param, $tidmatch)) {
					$forumstick_tid = $tidmatch[1];
				} else {
					cpmsg('threads_forumstick_url_invalid', 'action=threads&operation=forumstick&do=add', 'error');
				}
			} else {
				cpmsg('threads_forumstick_url_invalid', 'action=threads&operation=forumstick&do=add', 'error');
			}
			if(empty($_GET['forumsticktargets'])) {
				cpmsg('threads_forumstick_targets_empty', 'action=threads&operation=forumstick&do=add', 'error');
			}
			$stickthread = table_forum_thread::t()->fetch_thread($forumstick_tid);
			$stickthread_tmp = [
				'subject' => $stickthread['subject'],
				'tid' => $forumstick_tid,
				'forums' => $_GET['forumsticktargets'],
			];
			$forumstickthreads[$forumstick_tid] = $stickthread_tmp;
			table_forum_thread::t()->update($forumstick_tid, ['displayorder' => 4]);
		} elseif($do == 'edit') {
			if(empty($_GET['forumsticktargets'])) {
				cpmsg('threads_forumstick_targets_empty', "action=threads&operation=forumstick&do=edit&id={$_GET['id']}", 'error');
			}
			$forumstickthreads[$_GET['id']]['forums'] = $_GET['forumsticktargets'];
			table_forum_thread::t()->update($forumstick_tid, ['displayorder' => 4]);
		}

		table_common_setting::t()->update_setting('forumstickthreads', $forumstickthreads);
		updatecache(['forumstick', 'setting']);
		cpmsg('threads_forumstick_'.$do.'_succeed', 'action=threads&operation=forumstick', 'succeed');
	}
}

$_GET['tids'] && deletethreadcaches($_GET['tids']);

js_cpmsg($cpmsg);
	