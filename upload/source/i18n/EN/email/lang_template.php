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
	'hello' => 'Hello',
	'moderate_member_invalidate' => 'Rejected',
	'moderate_member_delete' => 'Delete',
	'moderate_member_validate' => 'Approved',

	'comma' => '，',
	'show_sender' => 'This email is sent by {$var[\'bbname\']}.',
	'show_reason' => 'You received this email because',
	'have_not_visit' => 'If you have not visited {$var[\'bbname\']},',
	'have_not_do_this' => 'or have not performed the above operation,',
	'not_interested' => 'If you are not interested in this,',
	'ignore_email' => 'please ignore this email.',
	'no_more_action' => 'You do not need to unsubscribe or take any further action.',
	'important' => 'Important!',
	'if_not_link' => 'If the above is not a link, please copy and paste the address into your browser address bar to visit',
	'msg_start' => 'Original Message Begins',
	'msg_end' => 'Original Message Ends',
	'not_responsible' => 'The website management team is not responsible for such emails.',
	'show_ip' => 'The IP of the requester is {$var[\'clientip\']}',
	'welcome_visit' => 'Welcome to {$_G[\'setting\'][\'bbname\']}',
	'thanks_for_visit' => 'Thank you for visiting, we wish you a pleasant experience!',
	'sincerely' => 'Sincerely',
	'admin_team' => '{$var[\'bbname\']} Management Team',


	'get_passwd_subject' => 'Password Reset Instructions',
	'get_passwd_reason' => 'This email address is registered as a user email at {$var[\'bbname\']}, and the user has requested to use the Email password reset function.',
	'get_passwd_if_not' => 'If you did not submit a password reset request or are not a registered user of {$var[\'bbname\']}, please ignore and delete this email immediately. You only need to continue reading the following content if you confirm that you need to reset your password.',
	'get_passwd_explain' => 'Password Reset Instructions',
	'get_passwd_click_link' => 'You just need to click the link below within three days after submitting the request to reset your password:',
	'get_passwd_new_pwd' => 'Enter your new password on the page opened by the link above and submit it, then you can log in to the website with your new password. You can change your password at any time in the User Control Panel.',

	'password_reset_subject' => 'Password Change Notification',
	'password_reset_reason' => 'This email is registered as a user email at {$var[\'bbname\']}, and the user has reset or changed the password.',
	'password_reset_if_not' => 'If you are not a registered user of {$var[\'bbname\']}, please ignore and delete this email immediately. You only need to continue reading the following content if you are a registered user of {$var[\'bbname\']}.',
	'password_reset_explain' => 'Your user account {$var[\'username\']} at {$var[\'bbname\']} had its password changed or reset on {$var[\'datetime\']}.',
	'password_reset_if_not_user_op' => 'If you did not perform a password change or reset, please log in to {$var[\'bbname\']} immediately to check your account status and change your password.',
	'password_reset_if_not_user_op_help' => 'If you have any questions or need assistance (such as freezing your account) while handling this issue, please contact the {$var[\'bbname\']} management team for more help and support.',

	'email_verify_subject' => 'Email Address Verification',
	'email_verify_reason' => 'A new user registered at {$var[\'bbname\']}, or a user modified their email and used this email address.',
	'email_verify_explain' => 'Account Activation Instructions',
	'email_verify_explain2' => 'If you are a new user of {$var[\'bbname\']}, or used this address when modifying your registered email, we need to verify the validity of your address to avoid spam or address abuse.',
	'email_verify_click_link' => 'You just need to click the link below to activate your account:',

	'email_reset_subject' => 'Email Address Change Notification',
	'email_reset_reason' => 'This email is registered as a user email at {$var[\'bbname\']}, and the user has changed their email address.',
	'email_reset_if_not' => 'If you are not a registered user of {$var[\'bbname\']}, please ignore and delete this email immediately. You only need to continue reading the following content if you are a registered user of {$var[\'bbname\']}.',
	'email_reset_explain' => 'Your user account {$var[\'username\']} at {$var[\'bbname\']} had its email address changed on {$var[\'datetime\']}.',
	'email_reset_new_email' => 'The new email address is: {$var[\'email\']} , verification email sent at: {$var[\'request_datetime\']}',
	'email_reset_if_not_user_op' => 'If you did not perform an email address change, please log in to {$var[\'bbname\']} immediately to check your account status, and change your password and email address.',
	'email_reset_if_not_user_op_help' => 'If you have any questions or need assistance (such as freezing your account) while handling this issue, please contact the {$var[\'bbname\']} management team for more help and support.',

	'secmobile_reset_subject' => 'Security Mobile Number Change Notification',
	'secmobile_reset_reason' => 'This email is registered as a user email at {$var[\'bbname\']}, and the user has changed their security mobile number.',
	'secmobile_reset_if_not' => 'If you are not a registered user of {$var[\'bbname\']}, please ignore and delete this email immediately. You only need to continue reading the following content if you are a registered user of {$var[\'bbname\']}.',
	'secmobile_reset_explain' => 'Your user account {$var[\'username\']} at {$var[\'bbname\']} had its security mobile number changed on {$var[\'datetime\']}.',
	'secmobile_reset_new_secmobile' => 'The new security mobile number is: {$var[\'secmobile\']}',
	'secmobile_reset_if_not_user_op' => 'If you did not perform a security mobile number change, please log in to {$var[\'bbname\']} immediately to check your account status, and change your password and security mobile number.',
	'secmobile_reset_if_not_user_op_help' => 'If you have any questions or need assistance (such as freezing your account) while handling this issue, please contact the {$var[\'bbname\']} management team for more help and support.',

	'email_register_subject' => 'Forum Registration Address',
	'email_register_reason' => 'A new user registration address was obtained at {$var[\'bbname\']} using this email address.',
	'email_register_explain' => 'New User Registration Instructions',
	'email_register_click_link' => 'You just need to click the link below to register as a user. The following link is valid for 3 days. After expiration, you can request to send a new email verification again:',

	'add_member_subject' => 'You Have Been Added as a Member',
	'add_member_intro' => 'I am {$var[\'adminusername\']}, one of the administrators of {$var[\'bbname\']}.',
	'add_member_reason' => 'You have just been added as a member of {$var[\'bbname\']}, and this email is the email address we registered for you.',
	'add_member_no_interest' => 'If you are not interested in {$var[\'bbname\']} or have no intention of becoming a member,',
	'add_member_info' => 'Account Information',
	'add_member_bbname' => 'Site Name:',
	'add_member_siteurl' => 'Site URL:',
	'add_member_newusername' => 'Username:',
	'add_member_newpassword' => 'Password:',
	'add_member_can_login' => 'From now on you can use your account to log in to {$var[\'bbname\']}, we wish you a pleasant experience!',

	'birthday_subject' => 'Happy Birthday To You',
	'birthday_reason' => 'This email address is registered as a user email at {$var[\'bbname\']},<br />
and according to the information you filled in, today is your birthday. It is a pleasure to send you birthday wishes at this time,<br />
on behalf of the {$var[\'bbname\']} management team, I sincerely wish you a happy birthday.',
	'birthday_if_not' => 'If you are not a member of {$var[\'bbname\']}, or today is not your birthday, it may be that someone misused your email address,<br />
or filled in incorrect birthday information. This email will not be sent repeatedly,',

	'email_to_friend_subject' => '{$_G[\'member\'][\'username\']} recommends to you: {$thread[\'subject\']}',
	'email_to_friend_sender' => 'This letter is sent by {$_G[\'member\'][\'username\']} of {$_G[\'setting\'][\'bbname\']}.',
	'email_to_friend_reason' => ' {$_G[\'member\'][\'username\']} recommended the following content to you through the "Recommend to Friend" function of {$_G[\'setting\'][\'bbname\']}.',
	'email_to_friend_not_official' => 'Please note that this letter is only sent by a user using "Recommend to Friend", it is not an official website email,',

	'email_to_invite_subject' => 'Your friend {$_G[\'member\'][\'username\']} sends you a {$_G[\'setting\'][\'bbname\']} site registration invitation code',
	'email_to_invite_reason' => ' {$_G[\'member\'][\'username\']} recommended the following content to you through the "Send invitation code to friend" function of {$var[\'bbname\']}.',
	'email_to_invite_not_official' => 'Please note that this letter is only sent by a user using "Send invitation code to friend", it is not an official website email,',

	'invitemail_subject' => '{username} invites you to join {sitename} and become friends',
	'invitemail_from' => 'Hi, I\'m {$var[\'username\']}, I invite you to join {$var[\'sitename\']} and become my friend',
	'invitemail_reason' => 'Please add me as your friend, so you can keep up with my latest activities, communicate with me, and stay in touch with me anytime.',
	'invitemail_start' => 'Invitation message:',
	'invitemail_accept_invite' => 'Please click the link below to accept the friend invitation:',
	'invitemail_viewpage' => 'If you already have an account on {$var[\'sitename\']}, please click the link below to view my personal homepage:',

	'moderate_member_invalidate' => 'Rejected',
	'moderate_member_delete' => 'Delete',
	'moderate_member_validate' => 'Approved',
	'moderate_member_subject' => 'User Review Result Notification',
	'moderate_member_reason' => 'This email address was used when a new user registered at {$var[\'bbname\']}, and the administrator has set that new users require manual review. This email will notify you of the review result of your submitted application.',
	'moderate_member_info' => 'Registration Information and Review Result',
	'moderate_member_username' => 'Username:',
	'moderate_member_regdate' => 'Registration Date:',
	'moderate_member_submitdate' => 'Submission Date:',
	'moderate_member_submittimes' => 'Submission Times:',
	'moderate_member_msg' => 'Registration Reason:',
	'moderate_member_modresult' => 'Review Result:',
	'moderate_member_moddate' => 'Review Date:',
	'moderate_member_adminusername' => 'Review Administrator:',
	'moderate_member_remark' => 'Administrator Remark:',
	'moderate_member_explain' => 'Review Result Explanation',
	'moderate_member_explain1' => 'Approved: Your registration has been approved, you have become an official user of {$var[\'bbname\']}.',
	'moderate_member_explain2' => 'Rejected: Your registration information is incomplete, or does not meet our requirements for new users. You can <a href="home.php?mod=spacecp&ac=profile" target="_blank">complete your registration information</a> based on the administrator\'s remark, then submit again.',
	'moderate_member_explain3' => 'Deleted: Your registration has been rejected because it deviates greatly from our requirements, or the number of new registrations on this site has exceeded expectations. Your account has been deleted from the database and can no longer be used to log in or submit for review again. We apologize for any inconvenience.',

	'adv_expiration_subject' => 'Your site advertisement will expire in {day} days, please handle it promptly',
	'adv_expiration_msg' => 'The following advertisement on your site will expire in {$var[\'day\']} days, please handle it promptly:',

	'invite_payment_subject' => 'Purchase Invitation Code',
	'invite_payment_msg' => 'Welcome to {$var[\'bbname\']} ({$var[\'siteurl\']}), your order {$var[\'orderid\']} has been paid and the order has been confirmed valid.',
	'invite_payment_invitecode' => 'Below are the invitation codes you obtained',

	'email_seccode_verify_subject' => 'Account Identity Verification',
	'email_seccode_code' => 'Your verification code is:',
	'email_seccode_verify_msg' => 'This is an email verification code sent by {$var[\'bbname\']} ({$var[\'siteurl\']}), this verification code is valid within {$var[\'emailinterval\']} minutes!',
	];

