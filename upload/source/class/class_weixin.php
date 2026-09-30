<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

/**
 * 微信 JS-SDK 分享（Discuz! 核心增强）
 *
 * 公众号凭据来源（优先级）：
 *   1. 后台「全局 -> 微信分享」中单独填写的 AppID / AppSecret（wxshare 设置）
 *   2. 后台「账号 -> 微信」已配置的公众号凭据（setting[wechat]）
 *
 * ────────────────────────────────────────────────────────────────────────
 * ★ 给「其他微信生态功能」复用的统一入口（避免重复刷新 access_token 互相挤掉）
 *
 *   微信的 client_credential（cgi-bin/token）是【同一 AppID 全局唯一、刷新即作废旧
 *   token】的凭证。若各功能各自独立去刷新，会互相把对方的 token 打掉，导致间歇性
 *   失效。因此本文件提供【唯一的、带缓存与并发锁】的令牌入口，其他功能请直接调用：
 *
 *     - weixin::get_access_token($appid, $appsecret)
 *           取得分享/模板消息/自定义菜单等后端 API 共用的 access_token，
 *           内部按 appid 隔离缓存、带文件锁防止并发刷新冲突。
 *     - weixin::get_jsapi_ticket($appid, $appsecret)
 *           取得 JS-SDK 用的 jsapi_ticket（基于上面的 token，自动复用）。
 *
 *   注意：本文件缓存的是「公众平台后端 API 凭证（cgi-bin/token）」，与核心
 *   account_wechat 用于「网页授权登录（sns/oauth2/access_token）」的用户级
 *   token 是【两种不同类型】，请勿混用，也无需复用。
 * ────────────────────────────────────────────────────────────────────────
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class weixin {

	/**
	 * 当前是否处于微信浏览器环境
	 *
	 * @return bool
	 */
	public static function in_event() {
		return isset($_SERVER['HTTP_USER_AGENT']) &&
			(strpos($_SERVER['HTTP_USER_AGENT'], 'MMWEBSDK') || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) &&
			!strpos($_SERVER['HTTP_USER_AGENT'], 'wxwork');
	}

	/**
	 * 取得「微信公众平台后端 API」access_token（带缓存 + 并发锁 + 同请求复用）
	 *
	 * 这是全站微信后端功能的【唯一】token 入口。其他功能（模板消息、自定义菜单、素材
	 * 管理等）都应调用本函数，而不是各自去请求 cgi-bin/token，以避免互相刷新冲突。
	 *
	 * @param string $appid
	 * @param string $appsecret
	 * @return string|false 成功返回 token，失败返回 false
	 */
	public static function get_access_token($appid, $appsecret) {
		static $runtimeCache = [];
		$key = $appid.':'.$appsecret;
		if(isset($runtimeCache[$key])) {
			return $runtimeCache[$key];
		}

		$cache = self::load_syscache('weixin_token');
		$slot = $cache[$key] ?? [];
		$token = $slot['token'] ?? '';
		$expire = intval($slot['expire'] ?? 0);

		// 留 300 秒缓冲提前刷新，降低并发窗口
		if($token && $expire > TIMESTAMP + 300) {
			$runtimeCache[$key] = $token;
			return $token;
		}

		if(!self::refresh_lock('token')) {
			// 锁被占用（其他请求正在刷新），读一次缓存降级返回，避免阻塞与重复刷新
			if($token) {
				$runtimeCache[$key] = $token;
				return $token;
			}
			return false;
		}

		try {
			// 双重检查：拿锁后再看一眼，可能刚被别的请求刷新好了
			$cache = self::load_syscache('weixin_token');
			$slot = $cache[$key] ?? [];
			if(!empty($slot['token']) && intval($slot['expire'] ?? 0) > TIMESTAMP + 300) {
				return $runtimeCache[$key] = $slot['token'];
			}

			$api = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.urlencode($appid).'&secret='.urlencode($appsecret);
			$resp = dfsockopen($api, 0, '', '', false, '', 15, true);
			$data = @json_decode($resp, true);
			if(empty($data['access_token'])) {
				return false;
			}

			$newExpire = TIMESTAMP + intval($data['expires_in'] ?: 7000);
			$cache[$key] = ['token' => $data['access_token'], 'expire' => $newExpire];
			self::save_syscache('weixin_token', $cache);
			return $runtimeCache[$key] = $data['access_token'];
		} finally {
			self::refresh_unlock('token');
		}
	}

	/**
	 * 取得 JS-SDK 用的 jsapi_ticket（复用上面的统一 token）
	 *
	 * @param string $appid
	 * @param string $appsecret
	 * @return string|false
	 */
	public static function get_jsapi_ticket($appid, $appsecret) {
		static $runtimeCache = [];
		$key = $appid.':'.$appsecret;
		if(isset($runtimeCache[$key])) {
			return $runtimeCache[$key];
		}

		$cache = self::load_syscache('weixin_jsapi_ticket');
		$slot = $cache[$key] ?? [];
		$ticket = $slot['ticket'] ?? '';
		$expire = intval($slot['expire'] ?? 0);

		if($ticket && $expire > TIMESTAMP + 300) {
			$runtimeCache[$key] = $ticket;
			return $ticket;
		}

		if(!self::refresh_lock('ticket')) {
			if($ticket) {
				$runtimeCache[$key] = $ticket;
				return $ticket;
			}
			return false;
		}

		try {
			$cache = self::load_syscache('weixin_jsapi_ticket');
			$slot = $cache[$key] ?? [];
			if(!empty($slot['ticket']) && intval($slot['expire'] ?? 0) > TIMESTAMP + 300) {
				return $runtimeCache[$key] = $slot['ticket'];
			}

			$accessToken = self::get_access_token($appid, $appsecret);
			if($accessToken === false) {
				return false;
			}
			$api = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.urlencode($accessToken).'&type=jsapi';
			$resp = dfsockopen($api, 0, '', '', false, '', 15, true);
			$data = @json_decode($resp, true);
			if(empty($data['ticket'])) {
				return false;
			}

			$newExpire = TIMESTAMP + intval($data['expires_in'] ?: 7000);
			$cache[$key] = ['ticket' => $data['ticket'], 'expire' => $newExpire];
			self::save_syscache('weixin_jsapi_ticket', $cache);
			return $runtimeCache[$key] = $data['ticket'];
		} finally {
			self::refresh_unlock('ticket');
		}
	}

	/**
	 * 获取微信 JS-SDK 签名所需参数（appId / timestamp / nonceStr / signature）
	 * 内部复用统一的 token / ticket 缓存入口。
	 *
	 * @param string $url 参与签名的页面地址，留空取当前地址
	 * @return array|false 成功返回签名数组，失败返回 false
	 */
	public static function jssdk_sign($url = '') {
		global $_G;

		$wx = !empty($_G['setting']['wxshare']) ? $_G['setting']['wxshare'] : [];
		$wechat = !empty($_G['setting']['wechat']) ? $_G['setting']['wechat'] : [];

		$appid = !empty($wx['appid']) ? $wx['appid'] : ($wechat['appId'] ?? '');
		$appsecret = !empty($wx['appsecret']) ? $wx['appsecret'] : ($wechat['appSecret'] ?? '');
		if(empty($appid) || empty($appsecret)) {
			return false;
		}

		$ticket = self::get_jsapi_ticket($appid, $appsecret);
		if($ticket === false) {
			return false;
		}

		if(empty($url)) {
			$url = self::current_url();
		}

		$nonceStr = self::random_str(16);
		$timestamp = TIMESTAMP;
		$string = 'jsapi_ticket='.$ticket.'&noncestr='.$nonceStr.'&timestamp='.$timestamp.'&url='.$url;
		$signature = sha1($string);

		return [
			'appId' => $appid,
			'nonceStr' => $nonceStr,
			'timestamp' => $timestamp,
			'signature' => $signature,
			'url' => $url,
		];
	}

	/**
	 * 当前页面完整地址（用于 JS-SDK 签名，需与用户访问地址一致，不含 # 片段）
	 *
	 * @return string
	 */
	public static function current_url() {
		// 代理 / 负载均衡环境下 $_SERVER['HTTPS'] 常常为空，必须兼容常见反向代理头，
		// 否则服务端拼出的签名地址是 http:// 而微信浏览器里是 https://，导致 wx.config
		// 签名校验失败、分享回退成纯 URL。
		$isHttps = false;
		if(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
			$isHttps = true;
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
			$isHttps = true;
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
			$isHttps = true;
		} elseif(!empty($_SERVER['HTTP_CF_VISITED_SCHEME']) && strtolower($_SERVER['HTTP_CF_VISITED_SCHEME']) === 'https') {
			$isHttps = true;
		} elseif(($_SERVER['SERVER_PORT'] ?? '') == '443') {
			$isHttps = true;
		}

		$protocol = $isHttps ? 'https://' : 'http://';
		$host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
		$request = $_SERVER['REQUEST_URI'] ?? '';
		$request = preg_replace('/#.*$/i', '', $request);
		return $protocol.$host.$request;
	}

	/**
	 * 生成随机字符串
	 *
	 * @param int $len
	 * @return string
	 */
	public static function random_str($len = 16) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$max = strlen($chars) - 1;
		$str = '';
		for($i = 0; $i < $len; $i++) {
			$str .= $chars[mt_rand(0, $max)];
		}
		return $str;
	}

	/**
	 * 读取自定义系统缓存
	 *
	 * @param string $name
	 * @return array
	 */
	public static function load_syscache($name) {
		global $_G;

		loadcache($name);
		$data = $_G['cache'][$name] ?? [];
		return is_array($data) ? $data : [];
	}

	/**
	 * 写入自定义系统缓存（数组结构，序列化存储于 common_syscache）
	 *
	 * @param string $name
	 * @param mixed $data
	 */
	public static function save_syscache($name, $data) {
		savecache($name, $data);
	}

	/**
	 * 刷新并发锁：利用文件锁保证同一时刻只有一个进程刷新 token / ticket，
	 * 避免并发请求同时刷新导致互相挤掉。
	 *
	 * @param string $type token | ticket
	 * @return bool 成功取得锁返回 true
	 */
	public static function refresh_lock($type) {
		$file = DISCUZ_DATA.'./wxlock_'.$type;
		$fp = @fopen($file, 'c');
		if($fp === false) {
			return true; // 无法加锁时放行（不阻塞业务），交由双重检查兜底
		}
		// 非阻塞排他锁：被占用立即返回 false
		if(!@flock($fp, LOCK_EX | LOCK_NB)) {
			@fclose($fp);
			return false;
		}
		$GLOBALS['_wxlock'][$type] = $fp;
		return true;
	}

	/**
	 * 释放刷新锁
	 *
	 * @param string $type
	 */
	public static function refresh_unlock($type) {
		if(isset($GLOBALS['_wxlock'][$type])) {
			@flock($GLOBALS['_wxlock'][$type], LOCK_UN);
			@fclose($GLOBALS['_wxlock'][$type]);
			unset($GLOBALS['_wxlock'][$type]);
		}
	}

	/**
	 * 输出微信分享所需的 JavaScript（仅在微信环境且后台已启用时返回内容）
	 *
	 * @param array $wxsharedata 可覆盖 title / desc / link / imgUrl
	 * @return string HTML 片段；不满足条件时返回空字符串
	 */
	public static function js_share() {
		global $_G, $navtitle;

		if(!self::in_event()) {
			return '';
		}

		$wx = !empty($_G['setting']['wxshare']) ? $_G['setting']['wxshare'] : [];
		if(empty($wx['enable'])) {
			return '';
		}

		$sign = self::jssdk_sign();
		if($sign === false) {
			return '';
		}

		$siteurl = rtrim($_G['setting']['siteurl'] ?? '', '/');
		$defaultLink = self::current_url() ?? $wx['link'];

		$share = [
			'title' => $_G['wxsharedata']['title'] ?? ($navtitle ?? $wx['title']),
			'desc' => $_G['wxsharedata']['desc'] ?? ($wx['desc'] ?? ''),
			'link' => $_G['wxsharedata']['link'] ?? $defaultLink,
			'imgUrl' => $_G['wxsharedata']['imgUrl'] ?? ($_G['wxsharedata']['img'] ?? ($wx['img'] ?? '')),
		];

		if($share['link'] && strpos($share['link'], 'http') !== 0 && $siteurl) {
			$share['link'] = $siteurl.'/'.ltrim($share['link'], '/');
		}
		if($share['imgUrl'] && strpos($share['imgUrl'], 'http') !== 0 && $siteurl) {
			$share['imgUrl'] = $siteurl.'/'.ltrim($share['imgUrl'], '/');
		}

		$config = [
			'debug' => !empty($wx['debug']),
			'appId' => $sign['appId'],
			'timestamp' => $sign['timestamp'],
			'nonceStr' => $sign['nonceStr'],
			'signature' => $sign['signature'],
			'jsApiList' => ['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'],
		];

		$configJson = json_encode($config, JSON_UNESCAPED_UNICODE);
		$shareJson = json_encode($share, JSON_UNESCAPED_UNICODE);

		// 双通道方案（同时兼容新版与旧版微信）：
		$html = '<script>'."\n".
			'window.__wxshareData='.$shareJson.';'."\n".
			'window.__wxshareApply=function(){'."\n".
			'if(typeof wx==="undefined"){return;}'."\n".
			'var s=window.__wxshareData;'."\n".
			's.success=function(){if(window.console){console.log("[wxshare] jweixin apply OK");}};'."\n".
			's.fail=function(e){if(window.console){console.log("[wxshare] jweixin apply FAIL", JSON.stringify(e));}};'."\n".
			'try{'."\n".
			'if(wx.updateAppMessageShareData){wx.updateAppMessageShareData(s);}'."\n".
			'if(wx.updateTimelineShareData){wx.updateTimelineShareData(s);}'."\n".
			'if(wx.onMenuShareAppMessage){wx.onMenuShareAppMessage(s);}'."\n".
			'if(wx.onMenuShareTimeline){wx.onMenuShareTimeline(s);}'."\n".
			'}catch(e){if(window.console){console.log("[wxshare] jweixin apply EXCEPTION", e&&e.message);}}'."\n".
			'};'."\n".
			'window.__wxshareStart=function(){'."\n".
			'if(typeof wx==="undefined"){if(window.console){console.log("[wxshare] wx undefined, abort");}return;}'."\n".
			'wx.error(function(res){if(window.console){console.log("[wxshare] CONFIG ERROR", JSON.stringify(res));}});'."\n".
			'wx.config('.$configJson.');'."\n".
			'wx.ready(function(){if(window.console){console.log("[wxshare] ready fired");}window.__wxshareApply();});'."\n".
			'setTimeout(window.__wxshareApply,800);'."\n".
			'setTimeout(window.__wxshareApply,1800);'."\n".
			'};'."\n".
			'window.__wxshareBridge=function(){'."\n".
			'if(typeof WeixinJSBridge==="undefined"){return;}'."\n".
			'var s=window.__wxshareData;'."\n".
			'try{'."\n".
			'WeixinJSBridge.on("menu:share:appmessage",function(){WeixinJSBridge.invoke("sendAppMessage",{title:s.title,desc:s.desc,link:s.link,img_url:s.imgUrl,img_width:"200",img_height:"200"},function(){});});'."\n".
			'WeixinJSBridge.on("menu:share:timeline",function(){WeixinJSBridge.invoke("shareTimeline",{title:s.title,desc:s.desc,link:s.link,img_url:s.imgUrl,img_width:"200",img_height:"200"},function(){});});'."\n".
			'if(window.console){console.log("[wxshare] legacy bridge handlers registered");}'."\n".
			'}catch(e){if(window.console){console.log("[wxshare] bridge EXCEPTION", e&&e.message);}}'."\n".
			'};'."\n".
			'if(typeof WeixinJSBridge!=="undefined"){window.__wxshareBridge();}else{document.addEventListener("WeixinJSBridgeReady",window.__wxshareBridge,false);}'."\n".
			'if(typeof wx!=="undefined"&&typeof wx.updateAppMessageShareData==="function"){window.__wxshareStart();}'."\n".
			'else{var __s=document.createElement("script");__s.src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js";__s.onload=window.__wxshareStart;document.head.appendChild(__s);}'."\n".
			'</script>'."\n";

		return $html;
	}

}
