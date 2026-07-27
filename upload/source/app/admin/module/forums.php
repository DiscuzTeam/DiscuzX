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

$operation = empty($operation) ? 'admin' : $operation;
$fid = intval(getgpc('fid'));

$file = childfile('forums/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}
require_once $file;

function showforum(&$forum, $type = '', $last = '', $toggle = false, $more = false) {
	global $_G;

	static $threadtypes = null;

	if($threadtypes == null) {
		require_once libfile('function/post');
		$query = table_forum_threadtype::t()->fetch_all_for_order();
		foreach($query as $_v) {
			$threadtypes[$_v['typeid']] = messagecutstr($_v['name'], 0, '');
		}
	}

	if($last == '') {

		$navs = [];
		foreach(table_common_nav::t()->fetch_all_by_navtype_type(0, 5) as $nav) {
			$navs[] = $nav['identifier'];
		}
		$return = '<tr class="hover">'.
			'<td class="td30"'.($type == 'group' ? ' onclick="toggle_group(\'group_'.$forum['fid'].'\', $(\'a_group_'.$forum['fid'].'\'))"' : '').'>'.($type == 'group' ? '<a href="javascript:;" id="a_group_'.$forum['fid'].'">'.($toggle ? '[+]' : '[-]').'</a>' : '').'</td>
			<td class="td25">'.(!$more ? '<input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" />' : '<input type="text" class="txt" disabled />').'</td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
			$_G['fg'] = !empty($_G['fg']) ? intval($_G['fg']) : 0;
			$_G['fg']++;
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		if($more) {
			$more_text = cplang('forums_admin_view_all_forum').' ('.$more.')';
			if($type == 'sub') {
				$more_text = cplang('forums_admin_view_all_sub').' ('.$more.')';
			}
			$return .= '<a href="'.ADMINSCRIPT.'?action=forums&fid='.$forum['fid'].'">'.$more_text.'</a></div></td><td class="td23"></td><td class="td23"></td><td width="280"></td></tr>';
		} else {
			$vfidstr = !empty($_GET['fid']) ? '&vfid='.$_GET['fid'] : '';
			$boardattr = '';
			$threadsorts = dunserialize($forum['threadsorts']);
			if(!$forum['status'] || $forum['password'] || $forum['redirect'] || in_array($forum['fid'], $navs) || !empty($threadsorts['suptypeid'])) {
				$boardattr = '<div class="boardattr">';
				$boardattr .= !empty($threadsorts['suptypeid']) ? $threadtypes[$threadsorts['suptypeid']].' ' : '';
				$boardattr .= $forum['status'] ? '' : cplang('forums_admin_hidden');
				$boardattr .= !$forum['password'] ? '' : ' '.cplang('forums_admin_password');
				$boardattr .= !$forum['redirect'] ? '' : ' '.cplang('forums_admin_url');
				$boardattr .= !in_array($forum['fid'], $navs) ? '' : ' '.cplang('misc_customnav_parent_top');
				$boardattr .= '</div>';
			}

			$return .= '<input type="text" name="name['.$forum['fid'].']" value="'.dhtmlspecialchars($forum['name']).'" class="txt" />'.
				($type == '' ? '<a href="###" onclick="addrowdirect = 1;addrow(this, 2, '.$forum['fid'].')" class="addchildboard">'.cplang('forums_admin_add_sub').'</a>' : '').
				'</div>'.$boardattr.
				'</td><td align="right" class="td23 lightfont">('.($type == 'group' ? 'gid:' : 'fid:').$forum['fid'].')</td>'.
				'</td><td class="td23">'.showforum_moderators($forum).'</td>
				<td width="280"><input class="checkbox" value="'.$forum['fid'].'" type="checkbox"'.($type != 'group' ? ' chkvalue="g'.$_G['fg'].'" onclick="multiupdate(this, '.$forum['fid'].')"' : ' name="gc'.$_G['fg'].'" onclick="checkAll(\'value\', this.form, \'g'.$_G['fg'].'\', \'gc'.$_G['fg'].'\', 1)"').' />'.'
				<a href="'.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'" title="'.cplang('forums_edit_comment').'" class="act">'.cplang('edit').'</a>'.
				($type != 'group' ? '<a href="'.ADMINSCRIPT.'?action=forums&operation=copy&source='.$forum['fid'].$vfidstr.'" title="'.cplang('forums_copy_comment').'" class="act">'.cplang('forums_copy').'</a>' : '').
				'<a href="'.ADMINSCRIPT.'?action=forums&operation=delete&fid='.$forum['fid'].'&formhash='.FORMHASH.$vfidstr.'" title="'.cplang('forums_delete_comment').'" class="act">'.cplang('delete').'</a></td></tr>';
			if($type == 'group') $return .= '<tbody id="group_'.$forum['fid'].'"'.($toggle ? ' style="display:none;"' : '').'>';
		}
	} else {
		if($last == 'lastboard') {
			$return = '</tbody><tr><td></td><td colspan="4"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.cplang('forums_admin_add_forum').'</a></div></td><td>&nbsp;</td></tr>';
		} elseif($last == 'addchildboard') {
			$return = '</tbody><tr><td></td><td colspan="4"><div class="lastboard"><a href="###" onclick="addrow(this, 2, '.$forum['fid'].')" class="addtr">'.cplang('forums_admin_add_sub').'</a></div></td><td>&nbsp;</td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '</tbody><tr><td></td><td colspan="4"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('forums_admin_add_category').'</a></div></td>'.
				'<td class="bold"><a href="javascript:;" onclick="if(getmultiids()) window.open(\''.ADMINSCRIPT.'?action=forums&operation=edit&multi=\' + getmultiids());return false;">'.cplang('multiedit').'</a></td>'.
				'</tr>';
		}
	}

	echo $return = $return ?? '';

	return $forum['fid'];
}

function showforum_moderators($forum) {
	global $_G;
	if($forum['moderators']) {
		$moderators = explode("\t", $forum['moderators']);
		$count = count($moderators);
		$max = $count > 2 ? 2 : $count;
		$mods = [];
		for($i = 0; $i < $max; $i++) {
			$mods[] = $forum['inheritedmod'] ? '<b>'.$moderators[$i].'</b>' : $moderators[$i];
		}
		$r = implode(', ', $mods);
		if($count > 2) {
			$r = '<span onmouseover="showMenu({\'ctrlid\':this.id})" id="mods_'.$forum['fid'].'">'.$r.'</span>';
			$mods = [];
			foreach($moderators as $moderator) {
				$mods[] = $forum['inheritedmod'] ? '<b>'.$moderator.'</b>' : $moderator;
			}
			$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.$r.' &raquo;</a>';
			$r .= '<div class="dropmenu1" id="mods_'.$forum['fid'].'_menu" style="display: none">'.implode('<br />', $mods).'</div>';
		} else {
			$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.$r.' &raquo;</a>';
		}


	} else {
		$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.cplang('forums_admin_no_moderator').'</a>';
	}
	return $r;
}

function getthreadclasses_html($fid) {
	global $_G;
	$threadtypes = table_forum_forumfield::t()->fetch($fid);
	$threadtypes = dunserialize($threadtypes['threadtypes']);

	foreach(table_forum_threadclass::t()->fetch_all_by_fid($fid) as $type) {
		$enablechecked = $moderatorschecked = '';
		$typeselected = [];
		if(isset($threadtypes['types'][$type['typeid']])) {
			$enablechecked = ' checked="checked"';
		}
		if($type['moderators']) {
			$moderatorschecked = ' checked="checked"';
		}
		$typeselect .= showtablerow('', ['class="td25"'], [
			"<input type=\"checkbox\" class=\"checkbox\" name=\"threadtypesnew[options][delete][]\" value=\"{$type['typeid']}\" />",
			"<input type=\"text\" size=\"2\" name=\"threadtypesnew[options][displayorder][{$type['typeid']}]\" value=\"{$type['displayorder']}\" />",
			"<input type=\"text\" name=\"threadtypesnew[options][name][{$type['typeid']}]\" value=\"".(str_replace(["'", "\""], [], $type['name']))."\" />",
			"<input type=\"text\" name=\"threadtypesnew[options][icon][{$type['typeid']}]\" value=\"{$type['icon']}\" />",
			'<input type="checkbox" name="threadtypesnew[options][enable]['.$type['typeid'].']" value="1" class="checkbox"'.$enablechecked.' />',
			"<input type=\"checkbox\" class=\"checkbox\" name=\"threadtypesnew[options][moderators][{$type['typeid']}]\" value=\"1\"{$moderatorschecked} />",
		], TRUE);
	}
	return $typeselect;
}

function get_subfids($fid) {
	global $subfids, $_G;
	$subfids[] = $fid;
	foreach($_G['cache']['forums'] as $key => $value) {
		if($value['fup'] == $fid) {
			get_subfids($value['fid']);
		}
	}
}

function copy_threadclasses($threadtypes, $fid) {
	global $_G;
	if($threadtypes) {
		$threadtypes = dunserialize($threadtypes);
		$i = 0;
		$data = [];
		foreach($threadtypes['types'] as $key => $val) {
			$data = ['fid' => $fid, 'name' => $val, 'displayorder' => $i++, 'icon' => $threadtypes['icons'][$key], 'moderators' => $threadtypes['moderators'][$key]];
			$newtypeid = table_forum_threadclass::t()->insert($data, true);
			$newtypes[$newtypeid] = $val;
			$newicons[$newtypeid] = $threadtypes['icons'][$key];
			$newmoderators[$newtypeid] = $threadtypes['moderators'][$key];
		}
		$threadtypes['types'] = $newtypes;
		$threadtypes['icons'] = $newicons;
		$threadtypes['moderators'] = $newmoderators;
		return serialize($threadtypes);
	}
	return '';
}

function showrulerow($rule, $sub = 0) {
	global $_G, $lang, $fid, $forum;

	$globalrule = $rule;
	$readonly = $checked = '';
	if(isset($forum['creditspolicy'][$rule['action']]) && $rule['rid'] == $forum['creditspolicy'][$rule['action']]['rid']) {
		$rule = $forum['creditspolicy'][$rule['action']];
		$checked = ' checked="checked"';
	} else {
		for($i = 1; $i <= 8; $i++) {
			$rule['extcredits'.$i] = '';
		}
		$readonly = ' readonly="readonly" style="display:none;"';
	}
	$usecustom = '<input type="checkbox" name="usecustom['.$rule['rid'].']" onclick="modifystate(this);" value="1" class="checkbox" '.$checked.' />';
	$tdarr = [$sub ? '<div class="board">&nbsp;</div>' : $rule['rulename'], $rule['rid'] ? cplang('setting_credits_policy_cycletype_'.$rule['cycletype']).($rule['cycletype'] != $globalrule['cycletype'] ? '&nbsp;&nbsp;<del style="color:#9f9f9f;">('.cplang('setting_credits_policy_cycletype_'.$globalrule['cycletype']).')</del>' : '') : 'N/A', $rule['rid'] && $rule['cycletype'] ? $rule['rewardnum'] : 'N/A', $usecustom];

	for($i = 1; $i <= 8; $i++) {
		if($_G['setting']['extcredits'][$i]) {
			array_push($tdarr, $sub ? '' : '<input type="text" name="creditnew['.$rule['rid'].']['.$i.']" class="txt smtxt" value="'.$rule['extcredits'.$i].'" '.$readonly.' /><span class="sml">('.($globalrule['extcredits'.$i]).')</span>');
		}
	}
	$opstr = '<a href="'.ADMINSCRIPT.'?action=credits&operation=edit&rid='.$rule['rid'].'&fid='.$fid.'" title="" class="act">'.cplang('edit').'</a>';
	array_push($tdarr, $opstr);
	showtablerow('', array_fill(4, count($_G['setting']['extcredits']) + 4, 'width="70"'), $tdarr);
}

