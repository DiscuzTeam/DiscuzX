<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$s = '';
$exists = false;
$interfaces = account_base::getInterfaces();
if($interfaces) {
	foreach($interfaces as $interface) {
		$atype = account_base::Interfaces_aType()[$interface];
		if(empty($atype) && str_starts_with($interface, 'plugin_')) {
			$atype = account_base::getAccountType(substr($interface, 7));
		}
		if(empty($atype)) {
			continue;
		}
		$colums = [
			'<input class="checkbox" title="'.cplang('select_all').'" type="checkbox" name="chkallaccount'.$atype.'" onclick="checkAll(\'value\', this.form, \'account'.$atype.'\', \'chkallaccount'.$atype.'\')" id="chkallaccount_'.$atype.'" />',
			'<label for="chkallaccount_'.$atype.'"> '.account_base::getName($interface).'</label>', 'a'.$atype];
		foreach($perms as $perm) {
			$checked = str_contains($forum[$perm], "\ta$atype\t") ? 'checked="checked"' : NULL;
			$checked && $exists = true;
			$colums[] = '<input class="checkbox" type="checkbox" name="'.$perm.'[]" value="a'.$atype.'" chkvalue="account'.$atype.'" '.$checked.'>';
		}
		$s .= showtablerow('', ['', '', 'class="lightfont"'], $colums, true);
	}

	$tg = '<a href="javascript:;" onclick="toggle_group(\'gaccount\', this)">['.($exists ? '-' : '+').']</a>';
	showtablerow('', ['', '', 'class="lightfont" colspan="'.$permcolspan.'"'], [$tg, '<b>'.cplang('account').'</b>', 'account']);

	showtagheader('tbody', 'gaccount', $exists);
	echo $s;
	$permtits = [
		'<input class="checkbox" title="'.cplang('select_all').'" type="checkbox" name="chkallmaccount" onclick="checkAll(\'value\', this.form, \'maccount\', \'chkallmaccount\')" id="chkallmaccount" />',
		'<label for="chkallmaccount"> <b><i>'.cplang('forums_edit_perm_mustall').'</i></b></label>', ''];
	foreach($perms as $perm) {
		$checked = preg_match("/(^|\t)_a\[(.+?)\]/", $forum[$perm]) ? 'checked="checked"' : NULL;
		$permtits[] = '<input class="checkbox" type="checkbox" name="maccount['.$perm.']" value="'.$perm.'" chkvalue="maccount" '.$checked.'>';
	}
	showtablerow('', '', $permtits);
	showtagfooter('tbody');
}