<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$operation = in_array($operation, ['trash', 'tag']) ? $operation : 'list';
loadcache('portalcategory');
$category = $_G['cache']['portalcategory'];

cpheader();
shownav('portal', 'article');

$searchctrl = '';
if($operation == 'list') {
	$searchctrl = '<span style="float: right; padding-right: 40px;">'
		.'<a href="javascript:;" onclick="$(\'tb_search\').style.display=\'\';$(\'a_search_show\').style.display=\'none\';$(\'a_search_hide\').style.display=\'\';" id="a_search_show" style="display:none">'.cplang('show_search').'</a>'
		.'<a href="javascript:;" onclick="$(\'tb_search\').style.display=\'none\';$(\'a_search_show\').style.display=\'\';$(\'a_search_hide\').style.display=\'none\';" id="a_search_hide">'.cplang('hide_search').'</a>'
		.'</span>';
}

$catid = $_GET['catid'] = intval($_GET['catid']);
showsubmenu('article', [
	['list', 'article&catid='.$catid, $operation == 'list'],
	['article_trash', 'article&operation=trash&catid='.$catid, $operation == 'trash'],
	['article_add', 'portal.php?mod=portalcp&ac=article', false, 1, 1]
], $searchctrl);

$operation = $operation ? $operation : 'list';
$file = childfile('article/'.$operation);
if(!file_exists($file)) {
	cpmsg('undefined_action');
}
require_once $file;

function showcategoryrow($key, $type = '', $last = '') {
	global $category, $lang;

	$forum = $forums[$key];
	$showedforums[] = $key;

	if($last == '') {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" /></td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		$boardattr = '';
		if(!$forum['status'] || $forum['password'] || $forum['redirect']) {
			$boardattr = '<div class="boardattr">';
			$boardattr .= $forum['status'] ? '' : $lang['forums_admin_hidden'];
			$boardattr .= !$forum['password'] ? '' : ' '.$lang['forums_admin_password'];
			$boardattr .= !$forum['redirect'] ? '' : ' '.$lang['forums_admin_url'];
			$boardattr .= '</div>';
		}

		$return .= '<input type="text" class="txt" name="name['.$forum['fid'].']" value="'.dhtmlspecialchars($forum['name']).'" class="txt" />'.
			($type == '' ? '<a href="###" onclick="addrowdirect = 1;addrow(this, 2, '.$forum['fid'].')" class="addchildboard">'.$lang['forums_admin_add_sub'].'</a>' : '').
			'</div>'.$boardattr.
			'</td><td>'.showforum_moderators($forum).'</td>
			<td><a href="'.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'" title="'.$lang['forums_edit_comment'].'" class="act">'.$lang['edit'].'</a>'.
			($type != 'group' ? '<a href="'.ADMINSCRIPT.'?action=forums&operation=copy&source='.$forum['fid'].'" title="'.$lang['forums_copy_comment'].'" class="act">'.$lang['forums_copy'].'</a>' : '').
			'<a href="'.ADMINSCRIPT.'?action=forums&operation=delete&fid='.$forum['fid'].'" title="'.$lang['forums_delete_comment'].'" class="act">'.$lang['delete'].'</a></td></tr>';
	} else {
		if($last == 'lastboard') {
			$return = '<tr><td></td><td colspan="3"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.$lang['forums_admin_add_forum'].'</a></div></td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['forums_admin_add_category'].'</a></div></td></tr>';
		}
	}

	return $return;
}

