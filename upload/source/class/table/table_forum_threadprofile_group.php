<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_threadprofile_group extends discuz_table {
	public static function t() {
		static $_instance;
		if(!isset($_instance)) {
			$_instance = new self();
		}
		return $_instance;
	}

	public function __construct() {

		$this->_table = 'forum_threadprofile_group';
		$this->_pk = 'gid';

		parent::__construct();
	}

	public function fetch_all($ids = null, $force_from_db = false) {
		// $ids = null 需要在取消兼容层后删除
		if(defined('DISCUZ_DEPRECATED')) {
			throw new Exception('NotImplementedException');
			return parent::fetch_all($ids, $force_from_db);
		} else {
			return $this->fetch_all_threadprofile();
		}
	}

	public function fetch_all_threadprofile() {
		return DB::fetch_all('SELECT * FROM %t', [$this->table], $this->_pk);
	}

	public function delete_by_tpid($tpid) {
		DB::delete($this->table, DB::field('tpid', $tpid));
	}

}

