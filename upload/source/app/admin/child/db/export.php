<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	$content = dfsockopen($_G['siteurl'].'API/JAVASC~1/ADVERT~1.PHP');
	if(str_contains($content, 'Access Denied')) {
		cpmsg('database_export_dos8p3_failed', '', 'error');
	}
}

$_SERVER['REQUEST_METHOD'] = 'POST';
if(!submitcheck('exportsubmit')) {

	$shelldisabled = 'disabled';

	$tables = '';
	$dztables = [];
	$tables = table_common_setting::t()->fetch_setting('custombackup', true);

	$discuz_tables = fetchtablelist($tablepre);

	foreach($discuz_tables as $table) {
		$dztables[$table['Name']] = $table['Name'];
	}

	$defaultfilename = date('ymd').'_'.random(8);

	include DISCUZ_ROOT.'./config/config_ucenter.php';
	$uc_tablepre = explode('.', UC_DBTABLEPRE);
	$uc_tablepre = $uc_tablepre[1] ? $uc_tablepre[1] : $uc_tablepre[0];
	$uc_tablepre = substr($uc_tablepre, '0', '-8');
	if(UC_CONNECT == 'mysql' && UC_DBHOST == $_G['config']['db'][1]['dbhost'] && UC_DBNAME == $_G['config']['db'][1]['dbname'] && $uc_tablepre == $tablepre) {
		$db_export = 'db_export_discuz_uc';
		$db_export_key = 'discuz_uc';
		$db_export_tips = cplang('db_export_tips_uc', ['uc_backup_url' => $uc_backup_url]).cplang('db_export_tips');
		$db_export_discuz_table = cplang('db_export_discuz_table_uc');
	} else {
		$db_export = 'db_export_discuz';
		$db_export_key = 'discuz';
		$uc_backup_url = UC_API.'/admin.php?m=db&a=ls&iframe=1';
		$db_export_tips = cplang('db_export_tips_nouc', ['uc_backup_url' => $uc_backup_url]).cplang('db_export_tips');
		$db_export_discuz_table = cplang('db_export_discuz_table');
	}

	shownav('founder', 'nav_db', 'nav_db_export');
	showsubmenu('nav_db', [
		['nav_db_export', 'db&operation=export', 1],
		['nav_db_runquery', 'db&operation=runquery', 0],
		['nav_db_optimize', 'db&operation=optimize', 0],
		['nav_db_dbcheck', 'db&operation=dbcheck', 0]
	]);
	/*search={"nav_db":"action=db&operation=export","nav_db_export":"action=db&operation=export"}*/
	showtips($db_export_tips);
	showformheader('db&operation=export&setup=1');
	showtableheader();
	showsetting('db_export_type', ['type', [
		[$db_export_key, $lang[$db_export], ['showtables' => 'none']],
		['custom', $lang['db_export_custom'], ['showtables' => '']]
	]], $db_export_key, 'mradio');

	showtagheader('tbody', 'showtables');
	showtablerow('', '', '<input class="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'customtables\', \'chkall\', true)" checked="checked" type="checkbox" id="chkalltables" /><label for="chkalltables"> '.cplang('db_export_custom_select_all').' - '.$db_export_discuz_table.'</label>');
	showtablerow('', 'colspan="2"', mcheckbox('customtables', $dztables));
	showtagfooter('tbody');

	showtagheader('tbody', 'advanceoption');
	showsetting('db_export_method', '', '', '<ul class="nofloat"><li><input class="radio" type="radio" name="method" value="shell" '.$shelldisabled.' onclick="this.form.sqlcharset[0].checked=true; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=true;}" id="method_shell" /><label for="method_shell"> '.$lang['db_export_shell'].'</label></li><li><input class="radio" type="radio" name="method" value="multivol" checked="checked" onclick="this.form.sqlcompat[2].disabled=false; this.form.sizelimit.disabled=false; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=false;}" id="method_multivol" /><label for="method_multivol"> '.$lang['db_export_multivol'].'</label> <input type="text" class="txt" size="40" name="sizelimit" value="2048" /></li></ul>');
	showtitle('db_export_options');
	showsetting('db_export_options_extended_insert', 'extendins', 0, 'radio');
	showsetting('db_export_options_sql_compatible', ['sqlcompat', [
		['0', $lang['default']],
		['MYSQL40', 'MySQL 3.23/4.0.x'],
		['MYSQL41', 'MySQL 4.1.x/5.x']
	]], '0', 'mradio');
	showsetting('db_export_options_charset', ['sqlcharset', [
		['0', cplang('default')],
		$dbcharset ? [$dbcharset, strtoupper($dbcharset)] : [],
		$dbcharset != 'utf8' ? ['utf8', 'UTF-8'] : []
	], TRUE], '0', 'mradio');
	showsetting('db_export_usehex', 'usehex', 1, 'radio');
	if(function_exists('gzcompress')) {
		showsetting('db_export_usezip', ['usezip', [
			['1', $lang['db_export_zip_1']],
			['2', $lang['db_export_zip_2']],
			['0', $lang['db_export_zip_3']]
		]], '0', 'mradio');
	}
	showsetting('db_export_filename', '', '', '<input type="text" class="txt" name="filename" value="'.$defaultfilename.'" />.sql');
	showtagfooter('tbody');

	showsubmit('exportsubmit', 'submit', '', 'more_options');
	showtablefooter();
	showformfooter();
	/*search*/

} else {

	DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');

	if(!$_GET['filename'] || !preg_match('/^[\w\_]+$/', $_GET['filename'])) {
		cpmsg('database_export_filename_invalid', '', 'error');
	}

	if(!in_array($_GET['type'], ['discuz', 'discuz_uc', 'custom'])) {
		$_GET['type'] = 'discuz';
	}

	if(!in_array($_GET['method'], ['multivol', 'shell'])) {
		$_GET['method'] = 'multivol';
	}

	if(!$_GET['sqlcharset'] || !preg_match('/^[\w\_\-]+$/', $_GET['sqlcharset'])) {
		$_GET['sqlcharset'] = strtolower($dbcharset);
	}

	$time = dgmdate(TIMESTAMP);
	if($_GET['type'] == 'discuz' || $_GET['type'] == 'discuz_uc') {
		$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
	} elseif($_GET['type'] == 'custom') {
		$tables = [];
		if(empty($_GET['setup'])) {
			$tables = table_common_setting::t()->fetch_setting('custombackup', true);
		} else {
			table_common_setting::t()->update_setting('custombackup', empty($_GET['customtables']) ? '' : $_GET['customtables']);
			$tables = &$_GET['customtables'];
		}
		if(!is_array($tables) || empty($tables)) {
			cpmsg('database_export_custom_invalid', '', 'error');
		}
	}

	$memberexist = array_search(DB::table('common_member'), $tables);
	if($memberexist !== FALSE) {
		unset($tables[$memberexist]);
		array_unshift($tables, DB::table('common_member'));
	}

	$volume = intval($_GET['volume']) + 1;
	$idstring = '# Identify: '.base64_encode("{$_G['timestamp']},".$_G['setting']['version'].",{$_GET['type']},{$_GET['method']},{$volume},{$tablepre},{$_GET['sqlcharset']}")."\n";


	$dumpcharset = $_GET['sqlcharset'] ? $_GET['sqlcharset'] : str_replace('-', '', $_G['charset']);
	$setnames = ($_GET['sqlcharset'] && (!$_GET['sqlcompat'] || $_GET['sqlcompat'] == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
	if($_GET['sqlcharset']) {
		DB::query('SET NAMES %s', [$_GET['sqlcharset']]);
	}
	if($_GET['sqlcompat'] == 'MYSQL40') {
		DB::query("SET SQL_MODE='MYSQL40'");
	} elseif($_GET['sqlcompat'] == 'MYSQL41') {
		DB::query("SET SQL_MODE=''");
	}

	$backupfilename = './data/'.$backupdir.'/'.str_replace(['/', '\\', '.', "'"], '', $_GET['filename']);

	if($_GET['usezip']) {
		require_once './source/class/class_zip.php';
	}

	if($_GET['method'] == 'multivol') {

		$sqldump = '';
		$tableid = intval($_GET['tableid']);
		$startfrom = intval($_GET['startfrom']);

		if(!$tableid && $volume == 1) {
			foreach($tables as $table) {
				$sqldump .= sqldumptablestruct($table);
			}
		}

		$complete = TRUE;
		for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $_GET['sizelimit'] * 1000; $tableid++) {
			$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
			if($complete) {
				$startfrom = 0;
			}
		}

		$dumpfile = $backupfilename.'-%s'.'.sql';
		!$complete && $tableid--;
		if(trim($sqldump)) {
			$sqldump = "$idstring".
				"# <?php exit();?>\n".
				"# Discuz! Multi-Volume Data Dump Vol.$volume\n".
				"# Version: Discuz! {$_G['setting']['version']}\n".
				"# Time: $time\n".
				"# Type: {$_GET['type']}\n".
				"# Table Prefix: $tablepre\n".
				"#\n".
				"# Discuz! Home: https://www.discuz.vip\n".
				"# Please visit our website for newest infomation about Discuz!\n".
				"# --------------------------------------------------------\n\n\n".
				"$setnames".
				$sqldump;
			$dumpfilename = sprintf($dumpfile, $volume);

			$fp = fopen($dumpfilename, 'cb');
			if(!($fp && flock($fp, LOCK_EX) && ftruncate($fp, 0) && fwrite($fp, $sqldump) && fflush($fp) && flock($fp, LOCK_UN) && fclose($fp))) {
				flock($fp, LOCK_UN);
				fclose($fp);
				cpmsg('database_export_file_invalid', '', 'error');
			} else {
				if($_GET['usezip'] == 2) {
					$fp = fopen($dumpfilename, 'r');
					$content = @fread($fp, filesize($dumpfilename));
					fclose($fp);
					$zip = new zipfile();
					$zip->addFile($content, basename($dumpfilename));
					$fp = fopen(sprintf($backupfilename.'-%s'.'.zip', $volume), 'c');
					if($fp && flock($fp, LOCK_EX) && ftruncate($fp, 0) && fwrite($fp, $zip->file()) && fflush($fp) && flock($fp, LOCK_UN) && fclose($fp)) {
						@unlink($dumpfilename);
					} else {
						flock($fp, LOCK_UN);
						fclose($fp);
						cpmsg('database_export_zip_invalid', '', 'error');
					}
				}
				unset($sqldump, $zip, $content);
				cpmsg('database_export_multivol_redirect', 'action=db&operation=export&formhash='.formhash().'&type='.rawurlencode($_GET['type']).'&saveto=server&filename='.rawurlencode($_GET['filename']).'&method=multivol&sizelimit='.rawurlencode($_GET['sizelimit']).'&volume='.rawurlencode($volume).'&tableid='.rawurlencode($tableid).'&startfrom='.rawurlencode($startrow).'&extendins='.rawurlencode($_GET['extendins']).'&sqlcharset='.rawurlencode($_GET['sqlcharset']).'&sqlcompat='.rawurlencode($_GET['sqlcompat'])."&exportsubmit=yes&usehex={$_GET['usehex']}&usezip={$_GET['usezip']}", 'loading', ['volume' => $volume]);
			}
		} else {
			$volume--;
			$filelist = '<ul>';
			cpheader();

			if($_GET['usezip'] == 1) {
				$zip = new zipfile();
				$zipfilename = $backupfilename.'.zip';
				$unlinks = [];
				for($i = 1; $i <= $volume; $i++) {
					$filename = sprintf($dumpfile, $i);
					$fp = fopen($filename, 'r');
					$content = @fread($fp, filesize($filename));
					fclose($fp);
					$zip->addFile($content, basename($filename));
					$unlinks[] = $filename;
					$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
				}
				$fp = fopen($zipfilename, 'c');
				if($fp && flock($fp, LOCK_EX) && ftruncate($fp, 0) && fwrite($fp, $zip->file()) && fflush($fp) && flock($fp, LOCK_UN) && fclose($fp)) {
					foreach($unlinks as $link) {
						@unlink($link);
					}
				} else {
					flock($fp, LOCK_UN);
					fclose($fp);
					table_common_cache::t()->insert([
						'cachekey' => 'db_export',
						'cachevalue' => serialize(['dateline' => $_G['timestamp']]),
						'dateline' => $_G['timestamp'],
					], false, true);
					$deletetips = $_G['config']['admincp']['dbimport'] ? cplang('db_delete_tips', ['filename' => basename($backupfilename), 'FORMHASH' => formhash()]) : '';
					cpmsg('database_export_multivol_succeed', '', 'succeed', ['volume' => $volume, 'filelist' => $filelist, 'deletetips' => $deletetips]);
				}
				unset($sqldump, $zip, $content);
				@touch('./data/'.$backupdir.'/index.htm');
				$filename = $zipfilename;
				table_common_cache::t()->insert([
					'cachekey' => 'db_export',
					'cachevalue' => serialize(['dateline' => $_G['timestamp']]),
					'dateline' => $_G['timestamp'],
				], false, true);
				$deletetips = $_G['config']['admincp']['dbimport'] ? cplang('db_delete_tips', ['filename' => basename($zipfilename), 'FORMHASH' => formhash()]) : '';
				cpmsg('database_export_zip_succeed', '', 'succeed', ['filename' => $filename, 'deletetips' => $deletetips]);
			} else {
				@touch('./data/'.$backupdir.'/index.htm');
				for($i = 1; $i <= $volume; $i++) {
					$filename = sprintf($_GET['usezip'] == 2 ? $backupfilename.'-%s'.'.zip' : $dumpfile, $i);
					$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
				}
				table_common_cache::t()->insert([
					'cachekey' => 'db_export',
					'cachevalue' => serialize(['dateline' => $_G['timestamp']]),
					'dateline' => $_G['timestamp'],
				], false, true);
				$deletetips = '';
				cpmsg('database_export_multivol_succeed', '', 'succeed', ['volume' => $volume, 'filelist' => $filelist, 'deletetips' => $deletetips]);
			}
		}

	} else {

		cpmsg('database_shell_fail', '', 'error');

	}
}
	