<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') && !defined('IN_RESTFUL')) {
	exit('Access Denied');
}

const RESTFUL_REDIS_PREFIX = '';
const RESTFUL_DEVELOPER_MODE = false;

define('ROOT_PATH', dirname(__FILE__).'/../../');

class restful {

	const AppIdConfPre = RESTFUL_REDIS_PREFIX.'rApp_';

	const ApiConfPre = RESTFUL_REDIS_PREFIX.'rApi_';

	public static function cache($type, $op, $id) {
		global $_G;

		$m = new memory_driver_redis();
		$m->init($_G['config']['memory']['redis']);
		if(!$m->enable) {
			return false;
		}

		switch($type) {
			case 'api':
				list($baseuri, $ver) = explode('|', $id);
				$key = self::ApiConfPre.$baseuri.'_v'.$ver;
				switch($op) {
					case 'del':
						$m->rm($key);
						break;
					case 'add':
						$data = table_restful_api::t()->fetch_by_baseuri_ver($baseuri, $ver);
						$cacheData = json_decode($data['data'], true);
						$m->set($key, serialize($cacheData));
						break;
				}
				break;
			case 'app':
				$key = self::AppIdConfPre.$id;
				switch($op) {
					case 'del':
						$m->rm($key);
						break;
					case 'add':
						$data = table_restful_app::t()->fetch($id);
						if(!$data || empty($data['status'])) {
							break;
						}
						$appData = json_decode($data['data'], true);
						$appPerm = table_restful_permission::t()->fetch_all_by_appid($id);
						$apis = $freq = [];
						foreach($appPerm as $row) {
							$k = $row['uri'].'/v'.$row['ver'];
							$apis[] = $k;
							if($row['freq'] > 0) {
								$freq[$k] = $row['freq'];
							}
						}
						$m->set($key, serialize([
							'secret' => $data['secret'],
							'apis' => $apis,
							'freq' => $freq,
							'seccheck' => $appData['seccheck'] ?? 0,
							'log' => $appData['log'] ?? false,
							'token' => $appData['tokenTTL'] ?? 0,
							'refreshTokenTTL' => $appData['refreshTokenTTL'] ?? 0,
						]));
						break;
				}
				break;
		}

		return true;
	}

	private $apiParam;

	private $postParam;

	public $token;

	public $tokenData;

	private $_config;

	private $_appId;

	private $_m;

	const SignTTL = 300;

	const TokenTTL = 3600;

	const TokenSaveTTL = 86400 * 7;

	const AuthTokenTTL = 300;

	const TokenPre = RESTFUL_REDIS_PREFIX.'rToken_';

	const AuthTokenPre = RESTFUL_REDIS_PREFIX.'rAuthToken_';

	const ApiFreqPre = RESTFUL_REDIS_PREFIX.'rFreq_';

	const UniqueIdPre = RESTFUL_REDIS_PREFIX.'rUniqueId_';

	const ApiFreqTTL = 60;

	const AuthPre = RESTFUL_REDIS_PREFIX.'rAuth_';
	const AuthTTL = 60;

	const Error = [
		-100 => 'run: script is exception',
		-101 => 'checkSign: param is missing',
		-102 => 'checkSign: appid is invalid',
		-103 => 'checkSign: sign is expire',
		-104 => 'checkSign: sign is invalid',
		-105 => 'checkToken: token is missing',
		-106 => 'checkToken: token is expire',
		-107 => 'checkToken: appid is error',
		-108 => 'getTokenData: token is error',
		-109 => 'getApiParam: api is invalid',
		-110 => 'getAppidPerm: appid is invalid',
		-111 => 'getSecret: appid is invalid',
		-112 => 'parseQuery: api url is empty',
		-113 => 'parseQuery: api url is error',
		-114 => 'initParam: api is invalid',
		-115 => 'apiPermCheck: api is invalid',
		-116 => 'apiFreqCheck: out of frequency',
		-117 => 'scriptCheck: script is empty',
		-118 => 'scriptCheck: script format is error',
		-119 => 'callback: authtoken is invalid',
		-120 => 'validate: data is invalid',
		-121 => 'validate: uniqueid is invalid',
	];

	public function __construct($postParam = []) {
		require_once ROOT_PATH.'./source/function/function_core.php';
		$this->postParam = $postParam;
	}

	public function parseQuery() {
		$s = rawurldecode($_SERVER['QUERY_STRING']);
		if(!$s) {
			$this->error(-112);
		}
		if(str_ends_with($s, '=')) {
			$s = substr($s, 0, -1);
		}
		if(!str_starts_with($s, '/')) {
			$s = '/'.$s;
		}
		$e = explode('/', $s);
		$c = count($e);
		if(preg_match('/^v\d+$/', $e[$c - 1])) {
			$api = array_slice($e, 1, -1);
			$ver = $e[$c - 1];
		} else {
			$api = array_slice($e, 1);
			$ver = 'v1';
		}
		if(!$api || !$ver) {
			$this->error(-113);
		}
		$_ENV['restful_api'] = '/'.implode('/', $api).
			(in_array($api[0], ['token', 'authtoken', 'deltoken']) ? '' : '/'.$ver);
		return [$api, $ver];
	}

	public function initParam($api, $ver) {
		$apiParams = $this->_getApiParam($api, $ver);
		$key = '/'.implode('/', $api);
		if(empty($apiParams[$key])) {
			$this->error(-114);
		}
		$params = $apiParams[$key];
		$params['perms'] = [$key.'/'.$ver];
		$this->apiParam = $params;
		return $params['script'];
	}

	public function validate(&$body) {
		if(!empty($this->apiParam['attr']['validateData'])) {
			$this->_validateData($body);
		}
		if(!empty($this->apiParam['attr']['validateUnique'])) {
			$this->_validateUnique($body);
		}
	}

	private function _validateData(&$body) {
		if(empty($_SERVER['HTTP_VALIDATECODE']) ||
			$_SERVER['HTTP_VALIDATECODE'] != sha1($this->tokenData['_conf']['secret'].$body)) {
			$this->error(-120);
		}
	}

	private function _validateUnique(&$body) {
		if(empty($_SERVER['HTTP_UNIQUEID'])) {
			$this->error(-121);
		}
		$k = self::UniqueIdPre.$this->_appId.'_'.$_SERVER['HTTP_UNIQUEID'];
		if($this->_memory('get', [$k])) {
			$this->error(-121);
		}
		$this->_memory('set', [$k, [time()], $this->apiParam['attr']['validateUnique']]);
	}

	public function paramDecode($key) {
		if(empty($this->apiParam[$key])) {
			return [];
		}

		if(is_array($this->apiParam[$key])) {
			$v = $this->apiParam[$key];
		} else {
			$_tmp = dunserialize($this->apiParam[$key]);
			$v = $_tmp === false ? $this->apiParam[$key] : $_tmp;
		}
		return $this->_replacePostParams($v);
	}

	public function getRequestParam() {
		return !empty($this->postParam['_REQUEST']) ? json_decode($this->postParam['_REQUEST'], true) : [];
	}

	public function getShutdownFunc() {
		$output = !empty($this->apiParam['output']) ? $this->apiParam['output'] : [];
		$rawOutput = !empty($this->apiParam['raw']);
		$shutdownFunc = 'showOutput';
		if($rawOutput) {
			$shutdownFunc = 'rawOutput';
		} elseif($output) {
			$shutdownFunc = 'convertOutput';
		}
		return [$shutdownFunc, $output];
	}

	private function _replacePostParams($v) {
		foreach($v as $_k => $_v) {
			if(is_array($_v)) {
				$v[$_k] = $this->_replacePostParams($_v);
			} elseif(is_string($_v)) {
				if(str_starts_with($_v, '{') && preg_match('/^\{:(\w+):\}$/', $_v, $r)) {
					$v[$_k] = !empty($this->postParam[$r[1]]) ? $this->postParam[$r[1]] : null;
				}
			}
		}
		return $v;
	}

	public function apiPermCheck() {
		if(!empty($this->tokenData['_conf']['apis']) && !array_intersect($this->apiParam['perms'], $this->tokenData['_conf']['apis'])) {
			$this->error(-115);
		}
	}

	public function apiFreqCheck() {
		$api = $this->apiParam['perms'][0];
		if(!empty($this->tokenData['_conf']['freq'][$api])) {
			$key = self::ApiFreqPre.$this->_appId.'_'.$api;
			$v = $this->_memory('get', [$key]);
			if(!$v) {
				$this->_memory('inc', [$key]);
				$this->_memory('expire', [$key, self::ApiFreqTTL]);
			} elseif($v >= $this->tokenData['_conf']['freq'][$api]) {
				$this->error(-116);
			} else {
				$this->_memory('inc', [$key]);
			}
		}
	}

	public function scriptCheck() {
		if(empty($this->apiParam['script'])) {
			$this->error(-117);
		}
		if(!preg_match('/^\w+$/', $this->apiParam['script'])) {
			$this->error(-118);
		}
		return $this->apiParam['script'];
	}

	public function error($code) {
		$this->output([
			'ret' => $code,
			'msg' => !empty(self::Error[$code]) ? self::Error[$code] : '',
		]);
	}

	public function output($value) {
		$return = [];
		$this->_log($value);
		$this->_setSysVar($return, $value);
		echo json_encode($value);
		exit;
	}

	public function newTokenExp() {
		return time() + ($this->tokenData['_conf']['token'] > 0 ? $this->tokenData['_conf']['token'] : self::TokenTTL);
	}

	public function newSaveTokenExp() {
		return time() + ($this->tokenData['_conf']['refreshTokenTTL'] > 0 ? $this->tokenData['_conf']['refreshTokenTTL'] : self::TokenSaveTTL);
	}

	public function showOutput() {
		$return = ['ret' => 0];

		$s = ob_get_contents();
		ob_end_clean();
		$return['data']['content'] = $s;

		$this->plugin('after', $return['data']);
		$this->output($return);
	}

	public function rawOutput() {
		$value = [];
		$this->_log($value);
		$newTokenData = $this->updateTokenData();
		if($newTokenData) {
			$ttl = $this->tokenData['refreshExptime'];
			$this->setToken($this->token, $newTokenData, $ttl);
		}
		exit;
	}

	public function convertOutput($output) {
		ob_end_clean();
		$return = ['ret' => 0];

		$tmp = $GLOBALS;
		foreach($output as $k => $v) {
			if(str_contains($k, '/')) {
				$return['data'][$v] = $this->_arrayVar($tmp, $k);
			} else {
				$return['data'][$v] = $this->_singleVar($tmp, $k);
			}
		}
		$this->plugin('after', $return['data']);

		$this->output($return);
	}

	private function _log(&$value) {
		if(class_exists('table_restful_stat')) {
			table_restful_stat::t()->updatestat($_SERVER['HTTP_APPID'], $_ENV['restful_api']);
		}
		if(empty($this->tokenData['_conf']['log']) || !function_exists('logger') || !function_exists('getLogInfo')) {
			return;
		}
		global $_G;
		logger('restful',
			$_G['member'] ?? [],
			$_G['member']['uid'] ?? 0,
			[
				'appid' => $_SERVER['HTTP_APPID'],
				'api' => $_ENV['restful_api'],
				'params' => strlen(serialize($this->postParam)) > 2048 ? substr(serialize($this->postParam), 0, 2048).'...(Too long, extract some records...)' : $this->postParam,
				'response' => strlen(serialize($value)) > 2048 ? substr(serialize($value), 0, 2048).'...(Too long, extract some records...)' : $value,
				'ret' => $value['ret'] ?? 'unknown',
			]);
	}

	public function plugin($type, &$data) {
		if(empty($this->apiParam['plugin'][$type])) {
			return;
		}
		foreach($this->apiParam['plugin'][$type] as $method => $value) {
			$vars = explode(':', $method);
			if(count($vars) == 2) {
				if(!preg_match('/^\w+$/', $vars[0])) {
					continue;
				}
				require_once ROOT_PATH.'./source/function/function_path.php';
				$f = ROOT_PATH.PLUGIN_ROOT.$vars[0].'/restful.class.php';
				$c = 'restful_'.$vars[0];
				$m = $vars[1];
			} else {
				$f = ROOT_PATH.'./source/class/class_restfulplugin.php';
				$c = 'restfulplugin';
				$m = $method;
			}
			if(!file_exists($f)) {
				continue;
			}
			@require_once $f;
			if(!class_exists($c) || !method_exists($c, $m)) {
				continue;
			}
			call_user_func_array([$c, $m], [&$data, explode(',', $value['param'] ?? '')]);
		}
	}

	public function sessionDecode() {
		$session = dunserialize(base64_decode($this->tokenData['_session']));
		if(!empty($this->postParam['_authSign'])) {
			$this->decodeAuthSign($session);
		}
		return $session;
	}

	public function checkSign() {
		if(empty($_SERVER['HTTP_APPID']) ||
			empty($_SERVER['HTTP_SIGN']) ||
			empty($_SERVER['HTTP_NONCE']) ||
			empty($_SERVER['HTTP_T'])) {
			$this->error(-101);
		}
		$this->_getAppidPerm($_SERVER['HTTP_APPID']);
		$secret = $this->_getSecret();
		if(!$secret) {
			$this->error(-102);
		}
		if(time() - $_SERVER['HTTP_T'] > self::SignTTL) {
			$this->error(-103);
		}
		if($_SERVER['HTTP_SIGN'] != base64_encode(hash('sha256', $_SERVER['HTTP_NONCE'].$_SERVER['HTTP_T'].$secret))) {
			$this->error(-104);
		}
	}

	public function checkToken() {
		if(empty($this->_getToken())) {
			$this->error(-105);
		}
		$v = $this->_getTokenData();
		if(time() >= $v['exptime']) {
			$this->error(-106);
		}
		$this->tokenData = $v;
		if(!$v['_appid'] || $v['_appid'] != $_SERVER['HTTP_APPID']) {
			$this->error(-107);
		}
		$this->_getAppidPerm($v['_appid']);
		$this->postParam['formhash'] = !empty($this->tokenData['_formhash']) ? $this->tokenData['_formhash'] : '';
		$this->token = $this->_getToken();
	}

	public function setToken($key, $value, $ttl = 0) {
		$this->_memory('set', [self::TokenPre.$key, serialize($value), ($ttl > 0 ? $ttl : $this->newSaveTokenExp()) - time()]);
	}

	public function setAuthToken($token, $value) {
		if($this->getAuthToken($token)) {
			return false;
		}
		global $_G;
		$value = authcode(serialize($value), 'ENCODE', md5($_G['config']['security']['authkey']));
		$this->_memory('set', [self::AuthTokenPre.$token, $value, self::AuthTokenTTL]);
		return true;
	}

	public function getAuthToken($token) {
		global $_G;
		$value = $this->_memory('get', [self::AuthTokenPre.$token]);
		return $value ? dunserialize(authcode($value, 'DECODE', md5($_G['config']['security']['authkey']))) : [];
	}

	public function isRefreshToken() {
		return !empty($this->_getToken());
	}

	private function _getToken() {
		return !empty($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : (!empty($this->postParam['token']) ? $this->postParam['token'] : '');
	}

	private function _getTokenData($return = false) {
		$v = dunserialize($this->_memory('get', [self::TokenPre.$this->_getToken()]));
		if(empty($v)) {
			if($return) {
				return [];
			}
			$this->error(-108);
		}
		return $v;
	}

	public function refreshTokenData() {
		global $_G;

		$v = $this->_getTokenData(true);
		if(!$v) {
			return false;
		}
		$data['_session'] = $v['_session'];
		$data['_formhash'] = $this->_singleVar($_G, 'formhash');
		$data['_appid'] = $_SERVER['HTTP_APPID'];
		$data['_conf'] = $this->tokenData['_conf'];
		$data['exptime'] = $this->newTokenExp();
		$data['refreshExptime'] = $this->newSaveTokenExp();
		return serialize($data);
	}

	public function delTokenData() {
		$this->_memory('rm', [self::TokenPre.$this->_getToken()]);
	}

	public function newTokenData() {
		global $_G;
		$data = [];
		$data['_session'] = $this->_sessionEncode($_COOKIE);
		$data['_formhash'] = $this->_singleVar($_G, 'formhash');
		$data['_appid'] = $_SERVER['HTTP_APPID'];
		$data['_conf'] = $this->tokenData['_conf'];
		$data['exptime'] = $this->newTokenExp();
		$data['refreshExptime'] = $this->newSaveTokenExp();
		return serialize($data);
	}

	private function _getApiParam($api, $ver) {
		if(RESTFUL_DEVELOPER_MODE) {
			return $this->_getApiParam_Developer($api, $ver);
		}
		$v = $this->_memory('get', [self::ApiConfPre.'/'.$api[0].'_'.$ver]);
		if(!$v) {
			$this->error(-109);
		}
		return $this->apiParam = dunserialize($v);
	}

	private function _getAppidPerm($appid) {
		$v = $this->_memory('get', [self::AppIdConfPre.$appid]);
		if(!$v) {
			$this->error(-110);
		}
		$this->_appId = $appid;
		$v = dunserialize($v);
		$this->tokenData['_conf'] = $v;
	}

	public function updateTokenData() {
		global $_G;
		$data = $this->tokenData;
		$_session = $this->_sessionEncode($_COOKIE);
		if(!empty($this->tokenData['_session']) && $_session == $this->tokenData['_session']) {
			return null;
		}
		$data['_session'] = $_session;
		$data['_formhash'] = $this->_singleVar($_G, 'formhash');
		return serialize($data);
	}

	private function _getSecret() {
		if(empty($this->tokenData['_conf']['secret'])) {
			$this->error(-111);
		}
		return $this->tokenData['_conf']['secret'];
	}

	private function _sessionEncode($v) {
		return base64_encode(serialize($v));
	}

	private function _setSysVar(&$data, &$output = []) {
		global $_G;

		$newTokenData = $this->updateTokenData();
		if(!empty($this->tokenData['refreshExptime']) && $newTokenData) {
			$ttl = $this->tokenData['refreshExptime'];
			$this->setToken($this->token, $newTokenData, $ttl);
		}

		if(!empty($_G['_multi'])) {
			$output['multi'] = $_G['_multi'];
		}

		unset($_G['config'],
			$_G['setting']['siteuniqueid'],
			$_G['setting']['ec_tenpay_opentrans_chnid'],
			$_G['setting']['ec_tenpay_opentrans_key'],
			$_G['setting']['ec_tenpay_bargainor'],
			$_G['setting']['ec_tenpay_key'],
			$_G['setting']['ec_account'],
			$_G['setting']['ec_contract']);
	}

	private function _singleVar(&$var, $k) {
		return $var[$k] ?? null;
	}

	private function _arrayVar(&$var, $k) {
		unset($GLOBALS['_L']['_G']);
		$value = null;
		$sVar = &$var;
		$e = explode('/', $k);
		$count = count($e);
		foreach($e as $i => $_k) {
			if($_k == '*') {
				foreach($sVar as $_k3 => $_v3) {
					$nKey = implode('/', array_slice($e, $i + 1));
					$value[$_k3] = $this->_arrayVar($_v3, $nKey);
				}
				break;
			}
			$isMulti = str_contains($_k, ',');
			if(!isset($sVar[$_k]) && !$isMulti) {
				break;
			}
			if($isMulti) {
				$value = null;
				foreach(explode(',', $_k) as $_k2) {
					$value[$_k2] = $this->_singleVar($sVar, $_k2);
				}
				break;
			} else {
				if($count - 1 == $i) {
					$value = $this->_singleVar($sVar, $_k);
				}
				$sVar = &$sVar[$_k];
			}
		}
		return $value;
	}

	private function _memory($method, $params = []) {
		if($this->_m == null) {
			require_once ROOT_PATH.'./source/class/memory/memory_driver_redis.php';
			$_config = [];
			require ROOT_PATH.'./config/config_global.php';
			$this->_m = new memory_driver_redis();
			if(empty($_config['memory']['redis'])) {
				return null;
			}
			$this->_m->init($_config['memory']['redis']);
			if(!$this->_m->enable) {
				return null;
			}
			$this->_config = $_config;
		}
		if($this->_m == null) {
			return null;
		}
		if(!method_exists($this->_m, $method)) {
			return null;
		}
		return call_user_func_array([$this->_m, $method], $params);
	}

	// for Developer

	private function _getApiParam_Developer($api, $ver) {
		$xml = ROOT_PATH.'./data/discuz_restful.xml';
		require_once ROOT_PATH.'./source/class/class_xml.php';
		$data = file_get_contents($xml);
		$xmldata = xml2array($data);
		foreach($xmldata['Data']['api'] as $ver => $apis) {
			if(!preg_match('/^v(\d+)$/', $ver, $r)) {
				continue;
			}
			$this->_parseApis($apis, $ver, '/');
		}
		if(empty($_ENV['api'][$ver]['/'.$api[0]])) {
			$this->error(-109);
		}
		return $this->apiParam = $_ENV['api'][$ver]['/'.$api[0]];
	}

	private function _parseApis($apis, $ver, $uriPre = '/') {
		if(!is_array($apis)) {
			return;
		}
		foreach($apis as $uri => $api) {
			$k = $uriPre.$uri;
			list(, $baseuri) = explode('/', $k);
			$baseuri = '/'.$baseuri;
			if(!empty($api['script']) && is_string($api['script'])) {
				$_ENV['api'][$ver][$baseuri][$k] = [];
				$_ENV['api'][$ver][$baseuri][$k] = $api;
			} else {
				$this->_parseApis($api, $ver, $k.'/');
			}
		}
	}

	public function getAuthSign() {
		static $authSign = null;
		if($authSign !== null) {
			return $authSign;
		}
		global $_G;

		$this->_memory('set', [self::AuthPre.$_G['cookie']['sid'], serialize([
			'auth' => $_G['cookie']['auth'],
			'saltkey' => $_G['cookie']['saltkey'],
			'sid' => $_G['cookie']['sid']
		]), self::AuthTTL]);
		return $authSign = authcode($_G['cookie']['sid'], 'ENCODE', md5($_G['config']['security']['authkey']), self::AuthTTL);
	}

	public function decodeAuthSign(&$session) {
		$sid = authcode($this->postParam['_authSign'], 'DECODE', md5($this->_config['security']['authkey']));
		if(!$sid) {
			return;
		}
		$data = $this->_memory('get', [self::AuthPre.$sid]);
		if(!$data) {
			return;
		}
		$cookiepre = $this->_config['cookie']['cookiepre'].substr(md5($this->_config['cookie']['cookiepath'].'|'.$this->_config['cookie']['cookiedomain']), 0, 4).'_';
		$session[$cookiepre.'auth'] = $data['auth'];
		$session[$cookiepre.'saltkey'] = $data['saltkey'];
		$session[$cookiepre.'sid'] = $data['sid'];
	}

}