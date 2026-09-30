<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
global $_G;
if(empty($_G['setting']['wechat']['appId']) || empty($_G['setting']['wechat']['appSecret'])) {
	jsonExit(-99);
}

$ac = !empty($_GET['ac']) ? $_GET['ac'] : '';
if(!empty($ac) && !in_array($ac, ['confirm']) && $_GET['formhash'] != FORMHASH) {
	if(in_array($ac, ['confirm_ok'])) {
		showmessage('illegal_operation');
	} else {
		jsonMsg('formhash error');
	}
}

switch($ac) {
	case 'qrcode':
		$authcode = !empty($_GET['authcode']) ? $_GET['authcode'] : '';
		if(!empty($ac) && empty($authcode)) {
			jsonMsg('authcode error');
		}
		require_once libfile('account/wechat', 'class');
		$wechat = new account_wechat();
		$wechat_login_url = $wechat->getWeChatUrl($authcode);
		require_once libfile('class/qrcode');
		$value = urldecode($wechat_login_url);
		$Level = 'L';
		$Size = 9;
		echo QRcode::png($value, false, $Level, $Size);
		break;
	case 'check':
		$authcode = !empty($_GET['authcode']) ? $_GET['authcode'] : '';
		if(!empty($ac) && empty($authcode)) {
			jsonMsg('authcode error');
		}
		$echostr = '0';
		$authcode = authcode(base64_decode(urldecode($authcode)), 'DECODE', $_G['config']['security']['authkey']);
		if($authcode) {
			$checkstutas = memory('get', 'wechat_code_'.$authcode);
			if($checkstutas['status'] == 1) {
				$echostr = '1';
				require_once libfile('function/member');
				$member = getuserbyuid($checkstutas['uid'], 1);
				setloginstatus($member, 1296000);
				memory('rm', 'wechat_code_'.$authcode);
			}
		}
		include template('common/header_ajax');
		echo $echostr;
		include template('common/footer_ajax');
		break;
	case 'confirm':
		$authcode = !empty($_GET['authcode']) ? $_GET['authcode'] : '';
		if(!empty($authcode) && defined('IN_MOBILE')) {
			include template('wechat/confirm');
		} else {
			showmessage('illegal_operation', $_G['siteurl']);
		}
		break;
	case 'confirm_ok':
		$authcode = !empty($_GET['authcode']) ? $_GET['authcode'] : '';
		$authcode_decode = authcode(base64_decode(urldecode($authcode)), 'DECODE', $_G['config']['security']['authkey']);
		list($code, $uid) = explode('_', $authcode_decode);
		$pc_login_data = memory('get', 'wechat_code_'.$code);
		if($pc_login_data && ($pc_login_data['uid'] <= 0 || $pc_login_data['uid'] == $_G['uid']) && intval($uid) > 0 && defined('IN_MOBILE')) {
			$data = [
				'uid' => intval($uid),
				'code' => $code,
				'status' => 1,
			];
			memory('set', 'wechat_code_'.$code, $data, 300);
			dheader('location:'.$_G['siteurl']);
		} else {
			showmessage('illegal_operation', $_G['siteurl']);
		}
		break;
	case 'getauthcode':
		require_once libfile('account/wechat', 'class');
		$wechat = new account_wechat();
		list($authcode, $code) = $wechat->getAuthCode();
		jsonMsg(['authcode' => $authcode]);
		break;
	case 'snapshotuser':
		include template('wechat/snapshotuser');
		break;
	default:
		if($_G['uid']) {
			$account = new account();
			$user = $account->getUser($_G['uid'], account::aType_wechatOpenid);
			if($user) {
				showmessage('account_bind_exists');
			}
		}
		require_once libfile('account/wechat', 'class');
		$wechat = new account_wechat();

		dsetcookie('wechat_referer', dreferer(), 86400);
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if(!weixin::in_event()) {
			[$authcode, $code] = $wechat->getAuthCode();
			include template('wechat/wechat');
		} else {
			$wechat->login();
		}
		break;
}
