<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$plugin = table_common_plugin::t()->fetch($pluginid);
$dir = substr($plugin['directory'], 0, -1);
$modules = dunserialize($plugin['modules']);
if($modules['system']) {
	cpmsg('plugins_delete_error');
}
$installtype = $modules['extra']['installtype'];
$importfile = getimportfilename(DISCUZ_PLUGIN($dir).'/discuz_plugin_'.$dir.($installtype ? '_'.$installtype : ''));
if(!$importfile) {
	$pluginarray['checkfile'] = $modules['extra']['checkfile'];
	$pluginarray['uninstallfile'] = $modules['extra']['uninstallfile'];
} else {
	$importtxt = @implode('', file($importfile));
	$pluginarray = getimportdata('Discuz! Plugin');
}
if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
	$filename = issafepath(DISCUZ_PLUGIN($plugin['identifier']).'/'.$pluginarray['checkfile']);
	if($filename) {
		loadcache('pluginlanguage_install');
		$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
		@include $filename;
	}
}
$identifier = $plugin['identifier'];
table_common_plugin::t()->delete($pluginid);
table_common_pluginvar::t()->delete_by_pluginid($pluginid);
table_common_nav::t()->delete_by_type_identifier(3, $identifier);

foreach(['script', 'template'] as $type) {
	loadcache('pluginlanguage_'.$type, 1);
	if(isset($_G['cache']['pluginlanguage_'.$type][$identifier])) {
		unset($_G['cache']['pluginlanguage_'.$type][$identifier]);
		savecache('pluginlanguage_'.$type, $_G['cache']['pluginlanguage_'.$type]);
	}
}

updatecache(['plugin', 'setting', 'styles']);
cleartemplatecache();
updatemenu('plugin');

if(file_exists(DISCUZ_PLUGIN($dir).'/block/blockclass.php')) {
	$blockstyles= table_common_block_style::t()->fetch_all_by_where(DB::field('blockclass', $plugin['identifier'].'%', 'like'), '', 0, 0);
	foreach($blockstyles as $blockstyle) {
		$f = substr($blockstyle['blockclass'], strlen($plugin['identifier']) + 1);
		if(file_exists(DISCUZ_PLUGIN($dir).'/block/block_'.$f.'.php')) {
			table_common_block_style::t()->delete($blockstyle['styleid']);
		}
	}
	include_once libfile('function/block');
	blockclass_cache();
}

if(!empty($pluginarray['uninstallfile']) && preg_match('/^[\w\.]+$/', $pluginarray['uninstallfile'])) {
	$filename = issafepath(DISCUZ_PLUGIN($plugin['identifier']).'/'.$pluginarray['uninstallfile']);
	if($filename) {
		loadcache('pluginlanguage_install');
		$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
		@include $filename;
	}
}

cron_delete($dir);

loadcache('pluginlanguage_install', 1);
if(!empty($_G['cache']['pluginlanguage_install']) && isset($_G['cache']['pluginlanguage_install'][$identifier])) {
	unset($_G['cache']['pluginlanguage_install'][$identifier]);
	savecache('pluginlanguage_install', $_G['cache']['pluginlanguage_install']);
}

cloudaddons_uninstall($dir.'.plugin', DISCUZ_PLUGIN($dir));
cpmsg('plugins_delete_succeed', 'action=plugins', 'succeed');
	