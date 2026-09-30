<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
global $_G;
$operation = in_array(getgpc('op'), ['list', 'unbind', 'verifyemail', 'verify']) ? trim(getgpc('op')) : 'list';
$method = in_array(getgpc('method'), array_merge(account_base::getInterfaces(), ['bind', 'unbind', 'bindmobile', 'unbindmobile', 'chgemail', 'chgpassword', 'chgusername', 'chgquestion', 'resend', 'freeze'])) ? trim(getgpc('method')) : '';
$interfaces_aType = account_base::Interfaces_aType();

if($operation != 'list' && empty($method)) {
	showmessage('undefined_action');
}
$account = new account();
if($operation == 'list') {
	$list = [];
	foreach(account_base::getInterfaces() as $interface) {
		if(!account_base::allow($interface)) {
			continue;
		}
		if((new (account_base::getClass($interface)))->interface_noBind) {
			continue;
		}
		if(account_base::callClass($interface, 'hideInCp')) {
			continue;
		}
		$list[] = [$interface, account_base::getName($interface), account_base::getIcon($interface)[0]];
	}
	$account = [];
	$account = table_common_member_account::t()->fetch_all_by_uid($_G['uid']);

	if(!empty($_G['setting']['account_plugin_atypes'])) {
		foreach($_G['setting']['account_plugin_atypes'] as $pluginid => $atype) {
			$interfaces_aType['plugin_'.$pluginid] = $atype;
		}
	}

	$account_list = $interfaces_aType;
	foreach($account as $k => $v) {
		foreach($interfaces_aType as $ik => $iv) {
			if($v['atype'] == $iv) {
				$account_list[$ik] = $v;
			}
		}
	}

	loadcache('profilesetting');
	$_G['member']['secmobile'] = authcode_field($_G['cache']['profilesetting']['mobile']['encrypt'], $_G['member']['secmobile'], 'DECODE');

	$creditExtra = !empty($_G['setting']['creditstransextra']['13']) ? $_G['setting']['creditstransextra']['13'] : $_G['setting']['creditstrans'];
	$extcredit = $_G['setting']['extcredits'][$creditExtra];

	if(defined('IN_RESTFUL')) {
		$_G['setting']['security_verify'] = dunserialize($_G['setting']['security_verify']);
	}

	include template('home/spacecp_account');
} elseif($operation == 'unbind') {
	if(getgpc('formhash') != FORMHASH) {
		showmessage('undefined_action');
	}
	if(empty($_G['member']['password'])) {
		showmessage('password_null_error', 'home.php?mod=spacecp&ac=account');
	}
	$atype = $interfaces_aType[$method];
	if(empty($atype) && str_starts_with($method, 'plugin_')) {
		$atype = account_base::getAccountType(substr($method, 7));
	}
	if(empty($atype)) {
		showmessage('account_unbind_fail', 'home.php?mod=spacecp&ac=account');
	}

	if(table_common_member_account::t()->delete_by_uid($_G['uid'], $atype)) {
		account_base::callClass($method, 'unbind', [$_G['uid']]);
		showmessage('account_unbind_success', 'home.php?mod=spacecp&ac=account');
	} else {
		showmessage('account_unbind_fail', 'home.php?mod=spacecp&ac=account');
	}
} elseif($operation == 'verifyemail') {
	if(getgpc('formhash') != FORMHASH) {
		showmessage('undefined_action');
	}
	if(empty($_G['member']['email'])) {
		showmessage('account_email_notexists');
	}
	$toemail = $_G['member']['email'];

	$mailinterval = (int)$_G['setting']['mailinterval'];
	$interval = $mailinterval > 0 ? $mailinterval : 300;
	$resend = getcookie('resendemail');
	$remain = !empty($resend) ? TIMESTAMP - $resend : $interval;
	$resend = empty($resend) || $remain > $interval;

	if($resend) {
		if(emailcheck_send($_G['uid'], $toemail)) {
			dsetcookie('resendemail', TIMESTAMP);
			showmessage('send_activate_mail_succeed', 'home.php?mod=spacecp&ac=account');
		} else {
			showmessage('send_activate_mail_error', 'home.php?mod=spacecp&ac=account', ['interval' => $interval - $remain]);
		}
	} else {
		showmessage('send_activate_mail_error', 'home.php?mod=spacecp&ac=account', ['interval' => $interval - $remain]);
	}
} elseif($operation == 'verify') {
	if(getgpc('formhash') != FORMHASH) {
		showmessage('undefined_action');
	}
	$handlekey = !empty(getgpc('handlekey')) ? getgpc('handlekey') : 'security_verify';
	$infloat = !empty(getgpc('infloat')) ? getgpc('infloat') : 'yes';
	$layerhash = !empty(getgpc('layerhash')) ? getgpc('layerhash') : '';

	$param = ['handlekey' => $handlekey, 'infloat' => $infloat, 'layerhash' => $layerhash, 'method' => $method];

	$verify_ok = false;
	$tmp_load = false;
	if($method == 'bindmobile' && !empty($_G['member']['secmobile'])) {
		showmessage('account_bind_exists');
	}
	$sign = !empty(getgpc('sign')) ? getgpc('sign') : '';
	if(!empty($sign)) {
		$uid = $_G['uid'];
		$idstring = !empty(getgpc('idstring')) ? getgpc('idstring') : '';
		$sign = !empty(getgpc('sign')) ? getgpc('sign') : '';

		if($uid && $idstring && $sign === make_sign($uid, $idstring)) {
			table_common_member_field_forum::t()->update($uid, ['authstr' => '']);
			$verify_ok = true;
			$tmp_load = true;
		} else {
			showmessage('account_sign_error');
		}
	}
	$security_verify = dunserialize($_G['setting']['security_verify']);
	$security_verify_count = count($security_verify);
	$need_security_verify = false;
	$verify = '';

	//
	$need_chg_security_verify = true;
	if($_G['uid'] && getgpc('idstring_v') && getgpc('sign_v') === make_sign($_G['uid'], getgpc('idstring_v'))) {
		$uid = $_G['uid'];
		table_common_member_field_forum::t()->update($uid, ['authstr' => '']);
		$need_security_verify = true;
		$need_chg_security_verify = false;

		if($method == 'bindmobile') {
			$verify = 'secmobile';
		} elseif($method == 'chgemail') {
			$verify = 'email';
		}
	}
	if(!$verify_ok && $security_verify_count > 0 && $need_chg_security_verify) {
		if($security_verify_count > 0) {
			$need_security_verify = true;
			$verify = !empty(getgpc('verify')) && in_array(getgpc('verify'), $security_verify) ? getgpc('verify') : '';
			if(empty($verify)) {
				$security_verify_tmp = $security_verify;
				if(empty($_G['member']['secmobile'])) {
					// 手机号为空，销毁手机验证方式
					$secmobile_index = array_search('secmobile', $security_verify);
					if($secmobile_index != '' && $secmobile_index >= 0) {
						unset($security_verify[$secmobile_index]);
					}
				}
				if(empty($_G['member']['email'])) {
					// 邮箱为空，销毁邮箱验证方式
					$email_index = array_search('email', $security_verify);
					if($email_index != '' && $email_index >= 0) {
						unset($security_verify[$email_index]);
					}
				}
				if(count($security_verify) == 0) {
					$security_verify[] = $security_verify_tmp[0];
					$verify = $security_verify_tmp[0];
					if($method == 'bindmobile' && empty($_G['member']['secmobile'])) {
						include template('home/spacecp_account_security_verify_type');
					}
				} else {
					$security_verify = array_values($security_verify);
					include template('home/spacecp_account_security_verify_type');
				}
			}
		} else {
			$need_security_verify = false;
			$verify = '';
		}

		if(!$need_security_verify && $method == 'bindmobile') {
			$need_security_verify = true;
			$verify = 'secmobile';
		} elseif(!$need_security_verify && $method == 'chgemail') {
			$need_security_verify = true;
			$verify = 'email';
		}

		if($need_security_verify && empty($verify)) {
			showmessage('account_verify_type_error');
		}
	}


	if($need_security_verify && !$verify_ok) {
		if($verify == 'secmobile') {
			if(!submitcheck('security_submit', 1)) {
				if($method != 'bindmobile' && empty($_G['member']['secmobile'])) {
					showmessage('message_secmobile_unbind_err');
				}
				include template('home/spacecp_account_security_verify');
			} else {
				$secprofile = [];
				$secmobicc = $method != 'bindmobile' ? $_G['member']['secmobicc'] : addslashes(getgpc('secmobicc'));
				$secmobile = $method != 'bindmobile' ? $_G['member']['secmobile'] : addslashes(getgpc('secmobile'));
				$secmobicc = !empty($secmobicc) ? $secmobicc : $_G['setting']['smsdefaultcc'];
				$secmobseccode = addslashes(getgpc('secmobseccode'));
				if(empty($secmobile)) {
					showmessage('message_secmobile_null_err');
				}
				if(empty($secmobseccode)) {
					showmessage('message_secmobseccode_null_err');
				}
				// 短信发送前先校验安全手机号是否正确, 避免错误安全手机号送往短信网关
				if(empty($secmobicc) || !preg_match('#^(\d){1,3}$#', $secmobicc)) {
					showmessage('profile_secmobicc_illegal');
				} else if(empty($secmobile) || !preg_match('#^(\d){1,12}$#', $secmobile)) {
					showmessage('profile_secmobile_illegal');
				}

				// 短信验证码验证, 未通过校验为 0, 通过校验为 1, 验证码验证超过有效验证次数则失效为 -1
				$secmobseccode_verify_result = sms::verify($_G['uid'], 1, $secmobicc, $secmobile, $secmobseccode);
				if($secmobseccode_verify_result == 0) {
					showmessage('message_secmobseccode_verify_err');
				} else if($secmobseccode_verify_result == -1) {
					showmessage('message_secmobseccode_invalid_err');
				} else {
					$secprofile['secmobicc'] = $secmobicc;
					$secprofile['secmobile'] = $secmobile;
					$secprofile['secmobilestatus'] = 1;
				}
				$verify_ok = true;
			}
		} elseif($verify == 'email') {
			if(!submitcheck('security_submit', 1)) {
				if($method != 'chgemail' && empty($_G['member']['email'])) {
					showmessage('message_email_unbind_err');
				}
				include template('home/spacecp_account_security_verify_email');
			} else {
				$secprofile = [];
				$email = $method != 'chgemail' ? $_G['member']['email'] : addslashes(getgpc('email'));
				$seccode = addslashes(getgpc('seccode'));
				if(empty($email)) {
					showmessage('message_email_unbind_err');
				}
				if(empty($seccode)) {
					showmessage('message_seccode_null_err');
				}
				// 短信发送前先校验安全手机号是否正确, 避免错误安全手机号送往短信网关
				if(empty($email) || !preg_match('/^[\-\.\w]+@[\.\-\w]+(\.\w+)+$/', $email)) {
					showmessage('profile_email_illegal');
				}

				// 邮件验证码验证, 未通过校验为 0, 通过校验为 1, 验证码验证超过有效验证次数则失效为 -1
				$secemailseccode_verify_result = mailcode::verify($_G['uid'], 1, $email, $seccode);
				if($secemailseccode_verify_result == 0) {
					showmessage('message_seccode_verify_err');
				} else if($secemailseccode_verify_result == -1) {
					showmessage('submit_seccode_invalid');
				} else {
					if($method == 'chgemail' && empty($_G['member']['email'])) {
						$method = 'bindemail';
					} elseif($method == 'bindmobile' && empty($_G['member']['secmobile'])) {
						$sign_arr = getsign();
						$idstring = $sign_arr['idstring'];
						$sign = $sign_arr['sign'];
						$url = 'home.php?mod=spacecp&ac=account&op=verify&method=bindmobile&formhash='.FORMHASH.'&idstring='.$idstring.'&sign='.$sign;
						if(!checkmobile()) {
							$js = '<script type="text/javascript">hideWindow("'.$handlekey.'");showWindow("bindmobile", "'.$url.'", "get", 0);</script>';
							showmessage('account_verify_success', '', [], ['alert' => 'right', 'showdialog' => count(dunserialize($_G['setting']['security_verify'])) ? 1 : 0, 'extrajs' => $js]);
						} else {
							showmessage('account_verify_success', $url, [], ['alert' => 'right', 'locationtime' => 0.1]);
						}
					}
				}
				$verify_ok = true;
			}
		} elseif($verify == 'password') {
			if(!submitcheck('security_submit', 1)) {
				include template('home/spacecp_account_security_verify_password');
			} else {
				$password = addslashes(getgpc('password'));
				if(empty($password)) {
					showmessage('message_password_null_err');
				}
				loaducenter();
				list($result) = uc_user_login($_G['uid'], $password, 1, 0);
				if($result < 0) {
					showmessage('message_password_err');
				} else {
					if($method == 'bindmobile' && empty($_G['member']['secmobile'])) {
						$sign_arr = getsign();
						$idstring = $sign_arr['idstring'];
						$sign = $sign_arr['sign'];
						$url = 'home.php?mod=spacecp&ac=account&op=verify&method=bindmobile&formhash='.FORMHASH.'&idstring='.$idstring.'&sign='.$sign;
						if(!checkmobile()) {
							$js = '<script type="text/javascript">hideWindow("'.$handlekey.'");showWindow("bindmobile", "'.$url.'", "get", 0);</script>';
							showmessage('account_verify_success', '', [], ['alert' => 'right', 'showdialog' => count(dunserialize($_G['setting']['security_verify'])) ? 1 : 0, 'extrajs' => $js]);
						} else {
							showmessage('account_verify_success', $url, [], ['alert' => 'right', 'locationtime' => 0.1]);
						}
					}
				}
				$verify_ok = true;
			}
		} else {
			$verify_ok = false;
		}
	} else {
		$verify_ok = true;
	}

	if(!$verify_ok) {
		showmessage('message_verify_err');
	}
	if($verify_ok && $method == 'bindmobile' && empty($_G['member']['secmobile']) && !submitcheck('security_submit')) {
		$sign_arr = getsign();
		$idstring_v = $sign_arr['idstring'];
		$sign_v = $sign_arr['sign'];
		include template('home/spacecp_account_security_verify');
	}
	switch($method) {
		case 'bindmobile':
			bindmobile($secprofile);
			break;
		case 'unbindmobile':
			$secprofile['secmobicc'] = '';
			$secprofile['secmobile'] = '';
			$secprofile['secmobilestatus'] = 0;
			unbindmobile($secprofile);
			break;
		case 'chgpassword':
			chgpassword($param, $tmp_load);
			break;
		case 'chgemail':
			chgemail($param, $tmp_load);
			break;
		case 'bindemail':
			bindemail($email);
			break;
		case 'chgusername':
			chgusername($param, $tmp_load);
			break;
		case 'chgquestion':
			chgquestion($param, $tmp_load);
			break;
		case 'freeze':
			freeze($param, $tmp_load);
			break;
		default:
			showmessage('message_verify_err');
			break;
	}
}


function bindmobile($secprofile) {
	global $_G;
	libfile('class/account');
	$user = table_common_member::t()->fetch_all_by_secmobile($secprofile['secmobicc'], $secprofile['secmobile']);
	$user_account = table_common_member_account::t()->fetch_by_account($secprofile['secmobile'], account::aType_phone);
	if(!empty($user) || !empty($user_account)) {
		showmessage('account_security_mobile_duplicate_error');
	}

	loaducenter();
	$ucresult = uc_user_edit(addslashes($_G['member']['loginname']), '', '', '', 1, '', '', $secprofile['secmobicc'], $secprofile['secmobile']);
	if($ucresult == -1) {
		showmessage('profile_passwd_wrong');
	} elseif($ucresult == -4) {
		showmessage('profile_email_illegal');
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal');
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate');
	} elseif($ucresult == -9) {
		showmessage('profile_secmobile_duplicate');
	}

	loadcache('profilesetting');
	if(!empty($_G['cache']['profilesetting']['mobile']['encrypt'])) {
		$secprofile['secmobile'] = authcode_field($_G['cache']['profilesetting']['mobile']['encrypt'], $secprofile['secmobile'], 'ENCODE');
	}

	table_common_member::t()->update($_G['uid'], $secprofile);
	table_common_member_profile::t()->update($_G['uid'], [
		'mobile' => $secprofile['secmobile'],
	]);

	// 将安全手机号同步给account表
	table_common_member_account::t()->insert([
		'uid' => $_G['uid'],
		'atype' => account::aType_phone,
		'account' => $secprofile['secmobile'],
		'bindname' => $_G['member']['username'],
	]);

	if(!checkmobile()) {
		$js = '<script type="text/javascript">setTimeout("location.reload();", 1500)</script>';
		showmessage('account_bind_success', '', [], ['showdialog' => 1, 'extrajs' => $js]);
	} else {
		showmessage('account_bind_success', 'home.php?mod=spacecp&ac=account');
	}
}

function unbindmobile($secprofile) {
	global $_G;
	loaducenter();
	$ucresult = uc_user_edit(addslashes($_G['member']['loginname']), '', '', '', 1, '', '', 0, 0);
	if($ucresult == -1) {
		showmessage('profile_passwd_wrong');
	} elseif($ucresult == -4) {
		showmessage('profile_email_illegal');
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal');
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate');
	} elseif($ucresult == -9) {
		showmessage('profile_secmobile_duplicate');
	}

	table_common_member::t()->update($_G['uid'], $secprofile);
	table_common_member_profile::t()->update($_G['uid'], [
		'mobile' => '',
	]);

	// 将安全手机号绑定从account表删除
	libfile('class/account');
	table_common_member_account::t()->delete_by_uid($_G['uid'], account::aType_phone);

	if(!checkmobile()) {
		$js = '<script type="text/javascript">setTimeout("location.reload();", 1500)</script>';
		showmessage('account_unbind_success', '', [], ['showdialog' => 1, 'extrajs' => $js]);
	} else {
		showmessage('account_unbind_success', 'home.php?mod=spacecp&ac=account');
	}
}

function chgpassword($param, $tmp_load = false) {
	global $_G;
	if(submitcheck('submit')) {
		$newpassword = getgpc('newpassword');
		$renewpassword = getgpc('renewpassword');

		if($newpassword != $renewpassword) {
			showmessage('profile_repasswd_illegal');
		}

		if($newpassword != addslashes($newpassword)) {
			showmessage('profile_passwd_illegal');
		}
		if($_G['setting']['pwlength']) {
			if(strlen($newpassword) < $_G['setting']['pwlength']) {
				showmessage('profile_password_tooshort', '', ['pwlength' => $_G['setting']['pwlength']]);
			}
		}
		if($_G['setting']['strongpw']) {
			$strongpw_str = [];
			if(in_array(1, $_G['setting']['strongpw']) && !preg_match('/\d+/', $newpassword)) {
				$strongpw_str[] = lang('member/template', 'strongpw_1');
			}
			if(in_array(2, $_G['setting']['strongpw']) && !preg_match('/[a-z]+/', $newpassword)) {
				$strongpw_str[] = lang('member/template', 'strongpw_2');
			}
			if(in_array(3, $_G['setting']['strongpw']) && !preg_match('/[A-Z]+/', $newpassword)) {
				$strongpw_str[] = lang('member/template', 'strongpw_3');
			}
			if(in_array(4, $_G['setting']['strongpw']) && !preg_match('/[^a-zA-z0-9]+/', $newpassword)) {
				$strongpw_str[] = lang('member/template', 'strongpw_4');
			}
			if($strongpw_str) {
				showmessage(lang('member/template', 'password_weak').implode(',', $strongpw_str));
			}
		}
		loaducenter();
		uc_user_edit(addslashes($_G['member']['loginname']), $newpassword, $newpassword, '', 1, 0);
		$password = md5(random(10));

		if(isset($_G['member']['_inarchive'])) {
			table_common_member_archive::t()->move_to_master($_G['uid']);
		}

		$data = ['password' => $password];
		if($_G['member']['freeze'] == 1) {
			$data['freeze'] = 0;
		}
		table_common_member::t()->update($_G['uid'], $data);
		if(!checkmobile()) {
			$js = '<script type="text/javascript">setTimeout("location.reload();", 1500)</script>';
			showmessage('account_change_success', '', [], ['showdialog' => 1, 'extrajs' => $js]);
		} else {
			showmessage('account_change_success', 'home.php?mod=spacecp&ac=account');
		}
	} else {
		$method = $param['method'];
		$infloat = $param['infloat'];
		$handlekey = $param['handlekey'];
		$sign_arr = getsign();
		$idstring = $sign_arr['idstring'];
		$sign = $sign_arr['sign'];

		if(!$_G['setting']['security_verify'] || $tmp_load) {
			include template('home/spacecp_account_security_chgpassword');
		} else {
			$url = 'home.php?mod=spacecp&ac=account&op=verify&method=chgpassword&formhash='.FORMHASH.'&idstring='.$idstring.'&sign='.$sign;
			if(!checkmobile()) {
				$js = '<script type="text/javascript">hideWindow("'.$handlekey.'");showWindow("chgpassword", "'.$url.'", "get", 0);</script>';
				showmessage('account_verify_success', '', [], ['alert' => 'right', 'showdialog' => count(dunserialize($_G['setting']['security_verify'])) ? 1 : 0, 'extrajs' => $js]);
			} else {
				showmessage('account_verify_success', $url, [], ['alert' => 'right', 'locationtime' => 0.1]);
			}
		}
	}

}

function chgemail($param, $tmp_load = false) {
	global $_G;
	if(submitcheck('submit')) {
		$email = getgpc('email');
		loaducenter();
		$ucresult = uc_user_checkemail($email);

		if($ucresult == -4) {
			showmessage('profile_email_illegal', '', [], ['handle' => false]);
		} elseif($ucresult == -5) {
			showmessage('profile_email_domain_illegal', '', [], ['handle' => false]);
		} elseif($ucresult == -6) {
			showmessage('profile_email_duplicate', '', [], ['handle' => false]);
		}

		$ucresult = uc_user_edit(addslashes($_G['member']['loginname']), '', '', $email, 1, '', '', '', '');
		if($ucresult == -1) {
			showmessage('profile_passwd_wrong');
		} elseif($ucresult == -4) {
			showmessage('profile_email_illegal');
		} elseif($ucresult == -5) {
			showmessage('profile_email_domain_illegal');
		} elseif($ucresult == -6) {
			showmessage('profile_email_duplicate');
		} elseif($ucresult == -9) {
			showmessage('profile_secmobile_duplicate');
		}

		table_common_member::t()->update($_G['uid'], [
			'email' => $email,
			'emailstatus' => 0,
		]);
		if(!checkmobile()) {
			$js = '<script type="text/javascript">setTimeout("location.reload();", 1500)</script>';
			showmessage('account_change_success', '', [], ['showdialog' => 1, 'extrajs' => $js]);
		} else {
			showmessage('account_change_success', 'home.php?mod=spacecp&ac=account');
		}
	} else {
		$method = $param['method'];
		$infloat = $param['infloat'];
		$handlekey = $param['handlekey'];
		$sign_arr = getsign();
		$idstring = $sign_arr['idstring'];
		$sign = $sign_arr['sign'];

		if(!$_G['setting']['security_verify'] || $tmp_load) {
			include template('home/spacecp_account_security_chgemail');
		} else {
			$url = 'home.php?mod=spacecp&ac=account&op=verify&method=chgemail&formhash='.FORMHASH.'&idstring='.$idstring.'&sign='.$sign;
			if(!checkmobile()) {
				$js = '<script type="text/javascript">hideWindow("'.$handlekey.'");showWindow("chgemail", "'.$url.'", "get", 0);</script>';
				showmessage('account_verify_success', '', [], ['alert' => 'right', 'showdialog' => count(dunserialize($_G['setting']['security_verify'])) ? 1 : 0, 'extrajs' => $js]);
			} else {
				showmessage('account_verify_success', $url, [], ['alert' => 'right', 'locationtime' => 0.1]);
			}
		}
	}

}

function bindemail($email) {
	global $_G;
	loaducenter();
	$ucresult = uc_user_checkemail($email);

	if($ucresult == -4) {
		showmessage('profile_email_illegal', '', [], ['handle' => false]);
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal', '', [], ['handle' => false]);
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate', '', [], ['handle' => false]);
	}

	$ucresult = uc_user_edit(addslashes($_G['member']['loginname']), '', '', $email, 1, '', '', '', '');
	if($ucresult == -1) {
		showmessage('profile_passwd_wrong');
	} elseif($ucresult == -4) {
		showmessage('profile_email_illegal');
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal');
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate');
	} elseif($ucresult == -9) {
		showmessage('profile_secmobile_duplicate');
	}

	table_common_member::t()->update($_G['uid'], [
		'email' => $email,
		'emailstatus' => 1,
	]);
	if(!checkmobile()) {
		$js = '<script type="text/javascript">setTimeout("location.reload();", 1500)</script>';
		showmessage('account_change_success', '', [], ['showdialog' => 1, 'extrajs' => $js]);
	} else {
		showmessage('account_change_success', 'home.php?mod=spacecp&ac=account');
	}

}


function chgusername($param, $tmp_load = false) {
	global $_G;
	$setting = $_G['setting']['chgusername'];

	if(submitcheck('submit')) {
		if($setting['max_times'] > 0 && table_common_member_username_history::t()->count_by_uid($_G['uid']) >= $setting['max_times']) {
			showmessage('account_change_username_max_times');
		}
		if($_G['member']['credits'] < $setting['credits_threshold'] &&
			!in_array($_G['member']['groupid'], (array)$setting['credits_unlimit_group'])) {
			showmessage('account_change_credits_low');
		}
		if($setting['credits_pay'] > 0) {
			$creditExtra = !empty($_G['setting']['creditstransextra']['13']) ? $_G['setting']['creditstransextra']['13'] : $_G['setting']['creditstrans'];
			$credit = getuserprofile('extcredits'.$creditExtra);
			if($credit < $setting['credits_pay']) {
				$extcredit = $_G['setting']['extcredits'][$creditExtra];
				showmessage(sprintf(lang('message', 'account_change_credits_pay_low'), $extcredit['title']));
			}
		}
		$username = getgpc('username');
		check_protect_username($username);
		loaducenter();
		uc_user_chgusername($_G['uid'], $username);
		if($setting['credits_pay'] > 0 && $username != $_G['username']) {
			updatemembercount($_G['uid'], [$creditExtra => -$setting['credits_pay']], 1, 'CHU', $_G['uid']);
		}
		if(!checkmobile()) {
			$js = '<script type="text/javascript">setTimeout("location.reload();", 1500)</script>';
			showmessage('account_change_username_success', '', [], ['showdialog' => 1, 'extrajs' => $js]);
		} else {
			showmessage('account_change_username_success', 'home.php?mod=spacecp&ac=account');
		}

	} else {
		$method = $param['method'];
		$infloat = $param['infloat'];
		$handlekey = $param['handlekey'];
		$sign_arr = getsign();
		$idstring = $sign_arr['idstring'];
		$sign = $sign_arr['sign'];

		if(!$_G['setting']['security_verify'] || $tmp_load) {
			include template('home/spacecp_account_security_chgusername');
		} else {
			$url = 'home.php?mod=spacecp&ac=account&op=verify&method=chgusername&formhash='.FORMHASH.'&idstring='.$idstring.'&sign='.$sign;
			if(!checkmobile()) {
				$js = '<script type="text/javascript">hideWindow("'.$handlekey.'");showWindow("chgusername", "'.$url.'", "get", 0);</script>';
				showmessage('account_verify_success', '', [], ['alert' => 'right', 'showdialog' => count(dunserialize($_G['setting']['security_verify'])) ? 1 : 0, 'extrajs' => $js]);
			} else {
				showmessage('account_verify_success', $url, [], ['alert' => 'right', 'locationtime' => 0.1]);
			}
		}
	}

}


function chgquestion($param, $tmp_load = false) {
	global $_G;
	if(submitcheck('submit')) {
		$questionidnew = getgpc('questionidnew');
		$answernew = getgpc('answernew');

		if($questionidnew === '') {
			$questionidnew = $answernew = '';
		}

		loaducenter();

		$ucresult = uc_user_edit(addslashes($_G['member']['loginname']), '', '', '', 1, $questionidnew, $answernew, '', '');
		if($ucresult == -1) {
			showmessage('profile_passwd_wrong');
		} elseif($ucresult == -4) {
			showmessage('profile_email_illegal');
		} elseif($ucresult == -5) {
			showmessage('profile_email_domain_illegal');
		} elseif($ucresult == -6) {
			showmessage('profile_email_duplicate');
		} elseif($ucresult == -9) {
			showmessage('profile_secmobile_duplicate');
		}

		if(!checkmobile()) {
			$js = '<script type="text/javascript">setTimeout("location.reload();", 1500)</script>';
			showmessage('account_change_success', '', [], ['showdialog' => 1, 'extrajs' => $js]);
		} else {
			showmessage('account_change_success', 'home.php?mod=spacecp&ac=account');
		}
	} else {
		$method = $param['method'];
		$infloat = $param['infloat'];
		$handlekey = $param['handlekey'];
		$sign_arr = getsign();
		$idstring = $sign_arr['idstring'];
		$sign = $sign_arr['sign'];

		if(!$_G['setting']['security_verify'] || $tmp_load) {
			include template('home/spacecp_account_security_chgquestion');
		} else {
			$url = 'home.php?mod=spacecp&ac=account&op=verify&method=chgquestion&formhash='.FORMHASH.'&idstring='.$idstring.'&sign='.$sign;
			if(!checkmobile()) {
				$js = '<script type="text/javascript">hideWindow("'.$handlekey.'");showWindow("chgquestion", "'.$url.'", "get", 0);</script>';
				showmessage('account_verify_success', '', [], ['alert' => 'right', 'showdialog' => count(dunserialize($_G['setting']['security_verify'])) ? 1 : 0, 'extrajs' => $js]);
			} else {
				showmessage('account_verify_success', $url, [], ['alert' => 'right', 'locationtime' => 0.1]);
			}
		}
	}

}

function freeze($param, $tmp_load = false) {
	global $_G;
	if(submitcheck('submit')) {
		if($_G['member']['freeze'] == 2 || $_G['member']['freeze'] == -1) {
			$status = table_common_member_validate::t()->fetch($_G['uid']);
			if($status) {
				table_common_member_validate::t()->update($_G['uid'], [
					'submitdate' => TIMESTAMP,
					'submittimes' => $status['submittimes'] + 1,
					'status' => 0,
					'message' => dhtmlspecialchars(addslashes($_POST['freezereson'])),
				]);
			} else {
				table_common_member_validate::t()->insert([
					'uid' => $_G['uid'],
					'submitdate' => TIMESTAMP,
					'moddate' => 0,
					'admin' => '',
					'submittimes' => 1,
					'status' => 0,
					'message' => dhtmlspecialchars(addslashes($_POST['freezereson'])),
					'remark' => '',
				], false, true);
			}
			manage_addnotify('verifyuser');
		}

		if(!checkmobile()) {
			$js = '<script type="text/javascript">setTimeout("location.reload();", 1500)</script>';
			showmessage('account_freeze_reason_success', '', [], ['showdialog' => 1, 'extrajs' => $js]);
		} else {
			showmessage('account_freeze_reason_success', 'home.php?mod=spacecp&ac=account');
		}
	} else {
		$method = $param['method'];
		$infloat = $param['infloat'];
		$handlekey = $param['handlekey'];
		$sign_arr = getsign();
		$idstring = $sign_arr['idstring'];
		$sign = $sign_arr['sign'];

		$validate = table_common_member_validate::t()->fetch($_G['uid']);

		if(!$_G['setting']['security_verify'] || $tmp_load) {
			include template('home/spacecp_account_security_freeze');
		} else {
			$url = 'home.php?mod=spacecp&ac=account&op=verify&method=freeze&formhash='.FORMHASH.'&idstring='.$idstring.'&sign='.$sign;
			if(!checkmobile()) {
				$js = '<script type="text/javascript">hideWindow("'.$handlekey.'");showWindow("freeze", "'.$url.'", "get", 0);</script>';
				showmessage('account_verify_success', '', [], ['alert' => 'right', 'showdialog' => count(dunserialize($_G['setting']['security_verify'])) ? 1 : 0, 'extrajs' => $js]);
			} else {
				showmessage('account_verify_success', $url, [], ['alert' => 'right', 'locationtime' => 0.1]);
			}
		}
	}

}

function getsign() {
	global $_G;
	$member = getuserbyuid($_G['uid'], 1);
	$table_ext = $member['_inarchive'] ? '_archive' : '';
	$idstring = random(6);
	C::t('common_member_field_forum'.$table_ext)->update($member['uid'], ['authstr' => "$_G[timestamp]\t1\t$idstring"]);
	require_once libfile('function/member');
	$sign = make_sign($member['uid'], $idstring);
	return ['uid' => $member['uid'], 'idstring' => $idstring, 'sign' => $sign];
}

function make_sign($uid, $idstring) {
	global $_G;
	$link = "account_security=uid={$uid}&id={$idstring}";
	return dsign($link);
}

