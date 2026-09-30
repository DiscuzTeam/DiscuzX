<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$account = $_G['setting']['account'];

if(!submitcheck('submit')) {
	$interfaceEnvs = [];
	foreach($interfaces as $interface) {
		$class = new (account_base::getClass($interface));
		$interfaceEnvs[$interface]['loginAuto'] = $class->interface_loginAuto;

		$interfaceEnvs[$interface]['avatarRegisterAuto'] = $interfaceEnvs[$interface]['avatarLoginAuto'] = $interfaceEnvs[$interface]['avatarBindAuto'] = !$class->interface_noAutoAvatar;
	}

	showtips('account_basesetting_tips');

	echo '<script type="text/javascript" src="static/js/iconfont.js"></script>';
	echo '<style>.atable {border-collapse: separate} .atable .header td{ padding: 2px 10px} .atable .header td {border:0}'.
		'.atable .t {width:80px;text-align:center} .atable .c {text-align:center} .atable .l { border-left:1px dotted #DEEFFB !important} .atable .r {border-right:1px dotted #DEEFFB !important;}'.
		'.iconfont { width: 1.5em; height: 1.5em; vertical-align: middle; fill: currentColor; overflow: hidden; margin-right: 5px;}</style>';
	showformheader('account');
	/*search={"account":"action=account"}*/
	showtableheader('', 'atable');
	$header = showtablerow('class="header"', [
		'rowspan="2" style="width:230px"',
		'colspan="2" class="l c r"',
		'class="t"',
		'colspan="3" class="l c"',
		'colspan="2" class="l c"',
		'colspan="3" class="l c"',
	], explode(',', cplang('account_basesetting_cols1')), true);
	$header .= showtablerow('class="header"', [
		'class="l t"',
		'class="t"',
		'class="l t"',
		'class="l t"',
		'class="t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="t"',
		'',
	], explode(',', cplang('account_basesetting_cols2')), true);
	echo str_replace('header hover', 'header', $header);
	echo '<tbody>';
	foreach($interfaces as $interface) {
		$disabled = $notice = '';

		if(!account_base::allow($interface)) {
			$notice = cplang('account_autoLogin_notAllow');
			$disabled = 'disabled ';
		}

		$icon = account_base::getIcon($interface);
		$value = [
			'iconId' => $icon[1],
			'loginAutoDefault' => !$disabled && !empty($account['loginAutoDefault']) && $account['loginAutoDefault'] == $interface ? 'checked ' : '',
			'loginAuto' => !$disabled && !empty($account['loginAuto']) && in_array($interface, $account['loginAuto']) ? 'checked ' : '',
			'loginLink' => !$disabled && !empty($account['loginLink']) && in_array($interface, $account['loginLink']) ? 'checked ' : '',
			'loginRedirectDefault' => !$disabled && !empty($account['loginRedirectDefault']) && $account['loginRedirectDefault'] == $interface ? 'checked ' : '',
			'loginRedirect' => !$disabled && !empty($account['loginRedirect']) && in_array($interface, $account['loginRedirect']) ? 'checked ' : '',
			'register' => !$disabled && !empty($account['register']) && in_array($interface, $account['register']) ? 'checked ' : '',
			'registerRedirectDefault' => !$disabled && !empty($account['registerRedirectDefault']) && $account['registerRedirectDefault'] == $interface ? 'checked ' : '',
			'registerRedirect' => !$disabled && !empty($account['registerRedirect']) && in_array($interface, $account['registerRedirect']) ? 'checked ' : '',
			'avatarRegisterAuto' => !$disabled && !empty($account['avatarRegisterAuto']) && in_array($interface, $account['avatarRegisterAuto']) ? 'checked ' : '',
			'avatarLoginAuto' => !$disabled && !empty($account['avatarLoginAuto']) && in_array($interface, $account['avatarLoginAuto']) ? 'checked ' : '',
			'avatarBindAuto' => !$disabled && !empty($account['avatarBindAuto']) && in_array($interface, $account['avatarBindAuto']) ? 'checked ' : '',
		];
		showtablerow('', [
			'',
			'class="l t"',
			'class="t r"',
			'class="t"',
			'class="l t"',
			'class="t"',
			'class="t"',
			'class="l t"',
			'class="t"',
			'class="l t"',
			'class="t"',
			'class="t"',
		], [
			$icon[0].
			account_base::getName($interface).$notice.' <a href="'.ADMINSCRIPT.'?action=account&method='.$interface.'" style="float: right" class="act">'.cplang('edit').'</a>',
			'<input '.$disabled.'name="account[loginAutoDefault]" value="'.$interface.'" type="radio" class="radio" '.$value['loginAutoDefault'].'/>',
			!empty($interfaceEnvs[$interface]['loginAuto']) ? '<input '.$disabled.'name="account[loginAuto][]" value="'.$interface.'" type="checkbox" class="checkbox" '.$value['loginAuto'].' />' : '<input disabled type="checkbox" class="checkbox" />',
			'<input '.$disabled.'name="account[loginLink][]" value="'.$interface.'" type="checkbox" class="checkbox" '.$value['loginLink'].'/>',
			'<input '.$disabled.'name="account[loginRedirectDefault]" value="'.$interface.'" type="radio" class="radio" '.$value['loginRedirectDefault'].'/>',
			!empty($interfaceEnvs[$interface]['loginAuto']) ? '<input '.$disabled.'name="account[loginRedirect][]" value="'.$interface.'" type="checkbox" class="checkbox" '.$value['loginRedirect'].'/>' : '<input disabled type="checkbox" class="checkbox" />',
			'<input '.$disabled.'name="account[register][]" value="'.$interface.'" type="checkbox" class="checkbox" '.$value['register'].'/>',
			'<input '.$disabled.'name="account[registerRedirectDefault]" value="'.$interface.'" type="radio" class="radio" '.$value['registerRedirectDefault'].'/>',
			!empty($interfaceEnvs[$interface]['loginAuto']) ? '<input '.$disabled.'name="account[registerRedirect][]" value="'.$interface.'" type="checkbox" class="checkbox" '.$value['registerRedirect'].'/>' : '<input disabled type="checkbox" class="checkbox" />',
			!empty($interfaceEnvs[$interface]['avatarRegisterAuto']) ? '<input name="account[avatarRegisterAuto][]" value="'.$interface.'" type="checkbox" class="checkbox" '.$value['avatarRegisterAuto'].'/>' : '<input disabled type="checkbox" class="checkbox" />',
			!empty($interfaceEnvs[$interface]['avatarLoginAuto']) ? '<input name="account[avatarLoginAuto][]" value="'.$interface.'" type="checkbox" class="checkbox" '.$value['avatarLoginAuto'].'/>' : '<input disabled type="checkbox" class="checkbox" />',
			!empty($interfaceEnvs[$interface]['avatarBindAuto']) ? '<input name="account[avatarBindAuto][]" value="'.$interface.'" type="checkbox" class="checkbox" '.$value['avatarBindAuto'].'/>' : '<input disabled type="checkbox" class="checkbox" />',
			'',
		]);
	}
	showtablerow('', [
		'',
		'class="l t"',
		'class="t r"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="t"',
	], [
		'<svg class="iconfont" aria-hidden="true"><use xlink:href="#icon-wushuju"></use></svg>'.cplang('none'),
		'<input name="account[loginAutoDefault]" value="" type="radio" class="radio" '.(empty($account['loginAutoDefault']) ? 'checked ' : '').'/>',
		'',
		'',
		'<input name="account[loginRedirectDefault]" value="" type="radio" class="radio" '.(empty($account['loginRedirectDefault']) ? 'checked ' : '').'/>',
		'',
		'',
		'<input name="account[registerRedirectDefault]" value="" type="radio" class="radio" '.(empty($account['registerRedirectDefault']) ? 'checked ' : '').'/>',
		'',
		'',
		'',
		'',
		'',
	]);
	showsubmit('submit');
	showtablefooter();
	/*search*/
	showformfooter();
} else {
	$settings = [
		'account' => $_GET['account'],
	];
	table_common_setting::t()->update_batch($settings);
	updatecache('setting');
	cpmsg('setting_update_succeed', 'action=account', 'succeed');
}
