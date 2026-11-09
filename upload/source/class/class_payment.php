<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class payment {

	public static function enable() {

		$channels = table_common_setting::t()->fetch_all_setting(['ec_wechat', 'ec_alipay', 'ec_qpay', 'payment_channels'], true);
		if($channels['ec_alipay']['on']) {
			return true;
		}
		if($channels['ec_wechat']['on']) {
			return true;
		}
		if($channels['ec_qpay']['on']) {
			return true;
		}
		if(!empty($channels['payment_channels'])) {
			foreach($channels['payment_channels'] as $channel => $val) {
				if(!empty($val['enable'])) {
					return true;
				}
			}
		}
		return false;
	}

	public static function channels_add($channel, $param) {
		$channels = self::channels_setting();
		$param['enable'] = 1;
		$channels[$channel] = $param;
		table_common_setting::t()->update_setting('payment_channels', $channels);
		self::cron_sync();
	}

	public static function channels_switch($channel, $boolean) {
		$channels = self::channels_setting();
		$channels[$channel]['enable'] = $boolean;
		table_common_setting::t()->update_setting('payment_channels', $channels);
		self::cron_sync();
	}

	public static function channels_delete($channel) {
		$channels = self::channels_setting();
		unset($channels[$channel]);
		table_common_setting::t()->update_setting('payment_channels', $channels);
		self::cron_sync();
	}

	/**
	 * 支付对账计划任务与渠道开关的幂等同步
	 *
	 * 有任意支付渠道开通：写入（或重新启用）cron_payment_check.php，每 10 分钟执行一次
	 * 全部渠道关闭：停用该计划任务（保留记录，便于再次开通时秒级生效）
	 * 可重复调用，不会产生重复记录，供后台电子商务设置与渠道开关共用
	 */
	public static function cron_sync() {
		$filename = 'cron_payment_check.php';
		$cronid = table_common_cron::t()->get_cronid_by_filename($filename);
		if(self::enable()) {
			if(!$cronid) {
				$name = '支付订单对账';
				if(defined('IN_ADMINCP')) {
					$langname = lang('admincp', 'ec_payment_check_cron');
					if($langname && $langname != 'ec_payment_check_cron') {
						$name = $langname;
					}
				}
				table_common_cron::t()->insert([
					'available' => 1,
					'type' => 'system',
					'name' => $name,
					'filename' => $filename,
					'lastrun' => 0,
					'nextrun' => TIMESTAMP + 600,
					'weekday' => -1,
					'day' => -1,
					'hour' => -1,
					'minute' => "0\t10\t20\t30\t40\t50",
				]);
				return self::cron_reset_nextrun();
			}
			$cron = table_common_cron::t()->fetch($cronid);
			if($cron && !$cron['available']) {
				table_common_cron::t()->update($cronid, ['available' => 1, 'nextrun' => TIMESTAMP + 600]);
				return self::cron_reset_nextrun();
			}
		} elseif($cronid) {
			$cron = table_common_cron::t()->fetch($cronid);
			if($cron && $cron['available']) {
				table_common_cron::t()->update($cronid, ['available' => 0]);
			}
		}
		return false;
	}

	/**
	 * 让计划任务缓存（cronnextrun）在下一次请求时重新计算，使新增/启用的任务尽快生效
	 */
	private static function cron_reset_nextrun() {
		savecache('cronnextrun', TIMESTAMP);
		return true;
	}

	public static function channels_setting() {
		$settings = table_common_setting::t()->fetch_all_setting(['payment_channels'], true);
		return $settings['payment_channels'];
	}

	public static function channels() {
		$result = [];
		$result['qpay'] = [
			'id' => 'qpay',
			'title' => lang('spacecp', 'payment_qpay'),
			'logo' => 'static/image/common/qpay_logo.svg',
			'enable' => 0
		];
		$result['wechat'] = [
			'id' => 'wechat',
			'title' => lang('spacecp', 'payment_wechat'),
			'logo' => 'static/image/common/wechatpay_logo.svg',
			'enable' => 0
		];
		$result['alipay'] = [
			'id' => 'alipay',
			'title' => lang('spacecp', 'payment_alipay'),
			'logo' => 'static/image/common/alipay_logo.svg',
			'enable' => 0
		];

		$channels = table_common_setting::t()->fetch_all_setting(['ec_wechat', 'ec_alipay', 'ec_qpay', 'payment_channels'], true);
		if($channels['ec_alipay']['on']) {
			$result['alipay']['enable'] = 1;
		}
		if($channels['ec_wechat']['on']) {
			$result['wechat']['enable'] = 1;
		}
		if($channels['ec_qpay']['on']) {
			$result['qpay']['enable'] = 1;
		}
		if(!empty($channels['payment_channels'])) {
			foreach($channels['payment_channels'] as $channel => $val) {
				$result[$val['id']] = $val;
			}
		}

		return $result;
	}

	public static function get($channel) {
		[$namespace, $pay_class] = explode(':', $channel);
		if(!empty($pay_class)) {
			if(!class_exists($classname = $namespace.'\\pay_'.$pay_class)) {
				return false;
			}
		} else {
			$classname = 'pay_'.$channel;
			if(!class_exists($classname)) {
				return false;
			}
		}
		return new $classname();
	}

	public static function create_order($type, $subject, $description, $amount, $return_url, $params = null, $fee = 0, $expire = 3600) {
		global $_G;

		if(str_contains($type, ':')) {
			$type_values = explode(':', $type);
			$type_name = lang('plugin/'.$type_values[0], $type_values[1]);
		} else {
			$type_name = lang('payment/type', $type);
		}

		$out_biz_no = dgmdate(TIMESTAMP, 'YmdHis').random(14, 1);
		$data = [
			'out_biz_no' => $out_biz_no,
			'type' => $type,
			'type_name' => $type_name,
			'uid' => $_G['uid'],
			'amount' => $amount,
			'amount_fee' => $fee,
			'subject' => $subject,
			'description' => $description,
			'expire_time' => time() + $expire,
			'status' => 0,
			'return_url' => str_replace(['{out_biz_no}'], [$out_biz_no], $return_url),
			'clientip' => $_G['clientip'],
			'remoteport' => $_G['remoteport'],
			'dateline' => time()
		];
		if($params) {
			$data['data'] = serialize($params);
		}
		$id = table_common_payment_order::t()->insert($data, true);
		return $_G['siteurl'].'home.php?mod=spacecp&ac=payment&op=pay&order_id='.$id;
	}

	public static function finish_order($channel, $out_biz_no, $trade_no, $payment_time) {
		$order = table_common_payment_order::t()->fetch_by_biz_no($out_biz_no);
		if(!$order || $order['status']) {
			if(!$order) {
				$error = 50002;
			} else {
				$error = 50003;
			}
			self::notifylog($channel, 0, $error, $out_biz_no, ['trade_no' => $trade_no], is_array($order) ? $order : []);
			return true;
		}

		$order['trade_no'] = $trade_no;
		$order['payment_time'] = $payment_time;
		$order['channel'] = $channel;
		$order['status'] = 1;

		$status = table_common_payment_order::t()->update_order_finish($order['id'], $order['trade_no'], $order['payment_time'], $order['channel']);
		if($status) {
			self::retry_callback_order($order);
		} else {
			self::notifylog($channel, 0, 50004, $out_biz_no, ['trade_no' => $trade_no], $order);
		}
		return true;
	}

	/**
	 * 记录支付渠道异步回调日志（X5 标准日志，写入 common_log，type=pmt）
	 *
	 * 后台：工具 - 日志 - 支付日志；异常码对应语言包 payment_error_{code}
	 *
	 * @param string $channel   渠道标识 wechat/alipay/qpay
	 * @param int $status       1=回调处理完成，0=回调异常
	 * @param int $error        异常码，0 表示无异常
	 * @param string $out_biz_no 商户订单号
	 * @param array $data       附加信息（展开日志行可见）
	 * @param array|null $order 已取到的订单记录，避免重复查询
	 */
	public static function notifylog($channel, $status, $error, $out_biz_no = '', $data = [], $order = null) {
		if($order === null) {
			$order = $out_biz_no ? table_common_payment_order::t()->fetch_by_biz_no($out_biz_no) : [];
		}
		$logdata = array_merge(['out_biz_no' => $out_biz_no], is_array($data) ? $data : ['detail' => $data]);
		self::paymentlog($channel, $status ? 1 : 0, (int)($order['uid'] ?? 0), (int)($order['id'] ?? 0), intval($error), self::notifylog_filter($logdata));
	}

	/**
	 * 回调日志落单：调用 finish_order 并记录成功回调
	 *
	 * 失败场景（订单不存在/已完成/更新失败）由 finish_order 自行记录，此处不重复记录
	 */
	public static function finish_order_notify($channel, $out_biz_no, $trade_no, $payment_time, $data = []) {
		$order = $out_biz_no ? table_common_payment_order::t()->fetch_by_biz_no($out_biz_no) : [];
		$unpaid = $order && empty($order['status']);
		$is_success = self::finish_order($channel, $out_biz_no, $trade_no, $payment_time);
		if($unpaid) {
			$order = table_common_payment_order::t()->fetch_by_biz_no($out_biz_no);
			if(!empty($order['status'])) {
				self::notifylog($channel, 1, 0, $out_biz_no, array_merge([
					'trade_no' => $trade_no,
					'payment_time' => $payment_time ? dgmdate($payment_time, 'Y-m-d H:i:s') : ''
				], is_array($data) ? $data : []), $order);
			}
		}
		return $is_success;
	}

	private static function notifylog_filter($data) {
		array_walk_recursive($data, function (&$v) {
			if(!is_scalar($v) || is_bool($v)) {
				$v = is_bool($v) ? intval($v) : '';
				return;
			}
			$v = (string)$v;
			if(strlen($v) > 500) {
				$v = substr($v, 0, 500).'...';
			}
		});
		// HEX_* 转义：日志明细会原样输出到后台页面，避免回调内容里的 HTML 被执行
		// common_log.data 为 json 字段，编码失败会导致写入异常，此处兜底
		$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
		return $json === false ? '{"detail":"encode failed"}' : $json;
	}

	public static function retry_callback_order($order) {
		if($order['status'] != 1) {
			return ['code' => 500, 'message' => lang('message', 'payment_retry_callback_no_pay')];
		}
		if(!$order['callback_status']) {
			$order_type = $order['type'];
			if(str_contains($order_type, ':')) {
				$order_type_values = explode(':', $order_type);
				$class_name = $order_type_values[0].'\\pay_'.$order_type_values[1];
			} else {
				$class_name = $order_type;
			}
			if(class_exists($class_name) && method_exists($class_name, 'callback')) {
				$callback = new $class_name();
				$callback->callback(dunserialize($order['data']), $order);
			}
			table_common_payment_order::t()->update($order['id'], ['callback_status' => 1]);
		}
		return ['code' => 200];
	}

	public static function query_order($channel, $order_id) {
		$order = table_common_payment_order::t()->fetch($order_id);
		if(!$order) {
			return ['code' => 500, 'message' => lang('message', 'payment_order_no_exist')];
		}
		$payment = payment::get($channel);
		if(!$payment) {
			return ['code' => 500, 'message' => lang('message', 'payment_type_no_exist')];
		}
		$result = $payment->status($order['out_biz_no']);
		if($result['code'] == 200 && $order['status'] != 1 && $result['data']) {
			payment::finish_order($channel, $order['out_biz_no'], $result['data']['trade_no'], $result['data']['payment_time']);
		}
		return $result;
	}

	public static function refund($refund_no, $order_id, $amount, $refund_desc) {
		global $_G;
		$order = table_common_payment_order::t()->fetch($order_id);
		if(!$order || $order['status'] != 1) {
			return ['code' => 500, 'message' => lang('message', 'payment_order_no_exist')];
		}

		$refund_order = table_common_payment_refund::t()->fetch_by_no($refund_no);
		if($refund_order) {
			if($refund_order['order_id'] != $order_id) {
				return ['code' => 500, 'message' => lang('message', 'payment_refund_id_exist')];
			}
			if($refund_order['status'] == 2) {
				return ['code' => 200, 'data' => [
					'refund_time' => $refund_order['refund_time']
				]];
			}
			if($refund_order['status'] == 1) {
				return ['code' => 500, 'message' => lang('message', 'payment_refund_exist')];
			}

			table_common_payment_refund::t()->update_refund_by_no($refund_no, [
				'amount' => $amount,
				'description' => $refund_desc,
				'clientip' => $_G['clientip'],
				'remoteport' => $_G['remoteport'],
				'status' => 1,
				'dateline' => time()
			]);
		} else {
			table_common_payment_refund::t()->insert([
				'order_id' => $order_id,
				'out_biz_no' => $refund_no,
				'amount' => $amount,
				'description' => $refund_desc,
				'status' => 1,
				'clientip' => $_G['clientip'],
				'remoteport' => $_G['remoteport'],
				'dateline' => time()
			]);
		}

		$payment = payment::get($order['channel']);
		$result = $payment->refund($refund_no, $order['trade_no'], $order['amount'], $amount, $refund_desc);
		if($result['code'] == 200) {
			table_common_payment_refund::t()->update_refund_by_no($refund_no, [
				'status' => 2,
				'refund_time' => $result['data']['refund_time']
			]);
		} elseif($result['code'] == 201) {
			table_common_payment_refund::t()->update_refund_by_no($refund_no, [
				'status' => -1,
				'error' => $result['message']
			]);
		} else {
			table_common_payment_refund::t()->update_refund_by_no($refund_no, [
				'status' => 2,
				'error' => $result['message']
			]);
		}
		return $result;
	}

	public static function refund_status($refund_no, $order_id) {
		$order = table_common_payment_order::t()->fetch($order_id);
		if(!$order || $order['status'] != 1) {
			return ['code' => 500, 'message' => lang('message', 'payment_order_no_exist')];
		}
		$refund_order = table_common_payment_refund::t()->fetch_by_no($refund_no);
		if($refund_order) {
			if($refund_order['order_id'] != $order_id) {
				return ['code' => 500, 'message' => lang('message', 'payment_refund_id_exist')];
			} elseif($refund_order['status'] == 1) {
				return ['code' => 200, 'data' => ['refund_time' => $refund_order['refund_time']]];
			}
		}

		$payment = payment::get($order['channel']);
		$result = $payment->refund_status($refund_no, $order['trade_no']);
		if($result['code'] == 200) {
			table_common_payment_refund::t()->update_refund_by_no($refund_no, [
				'status' => 2,
				'error' => '',
				'refund_time' => $result['data']['refund_time']
			]);
		} else {
			table_common_payment_refund::t()->update_refund_by_no($refund_no, [
				'status' => 2,
				'error' => $result['message']
			]);
		}
		return $result;
	}

	public static function transfer($channel, $transfer_no, $amount, $uid, $realname, $account, $title = '', $desc = '') {
		global $_G;
		$transfer_order = table_common_payment_transfer::t()->fetch_by_no($transfer_no);
		if($transfer_order) {
			if($transfer_order['channel'] != $channel || $transfer_order['amount'] != $amount || $transfer_order['account'] != $account) {
				return ['code' => 500, 'message' => lang('message', 'payment_transfer_id_exist')];
			}
			if($transfer_order['status'] == 2) {
				return ['code' => 200, 'data' => [
					'transfer_time' => $transfer_order['trade_time']
				]];
			}
			if($transfer_order['status'] == 1) {
				return ['code' => 500, 'message' => lang('message', 'payment_transfer_exist')];
			}

			table_common_payment_transfer::t()->update_transfer_by_no($transfer_no, [
				'subject' => $title,
				'description' => $desc,
				'realname' => $realname,
				'clientip' => $_G['clientip'],
				'remoteport' => $_G['remoteport'],
				'uid' => $uid,
				'status' => 1,
				'dateline' => time()
			]);
		} else {
			table_common_payment_transfer::t()->insert([
				'out_biz_no' => $transfer_no,
				'amount' => $amount,
				'subject' => $title,
				'description' => $desc,
				'realname' => $realname,
				'account' => $account,
				'channel' => $channel,
				'uid' => $uid,
				'status' => 1,
				'clientip' => $_G['clientip'],
				'remoteport' => $_G['remoteport'],
				'dateline' => time()
			]);
		}

		$payment = payment::get($channel);
		$result = $payment->transfer($transfer_no, $amount, $realname, $account, $title, $desc);
		if($result['code'] == 200) {
			table_common_payment_transfer::t()->update_transfer_by_no($transfer_no, [
				'status' => 2,
				'trade_time' => $result['data']['transfer_time']
			]);
		} else {
			table_common_payment_transfer::t()->update_transfer_by_no($transfer_no, [
				'status' => 3,
				'error' => $result['message']
			]);
		}
		return $result;
	}

	public static function transfer_status($transfer_no) {
		$refund_order = table_common_payment_transfer::t()->fetch_by_no($transfer_no);
		if(!$refund_order) {
			return ['code' => 500, 'message' => lang('message', 'payment_transfer_id_no_exist')];
		} elseif($refund_order['status'] == 2) {
			return ['code' => 200, 'data' => ['transfer_time' => $refund_order['trade_time']]];
		}

		$payment = payment::get($refund_order['channel']);
		$result = $payment->transfer_status($transfer_no);
		if($result['code'] == 200) {
			table_common_payment_transfer::t()->update_transfer_by_no($transfer_no, [
				'status' => 2,
				'trade_time' => $result['data']['transfer_time']
			]);
		} else {
			table_common_payment_transfer::t()->update_transfer_by_no($transfer_no, [
				'status' => 3,
				'error' => $result['message']
			]);
		}
		return $result;
	}

	public static function paymentlog($channel, $status, $uid, $order_id, $error, $data) {
		global $_G;
		require_once libfile('function/misc');

		/*
		writelog('pmtlog', implode("\t", clearlogstring(array(
			$_G['timestamp'],
			$channel,
			$status,
			$order_id,
			$uid,
			$_G['clientip'],
			$_G['remoteport'],
			$error,
			is_array($data) ? json_encode($data) : $data
		))));
		*/

		// logger start
		if($_G['setting']['log']['pmt']) {
			$errorlog = [
				'dateline' => $_G['timestamp'],
				'channel' => $channel,
				'status' => $status,
				'order_id' => $order_id,
				'uid' => $uid,
				'clientip' => $_G['clientip'],
				'remoteport' => $_G['remoteport'],
				'error' => $error,
				'data' => is_array($data) ? json_encode($data) : $data,
			];
			$member_log = getuserbyuid($uid);
			logger('pmt', $member_log, $uid, $errorlog);
		}
		// logger end
	}
}
