<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$rid = intval($_GET['rid']);
$rule = table_common_credit_rule::t()->fetch($rid);
list(, $isSub) = explode('/', $rule['action']);
if(!$isSub) {
	cpmsg('undefined_action', '', 'error');
}
if(empty($_GET['confirm']) || $_GET['formhash'] != FORMHASH) {
	cpmsg('credits_rule_delete_confirm', 'action=credits&operation=del&rid='.$rid.'&confirm=yes', 'form');
}
table_common_credit_rule::t()->delete($rid);

updatecache(['setting', 'creditrule']);
cpmsg('credits_update_succeed', 'action=credits&operation=list&anchor=policytable', 'succeed');
	