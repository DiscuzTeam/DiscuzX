<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$qpaysettings = table_common_setting::t()->fetch_setting('ec_qpay', true);
if(!empty($checktype)) {
	if($checktype == 'credit') {
		$return_url = $_G['siteurl'].'home.php?mod=spacecp&ac=credit';
		$pay_url = payment::create_order('payment_credit', $lang['ec_alipay_checklink_credit'], $lang['ec_alipay_checklink_credit'], 1, $return_url);
		ob_end_clean();
		dheader('location: '.$pay_url);
	}
	exit;
}

if(!submitcheck('qpaysubmit')) {

	/*search={"nav_ec":"action=ec&operation=base","nav_ec_qpay":"action=ec&operation=qpay"}*/
	showtips('ec_qpay_tips');
	showformheader('ec&operation=qpay');

	showtableheader('', 'nobottom');
	showtitle('ec_qpay');
	showtagheader('tbody', 'alipay_wechat', true);
	showsetting('ec_qpay_on', 'settingsnew[on]', $qpaysettings['on'], 'radio');
	showsetting('ec_qpay_jsapi', 'settingsnew[jsapi]', $qpaysettings['jsapi'], 'radio');

	showsetting('ec_qpay_appid', 'settingsnew[appid]', $qpaysettings['appid'], 'text');
	showsetting('ec_qpay_mch_id', 'settingsnew[mch_id]', $qpaysettings['mch_id'], 'text');
	showsetting('ec_qpay_op_user_id', 'settingsnew[op_user_id]', $qpaysettings['op_user_id'], 'text');
	$qpay_securitycodemask = $qpaysettings['op_user_passwd'] ? $qpaysettings['op_user_passwd'][0].'********'.substr($qpaysettings['op_user_passwd'], -4) : '';
	showsetting('ec_qpay_op_user_passwd', 'settingsnew[op_user_passwd]', $qpay_securitycodemask, 'text');
	showtagfooter('tbody');

	showtagheader('tbody', 'api_version_2', true);
	$qpay_securitycodemask = $qpaysettings['v1_key'] ? $qpaysettings['v1_key'][0].'********'.substr($qpaysettings['v1_key'], -4) : '';
	showsetting('ec_qpay_v1_key', 'settingsnew[v1_key]', $qpay_securitycodemask, 'text');
	showsetting('ec_qpay_v1_cert', 'settingsnew[v1_cert_path]', $qpaysettings['v1_cert_path'], 'text', '', 0, lang('admincp', 'ec_qpay_v1_cert_comment', ['randomstr' => random(10)]));
	showtagfooter('tbody');

	showsetting('ec_qpay_check', '', '',
		'<a href="'.ADMINSCRIPT.'?action=ec&operation=qpay&checktype=credit" target="_blank">'.$lang['ec_qpay_checklink_credit'].'</a><br />'
	);
	/*search*/
	showtableheader('', 'notop');
	showsubmit('qpaysubmit');
	showtablefooter();
	showformfooter();

} else {
	$settingsnew = $_GET['settingsnew'];
	foreach($settingsnew as $name => $value) {
		if($value == $qpaysettings[$name] || str_contains($value, '********')) {
			continue;
		}
		$value = daddslashes($value);
		if($name == 'op_user_passwd') {
			$value = md5($value);
		}
		$qpaysettings[$name] = $value;
	}
	table_common_setting::t()->update_setting('ec_qpay', $qpaysettings);
	updatecache('setting');

	payment::cron_sync();

	cpmsg('qpay_succeed', 'action=ec&operation=qpay', 'succeed');
}
	