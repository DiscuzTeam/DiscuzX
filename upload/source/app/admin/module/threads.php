<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/post');

cpheader();

$optype = $_GET['optype'];
$fromumanage = $_GET['fromumanage'] ? 1 : 0;

if((!$operation && !$optype) || ($operation == 'group' && empty($optype))) {
	require_once childfile('threads/form');
} else {
	require_once childfile('threads/submit');
}

function delete_position($select) {
	if(empty($select) || !is_array($select)) {
		cpmsg('select_thread_empty', '', 'error');
	}
	$tids = dimplode($select);
	table_forum_postposition::t()->delete_by_tid($select);
	table_forum_thread::t()->update_status_by_tid($tids, '1111111111111110', '&');
}

function js_cpmsg($cpmsg) {
	$cpmsg = cplang($cpmsg);
	$cpmsg = $cpmsg ? "alert('$cpmsg');" : '';
	echo '<script type="text/JavaScript">'.$cpmsg.'if(parent.$(\'threadforum\')) parent.$(\'threadforum\').searchsubmit.click();</script>';
	exit;
}