<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class account_base {

	public bool $interface_loginAuto = true;
	public bool $interface_noBind = false;
	public bool $interface_noAutoAvatar = false;

	public static function autoload() {
		spl_autoload_register(function($class) {
			[$interface] = explode('_', $class);
			if(isset(self::Interfaces_aType()[$interface]) && file_exists($f = DISCUZ_ROOT.'./source/class/account/'.$interface.'/'.$class.'.php')) {
				require_once $f;
			}
		}, true, true);
	}

	public static function Interfaces_Used() {
		list($interfaces, ,) = self::cacheInterfaces();
		return $interfaces;
	}

	public static function Interfaces_aType() {
		list(, $aTypes,) = self::cacheInterfaces();
		return $aTypes;
	}

	public static function Interfaces_iconId() {
		list(, , $iconIds) = self::cacheInterfaces();
		return $iconIds;
	}

	public static function cacheInterfaces(bool $force = false) {
		global $_G;
		static $value = null;
		if($value !== null) {
			return $value;
		}
		if(!$force) {
			loadcache('account_interfaces');
			if(!empty($_G['cache']['account_interfaces'])) {
				return $value = $_G['cache']['account_interfaces'];
			}
		}
		$interfaces = $aTypes = $iconIds = [];
		$dir = DISCUZ_ROOT.'./source/class/account/';
		foreach(glob($dir.'account_*.php') as $f) {
			$interface = str_replace('account_', '', $c = basename($f, '.php'));
			if($interface == 'base') {
				continue;
			}
			if(defined($c.'::aType')) {
				$aTypes[$interface] = $c::aType;
			}
			if(defined($c.'::iconId')) {
				$iconIds[$interface] = $c::iconId;
			}
			$interfaces[] = $interface;
		}
		savecache('account_interfaces', $value = [$interfaces, $aTypes, $iconIds]);
		return $value;
	}

	public static function getInterfaces() {
		global $_G;
		$return = self::Interfaces_Used();

		if(!empty($_G['setting']['account_plugin_atypes'])) {
			foreach($_G['setting']['account_plugin_atypes'] as $pluginid => $atype) {
				if(in_array($pluginid, $_G['setting']['plugins']['available'])) {
					$return[] = 'plugin_'.$pluginid;
				}
			}
		}
		return $return;
	}

	public static function call($func, $params = [], $switch = '') {
		$interfaces = !$switch ? self::getInterfaces() : account::getSwitch($switch);
		foreach($interfaces as $qClass) {
			self::callClass($qClass, $func, $params);
		}
	}

	public static function callClass($qClass, $func, $params = [], $ignoreAllow = false) {
		static $interfaceClasses = [];
		if(!$ignoreAllow && !self::allow($qClass)) {
			return null;
		}
		if(!isset($interfaceClasses[$qClass])) {
			$interfaceClasses[$qClass] = new (account_base::getClass($qClass));
		}
		if(!method_exists($interfaceClasses[$qClass], $func)) {
			return null;
		}
		return call_user_func_array([$interfaceClasses[$qClass], $func], $params);
	}

	public function html2markdown($html) {
		$html = preg_replace_callback('/<a\s+.*?href="(.+?)".*?>(.+?)<\/a>/is', function($matches) {
			global $_G;
			$url = $matches[1];
			if(!parse_url($url, PHP_URL_HOST)) {
				$url = $_G['siteurl'].$url;
			}
			return sprintf('[%s](%s)', $matches[2], $url);
		}, $html);
		$html = preg_replace_callback('/<p\s+class="summary">(.+?)<span>(.+?)<\/span><\/p>/is', function($matches) {
			return "\n> ".$matches[1].$matches[2];
		}, $html);
		$html = preg_replace_callback('/<div\s+class="quote"><blockquote>(.+?)<\/blockquote><\/div>/is', function($matches) {
			return "\n> ".$matches[1];
		}, $html);
		$html = str_replace(
			[
				'<p class="summary">',
				'<p class="mbn">',
				'<span class="pipe">|</span>',
				'<br />', '<br/>',
				"\r",
				"\n\n",
				'&nbsp;',
			],
			[
				"\n> ",
				"\n> ",
				' &nbsp; ',
				"\n", "\n",
				'',
				"\n",
				' ',
			], $html);
		$html = strip_tags($html);
		return $html;
	}

	public function appendArticles(&$articles, $title, $description = '', $url = '', $picurl = '') {
		$tmp = [
			'title' => $title
		];
		if($description !== '') {
			$tmp['description'] = $description;
		}
		if($url !== '') {
			$tmp['url'] = $url;
		}
		if($picurl !== '') {
			$tmp['picurl'] = $picurl;
		}
		$articles[] = $tmp;
	}

	public function appendArticlesByTid(&$articles, $tid, $aid = 0) {
		global $_G;
		$thread = table_forum_thread::t()->fetch_thread($tid);
		if(!$thread) {
			return;
		}
		$tmp = table_forum_post::t()->fetch_all_first_by_tid([$tid]);
		if(!$tmp) {
			return;
		}
		$message = !empty($tmp[$tid]) ? $tmp[$tid] : '';
		$attachment = [];
		$tmp = table_forum_attachment_n::t()->fetch_all_by_id('tid:'.$tid, 'tid', [$tid], 'dateline', true);
		if($tmp) {
			if($aid && isset($tmp[$aid])) {
				$attachment = $tmp[$aid];
			} else {
				$attachment = array_shift($tmp);
			}
		}
		$tmp = [
			'title' => $thread['subject'],
			'description' => $message,
			'url' => $_G['siteurl'].'forum.php?mod=viewthread&tid='.$thread['tid']
		];
		if($attachment) {
			$attachurl = $attachment['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['siteurl'].$_G['setting']['attachurl'];
			$tmp['picurl'] = $attachurl.'forum/'.$attachment['attachment'];
		}
		$articles[] = $tmp;
	}

	public function parseSendParam() {
		$toUser = !empty($_GET['toUser']) ? (is_array($_GET['toUser']) ? $_GET['toUser'] : explode(',', $_GET['toUser'])) : '@all';
		$toParty = !empty($_GET['toParty']) ? (is_array($_GET['toParty']) ? $_GET['toParty'] : explode(',', $_GET['toParty'])) : [];
		$toTag = !empty($_GET['toTag']) ? (is_array($_GET['toTag']) ? $_GET['toTag'] : explode(',', $_GET['toTag'])) : [];
		if($toParty || $toTag) {
			$toUser = '';
		}
		return [$toUser, $toParty, $toTag];
	}

	public function getAccountUDAuth() {
		global $_G;
		$auth = $_G['cookie']['accountUDAuth'];
		return dunserialize(authcode($auth, 'DECODE'));
	}

	public function set_avatar($uid, $avatar = '') {
		global $_G;
		$filetype = '.jpg';
		$avatarpath = $_G['setting']['attachdir'];
		dmkdir($avatarpath.'./temp/');
		$tmpavatar = $avatarpath.'./temp/upload'.$uid.$filetype;
		file_exists($tmpavatar) && @unlink($tmpavatar);
		file_put_contents($tmpavatar, file_get_contents($avatar));

		if(!is_file($tmpavatar)) {
			return false;
		}

		$tmpavatarbig = './temp/upload'.$uid.'big'.$filetype;
		$tmpavatarmiddle = './temp/upload'.$uid.'middle'.$filetype;
		$tmpavatarsmall = './temp/upload'.$uid.'small'.$filetype;
		$image = new image;
		if($image->Thumb($tmpavatar, $tmpavatarbig, 200, 250, 1) <= 0) {
			return false;
		}
		if($image->Thumb($tmpavatar, $tmpavatarmiddle, 120, 120, 2) <= 0) {
			return false;
		}
		if($image->Thumb($tmpavatar, $tmpavatarsmall, 48, 48, 2) <= 0) {
			return false;
		}

		$tmpavatarbig = $avatarpath.$tmpavatarbig;
		$tmpavatarmiddle = $avatarpath.$tmpavatarmiddle;
		$tmpavatarsmall = $avatarpath.$tmpavatarsmall;

		loaducenter();
		if(UC_STANDALONE) {
			if($_G['setting']['ftp']['on'] == 2) {
				define('ACCOUNT_DATADIR', DISCUZ_DATA.'./attachment/');
			} else {
				define('ACCOUNT_DATADIR', DISCUZ_ROOT.'data/');
			}
			define('ACCOUNT_UPAVTDIR', 'avatar/');

			@chmod(ACCOUNT_DATADIR.ACCOUNT_UPAVTDIR, 0777);

			$avatartype = '';
			$bigavatarfile = ACCOUNT_UPAVTDIR.$this->get_avatar($uid, 'big', $avatartype);
			dmkdir(dirname(ACCOUNT_DATADIR.$bigavatarfile));
			$middleavatarfile = ACCOUNT_UPAVTDIR.$this->get_avatar($uid, 'middle', $avatartype);
			dmkdir(dirname(ACCOUNT_DATADIR.$middleavatarfile));
			$smallavatarfile = ACCOUNT_UPAVTDIR.$this->get_avatar($uid, 'small', $avatartype);
			dmkdir(dirname(ACCOUNT_DATADIR.$smallavatarfile));
			$bigavatar = file_get_contents($tmpavatarbig);
			$middleavatar = file_get_contents($tmpavatarmiddle);
			$smallavatar = file_get_contents($tmpavatarsmall);
			if(!$bigavatar || !$middleavatar || !$smallavatar) {
				return false;
			}

			$fp = @fopen(ACCOUNT_DATADIR.$bigavatarfile, 'wb');
			@fwrite($fp, $bigavatar);
			@fclose($fp);

			$fp = @fopen(ACCOUNT_DATADIR.$middleavatarfile, 'wb');
			@fwrite($fp, $middleavatar);
			@fclose($fp);

			$fp = @fopen(ACCOUNT_DATADIR.$smallavatarfile, 'wb');
			@fwrite($fp, $smallavatar);
			@fclose($fp);

			ftpcmd('upload', $bigavatarfile);
			ftpcmd('upload', $middleavatarfile);
			ftpcmd('upload', $smallavatarfile);
		} else {
			loaducenter();
			$uc_avatarflash = uc_avatar($uid, 'virtual', 0);
			if(!empty($uc_avatarflash)) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $uc_avatarflash[11]);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, [
					'avatar1' => base64_encode(file_get_contents($tmpavatarbig)),
					'avatar2' => base64_encode(file_get_contents($tmpavatarmiddle)),
					'avatar3' => base64_encode(file_get_contents($tmpavatarsmall)),
				]);
				curl_exec($ch);
			}
		}

		@unlink($tmpavatar);
		@unlink($tmpavatarbig);
		@unlink($tmpavatarmiddle);
		@unlink($tmpavatarsmall);

		return true;
	}

	public function get_avatar($uid, $size = 'big', $type = '') {
		$size = in_array($size, ['big', 'middle', 'small']) ? $size : 'big';
		$uid = abs(intval($uid));
		$uid = sprintf('%09d', $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$typeadd = $type == 'real' ? '_real' : '';
		return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
	}

	public static function getClass($interface) {
		if(str_starts_with($interface, 'plugin_')) {
			$pluginid = substr($interface, 7);
			include_once DISCUZ_PLUGIN($pluginid).'/account.class.php';
			return 'account_'.$pluginid;
		} else {
			return 'account_'.$interface;
		}
	}

	public static function registerAccount($pluginid) {
		global $_G;

		$_G['setting']['account_plugin_atypes'] = !empty($_G['setting']['account_plugin_atypes']) ? $_G['setting']['account_plugin_atypes'] : [];

		$aTypeStart = 0;
		do {
			$aTypeStart--;
			if(in_array($aTypeStart, $_G['setting']['account_plugin_atypes'])) {
				continue;
			}
			$count = table_common_member_account::t()->count_by_atype($aTypeStart);
			if(!$count) {
				break;
			}

			if($aTypeStart == -100) {
				return false;
			}
		} while(true);

		$_G['setting']['account_plugin_atypes'][$pluginid] = $aTypeStart;

		table_common_setting::t()->update('account_plugin_atypes', $_G['setting']['account_plugin_atypes']);
		require_once libfile('function/cache');
		updatecache('setting');

		return $aTypeStart;
	}

	public static function getAccountType($pluginid) {
		global $_G;

		return !empty($_G['setting']['account_plugin_atypes'][$pluginid]) ? $_G['setting']['account_plugin_atypes'][$pluginid] : false;
	}

	public static function unregisterAccount($pluginid) {
		global $_G;

		if(empty($_G['setting']['account_plugin_atypes'][$pluginid])) {
			return false;
		}

		$atype = $_G['setting']['account_plugin_atypes'][$pluginid];
		table_common_member_account::t()->delete_by_atype($atype);

		unset($_G['setting']['account_plugin_atypes'][$pluginid]);
		table_common_setting::t()->update('account_plugin_atypes', $_G['setting']['account_plugin_atypes']);
		require_once libfile('function/cache');
		updatecache('setting');

		return true;
	}

	public static function getConfig($interface) {
		global $_G;

		if(str_starts_with($interface, 'plugin_')) {
			$pluginid = substr($interface, 7);
			return !empty($_G['setting']['account_plugin_confs'][$pluginid]) ? $_G['setting']['account_plugin_confs'][$pluginid] : [];
		} else {
			return !empty($_G['setting'][$interface]) && is_array($_G['setting'][$interface]) ? $_G['setting'][$interface] : [];
		}
	}

	public static function allow($interface) {
		return self::getConfig($interface)['allow'];
	}

	public static function getName($interface) {
		$name = self::callClass($interface, 'name', [], true);
		if($name) {
			return $name;
		}
		if(!defined('IN_ADMINCP')) {
			return lang('admincp_menu', 'menu_setting_'.$interface);
		} else {
			return cplang('menu_setting_'.$interface);
		}
	}

	public static function getIcon($interface) {
		if(empty($interface)) {
			return ['', ''];
		}
		$icon = self::callClass($interface, 'icon', [], true);
		if($icon) {
			return [$icon, ''];
		}

		global $_G;

		$account = $_G['setting']['account'];
		$iconId = !empty($account['iconId'][$interface]) ? $account['iconId'][$interface] :
			(!empty(account_base::Interfaces_iconId()[$interface]) ? account_base::Interfaces_iconId()[$interface] : '');
		if($iconId) {
			return [
				'<svg class="iconfont" aria-hidden="true"><use xlink:href="#'.$iconId.'"></use></svg>',
				$iconId
			];
		} else {
			return ['['.$interface.']', ''];
		}
	}

	public static function validatorSign() {
		global $_G;
		if(!$_G['uid']) {
			return true;
		}
		[$uid, $ip] = explode("\t", authcode($_G['cookie']['accountsign'], 'DECODE'));
		$v = !empty($uid) && !empty($ip) && $uid == $_G['uid'] && $ip == $_G['clientip'];
		dsetcookie('accountsign', '', -1);
		return $v;
	}

	public static function error_logger($message) {
		global $_G;
		$uid = $_G['uid'] ?? 0;
		$ip = $_G['clientip'] ?? '';
		$user = '<b>User:</b> uid='.intval($uid).'; IP='.$ip.'; RIP:'.$_SERVER['REMOTE_ADDR'];
		$uri = 'Request: '.htmlspecialchars(discuz_error::clear($_SERVER['REQUEST_URI']));

		$errorlog = [
			'timestamp' => TIMESTAMP,
			'message' => $message,
			'clientip' => $_G['clientip'],
			'user' => $user,
			'uri' => $uri,
		];
		logger('error', [], $uid, $errorlog);
	}
}