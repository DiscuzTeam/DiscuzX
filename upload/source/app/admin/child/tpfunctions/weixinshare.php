<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$mask = '**********';

if(submitcheck('settingsubmit')) {

	$settingnew = $_GET['settingnew'];

	// 兼容 GET / POST 两种表单提交方式（getgpc 会依次尝试）
	$raw = (array)getgpc('settingnew');
	$wx = (array)($raw['wxshare'] ?? $settingnew['wxshare'] ?? []);

	// AppSecret 掩码处理：若提交内容包含掩码，说明未修改，保留原值
	if(!empty($wx['appsecret']) && str_contains($wx['appsecret'], $mask)) {
		$wx['appsecret'] = $_G['setting']['wxshare']['appsecret'] ?? '';
	}

	$settingnew['wxshare'] = [
		'enable' => !empty($wx['enable']) ? 1 : 0,
		'appid' => trim($wx['appid'] ?? ''),
		'appsecret' => trim($wx['appsecret'] ?? ''),
		'title' => trim($wx['title'] ?? ''),
		'desc' => trim($wx['desc'] ?? ''),
		'img' => trim($wx['img'] ?? ''),
		'link' => trim($wx['link'] ?? ''),
		'debug' => !empty($wx['debug']) ? 1 : 0,
	];

	if($settingnew['wxshare']['enable'] != $_G['setting']['wxshare']['enable']) {
		$hookData = [
			['hookid' => 'global_footer_mobile', 'method' => ['weixin', 'js_share'], 'type' => 1, 'modid' => 'global::global', 'hookType' => 2],
		];
		if($settingnew['wxshare']['enable']) {
			hook::register($hookData, false);
		} else {
			hook::unregister($hookData, false);
		}
	}

	require_once childfile('setting/updatecache');

	cpmsg('setting_update_succeed', 'action=tpfunctions&operation=weixinshare', 'succeed');

} else {

	$wx = dunserialize($_G['setting']['wxshare'] ?? []);

	showtips('setting_weixinshare_tips');

	showformheader('tpfunctions&operation=weixinshare', 'enctype');
	showhiddenfields(['operation' => $operation]);

	/*search={"setting_weixinshare":"action=tpfunctions&operation=weixinshare"}*/
	showtableheader('setting_weixinshare_basic', 'nobottom');
	showsetting('setting_weixinshare_enable', 'settingnew[wxshare][enable]', $wx['enable'] ?? 0, 'radio');
	showsetting('setting_weixinshare_appid', 'settingnew[wxshare][appid]', $wx['appid'] ?? '', 'text');
	$maskedSecret = !empty($wx['appsecret']) ? substr($wx['appsecret'], 0, 6).$mask.substr($wx['appsecret'], -2) : '';
	showsetting('setting_weixinshare_appsecret', 'settingnew[wxshare][appsecret]', $maskedSecret, 'text');
	showsetting('setting_weixinshare_title', 'settingnew[wxshare][title]', $wx['title'] ?? '', 'text');
	showsetting('setting_weixinshare_desc', 'settingnew[wxshare][desc]', $wx['desc'] ?? '', 'textarea');
	showsetting('setting_weixinshare_img', 'settingnew[wxshare][img]', $wx['img'] ?? '', 'text');
	showsetting('setting_weixinshare_link', 'settingnew[wxshare][link]', $wx['link'] ?? '', 'text');
	showsetting('setting_weixinshare_debug', 'settingnew[wxshare][debug]', $wx['debug'] ?? 0, 'radio');
	showtablefooter();
	/*search*/

	showsubmit('settingsubmit');
	showtablefooter();
	showformfooter();

}
