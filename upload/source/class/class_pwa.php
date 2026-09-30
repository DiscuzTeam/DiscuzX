<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class pwa {

	const FILES = [
		DISCUZ_ROOT.'./source/data/admincp/pwa_default.js' => 'pwa.js',
		DISCUZ_ROOT.'./source/data/admincp/pwa_manifest.json' => 'manifest.json',
	];

	public static function header() {
		global $_G;

		return '<meta name="theme-color" content="'.$_G['setting']['pwa']['theme_color'].'" />'.
			'<meta name="mobile-web-app-capable" content="yes" />'.
			'<meta name="apple-mobile-web-app-capable" content="yes" />'.
			'<meta name="apple-mobile-web-app-status-bar-style" content="default" />'.
			'<meta name="apple-mobile-web-app-title" content="'.$_G['setting']['bbname'].'" />'.
			'<link rel="manifest" href="'.$_G['setting']['jspath'].'manifest.json" />'.
			'<link rel="apple-touch-icon" href="'.$_G['setting']['attachurl'].'pwa/180.png" />'.
			'<link rel="icon" type="image/png" sizes="32x32" href="'.$_G['setting']['attachurl'].'pwa/32.png" />';
	}

	public static function updatecache() {
		$replace = self::_getVars();

		foreach(self::FILES as $f => $entry) {
			$data = file_get_contents($f);
			$data = str_replace(array_keys($replace), array_values($replace), $data);
			if(file_put_contents(DISCUZ_DATA.'./cache/'.$entry, $data, LOCK_EX) === false) {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
			oss::writeCache($entry);
			if($entry == 'pwa.js') {
				@copy(DISCUZ_DATA.'./cache/'.$entry, DISCUZ_ROOT.'/pwa.js');
			}
		}
	}

	private static function _getVars() {
		global $_G;

		$imgPath = $_G['setting']['attachurl'];
		$e_siteurl = parse_url($_G['siteurl']);
		$e_imgpath = parse_url($imgPath);
		if(empty($e_imgpath['host'])) {
			$imgPath = $e_siteurl['path'].$imgPath;
		}

		return [
			'{PWA_CACHE_NAME}' => 'DPWA_'.md5($_G['siteurl']),
			'{PWA_NAME}' => $_G['setting']['pwa']['name'] ?? '',
			'{PWA_SHORT_NAME}' => $_G['setting']['pwa']['short_name'] ?? '',
			'{PWA_DESCRIPTION}' => $_G['setting']['pwa']['description'] ?? '',
			'{PWA_BACKGROUND_COLOR}' => $_G['setting']['pwa']['background_color'] ?? '',
			'{PWA_THEME_COLOR}' => $_G['setting']['pwa']['theme_color'] ?? '',
			'{PATH}' => $e_siteurl['path'] ?? '/',
			'{PWA_CACHE_URLS_192}' => $imgPath.'pwa/192.png',
			'{PWA_CACHE_URLS_512}' => $imgPath.'pwa/512.png',
			'{PWA_CACHE_URLS_180}' => $imgPath.'pwa/180.png',
		];
	}
}
