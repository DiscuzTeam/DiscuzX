<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(FORMHASH != $_GET['formhash']) {
	cpmsg('undefined_action');
}

$conflictplugins = '';
$plugin = table_common_plugin::t()->fetch($_GET['pluginid']);
if(!$plugin) {
	cpmsg('plugin_not_found', '', 'error');
}
$dir = substr($plugin['directory'], 0, -1);
$modules = dunserialize($plugin['modules']);
$file = getimportfilename(DISCUZ_PLUGIN($dir).'/discuz_plugin_'.$dir.($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : ''));
if(!$file) {
	$pluginarray[$operation.'file'] = $modules['extra'][$operation.'file'];
	$pluginarray['plugin']['version'] = $plugin['version'];
} else {
	$importtxt = @implode('', file($file));
	$pluginarray = getimportdata('Discuz! Plugin');
}
if(!empty($pluginarray[$operation.'file']) && preg_match('/^[\w\.]+$/', $pluginarray[$operation.'file'])) {
	$filename = issafepath(DISCUZ_PLUGIN($dir).'/'.$pluginarray[$operation.'file']);
	if($filename) {
		$installlang = load_installlang($dir);
		@include $filename;
	}
}

$available = $operation == 'enable' ? 1 : 0;
if($operation == 'enable') {

	require_once libfile('cache/setting', 'function');
	list(, , $hookscript) = get_cachedata_setting_plugin($plugin['identifier']);
	$exists = [];
	foreach($hookscript as $script => $modules) {
		foreach($modules as $module => $data) {
			foreach(['funcs' => '', 'outputfuncs' => '_output', 'messagefuncs' => '_message'] as $functype => $funcname) {
				foreach($data[$functype] as $k => $funcs) {
					$pluginids = [];
					foreach($funcs as $func) {
						$pluginids[$func[0]] = $func[0];
					}
					if(in_array($plugin['identifier'], $pluginids) && count($pluginids) > 1) {
						unset($pluginids[$plugin['identifier']]);
						foreach($pluginids as $pluginid) {
							$exists[$pluginid][$k.$funcname] = $k.$funcname;
						}
					}
				}
			}
		}
	}
	$addonid = $dir.'.plugin';
	$array = cloudaddons_getmd5($addonid);
	$array = [];
	if(preg_match('/^[a-z0-9_\.]+$/i', $addonid) && file_exists(DISCUZ_DATA.'./addonmd5/'.$addonid.'.xml')) {
		require_once libfile('class/xml');
		$xml = implode('', @file(DISCUZ_DATA.'./addonmd5/'.$addonid.'.xml'));
		$array = xml2array($xml);
	} else {
		$array = false;
	}
	if(dfsockopen(cloudaddons_url('&from=s').'&mod=app&ac=vali'.'dator&ver=2&addonid='.$addonid.($array !== false ? '&rid='.$array['RevisionID'].'&sn='.$array['SN'].'&rd='.$array['RevisionDateline'] : ''), 0, '', '', false, CLOUDADDONS_DOWNLOAD_IP, 15) === '0') {
		$available = 0;
	}
	if($exists) {
		$plugins = [];
		foreach(table_common_plugin::t()->fetch_all_by_identifier(array_keys($exists)) as $plugin) {
			$plugins[] = '<b>'.$plugin['name'].'</b>:'.
				'&nbsp;<a href="javascript:;" onclick="display(\'conflict_'.$plugin['identifier'].'\')">'.cplang('plugins_conflict_view').'</a>'.
				'&nbsp;<a href="'.cloudaddons_pluginlogo_url($plugin['identifier']).'" target="_blank">'.cplang('plugins_conflict_info').'</a>'.
				'<span id="conflict_'.$plugin['identifier'].'" style="display:none"><br />'.implode(',', $exists[$plugin['identifier']]).'</span>';
		}
		$conflictplugins = '<div align="left" style="margin: auto 100px; border: 1px solid #DEEEFA;padding: 4px;line-height: 25px;">'.implode('<br />', $plugins).'</div>';
	}
}
table_common_plugin::t()->update($_GET['pluginid'], ['available' => $available]);
updatecache(['plugin', 'setting', 'styles']);
cleartemplatecache();
updatemenu('plugin');

if(file_exists(DISCUZ_PLUGIN($dir).'/block/blockclass.php')) {
	include_once libfile('function/block');
	blockclass_cache();
}

if($operation == 'enable') {
	if(!$conflictplugins) {
		cpmsg('plugins_enable_succeed', 'action=plugins'.(!empty($_GET['system']) ? '&system=1' : ''), 'succeed');
	} else {
		cpmsg('plugins_conflict', 'action=plugins'.(!empty($_GET['system']) ? '&system=1' : ''), 'succeed', ['plugins' => $conflictplugins]);
	}
} else {
	cpmsg('plugins_disable_succeed', 'action=plugins'.(!empty($_GET['system']) ? '&system=1' : ''), 'succeed');
}
cpmsg('plugins_'.$operation.'_succeed', 'action=plugins'.(!empty($_GET['system']) ? '&system=1' : ''), 'succeed');
	