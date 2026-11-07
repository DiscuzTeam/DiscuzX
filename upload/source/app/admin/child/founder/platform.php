<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$do = !in_array(getgpc('do'), ['list', 'edit', 'update']) ? 'list' : getgpc('do');
$default_platform = getglobal('cache/admin/default_platform') ?: 'system';

if($do == 'list') {
	showsubmenu('menu_platform');
	showtips('platform_list_tips');

	$adminscript = preg_replace('#\?platform=\w+#', '', ADMINSCRIPT);
	$platformlist = table_common_admincp_menu_platform::t()->fetch_all_data();
	// system 固定置顶，与其 displayorder 无关
	if(isset($platformlist['system'])) {
		$system = $platformlist['system'];
		unset($platformlist['system']);
		$platformlist = ['system' => $system] + $platformlist;
	}

	showformheader('founder&operation=platform&do=update');
	showtableheader(cplang('platform_list').'&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=founder&operation=platform&do=edit&id=new">[+'.cplang('platform_new').']</a>');
	showsubtitle(['', cplang('display_order'), cplang('platform_name'), cplang('platform_menu_items'), cplang('platform_op')]);

	foreach($platformlist as $menuData) {
		$menu = dunserialize($menuData['menu']);
		if(!is_array($menu)) {
			$menu = [];
		}
		$checkbox = !empty($menu['userdef']) ? '<input type="checkbox" name="delete[]" value="'.$menuData['platform'].'" class="checkbox">' : '';
		if($checkbox) {
			$hasdelete = true;
		}
		$name = !empty($menu['custom']['name']) ? $menu['custom']['name'] : ($menu['name'] ?? $menu['title'] ?? $menuData['platform']);
		$issystem = $menuData['platform'] == 'system';
		$isdefault = $default_platform == $menuData['platform'];
		$editurl = ADMINSCRIPT.'?action=founder&operation=platform&do=edit&id='.$menuData['platform'];
		$enterurl = preg_replace('/platform=\w+/', 'platform='.$menuData['platform'], $adminscript);
		$menucount = 0;
		foreach((array)($menu['menu'] ?? []) as $submenu) {
			$menucount += count((array)$submenu);
		}
		$tags = ['<span class="lightnum">('.$menuData['platform'].')</span>'];
		if($isdefault) {
			$tags[] = '<span class="bold">'.cplang('platform_default').'</span>';
		}
		$tags[] = '<span class="lightnum">'.($issystem ? cplang('platform_system') : cplang('platform_custom')).'</span>';

		$ops = '<a href="'.$editurl.'">'.cplang('edit').'</a>&nbsp;&nbsp;<a href="'.$enterurl.'" target="_blank">'.cplang('platform_enter').'</a>';
		if($issystem) {
			$ops .= '&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=founder&operation=platform&frames=yes&resetmenu=true" target="_parent" class="lightnum">['.cplang('platform_resetmenu').']</a>';
		}

		showtablerow('style="height:32px"', ['class="td25"', 'class="td25"', '', 'class="td25"', 'class="td26"'], [
			$checkbox,
			'<input type="text" name="displayorder['.$menuData['platform'].']" class="txt" style="width:60px;text-align:center" value="'.$menuData['displayorder'].'" />',
			'<a href="'.$editurl.'"'.($isdefault ? ' class="bold"' : '').'>'.dhtmlspecialchars($name).'</a>&nbsp;'.implode('&nbsp;', $tags),
			'<span class="lightnum">'.$menucount.'</span>',
			$ops
		]);
	}
	showsubmit('submit', 'submit', !empty($hasdelete) ? 'del' : '');
	showtablefooter();
	showformfooter();

} elseif($do == 'update') {

	if(!submitcheck('submit')) {
		cpmsg('undefined_action', '', 'error');
	}

	if(!empty($_GET['delete'])) {
		foreach(table_common_admincp_menu_platform::t()->fetch_all_data() as $menuData) {
			$menu = dunserialize($menuData['menu']);
			if(!empty($menu['userdef']) && in_array($menuData['platform'], $_GET['delete'])) {
				menu::platform_del($menuData['platform']);
			}
		}
	}
	if(!empty($_GET['displayorder'])) {
		foreach(table_common_admincp_menu_platform::t()->fetch_all_data() as $menuData) {
			table_common_admincp_menu_platform::t()->update($menuData['platform'],
				['displayorder' => $_GET['displayorder'][$menuData['platform']] ?? 0]);
		}

		// 以平台表为准重建缓存：旧写法从 $_G['cache']['admin']['platform'] 逐项回填，
		// 缓存里缺的平台会被写成 NULL 并 savecache，导致右上角「全部应用」出现空白入口
		menu::platform_refresh();
	}

	cpmsg('founder_platform_update_succeed', 'action=founder&operation=platform', 'succeed');

} elseif($do == 'edit') {
	if(!submitcheck('submit')) {

		if($_GET['id'] == 'new') {
			showchildmenu([['menu_platform', 'founder&operation=platform']], cplang('platform_new'));

			$value = menu::newTemplate;
			showtableheader('');
			showsetting('platform_id', 'newid', '', 'text');
		} else {
			$menuData = table_common_admincp_menu_platform::t()->fetch($_GET['id']);
			if(!$menuData) {
				cpmsg('undefined_action', '', 'error');
			}

			$menu = dunserialize($menuData['menu']);
			if(!empty($menu['custom'])) {
				$menu = $menu['custom'];
			}

			$enter = '<a href="'.$adminscript.'?platform='.$menuData['platform'].'" target="_blank">'.cplang('platform_enter').'</a>';

			showchildmenu([['menu_platform', 'founder&operation=platform']], $menu['name'].'('.$_GET['id'].')', [], $enter);

			showtips('platform_tips', 'tips', true, 'platform_tips_title');

			showformheader('founder&operation=platform&do=edit&id='.$_GET['id'], 'target="hframe"');

			require_once libfile('class/xml');
			$value = menu::array2menu($menu);
			$adminscript = preg_replace('#\?platform=\w+#', '', ADMINSCRIPT);
			showtableheader('');
		}

		echo '<tr style="height: 600px"><td colspan="2">';
		echo '<div id="ace_editor" style="width:98%;height: 90%;border:1px solid #cdcdcd;min-height:980px"></div>';
		echo '<textarea id="content" style="display:none;" name="content" spellcheck="false">'.dhtmlspecialchars($value).'</textarea></td>';
		echo '</td></tr>';
		showsubmit('submit', 'submit', '',
			($_GET['id'] != 'new' ? '<label><input name="del" class="checkbox" value="1" type="checkbox" />'.cplang('to_default').'</label>' : '').
			'<label><input name="default" class="checkbox" value="1"'.($default_platform == $_GET['id'] ? ' checked' : '').' type="checkbox" />'.cplang('platform_default').'</label>'
		);
		showtablefooter();
		showformfooter();
		echo '<iframe id="hframe" name="hframe" style="display: none"></iframe>';

		echo <<<EOF
<script src="static/ace/ace.js?date=7.2.0" type="text/javascript" charset="UTF-8"></script>
<style>.ace_print-margin {display: none}</style>
<script>
var cookiepre = '{$_G['config']['cookie']['cookiepre']}', cookiedomain = '{$_G['config']['cookie']['cookiedomain']}', cookiepath = '{$_G['config']['cookie']['cookiepath']}';
var editor = ace.edit("ace_editor");
if(getcookie('darkmode') == 'd')
{
	editor.setTheme("ace/theme/monokai");
}
editor.session.setMode("ace/mode/html");
editor.setOption("wrap", "free");
editor.setOption("wrapBehavioursEnabled", true);
editor.setOption("displayIndentGuides", true);
editor.setOption("useWorker", false);
editor.setValue(document.getElementById('content').value);
editor.getSession().on('change', function(e) {
    $('content').value = editor.getValue(); 
});
editor.gotoLine(0);
</script>
EOF;

	} else {
		if($_GET['id'] == 'new') {
			if(empty($_GET['newid']) || !preg_match('#^[\w_]+$#', $_GET['newid'])) {
				frame_cpmsg('platform_id_error', false);
			}
			if(table_common_admincp_menu_platform::t()->fetch($_GET['newid'])) {
				frame_cpmsg('platform_id_exists', false);
			}

			$_GET['id'] = $_GET['newid'];
			$menu = menu::menu2array(menu::newTemplate);
		} else {
			$menuData = table_common_admincp_menu_platform::t()->fetch($_GET['id']);
			if(!$menuData) {
				cpmsg('undefined_action', '', 'error');
			}
			$menu = dunserialize($menuData['menu']);
		}

		if(!empty($_GET['del'])) {
			unset($menu['custom']);
		} else {
			$menu['custom'] = menu::menu2array($_GET['content']);
		}

		menu::platform_add($_GET['id'], $menu, true);
		if($_GET['default']) {
			$_G['cache']['admin']['default_platform'] = $_GET['id'];
			savecache('admin', $_G['cache']['admin']);
		}

		frame_cpmsg('founder_platform_update_succeed', true, ADMINSCRIPT.'?action=founder&operation=platform&do=edit&id='.$_GET['id']);
	}
}
	