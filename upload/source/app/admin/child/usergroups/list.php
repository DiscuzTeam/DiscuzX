<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('groupsubmit')) {

	$sgroups = $smembers = $specialgroup = [];
	$sgroupids = '0';
	$smembernum = $membergroup = $sysgroup = $membergroupoption = $specialgroupoption = '';

	foreach(table_common_usergroup::t()->range_orderby_creditshigher() as $group) {
		if($group['type'] == 'member') {

			$membergroupoption .= "<option value=\"g{$group['groupid']}\">".addslashes($group['grouptitle']).'</option>';

			$membergroup .= showtablerow('', ['class="td25"', '', 'class="td23 lightfont"', 'class="td28"', 'class=td28'], [
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[{$group['groupid']}]\" value=\"{$group['groupid']}\">",
				"<input type=\"text\" class=\"txt\" size=\"12\" name=\"groupnew[{$group['groupid']}][grouptitle]\" value=\"{$group['grouptitle']}\">".iconimg($group['icon']),
				"(groupid:{$group['groupid']})",
				"<input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[{$group['groupid']}][creditshigher]\" value=\"{$group['creditshigher']}\" /> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[{$group['groupid']}][creditslower]\" value=\"{$group['creditslower']}\" disabled />",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"groupnew[{$group['groupid']}][stars]\" value=\"{$group['stars']}\">",
				"<input type=\"text\" id=\"group_color_{$group['groupid']}_v\" class=\"left txt\" size=\"6\" name=\"groupnew[{$group['groupid']}][color]\" value=\"{$group['color']}\" onchange=\"updatecolorpreview('group_color_{$group['groupid']}')\"><input type=\"button\" id=\"group_color_{$group['groupid']}\"  class=\"colorwd\" onclick=\"group_color_{$group['groupid']}_frame.location='static/image/admincp/getcolor.htm?group_color_{$group['groupid']}|group_color_{$group['groupid']}_v';showMenu({'ctrlid':'group_color_{$group['groupid']}'})\" style=\"background: {$group['color']}\" /><span id=\"group_color_{$group['groupid']}_menu\" style=\"display: none\"><iframe name=\"group_color_{$group['groupid']}_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gmember\" value=\"{$group['groupid']}\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id={$group['groupid']}\" class=\"act\">{$lang['edit']}</a>".
				"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source={$group['groupid']}\" title=\"{$lang['usergroups_copy_comment']}\" class=\"act\">{$lang['usergroups_copy']}</a>".
				"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=merge&source={$group['groupid']}\" title=\"{$lang['usergroups_merge_comment']}\" class=\"act\">{$lang['usergroups_merge_link']}</a>"
			], TRUE);
		} elseif($group['type'] == 'system') {
			$sysgroup .= showtablerow('', ['', 'class="td23 lightfont"', '', 'class="td28"'], [
				"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[{$group['groupid']}]\" value=\"{$group['grouptitle']}\">".iconimg($group['icon']),
				"(groupid:{$group['groupid']})",
				$lang['usergroups_system_'.$group['groupid']],
				"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[{$group['groupid']}]\" value=\"{$group['stars']}\">",
				"<input type=\"text\" id=\"group_color_{$group['groupid']}_v\" class=\"left txt\" size=\"6\"name=\"group_color[{$group['groupid']}]\" value=\"{$group['color']}\" onchange=\"updatecolorpreview('group_color_{$group['groupid']}')\"><input type=\"button\" id=\"group_color_{$group['groupid']}\"  class=\"colorwd\" onclick=\"group_color_{$group['groupid']}_frame.location='static/image/admincp/getcolor.htm?group_color_{$group['groupid']}|group_color_{$group['groupid']}_v';showMenu({'ctrlid':'group_color_{$group['groupid']}'})\" style=\"background: {$group['color']}\" /><span id=\"group_color_{$group['groupid']}_menu\" style=\"display: none\"><iframe name=\"group_color_{$group['groupid']}_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gsystem\" value=\"{$group['groupid']}\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id={$group['groupid']}\" class=\"act\">{$lang['edit']}</a>".
				"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source={$group['groupid']}\" title=\"{$lang['usergroups_copy_comment']}\" class=\"act\">{$lang['usergroups_copy']}</a>"
			], TRUE);
		} elseif($group['type'] == 'special' && $group['radminid'] == '0') {

			$specialgroupoption .= "<option value=\"g{$group['groupid']}\">".addslashes($group['grouptitle']).'</option>';

			$sgroups[] = $group;
			$sgroupids .= ','.$group['groupid'];
		}
	}

	$specialupgroup = $lastupgid = $gsystems = [];
	foreach($sgroups as $group) {
		if($group['upgroupid'] == 0) {
			continue;
		}

		if(is_array($smembers[$group['groupid']])) {
			$num = count($smembers[$group['groupid']]);
			$specifiedusers = implode('', $smembers[$group['groupid']]).($num > $smembernum[$group['groupid']] ? '<br /><div style="float: right; clear: both; margin:5px"><a href="'.ADMINSCRIPT.'?action=members&submit=yes&usergroupid[]='.$group['groupid'].'" style="text-align: right;">'.$lang['more'].'&raquo;</a>&nbsp;</div>' : '<br /><br/>');
			unset($smembers[$group['groupid']]);
		} else {
			$specifiedusers = '';
			$num = 0;
		}
		if($specifiedusers) {
			$specifiedusers = "<style>#specifieduser span{width: 9em; height: 2em; float: left; overflow: hidden; margin: 2px;}</style><div id=\"specifieduser\">$specifiedusers</div>";
		}

		$gsystem = '';
		if($group['system'] != 'private') {
			list($dailyprice) = explode("\t", $group['system']);
			$gsystem .= $dailyprice > 0 ? cplang('usergroups_buy') : cplang('usergroups_free');
		}
		if($group['upgroupid'] == $group['groupid']) {
			$gsystems[$group['groupid']] = $gsystem;
			$readonly = 'readonly ';
		} else {
			$gsystem = $gsystems[$group['upgroupid']];
			$readonly = '';
		}
		$gsystem .= ' '.cplang('usergroups_up');

		$sg = showtablerow('', ['class="td25"', '', 'class="td23 lightfont"', '', 'class="td28"'], [
			"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[{$group['groupid']}]\" value=\"{$group['groupid']}\">",
			"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[{$group['groupid']}]\" value=\"{$group['grouptitle']}\">".iconimg($group['icon']),
			"(groupid:{$group['groupid']})",
			$gsystem,
			"<input type=\"text\" class=\"txt\" size=\"6\" name=\"group_credits[{$group['upgroupid']}][{$group['groupid']}][creditshigher]\" value=\"{$group['creditshigher']}\" $readonly/> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"group_credits[{$group['upgroupid']}][{$group['groupid']}][creditslower]\" value=\"{$group['creditslower']}\" disabled />",
			"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[{$group['groupid']}]\" value=\"{$group['stars']}\">",
			"<input type=\"text\" id=\"group_color_{$group['groupid']}_v\" class=\"left txt\" size=\"6\"name=\"group_color[{$group['groupid']}]\" value=\"{$group['color']}\" onchange=\"updatecolorpreview('group_color_{$group['groupid']}')\"><input type=\"button\" id=\"group_color_{$group['groupid']}\"  class=\"colorwd\" onclick=\"group_color_{$group['groupid']}_frame.location='static/image/admincp/getcolor.htm?group_color_{$group['groupid']}|group_color_{$group['groupid']}_v';showMenu({'ctrlid':'group_color_{$group['groupid']}'})\" style=\"background: {$group['color']}\" /><span id=\"group_color_{$group['groupid']}_menu\" style=\"display: none\"><iframe name=\"group_color_{$group['groupid']}_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
			"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gspecial\" value=\"{$group['groupid']}\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id={$group['groupid']}\" class=\"act\">{$lang['edit']}</a>".
			"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source={$group['groupid']}\" title=\"{$lang['usergroups_copy_comment']}\" class=\"act\">{$lang['usergroups_copy']}</a>".
			"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=merge&source={$group['groupid']}\" title=\"{$lang['usergroups_merge_comment']}\" class=\"act\">{$lang['usergroups_merge_link']}</a>".
			"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=viewsgroup&sgroupid={$group['groupid']}\" onclick=\"ajaxget(this.href, 'sgroup_{$group['groupid']}', 'sgroup_{$group['groupid']}');doane(event);\" class=\"act\">{$lang['view']}</a> &nbsp;"
		], TRUE);
		$sg .= showtablerow('', ['colspan="5" id="sgroup_'.$group['groupid'].'" style="display: none"'], [''], TRUE);

		$specialgroup['up'][$group['upgroupid']] .= $sg;
		$lastupgid[$group['upgroupid']] = $group['groupid'];
	}

	foreach($sgroups as $group) {
		if($group['upgroupid'] > 0) {
			continue;
		}
		if(is_array($smembers[$group['groupid']])) {
			$num = count($smembers[$group['groupid']]);
			$specifiedusers = implode('', $smembers[$group['groupid']]).($num > $smembernum[$group['groupid']] ? '<br /><div style="float: right; clear: both; margin:5px"><a href="'.ADMINSCRIPT.'?action=members&submit=yes&usergroupid[]='.$group['groupid'].'" style="text-align: right;">'.$lang['more'].'&raquo;</a>&nbsp;</div>' : '<br /><br/>');
			unset($smembers[$group['groupid']]);
		} else {
			$specifiedusers = '';
			$num = 0;
		}
		if($specifiedusers) {
			$specifiedusers = "<style>#specifieduser span{width: 9em; height: 2em; float: left; overflow: hidden; margin: 2px;}</style><div id=\"specifieduser\">$specifiedusers</div>";
		}

		$gsystem = '';
		if($group['system'] != 'private') {
			list($dailyprice) = explode("\t", $group['system']);
			$gsystem = $dailyprice > 0 ? cplang('usergroups_buy') : cplang('usergroups_free');
		}

		$sg = showtablerow('', ['class="td25"', '', 'class="td23 lightfont"', '', 'class="td28"'], [
			"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[{$group['groupid']}]\" value=\"{$group['groupid']}\">",
			"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[{$group['groupid']}]\" value=\"{$group['grouptitle']}\">".iconimg($group['icon']),
			"(groupid:{$group['groupid']})",
			$gsystem, '',
			"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[{$group['groupid']}]\" value=\"{$group['stars']}\">",
			"<input type=\"text\" id=\"group_color_{$group['groupid']}_v\" class=\"left txt\" size=\"6\"name=\"group_color[{$group['groupid']}]\" value=\"{$group['color']}\" onchange=\"updatecolorpreview('group_color_{$group['groupid']}')\"><input type=\"button\" id=\"group_color_{$group['groupid']}\"  class=\"colorwd\" onclick=\"group_color_{$group['groupid']}_frame.location='static/image/admincp/getcolor.htm?group_color_{$group['groupid']}|group_color_{$group['groupid']}_v';showMenu({'ctrlid':'group_color_{$group['groupid']}'})\" style=\"background: {$group['color']}\" /><span id=\"group_color_{$group['groupid']}_menu\" style=\"display: none\"><iframe name=\"group_color_{$group['groupid']}_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
			"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gspecial\" value=\"{$group['groupid']}\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id={$group['groupid']}\" class=\"act\">{$lang['edit']}</a>".
			"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source={$group['groupid']}\" title=\"{$lang['usergroups_copy_comment']}\" class=\"act\">{$lang['usergroups_copy']}</a>".
			"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=merge&source={$group['groupid']}\" title=\"{$lang['usergroups_merge_comment']}\" class=\"act\">{$lang['usergroups_merge_link']}</a>".
			"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=viewsgroup&sgroupid={$group['groupid']}\" onclick=\"ajaxget(this.href, 'sgroup_{$group['groupid']}', 'sgroup_{$group['groupid']}');doane(event);\" class=\"act\">{$lang['view']}</a> &nbsp;"
		], TRUE);
		$sg .= showtablerow('', ['colspan="5" id="sgroup_'.$group['groupid'].'" style="display: none"'], [''], TRUE);

		if($group['system'] == 'private') {
			$st = 'private';
		} else {
			list($dailyprice) = explode("\t", $group['system']);
			$st = $dailyprice > 0 ? 'buy' : 'free';
		}
		$specialgroup[$st] .= $sg;
	}

	$systemoption = ' <label><input name="systemnewadd[]" value="1" class="checkbox" type="checkbox">'.$lang['usergroups_pub'].'</label>';
	$systemoption .= '<label><input name="upnewadd[]" value="1" class="checkbox" type="checkbox" onclick="display(\\\'upname_{n}\\\')">'.$lang['usergroups_up'].'</label>';
	$systemoption .= ' <span style="width: 120px;display: inline-block"><input type="text" id="upname_{n}" style="display: none" class="txt" size="2" name="upnamenewadd[]"></span>';

	echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
[
	[1,'', 'td25'],
	[2,'<input type="text" class="txt" size="12" name="groupnewadd[grouptitle][]"><select name="groupnewadd[projectid][]"><option value="">{$lang['usergroups_project']}</option><option value="0">------------</option>$membergroupoption</select>'],
	[1,'<input type="text" class="txt" size="6" name="groupnewadd[creditshigher][]">', 'td28'],
	[1,'<input type="text" class="txt" size="2" name="groupnewadd[stars][]">', 'td28'],
	[2,'<input type="text" class="txt" size="6" name="groupnewadd[color][]">']
],
[
	[1,'', 'td25'],
	[4,'<input type="text" class="txt" size="12" name="grouptitlenewadd[]"><select name="groupnewaddproject[]"><option value="">{$lang['usergroups_project']}</option><option value="0">------------</option>$specialgroupoption</select>$systemoption'],
	[3,'<input type="text" class="txt" size="2" name="starsnewadd[]">', ''],
],
[
	[1,'', 'td25'],
	[3,'<input type="text" class="txt" size="12" name="grouptitlenewadd2[{1}][]"><input name="groupnewaddproject2[{1}][]" value="g{2}" type="hidden">'],
	[1,'<input type="text" class="txt" size="2" name="creditshighernewadd2[{1}][]">', 'td28'],
	[3,'<input type="text" class="txt" size="2" name="starsnewadd2[{1}][]">', ''],
],
];
</script>
EOT;
	shownav('user', 'nav_usergroups');
	showsubmenuanchors('nav_usergroups', [
		['usergroups_member', 'membergroups', !$_GET['type'] || $_GET['type'] == 'member'],
		['usergroups_special', 'specialgroups', $_GET['type'] == 'special'],
		['usergroups_system', 'systemgroups', $_GET['type'] == 'system']
	]);
	/*search={"nav_usergroups":"action=usergroups"}*/
	showtips('usergroups_tips');
	/*search*/

	showformheader('usergroups&type=member');
	showtableheader('usergroups_member', 'fixpadding', 'id="membergroups"'.($_GET['type'] && $_GET['type'] != 'member' ? ' style="display: none"' : ''));
	showsubtitle(['', 'usergroups_title', '', 'usergroups_creditsrange', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcmember" onclick="checkAll(\'value\', this.form, \'gmember\', \'gcmember\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) window.open(\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids());return false;">'.$lang['multiedit'].'</a>']);
	echo $membergroup;
	echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['usergroups_add'].'</a></div></td></tr>';
	showsubmit('groupsubmit', 'submit', 'del');
	showtablefooter();
	showformfooter();

	showformheader('usergroups&type=special');
	showtableheader('usergroups_special', 'fixpadding', 'id="specialgroups"'.($_GET['type'] != 'special' ? ' style="display: none"' : ''));
	showsubtitle(['', 'usergroups_title', '', 'usergroups_systemtype', 'usergroups_creditsrange', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcspecial" onclick="checkAll(\'value\', this.form, \'gspecial\', \'gcspecial\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) window.open(\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids());return false;">'.$lang['multiedit'].'</a>']);
	if($specialgroup['private']) {
		echo $specialgroup['private'];
	}
	if($specialgroup['buy']) {
		showsubtitle(['', 'usergroups_edit_system_buy']);
		echo $specialgroup['buy'];
	}
	if($specialgroup['free']) {
		showsubtitle(['', 'usergroups_edit_system_free']);
		echo $specialgroup['free'];
	}
	if($specialgroup['up']) {
		foreach($specialgroup['up'] as $groupid => $v) {
			echo '<tr><td>&nbsp;</td><td colspan="6"><strong>'.$_G['setting']['upgroup_name'][$groupid].'</strong></td></tr>';
			echo $v;
			echo '<tr><td>&nbsp;</td><td colspan="6"><div><a href="###" onclick="addrow(this, 2, '.$groupid.', '.$lastupgid[$groupid].')" class="addtr">'.$lang['usergroups_sepcialup_add'].'</a></div></td></tr>';
		}
	}
	echo '<tr><td>&nbsp;</td><td colspan="6"><div><a href="###" onclick="addrow(this, 1)" class="addtr">'.$lang['usergroups_sepcial_add'].'</a></div></td></tr>';
	showsubmit('groupsubmit', 'submit', 'del');
	showtablefooter();
	showformfooter();

	showformheader('usergroups&type=system');
	showtableheader('usergroups_system', 'fixpadding', 'id="systemgroups"'.($_GET['type'] != 'system' ? ' style="display: none"' : ''));
	showsubtitle(['usergroups_title', '', 'usergroups_status', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcsystem" onclick="checkAll(\'value\', this.form, \'gsystem\', \'gcsystem\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) window.open(\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids());return false;">'.$lang['multiedit'].'</a>']);
	echo $sysgroup;
	showsubmit('groupsubmit');
	showtablefooter();
	showformfooter();

} else {

	if(empty($_GET['type']) || !in_array($_GET['type'], ['member', 'special', 'system'])) {
		cpmsg('usergroups_type_nonexistence');
	}

	$oldgroups = $extadd = [];
	foreach(table_common_usergroup::t()->fetch_all_by_type($_GET['type'], null, true) as $gp) {
		$oldgroups[$gp['groupid']] = $gp;
	}

	foreach($oldgroups as $id => $vals) {
		$data = [];
		foreach($vals as $k => $v) {
			$v = addslashes($v);
			if(!in_array($k, ['groupid', 'radminid', 'type', 'system', 'grouptitle', 'creditshigher', 'creditslower', 'stars', 'color'])) {
				$data[$k] = $v;
			}
		}
		$extadd['g'.$id] = $data;
	}

	if($_GET['type'] == 'member') {
		$groupnewadd = array_flip_keys($_GET['groupnewadd']);
		foreach($groupnewadd as $k => $v) {
			if(!$v['grouptitle']) {
				unset($groupnewadd[$k]);
			} elseif(!$v['creditshigher']) {
				cpmsg('usergroups_update_creditshigher_invalid', '', 'error');
			}
		}
		$groupnewkeys = array_keys($_GET['groupnew']);
		$maxgroupid = max($groupnewkeys);
		foreach($groupnewadd as $k => $v) {
			$_GET['groupnew'][$k + $maxgroupid + 1] = $v;
		}
		$orderarray = [];
		if(is_array($_GET['groupnew'])) {
			foreach($_GET['groupnew'] as $id => $group) {
				if((is_array($_GET['delete']) && in_array($id, $_GET['delete'])) || ($id == 0 && (!$group['grouptitle'] || $group['creditshigher'] == ''))) {
					unset($_GET['groupnew'][$id]);
				} else {
					$orderarray[$group['creditshigher']] = $id;
				}
			}
		}

		if(empty($orderarray[0]) || min(array_flip($orderarray)) >= 0) {
			cpmsg('usergroups_update_credits_invalid', '', 'error');
		}

		ksort($orderarray);
		$rangearray = [];
		$lowerlimit = array_keys($orderarray);
		for($i = 0; $i < count($lowerlimit); $i++) {
			$rangearray[$orderarray[$lowerlimit[$i]]] = [
				'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
				'creditslower' => $lowerlimit[$i + 1] ?? 999999999
			];
		}

		foreach($_GET['groupnew'] as $id => $group) {
			$creditshighernew = $rangearray[$id]['creditshigher'];
			$creditslowernew = $rangearray[$id]['creditslower'];
			if($creditshighernew == $creditslowernew) {
				cpmsg('usergroups_update_credits_duplicate', '', 'error');
			}
			if(in_array($id, $groupnewkeys)) {
				table_common_usergroup::t()->update_usergroup($id, ['grouptitle' => $group['grouptitle'], 'creditshigher' => $creditshighernew, 'creditslower' => $creditslowernew, 'stars' => $group['stars'], 'color' => $group['color']], 'member');
				table_forum_onlinelist::t()->update_by_groupid($id, ['title' => $group['grouptitle']]);

			} elseif($group['grouptitle'] && $group['creditshigher'] != '') {
				$data = [
					'grouptitle' => $group['grouptitle'],
					'creditshigher' => $creditshighernew,
					'creditslower' => $creditslowernew,
					'stars' => $group['stars'],
					'color' => $group['color'],
				];
				if(!empty($group['projectid']) && !empty($extadd[$group['projectid']])) {
					$data = array_merge($data, $extadd[$group['projectid']]);
				}

				$newgid = table_common_usergroup::t()->insert($data, true);

				$datafield = [
					'groupid' => $newgid,
					'allowsearch' => 2,
				];


				table_common_usergroup_field::t()->insert($datafield);

				table_forum_onlinelist::t()->insert([
					'groupid' => $newgid,
					'title' => $data['grouptitle'],
					'displayorder' => '0',
					'url' => '',
				]);

				$sqladd = !empty($group['projectid']) && !empty($extadd[$group['projectid']]) ? $extadd[$group['projectid']] : '';
				if($sqladd) {
					$projectid = substr($group['projectid'], 1);
					$group_fields = table_common_usergroup_field::t()->fetch($projectid);
					unset($group_fields['groupid']);
					table_common_usergroup_field::t()->update($newgid, $group_fields);
					$query = table_forum_forumfield::t()->fetch_all_field_perm();
					foreach($query as $row) {
						$upforumperm = [];
						if($row['viewperm'] && in_array($projectid, explode("\t", $row['viewperm']))) {
							$upforumperm['viewperm'] = "{$row['viewperm']}$newgid\t";
						}
						if($row['postperm'] && in_array($projectid, explode("\t", $row['postperm']))) {
							$upforumperm['postperm'] = "{$row['postperm']}$newgid\t";
						}
						if($row['replyperm'] && in_array($projectid, explode("\t", $row['replyperm']))) {
							$upforumperm['replyperm'] = "{$row['replyperm']}$newgid\t";
						}
						if($row['getattachperm'] && in_array($projectid, explode("\t", $row['getattachperm']))) {
							$upforumperm['getattachperm'] = "{$row['getattachperm']}$newgid\t";
						}
						if($row['postattachperm'] && in_array($projectid, explode("\t", $row['postattachperm']))) {
							$upforumperm['postattachperm'] = "{$row['postattachperm']}$newgid\t";
						}
						if($row['postimageperm'] && in_array($projectid, explode("\t", $row['postimageperm']))) {
							$upforumperm['postimageperm'] = "{$row['postimageperm']}$newgid\t";
						}
						if($upforumperm) {
							table_forum_forumfield::t()->update($row['fid'], $upforumperm);
						}
					}
				}
			}
		}

		if($_GET['delete']) {
			table_common_usergroup::t()->delete_usergroup($_GET['delete'], 'member');
			table_common_usergroup_field::t()->delete($_GET['delete']);
			table_forum_onlinelist::t()->delete_by_groupid($_GET['delete']);
			deletegroupcache($_GET['delete']);
		}

	} elseif($_GET['type'] == 'special') {
		$upgids = $creditshighers = [];
		if(is_array($_GET['grouptitlenewadd2'])) {
			$ii = $_GET['grouptitlenewadd'] ? count($_GET['grouptitlenewadd']) : 0;
			$i = $ii;
			foreach($_GET['grouptitlenewadd2'] as $upgid => $v) {
				foreach($v as $_v) {
					$upgids[$i] = $upgid;
					$_GET['grouptitlenewadd'][$i] = $_v;
					$i++;
				}
			}
			$i = $ii;
			foreach($_GET['groupnewaddproject2'] as $upgid => $v) {
				foreach($v as $_v) {
					$_GET['groupnewaddproject'][$i] = $_v;
					$i++;
				}
			}
			$i = $ii;
			foreach($_GET['creditshighernewadd2'] as $upgid => $v) {
				foreach($v as $_v) {
					$creditshighers[$i] = $_v;
					$i++;
				}
			}
			$i = $ii;
			foreach($_GET['starsnewadd2'] as $upgid => $v) {
				foreach($v as $_v) {
					$_GET['starsnewadd'][$i] = $_v;
					$i++;
				}
			}
		}

		if(is_array($_GET['grouptitlenewadd'])) {
			foreach($_GET['grouptitlenewadd'] as $k => $v) {
				if($v) {
					$data = [
						'type' => 'special',
						'grouptitle' => $_GET['grouptitlenewadd'][$k],
						'color' => $_GET['colornewadd'][$k] ?? '',
						'stars' => $_GET['starsnewadd'][$k],
						'system' => !empty($_GET['systemnewadd'][$k]) ? "0\t0" : '',
					];
					if(!empty($upgids[$k])) {
						$data['upgroupid'] = $upgids[$k];
					}
					if(!empty($creditshighers)) {
						$data['creditshigher'] = $creditshighers[$k];
					}
					if(!empty($_GET['groupnewaddproject'][$k]) && !empty($extadd[$_GET['groupnewaddproject'][$k]])) {
						$data = array_merge($data, $extadd[$_GET['groupnewaddproject'][$k]]);
					}
					$newgid = table_common_usergroup::t()->insert($data, true);
					if(!empty($creditshighers)) {
						$_GET['group_credits'][$upgids[$k]][$newgid]['creditshigher'] = $creditshighers[$k];
					}
					$datafield = [
						'groupid' => $newgid,
						'allowsearch' => 2,
					];

					if(!empty($_GET['upnewadd'][$k])) {
						table_common_usergroup::t()->update($newgid, [
							'upgroupid' => $newgid,
						]);
						$_G['setting']['upgroup_name'][$newgid] = $_GET['upnamenewadd'][$k] ? $_GET['upnamenewadd'][$k] : $_GET['grouptitlenewadd'][$k];
						$settings = [
							'upgroup_name' => $_G['setting']['upgroup_name'],
						];
						table_common_setting::t()->update_batch($settings);
					}

					table_common_usergroup_field::t()->insert($datafield);
					table_forum_onlinelist::t()->insert([
						'groupid' => $newgid,
						'title' => $data['grouptitle'],
						'url' => '',
					]);
					$sqladd = !empty($_GET['groupnewaddproject'][$k]) && !empty($extadd[$_GET['groupnewaddproject'][$k]]) ? $extadd[$_GET['groupnewaddproject'][$k]] : '';
					if($sqladd) {
						$projectid = substr($_GET['groupnewaddproject'][$k], 1);
						$group_fields = table_common_usergroup_field::t()->fetch($projectid);
						unset($group_fields['groupid']);
						table_common_usergroup_field::t()->update($newgid, $group_fields);
						$query = table_forum_forumfield::t()->fetch_all_field_perm();
						foreach($query as $row) {
							$upforumperm = [];
							if($row['viewperm'] && in_array($projectid, explode("\t", $row['viewperm']))) {
								$upforumperm['viewperm'] = "{$row['viewperm']}$newgid\t";
							}
							if($row['postperm'] && in_array($projectid, explode("\t", $row['postperm']))) {
								$upforumperm['postperm'] = "{$row['postperm']}$newgid\t";
							}
							if($row['replyperm'] && in_array($projectid, explode("\t", $row['replyperm']))) {
								$upforumperm['replyperm'] = "{$row['replyperm']}$newgid\t";
							}
							if($row['getattachperm'] && in_array($projectid, explode("\t", $row['getattachperm']))) {
								$upforumperm['getattachperm'] = "{$row['getattachperm']}$newgid\t";
							}
							if($row['postattachperm'] && in_array($projectid, explode("\t", $row['postattachperm']))) {
								$upforumperm['postattachperm'] = "{$row['postattachperm']}$newgid\t";
							}
							if($row['postimageperm'] && in_array($projectid, explode("\t", $row['postimageperm']))) {
								$upforumperm['postimageperm'] = "{$row['postimageperm']}$newgid\t";
							}
							if($upforumperm) {
								table_forum_forumfield::t()->update($row['fid'], $upforumperm);
							}
						}
					}
				}
			}
		}

		if(is_array($_GET['group_title'])) {
			foreach($_GET['group_title'] as $id => $title) {
				if(!$_GET['delete'][$id]) {
					table_common_usergroup::t()->update_usergroup($id, ['grouptitle' => $_GET['group_title'][$id], 'stars' => $_GET['group_stars'][$id], 'color' => $_GET['group_color'][$id]]);
					table_forum_onlinelist::t()->update_by_groupid($id, ['title' => $_GET['group_title'][$id]]);
				}
			}
		}

		if(!empty($_GET['group_credits'])) {
			$orderarray = $rangearray = [];
			foreach($_GET['group_credits'] as $upgroupid => $groups) {
				foreach($groups as $id => $group) {
					if(is_array($_GET['delete']) && in_array($id, $_GET['delete'])) {
					} else {
						if($group['creditshigher'] < 0) {
							$group['creditshigher'] = 0;
						}
						if(isset($orderarray[$upgroupid][$group['creditshigher']])) {
							cpmsg('usergroups_up_update_credits_duplicate', '', 'error');
						}
						$orderarray[$upgroupid][$group['creditshigher']] = $id;
					}
				}

				if($orderarray[$upgroupid]) {
					ksort($orderarray[$upgroupid]);
					$lowerlimit = array_keys($orderarray[$upgroupid]);
					for($i = 0; $i < count($lowerlimit); $i++) {
						$rangearray[$orderarray[$upgroupid][$lowerlimit[$i]]] = [
							'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : 0,
							'creditslower' => $lowerlimit[$i + 1] ?? 999999999
						];
					}
				}
			}
			if(!empty($rangearray)) {
				foreach($rangearray as $groupid => $data) {
					table_common_usergroup::t()->update_usergroup($groupid, $data);
				}
			}
		}

		if(($ids = $_GET['delete'])) {
			table_common_usergroup::t()->delete_usergroup($ids, 'special');
			table_forum_onlinelist::t()->delete_by_groupid($ids);
			table_common_admingroup::t()->delete($ids);
			$newgroupid = table_common_usergroup::t()->fetch_new_groupid();
			table_common_member::t()->update_by_groupid($ids, ['groupid' => $newgroupid, 'adminid' => '0']);
			deletegroupcache($ids);
		}

	} elseif($_GET['type'] == 'system') {
		if(is_array($_GET['group_title'])) {
			foreach($_GET['group_title'] as $id => $title) {
				table_common_usergroup::t()->update_usergroup($id, ['grouptitle' => $_GET['group_title'][$id], 'stars' => $_GET['group_stars'][$id], 'color' => $_GET['group_color'][$id]]);
				table_forum_onlinelist::t()->update_by_groupid($id, ['title' => $_GET['group_title'][$id]]);
			}
		}
	}

	updatecache(['usergroups', 'onlinelist', 'groupreadaccess', 'setting']);
	cpmsg('usergroups_update_succeed', 'action=usergroups&type='.$_GET['type'], 'succeed');
}
	