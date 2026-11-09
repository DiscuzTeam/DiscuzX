<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if($do == 'add') {
	if(FORMHASH != $_GET['formhash']) {
		cpmsg('undefined_action', '', 'error');
	}
	$addcustom = strip_tags($_GET['addcustom'] ?? '');
	if($addcustom) {
		if(!($customid = table_common_advertisement_custom::t()->get_id_by_name($addcustom))) {
			$customid = table_common_advertisement_custom::t()->insert(['name' => $addcustom], true);
		}
		dheader('location: '.ADMINSCRIPT.'?action=adv&operation=add&type=custom&customid='.$customid);
	}
} elseif($do == 'edit') {
	$custom = table_common_advertisement_custom::t()->fetch($_GET['id']);
	$name = $custom['name'];
	if(!submitcheck('submit')) {
		ajaxshowheader();
		showformheader("adv&operation=custom&do=edit&id={$_GET['id']}");
		echo $lang['adv_custom_edit'].'<br /><input name="customnew" class="txt" value="'.dhtmlspecialchars($name).'" />&nbsp;'.
			'<input name="submit" class="btn" type="submit" value="'.$lang['submit'].'" />&nbsp;'.
			'<input class="btn" type="button" onclick="location.href=\''.ADMINSCRIPT.'?action=adv&operation=list\'" value="'.$lang['cancel'].'" />';
		showformfooter();
		ajaxshowfooter();
	} else {
		$customnew = strip_tags($_GET['customnew']);
		if($_GET['customnew'] != $name) {
			table_common_advertisement_custom::t()->update($_GET['id'], ['name' => $customnew]);
		}
	}
} elseif($do == 'delete') {
	if(!submitcheck('submit')) {
		ajaxshowheader();
		showformheader("adv&operation=custom&do=delete&id={$_GET['id']}");
		echo $lang['adv_custom_delete'].'<br /><input name="submit" class="btn" type="submit" value="'.$lang['delete'].'" />&nbsp;'.
			'<input class="btn" type="button" onclick="location.href=\''.ADMINSCRIPT.'?action=adv&operation=list\'" value="'.$lang['cancel'].'" />';
		showformfooter();
		ajaxshowfooter();
	} else {
		table_common_advertisement_custom::t()->delete($_GET['id']);
	}
}
dheader('location: '.ADMINSCRIPT.'?action=adv&operation=list');
	