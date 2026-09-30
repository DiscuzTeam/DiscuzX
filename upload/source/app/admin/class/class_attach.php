<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

namespace admin;

use discuz_upload;

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class class_attach {

	public static function getUrl($value, $path = 'common') {
		global $_G;

		if(strexists($value, '{STATICURL}')) {
			$return = str_replace('{STATICURL}', STATICURL, $value);
			if(!preg_match('/^'.preg_quote(STATICURL, '/').'/i', $return) && !(($valueparse = parse_url($return)) && isset($valueparse['host']))) {
				$return = $return.'?'.random(6);
			}
		} elseif(self::_inWhiteList($value)) {
			$return = $value;
		} else {
			$valueparse = parse_url($value);
			if(!isset($valueparse['host']) && !str_starts_with($value, $_G['setting']['attachurl'].$path.'/')) {
				$return = $_G['setting']['attachurl'].$path.'/'.$value;
			} else {
				$return = $value;
			}
		}
		return $return;
	}

	public static function upload($file, $path = 'common', $subdir = '', $dirtype = 1, $filename = '') {
		global $_G;

		$upload = new discuz_upload();
		if($upload->init($file, $path, subdir: $subdir, dirtype: $dirtype, filename: $filename) && $upload->save()) {
			return $_G['setting']['attachurl'].$path.'/'.$upload->attach['attachment'];
		} else {
			return '';
		}
	}

	public static function delete($value, $path = 'common') {
		global $_G;

		if(self::_inWhiteList($value, '{STATICURL}')) {
			return;
		}

		if(str_starts_with($value, $_G['setting']['attachurl'].$path.'/')) {
			$value = str_replace($_G['setting']['attachurl'].$path.'/', '', $value);
		}

		$valueparse = parse_url($value);
		if(!isset($valueparse['host'])) {
			$value = str_replace(['..', '//'], ['', '/'], $value);
			@unlink($_G['setting']['attachdir'].$path.'/'.$value);
			ftpcmd('delete', $path.'/'.$value);
		}
	}

	private static function _inWhiteList($value) {
		return str_starts_with($value, 'template/') || str_starts_with($value, 'source/plugin/');
	}

}