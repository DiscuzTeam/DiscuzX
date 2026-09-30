<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(empty($_GET['identifier'])) {
	$pluginarray = table_common_plugin::t()->fetch_all_data();
} else {
	$plugin = table_common_plugin::t()->fetch_by_identifier($_GET['identifier']);
	$pluginarray = $plugin ? [$plugin] : [];
}
$plugins = $errarray = $newarray = $nowarray = [];
if(!$pluginarray) {
	cpmsg('plugin_not_found', '', 'error');
} else {
	$addonids = [];
	foreach($pluginarray as $row) {
		if(ispluginkey($row['identifier'])) {
			$addonids[] = $row['identifier'].'.plugin';
		}
	}
	$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
	savecache('addoncheck_plugin', $checkresult);
	foreach($pluginarray as $row) {
		$addonid = $row['identifier'].'.plugin';
		if(is_array($checkresult) && isset($checkresult[$addonid])) {
			list($return, $newver, $sysver) = explode(':', $checkresult[$addonid]);
			$result[$row['identifier']]['result'] = $return;
			if($sysver) {
				if($sysver > $row['version']) {
					$result[$row['identifier']]['result'] = 2;
					$result[$row['identifier']]['newver'] = $sysver;
				} else {
					$result[$row['identifier']]['result'] = 1;
				}
			} elseif($newver) {
				$result[$row['identifier']]['newver'] = $newver;
			}
		}
		$plugins[$row['identifier']] = $row['name'].' '.$row['version'];
		$modules = dunserialize($row['modules']);

		$file = getimportfilename(DISCUZ_PLUGIN($row['identifier']).'/discuz_plugin_'.$row['identifier'].($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : ''));
		$upgrade = false;
		if($file) {
			$importtxt = @implode('', file($file));
			$pluginarray = getimportdata('Discuz! Plugin', 0, 1);
			$newver = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
			if($newver > $row['version']) {
				$upgrade = true;
				$nowarray[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$row['pluginid'].'">'.$plugins[$row['identifier']].' -> '.$newver.'</a>';
			}
		}
		if(!$upgrade) {
			$entrydir = DISCUZ_PLUGIN($row['identifier']);
			$upgradestr = '';
			if(file_exists($entrydir)) {
				$d = dir($entrydir);
				while($f = $d->read()) {
					if(preg_match('/^discuz\_plugin\_'.$row['identifier'].'(\_\w+)?\.(xml|json)$/', $f, $a)) {
						$extratxt = $extra = substr($a[1], 1);
						if(preg_match('/^SC\_UTF8$/i', $extra)) {
							$extratxt = '&#31616;&#20307;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
						} elseif(preg_match('/^TC\_UTF8$/i', $extra)) {
							$extratxt = '&#32321;&#39636;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
						}
						if($modules['extra']['installtype'] == $extratxt) {
							continue;
						}
						$importtxt = @implode('', file($entrydir.'/'.$f));
						$pluginarray = getimportdata('Discuz! Plugin', 0, 1);
						$newverother = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
						if($newverother > $row['version']) {
							$nowarray[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$row['pluginid'].'&confirmed=yes&installtype='.rawurlencode($extra).'">'.$plugins[$row['identifier']].' -> '.$newverother.($extra ? ' ('.$extratxt.')' : '').'</a>';
						}
					}
				}
			}
		}
	}
}
foreach($result as $id => $row) {
	if($row['result'] == 0) {
		$errarray[] = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&frame=no&id='.$id.'.plugin&from=newver" target="_blank">'.$plugins[$id].'</a>';
	} elseif($row['result'] == 2) {
		$newarray[] = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&frame=no&id='.$id.'.plugin&from=newver" target="_blank">'.$plugins[$id].($row['newver'] ? ' -> '.$row['newver'] : '').'</a>';
	}
}
if(!$nowarray && !$newarray && !$errarray) {
	cpmsg('plugins_validator_noupdate', '', 'error');
} else {
	shownav('plugin');
	showsubmenu('nav_plugins', [
		['plugins_list', 'plugins', 0],
		['plugins_hooklist', 'plugins&operation=hooklist', 0],
		['plugins_validator', 'plugins&operation=upgradecheck', 1],
		['cloudaddons_plugin_link', 'cloudaddons&frame=no&operation=plugins&from=more', 0, 1],
		['cloudaddons_witframe_link', 'cloudaddons&frame=no&operation=witframe&from=more', 0, 1],
	]);
	showboxheader('', '', '', 1);
	if($nowarray) {
		showboxtitle('plugins_validator_nowupgrade');
		foreach($nowarray as $row) {
			showboxbody('hover', $row);
		}
	}
	if($newarray) {
		showboxtitle('plugins_validator_newversion');
		foreach($newarray as $row) {
			showboxbody('hover', $row);
		}
	}
	if($errarray) {
		showboxtitle('plugins_validator_error');
		foreach($errarray as $row) {
			showboxbody('hover', $row);
		}
	}
	showboxfooter(1);
}
	