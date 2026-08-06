<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class account_wechat extends account_base {
	private array $token = [];
	private string $code = '';

	private array $conf = [];

	private const getUserByIDKey = 'weChatUser_';
	private const getUserByIDTTL = 3600;

	public function __construct() {
		parent::autoload();
		global $_G;
		$this->conf = $_G['setting']['wechat'] ?? [];

	}

	public function notificationAdd($touid, $note, $notestring) {
	}

	public function inEnv() {
		return isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') && !strpos($_SERVER['HTTP_USER_AGENT'], 'wxwork');
	}

	public function hideInCp() {
		global $_G;
		return $_G['setting']['wechat']['openweixin'] && $_G['setting']['weixin']['allow'];
	}

	public function login($referer = '', $op = 0) {
		global $_G;
		$referer = $referer ?? account::referer();

		if($this->inEnv()) {
			$query_data = [
				'appid' => $this->conf['appId'],
				'redirect_uri' => $this->conf['callbackUrl'].(str_contains($this->conf['callbackUrl'], '?') ? '&' : '?').'referer_url='.rawurlencode($referer),
			];
			$url = (new wechat_user(''))->getAuthUrl($query_data);
		} else {
			$url = 'misc.php?mod=wechat';
		}
		if($url) {
			if(!$op) {
				dheader('Location: '.$url, true, 302);
			} else {
				return $url;
			}
		}
	}

	public function register($referer = '', $op = 0) {
		$this->login();
	}

	public function getWeChatUrl($authcode = '') {
		global $_G;
		$query_data = [
			'appid' => $this->conf['appId'],
			'redirect_uri' => rawurlencode($_G['siteurl'].'api/wechat/callback.php?authcode='.$authcode.'&referer_url='.rawurlencode($_G['cookie']['wechat_referer'])),
		];
		return (new wechat_user(''))->getAuthUrl($query_data);
	}

	public function getAuthCode() {
		global $_G;
		$code = 0;
		$i = 0;
		do {
			$code = rand(1000000, 9999999);
			$codeExists = memory('exists', 'wechat_code_'.$code);;
			$i++;
		} while($codeExists && $i < 10);

		if($codeExists) {
			return $this->getAuthCode();
		}

		$authcode = urlencode(base64_encode(authcode($code, 'ENCODE', $_G['config']['security']['authkey'])));
		$data = [
			'uid' => $_G['uid'],
			'code' => $code,
			'status' => 0,
			'create_time' => $_G['timestamp'],
		];
		memory('set', 'wechat_code_'.$code, $data, 300);
		return [$authcode, $code];
	}

	public function bind($uid) {
		global $_G;
		$paramBase = $this->getAccountUDAuth();
		if(!$paramBase || !isset($paramBase['atype']) || !isset($paramBase['account']) || !isset($paramBase['authcode'])) {
			return;
		}

		$param = $paramBase;
		$account = new account($param);
		$account->userBind($uid);
		// 注册时同步头像
		$avatarRegisterAuto = $account->getSwitch('avatarRegisterAuto');
		if(in_array('wechat', $avatarRegisterAuto)) {
			if($uid && $_G['cookie']['accountHeadImg']) {
				if($content = dfsockopen($_G['cookie']['accountHeadImg'])) {
					dmkdir(DISCUZ_DATA.'./avatar/');
					$tmpFile = DISCUZ_DATA.'./avatar/'.TIMESTAMP.random(6);
					file_put_contents($tmpFile, $content);

					if(is_file($tmpFile)) {
						if($this->set_avatar($uid, $tmpFile)) {
							unlink($tmpFile);

							table_common_member::t()->update($uid, [
								'avatarstatus' => '1'
							]);
						}
					}
				}
			}
		}

		$authcode = authcode(base64_decode(urldecode($paramBase['authcode'])), 'DECODE', $_G['config']['security']['authkey']);
		if($authcode) {
			$confirm_authcode = urlencode(base64_encode(authcode($authcode.'_'.$uid, 'ENCODE', $_G['config']['security']['authkey'])));
			$_GET['referer'] = $_G['siteurl'].'misc.php?mod=wechat&ac=confirm&authcode='.$confirm_authcode;
		}
		dsetcookie('accountUDAuth', '', -1);
		dsetcookie('accountHeadImg', '', -1);
	}

	public function getLoginUser() {
		global $_G;
		$account = new account();
		// 是否关联了微信开放平台
		$_type = ($this->conf['openweixin'] && $_G['setting']['weixin']['allow'] && $this->inEnv()) ? 'weixin' : 'wechat';
		$_atype = ($this->conf['openweixin'] && $_G['setting']['weixin']['allow'] && $this->inEnv()) ? account::aType_weixin : account::aType_wechatOpenid;

		$authcode = authcode(base64_decode(urldecode($_GET['authcode'])), 'DECODE', $_G['config']['security']['authkey']);
		if($authcode) {
			$pc_login_data = memory('get', 'wechat_code_'.$authcode);
			if($pc_login_data['uid'] > 0) {
				require_once libfile('function/member');
				$member = getuserbyuid($pc_login_data['uid'], 1);
				setloginstatus($member, 1296000);

				$user = $account->getUser($pc_login_data['uid'], $_atype);
				if($user) {
					showmessage('account_bind_exists');
				}
			}
		}

		$this->code = $_GET['code'].'';
		$this->token = self::_getToken();

		if($this->token['is_snapshotuser'] == 1) {
			dheader('Location: '.$_G['siteurl'].'misc.php?mod=wechat&ac=snapshotuser&authcode='.$_GET['authcode'].'&formhash='.FORMHASH);
		}
		$user = new wechat_user($this->token);

		//获取访问用户身份
		$userInfo = $user->getAuthUser();
		if(!$userInfo) {
			dheader('Location: '.(!empty($_GET['referer_url']) ? $_GET['referer_url'] : $_G['siteurl']), true, 302);
		}
		if(!$userInfo['success']) {
			showmessage($userInfo['errmsg']);
		}

		// 是否关联了微信开放平台
		$_account = ($this->conf['openweixin'] && $_G['setting']['weixin']['allow'] && $this->inEnv()) ? $userInfo['unionid'] : $userInfo['openid'];

		$paramBase = ['type' => $_type, 'atype' => $_atype, 'account' => $_account, 'bindname' => $userInfo['nickname'], 'authcode' => $_GET['authcode']];
		if(!$account->checkUser($paramBase)) {
			if($_G['uid'] && (($authcode && $_G['uid'] == $pc_login_data['uid']) || !$authcode)) {
				$user = $account->getUser($_G['uid'], $_atype);
				if($user) {
					showmessage('account_bind_exists');
				}
				$account->userBind($_G['uid'], $paramBase);

				// 绑定时同步头像
				$avatarBindAuto = $account->getSwitch('avatarBindAuto');
				if(in_array('wechat', $avatarBindAuto)) {
					if($_G['uid'] && $userInfo['headimgurl']) {
						if($content = dfsockopen($userInfo['headimgurl'])) {
							dmkdir(DISCUZ_DATA.'./avatar/');
							$tmpFile = DISCUZ_DATA.'./avatar/'.TIMESTAMP.random(6);
							file_put_contents($tmpFile, $content);

							if(is_file($tmpFile)) {
								if($this->set_avatar($_G['uid'], $tmpFile)) {
									unlink($tmpFile);

									table_common_member::t()->update($_G['uid'], [
										'avatarstatus' => '1'
									]);
								}
							}
						}
					}
				}

				if($authcode) {
					$confirm_authcode = urlencode(base64_encode(authcode($authcode.'_'.$_G['uid'], 'ENCODE', $_G['config']['security']['authkey'])));
					dheader('Location: '.$_G['siteurl'].'misc.php?mod=wechat&ac=confirm&authcode='.$confirm_authcode);
				}

				dheader('Location: '.(!empty($_GET['referer_url']) ? $_GET['referer_url'] : $_G['siteurl']), true, 302);
			}

			if($this->conf['loginUsernameRule'] == 2) {
				if(!in_array($paramBase['type'], account::getSwitch('register'))) {
					showmessage('register_disable');
				}
				dsetcookie('accountUDAuth', authcode(serialize($paramBase), 'ENCODE'), 3600);
				dheader('Location: '.$_G['siteurl'].'member.php?mod=register&fromAccount='.formhash());
			}

			$username = $userInfo['nickname'];
			// $email = random(5).'@wechat.com';
			$email = '';
			$param = $paramBase + [
					'username' => $username,
					'password' => '',
					'email' => $email,
				];

			$msg = $account->userRegister($param);
			if($msg) {
				if(in_array($msg, ['profile_username_illegal', 'profile_username_protect', 'profile_username_duplicate', 'profile_email_illegal', 'profile_email_domain_illegal', 'profile_email_duplicate'])) {
					dsetcookie('accountUDAuth', authcode(serialize($paramBase), 'ENCODE'), 3600);
					dsetcookie('accountHeadImg', $userInfo['headimgurl'], 3600);
					dheader('Location: '.$_G['siteurl'].'member.php?mod=register&fromAccount='.formhash());
				}
				showmessage($msg);
			}
			// 注册时同步头像
			$avatarRegisterAuto = $account->getSwitch('avatarRegisterAuto');
			if(in_array('wechat', $avatarRegisterAuto)) {
				if($_G['uid'] && $userInfo['headimgurl']) {
					if($content = dfsockopen($userInfo['headimgurl'])) {
						dmkdir(DISCUZ_DATA.'./avatar/');
						$tmpFile = DISCUZ_DATA.'./avatar/'.TIMESTAMP.random(6);
						file_put_contents($tmpFile, $content);

						if(is_file($tmpFile)) {
							if($this->set_avatar($_G['uid'], $tmpFile)) {
								unlink($tmpFile);

								table_common_member::t()->update($_G['uid'], [
									'avatarstatus' => '1'
								]);
							}
						}
					}
				}
			}

		} else {
			if(!$authcode && $_G['uid']) {
				showmessage('account_bind_other_exists', (!empty($_GET['referer_url']) ? $_GET['referer_url'] : $_G['siteurl']));
			} elseif ($authcode && $account->getUid() > 0 && $_G['uid'] && $account->getUid() != $_G['uid']) {
				showmessage('account_bind_other_exists', 'home.php?mod=spacecp&ac=account');
			} else {
				$account->userLogin();
				// 登录时同步头像
				$avatarLoginAuto = $account->getSwitch('avatarLoginAuto');
				if(in_array('wechat', $avatarLoginAuto)) {
					if($_G['uid'] && $userInfo['headimgurl']) {
						if($content = dfsockopen($userInfo['headimgurl'])) {
							dmkdir(DISCUZ_DATA.'./avatar/');
							$tmpFile = DISCUZ_DATA.'./avatar/'.TIMESTAMP.random(6);
							file_put_contents($tmpFile, $content);

							if(is_file($tmpFile)) {
								if($this->set_avatar($_G['uid'], $tmpFile)) {
									unlink($tmpFile);

									table_common_member::t()->update($_G['uid'], [
										'avatarstatus' => '1'
									]);
								}
							}
						}
					}
				}
			}
		}

		if($authcode) {
			$confirm_authcode = urlencode(base64_encode(authcode($authcode.'_'.$_G['uid'], 'ENCODE', $_G['config']['security']['authkey'])));
			dheader('Location: '.$_G['siteurl'].'misc.php?mod=wechat&ac=confirm&authcode='.$confirm_authcode);
		}

		// 如果关联了微信开放平台，补充绑定 openid
		if($this->conf['openweixin'] && $_G['setting']['weixin']['allow'] && $this->inEnv()) {
			$_account_tmp = table_common_member_account::t()->fetch_by_account($this->token['openid'], account::aType_wechatOpenid);
			if(!$_account_tmp) {
				$paramBase = ['type' => 'wechat', 'atype' => account::aType_wechatOpenid, 'account' => $this->token['openid'], 'bindname' => $userInfo['nickname']];
				if(!$account->checkUser($paramBase)) {
					$account->userBind($_G['uid']);
				}
			}
		}

		// 如果关联了微信开放平台，并且未启用开放平台登录，补充绑定 Unionid
		if($this->conf['openweixin'] && !$_G['setting']['weixin']['allow'] && !empty($this->token['unionid'])) {
			$_account_tmp = table_common_member_account::t()->fetch_by_account($this->token['unionid'], account::aType_weixin);
			if(!$_account_tmp) {
				$paramBase = ['type' => 'weixin', 'atype' => account::aType_weixin, 'account' => $this->token['unionid'], 'bindname' => $userInfo['nickname']];
				if(!$account->checkUser($paramBase)) {
					$account->userBind($_G['uid']);
				}
			}
		}

		dheader('Location: '.(!empty($_GET['referer_url']) ? $_GET['referer_url'] : $_G['siteurl']), true, 302);
	}

	private function _getToken() {
		$createConnection = new wechat_connection();
		return $createConnection->getAccessToken($this->conf['appId'], $this->conf['appSecret'], $this->code);
	}

	private function _refreshToken() {
		$createConnection = new wechat_connection();
		return $createConnection->refreshAccessToken($this->conf['appId'], $this->conf['appSecret'], $this->token['refresh_token']);
	}

}