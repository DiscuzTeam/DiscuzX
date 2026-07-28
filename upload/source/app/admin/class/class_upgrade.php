<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

namespace admin;

use table_common_card;
use filesock_curl;
use ZipArchive;
use DB;

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class class_upgrade {

	const ApiUrl = 'https://addon.dismall.com/api/discuzupgrade/?ver=X5_EN';

	const RemoteBasePath = 'upload/';

	const RemoteMd5 = '/source/data/admincp/discuzfiles.md5';
	const RemoteVer = '/source/discuz_version.php';

	public $readmeUrl = '';

	public function getVersion($silent = false) {
		$current = $this->getCurrentData(true);
		$remote = $this->getRemoteData(true, $silent);
		$currentd = substr($current['ver'], 1).'0'.sprintf('%02d', intval(substr($current['subver'], 1))).$current['release'];
		$remoted = !empty($remote['ver']) ? substr($remote['ver'], 1).'0'.sprintf('%02d', intval(substr($remote['subver'], 1))).$remote['release'] : 0;
		return [
			$current['ver'].$current['subver'].' Release '.$current['release'],
			!empty($remote['ver']) ? $remote['ver'].$remote['subver'].' Release '.$remote['release'] : '',
			$currentd < $remoted,
		];
	}

	public function clearEvent() {
		$this->_deltree($this->getExtractDir());
		$this->_deltree($this->getPatchDir());
		@unlink($this->getDownFile());
		@unlink($this->getPatchFile());
	}

	public function check() {
		$this->currentData = $this->getCurrentData();
		$this->remoteData = $this->getRemoteData();
		$this->currentFile = array_keys($this->currentData);
		$this->remoteFile = array_keys($this->remoteData);
	}

	public function getDownFile() {
		return sys_get_temp_dir().'/DZNew.zip';
	}

	public function getExtractDir() {
		return sys_get_temp_dir().'/DZExtract/';
	}

	public function getPatchDir() {
		return sys_get_temp_dir().'/DZPatch/';
	}

	public function getPatchFile() {
		return sys_get_temp_dir().'/DZPatch.zip';
	}

	public function createPatch() {
		$newFiles = array_diff($this->remoteFile, $this->currentFile);
		$modFiles = [];
		foreach(array_intersect($this->currentFile, $this->remoteFile) as $file) {
			if($this->currentData[$file] != $this->remoteData[$file]) {
				$modFiles[] = $file;
			}
		}
		$this->pPath = $this->getPatchDir();
		$this->ePath .= '/';
		$this->_deltree($this->pPath);
		foreach($newFiles as $file) {
			if(str_starts_with($file, './')) {
				$file = substr($file, 2);
			}
			$from = $this->ePath.self::RemoteBasePath.$file;
			if(!file_exists($from) || !filesize($from)) {
				continue;
			}
			mkdir(dirname($this->pPath.$file), 0777, true);
			copy($from, $this->pPath.$file);
		}
		foreach($modFiles as $file) {
			$from = $this->ePath.self::RemoteBasePath.$file;
			if(!file_exists($from) || !filesize($from)) {
				continue;
			}
			mkdir(dirname($this->pPath.$file), 0777, true);
			copy($from, $this->pPath.$file);
		}
		if(!is_dir($this->pPath)) {
			$this->clearEvent();
			cpmsg('upgrade_latest', '', 'succeed');
		}
		$newMd5file = substr(self::RemoteMd5, 1);
		mkdir(dirname($this->pPath.$newMd5file), 0777, true);
		copy($this->ePath.self::RemoteBasePath.$newMd5file, $this->pPath.$newMd5file);

		$patchFile = $this->getPatchFile();
		$zip = new ZipArchive;
		@unlink($patchFile);
		if($zip->open($patchFile, ZipArchive::CREATE) !== TRUE) {
			$this->clearEvent();
			cpmsg('upgrade_patch_create_failed', '', 'error');
		}
		$this->_zip($zip, $this->pPath, strlen($this->pPath.'/'));
		$zip->close();
	}

	private function _getApiData($silent = false) {
		$c = new filesock_curl();
		$c->unsafe = true;
		$c->returnbody = true;
		$c->conntimeout = 10;
		$c->timeout = 10;
		$c->request(['url' => self::ApiUrl]);
		if($c->curlstatus['http_code'] != 200) {
			if(!$silent) {
				cpmsg('upgrade_remote_get_failed', '', 'error');
			} else {
				return false;
			}
		}
		$data = json_decode($c->filesockbody, true);
		return !empty($data['ver']) && !empty($data['release']) && !empty($data['url']) && !empty($data['md5']) ? $data : ['code' => 0];
	}

	private function _zip(&$zip, $path, $basePathLen) {
		$handler = opendir($path);
		while(($filename = readdir($handler)) !== false) {
			if($filename == '.' || $filename == '..') {
				continue;
			}
			$f = $path.'/'.$filename;
			$lpath = substr($f, $basePathLen);
			if(is_dir($f)) {
				$this->_zip($zip, $f, $basePathLen);
			} elseif(!$zip->addFile($f, $lpath)) {
				cpmsg('upgrade_patch_create_failed', '', 'error');
			}
		}
		@closedir($handler);
	}

	private function _splitMd5Data($data) {
		$md5Data = [];
		foreach($data as $line) {
			if(!$line) {
				continue;
			}
			$file = trim(substr($line, 34));
			$md5Data[$file] = substr($line, 0, 32);
		}
		ksort($md5Data);
		return $md5Data;
	}

	public function getCurrentData($verOnly = false) {
		require_once DISCUZ_ROOT.'./source/discuz_version.php';
		if($verOnly) {
			return ['ver' => DISCUZ_VERSION, 'subver' => DISCUZ_SUBVERSION, 'release' => DISCUZ_RELEASE];
		} else {
			if(!$data = @file('./source/data/admincp/discuzfiles.md5')) {
				cpmsg('filecheck_nofound_md5file', '', 'error');
			}

			return $this->_splitMd5Data($data);
		}
	}

	public function getCurrentDiff() {
		$diffData = [];
		foreach($this->currentData as $file => $md5) {
			if(is_dir(DISCUZ_ROOT.$file)) {
				continue;
			}
			if(md5_file(DISCUZ_ROOT.$file) != $md5) {
				$diffData[] = $file;
			}
		}
		return $diffData;
	}

	public function getRemoteData($verOnly = false, $silent = false) {
		set_time_limit(0);
		if(!($apiData = $this->_getApiData($silent))) {
			if(!$silent) {
				cpmsg('upgrade_remote_get_failed', extra: 'api error');
			} else {
				return [];
			}
		}
		if(!empty($apiData['readmeUrl'])) {
			$this->readmeUrl = $apiData['readmeUrl'];
		}
		if($verOnly) {
			return $apiData;
		}
		$file = $this->getDownFile();
		$this->ePath = $this->getExtractDir();
		if(!file_exists($file)) {
			$c = new filesock_curl();
			$c->unsafe = true;
			$c->returnbody = true;
			$c->conntimeout = 10;
			$c->timeout = 60;
			$c->request(['url' => $apiData['url']]);
			if(!empty($c->curlstatus['redirect_url'])) {
				$c->request(['url' => $c->curlstatus['redirect_url']]);
			}
			if($c->curlstatus['http_code'] != 200) {
				cpmsg('upgrade_remote_get_failed', extra: 'http_code: '.$c->curlstatus['http_code']);
			}
			if(empty($c->filesockbody)) {
				cpmsg('upgrade_remote_get_failed', extra: 'response is empty');
			}
			file_put_contents($file, $c->filesockbody);
			if(strtolower(md5_file($file)) != strtolower($apiData['md5'])) {
				@unlink($file);
				cpmsg('upgrade_remote_get_failed', extra: 'md5 check error');
			}
			$this->_deltree($this->ePath);
			$zip = new ZipArchive;
			if($zip->open($file) !== true) {
				@unlink($file);
				cpmsg('upgrade_remote_get_failed', extra: 'zip open error');
			}
			if(!$zip->extractTo($this->ePath)) {
				@unlink($file);
				cpmsg('upgrade_remote_get_failed', extra: 'zip extract error');
			}
			$zip->close();
		}

		if(!$data = @file($this->ePath.self::RemoteBasePath.self::RemoteMd5)) {
			cpmsg('filecheck_nofound_md5file', '', 'error');
		}

		$verData = file_get_contents($this->ePath.self::RemoteBasePath.self::RemoteVer);
		if(!$verData) {
			cpmsg('upgrade_remote_get_failed', extra: 'discuz_version.php not exists');
		}
		preg_match('/define\s*\(\s*[\'"]DISCUZ_VERSION[\'"]\s*,\s*[\'"]?([^\'"]+)[\'"]?\s*\)/i', $verData, $m);
		if(empty($m[1])) {
			cpmsg('upgrade_remote_get_failed', extra: 'DISCUZ_VERSION parse error');
		}
		$ver = $m[1];
		preg_match('/define\s*\(\s*[\'"]DISCUZ_SUBVERSION[\'"]\s*,\s*[\'"]?([^\'"]+)[\'"]?\s*\)/i', $verData, $m);
		if(empty($m[1])) {
			cpmsg('upgrade_remote_get_failed', extra: 'DISCUZ_SUBVERSION parse error');
		}
		$subver = $m[1];
		preg_match('/define\s*\(\s*[\'"]DISCUZ_RELEASE[\'"]\s*,\s*[\'"]?([^\'"]+)[\'"]?\s*\)/i', $verData, $m);
		if(empty($m[1])) {
			cpmsg('upgrade_remote_get_failed', extra: 'DISCUZ_RELEASE parse error');
		}
		if($apiData['ver'] != $ver || $apiData['subver'] != $subver || $apiData['release'] != $m[1]) {
			cpmsg('upgrade_remote_get_failed', extra: 'version is error');
		}
		return $this->_splitMd5Data($data);
	}

	private function _deltree($dir) {
		if($directory = @dir($dir)) {
			while($entry = $directory->read()) {
				if($entry == '.' || $entry == '..') {
					continue;
				}
				$filename = $dir.'/'.$entry;
				if(is_file($filename)) {
					@unlink($filename);
				} else {
					$this->_deltree($filename);
				}
			}
			$directory->close();
			@rmdir($dir);
		}
	}

}