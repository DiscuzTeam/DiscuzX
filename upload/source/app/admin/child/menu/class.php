<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

class menu_loader {

	public static function run(&$menuData) {
		global $_G;

		// 缓存里的平台条目可能因历史单条写入而缺失或为 NULL，损坏时以平台表为准自愈一次
		if(menu::platform_cache_broken()) {
			menu::platform_refresh();
		}

		if(PLATFORM == 'system') {
			if(!empty($_GET['closemenunotice'])) {
				require_once libfile('function/cache');
				$v = $_GET['closemenunotice'] == 'yes' ? 1 : 0;
				table_common_setting::t()->update('menu_updatenotice', $v);
				updatecache('setting');
				$_G['setting']['menu_updatenotice'] = $v;
				unset($_GET['closemenunotice']);
				dheader('location: '.ADMINSCRIPT.'?'.http_build_query($_GET));
			}
			if(empty($_G['cache']['admin']['platform'][PLATFORM]) || !empty($_GET['resetmenu'])) {
				require_once 'default.php';
				$initdata = menu_default::getMenu();
				menu::platform_add('system', $initdata['system'], true);
				if(!empty($initdata['ucenter'])) {
					menu::platform_add('ucenter', $initdata['ucenter'], true);
				}
			} elseif(self::isfounder() && empty($_G['setting']['menu_updatenotice'])) {
				require_once 'default.php';
				$initdata = menu_default::getMenu();
				if($initdata['system'] != $_G['cache']['admin']['platform'][PLATFORM]) {
					$_ENV['menuupdate'] = true;
				}
			}
		}
		$menuData = $_G['cache']['admin']['platform'][PLATFORM] ?? [];
		if(!empty($menuData['location'])) {
			dheader('location: '.$menuData['location']);
		}
		foreach((array)($menuData['menu'] ?? []) as $key => $submenu) {
			foreach($submenu as $k => $row) {
				if(!empty($row[3])) {
					list($s1, $s2) = explode('::', $row[3]);
					if(!$s2) {
						if(method_exists(__CLASS__, $row[3])) {
							if(!self::{$row[3]}()) {
								unset($submenu[$k]);
							}
						} elseif(empty($GLOBALS['_G']['setting'][$row[3]])) {
							unset($submenu[$k]);
						}
					} else {
						$f = DISCUZ_PLUGIN($s1).'/platform.class.php';
						if(file_exists($f)) {
							require_once $f;
							$c = 'platform_'.$s1;
							if(method_exists($c, $s2) && !$c::$s2()) {
								unset($submenu[$k]);
							}
						}
					}
				} elseif(!empty($row[4])) {
					list($s1, $s2) = explode('::', $row[4]);
					if(!$s2) {
						if(method_exists(__CLASS__, $row[4])) {
							$v = self::{$row[4]}();
							array_splice($submenu, $k, 0, $v);
						}
					} else {
						$f = DISCUZ_PLUGIN($s1).'/platform.class.php';
						if(file_exists($f)) {
							require_once $f;
							$c = 'platform_'.$s1;
							if(method_exists($c, $s2)) {
								$v = $c::$s2();
								array_splice($submenu, $k, 0, $v);
							}
						}
					}
				}
			}
			$menuData['menu'][$key] = $submenu;
		}
		$replaces = [
			'{ADMINSCRIPT}' => ADMINSCRIPT,
		];
		foreach($replaces as $k => $v) {
			$menuData['logo'] = str_replace($k, $v, $menuData['logo'] ?? '');
			$menuData['navbar'] = str_replace($k, $v, $menuData['navbar'] ?? '');
		}
	}

	public static function homestatus() {
		global $_G;

		return $_G['setting']['doingstatus'] || $_G['setting']['blogstatus'] ||
			$_G['setting']['feedstatus'] || $_G['setting']['albumstatus'] ||
			$_G['setting']['wallstatus'] || $_G['setting']['sharestatus'];
	}

	public static function isfounder() {
		return $GLOBALS['isfounder'];
	}

	public static function isdeveloper() {
		global $_G;
		return $GLOBALS['isfounder'] && isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] > 0;
	}

	public static function isdebug() {
		return defined('DISCUZ_DEBUG') && DISCUZ_DEBUG && $GLOBALS['isfounder'];
	}

	public static function verifyList() {
		global $_G;

		$menu = [];
		if(is_array($_G['setting']['verify'])) {
			foreach($_G['setting']['verify'] as $vid => $verify) {
				if($vid != 7 && $verify['available']) {
					$menu[] = [$verify['title'], "verify_verify_$vid"];
				}
			}
		}

		return $menu;
	}

	public static function pluginList() {
		global $_G;

		$menu = [];
		loadcache('adminmenu');
		if(is_array($_G['cache']['adminmenu'])) {
			foreach($_G['cache']['adminmenu'] as $row) {
				if($row['name'] == 'plugins_system') {
					$row['name'] = cplang('plugins_system');
				}
				$menu[] = [$row['name'], $row['action'], $row['sub']];
			}
		}

		return $menu;
	}

	public static function customMenuList() {
		return get_custommenu();
	}
}