<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/portalcp');

cpheader();
$operation = in_array($operation, ['delete', 'move', 'perm', 'add', 'edit']) ? $operation : 'list';

loadcache('portalcategory');
$portalcategory = $_G['cache']['portalcategory'];

if($operation == 'edit' || $operation == 'add') {
	require_once childfile('portalcategory/add_edit');
} else {
	$file = childfile('portalcategory/'.$operation);
	if(!file_exists($file)) {
		cpmsg('undefined_action');
	}
	require_once $file;
}

function showcategoryrow($key, $level = 0, $last = '') {
	global $_G;

	loadcache('portalcategory');
	$value = $_G['cache']['portalcategory'][$key];
	$return = '';

	include_once libfile('function/portalcp');
	$value['articles'] = category_get_num('portal', $key);
	$publish = '';
	if(empty($_G['cache']['portalcategory'][$key]['disallowpublish'])) {
		$publish = '&nbsp;<a href="portal.php?mod=portalcp&ac=article&catid='.$key.'" target="_blank">'.cplang('portalcategory_publish').'</a>';
	}
	if($level == 2) {
		$class = $last ? 'lastchildboard' : 'childboard';
		$return = '<tr class="hover" id="cat'.$value['catid'].'"><td>&nbsp;</td><td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="'.$class.'">'.
			'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
			'</div>'.
			'</td><td>'.$value['articles'].'</td>'.
			'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"' : '').' /></td>'.
			'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'&tpldirectory='.getdiydirectory($value['primaltplname']).'&from=portalcategory">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="'.ADMINSCRIPT.'?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover" id="cat'.$value['catid'].'"><td>&nbsp;</td><td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="board">'.
			'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
			'<a class="addchildboard" href="'.ADMINSCRIPT.'?action=portalcategory&operation=add&upid='.$value['catid'].'">'.cplang('portalcategory_addthirdcategory').'</a></div>'.
			'</td><td>'.$value['articles'].'</td>'.
			'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"' : '').' /></td>'.
			'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'&tpldirectory='.getdiydirectory($value['primaltplname']).'&from=portalcategory">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="'.ADMINSCRIPT.'?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr>';
		for($i = 0, $L = (is_array($value['children']) ? count($value['children']) : 0); $i < $L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 2, $i == $L - 1);
		}
	} else {
		$childrennum = is_array($_G['cache']['portalcategory'][$key]['children']) ? count($_G['cache']['portalcategory'][$key]['children']) : 0;
		$toggle = $childrennum > 25 ? ' style="display:none"' : '';
		$return = '<tbody><tr class="hover" id="cat'.$value['catid'].'"><td onclick="toggle_group(\'group_'.$value['catid'].'\')"><a id="a_group_'.$value['catid'].'" href="javascript:;">'.($toggle ? '[+]' : '[-]').'</a></td>'
			.'<td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="parentboard">'.
			'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
			'</div>'.
			'</td><td>'.$value['articles'].'</td>'.
			'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
			'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"' : '').' /></td>'.
			'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'&tpldirectory='.getdiydirectory($value['primaltplname']).'&from=portalcategory">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="'.ADMINSCRIPT.'?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr></tbody>
		<tbody id="group_'.$value['catid'].'"'.$toggle.'>';
		for($i = 0, $L = (is_array($value['children']) ? count($value['children']) : 0); $i < $L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '</tdoby><tr><td>&nbsp;</td><td colspan="9"><div class="lastboard"><a class="addtr" href="'.ADMINSCRIPT.'?action=portalcategory&operation=add&upid='.$value['catid'].'">'.cplang('portalcategory_addsubcategory').'</a></td></div>';
	}
	return $return;
}

function deleteportalcategory($ids) {
	global $_G;

	if(empty($ids)) return false;
	if(!is_array($ids) && $_G['cache']['portalcategory'][$ids]['upid'] == 0) {
		@require_once libfile('function/delete');
		deletedomain(intval($ids), 'channel');
	}
	if(!is_array($ids)) $ids = [$ids];

	require_once libfile('class/blockpermission');
	require_once libfile('class/portalcategory');
	$tplpermission = &template_permission::instance();
	$templates = [];
	foreach($ids as $id) {
		$templates[] = 'portal/list_'.$id;
		$templates[] = 'portal/view_'.$id;
	}
	$tplpermission->delete_allperm_by_tplname($templates);
	$categorypermission = &portal_category::instance();
	$categorypermission->delete_allperm_by_catid($ids);

	table_portal_category::t()->delete($ids);
	table_common_nav::t()->delete_by_type_identifier(4, $ids);

	$tpls = $defaultindex = [];
	foreach($ids as $id) {
		$defaultindex[] = $_G['cache']['portalcategory'][$id]['caturl'];
		$tpls[] = 'portal/list_'.$id;
		$tpls[] = 'portal/view_'.$id;
	}
	if(in_array($_G['setting']['defaultindex'], $defaultindex)) {
		table_common_setting::t()->update_setting('defaultindex', '');
	}
	table_common_diy_data::t()->delete($tpls, NULL);
	table_common_template_block::t()->delete_by_targettplname($tpls);

}


function makecategoryfile($dir, $catid, $domain) {
	if(!$dir) {
		return;
	}
	dmkdir(DISCUZ_ROOT_STATIC.'./'.$dir, 0777, FALSE);
	$portalcategory = getglobal('cache/portalcategory');
	$prepath = str_repeat('../', $portalcategory[$catid]['level'] + 1);
	if($portalcategory[$catid]['level']) {
		$upid = $portalcategory[$catid]['upid'];
		while($portalcategory[$upid]['upid']) {
			$upid = $portalcategory[$upid]['upid'];
		}
		$domain = $portalcategory[$upid]['domain'];
	}

	$sub_dir = $dir;
	if($sub_dir) {
		$sub_dir = str_ends_with($sub_dir, '/') ? '/'.$sub_dir : '/'.$sub_dir.'/';
	}
	$code = "<?php
chdir('$prepath');
define('SUB_DIR', '$sub_dir');
\$_GET['mod'] = 'list';
\$_GET['catid'] = '$catid';
require_once './portal.php';
?>";
	$r = file_put_contents(DISCUZ_ROOT_STATIC.$dir.'/index.php', $code);
	return $r;
}

function getportalcategoryfulldir($catid) {
	if(empty($catid)) return '';
	$portalcategory = getglobal('cache/portalcategory');
	$curdir = $portalcategory[$catid]['foldername'];
	$curdir = $curdir ? $curdir : '';
	if($catid && empty($curdir)) return FALSE;
	$upid = $portalcategory[$catid]['upid'];
	while($upid) {
		$updir = $portalcategory[$upid]['foldername'];
		if(!empty($updir)) {
			$curdir = $updir.'/'.$curdir;
		} else {
			return FALSE;
		}
		$upid = $portalcategory[$upid]['upid'];
	}
	return $curdir ? $curdir.'/' : '';
}

function delportalcategoryfolder($catid) {
	if(empty($catid)) return FALSE;
	$updatearr = [];
	$portalcategory = getglobal('cache/portalcategory');
	$children = $portalcategory[$catid]['children'];
	if($children) {
		foreach($children as $subcatid) {
			if($portalcategory[$subcatid]['foldername']) {
				$arr = delportalcategorysubfolder($subcatid);
				$updatearr = array_merge($updatearr, $arr);
			}
		}
	}

	$dir = getportalcategoryfulldir($catid);
	if(!empty($dir)) {
		unlink(DISCUZ_ROOT_STATIC.$dir.'index.html');
		unlink(DISCUZ_ROOT_STATIC.$dir.'index.php');
		rmdir(DISCUZ_ROOT_STATIC.$dir);
		$updatearr[] = $catid;
	}
	if(dimplode($updatearr)) {
		table_portal_category::t()->update($updatearr, ['foldername' => '']);
	}
}

function delportalcategorysubfolder($catid) {
	if(empty($catid)) return FALSE;
	$updatearr = [];
	$portalcategory = getglobal('cache/portalcategory');
	$children = $portalcategory[$catid]['children'];
	if($children) {
		foreach($children as $subcatid) {
			if($portalcategory[$subcatid]['foldername']) {
				$arr = delportalcategorysubfolder($subcatid);
				$updatearr = array_merge($updatearr, $arr);
			}
		}
	}

	$dir = getportalcategoryfulldir($catid);
	if(!empty($dir)) {
		unlink(DISCUZ_ROOT_STATIC.$dir.'index.html');
		unlink(DISCUZ_ROOT_STATIC.$dir.'index.php');
		rmdir(DISCUZ_ROOT_STATIC.$dir);
		$updatearr[] = $catid;
	}
	return $updatearr;
}

function remakecategoryfile($categorys) {
	if(is_array($categorys)) {
		$portalcategory = getglobal('cache/portalcategory');
		foreach($categorys as $subcatid) {
			$dir = getportalcategoryfulldir($subcatid);
			makecategoryfile($dir, $subcatid, $portalcategory[$subcatid]['domain']);
			if($portalcategory[$subcatid]['children']) {
				remakecategoryfile($portalcategory[$subcatid]['children']);
			}
		}
	}
}

function showportalprimaltemplate($pritplname, $type) {
	global $_G;
	include_once libfile('function/portalcp');
	$default_tpls = [];
	$tpls = ['./template/default:portal/'.$type => getprimaltplname('./template/default:portal/'.$type.'.htm')];
	foreach($alltemplate = table_common_template::t()->range() as $template) {
		if(($dir = dir(DISCUZ_TEMPLATE($template['directory'].'/portal/')))) {
			while(false !== ($file = $dir->read())) {
				$file = strtolower($file);
				if(in_array(fileext($file), ['htm', 'php']) && (substr($file, 0, strlen($type) + 1) == $type.'_') || (substr($file, 0, -4) == $type && $template['directory'] != './template/default')) {
					$key = $template['directory'].':portal/'.substr($file, 0, -4);
					if($_G['cache']['style_default']['tpldir'] && $_G['cache']['style_default']['tpldir'] == $template['directory']) {
						$default_tpls[$key] = getprimaltplname($template['directory'].':portal/'.$file);
					} else {
						$tpls[$key] = getprimaltplname($template['directory'].':portal/'.$file);
					}
				}
			}
		}
	}
	$tpls = array_merge($default_tpls, $tpls);

	foreach($tpls as $key => $value) {
		echo "<input name=signs[$type][".dsign($key)."] value='1' type='hidden' />";
	}

	$pritplvalue = '';
	if(empty($pritplname)) {
		$pritplhide = '';
		$pritplvalue = ' style="display:none;"';
	} else {
		$pritplhide = ' style="display:none;"';
	}
	$catetplselect = '<span'.$pritplhide.'><select id="'.$type.'select" name="'.$type.'primaltplname">';
	$selectedvalue = '';
	if($type == 'view') {
		$catetplselect .= '<option value="">'.cplang('portalcategory_inheritupsetting').'</option>';
	}
	foreach($tpls as $k => $v) {
		if($pritplname === $k) {
			$selectedvalue = $k;
			$selected = ' selected';
		} else {
			$selected = '';
		}
		$catetplselect .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
	}
	$pritplophide = !empty($pritplname) ? '' : ' style="display:none;"';
	$catetplselect .= '</select> <a href="javascript:;"'.$pritplophide.' onclick="$(\''.$type.'select\').value=\''.$selectedvalue.'\';$(\''.$type.'select\').parentNode.style.display=\'none\';$(\''.$type.'value\').style.display=\'\';">'.cplang('cancel').'</a></span>';

	if(empty($pritplname)) {
		showsetting('portalcategory_'.$type.'primaltplname', '', '', $catetplselect);
	} else {
		$tplname = getprimaltplname($pritplname.'.htm');
		$html = '<span id="'.$type.'value" '.$pritplvalue.'> '.$tplname.'<a href="javascript:;" onclick="$(\''.$type.'select\').parentNode.style.display=\'\';$(\''.$type.'value\').style.display=\'none\';"> '.cplang('modify').'</a></span>';
		showsetting('portalcategory_'.$type.'primaltplname', '', '', $catetplselect.$html);
	}
}

function remakediytemplate($primaltplname, $targettplname, $diytplname, $olddirectory) {
	global $_G;
	if(empty($targettplname)) return false;
	$tpldirectory = '';
	if(str_contains($primaltplname, ':')) {
		[$tpldirectory, $primaltplname] = explode(':', $primaltplname);
	}
	$tpldirectory = ($tpldirectory ? $tpldirectory : $_G['cache']['style_default']['tpldir']);
	$newdiydata = table_common_diy_data::t()->fetch_diy($targettplname, $tpldirectory);
	if($newdiydata) {
		return false;
	}
	$diydata = table_common_diy_data::t()->fetch_diy($targettplname, $olddirectory);
	$diycontent = empty($diydata['diycontent']) ? '' : $diydata['diycontent'];
	if($diydata) {
		table_common_diy_data::t()->update_diy($targettplname, $olddirectory, ['primaltplname' => $primaltplname, 'tpldirectory' => $tpldirectory]);
	} else {
		$diycontent = '';
		if(in_array($primaltplname, ['portal/list', 'portal/view'])) {
			$diydata = table_common_diy_data::t()->fetch_diy($targettplname, $olddirectory);
			$diycontent = empty($diydata['diycontent']) ? '' : $diydata['diycontent'];
		}
		$diyarr = [
			'targettplname' => $targettplname,
			'tpldirectory' => $tpldirectory,
			'primaltplname' => $primaltplname,
			'diycontent' => $diycontent,
			'name' => $diytplname,
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => TIMESTAMP,
		];
		table_common_diy_data::t()->insert($diyarr);
	}
	if(empty($diycontent)) {
		$file = DISCUZ_TEMPLATE($tpldirectory.'/'.$primaltplname.'.htm');
		if(!file_exists($file)) {
			$file = DISCUZ_TEMPLATE('./template/default/'.$primaltplname.'.htm');
		}
		$content = @file_get_contents($file);
		if(!$content) $content = '';
		$content = preg_replace('/\<\!\-\-\[name\](.+?)\[\/name\]\-\-\>/i', '', $content);
		file_put_contents(DISCUZ_DATA.'./diy/'.$tpldirectory.'/'.$targettplname.'.htm', $content);
	} else {
		updatediytemplate($targettplname, $tpldirectory);
	}
	return true;
}

function getparentviewprimaltplname($catid, $upid = 0) {
	global $_G;
	if($upid) {
		return $_G['cache']['portalcategory'][$upid]['articleprimaltplname'];
	}
	$tpl = 'view';
	if(empty($catid)) {
		return $tpl;
	}
	$cat = $_G['cache']['portalcategory'][$catid];
	if(!empty($cat['upid']['articleprimaltplname'])) {
		$tpl = $cat['upid']['articleprimaltplname'];
	} else {
		$cat = $_G['cache']['portalcategory'][$cat['upid']];
		if($cat && $cat['articleprimaltplname']) {
			$tpl = $cat['articleprimaltplname'];
		}
	}
	return $tpl;
}

