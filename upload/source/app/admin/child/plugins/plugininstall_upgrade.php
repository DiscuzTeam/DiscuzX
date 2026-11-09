<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$finish = FALSE;
$dir = $_GET['dir'];
$installtype = str_replace('/', '', $_GET['installtype']);
$extra = $installtype ? '_'.$installtype : '';
$importfile = getimportfilename(DISCUZ_PLUGIN().$dir.'/discuz_plugin_'.$dir.$extra);
if(!$importfile) {
	cpmsg('plugin_file_error', '', 'error');
}
$importtxt = @implode('', file($importfile));
$pluginarray = getimportdata('Discuz! Plugin');
if($operation == 'plugininstall') {
	$filename = $pluginarray['installfile'];
} else {
	$filename = $pluginarray['upgradefile'];
	$toversion = $pluginarray['plugin']['version'];
}
$installlang = load_installlang($dir);

if(!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
	$filename = issafepath(DISCUZ_PLUGIN().$dir.'/'.$filename);
	if($filename) {
		@include_once $filename;
	} else {
		$finish = TRUE;
	}
} else {
	$finish = TRUE;
}

if($finish) {
	updatecache('setting');
	updatemenu('plugin');
	if($operation == 'plugininstall') {
		cloudaddons_clear('plugin', $dir);
		cpmsg('plugins_install_succeed', 'action=plugins&hl='.$_GET['pluginid'], 'succeed');
	} else {
		cloudaddons_clear('plugin', $dir);
		cpmsg('plugins_upgrade_succeed', 'action=plugins', 'succeed', ['toversion' => $toversion]);
	}
}
	