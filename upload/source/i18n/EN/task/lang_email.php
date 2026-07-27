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
	'email_name' => 'Email Verification Task',
	'email_desc' => 'Verify your email to receive corresponding rewards.',
	'email_view' => '<strong>Please follow the instructions below to participate in this task:</strong>
		<ul>
		<li><a href="home.php?mod=spacecp&ac=profile&op=password" target="_blank">Open account settings page in new window</a></li>
		<li>On the newly opened settings page, fill in your real email address (newly filled email needs to be saved first), and click the "Resend verification email" link</li>
		<li>After a few minutes, the system will send you an email. After receiving the email, please follow the instructions in the email and visit the verification link in the email</li>
		</ul>',
	];
