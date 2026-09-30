<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$hookTypes = ['hookscript', 'hookscriptmobile'];

if(submitcheck('submit')) {
	$settingnew = [
		'hooksort' => (array)getgpc('hooksortnew')
	];

	require_once childfile('setting/updatecache');

	cpmsg('setting_update_succeed', 'action=plugins&operation=hooklist', 'succeed');
}

shownav('plugin');
showsubmenu('nav_plugins', [
	['plugins_list', 'plugins', 0],
	['plugins_hooklist', 'plugins&operation=hooklist', 1],
	['plugins_validator', 'plugins&operation=upgradecheck', 0],
	['cloudaddons_plugin_link', 'cloudaddons&frame=no&operation=plugins&from=more', 0, 1],
	['cloudaddons_witframe_link', 'cloudaddons&frame=no&operation=witframe&from=more', 0, 1],
]);

echo '<style>'.
	'.news-list .news-item:last-child { border-bottom: 2px solid var(--admincp-borderd) !important; }'.
	'input.txt { width: 40px; margin-right: 15px !important; }'.
	'</style>';

$hookfuncTypes = ['funcs', 'outputfuncs', 'messagefuncs'];

showtips('plugins_hooklist_tips');

showformheader('plugins&operation=hooklist');

foreach($hookTypes as $hooktype) {
	showboxheader('plugins_hooktype_'.$hooktype);
	foreach($_G['setting'][$hooktype] as $hscript => $hookscript) {
		echo '<div class="news-list"><div class="news-item xw1">'.indent(0).$hscript.'</div>';
		foreach($hookscript as $curscript => $scriptdata) {
			echo '<div class="news-item">'.indent(1).$curscript.'</div>';
			foreach($hookfuncTypes as $functype) {
				if(!is_array($scriptdata[$functype])) {
					continue;
				}
				echo '<div class="news-item">'.indent(2).$functype.'</div>';
				foreach($scriptdata[$functype] as $funcname => $funcs) {
					echo '<div class="news-item">'.indent(3).'{hook/'.$funcname.'}</div>';
					foreach($funcs as $func) {
						$sk = md5($func[0].':'.$func[1]);
						$key = "hooksortnew[$hooktype][$hscript][$curscript][$functype][$funcname][$sk]";
						echo '<div class="news-item">'.indent(4).'<input name="'.$key.'" class="txt" value="'.(!empty($func[99]) ? $func[99] : 0).'">'.$func[0].'::'.$func[1].'()</div>';
					}
				}
			}
		}
		echo '</div>';
	}
	showboxfooter();
}

showtableheader();
showsubmit('submit');
showtablefooter();

showformfooter();

function indent($level) {
	$w = $level * 30;
	return '<i style="width:'.$w.'px;display: inline-block;"></i>';
}