<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_payment_refund extends discuz_table {
	public static function t() {
		static $_instance;
		if(!isset($_instance)) {
			$_instance = new self();
		}
		return $_instance;
	}

	public function __construct() {
		$this->_table = 'common_payment_refund';
		$this->_pk = 'id';
		parent::__construct();
	}

	public function fetch_by_no($refund_no) {
		return DB::fetch_first('SELECT * FROM %t WHERE out_biz_no = %s', [$this->_table, $refund_no]);
	}

	public function fetch_all_by_orders($ids) {
		return DB::fetch_all('SELECT * FROM %t WHERE `order_id` in (%n)', [$this->_table, $ids]);
	}

	public function fetch_all_processing_by_orders($ids) {
		return DB::fetch_all('SELECT * FROM %t WHERE `order_id` in (%n) AND `status` = -1', [$this->_table, $ids]);
	}

	public function update_refund_by_no($refund_no, $data) {
		DB::update($this->_table, $data, DB::field('out_biz_no', $refund_no));
	}

	public function sum_by_orders($ids) {
		return DB::fetch_all('SELECT `order_id`, sum(`amount`) as `amount` FROM %t WHERE `order_id` in (%n) AND `status` = 2 GROUP BY `order_id`', [$this->_table, $ids], 'order_id');
	}

	public function sum_processing_by_orders($ids) {
		return DB::fetch_all('SELECT `order_id`, sum(`amount`) as `amount` FROM %t WHERE `order_id` in (%n) AND `status` = -1 GROUP BY `order_id`', [$this->_table, $ids], 'order_id');
	}
}

