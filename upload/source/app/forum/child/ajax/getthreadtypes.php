<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include template('common/header_ajax');
if(empty($_GET['selectname'])) $_GET['selectname'] = 'threadtypeid';
echo '<select name="'.dhtmlspecialchars($_GET['selectname'], ENT_QUOTES).'" '.($_GET['selectclass'] ? 'class="'.dhtmlspecialchars($_GET['selectclass'], ENT_QUOTES).'"' : '').'>';
if(!empty($_G['forum']['threadtypes']['types'])) {
	if(!$_G['forum']['threadtypes']['required']) {
		echo '<option value="0"></option>';
	}
	foreach($_G['forum']['threadtypes']['types'] as $typeid => $typename) {
		if($_G['forum']['threadtypes']['moderators'][$typeid] && $_G['forum'] && !$_G['forum']['ismoderator']) {
			continue;
		}
		echo '<option value="'.$typeid.'">'.$typename.'</option>';
	}
} else {
	echo '<option value="0" /></option>';
}
echo '</select>';
include template('common/footer_ajax');
	