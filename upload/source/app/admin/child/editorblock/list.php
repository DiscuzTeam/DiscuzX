<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$lpp = empty($_GET['lpp']) ? 20 : $_GET['lpp'];
$start = ($page - 1) * $lpp;

if(!submitcheck('editorblocksubmit')) {

	shownav('style', 'setting_editor');

	showsubmenu('setting_editor', [
		['setting_editor_global', 'setting&operation=editor', 0],
		['setting_editor_code', 'misc&operation=bbcode', 0],
		['setting_editor_media', 'misc&operation=mediacode', 0],
		['setting_editor_block', 'editorblock&operation=list', 1]
	]);
	showtips('editorblock_admin_list_tips');
	showformheader("editorblock&operation=$operation");
	showtableheader('', 'fixpadding');
	showsubtitle(['del', 'order', 'name', 'available', 'columns', 'type', 'jsfilename', '']);

	$flag = false;

	$classnames = [];
	$avaliableeditorblock = $alleditorblock = geteditorblocks();
	foreach(table_common_editorblock::t()->fetch_all_block_sort_id() as $editorblock) {
		$editorblockfile = '';
		if(!empty($editorblock['plugin'])) {
			$key = 'editorblock_'.$editorblock['class'].'.php';
			if(array_key_exists($key, $avaliableeditorblock)) {
				$editorblockfile = DISCUZ_PLUGIN($editorblock['plugin']).'/editorblock/editorblock_'.$editorblock['class'].'.php';
				$editorblockclass = 'editorblock_'.$editorblock['class'];
				unset($avaliableeditorblock[$key]);
			} else {
				table_common_editorblock::t()->delete($editorblock['blockid']);
				// table_common_editorblock::t()->update($editorblock['blockid'], array('available' => 0));
				$flag = true;
				continue;
			}
		} else {
			$key = 'editorblock_'.$editorblock['class'].'.php';
			if(array_key_exists($key, $avaliableeditorblock)) {
				$editorblockfile = libfile('editorblock/'.$editorblock['class'], 'class');
				$editorblockclass = 'editorblock_'.$editorblock['class'];
				unset($avaliableeditorblock[$key]);
			} else {
				table_common_editorblock::t()->delete($editorblock['blockid']);
				// table_common_editorblock::t()->update($editorblock['blockid'], array('available' => 0));
				$flag = true;
				continue;
			}
		}
		if(!file_exists($editorblockfile)) {
			table_common_editorblock::t()->delete($editorblock['blockid']);
			continue;
		}
		if(!empty($classnames[$editorblock['class']])) {
			require_once $editorblockfile;
			if(class_exists($editorblockclass)) {
				$editorblockclassv = new $editorblockclass();
				$classnames[$editorblock['class']] = lang('editorblock/'.$editorblock['class'], $editorblockclassv->name);
			} else {
				$classnames[$editorblock['class']] = $editorblock['class'];
			}
		}
	}
	// 如果有新增加的文件, 需要添加到列表内
	if(count($avaliableeditorblock) > 0) {
		foreach($avaliableeditorblock as $editorblock) {
			$arr = [
				'type' => $editorblock['type'],
				'class' => $editorblock['class'],
				'sort' => 0,
				'name' => $editorblock['name'],
				'available' => $editorblock['available'],
				'columns' => $editorblock['columns'],
				'parser' => $editorblock['parser'],
				'style' => $editorblock['style'],
				'filename' => $editorblock['filename'],
				'config' => $editorblock['config'],
				'identifier' => $editorblock['identifier'],
				'description' => $editorblock['description'],
				'filemtime' => $editorblock['filemtime'],
				'version' => $editorblock['version'],
				'plugin' => $editorblock['plugin'],
				'copyright' => $editorblock['copyright']
			];
			table_common_editorblock::t()->insert($arr);
			memory('set', 'editorblock_'.$editorblock['class'], $arr);

			if($editorblock['global_css']) {
				$settings = [
					'editor_global_css' => ($_G['setting']['editor_global_css'] ?? '').$editorblock['style'],
				];
				table_common_setting::t()->update_batch($settings);
				updatecache('setting');
			}
			$flag = true;
		}
	}
	if($flag) {
		//header("Location: ".ADMINSCRIPT."?action=editorblock&operation=$operation");
	}

	$blocks = [];
	$blockscount = table_common_editorblock::t()->count_all_blocks();
	$blocks = table_common_editorblock::t()->fetch_all_blocks($start, $lpp);
	foreach($blocks as $k => $editorblock) {
		if(empty($editorblock['type'])) {
			$editorblock['type'] = 0;
		}
		$key_class = 'editorblock_'.$editorblock['class'].'.php';
		showtablerow('', ['class="td25"', 'class="td25"', 'class="td31"', 'class="td25"', 'class="td31"', 'class="td25"', 'class="td31"', ''], [
			"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"{$editorblock['blockid']}\">",
			"<input type=\"text\" class=\"txt\" size=\"2\" name=\"sortnew[{$editorblock['blockid']}]\" value=\"{$editorblock['sort']}\">",
			(empty($editorblock['plugin']) ? '<em class="lightnum">['.$lang['inbuilt'].']</em> '.dhtmlspecialchars($editorblock['name']) : ' <em class="diffcolor3">['.$lang['plugin'].']</em> '.dhtmlspecialchars($editorblock['name'])).
			' '.(dhtmlspecialchars($editorblock['version']).($alleditorblock[$key_class]['version'] > $editorblock['version'] ? ' <strong style="color: red;"> -> <a href=\''.ADMINSCRIPT.'?action=editorblock&operation=update&blockid='.$editorblock['blockid'].'&lpp='.$lpp.'&page='.$page.'\' style="color: red;">'.$alleditorblock[$key_class]['version'].'</a></strong>' : '')),
			"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[{$editorblock['blockid']}]\" value=\"1\" ".($editorblock['available'] ? 'checked' : '').'>',
			"<input class=\"checkbox\" type=\"checkbox\" name=\"columnsnew[{$editorblock['blockid']}]\" value=\"1\" ".($editorblock['columns'] ? 'checked' : '').'>',
			//
			//dhtmlspecialchars($editorblock['identifier']),
			cplang('editorblock_type_message_'.$editorblock['type']),
			//dhtmlspecialchars($editorblock['description']),
			//$editorblock['copyright'],
			dhtmlspecialchars($editorblock['filename']),
			"<div style=\"display: flex; flex-wrap: wrap; gap: 10px;\">
			<a href=\"".ADMINSCRIPT."?action=editorblock&operation=parser&blockid={$editorblock['blockid']}\" class=\"act\">{$lang['parser']}</a>
			<a href=\"".ADMINSCRIPT."?action=editorblock&operation=parser&blockid={$editorblock['blockid']}&type=1\" class=\"act\">{$lang['css']}</a>
			<a href=\"".ADMINSCRIPT."?action=editorblock&operation=parser&blockid={$editorblock['blockid']}&type=2\" class=\"act\">Config</a>
			</div>"
		]);
		showtablerow('', ['class="td25"', 'colspan="7" class="xg1"'], ['',
			dhtmlspecialchars($editorblock['description'])
		]);
	}

	$multipage = multi($blockscount, $lpp, $page, ADMINSCRIPT."?action=editorblock&operation=$operation&lpp=$lpp", 0, 3);
	showsubmit('editorblocksubmit', 'submit', '', '<a href="'.ADMINSCRIPT.'?action=editorblock&operation=global_css">'.$lang["global_css"].'</a>', $multipage);

	showtablefooter();
	showformfooter();
} else {

	if($_GET['delete']) {
		table_common_editorblock::t()->delete($_GET['delete']);
	}

	if(is_array($_GET['sortnew'])) {
		foreach($_GET['sortnew'] as $blockid => $title) {
			table_common_editorblock::t()->update($blockid, [
				'available' => $_GET['availablenew'][$blockid],
				'columns' => $_GET['columnsnew'][$blockid],
				'sort' => $_GET['sortnew'][$blockid],
			]);
		}
	}

	updatecache('setting');

	cpmsg('operation_succeed', dreferer(), 'succeed');

}
	