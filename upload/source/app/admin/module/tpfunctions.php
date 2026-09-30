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

shownav('global', 'tpfunctions');

$operation = $operation ? $operation : 'weixinshare';
if($operation == 'index') {
	$operation = 'weixinshare';
}

showsubmenu('menu_setting_tpfunctions', [
	['menu_setting_weixinshare', 'tpfunctions&operation=weixinshare', $operation == 'weixinshare' ? 1 : 0],
	['menu_setting_pwa', 'tpfunctions&operation=pwa', $operation == 'pwa' ? 1 : 0],
]);

$file = childfile('tpfunctions/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}
require_once $file;