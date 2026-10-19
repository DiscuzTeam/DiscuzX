<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$username = trim($_GET['username']);
$usernamelen = dstrlen($username);
if($usernamelen < 3) {
	showmessage('profile_username_tooshort', '', [], ['handle' => false]);
} elseif($usernamelen > 15) {
	showmessage('profile_username_toolong', '', [], ['handle' => false]);
}

loaducenter();
$ucresult = uc_user_checkname($username);

if($ucresult == -1) {
	showmessage('profile_username_illegal', '', [], ['handle' => false]);
} elseif($ucresult == -2) {
	showmessage('profile_username_protect', '', [], ['handle' => false]);
} elseif($ucresult == -3) {
	if(table_common_member::t()->fetch_by_username($username) || table_common_member_archive::t()->fetch_by_username($username)) {
		showmessage('register_check_found', '', [], ['handle' => false]);
	} else {
		showmessage('register_activation', '', [], ['handle' => false]);
	}
} elseif(table_common_member::t()->fetch_by_username($username) || table_common_member_archive::t()->fetch_by_username($username)) {
	showmessage('register_check_found', '', [], ['handle' => false]);
}

if(table_common_member_username_history::t()->fetch($username)) {
	showmessage('register_check_found', '', [], ['handle' => false]);
}

$censorexp = '/^('.str_replace(['\\*', "\r\n", ' '], ['.*', '|', ''], preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';
if($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
	showmessage('profile_username_protect', '', [], ['handle' => false]);
}
	