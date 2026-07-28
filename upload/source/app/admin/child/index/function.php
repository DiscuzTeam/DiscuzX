<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

function show_user_bar() {
	global $_G;

	showsubmenu('home_welcome', [], isfounder() ? '<div id="user_bar">DIY</div>' : '', ['bbname' => $_G['setting']['bbname']]);
}

function show_todo() {
	global $_G;

	$membersmod = table_common_member_validate::t()->count_by_status(0);
	$threadsdel = $_G['setting']['forumstatus'] ? table_forum_thread::t()->count_by_displayorder(-1) : 0;
	$groupmod = $_G['setting']['forumstatus'] ? table_forum_forum::t()->validate_level_num() : 0;
	$reportcount = table_common_report::t()->fetch_count();

	$modcount = [];
	foreach(table_common_moderate::t()->count_group_idtype_by_status(0) as $value) {
		$modcount[$value['idtype']] = $value['count'];
	}

	$medalsmod = $_G['setting']['medalstatus'] ? table_forum_medallog::t()->count_by_type(2) : 0;
	$threadsmod = $modcount['tid'];
	$postsmod = $modcount['pid'];
	$blogsmod = $modcount['blogid'];
	$doingsmod = $modcount['doid'];
	$picturesmod = $modcount['picid'];
	$sharesmod = $modcount['sid'];
	$commentsmod = $modcount['uid_cid'] + $modcount['blogid_cid'] + $modcount['sid_cid'] + $modcount['picid_cid'];
	$articlesmod = $modcount['aid'];
	$articlecommentsmod = $modcount['aid_cid'];
	$topiccommentsmod = $modcount['topicid_cid'];
	$verify = [];
	if(!empty($_G['setting']['verify']['enabled'])) {
		foreach(table_common_member_verify_info::t()->group_by_verifytype_count() as $value) {
			if($value['num']) {
				if($value['verifytype']) {
					$verifyinfo = !empty($_G['setting']['verify'][$value['verifytype']]) ? $_G['setting']['verify'][$value['verifytype']] : [];
					if(!empty($verifyinfo['available'])) {
						$verify[$value['verifytype']] = [$verifyinfo['title'], $value['num']];
					}
				} else {
					$verify[0] = [cplang('members_verify_profile'), $value['num']];
				}
			}
		}
	}

	$errcredits = table_common_credit_log::t()->count_by_search(0, 'ERR', TIMESTAMP - 86400 * 7);

	$show = $membersmod || $threadsmod || $postsmod || $medalsmod || $blogsmod || $picturesmod || $doingsmod || $sharesmod || $commentsmod || $articlesmod || $articlecommentsmod || $topiccommentsmod || $reportcount || $threadsdel || !empty($verify) || $errcredits;
	if(!$show) {
		return;
	}

	// 计算各类待办总数
	$contentmod = $threadsmod + $postsmod + $blogsmod + $doingsmod + $picturesmod + $sharesmod + $commentsmod + $articlesmod + $articlecommentsmod + $topiccommentsmod;
	$othermod = $medalsmod + $groupmod + $reportcount + $threadsdel + ($verify ? array_sum(array_column($verify, 1)) : 0) + $errcredits;
	$totalcount = $membersmod + $contentmod + $othermod;

	require_once template('admin/index_todo');
}

function show_sitestatus() {
	global $_G, $lang;

	// 仅创始人可见
	if(!isfounder()) {
		return;
	}

	// 获取服务器资源信息
	$sitestatus = [];

	// 检测 shell_exec 是否可用
	$shellExecEnabled = function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));
	$sitestatus['shell_exec_enabled'] = $shellExecEnabled;

	// CPU 使用率
	$sitestatus['cpu'] = 0;
	$sitestatus['cpu_supported'] = false;
	if(!str_starts_with(PHP_OS, 'WIN')) {
		// 读取 /proc/stat 的辅助函数：优先 file_get_contents，受 open_basedir 限制时回退 shell_exec
		$readCpuStat = function() use ($shellExecEnabled) {
			$line = '';
			$line = @file_get_contents('/proc/stat');
			if(!$line && $shellExecEnabled) {
				$line = @shell_exec('cat /proc/stat 2>/dev/null');
			}
			if($line && preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $line, $m)) {
				return ['user' => (int)$m[1], 'nice' => (int)$m[2], 'system' => (int)$m[3], 'idle' => (int)$m[4], 'iowait' => (int)$m[5], 'irq' => (int)$m[6], 'softirq' => (int)$m[7]];
			}
			return null;
		};
		$stat1 = $readCpuStat();
		if($stat1) {
			usleep(1000000); // 1秒采样间隔
			$stat2 = $readCpuStat();
			if($stat2) {
				$total1 = array_sum($stat1);
				$total2 = array_sum($stat2);
				$idle1 = $stat1['idle'] + $stat1['iowait'];
				$idle2 = $stat2['idle'] + $stat2['iowait'];
				$totalDelta = $total2 - $total1;
				if($totalDelta > 0) {
					$sitestatus['cpu'] = round((1 - ($idle2 - $idle1) / $totalDelta) * 100, 1);
					$sitestatus['cpu_supported'] = true;
				}
			}
		}

		// 内存使用率
		$sitestatus['memory'] = 0;
		$sitestatus['memory_total'] = 0;
		$sitestatus['memory_used'] = 0;
		$sitestatus['memory_supported'] = false;

		// 优先 file_get_contents，受 open_basedir 限制时回退 shell_exec
		$meminfo = @file_get_contents('/proc/meminfo');
		if(!$meminfo && $shellExecEnabled) {
			$meminfo = @shell_exec('cat /proc/meminfo 2>/dev/null');
		}
		if($meminfo && preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $total) && preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $avail)) {
			$sitestatus['memory'] = round((($total[1] - $avail[1]) / $total[1]) * 100, 1);
			$sitestatus['memory_total'] = sizecount($total[1] * 1024);
			$sitestatus['memory_used'] = sizecount(($total[1] - $avail[1]) * 1024);
			$sitestatus['memory_supported'] = true;
		} elseif($meminfo && preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $total) && preg_match('/MemFree:\s+(\d+)\s+kB/', $meminfo, $free) && preg_match('/Buffers:\s+(\d+)\s+kB/', $meminfo, $buffers) && preg_match('/Cached:\s+(\d+)\s+kB/', $meminfo, $cached)) {
			$used = $total[1] - $free[1] - $buffers[1] - $cached[1];
			$sitestatus['memory'] = round(($used / $total[1]) * 100, 1);
			$sitestatus['memory_total'] = sizecount($total[1] * 1024);
			$sitestatus['memory_used'] = sizecount($used * 1024);
			$sitestatus['memory_supported'] = true;
		}
	}

	// 磁盘使用率
	$diskTotal = 0;
	$diskFree = 0;
	$diskFunctionsEnabled = function_exists('disk_total_space') && function_exists('disk_free_space') && !in_array('disk_total_space', array_map('trim', explode(',', ini_get('disable_functions')))) && !in_array('disk_free_space', array_map('trim', explode(',', ini_get('disable_functions'))));
	if($diskFunctionsEnabled) {
		$diskTotal = @disk_total_space(DISCUZ_ROOT);
		$diskFree = @disk_free_space(DISCUZ_ROOT);
	}
	$sitestatus['disk_supported'] = $diskFunctionsEnabled && $diskTotal && $diskFree;
	if($sitestatus['disk_supported']) {
		$sitestatus['disk'] = round((($diskTotal - $diskFree) / $diskTotal) * 100, 1);
		$sitestatus['disk_total'] = sizecount($diskTotal);
		$sitestatus['disk_used'] = sizecount($diskTotal - $diskFree);
	} else {
		$sitestatus['disk'] = 0;
		$sitestatus['disk_total'] = 0;
		$sitestatus['disk_used'] = 0;
	}

	$sitestatus['dbsize'] = '<span id="dbsizeMsg"><a class="sysinfo-detail" onclick="dbsize(this.href, event)" href="'.ADMINSCRIPT.'?action=index&formhash='.FORMHASH.'&operation=dbsize">'.$lang['detail'].'</a></span>';
	$sitestatus['attachsize'] = '<span id="attachsizeMsg"><a class="sysinfo-detail" onclick="attachsize(this.href, event)" href="'.ADMINSCRIPT.'?action=index&formhash='.FORMHASH.'&operation=attachsize">'.$lang['detail'].'</a></span>';

	// MySQL 状态
	try {
		$dbStatus = DB::fetch_first("SHOW STATUS LIKE 'Threads_connected'");
		$sitestatus['mysql_threads'] = $dbStatus['Value'] ?? 0;
		$sitestatus['mysql_status'] = 'running';
	} catch (Exception $e) {
		$sitestatus['mysql_status'] = 'error';
		$sitestatus['mysql_threads'] = 0;
	}

	// Redis 状态
	$sitestatus['redis_status'] = 'none';
	if(function_exists('redis') || class_exists('Redis')) {
		try {
			$m = new memory_driver_redis();
			$m->init($_G['config']['memory']['redis']);
			if($m->enable) {
				$sitestatus['redis_status'] = 'running';
				$info = $m->info('clients');
				$sitestatus['redis_threads'] = $info['connected_clients'] ?? 0;
				$memory = $m->info('memory');
				$sitestatus['used_memory'] = $memory['used_memory'] ? sizecount($memory['used_memory']) : 0;
			} else {
				$sitestatus['redis_status'] = 'stopped';
			}
		} catch (Exception $e) {
			$sitestatus['redis_status'] = 'error';
		}
	}

	// 备份状态
	$backupDir = DISCUZ_ROOT.'data/backup_';
	$backups = glob($backupDir.'*', GLOB_ONLYDIR);
	if($backups) {
		usort($backups, function($a, $b) {
			return filemtime($b) - filemtime($a);
		});
		$sitestatus['backup_last'] = dgmdate(filemtime($backups[0]), 'dt');
		$sitestatus['backup_status'] = (TIMESTAMP - filemtime($backups[0]) < 86400 * 30) ? 'normal' : 'warning';
	} else {
		$sitestatus['backup_last'] = cplang('none');
		$sitestatus['backup_status'] = 'warning';
	}

	// 安全评分计算
	$securityScore = 100;
	$securityIssues = [];

	// 检查关键配置 - 使用纯文本提示，避免HTML标签
	if(empty($_G['config']['admincp']['founder'])) {
		$securityScore -= 15;
		$securityIssues[] = cplang('sitestatus_issue_nofounder');
	}
	if(empty($_G['config']['admincp']['checkip'])) {
		$securityScore -= 10;
		$securityIssues[] = cplang('sitestatus_issue_checkip');
	}
	if(!empty($_G['config']['admincp']['runquery'])) {
		$securityScore -= 10;
		$securityIssues[] = cplang('sitestatus_issue_runquery');
	}
	if($sitestatus['backup_status'] == 'warning') {
		$securityScore -= 10;
		$securityIssues[] = cplang('sitestatus_issue_backup');
	}
	if($sitestatus['disk'] > 90) {
		$securityScore -= 15;
		$securityIssues[] = cplang('sitestatus_issue_disk');
	}
	if($sitestatus['memory_supported'] && $sitestatus['memory'] > 90) {
		$securityScore -= 10;
		$securityIssues[] = cplang('sitestatus_issue_memory');
	}

	$sitestatus['security_score'] = max(0, $securityScore);
	$sitestatus['security_level'] = $securityScore >= 80 ? 'good' : ($securityScore >= 60 ? 'warning' : 'danger');
	$sitestatus['security_issues'] = $securityIssues;

	require_once template('admin/index_sitestatus');
}

function show_edittips() {
	showtips('index_edit_tips');
}

function show_releasetips() {
	global $_G, $reldisp, $newversion, $lang;

	$siteuniqueid = $_G['setting']['siteuniqueid'] ?? table_common_setting::t()->fetch_setting('siteuniqueid');
	if(empty($siteuniqueid) || strlen($siteuniqueid) < 16) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$siteuniqueid = 'DX'.$chars[date('y') % 60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_G['clientip'].$_G['username'].TIMESTAMP), 0, 4).random(4);
		table_common_setting::t()->update_setting('siteuniqueid', $siteuniqueid);
		require_once libfile('function/cache');
		updatecache('setting');
	}

	if(!empty($_GET['closesitereleasetips'])) {
		table_common_setting::t()->update('sitereleasetips', 0);
		$sitereleasetips = 0;
		require_once libfile('function/cache');
		updatecache('setting');
	} else {
		$sitereleasetips = $_G['setting']['sitereleasetips'] ?? table_common_setting::t()->fetch('sitereleasetips');
	}

	$siterelease = $_G['setting']['siterelease'] ?? table_common_setting::t()->fetch('siterelease');
	$releasehash = substr(hash('sha512', $_G['config']['security']['authkey'].DISCUZ_VERSION.DISCUZ_RELEASE.$siteuniqueid), 0, 32);
	if(empty($siterelease) || strcmp($siterelease, $releasehash) !== 0) {
		table_common_setting::t()->update('siteversion', DISCUZ_VERSION);
		table_common_setting::t()->update('siterelease', $releasehash);
		table_common_setting::t()->update('sitereleasetips', 1);
		$sitereleasetips = 1;
		require_once libfile('function/cloudaddons');
		$newversion = json_decode(cloudaddons_open('&mod=app&ac=upgrade'), true);
		if(!empty($newversion['newversion'])) {
			$newversion['updatetime'] = $_G['timestamp'];
			table_common_setting::t()->update_setting('cloudaddons_newversion', ((CHARSET == 'utf-8') ? $newversion : json_encode($newversion)));
		} else {
			$newversion = [];
		}
		require_once libfile('function/cache');
		updatecache('setting');
	}

	$tips = $lang['home_security_business'];

	if(isfounder()) {
		$musts = check::extensions();
		if($musts) {
			if($musts['extension']) {
				$tips .= cplang('home_func_must_extension', ['exts' => implode(', ', $musts['extension'])]);
			}
			if($musts['function']) {
				$tips .= cplang('home_func_must_function', ['func' => implode(', ', $musts['function'])]);
			}
		}

		$tips .= !$_G['config']['admincp']['founder'] ? $lang['home_security_nofounder'] : '';
		$tips .= !$_G['config']['admincp']['checkip'] ? $lang['home_security_checkip'] : '';
		$tips .= $_G['config']['admincp']['runquery'] ? $lang['home_security_runquery'] : '';
	}

	if($tips) {
		showtips($tips);
	}
}

function show_onlines() {
	$admincp_session = table_common_admincp_session::t()->fetch_all_by_panel(1);
	if(count($admincp_session) == 1) {
		return;
	}
	$onlines = '';
	$members = table_common_member::t()->fetch_all(array_keys($admincp_session), false, 0);
	foreach($admincp_session as $uid => $online) {
		$onlines .= '<a href="home.php?mod=space&uid='.$online['uid'].'" title="'.dgmdate($online['dateline']).'" target="_blank">'.$members[$uid]['username'].'</a>&nbsp;&nbsp;&nbsp;';
	}
	showboxheader('home_onlines', '', 'id="home_onlines"');
	echo $onlines;
	showboxfooter();
}

function show_note() {
	global $_G;

	if(!$_G['inajax']) {
		showformheader('index&operation=note&notesubmit=yes', 'onsubmit="ajaxpost(this.id, \'home_notes\');return false;"');
		showboxheader('home_notes');
	}

	$notemsghtml = '';
	foreach(table_common_adminnote::t()->fetch_all_by_access(0) as $note) {
		if($note['expiration'] < TIMESTAMP) {
			table_common_adminnote::t()->delete_note($note['id']);
		} else {
			$note['adminenc'] = rawurlencode($note['admin']);
			$note['expiration'] = ceil(($note['expiration'] - $note['dateline']) / 86400);
			$note['dateline'] = dgmdate($note['dateline'], 'dt');
			$firstchar = dhtmlspecialchars(mb_strtoupper(mb_substr($note['admin'], 0, 1)));
			$delhtml = '';
			if(isfounder() || $_G['member']['username'] == $note['admin']) {
				$delhtml = '<a onclick="notedel(this, event)" href="'.ADMINSCRIPT.'?action=index&operation=notedel&noteid='.$note['id'].'&formhash='.$_G['formhash'].'" title="'.cplang('delete').'" class="ndel">×</a>';
			}
			$notemsghtml .= '<div class="dcol">'.
				'<div class="adminnote">'.$delhtml.'<div class="note-body">'.
				'<div class="note-header">'.
				'<div class="note-avatar">'.$firstchar.'</div>'.
				'<div><div class="note-author"><a href="home.php?mod=space&username='.$note['adminenc'].'" target="_blank">'.dhtmlspecialchars($note['admin']).'</a></div>'.
				'<div class="note-date">'.$note['dateline'].'</div></div></div>'.
				'<div class="note-message">'.$note['message'].'</div>'.
				'<div class="note-expire">'.cplang('home_notes_add').cplang('validity').': '.$note['expiration'].' '.cplang('days').'</div>'.
				'</div></div></div>';
		}
	}

	if(!$_G['inajax']) {
		echo '<div id="home_notes">';
		if($notemsghtml) {
			echo '<div class="drow">'.$notemsghtml.'</div>';
		}
		echo '</div></div><div class="boxbody adminnote-form">';

		echo '<div class="note-form-row">'.
			'<textarea name="newmessage" class="txt" rows="2" placeholder="'.cplang('home_notes_add').'..."></textarea>'.
			'<div class="note-form-meta">'.
			'<span class="meta-label">'.cplang('validity').'</span>'.
			'<input type="text" class="txt" name="newexpiration" value="30" />'.
			'<span class="meta-unit">'.cplang('days').'</span>'.
			'<input name="notesubmit" value="'.cplang('submit').'" type="submit" class="btn" />'.
			'</div>'.
			'</div>';

		showboxfooter();
		showformfooter();
	} else {
		include template('common/header');
		echo '<div class="drow">'.$notemsghtml.'</div>';
		echo '<script reload="1">$(\'cpform\').newmessage.value=\'\';</script>';
		include template('common/footer');
	}
}

function show_filecheck() {
	global $lang;

	if(!isfounder()) {
		return;
	}

	$filecheck = table_common_cache::t()->fetch('checktools_filecheck_result');
	$lastcheck = '';
	if($filecheck) {
		list($modifiedfiles, $deletedfiles, $unknownfiles, $doubt) = dunserialize($filecheck['cachevalue']);
		$lastcheck = dgmdate($filecheck['dateline'], 'u');
	} else {
		$modifiedfiles = $deletedfiles = $unknownfiles = $doubt = 0;
	}

	showboxheader($lang['nav_filecheck'].' <a href="javascript:;" style="float:right;" onclick="ajaxget(\''.ADMINSCRIPT.'?action=checktools&operation=filecheck&homecheck=yes\', \'filecheck_div\')">['.$lang['filecheck_check_now'].']</a>', 'nobottom fixpadding', 'id="filecheck"');

	echo '<div id="filecheck_div">';
	echo '<div class="fc-grid">'.
		'<div class="fc-item '.($modifiedfiles ? 'fc-warning' : 'fc-ok').'">'.
		'<div class="fc-icon"><i class="dzicon '.($modifiedfiles ? 'fc-i-warning' : 'fc-i-ok').'"></i></div>'.
		'<div class="fc-detail">'.
		'<div class="fc-label">'.$lang['filecheck_modify'].'</div>'.
		'<div class="fc-num">'.$modifiedfiles.'</div>'.
		'</div>'.
		'</div>'.
		'<div class="fc-item '.($deletedfiles ? 'fc-danger' : 'fc-ok').'">'.
		'<div class="fc-icon"><i class="dzicon '.($deletedfiles ? 'fc-i-danger' : 'fc-i-ok').'"></i></div>'.
		'<div class="fc-detail">'.
		'<div class="fc-label">'.$lang['filecheck_delete'].'</div>'.
		'<div class="fc-num">'.$deletedfiles.'</div>'.
		'</div>'.
		'</div>'.
		'<div class="fc-item '.($unknownfiles ? 'fc-info' : 'fc-ok').'">'.
		'<div class="fc-icon"><i class="dzicon '.($unknownfiles ? 'fc-i-info' : 'fc-i-ok').'"></i></div>'.
		'<div class="fc-detail">'.
		'<div class="fc-label">'.$lang['filecheck_unknown'].'</div>'.
		'<div class="fc-num">'.$unknownfiles.'</div>'.
		'</div>'.
		'</div>'.
		'<div class="fc-item '.($doubt ? 'fc-info' : 'fc-ok').'">'.
		'<div class="fc-icon"><i class="dzicon '.($doubt ? 'fc-i-info' : 'fc-i-ok').'"></i></div>'.
		'<div class="fc-detail">'.
		'<div class="fc-label">'.$lang['filecheck_doubt'].'</div>'.
		'<div class="fc-num">'.$doubt.'</div>'.
		'</div>'.
		'</div>'.
		'</div>';
	if($lastcheck) {
		echo '<div class="fc-footer">'.
			'<span class="fc-time">'.$lang['filecheck_last_homecheck'].': '.$lastcheck.'</span>'.
			'<a class="fc-link" href="'.ADMINSCRIPT.'?action=checktools&operation=filecheck&step=3">'.$lang['filecheck_view_list'].' <em>&rsaquo;</em></a>'.
			'</div>';
	}
	echo '</div>';

	showboxfooter();
	if($filecheck && TIMESTAMP - $filecheck['dateline'] > 86400 * 7) {
		echo '<script>ajaxget(\''.ADMINSCRIPT.'?action=checktools&operation=filecheck&homecheck=yes\', \'filecheck_div\');</script>';
	}
}

function _getSysLang() {
	$lang = [];
	@include DISCUZ_ROOT.'./source/i18n/'.currentlang().'/lang.php';
	if(!empty($lang['name'])) {
		return $lang['name'];
	}
	return '';
}

function show_sysinfo() {
	global $newversion, $reldisp, $lang, $_G;

	loaducenter();

	$newversion['newversion'] = !empty($newversion['newversion']) ? $newversion['newversion'] : [];
	$reldisp_addon = is_numeric($newversion['newversion']['release']) ? ('Release '.$newversion['newversion']['release']) : $newversion['newversion']['release'];
	$hc = 'class="sysinfo-header"';
	$dc = ['class="dcol lineheight d-14"', 'class="dcol lineheight d-1"'];

	showboxheader('home_sys_info', 'listbox', 'id="home_sys_info"');

	$newver = '';
	if(isfounder()) {
		loadcache('newver');
		if(empty($_G['cache']['newver']) || TIMESTAMP - $_G['cache']['newver']['t'] > 86400 * 5) {
			$u = new admin\class_upgrade();
			[, $remote, $new] = $u->getVersion(true);
			savecache('newver', [
				't' => TIMESTAMP,
				'remote' => $remote,
				'new' => $new,
			]);
		} else {
			$remote = $_G['cache']['newver']['remote'];
			$new = $_G['cache']['newver']['new'];
		}
		if($new) {
			$newver = ' &raquo; <a class="newver" href="'.ADMINSCRIPT.'?action=founder&operation=upgrade">'.$remote.'</a>';
		}
	}

	// ── Software Versions ──
	showboxrow($hc, ['class="dcol"'], ['<span class="sysinfo-label">'.cplang('home_version').'</span>']);
	showboxrow('', $dc, [
		cplang('home_discuz_version'),
		'<i class="dzlogo"></i> '.DISCUZ_VERSION_NAME.' '.DISCUZ_VERSION.DISCUZ_SUBVERSION.' '.$reldisp.
		((strlen(DISCUZ_RELEASE) == 8) ? '' : cplang('home_git_version')).
		$newver
	]);
	if(!UC_STANDALONE) {
		showboxrow('', $dc, [
			cplang('home_ucclient_version'),
			'<i class="uclogo"></i> UCenter '.UC_CLIENT_VERSION.' Release '.UC_CLIENT_RELEASE
		]);
	}
	require_once DISCUZ_ROOT.'./source/mitframe_version.php';
	showboxrow('', $dc, [
		cplang('home_mitframe_version'),
		'<i class="mitframe_gray"></i> '.MITFRAME_VERSION_NAME.' '.MITFRAME_VERSION,
	]);
	showboxrow('', $dc, [
		cplang('home_sys_lang'),
		'<i class="i18n_ico"></i> '._getSysLang(),
	]);

	// ── Server Environment ──
	showboxrow($hc, ['class="dcol"'], ['<span class="sysinfo-label">'.cplang('home_serversoftware').'</span>']);
	showboxrow('', $dc, [
		cplang('home_os'),
		'<i class="sysicon sy '.get_sysicon().'"></i> '.PHP_OS.' / '.php_uname()
	]);
	showboxrow('', $dc, [
		cplang('home_serversoftware'),
		'<i class="sysicon '.get_webicon().'"></i> '.$_SERVER['SERVER_SOFTWARE']
	]);
	showboxrow('', $dc, [
		cplang('home_environment'),
		'<i class="sysicon sy sys_php"></i> PHP '.PHP_VERSION.(PHP_ZTS ? ' TS' : '').(PHP_DEBUG ? ' DEBUG' : '').' , '.PHP_SAPI
	]);
	showboxrow('', $dc, [
		cplang('home_database'),
		'<i class="sysicon sy sys_mysql"></i> MySQL '.helper_dbtool::dbversion().' , '.$_G['mysql_driver']
	]);
	$meminfo = memory('check');
	showboxrow('', $dc, [
		cplang('home_memory'),
		'<i class="sysicon sy '.get_memicon().'"></i> '.get_meminfo()
	]);

	// ── Performance ──
	showboxrow($hc, ['class="dcol"'], ['<span class="sysinfo-label">'.cplang('home_benchmark').'</span>']);
	$opcache_on = function_exists('opcache_get_status');
	$opcache_msg = '';
	if($opcache_on) {
		$opcache_msg = 'OPcache: On';
		$value = opcache_get_status();
		$opcache_msg .= !empty($value['jit']['enabled']) ? ' , JIT: On' : ' , JIT: Off';
	} else {
		$opcache_msg = 'OPcache: Off';
	}
	$opcache_display = ' <span style="color:'.($opcache_on ? '#059669' : '#dc2626').'">('.$opcache_msg.')</span>';
	$benchmark = '<span id="benchmarkMsg"><a class="sysinfo-detail" onclick="benchmark(this.href, event);" href="'.ADMINSCRIPT.'?action=index&formhash='.FORMHASH.'&operation=benchmark">'.$lang['home_benchmark_run'].'</a></span>';
	$advice = '';
	showboxrow('', $dc, [
		cplang('home_benchmark'),
		$benchmark.$advice.$opcache_display
	]);
	showboxfooter();
}

function show_news() {
	global $newversion;

	showboxheader('discuz_news', 'listbox', 'id="discuz_news"');

	if(!empty($newversion['newversion'])) {
		$downlist = [];
		foreach($newversion['newversion']['downlist'] as $key => $value) {
			$downlist[] = '<a href="'.diconv($value['url'], 'utf-8', CHARSET).'" target="_blank">'.discuzcode(strip_tags(diconv($value['title'], 'utf-8', CHARSET)), 1, 0).'</a>';
		}

		$tips = '';
		if(!empty($downlist)) {
			$tips = implode('&#x3001;', $downlist);
		}

		if(empty($newversion['newversion']['qqqun'])) {
			$newversion['newversion']['qqqun'] = '73'.'210'.'36'.'90';
		}
		$tips .= '<span style="color:var(--admincp-fc)">'.cplang('qq_group').': '.$newversion['newversion']['qqqun'].'</span>';
	}

	echo '<div class="news-list">';
	if(!empty($newversion['news'])) {
		$newversion['news'] = dhtmlspecialchars($newversion['news']);
		foreach($newversion['news'] as $v) {
			$date = discuzcode(strip_tags($v['date']), 1, 0);
			$title = discuzcode(strip_tags(diconv($v['title'], 'utf-8', CHARSET)), 1, 0);
			echo '<div class="news-item"><span class="news-date">'.$date.'</span><a class="news-title" href="'.$v['url'].'" target="_blank">'.$title.'</a></div>';
		}
	} else {
		echo '<div class="news-item"><a class="news-title" href="https://www.dismall.com/" target="_blank">'.cplang('log_in_to_update').'</a></div>';
		echo '<div class="news-item"><a class="news-title" href="https://gitee.com/3dming/DiscuzL/attach_files" target="_blank">'.cplang('download_latest').'</a></div>';
	}
	echo '<div class="news-item">'.$tips.'</div>';
	echo '</div>';

	showboxfooter();
}

function show_widgets($type) {
	admin\widget_view::output($type);
}

function show_charts() {
	global $_G;
	if(!$_G['setting']['updatestat']) {
		return;
	}

	loadcache('statvars');

	require_once childfile('stat/function', 'misc');

	$statvars = getstatvars('basic');
	$statvars['online'] = C::app()->session->count();
	$statvars['onlinemembers'] = C::app()->session->count(1);

	require_once template('admin/index_charts');
}

function show_hotthreads() {
	require_once childfile('ranklist/function', 'misc');
	$threadlist = getranklistdata('thread', 'replies', 'thisweek');

	if(!$threadlist) {
		return;
	}

	$threadlist = array_slice($threadlist, 0, 10);

	require_once template('admin/index_hotthreads');
}

function show_forever_thanks() {
	$copyRightMessage = [
		'&#x7248;&#x6743;&#x6240;&#x6709;',
		'&#x5408;&#x80A5;&#x8D30;&#x9053;&#x7F51;&#x7EDC;&#x79D1;&#x6280;&#x6709;&#x9650;&#x516C;&#x53F8;',
		'&#x817E;&#x8BAF;&#x79D1;&#x6280;&#xFF08;&#x5317;&#x4EAC;&#xFF09;&#x6709;&#x9650;&#x516C;&#x53F8;',
	];
	$gitTeamStr = '';
	$gitTeam = [
		'yunnuowangluo' => '&#x4e91;&#x8bfa;',
		'guohui1301' => 'Hyman',
		'ytjxzxy' => '&#x5e73;&#x5b89;&#x7f51;&#x7edc;&#x79d1;&#x6280;',
		'comiis' => '&#x514b;&#x7c73;&#x8bbe;&#x8ba1;',
		'zoewho' => '&#x6E56;&#x4E2D;&#x6C89;',
		'3dming' => '&#x8BF8;&#x845B;&#x6653;&#x660E;',
		'laozhoubuluo' => '&#x8001;&#x5468;&#x90E8;&#x843D;',
		'popcorner' => 'popcorner',
		'brotherand2' => 'brotherand2',
		'nftstudio' => '&#x9006;&#x98CE;&#x5929;',
		'dzlab' => '&#x79D1;&#x7AD9;&#x7F51;',
		'ONEXIN' => 'ONEXIN',
	];
	foreach($gitTeam as $id => $name) {
		$gitTeamStr .= '<a href="https://gitee.com/'.$id.'" class="lightlink2" target="_blank">'.$name.'</a>';
	}
	$devTeamStr = '';
	$devTeam = [
		'174393' => 'Guode \'sup\' Li',
		'859' => 'Hypo \'Cnteacher\' Wang',
		'263098' => 'Liming \'huangliming\' Huang',
		'706770' => 'Jun \'Yujunhao\' Du',
		'80629' => 'Ning \'Monkeye\' Hou',
		'246213' => 'Lanbo Liu',
		'322293' => 'Qingpeng \'andy888\' Zheng',
		'401635' => 'Guosheng \'bilicen\' Zhang',
		'2829' => 'Mengshu \'msxcms\' Chen',
		'492114' => 'Liang \'Metthew\' Xu',
		'1087718' => 'Yushuai \'Max\' Cong',
		'875919' => 'Jie \'tom115701\' Zhang',
	];
	foreach($devTeam as $id => $name) {
		$devTeamStr .= '<a href="https://discuz.dismall.com/home.php?mod=space&uid='.$id.'" class="lightlink2" target="_blank">'.$name.'</a>';
	}
	$devSkins = [
		'294092' => 'Fangming \'Lushnis\' Li',
		'674006' => 'Jizhou \'Iavav\' Yuan',
		'717854' => 'Ruitao \'Pony.M\' Ma',
	];
	$devSkinsStr = '';
	foreach($devSkins as $id => $name) {
		$devSkinsStr .= '<a href="https://discuz.dismall.com/home.php?mod=space&uid='.$id.'" class="lightlink2" target="_blank">'.$name.'</a>';
	}
	$devThanksStr = '';
	$devThanks = [
		'122246' => 'Heyond',
		'632268' => 'JinboWang',
		'15104' => 'Redstone',
		'10407' => 'Qiang Liu',
		'210272' => 'XiaoDunFang',
		'86282' => 'Jianxieshui',
		'9600' => 'Theoldmemory',
		'2629' => 'Rain5017',
		'26926' => 'Snow Wolf',
		'17149' => 'Hehechuan',
		'9132' => 'Pk0909',
		'248' => 'feixin',
		'675' => 'Laobing Jiuba',
		'13877' => 'Artery',
		'233' => 'Huli Hutu',
		'122' => 'Lao Gui',
		'159' => 'Tyc',
		'177' => 'Stoneage',
		'7155' => 'Gregry',
	];
	foreach($devThanks as $id => $name) {
		$devThanksStr .= '<a href="https://discuz.dismall.com/home.php?mod=space&uid='.$id.'" class="lightlink2" target="_blank">'.$name.'</a>';
	}

	showboxheader('home_dev', 'listbox fixpadding', 'id="home_dev"');
	showboxrow('', ['class="dcol d-1 lineheight"', 'class="dcol lineheight team"'], [$copyRightMessage[0], '<span class="bold">'.$copyRightMessage[1].', '.$copyRightMessage[2].'</span>']);
	showboxrow('', ['class="dcol d-1 lineheight"', 'class="dcol lineheight team"'], [cplang('contributors'), $gitTeamStr]);
	showboxrow('', ['class="dcol d-1 lineheight"', 'class="dcol lineheight team"'], [cplang('home_forever'), '<a href="javascript:display(\'history\')">'.cplang('view').'</a>']);
	showtagheader('div', 'history');
	showboxrow('', ['class="dcol d-1 lineheight"', 'class="dcol lineheight team"'], [cplang('home_dev_manager'), '<a href="https://discuz.dismall.com/home.php?mod=space&uid=1" class="lightlink2" target="_blank">'.cplang('dev_manager').'</a>']);
	showboxrow('', ['class="dcol d-1 lineheight"', 'class="dcol lineheight team"'], [cplang('home_dev_team'), $devTeamStr]);
	showboxrow('', ['class="dcol d-1 lineheight"', 'class="dcol lineheight team"'], [cplang('home_dev_skins'), $devSkinsStr]);
	showboxrow('', ['class="dcol d-1 lineheight"', 'class="dcol lineheight team"'], [cplang('home_dev_thanks'), $devThanksStr]);
	showtagfooter('div');
	showboxfooter();
}

function get_sysicon() {
	$n = strtolower(php_uname());
	if(str_contains($n, 'windows')) {
		return 'sys_win';
	} elseif(str_contains($n, 'debian')) {
		return 'sys_debian';
	} elseif(str_contains($n, 'fedora')) {
		return 'sys_fedora';
	} elseif(str_contains($n, 'ubuntu')) {
		return 'sys_ubuntu';
	} elseif(str_contains($n, 'tencentos')) {
		return 'sys_linux';
	} elseif(str_contains($n, 'centos')) {
		return 'sys_centos';
	} elseif(str_contains($n, 'linux')) {
		return 'sys_linux';
	} else {
		return '';
	}
}

function get_memicon() {
	$n = strtolower(memory('check'));
	if(!$n) {
		return '';
	}
	if(str_contains($n, 'redis')) {
		return 'sys_redis';
	} elseif(str_contains($n, 'memcache')) {
		return 'sys_memcache';
	} else {
		return 'sys_memory';
	}
}

function get_meminfo() {
	$n = memory('check');
	if(!$n) {
		return cplang('none').' '.cplang('home_memory_advice');
	}
	if($n == 'Redis') {
		$v = memory('info', 'server');
		if(!empty($v['redis_version'])) {
			$n .= ' '.$v['redis_version'];
		}
	}
	return $n;
}

function get_webicon() {
	$n = strtolower($_SERVER['SERVER_SOFTWARE']);
	if(str_contains($n, 'nginx')) {
		return 'sys_nginx';
	} elseif(str_contains($n, 'apache')) {
		return 'sys_apache';
	} elseif(str_contains($n, 'lighttpd')) {
		return 'sys_lighttpd';
	} elseif(str_contains($n, 'iis')) {
		return 'sys_iis';
	} else {
		return 'sys_webserver';
	}
}

function get_benchmark() {
	$start = microtime(true);

	$r = 0;
	for($c = 0; $c < 1000000000; $c++) {
		$r += $c;
	}

	$end = microtime(true);
	return $end - $start;
}