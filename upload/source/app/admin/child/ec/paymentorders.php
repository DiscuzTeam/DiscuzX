<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(submitcheck('querysubmit')) {
	$order_id = intval($_GET['order_id']);
	$channel = daddslashes($_GET['channel']);

	$result = payment::query_order($channel, $order_id);
	if($result['code'] == 200) {
		cpmsg('payment_succeed', $_G['siteurl'].ADMINSCRIPT.'?action=ec&operation=paymentorders', 'succeed');
	} else {
		cpmsg($result['message'], $_G['siteurl'].ADMINSCRIPT.'?action=ec&operation=paymentorders', 'error');
	}
} elseif($_GET['op'] == 'retry') {
	$order_id = intval($_GET['order_id']);
	$order = table_common_payment_order::t()->fetch($order_id);
	$result = payment::retry_callback_order($order);
	if($result['code'] == 200) {
		cpmsg('payment_succeed', $_G['siteurl'].ADMINSCRIPT.'?action=ec&operation=paymentorders', 'succeed');
	} else {
		cpmsg($result['message'], $_G['siteurl'].ADMINSCRIPT.'?action=ec&operation=paymentorders', 'error');
	}
} elseif($_GET['op'] == 'query') {
	$order_id = intval($_GET['order_id']);
	$order = table_common_payment_order::t()->fetch($order_id);

	$channels = payment::channels();

	$user = getuserbyuid($order['uid']);
	showformheader('ec&operation=paymentorders');
	showhiddenfields(['order_id' => $order['id']]);
	showtableheader('ec_paymentorders_detail');
	showsetting('ec_paymentorders_no', '', '', $order['out_biz_no']);
	showsetting('ec_paymentorders_type', '', '', $order['type_name']);
	showsetting('ec_paymentorders_desc', '', '', $order['subject'].($order['description'] ? '<br/>'.$order['description'] : ''));
	showsetting('ec_paymentorders_user', '', '', $user['username'].' ('.$order['uid'].')'.'<br/>'.$order['clientip'].':'.$order['remoteport']);
	showsetting('ec_paymentorders_amount', '', '', number_format($order['amount'] / 100, 2, '.', ','));
	showsetting('ec_orders_submitdate', '', '', dgmdate($order['dateline']));
	$channelradios = '<ul onmouseover="altStyle(this);">';
	$channelindex = 0;
	foreach($channels as $index => $channel) {
		$channelradios .= '<li'.($channelindex === 0 ? ' class="checked"' : '').'><input class="radio" type="radio" name="channel" '.($channelindex === 0 ? 'checked' : '').' value="'.$channel['id'].'">&nbsp;'.$channel['title'].'</li>';
		$channelindex++;
	}
	$channelradios .= '</ul>';
	showsetting('ec_paymentorders_channel', '', '', $channelradios);
	showtablefooter();
	showsubmit('querysubmit', 'ec_paymentorders_op_status', '', $lang['ec_paymentorders_query_submit_tips']);
	showtablefooter();
	showformfooter();
} elseif($_GET['op'] == 'refundquery') {
	$order_id = intval($_GET['order_id']);
	$order = table_common_payment_order::t()->fetch($order_id);
	$processing_refund_list = table_common_payment_refund::t()->fetch_all_processing_by_orders($order_id);
	foreach($processing_refund_list as $refund) {
		$refund_result = payment::refund_status($refund['out_biz_no'], $order_id);
		if($refund_result['code'] == 200) {
			$refund_result = $refund_result['data'];
			if($refund_result['status'] == 'SUCCESS') {
				table_common_payment_refund::t()->update_refund_by_no($refund['out_biz_no'], ['status' => 2]);
			} elseif($refund_result['status'] == 'PROCESSING') {
			} else {
				table_common_payment_refund::t()->update_refund_by_no($refund['out_biz_no'], ['status' => 2 , 'error' => $refund_result['status']]);
			}
		}
	}
	cpmsg('operation_succeed', $_G['siteurl'].ADMINSCRIPT.'?action=ec&operation=paymentorders', 'succeed');
} else {
	$start_limit = ($page - 1) * $_G['tpp'];

	/*search={"nav_ec":"action=ec&operation=base","nav_ec_orders":"action=ec&operation=paymentorders"}*/
	echo '<style type="text/css">.order-status-0 td { color: #555; } .order-status-1 td { color: green; } .order-status-1 td a { color: #fe8080; } .order-status-2 td, .order-status-2 td a { color: #ccc; } .order-status-3 td { color: red; }</style>';
	echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
	$queryparams = [
		'out_biz_no' => daddslashes($_GET['out_biz_no']),
		'user' => daddslashes($_GET['user']),
		'type' => daddslashes($_GET['type']),
		'channel' => daddslashes($_GET['channel']),
		'status' => daddslashes($_GET['status']),
		'starttime' => daddslashes($_GET['starttime']),
		'endtime' => daddslashes($_GET['endtime']),
	];

	$types = table_common_payment_order::t()->fetch_type_all();
	$typeoptions = [];
	$typeoptions[] = '<option value="">'.$lang['all'].'</option>';
	foreach($types as $k => $v) {
		$typeoptions[] = "<option value=\"{$k}\"".($k == $queryparams['type'] ? ' selected' : '').">{$v}</option>";
	}
	showformheader('ec&operation=paymentorders');
	showtableheader('ec_paymentorders_search');
	showtablerow('', [
		'style="width:100px"', 'style="width:200px"',
		'style="width:100px"', 'style="width:200px"',
		'style="width:100px"', ''
	],
		[
			lang('admincp', 'ec_orders_search_id'), '<input type="text" class="txt" name="out_biz_no" value="'.$queryparams['out_biz_no'].'" />',
			lang('admincp', 'ec_paymentorders_user'), '<input type="text" class="txt" name="user" value="'.$queryparams['user'].'" />',
			lang('admincp', 'ec_paymentorders_type'), '<select name="type">'.implode('', $typeoptions).'</select>',
		]
	);

	$channels = payment::channels();
	$channeloptions = [];
	$channeloptions[] = '<option value="">'.$lang['all'].'</option>';
	foreach($channels as $channel) {
		$channeloptions[] = '<option value="'.$channel['id'].'"'.($queryparams['channel'] == $channel['id'] ? ' selected' : '').'>'.$channel['title'].'</option>';
	}
	$statusoptions = [];
	$statusoptions[] = '<option value="">'.$lang['all'].'</option>';
	$statusoptions[] = '<option value="0"'.($queryparams['status'] === '0' ? ' selected' : '').'>'.$lang['ec_paymentorders_status_0'].'</option>';
	$statusoptions[] = '<option value="1"'.($queryparams['status'] === '1' ? ' selected' : '').'>'.$lang['ec_paymentorders_status_1'].'</option>';
	$statusoptions[] = '<option value="2"'.($queryparams['status'] === '2' ? ' selected' : '').'>'.$lang['ec_paymentorders_status_2'].'</option>';
	showtablerow('', [],
		[
			lang('admincp', 'ec_paymentorders_channel'), '<select name="channel">'.implode('', $channeloptions).'</select>',
			lang('admincp', 'ec_paymentorders_status'), '<select name="status">'.implode('', $statusoptions).'</select>',
			lang('admincp', 'ec_paymentorders_date'), '<input type="text" class="txt" name="starttime" value="'.$queryparams['starttime'].'" style="width: 108px;" onclick="showcalendar(event, this)"> - <input type="text" class="txt" name="endtime" value="'.$queryparams['endtime'].'" style="width: 108px;" onclick="showcalendar(event, this)">',
		]
	);
	showtablefooter();
	showtableheader('', 'notop');
	showsubmit('searchsubmit');
	showtablefooter();
	/** list */
	if($queryparams['user']) {
		if(preg_match('/^\d+$/', $queryparams['user'])) {
			$queryparams['uid'] = $queryparams['user'];
		} else {
			$uid = table_common_member::t()->fetch_uid_by_username($queryparams['user']);
			if($uid) {
				$queryparams['uid'] = $uid;
			} else {
				$queryparams['uid'] = -1;
			}
		}
	}
	$ordercount = table_common_payment_order::t()->count_by_search($queryparams['uid'], $queryparams['type'], $queryparams['starttime'], $queryparams['endtime'], $queryparams['out_biz_no'], $queryparams['channel'], $queryparams['status']);
	$multipage = multi($ordercount, $_G['tpp'], $page, ADMINSCRIPT.'?action=ec&operation=paymentorders&'.http_build_query($queryparams));

	$tdstyles = [
		'style="width: 220px;"',
		'style="width: 100px; text-align: center"',
		'',
		'style="width: 120px;"',
		'style="width: 100px; text-align: center"',
		'style="width: 120px; text-align: right"',
		'style="width: 100px; text-align: center"',
		'style="width: 100px; text-align: right"',
		'style="width: 100px; text-align: right"',
		'style="width: 110px; text-align: right"'
	];
	showtableheader('result');
	showsubtitle(['ec_paymentorders_no', 'ec_paymentorders_type', 'ec_paymentorders_desc', 'ec_paymentorders_buyer', 'ec_paymentorders_channel', 'ec_paymentorders_amount', 'ec_paymentorders_status', 'ec_orders_submitdate', 'ec_orders_confirmdate', ''], 'header', $tdstyles);
	if($ordercount > 0) {
		$order_list = table_common_payment_order::t()->fetch_all_by_search($queryparams['uid'], $queryparams['type'], $queryparams['starttime'], $queryparams['endtime'], $queryparams['out_biz_no'], $queryparams['channel'], $queryparams['status'], $start_limit, $_G['tpp']);
		$refund_list = table_common_payment_refund::t()->sum_by_orders(array_keys($order_list));
		$processing_refund_list = table_common_payment_refund::t()->sum_processing_by_orders(array_keys($order_list));
		foreach($order_list as $order) {
			$user = getuserbyuid($order['uid']);
			if(!$order['status'] && $order['expire_time'] < time()) {
				$order['status'] = 2;
			} elseif($order['status'] == 1 && $refund_list[$order['id']]) {
				$order['status'] = 3;
				$order['refund_amount'] = $refund_list[$order['id']]['amount'];
			} elseif($order['status'] == 1 && $processing_refund_list[$order['id']]) {
				$order['status'] = 4;
				$order['refund_amount'] = $processing_refund_list[$order['id']]['amount'];
			}

			$amountstr = number_format($order['amount'] / 100, 2, '.', ',');
			if($order['status'] == 3) {
				$amountstr .= '<br/>'.$lang['ec_paymentorders_refund_amount'].': '.number_format($order['refund_amount'] / 100, 2, '.', ',');
			} elseif($order['status'] == 4) {
				$amountstr .= '<br/>'.$lang['ec_paymentorders_status_'.$order['status']].': '.number_format($order['refund_amount'] / 100, 2, '.', ',');
			}
			$operations = '';
			if(in_array($order['status'], [0, 2])) {
				$operations .= '<a href="'.ADMINSCRIPT.'?action=ec&operation=paymentorders&op=query&order_id='.$order['id'].'">'.$lang['ec_paymentorders_op_status'].'</a>';
			} elseif($order['status'] == 1 && !$order['callback_status']) {
				$operations = '<a href="'.ADMINSCRIPT.'?action=ec&operation=paymentorders&op=retry&order_id='.$order['id'].'">'.$lang['ec_paymentorders_callback_tips'].'</a>';
			} elseif($order['status'] == 4) {
				$operations .= '<a href="'.ADMINSCRIPT.'?action=ec&operation=paymentorders&op=refundquery&order_id='.$order['id'].'">'.$lang['ec_paymentorders_op_status'].'</a>';
			}

			showtablerow('class="order-status-'.$order['status'].'"', $tdstyles, [
				$order['out_biz_no'],
				$order['type_name'],
				$order['subject'].($order['description'] ? '<br/>'.$order['description'] : ''),
				$user['username'].' ('.$order['uid'].')'.'<br/>'.$order['clientip'].':'.$order['remoteport'],
				$channels[$order['channel']]['title'],
				$amountstr,
				$lang['ec_paymentorders_status_'.$order['status']],
				dgmdate($order['dateline']),
				$order['payment_time'] ? dgmdate($order['payment_time']) : 'N/A',
				$operations
			]);
		}
		showsubmit('', '', '', '', $multipage);
	} else {
		showtablerow('', ['class="center" colspan="25"'], [$lang['ec_paymentorders_no_data']]);
	}
	showtablefooter();
	showformfooter();
	/*search*/
}
	