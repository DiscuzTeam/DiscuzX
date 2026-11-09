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

$payment = new pay_qpay();

$data = $payment->qpay_sign_verify();
if($data && $data['code'] == 200) {
	$data = $data['data'];
	$out_biz_no = $data['out_trade_no'] ?? '';
	$payment_time = !empty($data['time_end']) ? strtotime(preg_replace('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', '$1-$2-$3 $4:$5:$6', $data['time_end'])) : TIMESTAMP;
	$is_success = $out_biz_no ? payment::finish_order_notify('qpay', $out_biz_no, $data['transaction_id'] ?? '', $payment_time, [
		'total_fee' => $data['total_fee'] ?? '',
	]) : false;
	if($is_success) {
		echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
		exit();
	}
} else {
	payment::notifylog('qpay', 0, $data['code'] ?? 50001, is_array($data['data'] ?? null) ? ($data['data']['out_trade_no'] ?? '') : '', [
		'detail' => is_array($data['data'] ?? null) ? $data['data'] : substr((string)($data['data'] ?? ''), 0, 500),
	]);
}

echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
exit();
