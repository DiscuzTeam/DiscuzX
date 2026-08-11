<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class account_discuz extends account_base {

	public bool $interface_loginAuto = false;

	public function __construct() {
		parent::autoload();
		global $_G;
		$this->conf = $_G['setting']['discuz'] ?? [];
		$this->token = self::_getToken();
	}

	public function name() {
		return !empty($this->conf['name']) ? strip_tags($this->conf['name']) : 'Discuz!';
	}

	public function icon() {
		if(!empty($this->conf['icon'])) {
			if(str_starts_with($this->conf['icon'], '<svg') && str_ends_with($this->conf['icon'], '</svg>')) {
				return $this->conf['icon'];
			} elseif(str_starts_with($this->conf['icon'], 'http')) {
				return '<img class="iconfont" src="'.$this->conf['icon'].'" />';
			}
		}
		return false;
	}

	public function login($referer = '', $op = 0) {
		global $_G;
		$referer = $referer ?? account::referer();

		$post = array(
			'method' => 'system',
			'returntype' => base64_encode($this->conf['callbackUrl']),
		);

		$ret = $this->_request('/member/loginInfo', $post);
		if(!$ret || $ret['ret'] > 0) {
			echo lang('message', 'account_api_error', ['message' => ': '.$ret['ret'].', /member/loginInfo']);
			exit;
		}
		if(empty($ret['data']['locationUrl'])) {
			echo lang('message', 'account_api_error', ['message' => ': no locationUrl']);
			exit;
		}

		dsetcookie('authtoken', authcode($this->token."\t".$ret['data']['authtoken']."\t".$referer, 'ENCODE', $_G['config']['security']['authkey']));

		dheader('Location: '.$ret['data']['locationUrl']['url'], true, 302);
	}

	public function register($referer = '', $op = 0) {
		$this->login();
	}

	public function getLoginUser() {
		global $_G;
		$account = new account();
		$authtoken = authcode($_G['cookie']['authtoken'], 'DECODE', $_G['config']['security']['authkey']);
		[$this->token, $authtoken, $_GET['referer']] = explode("\t", $authtoken);

		$ret = $this->_request('/authtoken', array('authtoken' => $authtoken));
		if(!$ret || $ret['ret'] > 0) {
			showmessage('account_api_error', '', ['message' => ': '.$ret['ret'].', /authtoken']);
		}

		//获取访问用户身份
		$ret = $this->_request('/profile', array('uid' => $ret['data']['uid']));
		if(!$ret || $ret['ret'] > 0) {
			showmessage('account_api_error', '', ['message' => ': '.$ret['ret'].', /profile']);
		}

		if(!$ret) {
			dheader('Location: '.(!empty($_GET['referer']) ? $_GET['referer'] : $_G['siteurl']), true, 302);
		}

		$paramBase = ['type' => 'discuz', 'atype' => account::aType_discuz, 'account' => $ret['data']['user']['uid'], 'bindname' => $ret['data']['user']['username']];
		if(!$account->checkUser($paramBase)) {
			if($_G['uid']) {
				$user = $account->getUser($_G['uid'], account::aType_discuz);
				if($user) {
					showmessage('account_bind_exists');
				}
				$account->userBind($_G['uid'], $paramBase);
				dheader('Location: '.(!empty($_GET['referer']) ? $_GET['referer'] : $_G['siteurl']), true, 302);
			}

			if($this->conf['loginUsernameRule'] == 2) {
				if(!in_array($paramBase['type'], account::getSwitch('register'))) {
					showmessage('register_disable');
				}
				dsetcookie('accountUDAuth', authcode(serialize($paramBase), 'ENCODE'), 3600);
				dheader('Location: '.$_G['siteurl'].'member.php?mod='.$_G['setting']['regname'].'&fromAccount='.formhash());
			}

			$username = $ret['data']['user']['username'];
			$email = '';
			$param = $paramBase + [
					'username' => $username,
					'password' => '',
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
		} else {
			if($_G['uid']) {
				showmessage('account_bind_other_exists', (!empty($_GET['referer']) ? $_GET['referer'] : $_G['siteurl']));
			} else {
				$account->userLogin();
			}
		}
		dheader('Location: '.(!empty($_GET['referer']) ? $_GET['referer'] : $_G['siteurl']), true, 302);
	}

	private function _getToken() {
		$ret = $this->_request('/token', []);
		if(!$ret || $ret['ret'] > 0 || empty($ret['token'])) {
			return '';
		}
		return $ret['token'];
	}

	private function _request($uri, $post) {
		$nonce = rand(1000, 2000);
		$t = time();
		$headers = [
			'appid' => $this->conf['appid'],
			'nonce' => $nonce,
			't' => $t,
			'sign' => base64_encode(hash('sha256', $nonce.$t.$this->conf['secret'])),
		];

		if($this->token) {
			$headers['token'] = $this->token;
		}

		$headersFmt = [];
		foreach($headers as $name => $value) {
			$canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
			$headersFmt[] = $canonicalName.': '.$value;
		}
		$ch = curl_init();
		!str_ends_with($this->conf['url'], '/') && $this->conf['url'] .= '/';
		$opts = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headersFmt,
			CURLOPT_URL => $this->conf['url'].'api/restful/?'.$uri,
			CURLOPT_POST => 'POST',
			CURLOPT_POSTFIELDS => $post,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
		];
		curl_setopt_array($ch, $opts);
		$response = curl_exec($ch);
		return json_decode($response, true);
	}

}