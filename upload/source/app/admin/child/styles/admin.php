<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$sarray = $tpldirs = $addonids = [];
foreach(table_common_style::t()->fetch_all_data(true) as $row) {
	if(preg_match('/^.?\/template\/([a-z]+[a-z0-9_]*)$/', $row['directory'], $a) && $a[1] != 'default') {
		$addonids[$row['styleid']] = $a[1].'.template';
	}
	$sarray[$row['styleid']] = $row;
	$tpldirs[] = realpath(DISCUZ_TEMPLATE($row['directory']));
}

$defaultid = table_common_setting::t()->fetch_setting('styleid');
$defaultid1 = table_common_setting::t()->fetch_setting('styleid1');
$defaultid2 = table_common_setting::t()->fetch_setting('styleid2');
$defaultid3 = table_common_setting::t()->fetch_setting('styleid3');

if(!submitcheck('stylesubmit')) {
	require_once libfile('function/importdata');

	$narray = [];

	$dir = DISCUZ_TEMPLATE();
	$templatedir = dir($dir);
	$i = -1;
	while($entry = $templatedir->read()) {
		$tpldir = realpath($dir.'/'.$entry);
		if(!in_array($entry, ['.', '..']) && !in_array($tpldir, $tpldirs) && is_dir($tpldir)) {
			$styleexist = 0;
			$stylearrays = import_styles(dir: $entry, returnonly: true);
			if(!empty($stylearrays[0])) {
				$styleexist += count($stylearrays);
			}
			if($styleexist) {
				$narray[$i] = [
					'styleid' => '',
					'available' => '',
					'directory' => './template/'.$entry,
					'name' => $entry,
					'tplname' => $stylearrays[0]['name'] ?? $entry,
					'filemtime' => @filemtime($dir.'/'.$entry),
					'stylecount' => $styleexist,
					'desc' => $stylearrays[0]['desc'] ?? '',
				];
				$i--;
			}
		}
	}

	uasort($narray, 'filemtimesort');
	$recommendaddon = dunserialize($_G['setting']['cloudaddons_recommendaddon']);
	if(empty($recommendaddon['updatetime']) || abs($_G['timestamp'] - $recommendaddon['updatetime']) > 7200 || (isset($_GET['checknew']) && $_G['formhash'] == $_GET['formhash'])) {
		$update_recommendaddon = true;
	}
	if(!empty($recommendaddon['templates']) && is_array($recommendaddon['templates'])) {
		$count = 0;
		foreach($recommendaddon['templates'] as $key => $value) {
			if(!empty($value['identifier']) && !is_dir($dir.'/'.$value['identifier'])) {
				$narray[$i] = [
					'styleid' => '',
					'available' => '',
					'name' => diconv($value['name'], 'utf-8', CHARSET),
					'directory' => './template/'.$value['identifier'],
					'tplname' => diconv($value['tplname'], 'utf-8', CHARSET),
					'filemtime' => $value['updatetime'],
					'stylecount' => $value['stylecount'],
					'down' => 1,
				];
				$i--;
				$count++;
				if(!empty($recommendaddon['templateshownum']) && $count >= $recommendaddon['templateshownum']) {
					break;
				}
			}
		}
	}
	$sarray += $narray;

	$stylelist = '';
	$updatestring = [];
	foreach($sarray as $id => $style) {
		$style['name'] = dhtmlspecialchars($style['name']);
		$isdefault = $id == $defaultid || $id == 1 ? 'checked' : '';
		$isdefault1 = $id == $defaultid1 ? 'checked' : '';
		$isdefault2 = $id == $defaultid2 ? 'checked' : '';
		$isdefault3 = $id == $defaultid3 ? 'checked' : '';
		$d2exists = file_exists(DISCUZ_TEMPLATE($style['directory']).'/touch');
		$d3exists = file_exists(DISCUZ_TEMPLATE($style['directory']).'/admin');
		$available = $style['available'] ? 'checked' : NULL;
		$_edir = explode('/', $style['directory']);
		$identifier = end($_edir);
		$preview = file_exists(DISCUZ_TEMPLATE($style['directory']).'/preview.jpg') ? $style['directory'].'/preview.jpg' : cloudaddons_pluginlogo_url($identifier, 'template');
		$previewlarge = file_exists(DISCUZ_TEMPLATE($style['directory']).'/preview_large.jpg') ? $style['directory'].'/preview_large.jpg' : cloudaddons_pluginlogo_url($identifier, 'template');
		$styleicons = $styleicons[$id] ?? '';
		if($addonids[$style['styleid']]) {
			if(!isset($updatestring[$addonids[$style['styleid']]])) {
				$updatestring[$addonids[$style['styleid']]] = "<p id=\"update_".$addonids[$style['styleid']]."\"></p>";
			} else {
				$updatestring[$addonids[$style['styleid']]] = '';
			}
		}
		require template('admin/style_list');
		$stylelist .= $row;
	}

	if(empty($_G['cookie']['addoncheck_template'])) {
		$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
		savecache('addoncheck_template', $checkresult);
		dsetcookie('addoncheck_template', 1, 3600);
	} else {
		loadcache('addoncheck_template');
		$checkresult = $_G['cache']['addoncheck_template'];
	}

	$updatecount = 0;
	$newvers = '';
	foreach($checkresult as $addonid => $value) {
		list($return, $newver) = explode(':', $value);
		if($newver) {
			$updatecount++;
			$newvers .= "if($('update_$addonid')) $('update_$addonid').innerHTML=' <a href=\"".ADMINSCRIPT."?action=cloudaddons&frame=no&id=$addonid&from=newver\" target=\"_blank\"><font color=\"red\">".cplang('styles_find_newversion')." $newver</font></a>';";
		}
	}

	shownav('template', 'styles_list');
	showsubmenu('styles_admin', [
		['styles_list', 'styles', 1],
		['styles_import', 'styles&operation=import', 0],
		$isfounder ? ['plugins_validator'.($updatecount ? '_new' : ''), 'styles&operation=upgradecheck', 0] : [],
		$isfounder ? ['cloudaddons_style_link', 'cloudaddons&frame=no&operation=templates&from=more', 0, 1] : [],
	], '<a href="https://www.dismall.com/?from=templates_question" target="_blank" class="rlink">'.$lang['templates_question'].'</a>', ['updatecount' => $updatecount]);
	showtips('styles_home_tips');
	showformheader('styles');
	showhiddenfields(['updatecsscache' => 0]);
	showboxheader('', 'nobottom');
	echo $stylelist;
	showboxfooter();
	showtableheader();
	showsubmit('stylesubmit', 'submit', 'del', '<input onclick="this.form.updatecsscache.value=1" type="submit" class="btn" name="stylesubmit" value="'.cplang('styles_csscache_update').'">'.($isfounder ? '&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=cloudaddons&frame=no&operation=templates&from=more" target="_blank">'.cplang('cloudaddons_style_link').'</a>' : ''));
	showtablefooter();
	showformfooter();
	if($newvers) {
		echo '<script type="text/javascript">'.$newvers.'</script>';
	}
	if($update_recommendaddon) {
		echo '<script type="text/javascript" src="'.ADMINSCRIPT.'?action=misc&operation=recommendupdate"></script>';
	}

} else {

	if($_GET['updatecsscache']) {
		updatecache(['setting', 'styles']);
		loadcache('style_default', true);
		updatecache('updatediytemplate');
		$tpl = dir(DISCUZ_TEMPLATE());
		while($entry = $tpl->read()) {
			if(preg_match('/\.tpl\.php$/', $entry)) {
				@unlink(DISCUZ_DATA.'./template/'.$entry);
			}
		}
		$tpl->close();
		cpmsg('csscache_update', 'action=styles', 'succeed');
	} else {
		$defaultids = [];
		$dfids = ['', '1', '2', '3'];
		foreach($dfids as $dfid) {
			$defaultnew = $_GET['defaultnew'.$dfid];
			if(is_numeric($defaultnew) && isset($sarray[$defaultnew])) {
				if(!in_array($defaultnew, $defaultids)) {
					if(basename($sarray[$defaultnew]['directory']) != 'default' && ispluginkey(basename($sarray[$defaultnew]['directory']))) {
						cpheader();
						$addonid = basename($sarray[$defaultnew]['directory']).'.template';
						$array = cloudaddons_getmd5($addonid);
						if(cloudaddons_open('&mod=app&ac=validator&ver=2&addonid='.$addonid.($array !== false ? '&rid='.$array['RevisionID'].'&sn='.$array['SN'].'&rd='.$array['RevisionDateline'] : '')) === '0') {
							cpmsg('clo'.'uda'.'ddon'.'s_gen'.'uine_'.'mes'.'sage', '', 'error', ['addonid' => $addonid]);
						}
					}
					$defaultids[] = $defaultnew;
				}
				table_common_setting::t()->update_setting('styleid'.$dfid, $defaultnew);
			}
		}

		if(isset($_GET['namenew'])) {
			foreach($sarray as $id => $old) {
				$namenew[$id] = trim($_GET['namenew'][$id]);
				if($namenew[$id] != $old['name']) {
					table_common_style::t()->update($id, ['name' => $namenew[$id]]);
				}
			}
		}

		$delete = $_GET['delete'];
		if(!empty($delete) && is_array($delete)) {
			$did = [];
			foreach($delete as $id) {
				$id = intval($id);
				if(in_array($id, $defaultids)) {
					cpmsg('styles_delete_invalid', '', 'error');
				} elseif($id != 1) {
					$did[] = intval($id);
				}
			}
			if($did) {
				$tplids = [];
				foreach(table_common_style::t()->fetch_all_data() as $style) {
					$tplids[$style['templateid']] = $style['templateid'];
				}
				table_common_style::t()->delete($did);
				table_common_stylevar::t()->delete_by_styleid($did);
				table_forum_forum::t()->update_styleid($did);
				foreach(table_common_style::t()->fetch_all_data() as $style) {
					unset($tplids[$style['templateid']]);
				}
				if($tplids) {
					foreach(table_common_template::t()->fetch_all($tplids) as $tpl) {
						cloudaddons_uninstall(basename($tpl['directory']).'.template', $tpl['directory']);
						table_common_syscache::t()->delete_syscache('tplfile_'.$tpl['templateid']);
					}
					table_common_template::t()->delete_tpl($tplids);
				}
			}
		}

		if($_GET['newname']) {
			$styleidnew = table_common_style::t()->insert(['name' => $_GET['newname'], 'templateid' => 1], true);
			foreach(array_keys($predefinedvars) as $variable) {
				$substitute = $predefinedvars[$variable][2] ?? '';
				table_common_stylevar::t()->insert(['styleid' => $styleidnew, 'variable' => $variable, 'substitute' => $substitute]);
			}
		}

		updatecache(['setting', 'styles']);
		loadcache('style_default', true);
		updatecache('updatediytemplate');
		cpmsg('styles_edit_succeed', 'action=styles', 'succeed');
	}

}
	