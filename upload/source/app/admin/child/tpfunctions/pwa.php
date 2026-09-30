<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(submitcheck('settingsubmit')) {

	$settingnew = $_GET['settingnew'];

	$raw = (array)getgpc('settingnew');
	$pwa = (array)($raw['pwa'] ?? $settingnew['pwa'] ?? []);

	$settingnew['pwa'] = [
		'allow' => !empty($pwa['allow']) ? 1 : 0,
		'name' => trim($pwa['name'] ?? ''),
		'short_name' => trim($pwa['short_name'] ?? ''),
		'description' => trim($pwa['description'] ?? ''),
		'background_color' => trim($pwa['background_color'] ?? ''),
		'theme_color' => trim($pwa['theme_color'] ?? ''),
	];

	if(!empty($_FILES)) {
		$img = new image();
		foreach([512, 192, 180, 32] as $size) {
			if(empty($_FILES['logo_'.$size]['tmp_name'])) {
				continue;
			}
			$v = getimagesize($_FILES['logo_'.$size]['tmp_name']);
			if($v === false) {
				cpmsg('setting_pwa_logo_error');
			}
			if($v[0] != $v[1] || $v[0] != $size || $v['mime'] != 'image/png') {
				cpmsg('setting_pwa_logo_error');
			}
			admin\class_attach::upload($_FILES['logo_'.$size], 'pwa', '', 0, $size);
			if(!file_exists(DISCUZ_DATA.'./attachment/pwa/'.$size.'.png')) {
				cpmsg('setting_pwa_upload_error');
			}
			if($_G['setting']['ftp']['on'] == 2) {
				ftpcmd('upload', 'pwa/'.$size.'.png');
			}
		}
	}

	if($settingnew['pwa']['allow'] != $_G['setting']['pwa']['allow']) {
		$hookData = [
			['hookid' => 'global_meta', 'method' => ['pwa', 'header'], 'type' => 1, 'modid' => 'global::global', 'hookType' => 1],
			['hookid' => 'global_meta_mobile', 'method' => ['pwa', 'header'], 'type' => 1, 'modid' => 'global::global', 'hookType' => 2],
		];
		if($settingnew['pwa']['allow']) {
			hook::register($hookData, false);
		} else {
			hook::unregister($hookData, false);
		}
	}

	require_once childfile('setting/updatecache');

	cpmsg('setting_update_succeed', 'action=tpfunctions&operation=pwa', 'succeed');

} else {

	$pwa = dunserialize($_G['setting']['pwa'] ?? []);

	showtips('setting_pwa_tips');

	showformheader('tpfunctions&operation=pwa', 'enctype');
	showhiddenfields(['operation' => $operation]);

	/*search={"setting_pwa":"action=tpfunctions&operation=pwa"}*/
	showtableheader('setting_pwa_basic', 'nobottom');
	showsetting('setting_pwa_enable', 'settingnew[pwa][allow]', $pwa['allow'] ?? 0, 'radio');
	showsetting('setting_pwa_name', 'settingnew[pwa][name]', $pwa['name'] ?? '', 'text');
	showsetting('setting_pwa_short_name', 'settingnew[pwa][short_name]', $pwa['short_name'] ?? '', 'text');
	showsetting('setting_pwa_description', 'settingnew[pwa][description]', $pwa['description'] ?? '', 'text');
	showsetting('setting_pwa_background_color', 'settingnew[pwa][background_color]', $pwa['background_color'] ?? '#FFFFFF', 'color');
	showsetting('setting_pwa_theme_color', 'settingnew[pwa][theme_color]', $pwa['theme_color'] ?? '#2B7ACD', 'color');
	foreach([512, 192, 180, 32] as $size) {
		$url = $_G['setting']['attachurl'].'pwa/'.$size.'.png';
		$s = $size > 100 ? 100 : $size;
		$img = '<br /><a href="'.$url.'" target="_blank"><img src="'.$url.'" style="width: '.$s.'px;" onerror="this.src=\''.STATICURL.'image/common/none.gif\';this.style.width=0;"></a>';
		showsetting('setting_pwa_logo_'.$size, 'logo_'.$size, '', 'file', comment: cplang('setting_pwa_logo_'.$size.'_comment').$img);
	}

	showtablefooter();
	/*search*/

	showsubmit('settingsubmit');
	showtablefooter();
	showformfooter();

}
