<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

const IN_API = true;
const CURSCRIPT = 'api';
const DISABLEXSSCHECK = true;
// 第三方服务器异步通知：不做 IP 白名单/禁止访问时段/计划任务等面向访客的检查
const DISABLEDEFENSE = true;

require '../../../source/class/class_core.php';

$discuz = C::app();
$discuz->init();

if(empty($_POST['sign']) || empty($_POST['sign_type'])) {
	payment::notifylog('alipay', 0, 50000, $_POST['out_trade_no'] ?? '');
	exit('fail');
}
$sign = $_POST['sign'];
unset($_POST['sign']);

$payment = new pay_alipay();
$isright = $payment->alipay_sign_verify($sign, $_POST);
if(!$isright) {
	$_POST['sign'] = $sign;
	payment::notifylog('alipay', 0, 50001, $_POST['out_trade_no'] ?? '', ['detail' => $_POST]);
	exit('fail');
}

if($_POST['trade_status'] == 'TRADE_SUCCESS') {
	$out_biz_no = $_POST['out_trade_no'];
	$payment_time = strtotime($_POST['gmt_payment']);

	$is_success = payment::finish_order_notify('alipay', $out_biz_no, $_POST['trade_no'], $payment_time, [
		'trade_status' => $_POST['trade_status'],
		'total_amount' => $_POST['total_amount'] ?? '',
	]);
	if($is_success) {
		exit('success');
	}
} else {
	payment::notifylog('alipay', 0, 50007, $_POST['out_trade_no'] ?? '', [
		'trade_status' => $_POST['trade_status'] ?? '',
		'total_amount' => $_POST['total_amount'] ?? '',
	]);
}

exit('fail');
