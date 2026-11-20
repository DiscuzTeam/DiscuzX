<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(empty($_GET['script'])) {
	cpmsg('undefined_action');
}

if(!$_GET['confirmed'] || $_GET['formhash'] != FORMHASH) {
	cpmsg('tasks_uninstall_confirm', "action=tasks&operation=uninstall&script={$_GET['script']}", 'form');
}

$ids = [];
foreach(table_common_task::t()->fetch_all_by_scriptname($_GET['script']) as $task) {
	$ids[] = $task['taskid'];
}
if($ids) {
	table_common_task::t()->delete($ids);
	table_common_taskvar::t()->delete_by_taskid($ids);
	table_common_mytask::t()->delete_mytask(0, $ids);
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
if(method_exists($task, 'uninstall')) {
	$task->uninstall();
}

unset($custom_types[$_GET['script']]);
table_common_setting::t()->update_setting('tasktypes', $custom_types);
require_once libfile('class/task');
$tasklib = &task::instance();
$tasklib->update_available(1);

cpmsg('tasks_uninstalled', 'action=tasks&operation=type', 'succeed');
	