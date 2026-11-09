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
	cpmsg('undefined_action', '', 'error');
}

if(empty($_GET['script'])) {
	cpmsg('undefined_action');
}

if(table_common_task::t()->count_by_scriptname($_GET['script'])) {
	cpmsg('tasks_install_duplicate', '', 'error');
}

$escript = explode(':', $_GET['script']);
if(count($escript) > 1 && preg_match('/^[\w\_:]+$/', $_GET['script'])) {
	include_once DISCUZ_PLUGIN($escript[0]).'/task/task_'.$escript[1].'.php';
	$taskclass = 'task_'.$escript[1];
} else {
	require_once libfile('task/'.$_GET['script'], 'class');
	$taskclass = 'task_'.$_GET['script'];
}
$task = new $taskclass;
if(method_exists($task, 'install')) {
	$task->install();
}

$custom_types[$_GET['script']] = ['name' => lang('task/'.$_GET['script'], $task->name), 'version' => $task->version];
table_common_setting::t()->update_setting('tasktypes', $custom_types);
require_once libfile('class/task');
$tasklib = &task::instance();
$tasklib->update_available(1);

cpmsg('tasks_installed', 'action=tasks&operation=type', 'succeed');
	