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
	'System Message' => 'Site Info',

	'config_notfound' => 'Configuration file "config_global.php" not found or inaccessible, please confirm you have installed the program correctly',
	'template_notfound' => 'Template file not found or inaccessible',
	'directory_notfound' => 'Directory not found or inaccessible',
	'request_tainting' => 'Your current access request contains illegal characters and has been rejected by the system',
	'db_help_link' => 'Click here for help',
	'db_error_message' => 'Error Message',
	'db_error_sql' => '<b>SQL</b>: $sql<br />',
	'db_error_backtrace' => '<b>Backtrace</b>: $backtrace<br />',
	'db_error_no' => 'Error Code',
	'db_notfound_config' => 'Configuration file "config_global.php" not found or inaccessible.',
	'db_notconnect' => 'Unable to connect to database server',
	'db_security_error' => 'Query statement security threat',
	'db_query_sql' => 'Query Statement',
	'db_query_error' => 'Query statement error',
	'db_config_db_not_found' => 'Database configuration error, please carefully check the config_global.php file',
	'system_init_ok' => 'Website system initialization complete, please <a href="index.php">click here</a> to enter',
	'backtrace' => 'Runtime Info',
	'error_end_message_user' => 'This site has recorded this error information in detail. We apologize for the inconvenience caused to you<br /><a href="http://{host}">{host}</a>',
	'error_end_message_admin' => 'This site has recorded this error information in detail. You can search by BackTraceID in "Operation Log > System Errors" in the Admin CP for quick identification<br /><a href="http://{host}">{host}</a>',
	'suggestion' => 'It is recommended that you try refreshing the page, closing all browser windows and re-operating',
	'suggestion_user' => 'If the problem persists, please provide the BackTraceID to the site administrator to report this issue for quick identification',
	'suggestion_plugin' => 'It is recommended that you try disabling the <span class="guess">{guess}</span> plugin in the Admin CP. If the problem is resolved after disabling the plugin, please contact the plugin provider with a complete screenshot for assistance',
	'suggestion_admin' => 'If the problem persists, please seek help through the <a href="https://www.dismall.com/" target="_blank">Discuz! Official Forum</a> with a complete screenshot, or submit an Issue to us at the official Git repository <a href="https://gitee.com/discuz/DiscuzX/issues" target="_blank">here</a>',

	'file_upload_error_-101' => 'Upload failed! Uploaded file does not exist or is invalid, please go back.',
	'file_upload_error_-102' => 'Upload failed! Non-image type file, please go back.',
	'file_upload_error_-103' => 'Upload failed! Cannot write to file or write failed, please go back.',
	'file_upload_error_-104' => 'Upload failed! Unrecognized image file format, please go back.',
	];
