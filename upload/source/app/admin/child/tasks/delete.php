<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(empty($id)) {
	cpmsg('undefined_action');
}

if(!$_GET['confirmed'] || $_GET['formhash'] != FORMHASH) {
	cpmsg('tasks_del_confirm', "action=tasks&operation=delete&id=$id", 'form');
}

table_common_task::t()->delete($id);
table_common_taskvar::t()->delete_by_taskid($id);
table_common_mytask::t()->delete_mytask(0, $id);
require_once libfile('class/task');
$tasklib = &task::instance();
$tasklib->update_available(1);

cpmsg('tasks_del', 'action=tasks', 'succeed');
	