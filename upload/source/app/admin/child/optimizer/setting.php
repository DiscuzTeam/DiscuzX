<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(submitcheck('setting_optimizer', 2)) {
	$setting_options = $_GET['options'];
	if($optimizer->option_optimizer($setting_options)) {
		cpmsg('founder_optimizer_setting_succeed', 'action=optimizer&operation=setting_optimizer&type=optimizer_setting&anchor=performance', 'succeed');
	} else {
		cpmsg('founder_optimizer_setting_error', '', 'error');
	}
} else {

	showformheader('optimizer&operation=setting_optimizer&type=optimizer_setting');
	showtableheader();

	$option = $optimizer->get_option();

	echo '<tr class="header">';
	echo '<th></th>';
	echo '<th class="td24">'.$lang['founder_optimizer_setting_option'].'</th>';
	echo '<th>'.$lang['founder_optimizer_setting_option_description'].'</th>';
	echo '<th class="td24">'.$lang['founder_optimizer_setting_description'].'</th>';
	echo '</tr>';
	foreach($option as $setting) {
		$color = ' style="'.($setting[4] ? 'color:red;' : 'color:green').'"';
		echo '<tr>';
		echo '<td><input type="checkbox" name="options[]" value="'.$setting[0].'" '.($setting[4] ? 'checked' : 'disabled').' /></td>';
		echo '<td'.$color.'>'.$setting[1].'</td>';
		echo '<td'.$color.'>'.$setting[2].'</td>';
		echo '<td'.$color.'>'.$setting[3].'</td>';
		echo '</tr>';
	}
	showsubmit('setting_optimizer');

	showtablefooter();
	showformfooter();
}
	