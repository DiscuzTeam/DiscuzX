<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$magics = '';
foreach(table_common_magic::t()->fetch_all_data(1) as $magic) {
	$eidentifier = explode(':', $magic['identifier']);

	$magics .= showtablerow('', ['class="td25"', 'class="td23"', 'class="td25"', ''], [
		"<input class=\"checkbox\" type=\"checkbox\" name=\"magic[]\" value=\"{$magic['magicid']}\" />",
		(count($eidentifier) > 1 ? (file_exists(DISCUZ_PLUGIN($eidentifier[0]).'/magic/magic_'.$eidentifier[1].'.gif') ? '<img class="vmiddle" src="source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.gif" />' : '')
			: (file_exists(DISCUZ_ROOT.'./static/image/magic/'.$magic['identifier'].'.gif') ? '<img class="vmiddle" src="static/image/magic/'.$magic['identifier'].'.gif" />' : '')),
		$magic['name'],
		'<input class="txt" type="text" name="magicnum['.$magic['magicid'].']" value="1" size="3">'
	], TRUE);
}

if(!$magics) {
	cpmsg('members_edit_magics_nonexistence', 'action=magics', 'error');
}

if(!submitcheck('confermagicsubmit')) {

	shownav('extended', 'nav_magics', 'nav_members_confermagic');
	showsubmenusteps('nav_members_confermagic', [
		['nav_members_select', !$_GET['submit']],
		['nav_members_confermagic', $_GET['submit']],
	], [
		['admin', 'magics&operation=admin', 0],
		['nav_magics_confer', 'members&operation=confermagic', 1]
	]);

	showsearchform('confermagic');

	if(submitcheck('submit', 1)) {

		$membernum = countmembers($search_condition, $urladd);

		showtagheader('div', 'confermedal', TRUE);
		showformheader('members&operation=confermagic'.$urladd);
		showboxheader('', 'tb1');

		if(!$membernum) {
			showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
			showboxfooter();
		} else {

			showboxrow('', ['class="dcol"', 'class="dcol"'], [
				cplang('members_confermagic_members'),
				cplang('members_search_result', ['membernum' => $membernum])."<a href=\"###\" onclick=\"$('searchmembers').style.display='';$('confermedal').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">{$lang['research']}</a>"
			]);
			showboxfooter();

			showboxheader('members_confermagic');
			showtableheader('', 'noborder');
			showsubtitle(['nav_magics_confer', 'nav_magics_image', 'nav_magics_name', 'nav_magics_num']);
			echo $magics;
			showtablefooter();
			showboxfooter();

			showtagheader('div', 'messagebody');
			shownewsletter();
			showtagfooter('div');
			showsubmit('confermagicsubmit', 'submit', 'td', '<input class="checkbox" type="checkbox" name="notifymember" value="1" onclick="$(\'messagebody\').style.display = this.checked ? \'\' : \'none\'" id="grant_notify"/><label for="grant_notify">'.cplang('magics_grant_notify').'</label>');

		}

		showformfooter();
		showtagfooter('div');

	}

} else {
	if(!empty($_POST['conditions'])) $search_condition = dunserialize($_POST['conditions']);
	$membernum = countmembers($search_condition, $urladd);
	notifymembers('confermagic', 'magicletter');
}
	