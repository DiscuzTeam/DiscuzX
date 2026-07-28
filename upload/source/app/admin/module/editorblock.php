<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
global $_G;
$root = '<a href="'.ADMINSCRIPT.'?action=editorblock">'.cplang('editorblock_admin').'</a>';

cpheader();

$file = childfile('editorblock/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}
require_once $file;

function geteditorblocks() {
	global $_G;
	$checkdirs = array_merge([''], $_G['setting']['plugins']['available']);
	$editorblocks = [];
	foreach($checkdirs as $key) {
		if($key) {
			$dir = DISCUZ_PLUGIN($key).'/editorblock';
		} else {
			$dir = DISCUZ_ROOT.'./source/class/editorblock';
		}
		if(!file_exists($dir)) {
			continue;
		}
		$editorblockdir = dir($dir);
		while($entry = $editorblockdir->read()) {
			if(!in_array($entry, ['.', '..']) && preg_match('/^editorblock\_[\w\.]+$/', $entry) && str_ends_with($entry, '.php') && strlen($entry) < 100 && is_file($dir.'/'.$entry) && empty($editorblocks[$entry])) {
				@include_once $dir.'/'.$entry;
				$editorblockclass = substr($entry, 0, -4);
				if(class_exists($editorblockclass)) {
					$editorblock = new $editorblockclass();
					$script = substr($editorblockclass, 12);
					//$script = ($key ? $key.':' : '').$script;
					$editorblocks[$entry] = [
						'class' => $script,
						'name' => lang('editorblock/'.$script, $editorblock->name),
						'available' => $editorblock->available,
						'columns' => $editorblock->columns,
						'global_css' => $editorblock->global_css,
						'version' => $editorblock->version,
						'copyright' => lang('editorblock/'.$script, $editorblock->copyright),
						'type' => $editorblock->type,
						'parser' => $editorblock->getParser(),
						'style' => $editorblock->getStyle(),
						'filename' => $editorblock->filename,
						'config' => $editorblock->getConfig(),
						'identifier' => $editorblock->identifier,
						'description' => lang('editorblock/'.$script, $editorblock->description),
						'filemtime' => @filemtime($dir.'/'.$entry),
						'plugin' => $key,
					];
				}
			}
		}
	}
	uasort($editorblocks, 'filemtimesort');
	return $editorblocks;
}

