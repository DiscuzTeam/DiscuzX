<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation == 'delete' ? 'delete' : 'list';

loadcache('blogcategory');
$category = $_G['cache']['blogcategory'];

$file = childfile('blogcategory/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}
require_once $file;

function showcategoryrow($key, $level = 0, $last = '') {
	global $_G;

	loadcache('blogcategory');
	$value = $_G['cache']['blogcategory'][$key];
	$return = '';

	include_once libfile('function/portalcp');
	$value['num'] = category_get_num('blog', $key);
	if($level == 2) {
		$class = $last ? 'lastchildboard' : 'childboard';
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="'.$class.'">'.
			'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
			'</div>'.
			'</td><td>'.$value['num'].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="board">'.
			'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
			'<a class="addchildboard" onclick="addrowdirect = 1;addrow(this, 2, '.$value['catid'].')" href="###">'.cplang('blogcategory_addthirdcategory').'</a></div>'.
			'</td><td>'.$value['num'].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
		for($i = 0, $L = (is_array($value['children']) ? count($value['children']) : 0); $i < $L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 2, $i == $L - 1);
		}
	} else {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="parentboard">'.
			'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
			'</div>'.
			'</td><td>'.$value['num'].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
		for($i = 0, $L = (is_array($value['children']) ? count($value['children']) : 0); $i < $L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '<tr><td class="td25"></td><td colspan="3"><div class="lastboard"><a class="addtr" onclick="addrow(this, 1, '.$value['catid'].')" href="###">'.cplang('blogcategory_addsubcategory').'</a></div>';
	}
	return $return;
}


