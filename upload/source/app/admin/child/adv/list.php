<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

shownav('extended', 'adv_admin');
showsubmenu('adv_admin', [
	['adv_admin_list', 'adv&operation=list', 1],
	['adv_admin_listall', 'adv&operation=ad', 0],
	['adv_admin_setting', 'adv&operation=setting', 0],
]);
/*search={"adv_admin":"action=adv","adv_admin_list":"action=adv&operation=list"}*/
showtips('adv_list_tip');
/*search*/

$advs = getadvs();
showtableheader('', 'fixpadding');

echo '<tr><td colspan="4">'.$lang['adv_targets'].': &nbsp;&nbsp; ';
foreach($defaulttargets as $target) {
	echo '<a href="'.ADMINSCRIPT.'?action=adv&operation=ad&target='.$target.'">'.$lang['adv_edit_targets_'.$target].'</a> &nbsp;&nbsp; ';
}

$row = 4;
$rowwidth = 1 / $row * 100;
$customadv = $ads = [];
$tmp = $advs['adv_custom.php'];
unset($advs['adv_custom.php']);
$advs['adv_custom.php'] = $tmp;
foreach(table_common_advertisement::t()->fetch_all_type() as $ad) {
	$ads[$ad['type']] = $ad['count'];
}
foreach(table_common_advertisement::t()->fetch_all_by_type('custom') as $ad) {
	$parameters = dunserialize($ad['parameters']);
	$ads['custom_'.$parameters['extra']['customid']]++;
}
if($advs) {
	$i = $row;
	foreach($advs as $adv) {
		if($i == $row) {
			echo '<tr>';
		}
		if($adv['class'] == 'custom') {
			$customadv = $adv;
			$img = file_exists(DISCUZ_ROOT.'./static/image/admincp/'.$customadv['class'].'.gif') ? '<img src="static/image/admincp/'.$customadv['class'].'.gif" /><br />' : '';
			echo '<td width="'.$rowwidth.'%" class="hover" align="center">';
			echo $img.$lang['adv_custom_add'];
			showformheader('adv&operation=custom&do=add');
			echo '<input name="addcustom" class="txt" /><input name="submit" class="btn" type="submit" value="'.$lang['submit'].'" />';
			showformfooter();
			echo '</td>';
		} else {
			echo '<td width="'.$rowwidth.'%" class="hover" align="center"><a href="'.ADMINSCRIPT.'?action=adv&operation=ad&type='.$adv['class'].'">';
			$eclass = explode(':', $adv['class']);
			if(count($eclass) > 1) {
				echo file_exists(DISCUZ_PLUGIN($eclass[0]).'/adv/adv_'.$eclass[1].'.gif') ? '<img src="source/plugin/'.$eclass[0].'/adv/adv_'.$eclass[1].'.gif" /><br />' : '';
			} else {
				echo file_exists(DISCUZ_ROOT.'./static/image/admincp/'.$adv['class'].'.gif') ? '<img src="static/image/admincp/'.$adv['class'].'.gif" /><br />' : '';
			}
			echo $adv['name'].($ads[$adv['class']] ? '('.$ads[$adv['class']].')' : '').($adv['filemtime'] > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : '');
			echo '</a></td>';
		}
		$i--;
		if(!$i) {
			$i = $row;
		}
	}
	if($i != $row) {
		echo str_repeat('<td></td>', $i);
	}
} else {
	showtablerow('', '', $lang['adv_nonexistence']);
}
if($customadv) {
	$img = file_exists(DISCUZ_ROOT.'./static/image/admincp/'.$customadv['class'].'.gif') ? '<img src="static/image/admincp/'.$customadv['class'].'.gif" /><br />' : '';
	$i = $row;
	foreach(table_common_advertisement_custom::t()->fetch_all_data() as $custom) {
		if($i == $row) {
			echo '<tr>';
		}
		echo '<td width="'.$rowwidth.'%" class="hover" align="center"><div id="op_'.$custom['id'].'"><a href="'.ADMINSCRIPT.'?action=adv&operation=ad&type='.$customadv['class'].'&customid='.$custom['id'].'">';
		echo $img.$lang['adv_custom'].' '.dhtmlspecialchars($custom['name'], ENT_QUOTES).($ads['custom_'.$custom['id']] ? '('.$ads['custom_'.$custom['id']].')' : '');
		echo '</a><br /><div class="right">';
		echo '<a onclick="ajaxget(this.href, \'op_'.$custom['id'].'\');return false;" href="'.ADMINSCRIPT.'?action=adv&operation=custom&do=edit&id='.$custom['id'].'">'.$lang['edit'].'</a>&nbsp;';
		echo '<a onclick="ajaxget(this.href, \'op_'.$custom['id'].'\');return false;" href="'.ADMINSCRIPT.'?action=adv&operation=custom&do=delete&id='.$custom['id'].'">'.$lang['delete'].'</a>';
		echo '</div></div></td>';
		$i--;
		if(!$i) {
			$i = $row;
		}
	}
	if($i != $row) {
		echo str_repeat('<td></td>', $i);
	}
}
echo '<tr>'.str_repeat('<td width="'.$rowwidth.'%"></td>', $row).'</tr>';
showtablefooter();
	