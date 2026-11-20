<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}
if(!empty($_G['fid'])) {
	$curcredits = $_G['setting']['creditstransextra'][8] ? $_G['setting']['creditstransextra'][8] : $_G['setting']['creditstrans'];
	$report_reward = dunserialize($_G['setting']['report_reward']);
	if(submitcheck('reportsubmit')) {
		if($_GET['reportids']) {
			$_modfids = [];
			foreach(explode(',', (string)$modforums['fids']) as $_fid) {
				$_fid = intval($_fid);
				if($_fid) {
					$_modfids[$_fid] = true;
				}
			}
			foreach($_GET['reportids'] as $reportid) {
				$reportid = intval($reportid);
				$_report = table_common_report::t()->fetch($reportid);
				if($_report && empty($_report['opuid']) && isset($_modfids[intval($_report['fid'])])) {
					$creditchange = '';
					$uid = intval($_report['uid']);
					$_creditvalue = is_array($_GET['creditsvalue']) ? intval($_GET['creditsvalue'][$reportid] ?? 0) : 0;
					if($uid && $uid != $_G['uid']) {
						$msg = !empty($_GET['msg'][$reportid]) ? '<br />'.dhtmlspecialchars($_GET['msg'][$reportid]) : '';
						if($_creditvalue) {
							$credittag = $_creditvalue > 0 ? '+' : '';
							if($report_reward['max'] < $_creditvalue || $_creditvalue < $report_reward['min']) {
								showmessage('quickclear_noperm', "$cpscript?mod=modcp&action=report&fid={$_G['fid']}");
							}
							$creditchange = '<br />'.lang('forum/misc', 'report_msg_your').$_G['setting']['extcredits'][$curcredits]['title'].'&nbsp;'.$credittag.$_creditvalue;
							updatemembercount($uid, [$curcredits => $_creditvalue], true, 'RPC', $reportid);
						}
						if($creditchange || $msg) {
							notification_add($uid, 'report', 'report_change_credits', ['creditchange' => $creditchange, 'msg' => $msg], 1);
						}
					}
					$opresult = $_creditvalue ? $curcredits."\t".$_creditvalue : 'ignore';
					table_common_report::t()->update($reportid, ['opuid' => $_G['uid'], 'opname' => $_G['username'], 'optime' => TIMESTAMP, 'opresult' => $opresult]);
				}
			}
			showmessage('modcp_report_success', "$cpscript?mod=modcp&action=report&fid={$_G['fid']}&lpp=$lpp");
		}
	}
	$rewardlist = '';
	$offset = abs(ceil(($report_reward['max'] - $report_reward['min']) / 10));
	if($report_reward['max'] > $report_reward['min']) {
		for($vote = $report_reward['max']; $vote >= $report_reward['min']; $vote -= $offset) {
			if($vote != 0) {
				$rewardlist .= $vote ? '<option value="'.$vote.'">'.($vote > 0 ? '+'.$vote : $vote).'</option>' : '';
			} else {
				$rewardlist .= '<option value="0" selected>'.lang('forum/misc', 'report_noreward').'</option>';
			}
		}
	}
	$reportlist = [];
	$lpp = empty($_GET['lpp']) ? 20 : intval($_GET['lpp']);
	$lpp = min(200, max(5, $lpp));
	$page = max(1, intval($_G['page']));
	$start = ($page - 1) * $lpp;

	$reportcount = table_common_report::t()->fetch_count(0, 0, $_G['fid']);
	$query = table_common_report::t()->fetch_all_report($start, $lpp, 0, $_G['fid']);
	foreach($query as $row) {
		$row['dateline'] = dgmdate($row['dateline']);
		$reportlist[] = $row;
	}
	$multipage = multi($reportcount, $lpp, $page, "$cpscript?mod=modcp&action=report&fid={$_G['fid']}&lpp=$lpp");
}
