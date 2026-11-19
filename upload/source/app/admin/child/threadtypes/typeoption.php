<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('typeoptionsubmit')) {

	$typeSetting = [];
	threadtype_sysdata($typeSetting);
	foreach($_G['setting']['plugins']['available'] as $plugin) {
		threadtype_data($plugin, $typeSetting);
	}

	$plugins = [];
	foreach($typeSetting as $plugin) {
		$plugins[$plugin[0]] = '<option value="plugin/'.$plugin[0].'">'.$plugin[1].'('.$plugin[0].')</option>';
	}

	if($_GET['classid']) {
		$typetitle = table_forum_typeoption::t()->fetch($_GET['classid']);
		if(!$typetitle['title']) {
			cpmsg('threadtype_infotypes_noexist', 'action=threadtypes', 'error');
		}

		$typeoptions = '';
		foreach(table_forum_typeoption::t()->fetch_all_by_classid($_GET['classid']) as $option) {
			if($option['type'] != 'plugin') {
				$type = $lang['threadtype_edit_vars_type_'.$option['type']];
			} else {
				$rules = dunserialize($option['rules']);
				$type = strip_tags($plugins[$rules['pluginthreadtype']]);
			}
			$typeoptions .= showtablerow('', ['class="td25"', 'class="td28"'], [
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"{$option['optionid']}\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[{$option['optionid']}]\" value=\"{$option['displayorder']}\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"title[{$option['optionid']}]\" value=\"".dhtmlspecialchars($option['title'])."\">",
				"{$option['identifier']}<input type=\"hidden\" name=\"identifier[{$option['optionid']}]\" value=\"{$option['identifier']}\">",
				$type,
				"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=optiondetail&optionid={$option['optionid']}\" class=\"act\">{$lang['detail']}</a>"
			], TRUE);
		}
	}

	$plugins = implode('', $plugins);
	echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1, '', 'td25'],
			[1, '<input type="text" class="txt" size="2" name="newdisplayorder[]" value="0">', 'td28'],
			[1, '<input type="text" class="txt" size="15" name="newtitle[]">'],
			[1, '<input type="text" class="txt" size="15" name="newidentifier[]">'],
			[1, '<select name="newtype[]"><option value="number">{$lang['threadtype_edit_vars_type_number']}</option><option value="text" selected>{$lang['threadtype_edit_vars_type_text']}</option><option value="textarea">{$lang['threadtype_edit_vars_type_textarea']}</option><option value="radio">{$lang['threadtype_edit_vars_type_radio']}</option><option value="checkbox">{$lang['threadtype_edit_vars_type_checkbox']}</option><option value="select">{$lang['threadtype_edit_vars_type_select']}</option><option value="calendar">{$lang['threadtype_edit_vars_type_calendar']}</option><option value="email">{$lang['threadtype_edit_vars_type_email']}</option><option value="image">{$lang['threadtype_edit_vars_type_image']}</option><option value="url">{$lang['threadtype_edit_vars_type_url']}</option><option value="range">{$lang['threadtype_edit_vars_type_range']}</option>$plugins</select>'],
			[1, '']
		],
	];
</script>
EOT;

	shownav('forum', 'threadtype_infotypes');
	showsubmenu('threadtype_infotypes', [
		['threadtype_infotypes_type', 'threadtypes', 0],
		['threadtype_infotypes_content', 'threadtypes&operation=content', 0],
		['threadtype_infotypes_class', 'threadtypes&operation=class', 0],
		[['menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu], 1]
	]);
	showformheader("threadtypes&operation=typeoption&typeid={$_GET['typeid']}");
	showhiddenfields(['classid' => $_GET['classid']]);
	showtableheader();

	showsubtitle(['', 'display_order', 'name', 'threadtype_variable', 'threadtype_type', '']);
	echo $typeoptions;
	echo '<tr><td></td><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['threadtype_infotypes_add_option'].'</a></div></td></tr>';
	showsubmit('typeoptionsubmit', 'submit', 'del');

	showtablefooter();
	showformfooter();

} else {

	if($ids = dimplode($_GET['delete'])) {
		table_forum_typeoption::t()->delete($_GET['delete']);
		table_forum_typevar::t()->delete_typevar(null, $_GET['delete']);
	}

	if(is_array($_GET['title'])) {
		foreach($_GET['title'] as $id => $val) {
			if(in_array(strtoupper($_GET['identifier'][$id]), $mysql_keywords)) {
				continue;
			}
			table_forum_typeoption::t()->update($id, [
				'displayorder' => $_GET['displayorder'][$id],
				'title' => $_GET['title'][$id],
				'identifier' => $_GET['identifier'][$id],
			]);
		}
	}

	if(is_array($_GET['newtitle'])) {
		foreach($_GET['newtitle'] as $key => $value) {
			$newtitle1 = dhtmlspecialchars(trim($value));
			$newidentifier1 = trim($_GET['newidentifier'][$key]);
			if($newtitle1 && $newidentifier1) {
				if(in_array(strtoupper($newidentifier1), $mysql_keywords)) {
					cpmsg('threadtype_infotypes_optionvariable_iskeyword', '', 'error');
				}
				if(table_forum_typeoption::t()->fetch_all_by_identifier($newidentifier1, 0, 1) || strlen($newidentifier1) > 40 || !ispluginkey($newidentifier1)) {
					cpmsg('threadtype_infotypes_optionvariable_invalid', '', 'error');
				}
				$rules = [];
				if(str_starts_with($_GET['newtype'][$key], 'plugin/')) {
					$rules['pluginthreadtype'] = substr($_GET['newtype'][$key], 7);
					$_GET['newtype'][$key] = 'plugin';
				}
				$data = [
					'classid' => $_GET['classid'],
					'displayorder' => $_GET['newdisplayorder'][$key],
					'title' => $newtitle1,
					'identifier' => $newidentifier1,
					'type' => $_GET['newtype'][$key],
					'rules' => serialize($rules),
				];
				table_forum_typeoption::t()->insert($data);
			} elseif($newtitle1 && !$newidentifier1) {
				cpmsg('threadtype_infotypes_option_invalid', 'action=threadtypes&operation=typeoption&classid='.$_GET['classid'], 'error');
			}
		}
	}
	updatecache('threadsorts');
	cpmsg('threadtype_infotypes_succeed', 'action=threadtypes&operation=typeoption&classid='.$_GET['classid'], 'succeed');

}
	