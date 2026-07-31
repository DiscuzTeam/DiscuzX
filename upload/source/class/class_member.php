<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class logging_ctl {

	function __construct() {
		require_once libfile('function/misc');
		loaducenter();
	}

	function logging_more($questionexist, $secchecklogin2 = 0) {
		global $_G;
		if(empty($_GET['lssubmit'])) {
			return;
		}
		$auth = authcode($_GET['username']."\t".$_GET['password']."\t".($questionexist ? 1 : 0), 'ENCODE');
		$js = '<script type="text/javascript">showWindow(\'login\', \'member.php?mod=logging&action=login&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()).(!empty($_GET['cookietime']) ? '&cookietime=1' : '').'\')</script>';
		showmessage('location_login', '', ['type' => 1], ['extrajs' => $js]);
	}

	function on_login() {
		global $_G;
		if($_G['uid']) {
			$referer = dreferer();
			$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
			$param = ['username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['member']['uid']];
			showmessage('login_succeed', $referer ? $referer : './', $param, ['showdialog' => 1, 'locationtime' => true, 'extrajs' => $ucsynlogin]);
		}

		list($seccodecheck, $secqaacheck) = seccheck('login');
		if(!empty($_GET['auth'])) {
			$dauth = authcode($_GET['auth'], 'DECODE');
			list(, , , $secchecklogin2) = explode("\t", $dauth);
			if($secchecklogin2) {
				$seccodecheck = true;
			}
		}
		$seccodestatus = !empty($_GET['lssubmit']) ? false : $seccodecheck;
		$secqaastatus = !empty($_GET['lssubmit']) ? false : $secqaacheck;
		$invite = getinvite();

		if(!submitcheck('loginsubmit', 1, $seccodestatus, $secqaastatus)) {

			if(!empty($_G['setting']['account']['loginRedirect']) || !empty($_G['setting']['account']['loginRedirectDefault'])) {
				$url = account::method_loginRedirect();
				if($url) {
					$this->template = 'member/login_location';
				}
			}

			$auth = '';
			$username = !empty($_G['cookie']['loginuser']) ? dhtmlspecialchars($_G['cookie']['loginuser']) : '';

			if(!empty($_GET['auth'])) {
				list($username, $password, $questionexist) = explode("\t", authcode($_GET['auth'], 'DECODE'));
				$username = dhtmlspecialchars($username);
				$auth = dhtmlspecialchars($_GET['auth']);
			}

			$cookietimecheck = !empty($_G['cookie']['cookietime']) || !empty($_GET['cookietime']) ? 'checked="checked"' : '';

			if($seccodecheck) {
				$seccode = random(6, 1) + $seccode[0] * 1000000;
			}

			if($this->extrafile && file_exists($this->extrafile)) {
				require_once $this->extrafile;
			}

			$navtitle = lang('core', 'title_login');
			include template($this->template);

		} else {
			if(!empty($_GET['auth'])) {
				list($_GET['username'], $_GET['password']) = daddslashes(explode("\t", authcode($_GET['auth'], 'DECODE')));
			}

			$loginhash = !empty($_GET['loginhash']) && preg_match('/^\w+$/', $_GET['loginhash']) ? $_GET['loginhash'] : '';

			if(!($_G['member_loginperm'] = logincheck($_GET['username']))) {
				showmessage('login_strike');
			}
			if($_GET['fastloginfield']) {
				$_GET['loginfield'] = $_GET['fastloginfield'];
			}
			$_G['uid'] = $_G['member']['uid'] = 0;
			$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
			if(!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
				showmessage('profile_passwd_illegal');
			}
			$result = userlogin($_GET['username'], $_GET['password'], $_GET['questionid'], $_GET['answer'], $this->setting['autoidselect'] ? 'auto' : $_GET['loginfield'], $_G['clientip']);
			$uid = $result['ucresult']['uid'];

			if(!empty($_GET['lssubmit']) && ($result['ucresult']['uid'] == -3 || $seccodecheck || $secqaacheck)) {
				if($result['ucresult']['username']) {
					$_GET['username'] = $result['ucresult']['username'];
				}
				$this->logging_more($result['ucresult']['uid'] == -3);
			}

			if($result['status'] == -1) {
				if(!$this->setting['fastactivation']) {
					$auth = authcode($result['ucresult']['username']."\t".FORMHASH, 'ENCODE');
					showmessage('location_activation', 'member.php?mod='.$this->setting['regname'].'&action=activation&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()), [], ['location' => true]);
				} else {
					$init_arr = explode(',', $this->setting['initcredits']);
					$groupid = $this->setting['regverify'] ? 8 : $this->setting['newusergroupid'];

					table_common_member::t()->insert_user($uid, $result['ucresult']['username'], md5(random(10)), $result['ucresult']['email'], $_G['clientip'], $groupid, $init_arr);
					$_G['member']['lastvisit'] = TIMESTAMP;
					$result['member'] = getuserbyuid($uid);
					$result['status'] = 1;
				}
			}

			if($result['status'] > 0) {

				if($this->extrafile && file_exists($this->extrafile)) {
					require_once $this->extrafile;
				}

				setloginstatus($result['member'], $_GET['cookietime'] ? 2592000 : 0);
				checkfollowfeed();
				if($_G['group']['forcelogin']) {
					if($_G['group']['forcelogin'] == 1) {
						clearcookies();
						showmessage('location_login_force_qq');
					} elseif($_G['group']['forcelogin'] == 2 && $_GET['loginfield'] != 'email' && $_GET['username'] !== $result['ucresult']['email']) {
						clearcookies();
						showmessage('location_login_force_mail');
					}
				}

				if($_G['member']['lastip'] && $_G['member']['lastvisit']) {
					dsetcookie('lip', $_G['member']['lastip'].','.$_G['member']['lastvisit']);
				}
				table_common_member_status::t()->update($_G['uid'], ['lastip' => $_G['clientip'], 'port' => $_G['remoteport'], 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP]);
				$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

				$pwold = false;
				if($this->setting['strongpw']) {
					if(in_array(1, $this->setting['strongpw']) && !preg_match('/\d+/', $_GET['password'])) {
						$pwold = true;
					}
					if(in_array(2, $this->setting['strongpw']) && !preg_match('/[a-z]+/', $_GET['password'])) {
						$pwold = true;
					}
					if(in_array(3, $this->setting['strongpw']) && !preg_match('/[A-Z]+/', $_GET['password'])) {
						$pwold = true;
					}
					if(in_array(4, $this->setting['strongpw']) && !preg_match('/[^a-zA-z0-9]+/', $_GET['password'])) {
						$pwold = true;
					}
				}

				if($_G['member']['adminid'] != 1) {
					if($this->setting['accountguard']['loginoutofdate'] && $_G['member']['lastvisit'] && TIMESTAMP - $_G['member']['lastvisit'] > ($this->setting['accountguard']['loginoutofdatenum'] >= 1 ? (int)$this->setting['accountguard']['loginoutofdatenum'] : 90) * 86400 && $_G['member']['freeze'] != -1) {
						table_common_member::t()->update($_G['uid'], ['freeze' => 2]);
						showmessage('location_login_outofdate', 'home.php?mod=spacecp&ac=profile&op=password&resend=1&formhash='.formhash(), ['type' => 1], ['showdialog' => true, 'striptags' => false, 'locationtime' => true]);
					}

					if($this->setting['accountguard']['loginpwcheck'] && $pwold && $_G['member']['freeze'] == 0) {
						$freeze = $pwold;
						if($this->setting['accountguard']['loginpwcheck'] == 2 && $freeze) {
							table_common_member::t()->update($_G['uid'], ['freeze' => 1]);
						}
					}
				}

				$seccheckrule = &$_G['setting']['seccodedata']['rule']['login'];
				if($seccheckrule['allow'] == 2) {
					if($seccheckrule['nolocal']) {
						require_once libfile('function/misc');
						$lastipConvert = process_ipnotice(convertip($_G['member']['lastip']));
						$nowipConvert = process_ipnotice(convertip($_G['clientip']));
						if($lastipConvert != $nowipConvert && !stripos($lastipConvert, $nowipConvert) && !stripos($nowipConvert, $lastipConvert)) {
							$seccodecheck = true;
						}
					}
					if(!$seccodecheck && $seccheckrule['pwsimple'] && $pwold) {
						$seccodecheck = true;
					}
					if(!$seccodecheck && $seccheckrule['outofday'] && $_G['member']['lastvisit'] && TIMESTAMP - $_G['member']['lastvisit'] > $seccheckrule['outofday'] * 86400) {
						$seccodecheck = true;
					}
					if(!$seccodecheck && $_G['member_loginperm'] != -1 && $_G['member_loginperm'] < 4) {
						$seccodecheck = true;
					}
					if(!$seccodecheck && $seccheckrule['numiptry']) {
						$seccodecheck = failedipcheck($seccheckrule['numiptry'], $seccheckrule['timeiptry']);
					}
					if($seccodecheck && !$secchecklogin2) {
						clearcookies();
						$auth = authcode($_GET['username']."\t".$_GET['password']."\t".($_GET['questionid'] ? 1 : 0)."\t1", 'ENCODE', $_G['config']['security']['authkey']);
						$location = 'member.php?mod=logging&action=login&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()).(!empty($_GET['cookietime']) ? '&cookietime=1' : '');
						if(defined('IN_MOBILE')) {
							showmessage('login_seccheck2', $location);
						} else {
							$js = '<script type="text/javascript">location.href=\''.$location.'\'</script>';
							showmessage('login_seccheck2', '', ['type' => 1], ['extrajs' => $js]);
						}
					}
				}

				if($invite['id']) {
					$result = table_common_invite::t()->count_by_uid_fuid($invite['uid'], $uid);
					if(!$result) {
						table_common_invite::t()->update($invite['id'], ['fuid' => $uid, 'fusername' => $_G['username']]);
						updatestat('invite');
					} else {
						$invite = [];
					}
				}
				if($invite['uid']) {
					require_once libfile('function/friend');
					friend_make($invite['uid'], $invite['username'], false);
					dsetcookie('invite_auth', '');
				}

				$param = [
					'username' => $result['ucresult']['username'],
					'usergroup' => $_G['group']['grouptitle'],
					'uid' => $_G['member']['uid'],
					'groupid' => $_G['groupid'],
					'syn' => $ucsynlogin ? 1 : 0
				];

				$extra = [
					'showdialog' => true,
					'locationtime' => true,
					'extrajs' => $ucsynlogin
				];

				if(!$freeze || !$this->setting['accountguard']['loginpwcheck']) {
					$loginmessage = $_G['groupid'] == 8 ? 'login_succeed_inactive_member' : 'login_succeed';
					$location = $invite || $_G['groupid'] == 8 ? 'home.php?mod=spacecp&ac=profile&op=password' : dreferer();
				} else {
					$loginmessage = 'login_succeed_password_change';
					$location = 'home.php?mod=spacecp&ac=profile&op=password';
					$_GET['lssubmit'] = 0;
				}

				if($_G['setting']['log']['login']) {
					$log = [
						'timestamp' => TIMESTAMP,
						'uid' => $_G['member']['uid'],
						'type' => 'username',
						'atype' => '',
						'account' => '',
					];
					logger('login', $_G['member'], $_G['member']['uid'], $log);
				}

				if(empty($_GET['handlekey']) || !empty($_GET['lssubmit'])) {
					if(defined('IN_MOBILE')) {
						showmessage($loginmessage, $location, $param, ['location' => true]);
					} else {
						if(!empty($_GET['lssubmit'])) {
							if(!$ucsynlogin) {
								$extra['location'] = true;
							}
							showmessage($loginmessage, $location, $param, $extra);
						} else {
							$href = str_replace("'", "\'", $location);
							showmessage('location_login_succeed', $location, [],
								[
									'showid' => 'succeedmessage',
									'extrajs' => '<script type="text/javascript">'.
										'setTimeout("window.location.href =\''.$href.'\';", 3000);'.
										'$(\'succeedmessage_href\').href = \''.$href.'\';'.
										'$(\'main_message\').style.display = \'none\';'.
										'$(\'main_succeed\').style.display = \'\';'.
										'$(\'succeedlocation\').innerHTML = \''.lang('message', $loginmessage, $param).'\';</script>'.$ucsynlogin,
									'striptags' => false,
									'showdialog' => true
								]
							);
						}
					}
				} else {
					showmessage($loginmessage, $location, $param, $extra);
				}
			} else {
				$password = preg_replace('/^(.{'.round(strlen($_GET['password']) / 4).'})(.+?)(.{'.round(strlen($_GET['password']) / 6).'})$/s', "\\1***\\3", $_GET['password']);
				if($_G['setting']['log']['illegal']) {
					$errorlog = [
						'logintime' => TIMESTAMP,
						'username' => ($result['ucresult']['username'] ? $result['ucresult']['username'] : $_GET['username']),
						'password' => $password,
						'questionid' => 'Ques #'.intval($_GET['questionid']),
						'clientip' => $_G['clientip']
					];
					$member_log = $result['ucresult']['username'] ? $result['ucresult'] : ['uid' => 0, 'username' => $_GET['username']];
					logger('illegal', $member_log, $member_log['uid'], $errorlog);
				}

				loginfailed($_GET['username']);
				failedip();
				$fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_GET['questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
				if($_G['member_loginperm'] > 1) {
					showmessage($fmsg, '', ['loginperm' => $_G['member_loginperm'] - 1]);
				} elseif($_G['member_loginperm'] == -1) {
					showmessage('login_password_invalid');
				} else {
					showmessage('login_strike');
				}
			}

		}

	}

	function on_logout() {
		global $_G;

		$ucsynlogout = $this->setting['allowsynlogin'] ? uc_user_synlogout() : '';

		if($_GET['formhash'] != $_G['formhash']) {
			showmessage('logout_succeed', $_G['siteurl'], ['formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout, 'referer' => rawurlencode($_G['siteurl'])]);
		}

		clearcookies();
		$_G['groupid'] = $_G['member']['groupid'] = 7;
		$_G['uid'] = $_G['member']['uid'] = 0;
		$_G['username'] = $_G['member']['username'] = $_G['loginname'] = $_G['member']['loginname'] = $_G['member']['password'] = '';
		$_G['setting']['styleid'] = $this->setting['styleid'];

		if(defined('IN_MOBILE')) {
			showmessage('location_logout_succeed_mobile', $_G['siteurl'], ['formhash' => FORMHASH, 'referer' => rawurlencode($_G['siteurl'])]);
		} else {
			showmessage('logout_succeed', $_G['siteurl'], ['formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout, 'referer' => rawurlencode($_G['siteurl'])]);
		}
	}

}

class register_ctl {

	var $showregisterform = 1;

	function __construct() {
		global $_G;
		if($_G['setting']['bbclosed']) {
			if(($_GET['action'] != 'activation' && !$_GET['activationauth']) || !$_G['setting']['closedallowactivation']) {
				showmessage('register_disable', NULL, [], ['login' => 1]);
			}
		}

		loadcache(['modreasons', 'stamptypeid', 'fields_required', 'fields_optional', 'fields_register', 'ipctrl']);
		require_once libfile('function/misc');
		require_once libfile('function/profile');
		if(!function_exists('sendmail')) {
			include libfile('function/mail');
		}
		loaducenter();
	}

	function on_register() {
		global $_G;

		$_G['setting']['forgeemail'] = $_G['setting']['regverify'] != 1;

		if(!empty($_G['setting']['account']['registerRedirect']) || !empty($_G['setting']['account']['registerRedirectDefault'])) {
			if((empty($_GET['fromAccount']) || $_GET['fromAccount'] != formhash()) && empty($_G['cookie']['accountUDAuth'])) {
				$url = account::method_registerRedirect();
				if($url) {
					$this->template = 'member/login_location';
				}
			}
		}

		$_GET['username'] = trim($_GET[''.$this->setting['reginput']['username']]);
		$_GET['password'] = $_GET[''.$this->setting['reginput']['password']];
		$_GET['password2'] = $_GET[''.$this->setting['reginput']['password2']];
		$_GET['email'] = $_GET[''.$this->setting['reginput']['email']];

		if($_G['uid']) {
			$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
			$url_forward = dreferer();
			if(str_contains($url_forward, $this->setting['regname'])) {
				$url_forward = 'index.php';
			}
			showmessage('login_succeed', $url_forward ? $url_forward : './', ['username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']], ['extrajs' => $ucsynlogin]);
		} elseif(!$this->setting['regclosed'] && (!$this->setting['regstatus'] || !$this->setting['ucactivation'])) {
			if($_GET['action'] == 'activation' || $_GET['activationauth']) {
				if(!$this->setting['ucactivation'] && !$this->setting['closedallowactivation']) {
					showmessage('register_disable_activation');
				}
			} elseif(!$this->setting['regstatus']) {
				showmessage(!$this->setting['regclosemessage'] ? 'register_disable' : str_replace(["\r", "\n"], '', $this->setting['regclosemessage']));
			}
		}

		$bbrules = &$this->setting['bbrules'];
		$bbrulesforce = &$this->setting['bbrulesforce'];
		$bbrulestxt = &$this->setting['bbrulestxt'];
		$welcomemsg = &$this->setting['welcomemsg'];
		$welcomemsgtitle = &$this->setting['welcomemsgtitle'];
		$welcomemsgtxt = &$this->setting['welcomemsgtxt'];
		$regname = $this->setting['regname'];

		if($this->setting['regverify']) {
			if($this->setting['areaverifywhite']) {
				$location = $whitearea = '';
				$location = trim(convertip($_G['clientip']));
				if($location) {
					$whitearea = preg_quote(trim($this->setting['areaverifywhite']), '/');
					$whitearea = str_replace(["\\*"], ['.*'], $whitearea);
					$whitearea = '.*'.$whitearea.'.*';
					$whitearea = '/^('.str_replace(["\r\n", ' '], ['.*|.*', ''], $whitearea).')$/i';
					if(@preg_match($whitearea, $location)) {
						$this->setting['regverify'] = 0;
					}
				}
			}

			if($_G['cache']['ipctrl']['ipverifywhite']) {
				foreach(explode("\n", $_G['cache']['ipctrl']['ipverifywhite']) as $ctrlip) {
					if(preg_match('/^('.preg_quote(($ctrlip = trim($ctrlip)), '/').')/', $_G['clientip'])) {
						$this->setting['regverify'] = 0;
						break;
					}
				}
			}
		}

		$invitestatus = false;
		if($this->setting['regstatus'] == 2) {
			if($this->setting['inviteconfig']['inviteareawhite']) {
				$location = $whitearea = '';
				$location = trim(convertip($_G['clientip']));
				if($location) {
					$whitearea = preg_quote(trim($this->setting['inviteconfig']['inviteareawhite']), '/');
					$whitearea = str_replace(["\\*"], ['.*'], $whitearea);
					$whitearea = '.*'.$whitearea.'.*';
					$whitearea = '/^('.str_replace(["\r\n", ' '], ['.*|.*', ''], $whitearea).')$/i';
					if(@preg_match($whitearea, $location)) {
						$invitestatus = true;
					}
				}
			}

			if($this->setting['inviteconfig']['inviteipwhite']) {
				foreach(explode("\n", $this->setting['inviteconfig']['inviteipwhite']) as $ctrlip) {
					if(preg_match('/^('.preg_quote(($ctrlip = trim($ctrlip)), '/').')/', $_G['clientip'])) {
						$invitestatus = true;
						break;
					}
				}
			}
		}

		$groupinfo = [];
		if($this->setting['regverify']) {
			$groupinfo['groupid'] = 8;
		} else {
			$groupinfo['groupid'] = $this->setting['newusergroupid'];
		}

		list($seccodecheck, $secqaacheck) = seccheck('register');
		$fromuid = !empty($_G['cookie']['promotion']) && $this->setting['creditspolicy']['promotion_register'] ? intval($_G['cookie']['promotion']) : 0;
		$username = $_GET['username'] ?? '';
		$bbrulehash = $bbrules ? substr(md5(FORMHASH), 0, 8) : '';
		$auth = $_GET['auth'];

		if(!$invitestatus) {
			$invite = getinvite();
		}
		$paramexist = preg_match_all('/(\?|&(amp)?(;)?)(.+?)=([^&?]*)/i', $_SERVER['QUERY_STRING'], $parammatchs);
		if($paramexist) {
			foreach($parammatchs[5] as $paramk => $paramv) {
				$param[$parammatchs[4][$paramk]] = $paramv;
			}
		}
		$gethash = $_GET['hash'] ?? $param['hash'];
		$email = $_GET['email'] ?? $param['email'];
		$sendurl = (bool)$this->setting['sendregisterurl'];
		if($sendurl) {
			if(!empty($gethash)) {
				$gethash = preg_replace('/[^\[A-Za-z0-9_\]%\s+-\/=]/', '', $gethash);
				$hash = explode("\t", authcode($gethash, 'DECODE', $_G['config']['security']['authkey']));
				if(is_array($hash) && isemail($hash[0]) && TIMESTAMP - $hash[1] < 259200) {
					$sendurl = false;
				}
			}
		}
		if(!submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck)) {

			if($_GET['action'] == 'activation') {
				$auth = explode("\t", authcode($auth, 'DECODE'));
				if(FORMHASH != $auth[1]) {
					showmessage('register_activation_invalid', 'member.php?mod=logging&action=login');
				}
				$username = $auth[0];
				$activationauth = authcode("$auth[0]\t".FORMHASH, 'ENCODE');
				$sendurl = false;
			}

			if(!$sendurl) {

				if($fromuid) {
					$member = getuserbyuid($fromuid);
					if(!empty($member)) {
						$fromuser = dhtmlspecialchars($member['username']);
					} else {
						dsetcookie('promotion');
					}
				}

				if($_GET['action'] == 'activation') {
					$auth = dhtmlspecialchars($auth);
				}

				if($seccodecheck) {
					$seccode = random(6, 1);
				}

				$username = dhtmlspecialchars($username);

				$htmls = $settings = [];
				foreach($_G['cache']['fields_register'] as $field) {
					$fieldid = $field['fieldid'];
					$html = profile_setting($fieldid, [], false, false, true, defined('IN_MOBILE'));
					if($html) {
						$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
						$htmls[$fieldid] = $html;
					}
				}

				$navtitle = $this->setting['reglinkname'];

				if($this->extrafile && file_exists($this->extrafile)) {
					require_once $this->extrafile;
				}
			} else {
				$navtitle = $this->setting['reglinkname'];
			}
			$bbrulestxt = nl2br("\n$bbrulestxt\n\n");
			$dreferer = dreferer();

			include template($this->template);

		} else {

			$activationauth = [];
			if(isset($_GET['activationauth']) && $_GET['activationauth']) {
				$activationauth = explode("\t", authcode($_GET['activationauth'], 'DECODE'));
				if($activationauth[1] != FORMHASH) {
					showmessage('register_activation_invalid', 'member.php?mod=logging&action=login');
				}
				$sendurl = false;
			}
			if(!$activationauth) {
				$email = $email ? $email : $_GET['email'];
				checkemail($email);
			}
			if($sendurl) {
				$mobile = $this->setting['mobile']['mobileregister'] ? '' : ($this->setting['mobile']['allowmobile'] ? '&amp;mobile=no' : '');
				$hashstr = urlencode(authcode("{$email}\t{$_G['timestamp']}", 'ENCODE', $_G['config']['security']['authkey']));
				$registerurl = $_G['setting']['securesiteurl'].'member.php?mod='.$this->setting['regname']."&amp;hash={$hashstr}&amp;email={$email}{$mobile}";
				$email_register_message = [
					'tpl' => 'email_register',
					'var' => [
						'bbname' => $this->setting['bbname'],
						'siteurl' => $_G['setting']['securesiteurl'],
						'url' => $registerurl
					]
				];
				if(!sendmail("{$email} <{$email}>", $email_register_message)) {
					runlog('sendmail', "{$email} sendmail failed.");
				}
				showmessage('register_email_send_succeed', dreferer(), ['bbname' => $this->setting['bbname']], ['showdialog' => false, 'msgtype' => 3, 'closetime' => 10]);
			}
			$emailstatus = 0;
			if($this->setting['sendregisterurl'] && !$sendurl) {
				$email = strtolower($hash[0]);
				$this->setting['regverify'] = $this->setting['regverify'] == 1 ? 0 : $this->setting['regverify'];
				if(!$this->setting['regverify']) {
					$groupinfo['groupid'] = $this->setting['newusergroupid'];
				}
				$emailstatus = 1;
			}

			if($this->setting['regstatus'] == 2 && empty($invite) && !$invitestatus) {
				showmessage('not_open_registration_invite');
			}

			if($bbrules && $bbrulehash != $_POST['agreebbrule']) {
				showmessage('register_rules_agree');
			}

			$activation = [];
			if(isset($_GET['activationauth']) && $activationauth && is_array($activationauth)) {
				if($activationauth[1] == FORMHASH && !($activation = uc_get_user($activationauth[0]))) {
					showmessage('register_activation_invalid', 'member.php?mod=logging&action=login');
				}
			}

			if(!$activation) {
				$usernamelen = dstrlen($username);
				if($usernamelen < 3) {
					showmessage('profile_username_tooshort');
				} elseif($usernamelen > 50) {
					showmessage('profile_username_toolong');
				}
				if(member::checkExists($username)) {
					if($_G['inajax']) {
						showmessage('profile_username_duplicate');
					} else {
						showmessage('register_activation_message', 'member.php?mod=logging&action=login', ['username' => $username]);
					}
				}
				if($this->setting['pwlength']) {
					if(strlen($_GET['password']) < $this->setting['pwlength']) {
						showmessage('profile_password_tooshort', '', ['pwlength' => $this->setting['pwlength']]);
					}
				}
				if($this->setting['strongpw']) {
					$strongpw_str = [];
					if(in_array(1, $this->setting['strongpw']) && !preg_match('/\d+/', $_GET['password'])) {
						$strongpw_str[] = lang('member/template', 'strongpw_1');
					}
					if(in_array(2, $this->setting['strongpw']) && !preg_match('/[a-z]+/', $_GET['password'])) {
						$strongpw_str[] = lang('member/template', 'strongpw_2');
					}
					if(in_array(3, $this->setting['strongpw']) && !preg_match('/[A-Z]+/', $_GET['password'])) {
						$strongpw_str[] = lang('member/template', 'strongpw_3');
					}
					if(in_array(4, $this->setting['strongpw']) && !preg_match('/[^a-zA-z0-9]+/', $_GET['password'])) {
						$strongpw_str[] = lang('member/template', 'strongpw_4');
					}
					if($strongpw_str) {
						showmessage(lang('member/template', 'password_weak').implode(',', $strongpw_str));
					}
				}
				$email = strtolower(trim($email));
				if($this->setting['regverify'] == 1 && !$email) {
					showmessage('profile_email_illegal');
				}
				if(empty($this->setting['ignorepassword'])) {
					if($_GET['password'] !== $_GET['password2']) {
						showmessage('profile_passwd_notmatch');
					}

					if(!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
						showmessage('profile_passwd_illegal');
					}
					$password = $_GET['password'];
				} else {
					$password = md5(random(10));
				}
			}

			check_protect_username($username);

			if($this->setting['regverify'] == 2 && !trim($_GET['regmessage'])) {
				showmessage('profile_required_info_invalid');
			}

			if($_G['cache']['ipctrl']['ipregctrl']) {
				foreach(explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {
					if(preg_match('/^('.preg_quote(($ctrlip = trim($ctrlip)), '/').')/', $_G['clientip'])) {
						$ctrlip = $ctrlip.'%';
						$this->setting['regctrl'] = $this->setting['ipregctrltime'];
						break;
					} else {
						$ctrlip = $_G['clientip'];
					}
				}
			} else {
				$ctrlip = $_G['clientip'];
			}

			if($this->setting['regctrl']) {
				if(table_common_regip::t()->count_by_ip_dateline($ctrlip, $_G['timestamp'] - $this->setting['regctrl'] * 3600)) {
					showmessage('register_ctrl', NULL, ['regctrl' => $this->setting['regctrl']]);
				}
			}

			$setregip = null;
			if($this->setting['regfloodctrl']) {
				$regip = table_common_regip::t()->fetch_by_ip_dateline($_G['clientip'], $_G['timestamp'] - 86400);
				if($regip) {
					if($regip['count'] >= $this->setting['regfloodctrl']) {
						showmessage('register_flood_ctrl', NULL, ['regfloodctrl' => $this->setting['regfloodctrl']]);
					} else {
						$setregip = 1;
					}
				} else {
					$setregip = 2;
				}
			}

			$profile = $verifyarr = [];
			foreach($_G['cache']['fields_register'] as $field) {
				$field_key = $field['fieldid'];
				$field_val = $_GET[''.$field_key];
				if($field['formtype'] == 'file' && !empty($_FILES[$field_key]) && $_FILES[$field_key]['error'] == 0) {
					$field_val = true;
				}

				if(!profile_check($field_key, $field_val)) {
					$showid = !in_array($field['fieldid'], ['birthyear', 'birthmonth']) ? $field['fieldid'] : 'birthday';
					showmessage($field['title'].lang('message', 'profile_illegal'), '', [], [
						'showid' => 'chk_'.$showid,
						'extrajs' => $field['title'].lang('message', 'profile_illegal').($field['formtype'] == 'text' ? '<script type="text/javascript">'.
								'$(\'registerform\').'.$field['fieldid'].'.className = \'px er\';'.
								'$(\'registerform\').'.$field['fieldid'].'.onblur = function () { if(this.value != \'\') {this.className = \'px\';$(\'chk_'.$showid.'\').innerHTML = \'\';}}'.
								'</script>' : '')
					]);
				}

				if($field['needverify']) {
					$verifyarr[$field_key] = $field_val;
				} else {
					$profile[$field_key] = $field_val;
				}
			}

			if(!$activation) {
				$uid = uc_user_register(addslashes($username), $password, $email, $questionid, $answer, $_G['clientip'], $secprofile['secmobicc'], $secprofile['secmobile']);
				if($uid <= 0) {
					if($uid == -1) {
						showmessage('profile_username_illegal');
					} elseif($uid == -2) {
						showmessage('profile_username_protect');
					} elseif($uid == -3) {
						showmessage('profile_username_duplicate');
					} elseif($uid == -4) {
						showmessage('profile_email_illegal');
					} elseif($uid == -5) {
						showmessage('profile_email_domain_illegal');
					} elseif($uid == -6) {
						showmessage('profile_email_duplicate');
					} elseif($uid == -9) {
						showmessage('profile_mobile_duplicate');
					} else {
						showmessage('undefined_action');
					}
				}
			} else {
				list($uid, $username, $email) = $activation;
			}
			$_G['username'] = $username;
			if(getuserbyuid($uid, 1)) {
				if(!$activation) {
					uc_user_delete($uid);
				}
				showmessage('profile_uid_duplicate', '', ['uid' => $uid]);
			}

			$password = md5(random(10));
			$secques = $questionid > 0 ? random(8) : '';

			if(isset($_POST['birthmonth']) && isset($_POST['birthday'])) {
				$profile['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);
			}
			if(isset($_POST['birthyear'])) {
				$profile['zodiac'] = get_zodiac($_POST['birthyear']);
			}

			if($_FILES) {
				$upload = new discuz_upload();

				foreach($_FILES as $key => $file) {
					$field_key = 'field_'.$key;
					if(!empty($_G['cache']['fields_register'][$field_key]) && $_G['cache']['fields_register'][$field_key]['formtype'] == 'file') {

						$upload->init($file, 'profile');
						$attach = $upload->attach;

						if(!$upload->error()) {
							$upload->save();

							if(!$upload->get_image_info($attach['target'])) {
								@unlink($attach['target']);
								continue;
							}

							$attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));
							if($_G['cache']['fields_register'][$field_key]['needverify']) {
								$verifyarr[$key] = $attach['attachment'];
							} else {
								$profile[$key] = $attach['attachment'];
							}
						}
					}
				}
			}

			if($setregip !== null) {
				if($setregip == 1) {
					table_common_regip::t()->update_count_by_ip($_G['clientip']);
				} else {
					table_common_regip::t()->insert(['ip' => $_G['clientip'], 'count' => 1, 'dateline' => $_G['timestamp']]);
				}
			}

			if($invite && $this->setting['inviteconfig']['invitegroupid']) {
				$groupinfo['groupid'] = $this->setting['inviteconfig']['invitegroupid'];
			}

			$init_arr = ['credits' => explode(',', $this->setting['initcredits']), 'profile' => $profile, 'emailstatus' => $emailstatus];

			table_common_member::t()->insert_user($uid, $username, $password, $email, $_G['clientip'], $groupinfo['groupid'], $init_arr, 0, $_G['remoteport']);
			if($emailstatus) {
				updatecreditbyaction('realemail', $uid);
			}
			if($verifyarr) {
				$setverify = [
					'uid' => $uid,
					'username' => $username,
					'verifytype' => '0',
					'field' => serialize($verifyarr),
					'dateline' => TIMESTAMP,
				];
				table_common_member_verify_info::t()->insert($setverify);
				table_common_member_verify::t()->insert(['uid' => $uid]);
			}

			require_once libfile('cache/userstats', 'function');
			build_cache_userstats();

			if($this->extrafile && file_exists($this->extrafile)) {
				require_once $this->extrafile;
			}

			if($this->setting['regctrl'] || $this->setting['regfloodctrl']) {
				table_common_regip::t()->delete_by_dateline($_G['timestamp'] - ($this->setting['regctrl'] > 72 ? $this->setting['regctrl'] : 72) * 3600);
				if($this->setting['regctrl']) {
					table_common_regip::t()->insert(['ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']]);
				}
			}

			$regmessage = dhtmlspecialchars($_GET['regmessage']);
			if($this->setting['regverify'] == 2) {
				table_common_member_validate::t()->insert([
					'uid' => $uid,
					'submitdate' => $_G['timestamp'],
					'moddate' => 0,
					'admin' => '',
					'submittimes' => 1,
					'status' => 0,
					'message' => $regmessage,
					'remark' => '',
				], false, true);
				manage_addnotify('verifyuser');
			}

			setloginstatus([
				'uid' => $uid,
				'username' => $_G['username'],
				'password' => $password,
				'groupid' => $groupinfo['groupid'],
			], 0);
			include_once libfile('function/stat');
			updatestat('register');

			if($invite['id']) {
				$result = table_common_invite::t()->count_by_uid_fuid($invite['uid'], $uid);
				if(!$result) {
					table_common_invite::t()->update($invite['id'], ['fuid' => $uid, 'fusername' => $_G['username'], 'regdateline' => $_G['timestamp'], 'status' => 2]);
					updatestat('invite');
				} else {
					$invite = [];
				}
			}
			if($invite['uid']) {
				if($this->setting['inviteconfig']['inviteaddcredit']) {
					updatemembercount($uid, [$this->setting['inviteconfig']['inviterewardcredit'] => $this->setting['inviteconfig']['inviteaddcredit']]);
				}
				if($this->setting['inviteconfig']['invitedaddcredit']) {
					updatemembercount($invite['uid'], [$this->setting['inviteconfig']['inviterewardcredit'] => $this->setting['inviteconfig']['invitedaddcredit']]);
				}
				require_once libfile('function/friend');
				friend_make($invite['uid'], $invite['username'], false);
				notification_add($invite['uid'], 'friend', 'invite_friend', ['actor' => '<a href="home.php?mod=space&uid='.$invite['uid'].'" target="_blank">'.$invite['username'].'</a>'], 1);

				space_merge($invite, 'field_home');
				if(!empty($invite['privacy']['feed']['invite'])) {
					require_once libfile('function/feed');
					$tite_data = ['username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$_G['username'].'</a>'];
					feed_add('friend', 'feed_invite', $tite_data, '', [], '', [], [], '', '', '', 0, 0, '', $invite['uid'], $invite['username']);
				}
			}

			if($welcomemsg && !empty($welcomemsgtxt)) {
				$welcomemsgtitle = replacesitevar($welcomemsgtitle);
				$welcomemsgtxt = replacesitevar($welcomemsgtxt);
				if($welcomemsg == 1) {
					$welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
					notification_add($uid, 'system', $welcomemsgtxt, ['from_id' => 0, 'from_idtype' => 'welcomemsg'], 1);
				} elseif($welcomemsg == 2) {
					sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);
				} elseif($welcomemsg == 3) {
					sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);
					$welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
					notification_add($uid, 'system', $welcomemsgtxt, ['from_id' => 0, 'from_idtype' => 'welcomemsg'], 1);
				}
			}

			if($fromuid) {
				updatecreditbyaction('promotion_register', $fromuid);
				dsetcookie('promotion', '');
			}
			dsetcookie('loginuser', '');
			dsetcookie('activationauth', '');
			dsetcookie('invite_auth', '');

			account_base::call('bind', [$uid], 'register');

			$url_forward = dreferer();
			$refreshtime = 3000;
			switch($this->setting['regverify']) {
				case 1:
					require_once libfile('function/spacecp');
					emailcheck_send($_G['uid'], $email);
					dsetcookie('resendemail', TIMESTAMP);
					$message = 'register_email_verify';
					$locationmessage = 'register_email_verify_location';
					$refreshtime = 10000;
					break;
				case 2:
					$message = 'register_manual_verify';
					$locationmessage = 'register_manual_verify_location';
					break;
				default:
					$message = 'register_succeed';
					$locationmessage = 'register_succeed_location';
					break;
			}
			$param = ['bbname' => $this->setting['bbname'], 'username' => $_G['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']];
			if(str_contains($url_forward, $this->setting['regname']) || str_contains($url_forward, 'buyinvitecode')) {
				$url_forward = 'index.php';
			}
			$href = str_replace("'", "\'", $url_forward);
			$extra = [
				'showid' => 'succeedmessage',
				'extrajs' => '<script type="text/javascript">'.
					'setTimeout("window.location.href =\''.$href.'\';", '.$refreshtime.');'.
					'$(\'succeedmessage_href\').href = \''.$href.'\';'.
					'$(\'main_message\').style.display = \'none\';'.
					'$(\'main_succeed\').style.display = \'\';'.
					'$(\'succeedlocation\').innerHTML = \''.lang('message', $locationmessage).'\';'.
					'</script>',
				'striptags' => false,
			];
			showmessage($message, $url_forward, $param, $extra);
		}
	}

}

class crime_action_ctl {

	static $actions = ['all', 'crime_delpost', 'crime_warnpost', 'crime_banpost', 'crime_banspeak', 'crime_banvisit', 'crime_banstatus', 'crime_avatar', 'crime_sightml', 'crime_customstatus', 'members_ban_none'];

	function __construct() {
	}

	public static function &instance() {
		static $object;
		if(empty($object)) {
			$object = new crime_action_ctl();
		}
		return $object;
	}

	function recordaction($uid, $action, $reason) {
		global $_G;

		$uid = intval($uid);
		$key = array_search($action, self::$actions);
		if($key === FALSE) {
			return false;
		}
		$insert = [
			'uid' => $uid,
			'operatorid' => $_G['uid'],
			'operator' => $_G['username'],
			'action' => $key,
			'reason' => $reason,
			'dateline' => $_G['timestamp']
		];
		table_common_member_crime::t()->insert($insert);
		return true;
	}

	function getactionlist($uid) {
		$uid = intval($uid);
		$clist = [];
		foreach(table_common_member_crime::t()->fetch_all_by_uid($uid) as $c) {
			$c['action'] = self::$actions[$c['action']];
			$clist[] = $c;
		}
		return $clist;
	}

	function getcount($uid, $action) {
		$uid = intval($uid);
		$key = array_search($action, self::$actions);
		if($key === FALSE) {
			return 0;
		}
		return table_common_member_crime::t()->count_by_uid_action($uid, $key);
	}

	function search($action, $username, $operator, $starttime, $endtime, $reason, $start, $limit) {
		$action = intval($action);
		$operator = daddslashes(trim($operator));
		$starttime = $starttime ? strtotime($starttime) : 0;
		$endtime = $endtime ? (strtotime($endtime) + 3600 * 24) : 0;
		$reason = daddslashes(trim($reason));
		$start = intval($start);
		$limit = intval($limit);

		if(!empty($username)) {
			$uid = table_common_member::t()->fetch_uid_by_username($username);
			$wheresql[] = "uid='$uid'";
		}

		if($action) {
			$wheresql[] = "action='$action'";
		}
		if($operator) {
			$wheresql[] = "operator='$operator'";
		}
		if($starttime) {
			$wheresql[] = "dateline>='$starttime'";
		}
		if($endtime) {
			$wheresql[] = "dateline<='$endtime'";
		}
		if($reason) {
			$wheresql[] = "reason LIKE '%$reason%'";
		}

		if($wheresql) {
			$wheresql = 'WHERE '.implode(' AND ', $wheresql);
		} else {
			$wheresql = '';
		}
		$clist = [];
		$count = table_common_member_crime::t()->count_by_where($wheresql);

		if($count) {
			$uids = [];
			foreach(table_common_member_crime::t()->fetch_all_by_where($wheresql, $start, $limit) as $crime) {
				$crime['action'] = self::$actions[$crime['action']];
				$clist[] = $crime;
				$uids[$crime['uid']] = $crime['uid'];
			}
			$members = table_common_member::t()->fetch_all($uids, false, 0);
			foreach($clist as $key => $crime) {
				$crime['username'] = $members[$crime['uid']]['username'];
				$clist[$key] = $crime;
			}
		}
		return [$count, $clist];
	}
}

class member {

	public static function checkExists($username) {
		$historyExists = table_common_member_username_history::t()->fetch($username);
		$memberExists = table_common_member::t()->fetch_uid_by_username($username) || table_common_member_archive::t()->fetch_uid_by_username($username);

		if($historyExists || $memberExists) {
			return true;
		}

		loaducenter();

		return uc_get_user(addslashes($username)) && !$memberExists;
	}

}

