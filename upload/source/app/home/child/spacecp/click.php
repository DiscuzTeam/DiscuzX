<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$clickid = empty($_GET['clickid']) ? 0 : intval($_GET['clickid']);
$idtype = empty($_GET['idtype']) ? '' : trim($_GET['idtype']);
$id = empty($_GET['id']) ? 0 : intval($_GET['id']);

loadcache('click');
$clicks = empty($_G['cache']['click'][$idtype]) ? [] : $_G['cache']['click'][$idtype];
$click = $clicks[$clickid];

if(empty($click)) {
	showmessage('click_error');
}

switch($idtype) {
	case 'picid':
		if(!$_G['setting']['albumstatus']) {
			showmessage('album_status_off');
		}
		$item = table_home_pic::t()->fetch($id);
		if($item) {
			$picfield = table_home_picfield::t()->fetch($id);
			$album = table_home_album::t()->fetch_album($item['albumid']);
			$item['hotuser'] = $picfield['hotuser'];
			$item['friend'] = $album['friend'];
			$item['username'] = $album['username'];
		}
		$tablename = 'home_pic';
		break;
	case 'aid':
		if(!$_G['setting']['portalstatus']) {
			showmessage('portal_status_off');
		}
		$item = table_portal_article_title::t()->fetch($id);
		$tablename = 'portal_article_title';
		break;
	default:
		if(!$_G['setting']['blogstatus']) {
			showmessage('blog_status_off');
		}
		$idtype = 'blogid';
		$item = array_merge(
			table_home_blog::t()->fetch($id),
			table_home_blogfield::t()->fetch($id)
		);
		$tablename = 'home_blog';
		break;
}
if(!$item) {
	showmessage('click_item_error');
}

$hash = dsign($item['uid']."\t".$item['dateline']);
if($_GET['op'] == 'add') {
	if(!checkperm('allowclick') || $_GET['hash'] != $hash) {
		showmessage('no_privilege_click');
	}

	if($item['uid'] == $_G['uid']) {
		showmessage('click_no_self');
	}

	if(isblacklist($item['uid'])) {
		showmessage('is_blacklist');
	}

	if(table_home_clickuser::t()->count_by_uid_id_idtype($space['uid'], $id, $idtype)) {
		showmessage('click_have');
	}

	$setarr = [
		'uid' => $space['uid'],
		'username' => $_G['username'],
		'id' => $id,
		'idtype' => $idtype,
		'clickid' => $clickid,
		'dateline' => $_G['timestamp']
	];
	table_home_clickuser::t()->insert($setarr);

	C::t($tablename)->update_click($id, $clickid, 1);

	hot_update($idtype, $id, $item['hotuser']);

	$q_note = '';
	$q_note_values = [];

	$fs = [];
	switch($idtype) {
		case 'blogid':
			$fs['title_template'] = 'feed_click_blog';
			$fs['title_data'] = [
				'touser' => "<a href=\"home.php?mod=space&uid={$item['uid']}\">{$item['username']}</a>",
				'subject' => "<a href=\"home.php?mod=space&uid={$item['uid']}&do=blog&id={$item['blogid']}\">{$item['subject']}</a>",
				'click' => $click['name']
			];

			$q_note = 'click_blog';
			$q_note_values = [
				'url' => "home.php?mod=space&uid={$item['uid']}&do=blog&id={$item['blogid']}",
				'subject' => $item['subject'],
				'from_id' => $item['blogid'],
				'from_idtype' => 'blogid'
			];
			break;
		case 'aid':
			require_once libfile('function/portal');
			$article_url = fetch_article_url($item);
			$fs['title_template'] = 'feed_click_article';
			$fs['title_data'] = [
				'touser' => "<a href=\"home.php?mod=space&uid={$item['uid']}\">{$item['username']}</a>",
				'subject' => "<a href=\"$article_url\">{$item['title']}</a>",
				'click' => $click['name']
			];

			$q_note = 'click_article';
			$q_note_values = [
				'url' => $article_url,
				'subject' => $item['title'],
				'from_id' => $item['aid'],
				'from_idtype' => 'aid'
			];
			break;
		case 'picid':
			$fs['title_template'] = 'feed_click_pic';
			$fs['title_data'] = [
				'touser' => "<a href=\"home.php?mod=space&uid={$item['uid']}\">{$item['username']}</a>",
				'click' => $click['name']
			];
			$fs['images'] = [pic_get($item['filepath'], 'album', $item['thumb'], $item['remote'])];
			$fs['image_links'] = ["home.php?mod=space&uid={$item['uid']}&do=album&picid={$item['picid']}"];
			$fs['body_general'] = $item['title'];

			$q_note = 'click_pic';
			$q_note_values = [
				'url' => "home.php?mod=space&uid={$item['uid']}&do=album&picid={$item['picid']}",
				'from_id' => $item['picid'],
				'from_idtype' => 'picid'
			];
			break;
	}

	if(empty($item['friend']) && ckprivacy('click', 'feed')) {
		require_once libfile('function/feed');
		$fs['title_data']['hash_data'] = "{$idtype}{$id}";
		feed_add('click', $fs['title_template'], $fs['title_data'], '', [], $fs['body_general'], $fs['images'], $fs['image_links']);
	}

	updatecreditbyaction('click', 0, [], $idtype.$id);

	require_once libfile('function/stat');
	updatestat('click');

	notification_add($item['uid'], 'click', $q_note, $q_note_values);

	showmessage('click_success', '', ['idtype' => $idtype, 'id' => $id, 'clickid' => $clickid], ['msgtype' => 3, 'showmsg' => true, 'closetime' => true]);

} elseif($_GET['op'] == 'show') {

	$maxclicknum = 0;
	foreach($clicks as $key => $value) {
		$value['clicknum'] = $item["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$perpage = 18;
	$page = intval($_GET['page']);
	$start = ($page - 1) * $perpage;
	if($start < 0) $start = 0;

	$count = table_home_clickuser::t()->count_by_id_idtype($id, $idtype);
	$clickuserlist = [];
	$click_multi = '';

	if($count) {
		foreach(table_home_clickuser::t()->fetch_all_by_id_idtype($id, $idtype, $start, $perpage) as $value) {
			$value['clickname'] = $clicks[$value['clickid']]['name'];
			$clickuserlist[] = $value;
		}

		$click_multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=click&op=show&clickid=$clickid&idtype=$idtype&id=$id");
	}
}

include_once(template('home/spacecp_click'));

