<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

const NOROBOT = true;

$discuz_action = 141;

if(submitcheck('lostpwsubmit')) {
	loaducenter();
	$_GET['email'] = strtolower(trim($_GET['email']));
	if($_GET['username']) {
		[$tmp['uid'], , $tmp['email']] = uc_get_user(addslashes($_GET['username']));
		$tmp['email'] = strtolower(trim($tmp['email']));
		if($_GET['email'] != $tmp['email']) {
			showmessage('getpasswd_account_notmatch');
		}
		$member = getuserbyuid($tmp['uid'], 1);
	} else {
		$emailcount = table_common_member::t()->count_by_email($_GET['email'], 1);
		if(!$emailcount) {
			showmessage('lostpasswd_email_not_exist');
		}
		if($emailcount > 1) {
			showmessage('lostpasswd_many_users_use_email');
		}
		$member = table_common_member::t()->fetch_by_email($_GET['email'], 1);
		[$tmp['uid'], , $tmp['email']] = uc_get_user(addslashes($member['username']));
		$tmp['email'] = strtolower(trim($tmp['email']));
	}
	if(!$member) {
		showmessage('getpasswd_account_notmatch');
	} elseif($member['adminid'] == 1 || $member['adminid'] == 2) {
		showmessage('getpasswd_account_notmatch');
	}

	$table_ext = $member['_inarchive'] ? '_archive' : '';
	if($member['email'] != $tmp['email']) {
		C::t('common_member'.$table_ext)->update($tmp['uid'], ['email' => $tmp['email']]);
	}

	$memberauthstr = C::t('common_member_field_forum'.$table_ext)->fetch($member['uid']);
	[$dateline, $operation, $idstring] = explode("\t", $memberauthstr['authstr']);
	$interval = $_G['setting']['mailinterval'] > 0 ? (int)$_G['setting']['mailinterval'] : 300;
	if($dateline && $operation == 1 && $dateline > TIMESTAMP - $interval) {
		showmessage('getpasswd_has_send', '', ['interval' => $interval]);
	}

	$idstring = random(6);
	C::t('common_member_field_forum'.$table_ext)->update($member['uid'], ['authstr' => "{$_G['timestamp']}\t1\t$idstring"]);
	require_once libfile('function/mail');
	$get_passwd_message = [
		'tpl' => 'get_passwd',
		'var' => [
			'username' => $member['username'],
			'bbname' => $_G['setting']['bbname'],
			'siteurl' => $_G['setting']['securesiteurl'],
			'uid' => $member['uid'],
			'idstring' => $idstring,
			'clientip' => $_G['clientip'],
			'sign' => make_getpws_sign($member['uid'], $idstring),
		]
	];
	if(!sendmail("{$_GET['username']} <{$tmp['email']}>", $get_passwd_message)) {
		runlog('sendmail', "{$tmp['email']} sendmail failed.");
	}
	showmessage('getpasswd_send_succeed', $_G['siteurl'], [], ['showdialog' => 1, 'locationtime' => true]);
}

