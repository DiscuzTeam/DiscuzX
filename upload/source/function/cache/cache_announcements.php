<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_announcements() {
	$rows = table_forum_announcement::t()->fetch_all_by_date(TIMESTAMP);
	$data = [];
	foreach($rows as $key => $datarow) {
		$datarow['groups'] = empty($datarow['groups']) ? [] : explode(',', $datarow['groups']);
		$data[$key] = $datarow;
	}

	savecache('announcements', $data);
}

