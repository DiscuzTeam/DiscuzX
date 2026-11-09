<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$tagarray = [];
if(submitcheck('submit') && !empty($_GET['ctidarray']) && is_array($_GET['ctidarray']) && count($_GET['ctidarray']) && !empty($_GET['operate_type'])) {
	$class_tag = new tag();
	$ctidarray = [];
	$operate_type = $_GET['operate_type'];
	$ctidarray = $_GET['ctidarray'];
	if($operate_type == 'delete') {
		require_once libfile('function/delete');
		foreach($ctidarray as $ctid) {
			deletecollection($ctid);
		}
	}
	cpmsg('collection_admin_updated', 'action=collection&operation=admin&searchsubmit=yes&perpage='.$_GET['perpage'].'&page='.$_GET['page'], 'succeed');
}
/*search={"collection":"action=collection"}*/
if(!submitcheck('searchsubmit', 1)) {
	showformheader('collection&operation=admin');
	showtableheader();
	showsetting('collection_name', 'collection_name', $collection_name, 'text');
	showsetting('collection_ctid', 'collection_ctid', $collection_ctid, 'text');
	showsetting('collection_username', 'collection_username', $collection_username, 'text');
	showsetting('collection_uid', 'collection_uid', $collection_uid, 'text');
	showsetting('feed_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>{$lang['perpage_20']}</option><option value='50'>{$lang['perpage_50']}</option><option value='100'>{$lang['perpage_100']}</option></select>");
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
} else {
	$collection_name = trim($_GET['collection_name']);
	$collection_ctid = dintval($_GET['collection_ctid']);
	$collection_username = trim($_GET['collection_username']);
	$collection_uid = dintval($_GET['collection_uid']);


	$ppp = $_GET['perpage'];
	$startlimit = ($page - 1) * $ppp;
	$multipage = '';
	$totalcount = table_forum_collection::t()->fetch_all_for_search($collection_name, $collection_ctid, $collection_username, $collection_uid, -1);
	$multipage = multi($totalcount, $ppp, $page, ADMINSCRIPT."?action=collection&operation=admin&searchsubmit=yes&collection_name=$collection_name&collection_ctid=$collection_ctid&collection_username=$collection_username&collection_uid=$collection_uid&perpage=$ppp&status=$status");
	$collection = table_forum_collection::t()->fetch_all_for_search($collection_name, $collection_ctid, $collection_username, $collection_uid, $startlimit, $ppp);
	showformheader('collection&operation=admin');
	showtableheader(cplang('collection_result').' '.$totalcount.' <a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=collection&operation=admin\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
	showhiddenfields(['page' => $_GET['page'], 'collection_name' => $collection_name, 'collection_ctid' => $collection_ctid, 'perpage' => $ppp]);
	showsubtitle(['', 'collection_name', 'collection_username', 'collection_date', 'collection_recommend']);
	foreach($collection as $uniquecollection) {
		showtablerow('', ['class="td25"', 'width=400', ''], [
			"<input class=\"checkbox\" type=\"checkbox\" name=\"ctidarray[]\" value=\"{$uniquecollection['ctid']}\" />",
			"<a href='forum.php?mod=collection&action=view&ctid={$uniquecollection['ctid']}' target='_blank'>{$uniquecollection['name']}</a>",
			"<a href='home.php?mod=space&uid={$uniquecollection['uid']}' target='_blank'>{$uniquecollection['username']}</a>",
			dgmdate($uniquecollection['dateline']),
			"<a href='".ADMINSCRIPT."?action=collection&operation=recommend&recommentctid={$uniquecollection['ctid']}&formhash=".FORMHASH."'>".cplang('collection_recommend').'</a>',
		]);
	}
	showtablerow('', ['class="td25" colspan="3"'], ['<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ctidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>']);
	showtablerow('', ['class="td25"', 'colspan="2"'], [
		cplang('operation'),
		'<input class="radio" type="radio" name="operate_type" value="delete"> '.cplang('delete').' '
	]);
	showsubmit('submit', 'submit', '', '', $multipage);
	showtablefooter();
	showformfooter();
}
/*search*/
	