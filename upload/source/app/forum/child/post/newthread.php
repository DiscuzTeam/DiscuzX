<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['forum']['fid']) || $_G['forum']['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if(($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate'])) {
	showmessage('group_nopermission', NULL, ['grouptitle' => $_G['group']['grouptitle']], ['login' => 1]);
}

if(!$_G['uid'] && !((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])))) {
	if(!defined('IN_MOBILE')) {
		showmessage('postperm_login_nopermission', NULL, [], ['login' => 1]);
	} else {
		showmessage('postperm_login_nopermission_mobile', NULL, ['referer' => rawurlencode(dreferer())], ['login' => 1]);
	}
} elseif(empty($_G['forum']['allowpost'])) {
	if(!$_G['forum']['postperm'] && !$_G['group']['allowpost']) {
		showmessage('postperm_none_nopermission', NULL, [], ['login' => 1]);
	} elseif($_G['forum']['postperm'] && !forumperm($_G['forum']['postperm'])) {
		showmessagenoperm('postperm', $_G['fid'], $_G['forum']['formulaperm']);
	}
} elseif($_G['forum']['allowpost'] == -1) {
	showmessage('post_forum_newthread_nopermission', NULL);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_secmobile'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage('postperm_login_nopermission', NULL, [], ['login' => 1]);
}

checklowerlimit('post', 0, 1, $_G['forum']['fid']);

if(!submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {

	$st_t = $_G['uid'].'|'.TIMESTAMP;
	dsetcookie('st_t', $st_t.'|'.md5($st_t.$_G['config']['security']['authkey']));

	if(helper_access::check_module('group')) {
		$mygroups = $groupids = [];
		$groupids = table_forum_groupuser::t()->fetch_all_fid_by_uids($_G['uid']);
		$groupids = array_slice($groupids, 0, 20);
		$query = table_forum_forum::t()->fetch_all_info_by_fids($groupids);
		foreach($query as $group) {
			$mygroups[$group['fid']] = $group['name'];
		}
	}

	$savethreads = [];
	$savethreadothers = [];
	foreach(table_forum_post::t()->fetch_all_by_authorid(0, $_G['uid'], false, '', 0, 20, 1, -3) as $savethread) {
		$savethread['dateline'] = dgmdate($savethread['dateline'], 'u');
		if($_G['fid'] == $savethread['fid']) {
			$savethreads[] = $savethread;
		} else {
			$savethreadothers[] = $savethread;
		}
	}
	$savethreadcount = count($savethreads);
	$savethreadothercount = count($savethreadothers);
	if($savethreadothercount) {
		loadcache('forums');
	}
	$savecount = $savethreadcount + $savethreadothercount;
	unset($savethread);

	$isfirstpost = 1;
	$allownoticeauthor = 1;
	$tagoffcheck = '';
	$showthreadsorts = !empty($sortid) || getglobal('forum/threadsorts/required') && empty($special);
	if(empty($sortid) && empty($special) && getglobal('forum/threadsorts/required') && $_G['forum']['threadsorts']['types']) {
		$tmp = array_keys($_G['forum']['threadsorts']['types']);
		$sortid = $tmp[0];

		require_once childfile('threadsorts');
	}

	if($special == 2 && $_G['group']['allowposttrade']) {

		$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
		$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m') + 1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m') + 3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m') + 6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y') + 1));

	} elseif($specialextra) {

		$threadpluginclass = null;
		if(isset($_G['setting']['threadplugins'][$specialextra]['module'])) {
			$threadpluginfile = DISCUZ_PLUGIN($_G['setting']['threadplugins'][$specialextra]['module']).'.class.php';
			if(file_exists($threadpluginfile)) {
				@include_once $threadpluginfile;
				$classname = 'threadplugin_'.$specialextra;
				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread')) {
					$threadplughtml = $threadpluginclass->newthread($_G['fid']);
					$pluginlang = lang('plugin/'.$specialextra);
					$buttontext = property_exists($threadpluginclass, 'buttontext') ? ($pluginlang[$threadpluginclass->buttontext] ?? $threadpluginclass->buttontext) : '';
					$iconfile = $threadpluginclass->iconfile;
					$iconsflip = $_G['cache']['icons'] ? array_flip($_G['cache']['icons']) : [];
					$thread['iconid'] = $iconsflip[$iconfile];
				}
			}
		}

		if(!is_object($threadpluginclass)) {
			$specialextra = '';
		}
	}

	if($special == 4) {
		$activity = ['starttimeto' => '', 'starttimefrom' => '', 'place' => '', 'class' => '', 'cost' => '', 'number' => '', 'gender' => '', 'expiration' => ''];
		$activitytypelist = $_G['setting']['activitytype'] ? explode("\n", trim($_G['setting']['activitytype'])) : '';
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach(0);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
	}

	!isset($attachs['unused']) && $attachs['unused'] = [];
	!isset($imgattachs['unused']) && $imgattachs['unused'] = [];

	if(!empty($_G['setting']['editormodetype']) && (!$_G['setting']['json_independence'] || empty($_GET['special']))) {
		$fields = ['blockid', 'type', 'available', 'columns', 'sort', 'name', 'identifier', 'class', 'config', 'plugin', 'filename'];
		$editorblocks = table_common_editorblock::t()->fetch_all_block_avaliable($fields);
		foreach($editorblocks as $ekey => $evalue) {
			if(!empty($evalue['plugin'])) {
				$jspath = 'source/plugin/'.$evalue['plugin'].'/editorblock';
			} else {
				$jspath = 'static/js/editorjs';
			}
			$editorblocks[$ekey]['jspath'] = $jspath.'/tools/'.$evalue['identifier'].'/'.$evalue['filename'].'.js';
		}
		if($_G['setting']['json_independence']) {
			include template('forum/jsoneditor');
		} else {
			getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');
		}

		//getgpc('infloat') ? include template('forum/jsoneditor_infloat') : include template('forum/jsoneditor');
	} else {
		getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');
	}

} else {
	if(getgpc('mygroupid')) {
		$mygroupid = explode('__', $_GET['mygroupid']);
		$mygid = intval($mygroupid[0]);
		if($mygid) {
			$mygname = $mygroupid[1];
			if(count($mygroupid) > 2) {
				unset($mygroupid[0]);
				$mygname = implode('__', $mygroupid);
			}
			$message .= '[groupid='.intval($mygid).']'.$mygname.'[/groupid]';
			table_forum_forum::t()->update_commoncredits(intval($mygroupid[0]));
		}
	}
	$modthread = C::m('\forum\model_thread');
	$bfmethods = $afmethods = [];

	$params = [
		'subject' => $subject,
		'message' => $message,
		'content' => $content,
		'contentType' => $contentType,
		'contentEditor' => $contentEditor,
		'typeid' => $typeid,
		'sortid' => $sortid,
		'special' => $special,
	];

	$_GET['save'] = $_G['uid'] ? $_GET['save'] : 0;

	if($_G['group']['allowsetpublishdate'] && $_GET['cronpublish'] && $_GET['cronpublishdate']) {
		$publishdate = strtotime($_GET['cronpublishdate']);
		if($publishdate > $_G['timestamp']) {
			$_GET['save'] = 1;
		} else {
			$publishdate = $_G['timestamp'];
		}
	} else {
		$publishdate = $_G['timestamp'];
	}
	$params['publishdate'] = $publishdate;
	$params['save'] = $_GET['save'];

	$params['sticktopic'] = getgpc('sticktopic');

	$params['digest'] = getgpc('addtodigest');
	$params['readperm'] = $readperm;
	$params['isanonymous'] = getgpc('isanonymous');
	$params['price'] = $_GET['price'];


	if(in_array($special, [1, 2, 3, 4, 5])) {
		$specials = [
			1 => 'forum\extend_thread_poll',
			2 => 'forum\extend_thread_trade',
			3 => 'forum\extend_thread_reward',
			4 => 'forum\extend_thread_activity',
			5 => 'forum\extend_thread_debate'
		];
		$bfmethods[] = ['class' => $specials[$special], 'method' => 'before_newthread'];
		$afmethods[] = ['class' => $specials[$special], 'method' => 'after_newthread'];

		if(!empty($_GET['addfeed'])) {
			$modthread->attach_before_method('feed', ['class' => $specials[$special], 'method' => 'before_feed']);
		}
	}

	if($specialextra) {
		@include_once DISCUZ_PLUGIN($_G['setting']['threadplugins'][$specialextra]['module']).'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit')) {
			$threadpluginclass->newthread_submit($_G['fid']);
		}
		$special = 127;
		$params['special'] = 127;
		$params['message'] .= chr(0).chr(0).chr(0).$specialextra;
	}

	$params['typeexpiration'] = getgpc('typeexpiration');

	// 原创声明 0未声明、1声明原创、-1非原创
	if(!empty($original)) {
		$params['original'] = $original;
	}
	// 文章来源
	if(!empty($source)) {
		$params['source'] = $source;
	}

	$params['ordertype'] = getgpc('ordertype');

	$params['hiddenreplies'] = getgpc('hiddenreplies');

	$params['allownoticeauthor'] = $_GET['allownoticeauthor'];
	$params['tags'] = $_GET['tags'];
	$params['bbcodeoff'] = getgpc('bbcodeoff');
	$params['smileyoff'] = getgpc('smileyoff');
	$params['parseurloff'] = getgpc('parseurloff');
	$params['usesig'] = $_GET['usesig'];
	$params['htmlon'] = getgpc('htmlon');
	if($_G['group']['allowimgcontent']) {
		$params['imgcontent'] = $_GET['imgcontent'];
		$params['imgcontentwidth'] = $_G['setting']['imgcontentwidth'] ? intval($_G['setting']['imgcontentwidth']) : 100;
	}

	$params['geoloc'] = diconv(getgpc('geoloc'), 'UTF-8');


	// 开始处理json编辑器内容中的图片、视频等附件
	if(is_valid_non_empty_json($params['content'], true)) {
		$blocksData = json_decode($params['content'], true);

		$blocks = table_common_editorblock::t()->fetch_all_block_by_type([1, 2, 3, 4, 5]);
		$identifiers = array_map(function($block) {
			return $block['identifier'];
		}, $blocks);
		$requiredIdentifiers = ['attaches', 'audio', 'image', 'video', 'images'];
		$missingIdentifiers = array_diff($requiredIdentifiers, $identifiers);
		if (!empty($missingIdentifiers)) {
			$identifiers = array_merge($identifiers, $missingIdentifiers);
		}

		foreach($blocksData['blocks'] as $key => $value) {
			if(in_array($value['type'], $identifiers)) {
				if(!empty($value['data']['files'])) {
					$_aids = $value['data']['files'] ?? [];
					foreach($_aids as $_aid) {
						if(!empty($_aid)) {
							$_GET['attachnew'][$_aid['aid']] = ['description' => '', 'readperm' => '', 'price' => 0];
						}
					}
				} elseif(!empty($value['data']['file'])) {
					$_aid = $value['data']['file']['aid'];
					if(!empty($_aid)) {
						$_GET['attachnew'][$_aid] = ['description' => '', 'readperm' => '', 'price' => 0];
					}
				}
			}
		}
	}
	// 结束处理json编辑器内容中的图片、视频等附件


	if(getgpc('rushreply')) {
		$bfmethods[] = ['class' => 'forum\extend_thread_rushreply', 'method' => 'before_newthread'];
		$afmethods[] = ['class' => 'forum\extend_thread_rushreply', 'method' => 'after_newthread'];
	}

	$bfmethods[] = ['class' => 'forum\extend_thread_replycredit', 'method' => 'before_newthread'];
	$afmethods[] = ['class' => 'forum\extend_thread_replycredit', 'method' => 'after_newthread'];

	if($sortid) {
		$bfmethods[] = ['class' => 'forum\extend_thread_sort', 'method' => 'before_newthread'];
		$afmethods[] = ['class' => 'forum\extend_thread_sort', 'method' => 'after_newthread'];
	}
	$bfmethods[] = ['class' => 'forum\extend_thread_allowat', 'method' => 'before_newthread'];
	$afmethods[] = ['class' => 'forum\extend_thread_allowat', 'method' => 'after_newthread'];
	$afmethods[] = ['class' => 'forum\extend_thread_image', 'method' => 'after_newthread'];

	if(!empty($_GET['adddynamic'])) {
		$afmethods[] = ['class' => 'forum\extend_thread_follow', 'method' => 'after_newthread'];
	}
	if(!empty($_GET['adddynamic_doing'])) {
		$afmethods[] = ['class' => 'forum\extend_thread_doing', 'method' => 'after_newthread'];
	}

	$modthread->attach_before_methods('newthread', $bfmethods);
	$modthread->attach_after_methods('newthread', $afmethods);

	$return = $modthread->newthread($params);
	$tid = $modthread->tid;
	$pid = $modthread->pid;

	dsetcookie('clearUserdata', 'forum');
	if($specialextra) {
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit_end')) {
			$threadpluginclass->newthread_submit_end($_G['fid'], $modthread->tid);
		}
	}
	if(!$modthread->param('modnewthreads') && !empty($_GET['addfeed'])) {
		$modthread->feed();
	}


	// cover start
	if($cover_aid) {
		convertunusedattach($cover_aid, $tid, $pid);
		$threadimage = table_forum_attachment_n::t()->fetch_attachment('aid:'.$cover_aid, $cover_aid);
		if(setthreadcover($pid, $tid, $cover_aid, 0)) {
			table_forum_threadimage::t()->delete_by_tid($tid);
			table_forum_threadimage::t()->insert([
				'tid' => $tid,
				'attachment' => $threadimage['attachment'],
				'remote' => $threadimage['remote'],
			]);
		}
	}
	// cover end


	if(rewriterulecheck('forum_viewthread')) {
		$returnurl = rewriteoutput('forum_viewthread', 1, '', $modthread->tid, 1, '', $extra);
	} else {
		$returnurl = "forum.php?mod=viewthread&tid={$modthread->tid}&extra=$extra";
	}
	$values = ['fid' => $modthread->forum('fid'), 'tid' => $modthread->tid, 'pid' => $modthread->pid, 'coverimg' => '', 'sechash' => !empty($_GET['sechash']) ? $_GET['sechash'] : ''];
	showmessage($return, $returnurl, array_merge($values, (array)$modthread->param('values')), $modthread->param('param'));


}


