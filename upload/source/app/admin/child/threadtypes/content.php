<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('searchsortsubmit', 1) && !submitcheck('delsortsubmit') && !submitcheck('sendpmsubmit')) {

	shownav('forum', 'threadtype_infotypes');
	showsubmenu('threadtype_infotypes', [
		['threadtype_infotypes_type', 'threadtypes', 0],
		['threadtype_infotypes_content', 'threadtypes&operation=content', 1],
		['threadtype_infotypes_class', 'threadtypes&operation=class', 0],
		[['menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu]]
	]);

	require_once libfile('function/post');

	$_GET['sortid'] = intval($_GET['sortid']);
	$threadtypes = '<select name="sortid" onchange="window.location.href = \''.ADMINSCRIPT.'?action=threadtypes&operation=content&sortid=\'+ this.options[this.selectedIndex].value"><option value="0">'.cplang('none').'</option>';
	$query = table_forum_threadtype::t()->fetch_all_for_order();
	foreach($query as $type) {
		$name = messagecutstr($type['name'], 0, '');
		$threadtypes .= '<option value="'.$type['typeid'].'" '.($_GET['sortid'] == $type['typeid'] ? 'selected="selected"' : '').'>'.dhtmlspecialchars($name).'</option>';
	}
	$threadtypes .= '</select>';

	showformheader('threadtypes&operation=content');
	showtableheader('threadtype_content_choose');
	showsetting('threadtype_content_name', '', '', $threadtypes);

	if($_GET['sortid']) {
		showtableheader('threadtype_content_sort_by_conditions');
		loadcache(['threadsort_option_'.$_GET['sortid']]);

		$sortoptionarray = $_G['cache']['threadsort_option_'.$_GET['sortid']];
		if(is_array($sortoptionarray)) foreach($sortoptionarray as $optionid => $option) {
			$optionshow = '';
			if($option['search']) {
				if(in_array($option['type'], ['radio', 'checkbox', 'select'])) {
					if($option['type'] == 'select') {
						$optionshow .= '<select name="searchoption['.$optionid.'][value]"><option value="0">'.cplang('unlimited').'</option>';
						foreach($option['choices'] as $id => $value) {
							$optionshow .= '<option value="'.$id.'" '.($_GET['searchoption'][$optionid]['value'] == $id ? 'selected="selected"' : '').'>'.$value.'</option>';
						}
						$optionshow .= '</select><input type="hidden" name="searchoption['.$optionid.'][type]" value="select">';
					} elseif($option['type'] == 'radio') {
						$optionshow .= '<input type="radio" class="radio" name="searchoption['.$optionid.'][value]" value="0" checked="checked"]>'.cplang('unlimited').'&nbsp;';
						foreach($option['choices'] as $id => $value) {
							$optionshow .= '<input type="radio" class="radio" name="searchoption['.$optionid.'][value]" value="'.$id.'" '.($_GET['searchoption'][$optionid]['value'] == $id ? 'checked="checked"' : '').'> '.$value.' &nbsp;';
						}
						$optionshow .= '<input type="hidden" name="searchoption['.$optionid.'][type]" value="radio">';
					} elseif($option['type'] == 'checkbox') {
						foreach($option['choices'] as $id => $value) {
							$optionshow .= '<input type="checkbox" class="checkbox" name="searchoption['.$optionid.'][value]['.$id.']" value="'.$id.'" '.($_GET['searchoption'][$optionid]['value'] == $id ? 'checked="checked"' : '').'> '.$value.'';
						}
						$optionshow .= '<input type="hidden" name="searchoption['.$optionid.'][type]" value="checkbox">';
					}
				} elseif(in_array($option['type'], ['number', 'text', 'email', 'calendar', 'image', 'url', 'textarea', 'upload', 'range'])) {
					if($option['type'] == 'calendar') {
						$optionshow .= '<script type="text/javascript" src="'.$_G['setting']['jspath'].'calendar.js?'.$_G['style']['verhash'].'"></script><input type="text" name="searchoption['.$optionid.'][value]" class="txt" value="'.$_GET['searchoption'][$optionid]['value'].'" onclick="showcalendar(event, this, false)" />';
					} elseif($option['type'] == 'number') {
						$optionshow .= '<select name="searchoption['.$optionid.'][condition]">
								<option value="0" '.($_GET['searchoption'][$optionid]['condition'] == 0 ? 'selected="selected"' : '').'>'.cplang('equal_to').'</option>
								<option value="1" '.($_GET['searchoption'][$optionid]['condition'] == 1 ? 'selected="selected"' : '').'>'.cplang('more_than').'</option>
								<option value="2" '.($_GET['searchoption'][$optionid]['condition'] == 2 ? 'selected="selected"' : '').'>'.cplang('lower_than').'</option>
							</select>&nbsp;&nbsp;
							<input type="text" class="txt" name="searchoption['.$optionid.'][value]" value="'.$_GET['searchoption'][$optionid]['value'].'" />
							<input type="hidden" name="searchoption['.$optionid.'][type]" value="number">';
					} elseif($option['type'] == 'range') {
						$optionshow .= '<input type="text" name="searchoption['.$optionid.'][value][min]" size="16" value="'.$_GET['searchoption'][$optionid]['value']['min'].'" /> -
							<input type="text" name="searchoption['.$optionid.'][value][max]" size="16" value="'.$_GET['searchoption'][$optionid]['value']['max'].'" />
							<input type="hidden" name="searchoption['.$optionid.'][type]" value="range">';
					} else {
						$optionshow .= '<input type="text" name="searchoption['.$optionid.'][value]" class="txt" value="'.$_GET['searchoption'][$optionid]['value'].'" />';
					}
				}
				$optionshow .= '&nbsp;'.$option['unit'];
				showsetting($option['title'], '', '', $optionshow);
			}
		}
	}

	showsubmit('searchsortsubmit', 'submit');
	showtablefooter();
	showformfooter();

} else {

	if(submitcheck('searchsortsubmit', 1)) {

		if(empty($_GET['searchoption']) && !$_GET['sortid']) {
			cpmsg('threadtype_content_no_choice', 'action=threadtypes&operation=content', 'error');
		}
		$mpurl = ADMINSCRIPT.'?action=threadtypes&operation=content&sortid='.$_GET['sortid'].'&searchsortsubmit=true';
		if(!is_array($_GET['searchoption'])) {
			$mpurl .= '&searchoption='.$_GET['searchoption'];
			$_GET['searchoption'] = dunserialize(base64_decode($_GET['searchoption']));
		} else {
			$mpurl .= '&searchoption='.base64_encode(serialize($_GET['searchoption']));
		}

		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', [
			['threadtype_infotypes_type', 'threadtypes', 0],
			['threadtype_infotypes_content', 'threadtypes&operation=content', 1],
			['threadtype_infotypes_class', 'threadtypes&operation=class', 0],
			[['menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu]]
		]);

		loadcache('forums');
		loadcache(['threadsort_option_'.$_GET['sortid']]);
		require_once libfile('function/threadsort');
		sortthreadsortselectoption($_GET['sortid']);
		$sortoptionarray = $_G['cache']['threadsort_option_'.$_GET['sortid']];
		$selectsql = '';
		if($_GET['searchoption']) {
			foreach($_GET['searchoption'] as $optionid => $option) {
				$fieldname = $sortoptionarray[$optionid]['identifier'] ? $sortoptionarray[$optionid]['identifier'] : 1;
				if(!preg_match('/^\w+$/', (string)$fieldname)) {
					continue;
				}
				if($option['value']) {
					if(in_array($option['type'], ['number', 'radio'])) {
						$option['value'] = intval($option['value']);
						$exp = '=';
						if($option['condition']) {
							$exp = $option['condition'] == 1 ? '>' : '<';
						}
						$sql = "$fieldname$exp'{$option['value']}'";
					} elseif($option['type'] == 'select') {
						$subvalues = $currentchoices = [];
						if(!empty($sortoptionarray)) {
							foreach($sortoptionarray as $subkey => $subvalue) {
								if($subvalue['identifier'] == $fieldname) {
									$currentchoices = $subvalue['choices'];
									break;
								}
							}
						}
						if(!empty($currentchoices)) {
							foreach($currentchoices as $subkey => $subvalue) {
								if(preg_match('/^'.$option['value'].'/i', $subkey)) {
									$subvalues[] = $subkey;
								}
							}
						}
						$sql = "$fieldname IN (".dimplode($subvalues).')';
					} elseif($option['type'] == 'checkbox') {
						$sql = "$fieldname LIKE '%".addslashes(implode('%', (array)$option['value']))."%'";
					} elseif($option['type'] == 'range') {
						$sql = $option['value']['min'] || $option['value']['max'] ? "$fieldname BETWEEN ".intval($option['value']['min']).' AND '.intval($option['value']['max']).'' : '';
					} else {
						$sql = "$fieldname LIKE '%".addslashes((string)$option['value'])."%'";
					}
					$selectsql .= $and."$sql ";
					$and = 'AND ';
				}
			}

			$selectsql = trim($selectsql);
			$searchtids = table_forum_optionvalue::t()->fetch_all_tid($_GET['sortid'], $selectsql ? 'WHERE '.$selectsql : '');
		}

		if($searchtids) {
			$lpp = max(5, empty($_GET['lpp']) ? 50 : intval($_GET['lpp']));
			$start_limit = ($page - 1) * $lpp;

			$threadcount = table_forum_thread::t()->count_by_tid_fid($searchtids);
			if($threadcount) {
				foreach(table_forum_thread::t()->fetch_all_by_tid($searchtids, $start_limit, $lpp) as $thread) {
					$threads .= showtablerow('', ['class="td25"', '', '', 'class="td28"', 'class="td28"'], [
						"<input class=\"checkbox\" type=\"checkbox\" name=\"tidsarray[]\" value=\"{$thread['tid']}\"/>".
						"<input type=\"hidden\" name=\"fidsarray[]\" value=\"{$thread['fid']}\"/>",
						"<a href=\"forum.php?mod=viewthread&tid={$thread['tid']}\" target=\"_blank\">{$thread['subject']}</a>",
						"<a href=\"forum.php?mod=forumdisplay&fid={$thread['fid']}\" target=\"_blank\">{$_G['cache']['forums'][$thread['fid']]['name']}</a>",
						"<a href=\"home.php?mod=space&uid={$thread['authorid']}\" target=\"_blank\">{$thread['author']}</a>",
						$thread['replies'],
						$thread['views'],
						dgmdate($thread['lastpost'], 'd'),
					], TRUE);
				}

				$multipage = multi($threadcount, $lpp, $page, $mpurl, 0, 3);
			}
		}

		showformheader('threadtypes&operation=content');
		showtableheader('admin', 'fixpadding');
		showsubtitle(['', 'subject', 'forum', 'author', 'threads_replies', 'threads_views', 'threads_lastpost']);
		echo $threads;
		echo $multipage;
		showsubmit('', '', '', "<input type=\"submit\" class=\"btn\" name=\"delsortsubmit\" value=\"{$lang['threadtype_content_delete']}\"/>");
		showtablefooter();
		showformfooter();

	} elseif(submitcheck('delsortsubmit')) {

		require_once libfile('function/post');

		if($_GET['tidsarray']) {
			require_once libfile('function/delete');
			deletethread($_GET['tidsarray']);

			if($_G['setting']['globalstick']) {
				updatecache('globalstick');
			}

			if($_GET['fidsarray']) {
				foreach(explode(',', $_GET['fidsarray']) as $fid) {
					updateforumcount(intval($fid));
				}
			}
		}
		cpmsg('threadtype_content_delete_succeed', 'action=threadtypes&operation=content', 'succeed');

	}
}
	