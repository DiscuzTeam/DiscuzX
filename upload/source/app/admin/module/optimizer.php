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
if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');

$optimizer_option = [
	'optimizer_thread',
	'optimizer_setting',
	'optimizer_post',
	'optimizer_dbbackup',
	'optimizer_dbbackup_clean',
	'optimizer_seo'
];

$security_option = [
	'optimizer_inviteregister',
	'optimizer_emailregister',
	'optimizer_pwlength',
	'optimizer_regmaildomain',
	'optimizer_ipregctrl',
	'optimizer_newbiespan',
	'optimizer_editperdel',
	'optimizer_recyclebin',
	'optimizer_forumstatus',
	'optimizer_usergroup9',
	'optimizer_usergroup4',
	'optimizer_usergroup5',
	'optimizer_usergroup6',
	'optimizer_attachexpire',
	'optimizer_attachrefcheck',
	'optimizer_filecheck',
	'optimizer_plugin',
	'optimizer_loginpwcheck',
	'optimizer_loginoutofdate',
	'optimizer_dbbackup_visit',
	'optimizer_filesafe',
	'optimizer_remote',
];

$serversec_option = [
	'optimizer_dos8p3',
	'optimizer_httphost'
];

$check_record_time_key = 'check_record_time';
if(in_array($operation, ['security', 'serversec', 'performance'])) {
	$_GET['anchor'] = $operation;
	$operation = '';
}
if($_GET['anchor'] == 'security') {
	shownav('safe', 'menu_security');
	$optimizer_option = $security_option;
	$check_record_time_key = 'security_check_record_time';
	showsubmenu('menu_security');
} elseif($_GET['anchor'] == 'serversec') {
	shownav('safe', 'menu_serversec');
	$optimizer_option = $serversec_option;
	$check_record_time_key = 'serversec_check_record_time';
	showsubmenu('menu_serversec');
} elseif($_GET['anchor'] == 'performance') {
	shownav('founder', 'menu_optimizer');
	showsubmenu('menu_optimizer');
}

if($operation) {
	$type = $_GET['type'];
	if(!in_array($type, $optimizer_option)) {
		cpmsg('parameters_error', '', 'error');
	}

	include_once 'source/discuz_version.php';
	$optimizer = new admin\class_optimizer($type);
}

$_GET['anchor'] = in_array($_GET['anchor'], ['security', 'serversec', 'performance']) ? $_GET['anchor'] : 'security';

if($operation == 'optimize_unit') {
	if(FORMHASH != $_GET['formhash']) {
		cpmsg('undefined_action', '', 'error');
	}
	$optimizer->optimizer();
} elseif($operation == 'check_unit') {
	require_once childfile('optimizer/check_unit');
} elseif($operation == 'setting_optimizer') {
	require_once childfile('optimizer/setting');
} else {
	require_once childfile('optimizer/list');
}

