<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

class account_qq extends account_base {
	private mixed $token;

	private array $conf;

	private const getUserByIDKey = 'qqUser_';
	private const getUserByIDTTL = 3600;

	public bool $interface_loginAuto = false;

	public function __construct() {
		parent::autoload();
		global $_G;
		$this->conf = $_G['setting']['qq'] ?? [];
		$this->token = self::_getToken();
	}

	public function notificationAdd($touid, $note, $notestring) {

	}


	public function login($referer = '', $op = 0) {
		global $_G;
		$referer = $referer ?? account::referer();
		$query_data = [
			'response_type' => 'code',
			'client_id' => $this->conf['clientId'],
			'state' => uniqid(),
			'scope' => 'get_user_info',
			'redirect_uri' => $this->conf['callbackUrl'],
		];
		dsetcookie('qq_referer', $referer, 3600);
		$url = (new qq_user(''))->getSsoUrl($query_data);
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

	public function bind($uid) {
		global $_G;
		$paramBase = $this->getAccountUDAuth();
		if(!$paramBase || !isset($paramBase['atype']) || !isset($paramBase['account'])) {
			return;
		}
		$param = $paramBase;
		$account = new account($param);
		$account->userBind($uid);
		// 注册时同步头像
		$avatarRegisterAuto = $account->getSwitch('avatarRegisterAuto');
		if(in_array('qq', $avatarRegisterAuto)) {
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

		dsetcookie('accountUDAuth', '', -1);
		dsetcookie('accountHeadImg', '', -1);
	}

	public function getLoginUser() {
		global $_G;
		$account = new account();

		$this->code = $_GET['code'].'';
		$this->token = self::_getToken();
		$user = new qq_user($this->token);

		$openid = $user->getOpenid();
		if(!$openid) {
			dheader('Location: '.(!empty($_G['cookie']['qq_referer']) ? $_G['cookie']['qq_referer'] : $_G['siteurl']), true, 302);
		}
		//获取访问用户身份
		$userInfo = $user->getAuthUser($openid);
		if(!$userInfo) {
			dheader('Location: '.(!empty($_G['cookie']['qq_referer']) ? $_G['cookie']['qq_referer'] : $_G['siteurl']), true, 302);
		}

		$paramBase = ['type' => 'qq', 'atype' => account::aType_qq, 'account' => $openid['openid'], 'bindname' => $userInfo['nickname']];
		if(!$account->checkUser($paramBase)) {
			if($_G['uid']) {
				$user = $account->getUser($_G['uid'], account::aType_qq);
				if($user) {
					showmessage('account_bind_exists');
				}
				$account->userBind($_G['uid'], $paramBase);
				// 绑定时同步头像
				$avatarBindAuto = $account->getSwitch('avatarBindAuto');
				if(in_array('qq', $avatarBindAuto)) {
					if($_G['uid'] && $userInfo['figureurl_2']) {
						if($content = dfsockopen($userInfo['figureurl_2'])) {
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
				dheader('Location: '.(!empty($_G['cookie']['qq_referer']) ? $_G['cookie']['qq_referer'] : $_G['siteurl']), true, 302);
			}

			if($this->conf['loginUsernameRule'] == 2) {
				if(!in_array($paramBase['type'], account::getSwitch('register'))) {
					showmessage('register_disable');
				}
				dsetcookie('accountUDAuth', authcode(serialize($paramBase), 'ENCODE'), 3600);
				dheader('Location: '.$_G['siteurl'].'member.php?mod='.$_G['setting']['regname'].'&fromAccount='.formhash());
			}

			$username = $userInfo['nickname'];
			// $email = random(9).'@qqlogin.com';
			$email = '';
			$param = $paramBase + [
					'username' => $username,
					'password' => '',
					'email' => $email,
				];

			$msg = $account->userRegister($param);
			if($msg) {
				if($msg == 'profile_username_duplicate' || $msg == 'profile_email_duplicate') {
					dsetcookie('accountUDAuth', authcode(serialize($paramBase), 'ENCODE'), 3600);
					dsetcookie('accountHeadImg', $userInfo['figureurl_2'], 3600);
					dheader('Location: '.$_G['siteurl'].'member.php?mod='.$_G['setting']['regname']);
				}
				showmessage($msg);
			}
			// 注册时同步头像
			$avatarRegisterAuto = $account->getSwitch('avatarRegisterAuto');
			if(in_array('qq', $avatarRegisterAuto)) {
				if($_G['uid'] && $userInfo['figureurl_2']) {
					if($content = dfsockopen($userInfo['figureurl_2'])) {
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
			if($_G['uid']) {
				showmessage('account_bind_other_exists', (!empty($_G['cookie']['qq_referer']) ? $_G['cookie']['qq_referer'] : $_G['siteurl']));
			} else {
				$account->userLogin();
				// 登录时同步头像
				$avatarLoginAuto = $account->getSwitch('avatarLoginAuto');
				if(in_array('qq', $avatarLoginAuto)) {
					if($_G['uid'] && $userInfo['figureurl_2']) {
						if($content = dfsockopen($userInfo['figureurl_2'])) {
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
		dheader('Location: '.(!empty($_G['cookie']['qq_referer']) ? $_G['cookie']['qq_referer'] : $_G['siteurl']), true, 302);
	}

	private function _getToken() {
		$createConnection = new qq_connection();
		return $createConnection->getAccessToken($this->conf['clientId'], $this->conf['clientSecret'], $this->code, $this->conf['callbackUrl']);
	}
}