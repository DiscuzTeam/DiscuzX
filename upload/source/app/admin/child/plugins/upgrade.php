<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$plugin = table_common_plugin::t()->fetch($pluginid);
$modules = dunserialize($plugin['modules']);
$dir = substr($plugin['directory'], 0, -1);

if(!$_GET['confirmed']) {
	cloudaddons_validator($dir.'.plugin');
	$file = getimportfilename(DISCUZ_PLUGIN($dir).'/discuz_plugin_'.$dir.($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : ''));
	$upgrade = false;
	if($file) {
		$importtxt = @implode('', file($file));
		$pluginarray = getimportdata('Discuz! Plugin');
		$newver = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
		$upgrade = $newver > $plugin['version'];
	}
	$entrydir = DISCUZ_PLUGIN($dir);
	$upgradestr = '';
	if(file_exists($entrydir)) {
		$d = dir($entrydir);
		while($f = $d->read()) {
			if(preg_match('/^discuz\_plugin\_'.$plugin['identifier'].'(\_\w+)?\.(xml|json)$/', $f, $a)) {
				$extratxt = $extra = substr($a[1], 1);
				if(preg_match('/^SC\_UTF8$/i', $extra)) {
					$extratxt = '&#31616;&#20307;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
				} elseif(preg_match('/^TC\_UTF8$/i', $extra)) {
					$extratxt = '&#32321;&#39636;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
				}
				if($modules['extra']['installtype'] == $extratxt) {
					continue;
				}
				$importtxt = @implode('', file($entrydir.'/'.$f));
				$pluginarray = getimportdata('Discuz! Plugin');
				$newverother = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
				$upgradestr .= $newverother > $plugin['version'] ? '<input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$pluginid.'&confirmed=yes&installtype='.rawurlencode($extra).'\'" type="button" value="'.($extra ? $extratxt : $lang['plugins_import_default']).' '.$newverother.'" />&nbsp;&nbsp;&nbsp;' : '';
			}
		}
	}
	if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
		$filename = issafepath(DISCUZ_PLUGIN().$plugin['identifier'].'/'.$pluginarray['checkfile']);
		if($filename) {
			$installlang = load_installlang($dir);
			@include $filename;
		}
	}

	if($upgrade) {

		cpmsg('plugins_config_upgrade_confirm', 'action=plugins&operation=upgrade&pluginid='.$pluginid.'&confirm=yes', 'form', ['pluginname' => $plugin['name'], 'version' => $plugin['version'], 'toversion' => $newver]);

	} elseif($upgradestr) {

		echo '<h3>'.cplang('discuz_message').'</h3><div class="infobox"><h4 class="marginbot normal">'.cplang('plugins_config_upgrade_other', ['pluginname' => $plugin['name'], 'version' => $plugin['version']]).'</h4><br /><p class="margintop">'.$upgradestr.
			'<input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins\'" type="button" value="'.$lang['cancel'].'"/></div></div>';

	} else {

		$addonid = $plugin['identifier'].'.plugin';
		$checkresult = dunserialize(cloudaddons_upgradecheck([$addonid]));

		if(is_array($checkresult) && isset($checkresult[$addonid])) {
			list($return, $newver, $sysver) = explode(':', $checkresult[$addonid]);
		} else {
			$newver = $sysver = '';
		}

		cloudaddons_installlog($pluginarray['plugin']['identifier'].'.plugin');
		dsetcookie('addoncheck_plugin', '', -1);

		cloudaddons_clear('plugin', $dir);

		if(!empty($sysver) && $sysver > $plugin['version']) {
			cpmsg('plugins_config_upgrade_new', '', 'succeed', ['newver' => $sysver, 'addonid' => $addonid]);
		} elseif(!empty($newver)) {
			cpmsg('plugins_config_upgrade_new', '', 'succeed', ['newver' => $newver, 'addonid' => $addonid]);
		} else {
			cpmsg('plugins_config_upgrade_missed', 'action=plugins', 'succeed');
		}

	}

} else {

	$installtype = !isset($_GET['installtype']) ? $modules['extra']['installtype'] : (preg_match('/^\w+$/', $_GET['installtype']) ? $_GET['installtype'] : '');
	$importfile = getimportfilename(DISCUZ_PLUGIN($dir).'/discuz_plugin_'.$dir.($installtype ? '_'.$installtype : ''));
	if(!$importfile) {
		cpmsg('plugin_file_error', '', 'error');
	}

	cloudaddons_validator($dir.'.plugin');

	$importtxt = @implode('', file($importfile));
	$pluginarray = getimportdata('Discuz! Plugin');

	if(!ispluginkey($pluginarray['plugin']['identifier']) || $pluginarray['plugin']['identifier'] != $plugin['identifier']) {
		cpmsg('plugins_edit_identifier_invalid', '', 'error');
	}
	if(is_array($pluginarray['vars'])) {
		foreach($pluginarray['vars'] as $config) {
			if(!ispluginkey($config['variable'])) {
				cpmsg('plugins_upgrade_var_invalid', '', 'error');
			}
		}
	}

	if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
		if(!empty($pluginarray['language'])) {
			$installlang[$pluginarray['plugin']['identifier']] = $pluginarray['language']['installlang'];
		}
		$filename = issafepath(DISCUZ_PLUGIN($dir).'/'.$pluginarray['checkfile']);
		if($filename) {
			loadcache('pluginlanguage_install');
			$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
			@include $filename;
		}
	}

	pluginupgrade($pluginarray, $installtype);

	if(!empty($plugin['directory']) && !empty($pluginarray['upgradefile']) && preg_match('/^[\w\.]+$/', $pluginarray['upgradefile'])) {
		dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=pluginupgrade&dir='.$dir.'&installtype='.$installtype.'&fromversion='.$plugin['version']);
	}
	$toversion = $pluginarray['plugin']['version'];

	cloudaddons_clear('plugin', $dir);

	cpmsg('plugins_upgrade_succeed', 'action=plugins', 'succeed', ['toversion' => $toversion]);

}
	