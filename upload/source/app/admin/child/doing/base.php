<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$setting = table_common_setting::t()->fetch_all_setting(null);

if(submitcheck('settingsubmit')) {
	$settingnew = $_GET['settingnew'];
	$settings = [];
	foreach($settingnew as $key => $val) {
		if(in_array($key, ['siteuniqueid', 'my_sitekey', 'my_siteid'])) {
			continue;
		}
		if($setting[$key] != $val) {
			$updatecache = TRUE;
			if(in_array($key, ['defaultforumid', 'pollforumid', 'tradeforumid', 'rewardforumid', 'activityforumid', 'debateforumid',
				'doingimgmaxnum', 'doingimgmaxsize', 'doingvideoallow', 'doingvideomaxsize'])) {
				$val = (float)$val;
			}
			$settings[$key] = $val;
		}
	}

	if($settings) {
		table_common_setting::t()->update_batch($settings);
	}
	if($updatecache) {
		updatecache('setting');
	}
	cpmsg('setting_update_succeed', 'action=doing&operation='.$operation.(!empty($from) ? '&from='.$from : ''), 'succeed');
}else{
	shownav('topic', 'nav_doing');
	showsubmenu('nav_doing', [
		['setting_home_base', 'doing&operation=base', true],
		['newlist', 'doing&operation=list', false],
		['search', 'doing&operation=list&search=true', false],
	]);

	showformheader('doing&operation=base&edit=yes', 'enctype');
	showhiddenfields(['operation' => $operation]);

	require_once libfile('function/forumlist');

	/*search={"setting_doing":"action=doing&operation=base","setting_doing_base":"action=doing&operation=base"}*/
	showtableheader('', 'nobottom', 'id="base"');
	showsetting('setting_home_base_default_doing', 'settingnew[defaultdoing]', $setting['defaultdoing'], 'textarea');
	loadcache('forums');
	$threadtype = ['0' => 'default', '1' => 'poll', '2' => 'trade', '3' => 'reward', '4' => 'activity', '5' => 'debate'];
	$oldforums = $_G['cache']['forums'];
	foreach($threadtype as $special => $key) {
		if($special == 0) {
			$fields = table_forum_forumfield::t()->fetch_all_by_fid(array_keys($_G['cache']['forums']));
			foreach($fields as $fid => $field) {
				if(!empty($field['threadsorts'])) {
					unset($_G['cache']['forums'][$fid]);
				}
			}
		} else {
			$_G['cache']['forums'] = $oldforums;
		}
		$forumselect = "<select name=\"%s\">\n<option value=\"\">&nbsp;&nbsp;> ".cplang('select').'</option>'.str_replace('%', '%%', forumselect(FALSE, 0, $setting[$key.'forumid'], TRUE, FALSE, $special)).'</select>';
		showsetting('setting_home_base_default_'.$key.'_forum', "settingnew[{$key}forumid]", $setting[$key.'forumid'], sprintf($forumselect, "settingnew[{$key}forumid]"));
	}
	showsetting('setting_doing_dynamic_fname', 'settingnew[doing_dynamic_fname]', $setting['doing_dynamic_fname'], 'radio');

	// 记录图片/视频上传限制设置，未保存过设置时显示默认值
	$setting['doingimgmaxnum'] = isset($setting['doingimgmaxnum']) ? $setting['doingimgmaxnum'] : 9;
	$setting['doingimgmaxsize'] = isset($setting['doingimgmaxsize']) ? $setting['doingimgmaxsize'] : 2048;
	$setting['doingvideoallow'] = isset($setting['doingvideoallow']) ? $setting['doingvideoallow'] : 1;
	$setting['doingvideomaxsize'] = isset($setting['doingvideomaxsize']) ? $setting['doingvideomaxsize'] : 50;
	$setting['doingvideoext'] = isset($setting['doingvideoext']) && $setting['doingvideoext'] !== '' ? $setting['doingvideoext'] : 'mp4,webm,mov';
	showsetting('setting_doing_imgmaxnum', 'settingnew[doingimgmaxnum]', $setting['doingimgmaxnum'], 'text');
	showsetting('setting_doing_imgmaxsize', 'settingnew[doingimgmaxsize]', $setting['doingimgmaxsize'], 'text');
	showsetting('setting_doing_videoallow', 'settingnew[doingvideoallow]', $setting['doingvideoallow'], 'radio');
	showsetting('setting_doing_videomaxsize', 'settingnew[doingvideomaxsize]', $setting['doingvideomaxsize'], 'text');
	showsetting('setting_doing_videoext', 'settingnew[doingvideoext]', $setting['doingvideoext'], 'text');

	showtablefooter();
	/*search*/
	showtableheader();

	showsubmit('settingsubmit', 'submit', '', $extbutton.(!empty($from) ? '<input type="hidden" name="from" value="'.$from.'">' : ''));
	showtablefooter();
	showformfooter();
}