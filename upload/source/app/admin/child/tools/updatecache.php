<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$step = max(1, intval($_GET['step']));
shownav('tools', 'nav_updatecache');
showsubmenusteps('nav_updatecache', [
	['nav_updatecache_confirm', $step == 1],
	['nav_updatecache_verify', $step == 2],
	['nav_updatecache_completed', $step == 3]
]);

/*search={"nav_updatecache":"action=tools&operation=updatecache"}*/
showtips('tools_updatecache_tips');
/*search*/

if($step == 1) {
	$extra = [];
	foreach(table_common_plugin::t()->fetch_all_data(1) as $plugin) {
		$dir = substr($plugin['directory'], 0, -1);
		$cachedir = DISCUZ_PLUGIN($dir).'/cache';
		if(file_exists($cachedir)) {
			$cachedirhandle = dir($cachedir);
			while($entry = $cachedirhandle->read()) {
				if(!in_array($entry, ['.', '..']) && preg_match('/^cache\_([\_\w]+)\.php$/', $entry, $entryr) && str_ends_with($entry, '.php') && is_file($cachedir.'/'.$entry)) {
					$id = 'plugin_'.$plugin['identifier'].':'.$entryr[1];
					$extra[] = "<input type=\"checkbox\" name=\"type[]\" value=\"{$id}\" id=\"{$id}\" class=\"checkbox\" /><label for=\"{$id}\">".$plugin['name'].'('.$entryr[1].')</label>';
				}
			}
		}
	}

	cpmsg("<input type=\"checkbox\" name=\"type[]\" value=\"data\" id=\"datacache\" class=\"checkbox\" checked /><label for=\"datacache\">".$lang['tools_updatecache_data'].'</label>'.
		"<input type=\"checkbox\" name=\"type[]\" value=\"tpl\" id=\"tplcache\" class=\"checkbox\" checked /><label for=\"tplcache\">".$lang['tools_updatecache_tpl'].'</label>'.
		($_G['setting']['ftp']['on'] == 2 ? "<input type=\"checkbox\" name=\"type[]\" value=\"oss\" id=\"osscache\" class=\"checkbox\" checked /><label for=\"osscache\">".$lang['tools_updatecache_oss'].'</label>' : '').
		"<input type=\"checkbox\" name=\"type[]\" value=\"blockclass\" id=\"blockclasscache\" class=\"checkbox\" /><label for=\"blockclasscache\">".$lang['tools_updatecache_blockclass'].'</label>'.
		"<input type=\"checkbox\" name=\"type[]\" value=\"csscache\" id=\"csscache\" class=\"checkbox\" /><label for=\"csscache\">".$lang['styles_csscache_update'].'</label>'.
		"<input type=\"checkbox\" name=\"type[]\" value=\"searchindex\" id=\"searchindex\" class=\"checkbox\" /><label for=\"searchindex\">".$lang['tools_updatecache_searchindex'].'</label>'.
		"<input type=\"checkbox\" name=\"type[]\" value=\"commonsearchindex\" id=\"commonsearchindex\" class=\"checkbox\" /><label for=\"commonsearchindex\">".$lang['tools_updatecache_common_searchindex'].'</label>'.
		implode('', $extra),
		'action=tools&operation=updatecache&step=2', 'form', '', FALSE);
} elseif($step == 2) {
	if(FORMHASH != $_GET['formhash']) {
		cpmsg('undefined_action', '', 'error');
	}
	$type = implode('_', (array)$_GET['type']);
	cpmsg(cplang('tools_updatecache_waiting'), "action=tools&operation=updatecache&step=3&type=$type&formhash=".FORMHASH, 'loading', '', FALSE);
} elseif($step == 3) {
	if(FORMHASH != $_GET['formhash']) {
		cpmsg('undefined_action', '', 'error');
	}
	if(substr($_GET['type'], 0, 6) != 'plugin') {
		$type = explode('_', $_GET['type']);
	} else {
		$type = [substr($_GET['type'], 7)];
	}
	if(in_array('oss', $type)) {
		define('IN_UPDATECACHE', 1);
		$type[] = 'data';
	}
	if(in_array('data', $type)) {
		updatecache();
		require_once libfile('function/group');
		$groupindex = [];
		$groupindex['randgroupdata'] = $randgroupdata = grouplist('lastupdate', ['ff.membernum', 'ff.icon'], 80);
		$groupindex['topgrouplist'] = $topgrouplist = grouplist('activity', ['f.commoncredits', 'ff.membernum', 'ff.icon'], 10);
		$groupindex['updateline'] = TIMESTAMP;
		$groupdata = table_forum_forum::t()->fetch_group_counter();
		$groupindex['todayposts'] = $groupdata['todayposts'];
		$groupindex['groupnum'] = $groupdata['groupnum'];
		savecache('groupindex', $groupindex);
		table_forum_groupfield::t()->truncate();
		savecache('forum_guide', []);
		if($_G['setting']['grid']['showgrid']) {
			savecache('grids', []);
		}
	} else {
		foreach($type as $v) {
			$extrys = explode(':', $v);
			if(count($extrys) > 1) {
				updatecache($v);
			}
		}
	}
	if((in_array('tpl', $type) && $_G['config']['output']['tplrefresh']) || in_array('csscache', $type)) {
		cleartemplatecache();
	}
	if(in_array('blockclass', $type)) {
		include_once libfile('function/block');
		blockclass_cache();
	}
	if(in_array('csscache', $type)) {
		if(in_array('data', $type)) {
			updatecache(['styles']);
		} else {
			updatecache(['setting', 'styles', 'smilies_js']);
		}
		loadcache('style_default', true);
		if(!in_array('data', $type)) {
			updatecache('updatediytemplate');
		}
	}
	if(in_array('searchindex', $type)) {
		require_once libfile('function/searchindex');
		searchindex_cache();
	}
	if(in_array('commonsearchindex', $type)) {
		table_common_searchindex::t()->truncate();
	}
	cpmsg('update_cache_succeed', '', 'succeed', '', FALSE);
}
	