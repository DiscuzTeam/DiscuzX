<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('RUN_MODE') || RUN_MODE != 'install') {
	show_msg('method_undefined', $method, 0);
}

$allow_method = ['show_license', 'env_check', 'app_reg', 'db_init', 'ext_info', 'install_check', 'tablepre_check', 'do_db_init', 'do_db_data_init', 'check_db_init_progress', 'upgrade_confirm', 'upgrade', 'do_db_upgrade'];

$step = intval(getgpc('step', 'R')) ? intval(getgpc('step', 'R')) : 0;
$method = getgpc('method');

if(empty($method) || !in_array($method, $allow_method)) {
	$method = isset($allow_method[$step]) ? $allow_method[$step] : '';
}

if(empty($method)) {
	show_msg('method_undefined', $method, 0);
}

if(file_exists($lockfile) && $method != 'ext_info' && $method != 'check_db_init_progress') {
	show_msg('install_locked', '', 0);
} elseif(!class_exists('dbstuff')) {
	show_msg('database_nonexistence', '', 0);
}

$uchidden = getgpc('uchidden');

if(in_array($method, ['app_reg', 'ext_info'])) {
	$isHTTPS = is_https();
	$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	// $bbserver使用的端口，不能来自于SERVER_PORT，因为dz的服务器端口不一定是用户访问的端口(比如在负载均衡后面)
	$bbserver = 'http'.($isHTTPS ? 's' : '').'://'.$_SERVER['HTTP_HOST'];
	$default_ucapi = $bbserver.'/ucenter';
	$default_appurl = $bbserver.substr($PHP_SELF, 0, strrpos($PHP_SELF, '/') - 8);
}

if(isset($_COOKIE['ULTRAXINSTID']) && strpos($_COOKIE['ULTRAXINSTID'], 'ULTRAXINSTID') === 0) {
	$instid = $_COOKIE['ULTRAXINSTID'];
} else {
	$instid = uniqid('ULTRAXINSTID', true);
	setcookie('ULTRAXINSTID', $instid);
}

if(empty($_COOKIE['LANG'])) {
	show_select_lang();
}

if($method == 'show_license') {

	transfer_ucinfo($_POST);
	$uchidden = getgpc('uchidden');
	if(empty($_GET['start'])) {
		show_version_notice();
	} else {
		show_license();
	}

} elseif($method == 'env_check') {

	VIEW_OFF && function_check($func_items);

	env_check($env_items);

	dirfile_check($dirfile_items);

	show_env_result($env_items, $dirfile_items, $func_items, $filesock_items);

} elseif($method == 'app_reg') {

	@include ROOT_PATH.CONFIG;
	@include ROOT_PATH.CONFIG_UC;
	if(!defined('UC_API')) {
		define('UC_API', '');
	}
	if(getgpc('install_ucenter') == 'upgrade') {
		header("Location: index.php?step=10&install_ucenter=".getgpc('install_ucenter'));
		die;
	}
	if(getgpc('install_ucenter') == 'yes' || getgpc('install_ucenter') == 'standalone') {
		header("Location: index.php?step=3&install_ucenter=".getgpc('install_ucenter'));
		die;
	}
	$submit = true;
	$error_msg = [];
	if(isset($form_app_reg_items) && is_array($form_app_reg_items)) {
		foreach($form_app_reg_items as $key => $items) {
			$$key = getgpc($key, 'p');
			if(!isset($$key) || !is_array($$key)) {
				$submit = false;
				break;
			}
			foreach($items as $k => $v) {
				$tmp = $$key;
				$$k = addslashes($tmp[$k]);
				if(empty($$k) || !preg_match($v['reg'], $$k)) {
					if(empty($$k) && !$v['required']) {
						continue;
					}
					$submit = false;
					VIEW_OFF or $error_msg[$key][$k] = 1;
				}
			}
		}
	} else {
		$submit = false;
	}

	$ucapi = defined('UC_API') && UC_API ? UC_API : $default_ucapi;

	if($submit) {

		$app_type = 'DISCUZX'; // Only For Discuz!

		$app_name = $sitename ? $sitename : SOFT_NAME;
		$app_url = $siteurl ? $siteurl : $default_appurl;

		$ucapi = $ucurl ? $ucurl : (defined('UC_API') && UC_API ? UC_API : $default_ucapi);
		$ucip = isset($ucip) ? $ucip : '';
		$ucfounderpw = $ucpw;
		$app_tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
			'apptagtemplates[fields][subject]='.urlencode($lang['tagtemplates_subject']).'&'.
			'apptagtemplates[fields][uid]='.urlencode($lang['tagtemplates_uid']).'&'.
			'apptagtemplates[fields][username]='.urlencode($lang['tagtemplates_username']).'&'.
			'apptagtemplates[fields][dateline]='.urlencode($lang['tagtemplates_dateline']).'&'.
			'apptagtemplates[fields][url]='.urlencode($lang['tagtemplates_url']);

		$ucapi = preg_replace("/\/$/", '', trim($ucapi));
		if(empty($ucapi) || !preg_match("/^(https?:\/\/)/i", $ucapi)) {
			show_msg('uc_url_invalid', $ucapi, 0);
		} else {
			if(!$ucip) {
				$temp = @parse_url($ucapi);
				$ucip = gethostbyname($temp['host']);
				if(!(filter_var($ucip, FILTER_VALIDATE_IP) !== false)) {
					show_msg('uc_dns_error', $ucapi, 0);
				}
			}
		}
		include_once ROOT_PATH.'./source/class/uc/client.php';

		$ucinfo = dfopen($ucapi.'/index.php?m=app&a=ucinfo&release='.UC_CLIENT_RELEASE, 500, '', '', 1, $ucip);
		[$status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes] = explode('|', $ucinfo);
		if($status != 'UC_STATUS_OK') {
			show_msg('uc_url_unreachable', $ucapi, 0);
		} else {
			$dbcharset = strtolower($dbcharset ? str_replace('-', '', $dbcharset) : $dbcharset);
			$ucdbcharset = strtolower($ucdbcharset ? str_replace('-', '', $ucdbcharset) : $ucdbcharset);
			if(UC_CLIENT_VERSION > $ucversion) {
				show_msg('uc_version_incorrect', $ucversion, 0);
			} elseif($dbcharset && $ucdbcharset != $dbcharset) {
				show_msg('uc_dbcharset_incorrect', '', 0);
			}

			$postdata = "m=app&a=add&ucfounder=&ucfounderpw=".urlencode($ucpw)."&apptype=".urlencode($app_type)."&appname=".urlencode($app_name)."&appurl=".urlencode($app_url)."&appip=&appcharset=".CHARSET.'&appdbcharset='.DBCHARSET.'&'.$app_tagtemplates.'&release='.UC_CLIENT_RELEASE;
			$ucconfig = dfopen($ucapi.'/index.php', 500, $postdata, '', 1, $ucip);
			if(empty($ucconfig)) {
				show_msg('uc_api_add_app_error', $ucapi, 0);
			} elseif($ucconfig == '-1') {
				show_msg('uc_admin_invalid', '', 0);
			} else {
				[$appauthkey, $appid] = explode('|', $ucconfig);
				$ucconfig_array = explode('|', $ucconfig);
				$ucconfig_array[] = $ucapi;
				$ucconfig_array[] = $ucip;
				$ucconfig_array[] = 0;
				if(empty($appauthkey) || empty($appid)) {
					show_msg('uc_data_invalid', '', 0);
				} elseif($succeed = save_uc_config($ucconfig_array, ROOT_PATH.CONFIG_UC)) {
					if(VIEW_OFF) {
						show_msg('app_reg_success');
					} else {
						$step = $step + 1;
						header("Location: index.php?step=$step");
						exit;
					}
				} else {
					show_msg('config_unwriteable', '', 0);
				}
			}
		}

	}
	if(VIEW_OFF) {

		show_msg('missing_parameter', '', 0);

	} else {

		show_form($form_app_reg_items, $error_msg);

	}

} elseif($method == 'db_init') {

	if(getgpc('install_ucenter') == 'yes' || getgpc('install_ucenter') == 'standalone') {
		define('DZUCFULL', true);
		define('DZUCSTL', (getgpc('install_ucenter') == 'standalone') ? true : false);
	} else {
		define('DZUCFULL', false);
		define('DZUCSTL', false);
	}

	$submit = true;

	$default_config = $_config = [];
	$default_configfile = './config/config_global_default.php';

	if(!file_exists(ROOT_PATH.$default_configfile)) {
		exit('config_global_default.php was lost, please reupload this  file.');
	} else {
		include ROOT_PATH.$default_configfile;
		$default_config = $_config;
	}

	if(file_exists(ROOT_PATH.CONFIG)) {
		include ROOT_PATH.CONFIG;
	} else {
		$_config = $default_config;
	}

	$dbhost = $_config['db'][1]['dbhost'];
	$dbname = $_config['db'][1]['dbname'];
	$dbpw = $_config['db'][1]['dbpw'];
	$dbuser = $_config['db'][1]['dbuser'];
	$tablepre = $_config['db'][1]['tablepre'];

	$adminemail = 'admin@admin.com';

	if($method == 'up_init') {
		unset($form_db_init_items['admininfo']);
	}

	$error_msg = [];
	if(isset($form_db_init_items) && is_array($form_db_init_items)) {
		foreach($form_db_init_items as $key => $items) {
			$$key = getgpc($key, 'p');
			if(!isset($$key) || !is_array($$key)) {
				$submit = false;
				break;
			}
			foreach($items as $k => $v) {
				$tmp = $$key;
				$$k = addslashes($tmp[$k]);
				if(empty($$k) || !preg_match($v['reg'], $$k)) {
					if(empty($$k) && !$v['required']) {
						continue;
					}
					$submit = false;
					VIEW_OFF or $error_msg[$key][$k] = 1;
				}
			}
		}
	} else {
		$submit = false;
	}

	if($submit && !VIEW_OFF && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if($password != $password2) {
			$error_msg['admininfo']['password2'] = 1;
			$submit = false;
		}
		$forceinstall = isset($_POST['dbinfo']['forceinstall']) ? $_POST['dbinfo']['forceinstall'] : '';
		$dbname_not_exists = true;
		if(!empty($dbhost) && empty($forceinstall)) {
			$dbname_not_exists = check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre);
			if(!$dbname_not_exists) {
				$form_db_init_items['dbinfo']['forceinstall'] = ['type' => 'checkbox', 'required' => 0, 'reg' => '/^.*+/'];
				$error_msg['dbinfo']['forceinstall'] = 1;
				$submit = false;
				$dbname_not_exists = false;
			}
		}
	}

	if($submit) {

		$step = $step + 1;
		if(empty($dbname)) {
			show_msg('dbname_invalid', $dbname, 0);
		} else {
			mysqli_report(MYSQLI_REPORT_OFF);

			$link = new mysqli($dbhost, $dbuser, $dbpw);
			if($link->connect_errno) {
				$errno = $link->connect_errno;
				$error = $link->connect_error;
				if($errno == 1045) {
					show_msg('database_errno_1045', $error, 0);
				} elseif($errno == 2003) {
					show_msg('database_errno_2003', $error, 0);
				} else {
					show_msg('database_connect_error', $error, 0);
				}
			}

			$link->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET ".constant('DBCHARSET'));

			if($link->errno) {
				show_msg('database_errno_1044', $link->error, 0);
			}
			$link->close();
		}

		if(strpos($tablepre, '.') !== false || intval($tablepre[0])) {
			show_msg('tablepre_invalid', $tablepre, 0);
		}

		if($username && $email && $password) {
			if(strlen($username) > 15 || preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^Guest/is", $username)) {
				show_msg('admin_username_invalid', $username, 0);
			} elseif(!strstr($email, '@') || $email != stripslashes($email) || $email != dhtmlspecialchars($email)) {
				show_msg('admin_email_invalid', $email, 0);
			} else {
				if(!DZUCFULL) {
					$adminuser = check_adminuser($username, $password, $email);
					if($adminuser['uid'] < 1) {
						show_msg($adminuser['error'], '', 0);
					}
				}
			}
		} else {
			show_msg('admininfo_invalid', '', 0);
		}

		$uid = DZUCFULL ? 1 : $adminuser['uid'];
		$authkey = md5((isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '').$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$username.$password.substr(time(), 0, 8)).secrandom(32);
		$_config['db'][1]['dbhost'] = $dbhost;
		$_config['db'][1]['dbname'] = $dbname;
		$_config['db'][1]['dbpw'] = $dbpw;
		$_config['db'][1]['dbuser'] = $dbuser;
		$_config['db'][1]['tablepre'] = $tablepre;
		$_config['admincp']['founder'] = (string)$uid;
		$_config['security']['authkey'] = $authkey;
		$_config['cookie']['cookiepre'] = secrandom(4).'_';
		$_config['memory']['prefix'] = secrandom(6).'_';
		$_config['lang'] = $_COOKIE['LANG'];

		save_config_file(ROOT_PATH.CONFIG, $_config, $default_config);

		if(!VIEW_OFF) {
			show_header();
			show_db_install();
			show_footer();
		}

	}
	if(VIEW_OFF) {
		show_msg('missing_parameter', '', 0);
	} else {
		show_form($form_db_init_items, $error_msg);
	}

} elseif($method == 'ext_info') {
	@touch($lockfile);
	if(VIEW_OFF) {
		show_msg('ext_info_succ');
	} else {
		show_header();
		echo '</div><div class="main inst_success"><div class="success_icon"></div><h2>'.$lang['install_finish'].'</h2><p>'.$lang['install_finish_next'].'</p>';
		echo '<div class="success_btn"><p class="btng btng_two">';
		echo '<a href="'.$default_appurl.'/admin.php?action=styles&operation=import&dir=discuzx5&default=1" class="btn finishbtn"><svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 5.5h16v11H9l-5 3v-14Z"/><path d="M8 9h8M8 13h5"/></svg>'.$lang['finish_btn_install_style_x5'].'</a>';
		echo '<a href="'.$default_appurl.'" class="btn finishbtn"><svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m3 10 9-7 9 7M5.5 9v11h13V9M9 20v-7h6v7"/></svg>'.$lang['finish_btn_direct'].'</a>';
		echo '</p>';

		echo '<p class="btng btng_two">';
		echo '<a href="'.$default_appurl.'/admin.php" class="btn finishbtn"><svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3 20 6v5c0 5-3.4 8.3-8 10-4.6-1.7-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/></svg>'.$lang['finish_btn_admin'].'</a>';
		echo '<a href="'.$default_appurl.'/admin.php?action=cloudaddons&frame=no&from=newinstall" class="btn finishbtn"><svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4z"/><path d="M16.5 13v7M13 16.5h7"/></svg>'.$lang['finish_btn_cloudaddon'].'</a>';
		echo '</p></div>';
		show_footer();
	}

} elseif($method == 'install_check') {

	if(file_exists($lockfile)) {
		show_msg('installstate_succ');
	} else {
		show_msg('lock_file_not_touch', $lockfile, 0);
	}

} elseif($method == 'tablepre_check') {
	$dbinfo = getgpc('dbinfo');
	extract($dbinfo);
	if(check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre)) {
		show_msg('tablepre_not_exists', 0);
	} else {
		show_msg('tablepre_exists', $tablepre, 0);
	}
} elseif($method == 'do_db_init') {
	$allinfo = getgpc('allinfo');
	$allinfo_arr = unserialize(base64_decode($allinfo));
	extract($allinfo_arr);

	set_exception_handler('sse_exception_handler');

	sse_header();

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);

	if($dzucfull) {
		install_uc_sql();
	}

	$sql = read_sql($install_sqlfile);
	if(!runquery($sql)) {
		sse_output(lang('failed'));
	}
	$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('sitevipkey', '".SITEVIP_KEY."')");

	sleep(2);

	rundatasql('lang_'.$install_sqlfile);

	sleep(2);

	!VIEW_OFF && showjsmessage(lang('initdbresult_succ')."\n");

	$onlineip = $_SERVER['REMOTE_ADDR'];
	$timestamp = time();
	$backupdir = substr(md5((isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '').$_SERVER['HTTP_USER_AGENT'].substr($timestamp, 0, 4)), 8, 6);
	$ret = false;
	if(is_dir(ROOT_PATH.'data/backup')) {
		$ret = @rename(ROOT_PATH.'data/backup', ROOT_PATH.'data/backup_'.$backupdir);
	}
	if(!$ret) {
		@mkdir(ROOT_PATH.'data/backup_'.$backupdir, 0777);
	}
	if(is_dir(ROOT_PATH.'data/backup_'.$backupdir)) {
		$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('backupdir', '$backupdir')");
	}
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$siteuniqueid = 'DX'.$chars[date('y') % 60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($onlineip.$timestamp), 0, 4).random(4);

	$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('authkey', '')");
	$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('siteuniqueid', '$siteuniqueid')");
	$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('adminemail', '$email')");

	install_extra_setting();

	$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('backupdir', '".$backupdir."')");

	$password = md5(random(10));

	$db->query("REPLACE INTO {$tablepre}common_member (uid, loginname, username, password, adminid, groupid, email, regdate, timeoffset) VALUES ('$uid', '$username', '$username', '$password', '1', '1', '$email', '".time()."', '9999');");

	// UID 是变量, 不做适配会导致积分操作等异常
	if($uid) {
		$db->query("REPLACE INTO {$tablepre}common_member_count SET uid='$uid';");
		$db->query("REPLACE INTO {$tablepre}common_member_status SET uid='$uid';");
		$db->query("REPLACE INTO {$tablepre}common_member_field_forum SET uid='$uid';");
		$db->query("REPLACE INTO {$tablepre}common_member_field_home SET uid='$uid';");
		$db->query("REPLACE INTO {$tablepre}common_member_profile SET uid='$uid';");
	}

	$notifyusers = addslashes('a:1:{i:1;a:2:{s:8:"username";s:'.strlen($username).':"'.$username.'";s:5:"types";s:20:"11111111111111111111";}}');
	$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('notifyusers', '$notifyusers')");

	$db->query("UPDATE {$tablepre}common_cron SET lastrun='0', nextrun='".($timestamp + 3600)."'");
	$db->query("UPDATE {$tablepre}common_adminnote SET dateline='$timestamp', expiration='".($timestamp + 2592000)."'");

	install_data($username, $uid);

	$portalstatus = 1;
	$groupstatus = $homestatus = 0;

	if(!$portalstatus) {
		$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('portalstatus', '0')");
	}

	if(!$groupstatus) {
		$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('groupstatus', '0')");
	}

	if(!$homestatus) {
		$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('homestatus', '0')");
	}

	dir_clear(ROOT_PATH.'./data/template');
	dir_clear(ROOT_PATH.'./data/cache');
	dir_clear(ROOT_PATH.'./data/threadcache');

	foreach($serialize_sql_setting as $k => $v) {
		$v = addslashes(serialize($v));
		$db->query("REPLACE INTO {$tablepre}common_setting VALUES ('$k', '$v')");
	}

	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}common_member");
	$totalmembers = $db->result($query, 0);
	$userstats = ['totalmembers' => $totalmembers, 'newsetuser' => $username];
	$ctype = 1;
	$data = addslashes(serialize($userstats));
	$db->query("REPLACE INTO {$tablepre}common_syscache (cname, ctype, dateline, data) VALUES ('userstats', '$ctype', '".time()."', '$data')");

	!VIEW_OFF && showjsmessage(lang('initdbdataresult_succ')."\n");
} elseif($method == 'do_db_upgrade') {
	$upgrade_sqlfile .= '_3.5';
	include ROOT_PATH.CONFIG;
	$_config['memory']['redis']['server'] = '';
	$_config['lang'] = $_COOKIE['LANG'];
	save_config_file(ROOT_PATH.CONFIG, $_config, []);

	$allinfo = getgpc('allinfo');
	$allinfo_arr = unserialize(base64_decode($allinfo));
	extract($allinfo_arr);

	set_exception_handler('sse_exception_handler');

	sse_header();

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);

	$sql = read_sql($upgrade_sqlfile);
	if(!runquery($sql, true)) {
		exit();
	}

	sleep(2);

	rundatasql('lang_'.$upgrade_sqlfile, true);

	sleep(2);

	$db->query("REPLACE INTO {$tablepre}common_setting (skey, svalue) VALUES ('sitevipkey', '".SITEVIP_KEY."')");

	unmark_system_plugin();

	!VIEW_OFF && showjsmessage(lang('initdbdataresult_succ')."\n");
} elseif($method == 'do_db_data_init') {
	$allinfo = getgpc('allinfo');
	$allinfo_arr = unserialize(base64_decode($allinfo));
	extract($allinfo_arr);

	@set_time_limit(0);
	@ignore_user_abort(true);
	ini_set('max_execution_time', 0);
	ini_set('mysql.connect_timeout', 0);

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);

} elseif($method == 'check_db_init_progress') {
	@set_time_limit(5);
	send_mime_type_header("text/plain");
	ob_start();
	read_install_log_file();
	ob_end_flush();
	exit();
} elseif($method == 'upgrade_confirm') {
	show_header();
	echo '<div class="box">';
	show_tips('upgrade_confirm');
	echo '</div>';
	show_setting('start');
	show_setting('hidden', 'step', 11);
	echo '<div class="btnbox">';
	show_setting('', 'submitname', 'new_step', 'submit|oldbtn');
	echo '</div>';
	show_setting('end');
	show_footer();
} elseif($method == 'upgrade') {
	define('DZUCFULL', true);
	define('DZUCSTL', true);

	if(!file_exists(ROOT_PATH.CONFIG)) {
		show_msg('config_nonexistence', '', 0);
	}
	include ROOT_PATH.CONFIG;

	$dbhost = $_config['db'][1]['dbhost'];
	$dbname = $_config['db'][1]['dbname'];
	$dbpw = $_config['db'][1]['dbpw'];
	$dbuser = $_config['db'][1]['dbuser'];
	$tablepre = $_config['db'][1]['tablepre'];

	if(empty($dbname)) {
		show_msg('dbname_invalid', $dbname, 0);
	} else {
		mysqli_report(MYSQLI_REPORT_OFF);

		$link = new mysqli($dbhost, $dbuser, $dbpw, $dbname);
		if($link->connect_errno) {
			$errno = $link->connect_errno;
			$error = $link->connect_error;
			if($errno == 1045) {
				show_msg('database_errno_1045', $error, 0);
			} elseif($errno == 2003) {
				show_msg('database_errno_2003', $error, 0);
			} else {
				show_msg('database_connect_error', $error, 0);
			}
		}

		if($link->errno) {
			show_msg('database_errno_1044', $link->error, 0);
		}

		if(!$link->query("SELECT svalue FROM {$tablepre}common_setting WHERE skey='bbclosed'")->fetch_row()[0]) {
			show_msg('status_bbclosed_invalid', $link->error, 0);
		}
		if($link->query("SELECT COUNT(*) FROM {$tablepre}common_plugin WHERE available=1")->fetch_row()[0] > 0) {
			show_msg('status_plugin_available', $link->error, 0);
		}

		$link->close();
	}

	if(!VIEW_OFF) {
		show_header();
		show_db_install(true);
		show_footer();
	}
}