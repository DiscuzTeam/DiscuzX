<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!$do) {

	if(!submitcheck('submit')) {

		shownav('style', 'nav_setting_customnav');
		showsubmenu('nav_setting_customnav', $navdata);

		showformheader('nav&operation=mnav');
		showtableheader();
		showsubtitle(['', 'display_order', 'icon', 'name', 'url', 'type', 'nav_mnav_is_post', 'available', '']);

		$navlist = [];
		foreach(table_common_nav::t()->fetch_all_by_navtype(6) as $nav) {
			$navlist[$nav['id']] = $nav;
		}

		foreach($navlist as $nav) {
			showtablerow('', ['class="td25"', 'class="td25"', 'class="td25"', '', '', '', 'class="td25"', 'class="td25"', ''], [
				in_array($nav['type'], ['2', '1']) ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"{$nav['id']}\">" : '<input type="checkbox" class="checkbox" value="" disabled="disabled" />',
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[{$nav['id']}]\" value=\"{$nav['displayorder']}\">",
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"iconnew[{$nav['id']}]\" value=\"".dhtmlspecialchars($nav['icon'])."\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[{$nav['id']}]\" value=\"".dhtmlspecialchars($nav['name'])."\">",
				$nav['type'] == '0' ? $nav['url'] : "<input type=\"text\" class=\"txt\" size=\"15\" name=\"urlnew[{$nav['id']}]\" value=\"".dhtmlspecialchars($nav['url'])."\">",
				cplang($nav['type'] == '0' ? 'inbuilt' : ($nav['type'] == '3' ? 'nav_plugin' : ($nav['type'] == '4' ? 'channel' : 'custom'))),
				"<input name=\"is_postnew\" class=\"radio\" type=\"radio\" value=\"{$nav['id']}\" ".($nav['identifier'] == 'post' ? 'checked' : '').'>',
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[{$nav['id']}]\" value=\"1\" ".($nav['available'] ? 'checked' : '').'>',
				"<a href=\"".ADMINSCRIPT."?action=nav&operation=mnav&do=edit&id={$nav['id']}\" class=\"act\">{$lang['edit']}</a>"
			]);
		}
		echo '<tr><td colspan="1"></td><td colspan="9"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.$lang['nav_mnav_add'].'</a></div></td></tr>';
		showsubmit('submit', 'submit', 'del');
		showtablefooter();
		showformfooter();

		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1, '', 'td25'], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<input name="newicon[]" value="" size="10" type="text" class="txt">'], [1, '<input name="newname[]" value="" size="15" type="text" class="txt">'], [4, '<input name="newurl[]" value="" size="15" type="text" class="txt">'], [1, '<input type="hidden" name="newtype[]" value="1">'], [1, '<input name="is_postnew" class="radio" type="radio" value="new">'], [1, '<input class="checkbox" type="checkbox" name="newavailable[]" value="1" checked>']],
	];
</script>
EOT;

	} else {

		if($ids = dimplode($_GET['delete'])) {
			table_common_nav::t()->delete_by_navtype_id(6, $_GET['delete']);
		}

		// 先将所有导航项的identifier设为空
		foreach($navlist as $nav) {
			table_common_nav::t()->update($nav['id'], ['identifier' => '']);
		}

		// 处理现有导航项
		if(is_array($_GET['namenew'])) {
			foreach($_GET['namenew'] as $id => $name) {
				$name = trim(dhtmlspecialchars($name));
				$iconnew = trim($_GET['iconnew'][$id]);
				$urlnew = str_replace(['&amp;'], ['&'], dhtmlspecialchars($_GET['urlnew'][$id]));
				$is_postnew = $_GET['is_postnew'] == $id ? 1 : 0;
				$availablenew[$id] = $name && (!isset($_GET['urlnew'][$id]) || $_GET['urlnew'][$id]) && $_GET['availablenew'][$id];
				$displayordernew[$id] = intval($_GET['displayordernew'][$id]);
				$data = [
					'displayorder' => $displayordernew[$id],
					'available' => $availablenew[$id],
					'identifier' => $is_postnew ? 'post' : '',
				];
				if(!empty($iconnew)) {
					$data['icon'] = $iconnew;
				}
				if(!empty($_GET['urlnew'][$id])) {
					$data['url'] = $urlnew;
				}
				if(!empty($name)) {
					$data['name'] = $name;
				}
				table_common_nav::t()->update($id, $data);
			}
		}

		// 处理新添加的导航项
		if(is_array($_GET['newname'])) {
			foreach($_GET['newname'] as $k => $v) {
				$v = dhtmlspecialchars(trim($v));
					if(!empty($v)) {
						$newicon = trim($_GET['newicon'][$k]);
					$newavailable = $v && $_GET['newurl'][$k];
					$newdisplayorder[$k] = intval($_GET['newdisplayorder'][$k]);
					$newurl[$k] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['newurl'][$k]));
					$newis_post = $_GET['is_postnew'] == 'new' ? 1 : 0;
					$data = [
						'name' => $v,
						'icon' => $newicon,
						'displayorder' => $newdisplayorder[$k],
						'url' => $newurl[$k],
						'type' => 1,
						'identifier' => $newis_post ? 'post' : '',
						'available' => $newavailable,
						'navtype' => 6
					];
					$new_id = table_common_nav::t()->insert($data);
					// 如果新添加的是发布按钮，更新其identifier
					if($newis_post) {
						table_common_nav::t()->update($new_id, ['identifier' => 'post']);
					}
				}
			}
		}

		updatecache('setting');
		cpmsg('nav_add_succeed', 'action=nav&operation=mnav', 'succeed');

	}

} elseif($do == 'edit' && ($id = $_GET['id'])) {

	$nav = table_common_nav::t()->fetch_by_id_navtype($id, 6);
	if(!$nav) {
		cpmsg('nav_not_found', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		shownav('style', 'nav_setting_customnav');
		showchildmenu([['nav_setting_customnav', 'nav'], ['nav_nav_mnav', 'nav&operation=mnav']], $nav['name']);

		showformheader("nav&operation=mnav&do=edit&id=$id");
		showtableheader();
		showsetting('misc_customnav_icon', 'iconnew', $nav['icon'], 'text', '', 0, '支持图片URL或字体图标代码，例如：<br/>图片URL: http://example.com/icon.png<br/>字体图标: &amp;#xf015; (FontAwesome图标代码)');
		showsetting('misc_customnav_name', 'namenew', $nav['name'], 'text');
		showsetting('misc_customnav_url', 'urlnew', $nav['url'], 'text', $nav['type'] == '0');
		if($nav['type']) {
			showsetting('misc_customnav_level', ['levelnew', [
				[0, cplang('nolimit')],
				[1, cplang('member')],
				[2, cplang('usergroups_system_3')],
				[3, cplang('usergroups_system_1')],
			]], $nav['level'], 'select');
		}
		showtagfooter('tbody');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$namenew = trim(dhtmlspecialchars($_GET['namenew']));
		$iconnew = trim($_GET['iconnew']);
		$urlnew = str_replace(['&amp;'], ['&'], dhtmlspecialchars($_GET['urlnew']));
		$levelnew = $nav['type'] ? (intval($_GET['levelnew']) && $_GET['levelnew'] > 0 && $_GET['levelnew'] < 4 ? intval($_GET['levelnew']) : 0) : 0;

		$data = [
			'name' => $namenew,
			'icon' => $iconnew,
			'level' => $levelnew
		];
		if($nav['type'] != '0' && $urlnew) {
			$data['url'] = $urlnew;
		}
		table_common_nav::t()->update($id, $data);

		updatecache('setting');
		cpmsg('nav_add_succeed', 'action=nav&operation=mnav', 'succeed');

	}

}
	