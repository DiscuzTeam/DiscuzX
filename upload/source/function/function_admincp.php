<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

@set_time_limit(0);

function istpldir($dir) {
	$tplRoot = DISCUZ_TEMPLATE();
	return is_dir($tplRoot.$dir) && !in_array(substr($dir, -1, 1), ['/', '\\']);
}

function isplugindir($dir) {
	return preg_match('/^[a-z]+[a-z0-9_]*\/$/', $dir);
}

function ispluginkey($key) {
	return preg_match('/^[a-z]+[a-z0-9_]*$/i', $key);
}

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function filemtimesort($a, $b) {
	if($a['filemtime'] == $b['filemtime']) {
		return 0;
	}
	return ($a['filemtime'] > $b['filemtime']) ? 1 : -1;
}

function checkpermission($action, $break = 1) {
	global $_G;
	if(!isset($_G['config']['admincp'])) {
		cpmsg('action_access_noexists', '', 'error');
	} elseif($break && !$_G['config']['admincp'][$action]) {
		cpmsg('action_noaccess_config', '', 'error');
	} else {
		return $_G['config']['admincp'][$action];
	}
}

function upgradeinformation($status = 0) {
	global $_G, $upgrade_step;

	if(empty($upgrade_step)) {
		return '';
	}

	$update = [];
	$siteuniqueid = table_common_setting::t()->fetch_setting('siteuniqueid');

	$update['uniqueid'] = $siteuniqueid;
	$update['curversion'] = $upgrade_step['curversion'];
	$update['currelease'] = $upgrade_step['currelease'];
	$update['upgradeversion'] = $upgrade_step['version'];
	$update['upgraderelease'] = $upgrade_step['release'];
	$update['step'] = $upgrade_step['step'] == 'dbupdate' ? 4 : $upgrade_step['step'];
	$update['status'] = $status;

	$data = '';
	foreach($update as $key => $value) {
		$data .= $key.'='.rawurlencode($value).'&';
	}

	$upgradeurl = 'ht'.'tp:/'.'/cus'.'tome'.'r.disc'.'uz.n'.'et/upg'.'rade'.'.p'.'hp?'.'os=dx&update='.rawurlencode(base64_encode($data)).'&timestamp='.TIMESTAMP;
	return '<img src="'.$upgradeurl.'" />';
}

function isfounder($user = '') {
	$user = empty($user) ? getglobal('member') : $user;
	return $GLOBALS['admincp']->checkfounder($user);
}


function cplang($name, $replace = [], $output = false, $mitframeApp = false) {
	global $_G;
	$ret = '';

	if(!isset($_G['lang']['admincp'])) {
		lang('admincp');
	}
	if(!isset($_G['lang']['admincp_menu'])) {
		lang('admincp_menu');
	}
	if(!isset($_G['lang']['admincp_msg'])) {
		lang('admincp_msg');
	}
	$lang = [];
	$mitframeApp = $mitframeApp ?: (defined('MITFRAME_APP_ADMIN') ? MITFRAME_APP_ADMIN : '');
	if($mitframeApp && file_exists($loadfile = MITFRAME_APP($mitframeApp).'/language/lang_admincp.php')) {
		include $loadfile;
		$_G['lang']['admincp'] += $lang;
	}

	if(isset($_G['lang']['admincp'][$name])) {
		$ret = $_G['lang']['admincp'][$name];
	} elseif(isset($_G['lang']['admincp_menu'][$name])) {
		$ret = $_G['lang']['admincp_menu'][$name];
	} elseif(isset($_G['lang']['admincp_msg'][$name])) {
		$ret = $_G['lang']['admincp_msg'][$name];
	}

	$ret = $ret ? $ret : ($replace === false ? '' : $name);
	if($replace && is_array($replace)) {
		$s = $r = [];
		foreach($replace as $k => $v) {
			$s[] = '{'.$k.'}';
			$r[] = $v;
		}
		$ret = str_replace($s, $r, $ret);
	}
	$output && print($ret);
	return $ret;
}

function admincustom($title, $url, $sort = 0) {
	global $_G;
	$url = ADMINSCRIPT.'?'.$url;
	$id = table_common_admincp_cmenu::t()->fetch_id_by_uid_sort_url($_G['uid'], $sort, $url);
	if($id) {
		table_common_admincp_cmenu::t()->update($id, ['title' => $title, 'dateline' => $_G['timestamp']]);
		table_common_admincp_cmenu::t()->increase_clicks($id);
	} else {
		$id = table_common_admincp_cmenu::t()->insert([
			'title' => $title,
			'url' => $url,
			'sort' => $sort,
			'uid' => $_G['uid'],
			'dateline' => $_G['timestamp'],
		], true);
	}
	return $id;
}

function cpurl($type = 'parameter', $filters = ['sid', 'frames']) {
	parse_str($_SERVER['QUERY_STRING'], $getarray);
	if(!isset($getarray['frames'])) {
		$getarray['frames'] = 'yes';
	}
	$extra = $and = '';
	foreach($getarray as $key => $value) {
		if(!in_array($key, $filters)) {
			$extra .= $and.$key.($type == 'parameter' ? '%3D' : '=').rawurlencode((string)$value);
			$and = $type == 'parameter' ? '%26' : '&';
		}
	}
	return $extra;
}


function showheader($key, $url, $return = 0) {
	$menuname = cplang('header_'.$key) != 'header_'.$key ? cplang('header_'.$key) : $key;
	$ret = '<li><button id="header_'.$key.'">'.$menuname.'</button></li>';
	if($return) {
		return $ret;
	} else {
		echo $ret;
	}
}

function shownav($header = '', $menu = '', $nav = '', $plaintext = false) {
}

function currentmenuid($parent = false) {
	$action_now = $_GET['action'] ?? '';
	$operation_now = !$parent && isset($_GET['operation']) ? $_GET['operation'] : '';
	$do_now = $action_now == 'plugins' && !$parent && isset($_GET['do']) ? $_GET['do'] : '';
	$identifier_now = $action_now == 'plugins' && isset($_GET['identifier']) ? $_GET['identifier'] : '';
	$pmod_now = $_GET['pmod'] ?? '';

	if(!$action_now) {
		return '';
	}
	if($action_now == 'plugins' && $identifier_now) {
		$menuid = 'plugin_'.$identifier_now.($pmod_now ? '_'.$pmod_now : '');
	} else {
		$menuid = $action_now.($operation_now ? '_'.$operation_now : '').($do_now ? ':'.$do_now : '');
	}
	return $menuid;
}

function showmenu($key, $menus, $return = 0) {
	global $_G;
	$body = '';
	$topMenu_now = false;
	if(is_array($menus)) {
		foreach($menus as $menu) {
			if($menu[0] && $menu[1]) {
				if(!str_contains($menu[1], 'plugins&operation=config') && !str_starts_with($menu[1], 'http')) {
					list($action, $operation, $do) = explode('_', $menu[1]);
					$active = '';
					if($action == 'plugin') {
						list($identifier, $pmod, $do) = explode(':', substr($menu[1], strpos($menu[1], '_') + 1));
						$identifier_now = $_GET['identifier'] ?? '';
						$pmod_now = $_GET['pmod'] ?? '';
						$do_now = $_GET['do'] ?? '';
						$menu_now = 'plugin'.($identifier_now ? '_'.$identifier_now : '').($pmod_now ? ':'.$pmod_now : '').($do_now ? ':'.$do_now : '');
						$menuid = $menu[1];
						$menu[1] = 'plugins&operation=config'.($identifier ? '&identifier='.$identifier.($pmod ? '&pmod='.$pmod : '') : '').($do ? '&do='.$do : '');
						if(!empty($_GET['pmod']) && !empty($_GET['do'])) {
							$_G['firstMenu'] = 'submn_'.$menuid;
						}
					} elseif($action == 'app') {
						list($app, $filename, $do) = explode(':', substr($menu[1], strpos($menu[1], '_') + 1));
						$operation_now = $_GET['operation'] ?? '';
						$do_now = $_GET['do'] ?? '';
						$menu_now = 'app'.($operation_now ? '_'.$operation_now : '').($do_now ? ':'.$do_now : '');
						$menuid = $menu[1];
						$menu[1] = 'app&operation='.($app ? $app.($filename ? ':'.$filename : '') : '').($do ? '&do='.$do : '');
					} else {
						$action_now = $_GET['action'] ?? '';
						$operation_now = $_GET['operation'] ?? '';
						$do_now = $_GET['do'] ?? '';
						$menu_now = $action_now.($operation_now ? '_'.$operation_now : '').($do_now ? '_'.$do_now : '');
						$menuid = $menu[1];
						$menu[1] = $action.($operation ? '&operation='.$operation.($do ? '&do='.$do : '') : '');
					}
					if($menuid == $menu_now) {
						$topMenu_now = true;
						$active = 'class="active"';
						$_G['defaultTab'] = $menuid;
					}
				}
				if(str_contains($menu[1], '{')) {
					loaducenter();
					$menu[1] = str_replace('{UCAPI}', UC_API, $menu[1]);
				}

				if(empty($_G['firstMenu'])) {
					$_G['firstMenu'] = $menu[1];
					$topMenu_now = true;
				}
				$body .= '<li><a '.($menuid ? 'id="submn_'.$menuid.'" ' : '').$active.'href="'.(str_starts_with($menu[1], 'http') ? $menu[1] : ADMINSCRIPT.'?action='.$menu[1]).'" target="'.($menu[2] ? $menu[2] : 'main').'"'.($menu[3] ? $menu[3] : '').'><em title="'.cplang('nav_newwin').'"></em><div>'.cplang($menu[0]).'</div></a></li>';
			} elseif($menu[0] && $menu[2]) {
				if($menu[2] == 1) {
					$id = 'M'.substr(md5($menu[0]), 0, 8);
					$hide = false;
					if(!empty($_G['cookie']['cpmenu_'.$id])) {
						$hide = true;
					}
					$body .= '<li class="s"><span class="group">'.$menu[0].'</span><ol style="display:'.($hide ? 'none' : '').'" id="'.$id.'">';
				}
				if($menu[2] == 2) {
					$body .= '<li class="sp"></li></ol></li>';
				}
			}
		}
	}
	if(!$return || $return == 2) {
		$ret = '<ul id="menu_'.$key.'">'.$body;
		if($topMenu_now) {
			$ret .= '<script>defaultNav = \''.$key.'\';</script>';
		}
		$ret .= '</ul>';
		if($return == 2) {
			return $ret;
		} else {
			echo $ret;
		}
	} else {
		return $body;
	}
}

function updatemenu($key) {
	require appfile('module/menu', 'admin');
	$s = showmenu($key, $menu[$key], 1);
	echo '<script type="text/JavaScript">if(parent.$(\'menu_'.$key.'\')) {
		parent.$(\'menu_'.$key.'\').innerHTML = \''.str_replace("'", "\'", $s).'\';parent.reloadmenu(\'nav ul #menu_plugin a\');
	}</script>';
}

function cpmsg_error($message, $url = '', $extra = '', $halt = TRUE) {
	return cpmsg($message, $url, 'error', [], $extra, $halt);
}

function frame_cpmsg($message, $succeed = false, $url = '') {
	if(!$_POST) {
		cpmsg_error($message);
		return;
	}
	$vars = explode(':', $message);
	$values = ['ADMINSCRIPT' => ADMINSCRIPT];
	$message = cplang($message, $values);
	if(!$succeed) {
		echo "<script>parent.showDialog('<div class=infotitle3 style=\"padding:5px 10px\">".$message."</div>', 'alert', '".cplang('frame_cpmsg_title')."');</script>";
	} else {
		$url = $url ? "'".$url."'" : 'parent.location';
		echo "<script>
			if(typeof parent.cpmsgHook == 'undefined') {	
				parent.showDialog('<div class=infotitle2 style=\"padding:5px 10px\">".$message."</div>', 'info', '".cplang('frame_cpmsg_title')."');
				setTimeout(function() {parent.location = $url;}, 3000);
                        } else {
                                parent.cpmsgHook('$message', $url);
                        }
			</script>";
	}
	exit;
}

function cpmsg($message, $url = '', $type = 'error', $values = [], $extra = '', $halt = TRUE, $cancelurl = '') {
	global $_G;
	if(!empty($values['frame'])) {
		if($type == 'succeed') {
			frame_cpmsg($message, true);
		} else {
			frame_cpmsg($message, false);
		}
		return;
	}
	$vars = explode(':', $message);
	$values = is_array($values) ? $values : (array)$values;
	$values['ADMINSCRIPT'] = ADMINSCRIPT;
	if(count($vars) == 2) {
		$message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$message = cplang($message, $values);
	}
	$classname = match ($type) {
		'download', 'succeed' => 'infotitle2',
		'error' => 'infotitle3',
		'loadingform', 'loading' => 'infotitle1',
		default => 'marginbot normal',
	};
	if($url) {
		$url = preg_match('/^https?:\/\//is', $url) ? $url : ADMINSCRIPT.'?'.$url;
	}
	$message = "<h4 class=\"$classname\">$message</h4>";
	$url .= $url && !empty($_GET['scrolltop']) ? '&scrolltop='.intval($_GET['scrolltop']) : '';

	if($type == 'form') {
		$message = "<form method=\"post\" action=\"$url\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\">".
			"<br />$message$extra<br />".
			"<p class=\"margintop\"><input type=\"submit\" class=\"btn\" name=\"confirmed\" value=\"".cplang('ok')."\"> &nbsp; \n".
			($cancelurl ? "<input type=\"button\" class=\"btn\" value=\"".cplang('cancel')."\" onClick=\"location.href='$cancelurl'\">" :
				"<script type=\"text/javascript\">".
				"if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<input type=\"button\" class=\"btn\" value=\"".cplang('cancel')."\" onClick=\"history.go(-1);\">');".
				'</script>').
			'</p></form><br />';
	} elseif($type == 'loadingform') {
		$message = "<form method=\"post\" action=\"$url\" id=\"loadingform\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\"><br />$message$extra<div class=\"loading-wrap\" style=\"width: 100%\"><div class=\"loading-inner\"></div></div><br />".
			'<p class="marginbot"><a href="###" onclick="$(\'loadingform\').submit();" class="lightlink">'.cplang('message_redirect').'</a></p></form><br /><script type="text/JavaScript">setTimeout("$(\'loadingform\').submit();", 2000);</script>';
	} else {
		$message .= $extra.($type == 'loading' ? '<div class="loading-wrap"><div class="loading-inner"></div></div><br />' : '');
		if($url) {
			if($type == 'button') {
				$message = "<br />$message<br /><p class=\"margintop\"><input type=\"submit\" class=\"btn\" name=\"submit\" value=\"".cplang('start')."\" onclick=\"location.href='$url'\" />";
			} else {
				$message .= '<p class="marginbot"><a href="'.$url.'" class="lightlink">'.cplang($type == 'download' ? 'message_download' : 'message_redirect').'</a></p>';
				$timeout = $type != 'loading' ? 3000 : 1000;
				$message .= "<script type=\"text/JavaScript\">setTimeout(\"redirect('$url');\", $timeout);</script>";
			}
		} elseif($type != 'succeed') {
			$message .= '<p class="marginbot">'.
				"<script type=\"text/javascript\">".
				"if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" class=\"lightlink\">".cplang('message_return')."</a>');".
				'</script>'.
				'</p>';
		}
	}

	if($halt) {
		echo '<div class="infobox"><h3>'.cplang('discuz_message').'</h3>'.$message.'</div>';
		exit();
	} else {
		echo '<div class="infobox">'.$message.'</div>';
	}
}

function cpheader() {
	global $_G;

	if(!defined('DISCUZ_CP_HEADER_OUTPUT')) {
		define('DISCUZ_CP_HEADER_OUTPUT', true);
	} else {
		return true;
	}
	$IMGDIR = $_G['style']['imgdir'];
	$STYLEID = $_G['setting']['styleid'];
	$VERHASH = $_G['style']['verhash'];
	$frame = getgpc('frame') != 'no' ? 1 : 0;
	$charset = CHARSET;
	$staticurl = STATICURL;
	$basescript = ADMINSCRIPT;

	$pagecss = '';
	if(!empty($_G['cache']['admin']['platform'][PLATFORM]['pagecss'])) {
		if(str_ends_with($_G['cache']['admin']['platform'][PLATFORM]['pagecss'], '.css')) {
			$pagecss = '<link rel="stylesheet" href="'.$_G['cache']['admin']['platform'][PLATFORM]['pagecss'].'?'.$_G['style']['verhash'].'" type="text/css" media="all" />';
		} else {
			$pagecss = '<style>'.$_G['cache']['admin']['platform'][PLATFORM]['pagecss'].'</style>';
		}
	}

	$title = !empty($_G['cache']['admin']['platform'][PLATFORM]['name']) ? $_G['cache']['admin']['platform'][PLATFORM]['name'] :
		cplang('home_welcome', ['bbname' => $_G['setting']['bbname']]);

	$mnid = currentmenuid();
	$mnidparent = currentmenuid(true);
	if(!empty($_G['cache']['admin']['subperms'][$mnid])) {
		$mnid = $_G['cache']['admin']['subperms'][$mnid];
	}
	$cpurl = cpurl();

	require_once template('admin/cpheader');

	if(empty($_G['inajax'])) {
		register_shutdown_function('cpfooter');
	}
}

function showsubmenu($title, $menus = [], $right = '', $replace = []) {
	$s = (!empty($title) || !empty($right)) ? '<div class="itemtitle"><div class="titlerow"><h3>'.cplang($title, $replace).'</h3>'.$right.'</div>' : '<div class="itemtitle">';
	if(empty($menus)) {
		$s .= '</div>';
	} elseif(is_array($menus)) {
		$s .= '<ul class="tab1">';
		foreach($menus as $k => $menu) {
			if(is_array($menu[0])) {
				$s .= '<li id="addjs'.$k.'" class="'.($menu[1] ? 'current' : 'hasdropmenu').'" onmouseover="dropmenu(this);"><a href="#"><span>'.cplang($menu[0]['menu'], $replace).'<em>&nbsp;&nbsp;</em></span></a><div id="addjs'.$k.'child" class="dropmenu" style="display:none;">';
				if(is_array($menu[0]['submenu'])) {
					foreach($menu[0]['submenu'] as $submenu) {
						$s .= $submenu[1] ? '<a href="'.ADMINSCRIPT.'?action='.$submenu[1].'" class="'.($submenu[2] ? 'current' : '').'" onclick="'.$submenu[3].'">'.cplang($submenu[0], $replace).'</a>' : '<a><b>'.cplang($submenu[0], $replace).'</b></a>';
					}
				}
				$s .= '</div></li>';
			} elseif(!empty($menu)) {
				$s .= '<li'.($menu[2] ? ' class="current"' : '').'><a href="'.(!$menu[4] ? ADMINSCRIPT.'?action='.$menu[1] : $menu[1]).'"'.(!empty($menu[3]) ? ' target="_blank"' : '').'><span>'.cplang($menu[0], $replace).'</span></a></li>';
			}
		}
		$s .= '</ul></div>';
	}
	echo !empty($menus) ? '<div class="floattop">'.$s.'</div><div class="floattopempty"></div>' : $s;
	echo '</div><div class="cpcontainer">';
}

function showchildmenu($parents, $title, $menus = [], $right = '', $isanchor = false) {
	$parenttitle = '';
	foreach($parents as $parent) {
		if($parent[0] === '') {
			continue;
		}
		$parenttitle .= !empty($parent[1]) ? '<a href="'.ADMINSCRIPT.'?action='.$parent[1].'" class="parent">'.cplang($parent[0], $parent[2] ?? []).'</a>' : cplang($parent[0], $parent[2] ?? []);
		$parenttitle .= ' <em>&raquo;</em> ';
	}
	$parenttitle .= dhtmlspecialchars($title);
	$isanchor ? showsubmenuanchors($parenttitle, $menus, $right) : showsubmenu($parenttitle, $menus, $right);
}

function showsubmenusteps($title, $menus = [], $mleft = [], $mright = []) {
	$s = '<div class="itemtitle"'.(empty($title) ? ' style="margin-bottom: 12px;"' : '').'>'.($title ? '<h3>'.cplang($title).'</h3>' : '');
	if(is_array($mleft) && !empty($mleft)) {
		$s .= '<ul class="tab1" style="margin-right:10px">';
		foreach($mleft as $k => $menu) {
			$s .= '<li'.($menu[2] ? ' class="current"' : '').'><a href="'.(!$menu[4] ? ADMINSCRIPT.'?action='.$menu[1] : $menu[1]).'"'.(!empty($menu[3]) ? ' target="_blank"' : '').'><span>'.cplang($menu[0]).'</span></a></li>';
		}
		$s .= '</ul>';
	}
	if(is_array($menus) && !empty($menus)) {
		$s .= '<ul class="stepstat" '.(empty($title) ? ' style="padding-top:16px"' : '').'>';
		$i = 0;
		$ic = 1;
		foreach($menus as $menu) {
			$i++;
			$s .= '<li'.($ic ? ' class="current"' : '').' id="step'.$i.'"><span>'.$i.'</span>'.cplang($menu[0]).'</li>';
			if($menu[1]) $ic = 0;
		}
		$s .= '</ul>';
	}
	if(is_array($mright) && !empty($mright)) {
		$s .= '<ul class="tab1">';
		foreach($mright as $k => $menu) {
			$s .= '<li'.($menu[2] ? ' class="current"' : '').'><a href="'.(!$menu[4] ? ADMINSCRIPT.'?action='.$menu[1] : $menu[1]).'"'.(!empty($menu[3]) ? ' target="_blank"' : '').'><span>'.cplang($menu[0]).'</span></a></li>';
		}
		$s .= '</ul>';
	}
	$s .= '</div>';
	echo $s;
	if(!empty($title)) {
		echo '</div><div class="cpcontainer">';
	}
}

function showsubmenuanchors($title, $menus = [], $right = '') {
	if(!$title || !$menus || !is_array($menus)) {
		return;
	}
	echo <<<EOT
<script type="text/JavaScript">var currentAnchor = '{$GLOBALS['anchor']}';</script>
EOT;
	$s = '<div class="itemtitle"><div class="titlerow"><h3>'.cplang($title).'</h3>'.$right.'</div>';
	$s .= '<ul class="tab1" id="submenu">';
	foreach($menus as $k => $menu) {
		if($menu && is_array($menu)) {
			if(is_array($menu[0])) {
				$s .= '<li id="nav_m'.$k.'" class="hasdropmenu" onmouseover="dropmenu(this);"><a href="#"><span>'.cplang($menu[0]['menu']).'<em>&nbsp;&nbsp;</em></span></a><div id="nav_m'.$k.'child" class="dropmenu" style="display:none;"><ul>';
				if(is_array($menu[0]['submenu'])) {
					foreach($menu[0]['submenu'] as $submenu) {
						if(empty($submenu[0])) {
							continue;
						}
						$s .= '<li '.(!$submenu[3] ? ' id="nav_'.$submenu[1].'" onclick="showanchor(this)"' : '').($submenu[2] ? ' class="current"' : '').'><a href="'.($submenu[3] ? ADMINSCRIPT.'?action='.$submenu[1] : '#anchor_'.$submenu[1]).'">'.cplang($submenu[0]).'</a></li>';
					}
				}
				$s .= '</ul></div></li>';
			} else {
				$s .= '<li'.(!$menu[3] ? ' id="nav_'.$menu[1].'" onclick="showanchor(this)"' : '').($menu[2] ? ' class="current"' : '').'><a href="'.($menu[3] ? ADMINSCRIPT.'?action='.$menu[1] : '#anchor_'.$menu[1]).'"><span>'.cplang($menu[0]).'</span></a></li>';
			}
		}
	}
	$s .= '</ul>';
	$s .= '</div>';
	echo !empty($menus) ? '<div class="floattop">'.$s.'</div><div class="floattopempty"></div>' : $s;
	echo '<script type="text/JavaScript">_attachEvent(window, \'load\', function() { if(location.hash.indexOf(\'#anchor_\') === 0) { showanchor($(\'nav_\' + location.hash.substring(8))); } }, document);</script>';
	echo '</div><div class="cpcontainer">';
}

function showtips($tips, $id = 'tips', $display = TRUE, $title = '') {
	$tips = cplang($tips);
	$tips = preg_replace('#</li>\s*<li>#i', '</li><li>', $tips);
	$tmp = explode('</li><li>', substr($tips, 4, -5));
	if(count($tmp) > 4) {
		$tips = '<li>'.$tmp[0].'</li><li>'.$tmp[1].'</li><li id="'.$id.'_more" style="border: none; background: none; margin-bottom: 6px;"><a href="###" onclick="var tiplis = $(\''.$id.'lis\').getElementsByTagName(\'li\');for(var i = 0; i < tiplis.length; i++){tiplis[i].style.display=\'\'}$(\''.$id.'_more\').style.display=\'none\';">'.cplang('tips_all').'...</a></li>';
		foreach($tmp as $k => $v) {
			if($k > 1) {
				$tips .= '<li style="display: none">'.$v.'</li>';
			}
		}
	}
	unset($tmp);
	$title = $title ? $title : 'tips';
	showboxheader('<i class="tips_title"></i>'.cplang($title), 'tips', 'id="'.$id.'"'.(!$display ? ' style="display: none;"' : ''), 0);
	showboxrow('', 'class="tipsblock" s="1"', '<ul id="'.$id.'lis">'.$tips.'</ul>');
	showboxfooter();
}

function showformheader($action, $extra = '', $name = 'cpform', $method = 'post', $autocomplete = 'on') {
	global $_G;
	$multi = $before = '';
	if(isset($_G['showsetting_multi'])) {
		$multi = ' onsubmit="return multisubmit(\''.$name.'\')" target="multiframe"';
		$before = '<iframe id="multiframe" name="multiframe" style="display: none"></iframe><script>var multiStep = 0;function cpmsgHook(msg, url) {multiStep++;multisubmit(\''.$name.'\', msg, url)}</script>';
	}
	$anchor = isset($_GET['anchor']) ? dhtmlspecialchars($_GET['anchor']) : '';
	echo $before.'<form name="'.$name.'" method="'.$method.'" autocomplete="'.$autocomplete.'" action="'.ADMINSCRIPT.'?action='.$action.'" id="'.$name.'"'.($extra == 'enctype' ? ' enctype="multipart/form-data"' : " $extra").$multi.'>'.
		'<input type="hidden" name="formhash" value="'.FORMHASH.'" />'.
		'<input type="hidden" id="formscrolltop" name="scrolltop" value="" />'.
		'<input type="hidden" name="anchor" value="'.$anchor.'" />'.
		(isset($_G['showsetting_multi']) ? '<input type="hidden" name="multijssubmit" value="yes" />' : '');
}

function showhiddenfields($hiddenfields = []) {
	if(is_array($hiddenfields)) {
		foreach($hiddenfields as $key => $val) {
			$val = is_string($val) ? dhtmlspecialchars($val) : $val;
			echo "\n<input type=\"hidden\" name=\"$key\" value=\"$val\">";
		}
	}
}

function showtableheader($title = '', $classname = '', $extra = '', $titlespan = 15) {
	global $_G;
	$classname = str_replace(['nobottom', 'notop'], ['nobottom nobdb', 'nobdt'], $classname);
	if(isset($_G['showsetting_multi'])) {
		if($_G['showsetting_multi'] == 0) {
			$extra .= ' style="width:'.($_G['showsetting_multicount'] * 300 + 230).'px"';
			$classname .= ' multitable';
		} else {
			return;
		}
	}
	echo "\n".'<table class="tb tb2 '.$classname.'"'.($extra ? " $extra" : '').'>';
	if($title) {
		$span = $titlespan ? 'colspan="'.$titlespan.'"' : '';
		echo "\n".'<tr><th '.$span.' class="partition">'.cplang($title).'</th></tr>';
		showmultititle(1);
	}
}

function showboxheader($title = '', $classname = '', $extra = '', $nobody = 0) {
	global $_G;
	$classname = str_replace(['nobottom', 'notop'], ['nobottom nobdb', 'nobdt'], $classname);
	echo "\n".'<div class="dbox'.($classname ? ' '.$classname : '').'"'.($extra ? " $extra" : '').'>';
	if($title) {
		echo "\n".'<div class="boxheader">'.cplang($title).'</div>';
		showmultititle(1);
	}
	if(!$nobody) {
		echo "\n".'<div class="boxbody">';
	}
}

function showmultititle($nofloat = 0) {
	global $_G;
	if(isset($_G['showtableheader_multi']) && $_G['showsetting_multi'] == 0) {
		$i = 0;
		$rows = '';
		foreach($_G['showtableheader_multi'] as $row) {
			$i++;
			$rows .= '<div class="multicol">'.$row.'</div>';
		}
		if($nofloat) {
			echo '<tr><td class="tbm multileft"></td><td class="tbm"><div>'.$rows.'</div></td></tr>';
		} else {
			$rows = '<div class="multileft">&nbsp;</div>'.$rows;
			echo '<div id="multititle" class="tbm" style="width:'.($i * 300 + 370).'px;display:none">'.$rows.'</div>';
			echo '<script type="text/javascript">floatbottom(\'multititle\');</script>';
		}
	}
}

function showtagheader($tagname, $id, $display = FALSE, $classname = '') {
	global $_G;
	if(!empty($_G['showsetting_multi'])) {
		return;
	}
	echo '<'.$tagname.(!isset($_G['showsetting_multi']) && $classname ? " class=\"$classname\"" : '').' id="'.$id.'"'.($display ? '' : ' style="display: none"').'>';
}

function showtitle($title, $extra = '', $multi = 1, $norelatedlink = false) {
	global $_G;
	if(!empty($_G['showsetting_multi'])) {
		return;
	}
	$title = cplang($title);
	!$norelatedlink && $title .= getrelatedlink($title);
	echo "\n".'<tr'.($extra ? " $extra" : '').'><th colspan="15" class="partition">'.$title.'</th></tr>';
	if($multi) {
		showmultititle(1);
	}
}

function showboxtitle($title, $extra = '', $multi = 1) {
	global $_G;
	if(!empty($_G['showsetting_multi'])) {
		return;
	}
	echo "\n".'<div class="boxheader"'.($extra ? " $extra" : '').'>'.cplang($title).'</div>';
}

function showsubtitle($title = [], $rowclass = 'header', $tdstyle = []) {
	if(is_array($title)) {
		$subtitle = "\n<tr class=\"$rowclass\">";
		foreach($title as $k => $v) {
			if($v !== NULL) {
				$subtitle .= '<th'.($tdstyle[$k] ? ' '.$tdstyle[$k] : '').'>'.cplang($v).'</th>';
			}
		}
		$subtitle .= '</tr>';
		echo $subtitle;
	}
}

function showtablerow($trstyle = '', $tdstyle = [], $tdtext = [], $return = FALSE) {
	$rowswapclass = '';
	if(!preg_match('/class\s*=\s*[\'"]([^\'"<>]+)[\'"]/i', $trstyle, $matches)) {
		$rowswapclass = is_array($tdtext) && count($tdtext) > 2 ? ' class="hover"' : '';
	} else {
		if(is_array($tdtext) && count($tdtext) > 2) {
			$rowswapclass = " class=\"{$matches[1]} hover\"";
			$trstyle = preg_replace('/class\s*=\s*[\'"]([^\'"<>]+)[\'"]/i', '', $trstyle);
		}
	}
	$cells = "\n".'<tr'.($trstyle ? ' '.$trstyle : '').$rowswapclass.'>';
	if(isset($tdtext)) {
		if(is_array($tdtext)) {
			foreach($tdtext as $key => $td) {
				$cells .= '<td'.(is_array($tdstyle) && !empty($tdstyle[$key]) ? ' '.$tdstyle[$key] : '').'>'.$td.'</td>';
			}
		} else {
			$cells .= '<td'.(!empty($tdstyle) && is_string($tdstyle) ? ' '.$tdstyle : '').'>'.$tdtext.'</td>';
		}
	}
	$cells .= '</tr>';
	if($return) {
		return $cells;
	}
	echo $cells;
}

function showboxrow($trstyle = '', $tdstyle = [], $tdtext = [], $return = FALSE) {
	$rowswapclass = '';
	if(preg_match('/class\s*=\s*[\'"]([^\'"<>]+)[\'"]/i', $trstyle, $matches)) {
		$rowswapclass = $matches[1];
		$trstyle = preg_replace('/class\s*=\s*[\'"]([^\'"<>]+)[\'"]/i', '', $trstyle);
	}
	if(is_array($tdtext) && count($tdtext) > 2) {
		$rowswapclass .= ' hover';
	}
	$rowswapclass = ' class="drow'.($rowswapclass ? (' '.$rowswapclass) : '').'"';
	$cells = "\n".'<div'.($trstyle ? ' '.$trstyle : '').$rowswapclass.'>';
	if(isset($tdtext)) {
		if(is_array($tdtext)) {
			foreach($tdtext as $key => $td) {
				$cells .= '<div'.(is_array($tdstyle) && !empty($tdstyle[$key]) ? ' '.$tdstyle[$key] : '').'>'.$td.'</div>';
			}
		} else {
			$cells .= '<div'.(!empty($tdstyle) && is_string($tdstyle) ? ' '.$tdstyle : '').'>'.$tdtext.'</div>';
		}
	}
	$cells .= '</div>';
	if($return) {
		return $cells;
	}
	echo $cells;
}

function showboxbody($class = '', $text = '', $extra = '') {
	echo '<div class="boxbody'.($class ? (' '.$class) : '').'" '.$extra.'>'.$text.'</div>';
}

function showcomponent($setname, $varname, $value, $type, $comment = '', $conf = null) {
	global $_G;

	static $showextra = [];

	$extra = [];
	$var = ['title' => $setname, 'variable' => $varname, 'value' => $value, 'type' => $type, 'description' => $comment, 'extra' => $conf];
	$_G['showcomponent'][$var['type']][] = $var['variable'];
	admin\class_component::type_plugin($var, $extra);

	showsetting(dhtmlspecialchars($var['title']), $var['variable'], $var['value'], $var['type'], '', 0, nl2br(dhtmlspecialchars($var['description'])), dhtmlspecialchars($var['extra']), '', true, 0, !empty($var['widemode']), $var['norelatedlink'] ?? false);

	foreach($extra as $k => $v) {
		if(!empty($showextra[$k])) {
			continue;
		}
		$showextra[$k] = 1;
		echo $v;
	}
}

function serializecomponent() {
	global $_G;

	if(empty($_GET['_components'])) {
		return;
	}

	$_components = json_decode($_GET['_components'], 1);
	foreach((array)$_components as $type => $varnames) {
		foreach($varnames as $varname) {
			$v = admin\class_component::assign_get($varname);
			admin\class_component::plugin_serialize($type, $v);
			admin\class_component::assign_get($varname, $v);
		}
	}
	unset($_GET['_components']);
}

function showsetting($setname, $varname, $value, $type = 'radio', $disabled = '', $hidden = 0, $comment = '', $extra = '', $setid = '', $nofaq = false, $inbox = 0, $widemode = false, $norelatedlink = false) {

	global $_G;
	$s = "\n";
	$check = [];
	$noborder = false;
	if(is_array($disabled)) {
		$hidden = $disabled['hidden'];
		$comment = $disabled['comment'];
		$extra = $disabled['extra'];
		$setid = $disabled['setid'];
		$nofaq = $disabled['nofaq'];
		$inbox = $disabled['inbox'];
		$disabled = $disabled['disabled'];
	}
	if(str_starts_with($disabled, 'noborder')) {
		$disabled = trim(substr($disabled, 8));
		$noborder = 'class="noborder" ';
	}
	$check['disabled'] = $disabled ? ($disabled == 'readonly' ? ' readonly' : ' disabled') : '';
	$check['disabledaltstyle'] = $disabled ? ', 1' : '';

	$nocomment = false;

	if(isset($_G['showsetting_multi'])) {
		$hidden = 0;
		if(is_array($varname)) {
			$varnameid = '_'.str_replace(['[', ']'], '_', $varname[0]).'|'.$_G['showsetting_multi'];
			$varname[0] = preg_replace('/\w+new/', 'multinew['.$_G['showsetting_multi'].'][\\0]', $varname[0]);
		} else {
			$varnameid = '_'.str_replace(['[', ']'], '_', $varname).'|'.$_G['showsetting_multi'];
			$varname = preg_replace('/\w+new/', 'multinew['.$_G['showsetting_multi'].'][\\0]', $varname);
		}
	} else {
		$varnameid = '';
	}

	if($type == 'radio') {
		$value ? $check['true'] = 'checked' : $check['false'] = 'checked';
		$value ? $check['false'] = '' : $check['true'] = '';
		$check['hidden1'] = $hidden ? ' onclick="$(\'hidden_'.$setname.'\').style.display = \'\';"' : '';
		$check['hidden0'] = $hidden ? ' onclick="$(\'hidden_'.$setname.'\').style.display = \'none\';"' : '';
		$onclick = $disabled && $disabled == 'readonly' ? ' onclick="return false"' : ($extra ? $extra : '');
		$s .= '<ul onmouseover="altStyle(this'.$check['disabledaltstyle'].');">'.
			'<li'.($check['true'] ? ' class="checked"' : '').'><input class="radio" type="radio"'.($varnameid ? ' id="_v1_'.$varnameid.'"' : '').' name="'.$varname.'" value="1" '.$check['true'].$check['hidden1'].$check['disabled'].$onclick.'>&nbsp;'.cplang('yes').'</li>'.
			'<li'.($check['false'] ? ' class="checked"' : '').'><input class="radio" type="radio"'.($varnameid ? ' id="_v0_'.$varnameid.'"' : '').' name="'.$varname.'" value="0" '.$check['false'].$check['hidden0'].$check['disabled'].$onclick.'>&nbsp;'.cplang('no').'</li>'.
			'</ul>';
	} elseif($type == 'text' || $type == 'password' || $type == 'number') {
		$s .= '<input name="'.$varname.'" value="'.dhtmlspecialchars($value).'" type="'.$type.'" class="txt" '.$check['disabled'].' '.$extra.' />';
	} elseif($type == 'htmltext') {
		$id = 'html'.random(2);
		$s .= '<div id="'.$id.'">'.$value.'</div><input id="'.$id.'_v" name="'.$varname.'" value="'.dhtmlspecialchars($value).'" type="hidden" /><script type="text/javascript">sethtml(\''.$id.'\')</script>';
	} elseif($type == 'file') {
		$s .= '<input name="'.$varname.'" value="" type="file" class="txt uploadbtn marginbot" '.$check['disabled'].' '.$extra.' />';
	} elseif($type == 'filetext') {
		$defaulttype = $value ? 1 : 0;
		$id = 'file'.random(2);
		$s .= '<input id="'.$id.'_0" style="display:'.($defaulttype ? 'none' : '').'" name="'.($defaulttype ? 'TMP' : '').$varname.'" value="" type="file" class="txt uploadbtn marginbot" '.$check['disabled'].' '.$extra.' />'.
			'<input id="'.$id.'_1" style="display:'.(!$defaulttype ? 'none' : '').'" name="'.(!$defaulttype ? 'TMP' : '').$varname.'" value="'.dhtmlspecialchars($value).'" type="text" class="txt marginbot" '.$extra.' /><br />'.
			'<a id="'.$id.'_0a" style="'.(!$defaulttype ? 'font-weight:bold' : '').'" href="javascript:;" onclick="$(\''.$id.'_1a\').style.fontWeight = \'\';this.style.fontWeight = \'bold\';$(\''.$id.'_1\').name = \'TMP'.$varname.'\';$(\''.$id.'_0\').name = \''.$varname.'\';$(\''.$id.'_0\').style.display = \'\';$(\''.$id.'_1\').style.display = \'none\'">'.cplang('switch_upload').'</a>&nbsp;'.
			'<a id="'.$id.'_1a" style="'.($defaulttype ? 'font-weight:bold' : '').'" href="javascript:;" onclick="$(\''.$id.'_0a\').style.fontWeight = \'\';this.style.fontWeight = \'bold\';$(\''.$id.'_0\').name = \'TMP'.$varname.'\';$(\''.$id.'_1\').name = \''.$varname.'\';$(\''.$id.'_1\').style.display = \'\';$(\''.$id.'_0\').style.display = \'none\'">'.cplang('switch_url').'</a>';
	} elseif($type == 'textarea') {
		$readonly = $disabled ? 'readonly' : '';
		$s .= "<textarea $readonly rows=\"6\" ".(!isset($_G['showsetting_multi']) ? "ondblclick=\"textareasize(this, 1)\"" : '')." onkeyup=\"textareasize(this, 0)\" onkeydown=\"textareakey(this, event)\" name=\"$varname\" id=\"$varname\" cols=\"50\" class=\"tarea\" $extra>".dhtmlspecialchars($value).'</textarea>';
	} elseif($type == 'select' || $type == 'mselect' || str_starts_with($type, 'mselect_')) {
		if($type == 'mselect') {
			$extra .= ' multiple="multiple" size="10"';
			$value = (array)$value;
		} elseif(str_starts_with($type, 'mselect_')) {
			$size = substr($type, 8);
			$extra .= ' multiple="multiple" size="'.$size.'"';
			$value = (array)$value;
		}
		$s .= '<select name="'.$varname[0].'" '.$extra.'>';
		foreach($varname[1] as $option) {
			if(!array_key_exists(0, $option)) {
				$option = array_values($option);
			}
			if(is_array($value)) {
				$selected = in_array($option[0], $value) ? 'selected="selected"' : '';
			} else {
				$selected = $option[0] == $value ? 'selected="selected"' : '';
			}
			if(empty($option[2])) {
				$s .= "<option value=\"$option[0]\" $selected>".$option[1]."</option>\n";
			} else {
				$s .= "<optgroup label=\"".$option[1]."\"></optgroup>\n";
			}
		}
		$s .= '</select>';
	} elseif($type == 'mradio' || $type == 'mradio2') {
		$nocomment = $type == 'mradio2' && !isset($_G['showsetting_multi']);
		$addstyle = $nocomment ? ' style="float: left; width: 18%"' : '';
		$ulstyle = $nocomment ? ' style="width: 900px"' : '';
		if(is_array($varname)) {
			$radiocheck = [$value => ' checked'];
			$s .= '<ul'.(empty($varname[2]) ? ' class="nofloat"' : '').' onmouseover="altStyle(this'.$check['disabledaltstyle'].');"'.$ulstyle.'>';
			foreach($varname[1] as $varary) {
				if(is_array($varary) && !empty($varary)) {
					if(!array_key_exists(0, $varary)) {
						$varary = array_values($varary);
					}
					$onclick = '';
					if(!isset($_G['showsetting_multi']) && !empty($varary[2])) {
						foreach($varary[2] as $ctrlid => $display) {
							$onclick .= '$(\''.$ctrlid.'\').style.display = \''.$display.'\';';
						}
					}
					$onclick && $onclick = ' onclick="'.$onclick.'"';
					$s .= '<li'.($radiocheck[$varary[0]] ? ' class="checked"' : '').$addstyle.'><input class="radio" type="radio"'.($varnameid ? ' id="_v'.md5($varary[0]).'_'.$varnameid.'"' : '').' name="'.$varname[0].'" value="'.$varary[0].'"'.$radiocheck[$varary[0]].$check['disabled'].$onclick.'>&nbsp;'.$varary[1].'</li>';
				} else {
					$s .= '<li>'.$varary.'</li>';
				}
			}
			$s .= '</ul>';
		}
	} elseif($type == 'mcheckbox' || $type == 'mcheckbox2') {
		$nocomment = $type != 'mcheckbox2' && count($varname[1]) > 3 && !isset($_G['showsetting_multi']);
		$addstyle = $nocomment ? ' style="float: left;'.(empty($_G['showsetting_multirow']) ? ' width: 18%;overflow: hidden;' : '').'"' : '';
		$ulstyle = $nocomment && empty($_G['showsetting_multirow']) ? ' style="width: 900px"' : '';
		$s .= '<ul class="nofloat" onmouseover="altStyle(this'.$check['disabledaltstyle'].');"'.$ulstyle.'>';
		foreach($varname[1] as $varary) {
			if(is_array($varary) && !empty($varary)) {
				if(!array_key_exists(0, $varary)) {
					$varary = array_values($varary);
				}
				$onclick = !isset($_G['showsetting_multi']) && !empty($varary[2]) ? ' onclick="$(\''.$varary[2].'\').style.display = $(\''.$varary[2].'\').style.display == \'none\' ? \'\' : \'none\';"' : '';
				$checked = is_array($value) && in_array($varary[0], $value) ? ' checked' : '';
				$s .= '<li'.($checked ? ' class="checked"' : '').$addstyle.' title="'.dhtmlspecialchars($varary[1]).'"><input class="checkbox" type="checkbox"'.($varnameid ? ' id="_v'.md5($varary[0]).'_'.$varnameid.'"' : '').' name="'.$varname[0].'[]" value="'.$varary[0].'"'.$checked.$check['disabled'].$onclick.'>&nbsp;'.$varary[1].'</li>';
			}
		}
		$s .= '</ul>';
	} elseif($type == 'binmcheckbox') {
		$checkboxs = count($varname[1]);
		$value = sprintf('%0'.$checkboxs.'b', $value);
		$i = 1;
		$s .= '<ul class="nofloat" onmouseover="altStyle(this'.$check['disabledaltstyle'].');">';
		foreach($varname[1] as $key => $var) {
			if($var !== false) {
				$s .= '<li'.($value[$checkboxs - $i] ? ' class="checked"' : '').'><input class="checkbox" type="checkbox"'.($varnameid ? ' id="_v'.md5($i).'_'.$varnameid.'"' : '').' name="'.$varname[0].'['.$i.']" value="1"'.($value[$checkboxs - $i] ? ' checked' : '').' '.(!empty($varname[2][$key]) ? $varname[2][$key] : '').'>&nbsp;'.$var.'</li>';
			}
			$i++;
		}
		$s .= '</ul>';
	} elseif($type == 'omcheckbox') {
		$nocomment = count($varname[1]) > 3;
		$addstyle = $nocomment ? 'style="float: left; width: 18%"' : '';
		$ulstyle = $nocomment ? 'style="width: 900px"' : '';
		$s .= '<ul onmouseover="altStyle(this'.$check['disabledaltstyle'].');"'.(empty($varname[2]) ? ' class="nofloat"' : 'class="ckbox"').' '.$ulstyle.'>';
		foreach($varname[1] as $varary) {
			if(is_array($varary) && !empty($varary)) {
				$checked = is_array($value) && $value[$varary[0]] ? ' checked' : '';
				$s .= '<li'.($checked ? ' class="checked"' : '').' '.$addstyle.'><input class="checkbox" type="checkbox" name="'.$varname[0].'['.$varary[0].']" value="'.$varary[2].'"'.$checked.$check['disabled'].'>&nbsp;'.$varary[1].'</li>';
			}
		}
		$s .= '</ul>';
	} elseif($type == 'mselect') {
		$s .= '<select name="'.$varname[0].'" multiple="multiple" size="10" '.$extra.'>';
		foreach($varname[1] as $option) {
			if(!array_key_exists(0, $option)) {
				$option = array_values($option);
			}
			$selected = is_array($value) && in_array($option[0], $value) ? 'selected="selected"' : '';
			if(empty($option[2])) {
				$s .= "<option value=\"$option[0]\" $selected>".$option[1]."</option>\n";
			} else {
				if(is_array($option[2])) {
					$s .= "<optgroup label=\"".$option[1]."\">\n";
					foreach($option[2] as $groupoption) {
						$selected = is_array($value) && in_array($groupoption[0], $value) ? 'selected="selected"' : '';
						$s .= "<option value=\"$groupoption[0]\" $selected>".$groupoption[1]."</option>\n";
					}
					$s .= "</optgroup>\n";
				} else {
					$s .= "<optgroup label=\"".$option[1]."\"></optgroup>\n";
				}
			}
		}
		$s .= '</select>';
	} elseif($type == 'color') {
		global $stylestuff;
		$preview_varname = str_replace('[', '_', str_replace(']', '', $varname));
		$code = explode(' ', $value);
		$css = '';
		for($i = 0; $i <= 1; $i++) {
			if($code[$i] != '') {
				if($code[$i][0] == '#') {
					$css .= strtoupper($code[$i]).' ';
				} elseif(preg_match('/^(https?:)?\/\//i', $code[$i])) {
					$css .= 'url(\''.$code[$i].'\') ';
				} else {
					$css .= 'url(\''.$stylestuff['imgdir']['subst'].'/'.$code[$i].'\') ';
				}
			}
		}
		$background = trim($css);
		$colorid = ++$GLOBALS['coloridcount'];
		$s .= "<input id=\"c{$colorid}_v\" type=\"text\" class=\"txt\" style=\"float:left; width:210px;\" value=\"$value\" name=\"$varname\" onchange=\"updatecolorpreview('c{$colorid}')\">\n".
			"<input id=\"c$colorid\" onclick=\"c{$colorid}_frame.location='static/image/admincp/getcolor.htm?c{$colorid}|c{$colorid}_v';showMenu({'ctrlid':'c$colorid'})\" type=\"button\" class=\"colorwd\" value=\"\" style=\"background: $background\"><span id=\"c{$colorid}_menu\" style=\"display: none\"><iframe name=\"c{$colorid}_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>\n$extra";
	} elseif($type == 'calendar') {
		$s .= "<input type=\"text\" class=\"txt\" name=\"$varname\" value=\"".dhtmlspecialchars($value)."\" onclick=\"showcalendar(event, this".($extra ? ', 1' : '').")\">\n";
	} elseif(in_array($type, ['multiply', 'range', 'daterange'])) {
		$onclick = $type == 'daterange' ? ' onclick="showcalendar(event, this'.($extra ? ', 1' : '').')"' : '';
		if(isset($_G['showsetting_multi'])) {
			$varname[1] = preg_replace('/\w+new/', 'multinew['.$_G['showsetting_multi'].'][\\0]', $varname[1]);
		}
		$s .= "<input type=\"text\" class=\"txt\" name=\"$varname[0]\" value=\"".dhtmlspecialchars($value[0])."\" style=\"width: 108px; margin-right: 5px;\"$onclick>".($type == 'multiply' ? ' X ' : ' -- ')."<input type=\"text\" class=\"txt\" name=\"$varname[1]\" value=\"".dhtmlspecialchars($value[1])."\"class=\"txt\" style=\"width: 108px; margin-left: 5px;\"$onclick>";
	} else {
		$s .= $type;
	}
	$name = cplang($setname);
	$name .= $name && !str_ends_with($name, ':') ? ':' : '';
	$name = $disabled ? '<span class="lightfont">'.$name.'</span>' : $name;
	$commenttext = $comment ? $comment : cplang($setname.'_comment', false);
	!$norelatedlink && $name .= getrelatedlink($name.' '.$commenttext);
	$setid = !$setid ? substr(md5($setname), 0, 4) : $setid;
	$setid = isset($_G['showsetting_multi']) ? 'S'.$setid : $setid;
	if(!empty($_G['showsetting_multirow'])) {
		if(empty($_G['showsetting_multirow_n'])) {
			echo '<tr>';
		}
		echo '<td class="vtop rowform"><p class="td27m">'.$name.'</p>'.$s.'</td>';
		$_G['showsetting_multirow_n']++;
		if($_G['showsetting_multirow_n'] == 2) {
			echo '</tr>';
			$_G['showsetting_multirow_n'] = 0;
		}
		return;
	}
	if(!isset($_G['showsetting_multi'])) {
		if($inbox) {
			echo '<div>'.$name.'</div>';
		} else {
			showtablerow('', 'colspan="2" class="td27" s="1"', $name);
		}
	} else {
		if(empty($_G['showsetting_multijs'])) {
			$_G['setting_JS'] .= 'var ss = new Array();';
			$_G['showsetting_multijs'] = 1;
		}
		/*
		if($_G['showsetting_multi'] == 0) {
			showtablerow('', array('class="td27"'), array('<div id="D'.$setid.'"></div>'));
			$_G['setting_JS'] .= 'ss[\'D'.$setid.'\'] = new Array();';
		}
		$name = preg_replace("/\r\n|\n|\r/", '\n', addcslashes($name, "'\\"));
		$_G['setting_JS'] .= 'ss[\'D'.$setid.'\'] += \'<div class="multicol">'.$name.'</div>\';';
		*/
	}
	if(!$nocomment && ($type != 'omcheckbox' || $varname[2] != 'isfloat')) {
		if(!isset($_G['showsetting_multi'])) {
			if($inbox) {
				echo '<div>'.$s.'</div><div>'.$commenttext.($type == 'textarea' ? '<br />'.cplang('tips_textarea') : '').
					($disabled ? '<br /><span class="smalltxt" style="color:#F00">'.cplang($setname.'_disabled', false).'</span>' : NULL).'</div>';
			} elseif(!$widemode) {
				showtablerow('class="noborder"', ['class="vtop rowform"', 'class="vtop tips2" s="1"'], [
					$s,
					$commenttext.($type == 'textarea' ? '<br />'.cplang('tips_textarea') : '').
					($disabled ? '<br /><span class="smalltxt" style="color:#F00">'.cplang($setname.'_disabled', false).'</span>' : NULL)
				]);
			} else {
				showtablerow('class="noborder"', ['class="vtop rowform" colspan="2"'], [$s]);
			}
		} else {
			if($_G['showsetting_multi'] == 0) {
				$name = preg_replace("/\r\n|\n|\r/", '\n', addcslashes($name, "'\\"));
				showtablerow('class="noborder"', ['class="multileft"', 'class="vtop rowform" style="width:auto"'], [
					$name, '<div id="'.$setid.'"></div>'
				]);
				$_G['setting_JS'] .= 'ss[\''.$setid.'\'] = new Array();';
			}
			$s = preg_replace("/\r\n|\n|\r/", '\n', addcslashes($s, "'\\"));
			$_G['setting_JS'] .= 'ss[\''.$setid.'\'] += \'<div class="multicol">'.$s.'</div>\';';
		}
	} else {
		showtablerow('class="noborder"', ['colspan="2" class="vtop rowform"'], [$s]);
	}

	if($hidden) {
		showtagheader('tbody', 'hidden_'.$setname, $value, 'sub');
	}

}

function getrelatedlink($relatedtext) {
	static $relatedlang = null;
	if($relatedlang === null) {
		$relatedlang = lang('admincp_related');
	}
	if(!$relatedlang) {
		return '';
	}
	$relatedkeyword = '';
	foreach($relatedlang as $key) {
		if(str_contains($relatedtext, $key)) {
			$relatedkeyword = $key;
		}
	}
	if(!$relatedkeyword) {
		return '';
	}
	if(str_contains($relatedkeyword, ' ')) {
		$relatedkeyword = '('.$relatedkeyword.')';
	}
	return '<a href="'.ADMINSCRIPT.'?action=search&keywords='.$relatedkeyword.'&searchsubmit=yes" class="related" target="_blank" title="'.cplang('admincp_related_search').'"></a>';
}

function showmulti() {
	global $_G;
	$_G['setting_JS'] .= <<<EOF
	for(i in ss) {
		$(i).innerHTML=ss[i];
	}
EOF;
}

function mradio($name, $items = [], $checked = '', $float = TRUE) {
	$list = '<ul'.($float ? '' : ' class="nofloat"').' onmouseover="altStyle(this);">';
	if(is_array($items)) {
		foreach($items as $value => $item) {
			$list .= '<li'.($checked == $value ? ' class="checked"' : '').'><input type="radio" name="'.$name.'" value="'.$value.'" class="radio"'.($checked == $value ? ' checked="checked"' : '').' /> '.$item.'</li>';
		}
	}
	$list .= '</ul>';
	return $list;
}

function mcheckbox($name, $items = [], $checked = []) {
	$list = '<ul class="dblist" onmouseover="altStyle(this);">';
	if(is_array($items)) {
		foreach($items as $value => $item) {
			$list .= '<li'.(empty($checked) || in_array($value, $checked) ? ' class="checked"' : '').'><input type="checkbox" name="'.$name.'[]" value="'.$value.'" class="checkbox"'.(empty($checked) || in_array($value, $checked) ? ' checked="checked"' : '').' /> '.$item.'</li>';
		}
	}
	$list .= '</ul>';
	return $list;
}

function showsubmit($name = '', $value = 'submit', $before = '', $after = '', $floatright = '', $entersubmit = true, $extra = '') {
	global $_G;
	if(!empty($_G['showsetting_multi'])) {
		return;
	}
	if(!empty($_G['showcomponent'])) {
		showhiddenfields(['_components' => json_encode($_G['showcomponent'])]);
	}
	$random = random(4);
	$str = '<tr'.($extra ? " $extra" : '').'>';
	$str .= $name && in_array($before, ['del', 'select_all', 'td']) ? '<td class="td25">'.($before != 'td' ? '<input type="checkbox" name="chkall" id="chkall'.($chkkallid = random(4)).'" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'delete\')" /><label for="chkall'.$chkkallid.'">'.cplang($before).'</label>' : '').'</td>' : '';
	$str .= '<td colspan="15">';
	$str .= $floatright ? '<div class="cuspages right">'.$floatright.'</div>' : '';
	$str .= '<div class="fixsel">';
	$str .= $before && !in_array($before, ['del', 'select_all', 'td']) ? $before.' &nbsp;' : '';
	$str .= $name ? '<input type="submit" class="btn" id="submit_'.$name.'_'.$random.'" name="'.$name.'" title="'.($entersubmit ? cplang('submit_tips') : '').'" value="'.cplang($value).'" />' : '';
	$after = $after == 'more_options' ? '<input class="checkbox" type="checkbox" value="1" onclick="$(\'advanceoption\').style.display = $(\'advanceoption\').style.display == \'none\' ? \'\' : \'none\'; this.value = this.value == 1 ? 0 : 1; this.checked = this.value == 1 ? false : true" id="btn_more" /><label for="btn_more">'.cplang('more_options').'</label>' : $after;
	$str = $after ? $str.(($before && $before != 'del') || $name ? ' &nbsp;' : '').$after : $str;
	$str .= '</div></td>';
	$str .= '</tr>';
	echo $str.($name && $entersubmit ? '<script type="text/JavaScript">_attachEvent(document.documentElement, \'keydown\', function (e) { entersubmit(e, \''.$name.'_'.$random.'\'); });</script>' : '');
}

function showtagfooter($tagname) {
	global $_G;
	if(!empty($_G['showsetting_multi'])) {
		return;
	}
	echo '</'.$tagname.'>';
}

function showtablefooter() {
	global $_G;
	if(!empty($_G['showsetting_multi'])) {
		return;
	}
	echo '</table>'."\n";
}

function showboxfooter($nobody = 0) {
	global $_G;
	if(!empty($_G['showsetting_multi'])) {
		return;
	}
	echo $nobody ? '</div>'."\n" : '</div></div>'."\n";
}

function showformfooter() {
	global $_G;
	if(!empty($_G['setting_JS'])) {
		echo '<script type="text/JavaScript">'.$_G['setting_JS'].'</script>';
	}

	updatesession();

	echo '</form>'."\n";
	if($scrolltop = intval(getgpc('scrolltop'))) {
		echo '<script type="text/JavaScript">_attachEvent(window, \'load\', function () { scroll(0,'.$scrolltop.') }, document);</script>';
	}
}

function cpfooter() {
	global $_G, $admincp;
	if(defined('FOOTERDISABLED')) {
		exit;
	}

	require_once DISCUZ_ROOT.'./source/discuz_version.php';
	$version = DISCUZ_VERSION;
	$charset = CHARSET;

	echo "\n</div>";
	if(!empty($_GET['highlight'])) {
		$kws = explode(' ', $_GET['highlight']);
		echo '<script type="text/JavaScript">';
		foreach($kws as $kw) {
			$kw = addslashes($kw);
			echo 'parsetag(\''.dhtmlspecialchars($kw, ENT_QUOTES).'\');';
		}
		echo '</script>';
	}

	if(defined('DISCUZ_DEBUG') && DISCUZ_DEBUG && @include_once(libfile('function/debug'))) {
		function_exists('debugmessage') && debugmessage();
	}

	echo "\n</body>\n</html>";

}

if(!function_exists('ajaxshowheader')) {
	function ajaxshowheader() {
		global $_G;
		ob_end_clean();
		@header('Expires: -1');
		@header('Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0', FALSE);
		@header('Pragma: no-cache');
		header('Content-type: application/xml');
		echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<root><![CDATA[";
	}
}

if(!function_exists('ajaxshowfooter')) {
	function ajaxshowfooter() {
		echo ']]></root>';
		exit();
	}
}

function showimportdata() {
	showsetting('import_type', ['importtype', [
		['file', cplang('import_type_file'), ['importfile' => '', 'importtxt' => 'none']],
		['txt', cplang('import_type_txt'), ['importfile' => 'none', 'importtxt' => '']]
	]], 'file', 'mradio');
	showtagheader('tbody', 'importfile', TRUE);
	showsetting('import_file', 'importfile', '', 'file');
	showtagfooter('tbody');
	showtagheader('tbody', 'importtxt');
	showsetting('import_txt', 'importtxt', '', 'textarea');
	showtagfooter('tbody');
}

function getimportdata($name = '', $addslashes = 0, $ignoreerror = 0) {
	global $_G;
	if($_GET['importtype'] == 'file') {
		$data = @implode('', file($_FILES['importfile']['tmp_name']));
		@unlink($_FILES['importfile']['tmp_name']);
	} else {
		if(!empty($_GET['importtxt'])) {
			$data = $_GET['importtxt'];
		} else {
			$data = $GLOBALS['importtxt'];
		}
	}
	$xmldata = [];
	if(str_starts_with($data, '{') && json_decode($data, true)) {
		$xmldata = json_decode($data, true);
	}
	if(empty($xmldata)) {
		require_once libfile('class/xml');
		$xmldata = xml2array($data);
	}
	if(!is_array($xmldata) || !$xmldata) {
		if(!$ignoreerror) {
			cpmsg(cplang('import_data_invalid').cplang($data), '', 'error');
		} else {
			return [];
		}
	} else {
		if($name && $name != $xmldata['Title']) {
			if(!$ignoreerror) {
				cpmsg(cplang('import_data_typeinvalid').cplang($data), '', 'error');
			} else {
				return [];
			}
		}
		$data = exportarray($xmldata['Data'], 0);
	}
	if($addslashes) {
		$data = daddslashes($data, 1);
	}

	if($name == 'Discuz! Plugin') {
		if(file_exists($langfile = DISCUZ_PLUGIN($data['plugin']['identifier']).'/i18n/'.currentlang().'/lang_plugin.php')) {
			$scriptlang = $templatelang = $systemlang = $installlang = [];
			require $langfile;
			if(!empty($scriptlang[$data['plugin']['identifier']])) {
				$data['language']['scriptlang'] = $scriptlang[$data['plugin']['identifier']];
			}
			if(!empty($templatelang[$data['plugin']['identifier']])) {
				$data['language']['templatelang'] = $templatelang[$data['plugin']['identifier']];
			}
			if(!empty($systemlang[$data['plugin']['identifier']])) {
				$data['language']['systemlang'] = $systemlang[$data['plugin']['identifier']];
			}
			if(!empty($installlang[$data['plugin']['identifier']])) {
				$data['language']['installlang'] = $installlang[$data['plugin']['identifier']];
				$_lang = &$data['language']['installlang'];
				if(!empty($_lang[$data['plugin']['name']])) {
					$data['plugin']['name'] = $_lang[$data['plugin']['name']];
				}
				if(!empty($_lang[$data['plugin']['description']])) {
					$data['plugin']['description'] = $_lang[$data['plugin']['description']];
				}
				if(!empty($_lang[$data['plugin']['copyright']])) {
					$data['plugin']['copyright'] = $_lang[$data['plugin']['copyright']];
				}
				if(!empty($_lang[$data['plugin']['version']])) {
					$data['plugin']['version'] = $_lang[$data['plugin']['version']];
				}
				if(!empty($_lang[$data['license']])) {
					$data['license'] = $_lang[$data['license']];
				}
				if(!empty($_lang[$data['intro']])) {
					$data['intro'] = $_lang[$data['intro']];
				}
				if(!empty($data['var'])) {
					foreach($data['var'] as $k => $v) {
						if(!empty($_lang[$v['title']])) {
							$data['var'][$k]['title'] = $_lang[$v['title']];
						}
						if(!empty($_lang[$v['description']])) {
							$data['var'][$k]['description'] = $_lang[$v['description']];
						}
						if(!empty($_lang[$v['extra']])) {
							$data['var'][$k]['extra'] = $_lang[$v['extra']];
						}
					}
				}
				if(!empty($data['plugin']['modules'])) {
					$modules = dunserialize($data['plugin']['modules']);
					foreach($modules as $k => $v) {
						if(!empty($v['menu']) && !empty($_lang[$v['menu']])) {
							$modules[$k]['menu'] = $_lang[$v['menu']];
						}
					}
					$data['plugin']['modules'] = serialize($modules);
				}
			}
		}
	} elseif($name == 'Discuz! Style') {
		if(file_exists($langfile = DISCUZ_TEMPLATE($data['directory']).'/i18n/'.currentlang().'/lang_template.php')) {
			$lang = [];
			require $langfile;
			if(!empty($lang[$data['name']])) {
				$data['name'] = $lang[$data['name']];
			}
			if(!empty($lang[$data['tplname']])) {
				$data['tplname'] = $lang[$data['tplname']];
			}
			if(!empty($lang[$data['copyright']])) {
				$data['copyright'] = $lang[$data['copyright']];
			}
			if(!empty($lang[$data['style']['version']])) {
				$data['style']['version'] = $lang[$data['style']['version']];
			}
			if(!empty($data['var'])) {
				foreach($data['var'] as $k => $v) {
					if(!empty($lang[$v['title']])) {
						$data['var'][$k]['title'] = $lang[$v['title']];
					}
					if(!empty($lang[$v['description']])) {
						$data['var'][$k]['description'] = $lang[$v['description']];
					}
					if(!empty($lang[$v['extra']])) {
						$data['var'][$k]['extra'] = $lang[$v['extra']];
					}
				}
			}
		}
	}
	return $data;
}

function exportdata($name, $filename, $data, $return = false) {
	global $_G;
	$root = [
		'Title' => $name,
		'Version' => $_G['setting']['version'],
		'Time' => dgmdate(TIMESTAMP, 'Y-m-d H:i'),
		'From' => $_G['setting']['bbname'],
		'Data' => exportarray($data, 1)
	];
	$plugin_export = json_encode($root, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	if($return) {
		return $plugin_export;
	}
	$filename = strtolower(str_replace(['!', ' '], ['', '_'], $name)).'_'.$filename.'.json';
	ob_end_clean();
	dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	dheader('Cache-Control: no-cache, must-revalidate');
	dheader('Pragma: no-cache');
	dheader('Content-Encoding: none');
	dheader('Content-Length: '.strlen($plugin_export));
	dheader('Content-Disposition: attachment; filename='.$filename);
	dheader('Content-Type: text/json');
	echo $plugin_export;
	define('FOOTERDISABLED', 1);
	exit();
}

function exportxmldata($name, $filename, $data) {
	global $_G;
	require_once libfile('class/xml');
	$root = array(
		'Title' => $name,
		'Version' => $_G['setting']['version'],
		'Time' => dgmdate(TIMESTAMP, 'Y-m-d H:i'),
		'From' => $_G['setting']['bbname'].' ('.$_G['siteurl'].')',
		'Data' => exportarray($data, 1)
	);
	$filename = strtolower(str_replace(array('!', ' '), array('', '_'), $name)).'_'.$filename.'.xml';
	$plugin_export = array2xml($root, 1);
	ob_end_clean();
	dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	dheader('Cache-Control: no-cache, must-revalidate');
	dheader('Pragma: no-cache');
	dheader('Content-Encoding: none');
	dheader('Content-Length: '.strlen($plugin_export));
	dheader('Content-Disposition: attachment; filename='.$filename);
	dheader('Content-Type: text/xml');
	echo $plugin_export;
	define('FOOTERDISABLED' , 1);
	exit();
}

function exportarray($array, $method) {
	$tmp = $array;
	if($method) {
		foreach($array as $k => $v) {
			if(is_array($v)) {
				$tmp[$k] = exportarray($v, 1);
			} else {
				$uv = dunserialize($v);
				if($uv && is_array($uv)) {
					$tmp['__'.$k] = exportarray($uv, 1);
					unset($tmp[$k]);
				} else {
					$tmp[$k] = $v;
				}
			}
		}
	} else {
		foreach($array as $k => $v) {
			if(is_array($v)) {
				if(str_starts_with($k, '__')) {
					$tmp[substr($k, 2)] = serialize(exportarray($v, 0));
					unset($tmp[$k]);
				} else {
					$tmp[$k] = exportarray($v, 0);
				}
			} else {
				$tmp[$k] = $v;
			}
		}
	}
	return $tmp;
}

function getwheres($intkeys, $strkeys, $randkeys, $likekeys, $pre = '') {

	$wherearr = [];
	$urls = [];

	foreach($intkeys as $var) {
		$value = $_GET[$var] ?? '';
		if(strlen($value)) {
			$urls[] = "$var=$value";
			$var = addslashes($var);
			$wherearr[] = "{$pre}{$var}='".intval($value)."'";
		}
	}

	foreach($strkeys as $var) {
		$value = isset($_GET[$var]) ? trim($_GET[$var]) : '';
		if(strlen($value)) {
			$urls[] = "$var=".rawurlencode($value);
			$var = addslashes($var);
			$value = addslashes($value);
			$wherearr[] = "{$pre}{$var}='$value'";
		}
	}

	foreach($randkeys as $vars) {
		$value1 = isset($_GET[$vars[1].'1']) ? $vars[0]($_GET[$vars[1].'1']) : '';
		$value2 = isset($_GET[$vars[1].'2']) ? $vars[0]($_GET[$vars[1].'2']) : '';
		if($value1) {
			$urls[] = "{$vars[1]}1=".rawurlencode($_GET[$vars[1].'1']);
			$vars[1] = addslashes($vars[1]);
			$value1 = addslashes($value1);
			$wherearr[] = "{$pre}{$vars[1]}>='$value1'";
		}
		if($value2) {
			$wherearr[] = "{$pre}{$vars[1]}<='$value2'";
			$vars[2] = addslashes($vars[2]);
			$value2 = addslashes($value2);
			$urls[] = "{$vars[1]}2=".rawurlencode($_GET[$vars[1].'2']);
		}
	}

	foreach($likekeys as $var) {
		$value = isset($_GET[$var]) ? stripsearchkey($_GET[$var]) : '';
		if(strlen($value) > 1) {
			$urls[] = "$var=".rawurlencode($_GET[$var]);
			$var = addslashes($var);
			$value = addslashes($value);
			$wherearr[] = "{$pre}{$var} LIKE BINARY '%$value%'";
		}
	}

	return ['wherearr' => $wherearr, 'urls' => $urls];
}

function getorders($alloworders, $default, $pre = '') {
	$orders = ['sql' => '', 'urls' => []];
	if(empty($_GET['orderby']) || !in_array($_GET['orderby'], $alloworders)) {
		$_GET['orderby'] = $default;
		if(empty($_GET['ordersc'])) $_GET['ordersc'] = 'desc';
	}

	$orders['sql'] = " ORDER BY {$pre}{$_GET['orderby']} ";
	$orders['urls'][] = "orderby={$_GET['orderby']}";

	if(!empty($_GET['ordersc']) && $_GET['ordersc'] == 'desc') {
		$orders['urls'][] = 'ordersc=desc';
		$orders['sql'] .= ' DESC ';
	} else {
		$orders['urls'][] = 'ordersc=asc';
	}
	return $orders;
}


function blog_replynum_stat($start, $perpage) {
	global $_G;

	$next = false;
	$updates = [];
	$query = table_home_blog::t()->range_blog($start, $perpage);
	foreach($query as $value) {
		$next = true;
		$count = table_home_comment::t()->count_by_id_idtype($value['blogid'], 'blogid');
		if($count != $value['replynum']) {
			$updates[$value['blogid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach($nums[0] as $count) {
		table_home_blog::t()->update($nums[1][$count], ['replynum' => $count]);
	}
	return $next;
}

function space_friendnum_stat($start, $perpage) {
	global $_G;

	$next = false;
	$updates = [];
	foreach(table_common_member_count::t()->range($start, $perpage) as $uid => $value) {
		$next = true;
		$count = table_home_friend::t()->count_by_uid($value['uid']);
		if($count != $value['friends']) {
			$updates[$value['uid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach($nums[0] as $count) {
		table_common_member_count::t()->update($nums[1][$count], ['friends' => $count]);
	}
	return $next;
}

function album_picnum_stat($start, $perpage) {
	global $_G;

	$next = false;
	$updates = [];
	$query = table_home_album::t()->range($start, $perpage);
	foreach($query as $value) {
		$next = true;
		$count = table_home_pic::t()->check_albumpic($value['albumid']);
		if($count != $value['picnum']) {
			$updates[$value['albumid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach($nums[0] as $count) {
		table_home_album::t()->update($nums[1][$count], ['picnum' => $count]);
	}
	return $next;
}

function tagitemnum_stat($start, $perpage) {
	global $_G;

	$next = false;
	$updates = [];
	$query = table_common_tag::t()->range($start, $perpage);
	foreach($query as $value) {
		$next = true;
		$count = table_common_tagitem::t()->count_by_tagid($value['tagid']);
		if($count != $value['related_count']) {
			$updates[$value['tagid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach($nums[0] as $count) {
		table_common_tag::t()->update($nums[1][$count], ['related_count' => $count]);
	}
	return $next;
}

function get_custommenu() {
	global $_G;
	$custommenu = [];
	foreach(table_common_admincp_cmenu::t()->fetch_all_by_uid($_G['uid']) as $custom) {
		$custom['url'] = 'misc&operation=custommenu&do=redirect&mid='.$custom['id'];
		$custommenu[] = [$custom['title'], $custom['url']];
	}
	return $custommenu;
}

function get_pluginsetting($type) {
	global $_G;

	loadcache('pluginsetting');
	$pluginvalue = [];
	$pluginsetting = $_G['cache']['pluginsetting'][$type] ?? [];

	$varids = [];
	foreach($pluginsetting as $v) {
		foreach($v['setting'] as $varid => $var) {
			$varids[] = $varid;
		}
	}
	if($varids) {
		foreach(table_common_pluginvar::t()->fetch_all($varids) as $plugin) {
			$values = (array)dunserialize($plugin['value']);
			foreach($values as $id => $value) {
				$pluginvalue[$id][$plugin['pluginvarid']] = $value;
			}
		}
	}

	return [$pluginsetting, $pluginvalue];
}

function set_pluginsetting($pluginvars) {
	foreach($pluginvars as $varid => $value) {
		$pluginvar = table_common_pluginvar::t()->fetch($varid);
		$valuenew = dunserialize($pluginvar['value']);
		$valuenew = is_array($valuenew) ? $valuenew : [];
		foreach($value as $k => $v) {
			$valuenew[$k] = $v;
		}
		table_common_pluginvar::t()->update($varid, ['value' => serialize($valuenew)]);
	}
	updatecache('plugin');
}

function get_stylesetting($type) {
	global $_G;

	loadcache('stylesetting');
	$stylevalue = [];
	$stylesetting = $_G['cache']['stylesetting'][$type] ?? [];

	$varids = [];
	foreach($stylesetting as $v) {
		foreach($v['setting'] as $varid => $var) {
			$varids[] = $varid;
		}
	}
	if($varids) {
		foreach(table_common_stylevar_extra::t()->fetch_all($varids) as $style) {
			$values = (array)dunserialize($style['value']);
			foreach($values as $id => $value) {
				$stylevalue[$id][$style['stylevarid']] = $value;
			}
		}
	}

	return [$stylesetting, $stylevalue];
}

function set_stylesetting($stylevars) {
	foreach($stylevars as $varid => $value) {
		$stylevar = table_common_stylevar_extra::t()->fetch($varid);
		$valuenew = dunserialize($stylevar['value']);
		$valuenew = is_array($valuenew) ? $valuenew : [];
		foreach($value as $k => $v) {
			$valuenew[$k] = $v;
		}
		table_common_stylevar_extra::t()->update($varid, ['value' => serialize($valuenew)]);
	}
	updatecache('styles');
}

function checkformulaperm($formula) {
	$formula = preg_replace('/(\{([0-9a-fA-F\.\-\:\/]+?)\})/', "'\\1'", $formula);
	return checkformulasyntax(
		$formula,
		['+', '-', '*', '/', '<', '<=', '==', '>=', '>', '!=', 'and', 'or'],
		['regdate', 'regday', 'regip', 'lastip', 'buyercredit', 'sellercredit', 'digestposts', 'posts', 'threads', 'oltime', 'extcredits[1-8]', 'field[\d]+'],
		'\'\{[0-9a-fA-F\.\-\:\/]+\}\''
	);
}

function getposttableselect_admin() {
	global $_G;

	loadcache('posttable_info');
	if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
		$posttableselect = '<select name="posttableid" id="posttableid" class="ps">';
		foreach($_G['cache']['posttable_info'] as $posttableid => $data) {
			$posttableselect .= '<option value="'.$posttableid.'"'.($_GET['posttableid'] == $posttableid ? ' selected="selected"' : '').'>'.($data['memo'] ? $data['memo'] : 'post_'.$posttableid).'</option>';
		}
		$posttableselect .= '</select>';
	} else {
		$posttableselect = '';
	}
	return $posttableselect;
}

function rewritedata($alldata = 1) {
	global $_G;
	$data = [];
	if(!$alldata) {
		if(is_array($_G['setting']['rewritestatus']) && in_array('portal_topic', $_G['setting']['rewritestatus'])) {
			$data['search']['portal_topic'] = '/'.$_G['domain']['pregxprw']['portal']."\?mod\=topic&(amp;)?topic\=([^#]+?)?\"([^\>]*)\>/";
			$data['replace']['portal_topic'] = 'rewriteoutput(\'portal_topic\', 0, $matches[1], $matches[3], $matches[4])';
		}

		if(is_array($_G['setting']['rewritestatus']) && in_array('portal_article', $_G['setting']['rewritestatus'])) {
			$data['search']['portal_article'] = '/'.$_G['domain']['pregxprw']['portal']."\?mod\=view&(amp;)?aid\=(\d+)(&amp;page\=(\d+))?\"([^\>]*)\>/";
			$data['replace']['portal_article'] = 'rewriteoutput(\'portal_article\', 0, $matches[1], $matches[3], $matches[5], $matches[6])';
		}

		if(is_array($_G['setting']['rewritestatus']) && in_array('forum_forumdisplay', $_G['setting']['rewritestatus'])) {
			$data['search']['forum_forumdisplay'] = '/'.$_G['domain']['pregxprw']['forum']."\?mod\=forumdisplay&(amp;)?fid\=(\w+)(&amp;page\=(\d+))?\"([^\>]*)\>/";
			$data['replace']['forum_forumdisplay'] = 'rewriteoutput(\'forum_forumdisplay\', 0, $matches[1], $matches[3], $matches[5], $matches[6])';
		}

		if(is_array($_G['setting']['rewritestatus']) && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
			$data['search']['forum_viewthread'] = '/'.$_G['domain']['pregxprw']['forum']."\?mod\=viewthread&(amp;)?tid\=(\d+)(&amp;extra\=(page\%3D(\d+))?)?(&amp;page\=(\d+))?\"([^\>]*)\>/";
			$data['replace']['forum_viewthread'] = 'rewriteoutput(\'forum_viewthread\', 0, $matches[1], $matches[3], $matches[8], $matches[6], $matches[9])';
		}

		if(is_array($_G['setting']['rewritestatus']) && in_array('group_group', $_G['setting']['rewritestatus'])) {
			$data['search']['group_group'] = '/'.$_G['domain']['pregxprw']['forum']."\?mod\=group&(amp;)?fid\=(\d+)(&amp;page\=(\d+))?\"([^\>]*)\>/";
			$data['replace']['group_group'] = 'rewriteoutput(\'group_group\', 0, $matches[1], $matches[3], $matches[5], $matches[6])';
		}

		if(is_array($_G['setting']['rewritestatus']) && in_array('home_space', $_G['setting']['rewritestatus'])) {
			$data['search']['home_space'] = '/'.$_G['domain']['pregxprw']['home']."\?mod=space&(amp;)?(uid\=(\d+)|username\=([^&]+?))\"([^\>]*)\>/";
			$data['replace']['home_space'] = 'rewriteoutput(\'home_space\', 0, $matches[1], $matches[4], $matches[5], $matches[6])';
		}

		if(is_array($_G['setting']['rewritestatus']) && in_array('home_blog', $_G['setting']['rewritestatus'])) {
			$data['search']['home_blog'] = '/'.$_G['domain']['pregxprw']['home']."\?mod=space&(amp;)?uid\=(\d+)&(amp;)?do=blog&(amp;)?id=(\d+)\"([^\>]*)\>/";
			$data['replace']['home_blog'] = 'rewriteoutput(\'home_blog\', 0, $matches[1], $matches[3], $matches[6], $matches[7])';
		}

		if(is_array($_G['setting']['rewritestatus']) && in_array('forum_archiver', $_G['setting']['rewritestatus'])) {
			$data['search']['forum_archiver'] = "/<a href\=\"\?(fid|tid)\-(\d+)\.html(&page\=(\d+))?\"([^\>]*)\>/";
			$data['replace']['forum_archiver'] = 'rewriteoutput(\'forum_archiver\', 0, $matches[1], $matches[2], $matches[4], $matches[5])';
		}

		if(is_array($_G['setting']['rewritestatus']) && in_array('plugin', $_G['setting']['rewritestatus'])) {
			$data['search']['plugin'] = "/<a href\=\"plugin\.php\?id=([a-z]+[a-z0-9_]*):([a-z0-9_\-]+)(&amp;|&)?(.*?)?\"([^\>]*)\>/";
			$data['replace']['plugin'] = 'rewriteoutput(\'plugin\', 0, $matches[1], $matches[2], $matches[3], $matches[4], $matches[5])';
		}
	} else {
		$data['rulesearch']['portal_topic'] = 'topic-{name}.html';
		$data['rulereplace']['portal_topic'] = 'portal.php?mod=topic&topic={name}';
		$data['rulevars']['portal_topic']['{name}'] = '(.+)';

		$data['rulesearch']['portal_article'] = 'article-{id}-{page}.html';
		$data['rulereplace']['portal_article'] = 'portal.php?mod=view&aid={id}&page={page}';
		$data['rulevars']['portal_article']['{id}'] = '([0-9]+)';
		$data['rulevars']['portal_article']['{page}'] = '([0-9]+)';

		$data['rulesearch']['forum_forumdisplay'] = 'forum-{fid}-{page}.html';
		$data['rulereplace']['forum_forumdisplay'] = 'forum.php?mod=forumdisplay&fid={fid}&page={page}';
		$data['rulevars']['forum_forumdisplay']['{fid}'] = '(\w+)';
		$data['rulevars']['forum_forumdisplay']['{page}'] = '([0-9]+)';

		$data['rulesearch']['forum_viewthread'] = 'thread-{tid}-{page}-{prevpage}.html';
		$data['rulereplace']['forum_viewthread'] = 'forum.php?mod=viewthread&tid={tid}&extra=page\%3D{prevpage}&page={page}';
		$data['rulevars']['forum_viewthread']['{tid}'] = '([0-9]+)';
		$data['rulevars']['forum_viewthread']['{page}'] = '([0-9]+)';
		$data['rulevars']['forum_viewthread']['{prevpage}'] = '([0-9]+)';

		$data['rulesearch']['group_group'] = 'group-{fid}-{page}.html';
		$data['rulereplace']['group_group'] = 'forum.php?mod=group&fid={fid}&page={page}';
		$data['rulevars']['group_group']['{fid}'] = '([0-9]+)';
		$data['rulevars']['group_group']['{page}'] = '([0-9]+)';

		$data['rulesearch']['home_space'] = 'space-{user}-{value}.html';
		$data['rulereplace']['home_space'] = 'home.php?mod=space&{user}={value}';
		$data['rulevars']['home_space']['{user}'] = '(username|uid)';
		$data['rulevars']['home_space']['{value}'] = '(.+)';

		$data['rulesearch']['home_blog'] = 'blog-{uid}-{blogid}.html';
		$data['rulereplace']['home_blog'] = 'home.php?mod=space&uid={uid}&do=blog&id={blogid}';
		$data['rulevars']['home_blog']['{uid}'] = '([0-9]+)';
		$data['rulevars']['home_blog']['{blogid}'] = '([0-9]+)';

		$data['rulesearch']['forum_archiver'] = '{action}-{value}.html';
		$data['rulereplace']['forum_archiver'] = 'index.php?action={action}&value={value}';
		$data['rulevars']['forum_archiver']['{action}'] = '(fid|tid)';
		$data['rulevars']['forum_archiver']['{value}'] = '([0-9]+)';

		$data['rulesearch']['plugin'] = '{pluginid}-{module}.html';
		$data['rulereplace']['plugin'] = 'plugin.php?id={pluginid}:{module}';
		$data['rulevars']['plugin']['{pluginid}'] = '([a-z]+[a-z0-9_]*)';
		$data['rulevars']['plugin']['{module}'] = '([a-z0-9_\-]+)';
	}
	return $data;
}

function siteftp_form($action) {
	showformheader($action);
	showtableheader('cloudaddons_ftp_setting');
	showsetting('setting_attach_remote_enabled_ssl', 'siteftp[ssl]', '', 'radio');
	showsetting('setting_attach_remote_ftp_host', 'siteftp[host]', '', 'text');
	showsetting('setting_attach_remote_ftp_port', 'siteftp[port]', '21', 'text');
	showsetting('setting_attach_remote_ftp_user', 'siteftp[username]', '', 'text');
	showsetting('setting_attach_remote_ftp_pass', 'siteftp[password]', '', 'text');
	showsetting('setting_attach_remote_ftp_pasv', 'siteftp[pasv]', 0, 'radio');
	showsetting('setting_attach_ftp_dir', 'siteftp[attachdir]', '', 'text');
	showsubmit('settingsubmit');
	showtablefooter();
	showformfooter();
}

function siteftp_check($siteftp, $dir) {
	global $_G;
	$siteftp['on'] = 1;
	$siteftp['password'] = authcode($siteftp['password'], 'ENCODE', md5($_G['config']['security']['authkey']));
	$ftp = &discuz_ftp::instance($siteftp);
	$ftp->connect();
	$ftp->upload(DISCUZ_ROOT.'./source/discuz_version.php', $dir.'/discuz_version.php');
	if($ftp->error()) {
		cpmsg('setting_ftp_remote_'.$ftp->error(), '', 'error');
	}
	if(!file_exists(DISCUZ_ROOT.'./'.$dir.'/discuz_version.php')) {
		cpmsg('cloudaddons_ftp_path_error', '', 'error');
	}
	$ftp->ftp_delete($dir.'/discuz_version.php');
	$_G['siteftp'] = $ftp;
}

function siteftp_upload($readfile, $writefile) {
	global $_G;
	if(!isset($_G['siteftp'])) {
		return;
	}
	$_G['siteftp']->upload($readfile, $writefile);
	if($_G['siteftp']->error()) {
		cpmsg('setting_ftp_remote_'.$_G['siteftp']->error(), '', 'error');
	}
}

function site_userinfo() {
	if($auth = getglobal('auth', 'cookie')) {
		$auth = daddslashes(explode("\t", authcode($auth, 'DECODE')));
	}
	list($discuz_pw, $discuz_uid) = empty($auth) || count($auth) < 2 ? ['', ''] : $auth;

	if($discuz_uid) {
		$user = getuserbyuid($discuz_uid, 1);
		if(!empty($user) && $user['password'] == $discuz_pw && $user['freeze'] != -2 && getstatus($user['allowadmincp'], 1)) {
			return $user;
		}
	}
	return [];
}

