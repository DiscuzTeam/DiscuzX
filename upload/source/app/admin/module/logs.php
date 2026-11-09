<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$lpp = empty($_GET['lpp']) ? 50 : $_GET['lpp'];
$checklpp = [];
$checklpp[$lpp] = 'selected="selected"';
$extrainput = '';

$operation = !empty($operation) ? $operation : 'setting';

$start = ($page - 1) * $lpp;

$conditions = [];
$conditions[] = ['type', '=', "'".$operation."'"];
if(!empty($_GET['search']) && !empty($_GET['search']['field']) && in_array($_GET['search']['field'], ['data', 'device']) && preg_match('/^\w+$/', $_GET['search']['key'])
	&& !empty($_GET['search']['key'])&& !empty($_GET['search'][$_GET['search']['key']])) {
	$searchvalue = $_GET['search'][$_GET['search']['key']];
	if(!is_scalar($searchvalue)) {
		$searchvalue = '';
	}
	$searchvalue = addslashes((string)$searchvalue);
	$conditions[] = ['JSON_EXTRACT('.$_GET['search']['field'].', \'$.'.$_GET['search']['key'].'\')', '=', "'".$searchvalue."'"];
}

$logs = [];
$num = table_common_log::t()->fetch_all_by_conditions($conditions, 0, 0, 1);
$logs = table_common_log::t()->fetch_all_by_conditions($conditions, $start, $lpp, 0);
$multipage = multi($num, $lpp, $page, ADMINSCRIPT."?action=logs&operation=$operation&lpp=$lpp".(!empty($_GET['day']) ? '&day='.$_GET['day'] : ''), 0, 3);

$usergroup = [];

if(in_array($operation, ['rate', 'mods', 'ban', 'cp', 'modcp'])) {
	foreach(table_common_usergroup::t()->range() as $group) {
		$usergroup[$group['groupid']] = $group['grouptitle'];
	}
}

shownav('tools', 'nav_logs', 'nav_logs_'.$operation);

$sel = '';
$menu = [
	['nav_logs_setting', 'logs&operation=setting', $operation == 'setting'],
	[['menu' => 'nav_logs_member', 'submenu' => [
		['nav_logs_illegal', 'logs&operation=illegal'],
		['nav_logs_ban', 'logs&operation=ban'],
		['nav_logs_mods', 'logs&operation=mods'],
		['nav_logs_sms', 'logs&operation=sms'],
		['nav_logs_login', 'logs&operation=login'],
	]], '', in_array($operation, ['illegal', 'ban', 'mods', 'sms', 'login'])],
	[['menu' => 'nav_logs_system', 'submenu' => [
		['nav_logs_cp', 'logs&operation=cp'],
		['nav_logs_modcp', 'logs&operation=modcp'],
		['nav_logs_error', 'logs&operation=error'],
		['nav_logs_sendmail', 'logs&operation=sendmail'],
		['nav_logs_SMTP', 'logs&operation=SMTP'],
		['nav_logs_restful', 'logs&operation=restful'],
	]], '', in_array($operation, ['cp', 'error', 'sendmail', 'SMTP'])],
	[['menu' => 'nav_logs_extended', 'submenu' => [
		['nav_logs_rate', 'logs&operation=rate'],
		['nav_logs_warn', 'logs&operation=warn'],
		['nav_logs_credit', 'logs&operation=credit'],
		['nav_logs_magic', 'logs&operation=magic'],
		['nav_logs_medal', 'logs&operation=medal'],
		['nav_logs_invite', 'logs&operation=invite'],
		['nav_logs_payment', 'logs&operation=payment'],
		['nav_logs_pmt', 'logs&operation=pmt'],
	]], '', in_array($operation, ['rate', 'warn', 'credit', 'magic', 'medal', 'invite', 'payment', 'pmt'])],
	[['menu' => 'nav_logs_crime', 'submenu' => [
		['all', 'logs&operation=crime'],
		['nav_logs_crime_delpost', 'logs&operation=crime&crimeactions=crime_delpost'],
		['nav_logs_crime_warnpost', 'logs&operation=crime&crimeactions=crime_warnpost'],
		['nav_logs_crime_banpost', 'logs&operation=crime&crimeactions=crime_banpost'],
		['nav_logs_crime_banspeak', 'logs&operation=crime&crimeactions=crime_banspeak'],
		['nav_logs_crime_banvisit', 'logs&operation=crime&crimeactions=crime_banvisit'],
		['nav_logs_crime_banstatus', 'logs&operation=crime&crimeactions=crime_banstatus'],
		['nav_logs_crime_avatar', 'logs&operation=crime&crimeactions=crime_avatar'],
		['nav_logs_crime_sightml', 'logs&operation=crime&crimeactions=crime_sightml'],
		['nav_logs_crime_customstatus', 'logs&operation=crime&crimeactions=crime_customstatus'],
	]], '', $operation == 'crime'],
];

loadcache('adminlog');
$cleartypesext = [];
if(!empty($_G['cache']['adminlog'])) {
	$_submenu = [];
	foreach($_G['cache']['adminlog'] as $_operation) {
		$p = strpos($_operation, ':');
		if($p !== false) {
			list($pluginid, $f) = explode(':', $_operation);
			$_submenu[] = [lang('plugin/'.$pluginid, 'log_'.$f), 'logs&operation='.$_operation, $operation == $_operation];
			$cleartypesext[] = [$_operation, lang('plugin/'.$pluginid, 'log_'.$f)];
		}
	}
	$menu[] = [['menu' => 'nav_logs_plugin', 'submenu' => $_submenu]];
}

showsubmenu('nav_logs', $menu, $sel);

$filters = '';
echo <<<EOD
<script type="text/javascript">
function togglelog(k) {
	var logobj = $('log_'+k);
	if(logobj.style.display == 'none') {
		logobj.style.display = '';
	} else {
		logobj.style.display = 'none';
	}
}
</script>
EOD;

$file = childfile('logs/'.$operation);
if(!file_exists($file)) {
	$p = strpos($operation, ':');
	if($p !== false) {
		list($pluginid, $f) = explode(':', $operation);
		if(!ispluginkey($pluginid) || !preg_match('/^\w+$/', $f) || !file_exists($file = DISCUZ_PLUGIN($pluginid).'/log/log_'.$f.'.php')) {
			cpmsg('undefined_action');
		}
	} else {
		cpmsg('undefined_action');
	}
}

require_once $file;

function showdevice($id, $device, $colspan = 1) {
	return '<tbody id="log_'.$id.'" style="display:none; background-color: #cfd6dd;">'.
		'<tr><td colspan="'.$colspan.'"><strong>ClientIP:</strong> '.$device['client_ip'].'</td></tr>'.
		'<tr><td colspan="'.$colspan.'"><strong>Port:</strong> '.$device['client_port'].'</td></tr>'.
		'<tr><td colspan="'.$colspan.'"><strong>Browser:</strong> '.$device['client_browser'].'</td></tr>'.
		'<tr><td colspan="'.$colspan.'"><strong>Os:</strong> '.$device['client_os'].'</td></tr>'.
		'<tr><td colspan="'.$colspan.'"><strong>Device:</strong> '.$device['client_device'].'</td></tr>'.
		'<tr><td colspan="'.$colspan.'"><strong>User Agent:</strong> '.$device['client_useragent'].'</td></tr>'.
		'</tbody>';
}

function getactionarray() {
	$isfounder = true;
	$menu = $topmenu = [];
	foreach(table_common_admincp_menu_platform::t()->fetch_all_data() as $menuData) {
		$menu += (array)dunserialize($menuData['menu'])['menu'];
	}
	foreach($menu as $top => $v) {
		if(empty($v)) {
			continue;
		}
		$topmenu[$top] = '';
	}

	unset($topmenu['index'], $menu['index']);
	$actioncat = $actionarray = [];
	$actioncat[] = 'setting';
	$actioncat = array_merge($actioncat, array_keys($topmenu));
	foreach($menu as $tkey => $items) {
		foreach($items as $item) {
			$actionarray[$tkey][] = $item;
		}
	}
	return ['actions' => $actionarray, 'cats' => $actioncat];
}

function get_log_files($logdir = '', $action = 'action') {
	$dir = opendir($logdir);
	$files = [];
	while($entry = readdir($dir)) {
		$files[] = $entry;
	}
	closedir($dir);

	if($files) {
		sort($files);
		$logfile = $action;
		$logfiles = [];
		$ym = '';
		foreach($files as $file) {
			if(str_contains($file, $logfile)) {
				if(substr($file, 0, 6) != $ym) {
					$ym = substr($file, 0, 6);
				}
				$logfiles[$ym][] = $file;
			}
		}
		if($logfiles) {
			$lfs = [];
			foreach($logfiles as $ym => $lf) {
				$lastlogfile = $lf[0];
				unset($lf[0]);
				$lf[] = $lastlogfile;
				$lfs = array_merge($lfs, $lf);
			}
			return $lfs;
		}
		return [];
	}
	return [];
}

showtablefooter();
showtableheader('', 'fixpadding');
echo $multipage;
showtablefooter();

echo <<<EOD
<script type="text/javascript">
function togglecplog(k) {
	var cplogobj = $('cplog_'+k);
	if(cplogobj.style.display == 'none') {
		cplogobj.style.display = '';
	} else {
		cplogobj.style.display = 'none';
	}
}
</script>
EOD;


