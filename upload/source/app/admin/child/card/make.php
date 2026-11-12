<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('cardsubmit', 2)) {
	if($card_log = table_common_card_log::t()->fetch_by_operation(1)) {
		$card_log['rule'] = dunserialize($card_log['cardrule']);
	}
	$card_type[] = [0, cplang('card_type_default')];
	foreach(table_common_card_type::t()->range(0, 0, 'ASC') as $result) {
		$card_type[] = [$result['id'], $result['typename']];
	}

	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>';
	showformheader('card&operation=make&');
	/*search={"card_make_tips":"admin.php?action=card&operation=make"}*/
	showtips('card_make_tips');
	showtableheader();

	showsetting('card_make_rule', '', '', '<input type="text" name="rule" class="txt" value="'.($card_log['rule']['rule'] ? $card_log['rule']['rule'] : '').'" onkeyup="javascript:checkcardrule(this);"><br /><span id="cardrule_view" class="tips2" style="display:none;"></span>');
	echo <<<EOT
	<script type="text/javascript" charset="{$_G['charset']}">
		function checkcardrule(obj) {
			var chrLength = obj.value.length;
			$('cardrule_view').style.display = "";
			$('cardrule_view').innerHTML = "{$lang['card_number']}<strong>"+chrLength+"</strong>{$lang['card_number_unit']}";
		}
	</script>
EOT;

	showsetting('card_type', ['typeid', $card_type], $card_log['rule']['typeid'], 'select');
	showsetting('card_make_num', 'num', ($card_log['rule']['num'] ? $card_log['rule']['num'] : 1), 'text');
	$extcredits_option = '';
	foreach($_G['setting']['extcredits'] as $key => $val) {
		$extcredits_option .= "<option value='$key'".($card_log['rule']['extcreditskey'] == $key ? 'selected' : '').">{$val['title']}</option>";
	}
	showsetting('card_make_extcredits', '', '', '<select name="extcreditskey" style="width:80px;">'.$extcredits_option.'</select><input type="text" name="extcreditsval" value="'.($card_log['rule']['extcreditsval'] ? $card_log['rule']['extcreditsval'] : 1).'" class="txt" style="width:50px;">');
	showsetting('card_make_price', 'price', ($card_log['rule']['price'] ? $card_log['rule']['price'] : 0), 'text');

	showsetting('card_make_cleardateline', 'cleardateline', date('Y-m-d', $_G['timestamp'] + 31536000), 'calendar', '', 0, '');

	showsetting('card_make_description', 'description', $card_log['description'], 'text');
	/*search*/
	showsubmit('cardsubmit');
	showtablefooter();
	showformfooter();
} else {
	$_GET['rule'] = rawurldecode(trim($_GET['rule']));
	$_GET['num'] = intval($_GET['num']);
	list($y, $m, $d) = explode('-', $_GET['cleardateline']);
	$_GET['step'] = $_GET['step'] ? $_GET['step'] : 1;
	$cleardateline = $_GET['cleardateline'] && $y && $m ? mktime(23, 59, 59, $m, $d, $y) : 0;
	if($cleardateline < TIMESTAMP) {
		cpmsg('card_make_cleardateline_early', '', 'error');
	}
	if(!$_GET['rule']) {
		cpmsg('card_make_rule_empty', '', 'error');
	}
	if($_GET['num'] < 1) {
		cpmsg('card_make_num_error', '', 'error');
	}

	$card = new admin\class_card();
	$checkrule = $card->checkrule($_GET['rule'], 1);

	if($checkrule === -2) {
		cpmsg('card_make_rule_error', '', 'error');
	}

	if($_GET['step'] == 1) {
		$card_rule = serialize(['rule' => $_GET['rule'], 'price' => $_GET['price'], 'extcreditskey' => $_GET['extcreditskey'], 'extcreditsval' => $_GET['extcreditsval'], 'num' => $_GET['num'], 'cleardateline' => $cleardateline, 'typeid' => $_GET['typeid']]);
		$cardlog = [
			'uid' => $_G['uid'],
			'username' => $_G['member']['username'],
			'cardrule' => $card_rule,
			'dateline' => $_G['timestamp'],
			'description' => $_GET['description'],
			'operation' => 1,

		];
		$logid = table_common_card_log::t()->insert($cardlog, true);
	}
	$onepage_make = 500;
	$_GET['logid'] = $logid ? $logid : $_GET['logid'];
	if($_GET['num'] > $onepage_make) {
		$step_num = ceil($_GET['num'] / $onepage_make);
		if($step_num > 1) {
			if($_GET['step'] == $step_num) {
				if($_GET['num'] % $onepage_make == 0) {
					$makenum = $onepage_make;
				} else {
					$makenum = $_GET['num'] % $onepage_make;
				}
			} else {
				$makenum = $onepage_make;
				$nextstep = $_GET['step'] + 1;
			}
		}
	} else {
		$makenum = $_GET['num'];
	}

	$cardval = [
		'typeid' => $_GET['typeid'],
		'price' => $_GET['price'],
		'extcreditskey' => $_GET['extcreditskey'],
		'extcreditsval' => $_GET['extcreditsval'],
		'cleardateline' => $cleardateline
	];
	$card->make($_GET['rule'], $makenum, $cardval);
	$_GET['succeed_num'] += $card->succeed;
	$_GET['fail_num'] += $card->fail;
	if($nextstep) {
		$_GET['rule'] = rawurlencode($_GET['rule']);
		$nextlink = "action=card&operation=make&rule={$_GET['rule']}&num={$_GET['num']}&price={$_GET['price']}&extcreditskey={$_GET['extcreditskey']}&extcreditsval={$_GET['extcreditsval']}&cleardateline={$_GET['cleardateline']}&step={$nextstep}&succeed_num={$_GET['succeed_num']}&fail_num={$_GET['fail_num']}&typeid={$_GET['typeid']}&logid={$_GET['logid']}&cardsubmit=yes&formhash=".FORMHASH;
		cpmsg('card_make_step', $nextlink, 'loading', ['step' => $nextstep - 1, 'step_num' => $step_num, 'succeed_num' => $card->succeed, 'fail_num' => $card->fail]);
	} else {
		$card_info = serialize(['num' => $_GET['num'], 'succeed_num' => $_GET['succeed_num'], 'fail_num' => $_GET['fail_num']]);
		table_common_card_log::t()->update($_GET['logid'], ['info' => $card_info]);
		if(ceil($_GET['num'] * 0.6) > $_GET['succeed_num']) {
			cpmsg('card_make_rate_succeed', 'action=card&operation=make', 'succeed', ['succeed_num' => $_GET['succeed_num'], 'fail_num' => $_GET['fail_num']]);
		}
		cpmsg('card_make_succeed', 'action=card&operation=manage', 'succeed', ['succeed_num' => $_GET['succeed_num'], 'fail_num' => $_GET['fail_num']]);
	}

}