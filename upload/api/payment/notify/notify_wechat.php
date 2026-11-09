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

$payment = new pay_wechat();

if(!empty($_SERVER['HTTP_WECHATPAY_SIGNATURE'])) {

	// APIv3
	$result = $payment->v3_wechat_sign_verify();
	if($result['code'] != 200) {
		// 50000 参数不完整 / 50001 验签失败（含序列号未匹配到公钥）/ 50006 解密失败 / 50008 报文格式错误
		payment::notifylog('wechat', 0, $result['code'], '', [
			'api' => 'v3',
			'serial' => $result['serial'] ?? '',
			'certs' => $result['certs'] ?? '',
			'bodylen' => strlen((string)($result['data'] ?? '')),
			'body' => $result['code'] == 50008 ? substr((string)($result['data'] ?? ''), 0, 300) : '',
		]);
		exit('{"code":"fail","message":"fail"}');
	}
	if(!empty($result['ignored'])) {
		payment::notifylog('wechat', 0, 50007, '', ['api' => 'v3', 'event_type' => $result['event_type']]);
		exit('{"code":"SUCCESS","message":"ok"}');
	}
	$data = json_decode($result['data'], true);
	if(!is_array($data)) {
		payment::notifylog('wechat', 0, 50006, '', ['api' => 'v3']);
		exit('{"code":"fail","message":"fail"}');
	}
	if(($data['trade_state'] ?? '') != 'SUCCESS') {
		payment::notifylog('wechat', 0, 50007, $data['out_trade_no'] ?? '', ['api' => 'v3', 'trade_state' => $data['trade_state'] ?? '']);
		exit('{"code":"fail","message":"fail"}');
	}
	$out_biz_no = $data['out_trade_no'] ?? '';
	$payment_time = !empty($data['success_time']) ? strtotime($data['success_time']) : TIMESTAMP;
	$is_success = $out_biz_no ? payment::finish_order_notify('wechat', $out_biz_no, $data['transaction_id'] ?? '', $payment_time, [
		'api' => 'v3',
		'amount' => $data['amount']['total'] ?? '',
	]) : false;
	if($is_success) {
		exit('{"code":"SUCCESS","message":"ok"}');
	}
	exit('{"code":"fail","message":"fail"}');

} else {

	// APIv2
	$result = $payment->wechat_sign_verify();
	if($result && $result['code'] == 200) {
		$data = $result['data'];
		$out_biz_no = $data['out_trade_no'] ?? '';
		$payment_time = !empty($data['time_end']) ? strtotime(preg_replace('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', '$1-$2-$3 $4:$5:$6', $data['time_end'])) : TIMESTAMP;
		$is_success = $out_biz_no ? payment::finish_order_notify('wechat', $out_biz_no, $data['transaction_id'] ?? '', $payment_time, [
			'api' => 'v2',
			'total_fee' => $data['total_fee'] ?? '',
		]) : false;
		if($is_success) {
			echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
			exit();
		}
	} else {
		// 50000 参数不完整 / 50001 验签失败 / 50007 业务结果非 SUCCESS / 50008 返回码非 SUCCESS
		$logdata = $result['data'] ?? [];
		payment::notifylog('wechat', 0, $result['code'] ?? 50000, is_array($logdata) ? ($logdata['out_trade_no'] ?? '') : '', [
			'api' => 'v2',
			'detail' => is_array($logdata) ? $logdata : substr((string)$logdata, 0, 500),
		]);
	}
	echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
	exit();

}
