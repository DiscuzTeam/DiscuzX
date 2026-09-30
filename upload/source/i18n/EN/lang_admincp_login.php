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
	'admincp_title' => '{bbname} Admin CP',
	'login_title' => 'Login to Admin CP',
	'login_username' => 'Username',
	'login_password' => 'Password',
	'login_dk_light_mode' => 'Light Mode',
	'login_dk_by_system' => 'Follow System',
	'login_dk_normal_mode' => 'Normal Mode',
	'login_dk_dark_mode' => 'Dark Mode',

	'submit' => 'Submit',
	'forcesecques' => 'Required',
	'security_question' => 'Security Question',
	'security_answer' => 'Answer',
	'security_question_0' => 'No Security Question',
	'security_question_1' => 'Mother\'s Name',
	'security_question_2' => 'Grandfather\'s Name',
	'security_question_3' => 'Father\'s Birth City',
	'security_question_4' => 'Name of One of Your Teachers',
	'security_question_5' => 'Your Personal Computer Model',
	'security_question_6' => 'Your Favorite Restaurant Name',
	'security_question_7' => 'Last 4 Digits of Driver License',
	'other_loginname' => 'Other User Login',

	'login_tips' => 'Discuz! is a professional community-based website building platform that helps websites achieve one-stop services.',
	'login_nosecques' => 'You have not set up secure login yet. Please set your security question in the User Control Panel before accessing the Admin CP. You can <a href="forum.php?mod=memcp&action=profile&typeid=1" target="_blank">click here</a> to set up your security question.',
	'copyright' => '&copy; 2001-'.date('Y').' <a href="https://code.dismall.com/" target="_blank">Discuz! Team</a>.',

	'login_cp_guest' => '<h1>You are not logged in</h1><a href="member.php?mod=logging&action=login" class="btn">Login</a><p>When the webmaster requires mandatory login, modify config/config_global.php to disable this feature.</p>',
	'login_cplock' => 'Your admin panel has been locked!<br>Please revisit the admin CP after <b> {ltime} </b> seconds.',
	'login_user_lock' => 'Due to too many failed login attempts, this login request has been rejected. Please try again in 15 minutes.',
	'login_cp_noaccess' => '<b>Admin CP (or this operation) is not available for the current account</b><br><br>Please log in again with an account that has permission',
	'login_ip_noaccess' => '<a href="https://www.dismall.com/thread-17514-1-1.html" target="_blank">IP changes may cause login failure, view solutions</a>',
	'noaccess' => 'Admin management permissions (or this operation) are not available to you yet. Please contact the site administrator',

	'qrcode_login' => 'QR Code Login',
	'pwd_login' => 'Account Login',
	'qrcode_wechat_scan' => 'Please use WeChat to scan the QR code to login',

	];

