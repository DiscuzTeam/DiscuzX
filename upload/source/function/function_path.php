<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') && !defined('IN_API')) {
	exit('Access Denied');
}
const PLUGIN_ROOT = './source/plugin/';
const TEMPLATE_ROOT = './template/';

function DISCUZ_ROOT() {
	if(defined('DISCUZ_ROOT')) {
		return DISCUZ_ROOT;
	} else {
		return substr(dirname(__FILE__), 0, -15);
	}
}

function MITFRAME_APP($app = '') {
	return DISCUZ_ROOT.'./source/app'.($app !== '' ? '/'.$app : '');
}

function DISCUZ_PLUGIN($plugin = '') {
	$key = $plugin;
	$rootPath = DISCUZ_ROOT();
	if($plugin === '') {
		return $rootPath.PLUGIN_ROOT;
	}
	if(str_contains($plugin, '..')) {
		return '';
	}
	static $cache = [];
	if(isset($cache[$key])) {
		return $cache[$key];
	}
	$pathAppend = '';
	if(($p = strpos($plugin, '/')) !== false) {
		$pathAppend = substr($plugin, $p);
		$plugin = substr($plugin, 0, $p);
	}
	$path = PLUGIN_ROOT.$plugin;
	$d = $rootPath.$path;
	if(!is_dir($d)) {
		return $cache[$key] = '';
	}
	return $cache[$key] = $rootPath.$path.$pathAppend;
}

function DISCUZ_TEMPLATE($template = '') {
	$key = $template;
	$rootPath = DISCUZ_ROOT();
	if($template === '') {
		return $rootPath.TEMPLATE_ROOT;
	}
	if(str_contains($template, '..')) {
		return '';
	}
	static $cache = [];
	if(isset($cache[$key])) {
		return $cache[$key];
	}
	if(str_starts_with($template, 'data/diy/')) {
		return $rootPath.$template;
	}
	$template = str_replace('././', './', $template);
	if(str_starts_with($template, './template/')) {
		$template = str_replace('./template/', '', $template);
	}
	if(str_starts_with($template, $rootPath)) {
		$template = str_replace($rootPath, '', $template);
	}
	if(str_starts_with($template, PLUGIN_ROOT)) {
		return $cache[$key] = $rootPath.$template;
	}
	$pathAppend = '';
	if(($p = strpos($template, '/')) !== false) {
		$pathAppend = substr($template, $p);
		$template = substr($template, 0, $p);
	}
	$path = TEMPLATE_ROOT.$template;
	$d = $rootPath.$path;
	if(!file_exists($d)) {
		return $cache[$key] = '';
	}
	return $cache[$key] = $rootPath.$path.$pathAppend;
}