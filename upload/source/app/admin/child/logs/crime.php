<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

include_once libfile('function/member');

if($_GET['crimeactions']) {
	$_GET['crimeaction'] = array_search($_GET['crimeactions'], crime('actions'));
}
$operator = trim($_GET['operator']);
$crimeaction = intval($_GET['crimeaction']);
$username = trim($_GET['username']);
$starttime = trim($_GET['starttime']);
$endtime = trim($_GET['endtime']);
$keyword = trim($_GET['keyword']);

$_GET['crimesearch'] = 1;

showformheader('logs', null, null, 'post');
showtableheader('', 'fixpadding');

foreach(crime('actions') as $key => $value) {
	$crimeactionselect .= '<option value="'.$key.'"'.($key == $crimeaction ? ' selected' : '').'>'.$lang[$value].'</option>';
}
$staticurl = STATICURL;
print <<<SEARCH
		<script src="{$staticurl}js/calendar.js"></script>
		<input type="hidden" name="operation" value="$operation">
		<input type="hidden" name="action" value="$action">
		<tr class="hover">
			<td class="td23">{$lang['crime_operator']}: </td><td width="160"><input type="text" class="txt" name="operator" value="$operator" /></td>
			<td class="td23">{$lang['crime_action']}: </td><td><select name="crimeaction">$crimeactionselect</select></td>
		</tr>
		<tr class="hover">
			<td class="td23">{$lang['crime_user']}: </td><td><input type="text" class="txt" name="username" value="$username" /></td>
			<td class="td23">{$lang['startendtime']}: </td><td><input type="text" onclick="showcalendar(event, this)" style="width: 80px; margin-right: 5px;" value="$starttime" name="starttime" class="txt" /> -- <input type="text" onclick="showcalendar(event, this)" style="width: 80px; margin-left: 5px;" value="$endtime" name="endtime" class="txt" /></td>
		</tr>
		<tr class="hover">
			<td class="td23">{$lang['keywords']}: </td><td><input type="text" class="txt" name="keyword" value="$keyword" /></td>
			<td class="td23"><input type="submit" name="crimesearch" value="{$lang['search']}" class="btn" /></td><td></td>
		</tr>
SEARCH;
showformfooter();

if(submitcheck('crimesearch', 1)) {
	include_once libfile('function/member');
	list($count, $clist) = crime('search', $crimeaction, $username, $operator, $starttime, $endtime, $keyword, $start, $lpp);

	showtablefooter();
	showtableheader($lang['members_ban_crime_record'].(!empty($lang[$_GET['crimeactions']]) ? ' - '.$lang[$_GET['crimeactions']] : ''), 'fixpadding', '', 5);

	if($clist) {
		showtablerow('class="header"', ['class="td24"', 'class="td24"', 'class="td31"', '', 'class="td24"'], [$lang['crime_user'], $lang['crime_action'], $lang['crime_dateline'], $lang['crime_reason'], $lang['crime_operator']]);
		foreach($clist as $crime) {
			showtablerow('', '', ['<a href="home.php?mod=space&uid='.$crime['uid'].'" target="_blank">'.$crime['username'].'</a>', $lang[$crime['action']], date('Y-m-d H:i:s', $crime['dateline']), $crime['reason'], '<a href="home.php?mod=space&uid='.$crime['operatorid'].'" target="_blank">'.$crime['operator'].'</a>']);
		}
		$multipage = multi($count, $lpp, $page, ADMINSCRIPT."?action=logs&operation=$operation&keyword=".rawurlencode($_GET['keyword'])."&starttime=$starttime&endtime=$endtime&username=".rawurlencode($username).'&operator='.rawurlencode($operator)."&crimeaction=$crimeaction&lpp=$lpp&crimesearch=yes");
	} else {
		showtablerow('', 'colspan=5', [$lang['none']]);
	}
}
	