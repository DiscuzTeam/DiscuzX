<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('MITFRAME_APP')) {
	exit('Access Denied');
}

if(!empty($_GET['redirect'])) {
	[$identifier, $module] = explode(':', $_GET['redirect'].':');

	if(!preg_match('/^[\w\_]+$/', $identifier) || !preg_match('/^[a-z0-9_\-]+$/i', $module)) {
		header('HTTP/1.1 404 Not Found');
		exit;
	}

	$f = dirname(dirname(dirname(__FILE__))).'/plugin/'.$identifier.'/'.$module.'.inc.php';
	if(!file_exists($f)) {
		header('HTTP/1.1 404 Not Found');
		exit;
	}
	require_once $f;
	exit;
}

const APPTYPEID = 127;
const NOT_IN_MOBILE_API = 1;

require './source/class/class_core.php';

$discuz = C::app();

$cachelist = ['plugin', 'diytemplatename'];

$discuz->cachelist = $cachelist;
$discuz->init();

if(!empty($_GET['id'])) {
	[$identifier, $module] = explode(':', $_GET['id'].':');
	$module = $module !== '' ? $module : $identifier;
} else {
	showmessage('plugin_nonexistence');
}
$mnid = 'plugin_'.$identifier.'_'.$module;
$pluginmodule = $_G['setting']['pluginlinks'][$identifier][$module] ?? ($_G['setting']['plugins']['script'][$identifier][$module] ?? ['adminid' => 0, 'directory' => preg_match('/^[a-z]+[a-z0-9_]*$/i', $identifier) ? $identifier.'/' : '']);

if(!preg_match('/^[\w\_]+$/', $identifier)) {
	showmessage('plugin_nonexistence');
}

if(empty($identifier) || !preg_match('/^[a-z0-9_\-]+$/i', $module) || !in_array($identifier, $_G['setting']['plugins']['available'])) {
	showmessage('plugin_nonexistence');
} elseif($pluginmodule['adminid'] && ($_G['adminid'] < 1 || ($_G['adminid'] > 0 && $pluginmodule['adminid'] < $_G['adminid']))) {
	showmessage('plugin_nopermission');
} elseif(@!file_exists($modfile = DISCUZ_PLUGIN($pluginmodule['directory']).$module.'.inc.php')) {
	showmessage('plugin_module_nonexistence', '', ['mod' => $pluginmodule['directory'].$module.'.inc.php']);
}

define('CURMODULE', $identifier);
runhooks();

include $modfile;

