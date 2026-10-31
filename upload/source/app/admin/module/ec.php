<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
if(!defined('APPTYPEID')) {
	define('APPTYPEID', 2);
}
$checktype = $_GET['checktype'];
cpheader();

// 幂等同步支付对账计划任务：老站点升级后打开本页即自动注册，无需执行 SQL
payment::cron_sync();

$operations[$operation] = true;

$navs = [
	['nav_ec_config', 'ec&operation=base', $operations['base']],
	['nav_ec_credit', 'ec&operation=credit', $operations['credit']],
	['nav_ec_qpay', 'ec&operation=qpay', $operations['qpay']],
	['nav_ec_wechat', 'ec&operation=wechat', $operations['wechat']],
	['nav_ec_alipay', 'ec&operation=alipay', $operations['alipay']],
];

$channels = payment::channels_setting();
foreach($channels as $channel) {
	[$plugin, $class] = explode(':', $channel['id']);
	if(!in_array($plugin, $_G['setting']['plugins']['available'])) {
		continue;
	}
	$class_name = $plugin.'\\admin\\payment_'.$class;
	if(!class_exists($class_name) || !method_exists($class_name, 'admincp')) {
		continue;
	}
	$c = new $class_name();
	$navs[] = [$c->name ?? $channel['id'], 'ec&operation=method&id='.$channel['id'], $operations['method'] && $_GET['id'] == $channel['id']];
}

$navs = array_merge($navs, [[[
	'menu' => 'nav_ec_orders_submenu', 'submenu' => [
		['nav_ec_orders', 'ec&operation=orders', $operations['orders']],
		['nav_ec_tradelog', 'ec&operation=tradelog', $operations['tradelog']],
		['nav_ec_inviteorders', 'ec&operation=inviteorders', $operations['inviteorders']],
		['nav_ec_paymentorders', 'ec&operation=paymentorders', $operations['paymentorders']],
		['nav_ec_transferorders', 'ec&operation=transferorders', $operations['transferorders']],
	]], $operations['orders'] || $operations['tradelog'] || $operations['inviteorders'] || $operations['paymentorders'] || $operations['transferorders']
]]);

shownav('extended', 'nav_ec');
showsubmenu('nav_ec', $navs);

$file = childfile('ec/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}

require_once $file;
