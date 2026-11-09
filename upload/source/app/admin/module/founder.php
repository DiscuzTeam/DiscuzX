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

if(empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
	exit('Access Denied');
}

$file = childfile('founder/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}

require_once $file;

function getactionarray() {
	$isfounder = false;
	$menu = $topmenu = [];
	foreach(table_common_admincp_menu_platform::t()->fetch_all_data() as $menuData) {
		$menu_array = (array)dunserialize($menuData['menu']);
		if(!empty($menu_array['custom'])) {
			$menu_array = $menu_array['custom'];
		}
		$menu += [$menuData['platform'] => ['name' => $menu_array['name'] ?? $menu_array['title'] ?? $menuData['platform'], 'menu' => (array)($menu_array['menu'] ?? [])]];
	}
	foreach($menu as $platform => $pv) {
		foreach($pv['menu'] as $top => $v) {
			if(empty($v)) {
				continue;
			}
			$topmenu[$platform][$top] = '';
		}
	}

	unset($topmenu['system']['index'], $topmenu['system']['cloudaddons'], $topmenu['system']['uc']);
	unset($menu['system']['index'], $menu['system']['cloudaddons'], $menu['system']['uc']);

	require childfile('menu/append');

	$actioncat = $actionarray = [];

	foreach($topmenu as $platform => $pv) {
		if($platform == 'system') {
			$_pv['setting'] = '';
			$pv = array_merge($_pv, $pv);
		}
		$actioncat = array_merge($actioncat, [$platform => array_keys($pv)]);
	}

	require_once childfile('menu/class', 'admin');

	$actionarray['system']['setting'][] = ['founder_perm_allowpost', '_allowpost'];
	foreach($menu as $platform => $pv) {
		foreach($pv['menu'] as $tkey => $items) {
			foreach($items as $item) {
				if(!empty($item[4]) && method_exists('menu_loader', $item[4])) {
					$v = menu_loader::{$item[4]}();
					$submenu = [];
					array_splice($submenu, $k, 0, $v);
					foreach($submenu as $k => $item) {
						$actionarray[$platform][$tkey][] = $item;
					}
				} else {
					$actionarray[$platform][$tkey][] = $item;
				}
			}
		}
	}

	$actionname = [];
	foreach($menu as $platform => $pv) {
		$actionname[$platform] = $pv['name'];
	}
	return ['actions' => $actionarray, 'cats' => $actioncat, 'names' => $actionname];
}

function showpermstyle() {
	$staticurl = STATICURL;
	echo <<<EOF
	<style>
.item{ float: left; width: 180px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; }
.vtop .right, .item .right{ padding: 0 10px; line-height: 22px; background: url('{$staticurl}/image/admincp/bg_repno.gif') no-repeat -286px -145px; font-weight: normal;margin-right:10px; }
.vtop a:hover.right, .item a:hover.right { text-decoration:none; }
</style>
<script type="text/JavaScript">
function permcheckall(obj, perms, t) {
	var t = !t ? 0 : t;
	var checkboxs = $(perms).getElementsByTagName('INPUT');
	for(var i = 0; i < checkboxs.length; i++) {
		var e = checkboxs[i];
		if(e.type == 'checkbox') {
			if(!t) {
				if(!e.disabled) {
					e.checked = obj.checked;
				}
			} else {
				if(obj != e) {
					e.style.visibility = obj.checked ? 'hidden' : 'visible';
				}
			}
			e.parentNode.parentNode.className = e.checked ? 'item checked' : 'item';
		}
	}
}
function checkclk(obj) {
	var obj = obj.parentNode.parentNode;
	obj.className = obj.className == 'item' ? 'item checked' : 'item';
}
</script>
EOF;
}
