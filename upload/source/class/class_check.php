<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class check {

	public function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
		$dir = @opendir(DISCUZ_ROOT.$currentdir);
		$exts = '/('.$ext.')$/i';
		$skips = explode(',', $skip);

		if(!$dir) {
			return;
		}

		while($entry = @readdir($dir)) {
			$file = $currentdir.$entry;
			if($entry != '.' && $entry != '..' && (($ext && preg_match($exts, $entry) || !$ext) || $sub && is_dir($file)) && !in_array($entry, $skips)) {
				if($sub && is_dir($file)) {
					$this->checkfiles($file.'/', $ext, $sub, $skip);
				} else {
					if(is_dir($file)) {
						$this->md5data[$file] = md5($file);
					} else {
						$this->md5data[$file] = md5_file($file);
					}
				}
			}
		}
	}

	public function checkcachefiles($currentdir) {
		global $_G;
		$dir = opendir($currentdir);
		$exts = '/\.php$/i';
		$showlist = $modifylist = $addlist = [];
		while($entry = readdir($dir)) {
			$file = $currentdir.$entry;
			if($entry != '.' && $entry != '..' && preg_match($exts, $entry)) {
				$fp = fopen($file, 'rb');
				$cachedata = fread($fp, filesize($file));
				fclose($fp);

				if(preg_match("/^<\?php\n\/\/Discuz! cache file, DO NOT modify me!\n\/\/Identify: (\w+)\n\n(.+?)\?>$/s", $cachedata, $match)) {
					$showlist[$file] = $md5 = $match[1];
					$cachedata = $match[2];

					if(md5($entry.$cachedata.$_G['config']['security']['authkey']) != $md5) {
						$modifylist[$file] = $md5;
					}
				} else {
					$showlist[$file] = '';
				}
			}
		}

		return [$showlist, $modifylist, $addlist];
	}

	public function run() {
		if(!$discuzfiles = @file(DISCUZ_ROOT.'./source/data/admincp/discuzfiles.md5')) {
			return false;
		}

		$this->md5data = $md5datanew = $addlist = $dellist = $modifylist = $showlist = [];
		$cachelist = $this->checkcachefiles('data/sysdata/');
		$this->checkfiles('./', '', 0);
		$this->checkfiles('api/', '\.php|\.htm|\.pem');
		$this->checkfiles('archiver/', '\.php', 0);
		$this->checkfiles('avatar/', '\.php', 0);
		$this->checkfiles('config/', '\.php|\.htm', 1, 'config_global.php,config_ucenter.php');
		$this->checkfiles('data/', '\.xml|\.htm', 0);
		$this->checkfiles('data/avatar/', '\.htm', 0);
		$this->checkfiles('data/log/', '\.htm', 0);
		$this->checkfiles('data/cache/', '\.htm', 0);
		$this->checkfiles('data/template/', '\.htm', 0);
		$this->checkfiles('data/plugindata/', '\.htm', 0);
		$this->checkfiles('data/threadcache/', '\.htm', 0);
		$this->checkfiles('data/download/', '\.htm', 0);
		$this->checkfiles('data/addonmd5/', '\.htm', 0);
		$this->checkfiles('source/', '\.php|\.md5|\.htm|\.table|\.xml', 0);
		$this->checkfiles('source/data/', '\.md5|\.htm|\.table|\.xml|\.dat|\.txt|\.png|\.jpg|\.gif|\.ttf|\.php|\.js|\.json', 1, 'discuzfiles.md5');
		$this->checkfiles('source/archiver/', '\.php|\.md5|\.htm|\.table|\.xml');
		$this->checkfiles('source/app/', '\.php|\.htm');
		$this->checkfiles('source/i18n/', '\.php|\.htm');
		$this->checkfiles('source/child/', '\.php|\.htm');
		$this->checkfiles('source/class/', '\.php|\.htm');
		$this->checkfiles('source/function/', '\.php|\.htm');
		$this->checkfiles('static/', '\.js|\.png|\.json|\.css|\.jpg|\.gif|\.txt|\.htm|\.ico|\.swf|\.ttf|\.mp3|\.xml|\.svg|\.woff|\.woff2|\.map|\.eot');
		$this->checkfiles('template/', '\.php|\.htm|\.css|\.jpg|\.xml|\.json|\.gif|\.png|\.eot|\.svg|\.ttf|\.woff|\.woff2|\.js');

		$md5data = &$this->md5data;

		table_common_cache::t()->insert([
			'cachekey' => 'checktools_filecheck',
			'cachevalue' => serialize(['dateline' => $_G['timestamp']]),
			'dateline' => $_G['timestamp'],
		], false, true);

		foreach($discuzfiles as $line) {
			$file = trim(substr($line, 34));
			$md5datanew[$file] = substr($line, 0, 32);
			if($md5datanew[$file] != $md5data[$file]) {
				$modifylist[$file] = $md5data[$file];
			}
			$md5datanew[$file] = $md5data[$file];
		}

		$weekbefore = TIMESTAMP - 604800;
		$addlist = array_merge(array_diff_assoc($md5data, $md5datanew), is_array($cachelist[2]) ? $cachelist[2] : []);
		$dellist = array_diff_assoc($md5datanew, $md5data);
		$modifylist = array_merge(array_diff_assoc($modifylist, $dellist), is_array($cachelist[1]) ? $cachelist[1] : []);
		$showlist = array_merge($md5data, $md5datanew, $cachelist[0]);
		$doubt = 0;
		$dirlist = $dirlog = [];
		foreach($showlist as $file => $md5) {
			$dir = dirname($file);
			if(is_array($modifylist) && array_key_exists($file, $modifylist)) {
				$fileststus = 'modify';
			} elseif(is_array($dellist) && array_key_exists($file, $dellist)) {
				$fileststus = 'del';
			} elseif(is_array($addlist) && array_key_exists($file, $addlist)) {
				$fileststus = 'add';
			} else {
				$filemtime = @filemtime($file);
				if($filemtime > $weekbefore) {
					$fileststus = 'doubt';
					$doubt++;
				} else {
					$fileststus = '';
				}
			}
			if(file_exists($file)) {
				$filemtime = @filemtime($file);
				$fileststus && $dirlist[$fileststus][$dir][basename($file)] = [number_format(filesize($file)).' Bytes', dgmdate($filemtime)];
			} else {
				$fileststus && $dirlist[$fileststus][$dir][basename($file)] = ['', ''];
			}
		}

		$modifiedfiles = count($modifylist);
		$deletedfiles = count($dellist);
		$unknownfiles = count($addlist);
		$doubt = intval($doubt);

		$v = [$modifiedfiles, $deletedfiles, $unknownfiles, $doubt, $dirlist];

		table_common_cache::t()->insert([
			'cachekey' => 'checktools_filecheck_result',
			'cachevalue' => serialize($v),
			'dateline' => TIMESTAMP,
		], false, true);

		return $v;
	}

	const EXTENSIONS = [
		'mysqli' => ['mysqli_connect', 'mysqli_query'],
		'json' => ['json_encode', 'json_decode'],
		'mbstring' => ['mb_convert_encoding'],
		'curl' => ['curl_init', 'curl_setopt'],
		'openssl' => ['openssl_random_pseudo_bytes', 'openssl_sign'],
		'xml' => ['xml_parser_create'],
		'filter' => ['filter_var'],
		'ctype' => ['ctype_alnum'],
		'spl' => ['spl_autoload_register'],
	];

	private static function _extensionCheck($extension, $testFunctions = []) {
		if($extension && !extension_loaded($extension)) {
			return ['extension', $extension];
		}

		if(!empty($testFunctions)) {
			foreach($testFunctions as $func) {
				if(!function_exists($func) && !class_exists($func)) {
					return ['function', $func.'()'];
				}
			}
		}

		return ['', ''];
	}

	public static function extensions() {
		$must = [];
		$extensions = self::EXTENSIONS;
		foreach($extensions as $ext => $funcs) {
			list($type, $name) = self::_extensionCheck($ext, $funcs);
			if(!empty($type)) {
				$must[$type][] = $name;
			}
		}

		return $must;
	}
}