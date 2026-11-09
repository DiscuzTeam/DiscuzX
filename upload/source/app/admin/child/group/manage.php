<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!$_GET['mtype']) {
	if(!submitcheck('submit', 1)) {

		shownav('group', 'nav_group_manage');
		showsubmenu('nav_group_manage');
		searchgroups($_GET['submit']);

	} else {
		list($page, $start_limit, $groupnum, $conditions, $urladd) = countgroups();
		$multipage = multi($groupnum, $_G['setting']['group_perpage'], $page, ADMINSCRIPT.'?action=group&operation=manage&submit=yes'.$urladd);
		$query = table_forum_forum::t()->fetch_all_for_search($conditions, $start_limit, $_G['setting']['group_perpage']);
		foreach($query as $group) {
			$groups .= showtablerow('', ['class="td25"', '', ''], [
				"<input type=\"checkbox\" name=\"fidarray[]\" value=\"{$group['fid']}\" class=\"checkbox\">",
				"<span class=\"lightfont right\">(fid:{$group['fid']})</span><a href=\"forum.php?mod=forumdisplay&fid={$group['fid']}\" target=\"_blank\">{$group['name']}</a>",
				$group['posts'],
				$group['threads'],
				$group['membernum'],
				"<a href=\"home.php?mod=space&uid={$group['founderuid']}\" target=\"_blank\">{$group['foundername']}</a>",
				"<a href=\"".ADMINSCRIPT."?action=group&operation=editgroup&fid={$group['fid']}\" class=\"act\">".cplang('detail').'</a>'
			], TRUE);
		}

		shownav('group', 'nav_group_manage');
		showsubmenu('nav_group_manage');
		showformheader('group&operation=manage&mtype=managetype');
		showtableheader(cplang('groups_search_result', ['groupnum' => $groupnum]).' <a href="javascript:history.go(-1);" class="act lightlink normal">'.cplang('research').'</a>');
		showsubtitle(['', 'groups_manage_name', 'groups_manage_postcount', 'groups_manage_threadcount', 'groups_manage_membercount', 'groups_manage_founder', '']);
		echo $groups;
		showtablerow('', ['class="td25"'], ['<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'fidarray\')" /><label for="chkall">'.cplang('select_all').'</label>']);
		showtablefooter();
		showtableheader('operation', 'notop');
		showtablerow('', ['class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'], [
			'<input class="radio" type="radio" name="optype" value="delete" >',
			cplang('founder_perm_group_deletegroup'), cplang('founder_perm_group_deletegroupcomments')]);
		require_once libfile('function/group');
		$groupselect = get_groupselect(0, $group['fup'], 0);
		showtablerow('', ['class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'], [
			'<input class="radio" type="radio" name="optype" value="changetype" >',
			cplang('group_changetype'),
			'<select name="newtypeid"><option value="">'.cplang('group_mergetype_selecttype').'</option>'.$groupselect.'</select>']);
		showtablerow('', ['class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'], [
			'<input class="radio" type="radio" name="optype" value="mergegroup" >',
			cplang('group_mergegroup'),
			'<input type="text" name="targetgroup" class="text" value="">&nbsp;&nbsp;'.cplang('groups_mergegroup_id')
		]);
		showsubmit('submit', 'submit', '', '', $multipage);
		showtablefooter();
		showformfooter();

	}
} elseif($_GET['mtype'] == 'managetype') {
	$fidarray = $_GET['fidarray'];
	$optype = $_GET['optype'];
	$newtypeid = intval($_GET['newtypeid']);
	$targetgroup = intval($_GET['targetgroup']);
	if(submitcheck('confirmed', 2)) {
		$fidarray = explode(',', $fidarray);
		$recommend = $_G['setting']['group_recommend'] ? array_keys(dunserialize($_G['setting']['group_recommend'])) : [];
		$fidstr = $_G['setting']['group_recommend'] ? implode(',', $recommend) : '';
		$updaterecommend = false;
		foreach($fidarray as $fid) {
			if(in_array($fid, $recommend)) {
				$updaterecommend = true;
				break;
			}
		}
		if($optype == 'delete') {
			delete_groupimg($fidarray);
			require_once libfile('function/post');
			$tids = $nums = [];
			$pp = 100;
			$start = intval($_GET['start']);
			$query = table_forum_forum::t()->fetch_all_info_by_fids($fidarray);
			foreach($query as $fup) {
				$nums[$fup['fup']]++;
			}
			foreach($nums as $fup => $num) {
				empty($start) && table_forum_forumfield::t()->update_groupnum($fup, -$num);
			}
			foreach(table_forum_thread::t()->fetch_all_by_fid($fidarray, $start, $pp) as $thread) {
				$tids[] = $thread['tid'];
			}
			require_once libfile('function/delete');
			if($tids) {
				deletepost($tids, 'tid');
				deletethread($tids);
				cpmsg('group_thread_removing', 'action=group&operation=manage&mtype=managetype&optype=delete&submit=yes&confirmed=yes&formhash='.FORMHASH.'&fidarray='.$_GET['fidarray'].'&start='.($start + $pp));
			}
			loadcache('posttable_info');
			if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
				foreach($_G['cache']['posttable_info'] as $key => $value) {
					table_forum_post::t()->delete_by_fid($key, $fidarray, true);
				}
			}
			loadcache('threadtableids');
			$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : ['0'];
			foreach($threadtableids as $tableid) {
				table_forum_thread::t()->delete_by_fid($fidarray, true, $tableid);
			}
			table_forum_forumrecommend::t()->delete_by_fid($fidarray);
			table_forum_forumrecommend::t()->delete_by_fid($fidarray);
			table_forum_forum::t()->delete_by_fid($fidarray);
			table_home_favorite::t()->delete_by_id_idtype($fidarray, 'gid');
			table_forum_groupuser::t()->delete_by_fid($fidarray);
			table_forum_groupcreditslog::t()->delete_by_fid($fidarray);
			table_forum_groupfield::t()->delete($fidarray);


			require_once libfile('function/delete');
			deletedomain($fidarray, 'group');
			if($updaterecommend) {
				cacherecommend($fidstr, false);
			}
			updatecache(['setting', 'grouptype']);
			cpmsg('group_delete_succeed', 'action=group&operation=manage', 'succeed');
		} elseif($optype == 'changetype') {
			$fups = [];
			$query = table_forum_forum::t()->fetch_all_info_by_fids($fidarray);
			foreach($query as $fup) {
				$fups[$fup['fup']]++;
			}
			table_forum_forum::t()->update($fidarray, ['fup' => $newtypeid]);
			table_forum_forumfield::t()->update_groupnum($newtypeid, count($fidarray));
			foreach($fups as $fup => $num) {
				table_forum_forumfield::t()->update_groupnum($fup, -$num);
			}
			updatecache('grouptype');
			cpmsg('group_changetype_succeed', 'action=group&operation=manage', 'succeed');

		} elseif($optype == 'mergegroup') {
			$start = intval($_GET['start']) ? $_GET['start'] : 0;
			$threadtables = ['0'];
			foreach(table_forum_forum_threadtable::t()->fetch_all_by_fid($targetgroup) as $data) {
				$threadtables[] = $data['threadtableid'];
			}

			if($fidarray[$start]) {
				$sourcefid = $fidarray[$start];
				if(empty($start)) {
					$nums = [];
					$query = table_forum_forum::t()->fetch_all_info_by_fids($fidarray);
					foreach($query as $fup) {
						$nums[$fup['fup']]++;
					}
					foreach($nums as $fup => $num) {
						table_forum_forumfield::t()->update_groupnum($fup, -$num);
					}
				}
				foreach($threadtables as $tableid) {
					table_forum_thread::t()->update_by_fid($sourcefid, ['fid' => $targetgroup], $tableid);
				}
				loadcache('posttableids');
				$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : ['0'];
				foreach($posttableids as $id) {
					table_forum_post::t()->update_fid_by_fid($id, $sourcefid, $targetgroup);
				}

				$targetusers = $newgroupusers = [];
				$query = table_forum_groupuser::t()->fetch_all_by_fid($targetgroup, -1);
				foreach($query as $row) {
					$targetusers[$row['uid']] = $row['uid'];
				}
				$adduser = 0;
				$query = table_forum_groupuser::t()->fetch_all_by_fid($sourcefid, -1);
				foreach($query as $row) {
					if(empty($targetusers[$row['uid']])) {
						$newgroupusers[$row['uid']] = daddslashes($row['username']);
						$adduser++;
					}
				}
				if($adduser) {
					foreach($newgroupusers as $newuid => $newusername) {
						table_forum_groupuser::t()->insert($targetgroup, $newuid, $newusername, 4, TIMESTAMP);
					}
					table_forum_forumfield::t()->update_membernum($targetgroup, $adduser);
				}
				table_forum_groupuser::t()->delete_by_fid($sourcefid);
				table_forum_groupcreditslog::t()->delete_by_fid($sourcefid);
				table_forum_groupfield::t()->delete($sourcefid);
				$start++;
				cpmsg('group_merge_continue', 'action=group&operation=manage&mtype=managetype&optype='.$optype.'&submit=yes&confirmed=yes&formhash='.FORMHASH.'&targetgroup='.$targetgroup.'&fidarray='.$_GET['fidarray'].'&start='.$start, '', ['m' => $start, 'n' => count($fidarray) - $start]);
			}
			$threads = $posts = 0;
			$archive = 0;
			foreach($threadtables as $tableid) {
				$data = table_forum_thread::t()->count_posts_by_fid($targetgroup, $tableid);
				$threads += $data['threads'];
				$posts += $data['posts'];
				if($data['threads'] > 0 && $tableid != 0) {
					$archive = 1;
				}
			}
			table_forum_forum::t()->update($targetgroup, ['archive' => $archive]);
			table_forum_forum::t()->update_forum_counter($targetgroup, $threads, $posts);

			delete_groupimg($fidarray);
			table_forum_forum::t()->delete_by_fid($fidarray);
			table_home_favorite::t()->delete_by_id_idtype($fidarray, 'gid');
			require_once libfile('function/delete');
			deletedomain($fidarray, 'group');
			if($updaterecommend) {
				cacherecommend($fidstr, false);
			}
			updatecache(['setting', 'grouptype']);
			cpmsg('group_mergegroup_succeed', 'action=group&operation=manage', 'succeed');
		}

	}
	if(empty($optype) || !in_array($optype, ['delete', 'changetype', 'mergegroup'])) {
		cpmsg('group_optype_no_choice', '', 'error');
	}
	if($optype == 'changetype' && empty($newtypeid)) {
		cpmsg('group_newtypeid_no_choice', '', 'error');
	}
	if($optype == 'mergegroup' && empty($targetgroup)) {
		cpmsg('group_targetgroup_no_choice', '', 'error');
	}
	if($fidarray) {
		$targetid = 0;
		$targetname = '';
		if($optype == 'changetype' && $newtypeid) {
			$targetid = $newtypeid;
		} elseif($optype == 'mergegroup' && $targetgroup) {
			if(in_array($targetgroup, $fidarray)) {
				cpmsg('group_targetgroup_repeat', '', 'error');
			}
			$targetid = $targetgroup;
		}
		if($targetid) {
			$targetname = table_forum_forum::t()->fetch($targetid);
			$targetname = $targetname['name'];
			if(empty($targetname)) {
				cpmsg('group_targetid_error');
			}
		}
		if(is_array($fidarray)) {
			$fidarray = implode(',', $fidarray);
		}
		cpmsg('group_'.$optype.'_confirm', 'action=group&operation=manage&mtype=managetype&optype='.$optype.'&submit=yes', 'form', ['targetname' => $targetname], '<input type="hidden" name="fidarray" value="'.$fidarray.'"><input type="hidden" name="newtypeid" value="'.$newtypeid.'"><input type="hidden" name="targetgroup" value="'.$targetgroup.'">');
	} else {
		cpmsg('group_group_no_choice', '', 'error');
	}
}
	