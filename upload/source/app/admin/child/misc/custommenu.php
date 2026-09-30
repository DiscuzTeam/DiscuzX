<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!$do) {

	if(!submitcheck('optionsubmit')) {
		$mpp = 10;
		$startlimit = ($page - 1) * $mpp;
		$num = table_common_admincp_cmenu::t()->count_by_uid($_G['uid']);
		$multipage = multi($num, $mpp, $page, ADMINSCRIPT.'?action=misc&operation=custommenu');
		$optionlist = $ajaxoptionlist = '';
		foreach(table_common_admincp_cmenu::t()->fetch_all_by_uid($_G['uid'], $startlimit, $mpp) as $custom) {
			$custom['url'] = rawurldecode($custom['url']);
			$optionlist .= showtablerow('', ['class="td25"', 'class="td28"', '', 'class="td26"'], [
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"{$custom['id']}\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayordernew[{$custom['id']}]\" value=\"{$custom['displayorder']}\">",
				"<input type=\"text\" class=\"txt\" size=\"25\" name=\"titlenew[{$custom['id']}]\" value=\"".cplang($custom['title'])."\"><input type=\"hidden\" name=\"langnew[{$custom['id']}]\" value=\"{$custom['title']}\">",
				"<input type=\"text\" class=\"txt\" size=\"40\" name=\"urlnew[{$custom['id']}]\" value=\"{$custom['url']}\">"
			], TRUE);
			$ajaxoptionlist .= '<li><a href="'.$custom['url'].'" target="'.(substr(rawurldecode($custom['url']), 0, 17) == ADMINSCRIPT.'?action=' ? 'main' : '_blank').'">'.cplang($custom['title']).'</a></li>';
		}

		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
			[1,'<input type="text" class="txt" name="newtitle[]" size="25">'],
			[1,'<input type="text" class="txt" name="newurl[]" size="40">', 'td26']
		]
	];
</script>
EOT;
		shownav('tools', 'nav_custommenu');
		showsubmenu('nav_custommenu');
		showformheader('misc&operation=custommenu');
		showtableheader();
		showsubtitle(['', 'display_order', 'name', 'URL']);
		echo $optionlist;
		echo '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['custommenu_add'].'</a></div></td></tr>';
		showsubmit('optionsubmit', 'submit', 'del', '', $multipage);
		showtablefooter();
		showformfooter();

	} else {

		if($ids = dimplode($_GET['delete'])) {
			table_common_admincp_cmenu::t()->delete_cmenu($_GET['delete'], $_G['uid']);
		}

		if(is_array($_GET['titlenew'])) {
			foreach($_GET['titlenew'] as $id => $title) {
				$_GET['urlnew'][$id] = rawurlencode($_GET['urlnew'][$id]);
				$title = dhtmlspecialchars($_GET['langnew'][$id] && cplang($_GET['langnew'][$id], false) ? $_GET['langnew'][$id] : $title);
				$ordernew = intval($_GET['displayordernew'][$id]);
				table_common_admincp_cmenu::t()->update($id, ['title' => $title, 'displayorder' => $ordernew, 'url' => dhtmlspecialchars($_GET['urlnew'][$id])]);
			}
		}

		if(is_array($_GET['newtitle'])) {
			foreach($_GET['newtitle'] as $k => $v) {
				$_GET['urlnew'][$k] = rawurlencode($_GET['urlnew'][$k]);
				table_common_admincp_cmenu::t()->insert([
					'title' => dhtmlspecialchars($v),
					'displayorder' => intval($_GET['newdisplayorder'][$k]),
					'url' => dhtmlspecialchars($_GET['newurl'][$k]),
					'sort' => 1,
					'uid' => $_G['uid'],
				]);
			}
		}

		updatemenu('index');
		$extra = '<script>parent.ajaxget(admincpfilename + \'?action=misc&operation=custommenu&do=show\', \'favbar_list\');</script>';
		cpmsg('custommenu_edit_succeed', 'action=misc&operation=custommenu', 'succeed', [], $extra);

	}

} elseif($do == 'add') {

	if($_GET['title'] && $_GET['url']) {
		if(($p = strpos($_GET['url'], ADMINSCRIPT)) !== false) {
			$_GET['url'] = substr($_GET['url'], $p + strlen(ADMINSCRIPT) + 1);
		}
		admincustom($_GET['title'], dhtmlspecialchars($_GET['url']), 1);
		updatemenu('index');

		if(!empty($_GET['fromFavbars'])) {
			show_custommenu();
		}

		cpmsg('custommenu_add_succeed', rawurldecode($_GET['url']), 'succeed', ['title' => cplang($_GET['title'])]);
	} else {
		cpmsg('parameters_error', '', 'error');
	}

} elseif($do == 'show') {
	show_custommenu();
} elseif($do == 'redirect') {
	if($cmenu = table_common_admincp_cmenu::t()->fetch(intval($_GET['mid']))) {
		$url = rawurldecode($cmenu['url']);
		$url = str_replace('&amp;', '&', $url);
		dheader('location: '.$url);
	} else {
		cpmsg('parameters_error', '', 'error');
	}
}

function show_custommenu() {
	$s = '';
	foreach(get_custommenu() as $row) {
		$s .= '<a href="'.ADMINSCRIPT.'?action='.$row[1].'">'.$row[0].'</a>';
	}
	require_once template('common/header_ajax');
	echo $s;
	require_once template('common/footer_ajax');
	exit;
}