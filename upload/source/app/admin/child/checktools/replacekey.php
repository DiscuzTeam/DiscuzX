<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$step = max(1, intval($_GET['step']));
shownav('tools', 'nav_replacekey');
showsubmenusteps('nav_replacekey', [
	['nav_replacekey_confirm', $step == 1],
	['nav_replacekey_verify', $step == 2],
	['nav_replacekey_completed', $step == 3]
]);
showtips('replacekey_tips');
if($step == 1) {
	cpmsg(cplang('replacekey_tips_step1'), 'action=checktools&operation=replacekey&step=2', 'form', '', FALSE);
} elseif($step == 2) {
	if(FORMHASH != $_GET['formhash']) {
		cpmsg('undefined_action', '', 'error');
	}
	cpmsg(cplang('replacekey_tips_step2'), 'action=checktools&operation=replacekey&step=3&formhash='.FORMHASH, 'loading', '', FALSE);
} elseif($step == 3) {
	if(FORMHASH != $_GET['formhash']) {
		cpmsg('undefined_action', '', 'error');
	}
	if(!is_writeable('./config/config_global.php')) {
		cpmsg('replacekey_must_write_config', '', 'error');
	}

	$oldauthkey = $_G['config']['security']['authkey'];
	$newauthkey = generate_key(64);

	$configfile = trim(file_get_contents(DISCUZ_ROOT.'./config/config_global.php'));
	$configfile = str_ends_with($configfile, '?>') ? substr($configfile, 0, -2) : $configfile;
	$configfile = str_replace($oldauthkey, $newauthkey, $configfile);

	if(file_put_contents(DISCUZ_ROOT.'./config/config_global.php', trim($configfile)) === false) {
		cpmsg('replacekey_must_write_config', '', 'error');
	}

	$ecdata = authcode($_G['setting']['ec_contract'], 'DECODE', $oldauthkey);
	$ecdata = authcode($ecdata, 'ENCODE', $newauthkey);
	table_common_setting::t()->update('ec_contract', $ecdata);

	$ftpdata = $_G['setting']['ftp'];
	$ftppasswd = authcode($ftpdata['password'], 'DECODE', md5($oldauthkey));
	$ftpdata['password'] = authcode($ftppasswd, 'ENCODE', md5($newauthkey));
	table_common_setting::t()->update('ftp', $ftpdata);

	updatecache('setting');

	cpmsg('replacekey_succeed', '', 'succeed', '', FALSE);
}
	