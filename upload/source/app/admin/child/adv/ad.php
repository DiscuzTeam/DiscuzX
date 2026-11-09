<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('advsubmit')) {

	shownav('extended', 'adv_admin');
	$type = $_GET['type'];
	$target = $_GET['target'];
	$typeadd = $advfile = '';
	if($type) {
		$etype = explode(':', $type);
		if(count($etype) > 1 && preg_match('/^[\w\_:]+$/', $type)) {
			if(ispluginkey($etype[0]) && preg_match('/^\w$/', $etype[1])) {
				$advfile = DISCUZ_PLUGIN($etype[0]).'/adv/adv_'.$etype[1].'.php';
				$advclass = 'adv_'.$etype[1];
			}
		} else {
			$advfile = libfile('adv/'.$type, 'class');
			$advclass = 'adv_'.$type;
		}
		if($advfile && file_exists($advfile)) {
			require_once $advfile;
			$advclassv = new $advclass();
			if(class_exists($advclass)) {
				$advsetting = $advclassv->getsetting();
				$typeadd = lang('adv/'.$type, $advclassv->name);
				if($type == 'custom') {
					$typeadd .= ' '.$advclassv->customname;
				}
			}
		}
		showchildmenu([['adv_admin', 'adv']], $typeadd);
	} else {
		showsubmenu('adv_admin', [
			['adv_admin_list', 'adv&operation=list', 0],
			['adv_admin_listall', 'adv&operation=ad', 1],
			['adv_admin_setting', 'adv&operation=setting', 0],
		]);
	}

	showformheader('adv&operation=ad');
	showtableheader('', 'fixpadding');
	showsubtitle(['', 'display_order', 'available', 'subject', !$type ? 'type' : '', 'adv_style', 'start_time', 'end_time', 'adv_targets', '']);

	$advppp = $type != 'custom' ? 25 : 9999;
	$conditions = '';
	$order_by = 'displayorder, advid DESC, targets DESC';
	$start_limit = ($page - 1) * $advppp;

	$title = dhtmlspecialchars(trim($_GET['title']));
	$starttime = dhtmlspecialchars($_GET['starttime']);
	$endtime = dhtmlspecialchars($_GET['endtime']);
	$orderby = dhtmlspecialchars($_GET['orderby']);

	$advnum = table_common_advertisement::t()->count_search($title, $starttime, $endtime, $type, $target);

	if(!$type) {
		$customadv = [];
		foreach(table_common_advertisement_custom::t()->fetch_all_data() as $custom) {
			$customadv[$custom['id']] = $custom['name'];
		}
	}

	$typenames = [];
	foreach(table_common_advertisement::t()->fetch_all_search($title, $starttime, $endtime, $type, $target, $orderby, $start_limit, $advppp) as $adv) {
		if(!$type) {
			$advfile = '';
			$etype = explode(':', $adv['type']);
			if(count($etype) > 1 && preg_match('/^[\w\_:]+$/', $adv['type'])) {
				$advfile = DISCUZ_PLUGIN($etype[0]).'/adv/adv_'.$etype[1].'.php';
				$advclass = 'adv_'.$etype[1];
			} else {
				$advfile = libfile('adv/'.$adv['type'], 'class');
				$advclass = 'adv_'.$adv['type'];
			}
			if(!$advfile || !file_exists($advfile)) {
				continue;
			}
			if(!isset($typenames[$adv['type']])) {
				require_once $advfile;
				if(class_exists($advclass)) {
					$advclassv = new $advclass();
					$typenames[$adv['type']] = lang('adv/'.$adv['type'], $advclassv->name);
				} else {
					$typenames[$adv['type']] = $adv['type'];
				}
			}
		}
		$adv['parameters'] = dunserialize($adv['parameters']);
		if($adv['type'] == 'custom' && $type && $_GET['customid'] != $adv['parameters']['extra']['customid']) {
			continue;
		}
		$targets = [];
		foreach(explode("\t", $adv['targets']) as $t) {
			if('adv_edit_targets_'.$t != 'adv_edit_targets_custom') {
				$targets[] = $lang['adv_edit_targets_'.$t] ? $lang['adv_edit_targets_'.$t] : $t;
			}
		}

		showtablerow('', ['class="td25"', 'class="td25"', 'class="td25"'], [
			"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"{$adv['advid']}\">",
			"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[{$adv['advid']}]\" value=\"{$adv['displayorder']}\">",
			"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[{$adv['advid']}]\" value=\"1\" ".($adv['available'] ? 'checked' : '').'>',
			"<input type=\"text\" class=\"txt\" size=\"15\" name=\"titlenew[{$adv['advid']}]\" value=\"".dhtmlspecialchars($adv['title'])."\">",
			!$type ? '<a href="'.ADMINSCRIPT.'?action=adv&operation=ad&type='.$adv['type'].($adv['type'] != 'custom' ? '' : '&customid='.$adv['parameters']['extra']['customid']).'">'.$typenames[$adv['type']].($adv['type'] != 'custom' ? '' : ' '.$customadv[$adv['parameters']['extra']['customid']]).'</a>' : '',
			$lang['adv_style_'.$adv['parameters']['style']],
			$adv['starttime'] ? dgmdate($adv['starttime'], 'd') : $lang['unlimited'],
			$adv['endtime'] ? dgmdate($adv['endtime'], 'd') : $lang['unlimited'],
			$adv['type'] != 'custom' ? implode(', ', $targets) : $lang['custom'],
			"<a href=\"".ADMINSCRIPT."?action=adv&operation=edit&advid={$adv['advid']}".($adv['type'] != 'custom' ? '' : '&customid='.$adv['parameters']['extra']['customid']).(!$type ? '&from=all' : '')."\" class=\"act\">{$lang['edit']}</a>"
		]);
	}

	$multipage = multi($advnum, $advppp, $page, ADMINSCRIPT.'?action=adv&operation=ad'.($type ? '&type='.rawurlencode($type) : '').($target ? '&target='.rawurlencode($target) : '').($title ? '&title='.rawurlencode($title) : '').($starttime ? "&starttime=$starttime" : '').($endtime ? "&endtime=$endtime" : '').($orderby ? "&orderby=$orderby" : ''), 0, 3, TRUE, TRUE);

	$starttimecheck = [$starttime => 'selected="selected"'];
	$endtimecheck = [$endtime => 'selected="selected"'];
	$orderbycheck = [$orderby => 'selected="selected"'];

	$targetselect = '<select name="target"><option value="">'.$lang['adv_targets'].'</option>';
	foreach($defaulttargets as $v) {
		$targetselect .= '<option value="'.$v.'"'.($v == $target ? ' selected="selected"' : '').'>'.$lang['adv_edit_targets_'.$v].'</option>';
	}
	$targetselect .= '</select>';

	showsubmit('advsubmit', 'submit', 'del', $type ? '<input type="button" class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=adv&operation=add&type='.$_GET['type'].($_GET['type'] != 'custom' ? '' : '&customid='.$_GET['customid']).'\'" value="'.cplang('add').'" />' : '', $multipage.'
<input type="text" class="txt" name="title" value="'.$title.'" size="15" onkeyup="if(event.keyCode == 13) this.form.searchsubmit.click()" onclick="this.value=\'\'"> &nbsp;&nbsp;
<select name="starttime">
<option value=""> '.cplang('start_time').'</option>
<option value="0" '.$starttimecheck['0'].'> '.cplang('all').'</option>
<option value="-1" '.$starttimecheck['-1'].'> '.cplang('nolimit').'</option>
<option value="86400" '.$starttimecheck['86400'].'> '.cplang('1_day').'</option>
<option value="604800" '.$starttimecheck['604800'].'> '.cplang('7_day').'</option>
<option value="2592000" '.$starttimecheck['2592000'].'> '.cplang('30_day').'</option>
<option value="7776000" '.$starttimecheck['7776000'].'> '.cplang('90_day').'</option>
<option value="15552000" '.$starttimecheck['15552000'].'> '.cplang('180_day').'</option>
<option value="31536000" '.$starttimecheck['31536000'].'> '.cplang('365_day').'</option>
</select> &nbsp;&nbsp;
<select name="endtime">
<option value=""> '.cplang('end_time').'</option>
<option value="0" '.$endtimecheck['0'].'> '.cplang('all').'</option>
<option value="-1" '.$endtimecheck['-1'].'> '.cplang('nolimit').'</option>
<option value="86400" '.$endtimecheck['86400'].'> '.cplang('1_day').'</option>
<option value="604800" '.$endtimecheck['604800'].'> '.cplang('7_day').'</option>
<option value="2592000" '.$endtimecheck['2592000'].'> '.cplang('30_day').'</option>
<option value="7776000" '.$endtimecheck['7776000'].'> '.cplang('90_day').'</option>
<option value="15552000" '.$endtimecheck['15552000'].'> '.cplang('180_day').'</option>
<option value="31536000" '.$endtimecheck['31536000'].'> '.cplang('365_day').'</option>
</select> &nbsp;&nbsp;
<select name="orderby">
<option value=""> '.cplang('adv_orderby').'</option>
<option value="starttime" '.$orderbycheck['starttime'].'> '.cplang('adv_addtime').'</option>
'.(!$type ? '<option value="type" '.$orderbycheck['type'].'> '.cplang('adv_type').'</option>' : '').'
<option value="displayorder" '.$orderbycheck['displayorder'].'> '.cplang('display_order').'</option>
</select> &nbsp;&nbsp;
'.$targetselect.' &nbsp;&nbsp;
<input type="button" class="btn" name="searchsubmit" value="'.cplang('search').'" onclick="if(this.form.title.value==\''.cplang('adv_inputtitle').'\'){this.form.title.value=\'\'}location.href=\''.ADMINSCRIPT.'?action=adv&operation=ad'.($type ? '&type='.rawurlencode($type) : '').'&title=\'+this.form.title.value+\'&starttime=\'+this.form.starttime.value+\'&endtime=\'+this.form.endtime.value+\'&target=\'+this.form.target.value+\'&orderby=\'+this.form.orderby.value;"> &nbsp;
		');
	showtablefooter();
	showformfooter();

} else {

	if($_GET['delete']) {
		table_common_advertisement::t()->delete($_GET['delete']);
	}

	if(is_array($_GET['titlenew'])) {
		foreach($_GET['titlenew'] as $advid => $title) {
			table_common_advertisement::t()->update($advid, [
				'available' => $_GET['availablenew'][$advid],
				'displayorder' => $_GET['displayordernew'][$advid],
				'title' => cutstr($_GET['titlenew'][$advid], 50)
			]);
		}
	}

	updatecache('advs');
	updatecache('setting');

	cpmsg('adv_update_succeed', dreferer(), 'succeed');

}
	