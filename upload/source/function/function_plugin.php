<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/cloudaddons');

function plugininstall($pluginarray, $installtype = '', $available = 0) {
	if(!$pluginarray || !$pluginarray['plugin']['identifier']) {
		return false;
	}
	$plugin = table_common_plugin::t()->fetch_by_identifier($pluginarray['plugin']['identifier']);
	if($plugin) {
		return false;
	}

	$pluginarray['plugin']['modules'] = dunserialize($pluginarray['plugin']['modules']);
	$pluginarray['plugin']['modules']['extra']['installtype'] = $installtype;

	if(!$installtype) {
		$identifier = $pluginarray['plugin']['identifier'];
		$langfile = DISCUZ_PLUGIN($identifier).'/i18n/'.currentlang().'/lang_plugin.php';
		if(file_exists($langfile)) {
			$scriptlang = $templatelang = $systemlang = $installlang = [];
			require $langfile;
			if(!empty($scriptlang[$identifier])) {
				$pluginarray['language']['scriptlang'] = $scriptlang[$identifier];
			}
			if(!empty($templatelang[$identifier])) {
				$pluginarray['language']['templatelang'] = $templatelang[$identifier];
			}
			if(!empty($systemlang[$identifier])) {
				$pluginarray['language']['systemlang'] = $systemlang[$identifier];
			}
			if(!empty($installlang[$identifier])) {
				$pluginarray['language']['installlang'] = $installlang[$identifier];
			}
		}
	}

	if(updatepluginlanguage($pluginarray)) {
		$pluginarray['plugin']['modules']['extra']['langexists'] = 1;
	}
	if(!empty($pluginarray['intro'])) {
		if(!empty($pluginarray['intro'])) {
			require_once libfile('function/discuzcode');
			$pluginarray['plugin']['modules']['extra']['intro'] = discuzcode(strip_tags($pluginarray['intro']), 1, 0);
		}
	}
	if(!empty($pluginarray['uninstallfile'])) {
		$pluginarray['plugin']['modules']['extra']['uninstallfile'] = $pluginarray['uninstallfile'];
	}
	if(!empty($pluginarray['checkfile'])) {
		$pluginarray['plugin']['modules']['extra']['checkfile'] = $pluginarray['checkfile'];
	}
	if(!empty($pluginarray['enablefile'])) {
		$pluginarray['plugin']['modules']['extra']['enablefile'] = $pluginarray['enablefile'];
	}
	if(!empty($pluginarray['disablefile'])) {
		$pluginarray['plugin']['modules']['extra']['disablefile'] = $pluginarray['disablefile'];
	}

	$pluginarray['plugin']['modules'] = serialize($pluginarray['plugin']['modules']);

	$data = [];
	foreach($pluginarray['plugin'] as $key => $val) {
		if($key == 'directory') {
			$val .= (!empty($val) && !str_ends_with($val, '/')) ? '/' : '';
		} elseif($key == 'available') {
			$val = $available;
		}
		$data[$key] = $val;
	}

	$pluginid = table_common_plugin::t()->insert($data, true);

	if(is_array($pluginarray['var'])) {
		foreach($pluginarray['var'] as $config) {
			$data = ['pluginid' => $pluginid];
			foreach($config as $key => $val) {
				$data[$key] = $val;
			}
			table_common_pluginvar::t()->insert($data);
		}
	}

	if(!empty($dir) && !empty($pluginarray['importfile'])) {
		require_once libfile('function/importdata');
		foreach($pluginarray['importfile'] as $importtype => $file) {
			if(in_array($importtype, ['smilies', 'styles'])) {
				$files = explode(',', $file);
				foreach($files as $file) {
					if(file_exists($file = DISCUZ_PLUGIN($dir).'/'.$file)) {
						$importtxt = @implode('', file($file));
						$imporfun = 'import_'.$importtype;
						$imporfun();
					}
				}
			}
		}
	}

	cloudaddons_installlog($pluginarray['plugin']['identifier'].'.plugin');
	cron_create($pluginarray['plugin']['identifier']);
	updatecache(['plugin', 'setting', 'styles']);
	cleartemplatecache();
	dsetcookie('addoncheck_plugin', '', -1);
	return $pluginid;
}

function pluginupgrade($pluginarray, $installtype) {
	if(!$pluginarray || !$pluginarray['plugin']['identifier']) {
		return false;
	}
	$plugin = table_common_plugin::t()->fetch_by_identifier($pluginarray['plugin']['identifier']);
	if(!$plugin) {
		return false;
	}
	if(is_array($pluginarray['var'])) {
		$pluginvars = $pluginvarsnew = [];
		foreach(table_common_pluginvar::t()->fetch_all_by_pluginid($plugin['pluginid']) as $pluginvar) {
			$pluginvars[] = $pluginvar['variable'];
		}
		foreach($pluginarray['var'] as $config) {
			if(!in_array($config['variable'], $pluginvars)) {
				$data = ['pluginid' => $plugin['pluginid']];
				foreach($config as $key => $val) {
					$data[$key] = $val;
				}
				table_common_pluginvar::t()->insert($data);
			} else {
				$data = [];
				foreach($config as $key => $val) {
					if($key != 'value') {
						$data[$key] = $val;
					}
				}
				if($data) {
					table_common_pluginvar::t()->update_by_variable($plugin['pluginid'], $config['variable'], $data);
				}
			}
			$pluginvarsnew[] = $config['variable'];
		}
		$pluginvardiff = array_diff($pluginvars, $pluginvarsnew);
		if($pluginvardiff) {
			table_common_pluginvar::t()->delete_by_variable($plugin['pluginid'], $pluginvardiff);
		}
	}

	$langexists = updatepluginlanguage($pluginarray);

	$pluginarray['plugin']['modules'] = dunserialize($pluginarray['plugin']['modules']);
	$plugin['modules'] = dunserialize($plugin['modules']);
	if(!empty($plugin['modules']['system'])) {
		$pluginarray['plugin']['modules']['system'] = $plugin['modules']['system'];
	}
	$plugin['modules']['extra']['installtype'] = $installtype;
	$pluginarray['plugin']['modules']['extra'] = $plugin['modules']['extra'];
	if(!empty($pluginarray['intro']) || $langexists) {
		if(!empty($pluginarray['intro'])) {
			require_once libfile('function/discuzcode');
			$pluginarray['plugin']['modules']['extra']['intro'] = discuzcode(strip_tags($pluginarray['intro']), 1, 0);
		}
		$langexists && $pluginarray['plugin']['modules']['extra']['langexists'] = 1;
	}
	if(!empty($pluginarray['uninstallfile'])) {
		$pluginarray['plugin']['modules']['extra']['uninstallfile'] = $pluginarray['uninstallfile'];
	}
	if(!empty($pluginarray['checkfile'])) {
		$pluginarray['plugin']['modules']['extra']['checkfile'] = $pluginarray['checkfile'];
	}
	if(!empty($pluginarray['enablefile'])) {
		$pluginarray['plugin']['modules']['extra']['enablefile'] = $pluginarray['enablefile'];
	}
	if(!empty($pluginarray['disablefile'])) {
		$pluginarray['plugin']['modules']['extra']['disablefile'] = $pluginarray['disablefile'];
	}
	$pluginarray['plugin']['modules'] = serialize($pluginarray['plugin']['modules']);

	$data = [];
	foreach($pluginarray['plugin'] as $key => $val) {
		if($key == 'directory') {
			$val .= (!empty($val) && !str_ends_with($val, '/')) ? '/' : '';
		} elseif($key == 'available') {
			continue;
		}
		$data[$key] = $val;
	}

	table_common_plugin::t()->update($plugin['pluginid'], $data);

	cloudaddons_installlog($pluginarray['plugin']['identifier'].'.plugin');
	cron_create($pluginarray['plugin']['identifier']);
	updatecache(['plugin', 'setting', 'styles']);
	cleartemplatecache();
	dsetcookie('addoncheck_plugin', '', -1);
	return true;
}

function modulecmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

function issafepath($filename) {
	if(!$filename || !is_string($filename)) {
		return false;
	}
	if(!preg_match('/\.php$/i', $filename)) {
		return false;
	}
	$pluginroot = realpath(rtrim(DISCUZ_ROOT, '/\\').'/'.PLUGIN_ROOT);
	if(!$pluginroot) {
		return false;
	}
	$pluginroot = rtrim(str_replace('\\', '/', $pluginroot), '/');
	$realfile = realpath($filename);
	if(!$realfile || !is_file($realfile)) {
		return false;
	}
	$realfile = str_replace('\\', '/', $realfile);
	if(!str_starts_with($realfile, $pluginroot.'/')) {
		return false;
	}
	return $realfile;
}

function updatepluginlanguage($pluginarray) {
	global $_G;
	if(!$pluginarray['language']) {
		return false;
	}
	foreach(['script', 'template', 'install', 'system'] as $type) {
		loadcache('pluginlanguage_'.$type, 1);
		if(empty($_G['cache']['pluginlanguage_'.$type])) {
			$_G['cache']['pluginlanguage_'.$type] = [];
		}
		if($type != 'system') {
			if(!empty($pluginarray['language'][$type.'lang'])) {
				$_G['cache']['pluginlanguage_'.$type][$pluginarray['plugin']['identifier']] = $pluginarray['language'][$type.'lang'];
			}
		} else {
			if(!empty($_G['config']['plugindeveloper']) && @include(DISCUZ_DATA.'./plugindata/'.$pluginarray['plugin']['identifier'].'.lang.php')) {
				if(!empty($systemlang[$pluginarray['plugin']['identifier']])) {
					$pluginarray['language']['systemlang'] = $systemlang[$pluginarray['plugin']['identifier']];
				}
			}
			foreach($pluginarray['language']['systemlang'] as $file => $vars) {
				foreach($vars as $key => $var) {
					$_G['cache']['pluginlanguage_system'][$file][$key] = $var;
				}
			}
		}
		savecache('pluginlanguage_'.$type, $_G['cache']['pluginlanguage_'.$type]);
	}
	return true;
}

function runquery($sql) {
	global $_G;
	$tablepre = $_G['config']['db'][1]['tablepre'];
	$dbcharset = $_G['config']['db'][1]['dbcharset'];

	$sql = str_replace([' cdb_', ' `cdb_', ' pre_', ' `pre_'], [' {tablepre}', ' `{tablepre}', ' {tablepre}', ' `{tablepre}'], $sql);
	$sql = str_replace("\r", "\n", str_replace([' {tablepre}', ' `{tablepre}'], [' '.$tablepre, ' `'.$tablepre], $sql));

	$ret = [];
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(str_starts_with($query, 'CREATE TABLE')) {
				$name = preg_replace('/CREATE TABLE ([a-z0-9_]+) .*/is', "\\1", $query);
				DB::query(createtable($query, $dbcharset));

			} else {
				DB::query($query);
			}

		}
	}
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace('/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU', "\\2", $sql));
	$defaultengine = strtolower(getglobal('config/db/common/engine')) !== 'innodb' ? 'MyISAM' : 'InnoDB';
	$type = in_array($type, ['INNODB', 'MYISAM', 'HEAP', 'MEMORY']) ? $type : $defaultengine;
	return preg_replace('/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU', "\\1", $sql)." ENGINE=$type DEFAULT CHARSET=".getglobal('config/db/1/dbcharset').(getglobal('config/db/1/dbcharset') === 'utf8mb4' ? ' COLLATE=utf8mb4_unicode_ci' : '');
}

function updatetable($sql) {
	global $_G;

	$config = [
		'dbcharset' => $_G['config']['db']['1']['dbcharset'],
		'charset' => $_G['config']['output']['charset'],
		'tablepre' => $_G['config']['db']['1']['tablepre'],
		'engine' => $_G['config']['db']['common']['engine']
	];

	preg_match_all('/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is', $sql, $matches);
	$newtables = empty($matches[1]) ? [] : $matches[1];
	$newsqls = empty($matches[0]) ? [] : $matches[0];
	if(empty($newtables) || empty($newsqls)) {
		return [1];
	}

	foreach($newtables as $i => $newtable) {
		$newcols = updatetable_getcolumn($newsqls[$i]);

		if(!$query = DB::query('SHOW CREATE TABLE '.DB::table($newtable), 'SILENT')) {
			preg_match('/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is', $newsqls[$i], $maths);

			$maths[3] = strtoupper($maths[3]);
			if($maths[3] == 'MEMORY' || $maths[3] == 'HEAP') {
				$type = ' ENGINE=MEMORY'.(empty($config['dbcharset']) ? '' : " DEFAULT CHARSET={$config['dbcharset']}");
			} else {
				$engine = $config['engine'] !== 'innodb' ? 'MyISAM' : 'InnoDB';
				$type = ' ENGINE='.$engine.(empty($config['dbcharset']) ? '' : " DEFAULT CHARSET={$config['dbcharset']}");
			}
			$usql = $maths[1].$type;

			$usql = str_replace('CREATE TABLE IF NOT EXISTS pre_', 'CREATE TABLE IF NOT EXISTS '.$config['tablepre'], $usql);
			$usql = str_replace('CREATE TABLE pre_', 'CREATE TABLE '.$config['tablepre'], $usql);

			if(!DB::query($usql, 'SILENT')) {
				return [-1, $newtable];
			}
		} else {
			$value = DB::fetch($query);
			$oldcols = updatetable_getcolumn($value['Create Table']);

			$updates = [];
			$allfileds = array_keys($newcols);
			foreach($newcols as $key => $value) {
				if($key == 'PRIMARY') {
					if($value != $oldcols[$key]) {
						if(!empty($oldcols[$key])) {
							$usql = 'RENAME TABLE '.DB::table($newtable).' TO '.DB::table($newtable.'_bak');
							if(!DB::query($usql, 'SILENT')) {
								return [-1, $newtable];
							}
						}
						$updates[] = "ADD PRIMARY KEY $value";
					}
				} elseif($key == 'KEY') {
					foreach($value as $subkey => $subvalue) {
						if(!empty($oldcols['KEY'][$subkey])) {
							if($subvalue != $oldcols['KEY'][$subkey]) {
								$updates[] = "DROP INDEX `$subkey`";
								$updates[] = "ADD INDEX `$subkey` $subvalue";
							}
						} else {
							$updates[] = "ADD INDEX `$subkey` $subvalue";
						}
					}
				} elseif($key == 'UNIQUE') {
					foreach($value as $subkey => $subvalue) {
						if(!empty($oldcols['UNIQUE'][$subkey])) {
							if($subvalue != $oldcols['UNIQUE'][$subkey]) {
								$updates[] = "DROP INDEX `$subkey`";
								$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
							}
						} else {
							$usql = 'ALTER TABLE  '.DB::table($newtable)." DROP INDEX `$subkey`";
							DB::query($usql, 'SILENT');
							$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
						}
					}
				} else {
					if(!empty($oldcols[$key])) {
						if(strtolower($value) != strtolower($oldcols[$key])) {
							$updates[] = "CHANGE `$key` `$key` $value";
						}
					} else {
						$i = array_search($key, $allfileds);
						$fieldposition = $i > 0 ? 'AFTER `'.$allfileds[$i - 1].'`' : 'FIRST';
						$updates[] = "ADD `$key` $value $fieldposition";
					}
				}
			}

			if(!empty($updates)) {
				$usql = 'ALTER TABLE '.DB::table($newtable).' '.implode(', ', $updates);
				if(!DB::query($usql, 'SILENT')) {
					return [-1, $newtable];
				}
			}
		}
	}
	return [1];
}

function updatetable_getcolumn($creatsql) {

	$creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
	preg_match('/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is', $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = [];
	foreach($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = updatetable_remakesql($value);
		if(str_ends_with($value, ',')) $value = substr($value, 0, -1);

		$vs = explode(' ', $value);
		$cname = $vs[0];

		if($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

			$name_length = strlen($cname);
			if($cname == 'UNIQUE') $name_length = $name_length + 4;

			$subvalue = trim(substr($value, $name_length));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols[$cname][$subcname] = trim(substr($value, ($name_length + 2 + strlen($subcname))));

		} elseif($cname == 'PRIMARY') {

			$newcols[$cname] = trim(substr($value, 11));

		} else {

			$newcols[$cname] = trim(substr($value, strlen($cname)));
		}
	}
	return $newcols;
}

function updatetable_remakesql($value) {
	$value = trim(preg_replace('/\s+/', ' ', $value));
	$value = str_replace(['`', ', ', ' ,', '( ', ' )', 'mediumtext'], ['', ',', ',', '(', ')', 'text'], $value);
	return $value;
}

function cron_create($pluginid, $filename = null, $name = null, $weekday = null, $day = null, $hour = null, $minute = null) {
	if(!ispluginkey($pluginid)) {
		return false;
	}
	$dir = DISCUZ_PLUGIN($pluginid).'/cron';
	if(!file_exists($dir)) {
		return false;
	}
	$crondir = dir($dir);
	while($filename = $crondir->read()) {
		if(!in_array($filename, ['.', '..']) && preg_match('/^cron\_[\w\.]+$/', $filename)) {
			$content = file_get_contents($dir.'/'.$filename);
			preg_match("/cronname\:(.+?)\n/", $content, $r);
			$name = lang('plugin/'.$pluginid, trim($r[1]));
			preg_match("/week\:(.+?)\n/", $content, $r);
			$weekday = trim($r[1]) ? intval($r[1]) : -1;
			preg_match("/day\:(.+?)\n/", $content, $r);
			$day = trim($r[1]) ? intval($r[1]) : -1;
			preg_match("/hour\:(.+?)\n/", $content, $r);
			$hour = trim($r[1]) ? intval($r[1]) : -1;
			preg_match("/minute\:(.+?)\n/", $content, $r);
			$minute = trim($r[1]) ? trim($r[1]) : 0;
			$minutenew = explode(',', $minute);
			foreach($minutenew as $key => $val) {
				$minutenew[$key] = $val = intval($val);
				if($val < 0 || $val > 59) {
					unset($minutenew[$key]);
				}
			}
			$minutenew = array_slice(array_unique($minutenew), 0, 12);
			$minutenew = implode("\t", $minutenew);
			$filename = $pluginid.':'.$filename;
			$cronid = table_common_cron::t()->get_cronid_by_filename($filename);
			if(!$cronid) {
				table_common_cron::t()->insert([
					'available' => 1,
					'type' => 'plugin',
					'name' => $name,
					'filename' => $filename,
					'weekday' => $weekday,
					'day' => $day,
					'hour' => $hour,
					'minute' => $minutenew,
				], true);
			} else {
				table_common_cron::t()->update($cronid, [
					'name' => $name,
					'weekday' => $weekday,
					'day' => $day,
					'hour' => $hour,
					'minute' => $minutenew,
				]);
			}
		}
	}
	return true;
}

function cron_delete($pluginid) {
	if(!ispluginkey($pluginid)) {
		return false;
	}
	$dir = DISCUZ_PLUGIN($pluginid).'/cron';
	if(!file_exists($dir)) {
		return false;
	}
	$crondir = dir($dir);
	$count = 0;
	while($filename = $crondir->read()) {
		if(!in_array($filename, ['.', '..']) && preg_match('/^cron\_[\w\.]+$/', $filename)) {
			$filename = $pluginid.':'.$filename;
			$cronid = table_common_cron::t()->get_cronid_by_filename($filename);
			table_common_cron::t()->delete($cronid);
			$count++;
		}
	}
	return $count;
}

function domain_create($pluginid, $domain, $domainroot) {
	$plugin = table_common_plugin::t()->fetch_by_identifier($pluginid);
	if(!$plugin || !$plugin['available']) {
		return;
	}
	table_common_domain::t()->delete_by_id_idtype($plugin['pluginid'], 'plugin');
	$data = [
		'id' => $plugin['pluginid'],
		'idtype' => 'plugin',
		'domain' => $domain,
		'domainroot' => $domainroot,
	];
	table_common_domain::t()->insert($data);
	require_once libfile('function/cache');
	updatecache('setting');
}

function domain_delete($pluginid) {
	$plugin = table_common_plugin::t()->fetch_by_identifier($pluginid);
	if(!$plugin || !$plugin['available']) {
		return;
	}
	table_common_domain::t()->delete_by_id_idtype($plugin['pluginid'], 'plugin');
	require_once libfile('function/cache');
	updatecache('setting');
}

function rewrite_rules($identifier, $rules) {
}

function rm_rewrite_rules($identifier) {
}

function set_admin_menu($topmenu_name, $menus = []) {
	global $_G;
	loadcache('admincp_menu');
	$_G['cache']['admincp_menu']['topmenu'] = $_G['cache']['admincp_menu']['topmenu'] ?? [];
	$_G['cache']['admincp_menu']['menu'] = $_G['cache']['admincp_menu']['menu'] ?? [];
	$_G['cache']['admincp_menu']['topmenu'][$topmenu_name] = '';
	$_G['cache']['admincp_menu']['menu'][$topmenu_name] = $menus;
	savecache('admincp_menu', $_G['cache']['admincp_menu']);
}


function remove_admin_menu($topmenu_names) {
	global $_G;
	if(!is_array($topmenu_names)) {
		$topmenu_names = [$topmenu_names];
	}
	loadcache('admincp_menu');
	$_G['cache']['admincp_menu']['topmenu'] = $_G['cache']['admincp_menu']['topmenu'] ?? [];
	$_G['cache']['admincp_menu']['menu'] = $_G['cache']['admincp_menu']['menu'] ?? [];
	foreach($topmenu_names as $topmenu_name) {
		foreach(['topmenu', 'menu'] as $key) {
			if(isset($_G['cache']['admincp_menu'][$key])) {
				unset($_G['cache']['admincp_menu'][$key][$topmenu_name]);
			}
		}
	}
	savecache('admincp_menu', $_G['cache']['admincp_menu']);
}

function load_installlang($pluginid) {
	global $_G;
	static $_value = [];
	if($_value[$pluginid] !== null) {
		return $_value;
	}
	$_value[$pluginid] = null;

	if(!empty($_G['cache']['pluginlanguage_install'][$pluginid])) {
		return $_value[$pluginid] = $_G['cache']['pluginlanguage_install'][$pluginid];
	}
	if(!ispluginkey($pluginid)) {
		return $_value[$pluginid] = [];
	}
	loadcache('pluginlanguage_install');
	if(!empty($_G['cache']['pluginlanguage_install'][$pluginid])) {
		return $_value[$pluginid] = $_G['cache']['pluginlanguage_install'][$pluginid];
	}
	if(file_exists($langfile = DISCUZ_PLUGIN($pluginid).'/i18n/'.currentlang().'/lang_plugin.php')) {
		$installlang = [];
		require $langfile;
		return $_value[$pluginid] = $installlang[$pluginid] ?? [];
	} elseif(file_exists($langfile = DISCUZ_DATA.'./plugindata/'.$pluginid.'.lang.php')) {
		require $langfile;
		return $_value[$pluginid] = $installlang[$pluginid] ?? [];
	} else {
		return $_value[$pluginid] = [];
	}
}

function threadtype_install($name, $fieldPrefix, $typeData, $fieldData) {
	global $_G;

	$fieldIndex = [];
	$classId = table_forum_typeoption::t()->insert(['title' => $name, 'identifier' => $fieldPrefix], 1);
	foreach($fieldData as $fieldKey => $row) {
		$row['classid'] = $classId;
		$fieldId = table_forum_typeoption::t()->insert($row, 1);
		$fieldIndex[$fieldId] = $row;
	}
	$typeId = table_forum_threadtype::t()->insert($typeData, 1);
	foreach($fieldIndex as $fieldId => $row) {
		table_forum_typevar::t()->insert(['sortid' => $typeId, 'optionid' => $fieldId, 'available' => 1, 'subjectshow' => 1]);
	}

	$fields = '';
	foreach(table_forum_typevar::t()->fetch_all_by_sortid($typeId) as $optionid => $option) {
		$identifier = $fieldIndex[$optionid]['identifier'];
		if($identifier) {
			if($fieldIndex[$optionid]['type'] == 'radio') {
				$create_tableoption_sql .= "$separator$identifier smallint(6) UNSIGNED NOT NULL DEFAULT '0'";
			} elseif(in_array($fieldIndex[$optionid]['type'], ['number', 'range'])) {
				$create_tableoption_sql .= "$separator$identifier int(10) UNSIGNED NOT NULL DEFAULT '0'";
			} elseif($fieldIndex[$optionid]['type'] == 'select') {
				$create_tableoption_sql .= "$separator$identifier varchar(50) NOT NULL";
			} else {
				$create_tableoption_sql .= "$separator$identifier mediumtext NOT NULL";
			}
			$separator = ' ,';
			if(in_array($fieldIndex[$optionid]['type'], ['radio', 'select', 'number'])) {
				$indexoption[] = $identifier;
			}
		}
	}
	$fields .= ($create_tableoption_sql ? $create_tableoption_sql.',' : '')."tid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',fid smallint(6) UNSIGNED NOT NULL DEFAULT '0',dateline int(10) UNSIGNED NOT NULL DEFAULT '0',expiration int(10) UNSIGNED NOT NULL DEFAULT '0',";
	$fields .= 'KEY (fid), KEY(dateline)';
	if($indexoption) {
		foreach($indexoption as $index) {
			$fields .= "$separator KEY $index ($index)";
			$separator = ' ,';
		}
	}
	$dbcharset = $_G['config']['db'][1]['dbcharset'];
	$dbcharset = empty($dbcharset) ? str_replace('-', '', CHARSET) : $dbcharset;

	table_forum_optionvalue::t()->create($typeId, $fields, $dbcharset);

	require_once libfile('function/cache');
	updatecache('threadsorts');
}

function threadtype_uninstall($fieldPrefix) {
	$fields = array_keys(table_forum_typeoption::t()->fetch_all_by_identifier_prefix($fieldPrefix));
	$sortids = [];
	foreach(table_forum_typevar::t()->fetch_all_by_optionid($fields) as $row) {
		$sortids[] = $row['sortid'];
		table_forum_typevar::t()->delete_typevar($row['sortid'], $row['optionid']);
	}
	foreach($sortids as $sortid) {
		table_forum_threadtype::t()->delete($sortid);
		table_forum_optionvalue::t()->drop($sortid);
	}
	foreach($fields as $field) {
		table_forum_typeoption::t()->delete($field);
	}

	require_once libfile('function/cache');
	updatecache('threadsorts');
}