<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

function showsearchform($operation = '') {
	global $_G, $lang;

	$groupselect = [];
	$usergroupid = isset($_GET['usergroupid']) && is_array($_GET['usergroupid']) ? $_GET['usergroupid'] : [];
	$medals = isset($_GET['medalid']) && is_array($_GET['medalid']) ? $_GET['medalid'] : [];
	$tagid = isset($_GET['tagid']) && is_array($_GET['tagid']) ? $_GET['tagid'] : [];
	$query = table_common_usergroup::t()->fetch_all_not([6, 7], true);
	foreach($query as $group) {
		$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
		$groupselect[$group['type']] .= "<option value=\"{$group['groupid']}\" ".(in_array($group['groupid'], $usergroupid) ? 'selected' : '').">{$group['grouptitle']}</option>\n";
	}
	$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
		($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
		($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
		'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';
	$medalselect = $usertagselect = '';
	foreach(table_forum_medal::t()->fetch_all_data(1) as $medal) {
		$medalselect .= "<option value=\"{$medal['medalid']}\" ".(in_array($medal['medalid'], $medals) ? 'selected' : '').">{$medal['name']}</option>\n";
	}
	$query = table_common_tag::t()->fetch_all_by_status(3);
	foreach($query as $row) {
		$usertagselect .= "<option value=\"{$row['tagid']}\" ".(in_array($row['tagid'], $tagid) ? 'selected' : '').">{$row['tagname']}</option>\n";
	}


	$interfaces_aType = account_base::Interfaces_aType();
	if(!empty($_G['setting']['account_plugin_atypes'])) {
		foreach($_G['setting']['account_plugin_atypes'] as $pluginid => $atype) {
			$interfaces_aType['plugin_'.$pluginid] = $atype;
		}
	}
	$accountused = account_base::getInterfaces();
	$accouts = [['', cplang('nolimit')]];
	foreach($interfaces_aType as $interface => $atype) {
		if(in_array($interface, $accountused)) {
			$accouts[] = [$atype, account_base::getName($interface)];
		}
	}

	/*search={"nav_members":"action=members&operation=search"}*/
	showtagheader('div', 'searchmembers', !$_GET['submit']);
	echo '<script src="'.STATICURL.'js/calendar.js" type="text/javascript"></script>';
	echo '<style type="text/css">#residedistrictbox select, #birthdistrictbox select{width: auto;}</style>';
	$formurl = "members&operation=$operation".(($_GET['do'] == 'mobile' || $_GET['do'] == 'sms') ? '&do='.$_GET['do'] : '');
	showformheader($formurl, "onSubmit=\"if($('updatecredittype1') && $('updatecredittype1').checked && !window.confirm('{$lang['members_reward_clean_alarm']}')){return false;} else {return true;}\"");
	showtableheader('', 'nobottom');
	if(isset($_G['setting']['membersplit'])) {
		showsetting('members_search_table', '', '', '<select name="tablename" ><option value="master">'.$lang['members_search_table_master'].'</option><option value="archive">'.$lang['members_search_table_archive'].'</option></select>');
	}
	showsetting('members_search_login', 'loginname', $_GET['loginname'], 'text');
	showsetting('members_search_user', 'username', $_GET['username'], 'text');
	showsetting('members_search_user_history', 'username_his', $_GET['username_his'], 'text');
	showsetting('members_search_uid', 'uid', $_GET['uid'], 'text');
	showsetting('members_search_secmobile', 'secmobile', $_GET['secmobile'], 'text');
	showsetting('account', ['atype', $accouts], $_GET['account'], 'select');
	showtablefooter();

	showtableheader();
	showtagheader('tbody', 'advanceoption');
	$_G['showsetting_multirow'] = 1;
	if(empty($medalselect)) {
		$medalselect = '<option value="">'.cplang('members_search_nonemedal').'</option>';
	}
	if(empty($usertagselect)) {
		$usertagselect = '<option value="">'.cplang('members_search_noneusertags').'</option>';
	}

	showsetting('members_search_group', '', '', '<select name="groupid[]" multiple="multiple" size="10">'.$groupselect.'</select>');
	showsetting('members_search_medal', '', '', '<select name="medalid[]" multiple="multiple" size="10">'.$medalselect.'</select>');
	showsetting('members_search_usertag', '', '', '<select name="tagid[]" multiple="multiple" size="10">'.$usertagselect.'</select>');
	showsetting('members_search_online', ['sid_noempty', [
		[1, $lang['yes']],
		[0, $lang['no']],
	], 1], $_GET['online'], 'mradio');
	showsetting('members_search_lockstatus', ['status', [
		[-1, $lang['yes']],
		[0, $lang['no']],
	], 1], $_GET['status'], 'mradio');
	showsetting('members_search_freezestatus', ['freeze', [
		[2, $lang['members_edit_freeze_email']],
		[-1, $lang['members_edit_freeze_admincp']],
		[1, $lang['members_edit_freeze_password']],
		[0, $lang['members_edit_freeze_false']],
	], 1], $_GET['freeze'], 'mradio');
	showsetting('members_search_emailstatus', ['emailstatus', [
		[1, $lang['yes']],
		[0, $lang['no']],
	], 1], $_GET['emailstatus'], 'mradio');
	showsetting('members_search_avatarstatus', ['avatarstatus', [
		[1, $lang['yes']],
		[0, $lang['no']],
	], 1], $_GET['avatarstatus'], 'mradio');
	showsetting('members_search_email', 'email', $_GET['email'], 'text');
	showsetting("{$lang['credits']} {$lang['members_search_between']}", ['credits_low', 'credits_high'], [$_GET['credits_low'], $_GET['credtis_high']], 'range');

	if(!empty($_G['setting']['extcredits'])) {
		foreach($_G['setting']['extcredits'] as $id => $credit) {
			showsetting("{$credit['title']} {$lang['members_search_between']}", ["extcredits$id".'_low', "extcredits$id".'_high'], [$_GET['extcredits'.$id.'_low'], $_GET['extcredits'.$id.'_high']], 'range');
		}
	}

	showsetting('members_search_friendsrange', ['friends_low', 'friends_high'], [$_GET['friends_low'], $_GET['friends_high']], 'range');
	showsetting('members_search_postsrange', ['posts_low', 'posts_high'], [$_GET['posts_low'], $_GET['posts_high']], 'range');
	showsetting('members_search_regip', 'regip', $_GET['regip'], 'text');
	showsetting('members_search_lastip', 'lastip', $_GET['lastip'], 'text');
	showsetting('members_search_oltimerange', ['oltime_low', 'oltime_high'], [$_GET['oltime_low'], $_GET['oltime_high']], 'range');
	showsetting('members_search_regdaterange', ['regdate_after', 'regdate_before'], [$_GET['regdate_after'], $_GET['regdate_before']], 'daterange', '', 0, '', 1);
	showsetting('members_search_lastvisitrange', ['lastvisit_after', 'lastvisit_before'], [$_GET['lastvisit_after'], $_GET['lastvisit_before']], 'daterange', '', 0, '', 1);
	showsetting('members_search_lastpostrange', ['lastpost_after', 'lastpost_before'], [$_GET['lastpost_after'], $_GET['lastpost_before']], 'daterange', '', 0, '', 1);
	showsetting('members_search_group_fid', 'fid', $_GET['fid'], 'text');
	if($_G['setting']['verify']) {
		$verifydata = [];
		foreach($_G['setting']['verify'] as $key => $value) {
			if($value['available']) {
				$verifydata[] = ['verify'.$key, $value['title']];
			}
		}
		if(!empty($verifydata)) {
			showsetting('members_search_verify', ['verify', $verifydata], $_GET['verify'], 'mcheckbox');
		}
	}
	$yearselect = $monthselect = $dayselect = "<option value=\"\">".cplang('nolimit')."</option>\n";
	$yy = dgmdate(TIMESTAMP, 'Y');
	for($y = $yy; $y >= $yy - 100; $y--) {
		$y = sprintf('%04d', $y);
		$yearselect .= "<option value=\"$y\" ".($_GET['birthyear'] == $y ? 'selected' : '').">$y</option>\n";
	}
	for($m = 1; $m <= 12; $m++) {
		$m = sprintf('%02d', $m);
		$monthselect .= "<option value=\"$m\" ".($_GET['birthmonth'] == $m ? 'selected' : '').">$m</option>\n";
	}
	for($d = 1; $d <= 31; $d++) {
		$d = sprintf('%02d', $d);
		$dayselect .= "<option value=\"$d\" ".($_GET['birthday'] == $d ? 'selected' : '').">$d</option>\n";
	}
	showsetting('members_search_birthday', '', '', '<select class="txt" name="birthyear" style="width:75px; margin-right:0">'.$yearselect.'</select> '.$lang['year'].' <select class="txt" name="birthmonth" style="width:75px; margin-right:0">'.$monthselect.'</select> '.$lang['month'].' <select class="txt" name="birthday" style="width:75px; margin-right:0">'.$dayselect.'</select> '.$lang['day']);

	loadcache('profilesetting');
	unset($_G['cache']['profilesetting']['uid']);
	unset($_G['cache']['profilesetting']['birthyear']);
	unset($_G['cache']['profilesetting']['birthmonth']);
	unset($_G['cache']['profilesetting']['birthday']);
	require_once libfile('function/profile');
	foreach($_G['cache']['profilesetting'] as $fieldid => $value) {
		if(!$value['available'] || in_array($fieldid, ['birthcountry', 'birthprovince', 'birthdist', 'birthcommunity', 'residecountry', 'resideprovince', 'residedist', 'residecommunity'])) {
			continue;
		}
		if($fieldid == 'gender') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			$select .= "<option value=\"0\">".cplang('members_edit_gender_secret')."</option>\n";
			$select .= "<option value=\"1\">".cplang('members_edit_gender_male')."</option>\n";
			$select .= "<option value=\"2\">".cplang('members_edit_gender_female')."</option>\n";
			showsetting($value['title'], '', '', '<select class="txt" name="gender">'.$select.'</select>');
		} elseif($fieldid == 'birthcity') {
			$elems = ['birthcountry', 'birthprovince', 'birthcity', 'birthdist', 'birthcommunity'];
			showsetting($value['title'], '', '', '<div id="birthdistrictbox">'.showdistrict([0, 0, 0, 0, 0], $elems, 'birthdistrictbox', 1, 'birth').'</div>');
		} elseif($fieldid == 'residecity') {
			$elems = ['residecountry', 'resideprovince', 'residecity', 'residedist', 'residecommunity'];
			showsetting($value['title'], '', '', '<div id="residedistrictbox">'.showdistrict([0, 0, 0, 0, 0], $elems, 'residedistrictbox', 1, 'reside').'</div>');
		} elseif($fieldid == 'constellation') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			for($i = 1; $i <= 12; $i++) {
				$name = lang('space', 'constellation_'.$i);
				$select .= "<option value=\"$name\">$name</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="constellation">'.$select.'</select>');
		} elseif($fieldid == 'zodiac') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			for($i = 1; $i <= 12; $i++) {
				$option = lang('space', 'zodiac_'.$i);
				$select .= "<option value=\"$option\">$option</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="zodiac">'.$select.'</select>');
		} elseif($value['formtype'] == 'select' || $value['formtype'] == 'list') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			$value['choices'] = explode("\n", $value['choices']);
			foreach($value['choices'] as $option) {
				$option = trim($option);
				$select .= "<option value=\"$option\">$option</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="'.$fieldid.'">'.$select.'</select>');
		} else {
			showsetting($value['title'], '', '', '<input class="txt" name="'.$fieldid.'" />');
		}
	}
	showtagfooter('tbody');
	$_G['showsetting_multirow'] = 0;
	showsubmit('submit', $operation == 'clean' ? 'members_delete' : 'search', '', 'more_options');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
	/*search*/
}

function searchcondition($condition) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->filtercondition($condition);
}

function searchmembers($condition, $limit = 2000, $start = 0) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->search($condition, $limit, $start);
}

function countmembers($condition, &$urladd) {

	$urladd = '';
	foreach($condition as $k => $v) {
		if(in_array($k, ['formhash', 'submit', 'page']) || $v === '') {
			continue;
		}
		if(is_array($v)) {
			foreach($v as $vk => $vv) {
				if($vv === '') {
					continue;
				}
				$urladd .= '&'.$k.'['.$vk.']='.rawurlencode($vv);
			}
		} else {
			$urladd .= '&'.$k.'='.rawurlencode($v);
		}
	}
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->getcount($condition);
}

function shownewsletter() {
	global $lang;

	showtableheader('', 'nobottom');
	showsetting('members_newsletter_subject', 'subject', '', 'text');
	showsetting('members_newsletter_message', 'message', '', 'textarea');
	if($_GET['do'] == 'mobile' || $_GET['do'] == 'sms') {
		if($_GET['do'] == 'mobile') {
			showsetting('members_newsletter_system', 'system', 0, 'radio');
		} else {
			showhiddenfields(['system' => 0]);
		}
		showhiddenfields(['notifymembers' => $_GET['do']]);
	} else {
		showsetting('members_newsletter_method', ['notifymembers', [
			['email', $lang['email'], ['pmextra' => 'none', 'posttype' => '']],
			['notice', $lang['notice'], ['pmextra' => 'none', 'posttype' => '']],
			['pm', $lang['grouppm'], ['pmextra' => '', 'posttype' => 'none']]
		]], 'pm', 'mradio');
		showtagheader('tbody', 'posttype', '', 'sub');
		showsetting('members_newsletter_posttype', ['posttype', [
			[0, cplang('members_newsletter_posttype_text')],
			[1, cplang('members_newsletter_posttype_html')],
		], TRUE], '0', 'mradio');
		showtagfooter('tbody');
		showtagheader('tbody', 'pmextra', true, 'sub');
		showsetting('members_newsletter_system', 'system', 0, 'radio');
		showtagfooter('tbody');
	}
	showsetting('members_newsletter_num', 'pertask', 100, 'text');
	showtablefooter();

}

function notifymembers($operation, $variable) {
	global $_G, $lang, $urladd, $conditions, $search_condition;

	if(!empty($_GET['current'])) {

		$subject = $message = '';
		if($settings = table_common_setting::t()->fetch_setting($variable, true)) {
			$subject = $settings['subject'];
			$message = $settings['message'];
		}

		$setarr = [];
		foreach($_G['setting']['extcredits'] as $id => $value) {
			if(isset($_GET['extcredits'.$id])) {
				if($_GET['updatecredittype'] == 0) {
					$setarr['extcredits'.$id] = $_GET['extcredits'.$id];
				} else {
					$setarr[] = 'extcredits'.$id;
				}
			}
		}

	} else {

		$current = 0;
		$subject = $_GET['subject'];
		$message = $_GET['message'];
		$subject = dhtmlspecialchars(trim($subject));
		$message = trim(str_replace("\t", ' ', $message));
		$addmsg = '';
		if(($_GET['notifymembers'] && $_GET['notifymember']) && !($subject && $message)) {
			cpmsg('members_newsletter_sm_invalid', '', 'error');
		}

		if($operation == 'reward') {

			$serarr = [];
			if($_GET['updatecredittype'] == 0) {
				if(is_array($_GET['addextcredits']) && !empty($_GET['addextcredits'])) {
					foreach($_GET['addextcredits'] as $key => $value) {
						$value = intval($value);
						if(isset($_G['setting']['extcredits'][$key]) && !empty($value)) {
							$setarr['extcredits'.$key] = $value;
							$addmsg .= $_G['setting']['extcredits'][$key]['title'].': '.($value > 0 ? '<em class="xi1">+' : '<em class="xg1">')."$value</em> ".$_G['setting']['extcredits'][$key]['unit'].' &nbsp; ';
						}
					}
				}
			} else {
				if(is_array($_GET['resetextcredits']) && !empty($_GET['resetextcredits'])) {
					foreach($_GET['resetextcredits'] as $key => $value) {
						$value = intval($value);
						if(isset($_G['setting']['extcredits'][$key]) && !empty($value)) {
							$setarr[] = 'extcredits'.$key;
							$addmsg .= $_G['setting']['extcredits'][$key]['title'].': <em class="xg1">'.cplang('members_reward_clean').'</em> &nbsp; ';
						}
					}
				}
			}
			if($addmsg) {
				$addmsg = ' &nbsp; <br /><br /><b>'.cplang('members_reward_affect').':</b><br \>'.$addmsg;
			}

			if(!empty($setarr)) {
				$limit = 2000;
				set_time_limit(0);
				$i = 0;
				while(true) {
					$uids = searchmembers($search_condition, $limit, $i * $limit);
					$allcount = table_common_member_count::t()->fetch_all($uids);
					$insertmember = array_diff($uids, array_keys($allcount));
					foreach($insertmember as $uid) {
						table_common_member_count::t()->insert(['uid' => $uid]);
					}

					$log = [];
					if($_GET['updatecredittype'] == 0) {
						table_common_member_count::t()->increase($uids, $setarr);

						foreach($setarr as $key => $val) {
							if(empty($val)) continue;
							$val = intval($val);
							$id = intval($key);
							$id = !$id && substr($key, 0, -1) == 'extcredits' ? intval(substr($key, -1, 1)) : $id;
							if(0 < $id && $id < 9) {
								$log['extcredits'.$id] = $val;
							}
						}
						$logtype = 'RPR';
					} else {
						table_common_member_count::t()->clear_extcredits($uids, $setarr);

						foreach($setarr as $val) {
							if(empty($val)) continue;
							$id = intval($val);
							$id = !$id && substr($val, 0, -1) == 'extcredits' ? intval(substr($val, -1, 1)) : $id;
							if(0 < $id && $id < 9) {
								$log['extcredits'.$id] = '-1';
							}
						}
						$logtype = 'RPZ';
					}

					include_once libfile('function/credit');
					foreach($uids as $uid) {
						credit_log($uid, $logtype, $uid, $log);
					}

					if(count($uids) < $limit) break;
					$i++;
				}
			} else {
				cpmsg('members_reward_invalid', '', 'error');
			}

			if(!$_GET['notifymembers']) {
				cpmsg('members_reward_succeed', '', 'succeed');
			}

		} elseif($operation == 'confermedal') {

			$medals = $_GET['medals'];
			if(!empty($medals)) {
				$medalids = [];
				foreach($medals as $key => $medalid) {
					$medalids[] = $key;
				}

				$medalsnew = $comma = '';
				$medalsnewarray = $medalidarray = [];
				foreach(table_forum_medal::t()->fetch_all_by_id($medalids) as $medal) {
					$medal['status'] = empty($medal['expiration']) ? 0 : 1;
					$medal['expiration'] = empty($medal['expiration']) ? 0 : TIMESTAMP + $medal['expiration'] * 86400;
					$medal['medal'] = $medal['medalid'].(empty($medal['expiration']) ? '' : '|'.$medal['expiration']);
					$medalsnew .= $comma.$medal['medal'];
					$medalsnewarray[] = $medal;
					$medalidarray[] = $medal['medalid'];
					$comma = "\t";
				}

				$uids = searchmembers($search_condition);
				if($uids) {
					foreach(table_common_member_field_forum::t()->fetch_all($uids) as $uid => $medalnew) {
						$usermedal = [];
						$addmedalnew = '';
						if(empty($medalnew['medals'])) {
							$addmedalnew = $medalsnew;
						} else {
							foreach($medalidarray as $medalid) {
								$usermedal_arr = explode("\t", $medalnew['medals']);
								foreach($usermedal_arr as $key => $medalval) {
									list($usermedalid,) = explode('|', $medalval);
									$usermedal[] = $usermedalid;
								}
								if(!in_array($medalid, $usermedal)) {
									$addmedalnew .= $medalid."\t";
								}
							}
							$addmedalnew .= $medalnew['medals'];
						}
						table_common_member_field_forum::t()->update($medalnew['uid'], ['medals' => $addmedalnew], true);
						foreach($medalsnewarray as $medalnewarray) {
							$data = [
								'uid' => $medalnew['uid'],
								'medalid' => $medalnewarray['medalid'],
								'type' => 0,
								'dateline' => $_G['timestamp'],
								'expiration' => $medalnewarray['expiration'],
								'status' => $medalnewarray['status'],
							];
							table_forum_medallog::t()->insert($data);
							table_common_member_medal::t()->insert(['uid' => $medalnew['uid'], 'medalid' => $medalnewarray['medalid']], 0, 1);
						}
					}
				}
			}

			if(!$_GET['notifymember']) {
				cpmsg('members_confermedal_succeed', '', 'succeed');
			}
		} elseif($operation == 'confermagic') {
			$magics = $_GET['magic'];
			$magicnum = $_GET['magicnum'];
			if($magics) {
				require_once libfile('function/magic');
				$limit = 200;
				set_time_limit(0);
				for($i = 0; $i > -1; $i++) {
					$uids = searchmembers($search_condition, $limit, $i * $limit);

					foreach($magics as $magicid) {
						$uparray = $insarray = [];
						if(empty($magicnum[$magicid])) {
							continue;
						}
						$query = table_common_member_magic::t()->fetch_all_magic($uids ? $uids : -1, $magicid);
						foreach($query as $row) {
							$uparray[] = $row['uid'];
						}
						if($uparray) {
							table_common_member_magic::t()->increase($uparray, $magicid, ['num' => $magicnum[$magicid]]);
						}
						$insarray = array_diff($uids, $uparray);
						if($insarray) {
							$sqls = [];
							foreach($insarray as $uid) {
								table_common_member_magic::t()->insert([
									'uid' => $uid,
									'magicid' => $magicid,
									'num' => $magicnum[$magicid]
								]);
							}
						}
						foreach($uids as $uid) {
							updatemagiclog($magicid, '3', $magicnum[$magicid], '', $uid);
						}
					}
					if(count($uids) < $limit) break;
				}
			}
		}

		table_common_setting::t()->update_setting($variable, ['subject' => $subject, 'message' => $message]);
	}

	$pertask = intval($_GET['pertask']);
	$current = $_GET['current'] ? intval($_GET['current']) : 0;
	$continue = FALSE;

	if(!function_exists('sendmail')) {
		include libfile('function/mail');
	}
	if($_GET['notifymember'] && in_array($_GET['notifymembers'], ['pm', 'notice', 'email', 'sms'])) {
		$uids = searchmembers($search_condition, $pertask, $current);

		require_once libfile('function/discuzcode');
		$message = in_array($_GET['notifymembers'], ['email', 'notice']) && $_GET['posttype'] ? discuzcode($message, 1, 0, 1, '', '', '', 1) : discuzcode($message, 1, 0);
		$pmuids = [];
		if($_GET['notifymembers'] == 'pm') {
			$membernum = countmembers($search_condition, $urladd);
			$gpmid = $_GET['gpmid'];
			if(!$gpmid) {
				$pmdata = [
					'authorid' => $_G['uid'],
					'author' => !$_GET['system'] ? $_G['member']['username'] : '',
					'dateline' => TIMESTAMP,
					'message' => ($subject ? '<b>'.$subject.'</b><br /> &nbsp; ' : '').$message.$addmsg,
					'numbers' => $membernum
				];
				$gpmid = table_common_grouppm::t()->insert($pmdata, true);
			}
			$urladd .= '&gpmid='.$gpmid;
		}
		$members = table_common_member::t()->fetch_all($uids);
		foreach($members as $member) {
			if($_GET['notifymembers'] == 'pm') {
				table_common_member_grouppm::t()->insert([
					'uid' => $member['uid'],
					'gpmid' => $gpmid,
					'status' => 0
				], false, true);
				$newpm = setstatus(2, 1, $member['newpm']);
				table_common_member::t()->update($member['uid'], ['newpm' => $newpm]);
			} elseif($_GET['notifymembers'] == 'notice') {
				notification_add($member['uid'], 'system', 'system_notice', ['subject' => $subject, 'message' => $message.$addmsg, 'from_id' => 0, 'from_idtype' => 'sendnotice'], 1);
			} elseif($_GET['notifymembers'] == 'email') {
				if(!sendmail("{$member['username']} <{$member['email']}>", $subject, $message.$addmsg)) {
					runlog('sendmail', "{$member['email']} sendmail failed.");
				}
			} elseif($_GET['notifymembers'] == 'sms') {
				// 用户 UID : $member['uid'], 短信类型: 通知类短信, 服务类型: 系统级短消息通知业务
				// 国际电话区号: $member['secmobicc'], 手机号: $member['secmobile'], 内容: "[$subject]$message$addmsg", 强制发送: true
				// 短信发送前先校验安全手机号是否正确, 避免错误安全手机号送往短信网关
				if(!empty($member['secmobicc']) && !empty($member['secmobile']) && preg_match('#^(\d){1,3}$#', $member['secmobicc']) && preg_match('#^(\d){1,12}$#', $member['secmobile'])) {
					sms::send($member['uid'], 1, 2, $member['secmobicc'], $member['secmobile'], "[$subject]$message$addmsg", 1);
				}
			}

			$continue = TRUE;
		}
	}

	$newsletter_detail = [];
	if($continue) {
		$next = $current + $pertask;
		$newsletter_detail = [
			'uid' => $_G['uid'],
			'current' => $current,
			'next' => $next,
			'search_condition' => serialize($search_condition),
			'action' => "action=members&operation=$operation&{$operation}submit=yes&current=$next&pertask=$pertask&system={$_GET['system']}&posttype={$_GET['posttype']}&notifymember={$_GET['notifymember']}&notifymembers=".rawurlencode($_GET['notifymembers']).$urladd
		];
		save_newsletter('newsletter_detail', $newsletter_detail);

		$logaddurl = '';
		foreach($setarr as $k => $v) {
			if($_GET['updatecredittype'] == 0) {
				$logaddurl .= '&'.$k.'='.$v;
			} else {
				$logaddurl .= '&'.$v.'=-1';
			}
		}
		$logaddurl .= '&updatecredittype='.$_GET['updatecredittype'];

		cpmsg("{$lang['members_newsletter_send']}: ".cplang('members_newsletter_processing', ['current' => $current, 'next' => $next, 'search_condition' => serialize($search_condition)]), "action=members&operation=$operation&{$operation}submit=yes&current=$next&pertask=$pertask&system={$_GET['system']}&posttype={$_GET['posttype']}&notifymember={$_GET['notifymember']}&notifymembers=".rawurlencode($_GET['notifymembers']).$urladd.$logaddurl, 'loadingform');
	} else {
		del_newsletter('newsletter_detail');

		if($operation == 'reward' && $_GET['notifymembers'] == 'pm') {
			$message = '';
		} else {
			$message = '_notify';
		}
		cpmsg('members'.($operation ? '_'.$operation : '').$message.'_succeed', '', 'succeed');
	}

}

function banlog($username, $origgroupid, $newgroupid, $expiration, $reason, $status = 0) {
	global $_G, $_POST;
	$cloud_apps = dunserialize($_G['setting']['cloud_apps']);
	//writelog('banlog', dhtmlspecialchars("{$_G['timestamp']}\t{$_G['member']['username']}\t{$_G['groupid']}\t{$_G['clientip']}\t$username\t$origgroupid\t$newgroupid\t$expiration\t$reason\t$status"));
	// logger start
	if($_G['setting']['log']['ban']) {
		$errorlog = [
			'timestamp' => TIMESTAMP,
			'operator_username' => $_G['member']['username'],
			'operator_groupid' => $_G['groupid'],
			'clientip' => $_G['clientip'],
			'username' => $username,
			'origgroupid' => $origgroupid,
			'newgroupid' => $newgroupid,
			'expiration' => $expiration,
			'reason' => $reason,
			'status' => $status,
		];
		$member_log = table_common_member::t()->fetch_by_username($username);
		logger('ban', $member_log, $_G['member']['uid'], $errorlog);
	}
	// logger end
}

function selectday($varname, $dayarray) {
	global $lang;
	$selectday = '<select name="'.$varname.'">';
	if($dayarray && is_array($dayarray)) {
		foreach($dayarray as $day) {
			$langday = $day.'_day';
			$daydate = $day ? '('.dgmdate(TIMESTAMP + $day * 86400).')' : '';
			$selectday .= '<option value='.$day.'>'.$lang[$langday].'&nbsp;'.$daydate.'</option>';
		}
	}
	$selectday .= '</select>';

	return $selectday;
}

function accessimg($access) {
	return $access == -1 ? '<img src="'.STATICURL.'image/common/access_disallow.gif" />' :
		($access == 1 ? '<img src="'.STATICURL.'image/common/access_allow.gif" />' : '<img src="'.STATICURL.'image/common/access_normal.gif" />');
}

function save_newsletter($cachename, $data) {
	table_common_cache::t()->insert(['cachekey' => $cachename, 'cachevalue' => serialize($data), 'dateline' => TIMESTAMP], false, true);
}

function del_newsletter($cachename) {
	table_common_cache::t()->delete($cachename);
}

function get_newsletter($cachename) {
	foreach(table_common_cache::t()->fetch_all($cachename) as $result) {
		$data = $result['cachevalue'];
	}
	return $data;
}

function is_protect_member(&$member) {
	global $_G;
	return $member['adminid'] == 1 || $member['groupid'] == 1 || $member['uid'] == $_G['uid'];
}