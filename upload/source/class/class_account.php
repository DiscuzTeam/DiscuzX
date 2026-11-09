<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class account {

	public const aType_workWx = 1;
	public const aType_dingTalk = 2;
	public const aType_feishu = 3;
	public const aType_wechatOpenid = 4;
	public const aType_weixin = 5;
	public const aType_qq = 6;
	public const aType_phone = 7;
	public const aType_douyin = 8;
	public const aType_weibo = 9;

	public const aType_discuz = 10;
	public const aType_ucenter = 11;

	private array $user;

	public function __construct($user = []) {
		$this->user = $user;
	}

	public function checkUser($user = []) {
		if($user) {
			$this->user = $user;
		}
		if(empty($this->user['account']) || empty($this->user['atype'])) {
			return false;
		}
		$account = table_common_member_account::t()->fetch_by_account($this->user['account'], $this->user['atype']);
		if(!$account) {
			return false;
		}
		$this->user['uid'] = $account['uid'];
		return true;
	}

	public function getUid() {
		return $this->user['uid'];
	}

	public function getUser($uid, $atype) {
		$account = table_common_member_account::t()->fetch_by_uid($uid, $atype);
		if(!$account) {
			return false;
		}
		return $account;
	}

	public function userLogin($cookieTime = 2592000) {
		if(!$this->user['uid']) {
			return false;
		}
		global $_G;
		require_once libfile('function/member');
		$userinfo = getuserbyuid($this->user['uid']);
		setloginstatus($userinfo, $cookieTime);

		// logger start
		if($_G['setting']['log']['login']) {
			$log = [
				'timestamp' => TIMESTAMP,
				'uid' => $userinfo['uid'],
				'type' => $this->user['type'],
				'atype' => $this->user['atype'],
				'account' => $this->user['account'],
			];
			logger('login', $userinfo, $userinfo['uid'], $log);
		}
		// logger end

		return true;
	}

	public function userRegister($user = []) {
		if($user) {
			$this->user = $user;
		}
		if(!isset($this->user['atype']) ||
			!isset($this->user['account']) ||
			!isset($this->user['username']) ||
			!isset($this->user['password'])) {
			return 'account_getuserinfo_error';
		}
		global $_G;
		if(!in_array($this->user['type'], account::getSwitch('register'))) {
			return 'register_disable';
		}

		if(member::checkExists($this->user['username'])) {
			return 'profile_username_duplicate';
		}

		$uid = uc_user_register($this->user['username'], $this->user['password'], $this->user['email'],
			'', '', $_G['clientip'], $this->user['secmobicc'] ?? '', $this->user['secmobile'] ?? '');
		if($uid <= 0) {
			if($uid == -1) {
				$msg = 'profile_username_illegal';
			} elseif($uid == -2) {
				$msg = 'profile_username_protect';
			} elseif($uid == -3) {
				$msg = 'profile_username_duplicate';
			} elseif($uid == -4) {
				$msg = 'profile_email_illegal';
			} elseif($uid == -5) {
				$msg = 'profile_email_domain_illegal';
			} elseif($uid == -6) {
				$msg = 'profile_email_duplicate';
			} elseif($uid == -9) {
				$msg = 'profile_mobile_duplicate';
			} else {
				$msg = 'undefined_action';
			}
			return $msg;
		}
		$this->user['uid'] = $uid;
		table_common_member::t()->insert($uid, $this->user['username'], $this->user['password'], $this->user['email'],
			$_G['clientip'], $this->user['groupid'] ?? $_G['setting']['newusergroupid'], [
				'credits' => explode(',', $this->user['initcredits'] ?? $_G['setting']['initcredits']),
				'profile' => [
					'realname' => $this->user['realname'] ?? '',
					'mobile' => $this->user['secmobile'] ?? '',
					...($this->user['profile'] ?? [])
				],
				'emailstatus' => $this->user['emailstatus'] ? 1 : 0
			],
			$this->user['adminid'] ?? 0,
			$this->user['port'] ?? 0,
			$this->user['secmobicc'] ?? '',
			$this->user['secmobile'] ?? '',
			$this->user['secmobilestatus'] ? 1 : 0
		);
		$this->userBind($uid);
		$this->userLogin();
		$this->setInvite($uid);
		include_once libfile('function/stat');
		updatestat('register');
		require_once libfile('cache/userstats', 'function');
		build_cache_userstats();
		return '';
	}

	public function setInvite($uid) {
		require_once libfile('function/member');
		require_once libfile('function/stat');
		$GLOBALS['_G']['setting']['regstatus'] = 99;
		$invite = getinvite();
		if($invite['id']) {
			$result = table_common_invite::t()->count_by_uid_fuid($invite['uid'], $uid);
			if(!$result) {
				$consumed = table_common_invite::t()->update_consume($invite['id'], $uid, $this->user['username'], [
					'regdateline' => $GLOBALS['_G']['timestamp'],
					'status' => 2,
				]);
				if(intval($consumed) !== 1) {
					$invite = [];
				} else {
					updatestat('invite');
				}
			} else {
				$invite = [];
			}
		}
		if($invite['uid']) {
			require_once libfile('function/friend');
			friend_make($invite['uid'], $invite['username'], false);
			dsetcookie('invite_auth', '');
		}
	}

	public function userInfoUpdate($uid) {
		table_common_member_profile::t()->update($uid, [
			'realname' => $this->user['realname'],
		]);
	}

	public function userInfoMoreUpdate($uid, $data) {
		table_common_member_profile::t()->update($uid, $data);
	}

	public function userEmailStatusUpdate($uid) {
		table_common_member::t()->update($uid, [
			'emailstatus' => 1,
		]);
	}

	public function userMobileAndEmailUpdate($info) {
		global $_G;
		$secprofile = array();
		$secprofile['secmobicc'] = $info['secmobicc'] ?? 86;
		$secprofile['secmobile'] = $info['secmobile'] ?? '';
		$secprofile['secmobilestatus'] = 1;
		$secprofile['email'] = $info['email'] ?? '';
		$secprofile['emailstatus'] = $info['mailChecked'] ? 1 : 0;

		loaducenter();
		$ucresult = uc_user_edit(addslashes($_G['member']['loginname']), '', '', $secprofile['email'], 1, '', '', $secprofile['secmobicc'], $secprofile['secmobile']);
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
		table_common_member_profile::t()->update($_G['uid'], array(
			'mobile' => $secprofile['secmobile'],
		));
	}

	public function userBind($uid, $user = []) {
		table_common_member_account::t()->insert([
			'uid' => $uid,
			'atype' => $user ? $user['atype'] : $this->user['atype'],
			'account' => $user ? $user['account'] : $this->user['account'],
			'bindname' => $user ? $user['bindname'] : $this->user['bindname'],
		]);
	}

	public static function checkSwitch($key, $value) {
		global $_G;
		$switch = &$_G['setting']['account'];
		if(!is_array($switch)) {
			return false;
		}
		if(!isset($switch[$key])) {
			return $key == 'register';
		}
		if(is_array($switch[$key])) {
			return in_array($value, $switch[$key]);
		} else {
			return $value == $switch[$key];
		}
	}

	public static function getSwitch($key) {
		global $_G;
		$isArray = !str_ends_with($key, 'Default');
		$switch = &$_G['setting']['account'];
		if(is_array($switch) && isset($switch[$key])) {
			return $isArray ? (array)$switch[$key] : (string)$switch[$key];
		}
		return $isArray ? [] : '';
	}

	public static function referer() {
		global $_G;
		return $_G['siteurl'].substr($_SERVER['REQUEST_URI'], 1);
	}

	public static function method_loginAuto() {
		foreach(self::getSwitch('loginAuto') as $_interface) {
			if(account_base::callClass($_interface, 'inEnv')) {
				account_base::callClass($_interface, 'login', [self::referer()]);
			}
		}
		if($c = self::getSwitch('loginAutoDefault')) {
			account_base::callClass($c, 'login', [self::referer()]);
		}
	}

	public static function method_loginRedirect() {
		foreach(self::getSwitch('loginRedirect') as $_interface) {
			if(account_base::callClass($_interface, 'inEnv')) {
				return account_base::callClass($_interface, 'login', [dreferer(), 1]);
			}
		}
		if($c = self::getSwitch('loginRedirectDefault')) {
			return account_base::callClass($c, 'login', [dreferer(), 1]);
		}
		return '';
	}

	public static function method_registerRedirect() {
		foreach(self::getSwitch('registerRedirect') as $_interface) {
			if(account_base::callClass($_interface, 'inEnv')) {
				return account_base::callClass($_interface, 'register', [dreferer(), 1]);
			}
		}
		if($c = self::getSwitch('registerRedirectDefault')) {
			return account_base::callClass($c, 'register', [dreferer(), 1]);
		}
		return '';
	}

}