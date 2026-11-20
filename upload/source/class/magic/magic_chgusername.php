<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_chgusername {

	var $version = '1.0';
	var $name = 'chgusername_name';
	var $description = 'chgusername_desc';
	var $price = '10';
	var $weight = '10';
	var $useevent = 1;
	var $targetgroupperm = true;
	var $copyright = '<a href="https://www.discuz.vip" target="_blank">Discuz! Community Team</a>';
	var $magic = [];
	var $parameters = [];

	function getsetting(&$magic) {
	}

	function setsetting(&$magicnew, &$parameters) {
	}

	function usesubmit() {
		global $_G;
		if(empty($_GET['newusername'])) {
			showmessage(lang('magic/chgusername', 'chgusername_info_nonexistence'));
		}

		$censorexp = '/^('.str_replace(['\\*', "\r\n", ' '], ['.*', '|', ''], preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';

		if($_G['setting']['censoruser'] && @preg_match($censorexp, $_GET['newusername'])) {
			showmessage(lang('magic/chgusername', 'chgusername_name_badword'));
		}

		loaducenter();
		uc_user_chgusername($_G['uid'], addslashes(trim($_GET['newusername'])));

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'uid', $_G['uid']);

		showmessage(lang('magic/chgusername', 'chgusername_change_success'), '', '', ['alert' => 'info', 'showdialog' => 1]);
	}

	function show() {
		magicshowtype('top');
		magicshowsetting(lang('magic/chgusername', 'chgusername_newusername'), 'newusername', '', 'text');
		magicshowtype('bottom');
	}

}

