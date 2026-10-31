<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_application extends discuz_base {


	var $mem = null;

	var $session = null;

	var $config = [];

	var $var = [];

	var $cachelist = [];

	var $init_db = true;
	var $init_setting = true;
	var $init_user = true;
	var $init_session = true;
	var $init_cron = true;
	var $init_misc = true;
	var $init_mobile = true;

	var $initated = false;

	var $superglobal = [
		'GLOBALS' => 1,
		'_GET' => 1,
		'_POST' => 1,
		'_REQUEST' => 1,
		'_COOKIE' => 1,
		'_SERVER' => 1,
		'_ENV' => 1,
		'_FILES' => 1,
	];

	static function &instance() {
		static $object;
		if(empty($object)) {
			$object = new self();
		}
		return $object;
	}

	public function __construct() {
		$this->_init_cnf();
		$this->_init_env();
		$this->_init_config();
		$this->_init_input();
		$this->_init_output();
	}

	public function init() {
		if(!$this->initated) {
			$this->_init_db();
			$this->_init_setting();
			$this->_init_user();
			$this->_init_session();
			$this->_init_mobile();
			$this->_init_cron();
			$this->_init_misc();
			$this->_init_platform();
		}
		$this->initated = true;
	}

	private function _init_platform() {
		if(!defined('IN_ADMINCP')) {
			return '';
		}
		if(!empty($_GET['platform'])) {
			$_p = strpos($_GET['platform'], '?');
			if($_p !== false) {
				parse_str(substr($_GET['platform'], $_p + 1), $_get);
				$_platform_ = substr($_GET['platform'], 0, $_p);
				$_GET += $_get;
				$_p = strpos($_SERVER['QUERY_STRING'], '?');
				$_SERVER['QUERY_STRING'] = substr($_SERVER['QUERY_STRING'], $_p + 1);
			} else {
				$_platform_ = $_GET['platform'];
				$_SERVER['QUERY_STRING'] = '';
			}
			unset($_GET['platform']);
		} else {
			$_platform_ = getglobal('cache/admin/default_platform') ?: 'system';
		}
		define('PLATFORM', $_platform_);
	}

	private function _init_env() {
		error_reporting(E_ERROR);

		define('ICONV_ENABLE', function_exists('iconv'));
		define('MB_ENABLE', function_exists('mb_convert_encoding'));
		define('EXT_OBGZIP', function_exists('ob_gzhandler'));

		define('TIMESTAMP', time());
		$this->_timezone_set();

		if(!defined('DISCUZ_CORE_FUNCTION') && !@include(DISCUZ_ROOT.'./source/function/function_core.php')) {
			exit('function_core.php is missing');
		}

		if(!defined('DISCUZ_LOG_FUNCTION') && !@include(DISCUZ_ROOT.'./source/function/function_log.php')) {
			exit('function_log.php is missing');
		}

		if(function_exists('ini_get')) {
			$memorylimit = @ini_get('memory_limit');
			if($memorylimit && return_bytes($memorylimit) < 33554432 && function_exists('ini_set')) {
				ini_set('memory_limit', '128m');
			}
		}

		define('IS_ROBOT', checkrobot());

		foreach($GLOBALS as $key => $value) {
			if(!isset($this->superglobal[$key])) {
				$GLOBALS[$key] = null;
				unset($GLOBALS[$key]);
			}
		}

		if(!defined('APPTYPEID')) {
			define('APPTYPEID', 0);
		}

		if(!defined('MITFRAME_APP')) {
			define('MITFRAME_APP', '');
		}

		if(!defined('CURSCRIPT')) {
			define('CURSCRIPT', MITFRAME_APP);
		}

		define('DISCUZ_LANG', $this->config['lang'] ?? 'SC_UTF8');

		global $_G;
		$_G = [
			'uid' => 0,
			'username' => '',
			'adminid' => 0,
			'groupid' => 1,
			'sid' => '',
			'formhash' => '',
			'connectguest' => 0,
			'timestamp' => TIMESTAMP,
			'starttime' => microtime(true),
			'clientip' => $this->_get_client_ip(),
			'remoteport' => $_SERVER['REMOTE_PORT'],
			'referer' => '',
			'charset' => '',
			'gzipcompress' => '',
			'authkey' => '',
			'timenow' => [],
			'widthauto' => 0,
			'disabledwidthauto' => 0,

			'PHP_SELF' => '',
			'siteurl' => '',
			'siteroot' => '',
			'siteport' => '',

			'pluginrunlist' => !defined('PLUGINRUNLIST') ? [] : explode(',', PLUGINRUNLIST),

			'config' => & $this->config,
			'setting' => [],
			'member' => [],
			'group' => [],
			'cookie' => [],
			'style' => [],
			'cache' => [],
			'session' => [],
			'lang' => [],

			'fid' => 0,
			'tid' => 0,
			'forum' => [],
			'thread' => [],
			'rssauth' => '',

			'home' => [],
			'space' => [],

			'block' => [],
			'article' => [],

			'action' => [
				'action' => APPTYPEID,
				'fid' => 0,
				'tid' => 0,
			],

			'mobile' => '',
			'notice_structure' => [
				'mypost' => ['post', 'rate', 'pcomment', 'activity', 'reward', 'goods', 'at'],
				'interactive' => ['poke', 'friend', 'wall', 'comment', 'click', 'sharenotice'],
				'system' => ['system', 'credit', 'group', 'verify', 'magic', 'task', 'show', 'group', 'pusearticle', 'mod_member', 'blog', 'article'],
				'manage' => ['mod_member', 'report', 'pmreport'],
				'app' => [],
			],
			'mobiletpl' => ['1' => 'touch', '2' => 'touch', '3' => 'touch', 'yes' => 'touch'],
		];
		$_G['PHP_SELF'] = dhtmlspecialchars($this->_get_script_url());
		$_G['basescript'] = CURSCRIPT;
		$_G['basefilename'] = basename($_G['PHP_SELF']);
		$sitepath = substr($_G['PHP_SELF'], 0, strrpos($_G['PHP_SELF'], '/'));
		if(defined('IN_API')) {
			$sitepath = preg_replace('/\/api\/?.*?$/i', '', $sitepath);
		} elseif(defined('IN_ARCHIVER')) {
			$sitepath = preg_replace('/\/archiver/i', '', $sitepath);
		}
		if(defined('IN_NEWMOBILE')) {
			$sitepath = preg_replace('/\/m/i', '', $sitepath);
		}
		$_G['isHTTPS'] = !empty($_G['config']['output']['forcehttps']) || $this->_is_https();
		$_G['scheme'] = 'http'.($_G['isHTTPS'] ? 's' : '');
		$_G['siteurl'] = dhtmlspecialchars($_G['scheme'].'://'.$_SERVER['HTTP_HOST'].$sitepath.'/');

		$url = parse_url($_G['siteurl']);
		$_G['siteroot'] = $url['path'] ?? '';
		$_G['siteport'] = empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' ? '' : ':'.$_SERVER['SERVER_PORT'];

		if(defined('SUB_DIR')) {
			$_G['siteurl'] = str_replace(SUB_DIR, '/', $_G['siteurl']);
			$_G['siteroot'] = str_replace(SUB_DIR, '/', $_G['siteroot']);
		}

		$this->var = &$_G;
	}

	private function _get_script_url() {
		if(!isset($this->var['PHP_SELF'])) {
			$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
			if(basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
				$this->var['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
			} else if(basename($_SERVER['PHP_SELF']) === $scriptName) {
				$this->var['PHP_SELF'] = $_SERVER['PHP_SELF'];
			} else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
				$this->var['PHP_SELF'] = $_SERVER['ORIG_SCRIPT_NAME'];
			} else if(($pos = strpos($_SERVER['PHP_SELF'], '/'.$scriptName)) !== false) {
				$this->var['PHP_SELF'] = substr($_SERVER['SCRIPT_NAME'], 0, $pos).'/'.$scriptName;
			} else if(isset($_SERVER['DOCUMENT_ROOT']) && str_starts_with($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT'])) {
				$this->var['PHP_SELF'] = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
				$this->var['PHP_SELF'][0] != '/' && $this->var['PHP_SELF'] = '/'.$this->var['PHP_SELF'];
			} else {
				system_error('request_tainting');
			}
		}
		return $this->var['PHP_SELF'];
	}

	private function _init_input() {
		if(isset($_GET['GLOBALS']) || isset($_POST['GLOBALS']) || isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
			system_error('request_tainting');
		}

		if(!empty($_COOKIE['token'])) {
			$m = new memory_driver_redis();
			if(!empty($this->var['config']['memory']['redis'])) {
				$m->init($this->var['config']['memory']['redis']);
				if($m->enable) {
					$data = unserialize($m->get('rToken_'.$_COOKIE['token']));
					if(is_array($data) && !empty($data['_session'])) {
						$cookies = unserialize(base64_decode($data['_session']));
						if(is_array($cookies)
							&& !empty($cookies[$this->config['cookie']['cookiepre'].'auth'])
							&& !empty($cookies[$this->config['cookie']['cookiepre'].'saltkey'])) {
							$_COOKIE[$this->config['cookie']['cookiepre'].'auth'] = $cookies[$this->config['cookie']['cookiepre'].'auth'];
							$_COOKIE[$this->config['cookie']['cookiepre'].'saltkey'] = $cookies[$this->config['cookie']['cookiepre'].'saltkey'];
							unset($cookies);
						}
					}
				}
			}
		}

		$prelength = strlen($this->config['cookie']['cookiepre']);
		foreach($_COOKIE as $key => $val) {
			if(substr($key, 0, $prelength) == $this->config['cookie']['cookiepre']) {
				$this->var['cookie'][substr($key, $prelength)] = $val;
			}
		}


		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
			foreach($_POST as $k => $v) {
				$_GET[$k] = $v;
			}
		}

		if(isset($_GET['page'])) {
			$_GET['page'] = rawurlencode($_GET['page']);
		}

		if(!(!empty($_GET['handlekey']) && preg_match('/^\w+$/', $_GET['handlekey']))) {
			unset($_GET['handlekey']);
		}

		$this->var['mod'] = empty($_GET['mod']) ? '' : dhtmlspecialchars($_GET['mod']);
		$this->var['inajax'] = empty($_GET['inajax']) ? 0 : (empty($this->var['config']['output']['ajaxvalidate']) ? 1 : ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || $_SERVER['REQUEST_METHOD'] == 'POST' ? 1 : 0));
		$this->var['page'] = empty($_GET['page']) ? 1 : max(1, intval($_GET['page']));
		$this->var['sid'] = $this->var['cookie']['sid'] = isset($this->var['cookie']['sid']) ? dhtmlspecialchars($this->var['cookie']['sid']) : '';

		if(empty($this->var['cookie']['saltkey'])) {
			$this->var['cookie']['saltkey'] = random(8);
			dsetcookie('saltkey', $this->var['cookie']['saltkey'], 86400 * 30, 1, 1);
		}
		$this->var['authkey'] = md5($this->var['config']['security']['authkey'].$this->var['cookie']['saltkey']);

	}

	private function _init_cnf() {// 新增本方法用于预先加载配置文件，便于在初始化环境时通过$this->config使用配置文件内选项控制初始化流程

		$_config = [];
		@include DISCUZ_ROOT.'./config/config_global.php';
		if(empty($_config)) {
			if(!file_exists(DISCUZ_DATA.'./install.lock')) {
				header('location: install/');
				exit;
			} else {
				exit('config_global.php is missing');
			}
		}

		$this->config = &$_config;

	}

	private function _init_config() {// 原有的基于配置文件设置站点的方法保留原方法名，改为使用$this->var['config']对config进行读写

		if(empty($this->var['config']['security']['authkey'])) {
			$this->var['config']['security']['authkey'] = md5($this->var['config']['cookie']['cookiepre'].$this->var['config']['db'][1]['dbname']);
		}

		if(empty($this->var['config']['debug']) || !file_exists(libfile('function/debug'))) {
			define('DISCUZ_DEBUG', false);
			error_reporting(0);
		} elseif($this->var['config']['debug'] === 1 || $this->var['config']['debug'] === 2 || !empty($_REQUEST['debug']) && $_REQUEST['debug'] === $this->var['config']['debug']) {
			define('DISCUZ_DEBUG', true);
			error_reporting(E_ERROR);
			if($this->var['config']['debug'] === 2) {
				error_reporting(E_ALL);
			}
		} else {
			define('DISCUZ_DEBUG', false);
			error_reporting(0);
		}

		// DISCUZ_DEPRECATED 用于标识 Discuz! X 体系内即将废弃的功能、函数或方法, 便于二开站点维护或三方插件开发
		// 开启后系统出错意味着相关功能将在后续版本删除或抛出异常, 我们建议您不要继续依赖已在此标识下抛出异常的功能
		if(!empty($this->var['config']['deprecated'])) {
			define('DISCUZ_DEPRECATED', $this->var['config']['deprecated']);
		}

		$staticurl = !empty($this->var['config']['output']['staticurl']) ? $this->var['config']['output']['staticurl'] : 'static/';
		if(defined('IN_RESTFUL') && !preg_match('/^(https?:)?\/\//i', $staticurl)) {
			$staticurl = $this->var['siteurl'].$staticurl;
		}
		define('STATICURL', $staticurl);
		$this->var['staticurl'] = STATICURL;

		if(!str_starts_with($this->var['config']['cookie']['cookiepath'], '/')) {
			$this->var['config']['cookie']['cookiepath'] = '/'.$this->var['config']['cookie']['cookiepath'];
		}
		$this->var['config']['cookie']['cookiepre'] = $this->var['config']['cookie']['cookiepre'].substr(md5($this->var['config']['cookie']['cookiepath'].'|'.$this->var['config']['cookie']['cookiedomain']), 0, 4).'_';

	}

	private function _init_output() {

		setglobal('charset', $this->config['output']['charset']);
		define('CHARSET', $this->config['output']['charset']);

		if(defined('IN_RESTFUL')) {
			return;
		}

		if($this->config['security']['attackevasive'] && (!defined('CURSCRIPT') || !in_array($this->var['mod'], ['seccode', 'secqaa', 'swfupload']) && !defined('DISABLEDEFENSE'))) {
			require_once childfile('security', 'global/core');
		}

		if(!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && !str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			$this->config['output']['gzip'] = false;
		}

		$allowgzip = $this->config['output']['gzip'] && empty($this->var['inajax']) && $this->var['mod'] != 'attachment' && EXT_OBGZIP;
		setglobal('gzipcompress', $allowgzip);

		if(!ob_start($allowgzip ? 'ob_gzhandler' : null)) {
			ob_start();
		}

		if($this->config['output']['forceheader']) {
			@header('Content-Type: text/html; charset='.CHARSET);
		}

		if($this->var['isHTTPS'] && isset($this->config['output']['upgradeinsecure']) && $this->config['output']['upgradeinsecure']) {
			@header('Content-Security-Policy: upgrade-insecure-requests');
		}

	}

	public function reject_robot() {
		if(IS_ROBOT) {
			exit(header('HTTP/1.1 403 Forbidden'));
		}
	}

	private function _xss_check() {
		static $check = ['"', '>', '<', '\'', 'CONTENT-TRANSFER-ENCODING'];

		if(isset($_GET['formhash']) && $_GET['formhash'] !== formhash()) {
			if(defined('CURMODULE') && constant('CURMODULE') == 'logging' && isset($_GET['action']) && $_GET['action'] == 'logout') {
				header('HTTP/1.1 302 Found');// 修复多次点击退出时偶发“您当前的访问请求当中含有非法字符，已经被系统拒绝”的Bug
				header('Location: index.php');
				exit();
			} else {
				system_error('request_tainting');
			}
		}

		if($_SERVER['REQUEST_METHOD'] == 'GET') {
			$temp = $_SERVER['REQUEST_URI'];
		} elseif(empty ($_GET['formhash'])) {
			$temp = $_SERVER['REQUEST_URI'].http_build_query($_POST);
		} else {
			$temp = '';
		}

		if(!empty($temp)) {
			$temp = strtoupper(urldecode(urldecode($temp)));
			foreach($check as $str) {
				if(str_contains($temp, $str)) {
					system_error('request_tainting');
				}
			}
		}

		return true;
	}

	private function _is_https() {
		// PHP 标准服务器变量
		if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
			return true;
		}
		// X-Forwarded-Proto 事实标准头部, 用于反代透传 HTTPS 状态
		if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
			return true;
		}
		// 阿里云全站加速私有 HTTPS 状态头部
		// Git 意见反馈 https://gitee.com/Discuz/DiscuzX/issues/I3W5GP
		if(isset($_SERVER['HTTP_X_CLIENT_SCHEME']) && strtolower($_SERVER['HTTP_X_CLIENT_SCHEME']) == 'https') {
			return true;
		}
		// 西部数码建站助手私有 HTTPS 状态头部
		// 官网意见反馈 https://discuz.dismall.com/thread-3849819-1-1.html
		if(isset($_SERVER['HTTP_FROM_HTTPS']) && strtolower($_SERVER['HTTP_FROM_HTTPS']) != 'off') {
			return true;
		}
		// 服务器端口号兜底判断
		if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
			return true;
		}
		return false;
	}

	private function _get_client_ip() {
		$ip = $_SERVER['REMOTE_ADDR'];
		if(!array_key_exists('security', $this->config) || !$this->config['security']['onlyremoteaddr']) {
			if(array_key_exists('ipgetter', $this->config) && !empty($this->config['ipgetter']['setting'])) {
				$s = empty($this->config['ipgetter'][$this->config['ipgetter']['setting']]) ? [] : $this->config['ipgetter'][$this->config['ipgetter']['setting']];
				$c = 'ip_getter_'.$this->config['ipgetter']['setting'];
				$r = $c::get($s);
				$ip = ip::validate_ip($r) ? $r : $ip;
			} elseif(isset($_SERVER['HTTP_CLIENT_IP']) && ip::validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
					$exp = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
					$ip = ip::validate_ip(trim($exp[0])) ? $exp[0] : $ip;
				} else {
					$ip = ip::validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $ip;
				}
			}
		}
		return $ip;
	}

	private function _init_db() {
		if($this->init_db || $this->init_setting) {
			if($this->config['db']['driver'] == 'pdo' && class_exists('PDO')) {
				$driver = 'db_driver_pdo';
				if(getglobal('config/db/slave')) {
					$driver = 'db_driver_pdo_slave';
				}
				$this->var['db_driver'] = 'pdo';
			} else {
				$driver = 'db_driver_mysqli';
				if(getglobal('config/db/slave')) {
					$driver = 'db_driver_mysqli_slave';
				}
				$this->var['db_driver'] = 'mysqli';
			}

			$this->var['mysql_driver'] = $driver;
			DB::init($driver, $this->config['db']);
		}
	}

	private function _init_session() {

		$sessionclose = !empty($this->var['setting']['sessionclose']);
		$this->session = $sessionclose ? new discuz_session_close() : new discuz_session();

		if($this->init_session) {
			$this->session->init($this->var['cookie']['sid'], $this->var['clientip'], $this->var['uid']);
			$this->var['sid'] = $this->session->sid;
			$this->var['session'] = $this->session->var;

			if(isset($this->var['sid']) && $this->var['sid'] !== $this->var['cookie']['sid']) {
				dsetcookie('sid', $this->var['sid'], 86400);
			}

			if(ip::checkbanned($this->var['clientip'])) {
				$this->session->set('groupid', 6);
			}

			if($this->session->get('groupid') == 6) {
				$this->var['member']['groupid'] = 6;
				if(!defined('IN_MOBILE_API')) {
					sysmessage('user_banned');
				} else {
					$this->_restful_output('user_banned');
				}
			}

			if($this->var['uid'] && !$sessionclose && ($this->session->isnew || ($this->session->get('lastactivity') + 600) < TIMESTAMP)) {
				$this->session->set('lastactivity', TIMESTAMP);
				if($this->session->isnew) {
					if($this->var['member']['lastip'] && $this->var['member']['lastvisit']) {
						dsetcookie('lip', $this->var['member']['lastip'].','.$this->var['member']['lastvisit']);
					}
					table_common_member_status::t()->update($this->var['uid'], ['lastip' => $this->var['clientip'], 'port' => $this->var['remoteport'], 'lastvisit' => TIMESTAMP]);
				}
			}

		}
	}

	private function _init_user() {
		if($this->init_user) {
			$_authkey = getglobal('config/admincp/mustlogin') || !defined('IN_ADMINCP') ? 'auth' : 'adminauth';
			if($auth = getglobal($_authkey, 'cookie')) {
				$auth = daddslashes(explode("\t", authcode($auth, 'DECODE')));
			}
			list($discuz_pw, $discuz_uid) = empty($auth) || count($auth) < 2 ? ['', ''] : $auth;

			if($discuz_uid) {
				$user = getuserbyuid($discuz_uid, 1);
			}

			if(!empty($user) && $user['password'] == $discuz_pw && $user['freeze'] != -2) {
				if(isset($user['_inarchive'])) {
					table_common_member_archive::t()->move_to_master($discuz_uid);
				}
				$this->var['member'] = $user;
			} else {
				$user = [];
				$this->_init_guest();
			}

			if(empty($user) && (!empty($this->var['setting']['account']['loginAuto']) || !empty($this->var['setting']['account']['loginAutoDefault'])) &&
				!defined('IN_API') && (!defined('CURSCRIPT') || !in_array(CURSCRIPT, ['admin', 'member', 'misc']))) {
				account::method_loginAuto();
			}

			if($user && $user['groupexpiry'] > 0 && $user['groupexpiry'] < TIMESTAMP) {
				$memberfieldforum = table_common_member_field_forum::t()->fetch($discuz_uid);
				$groupterms = dunserialize($memberfieldforum['groupterms']);
				if(!empty($groupterms['main'])) {
					if($groupterms['main']['groupid']) {
						$user['groupid'] = $groupterms['main']['groupid'];
					} else {
						$groupnew = table_common_usergroup::t()->fetch_by_credits($user['credits']);
						$user['groupid'] = $groupnew['groupid'];
					}
					$user['adminid'] = $groupterms['main']['adminid'];
					C::t('common_member')->update($user['uid'], ['groupexpiry' => 0, 'groupid' => $user['groupid'], 'adminid' => $user['adminid']]);
					unset($groupterms['main'], $groupterms['ext'][$this->var['member']['groupid']]);
					$this->var['member'] = $user;
					table_common_member_field_forum::t()->update($discuz_uid, ['groupterms' => serialize($groupterms)]);
				} elseif((getgpc('mod') != 'spacecp' || CURSCRIPT != 'home') && CURSCRIPT != 'member') {
					dheader('location: home.php?mod=spacecp&ac=usergroup&do=expiry');
				}
			}

			if($user && $user['freeze'] && (getgpc('mod') != 'spacecp' && getgpc('mod') != 'misc' || CURSCRIPT != 'home') && CURSCRIPT != 'member' && CURSCRIPT != 'misc') {
				dheader('location: home.php?mod=spacecp&ac=account');
			}

			$this->cachelist[] = 'usergroup_'.$this->var['member']['groupid'];
			if($user && $user['adminid'] > 0 && $user['groupid'] != $user['adminid']) {
				$this->cachelist[] = 'admingroup_'.$this->var['member']['adminid'];
			}

		} else {
			$this->_init_guest();
		}
		setglobal('groupid', getglobal('groupid', 'member'));
		!empty($this->cachelist) && loadcache($this->cachelist);

		if($this->var['member'] && $this->var['group']['radminid'] == 0 && $this->var['member']['adminid'] > 0 && $this->var['member']['groupid'] != $this->var['member']['adminid'] && !empty($this->var['cache']['admingroup_'.$this->var['member']['adminid']])) {
			$this->var['group'] = array_merge($this->var['group'], $this->var['cache']['admingroup_'.$this->var['member']['adminid']]);
		}

		if(!empty($this->var['group']['allowmakehtml']) && isset($_GET['_makehtml'])) {
			$this->var['makehtml'] = 1;
			$this->_init_guest();
			loadcache(['usergroup_7']);
			$this->var['group'] = $this->var['cache']['usergroup_7'];
			unset($this->var['inajax']);
		}

		if(empty($this->var['cookie']['lastvisit'])) {
			$this->var['member']['lastvisit'] = TIMESTAMP - 3600;
			dsetcookie('lastvisit', TIMESTAMP - 3600, 86400 * 30);
		} else {
			$this->var['member']['lastvisit'] = $this->var['cookie']['lastvisit'];
		}

		setglobal('uid', getglobal('uid', 'member'));
		setglobal('username', getglobal('username', 'member'));
		setglobal('adminid', getglobal('adminid', 'member'));
		setglobal('groupid', getglobal('groupid', 'member'));
		if(!empty($this->var['member']['newprompt'])) {
			$this->var['member']['newprompt_num'] = table_common_member_newprompt::t()->fetch($this->var['member']['uid']);
			$this->var['member']['newprompt_num'] = dunserialize($this->var['member']['newprompt_num']['data']);
			$this->var['member']['category_num'] = helper_notification::get_categorynum($this->var['member']['newprompt_num']);
		}

	}

	private function _init_guest() {
		setglobal('member', ['uid' => 0, 'username' => '', 'adminid' => 0, 'groupid' => 7, 'credits' => 0, 'timeoffset' => 9999]);
	}

	private function _init_cron() {
		$ext = empty($this->config['remote']['on']) || empty($this->config['remote']['cron']) || APPTYPEID == 200;
		if(!defined('DISABLEDEFENSE') && $this->init_cron && $this->init_setting && $ext) {
			if($this->var['cache']['cronnextrun'] <= TIMESTAMP) {
				discuz_cron::run();
			}
		}
	}

	private function _init_i18n() {
		$newi18n = '';
		$isDefault = !empty($this->var['cookie']['d_i18n']);
		$this->var['i18n'] = !empty($this->var['cookie']['i18n']) && preg_match('/^\w+$/', $this->var['cookie']['i18n']) ? $this->var['cookie']['i18n'] : '';
		if(!$this->var['i18n'] || $isDefault) {
			$newi18n = !empty($this->var['setting']['i18n_default']) ? $this->var['setting']['i18n_default'] : '';
			dsetcookie('d_i18n', 1, 86400 * 365);

			if($this->var['i18n'] != $newi18n) {
				dsetcookie('i18n', $newi18n, 86400 * 365);
			}
		}
		if($newi18n) {
			$this->var['i18n'] = $newi18n;
		}
	}

	private function _init_misc() {
		if($this->config['security']['urlxssdefend'] && !defined('DISABLEXSSCHECK')) {
			$this->_xss_check();
		}

		if(!$this->init_misc) {
			return false;
		}

		$this->_init_i18n();

		lang('core');

		if($this->init_setting && $this->init_user) {
			if(!isset($this->var['member']['timeoffset']) || $this->var['member']['timeoffset'] == 9999 || $this->var['member']['timeoffset'] === '') {
				$this->var['member']['timeoffset'] = $this->var['setting']['timeoffset'];
			}
		}

		$timeoffset = $this->init_setting ? $this->var['member']['timeoffset'] : $this->var['setting']['timeoffset'];
		$this->var['timenow'] = [
			'time' => dgmdate(TIMESTAMP),
			'offset' => $timeoffset >= 0 ? ($timeoffset == 0 ? '' : '+'.$timeoffset) : $timeoffset
		];
		$this->_timezone_set($timeoffset);

		$this->var['formhash'] = formhash();
		define('FORMHASH', $this->var['formhash']);

		if($this->init_user) {
			$allowvisitflag = CURSCRIPT == 'member' || defined('ALLOWGUEST') && ALLOWGUEST;
			if($this->var['group'] && isset($this->var['group']['allowvisit']) && !$this->var['group']['allowvisit']) {
				if($this->var['uid'] && !$allowvisitflag) {
					if(!defined('IN_MOBILE_API')) {
						($this->var['member']['groupexpiry'] > 0) ? showmessage('user_banned_has_expiry', '', ['expiry' => dgmdate($this->var['member']['groupexpiry'], 'Y-m-d H:i:s')]) : showmessage('user_banned');
					} else {
						($this->var['member']['groupexpiry'] > 0) ? $this->_restful_output('user_banned_has_expiry') : $this->_restful_output('user_banned');
					}
				} elseif((!defined('ALLOWGUEST') || !ALLOWGUEST) && !in_array(CURSCRIPT, ['admin', 'member', 'api'])) {
					if(defined('IN_ARCHIVER')) {
						dheader('location: ../member.php?mod=logging&action=login&referer='.rawurlencode($this->var['siteurl'].'archiver/'.$this->var['basefilename'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '')));
					} else if(!defined('IN_MOBILE_API')) {
						dheader('location: member.php?mod=logging&action=login&referer='.rawurlencode($this->var['siteurl'].$this->var['basefilename'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '')));
					} else {
						$this->_restful_output('to_login');
					}
				}
			}
			if(isset($this->var['member']['status']) && $this->var['member']['status'] == -1 && !$allowvisitflag) {
				if(!defined('IN_MOBILE_API')) {
					showmessage('user_banned');
				} else {
					$this->_restful_output('user_banned');
				}
			}
		}

		if(!defined('DISABLEDEFENSE') && $this->var['setting']['ipaccess'] && !ipaccess($this->var['clientip'], $this->var['setting']['ipaccess'])) {
			if(!defined('IN_MOBILE_API')) {
				showmessage('user_banned');
			} else {
				$this->_restful_output('user_banned');
			}
		}

		if($this->var['setting']['bbclosed']) {
			if($this->var['uid'] && ($this->var['group']['allowvisit'] == 2 || $this->var['groupid'] == 1)) {
			} elseif(in_array(CURSCRIPT, ['admin', 'member', 'api']) || defined('ALLOWGUEST') && ALLOWGUEST) {
			} else {
				$closedreason = table_common_setting::t()->fetch_setting('closedreason');
				$closedreason = str_replace(':', '&#58;', $closedreason);
				if(!defined('IN_MOBILE_API')) {
					showmessage($closedreason ? $closedreason : 'board_closed', NULL, ['adminemail' => $this->var['setting']['adminemail']], ['login' => 1]);
				} else {
					$this->_restful_output($closedreason ? $closedreason : 'board_closed');
				}
			}
		}

		if(!defined('DISABLEDEFENSE') && CURSCRIPT != 'admin' && !(in_array($this->var['mod'], ['logging', 'seccode']))) {
			periodscheck('visitbanperiods');
		}

		if(defined('IN_MOBILE')) {
			$this->var['tpp'] = $this->var['setting']['mobile']['forum']['topicperpage'] ? intval($this->var['setting']['mobile']['forum']['topicperpage']) : ($this->var['setting']['topicperpage'] ? intval($this->var['setting']['topicperpage']) : 20);
			$this->var['ppp'] = $this->var['setting']['mobile']['forum']['postperpage'] ? intval($this->var['setting']['mobile']['forum']['postperpage']) : ($this->var['setting']['postperpage'] ? intval($this->var['setting']['postperpage']) : 10);
		} else {
			$this->var['tpp'] = $this->var['setting']['topicperpage'] ? intval($this->var['setting']['topicperpage']) : 20;
			$this->var['ppp'] = $this->var['setting']['postperpage'] ? intval($this->var['setting']['postperpage']) : 10;
		}

		if($this->var['setting']['nocacheheaders']) {
			@header('Expires: -1');
			@header('Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0', FALSE);
			@header('Pragma: no-cache');
		}

		if($this->session->isnew && $this->var['uid']) {
			include_once libfile('function/stat');
			updatestat('login', 1);
			if(defined('IN_MOBILE')) {
				updatestat('mobilelogin', 1);
			}
		}

		$lastact = TIMESTAMP."\t".dhtmlspecialchars(basename($this->var['PHP_SELF']))."\t".dhtmlspecialchars($this->var['mod']);
		dsetcookie('lastact', $lastact, 86400);
		setglobal('currenturl_encode', base64_encode($this->var['scheme'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));

		if((!empty($_GET['fromuid']) || !empty($_GET['fromuser'])) && ($this->var['setting']['creditspolicy']['promotion_visit'] || $this->var['setting']['creditspolicy']['promotion_register'])) {
			require_once childfile('promotion', 'global/core');;
		}

		$this->var['seokeywords'] = !empty($this->var['setting']['seokeywords'][CURSCRIPT]) ? $this->var['setting']['seokeywords'][CURSCRIPT] : '';
		$this->var['seodescription'] = !empty($this->var['setting']['seodescription'][CURSCRIPT]) ? $this->var['setting']['seodescription'][CURSCRIPT] : '';
	}

	private function _restful_output($msg) {
		if(!defined('IN_RESTFUL') || empty($_ENV['restful']) || !defined('IN_RESTFUL_API')) {
			return;
		}
		$_ENV['restful']->output([
			'ret' => -1,
			'msg' => $msg
		]);
	}

	private function _init_setting() {
		if($this->init_setting) {
			if(empty($this->var['setting'])) {
				$this->cachelist[] = 'setting';
			}

			if(empty($this->var['style'])) {
				$this->cachelist[] = 'style_default';
			}

			if(!isset($this->var['cache']['cronnextrun'])) {
				$this->cachelist[] = 'cronnextrun';
			}
		}

		!empty($this->cachelist) && loadcache($this->cachelist);
	}

	public function _init_style() {
		if(defined('IN_MOBILE')) {
			$mobile = max(1, intval(IN_MOBILE));
			if($mobile && $this->var['setting']['styleid2']) {
				$styleid = $this->var['setting']['styleid2'];
			}
		} else {
			$styleid = !empty($this->var['cookie']['styleid']) ? $this->var['cookie']['styleid'] : 0;

			if(intval(!empty($this->var['forum']['styleid']))) {
				$this->var['cache']['style_default']['styleid'] = $styleid = $this->var['forum']['styleid'];
			} elseif(intval(!empty($this->var['category']['styleid']))) {
				$this->var['cache']['style_default']['styleid'] = $styleid = $this->var['category']['styleid'];
			}
		}

		if(defined('IN_NEWMOBILE') && $this->var['setting']['mobile']['allowmnew'] && $this->var['setting']['styleid2']) {
			$styleid = $this->var['setting']['styleid2'];
		}

		if(defined('IN_ADMINCP') && $this->var['setting']['styleid3']) {
			$styleid = $this->var['setting']['styleid3'];
		}

		$styleid = intval($styleid);

		if($styleid && $styleid != $this->var['setting']['styleid']) {
			loadcache('style_'.$styleid);
			if($this->var['cache']['style_'.$styleid]) {
				$this->var['style'] = $this->var['cache']['style_'.$styleid];
			}
		}

		define('IMGDIR', $this->var['style']['imgdir']);
		define('STYLEID', $this->var['style']['styleid']);
		define('VERHASH', $this->var['style']['verhash']);
		define('TPLDIR', $this->var['style']['tpldir']);
		define('TEMPLATEID', $this->var['style']['templateid']);
	}

	private function _init_mobile() {
		if(!empty(getgpc('mobilediy'))) {
			define('IN_MOBILE', '2');
			return true;
		}
		if(!$this->init_mobile) {
			if(!defined('HOOKTYPE')) {
				define('HOOKTYPE', 'hookscript');
			}
			return false;
		}

		if(!$this->var['setting'] || !$this->var['setting']['mobile']['allowmobile'] || !is_array($this->var['setting']['mobile'])) {
			$nomobile = true;
			$unallowmobile = true;
		}

		if(getgpc('forcemobile')) {
			dsetcookie('dismobilemessage', '1', 3600);
		}

		$mobile = getgpc('mobile');
		if(!getgpc('mobile') && getgpc('showmobile')) {
			$mobile = getgpc('showmobile');
		}
		$mobileflag = isset($this->var['mobiletpl'][$mobile]);
		if($mobile === 'no') {
			dsetcookie('mobile', 'no', 3600);
			$nomobile = true;
		} elseif(isset($this->var['cookie']['mobile']) && $this->var['cookie']['mobile'] == 'no' && $mobileflag) {
			checkmobile();
			dsetcookie('mobile', '');
		} elseif(isset($this->var['cookie']['mobile']) && $this->var['cookie']['mobile'] == 'no') {
			$nomobile = true;
		} elseif(!($mobile_ = checkmobile())) {
			$nomobile = true;
		}
		if(!$mobile || $mobile == 'yes') {
			$mobile = $mobile_ ?? 2;
		}

		if(!$this->var['mobile'] && empty($unallowmobile) && $mobileflag) {
			if(getgpc('showmobile')) {
				dheader('Location:misc.php?mod=mobile');
			}
			parse_str($_SERVER['QUERY_STRING'], $query);
			$query['mobile'] = 'no';
			unset($query['simpletype']);
			$query_sting_tmp = http_build_query($query);
			$redirect = ($this->var['setting']['domain']['app']['forum'] ? $this->var['scheme'].'://'.$this->var['setting']['domain']['app']['forum'].'/' : $this->var['siteurl']).$this->var['basefilename'].'?'.$query_sting_tmp;
			dheader('Location: '.$redirect);
		}

		if($nomobile || (!$this->var['setting']['mobile']['mobileforward'] && !$mobileflag)) {
			if(!defined('HOOKTYPE')) {
				define('HOOKTYPE', 'hookscript');
			}
			if(!empty($this->var['setting']['domain']['app']['mobile']) && $_SERVER['HTTP_HOST'] == $this->var['setting']['domain']['app']['mobile'] && !empty($this->var['setting']['domain']['app']['default'])) {
				dheader('Location:'.$this->var['scheme'].'://'.$this->var['setting']['domain']['app']['default'].$_SERVER['REQUEST_URI']);
				return false;
			} else {
				return false;
			}
		}

		if($mobile !== '2' && empty($this->var['setting']['mobile']['legacy'])) {
			$mobile = '2';
		}
		define('IN_MOBILE', isset($this->var['mobiletpl'][$mobile]) ? $mobile : '2');
		if(!defined('HOOKTYPE')) {
			define('HOOKTYPE', 'hookscriptmobile');
		}
		setglobal('gzipcompress', 0);

		$arr = [];
		foreach(array_keys($this->var['mobiletpl']) as $mobiletype) {
			$arr[] = '&mobile='.$mobiletype;
			$arr[] = 'mobile='.$mobiletype;
		}

		parse_str($_SERVER['QUERY_STRING'], $query);
		$query['mobile'] = 'no';
		unset($query['simpletype']);
		$query_sting_tmp = http_build_query($query);
		$this->var['setting']['mobile']['nomobileurl'] = ($this->var['setting']['domain']['app']['forum'] ? $this->var['scheme'].'://'.$this->var['setting']['domain']['app']['forum'].'/' : $this->var['siteurl']).$this->var['basefilename'].'?'.$query_sting_tmp;

		$this->var['setting']['lazyload'] = 0;

		if('utf-8' != CHARSET) {
			if(strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
				foreach($_POST as $pk => $pv) {
					if(!is_numeric($pv)) {
						$_GET[$pk] = $_POST[$pk] = $this->_mobile_iconv_recurrence($pv);
					}
				}
			}
		}


		if(!$this->var['setting']['mobile']['mobilesimpletype']) {
			$this->var['setting']['imagemaxwidth'] = 224;
		}

		$this->var['setting']['regstatus'] = $this->var['setting']['mobile']['mobileregister'] ? $this->var['setting']['regstatus'] : 0;
		$this->var['setting']['avatarmethod'] = 0;
		ob_start();
	}

	private function _timezone_set($timeoffset = 0) {
		if(function_exists('date_default_timezone_set')) {
			@date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
		}
	}

	private function _mobile_iconv_recurrence($value) {
		if(is_array($value)) {
			foreach($value as $key => $val) {
				$value[$key] = $this->_mobile_iconv_recurrence($val);
			}
		} else {
			$value = diconv($value, 'utf-8', CHARSET);
		}
		return $value;
	}
}

