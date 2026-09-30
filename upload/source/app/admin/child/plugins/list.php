<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}


if(!submitcheck('submit')) {

	$recommendaddon = dunserialize($_G['setting']['cloudaddons_recommendaddon']);
	if(empty($recommendaddon['updatetime']) || abs($_G['timestamp'] - $recommendaddon['updatetime']) > 7200 || (isset($_GET['checknew']) && $_G['formhash'] == $_GET['formhash'])) {
		$update_recommendaddon = true;
	}

	loadcache(['plugin', 'pluginsetting']);
	$outputsubmit = false;
	$plugins = $addonids = $pluginlist = [];
	$plugins = table_common_plugin::t()->fetch_all_data();
	if(empty($_G['cookie']['addoncheck_plugin'])) {
		foreach($plugins as $plugin) {
			$addonids[$plugin['pluginid']] = $plugin['identifier'].'.plugin';
		}
		$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
		savecache('addoncheck_plugin', $checkresult);
		dsetcookie('addoncheck_plugin', 1, 7200);
	} else {
		loadcache('addoncheck_plugin');
		$checkresult = $_G['cache']['addoncheck_plugin'];
	}

	loadwitframe();
	$baseConf = Lib\Core::GetSetting();
	if($baseConf) {
		$witPlugins = witframe_plugin::pluginList();
		$apis = witframe_plugin::getApis($witPlugins);
		if($apis) {
			$title = '<div class="boxheader">'.cplang('cloudaddons_witframe_link').'</div>';
			foreach($apis as $plugin) {
				$pluginlist['witframe'][$plugin['path']] = $title.'<div class="boxbody">'.showboxrow('', ['class="dcol"', 'class="dcol d-1"', 'class="plugin_control"'],
						[
							'<img src="'.$plugin['appIcon'].'" onerror="this.src=\''.STATICURL.'image/admincp/plugin_logo.png\';this.onerror=null" width="80" height="80" align="left" />',
							'<h3 class="light" style="font-size:16px">'.dhtmlspecialchars($plugin['appName']).' <span class="smallfont">(<a href="'.$plugin['appUrl'].'" style="color: #555;" target="_blank">'.$plugin['path'].'</a>)</span></h3>'.
							'<p><span class="light">'.($plugin['sellerName'] ? cplang('author').': '.dhtmlspecialchars($plugin['sellerName']) : '').'</p>'.
							'<p><a href="'.ADMINSCRIPT.'?action=plugins&operation=witframeConfig&do='.$plugin['path'].'">'.$lang['config'].'</a></p>'
						], true).'</div>';
				$title = '';
			}
		}
		$newSetting = witframe_plugin::getSettingValue($apis);
		if(serialize($newSetting) != serialize($_G['setting']['witframe_plugins'])) {
			updatecache('setting');
		}
	}

	$updatecount = 0;
	$splitavailable = $addonids = [];
	foreach($plugins as $plugin) {
		$addonid = $plugin['identifier'].'.plugin';
		$updateinfo = $newver = $sysver = '';
		if(is_array($checkresult) && isset($checkresult[$addonid])) {
			list(, $newver, $sysver) = explode(':', $checkresult[$addonid]);
		}
		if(!empty($sysver) && $sysver > $plugin['version']) {
			$updateinfo = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&frame=no&id='.$addonid.'&from=newver" title="'.$lang['plugins_online_update'].'" target="_blank"><font color="red">'.$lang['plugins_find_newversion'].'<span class="w1000hide w1300hide"> '.$sysver.'</span></font></a>';
		} elseif(!empty($newver)) {
			$updateinfo = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&frame=no&id='.$addonid.'&from=newver" title="'.$lang['plugins_online_update'].'" target="_blank"><font color="red">'.$lang['plugins_find_newversion'].'<span class="w1000hide w1300hide"> '.$newver.'</span></font></a>';
		}
		$hookexists = FALSE;
		$plugin['modules'] = dunserialize($plugin['modules']);
		$submenuitem = [];
		if(isset($_G['cache']['plugin'][$plugin['identifier']])) {
			//为配合插件完全接管变量设置功能，当插件第一个后台设置模块为config时，插件列表不显示默认的设置
			$configexists = FALSE;
			if(is_array($plugin['modules'])) {
				foreach($plugin['modules'] as $k => $module) {
					if(isset($module['type']) && $module['type'] == 3) {
						if($module['name'] == 'config') {
							$configexists = TRUE;
						}
						break;
					}
				}
			}
			if(!$configexists && !empty($_G['cache']['pluginsetting']['config'][$plugin['identifier']])) {
				$submenuitem[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'">'.$lang['config'].'</a>';
			}
		}
		if(is_array($plugin['modules'])) {
			foreach($plugin['modules'] as $k => $module) {
				if(isset($module['type'])) {
					if($module['type'] == 11) {
						$hookorder = $module['displayorder'];
						$hookexists = $k;
					}
					if($module['type'] == 3 && $module['displayorder'] >= 0) {
						$submenuitem[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'&identifier='.$plugin['identifier'].'&pmod='.$module['name'].($module['param'] ? '&'.$module['param'] : '').'">'.$module['menu'].'</a>';
					}
					if($module['type'] == 29) {
						$submenuitem[] = '<a href="'.$module['url'].'" target="_blank">'.$module['menu'].'</a>';
					}
				}
			}
		}
		$outputsubmit = $hookexists !== FALSE && $plugin['available'] || $outputsubmit;
		$hl = !empty($_GET['hl']) && $_GET['hl'] == $plugin['pluginid'];
		$intro = $title = '';
		if($updateinfo) {
			$updatecount++;
		}
		$order = $plugin['available'] ? 'open' : 'close';
		if($plugin['pluginid'] == $_GET['hl']) {
			$order = 'hightlight';
		} else {
			if($plugin['available']) {
				if(empty($splitavailable[0])) {
					if(!empty($splitavailable)) {
						$title = '</div><div class="dbox psetting">';
					}
					$title .= '<div class="boxheader">'.cplang('plugins_list_available').'</div>';
					$splitavailable[0] = 1;
				}
			} else {
				if(empty($splitavailable[1])) {
					if(!empty($splitavailable)) {
						$title = '</div><div class="dbox psetting">';
					}
					$title .= '<div class="boxheader">'.cplang('plugins_list_unavailable').'</div>';
					$splitavailable[1] = 1;
				}
			}
		}
		$logo = cloudaddons_pluginlogo_url($plugin['identifier']);
		$name = dhtmlspecialchars($plugin['name']);
		$version = dhtmlspecialchars($plugin['version']);
		$copyright = dhtmlspecialchars($plugin['copyright']);
		$submenuitems = implode(' | ', $submenuitem);
		$uninstalltips = lang('admincp', 'plugins_config_uninstall_tips', ['pluginname' => dhtmlspecialchars($plugin['name'])]);
		include template('admin/plugin_list');
		$pluginlist[$order][$plugin['pluginid']] = $title.'<div class="boxbody'.($hl ? ' hl' : '').'">'.showboxrow('', ['class="dcol"', 'class="dcol d-1"', 'class="plugin_control"'],
				[$col1, $col2, $col3], true).'</div>';
		$addonids[] = $plugin['identifier'];
	}

	shownav('plugin', 'plugins_list');
	showsubmenu('nav_plugins', [
		['plugins_list', 'plugins', 1],
		['plugins_hooklist', 'plugins&operation=hooklist', 0],
		['plugins_validator'.($updatecount ? '_new' : ''), 'plugins&operation=upgradecheck', 0],
		['cloudaddons_plugin_link', 'cloudaddons&frame=no&operation=plugins&from=more', 0, 1],
		['cloudaddons_witframe_link', 'cloudaddons&frame=no&operation=witframe&from=more', 0, 1],
	], '<a href="https://www.dismall.com/?from=plugins_question" target="_blank" class="rlink">'.$lang['plugins_question'].'</a>', ['updatecount' => $updatecount]);
	showformheader('plugins', 'class="pluginlist"');
	showboxheader('', 'psetting', '', 1);

	ksort($pluginlist);
	$pluginlist = (array)$pluginlist['hightlight'] + (array)$pluginlist['witframe'] + (array)$pluginlist['open'] + (array)$pluginlist['close'];
	echo implode('', $pluginlist);
	showboxfooter(1);

	if(empty($_GET['system'])) {
		$plugindir = DISCUZ_PLUGIN();
		$pluginsdir = dir($plugindir);
		$newplugins = [];
		$newlist = '';
		while($entry = $pluginsdir->read()) {
			if(!in_array($entry, ['.', '..']) && is_dir($plugindir.'/'.$entry) && !in_array($entry, $addonids)) {
				$entrydir = DISCUZ_PLUGIN($entry);
				$d = dir($entrydir);
				$filemtime = filemtime($entrydir);
				$entrytitle = $entry;
				$entryversion = $entrycopyright = $importtxt = '';
				$extra = currentlang();
				$extra = $extra ? '_'.$extra : '';
				if($f = getimportfilename($entrydir.'/discuz_plugin_'.$entry.$extra)) {
					$importtxt = @implode('', file($f));
				} elseif($f = getimportfilename($entrydir.'/discuz_plugin_'.$entry)) {
					$importtxt = @implode('', file($f));
				}
				if(!empty($importtxt)) {
					$pluginarray = getimportdata('Discuz! Plugin', 0, 1);
					if(!empty($pluginarray['plugin']['name'])) {
						$entrytitle = dhtmlspecialchars($pluginarray['plugin']['name']);
						$entryversion = dhtmlspecialchars($pluginarray['plugin']['version']);
						$entrycopyright = dhtmlspecialchars($pluginarray['plugin']['copyright']);
					}
					$file = $entrydir.'/'.$f;
					$logo = cloudaddons_pluginlogo_url($entry);
					include template('admin/plugin_newlist');
					$newlist .= '<div class="boxbody">'.showboxrow('', ['class="dcol"', 'class="dcol d-1"', ' class="plugin_control"'],
							[$col1, $col2, $col3], true).'</div>';
				}
				$addonids[] = $entry;
			}
		}
		$recommendlist = '';
		if($recommendaddon['plugins']) {
			$title = '<div class="boxheader">'.cplang('cloudaddons_recommendaddon').'</div>';
			foreach($recommendaddon['plugins'] as $plugin) {
				if(in_array($plugin['identifier'], $addonids)) {
					continue;
				}
				$filemtime = TIMESTAMP;
				$entry = $plugin['identifier'];
				$logo = cloudaddons_pluginlogo_url($plugin['identifier']);
				$entrytitle = dhtmlspecialchars($plugin['name']);
				$entryversion = dhtmlspecialchars($plugin['version']);
				$entrycopyright = dhtmlspecialchars($plugin['copyright']);
				include template('admin/plugin_newlist');
				$recommendlist .= '<div class="boxbody">'.showboxrow('', ['class="dcol"', 'class="dcol d-1"', 'class="plugin_control"'],
						[$col1, $col2, $col3], true).'</div>';
			}
			$newlist .= $recommendlist;
		}
		if($newlist) {
			showboxheader('', 'psetting', '', 1);
			showboxtitle('plugins_list_new');
			echo $newlist;
			showboxfooter(1);
		}
	}

	showtableheader();
	if($outputsubmit) {
		showsubmit('submit', 'submit', '', '<a href="'.ADMINSCRIPT.'?action=cloudaddons&frame=no&operation=plugins&from=more" target="_blank">'.cplang('cloudaddons_plugin_link').'</a>');
	} else {
		showsubmit('', '', '', '<a href="'.ADMINSCRIPT.'?action=cloudaddons&frame=no&operation=plugins&from=more" target="_blank">'.cplang('cloudaddons_plugin_link').'</a>');
	}
	showtablefooter();
	showformfooter();

	if($update_recommendaddon) {
		echo '<script type="text/javascript" src="'.ADMINSCRIPT.'?action=misc&operation=recommendupdate"></script>';
	}

} else {

	foreach(table_common_plugin::t()->fetch_all_data(1) as $plugin) {
		if(!empty($_GET['displayordernew'][$plugin['pluginid']])) {
			$plugin['modules'] = dunserialize($plugin['modules']);
			$k = array_keys($_GET['displayordernew'][$plugin['pluginid']]);
			$v = array_values($_GET['displayordernew'][$plugin['pluginid']]);
			foreach($plugin['modules'] as $key => $value) {
				if(isset($value['type']) && in_array($value['type'], [11, 28])) {
					$plugin['modules'][$key]['displayorder'] = $v[0];
				}
			}
			table_common_plugin::t()->update($plugin['pluginid'], ['modules' => serialize($plugin['modules'])]);
		}
	}

	updatecache(['plugin', 'setting', 'styles']);
	cleartemplatecache();

	cpmsg('plugins_edit_succeed', 'action=plugins', 'succeed');

}
	