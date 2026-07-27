<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('SOFT_NAME', DISCUZ_VERSION_NAME);

define('CONFIG', './config/config_global.php');
define('CONFIG_UC', './config/config_ucenter.php');

$lockfile = ROOT_PATH.'./data/install.lock';

@include ROOT_PATH.CONFIG;

$install_sqlfile = 'sql_install';
$upgrade_sqlfile = 'sql_upgrade';

define('CHARSET', 'utf-8');
define('DBCHARSET', 'utf8mb4');

define('ORIG_TABLEPRE', 'pre_');

define('METHOD_UNDEFINED', 255);
define('ENV_CHECK_RIGHT', 0);
define('ERROR_CONFIG_VARS', 1);
define('SHORT_OPEN_TAG_INVALID', 2);
define('INSTALL_LOCKED', 3);
define('DATABASE_NONEXISTENCE', 4);
define('PHP_VERSION_TOO_LOW', 5);
define('MYSQL_VERSION_TOO_LOW', 6);
define('UC_URL_INVALID', 7);
define('UC_DNS_ERROR', 8);
define('UC_URL_UNREACHABLE', 9);
define('UC_VERSION_INCORRECT', 10);
define('UC_DBCHARSET_INCORRECT', 11);
define('UC_API_ADD_APP_ERROR', 12);
define('UC_ADMIN_INVALID', 13);
define('UC_DATA_INVALID', 14);
define('DBNAME_INVALID', 15);
define('DATABASE_ERRNO_2003', 16);
define('DATABASE_ERRNO_1044', 17);
define('DATABASE_ERRNO_1045', 18);
define('DATABASE_CONNECT_ERROR', 19);
define('TABLEPRE_INVALID', 20);
define('CONFIG_UNWRITEABLE', 21);
define('ADMIN_USERNAME_INVALID', 22);
define('ADMIN_EMAIL_INVALID', 25);
define('ADMIN_EXIST_PASSWORD_ERROR', 26);
define('ADMININFO_INVALID', 27);
define('LOCKFILE_NO_EXISTS', 28);
define('TABLEPRE_EXISTS', 29);
define('ERROR_UNKNOW_TYPE', 30);
define('ENV_CHECK_ERROR', 31);
define('UNDEFINE_FUNC', 32);
define('MISSING_PARAMETER', 33);
define('LOCK_FILE_NOT_TOUCH', 34);
define('SITEVIP_KEY', 1);
define('UPGRADE_FROM_VERSION', 'X3.5');

$func_items = array('mysqli_connect', 'xml_parser_create', 'json_encode', 'dns_get_record');

$filesock_items = array('fsockopen', 'pfsockopen', 'stream_socket_client', 'curl_init');

$env_items = array
(
	'os' => array('c' => 'PHP_OS', 'r' => 'notset', 'b' => 'unix'),
	'php' => array('c' => 'PHP_VERSION', 'r' => '8.0+', 'b' => '8.2'),
	'mysql' => array('r' => 'MySQL 5.7+/MariaDB 10.2', 'b' => 'MySQL 8.0'),
	'attachmentupload' => array('r' => 'notset', 'b' => '2M'),
	'diskspace' => array('r' => 40 * 1048576, 'b' => 'notset'),
	'extensions' => array('r' => 'notset', 'b' => 'enable'),
);

$dirfile_items = array
(

	'config' => array('type' => 'file', 'path' => CONFIG),
	'ucenter config' => array('type' => 'file', 'path' => CONFIG_UC),
	'config_dir' => array('type' => 'dir', 'path' => './config'),
	'data' => array('type' => 'dir', 'path' => './data'),
	'diy' => array('type' => 'dir', 'path' => './data/diy'),
	'logs' => array('type' => 'dir', 'path' => './data/log'),
	'cache' => array('type' => 'dir', 'path' => './data/cache'),
	'syscache' => array('type' => 'dir', 'path' => './data/sysdata'),
	'avatar' => array('type' => 'dir', 'path' => './data/avatar'),
	'plugindata' => array('type' => 'dir', 'path' => './data/plugindata'),
	'plugindownload' => array('type' => 'dir', 'path' => './data/download'),
	'addonmd5' => array('type' => 'dir', 'path' => './data/addonmd5'),
	'ftemplates' => array('type' => 'dir', 'path' => './data/template'),
	'threadcache' => array('type' => 'dir', 'path' => './data/threadcache'),
	'attach' => array('type' => 'dir', 'path' => './data/attachment'),
	'attach_avatar' => array('type' => 'dir', 'path' => './data/attachment/avatar'),
	'attach_album' => array('type' => 'dir', 'path' => './data/attachment/album'),
	'attach_category' => array('type' => 'dir', 'path' => './data/attachment/category'),
	'attach_common' => array('type' => 'dir', 'path' => './data/attachment/common'),
	'attach_forum' => array('type' => 'dir', 'path' => './data/attachment/forum'),
	'attach_group' => array('type' => 'dir', 'path' => './data/attachment/group'),
	'attach_portal' => array('type' => 'dir', 'path' => './data/attachment/portal'),
	'attach_profile' => array('type' => 'dir', 'path' => './data/attachment/profile'),
	'attach_swfupload' => array('type' => 'dir', 'path' => './data/attachment/swfupload'),
	'attach_temp' => array('type' => 'dir', 'path' => './data/attachment/temp'),
);


$form_app_reg_items = array
(
	'ucenter' => array
	(
		'ucurl' => array('type' => 'text', 'required' => 1, 'reg' => '/^https?:\/\//', 'value' => array('type' => 'var', 'var' => 'ucapi')),
		'ucip' => array('type' => 'text', 'required' => 0, 'reg' => '/^\d+\.\d+\.\d+\.\d+$/'),
		'ucpw' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/')
	),
	'siteinfo' => array
	(
		'sitename' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'constant', 'var' => 'SOFT_NAME')),
		'siteurl' => array('type' => 'text', 'required' => 1, 'reg' => '/^https?:\/\//', 'value' => array('type' => 'var', 'var' => 'default_appurl'))
	)
);

$form_db_init_items = array
(
	'dbinfo' => array
	(
		'dbhost' => array('type' => 'text', 'required' => 1, 'reg' => '/^.+$/', 'value' => array('type' => 'var', 'var' => 'dbhost')),
		'dbname' => array('type' => 'text', 'required' => 1, 'reg' => '/^.+$/', 'value' => array('type' => 'var', 'var' => 'dbname')),
		'dbuser' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'var', 'var' => 'dbuser')),
		'dbpw' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'var', 'var' => 'dbpw')),
		'tablepre' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*+/', 'value' => array('type' => 'var', 'var' => 'tablepre')),
		'adminemail' => array('type' => 'text', 'required' => 1, 'reg' => '/@/', 'value' => array('type' => 'var', 'var' => 'adminemail')),
	),
	'admininfo' => array
	(
		'username' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'constant', 'var' => 'admin')),
		'password' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		'password2' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		'email' => array('type' => 'text', 'required' => 1, 'reg' => '/@/', 'value' => array('type' => 'var', 'var' => 'adminemail')),
	)
);

$serialize_sql_setting = array(
);

?>