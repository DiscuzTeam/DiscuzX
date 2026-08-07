<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = [
	'name' => 'International',
	'select' => 'Please select the language you want to install',

	'title_install' => SOFT_NAME.' Installation Wizard',
	'agreement_yes' => 'Agree',
	'agreement_no' => 'Cancel',
	'agreement_notice' => 'To ensure a smooth installation, please carefully read and fully understand the terms of the license agreement',
	'notset' => 'Unlimited',
	'enable' => 'Enable',
	'disable' => 'Disable',

	'message_title' => 'Message',
	'error_message' => 'Error Message',
	'message_return' => 'Return',
	'return' => 'Return',
	'install_wizard' => 'Installation Wizard',
	'config_nonexistence' => 'Configuration file does not exist',
	'nodir' => 'Directory does not exist',
	'redirect' => 'The browser will redirect automatically. No manual intervention is required.<br>If your browser does not redirect automatically, please click here',
	'auto_redirect' => 'The browser will redirect automatically. No manual intervention is required',
	'database_errno_1064' => 'SQL Syntax Error',

	'dbpriv_createtable' => 'No CREATE TABLE permission, cannot continue installation',
	'dbpriv_insert' => 'No INSERT permission, cannot continue installation',
	'dbpriv_select' => 'No SELECT permission, cannot continue installation',
	'dbpriv_update' => 'No UPDATE permission, cannot continue installation',
	'dbpriv_delete' => 'No DELETE permission, cannot continue installation',
	'dbpriv_droptable' => 'No DROP TABLE permission, cannot install',

	'db_not_null' => 'UCenter has already been installed in the database. Continuing installation will erase existing data.',
	'db_drop_table_confirm' => 'Continuing installation will erase all existing data. Are you sure you want to continue?',

	'writeable' => 'Writable',
	'unwriteable' => 'Unwritable',
	'old_step' => 'Previous',
	'new_step' => 'Next',
	'start_install' => 'Start Installation',

	'database_errno_2003' => 'Cannot connect to database, please check if the database is running and if the database server address is correct',
	'database_errno_1044' => 'Cannot create new database, please check if the database name is correct',
	'database_errno_1045' => 'Cannot connect to database, please check if the database username or password is correct',
	'database_connect_error' => 'Database Connection Error',
	'status_bbclosed_invalid' => 'Your site is not closed yet. Please close the site before upgrading',
	'status_plugin_available' => 'Your plugins are not disabled yet. Please disable all plugins before upgrading',
	'run_sql_error' => 'Discuz! Database Error',

	'step_title_1' => 'Check Installation Environment',
	'step_title_2' => 'Configure Runtime Environment',
	'step_title_3' => 'Create Database',
	'step_title_4' => 'Install',
	'step_title_3u' => 'Precautions',
	'step_title_4u' => 'Upgrade',
	'step_env_check_title' => 'Start Installation',
	'step_env_check_desc' => 'Environment and directory/file permission check',
	'step_db_init_title' => 'Install Database',
	'step_db_init_desc' => 'Performing database installation',
	'step_upgrade_title' => 'Start Upgrade',
	'step_upgrade_desc' => 'Performing database upgrade',
	'step_upgrade_confirm_title' => 'Precautions',
	'step_upgrade_confirm_desc' => 'Please carefully read the following precautions before starting the upgrade',

	'step1_file' => 'Directory/File',
	'step1_need_status' => 'Required Status',
	'step1_status' => 'Current Status',
	'not_continue' => 'Please fix the above errors marked with red X and try again',

	'tips_dbinfo' => 'Fill in Database Information',
	'tips_dbinfo_comment' => '',
	'tips_admininfo' => 'Fill in Administrator Information',
	'step_ext_info_title' => 'Installation successful.',
	'step_ext_info_comment' => 'Click to enter login',

	'ext_info_succ' => 'Installation successful.',
	'install_submit' => 'Submit',
	'install_locked' => 'Installation locked. Already installed. If you are sure you want to reinstall, please delete on the server<br /> '.str_replace(ROOT_PATH,'',$lockfile).'<br /><br />If you want to access the Toolbox, please rename this installation file (./install/index.php), then access using the modified filename',
	'error_stuck_msg' => 'The installation process has not progressed for a long time. The request may have exited abnormally due to network timeout or severe server error',
	'error_quit_msg' => 'You must resolve the above issues before continuing',
	'error_reinstall_msg' => 'Your database may have poor InnoDB performance. Please increase the PHP timeout, refresh the page and try reinstalling',

	'step_app_reg_title' => 'Configure Runtime Environment',
	'step_app_reg_desc' => 'Detect server environment and configure UCenter',
	'tips_ucenter' => 'Please fill in UCenter related information',
	'tips_ucenter_comment' => 'UCenter is the core service program of Comsenz products. The installation and operation of Discuz! Board depend on this program. If you have already installed UCenter, please fill in the following information. Otherwise, please go to <a href="https://www.discuz.vip/" target="blank">Comsenz Product Center</a> to download and install it before continuing.',

	'advice_mysqli_connect' => 'Please check if the mysqli module is loaded correctly',
	'advice_xml_parser_create' => 'This function requires PHP XML support. Please contact your service provider to confirm this feature is enabled',
	'advice_json_encode' => 'This function requires PHP JSON support. Please contact your service provider to confirm this feature is enabled',
	'advice_dns_get_record' => 'This function requires PHP DNS query support, which is included in PHP by default. This issue is often caused by incorrect compilation installation or missing components. Please contact your service provider to confirm this feature is enabled',
	'advice_fsockopen' => 'This function requires the allow_url_fopen option in php.ini to be enabled. Please contact your service provider to confirm this feature is enabled',
	'advice_pfsockopen' => 'This function requires the allow_url_fopen option in php.ini to be enabled. Please contact your service provider to confirm this feature is enabled',
	'advice_stream_socket_client' => 'This function requires the stream_socket_client function in php.ini to be enabled. Please contact your service provider to confirm this feature is enabled',
	'advice_curl_init' => 'This function requires the curl_init function in php.ini to be enabled. Please contact your service provider to confirm this feature is enabled',

	'ucurl' => 'UCenter URL',
	'ucpw' => 'UCenter Founder Password',
	'ucip' => 'UCenter IP Address',
	'ucenter_ucip_invalid' => 'Format error, please enter a valid IP address',
	'ucip_comment' => 'In most cases you can leave this blank',

	'tips_siteinfo' => 'Please fill in site information',
	'sitename' => 'Site Name',
	'siteurl' => 'Site URL',

	'forceinstall' => 'Force Installation',
	'dbinfo_forceinstall_invalid' => 'The current database already contains tables with the same table prefix. You can modify the "table prefix" to avoid deleting old data, or choose force installation. Force installation will delete old data and cannot be recovered',
	'dbinfo_myisam2innodb_invalid' => 'If InnoDB performance is poor and installation times out, you can try this method to force installation',

	'click_to_back' => 'Click to return to previous step',
	'adminemail' => 'System Email',
	'adminemail_comment' => 'Used to send program error reports',
	'dbhost_comment' => 'Usually 127.0.0.1 or localhost',
	'dbname_comment' => 'Database for installing Discuz!',
	'dbuser_comment' => 'Your database username',
	'dbpw_comment' => 'Your database password',
	'tablepre_comment' => 'When running multiple forums on the same database, please modify the prefix',
	'forceinstall_check_label' => 'I want to delete data, force installation !!!',
	'myisam2innodb_check_label' => 'Install with MyISAM, then convert to InnoDB',
	'initdbresult_succ' => 'Database table creation completed',
	'initdbdataresult_succ' => 'Database data initialization completed',
	'initdbinnodbresult_succ' => 'InnoDB table conversion completed',
	'initsys' => 'System initializing',

	'uc_url_empty' => 'You did not fill in the UCenter URL, please go back and fill it in',
	'uc_url_invalid' => 'URL format error',
	'uc_url_unreachable' => 'The UCenter URL may be incorrect. Possible reasons:<br />1. Incorrect UCenter path or abnormal status<br />2. Application cannot initiate or is blocked from querying UCenter status<br />3. "Add application via URL" feature is not enabled in UCenter admin panel',
	'uc_ip_invalid' => 'Cannot resolve this domain name, please enter the site IP',
	'uc_admin_invalid' => 'UCenter founder password verification failed. Possible reasons:<br />1. Incorrect UCenter founder password<br />2. Founder user and IP address locked due to multiple incorrect password attempts<br />3. "Add application via URL" feature is not enabled in UCenter admin panel',
	'uc_data_invalid' => 'Communication failed, please check if the UCenter URL is correct',
	'uc_dbcharset_incorrect' => 'UCenter database character set does not match the current application character set',
	'uc_api_add_app_error' => 'Error adding application to UCenter',
	'uc_dns_error' => 'UCenter DNS resolution error, please go back and fill in the UCenter IP address',

	'ucenter_ucurl_invalid' => 'UCenter URL is empty or format is incorrect, please check',
	'ucenter_ucpw_invalid' => 'UCenter founder password is empty or format is incorrect, please check',
	'siteinfo_siteurl_invalid' => 'Site URL is empty or format is incorrect, please check',
	'siteinfo_sitename_invalid' => 'Site name is empty or format is incorrect, please check',
	'dbinfo_dbhost_invalid' => 'Database server is empty or format is incorrect, please check',
	'dbinfo_dbname_invalid' => 'Database name is empty or format is incorrect, please check',
	'dbinfo_dbuser_invalid' => 'Database username is empty or format is incorrect, please check',
	'dbinfo_dbpw_invalid' => 'Database password is empty or format is incorrect, please check',
	'dbinfo_adminemail_invalid' => 'System email is empty or format is incorrect, please check',
	'dbinfo_tablepre_invalid' => 'Table prefix is empty or format is incorrect, please check',
	'admininfo_username_invalid' => 'Administrator username is empty or format is incorrect, please check',
	'admininfo_email_invalid' => 'Administrator email is empty or format is incorrect, please check',
	'admininfo_password_invalid' => 'Administrator password is empty, please fill in',
	'admininfo_password2_invalid' => 'The two passwords do not match, please check',

	'install_dzstandalone' => '<div class="selradio"><input type="radio" id="install_ucenter_standalone"'.(getgpc('install_ucenter')!='no'?' checked="checked"':'').' name="install_ucenter" value="standalone" onclick="if(this.checked)$(\'form_items_2\').style.display=\'none\';" /><label for="install_ucenter_standalone">Fresh Install Discuz! X</label></div>',
	'install_dzfull' => '<div class="selradio"><input type="radio" id="install_ucenter_yes"'.(getgpc('install_ucenter')!='no'?' checked="checked"':'').' name="install_ucenter" value="yes" onclick="if(this.checked)$(\'form_items_2\').style.display=\'none\';" /><label for="install_ucenter_yes">Fresh Install Discuz! X with UCenter Server</label></div>',
	'install_dzonly' => '<div class="selradio"><input type="radio" id="install_ucenter_no"'.(getgpc('install_ucenter')=='no'?' checked="checked"':'').' name="install_ucenter" value="no" onclick="if(this.checked)$(\'form_items_2\').style.display=\'\';" /><label for="install_ucenter_no">Connect to Existing UCenter Server</label></div>',
	'upgrade_upgrade' => '<div class="selradio"><input type="radio" id="upgrade_ucenter_standalone"'.(getgpc('install_ucenter')=='upgrade'?' checked="checked"':'').' name="install_ucenter" value="upgrade" onclick="if(this.checked)$(\'form_items_2\').style.display=\'none\';" /><label for="upgrade_ucenter_standalone">Upgrade from Discuz! X3.5</label></div>',

	'username' => 'Administrator Account',
	'email' => 'Administrator Email',
	'password' => 'Administrator Password',
	'password_comment' => 'Administrator password cannot be empty',
	'password2' => 'Confirm Password',

	'admininfo_invalid' => 'Incomplete administrator information, please check administrator account, password, and email',
	'dbname_invalid' => 'Database name is empty, please enter the database name',
	'tablepre_invalid' => 'Table prefix is empty or format is incorrect, please check',
	'admin_username_invalid' => 'Invalid username. Username should not exceed 15 English characters and should not contain special characters. Usually Chinese, letters or numbers',
	'admin_password_invalid' => 'Password does not match the one above, please re-enter',
	'admin_email_invalid' => 'Invalid email address. This email address is already in use or has invalid format, please use a different address',
	'admin_invalid' => 'Your administrator information is incomplete, please carefully fill in each item',
	'admin_exist_password_error' => 'This user already exists. If you want to set this user as the forum administrator, please enter the user\'s password correctly, or change the forum administrator\'s name',

	'tagtemplates_subject' => 'Title',
	'tagtemplates_uid' => 'User ID',
	'tagtemplates_username' => 'Poster',
	'tagtemplates_dateline' => 'Date',
	'tagtemplates_url' => 'Thread URL',

	'uc_version_incorrect' => 'Your UCenter server version is too low. Please upgrade UCenter server to the latest version and then upgrade. Download URL: https://www.discuz.vip/.',
	'config_unwriteable' => 'The installation wizard cannot write to the configuration file. Please set config.inc.php file permissions to writable (777)',

	'install_in_processed' => 'Installing...',
	'install_succeed' => 'Installation successful, click to enter',

	'init_credits_karma' => 'Reputation',
	'init_credits_money' => 'Money',

	'init_postno0' => 'Poster',
	'init_postno1' => 'Sofa)',
	'init_postno2' => 'Bench',
	'init_postno3' => 'Floor',

	'init_support' => 'Support',
	'init_opposition' => 'Oppose',

	'init_group_0' => 'Member',
	'init_group_1' => 'Administrator',
	'init_group_2' => 'Super Moderator',
	'init_group_3' => 'Moderator',
	'init_group_4' => 'Muted',
	'init_group_5' => 'Access Denied',
	'init_group_6' => 'IP Banned',
	'init_group_7' => 'Guest',
	'init_group_8' => 'Awaiting Validation',
	'init_group_9' => 'Beggar',
	'init_group_10' => 'Newbie',
	'init_group_11' => 'Registered Member',
	'init_group_12' => 'Intermediate Member',
	'init_group_13' => 'Senior Member',
	'init_group_14' => 'Gold Member',
	'init_group_15' => 'Forum Veteran',

	'init_rank_1' => 'Freshman',
	'init_rank_2' => 'Novice',
	'init_rank_3' => 'Intern Reporter',
	'init_rank_4' => 'Freelance Writer',
	'init_rank_5' => 'Distinguished Writer',

	'init_cron_1' => 'Reset Today\'s Post Count',
	'init_cron_2' => 'Reset Monthly Online Time',
	'init_cron_3' => 'Daily Data Cleanup',
	'init_cron_4' => 'Birthday Statistics and Email Wishes',
	'init_cron_5' => 'Thread Reply Notification',
	'init_cron_6' => 'Daily Announcement Cleanup',
	'init_cron_7' => 'Time-limited Operation Cleanup',
	'init_cron_8' => 'Forum Promotion Cleanup',
	'init_cron_9' => 'Monthly Thread Cleanup',
	'init_cron_10' => 'Daily X-Space User Update',
	'init_cron_11' => 'Weekly Thread Update',

	'init_bbcode_1' => 'Scrolls content horizontally. This effect is similar to the HTML marquee tag. Note: This effect only works in Internet Explorer.',
	'init_bbcode_2' => 'Embed Flash Animation',
	'init_bbcode_3' => 'Show QQ online status. Click this icon to chat with him/her',
	'init_bbcode_4' => 'Superscript',
	'init_bbcode_5' => 'Subscript',
	'init_bbcode_6' => 'Embed Windows Media Audio',
	'init_bbcode_7' => 'Embed Windows Media Audio or Video',

	'init_qihoo_searchboxtxt' =>'Enter keywords to quickly search this forum',
	'init_threadsticky' =>'Global Sticky, Category Sticky, Forum Sticky',

	'init_default_style' => 'Default Style',
	'init_default_forum' => 'Default Forum',
	'init_default_template' => 'Default Template Set',
	'init_default_template_copyright' => 'Discuz!',

	'init_dataformat' => 'Y-n-j',
	'init_modreasons' => 'Advertising/SPAM\\r\\nMalicious Flooding\\r\\nViolating Content\\r\\nOff-topic\\r\\nDuplicate Post\\r\\n\\r\\nI Agree\\r\\nQuality Article\\r\\nOriginal Content',
	'init_userreasons' => 'Awesome!\\r\\nNothing Matters\\r\\nThumbs Up!\\r\\nCopycat\\r\\nCalm',
	'init_link' => 'Discuz! Official Forum',
	'init_link_note' => 'Provides the latest Discuz! product news, software downloads and technical discussions',

	'init_promotion_task' => 'Website Promotion Task',
	'init_gift_task' => 'Red Packet Task',
	'init_avatar_task' => 'Avatar Task',

	'copyright' => '&copy; 2001-'.date('Y').' <a href="https://code.dismall.com/" target="_blank">Discuz! Team</a>.',

	'license' => '
<div class="license"><h1>English License Agreement</h1>
<p>Copyright (c) 2001-'.date('Y').' Hefei Erdao Network Technology Co., Ltd. and Tencent Technology (Beijing) Co., Ltd. All rights reserved.</p>

<p>Thank you for choosing the Discuz! product. We hope our efforts can provide you with an efficient, fast, and powerful site solution, and a powerful community forum solution.</p>

<p>Official product website: https://www.discuz.vip/.</p>
<p>Official product discussion community: https://www.dismall.com/.</p>
<p>Official product App Center website: https://addon.dismall.com/.</p>
<p>Product source code website: https://code.dismall.com/.</p>

<p>"Hefei Erdao Network Technology Co., Ltd." will hereinafter be referred to as "Erdao Network", "Tencent Technology (Beijing) Co., Ltd." will be referred to as "Tencent", and the "Discuz!" product will be referred to as "this Product".</p>

<p>The official App Center included in the Discuz! X project is operated by Erdao Network. The Discuz! X open source project is maintained by Erdao Network. Discuz! X and its derivative products are developed by Erdao Network. The Discuz! X open source code is jointly maintained by the project open source management committee and community developers.</p>

<p>User notice: This agreement is a legal agreement between you and Erdao Network and Tencent regarding your use of this software product and services. Whether you are an individual or an organization, for profit or not, and regardless of the purpose (including for study and research purposes), you must carefully read this agreement, including the disclaimer clauses that exempt or limit the liability of Erdao Network and the restrictions on your rights. Please review and accept or decline these service terms. If you do not agree to these service terms and/or any modifications made by Erdao Network at any time, you should not use or should actively cancel the products provided by Erdao Network. Otherwise, any of your registration, login, download, viewing and other use behaviors of the relevant services in this Product will be deemed as your full and complete acceptance of all the service terms, including acceptance of any modifications made by Erdao Network to the service terms at any time.</p>
<p>Once the service terms are changed, Erdao Network will publish the modified content on the web page. The modified service terms, once published on the website admin center, will effectively replace the original service terms. You can log in to the source code website at any time to review the latest version of the service terms. If you choose to accept this agreement, it means that you agree to be bound by the conditions of the agreement. If you do not agree to these service terms, you cannot obtain the right to use this service. If you violate the provisions of this agreement, Erdao Network has the right to suspend or terminate your qualification to use Erdao Network products at any time and reserves the right to pursue relevant legal responsibilities.</p>
<p>Only after understanding, agreeing to, and complying with all the terms of this agreement may you begin to use this Product. You may enter into another written agreement directly with Erdao Network to supplement or replace all or any part of this agreement.</p>

<p>Erdao Network and Tencent own all intellectual property rights of this Product. This Product is only licensed, not sold. Erdao Network only allows you to copy, download, install, use, or otherwise benefit from the functions or intellectual property rights of this Product in compliance with the terms of this agreement.</p>

<h3>I. Rights Granted by the Agreement</h3>
<ol>
   <li>You may use this Product for non-commercial or commercial purposes (subject to the licenses adapted under this agreement) in full compliance with this license agreement, without having to pay software copyright license fees.</li>
   <li>You may modify the source code of this Product (if provided) or the interface style within the constraints and limitations specified in the agreement to suit your website requirements.</li>
   <li>You own all member data, articles, and related information in the website built with this Product, and you independently bear the review and duty of care obligations for the content of the website built with this Product, ensuring that it does not infringe upon the legitimate rights and interests of any person. You independently bear all responsibilities arising from the use of this Product and services. If losses are caused to Erdao Network, Tencent, and Users, you shall compensate in full.</li>
   <li>If you need to use this Product or service for commercial purposes, you must comply with the relevant laws of the People\'s Republic of China. If you need technical support methods or technical support content, please obtain technical support services from the official website.</li>
   <li>You can download app programs suitable for your website from the App Center service provided by Erdao Network, but you should pay the corresponding fees to the app program developer (owner). Erdao Network will only download the app programs to your server after being authorized by the app program developer (owner). You must immediately delete any unauthorized app programs, and Erdao Network and Tencent shall not bear any responsibility.</li>
</ol>

<h3>II. Constraints and Limitations Specified in the Agreement</h3>
<ol>
   <li>You may not rent, sell, mortgage, or sublicense this Product, app programs, or the commercial licenses associated with them.</li>
   <li>In any case, regardless of the purpose, whether modified or beautified, or the degree of modification, as long as the whole or any part of this Product is used, without written permission, the following content in this Product must be retained and cannot be cleared, modified, or replaced:<br />
	a. The text and link of "Powered by Discuz!" at the page footer;<br />
	b. The text and link of "App Center" at the admin center home footer;<br />
	c. The text and link of "Based on MitFrame" and "Cloud services by WitFrame" at the admin center home footer;
   </li>
   <li>It is prohibited to develop any derived version, modified version, or third-party version based on the whole or any part of this Product for redistribution.</li>
   <li>For app programs downloaded from the official App Center, without the written permission of the app program developer (owner), you may not reverse engineer, reverse assemble, reverse compile, etc., and may not, without authorization, copy, modify, link, reprint, compile, publish, distribute, or develop derivative products or works related to them.</li>
   <li>If you fail to comply with the terms of this agreement, your authorization will be terminated, the licensed rights will be withdrawn, and you shall bear the corresponding legal responsibilities.</li>
</ol>

<h3>III. Limited Warranty and Disclaimer</h3>
<ol>
   <li>This Product and the accompanying files are provided in a form that does not provide any explicit or implicit compensation or warranty.</li>
   <li>Users use this Product voluntarily. You must understand the risks of using this Product. We do not commit to providing any form of technical support or usage warranty, nor do we bear any related responsibility for problems arising from the use of this Product.</li>
   <li>Erdao Network is not responsible for articles or information in the website or forum built with this Product, and you bear all responsibilities yourself.</li>
   <li>The official App Center cannot fully monitor the app programs uploaded by third parties to the App Center, and therefore does not guarantee the legality, security, integrity, authenticity, or quality of the app programs. When you download app programs from the official App Center, you agree to make your own judgment and bear all risks, without relying on Erdao Network and the official App Center. However, in any case, the official App Center has the right to stop the App Center service according to law and take corresponding actions, including but not limited to uninstalling relevant app programs, suspending all or part of the service, saving relevant records, and reporting to the relevant authorities. Erdao Network, Tencent, and the official App Center shall not bear any direct, indirect, or consequential liability for any losses that may be caused to you and third parties thereby.</li>
   <li>Erdao Network does not warrant the timeliness, security, or accuracy of this Product and services. If the use of software and services is suspended or terminated due to force majeure or factors beyond Erdao Network\'s control (including hacker attacks, power outages, etc.), causing losses to you, you agree to waive all rights to pursue the responsibilities of Erdao Network and Tencent.</li>
   <li>Erdao Network specially reminds you that, in order to safeguard the autonomy of company business development and adjustment, Erdao Network has the right to modify the service content, suspend or terminate part or all of the software use and services at any time with or without prior notice. The modifications will be published on the relevant pages of Erdao Network\'s website, and once published, they shall be deemed as notice. Erdao Network and Tencent shall not be responsible to you or any third party for losses caused by Erdao Network exercising the right to modify, suspend, or terminate part or all of the software use and services.</li>
</ol>

<p>The detailed content of the final User license agreement, commercial license, and technical services of this Product are all provided by Erdao Network. Erdao Network has the right to modify the license agreement and service price list without prior notice. The modified agreement or price list shall take effect for newly authorized Users from the date of change.</p>

<p>Once you start installing this Product, you are deemed to fully understand and accept all the terms of this agreement, and while enjoying the rights granted by the above terms, you are subject to the relevant constraints and limitations. Any behavior beyond the scope of the license agreement will directly violate this license agreement and constitute infringement. We have the right to terminate the authorization at any time, order the cessation of damage, and reserve the right to pursue relevant responsibilities.</p>

<p>The interpretation, validity, and dispute resolution of the terms of this license agreement are governed by the laws of the People\'s Republic of China (mainland).</p>

<p>(End of text)</p>

</div>',

	'version_title' => 'Product Information',
	'version_notice' => '
<p>- A completely rebuilt framework system based on MitFrame<sup>&reg;</sup> core, both a community and a framework, fully expanding broader open perspectives</p>
<p>- From frontend to backend, openness is more thorough</p>
<p>- Multi-functional integrated community with both light and heavy features</p>
<p>- Brand new JSON editor</p>
<p>- Forums, user groups, and points are comprehensively enhanced</p>
<p>- Fully embracing OAuth2.0 RESTful API interfaces, developers can freely customize their own interfaces</p>
<p><a href="https://www.dismall.com/thread-27135-1-1.html" target="_blank">Click to learn more...</a></p>
',

	'php_version_too_low' => 'PHP version does not meet Discuz! installation requirements. PHP %s is required',
	'php8_tips' => 'Hello, this product does not support PHP 9.0 installation yet. Please downgrade to at least PHP 8.0 and try again!',
	'no_utf8_tips' => 'Hello, you are using a localized encoding version such as GBK/BIG-5. This version is no longer the primary version. If you plan to build a new site, we strongly recommend using the latest official UTF-8 version for installation.',
	'no_latest_tips' => 'Hello, you are using an older version which may have bugs and security risks. Unless there are special circumstances, we recommend using the latest official UTF-8 version for installation.',
	'unstable_tips' => 'Hello, you are using an unofficial version which may have unknown bugs or defects. If you plan to build an official site or purchase plugins, we recommend using the latest official UTF-8 version for installation.',
	'next_tips' => '\\r\\nClick [OK] to redirect to the latest official UTF-8 version download page, click [Cancel] to continue installation (not recommended)',

	'uc_installed' => 'You have already installed UCenter. If you need to reinstall, please delete the data/install.lock file',
	'i_agree' => 'I have carefully read and agree to all the above terms',
	'supportted' => 'Support',
	'unsupportted' => 'Not Supported',
	'max_size' => 'Supported/Max Size',
	'project' => 'Item',
	'ucenter_required' => 'Discuz! Required Configuration',
	'ucenter_best' => 'Discuz! Recommended',
	'curr_server' => 'Current Server',
	'env_check' => 'Environment Check',
	'os' => 'Operating System',
	'php' => 'PHP Version',
	'mysql' => 'MySQL Version',
	'mysql_enable' => 'Not connected, cannot determine version',
	'attachmentupload' => 'Attachment Upload',
	'unlimit' => 'Unlimited',
	'version' => 'Version',
	'gdversion' => 'GD Library',
	'allow' => 'Allow ',
	'unix' => 'Unix-like',
	'diskspace' => 'Disk Space',
	'opcache' => 'OPCache Library',
	'redis' => 'Redis Cache/Library',
	'imagick' => 'ImageMagick Library',
	'curl' => 'cURL Library',
	'priv_check' => 'Directory and File Permission Check',
	'func_depend' => 'Function Dependency Check',
	'func_name' => 'Function Name',
	'check_result' => 'Check Result',
	'suggestion' => 'Suggestion',
	'advice_mysqli' => 'Please check if the mysqli module is loaded correctly',
	'advice_fopen' => 'This function requires the allow_url_fopen option in php.ini to be enabled. Please contact your service provider to confirm this feature is enabled',
	'advice_xml' => 'This function requires PHP XML support. Please contact your hosting provider to ensure this feature is enabled',
	'none' => 'None',
	'undefine_func' => 'Non-existent function',
	'mysqli_unsupport' => 'Please check if the mysqli module is loaded correctly',

	'dbhost' => 'Database Server Address',
	'dbuser' => 'Database Username',
	'dbpw' => 'Database Password',
	'dbname' => 'Database Name',
	'tablepre' => 'Table Prefix',

	'ucfounderpw' => 'Founder Password',
	'ucfounderpw2' => 'Confirm Founder Password',

	'clear_dir' => 'Clear Directory',
	'innodb' => 'InnoDB Table Conversion',
	'select_db' => 'Select Database',
	'create_table' => 'Create Tables',
	'succeed' => 'Success',
	'failed' => 'Failed',

	'init_table_data' => 'Initializing table data',
	'install_data' => 'Installing data',
	'alter_table_data' => 'Modifying table structure',
	'install_test_data' => 'Installing additional data',

	'method_undefined' => 'Undefined method',
	'database_nonexistence' => 'Database operation object does not exist',
	'skip_current' => 'Skip this step',
	'topic' => 'Topic',
	'install_finish' => 'Site installation completed. Thank you for your support!',
	'install_finish_next' => 'Next you can:',
	'finish_btn_admin' => 'Enter Admin Panel',
	'finish_btn_cloudaddon' => 'Install Plugins & Templates',
	'finish_btn_direct' => 'Visit Site Directly',

	'upgrade_confirm' => '
	<ul style="font-size: 14px;line-height: 30px;list-style-type: decimal;padding-left: 20px;">
	<li>Ensure your old Discuz! version must be X3.5. If not, please upgrade to this version first;</li>
	<li>Ensure UCenter and Discuz! are deployed in the same database;</li>
	<li>Ensure you have backed up the database and program files, and move the old version program files to another directory;</li>
	<li>Copy the old version configuration files config/config_global.php and config/config_ucenter.php to the config/ directory of the current new version;</li>
	<li>Click "Next" to start the upgrade;</li>
	<li>After the upgrade is complete, you can selectively copy plugin files from the old version source/plugin/ directory to the corresponding directory of the new version, and selectively copy template files from the template/ directory to the corresponding directory of the new version (do not copy the template/default/ directory);</li>
	<li>After the upgrade is complete, copy the data/attachment/ directory and other directories under the data/ directory as appropriate; if old version applications involve files in other directories, please consult the relevant developers for copying;</li>
	</ul>
	',
	'upgrade_version_error' => 'Cannot upgrade, old version of Discuz! must be '.UPGRADE_FROM_VERSION.' version',

	'title_tool' => SOFT_NAME.' Toolbox',
	'tool_wizard' => 'Toolbox',
	'tool_start' => 'Start',

	'install_locked_exists' => 'Cannot enter the toolbox. Please rename this installation script to index.php to run installation mode',
	'install_locked_format_error' => 'Cannot enter the toolbox. The renamed file cannot contain "index"',

	'tool_tips' => 'For security reasons, if you have completed all operations, please click "Done" and we will help you delete the current script',
	'tool_select_resetpw' => '<div class="selradio"><input type="radio" name="method" id="select_resetpw" value="resetpw" /><label for="select_resetpw">Reset Founder Password</label></div>',
	'tool_select_dircheck' => '<div class="selradio"><input type="radio" name="method" id="select_dircheck" value="dircheck" /><label for="select_dircheck">File Directory Check</label></div>',
	'tool_select_updatecache' => '<div class="selradio"><input type="radio" name="method" id="select_updatecache" value="updatecache" /><label for="select_updatecache">Update Cache</label></div>',
	'tool_select_restore' => '<div class="selradio"><input type="radio" name="method" id="select_restore" value="restore" /><label for="select_restore">Restore Database</label></div>',

	'tool_resetpw_uid1' => 'This tool only resets the founder password for UID 1. Please restore this user as founder before performing this operation',
	'tool_resetpw_founder' => 'Fill in Founder Information',
	'tool_resetpw_loginname' => 'Account Login Name',
	'tool_resetpw_password' => 'New Account Password',
	'tool_resetpw_password2' => 'Please Re-enter',
	'tool_resetpw_password_error' => 'The two passwords do not match',
	'tool_resetpw_success' => 'Password has been reset. Please use the new password to login',

	'tool_dircheck_unwritable' => 'Directory is not writable, please check directory permissions',
	'tool_dircheck_checkfile_notexists' => 'Verification file does not exist, cannot perform file verification',
	'tool_dircheck_result_download' => 'Abnormal files detected. Please <a href="?method=dircheck&getExport=%s">download the report</a> for detailed view',
	'tool_dircheck_result_noerror' => 'File directory is normal',

	'tool_updateceche_doing' => 'Cache updating...',
	'tool_updatecache_done' => 'Cache update completed',

	'done' => 'Done',
	'all_done_exists' => 'Current script deletion failed. For security reasons, please delete it manually',
	'all_done_noexists' => 'Current script deletion completed',

	'filename' => 'File Name',
	'time' => 'Backup Time',
	'type' => 'Type',
	'size' => 'Size',
	'db_volume' => 'Volumes',
	'import' => 'Import',
	'different_dbcharset_tablepre' => 'The imported backup data is different from the configuration file {diff}. Do you still want to continue running this program?',
	'db_import_tips' => '<ul style="margin:0px 20px 20px 20px;font-size: 14px">
		<li>Please ensure the site is closed before this operation. After restoration is complete, you can reopen the site</li>
		<li>This tool does not support importing compressed volume backup data. Please decompress it yourself first</li>
	</ul>',
	'db_export_discuz' => 'Discuz! Data (excluding UCenter)',
	'db_export_discuz_uc' => 'Discuz! and UCenter Data',
	'db_export_custom' => 'Custom Backup',
	'unknown' => 'Unknown',
	'backup_file_unexist' => 'Backup file does not exist',
	'dbcharsetdiff' => ' Database character set('.$_config['db']['1']['dbcharset'].')',
	'tableprediff' => ' Table prefix('.$_config['db']['1']['tablepre'].')',
	'database_import_file_illegal' => 'Data file does not exist: the server may not allow file uploads or the file size exceeds the limit',
	'database_import_file_write_error' => 'Data file decompression and write failed. Please check if the server has write permissions',
	'database_import_multivol_prompt' => 'The first volume of the volume data was successfully imported into the database. Do you want to automatically import other volumes of this backup?',
	'database_import_succeed' => 'Data has been successfully imported into the site database<br />Please update the cache in the admin panel<br /><span class="red">For security reasons, we strongly recommend deleting the backup files</span>',
	'database_import_format_illegal' => 'Data file is not in Discuz! format, cannot import',
	'database_import_confirm' => 'Importing data inconsistent with the current Discuz! version is very likely to cause unsolvable issues. Are you sure you want to continue?',
	'database_import_confirm_sql' => 'Are you sure you want to import this backup?',
	'database_import_confirm_zip' => 'Are you sure you want to decompress this backup?',
	'database_import_multivol_confirm' => 'All volume files have been decompressed. Do you want to automatically import the backup? The decompressed files will be deleted after import',
	'database_import_multivol_start' => 'Importing backup files, the program will continue automatically',
	'database_import_multivol_redirect' => 'Data file #{volume} successfully imported, the program will continue automatically',
	'database_waiting_link' => 'The browser will redirect automatically. No manual intervention is required. If your browser does not redirect for a long time, please click here',
	'database_confirm' => 'Confirm',
	'database_cancel' => 'Cancel',

	'ext_missing' => 'Please install PHP extension',
	'func_missing' => 'Function/Class Unavailable',
];

$msglang = array(
	'config_nonexistence' => 'Your config.inc.php does not exist. Installation cannot continue. Please upload the file via FTP and try again.',
);

?>