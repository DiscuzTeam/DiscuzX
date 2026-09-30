<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$_GET['dir'] = isset($_GET['dir']) ? preg_replace('#([^\w]+)#is', '', $_GET['dir']) : '';
if(!submitcheck('importsubmit') && empty($_GET['dir'])) {

	shownav('template', 'styles_import');
	showsubmenu('styles_admin', [
		['styles_list', 'styles', 0],
		['styles_import', 'styles&operation=import', 1],
		$isfounder ? ['plugins_validator'.($updatecount ? '_new' : ''), 'styles&operation=upgradecheck', 0] : [],
		$isfounder ? ['cloudaddons_style_link', 'cloudaddons&frame=no&operation=templates&from=more', 0, 1] : [],
	], '<a href="https://www.dismall.com/?from=templates_question" target="_blank" class="rlink">'.$lang['templates_question'].'</a>');
	showformheader('styles&operation=import', 'enctype');
	showtableheader('');
	showimportdata();
	showtablerow('', 'colspan="2"', '<input class="checkbox" type="checkbox" name="ignoreversion" id="ignoreversion" value="1" /><label for="ignoreversion"> '.cplang('styles_import_ignore_version').'</label>');
	showsubmit('importsubmit');
	showtablefooter();
	showformfooter();

} else {
	if(!is_dir(DISCUZ_TEMPLATE($_GET['dir']))) {
		echo '<script type="text/javascript">top.location.href=\''.ADMINSCRIPT.'?action=cloudaddons&frame=no&id='.$_GET['dir'].'.template&from=recommendaddon\';</script>';
		exit;
	}
	require_once libfile('function/importdata');
	$restore = !empty($_GET['restore']) ? $_GET['restore'] : 0;
	if($restore) {
		$style = table_common_style::t()->fetch_by_styleid($restore);
		$_GET['dir'] = $style['directory'];
	}
	if(!empty($_GET['dir'])) {
		$renamed = import_styles($_GET['ignoreversion'], $_GET['dir'], $restore, setdefault: !empty($_GET['default']));
	} else {
		$renamed = import_styles($_GET['ignoreversion'], $_GET['dir']);
	}

	dsetcookie('addoncheck_template', '', -1);
	cpmsg(!empty($_GET['dir']) ? (!$restore ? 'styles_install_succeed' : 'styles_restore_succeed') : ($renamed ? 'styles_import_succeed_renamed' : 'styles_import_succeed'), 'action=styles', 'succeed');
}
	