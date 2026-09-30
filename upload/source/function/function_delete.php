<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');

function deletemember($uids, $delpost = true) {
	global $_G;
	if(!$uids) {
		return;
	}
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletemember']) {
		$_G['deleteposuids'] = &$uids;
		$hookparam = func_get_args();
		hookscript('deletemember', 'global', 'funcs', ['param' => $hookparam, 'step' => 'check'], 'deletemember');
	}
	if($delpost) {
		deleteattach($uids, 'uid');
		deletepost($uids, 'authorid');
	}

	$arruids = $uids;
	$uids = dimplode($uids);
	$numdeleted = count($arruids);
	foreach(['common_member_field_forum', 'common_member_field_home', 'common_member_count',
		        'common_member_profile', 'common_member_status',] as $table) {
		C::t($table)->delete($arruids, true, 1);
	}

	foreach(['common_member_verify', 'common_member_validate', 'common_member_magic'] as $table) {
		C::t($table)->delete($arruids, true);
	}

	table_forum_access::t()->delete_by_uid($arruids);
	table_common_member_verify_info::t()->delete_by_uid($arruids);
	table_common_member_action_log::t()->delete_by_uid($arruids);
	table_forum_moderator::t()->delete_by_uid($arruids);
	table_forum_post_location::t()->delete_by_uid($arruids);
	$doids = [];
	$query = table_home_doing::t()->fetch_all_by_uid_doid($arruids);
	foreach($query as $value) {
		$doids[$value['doid']] = $value['doid'];
	}

	table_home_docomment::t()->delete_by_doid_uid($doids, $arruids);
	table_common_domain::t()->delete_by_id_idtype($arruids, 'home');
	table_home_feed::t()->delete_by_uid($arruids);
	table_home_notification::t()->delete_by_uid($arruids);
	table_home_poke::t()->delete_by_uid_or_fromuid($uids);
	table_home_comment::t()->delete_by_uid($arruids);
	table_home_visitor::t()->delete_by_uid_or_vuid($uids);
	table_home_friend::t()->delete_by_uid_fuid($arruids);
	table_home_friend_request::t()->delete_by_uid_or_fuid($arruids);
	table_common_invite::t()->delete_by_uid_or_fuid($arruids);
	table_common_moderate::t()->delete_moderate($arruids, 'uid_cid');
	table_common_member_forum_buylog::t()->delete_by_uid($arruids);
	table_forum_threadhidelog::t()->delete_by_uid($arruids);
	table_common_member_crime::t()->delete_by_uid($arruids);
	table_home_follow::t()->delete_by_uid($arruids);
	table_home_follow::t()->delete_by_followuid($arruids);
	table_home_follow_feed::t()->delete_by_uid($arruids);
	table_common_member_account::t()->delete_by_uid($arruids);
	table_common_member_username_history::t()->delete_by_uid($arruids);

	foreach(table_forum_collectionfollow::t()->fetch_all_by_uid($arruids) as $follow) {
		table_forum_collection::t()->update_by_ctid($follow['ctid'], 0, -1);
	}

	foreach(table_forum_collectioncomment::t()->fetch_all_by_uid($arruids) as $comment) {
		table_forum_collection::t()->update_by_ctid($comment['ctid'], 0, 0, -1);
	}

	$query = table_home_pic::t()->fetch_all_by_uid($uids);
	foreach($query as $value) {
		$pics[] = $value;
	}
	deletepicfiles($pics);

	include_once libfile('function/home');
	$query = table_home_album::t()->fetch_all_by_uid($arruids);
	foreach($query as $value) {
		pic_delete($value['pic'], 'album', 0, ($value['picflag'] == 2 ? 1 : 0));
	}

	$query = table_home_doing_attachment::t()->fetch_all_by_id(0, 'uid', $arruids);
	foreach ($query as $value) {
		pic_delete($value['pic'], 'doing', 0, $value['remote']);
	}

	table_common_mailcron::t()->delete_by_touid($arruids);

	foreach (
		[
			'home_doing',
			'home_doing_attachment',
			'home_share',
			'home_album',
			'common_credit_rule_log',
			'common_credit_rule_log_field',
			'home_pic',
			'home_blog',
			'home_blogfield',
			'home_class',
			'home_clickuser',
			'home_show',
			'forum_collectioncomment',
			'forum_collectionfollow',
			'forum_collectionteamworker'
		] as $table
	) {
		C::t($table)->delete_by_uid($arruids);
	}
	table_common_member::t()->delete($arruids, 1, 1);

	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletemember']) {
		hookscript('deletemember', 'global', 'funcs', ['param' => $hookparam, 'step' => 'delete'], 'deletemember');
	}
	return $numdeleted;
}

function deletepost($ids, $idtype = 'pid', $credit = false, $posttableid = false, $recycle = false) {
	global $_G;
	$recycle = $recycle && $idtype == 'pid';
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletepost']) {
		$_G['deletepostids'] = &$ids;
		$hookparam = func_get_args();
		hookscript('deletepost', 'global', 'funcs', ['param' => $hookparam, 'step' => 'check'], 'deletepost');
	}
	if(!$ids || !in_array($idtype, ['authorid', 'tid', 'pid'])) {
		return 0;
	}

	loadcache('posttableids');
	$posttableids = !empty($_G['cache']['posttableids']) ? ($posttableid !== false && in_array($posttableid, $_G['cache']['posttableids']) ? [$posttableid] : $_G['cache']['posttableids']) : ['0'];

	$count = count($ids);
	$idsstr = dimplode($ids);

	if($credit) {
		$replycredit_list = $tuidarray = $ruidarray = $_G['deleteauthorids'] = [];
		foreach($posttableids as $id) {
			$postlist = [];
			if($idtype == 'pid') {
				$postlist = table_forum_post::t()->fetch_all_post($id, $ids, false);
			} elseif($idtype == 'tid') {
				$postlist = table_forum_post::t()->fetch_all_by_tid($id, $ids, false);
			} elseif($idtype == 'authorid') {
				$postlist = table_forum_post::t()->fetch_all_by_authorid($id, $ids, false);
			}
			foreach($postlist as $post) {
				if($post['invisible'] != -1 && $post['invisible'] != -5) {
					if($post['first']) {
						$tuidarray[$post['fid']][] = $post['authorid'];
					} else {
						$ruidarray[$post['fid']][] = $post['authorid'];
						if($post['authorid'] > 0 && $post['replycredit'] > 0) {
							$replycredit_list[$post['authorid']][$post['tid']] += $post['replycredit'];
						}
					}
					$tids[$post['tid']] = $post['tid'];
					$_G['deleteauthorids'][$post['authorid']] = $post['authorid'];
				}
			}
			unset($postlist);
		}

		if($tuidarray || $ruidarray) {
			require_once libfile('function/post');
		}
		if($tuidarray) {
			foreach($tuidarray as $fid => $tuids) {
				updatepostcredits('-', $tuids, 'post', $fid);
			}
		}
		if($ruidarray) {
			foreach($ruidarray as $fid => $ruids) {
				updatepostcredits('-', $ruids, 'reply', $fid);
			}
		}
	}

	foreach($posttableids as $id) {
		if($recycle) {
			table_forum_post::t()->update_post($id, $ids, ['invisible' => -5]);
		} else {
			if($idtype == 'pid') {
				table_forum_post::t()->delete_post($id, $ids);
				table_forum_postcomment::t()->delete_by_pid($ids);
				table_forum_postcomment::t()->delete_by_rpid($ids);
			} elseif($idtype == 'tid') {
				table_forum_post::t()->delete_by_tid($id, $ids);
				table_forum_postcomment::t()->delete_by_tid($ids);
			} elseif($idtype == 'authorid') {
				table_forum_post::t()->delete_by_authorid($id, $ids);
				table_forum_postcomment::t()->delete_by_authorid($ids);
			}
			table_forum_trade::t()->delete_by_id_idtype($ids, ($idtype == 'authorid' ? 'sellerid' : $idtype));
			table_home_feed::t()->delete_by_id_idtype($ids, ($idtype == 'authorid' ? 'uid' : $idtype));
		}
	}
	if(!$recycle && $idtype != 'authorid') {
		if($idtype == 'pid') {
			table_forum_poststick::t()->delete_by_pid($ids);
		} elseif($idtype == 'tid') {
			table_forum_poststick::t()->delete_by_tid($ids);
		}

	}
	if($idtype == 'pid') {
		table_forum_postcomment::t()->delete_by_rpid($ids);
		table_common_moderate::t()->delete_moderate($ids, 'pid');
		table_forum_post_location::t()->delete($ids);
		table_forum_filter_post::t()->delete_by_pid($ids);
		table_forum_hotreply_number::t()->delete_by_pid($ids);
		table_forum_hotreply_member::t()->delete_by_pid($ids);
	} elseif($idtype == 'tid') {
		table_forum_post_location::t()->delete_by_tid($ids);
		table_forum_filter_post::t()->delete_by_tid($ids);
		table_forum_hotreply_number::t()->delete_by_tid($ids);
		table_forum_hotreply_member::t()->delete_by_tid($ids);
		table_forum_sofa::t()->delete($ids);
	} elseif($idtype == 'authorid') {
		table_forum_post_location::t()->delete_by_uid($ids);
	}
	if($replycredit_list) {
		foreach(table_forum_replycredit::t()->fetch_all($tids) as $rule) {
			$rule['extcreditstype'] = $rule['extcreditstype'] ? $rule['extcreditstype'] : $_G['setting']['creditstransextra'][10];
			$replycredity_rule[$rule['tid']] = $rule;
		}
		foreach($replycredit_list as $uid => $tid_credit) {
			foreach($tid_credit as $tid => $credit) {
				$uid_credit[$replycredity_rule[$tid]['extcreditstype']] -= $credit;
			}
			updatemembercount($uid, $uid_credit, true);
		}
	}
	if(!$recycle) {
		deleteattach($ids, $idtype);
	}
	if($tids) {
		foreach($tids as $tid) {
			updatethreadcount($tid, 1);
		}
	}
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletepost']) {
		hookscript('deletepost', 'global', 'funcs', ['param' => $hookparam, 'step' => 'delete'], 'deletepost');
	}
	return $count;
}

function deletethreadcover($tids) {
	global $_G;
	loadcache(['threadtableids', 'posttableids']);
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : [0];
	$deletecover = [];
	foreach($threadtableids as $tableid) {
		foreach(table_forum_thread::t()->fetch_all_by_tid($tids, 0, 0, $tableid) as $row) {
			if($row['cover']) {
				$deletecover[$row['tid']] = $row['cover'];
			}
		}
	}
	if($deletecover) {
		foreach($deletecover as $tid => $cover) {
			$filename = getthreadcover($tid, 0, 1);
			$remote = $cover < 0 ? 1 : 0;
			dunlink(['attachment' => $filename, 'remote' => $remote, 'thumb' => 0]);
		}
	}
}

function deletethread($tids, $membercount = false, $credit = false, $ponly = false) {
	global $_G;
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletethread']) {
		$_G['deletethreadtids'] = &$tids;
		$hookparam = func_get_args();
		hookscript('deletethread', 'global', 'funcs', ['param' => $hookparam, 'step' => 'check'], 'deletethread');
	}
	if(!$tids) {
		return 0;
	}

	$count = count($tids);
	$arrtids = $tids;
	$tids = dimplode($tids);

	loadcache(['threadtableids', 'posttableids']);
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : [];
	$posttableids = !empty($_G['cache']['posttableids']) ? $_G['cache']['posttableids'] : ['0'];
	if(!in_array(0, $threadtableids)) {
		$threadtableids = array_merge([0], $threadtableids);
	}

	table_common_moderate::t()->delete_moderate($arrtids, 'tid');
	table_forum_threadclosed::t()->delete($arrtids);
	table_forum_newthread::t()->delete_by_tids($arrtids);

	$cachefids = $atids = $fids = $postids = $threadtables = $_G['deleteauthorids'] = [];
	foreach($threadtableids as $tableid) {
		foreach(table_forum_thread::t()->fetch_all_by_tid($arrtids, 0, 0, $tableid) as $row) {
			$atids[] = $row['tid'];
			$row['posttableid'] = !empty($row['posttableid']) && in_array($row['posttableid'], $posttableids) ? $row['posttableid'] : '0';
			$postids[$row['posttableid']][$row['tid']] = $row['tid'];
			if($tableid) {
				$fids[$row['fid']][] = $tableid;
			}
			$cachefids[$row['fid']] = $row['fid'];
			$_G['deleteauthorids'][$row['authorid']] = $row['authorid'];
		}
		if(!$tableid && !$ponly) {
			$threadtables[] = $tableid;
		}
	}

	if($credit || $membercount) {
		$losslessdel = $_G['setting']['losslessdel'] > 0 ? TIMESTAMP - $_G['setting']['losslessdel'] * 86400 : 0;

		$postlist = $uidarray = $tuidarray = $ruidarray = [];
		foreach($postids as $posttableid => $posttabletids) {
			foreach(table_forum_post::t()->fetch_all_by_tid($posttableid, $posttabletids, false) as $post) {
				if($post['invisible'] != -1 && $post['invisible'] != -5) {
					$postlist[] = $post;
				}
			}
		}
		foreach(table_forum_replycredit::t()->fetch_all($arrtids) as $rule) {
			$rule['extcreditstype'] = $rule['extcreditstype'] ? $rule['extcreditstype'] : $_G['setting']['creditstransextra'][10];
			$replycredit_rule[$rule['tid']] = $rule;
		}

		foreach($postlist as $post) {
			if($post['dateline'] < $losslessdel) {
				if($membercount) {
					if($post['first']) {
						updatemembercount($post['authorid'], ['threads' => -1, 'post' => -1], false);
					} else {
						updatemembercount($post['authorid'], ['posts' => -1], false);
					}
				}
			} else {
				if($credit) {
					if($post['first']) {
						$tuidarray[$post['fid']][] = $post['authorid'];
					} else {
						$ruidarray[$post['fid']][] = $post['authorid'];
					}
				}
			}
			if($credit || $membercount) {
				if($post['authorid'] > 0 && $post['replycredit'] > 0) {
					if($replycredit_rule[$post['tid']]['extcreditstype']) {
						updatemembercount($post['authorid'], [$replycredit_rule[$post['tid']]['extcreditstype'] => (int)('-'.$post['replycredit'])]);
					}
				}
			}
		}

		if($credit) {
			if($tuidarray || $ruidarray) {
				require_once libfile('function/post');
			}
			if($tuidarray) {
				foreach($tuidarray as $fid => $tuids) {
					updatepostcredits('-', $tuids, 'post', $fid);
				}
			}
			if($ruidarray) {
				foreach($ruidarray as $fid => $ruids) {
					updatepostcredits('-', $ruids, 'reply', $fid);
				}
			}
			$auidarray = $attachtables = [];
			foreach($atids as $tid) {
				$attachtables[getattachtableid($tid)][] = $tid;
			}
			foreach($attachtables as $attachtable => $attachtids) {
				foreach(table_forum_attachment_n::t()->fetch_all_by_id($attachtable, 'tid', $attachtids) as $attach) {
					if($attach['dateline'] > $losslessdel) {
						$auidarray[$attach['uid']] = !empty($auidarray[$attach['uid']]) ? $auidarray[$attach['uid']] + 1 : 1;
					}
				}
			}
			if($auidarray) {
				$postattachcredits = !empty($_G['forum']['postattachcredits']) ? $_G['forum']['postattachcredits'] : $_G['setting']['creditspolicy']['postattach'];
				updateattachcredits('-', $auidarray, $postattachcredits);
			}
		}
	}

	$relatecollection = table_forum_collectionthread::t()->fetch_all_by_tids($arrtids);
	if(count($relatecollection) > 0) {
		$collectionids = [];
		foreach($relatecollection as $collection) {
			$collectionids[] = $collection['ctid'];
		}
		$collectioninfo = table_forum_collection::t()->fetch_all($collectionids);
		foreach($relatecollection as $collection) {
			$decthread = table_forum_collectionthread::t()->delete_by_ctid_tid($collection['ctid'], $arrtids);
			$lastpost = null;
			if(in_array($collectioninfo[$collection['ctid']]['lastpost'], $arrtids) && ($collectioninfo[$collection['ctid']]['threadnum'] - $decthread) > 0) {
				$collection_thread = table_forum_collectionthread::t()->fetch_by_ctid_dateline($collection['ctid']);
				if($collection_thread) {
					$thread = table_forum_thread::t()->fetch_thread($collection_thread['tid']);
					$lastpost = [
						'lastpost' => $thread['tid'],
						'lastsubject' => $thread['subject'],
						'lastposttime' => $thread['dateline'],
						'lastposter' => $thread['authorid']
					];
				}
			}
			table_forum_collection::t()->update_by_ctid($collection['ctid'], -$decthread, 0, 0, 0, 0, 0, $lastpost);
		}
		table_forum_collectionrelated::t()->delete($arrtids);
	}
	if($cachefids) {
		table_forum_thread::t()->clear_cache($cachefids, 'forumdisplay_');
	}
	if($ponly) {
		if($_G['setting']['plugins']['func'][HOOKTYPE]['deletethread']) {
			hookscript('deletethread', 'global', 'funcs', ['param' => $hookparam, 'step' => 'delete'], 'deletethread');
		}
		table_forum_thread::t()->update($arrtids, ['displayorder' => -1, 'digest' => 0, 'moderated' => 1]);
		foreach($postids as $posttableid => $oneposttids) {
			table_forum_post::t()->update_by_tid($posttableid, $oneposttids, ['invisible' => '-1']);
		}
		return $count;
	}

	table_forum_replycredit::t()->delete($arrtids);
	table_forum_post_location::t()->delete_by_tid($arrtids);
	table_common_credit_log::t()->delete_by_operation_relatedid(['RCT', 'RCA', 'RCB'], $arrtids);
	table_forum_threadhidelog::t()->delete_by_tid($arrtids);
	deletethreadcover($arrtids);
	foreach($threadtables as $tableid) {
		table_forum_thread::t()->delete_by_tid($arrtids, false, $tableid);
	}

	if($atids) {
		foreach($postids as $posttableid => $oneposttids) {
			deletepost($oneposttids, 'tid', false, $posttableid);
		}
		deleteattach($atids, 'tid');
	}

	if($fids) {
		loadcache('forums');
		foreach($fids as $fid => $tableids) {
			if(empty($_G['cache']['forums'][$fid]['archive'])) {
				continue;
			}
			foreach(table_forum_thread::t()->count_posts_by_fid($fid) as $row) {
				table_forum_forum_threadtable::t()->insert([
					'fid' => $fid,
					'threadtableid' => $tableid,
					'threads' => $row['threads'],
					'posts' => $row['posts']
				], false, true);
			}
		}
	}

	foreach(
		[
			'forum_forumrecommend',
			'forum_polloption',
			'forum_poll',
			'forum_polloption_image',
			'forum_activity',
			'forum_activityapply',
			'forum_debate',
			'forum_debatepost',
			'forum_threadmod',
			'forum_relatedthread',
			'forum_pollvoter',
			'forum_threadimage',
			'forum_threadpreview'
		] as $table
	) {
		C::t($table)->delete_by_tid($arrtids);
	}
	table_forum_typeoptionvar::t()->delete_by_tid($arrtids);
	table_forum_poststick::t()->delete_by_tid($arrtids);
	table_forum_filter_post::t()->delete_by_tid($arrtids);
	table_forum_hotreply_member::t()->delete_by_tid($arrtids);
	table_forum_hotreply_number::t()->delete_by_tid($arrtids);
	table_home_feed::t()->delete_by_id_idtype($arrtids, 'tid');
	table_common_tagitem::t()->delete_tagitem(0, $arrtids, 'tid');
	table_forum_threadrush::t()->delete($arrtids);
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletethread']) {
		hookscript('deletethread', 'global', 'funcs', ['param' => $hookparam, 'step' => 'delete'], 'deletethread');
	}
	return $count;
}

function deleteattach($ids, $idtype = 'aid') {
	global $_G;
	if(!$ids || !in_array($idtype, ['authorid', 'uid', 'tid', 'pid'])) {
		return;
	}
	$idtype = $idtype == 'authorid' ? 'uid' : $idtype;

	$pics = $attachtables = [];

	if($idtype == 'tid') {
		$pollImags = table_forum_polloption_image::t()->fetch_all_by_tid($ids);
		foreach($pollImags as $image) {
			dunlink($image);
		}
	}
	foreach(table_forum_attachment::t()->fetch_all_by_id($idtype, $ids) as $attach) {
		$attachtables[$attach['tableid']][] = $attach['aid'];
	}

	foreach($attachtables as $attachtable => $aids) {
		if($attachtable == 127) {
			continue;
		}
		$attachs = table_forum_attachment_n::t()->fetch_all_attachment($attachtable, $aids);
		foreach($attachs as $attach) {
			if($attach['picid']) {
				$pics[] = $attach['picid'];
			}
			dunlink($attach);
		}
		table_forum_attachment_exif::t()->delete($aids);
		table_forum_attachment_n::t()->delete_attachment($attachtable, $aids);
	}
	table_forum_attachment::t()->delete_by_id($idtype, $ids);
	if($pics) {
		$albumids = [];
		table_home_pic::t()->delete($pics);
		$query = table_home_pic::t()->fetch_all($pics);
		foreach($query as $album) {
			if(!in_array($album['albumid'], $albumids)) {
				table_home_album::t()->update($album['albumid'], ['picnum' => table_home_pic::t()->check_albumpic($album['albumid'])]);
				$albumids[] = $album['albumid'];
			}
		}
	}
}

function deletecomments($cids) {
	global $_G;

	$blognums = $newcids = $dels = $counts = [];
	$allowmanage = checkperm('managecomment');

	$query = table_home_comment::t()->fetch_all($cids);
	$deltypes = [];
	foreach($query as $value) {
		if($allowmanage || $value['authorid'] == $_G['uid'] || $value['uid'] == $_G['uid']) {
			$dels[] = $value;
			$newcids[] = $value['cid'];
			$deltypes[] = $value['idtype'].'_cid';
			if($value['authorid'] != $_G['uid'] && $value['uid'] != $_G['uid']) {
				$counts[$value['authorid']]['coef'] -= 1;
			}
			if($value['idtype'] == 'blogid') {
				$blognums[$value['id']]++;
			}
		}
	}

	if(empty($dels)) return [];

	table_home_comment::t()->delete_comment($newcids);
	for($i = 0; $i < count($newcids); $i++) {
		table_common_moderate::t()->delete_moderate($newcids[$i], $deltypes[$i]);
	}

	if($counts) {
		foreach($counts as $uid => $setarr) {
			batchupdatecredit('comment', $uid, [], $setarr['coef']);
		}
	}
	if($blognums) {
		$nums = renum($blognums);
		foreach($nums[0] as $num) {
			table_home_blog::t()->increase($nums[1][$num], 0, ['replynum' => -$num]);
		}
	}
	return $dels;
}

function deleteblogs($blogids, $force = false) {
	global $_G;

	$blogs = $newblogids = $counts = [];
	$allowmanage = checkperm('manageblog');

	$query = table_home_blog::t()->fetch_all_blog($blogids);
	foreach($query as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$blogs[] = $value;
			$newblogids[] = $value['blogid'];

			if($value['status'] == 0) {
				if($value['uid'] != $_G['uid']) {
					$counts[$value['uid']]['coef'] -= 1;
				}
				$counts[$value['uid']]['blogs'] -= 1;
			}
		}
	}
	if(empty($blogs)) return [];

	table_common_moderate::t()->delete_moderate($newblogids, 'blogid');
	table_common_moderate::t()->delete_moderate($newblogids, 'blogid_cid');

	if(getglobal('setting/blogrecyclebin') && !$force) {
		table_home_blog::t()->update($newblogids, ['status' => -1]);
		return $blogs;
	}
	table_home_blog::t()->delete($newblogids);
	table_home_blogfield::t()->delete($newblogids);
	table_home_comment::t()->delete_comment('', $newblogids, 'blogid');
	table_home_feed::t()->delete_by_id_idtype($newblogids, 'blogid');
	table_home_clickuser::t()->delete_by_id_idtype($newblogids, 'blogid');

	if($counts) {
		foreach($counts as $uid => $setarr) {
			batchupdatecredit('publishblog', $uid, ['blogs' => $setarr['blogs']], $setarr['coef']);
		}
	}

	table_common_tagitem::t()->delete_tagitem(0, $newblogids, 'blogid');

	return $blogs;
}

function deletefeeds($feedids) {
	global $_G;

	$allowmanage = checkperm('managefeed');

	$feeds = $newfeedids = [];
	$query = table_home_feed::t()->fetch_all($feedids);
	foreach($query as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$newfeedids[] = $value['feedid'];
			$feeds[] = $value;
		}
	}

	if(empty($newfeedids)) return [];

	table_home_feed::t()->delete_feed($newfeedids);

	return $feeds;
}

function deleteshares($sids) {
	global $_G;

	$allowmanage = checkperm('manageshare');

	$shares = $newsids = $counts = [];
	foreach(table_home_share::t()->fetch_all($sids) as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$shares[] = $value;
			$newsids[] = $value['sid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['sharings'] -= 1;
		}
	}
	if(empty($shares)) return [];

	table_home_share::t()->delete($newsids);
	table_home_comment::t()->delete_comment('', $newsids, 'sid');
	table_home_feed::t()->delete_by_id_idtype($newsids, 'sid');
	table_common_moderate::t()->delete_moderate($newsids, 'sid');
	table_common_moderate::t()->delete_moderate($newsids, 'sid_cid');

	if($counts) {
		foreach($counts as $uid => $setarr) {
			batchupdatecredit('createshare', $uid, ['sharings' => $setarr['sharings']], $setarr['coef']);
		}
	}

	return $shares;
}

function deletedoings($ids) {
	global $_G;

	$allowmanage = checkperm('managedoing');

	$doings = $newdoids = $counts = $attachments = $tagids = [];
	$query = table_home_doing::t()->fetch_all($ids);
	foreach($query as $value) {
		$value['fields'] = json_decode($value['fields'], true);
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$doings[] = $value;
			$newdoids[] = $value['doid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['doings'] -= 1;
		}
		if(!empty($value['fields']['tags']) && is_array($value['fields']['tags'])) {
			foreach($value['fields']['tags'] as $tagid => $tag) {
				$tagids[] = $tagid;
			}
		}
	}

	if(empty($doings)) return [];

	// 获取并处理附件
	include_once libfile('function/home');
	$all_attachments = table_home_doing_attachment::t()->fetch_all_by_id(0, 'doid', $newdoids);

	// 分组附件并计算大小
	$attach_by_doid = [];
	foreach($all_attachments as $attach) {
		$attach_by_doid[$attach['doid']][] = $attach;
		$attachments[] = $attach;
	}

	// 删除物理文件（包含图片与视频等全部附件；视频封面「附件名.thumb.jpg」一并清理）
	foreach($attachments as $attach) {
		pic_delete($attach['attachment'], 'doing', empty($attach['isimage']) ? 1 : 0, $attach['remote']);
	}

	// 删除附件记录
	table_home_doing_attachment::t()->delete_by_id('doid', $newdoids);
	table_home_doing::t()->delete($newdoids);
	table_home_docomment::t()->delete_by_doid_uid($newdoids);
	table_home_feed::t()->delete_by_id_idtype($newdoids, 'doid');
	table_common_moderate::t()->delete_moderate($newdoids, 'doid');

	// 删除点赞记录
	foreach($newdoids as $doid) {
		table_home_doing_recomend_log::t()->delete_by_doid($doid);
	}

	if($counts) {
		foreach($counts as $uid => $setarr) {
			if($uid) {
				batchupdatecredit('doing', $uid, ['doings' => $setarr['doings']], $setarr['coef']);
				$lastdoing = table_home_doing::t()->fetch_all_by_uid_doid($uid, '', 'dateline', 0, 1, true, true);
				$setarr = ['recentnote' => $lastdoing[0]['message'], 'spacenote' => $lastdoing[0]['message']];
				table_common_member_field_home::t()->update($uid, $setarr);
			}
		}
	}
	table_common_tagitem::t()->delete_tagitem(0, $newdoids, 'doid');
	if($tagids) {
		foreach($tagids as $tagid) {
			table_common_tag::t()->increase($tagid, ['related_count' => -1]);
		}
	}

	return $doings;
}

function deletespace($uid) {
	global $_G;

	$allowmanage = checkperm('managedelspace');

	if($allowmanage) {
		table_common_member::t()->update($uid, ['status' => 1]);
		return true;
	} else {
		return false;
	}
}

function deletepics($picids) {
	global $_G;

	$albumids = $sizes = $pics = $newids = [];
	$allowmanage = checkperm('managealbum');

	$haveforumpic = false;
	$query = table_home_pic::t()->fetch_all($picids);
	foreach($query as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$pics[] = $value;
			$newids[] = $value['picid'];
			$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
			$albumids[$value['albumid']] = $value['albumid'];
			if(!$haveforumpic && $value['remote'] > 1) {
				$haveforumpic = true;
			}
		}
	}
	if(empty($pics)) return [];

	table_home_pic::t()->delete($newids);
	if($haveforumpic) {
		for($i = 0; $i < 10; $i++) {
			table_forum_attachment_n::t()->reset_picid($i, $newids);
		}
	}

	table_home_comment::t()->delete_comment('', $newids, 'picid');
	table_home_feed::t()->delete_by_id_idtype($newids, 'picid');
	table_home_clickuser::t()->delete_by_id_idtype($newids, 'picid');
	table_common_moderate::t()->delete_moderate($newids, 'picid');
	table_common_moderate::t()->delete_moderate($newids, 'picid_cid');

	if($sizes) {
		foreach($sizes as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			updatemembercount($uid, ['attachsize' => -$attachsize], false);
		}
	}

	require_once libfile('function/spacecp');
	foreach($albumids as $albumid) {
		if($albumid) {
			album_update_pic($albumid);
		}
	}

	deletepicfiles($pics);

	return $pics;
}

function deletepicfiles($pics) {
	global $_G;
	$remotes = [];
	include_once libfile('function/home');
	foreach($pics as $pic) {
		pic_delete($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
	}
}

function deletealbums($albumids) {
	global $_G;

	$sizes = $dels = $newids = $counts = [];
	$allowmanage = checkperm('managealbum');

	$albums = table_home_album::t()->fetch_all_album($albumids);
	foreach($albums as $value) {
		if($value['albumid']) {
			if($allowmanage || $value['uid'] == $_G['uid']) {
				$dels[] = $value;
				$newids[] = $value['albumid'];
				if(!empty($value['pic'])) {
					include_once libfile('function/home');
					pic_delete($value['pic'], 'album', 0, ($value['picflag'] == 2 ? 1 : 0));
				}
			}
			$counts[$value['uid']]['albums'] -= 1;
		}
	}

	if(empty($dels)) return [];

	$pics = $picids = [];
	$query = table_home_pic::t()->fetch_all_by_albumid($newids);
	foreach($query as $value) {
		$pics[] = $value;
		$picids[] = $value['picid'];
		$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
	}

	if($picids) {
		deletepics($picids);
	}
	table_home_album::t()->delete($newids);
	table_home_feed::t()->delete_by_id_idtype($newids, 'albumid');
	if($picids) {
		table_home_clickuser::t()->delete_by_id_idtype($picids, 'picid');
	}

	if($sizes) {
		foreach($sizes as $uid => $value) {
			$attachsize = intval($sizes[$uid]);
			$albumnum = $counts[$uid]['albums'] ? $counts[$uid]['albums'] : 0;
			updatemembercount($uid, ['albums' => $albumnum, 'attachsize' => -$attachsize], false);
		}
	}
	return $dels;
}

function deletetrasharticle($aids) {
	global $_G;

	require_once libfile('function/home');
	$articles = $trashid = $pushs = $dels = [];
	foreach(table_portal_article_trash::t()->fetch_all($aids) as $value) {
		$dels[$value['aid']] = $value['aid'];
		$article = dunserialize($value['content']);
		$articles[$article['aid']] = $article;
		if(!empty($article['idtype'])) $pushs[$article['idtype']][] = $article['id'];
		if($article['pic']) {
			pic_delete($article['pic'], 'portal', $article['thumb'], $article['remote']);
		}
		if($article['htmlmade'] && $article['htmldir'] && $article['htmlname']) {
			deletehtml(DISCUZ_ROOT_STATIC.'/'.$article['htmldir'].$article['htmlname'], $article['contents']);
		}
	}

	if($dels) {
		table_portal_article_trash::t()->delete($dels, 'UNBUFFERED');
		deletearticlepush($pushs);
		deletearticlerelated($dels);
	}
	table_common_tagitem::t()->delete_tagitem(0, $dels, 'articleid');
	return $articles;
}

function deletearticle($aids, $istrash = true) {
	global $_G;

	if(empty($aids)) return false;
	$trasharr = $article = $bids = $dels = $attachment = $attachaid = $catids = $pushs = [];
	$query = table_portal_article_title::t()->fetch_all($aids);
	foreach($query as $value) {
		$catids[] = intval($value['catid']);
		$dels[$value['aid']] = $value['aid'];
		$article[] = $value;
		if(!empty($value['idtype'])) $pushs[$value['idtype']][] = $value['id'];
	}
	if($dels) {
		foreach($article as $key => $value) {
			if($istrash) {
				$trasharr[] = ['aid' => $value['aid'], 'content' => serialize($value)];
			} else {
				if($value['pic']) {
					pic_delete($value['pic'], 'portal', $value['thumb'], $value['remote']);
				}
				if($value['htmlmade'] && $value['htmldir'] && $value['htmlname']) {
					deletehtml(DISCUZ_ROOT_STATIC.'/'.$value['htmldir'].$value['htmlname'], $value['contents']);
				}
			}
		}
		if($istrash && $trasharr) {
			table_portal_article_trash::t()->insert_batch($trasharr);
		} else {
			deletearticlepush($pushs);
			deletearticlerelated($dels);
		}

		table_portal_article_title::t()->delete($dels);
		table_common_moderate::t()->delete_moderate($dels, 'aid');

		$catids = array_unique($catids);
		if($catids) {
			foreach($catids as $catid) {
				$cnt = table_portal_article_title::t()->fetch_count_for_cat($catid);
				table_portal_category::t()->update($catid, ['articles' => dintval($cnt)]);
			}
		}
	}
	return $article;
}

function deletearticlepush($pushs) {
	if(!empty($pushs) && is_array($pushs)) {
		foreach($pushs as $idtype => $fromids) {
			switch($idtype) {
				case 'blogid':
					if(!empty($fromids)) table_home_blogfield::t()->update($fromids, ['pushedaid' => '0']);
					break;
				case 'tid':
					if(!empty($fromids)) table_forum_thread::t()->update($fromids, ['pushedaid' => '0']);
					break;
			}
		}
	}
}

function deletearticlerelated($dels) {

	table_portal_article_count::t()->delete($dels);
	table_portal_article_content::t()->delete_by_aid($dels);

	if($attachment = table_portal_attachment::t()->fetch_all_by_aid($dels)) {
		require_once libfile('function/home');
		foreach($attachment as $value) {
			pic_delete($value['attachment'], 'portal', $value['thumb'], $value['remote']);
		}
		table_portal_attachment::t()->delete(array_keys($attachment));
	}

	table_portal_comment::t()->delete_by_id_idtype($dels, 'aid');
	table_common_moderate::t()->delete_moderate($dels, 'aid_cid');

	table_portal_article_related::t()->delete_by_aid_raid($dels);

}

function deleteportaltopic($dels) {
	if(empty($dels)) return false;
	$targettplname = [];
	foreach((array)$dels as $key => $value) {
		$targettplname[] = 'portal/portal_topic_content_'.$value;
	}
	table_common_diy_data::t()->delete($targettplname, null);

	require_once libfile('class/blockpermission');
	$tplpermission = &template_permission::instance();
	$templates = [];
	$tplpermission->delete_allperm_by_tplname($targettplname);

	deletedomain($dels, 'topic');
	table_common_template_block::t()->delete_by_targettplname($targettplname);

	require_once libfile('function/home');

	$picids = [];
	foreach(table_portal_topic::t()->fetch_all($dels) as $value) {
		if($value['picflag'] != '0') pic_delete(str_replace('portal/', '', $value['cover']), 'portal', 0, $value['picflag'] == '2' ? '1' : '0');
	}

	$picids = [];
	foreach(table_portal_topic_pic::t()->fetch_all($dels) as $value) {
		$picids[] = $value['picid'];
		pic_delete($value['filepath'], 'portal', $value['thumb'], $value['remote']);
	}
	if(!empty($picids)) {
		table_portal_topic_pic::t()->delete($picids, true);
	}


	table_portal_topic::t()->delete($dels);
	table_portal_comment::t()->delete_by_id_idtype($dels, 'topicid');
	table_common_moderate::t()->delete_moderate($dels, 'topicid_cid');

	include_once libfile('function/block');
	block_clear();

	include_once libfile('function/cache');
	updatecache('diytemplatename');
}

function deletedomain($ids, $idtype) {
	if($ids && $idtype) {
		table_common_domain::t()->delete_by_id_idtype($ids, $idtype);
	}
}

function deletecollection($ctid) {
	$tids = [];
	$threadlist = table_forum_collectionthread::t()->fetch_all_by_ctid($ctid);
	$tids = array_keys($threadlist);

	deleterelatedtid($tids, $ctid);

	$collectionteamworker = table_forum_collectionteamworker::t()->fetch_all_by_ctid($ctid);
	foreach($collectionteamworker as $worker) {
		notification_add($worker['uid'], 'system', 'collection_removed', ['ctid' => $worker['ctid'], 'collectionname' => $worker['name']], 1);
	}

	table_forum_collectionthread::t()->delete_by_ctid($ctid);
	table_forum_collectionfollow::t()->delete_by_ctid($ctid);
	table_forum_collectioncomment::t()->delete_by_ctid($ctid);
	table_forum_collectionteamworker::t()->delete_by_ctid($ctid);
	table_forum_collectioninvite::t()->delete_by_ctid($ctid);
	table_forum_collection::t()->delete($ctid, true);
}

function deleterelatedtid($tids, $ctid) {
	$loadreleated = table_forum_collectionrelated::t()->fetch_all($tids, true);
	foreach($loadreleated as $loadexist) {
		if($loadexist['tid']) {
			$collectionlist = explode("\t", $loadexist['collection']);
			if(count($collectionlist) > 0) {
				foreach($collectionlist as $collectionkey => $collectionvalue) {
					if($collectionvalue == $ctid) {
						unset($collectionlist[$collectionkey]);
						break;
					}
				}
			}
			$newcollection = implode("\t", $collectionlist);
			if(trim($newcollection) == '') {
				table_forum_collectionrelated::t()->delete($loadexist['tid']);
				table_forum_thread::t()->update_status_by_tid($loadexist['tid'], '1111111011111111', '&');
			} else {
				table_forum_collectionrelated::t()->update_collection_by_ctid_tid($newcollection, $loadexist['tid'], true);
			}
		}
	}
}

function deletehtml($htmlname, $count = 1) {
	global $_G;
	@unlink($htmlname.'.'.$_G['setting']['makehtml']['extendname']);
	if($count > 1) {
		for($i = 2; $i <= $count; $i++) {
			@unlink($htmlname.$i.'.'.$_G['setting']['makehtml']['extendname']);
		}
	}
}

function deletememberpost($uids) {
	global $_G;
	require_once libfile('function/post');
	loadcache('posttableids');

	foreach($uids as $uid) {
		$tidsdelete = [];
		$posttables = empty($_G['cache']['posttableids']) ? [0] : $_G['cache']['posttableids'];
		foreach($posttables as $posttableid) {
			$pidsthread = $pidsdelete = [];
			$postlist = table_forum_post::t()->fetch_all_by_authorid($posttableid, $uid, false);
			if($postlist) {
				foreach($postlist as $post) {
					if($post['first']) {
						$tidsdelete[] = $post['tid'];
					}
					$pidsdelete[] = $post['pid'];
					$pidsthread[$post['pid']] = $post['tid'];
				}
			}
			deletepost($pidsdelete, 'pid', true, $posttableid, true);
		}
		unset($postlist);
		if($tidsdelete) {
			deletethread($tidsdelete, true, true, true);
		}
	}
}

