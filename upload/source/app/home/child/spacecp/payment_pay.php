<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$order_id = intval($_GET['order_id']);
if(!$order_id) {
	showmessage('payment_order_no_exist', '', [], ['showdialog' => true]);
}

$order = table_common_payment_order::t()->fetch($order_id);
if(!$order || $order['expire_time'] < time() || ($order['uid'] && $_G['uid'] != $order['uid'])) {
	showmessage('payment_order_no_exist', '', [], ['showdialog' => true]);
}
if($order['status']) {
	$return_url = $order['return_url'];
	if(!$return_url) {
		$return_url = $_G['siteurl'].'home.php?mod=spacecp&ac=payment';
	}
	$return_url .= (str_contains($return_url, '?') ? '&' : '?').'order_id='.$order_id;
	showmessage('payment_succeed', $return_url, [], ['alert' => 'right']);
}

if(submitcheck('paysubmit')) {
	$pay_channel = daddslashes($_GET['pay_channel']);
	$payclass = payment::get($pay_channel);
	if(!$payclass) {
		showmessage('payment_type_no_exist', $_G['siteurl'].'home.php?mod=spacecp&ac=payment&op=pay&order_id='.$order_id, [], ['showdialog' => true, 'locationtime' => 3]);
	}

	// QQ 钱包 JSAPI 支付
	if($pay_channel == 'qpay' && checkmobile() && str_contains($_SERVER['HTTP_USER_AGENT'], ' QQ') && str_contains($_SERVER['HTTP_USER_AGENT'], 'MQQBrowser')) {
		$ec_qpay = table_common_setting::t()->fetch_setting('ec_qpay', true);
		if($ec_qpay['jsapi']) {
			$result = $payclass->pay_jsapi($order);
			if($result['code'] == 200) {
				$prepay_id = $result['prepay_id'];
				$title = dhtmlspecialchars($order['subject']);
				include template('home/spacecp_payment_qpayjsapi');
				exit();
			}
		}
	}

	if($pay_channel == 'wechat' && checkmobile() && weixin::in_event()) {
		$redirect_uri = $_G['siteurl'].'home.php?mod=spacecp&ac=payment&op=pay&sop=wxjsapi&order_id='.$order_id;
		$state = md5($order_id.$order['dateline']);
		$pay_url = $payclass->wechat_authorize($redirect_uri, $state);
		dheader('Location: '.$pay_url);
	} else {
		$result = $payclass->pay($order);
		if($result['code'] != 200) {
			showmessage($result['message'], $_G['siteurl'].'home.php?mod=spacecp&ac=payment&op=pay&order_id='.$order_id, [], ['showdialog' => true, 'locationtime' => 3]);
		}
		$pay_url = $result['url'];

		include template('home/spacecp_payment_redirect');
	}
} elseif($_GET['sop'] == 'wxjsapi') {
	$code = daddslashes($_GET['code']);
	$state = daddslashes($_GET['state']);
	if(!$code || !$state || !$order_id || $state != md5($order_id.$order['dateline'])) {
		exit('Access Denied');
	}

	$payment = payment::get('wechat');
	$result = $payment->wechat_access_token_by_code($code);
	$result = json_decode($result, true);
	if(!$result['openid']) {
		if(strtoupper($_G['charset']) != 'UTF-8') {
			$result['errmsg'] = diconv($result['errmsg'], 'UTF-8', $_G['charset']);
		}
		showmessage($result['errmsg'], $order['return_url'], [], ['showdialog' => true, 'locationtime' => 3]);
	}

	$result = $payment->pay_jsapi($order, $result['openid']);
	if($result['code'] != 200) {
		showmessage($result['message'], $order['return_url'], [], ['showdialog' => true, 'locationtime' => 3]);
	}

	$jsapidata = $payment->wechat_jsapidata($result['url']);
	$title = dhtmlspecialchars($order['subject']);
	include template('home/spacecp_payment_wxjsapi');
} elseif($_GET['sop'] == 'status') {
	exit();
} else {
	$order['subject'] = dhtmlspecialchars($order['subject']);
	$order['description'] = dhtmlspecialchars($order['description']);
	if($order['amount_fee']) {
		$order['total_amount'] = number_format((intval($order['amount']) + intval($order['amount_fee'])) / 100, '2', '.', ',');
	}
	$order['amount'] = number_format($order['amount'] / 100, '2', '.', ',');

	$pay_channel_list = [];
	$channels = payment::channels();
	foreach($channels as $channel) {
		if($channel['enable']) {
			$pay_channel_list[] = $channel;
		}
	}

	include template('home/spacecp_payment_pay');
}

