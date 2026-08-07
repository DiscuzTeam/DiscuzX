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
	'profile_name' => 'Complete User Profile Task',
	'profile_desc' => 'Complete specified user profile information to receive corresponding rewards',

	'profile_view' => '<strong>You still need to complete the following profile items:</strong><br>
		<span style="color:red;">{profiles}</span><br><br>
		<strong>Please follow the instructions below to complete this task:</strong>
		<ul>
		<li><a href="home.php?mod=spacecp&ac=profile" target="_blank" class="xi2">Click here to open profile settings page</a></li>
		<li>On the newly opened settings page, complete the above profile information</li>
		</ul>',
	];

