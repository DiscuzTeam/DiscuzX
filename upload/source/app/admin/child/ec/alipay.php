<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$alipaysettings = table_common_setting::t()->fetch_setting('ec_alipay', true);

if(!empty($checktype)) {
	if($checktype == 'credit') {
		$return_url = $_G['siteurl'].'home.php?mod=spacecp&ac=credit';
		$pay_url = payment::create_order('payment_credit', $lang['ec_alipay_checklink_credit'], $lang['ec_alipay_checklink_credit'], 1, $return_url);
		ob_end_clean();
		dheader('location: '.$pay_url);
	}
	exit;
}

if(!submitcheck('alipaysubmit')) {

	/*search={"nav_ec":"action=ec&operation=base","nav_ec_alipay":"action=ec&operation=alipay"}*/
	showtips('ec_alipay_tips');
	showformheader('ec&operation=alipay');

	showtableheader('', 'nobottom');
	showtitle('ec_alipay');

	showtagheader('tbody', 'alipay_setting', true);
	showsetting('ec_alipay_on', 'settingsnew[on]', $alipaysettings['on'], 'radio');
	$check = [];
	$alipaysettings['ec_alipay_sign_mode'] ? $check['true'] = 'checked' : $check['false'] = 'checked';
	$alipaysettings['ec_alipay_sign_mode'] ? $check['false'] = '' : $check['true'] = '';
	$check['hidden1'] = ' onclick="$(\'sign_model_01\').style.display = \'none\';$(\'sign_model_02\').style.display = \'\';"';
	$check['hidden0'] = ' onclick="$(\'sign_model_01\').style.display = \'\';$(\'sign_model_02\').style.display = \'none\';"';
	$html = '<ul onmouseover="altStyle(this);">'.
		'<li'.($check['false'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingsnew[ec_alipay_sign_mode]" value="0" '.$check['false'].$check['hidden0'].'>&nbsp;'.lang('admincp', 'ec_alipay_sign_mode_01').'</li>'.
		'<li'.($check['true'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingsnew[ec_alipay_sign_mode]" value="1" '.$check['true'].$check['hidden1'].'>&nbsp;'.lang('admincp', 'ec_alipay_sign_mode_02').'</li>'.
		'</ul>';
	showsetting('ec_alipay_sign_mode', '', '', $html);
	showtagfooter('tbody');

	showtagheader('tbody', 'sign_model_01', !$alipaysettings['ec_alipay_sign_mode']);
	showsetting('ec_alipay_appid', 'settingsnew[mode_a_appid]', $alipaysettings['mode_a_appid'], 'text');
	$alipay_securitycodemask = $alipaysettings['mode_a_app_private_key'] ? substr($alipaysettings['mode_a_app_private_key'], 0, 40).'********'.substr($alipaysettings['mode_a_app_private_key'], -40) : '';
	showsetting('ec_alipay_app_private_key', 'settingsnew[mode_a_app_private_key]', $alipay_securitycodemask, 'textarea');
	$alipay_securitycodemask = $alipaysettings['mode_a_alipay_public_key'] ? substr($alipaysettings['mode_a_alipay_public_key'], 0, 40).'********'.substr($alipaysettings['mode_a_alipay_public_key'], -40) : '';
	showsetting('ec_alipay_public_key', 'settingsnew[mode_a_alipay_public_key]', $alipay_securitycodemask, 'textarea');
	showtagfooter('tbody');

	showtagheader('tbody', 'sign_model_02', $alipaysettings['ec_alipay_sign_mode']);
	showsetting('ec_alipay_appid', 'settingsnew[mode_b_appid]', $alipaysettings['mode_b_appid'], 'text');
	$alipay_securitycodemask = $alipaysettings['mode_b_app_private_key'] ? $alipaysettings['mode_b_app_private_key'][0].'********'.substr($alipaysettings['mode_b_app_private_key'], -4) : '';
	showsetting('ec_alipay_app_private_key', 'settingsnew[mode_b_app_private_key]', $alipay_securitycodemask, 'textarea', '', 0, lang('admincp', 'ec_alipay_app_private_key_b_comment'));
	$alipay_securitycodemask = $alipaysettings['mode_b_app_cert'] ? substr($alipaysettings['mode_b_app_cert'], 0, 40).'********'.substr($alipaysettings['mode_b_app_cert'], -40) : '';
	showsetting('ec_alipay_app_cert', 'settingsnew[mode_b_app_cert]', $alipay_securitycodemask, 'textarea');
	$alipay_securitycodemask = $alipaysettings['mode_b_alipay_cert'] ? substr($alipaysettings['mode_b_alipay_cert'], 0, 40).'********'.substr($alipaysettings['mode_b_alipay_cert'], -40) : '';
	showsetting('ec_alipay_alipay_cert', 'settingsnew[mode_b_alipay_cert]', $alipay_securitycodemask, 'textarea');
	$alipay_securitycodemask = $alipaysettings['mode_b_alipay_root_cert'] ? substr($alipaysettings['mode_b_alipay_root_cert'], 0, 40).'********'.substr($alipaysettings['mode_b_alipay_root_cert'], -40) : '';
	showsetting('ec_alipay_alipay_root_cert', 'settingsnew[mode_b_alipay_root_cert]', $alipay_securitycodemask, 'textarea');
	showtagfooter('tbody');

	showsetting('ec_alipay_check', '', '',
		'<a href="'.ADMINSCRIPT.'?action=ec&operation=alipay&checktype=credit" target="_blank">'.$lang['ec_alipay_checklink_credit'].'</a><br />'
	);
	/*search*/
	showtableheader('', 'notop');
	showsubmit('alipaysubmit');
	showtablefooter();
	showformfooter();

} else {
	$settingsnew = $_GET['settingsnew'];
	foreach($settingsnew as $name => $value) {
		if($value == $alipaysettings[$name] || str_contains($value, '********')) {
			continue;
		}
		$value = daddslashes($value);
		$alipaysettings[$name] = $value;
	}
	table_common_setting::t()->update_setting('ec_alipay', $alipaysettings);
	updatecache('setting');

	payment::cron_sync();

	cpmsg('alipay_succeed', 'action=ec&operation=alipay', 'succeed');
}
	