<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

const IN_API = true;
const IN_RESTFUL = true;
const IN_MOBILE_API = true;
if(!empty($_POST['debug'])) {
	define('IN_RESTFUL_DEBUG', true);
}

require_once '../../source/class/class_restful.php';

$_body = '';
if(empty($_POST) && $_body = file_get_contents('php://input')) {
	$_POST = json_decode($_body, true);
}

$_ENV['restful'] = new restful($_POST);

[$api, $ver] = $_ENV['restful']->parseQuery();

if($api[0] == 'token') {
	$_COOKIE = [];

	require_once '../../source/class/class_core.php';

	$discuz = C::app();
	$discuz->init_cron = false;
	$discuz->init_session = false;
	$discuz->init();

	//检测 appid 的有效性
	$_ENV['restful']->checkSign();

	//生成 token
	if($_ENV['restful']->isRefreshToken()) {
		$tokenData = $_ENV['restful']->refreshTokenData();
	}
	if(!$tokenData) {
		$tokenData = $_ENV['restful']->newTokenData();
	}
	$token = strtoupper(random(16));
	$_ENV['restful']->setToken($token, $tokenData, $_ENV['restful']->newSaveTokenExp());
	if($_ENV['restful']->isRefreshToken()) {
		$_ENV['restful']->delTokenData();
	}
	$_ENV['restful']->output([
		'ret' => 0,
		'token' => $token,
		'expires_in' => $_ENV['restful']->newTokenExp(),
	]);
} elseif($api[0] == 'callback') {
	$_ENV['authtoken'] = $api[1];
	$_ENV['returntype'] = !empty($api[2]) ? $api[2] : '';
	require_once '../../source/class/class_core.php';

	$discuz = C::app();
	$discuz->init_cron = false;
	$discuz->init_session = false;
	$discuz->init();

	if(!$_G['uid']) {
		$authed = false;
	} else {
		$authed = $_ENV['restful']->setAuthToken($_ENV['authtoken'], [$_G['uid'], time()]);
	}
	if($authed) {
		require_once libfile('function/member');
		clearcookies();
	}
	if($_ENV['returntype'] == 'json') {
		if($authed) {
			$_ENV['restful']->error(0);
		} else {
			$_ENV['restful']->error(-119);
		}
	} elseif($_ENV['returntype'] == 'html') {
		$message = $authed ? lang('core', 'restful_auth_success') : lang('core', 'restful_auth_error');
		include template('common/header_common');
		include template('common/restful_auth');
	} elseif($_ENV['returntype'] == 'text') {
		if($authed) {
			echo lang('core', 'restful_auth_success');
		} else {
			echo lang('core', 'restful_auth_error');
		}
	} else {
		if($authed) {
			header('location: '.base64_decode($_ENV['returntype']));
			exit;
		} else {
			echo lang('core', 'restful_auth_error');
		}
	}
} elseif($api[0] == 'authtoken') {
	require_once '../../source/class/class_core.php';

	$discuz = C::app();
	$discuz->init_cron = false;
	$discuz->init_session = false;
	$discuz->init();

	$data = $_ENV['restful']->getAuthToken($_GET['authtoken']);
	if(!$data) {
		$_ENV['restful']->error(-119);
	} else {
		// token校验
		$_ENV['restful']->checkToken();

		require_once libfile('function/member');
		$member = getuserbyuid($data[0]);
		if(!$member) {
			$_ENV['restful']->error(-119);
		}
		setloginstatus($member, 2592000);
		$_ENV['restful']->convertOutput(['member/uid' => 'uid']);
	}
} elseif($api[0] == 'deltoken') {
	$_ENV['restful']->delTokenData();
	$_ENV['restful']->output([
		'ret' => 0,
		'token' => '',
		'data' => [
			'msg' => 'ok'
		]
	]);
} else {
	define('IN_RESTFUL_API', true);

	//检测 appid 的有效性
	$_ENV['restful']->checkSign();

	// token校验
	$_ENV['restful']->checkToken();

	// 初始化接口参数
	$_ENV['restful']->initParam($api, $ver);
	$_ENV['restful']->validate($_body);
	// 接口频率控制
	$_ENV['restful']->apiFreqCheck();
	// 接口权限校验
	$_ENV['restful']->apiPermCheck();
	// script校验
	$script = $_ENV['restful']->scriptCheck();
	// 释放 GPC
	$_GET = $_ENV['restful']->paramDecode('get');
	$_POST = $_ENV['restful']->paramDecode('post');
	$_COOKIE = $_ENV['restful']->sessionDecode();
	$requestParams = $_ENV['restful']->getRequestParam();
	if($requestParams) {
		if(!empty($requestParams['GET']) && is_array($requestParams['GET'])) {
			foreach($requestParams['GET'] as $k => $v) {
				$_GET[$k] = $v;
			}
		}
		if(!empty($_GET)) {
			$_SERVER['QUERY_STRING'] = http_build_query($_GET);
		}
		if(!empty($requestParams['POST']) && is_array($requestParams['POST'])) {
			foreach($requestParams['POST'] as $k => $v) {
				$_POST[$k] = $v;
			}
		}
		if(!empty($requestParams['COOKIE']) && is_array($requestParams['COOKIE'])) {
			foreach($requestParams['COOKIE'] as $k => $v) {
				$_COOKIE[$k] = $v;
			}
		}
	}
	foreach($_COOKIE as $k => $v) {
		!empty($v) && setcookie($k, $v);
	}

	if(empty($_ENV['restful']->tokenData['_conf']['seccheck'])) {
		define('DISABLE_SECCHECK', true);
	}

	if(!defined('IN_RESTFUL_DEBUG')) {
		// 准备输出
		[$shutdownFunc, $output] = $_ENV['restful']->getShutdownFunc();
		// 运行 script
		register_shutdown_function([$_ENV['restful'], $shutdownFunc], $output);
	}

	$empty = [];
	$_ENV['restful']->plugin('before', $empty);

	try {
		$_GET['app'] = $script;
		chdir('../../');
		require './index.php';
	} catch (Exception $e) {
		$_ENV['restful']->error(-100);
	}
}