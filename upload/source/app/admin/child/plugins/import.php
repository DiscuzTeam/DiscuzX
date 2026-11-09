<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(submitcheck('importsubmit') || isset($_GET['dir'])) {
	if(isset($_GET['dir']) && (empty($_GET['formhash']) || FORMHASH != $_GET['formhash'])) {
		cpmsg('undefined_action');
	}
	if(!is_dir(DISCUZ_PLUGIN($_GET['dir']))) {
		echo '<script type="text/javascript">top.location.href=\''.ADMINSCRIPT.'?action=cloudaddons&frame=no&id='.$_GET['dir'].'.plugin&from=recommendaddon\';</script>';
		exit;
	}
	if(!isset($_GET['installtype'])) {
		cloudaddons_validator($_GET['dir'].'.plugin');
		$pdir = DISCUZ_PLUGIN($_GET['dir']);
		$d = dir($pdir);
		$xmls = '';
		$count = 0;
		$noextra = false;
		$currentlang = currentlang();
		$urls = [];
		while($f = $d->read()) {
			if(preg_match('/^discuz\_plugin_'.$_GET['dir'].'(\_\w+)?\.(xml|json)$/', $f, $a)) {
				$extratxt = $extra = substr($a[1], 1);
				if($extra) {
					if($currentlang && $currentlang == $extra) {
						dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$_GET['dir'].'&formhash='.FORMHASH.'&installtype='.rawurlencode($extra));
					}
				} else {
					$noextra = true;
				}
				$url = ADMINSCRIPT.'?action=plugins&operation=import&dir='.$_GET['dir'].'&formhash='.FORMHASH.'&installtype='.rawurlencode($extra);
				if(!empty($urls[$url])) {
					continue;
				}
				$urls[$url] = true;
				$xmls .= '&nbsp;<input type="button" class="btn" onclick="location.href=\''.$url.'\'" value="'.($extra ? $extratxt : $lang['plugins_import_default']).'">&nbsp;';
				$count++;
			}
		}
		if($count == 1 && $noextra) {
			dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$_GET['dir'].'&formhash='.FORMHASH.'&installtype=');
		}
		$xmls .= '<br /><br /><input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins\'" type="button" value="'.$lang['cancel'].'"/>';
		echo '<div class="infobox"><h4 class="infotitle2">'.$lang['plugins_import_installtype_1'].' '.$_GET['dir'].' '.$lang['plugins_import_installtype_2'].' '.$count.' '.$lang['plugins_import_installtype_3'].'</h4>'.$xmls.'</div>';
		exit;
	} else {
		$installtype = $_GET['installtype'];
		$dir = $_GET['dir'];
		$license = $_GET['license'];
		$extra = $installtype ? '_'.$installtype : '';
		$importfile = getimportfilename(DISCUZ_PLUGIN($dir).'/discuz_plugin_'.$dir.$extra);
		if(!$importfile) {
			cpmsg('plugin_file_error', 'action=plugins', 'error');
		}
		$importtxt = @implode('', file($importfile));
		$pluginarray = getimportdata('Discuz! Plugin');
		if(empty($license) && $pluginarray['license']) {
			require_once libfile('function/discuzcode');
			$pluginarray['license'] = discuzcode(strip_tags($pluginarray['license']), 1, 0);
			echo '<div class="infobox"><h4 class="infotitle2">'.$pluginarray['plugin']['name'].' '.$pluginarray['plugin']['version'].' '.$lang['plugins_import_license'].'</h4><div style="text-align:left;line-height:25px;">'.$pluginarray['license'].'</div><br /><br /><center>'.
				'<button onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$dir.'&formhash='.FORMHASH.'&installtype='.$installtype.'&license=yes\'">'.$lang['plugins_import_agree'].'</button>&nbsp;&nbsp;'.
				'<button onclick="location.href=\''.ADMINSCRIPT.'?action=plugins\'">'.$lang['plugins_import_pass'].'</button></center></div>';
			exit;
		}
		$addonid = $dir.'.plugin';
		$array = cloudaddons_getmd5($addonid);
		if(cloudaddons_open('&mod=app&ac=validator&ver=2&addonid='.$addonid.($array !== false ? '&rid='.$array['RevisionID'].'&sn='.$array['SN'].'&rd='.$array['RevisionDateline'] : '')) === '0') {
			cpmsg('c'.'lou'.'dad'.'dons'.'_genu'.'ine_m'.'essa'.'ge', '', 'error', ['addonid' => $addonid]);
		}
	}

	if(!ispluginkey($pluginarray['plugin']['identifier'])) {
		cpmsg('plugins_edit_identifier_invalid', 'action=plugins', 'error');
	}
	if(is_array($pluginarray['vars'])) {
		foreach($pluginarray['vars'] as $config) {
			if(!ispluginkey($config['variable'])) {
				cpmsg('plugins_import_var_invalid', 'action=plugins', 'error');
			}
		}
	}

	$plugin = table_common_plugin::t()->fetch_by_identifier($pluginarray['plugin']['identifier']);
	if($plugin) {
		cpmsg('plugins_import_identifier_duplicated', 'action=plugins', 'error', ['plugin_name' => $plugin['name']]);
	}

	if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
		$filename = issafepath(DISCUZ_PLUGIN().$_GET['dir'].'/'.$pluginarray['checkfile']);
		if($filename) {
			$installlang = load_installlang($dir);
			@include $filename;
		}
	}

	if(!versioncompatible($pluginarray['version'])) {
		cpmsg('plugins_import_version_invalid', 'action=plugins', 'error', ['cur_version' => $pluginarray['version'], 'set_version' => $_G['setting']['version']]);
	}

	$pluginid = plugininstall($pluginarray, $installtype);

	updatemenu('plugin');

	if(!empty($dir) && !empty($pluginarray['installfile']) && preg_match('/^[\w\.]+$/', $pluginarray['installfile'])) {
		dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=plugininstall&dir='.$dir.'&installtype='.$installtype.'&pluginid='.$pluginid);
	}

	cloudaddons_clear('plugin', $dir);

	if(!empty($dir)) {
		cpmsg('plugins_install_succeed', 'action=plugins&hl='.$pluginid, 'succeed');
	} else {
		cpmsg('plugins_import_succeed', 'action=plugins&hl='.$pluginid, 'succeed');
	}

}
	