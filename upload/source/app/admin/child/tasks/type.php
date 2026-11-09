<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

shownav('extended', 'nav_tasks');
showsubmenu('nav_tasks', [
	['admin', 'tasks', 0],
	$submenus ? [['menu' => 'add', 'submenu' => $submenus]] : [],
	['nav_task_type', 'tasks&operation=type', 1]
]);
showtips('tasks_tips_add_type');

$tasks = gettasks();

showtableheader('', 'fixpadding');

if($tasks) {
	showsubtitle(['name', 'tasks_version', 'copyright', '']);
	foreach($tasks as $task) {
		showtablerow('', '', [
			$task['name'].($task['filemtime'] > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : ''),
			$task['version'],
			$task['copyright'],
			in_array($task['class'], $custom_scripts) ? "<a href=\"".ADMINSCRIPT."?action=tasks&operation=upgrade&script={$task['class']}&formhash=".FORMHASH."\" class=\"act\">{$lang['tasks_upgrade']}</a> <a href=\"".ADMINSCRIPT."?action=tasks&operation=uninstall&script={$task['class']}&formhash=".FORMHASH."\" class=\"act\">{$lang['tasks_uninstall']}</a><br />" : "<a href=\"".ADMINSCRIPT."?action=tasks&operation=install&script={$task['class']}&formhash=".FORMHASH."\" class=\"act\">{$lang['tasks_install']}</a>"
		]);
	}
} else {
	showtablerow('', '', $lang['task_module_nonexistence']);
}

showtablefooter();
	