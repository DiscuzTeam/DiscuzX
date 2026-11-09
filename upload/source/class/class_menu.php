<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class menu {

	const defaultLogo = '<a href="{ADMINSCRIPT}?frames=yes&action=index" class="logo"><img src="static/image/admincp/logo.svg" alt="Discuz! Administrator\'s Control Panel"></a>';

	const defaultNavbar = '
<form name="search" method="post" autocomplete="off" action="{ADMINSCRIPT}?action=search" target="main">
	<input type="text" name="keywords" value="" class="txt" required>
	<button type="submit" name="searchsubmit" value="yes" class="btn"></button>
</form>
';

	const newTemplate = '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
	<name><![CDATA[新平台]]></name>
	<title><![CDATA[新平台]]></title>
	<framecss><![CDATA[xxx.css]]></framecss>
	<pagecss><![CDATA[ccc.css]]></pagecss>	
	<logo><![CDATA[<a class="logo"><img src="static/image/admincp/logo.svg"></a>]]></logo>
	<navbar><![CDATA[<form></form>]]></navbar>
	<defaultId><![CDATA[默认首页的menuId]]></defaultId>
	<menu>
		<menuId>主菜单1</menuId>
		<sub>
			<subId>action_operation_do1</subId>
			<title>子菜单1</title>
		</sub>
		<sub>
			<subId>action_operation_do2</subId>
			<title>子菜单2</title>
		</sub>
	</menu>
	<menu>
		<menuId>主菜单2</menuId>
		<sub>
			<subId>plugin_id:pmod1</subId>
			<title>子菜单3</title>
		</sub>
		<sub>
			<subId>plugin_id:pmod2</subId>
			<title>子菜单4</title>
		</sub>
	</menu>
	<userdef><![CDATA[1]]></userdef>
</root>';

	public static function array2menu($menuData) {
		$data = '<?xml version="1.0" encoding="ISO-8859-1"?>';
		$data .= "\n<root>";
		foreach($menuData as $key => $value) {
			if($key == 'menu') {
				foreach($value as $menu => $submenu) {
					$data .= "\n\t".'<menu>';
					$data .= "\n\t\t".'<menuId>'.$menu.'</menuId>';
					$_lang = cplang('header_'.$menu);
					if(!str_starts_with($_lang, 'header_')) {
						$data .= '  <!--'.$_lang.' -->';
					}
					foreach($submenu as $row) {
						$data .= "\n\t\t".'<sub>';
						if(!empty($row[1])) {
							$data .= "\n\t\t\t".'<subId>'.$row[1].'</subId>';
						}
						if(!empty($row[0])) {
							$data .= "\n\t\t\t".'<title>'.$row[0].'</title>';
							$_lang = cplang($row[0]);
							if($_lang != $row[0]) {
								$data .= '  <!--'.$_lang.' -->';
							}
						}
						if(!empty($row[2])) {
							$data .= "\n\t\t\t".'<type>'.$row[2].'</type>';
						}
						if(!empty($row[3])) {
							$data .= "\n\t\t\t".'<showMethod>'.$row[3].'</showMethod>';
						}
						if(!empty($row[4])) {
							$data .= "\n\t\t\t".'<listMethod>'.$row[4].'</listMethod>';
						}
						if(!empty($row[5])) {
							$data .= "\n\t\t\t".'<subPerms>'.implode(',', $row[5]).'</subPerms>';
						}
						$data .= "\n\t\t".'</sub>';
					}
					$data .= "\n\t</menu>";
				}
			} else {
				$data .= "\n\t".'<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
			}
		}
		$data .= "\n</root>";
		return $data;
	}

	public static function menu2array($data) {
		preg_match_all('#<menu>.+?</menu>#is', $data, $rMenu);
		$menu = [];
		foreach($rMenu[0] as $rMenu_row) {
			preg_match('#<menuId>(.+?)</menuId>#', $rMenu_row, $rMenuId);
			if(!$rMenuId) {
				continue;
			}
			$menuId = $rMenuId[1];
			preg_match_all('#<sub>(.+?)</sub>#is', $rMenu_row, $rSub);
			if(!$rSub) {
				continue;
			}
			foreach($rSub[1] as $rSub_row) {
				$subItem = [];
				preg_match('#<subId>(.+?)</subId>#', $rSub_row, $rSubId);
				if(!empty($rSubId[1])) {
					$subItem[1] = $rSubId[1];
				}
				preg_match('#<title>(.+?)</title>#', $rSub_row, $rTitle);
				if(!empty($rTitle[1])) {
					$subItem[0] = $rTitle[1];
				}
				preg_match('#<type>(.+?)</type>#', $rSub_row, $rType);
				if(!empty($rType[1])) {
					$subItem[2] = $rType[1];
				}
				preg_match('#<showMethod>(.+?)</showMethod>#', $rSub_row, $rShowMethod);
				if(!empty($rShowMethod[1])) {
					$subItem[3] = $rShowMethod[1];
				}
				preg_match('#<listMethod>(.+?)</listMethod>#', $rSub_row, $rListMethod);
				if(!empty($rListMethod[1])) {
					$subItem[4] = $rListMethod[1];
				}
				preg_match('#<subPerms>(.+?)</subPerms>#', $rSub_row, $rSubPerms);
				if(!empty($rSubPerms[1])) {
					$subItem[5] = explode(',', $rSubPerms[1]);
				}
				$menu[$menuId][] = $subItem;
			}
		}
		$data = preg_replace('#<menu>.+?</menu>#is', '', $data);
		preg_match_all('#<(\w+)><!\[CDATA\[(.+?)\]\]></\\1>#is', $data, $rAll);
		if($rAll) {
			foreach($rAll[1] as $k => $v) {
				$menuData[$v] = $rAll[2][$k];
			}
		}
		$menuData['menu'] = $menu;
		return $menuData;
	}

	// 补全平台菜单的 logo 与 navbar：未提供、或 logo 中不含图片时使用默认值，避免后台顶部用户信息显示错位
	private static function fixdefault(&$menuData) {
		if(empty($menuData['logo']) || (stripos($menuData['logo'], '<img') === false && stripos($menuData['logo'], '<svg') === false)) {
			$menuData['logo'] = self::defaultLogo;
		}
		if(empty($menuData['navbar'])) {
			$menuData['navbar'] = self::defaultNavbar;
		}
	}

	// 以平台表为准全量重建缓存里的 platform 列表。
	// 平台注册/卸载过去是「改进程内 $_G['cache']['admin'] 再整份 savecache」，
	// 一旦调用方没先 loadcache('admin')，单条写入就会把其它平台从缓存里挤掉，
	// 之后又被平台管理页的旧值回填固化成 NULL（右上角「全部应用」只剩图标没有名字）。
	// 因此写库后一律从库全量重建，顺带丢弃 NULL / 缺 name / 缺 menu 的坏条目。
	public static function platform_refresh() {
		global $_G;

		$cache = is_array($_G['cache']['admin'] ?? null) ? $_G['cache']['admin'] : [];
		$platforms = [];
		foreach(table_common_admincp_menu_platform::t()->fetch_all_data() as $platform => $row) {
			$menuData = dunserialize($row['menu']);
			if(!empty($menuData['custom']) && is_array($menuData['custom'])) {
				$menuData = $menuData['custom'];
			}
			if(!is_array($menuData) || empty($menuData['menu'])) {
				continue;
			}
			unset($menuData['custom']);
			self::fixdefault($menuData);
			if(empty($menuData['name'])) {
				$menuData['name'] = !empty($menuData['title']) ? $menuData['title'] : $platform;
			}
			$platforms[$platform] = $menuData;
		}
		// system（框架自带后台）固定排在最前，不受显示顺序与注册先后影响
		if(isset($platforms['system'])) {
			$system = $platforms['system'];
			unset($platforms['system']);
			$platforms = ['system' => $system] + $platforms;
		}

		// 用严格比较：键顺序也算变更（system 必须始终在最前）
		if(isset($cache['platform']) && is_array($cache['platform']) && $cache['platform'] === $platforms) {
			return $platforms;
		}
		// 进程内 admin 缓存不完整时以已存缓存为底，避免整份缓存被写成只剩 platform
		if(!isset($cache['platform'])) {
			$stored = table_common_syscache::t()->fetch_syscache('admin');
			if(is_array($stored)) {
				$cache += $stored;
			}
		}
		$cache['platform'] = $platforms;
		$_G['cache']['admin'] = $cache;
		savecache('admin', $cache);
		return $platforms;
	}

	// 缓存里的平台列表是否已损坏（NULL 条目 / 缺 name / 缺 menu），后台入口据此自愈
	public static function platform_cache_broken() {
		global $_G;

		$platforms = $_G['cache']['admin']['platform'] ?? null;
		if(!is_array($platforms)) {
			return true;
		}
		foreach($platforms as $platform_data) {
			if(!is_array($platform_data) || empty($platform_data['name']) || empty($platform_data['menu'])) {
				return true;
			}
		}
		return false;
	}

	public static function platform_add($platform, $data, $isarray = false) {
		global $_G;

		$menuData = !$isarray ? self::menu2array($data) : $data;
		if(!empty($menuData['custom'])) {
			self::fixdefault($menuData['custom']);
		} else {
			self::fixdefault($menuData);
		}
		table_common_admincp_menu_platform::t()->insert([
			'platform' => $platform,
			'menu' => serialize($menuData)
		], false, true);

		if(!empty($menuData['custom'])) {
			$menuData = $menuData['custom'];
			unset($menuData['custom']);
		}

		self::platform_refresh();
		if($platform != 'system') {
			echo "<script type=\"text/javascript\">top.addplatform('$platform', '{$menuData['name']}')</script>";
		}
	}

	public static function platform_del($platform) {
		if($platform == 'system') {
			return;
		}

		table_common_admincp_menu_platform::t()->delete($platform);

		self::platform_refresh();
		echo "<script type=\"text/javascript\">top.removeplatform('$platform')</script>";
	}

}