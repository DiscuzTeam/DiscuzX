<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

echo '<script type="text/javascript" src="'.STATICURL.'js/home.js"></script>';
$uid = $member['uid'];
if($do == 'account') {
	showchildmenu([['nav_members', 'members&operation=list']], $member['username'], [
		['connect_member_info', 'members&operation=edit&uid='.$uid, 0],
		['account', 'members&operation=edit&do=account&uid='.$uid, 1],
	]);

	$interfaces_aType = account_base::Interfaces_aType();
	$interfaces_aType = array_flip($interfaces_aType);

	$interfaces = [];
	foreach(account_base::getInterfaces() as $interface) {
		$interfaces[$interface] = [$interface, account_base::getName($interface), account_base::getIcon($interface)[0]];
	}

	if(!empty($_G['setting']['account_plugin_atypes'])) {
		foreach($_G['setting']['account_plugin_atypes'] as $pluginid => $atype) {
			$interfaces_aType[$atype] = 'plugin_'.$pluginid;
		}
	}

	echo '<script type="text/javascript" src="static/js/iconfont.js"></script>';
	echo '<style>.iconfont { width: 1.5em; height: 1.5em; vertical-align: middle; fill: currentColor; overflow: hidden; margin-right: 5px;}</style>';
	showtableheader();
	showsubtitle(['account_type', 'account_createtime', 'account_bindname', 'account_detail']);
	$account = table_common_member_account::t()->fetch_all_by_uid($uid);
	foreach($account as $row) {
		showtablerow('', ['', '', '', ''], [
			$interfaces[$interfaces_aType[$row['atype']]][2].$interfaces[$interfaces_aType[$row['atype']]][1],
			$row['create_time'],
			$row['bindname'],
			$row['account'],
		]);
	}
	showtablefooter();
	exit;
}
$member = array_merge($member, C::t('common_member_field_forum'.$tableext)->fetch($uid),
	C::t('common_member_field_home'.$tableext)->fetch($uid),
	C::t('common_member_count'.$tableext)->fetch($uid),
	C::t('common_member_status'.$tableext)->fetch($uid),
	C::t('common_member_profile'.$tableext)->fetch($uid),
	table_common_usergroup::t()->fetch($member['groupid']),
	table_common_usergroup_field::t()->fetch($member['groupid']));
loadcache(['profilesetting']);
$fields = [];
foreach($_G['cache']['profilesetting'] as $fieldid => $field) {
	if($field['available']) {
		$_G['cache']['profilesetting'][$fieldid]['unchangeable'] = 0;
		$fields[$fieldid] = $field['title'];
	}
}

if(!submitcheck('editsubmit')) {

	require_once libfile('function/editor');

	$styleselect = "<select name=\"styleidnew\">\n<option value=\"\">{$lang['use_default']}</option>";
	foreach(table_common_style::t()->fetch_all_data() as $style) {
		$styleselect .= "<option value=\"{$style['styleid']}\" ".($style['styleid'] == $member['styleid'] ? 'selected="selected"' : '').">{$style['name']}</option>\n";
	}
	$styleselect .= '</select>';

	$tfcheck = [$member['timeformat'] => 'checked'];
	$gendercheck = [$member['gender'] => 'checked'];
	$pscheck = [$member['pmsound'] => 'checked'];

	$member['regdate'] = dgmdate($member['regdate'], 'Y-n-j h:i A');
	$member['lastvisit'] = dgmdate($member['lastvisit'], 'Y-n-j h:i A');

	$member['bio'] = html2bbcode($member['bio']);
	$member['signature'] = html2bbcode($member['sightml']);

	$taginfo = '';
	$tags = table_common_tagitem::t()->select(0, $uid, 'uid');
	if($tags) {
		$tagids = [];
		foreach($tags as $tag) {
			$tagids[] = $tag['tagid'];
		}
		if($tagids) {
			foreach(table_common_tag::t()->fetch_all($tagids) as $tag) {
				$taginfo .= $tag['tagname'].' <a href="'.ADMINSCRIPT.'?action=usertag&uid='.$uid.'&operation=del&tagid='.$tag['tagid'].'">['.cplang('delete').']</a>&nbsp; ';
			}
		}
	}

	shownav('user', 'members_edit');
	/*search={"members_edit":"action=members&operation=edit"}*/
	showchildmenu([['nav_members', 'members&operation=list']], $member['username'], [
		['connect_member_info', 'members&operation=edit&uid='.$uid, 1],
		['account', 'members&operation=edit&do=account&uid='.$uid, 0],
	]);

	showformheader("members&operation=edit&uid=$uid", 'enctype');
	showtableheader();
	$status = [$member['status'] => ' checked'];
	$freeze = [$member['freeze'] => ' checked'];
	showsetting('members_edit_uid', '', '', $member['uid']);
	showsetting('members_edit_loginname', '', '', $member['loginname']);
	showsetting('members_edit_username', '', '', $member['username']);
	showsetting('members_edit_avatar', '', '', avatar($uid, 'middle', ['random' => 1]).'<br /><br /><input name="clearavatar" class="checkbox" type="checkbox" value="1" /> '.$lang['members_edit_avatar_clear']);
	$hrefext = "&detail=1&users={$member['username']}&searchsubmit=1&perpage=50&fromumanage=1";
	showsetting('members_edit_statistics', '', '', "<a href=\"".ADMINSCRIPT."?action=prune$hrefext\" class=\"act\">{$lang['posts']}({$member['posts']})</a>".
		"<a href=\"".ADMINSCRIPT."?action=doing$hrefext\" class=\"act\">{$lang['doings']}({$member['doings']})</a>".
		"<a href=\"".ADMINSCRIPT."?action=blog$hrefext\" class=\"act\">{$lang['blogs']}({$member['blogs']})</a>".
		"<a href=\"".ADMINSCRIPT."?action=album$hrefext\" class=\"act\">{$lang['albums']}({$member['albums']})</a>".
		"<a href=\"".ADMINSCRIPT."?action=share$hrefext\" class=\"act\">{$lang['shares']}({$member['sharings']})</a> <br>&nbsp;{$lang['setting_styles_viewthread_userinfo_oltime']}: {$member['oltime']}{$lang['hourtime']}");
	showsetting('members_edit_tag', '', '', $taginfo);
	showsetting('members_edit_password', 'passwordnew', '', 'text');
	showsetting('members_edit_clearquestion', 'clearquestion', 0, 'radio');
	showsetting('members_edit_status', 'statusnew', $member['status'], 'radio');

	showsetting('members_edit_freeze', ['freezenew', [
		[0, $lang['members_edit_freeze_false']],
		[1, $lang['members_edit_freeze_password']],
		[-1, $lang['members_edit_freeze_admincp']],
		[2, $lang['members_edit_freeze_email']]]], $member['freeze'], 'mradio');
	showsetting('members_edit_email', 'emailnew', $member['email'], 'text');
	showsetting('members_edit_email_emailstatus', 'emailstatusnew', $member['emailstatus'], 'radio');
	showsetting('members_edit_secmobile_secmobicc', 'secmobiccnew', $member['secmobicc'], 'text');
	showsetting('members_edit_secmobile_secmobile', 'secmobilenew', $member['secmobile'], 'text');
	showsetting('members_edit_secmobile_secmobilestatus', 'secmobilestatusnew', $member['secmobilestatus'], 'radio');
	showsetting('members_edit_posts', 'postsnew', $member['posts'], 'text');
	showsetting('members_edit_digestposts', 'digestpostsnew', $member['digestposts'], 'text');
	showsetting('members_edit_regip', 'regipnew', $member['regip'], 'text');
	showsetting('members_edit_regport', 'regportnew', $member['regport'], 'text');
	showsetting('members_edit_regdate', 'regdatenew', $member['regdate'], 'text');
	showsetting('members_edit_lastvisit', 'lastvisitnew', $member['lastvisit'], 'text');
	showsetting('members_edit_lastip', 'lastipnew', $member['lastip'], 'text');
	showsetting('members_edit_port', 'portnew', $member['port'], 'text');
	showsetting('members_edit_addsize', 'addsizenew', $member['addsize'], 'text');
	showsetting('members_edit_addfriend', 'addfriendnew', $member['addfriend'], 'text');

	showsetting('members_edit_timeoffset', 'timeoffsetnew', $member['timeoffset'], 'text');
	showsetting('members_edit_invisible', 'invisiblenew', $member['invisible'], 'radio');

	showtitle('members_edit_option');
	showsetting('members_edit_cstatus', 'cstatusnew', $member['customstatus'], 'text');
	showsetting('members_edit_signature', 'signaturenew', $member['signature'], 'textarea');

	if($fields) {
		showtitle('members_profile');
		include_once libfile('function/profile');
		foreach($fields as $fieldid => $fieldtitle) {
			$html = profile_setting($fieldid, $member);
			if($html) {
				showsetting($fieldtitle, '', '', $html);
			}
		}
	}

	showsetting('members_edit_fields', 'fieldsnew', $member['fields'], 'textarea');
	// 用户历史资料下载 开始
	showsetting('members_edit_exphistory', '', '', "<a href=\"".ADMINSCRIPT."?action=members&operation=exphistory&uid={$member['uid']}\" class=\"act\">{$lang['members_edit_exphistory']}</a>");
	// 用户历史资料下载 结束

	showsubmit('editsubmit');
	showtablefooter();
	showformfooter();
	/*search*/

} else {

	loaducenter();
	require_once libfile('function/discuzcode');

	$questionid = $_GET['clearquestion'] ? 0 : '';
	$secmobicc = $_GET['secmobiccnew'];
	$secmobile = $_GET['secmobilenew'];

	if(!empty($secmobile) && $secmobicc === '') {
		//安全手机号非空，区号为空时，使用默认区号
		$secmobicc = $_G['setting']['smsdefaultcc'];
	} elseif($secmobicc === '') {
		//空字符串代表没传递这个参数，传递0时，代表清空这个数据
		$secmobicc = 0;
	} elseif(!preg_match('#^(\d){1,3}$#', $secmobicc)) {
		cpmsg('members_mobicc_illegal', '', 'error');
	}

	if($secmobile === '') {
		$secmobile = 0;
	} elseif($secmobile !== '' && !preg_match('#^(\d){1,12}$#', $secmobile)) {
		cpmsg('members_mobile_illegal', '', 'error');
	}

	$ucresult = uc_user_edit(addslashes($member['loginname']), $_GET['passwordnew'], $_GET['passwordnew'], addslashes(strtolower(trim($_GET['emailnew']))), 1, $questionid, '', $secmobicc, $secmobile);
	if($ucresult < 0) {
		if($ucresult == -4) {
			cpmsg('members_email_illegal', '', 'error');
		} elseif($ucresult == -5) {
			cpmsg('members_email_domain_illegal', '', 'error');
		} elseif($ucresult == -6) {
			cpmsg('members_email_duplicate', '', 'error');
		} elseif($ucresult == -8) {
			cpmsg('members_edit_protectedmembers', '', 'error');
		} elseif($ucresult == -9) {
			cpmsg('members_mobile_duplicate', '', 'error');
		}
	}

	if($_GET['clearavatar']) {
		C::t('common_member'.$tableext)->update($_GET['uid'], ['avatarstatus' => 0]);
		uc_user_deleteavatar($uid);
	}

	$creditsnew = intval($creditsnew);

	$regdatenew = strtotime($_GET['regdatenew']);
	$lastvisitnew = strtotime($_GET['lastvisitnew']);

	$secquesadd = $_GET['clearquestion'] ? ", secques=''" : '';

	$signaturenew = censor($_GET['signaturenew']);
	$sigstatusnew = $signaturenew ? 1 : 0;
	$sightmlnew = discuzcode($signaturenew, 1, 0, 0, 0, ($member['allowsigbbcode'] ? ($member['allowcusbbcode'] ? 2 : 1) : 0), $member['allowsigimgcode'], 0);

	$oltimenew = round($_GET['totalnew'] / 60);

	$fieldadd = '';
	$fieldarr = [];
	include_once libfile('function/profile');
	foreach($_POST as $field_key => $field_val) {
		if(isset($fields[$field_key]) && (profile_check($field_key, $field_val) || $_G['adminid'] == 1)) {
			$fieldarr[$field_key] = $field_val;
			if(!empty($_G['cache']['profilesetting'][$field_key]['encrypt'])) {
				$fieldarr[$field_key] = authcode_field($_G['cache']['profilesetting'][$field_key]['encrypt'], $fieldarr[$field_key], 'ENCODE');
			}
		}
	}
	// 判断更多自定义字段是否符合预设结构
	$fieldsnew = trim($_GET['fieldsnew']);
	if(!empty($fieldsnew) && !in_array($fieldsnew, ['{}', 'null'])) {
		$field = $_G['cache']['profilesetting']['fields'];
		if(empty($field['choices'])) {
			cpmsg('members_edit_fields_default_null', '', 'error');
		}
		if(!compareJsonStructures($fieldsnew, $field['choices'])) {
			cpmsg('members_edit_fields_compare_error', '', 'error');
		}
		$fieldarr['fields'] = $fieldsnew;
	} else {
		$fieldarr['fields'] = '{}';
	}

	if($_GET['deletefile'] && is_array($_GET['deletefile'])) {
		foreach($_GET['deletefile'] as $key => $value) {
			if(isset($fields[$key]) && $_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
				@unlink(getglobal('setting/attachdir').'./profile/'.$member[$key]);
				$fieldarr[$key] = '';
			}
		}
	}

	if($_FILES) {
		$upload = new discuz_upload();

		foreach($_FILES as $key => $file) {
			if(isset($fields[$key])) {
				$upload->init($file, 'profile');
				$attach = $upload->attach;

				if(!$upload->error()) {
					$upload->save();

					if(!$upload->get_image_info($attach['target'])) {
						@unlink($attach['target']);
						continue;
					}
					$attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));
					@unlink(getglobal('setting/attachdir').'./profile/'.$member[$key]);
					$fieldarr[$key] = $attach['attachment'];
				}
			}
		}
	}

	$memberupdate = [];
	if($ucresult >= 0) {
		$memberupdate['email'] = strtolower(trim($_GET['emailnew']));
		$memberupdate['secmobicc'] = $secmobicc == 0 ? '' : $secmobicc;
		$memberupdate['secmobile'] = $secmobile == 0 ? '' : $secmobile;
	}
	if($ucresult >= 0 && !empty($_GET['passwordnew'])) {
		$memberupdate['password'] = md5(random(10));
	}
	$addsize = intval($_GET['addsizenew']);
	$addfriend = intval($_GET['addfriendnew']);
	$status = intval($_GET['statusnew']) ? -1 : 0;
	if($status == -1 && $member['groupid'] == 1) {
		cpmsg('members_edit_lock', '', 'error', ['grouptitle' => $member['grouptitle'], 'uid' => $member['uid']]);
	}
	$freeze = in_array($_GET['freezenew'], [-2, -1, 0, 1, 2]) ? $_GET['freezenew'] : 0;
	$emailstatusnew = intval($_GET['emailstatusnew']);
	$secmobilestatusnew = intval($_GET['secmobilestatusnew']);
	$memberupdate = array_merge($memberupdate, ['regdate' => $regdatenew, 'emailstatus' => $emailstatusnew, 'secmobilestatus' => $secmobilestatusnew, 'status' => $status, 'freeze' => $freeze, 'timeoffset' => $_GET['timeoffsetnew']]);
	C::t('common_member'.$tableext)->update($uid, $memberupdate);
	C::t('common_member_field_home'.$tableext)->update($uid, ['addsize' => $addsize, 'addfriend' => $addfriend]);
	C::t('common_member_count'.$tableext)->update($uid, ['posts' => $_GET['postsnew'], 'digestposts' => $_GET['digestpostsnew']]);
	C::t('common_member_status'.$tableext)->update($uid, ['regip' => $_GET['regipnew'], 'regport' => $_GET['regportnew'], 'lastvisit' => $lastvisitnew, 'lastip' => $_GET['lastipnew'], 'port' => $_GET['portnew'], 'invisible' => $_GET['invisiblenew']]);
	C::t('common_member_field_forum'.$tableext)->update($uid, ['customstatus' => $_GET['cstatusnew'], 'sightml' => $sightmlnew]);
	if(!empty($fieldarr)) {
		C::t('common_member_profile'.$tableext)->update($uid, $fieldarr);
	}
	if($freeze == 0 && table_common_member_validate::t()->fetch($uid)) {
		table_common_member_validate::t()->delete($uid);
	}

	// 将安全手机号同步给account表
	if(!empty($secmobile)) {
		require_once libfile('class/account');
		if(table_common_member_account::t()->fetch_by_uid($uid, account::aType_phone)) {
			table_common_member_account::t()->update_by_uid_and_atype($uid, account::aType_phone, ['account' => $secmobicc.'-'.$secmobile]);
		} else {
			table_common_member_account::t()->insert([
				'uid' => $uid,
				'atype' => account::aType_phone,
				'account' => $secmobicc.'-'.$secmobile,
				'bindname' => $member['username'],
			]);
		}
	} else {
		table_common_member_account::t()->delete_by_uid($uid, account::aType_phone);
	}

	cpmsg('members_edit_succeed', 'action=members&operation=edit&uid='.$uid, 'succeed');

}
	