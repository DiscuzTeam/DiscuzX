<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$Plang = $scriptlang['myrepeats'];

if($_GET['op'] == 'lock' && FORMHASH == $_GET['formhash']) {
	$myrepeat = myrepeats\table_myrepeats::t()->fetch_all_by_uid_username($_GET['uid'], $_GET['username']);
	$lock = $myrepeat[0]['locked'];
	$locknew = $lock ? 0 : 1;
	myrepeats\table_myrepeats::t()->update_locked_by_uid_username($_GET['uid'], $_GET['username'], $locknew);
	ajaxshowheader();
	echo $lock ? $Plang['normal'] : $Plang['lock'];
	ajaxshowfooter();
} elseif($_GET['op'] == 'delete' && FORMHASH == $_GET['formhash']) {
	myrepeats\table_myrepeats::t()->delete_by_uid_usernames($_GET['uid'], $_GET['username']);
	ajaxshowheader();
	echo $Plang['deleted'];
	ajaxshowfooter();
}

$ppp = 100;
$resultempty = FALSE;
$srchadd = $searchtext = $extra = $srchuid = '';
$page = max(1, intval($_GET['page']));
if(!empty($_GET['srchuid'])) {
	$srchuid = intval($_GET['srchuid']);
	$srchadd = "AND uid='$srchuid'";
} elseif(!empty($_GET['srchusername'])) {
	$srchuid = table_common_member::t()->fetch_uid_by_username($_GET['srchusername']);
	if($srchuid) {
		$srchadd = "AND uid='$srchuid'";
	} else {
		$resultempty = TRUE;
	}
} elseif(!empty($_GET['srchrepeat'])) {
	$extra = '&srchrepeat='.rawurlencode($_GET['srchrepeat']);
	$srchadd = "AND username='".addslashes($_GET['srchrepeat'])."'";
	$searchtext = $Plang['search'].' "'.dhtmlspecialchars($_GET['srchrepeat']).'" '.$Plang['repeats'].'&nbsp;';
}

if($srchuid) {
	$extra = '&srchuid='.$srchuid;
	$member = getuserbyuid($srchuid);
	$searchtext = $Plang['search'].' "'.$member['username'].'" '.$Plang['repeatusers'].'&nbsp;';
}

$statary = [-1 => $Plang['status'], 0 => $Plang['normal'], 1 => $Plang['lock']];
$status = isset($_GET['status']) ? intval($_GET['status']) : -1;

if(isset($status) && $status >= 0) {
	$srchadd .= " AND locked='$status'";
	$searchtext .= $Plang['search'].$statary[$status].$Plang['statuss'];
}

if($searchtext) {
	$searchtext = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp">'.$Plang['viewall'].'</a>&nbsp'.$searchtext;
}

loadcache('usergroups');

showtableheader();
showformheader('plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp', 'repeatsubmit');
showsubmit('repeatsubmit', $Plang['search'], $Plang['username'].': <input name="srchusername" value="'.htmlspecialchars($_GET['srchusername']).'" class="txt" />&nbsp;&nbsp;'.$Plang['repeat'].': <input name="srchrepeat" value="'.htmlspecialchars($_GET['srchrepeat']).'" class="txt" />', $searchtext);
showformfooter();

$statselect = '<select onchange="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp'.$extra.'&status=\' + this.value">';
foreach($statary as $k => $v) {
	$statselect .= '<option value="'.$k.'"'.($k == $status ? ' selected' : '').'>'.$v.'</option>';
}
$statselect .= '</select>';

echo '<tr class="header"><th>'.$Plang['username'].'</th><th>'.$lang['usergroup'].'</th><th>'.$Plang['repeat'].'</th><th>'.$Plang['lastswitch'].'</th><th>'.$statselect.'</th><th></th></tr>';
if(!$resultempty) {
	$count = myrepeats\table_myrepeats::t()->count_by_search($srchadd);
	$myrepeats = myrepeats\table_myrepeats::t()->fetch_all_by_search($srchadd, ($page - 1) * $ppp, $ppp);
	$uids = [];
	foreach($myrepeats as $myrepeat) {
		$uids[] = $myrepeat['uid'];
	}
	$users = table_common_member::t()->fetch_all($uids);
	$i = 0;
	foreach($myrepeats as $myrepeat) {
		$myrepeat['lastswitch'] = $myrepeat['lastswitch'] ? dgmdate($myrepeat['lastswitch']) : '';
		$myrepeat['usernameenc'] = rawurlencode($myrepeat['username']);
		$opstr = !$myrepeat['locked'] ? $Plang['normal'] : $Plang['lock'];
		$i++;
		echo '<tr><td><a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp&srchuid='.$myrepeat['uid'].'">'.$users[$myrepeat['uid']]['username'].'</a></td>'.
			'<td>'.$_G['cache']['usergroups'][$users[$myrepeat['uid']]['groupid']]['grouptitle'].'</td>'.
			'<td><a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp&srchrepeat='.rawurlencode($myrepeat['username']).'" title="'.htmlspecialchars($myrepeat['comment']).'">'.$myrepeat['username'].'</a>'.'</td>'.
			'<td>'.($myrepeat['lastswitch'] ? $myrepeat['lastswitch'] : '').'</td>'.
			'<td><a id="d'.$i.'" onclick="ajaxget(this.href, this.id, \'\');return false" href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp&uid='.$myrepeat['uid'].'&username='.$myrepeat['usernameenc'].'&op=lock&formhash='.FORMHASH.'">'.$opstr.'</a></td>'.
			'<td><a id="p'.$i.'" onclick="ajaxget(this.href, this.id, \'\');return false" href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp&uid='.$myrepeat['uid'].'&username='.$myrepeat['usernameenc'].'&op=delete&formhash='.FORMHASH.'">['.$lang['delete'].']</a></td></tr>';
	}
}
showtablefooter();

echo multi($count, $ppp, $page, ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=myrepeats&pmod=admincp$extra");

