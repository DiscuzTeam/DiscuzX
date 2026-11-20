<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(submitcheck('settingsubmit')) {
	$_GET['templatenew'] = serialize($_GET['templatenew']);
	if(!$_GET['namenew']) {
		cpmsg('setting_threadprofile_name_empty', '', 'error');
	}
	if($_GET['do'] == 'add') {
		table_forum_threadprofile::t()->insert(['name' => strip_tags($_GET['namenew']), 'template' => $_GET['templatenew']]);
	} elseif($_GET['do'] == 'edit') {
		table_forum_threadprofile::t()->update($_GET['id'], ['name' => strip_tags($_GET['namenew']), 'template' => $_GET['templatenew']]);
	}
	updatecache('setting');
	cpmsg('setting_update_succeed', 'action=setting&operation=styles&anchor=threadprofile', 'succeed');
} else {
	shownav('style', 'setting_styles', 'setting_threadprofile');

	$authorinfoitems = [];
	$authorinfoitems = [
		'{numbercard}' => $lang['setting_styles_threadprofile_attrcard'],
		'{groupicon}<p>{*}</p>{/groupicon}' => $lang['setting_styles_threadprofile_groupicon'],
		'{authortitle}<p><em>{*}</em></p>{/authortitle}' => $lang['setting_styles_threadprofile_groupname'],
		'{customstatus}<p class=xg1>{*}</p>{/customstatus}' => $lang['members_edit_nickname'],
		'{star}<p>{*}</p>{/star}' => $lang['group_level_icon'],
		'{upgradeprogress}' => $lang['setting_styles_threadprofile_groupstep'],
	];
	if(!empty($_G['setting']['extcredits'])) {
		foreach($_G['setting']['extcredits'] as $key => $value) {
			$authorinfoitems['extcredits'.$key] = $value['title'];
		}
	}
	$authorinfoitems = array_merge($authorinfoitems, [
		1 => '-',
		'uid' => 'UID',
		'friends' => $lang['setting_styles_viewthread_userinfo_friends'],
		'doings' => $lang['setting_styles_viewthread_userinfo_doings'],
		'blogs' => $lang['setting_styles_viewthread_userinfo_blogs'],
		'albums' => $lang['setting_styles_viewthread_userinfo_albums'],
		'posts' => $lang['setting_styles_viewthread_userinfo_posts'],
		'threads' => $lang['setting_styles_viewthread_userinfo_threads'],
		'sharings' => $lang['setting_styles_viewthread_userinfo_sharings'],
		'digest' => $lang['setting_styles_viewthread_userinfo_digest'],
		'credits' => $lang['setting_styles_viewthread_userinfo_credits'],
		'readperm' => $lang['setting_styles_viewthread_userinfo_readperm'],
		'regtime' => $lang['setting_styles_viewthread_userinfo_regtime'],
		'lastdate' => $lang['setting_styles_viewthread_userinfo_lastdate'],
		'oltime' => $lang['setting_styles_viewthread_userinfo_oltime'],
		'eccredit_seller' => $lang['setting_styles_threadprofile_eccredit_seller'],
		'eccredit_buyer' => $lang['setting_styles_threadprofile_eccredit_buyer'],
		'follower' => $lang['setting_styles_viewthread_userinfo_follower'],
		'following' => $lang['setting_styles_viewthread_userinfo_following']
	]);
	foreach(table_common_member_profile_setting::t()->fetch_all_by_available(1) as $profilefields) {
		if($profilefields['fieldid'] == 'birthyear' || $profilefields['fieldid'] == 'birthmonth') {
			continue;
		} elseif($profilefields['fieldid'] == 'realname') {
			$setting['verify'] = dunserialize($setting['verify']);
			if($setting['verify'][6]['available'] && !$setting['verify'][6]['viewrealname']) {
				continue;
			}
		}
		$authorinfoitems['field_'.$profilefields['fieldid']] = $profilefields['title'];
	}

	if($_G['setting']['hookscript']['global']['profile']['funcs']['profile_node']) {
		$pluginidentifiers = [];
		foreach($_G['setting']['hookscript']['global']['profile']['funcs']['profile_node'] as $plugin) {
			$pluginidentifiers[] = $plugin[0];
		}
		$plugins = table_common_plugin::t()->fetch_all_identifier($pluginidentifiers);
		foreach($plugins as $id => $value) {
			$authorinfoitems['{plugin:'.$id.'}'] = $value['name'];
		}
	}

	if($_GET['do'] == 'add') {
		showchildmenu([['setting_styles_threadprofile', 'setting&operation=styles&anchor=threadprofile']], cplang('setting_styles_threadprofile_addplan'));

		showformheader('setting&edit=yes', 'enctype');
		showhiddenfields(['operation' => $operation]);

		/*search={"setting_styles":"action=setting&operation=threadprofile&do=add"}*/
		showtips('setting_threadprofile_tpl_tpls');
		showtableheader('');
		showhiddenfields(['do' => 'add']);
		showsetting('setting_styles_threadprofile_name', 'namenew', '', 'text');
		showsetting_threadprfile($authorinfoitems);
		showtagfooter('tbody');
		/*search*/
	} elseif($_GET['do'] == 'edit') {
		$id = intval($_GET['id']);
		$threadprofile = table_forum_threadprofile::t()->fetch($id);
		if(!$threadprofile) {
			dheader('location: '.ADMINSCRIPT.'?action=setting&operation=styles&anchor=threadprofile');
		}
		showchildmenu([['setting_styles', 'setting&operation=styles'], ['setting_styles_threadprofile', 'setting&operation=styles&anchor=threadprofile']], $threadprofile['name']);

		showtips('setting_threadprofile_tpl_tpls');
		showformheader('setting&edit=yes', 'enctype');
		showhiddenfields(['operation' => $operation]);
		showtableheader('');
		showhiddenfields(['do' => 'edit', 'id' => $id]);
		$threadprofile['template'] = dunserialize($threadprofile['template']);
		showsetting('setting_styles_threadprofile_name', 'namenew', $threadprofile['name'], 'text');
		showsetting_threadprfile($authorinfoitems, $threadprofile['template']);
		showtagfooter('tbody');
	} elseif($_GET['do'] == 'delete') {
		$id = intval($_GET['id']);
		table_forum_threadprofile::t()->delete($id);
		table_forum_threadprofile_group::t()->delete_by_tpid($id);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=setting&operation=styles&anchor=threadprofile', 'succeed');
	}

	showsubmit('settingsubmit', 'submit', '', $extbutton.(!empty($from) ? '<input type="hidden" name="from" value="'.$from.'">' : ''));
	showtablefooter();
	showformfooter();
}