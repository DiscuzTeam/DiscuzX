<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$operation = in_array($operation, ['all', 'index', 'category', 'article', 'topic', 'aids', 'catids', 'topicids', 'makehtmlsetting', 'cleanhtml']) ? $operation : 'makehtmlsetting';

cpheader();
shownav('portal', 'nav_makehtml');

$css = '<style>
		#mk_result {width:100%; margin-top:10px; border: 1px solid #ccc; margin: 0 auto; font-size:16px; text-align:center; display:none; }
		#mk_article, #mk_category, #mk_index{ line-height:30px;}
		#progress_bar{ width:400px; height:25px; border:1px solid #09f; margin: 10px auto 0; display:none;}
		.mk_msg{ width:100%; line-height:120px;}
		</style>';

$result = '<tr><td colspan="15"><div id="mk_result">
			<div id="progress_bar"></div>
			<div id="mk_topic" mktitle="'.$lang['makehtml_topic'].'"></div>
			<div id="mk_article" mktitle="'.$lang['makehtml_article'].'"></div>
			<div id="mk_category" mktitle="'.$lang['makehtml_category'].'"></div>
			<div id="mk_index" mktitle="'.$lang['makehtml_index'].'"></div>
			</div></td></tr>';

if(!in_array($operation, ['aids', 'catids', 'topicids'])) {
	$_nav = [];
	if(!empty($_G['setting']['makehtml']['flag'])) {
		$_nav = [
			['makehtml_createall', 'makehtml&operation=all', $operation == 'all'],
			['makehtml_createindex', 'makehtml&operation=index', $operation == 'index'],
			['makehtml_createcategory', 'makehtml&operation=category', $operation == 'category'],
			['makehtml_createarticle', 'makehtml&operation=article', $operation == 'article'],
			['makehtml_createtopic', 'makehtml&operation=topic', $operation == 'topic']
		];
	}
	$_nav[] = ['config', 'makehtml&operation=makehtmlsetting', $operation == 'makehtmlsetting'];
	if(empty($_G['setting']['makehtml']['flag'])) {
		$_nav[] = ['makehtml_clear', 'makehtml&operation=cleanhtml', $operation == 'cleanhtml'];
	}
	showsubmenu('html', $_nav, '');
}

$file = childfile('makehtml/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}

require_once $file;

function mk_format_category($catids) {
	global $_G, $selectdata;
	foreach($catids as $catid) {
		if(!isset($selectdata[1][$catid])) {
			$cate = $_G['cache']['portalcategory'][$catid];
			if($cate['level'] == 0) {
				$selectdata[1][$catid] = [$catid, $cate['catname']];
				mk_format_category($cate['children']);
			} elseif($cate['level'] == 1) {
				$selectdata[1][$catid] = [$catid, '&nbsp;&nbsp;&nbsp;'.$cate['catname']];
				mk_format_category($cate['children']);
			} elseif($cate['level'] == 2) {
				$selectdata[1][$catid] = [$catid, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$cate['catname']];
			}
		}
	}
}

function drmdir($dir, $fileext = 'html') {
	if($dir === '.' || $dir === '..' || str_contains($dir, '..')) {
		return false;
	}
	if(str_ends_with($dir, '/')) {
		$dir = substr($dir, 0, -1);
	}
	if(!file_exists($dir) || !is_dir($dir)) {
		return false;
	} elseif(!is_readable($dir)) {
		return false;
	} else {
		if(($dirobj = dir($dir))) {
			while(false !== ($file = $dirobj->read())) {
				if($file != '.' && $file != '..') {
					$path = $dirobj->path.'/'.$file;
					if(is_dir($path)) {
						drmdir($path);
					} elseif(fileext($path) === $fileext) {
						echo $path, '<br>';
						unlink($path);
					}
				}
			}
			$dirobj->close();
		}
		rmdir($dir);
		return true;
	}
	return false;
}

function check_son_folder($file, $cat) {
	global $_G;
	$category = $_G['cache']['portalcategory'];
	if(!empty($cat['children'])) {
		foreach($cat['children'] as $catid) {
			if($category[$catid]['upid'] == $cat['catid'] && $category[$catid]['foldername'] == $file) {
				return true;
			}
		}
	}
	return false;
}

function check_html_dir($dir) {
	$dir = str_replace("\\", '/', $dir);
	list($first) = explode('/', $dir);
	if(in_array(strtolower($first), ['uc_server', 'uc_client', 'template', 'static', 'source', 'm', 'install', 'data', 'config', 'api', 'archiver'], true)) {
		return false;
	}
	return true;
}

