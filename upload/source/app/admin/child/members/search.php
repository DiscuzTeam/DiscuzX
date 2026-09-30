<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('submit', 1)) {

	shownav('user', 'nav_members');
	showsubmenu('nav_members', [
		['search', 'members&operation=search', 1],
		['clean', 'members&operation=clean', 0],
		['nav_repeat', 'members&operation=repeat', 0],
		['add', 'members&operation=add', 0],
	]);
	showtips('members_admin_tips');
	if(!empty($_GET['vid']) && ($_GET['vid'] > 0 && $_GET['vid'] < 8)) {
		$_GET['verify'] = ['verify'.intval($_GET['vid'])];
	}
	showsearchform('search');
	if($_GET['more']) {
		print <<<EOF
		<script type="text/javascript">
			$('btn_more').click();
		</script>

EOF;
	}
} else {

	$membernum = countmembers($search_condition, $urladd);

	$members = '';
	if($membernum > 0) {
		$multipage = multi($membernum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT.'?action=members&operation=search&submit=yes'.$urladd);

		$usergroups = [];
		foreach(table_common_usergroup::t()->range() as $group) {
			switch($group['type']) {
				case 'system':
					$group['grouptitle'] = '<b>'.$group['grouptitle'].'</b>';
					break;
				case 'special':
					$group['grouptitle'] = '<i>'.$group['grouptitle'].'</i>';
					break;
			}
			$usergroups[$group['groupid']] = $group;
		}

		$uids = searchmembers($search_condition, $_G['setting']['memberperpage'], $start_limit);
		if($uids) {
			$interfaces_aType = account_base::Interfaces_aType();
			if(!empty($_G['setting']['account_plugin_atypes'])) {
				foreach($_G['setting']['account_plugin_atypes'] as $pluginid => $atype) {
					$interfaces_aType['plugin_'.$pluginid] = $atype;
				}
			}
			$interfaces = array_flip($interfaces_aType);

			$allmember = table_common_member::t()->fetch_all($uids);
			$allcount = table_common_member_count::t()->fetch_all($uids);
			$allaccount = table_common_member_account::t()->fetch_all_atype_by_uid($uids);
			foreach($allmember as $uid => $member) {
				$member = array_merge($member, (array)$allcount[$uid]);
				$memberextcredits = [];
				if($_G['setting']['extcredits']) {
					foreach($_G['setting']['extcredits'] as $id => $credit) {
						$memberextcredits[] = $member['extcredits'.$id];
					}
				}
				$accounts = '';
				if(!empty($allaccount[$uid])) {
					foreach($allaccount[$uid] as $interface) {
						$accounts .= account_base::getIcon($interfaces[$interface])[0];
					}
				}
				$lockshow = $member['status'] == '-1' ? '<em class="lightnum">['.cplang('lock').']</em>' : '';
				$freezeshow = $member['freeze'] ? '<em class="lightnum">['.cplang('freeze').']</em>' : '';
				$members .= showtablerow('', ['class="td25"'], [
					"<input type=\"checkbox\" name=\"uidarray[]\" value=\"{$member['uid']}\"".(is_protect_member($member) ? 'disabled' : '')." class=\"checkbox\">".
					avatar($member['uid'], 'small', class: 'vmiddle', extra: 'width="30"'),
					"<a href=\"home.php?mod=space&uid={$member['uid']}\" target=\"_blank\">{$member['uid']}</a>",
					"<a href=\"home.php?mod=space&uid={$member['uid']}\" target=\"_blank\">{$member['loginname']}</a>",
					"<a href=\"home.php?mod=space&uid={$member['uid']}\" target=\"_blank\">{$member['username']}</a>",
					$member['credits'],
					...$memberextcredits,
					$member['threads'],
					$member['posts'],
					$usergroups[$member['adminid']]['grouptitle'],
					$usergroups[$member['groupid']]['grouptitle'].$lockshow.$freezeshow,
					$accounts,

					"<a href=\"".ADMINSCRIPT."?action=members&operation=edit&uid={$member['uid']}\" class=\"act\">{$lang['detail']}</a>".
					"<a href=\"".ADMINSCRIPT."?action=members&operation=group&uid={$member['uid']}\" class=\"act\">{$lang['usergroup']}</a><a href=\"".ADMINSCRIPT."?action=members&operation=access&uid={$member['uid']}\" class=\"act\">{$lang['members_access']}</a>".
					($_G['setting']['extcredits'] ? "<a href=\"".ADMINSCRIPT."?action=members&operation=credit&uid={$member['uid']}\" class=\"act\">{$lang['credits']}</a>" : "<span disabled>{$lang['edit']}</span>").
					"<a href=\"".ADMINSCRIPT."?action=members&operation=medal&uid={$member['uid']}\" class=\"act\">{$lang['medals']}</a>".
					"<a href=\"".ADMINSCRIPT."?action=members&operation=repeat&uid={$member['uid']}\" class=\"act\">{$lang['members_repeat']}</a>".
					"<a href=\"".ADMINSCRIPT."?action=members&operation=ban&uid={$member['uid']}\" class=\"act\">{$lang['members_ban']}</a>".
					"<a href=\"".ADMINSCRIPT."?action=members&operation=chgusername&uid={$member['uid']}\" class=\"act\">{$lang['members_chgusername']}</a>"
				], TRUE);
			}
		}
	}

	shownav('user', 'nav_members');
	showsubmenu('nav_members');
	showtips('members_export_tips');
	foreach($search_condition as $k => $v) {
		if($k == 'username') {
			$v = explode(',', $v);
			$tmpv = [];
			foreach($v as $subvalue) {
				$tmpv[] = rawurlencode($subvalue);
			}
			$v = implode(',', $tmpv);
		}
		if(is_array($v)) {
			foreach($v as $value) {
				$condition_str .= '&'.$k.'[]='.$value;
			}
		} else {
			$condition_str .= '&'.$k.'='.$v;
		}
	}
	showformheader('members&operation=clean'.$condition_str);
	showtableheader(cplang('members_search_result', ['membernum' => $membernum]).'<a href="'.ADMINSCRIPT.'?action=members&operation=search" class="act lightlink normal">'.cplang('research').'</a>&nbsp;&nbsp;&nbsp;<a href='.ADMINSCRIPT.'?action=members&operation=export'.$condition_str.'>'.$lang['members_search_export'].'</a>');
	showtableheader();

	if($membernum) {
		$extcredits = [];
		foreach($_G['setting']['extcredits'] as $id => $credit) {
			$extcredits[] = $credit['title'];
		}
		showsubtitle(['', 'uid', 'loginname', 'username', 'credits', ...$extcredits, 'members_threads_num', 'members_posts_num', 'admingroup', 'usergroup', 'account', '']);
		echo $members;
		$condition_str = str_replace('&tablename=master', '', $condition_str);
		$unarchive = isset($_GET['tablename']) && $_GET['tablename'] == 'archive' ? '<input type="submit" class="btn" id="submit_unarchivesubmit" name="unarchivesubmit" onclick="document.cpform.action=\''.ADMINSCRIPT.'?action=members&operation=unarchive'.$condition_str.'\';document.cpform.submit();" value="'.cplang('unarchive').'">' : '';
		showsubmit('deletesubmit', cplang('delete'), ($tmpsearch_condition ? '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'uidarray\');if(this.checked){$(\'deleteallinput\').style.display=\'\';}else{$(\'deleteall\').checked = false;$(\'deleteallinput\').style.display=\'none\';}" class="checkbox">'.cplang('select_all') : ''), $unarchive.' &nbsp;&nbsp;&nbsp;<span id="deleteallinput" style="display:none"><input id="deleteall" type="checkbox" name="deleteall" class="checkbox">'.cplang('members_search_deleteall', ['membernum' => $membernum]).'</span>', $multipage);
	}
	showtablefooter();
	showboxfooter();
	showformfooter();

	echo '<script type="text/javascript" src="static/js/iconfont.js"></script>';
	echo '<style>.iconfont { width: 1.5em; height: 1.5em; vertical-align: middle; fill: currentColor; overflow: hidden; margin-right: 5px;}</style>';

}
