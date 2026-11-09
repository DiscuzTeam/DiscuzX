<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(is_numeric($_GET['recommentctid'])) {
	if(empty($_GET['formhash']) || FORMHASH != $_GET['formhash']) {
		cpmsg('undefined_action');
	}
	$collectiondata = table_forum_collection::t()->fetch($_GET['recommentctid']);
	if($collectiondata['ctid']) {
		$collectionrecommend = $_G['setting']['collectionrecommend'] ? dunserialize($_G['setting']['collectionrecommend']) : [];
		$collectionrecommend['ctids'][$collectiondata['ctid']] = 0;
		$collectionrecommend['ctids'] = removeNonExistsCollection($collectionrecommend['ctids']);
		$collectionrecommend['adminrecommend'] = count($collectionrecommend['ctids']);
		asort($collectionrecommend['ctids']);
		$data = ['collectionrecommendnum' => $collectionrecommend['autorecommend'] + $collectionrecommend['adminrecommend'], 'collectionrecommend' => $collectionrecommend];
		table_common_setting::t()->update_batch($data);
		updatecache('setting');
		savecache('collection_index', []);
	}
	cpmsg('collection_admin_updated', 'action=collection&operation=recommend', 'succeed');
}
/*search={"collection":"action=collection"}*/
if(!submitcheck('submit', 1)) {
	$ctidarray = [];
	$collectionrecommend = dunserialize($_G['setting']['collectionrecommend']);


	showformheader('collection&operation=recommend');
	showtableheader(cplang('collection_recommend_settings'), 'nobottom');
	showsetting('collection_recommend_index_autonumber', 'settingnew[autorecommend]', $collectionrecommend['autorecommend'] ? $collectionrecommend['autorecommend'] : 0, 'text');
	showtableheader(cplang('collection_recommend_existed'), 'nobottom');
	showhiddenfields(['page' => $_GET['page'], 'tagname' => $tagname, 'status' => $status, 'perpage' => $ppp]);
	showsubtitle(['', 'collection_name', 'collection_username', 'collection_threadnum', 'collection_commentnum', 'collection_date', 'display_order']);

	if($collectionrecommend['ctids']) {
		$collectiondata = table_forum_collection::t()->fetch_all(array_keys($collectionrecommend['ctids']));
		foreach($collectiondata as $collection) {
			showtablerow('', ['class="td25"', 'width=400', ''], [
				"<input class=\"checkbox\" type=\"checkbox\" name=\"ctidarray[]\" value=\"{$collection['ctid']}\" />",
				"<a href='forum.php?mod=collection&action=view&ctid={$collection['ctid']}' target='_blank'>{$collection['name']}</a>",
				"<a href='home.php?mod=space&uid={$collection['uid']}' target='_blank'>{$collection['username']}</a>",
				$collection['threadnum'],
				$collection['commentnum'],
				dgmdate($collection['dateline']),
				"<input class=\"txt\" type=\"text\" name=\"ctidorder[{$collection['ctid']}]\" value=\"{$collectionrecommend['ctids'][$collection['ctid']]}\" />",
			]);
		}
	} else {
		showtablerow('', ['class="td25" colspan="7" align="center"', ''], [
			cplang('collection_recommend_tips'),
		]);
	}
	showtablerow('', ['class="td25" colspan="7"'], ['<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ctidarray\', \'chkall\')" /><label for="chkall"> '.cplang('select_all').'</label>']);
	showtablerow('', ['class="td25"', 'colspan="2"'], [
		cplang('operation'),
		'<input class="checkbox" type="checkbox" name="operate_type" id="operate_type" value="delete"><label for="operate_type"> '.cplang('delete').'</label> '
	]);
	showsubmit('submit', 'submit', '', '');
	showtablefooter();
	showformfooter();
} else {
	$collectionrecommend = $_G['setting']['collectionrecommend'] ? dunserialize($_G['setting']['collectionrecommend']) : [];
	foreach($collectionrecommend['ctids'] as $rCtid => &$rCollection) {
		if($_GET['operate_type'] == 'delete' && in_array($rCtid, $_GET['ctidarray'])) {
			unset($collectionrecommend['ctids'][$rCtid]);
			continue;
		}
		$rCollection = $_GET['ctidorder'][$rCtid];
	}
	$collectionrecommend['ctids'] = is_array($collectionrecommend['ctids']) ? removeNonExistsCollection($collectionrecommend['ctids']) : [];
	$collectionrecommend['autorecommend'] = intval($_GET['settingnew']['autorecommend']);
	$collectionrecommend['adminrecommend'] = count($collectionrecommend['ctids']);
	asort($collectionrecommend['ctids']);

	$data = ['collectionrecommendnum' => $collectionrecommend['autorecommend'] + $collectionrecommend['adminrecommend'], 'collectionrecommend' => $collectionrecommend];
	table_common_setting::t()->update_batch($data);
	updatecache('setting');
	savecache('collection_index', []);
	cpmsg('collection_admin_updated', 'action=collection&operation=recommend', 'succeed');
}
/*search*/
	