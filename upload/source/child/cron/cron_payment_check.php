<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

// 支付订单对账：向支付渠道主动查单，补偿丢失的异步通知
$setting = table_common_setting::t()->fetch_all_setting(['ec_wechat', 'ec_alipay', 'ec_qpay', 'payment_channels'], true);
$channels = [];
foreach(['wechat' => 'ec_wechat', 'alipay' => 'ec_alipay', 'qpay' => 'ec_qpay'] as $channel => $skey) {
	if(!empty($setting[$skey]['on'])) {
		$channels[] = $channel;
	}
}
if(!empty($setting['payment_channels'])) {
	foreach($setting['payment_channels'] as $channel => $val) {
		if(!empty($val['enable'])) {
			$channels[] = $val['id'];
		}
	}
}
$channels = array_values(array_unique(array_filter($channels)));
if(empty($channels)) {
	return;
}

$orders = DB::fetch_all('SELECT `id`, `out_biz_no`, `channel` FROM %t WHERE `status` = %d AND %i AND `dateline` > %d ORDER BY `id` DESC LIMIT 20', [
	'common_payment_order',
	0,
	DB::field('channel', $channels),
	TIMESTAMP - 86400 * 3
]);

foreach($orders as $order) {
	if(!$order['out_biz_no'] || !$order['channel']) {
		continue;
	}
	$result = payment::query_order($order['channel'], $order['id']);
	if(!empty($result['code']) && $result['code'] == 200) {
		runlog('payment', 'payment check: order '.$order['out_biz_no'].' finished by channel '.$order['channel']);
	}
}
