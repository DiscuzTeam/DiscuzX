<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

lang('admincp_menu');

$extra = cpurl('url');
$charset = CHARSET;
$header_logout = cplang('header_logout');
$header_switchmenu = cplang('header_switchmenu');
$header_bbs = cplang('header_bbs');
if(isfounder()) {
	$cpadmingroup = cplang('founder_admin');
} else {
	if($GLOBALS['admincp']->adminsession['cpgroupid']) {
		$cpgroup = table_common_admincp_group::t()->fetch($GLOBALS['admincp']->adminsession['cpgroupid']);
		$cpadmingroup = $cpgroup['cpgroupname'];
	} else {
		$cpadmingroup = cplang('founder_master');
	}
}
require appfile('module/menu');
if(!$menu) {
	exit('Access Denied');
}

$framecss = '';
if(!empty($menuData['framecss'])) {
	if(str_ends_with($menuData['framecss'], '.css')) {
		$framecss = '<link rel="stylesheet" href="'.$menuData['framecss'].'?'.$_G['style']['verhash'].'" type="text/css" media="all" />';
	} else {
		$framecss = '<style>'.$menuData['framecss'].'</style>';
	}
}

$basescript = ADMINSCRIPT;
$staticurl = STATICURL;
$oldlayout = !empty($_G['cookie']['admincp_oldlayout']) ? ' class="oldlayout"' : '';
$first = reset($menu);

$leftmenus = '';
$_G['firstMenu'] = null;
foreach($topmenu as $k => $v) {
	if($k == 'cloud') {
		continue;
	}
	$leftmenus .= '<li id="lm_'.$k.'">';
	$menuname = cplang('header_'.$k) != 'header_'.$k ? cplang('header_'.$k) : $k;
	$leftmenus .= '<a id="leftmn_'.$k.'"><span>'.$menuname.'</span></a>';
	$leftmenus .= showmenu($k, $menu[$k], 2);
	$leftmenus .= '</li>';
}

if(!$extra || !getgpc('action')) {
	$extra = 'action='.$_G['firstMenu'];
}

$topmenus = '';
foreach($topmenu as $k => $v) {
	if($k == 'cloud') {
		continue;
	}
	if($v === '') {
		$v = is_array($menu[$k]) ? array_keys($menu[$k]) : [];
		$v = $menu[$k][$v[0]][1];
	}
	$topmenus .= showheader($k, $v, 1);
}
unset($menu);

$headers = "'".implode("','", array_keys($topmenu))."'";
$useravt = avatar(getglobal('uid'), 'middle', ['class' => 'avt']);

if(!empty($_GET['js'])) {
	$leftmenus = str_replace('target="main"', '', $leftmenus);
	echo '$(\'retheader_menu\').innerHTML = \''.addslashes('<ul id="jsmenu">'.$leftmenus.'</ul>').'\';';
	exit;
} else {
	$sitevip = '';
	if(isfounder() && $_G['setting']['siteuniqueid'] && !empty($_G['setting']['sitevipkey'])) {
		@include_once DISCUZ_ROOT.'./source/discuz_version.php';
		$sitevip = base64_encode($_G['setting']['siteuniqueid'].','.$_G['setting']['sitevipkey'].','.DISCUZ_VERSION.'/'.DISCUZ_SUBVERSION.'/'.DISCUZ_RELEASE.',EN');
	}
	require_once template('admin/main');
}

