<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_docomment extends discuz_table {
	public static function t() {
		static $_instance;
		if(!isset($_instance)) {
			$_instance = new self();
		}
		return $_instance;
	}

	public function __construct() {

		$this->_table = 'home_docomment';
		$this->_pk = 'id';

		parent::__construct();
	}

	public function delete_by_doid_uid($doids = null, $uids = null) {
		$sql = [];
		$doids && $sql[] = DB::field('doid', $doids);
		$uids && $sql[] = DB::field('uid', $uids);
		if($sql) {
			return DB::query('DELETE FROM %t WHERE %i', [$this->_table, implode(' OR ', $sql)]);
		} else {
			return false;
		}
	}

	public function fetch_all_by_doid($doids) {
		if(empty($doids)) {
			return [];
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('doid', $doids).' ORDER BY dateline', [$this->_table]);
	}
	
	public function count_top_by_doid($doid) {
		if(empty($doid)) {
			return 0;
		}
		return intval(DB::result_first("SELECT COUNT(*) FROM %t WHERE doid=%d AND upid=0 AND uid>0 AND message!=''", [$this->_table, $doid]));
	}
	
	public function fetch_top_by_doid($doid, $limit = 0) {
		if(empty($doid)) {
			return [];
		}
		// 将参数转换为整数类型，避免类型不兼容错误
		$doid = intval($doid);
		$limit = intval($limit);
		// 顶级评论按时间倒序，最新的在最前面
		return DB::fetch_all("SELECT * FROM %t WHERE doid=%d AND upid=0 AND uid>0 AND message!='' ORDER BY dateline DESC ".DB::limit(0, $limit), [$this->_table, $doid]);
	}
	
	public function fetch_all_top_by_doid($doid, $start = 0, $limit = 0) {
		if(empty($doid)) {
			return [];
		}
		// 将参数转换为整数类型，避免类型不兼容错误
		$doid = intval($doid);
		$start = intval($start);
		$limit = intval($limit);
		// 顶级评论按时间倒序，最新的在最前面
		return DB::fetch_all("SELECT * FROM %t WHERE doid=%d AND upid=0 AND uid>0 AND message!='' ORDER BY dateline DESC ".DB::limit($start, $limit), [$this->_table, $doid]);
	}
	
	public function fetch_all_child_by_doid($doid) {
		if(empty($doid)) {
			return [];
		}
		return DB::fetch_all("SELECT * FROM %t WHERE doid=%d AND upid>0 AND uid>0 AND message!='' ORDER BY dateline", [$this->_table, $doid]);
	}

	// 列表页批量取每条记录最热门的前N条一级评论（热度=回复数>推荐数>时间）
	// 注：SQL 安全检测禁止子查询，改为逐条主键查询（每页 ≤20 条记录，走 doid 索引，开销可忽略）
	public function fetch_hot_top_by_doids($doids, $limit = 3) {
		$doids = array_filter(array_map('intval', (array)$doids));
		if(empty($doids)) {
			return [];
		}
		$limit = max(1, intval($limit));
		$result = [];
		foreach($doids as $doid) {
			$rows = DB::fetch_all("SELECT * FROM %t WHERE doid=%d AND upid=0 AND uid>0 AND message!='' ORDER BY replynum DESC, recomends DESC, dateline DESC ".DB::limit(0, $limit), [$this->_table, $doid]);
			foreach($rows as $row) {
				$result[] = $row;
			}
		}
		return $result;
	}

	// 批量统计每条记录的一级评论总数（用于列表页「查看全部」入口）
	public function count_top_by_doids($doids) {
		if(empty($doids)) {
			return [];
		}
		$w = DB::field('doid', $doids);
		return DB::fetch_all("SELECT doid, COUNT(*) AS cnt FROM %t WHERE $w AND upid=0 AND uid>0 AND message!='' GROUP BY doid", [$this->_table]);
	}

}

