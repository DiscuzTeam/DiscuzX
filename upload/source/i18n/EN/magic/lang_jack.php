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
	'jack_name' => 'Jack',
	'jack_desc' => 'Can bump a thread up for a period of time, repeated use can extend the bump time',
	'jack_expiration' => 'Duration',
	'jack_expiration_comment' => 'Set how long the thread can be bumped up, default 1 hour',
	'jack_forum' => 'Forums allowed to use this magic item',
	'jack_info' => '<p class="mtn xw0 mbn">Bump up the specified thread for<span class="xi1 xw1 xs2"> {expiration} </span> hours.</p> <p class="mtn xw0 mbn">You have<span class="xi1 xw1 xs2"> {magicnum} </span>Jacks available now.</p>',
	'jack_num' => 'Number to use this time:',
	'jack_num_not_enough' => 'Insufficient magic items or number of uses not filled in.',
	'jack_info_nonexistence' => 'Please specify the thread to bump up',
	'jack_succeed' => 'Jack successfully bumped up the thread',
	'jack_info_noperm' => 'Sorry, this magic item is not allowed in the forum where this thread is located',

	'jack_notification' => 'Your thread {subject} was used {magicname} by {actor}, <a href="forum.php?mod=viewthread&tid={tid}">go check it out!</a>',
	];

