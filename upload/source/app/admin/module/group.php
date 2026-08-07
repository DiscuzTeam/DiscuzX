<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
if(empty($_G['setting']['groupstatus'])) {
	cpmsg('group_status_off', 'action=setting&operation=functions', 'error');
}

$file = childfile('group/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}

require_once $file;

function showgroup(&$forum, $type = '', $last = '') {
	global $_G;
	loadcache('grouptype');
	if($last == '') {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" /></td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		$boardattr = $fcolumns = '';
		$fcolumns = ' '.cplang('groups_type_show_rows').'<input type="text" name="forumcolumnsnew['.$forum['fid'].']" value="'.$forum['forumcolumns'].'" class="txt" style="width: 30px;" />';

		if(!$forum['status'] || $forum['password'] || $forum['redirect']) {
			$boardattr = '<div class="boardattr">';
			$boardattr .= $forum['status'] ? '' : cplang('forums_admin_hidden');
			$boardattr .= !$forum['password'] ? '' : ' '.cplang('forums_admin_password');
			$boardattr .= !$forum['redirect'] ? '' : ' '.cplang('forums_admin_url');
			$boardattr .= '</div>';
		}
		$selectgroups = '';
		if($type == 'group') {
			$secondlist = [];
			if(!empty($_G['cache']['grouptype']['first'][$forum['fid']]['secondlist'])) {
				$secondlist = $_G['cache']['grouptype']['first'][$forum['fid']]['secondlist'];
			}
			$secondlist[] = $forum['fid'];
			foreach($secondlist as $sfid) {
				$selectgroups .= "&selectgroupid[]=$sfid";
			}
			$forum['groupnum'] = $_G['cache']['grouptype']['first'][$forum['fid']]['groupnum'];
		} else {
			$selectgroups = '&selectgroupid[]='.$forum['fid'];
		}

		$return .= '<input type="text" name="name['.$forum['fid'].']" value="'.dhtmlspecialchars($forum['name']).'" class="txt" />&nbsp;'.$fcolumns.'</div>'.$boardattr.
			'</td>
			<td>'.$forum['groupnum'].'</td>
			<td><a href="'.ADMINSCRIPT.'?action=group&operation=deletetype&fid='.$forum['fid'].'" title="'.cplang('groups_type_delete').'" class="act">'.cplang('delete').'</a>';
		$return .= '<a href="'.ADMINSCRIPT.'?action=group&operation=manage&submit=yes'.$selectgroups.'" class="act">'.cplang('groups_type_search').'</a><a href="'.ADMINSCRIPT.'?action=group&operation=mergetype&fid='.$forum['fid'].'" class="act">'.cplang('group_mergetype').'</a>';
		$return .= '</td></tr>';
	} else {
		if($last == 'lastboard') {
			$return = '<tr><td></td><td colspan="3"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.cplang('groups_type_sub_new').'</a></div></td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '<tr><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('groups_type_level_1_add').'</a></div></td></tr>';
		}
	}
	echo $return;
	return $forum['fid'];
}

function searchgroups($submit) {
	global $_G;
	require_once libfile('function/group');
	empty($_GET['selectgroupid']) && $_GET['selectgroupid'] = [];
	$groupselect = get_groupselect(0, $_GET['selectgroupid'], 0);
	$monthselect = $dayselect = $birthmonth = $birthday = '';
	for($m = 1; $m <= 12; $m++) {
		$m = sprintf('%02d', $m);
		$monthselect .= "<option value=\"$m\" ".($birthmonth == $m ? 'selected' : '').">$m</option>\n";
	}
	for($d = 1; $d <= 31; $d++) {
		$d = sprintf('%02d', $d);
		$dayselect .= "<option value=\"$d\" ".($birthday == $d ? 'selected' : '').">$d</option>\n";
	}

	/*search={"nav_group_manage":"action=group&operation=manage"}*/
	showtagheader('div', 'searchgroups', !$submit);
	echo '<script src="'.STATICURL.'js/calendar.js" type="text/javascript"></script>';
	showformheader('group&operation=manage');
	showtableheader();
	showsetting('groups_manage_name', 'srchname', $srchname, 'text');
	showsetting('groups_manage_id', 'srchfid', $srchfid, 'text');
	showsetting('groups_editgroup_category', '', '', '<select name="selectgroupid[]" multiple="multiple" size="10"><option value="all"'.(in_array('all', $_GET['selectgroupid']) ? ' selected' : '').'>'.cplang('unlimited').'</option>'.$groupselect.'</select>');
	showsetting('groups_manage_membercount', ['memberlower', 'memberhigher'], [$_GET['memberlower'], $_GET['memberhigher']], 'range');
	showsetting('groups_manage_threadcount', ['threadshigher', 'threadslower'], [$threadshigher, $threadslower], 'range');
	showsetting('groups_manage_replycount', ['postshigher', 'postslower'], [$postshigher, $postslower], 'range');
	showsetting('groups_manage_createtime', ['datelineafter', 'datelinebefore'], [$datelineafter, $datelinebefore], 'daterange');
	showsetting('groups_manage_updatetime', ['lastupdateafter', 'lastupdatebefore'], [$lastupdateafter, $lastupdatebefore], 'daterange');
	showsetting('groups_manage_founder', 'srchfounder', $srchfounder, 'text');
	showsetting('groups_manage_founder_uid', 'srchfounderid', $srchfounderid, 'text');

	showtagfooter('tbody');
	showsubmit('submit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
	/*search*/
}

function countgroups() {
	global $_G;
	$_G['setting']['group_perpage'] = 100;
	$page = $_GET['page'] ? $_GET['page'] : 1;
	$start_limit = ($page - 1) * $_G['setting']['group_perpage'];
	$dateoffset = date('Z') - ($_G['setting']['timeoffset'] * 3600);
	$username = trim($username);

	$conditions = 'f.type=\'sub\' AND f.status=\'3\'';
	if($_GET['srchname'] != '') {
		$srchname = explode(',', addslashes($_GET['srchname']));
		foreach($srchname as $u) {
			$srchnameary[] = " f.name LIKE '%".str_replace(['%', '*', '_'], ['\%', '%', '\_'], $u)."%'";
		}
		$srchnameary = is_array($srchnameary) ? $srchnameary : [$srchnameary];
		$conditions .= ' AND ('.implode(' OR ', $srchnameary).')';
	}
	$conditions .= intval($_GET['srchfid']) ? " AND f.fid='".intval($_GET['srchfid'])."'" : '';
	$conditions .= !empty($_GET['selectgroupid']) && is_array($_GET['selectgroupid']) && !in_array('all', $_GET['selectgroupid']) != '' ? " AND f.fup IN ('".implode('\',\'', dintval($_GET['selectgroupid'], true))."')" : '';

	$conditions .= $_GET['postshigher'] != '' ? " AND f.posts>'".intval($_GET['postshigher'])."'" : '';
	$conditions .= $_GET['postslower'] != '' ? " AND f.posts<'".intval($_GET['postslower'])."'" : '';

	$conditions .= $_GET['threadshigher'] != '' ? " AND f.threads>'".intval($_GET['threadshigher'])."'" : '';
	$conditions .= $_GET['threadslower'] != '' ? " AND f.threads<'".intval($_GET['threadslower'])."'" : '';

	$conditions .= $_GET['memberhigher'] != '' ? " AND ff.membernum<'".intval($_GET['memberhigher'])."'" : '';
	$conditions .= $_GET['memberlower'] != '' ? " AND ff.membernum>'".intval($_GET['memberlower'])."'" : '';

	$conditions .= $_GET['datelinebefore'] != '' ? " AND ff.dateline<'".strtotime($_GET['datelinebefore'])."'" : '';
	$conditions .= $_GET['datelineafter'] != '' ? " AND ff.dateline>'".strtotime($_GET['datelineafter'])."'" : '';

	$conditions .= $_GET['lastupbefore'] != '' ? " AND ff.lastupdate<'".strtotime($_GET['lastupbefore'])."'" : '';
	$conditions .= $_GET['lastupafter'] != '' ? " AND ff.lastupdate>'".strtotime($_GET['lastupafter'])."'" : '';

	if($_GET['srchfounder'] != '') {
		$srchfounder = explode(',', addslashes($_GET['srchfounder']));
		foreach($srchfounder as $fu) {
			$srchfnameary[] = " ff.foundername LIKE '".str_replace(['%', '*', '_'], ['\%', '%', '\_'], $fu)."'";
		}
		$conditions .= ' AND ('.implode(' OR ', $srchfnameary).')';
	}

	$conditions .= intval($_GET['srchfounderid']) ? " AND ff.founderuid='".intval($_GET['srchfounderid'])."'" : '';


	if(!$conditions && !$uidarray && $operation == 'clean') {
		cpmsg('groups_search_invalid', '', 'error');
	}

	$urladd = '&srchname='.rawurlencode($_GET['srchname']).'&srchfid='.intval($_GET['srchfid']).'&postshigher='.rawurlencode($_GET['postshigher']).'&postslower='.rawurlencode($_GET['postslower']).'&threadshigher='.rawurlencode($_GET['threadshigher']).'&threadslower='.rawurlencode($_GET['threadslower']).'&memberhigher='.rawurlencode($_GET['memberhigher']).'&memberlower='.rawurlencode($_GET['memberlower']).'&datelinebefore='.rawurlencode($_GET['datelinebefore']).'&datelineafter='.rawurlencode($_GET['datelineafter']).'&lastupbefore='.rawurlencode($_GET['lastupbefore']).'&lastupafter='.rawurlencode($_GET['lastupafter']).'&srchfounderid='.rawurlencode($_GET['srchfounderid']);

	$groupnum = table_forum_forum::t()->fetch_all_for_search($conditions, -1);
	return [$page, $start_limit, $groupnum, $conditions, $urladd];
}

function delete_groupimg($fidarray) {
	global $_G;
	if(!empty($fidarray)) {
		$query = table_forum_forumfield::t()->fetch_all($fidarray);
		$imgdir = $_G['setting']['attachdir'].'/group/';
		foreach($query as $group) {
			@unlink($imgdir.$group['icon']);
			@unlink($imgdir.$group['banner']);
		}
	}
}

function array_flip_keys($arr) {
	$arr2 = [];
	$arr = is_array($arr) ? $arr : [];
	$arrkeys = is_array($arr) ? array_keys($arr) : [];
	$first = current(array_slice($arr, 0, 1));
	if($first) {
		foreach($first as $k => $v) {
			foreach($arrkeys as $key) {
				$arr2[$k][$key] = $arr[$key][$k];
			}
		}
	}
	return $arr2;
}

function cacherecommend($fidstr, $return = true) {
	require_once libfile('function/group');
	$group_recommend = [];
	$recommend_num = 8;
	$recommends = $fidstr ? explode(',', $fidstr) : [];
	if($recommends) {
		$query = table_forum_forum::t()->fetch_all_info_by_fids($recommends, 3);
		foreach($query as $val) {
			$row = [];
			if($val['type'] == 'sub') {
				$row = ['fid' => $val['fid'], 'name' => $val['name'], 'description' => $val['description'], 'icon' => $val['icon']];
				$row['icon'] = get_groupimg($row['icon'], 'icon');
				$temp[$row['fid']] = $row;
			}
		}
		foreach($recommends as $key) {
			if(!empty($temp[$key])) {
				$group_recommend[$key] = $temp[$key];
			}
		}
	}
	if(count($group_recommend) < $recommend_num) {
		$query = table_forum_forum::t()->fetch_all_default_recommend($recommend_num);
		foreach($query as $row) {
			$row['icon'] = get_groupimg($row['icon'], 'icon');
			if(count($group_recommend) == $recommend_num) {
				break;
			} elseif(empty($group_recommend[$row['fid']])) {
				$group_recommend[$row['fid']] = $row;
			}
		}
	}
	if($return) {
		return $group_recommend;
	} else {
		table_common_setting::t()->update_batch(['group_recommend' => $group_recommend]);
	}
}

