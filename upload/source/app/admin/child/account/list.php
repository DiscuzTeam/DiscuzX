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
	echo <<<'ACCSTYLE'
<style>
.acc-scroll { overflow-x: auto; }
.acc-scroll .tb { min-width: 1080px; margin-bottom: 0; }
.atable { border-collapse: separate; border-spacing: 0; }
.atable td { vertical-align: middle; }
.atable .header td { padding: 8px 10px; border: 0; background: var(--admincp-bgf7); color: var(--admincp-fb); font-weight: 400; }
.atable .t { width: 84px; text-align: center; }
.atable .c { text-align: center; }
.atable .l { border-left: 1px solid var(--admincp-borderb); }
.atable .acc-sticky { position: sticky; left: 0; z-index: 2; background: var(--admincp-bgf); }
.atable .header .acc-sticky { background: var(--admincp-bgf7); }
.atable .acc-name-cell { white-space: nowrap; }
.atable .iconfont, .acc-chip .iconfont { width: 1.5em; height: 1.5em; fill: currentColor; overflow: hidden; vertical-align: middle; margin-right: 5px; }
.atable tr.acc-none td { background: var(--admincp-bgf7); }
.atable tr.acc-closed-row td { padding: 12px 10px 14px; }
.acc-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.acc-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border: 1px dashed var(--admincp-borderd); border-radius: var(--admincp-radius-xl); background: var(--admincp-bgf); color: var(--admincp-fb); }
.acc-chip .iconfont { margin-right: 0; }
</style>
ACCSTYLE;

	showformheader('account');
	/*search={"account":"action=account"}*/
	echo '<div class="acc-scroll">';
	showtableheader('', 'atable');
	$cols1 = explode(',', cplang('account_basesetting_cols1'));
	$cols2 = explode(',', cplang('account_basesetting_cols2'));
	while(count($cols1) < 7) {
		$cols1[] = '';
	}
	while(count($cols2) < 12) {
		$cols2[] = '';
	}
	$header = showtablerow('class="header"', [
		'rowspan="2" class="acc-sticky" style="width:230px"',
		'colspan="2" class="l c"',
		'class="t"',
		'colspan="2" class="l c"',
		'colspan="3" class="l c"',
		'colspan="3" class="l c"',
		'class="l t"',
	], array_slice($cols1, 0, 7), true);
	$header .= showtablerow('class="header"', [
		'class="l t"',
		'class="t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="t"',
		'class="l t"',
	], array_slice($cols2, 0, 12), true);
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
			'class="acc-sticky acc-name-cell"',
			'class="l t"',
			'class="t"',
			'class="t"',
			'class="l t"',
			'class="t"',
			'class="l t"',
			'class="t"',
			'class="t"',
			'class="l t"',
			'class="t"',
			'class="t"',
			'class="l t"',
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
	showtablerow('class="acc-none"', [
		'class="acc-sticky acc-name-cell"',
		'class="l t"',
		'class="t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="t"',
		'class="l t"',
		'class="t"',
		'class="t"',
		'class="l t"',
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
	echo '</div>';
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
