<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


if($maxposition) {
	$start = ($page - 1) * $_G['ppp'] + 1;
	$end = $start + $_G['ppp'];
	if($ordertype == 1) {
		$end = $maxposition - ($page - 1) * $_G['ppp'] + ($page > 1 ? 2 : 1);
		$start = $end - $_G['ppp'] + ($page > 1 ? 0 : 1);
		$start = max([1, $start]);
	}
	$have_badpost = $realpost = $lastposition = 0;
	foreach(table_forum_post::t()->fetch_all_by_tid_range_position($posttableid, $_G['tid'], $start, $end, $maxposition, $ordertype) as $post) {
		if($post['invisible'] != 0) {
			$have_badpost = 1;
		}
		$cachepids[$post['position']] = $post['pid'];
		$postarr[$post['position']] = $post;
		$lastposition = $post['position'];
	}
	$realpost = is_array($postarr) ? count($postarr) : 0;
	if($realpost != $_G['ppp'] || $have_badpost) {
		$k = 0;
		for($i = $start; $i < $end; $i++) {
			if(!empty($cachepids[$i])) {
				$k = $cachepids[$i];
				$isdel_post[$i] = ['deleted' => 1, 'pid' => $k, 'message' => '', 'position' => $i];
			} elseif($i < $maxposition || ($lastposition && $i < $lastposition)) {
				$isdel_post[$i] = ['deleted' => 1, 'pid' => $k, 'message' => '', 'position' => $i];
			}
			$k++;
		}
	}
	$pagebydesc = false;
}

if(getgpc('checkrush') && $rushreply) {
	$_G['forum_thread']['replies'] = $temp_reply;
}

if(!$maxposition && empty($postarr)) {

	if(empty($_GET['viewpid'])) {
		if($_G['forum_thread']['special'] == 2) {
			$postarr = table_forum_post::t()->fetch_all_tradepost_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $tpids, $_G['forum_pagebydesc'], $ordertype, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
		} elseif($_G['forum_thread']['special'] == 5) {
			$postarr = table_forum_post::t()->fetch_all_debatepost_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $_GET['stand'], $_G['forum_pagebydesc'], $ordertype, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
		} else {
			$postarr = table_forum_post::t()->fetch_all_common_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $_G['forum_pagebydesc'], $ordertype, $_G['forum_thread']['replies'] + 1, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
		}
	} else {
		$post = [];
		if($_G['forum_thread']['special'] == 2) {
			if(!in_array($_GET['viewpid'], $tpids)) {
				$post = table_forum_post::t()->fetch_post('tid:'.$_G['tid'], $_GET['viewpid']);
			}
		} elseif($_G['forum_thread']['special'] == 5) {
			$post = table_forum_post::t()->fetch_post('tid:'.$_G['tid'], $_GET['viewpid']);
			$debatpost = table_forum_debatepost::t()->fetch($_GET['viewpid']);
			if(!isset($_GET['stand']) || (isset($_GET['stand']) && ($post['first'] == 1 || $debatpost['stand'] == $_GET['stand']))) {
				$post = array_merge($post, $debatpost);
			} else {
				$post = [];
			}
			unset($debatpost);
		} else {
			$post = table_forum_post::t()->fetch_post('tid:'.$_G['tid'], $_GET['viewpid']);
		}
		if($post['tid'] != $_G['tid']) {
			$post = [];
		}

		if($post) {
			if($visibleallflag || (!$visibleallflag && !$post['invisible'])) {
				$postarr[0] = $post;
			}
		}
	}

}

if(!empty($isdel_post)) {
	$updatedisablepos = false;
	foreach($isdel_post as $id => $post) {
		if(isset($postarr[$id]['invisible']) && ($postarr[$id]['invisible'] == 0 || $visibleallflag)) {
			continue;
		}
		$postarr[$id] = $post;
		$updatedisablepos = true;
	}
	if($updatedisablepos && !$rushreply) {
		table_forum_threaddisablepos::t()->insert(['tid' => $_G['tid']], false, true);
		dheader('Location:'.$_G['siteurl'].'forum.php?mod=viewthread&tid='.$_G['tid'].($_G['forum_auditstatuson'] ? '&modthreadkey='.$_GET['modthreadkey'] : '').($_G['page'] > 1 ? '&page='.$_G['page'] : ''));
	}
	$ordertype != 1 ? ksort($postarr) : krsort($postarr);
}

$summary = '';
$curpagepids = [];
if($page == 1 && $ordertype == 1) {
	$firstpost = table_forum_post::t()->fetch_threadpost_by_tid_invisible($_G['tid']);
	if($firstpost['invisible'] == 0 || $visibleallflag == 1) {
		$postarr = array_merge([$firstpost], $postarr);
		unset($firstpost);
	}
}
foreach($postarr as $post) {
	$curpagepids[] = $post['pid'];
}
$tagnames = $locationpids = $hotpostarr = $hotpids = $member_blackList = [];

$remainhots = ($_G['page'] == 1 && !$rushreply && !$hiddenreplies && !$_G['forum_thread']['special'] && !$_G['forum']['noforumrecommend'] && empty($_GET['authorid'])) ? $_G['setting']['threadhotreplies'] : 0;
if($remainhots) {
	$hotpids = array_keys(table_forum_hotreply_number::t()->fetch_all_by_tid_total($_G['tid'], 10));
	$remainhots = $remainhots - count($hotpids);
}

if($_G['setting']['nofilteredpost'] && $_G['forum_thread']['replies'] > $_G['setting']['postperpage'] && $remainhots) {
	$hotpids = array_merge($hotpids, array_keys(table_forum_filter_post::t()->fetch_all_by_tid_postlength_limit($_G['tid'], $remainhots)));
}

if($hotpids) {
	$hotposts = table_forum_post::t()->fetch_all_by_pid($posttableid, $hotpids);
	foreach($hotpids as $hotpid) {
		if($hotposts[$hotpid]) {
			$hotpostarr[$hotpid] = $hotposts[$hotpid];
			$hotpostarr[$hotpid]['hotrecommended'] = true;
		}
	}
	unset($hotposts);
}

if($hotpostarr || $sticklist) {
	$_newpostarr_first = array_shift($postarr);
	$postarr = (array)$sticklist + (array)$hotpostarr + $postarr;
	array_unshift($postarr, $_newpostarr_first);
	unset($_newpostarr_first, $sticklist);
}

