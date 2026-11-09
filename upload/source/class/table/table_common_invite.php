<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_invite extends discuz_table {
	private $_uids = [];

	public static function t() {
		static $_instance;
		if(!isset($_instance)) {
			$_instance = new self();
		}
		return $_instance;
	}

	public function __construct() {

		$this->_table = 'common_invite';
		$this->_pk = 'id';

		parent::__construct();
	}

	public function fetch_by_id_uid($id, $uid) {
		return DB::fetch_first('SELECT * FROM %t WHERE id=%d AND uid=%d', [$this->_table, $id, $uid]);
	}

	public function fetch_by_code($code) {
		return DB::fetch_first('SELECT * FROM %t WHERE code=%s', [$this->_table, $code]);
	}

	public function update_consume($id, $uid, $fusername, array $extra = []) {
		$id = intval($id);
		$uid = intval($uid);
		if(!$id || !$uid) {
			return 0;
		}

		$data = array_merge($extra, [
			'fuid' => $uid,
			'fusername' => (string)$fusername,
		]);

		$condition = DB::field('id', $id).' AND '.DB::field('fuid', 0);
		DB::update($this->_table, $data, $condition, true);

		return DB::affected_rows();
	}

	public function fetch_all_by_uid($uid) {
		return DB::fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC', [$this->_table, $uid], $this->_pk);
	}

	public function fetch_all_invitenum_group_by_uid($dateline = 0, $start = 0, $limit = 0) {
		$sql = '';
		$parameter = [$this->_table];
		if($dateline) {
			$sql = ' AND dateline>%d';
			$parameter[] = $dateline;
		}
		return DB::fetch_all("SELECT count(*) AS invitenum ,uid FROM %t WHERE status=2 $sql GROUP BY uid ORDER BY invitenum DESC ".DB::limit($start, $limit), $parameter);
	}

	public function fetch_all_orderid($orderid) {
		return DB::fetch_all('SELECT * FROM %t WHERE orderid=%s', [$this->_table, $orderid]);
	}

	public function fetch_all_by_search($uid = 0, $fuid = 0, $fusername = '', $buydatestart = 0, $buydateend = 0, $inviteip = '', $code = '', $start = 0, $limit = 0) {
		$condition = $this->make_query_condition($uid, $fuid, $fusername, $buydatestart, $buydateend, $inviteip, $code);
		$data = [];
		$query = DB::query("SELECT * FROM %t $condition[0] ORDER BY id DESC ".DB::limit($start, $limit), $condition[1]);
		while($value = DB::fetch($query)) {
			$this->_uids[$value['uid']] = $value['uid'];
			$data[] = $value;
		}
		return $data;
	}

	public function count_by_uid_fuid($uid, $fuid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d AND fuid=%d', [$this->_table, $uid, $fuid]);
	}

	public function count_by_uid_dateline($uid, $dateline) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d AND dateline>%d', [$this->_table, $uid, $dateline]);
	}

	public function count_by_search($uid = 0, $fuid = 0, $fusername = '', $buydatestart = 0, $buydateend = 0, $inviteip = '', $code = '') {
		$condition = $this->make_query_condition($uid, $fuid, $fusername, $buydatestart, $buydateend, $inviteip, $code);
		return DB::result_first('SELECT COUNT(*) FROM %t '.$condition[0], $condition[1]);
	}

	public function delete_by_uid_or_fuid($uids) {
		$uids = dintval($uids, true);
		if(!$uids) {
			return null;
		}
		DB::delete($this->_table, DB::field('uid', $uids).' OR '.DB::field('fuid', $uids));
	}

	public function get_uids() {
		return $this->_uids;
	}

	private function make_query_condition($uid = 0, $fuid = 0, $fusername = '', $buydatestart = 0, $buydateend = 0, $inviteip = '', $code = '') {
		$parameter = [$this->_table];
		$wherearr = [];
		if(!empty($uid)) {
			$parameter[] = $uid;
			$wherearr[] = 'uid=%d';
		}
		if(!empty($fuid)) {
			$parameter[] = $fuid;
			$wherearr[] = 'fuid=%d';
		}
		if(!empty($fusername)) {
			$parameter[] = $fusername;
			$wherearr[] = 'fusername=%s';
		}
		if(!empty($code)) {
			$parameter[] = $code;
			$wherearr[] = 'code=%s';
		}
		if($buydatestart) {
			$parameter[] = $buydatestart;
			$wherearr[] = 'dateline>%d';
		}
		if($buydateend) {
			$parameter[] = $buydateend;
			$wherearr[] = 'dateline<%d';
		}
		if(!empty($inviteip)) {
			$parameter[] = '%'.$inviteip.'%';
			$wherearr[] = 'inviteip LIKE %s';
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return [$wheresql, $parameter];
	}

	public function delete_by_removetime($removetime) {
		return DB::query('DELETE FROM %t WHERE dateline < %d', [$this->_table, $removetime]);
	}
}

