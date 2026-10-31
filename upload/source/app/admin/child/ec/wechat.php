<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$wechatsettings = table_common_setting::t()->fetch_setting('ec_wechat', true);
if(!empty($checktype)) {
	if($checktype == 'credit') {
		$return_url = $_G['siteurl'].'home.php?mod=spacecp&ac=credit';
		$pay_url = payment::create_order('payment_credit', $lang['ec_alipay_checklink_credit'], $lang['ec_alipay_checklink_credit'], 1, $return_url);
		ob_end_clean();
		dheader('location: '.$pay_url);
	}
	exit;
}

if(!submitcheck('wechatsubmit')) {

	/*search={"nav_ec":"action=ec&operation=base","nav_ec_wechat":"action=ec&operation=wechat"}*/
	showtips('ec_wechat_tips');
	showformheader('ec&operation=wechat');

	showtableheader('', 'nobottom');
	showtitle('ec_wechat');
	showtagheader('tbody', 'alipay_wechat', true);
	showsetting('ec_wechat_on', 'settingsnew[on]', $wechatsettings['on'], 'radio');

	$wxpayment = payment::get('wechat');
	$check = [];
	$wechatsettings['ec_wechat_version'] ? $check['true'] = 'checked' : $check['false'] = 'checked';
	$wechatsettings['ec_wechat_version'] ? $check['false'] = '' : $check['true'] = '';
	$check['hidden1'] = ' onclick="$(\'api_version_2\').style.display = \'none\';$(\'api_version_3\').style.display = \'\';"';
	$check['hidden0'] = ' onclick="$(\'api_version_2\').style.display = \'\';$(\'api_version_3\').style.display = \'none\';"';
	$html = '<ul onmouseover="altStyle(this);"><li'.($check['false'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingsnew[ec_wechat_version]" value="0" '.$check['false'].$check['hidden0'].'>&nbsp;'.$lang['ec_wechat_version_2'].'</li>';
	if($wxpayment->v3_wechat_support()) {
		$html .= '<li'.($check['true'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingsnew[ec_wechat_version]" value="1" '.$check['true'].$check['hidden1'].'>&nbsp;'.$lang['ec_wechat_version_3'].'</li>';
	} else {
		$html .= '<li style="margin-left: 5px; color: red;">'.$lang['ec_wechat_version_3'].'('.$lang['ec_wechat_php_version_low'].')</li>';
	}
	$html .= '</ul>';
	showsetting('ec_wechat_version', '', '', $html);
	showsetting('ec_wechat_appid', 'settingsnew[appid]', $wechatsettings['appid'], 'text');
	$wechat_securitycodemask = $wechatsettings['appsecret'] ? $wechatsettings['appsecret'][0].'********'.substr($wechatsettings['appsecret'], -4) : '';
	showsetting('ec_wechat_appsecret', 'settingsnew[appsecret]', $wechat_securitycodemask, 'text');
	showsetting('ec_wechat_mch_id', 'settingsnew[mch_id]', $wechatsettings['mch_id'], 'text');
	showtagfooter('tbody');

	showtagheader('tbody', 'api_version_2', !$wechatsettings['ec_wechat_version']);
	$wechat_securitycodemask = $wechatsettings['v1_key'] ? $wechatsettings['v1_key'][0].'********'.substr($wechatsettings['v1_key'], -4) : '';
	showsetting('ec_wechat_v1_key', 'settingsnew[v1_key]', $wechat_securitycodemask, 'text');
	showsetting('ec_wechat_v1_cert', 'settingsnew[v1_cert_path]', $wechatsettings['v1_cert_path'], 'text', '', 0, lang('admincp', 'ec_wechat_v1_cert_comment', ['randomstr' => random(10)]));
	showtagfooter('tbody');

	showtagheader('tbody', 'api_version_3', $wechatsettings['ec_wechat_version']);
	$wechat_securitycodemask = $wechatsettings['v3_key'] ? $wechatsettings['v3_key'][0].'********'.substr($wechatsettings['v3_key'], -4) : '';
	showsetting('ec_wechat_v3_key', 'settingsnew[v3_key]', $wechat_securitycodemask, 'text');
	$wechat_securitycodemask = $wechatsettings['v3_private_key'] ? substr($wechatsettings['v3_private_key'], 0, 40).'********'.substr($wechatsettings['v3_private_key'], -40) : '';
	showsetting('ec_wechat_v3_private_key', 'settingsnew[v3_private_key]', $wechat_securitycodemask, 'textarea');
	$wechat_securitycodemask = $wechatsettings['v3_serial_no'] ? $wechatsettings['v3_serial_no'][0].'********'.substr($wechatsettings['v3_serial_no'], -4) : '';
	showsetting('ec_wechat_v3_serial_no', 'settingsnew[v3_serial_no]', $wechat_securitycodemask, 'text');
	$wechat_securitycodemask = $wechatsettings['v3_public_key_id'] ? $wechatsettings['v3_public_key_id'][0].'********'.substr($wechatsettings['v3_public_key_id'], -4) : '';
	showsetting('ec_wechat_v3_public_key_id', 'settingsnew[v3_public_key_id]', $wechat_securitycodemask, 'text');
	$wechat_securitycodemask = $wechatsettings['v3_public_key'] ? substr($wechatsettings['v3_public_key'], 0, 40).'********'.substr($wechatsettings['v3_public_key'], -40) : '';
	showsetting('ec_wechat_v3_public_key', 'settingsnew[v3_public_key]', $wechat_securitycodemask, 'textarea');
	showtagfooter('tbody');

	showsetting('ec_wechat_check', '', '',
		'<a href="'.ADMINSCRIPT.'?action=ec&operation=wechat&checktype=credit" target="_blank">'.$lang['ec_wechat_checklink_credit'].'</a><br />'
	);
	/*search*/
	showtableheader('', 'notop');
	showsubmit('wechatsubmit');
	showtablefooter();
	showformfooter();

} else {
	$settingsnew = $_GET['settingsnew'];
	foreach($settingsnew as $name => $value) {
		if($value == $wechatsettings[$name] || str_contains($value, '********')) {
			continue;
		}
		$value = daddslashes($value);
		$wechatsettings[$name] = $value;
	}
	table_common_setting::t()->update_setting('ec_wechat', $wechatsettings);
	updatecache('setting');

	if($wechatsettings['ec_wechat_version'] && $wechatsettings['appid'] && $wechatsettings['mch_id'] && $wechatsettings['v3_key'] && $wechatsettings['v3_private_key'] && $wechatsettings['v3_serial_no']) {
		$payment = payment::get('wechat');
		$result = $payment->v3_wechat_certificates();
		// 记录本次刷新时间，避免回调侧在短时间内重复请求平台证书
		$wechatsettings['v3_certificates_time'] = TIMESTAMP;
		if($result['code'] == 200 && !empty($result['data'])) {
			$wechatsettings['v3_certificates'] = $result['data'];
		}
		table_common_setting::t()->update_setting('ec_wechat', $wechatsettings);
		updatecache('setting');
	}

	payment::cron_sync();

	cpmsg('wechat_succeed', 'action=ec&operation=wechat', 'succeed');
}
	