<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang =
	[
	'stick_name' => 'Sticky Thread Card',
	'stick_desc' => 'Can stick a thread to the top',
	'stick_expiration' => 'Sticky duration',
	'stick_expiration_comment' => 'Set how long the thread can be stuck, default 24 hours',
	'stick_forum' => 'Forums allowed to use this magic item',
	'stick_info' => 'Stick the specified thread for {expiration} hours, please enter the thread ID',
	'stick_info_nonexistence' => 'Please specify the thread to stick',
	'stick_succeed' => 'The thread you operated on has been stuck',
	'stick_info_noperm' => 'Sorry, this magic item is not allowed in the forum where this thread is located',

	'stick_notification' => 'Your thread {subject} was used {magicname} by {actor}, <a href="forum.php?mod=viewthread&tid={tid}">go check it out!</a>',
	];
