<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(empty($_GET['edit']) && empty($_GET['run'])) {

	if(!submitcheck('cronssubmit')) {

		shownav('tools', 'misc_cron');
		showsubmenu('nav_misc_cron');
		/*search={"misc_cron":"action=misc&operation=cron"}*/
		showtips('misc_cron_tips');
		/*search*/
		showformheader('misc&operation=cron');
		showtableheader('', 'fixpadding');
		showsubtitle(['', 'name', 'available', 'type', 'time', 'misc_cron_last_run', 'misc_cron_next_run', '']);

		$query = DB::query('SELECT * FROM '.DB::table('common_cron').' ORDER BY type DESC');
		while($cron = DB::fetch($query)) {
			$disabled = $cron['weekday'] == -1 && $cron['day'] == -1 && $cron['hour'] == -1 && $cron['minute'] == '' ? 'disabled' : '';

			if($cron['day'] > 0 && $cron['day'] < 32) {
				$cron['time'] = cplang('misc_cron_permonth').$cron['day'].cplang('misc_cron_day');
			} elseif($cron['weekday'] >= 0 && $cron['weekday'] < 7) {
				$cron['time'] = cplang('misc_cron_perweek').cplang('misc_cron_week_day_'.$cron['weekday']);
			} elseif($cron['hour'] >= 0 && $cron['hour'] < 24) {
				$cron['time'] = cplang('misc_cron_perday');
			} else {
				$cron['time'] = cplang('misc_cron_perhour');
			}

			$cron['time'] .= $cron['hour'] >= 0 && $cron['hour'] < 24 ? sprintf('%02d', $cron['hour']).cplang('misc_cron_hour') : '';

			if(!in_array($cron['minute'], [-1, ''])) {
				foreach($cron['minute'] = explode("\t", $cron['minute']) as $k => $v) {
					$cron['minute'][$k] = sprintf('%02d', $v);
				}
				$cron['minute'] = implode(',', $cron['minute']);
				$cron['time'] .= $cron['minute'].cplang('misc_cron_minute');
			} else {
				$cron['time'] .= '00'.cplang('misc_cron_minute');
			}

			$cron['lastrun'] = $cron['lastrun'] ? dgmdate($cron['lastrun'], $_G['setting']['dateformat']."<\b\\r />".$_G['setting']['timeformat']) : '<b>N/A</b>';
			$cron['nextcolor'] = $cron['nextrun'] && $cron['nextrun'] + $_G['setting']['timeoffset'] * 3600 < TIMESTAMP ? 'style="color: #ff0000"' : '';
			$cron['nextrun'] = $cron['nextrun'] ? dgmdate($cron['nextrun'], $_G['setting']['dateformat']."<\b\\r />".$_G['setting']['timeformat']) : '<b>N/A</b>';
			$cron['run'] = $cron['available'];
			$efile = explode(':', $cron['filename']);
			if(count($efile) > 1 && !in_array($efile[0], $_G['setting']['plugins']['available'])) {
				$cron['run'] = 0;
			}

			showtablerow('', ['class="td25"', 'class="crons"', 'class="td25"', 'class="td25"', 'class="td23"', 'class="td23"', 'class="td23"'.$cron['nextcolor'], 'class="td25"'], [
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"{$cron['cronid']}\" ".($cron['type'] == 'system' ? 'disabled' : '').'>',
				"<input type=\"text\" class=\"txt\" name=\"namenew[{$cron['cronid']}]\" size=\"20\" value=\"{$cron['name']}\"><br /><b>{$cron['filename']}</b>",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[{$cron['cronid']}]\" value=\"1\" ".($cron['available'] ? 'checked' : '')." $disabled>",
				cplang($cron['type'] == 'system' ? 'inbuilt' : ($cron['type'] == 'plugin' ? 'plugin' : 'custom')),
				$cron['time'],
				$cron['lastrun'],
				$cron['nextrun'],
				"<a href=\"".ADMINSCRIPT."?action=misc&operation=cron&edit={$cron['cronid']}\" class=\"act\">{$lang['edit']}</a><br />".
				($cron['run'] ? " <a href=\"".ADMINSCRIPT."?action=misc&operation=cron&run={$cron['cronid']}&formhash=".FORMHASH."\" class=\"act\">{$lang['misc_cron_run']}</a>" : " <a href=\"###\" class=\"act\" disabled>{$lang['misc_cron_run']}</a>")
			]);
		}

		showtablerow('', ['', 'colspan="10"'], [
			cplang('add_new'),
			'<input type="text" class="txt" name="newname" value="" size="20" />'
		]);
		showsubmit('cronssubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($ids = dimplode($_GET['delete'])) {
			DB::delete('common_cron', "cronid IN ($ids) AND type='user'");
		}

		if(is_array($_GET['namenew'])) {
			foreach($_GET['namenew'] as $id => $name) {
				$newcron = [
					'name' => dhtmlspecialchars($_GET['namenew'][$id]),
					'available' => $_GET['availablenew'][$id]
				];
				if(empty($_GET['availablenew'][$id])) {
					$newcron['nextrun'] = '0';
				}
				DB::update('common_cron', $newcron, DB::field('cronid', $id));
			}
		}

		if($newname = trim($_GET['newname'])) {
			DB::insert('common_cron', [
				'name' => dhtmlspecialchars($newname),
				'type' => 'user',
				'available' => '0',
				'weekday' => '-1',
				'day' => '-1',
				'hour' => '-1',
				'minute' => '',
				'nextrun' => $_G['timestamp'],
			]);
		}

		$query = DB::query('SELECT cronid, filename FROM '.DB::table('common_cron'));
		while($cron = DB::fetch($query)) {
			$efile = explode(':', $cron['filename']);
			$pluginid = '';
			if(count($efile) > 1 && ispluginkey($efile[0])) {
				$pluginid = $efile[0];
				$cron['filename'] = $efile[1];
			}
			if(!$pluginid) {
				$cronfile = childfile(preg_replace('/\.php$/', '', $cron['filename']), 'global/cron');
				if(!file_exists($cronfile)) {
					DB::update('common_cron', [
						'available' => '0',
						'nextrun' => '0',
					], "cronid='{$cron['cronid']}'");
				}
			} else {
				if(!file_exists(DISCUZ_PLUGIN($pluginid).'/cron/'.$cron['filename'])) {
					DB::delete('common_cron', "cronid='{$cron['cronid']}'");
				}
			}
		}

		updatecache('setting');
		cpmsg('crons_succeed', 'action=misc&operation=cron', 'succeed');

	}

} else {

	$cronid = empty($_GET['run']) ? $_GET['edit'] : $_GET['run'];
	$cron = DB::fetch_first('SELECT * FROM '.DB::table('common_cron').' WHERE '.DB::field('cronid', $cronid));
	if(!$cron) {
		cpmsg('cron_not_found', '', 'error');
	}
	$cron['filename'] = str_replace(['..', '/', '\\'], ['', '', ''], $cron['filename']);
	$cronminute = str_replace("\t", ',', $cron['minute']);
	$cron['minute'] = explode("\t", $cron['minute']);

	if(!empty($_GET['edit'])) {

		if(!submitcheck('editsubmit')) {

			shownav('tools', 'misc_cron');
			showchildmenu([['nav_misc_cron', 'misc&operation=cron']], $cron['name']);

			showtips('misc_cron_edit_tips');

			$weekdayselect = $dayselect = $hourselect = '';

			for($i = 0; $i <= 6; $i++) {
				$weekdayselect .= "<option value=\"$i\" ".($cron['weekday'] == $i ? 'selected' : '').'>'.$lang['misc_cron_week_day_'.$i].'</option>';
			}

			for($i = 1; $i <= 31; $i++) {
				$dayselect .= "<option value=\"$i\" ".($cron['day'] == $i ? 'selected' : '').">$i {$lang['misc_cron_day']}</option>";
			}

			for($i = 0; $i <= 23; $i++) {
				$hourselect .= "<option value=\"$i\" ".($cron['hour'] == $i ? 'selected' : '').">$i {$lang['misc_cron_hour']}</option>";
			}

			shownav('tools', 'misc_cron');
			showformheader("misc&operation=cron&edit=$cronid");
			showtableheader();
			showsetting('misc_cron_edit_weekday', '', '', "<select name=\"weekdaynew\"><option value=\"-1\">*</option>$weekdayselect</select>");
			showsetting('misc_cron_edit_day', '', '', "<select name=\"daynew\"><option value=\"-1\">*</option>$dayselect</select>");
			showsetting('misc_cron_edit_hour', '', '', "<select name=\"hournew\"><option value=\"-1\">*</option>$hourselect</select>");
			showsetting('misc_cron_edit_minute', 'minutenew', $cronminute, 'text');
			showsetting('misc_cron_edit_filename', 'filenamenew', $cron['filename'], 'text');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$daynew = $_GET['weekdaynew'] != -1 ? -1 : $_GET['daynew'];
			if(str_contains($_GET['minutenew'], ',')) {
				$minutenew = explode(',', $_GET['minutenew']);
				foreach($minutenew as $key => $val) {
					$minutenew[$key] = $val = intval($val);
					if($val < 0 || $var > 59) {
						unset($minutenew[$key]);
					}
				}
				$minutenew = array_slice(array_unique($minutenew), 0, 12);
				$minutenew = implode("\t", $minutenew);
			} else {
				$minutenew = intval($_GET['minutenew']);
				$minutenew = $minutenew >= 0 && $minutenew < 60 ? $minutenew : '';
			}

			$efile = explode(':', $_GET['filenamenew']);
			if(!str_ends_with($_GET['filenamenew'], '.php')) {
				cpmsg('crons_filename_illegal', '', 'error');
			}

			$pluginid = '';
			if(count($efile) > 1 && ispluginkey($efile[0])) {
				$pluginid = $efile[0];
				$_GET['filenamenew'] = $efile[1];
			}

			if(!$pluginid) {
				if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $_GET['filenamenew'])) {
					cpmsg('crons_filename_illegal', '', 'error');
				} elseif(!is_readable(childfile(preg_replace('/\.php$/', '', $_GET['filenamenew']), 'global/cron'))) {
					cpmsg('crons_filename_invalid', '', 'error', ['cronfile' => $_GET['filenamenew']]);
				} elseif($_GET['weekdaynew'] == -1 && $daynew == -1 && $_GET['hournew'] == -1 && $minutenew === '') {
					cpmsg('crons_time_invalid', '', 'error');
				}
			} else {
				if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $_GET['filenamenew'])) {
					cpmsg('crons_filename_illegal', '', 'error');
				} elseif(!is_readable($cronfile = DISCUZ_PLUGIN($pluginid)."/cron/{$_GET['filenamenew']}")) {
					cpmsg('crons_filename_invalid', '', 'error', ['cronfile' => $pluginid."/cron/{$_GET['filenamenew']}"]);
				} elseif($_GET['weekdaynew'] == -1 && $daynew == -1 && $_GET['hournew'] == -1 && $minutenew === '') {
					cpmsg('crons_time_invalid', '', 'error');
				}
				$_GET['filenamenew'] = $pluginid.':'.$_GET['filenamenew'];
			}

			DB::update('common_cron', [
				'weekday' => $_GET['weekdaynew'],
				'day' => $daynew,
				'hour' => $_GET['hournew'],
				'minute' => $minutenew,
				'filename' => trim($_GET['filenamenew']),
			], DB::field('cronid', $cronid));

			discuz_cron::run($cronid);

			cpmsg('crons_succeed', 'action=misc&operation=cron', 'succeed');

		}

	} else {

		if(FORMHASH != $_GET['formhash']) {
			cpmsg('undefined_action', '', 'error');
		}
		$efile = explode(':', $cron['filename']);
		if(count($efile) > 1 && ispluginkey($efile[0])) {
			$cronfile = DISCUZ_PLUGIN($efile[0]).'/cron/'.$efile[1];
		} else {
			$cronfile = childfile(preg_replace('/\.php$/', '', $cron['filename']), 'global/cron');
		}

		if(!str_ends_with($cronfile, '.php') || !file_exists($cronfile)) {
			cpmsg('crons_run_invalid', '', 'error', ['cronfile' => $cronfile]);
		} else {
			discuz_cron::run($cron['cronid']);
			cpmsg('crons_run_succeed', 'action=misc&operation=cron', 'succeed');
		}

	}

}
	