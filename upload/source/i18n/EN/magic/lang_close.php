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
	'close_name' => 'Silence Card',
	'close_desc' => 'Can close a thread and prohibit replies',
	'close_expiration' => 'Close duration',
	'close_expiration_comment' => 'Set how long the thread can be closed, default 24 hours',
	'close_forum' => 'Forums allowed to use this magic item',
	'close_info' => 'Close the specified thread for {expiration} hours, please enter the thread ID',
	'close_info_nonexistence' => 'Please specify the thread to close',
	'close_succeed' => 'The thread you operated on has been closed',
	'close_info_noperm' => 'Sorry, this magic item is not allowed in the forum where this thread is located',
	'close_info_user_noperm' => 'Sorry, you cannot use this magic item on this person',

	'close_notification' => 'Your thread {subject} was used {magicname} by {actor}, <a href="forum.php?mod=viewthread&tid={tid}">go check it out!</a>',
	];

