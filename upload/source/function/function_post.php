<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getattach($pid, $posttime = 0, $aids = '') {
	global $_G;

	require_once libfile('function/attachment');
	$attachs = $imgattachs = [];
	$aids = $aids ? explode('|', $aids) : [];
	if($aids) {
		$aidsnew = [];
		foreach($aids as $aid) {
			if($aid) {
				$aidsnew[] = intval($aid);
			}
		}
		$aids = 'aid IN ('.dimplode($aidsnew).') AND';
	} else {
		$aids = '';
	}
	$sqladd1 = $posttime > 0 ? "AND af.dateline>'$posttime'" : '';
	if(!empty($_G['fid']) && $_G['forum']['attachextensions']) {
		$allowext = str_replace(' ', '', strtolower($_G['forum']['attachextensions']));
		$allowext = explode(',', $allowext);
	} else {
		$allowext = '';
	}
	foreach(table_forum_attachment::t()->fetch_all_unused_attachment($_G['uid'], empty($aidsnew) ? null : $aidsnew, $posttime > 0 ? $posttime : null) as $attach) {
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = fileext($attach['filename']);
		if($attach['isimage'] == 2) {
			$attach['isimage'] = 0;
		}
		if($allowext && !in_array($attach['ext'], $allowext)) {
			continue;
		}
		getattach_row($attach, $attachs, $imgattachs);
	}
	if($pid > 0) {
		$attachmentns = table_forum_attachment_n::t()->fetch_all_by_id('tid:'.$_G['tid'], 'pid', $pid);
		foreach(table_forum_attachment::t()->fetch_all_by_id('pid', $pid, 'aid') as $attach) {
			if(!empty($attachmentns[$attach['aid']])) {
				$attach = array_merge($attach, $attachmentns[$attach['aid']]);
			}
			if($attach['isimage'] == 2) {
				$attach['isimage'] = 0;
			}
			$attach['filenametitle'] = $attach['filename'];
			$attach['ext'] = fileext($attach['filename']);
			if($allowext && !in_array($attach['ext'], $allowext)) {
				continue;
			}
			getattach_row($attach, $attachs, $imgattachs);
		}
	}
	return ['attachs' => $attachs, 'imgattachs' => $imgattachs];
}

function getattach_row($attach, &$attachs, &$imgattachs) {
	global $_G;
	$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
	$attach['attachsize'] = sizecount($attach['filesize']);
	$attach['dateline'] = dgmdate($attach['dateline']);
	$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
	if($attach['isimage'] < 1 || $attach['isimage'] == 2) {
		if($attach['isimage']) {
			$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
			$attach['width'] = $attach['width'] > 300 ? 300 : $attach['width'];
		}
		if($attach['pid']) {
			$attachs['used'][] = $attach;
		} else {
			$attachs['unused'][] = $attach;
		}
	} else {
		$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'/forum';
		$attach['width'] = $attach['width'] > 300 ? 300 : $attach['width'];
		if($attach['pid']) {
			$imgattachs['used'][] = $attach;
		} else {
			$imgattachs['unused'][] = $attach;
		}
	}
}

function parseattachmedia($attach) {
	$attachurl = 'attach://'.$attach['aid'].'.'.$attach['ext'];
	return match (strtolower($attach['ext'])) {
		'mp3', 'm4a', 'wma', 'ra', 'ram', 'wav', 'mid', 'ogg', 'aac', 'flac', 'weba' => '[audio]'.$attachurl.'[/audio]',
		'wmv', 'rm', 'rmvb', 'avi', 'asf', 'asx', 'mpg', 'mpeg', 'mov', 'flv', 'swf', 'mp4', 'm4v', '3gp', 'ogv', 'webm' => '[media='.$attach['ext'].',400,300]'.$attachurl.'[/media]',
		default => null,
	};
}

function ftpupload($aids, $uid = 0) {
	global $_G;
	$uid = $uid ? $uid : $_G['uid'];

	if(!$aids || !$_G['setting']['ftp']['on']) {
		return;
	}
	$attachtables = $pics = [];
	foreach(table_forum_attachment::t()->fetch_all($aids) as $attach) {
		if($uid != $attach['uid'] && !$_G['forum']['ismoderator']) {
			continue;
		}
		$attachtables[$attach['tableid']][] = $attach['aid'];
	}
	foreach($attachtables as $attachtable => $aids) {
		$remoteaids = [];
		foreach(table_forum_attachment_n::t()->fetch_all_attachment($attachtable, $aids, 0) as $attach) {
			if(ftpperm(fileext($attach['filename']), $attach['filesize'])) {
				if($_G['setting']['ftp']['on'] == 2 || ftpcmd('upload', 'forum/'.$attach['attachment']) && (!$attach['thumb'] || ftpcmd('upload', 'forum/'.getimgthumbname($attach['attachment'])))) {
					dunlink($attach);
					$remoteaids[$attach['aid']] = $attach['aid'];
					if($attach['picid']) {
						$pics[] = $attach['picid'];
					}
				}
			}
		}

		if($remoteaids) {
			table_forum_attachment_n::t()->update_attachment($attachtable, $remoteaids, ['remote' => 1]);
		}
	}
	if($pics) {
		table_home_pic::t()->update($pics, ['remote' => 3]);
	}
}

function updateattach($modnewthreads, $tid, $pid, $attachnew, $attachupdate = [], $uid = 0) {
	global $_G;
	$thread = table_forum_thread::t()->fetch_thread($tid);
	$uid = $uid ? $uid : $_G['uid'];
	if($attachnew) {
		$newaids = array_keys($attachnew);
		$newattach = $newattachfile = $albumattach = [];
		foreach(table_forum_attachment_unused::t()->fetch_all($newaids) as $attach) {
			if($attach['uid'] != $uid && !$_G['forum']['ismoderator']) {
				continue;
			}
			$attach['uid'] = $uid;
			$newattach[$attach['aid']] = daddslashes($attach);
			if(abs($attach['isimage']) == 1) {
				$newattachfile[$attach['aid']] = $attach['attachment'];
			}
		}
		if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark']) || !$_G['setting']['thumbdisabledmobile']) {
			require_once libfile('class/image');
			$image = new image;
		}
		if(!empty($_GET['albumaid'])) {
			array_unshift($_GET['albumaid'], '');
			$_GET['albumaid'] = array_unique($_GET['albumaid']);
			unset($_GET['albumaid'][0]);
			foreach($_GET['albumaid'] as $aid) {
				if(isset($newattach[$aid])) {
					$albumattach[$aid] = $newattach[$aid];
				}
			}
			if(!empty($_GET['uploadalbum'])) {
				$_GET['uploadalbum'] = intval($_GET['uploadalbum']);
				$albuminfo = table_home_album::t()->fetch_album($_GET['uploadalbum'], $uid);
				if(empty($albuminfo)) {
					$_GET['uploadalbum'] = 0;
				}
			}
		}
		foreach($attachnew as $aid => $attach) {
			if(!$newattach[$aid]) {
				$attachexist = table_forum_attachment::t()->fetch($aid);
				if(!$attachexist || ($attachexist['uid'] != $uid && !$_G['forum']['ismoderator'])) {
					continue;
				}
			}
			$update = [];
			$update['readperm'] = $_G['group']['allowsetattachperm'] ? (!empty($attach['readperm']) ? $attach['readperm'] : 0) : 0;
			$update['price'] = $_G['group']['maxprice'] ? (intval($attach['price']) <= $_G['group']['maxprice'] ? intval($attach['price']) : $_G['group']['maxprice']) : 0;
			$update['tid'] = $tid;
			$update['pid'] = $pid;
			$update['uid'] = $uid;
			$update['description'] = censor(cutstr(dhtmlspecialchars($attach['description']), 100));
			table_forum_attachment_n::t()->update_attachment('tid:'.$tid, $aid, $update);
			if(!$newattach[$aid]) {
				continue;
			}
			$update = array_merge($update, $newattach[$aid]);
			if(!empty($newattachfile[$aid])) {
				if($_G['setting']['thumbstatus'] && $_G['forum']['disablethumb']) {
					$update['thumb'] = 0;
					@unlink($_G['setting']['attachdir'].'/forum/'.getimgthumbname($newattachfile[$aid]));
					if(!empty($albumattach[$aid])) {
						$albumattach[$aid]['thumb'] = 0;
					}
				} elseif(!$_G['setting']['thumbdisabledmobile']) {
					$_daid = sprintf('%09d', $aid);
					$dir1 = substr($_daid, 0, 3);
					$dir2 = substr($_daid, 3, 2);
					$dir3 = substr($_daid, 5, 2);
					$dw = 320;
					$dh = 320;
					$thumbfile = 'image/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($_daid, -2).'_'.$dw.'_'.$dh.'.jpg';
					$image->Thumb($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid], $thumbfile, $dw, $dh, 'fixwr');
					$dw = 720;
					$dh = 720;
					$thumbfile = 'image/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($_daid, -2).'_'.$dw.'_'.$dh.'.jpg';
					$image->Thumb($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid], $thumbfile, $dw, $dh, 'fixwr');
				}
				$attachfilename_fullpath = $_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid];
				if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark']) && is_file($attachfilename_fullpath) ) {
					$image->Watermark($attachfilename_fullpath, '', 'forum');
					$update['filesize'] = $image->imginfo['size'];
				}
			}
			if(!empty($_GET['albumaid']) && isset($albumattach[$aid])) {
				$newalbum = 0;
				if(!$_GET['uploadalbum']) {
					require_once libfile('function/spacecp');
					$_GET['uploadalbum'] = album_creat(['albumname' => $_GET['newalbum']]);
					$newalbum = 1;
				}
				$picdata = [
					'albumid' => $_GET['uploadalbum'],
					'uid' => $uid,
					'username' => $_G['username'],
					'dateline' => $albumattach[$aid]['dateline'],
					'postip' => $_G['clientip'],
					'filename' => censor($albumattach[$aid]['filename']),
					'title' => censor(cutstr(dhtmlspecialchars($attach['description']), 100)),
					'type' => fileext($albumattach[$aid]['attachment']),
					'size' => $albumattach[$aid]['filesize'],
					'filepath' => $albumattach[$aid]['attachment'],
					'thumb' => $albumattach[$aid]['thumb'],
					'remote' => $albumattach[$aid]['remote'] + 2,
				];

				$update['picid'] = table_home_pic::t()->insert($picdata, 1);

				if($newalbum) {
					require_once libfile('function/home');
					require_once libfile('function/spacecp');
					album_update_pic($_GET['uploadalbum']);
				}
			}
			table_forum_attachment_n::t()->insert('tid:'.$tid, $update, false, true);
			table_forum_attachment::t()->update($aid, ['tid' => $tid, 'pid' => $pid, 'tableid' => getattachtableid($tid)]);
			table_forum_attachment_unused::t()->delete($aid);
		}

		if(!empty($_GET['albumaid'])) {
			$albumdata = [
				'picnum' => table_home_pic::t()->check_albumpic($_GET['uploadalbum']),
				'updatetime' => $_G['timestamp'],
			];
			table_home_album::t()->update($_GET['uploadalbum'], $albumdata);
			require_once libfile('function/home');
			require_once libfile('function/spacecp');
			album_update_pic($_GET['uploadalbum']);
		}
		if($newattach) {
			ftpupload($newaids, $uid);
		}
	}

	if(!$modnewthreads && $newattach && $uid == $_G['uid']) {
		updatecreditbyaction('postattach', $uid, [], '', count($newattach), 1, $_G['fid']);
	}

	if($attachupdate) {
		$attachs = table_forum_attachment_n::t()->fetch_all_by_id('tid:'.$tid, 'aid', array_keys($attachupdate));
		foreach($attachs as $attach) {
			if(array_key_exists($attach['aid'], $attachupdate) && $attachupdate[$attach['aid']]) {
				dunlink($attach);
			}
		}
		$unusedattachs = table_forum_attachment_unused::t()->fetch_all($attachupdate);
		$attachupdate = array_flip($attachupdate);
		$unusedaids = [];
		foreach($unusedattachs as $attach) {
			if($attach['uid'] != $uid && !$_G['forum']['ismoderator']) {
				continue;
			}
			$unusedaids[] = $attach['aid'];
			$update = $attach;
			$update['dateline'] = TIMESTAMP;
			$update['remote'] = 0;
			unset($update['aid']);
			$attachfilename_fullpath = $_G['setting']['attachdir'].'/forum/'.$attach['attachment'];
			if($attach['isimage'] && $_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark']) && is_file($attachfilename_fullpath)) {
				$image->Watermark($attachfilename_fullpath, '', 'forum');
				$update['filesize'] = $image->imginfo['size'];
			}
			table_forum_attachment_n::t()->update('tid:'.$tid, $attachupdate[$attach['aid']], $update);
			@unlink($_G['setting']['attachdir'].'image/'.$attach['aid'].'_100_100.jpg');
			table_forum_attachment_exif::t()->delete($attachupdate[$attach['aid']]);
			table_forum_attachment_exif::t()->update($attach['aid'], ['aid' => $attachupdate[$attach['aid']]]);
			ftpupload([$attachupdate[$attach['aid']]], $uid);
		}
		if($unusedaids) {
			table_forum_attachment_unused::t()->delete($unusedaids);
		}
	}

	$attachcount = table_forum_attachment_n::t()->count_by_id('tid:'.$tid, $pid ? 'pid' : 'tid', $pid ? $pid : $tid);
	$attachment = 0;
	if($attachcount) {
		if(table_forum_attachment_n::t()->count_image_by_id('tid:'.$tid, $pid ? 'pid' : 'tid', $pid ? $pid : $tid)) {
			$attachment = 2;
		} else {
			$attachment = 1;
		}
	} else {
		$attachment = 0;
	}
	table_forum_thread::t()->update($tid, ['attachment' => $attachment]);
	table_forum_post::t()->update_post('tid:'.$tid, $pid, ['attachment' => $attachment], true);

	if(!$attachment) {
		table_forum_threadimage::t()->delete_by_tid($tid);
	}
	$_G['forum_attachexist'] = $attachment;
}

function checkflood() {
	global $_G;
	if(!$_G['group']['disablepostctrl'] && $_G['uid']) {
		if($_G['setting']['floodctrl'] && discuz_process::islocked('post_lock_'.$_G['uid'], $_G['setting']['floodctrl'])) {
			return true;
		}
		return false;


	}
	return FALSE;
}

function checkmaxperhour($type) {
	global $_G;
	$morenumperhour = false;
	if(!$_G['group']['disablepostctrl'] && $_G['uid']) {
		if($_G['group']['max'.($type == 'pid' ? 'posts' : 'threads').'perhour']) {
			$usernum = table_common_member_action_log::t()->count_per_hour($_G['uid'], $type);
			$var = $type === 'tid' ? 'maxthreadsperhour' : 'maxpostsperhour';
			$isflood = $usernum && ($usernum >= $_G['group'][$var]);
			if($isflood) {
				$morenumperhour = true;
			}
		}
	}
	return $morenumperhour;
}

function checkpost($subject, $message, $special = 0, $isJson = false) {
	global $_G;
	if(dstrlen($subject) > 255) {
		return 'post_subject_toolong';
	}
	if(!$_G['group']['disablepostctrl'] && !$special && !$isJson) {
		if($_G['setting']['maxpostsize'] && strlen($message) > $_G['setting']['maxpostsize']) {
			return 'post_message_toolong';
		} elseif($_G['setting']['minpostsize']) {
			$minpostsize = !defined('IN_MOBILE') || !constant('IN_MOBILE') || !$_G['setting']['minpostsize_mobile'] ? $_G['setting']['minpostsize'] : $_G['setting']['minpostsize_mobile'];
			if(strlen(preg_replace('/\[quote\].+?\[\/quote\]/is', '', $message)) < $minpostsize || strlen(preg_replace('/\[postbg\].+?\[\/postbg\]/is', '', $message)) < $minpostsize) {
				return 'post_message_tooshort';
			}
		}
		if($_G['setting']['maxsubjectsize'] && dstrlen($subject) > $_G['setting']['maxsubjectsize']) {
			return 'post_subject_toolong';
		} elseif(dstrlen($subject) && $_G['setting']['minsubjectsize'] && dstrlen($subject) < $_G['setting']['minsubjectsize']) {
			return 'post_subject_tooshort';
		}
	}
	return FALSE;
}

function checkbbcodes($message, $bbcodeoff) {
	return !$bbcodeoff && (!strpos($message, '[/') && !strpos($message, '[hr]')) ? -1 : $bbcodeoff;
}

function checksmilies($message, $smileyoff) {
	global $_G;

	if($smileyoff) {
		return 1;
	} else {
		if(!empty($_G['cache']['smileycodes']) && is_array($_G['cache']['smileycodes'])) {
			foreach($_G['cache']['smileycodes'] as $id => $code) {
				if(str_contains($message, $code)) {
					return 0;
				}
			}
		}
		return -1;
	}
}

function updatepostcredits($operator, $uidarray, $action, $fid = 0) {
	global $_G;
	$val = $operator == '+' ? 1 : -1;
	$extsql = [];
	if(empty($uidarray)) {
		return false;
	}
	$uidarray = (array)$uidarray;
	$uidarr = [];
	foreach($uidarray as $uid) {
		$uidarr[$uid] = !isset($uidarr[$uid]) ? 1 : $uidarr[$uid] + 1;
	}
	foreach($uidarr as $uid => $coef) {
		$opnum = $val * $coef;
		if($action == 'reply') {
			$extsql = ['posts' => $opnum];
		} elseif($action == 'post') {
			$extsql = ['threads' => $opnum, 'posts' => $opnum];
		}
		if($uid == $_G['uid']) {
			updatecreditbyaction($action, $uid, $extsql, '', $opnum, 1, $fid);
		} elseif(empty($uid)) {
			continue;
		} else {
			batchupdatecredit($action, $uid, $extsql, $opnum, $fid);
		}
	}
	if($operator == '+' && ($action == 'reply' || $action == 'post')) {
		table_common_member_status::t()->update(array_keys($uidarr), ['lastpost' => TIMESTAMP], 'UNBUFFERED');
	}
}

function updateattachcredits($operator, $uidarray) {
	global $_G;
	foreach($uidarray as $uid => $attachs) {
		updatecreditbyaction('postattach', $uid, [], '', $operator == '-' ? -$attachs : $attachs, 1, $_G['fid']);
	}
}

function updateforumcount($fid) {

	$fidposts = table_forum_thread::t()->count_posts_by_fid($fid);
	extract($fidposts);

	$thread = table_forum_thread::t()->fetch_by_fid_displayorder($fid, 0, '=');

	$thread['subject'] = addslashes($thread['subject']);
	$thread['lastposter'] = $thread['author'] ? addslashes($thread['lastposter']) : lang('forum/misc', 'anonymous');
	$tid = $thread['closed'] > 1 ? $thread['closed'] : $thread['tid'];
	$setarr = ['posts' => $posts, 'threads' => $threads, 'lastpost' => "$tid\t{$thread['subject']}\t{$thread['lastpost']}\t{$thread['lastposter']}"];
	table_forum_forum::t()->update($fid, $setarr);
}

function updatethreadcount($tid, $updateattach = 0) {
	$replycount = table_forum_post::t()->count_visiblepost_by_tid($tid) - 1;
	$lastpost = table_forum_post::t()->fetch_visiblepost_by_tid('tid:'.$tid, $tid, 0, 1);

	$lastpost['author'] = $lastpost['anonymous'] ? lang('forum/misc', 'anonymous') : addslashes($lastpost['author']);
	$lastpost['dateline'] = !empty($lastpost['dateline']) ? $lastpost['dateline'] : TIMESTAMP;

	$data = ['replies' => $replycount, 'lastposter' => $lastpost['author'], 'lastpost' => $lastpost['dateline']];
	if($updateattach) {
		$attach = table_forum_post::t()->fetch_attachment_by_tid($tid);
		$data['attachment'] = $attach ? $attach : 0;
	}
	table_forum_thread::t()->update($tid, $data);
}

function updatemodlog($tids, $action, $expiration = 0, $iscron = 0, $reason = '', $stamp = 0) {
	global $_G;
	if(is_array($tids)) {
		$tids = implode(',', $tids);
	}
	$uid = empty($iscron) ? $_G['uid'] : 0;
	$username = empty($iscron) ? $_G['member']['username'] : 0;
	$expiration = empty($expiration) ? 0 : intval($expiration);

	$data = $comma = '';
	$stampadd = $stampaddvalue = '';
	if($stamp) {
		$stampadd = ', stamp';
		$stampaddvalue = ", '$stamp'";
	}
	foreach(explode(',', str_replace(['\'', ' '], ['', ''], $tids)) as $tid) {
		if($tid) {

			$data = [
				'tid' => $tid,
				'uid' => $uid,
				'username' => $username,
				'dateline' => $_G['timestamp'],
				'action' => $action,
				'expiration' => $expiration,
				'status' => 1,
				'reason' => $reason
			];
			if($stamp) {
				$data['stamp'] = $stamp;
			}
			table_forum_threadmod::t()->insert($data);
		}
	}


}

function isopera() {
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(str_contains($useragent, 'opera')) {
		preg_match('/opera(\/| )([0-9\.]+)/', $useragent, $regs);
		return $regs[2];
	}
	return FALSE;
}

function deletethreadcaches($tids) {
	global $_G;
	if(!$_G['setting']['cachethreadon']) {
		return FALSE;
	}
	require_once libfile('function/forumlist');
	if(!empty($tids)) {
		foreach(explode(',', $tids) as $tid) {
			$fileinfo = getcacheinfo($tid);
			@unlink($fileinfo['filename']);
		}
	}
	return TRUE;
}


function disuploadedfile($file) {
	return function_exists('is_uploaded_file') && (is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file)));
}

function postfeed($feed) {
	global $_G;
	if($feed) {
		require_once libfile('function/feed');
		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', $feed['images'], $feed['image_links'], '', '', '', 0, $feed['id'], $feed['idtype']);
	}
}

function messagesafeclear($message) {
	if(str_contains($message, '[/password]')) {
		$message = '';
	}
	if(str_contains($message, '[/postbg]')) {
		$message = preg_replace("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/is", '', $message);
	}
	if(str_contains($message, '[/begin]')) {
		$message = preg_replace("/\[begin(=\s*([^\[\<\r\n]*?)\s*,(\d*),(\d*),(\d*),(\d*))?\]\s*([^\[\<\r\n]+?)\s*\[\/begin\]/is", '', $message);
	}
	if(str_contains($message, '[page]')) {
		$message = preg_replace('/\s?\[page\]\s?/is', '', $message);
	}
	if(str_contains($message, '[/index]')) {
		$message = preg_replace('/\s?\[index\](.+?)\[\/index\]\s?/is', '', $message);
	}
	if(str_contains($message, '[/begin]')) {
		$message = preg_replace("/\[begin(=\s*([^\[\<\r\n]*?)\s*,(\d*),(\d*),(\d*),(\d*))?\]\s*([^\[\<\r\n]+?)\s*\[\/begin\]/is", '', $message);
	}
	if(str_contains($message, '[/groupid]')) {
		$message = preg_replace('/\[groupid=\d+\].*\[\/groupid\]/i', '', $message);
	}
	$language = lang('forum/misc');
	$message = preg_replace([$language['post_edithtml_regexp'], $language['post_editnobbcode_regexp'], $language['post_edit_regexp']], '', $message);
	return $message;
}

function messagecutstr($message, $length = 0, $dot = ' ...') {
	global $_G;
	$str = messagesafeclear($message);
	$sppos = strpos($str, chr(0).chr(0).chr(0));
	if($sppos !== false) {
		$str = substr($str, 0, $sppos);
	}
	$language = lang('forum/misc');
	loadcache(['bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes', 'domainwhitelist']);
	$bbcodes = 'b|i|u|p|color|backcolor|size|font|align|list|indent|float|md';
	$bbcodesclear = 'email|code|free|table|tr|td|img|swf|flash|attach|attachimg|media|audio|groupid|payto'.(!empty($_G['cache']['bbcodes_display'][$_G['groupid']]) ? '|'.implode('|', array_keys($_G['cache']['bbcodes_display'][$_G['groupid']])) : '');
	$str = strip_tags(preg_replace([
		'/\[hide=?\d*\](.*?)\[\/hide\]/is',
		'/\[quote](.*?)\[\/quote]/si',
		$language['post_edit_regexp'],
		'/\[url=?.*?\](.+?)\[\/url\]/si',
		"/\[($bbcodesclear)(=.*?)?\].+?\[\/\\1\]/si",
		"/\[($bbcodes)(=.*?)?\]/i",
		"/\[\/($bbcodes)\]/i",
		"/\\\\u/i"
	], [
		$language['post_hidden'],
		'',
		'',
		'\\1',
		'',
		'',
		'',
		'%u'
	], $str));
	$str = preg_replace($_G['cache']['smilies']['searcharray'], '', $str);
	if($_G['setting']['plugins']['func'][HOOKTYPE]['discuzcode']) {
		$_G['discuzcodemessage'] = &$str;
		$param = func_get_args();
		hookscript('discuzcode', 'global', 'funcs', ['param' => $param, 'caller' => 'messagecutstr'], 'discuzcode');
	}
	if($length) {
		$str = cutstr($str, $length, $dot);
	}
	return trim($str);
}

function threadmessagecutstr($thread, $str, $length = 0, $dot = ' ...') {
	global $_G;
	if(!empty($thread)) {
		if(!empty($thread['readperm']) && $thread['readperm'] > 0) {
			$str = '';
		} elseif(!empty($thread['price']) && $thread['price'] > 0) {
			preg_match_all('/\[free\](.+?)\[\/free\]/is', $str, $matches);
			$str = '';
			if(!empty($matches[1])) {
				foreach($matches[1] as $match) {
					$str .= $match.' ';
				}
			} else {
				$language = lang('forum/misc');
				$str = $language['post_sold'];
			}
		}
	}
	return messagecutstr($str, $length, $dot);
}

function setthreadcover($pid, $tid = 0, $aid = 0, $countimg = 0, $imgurl = '') {
	global $_G;
	$cover = 0;
	if(empty($_G['uid']) || !intval($_G['setting']['forumpicstyle']['thumbwidth'])) {
		return false;
	}

	if(($pid || $aid) && empty($countimg)) {
		if(empty($imgurl)) {
			if($aid) {
				$attachtable = 'aid:'.$aid;
				$attach = table_forum_attachment_n::t()->fetch_attachment('aid:'.$aid, $aid);
			} else {
				$attachtable = 'pid:'.$pid;
				$attach = table_forum_attachment_n::t()->fetch_max_image('pid:'.$pid, 'pid', $pid);
			}
			if(!$attach || !in_array($attach['isimage'], [1, -1]) && !$attach['thumb']) {
				return false;
			}
			if(empty($_G['forum']['ismoderator']) && $_G['uid'] != $attach['uid']) {
				return false;
			}
			$pid = empty($pid) ? $attach['pid'] : $pid;
			$tid = empty($tid) ? $attach['tid'] : $tid;
			$picsource = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
			if(!$attach['isimage'] && $attach['thumb']) {
				$picsource = getimgthumbname($picsource);
			}
		} else {
			return true;
		}

		$basedir = !$_G['setting']['attachdir'] ? (DISCUZ_DATA.'./attachment/') : $_G['setting']['attachdir'];
		$coverdir = 'threadcover/'.substr(md5($tid), 0, 2).'/'.substr(md5($tid), 2, 2).'/';
		dmkdir($basedir.'./forum/'.$coverdir);

		require_once libfile('class/image');
		$image = new image();
		if($image->Thumb($picsource, 'forum/'.$coverdir.$tid.'.jpg', $_G['setting']['forumpicstyle']['thumbwidth'], $_G['setting']['forumpicstyle']['thumbheight'], 2)) {
			$remote = '';
			if(ftpperm('jpg', filesize($_G['setting']['attachdir'].'forum/'.$coverdir.$tid.'.jpg'))) {
				if(ftpcmd('upload', 'forum/'.$coverdir.$tid.'.jpg')) {
					@unlink($_G['setting']['attachdir'].'forum/'.$coverdir.$tid.'.jpg');
					$remote = '-';
				}
			}
			$cover = $remote.'1';
		} else {
			return false;
		}
	}
	if($countimg) {
		if(empty($cover)) {
			$thread = table_forum_thread::t()->fetch_thread($tid);
			$oldcover = $thread['cover'];

			$cover = table_forum_attachment_n::t()->count_image_by_id('tid:'.$tid, 'pid', $pid);
			if($cover) {
				$cover = $oldcover < 0 ? '-'.$cover : $cover;
			}
		}
	}
	if($cover) {
		table_forum_thread::t()->update($tid, ['cover' => $cover]);
		return true;
	}
}

