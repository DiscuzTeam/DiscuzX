<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_setting() {
	global $_G;

	$skipkeys = ['posttableids', 'mastermobile', 'masterqq', 'masteremail', 'closedreason',
		'creditsnotify', 'backupdir', 'custombackup', 'jswizard', 'maxonlines', 'modreasons', 'newsletter',
		'postno', 'postnocustom', 'customauthorinfo', 'domainwhitelist', 'ipregctrl',
		'ipverifywhite', 'fastsmiley', 'defaultdoing', 'antitheftsetting',
	];
	$serialized = ['reginput', 'memory', 'search', 'creditspolicy', 'ftp', 'secqaa', 'ec_credit', 'qihoo', 'spacedata',
		'infosidestatus', 'uc', 'indexhot', 'relatedtag', 'sitemessage', 'uchome', 'heatthread', 'recommendthread',
		'disallowfloat', 'allowviewuserthread', 'advtype', 'click', 'card', 'rewritestatus', 'rewriterule', 'privacy', 'focus',
		'forumkeys', 'article_tags', 'verify', 'seotitle', 'seodescription', 'seokeywords', 'domain', 'ranklist', 'my_search_data',
		'seccodedata', 'inviteconfig', 'advexpiration', 'allowpostcomment', /*(IN_MOBILE)*/
		'mobile', 'connect', 'upgrade', 'patch', 'strongpw',
		'posttable_info', 'threadtable_info', 'profilegroup', 'antitheft', 'makehtml', 'guestviewthumb', 'grid', 'guesttipsinthread', 'accountguard',
		'security_usergroups_white_list', 'security_forums_white_list', 'account', 'oss', 'chgusername', 'cells', 'forumportal', 'log', 'upgroup_name',
		'i18n', 'i18ns', 'i18n_custom', 'account_plugin_atypes', 'account_plugin_confs', 'mediasetting',
		'mobile',
		'wxshare', 'pwa',
	];
	account_base::cacheInterfaces(true);
	$serialized = array_merge($serialized, account_base::Interfaces_Used());

	$data = [];

	foreach(table_common_setting::t()->fetch_all_not_key($skipkeys) as $setting) {
		if($setting['skey'] == 'extcredits') {
			if(is_array($setting['svalue'] = dunserialize($setting['svalue']))) {
				foreach($setting['svalue'] as $key => $value) {
					if($value['available']) {
						unset($setting['svalue'][$key]['available']);
					} else {
						unset($setting['svalue'][$key]);
					}
				}
			}
		} elseif($setting['skey'] == 'creditsformula') {
			if(!checkformulacredits($setting['svalue'])) {
				$setting['svalue'] = '$member[\'extcredits1\']';
			} else {
				$setting['svalue'] = preg_replace('/(friends|doings|blogs|albums|polls|sharings|digestposts|posts|threads|oltime|extcredits[1-8])/', "\$member['\\1']", $setting['svalue']);
			}
		} elseif($setting['skey'] == 'maxsmilies') {
			$setting['svalue'] = $setting['svalue'] <= 0 ? -1 : $setting['svalue'];
		} elseif($setting['skey'] == 'threadsticky') {
			$setting['svalue'] = explode(',', $setting['svalue']);
		} elseif($setting['skey'] == 'attachdir') {
			$setting['svalue'] = preg_replace("/\.asp|\\0/i", '0', $setting['svalue']);
			$setting['svalue'] = str_replace('\\', '/', str_starts_with($setting['svalue'], './') ? DISCUZ_ROOT.$setting['svalue'] : $setting['svalue']);
			$setting['svalue'] .= !str_ends_with($setting['svalue'], '/') ? '/' : '';
		} elseif($setting['skey'] == 'attachurl') {
			$setting['svalue'] .= !str_ends_with($setting['svalue'], '/') ? '/' : '';
		} elseif($setting['skey'] == 'onlinehold') {
			$setting['svalue'] = $setting['svalue'] * 60;
		} elseif(in_array($setting['skey'], $serialized)) {
			$setting['svalue'] = @dunserialize($setting['svalue'], $setting['skey']);
			if($setting['skey'] == 'search') {
				foreach($setting['svalue'] as $key => $val) {
					foreach($val as $k => $v) {
						$setting['svalue'][$key][$k] = max(0, intval($v));
					}
				}
			} elseif($setting['skey'] == 'ftp') {
				$setting['svalue']['attachurl'] .= !str_ends_with($setting['svalue']['attachurl'], '/') ? '/' : '';
			} elseif($setting['skey'] == 'inviteconfig') {
				$setting['svalue']['invitecodeprompt'] = !empty($setting['svalue']['invitecodeprompt']) ? stripslashes($setting['svalue']['invitecodeprompt']) : '';
			} elseif($setting['skey'] == 'profilegroup') {
				$profile_settings = table_common_member_profile_setting::t()->fetch_all_by_available(1);
				foreach($setting['svalue'] as $key => $val) {
					$temp = [];
					if(!empty($val['field']) && is_array($val['field'])) {
						foreach($profile_settings as $pval) {
							if(in_array($pval['fieldid'], $val['field'])) {
								$temp[$pval['fieldid']] = $pval['fieldid'];
							}
						}
						foreach($val['field'] as $fieldid) {
							if(!in_array($fieldid, $temp)) {
								$temp[$fieldid] = $fieldid;
							}
						}
					}
					$setting['svalue'][$key]['field'] = $temp;
				}
				table_common_setting::t()->update_setting('profilegroup', $setting['svalue']);
			}
		}
		$_G['setting'][$setting['skey']] = $data[$setting['skey']] = $setting['svalue'];
	}

	$usergroup = table_common_usergroup::t()->fetch_by_credits($data['initcredits']);
	$data['newusergroupid'] = $usergroup['groupid'];
	$data['buyusergroupexists'] = table_common_usergroup::t()->buyusergroup_exists();

	if($data['srchhotkeywords']) {
		$data['srchhotkeywords'] = explode("\n", str_replace("\r", '', $data['srchhotkeywords']));
		$data['srchhotkeywords'] = array_filter($data['srchhotkeywords']);
	}

	if($data['search']) {
		$searchstatus = 0;
		foreach($data['search'] as $item) {
			if($item['status']) {
				$searchstatus = 1;
				break;
			}
		}
		if(!$searchstatus) {
			$data['search'] = [];
		}
	}

	$data['creditspolicy'] = array_merge($data['creditspolicy'], get_cachedata_setting_creditspolicy());

	if($data['heatthread']['iconlevels']) {
		$data['heatthread']['iconlevels'] = explode(',', $data['heatthread']['iconlevels']);
		arsort($data['heatthread']['iconlevels']);
	} else {
		$data['heatthread']['iconlevels'] = [];
	}
	if($data['verify']) {
		foreach($data['verify'] as $key => $value) {
			if($value['available']) {
				if(!empty($value['unverifyicon'])) {
					$icourl = parse_url($value['unverifyicon']);
					if(!$icourl['host'] && !file_exists($value['unverifyicon'])) {
						$data['verify'][$key]['unverifyicon'] = $data['attachurl'].'common/'.$value['unverifyicon'];
					}
				}
				if(!empty($value['icon'])) {
					$icourl = parse_url($value['icon']);
					if(!$icourl['host'] && !file_exists($value['icon'])) {
						$data['verify'][$key]['icon'] = $data['attachurl'].'common/'.$value['icon'];
					}
				}
			}
		}
	}

	if($data['recommendthread']['status']) {
		if($data['recommendthread']['iconlevels']) {
			$data['recommendthread']['iconlevels'] = explode(',', $data['recommendthread']['iconlevels']);
			arsort($data['recommendthread']['iconlevels']);
		} else {
			$data['recommendthread']['iconlevels'] = [];
		}
	} else {
		$data['recommendthread'] = ['allow' => 0];
	}

	if($data['commentnumber'] && !$data['allowpostcomment']) {
		$data['commentnumber'] = 0;
	}

	if(!empty($data['ftp'])) {
		if(!empty($data['ftp']['allowedexts'])) {
			$data['ftp']['allowedexts'] = str_replace(["\r\n", "\r"], ["\n", "\n"], $data['ftp']['allowedexts']);
			$data['ftp']['allowedexts'] = explode("\n", strtolower($data['ftp']['allowedexts']));
			array_walk($data['ftp']['allowedexts'], 'trim');
		}
		if(!empty($data['ftp']['disallowedexts'])) {
			$data['ftp']['disallowedexts'] = str_replace(["\r\n", "\r"], ["\n", "\n"], $data['ftp']['disallowedexts']);
			$data['ftp']['disallowedexts'] = explode("\n", strtolower($data['ftp']['disallowedexts']));
			array_walk($data['ftp']['disallowedexts'], 'trim');
		}
		$data['ftp']['connid'] = 0;
	}

	if(!empty($data['forumkeys'])) {
		$data['forumfids'] = array_flip($data['forumkeys']);
	} else {
		$data['forumfids'] = [];
	}

	$data['commentitem'] = explode("\t", $data['commentitem']);
	$commentitem = [];
	foreach($data['commentitem'] as $k => $v) {
		$tmp = explode(chr(0).chr(0).chr(0), $v);
		if(count($tmp) > 1) {
			$commentitem[$tmp[0]] = $tmp[1];
		} else {
			$commentitem[$k] = $v;
		}
	}
	$data['commentitem'] = $commentitem;

	if($data['allowviewuserthread']['allow']) {
		$data['allowviewuserthread'] = is_array($data['allowviewuserthread']['fids']) && $data['allowviewuserthread']['fids'] && !in_array('', $data['allowviewuserthread']['fids']) ? dimplode($data['allowviewuserthread']['fids']) : '';
	} else {
		$data['allowviewuserthread'] = -1;
	}

	include_once DISCUZ_ROOT.'./source/discuz_version.php';
	$_G['setting']['version'] = $data['version'] = DISCUZ_VERSION;

	$data['sitemessage']['time'] = !empty($data['sitemessage']['time']) ? $data['sitemessage']['time'] * 1000 : 0;
	foreach(['register', 'login', 'newthread', 'reply'] as $type) {
		$data['sitemessage'][$type] = !empty($data['sitemessage'][$type]) ? explode("\n", $data['sitemessage'][$type]) : [];
	}

	$data['cachethreadon'] = table_forum_forum::t()->fetch_threadcacheon_num() ? 1 : 0;
	$data['disallowfloat'] = is_array($data['disallowfloat']) ? implode('|', $data['disallowfloat']) : '';

	if(!$data['imagelib']) unset($data['imageimpath']);

	if(is_array($data['relatedtag']['order'])) {
		asort($data['relatedtag']['order']);
		$relatedtag = [];
		foreach($data['relatedtag']['order'] as $k => $v) {
			$relatedtag['status'][$k] = $data['relatedtag']['status'][$k];
			$relatedtag['name'][$k] = $data['relatedtag']['name'][$k];
			$relatedtag['limit'][$k] = $data['relatedtag']['limit'][$k];
			$relatedtag['template'][$k] = $data['relatedtag']['template'][$k];
		}
		$data['relatedtag'] = $relatedtag;

		foreach((array)$data['relatedtag']['status'] as $appid => $status) {
			if(!$status) {
				unset($data['relatedtag']['limit'][$appid]);
			}
		}
		unset($data['relatedtag']['status'], $data['relatedtag']['order'], $relatedtag);
	}

	$data['iconfont'] = $_G['setting']['iconfont'] ?? 'static/js/iconfont.js';
	$data['domain']['defaultindex'] = isset($data['defaultindex']) && $data['defaultindex'] != '#' ? $data['defaultindex'] : '';
	$data['domain']['holddomain'] = $data['holddomain'] ?? '';
	$data['domain']['list'] = [];
	foreach(table_common_domain::t()->fetch_all_by_idtype(['subarea', 'forum', 'topic', 'channel', 'plugin']) as $value) {
		if($value['idtype'] == 'plugin') {
			$plugin = table_common_plugin::t()->fetch($value['id']);
			if(!$plugin || !$plugin['available']) {
				continue;
			}
			$value['id'] = $plugin['identifier'];
		}
		$data['domain']['list'][$value['domain'].'.'.$value['domainroot']] = ['id' => $value['id'], 'idtype' => $value['idtype']];
	}
	savecache('domain', $data['domain']);
	@unlink(DISCUZ_DATA.'./sysdata/cache_domain.php');

	$data['seccodedata'] = is_array($data['seccodedata']) ? $data['seccodedata'] : [];
	if($data['seccodedata']['type'] == 2) {
		if(extension_loaded('ming')) {
			unset($data['seccodedata']['background'], $data['seccodedata']['adulterate'],
				$data['seccodedata']['ttf'], $data['seccodedata']['angle'],
				$data['seccodedata']['color'], $data['seccodedata']['size'],
				$data['seccodedata']['animator']);
		} else {
			$data['seccodedata']['animator'] = 0;
		}
	} elseif($data['seccodedata']['type'] == 99) {
		$data['seccodedata']['width'] = 32;
		$data['seccodedata']['height'] = 24;
	}

	$data['watermarktype'] = !empty($data['watermarktype']) ? dunserialize($data['watermarktype']) : [];
	$data['watermarktext'] = !empty($data['watermarktext']) ? dunserialize($data['watermarktext']) : [];
	foreach($data['watermarktype'] as $k => $v) {
		if($data['watermarktype'][$k] == 'text' && $data['watermarktext']['text'][$k]) {
			if($data['watermarktext']['text'][$k] && strtoupper(CHARSET) != 'UTF-8') {
				$data['watermarktext']['text'][$k] = diconv($data['watermarktext']['text'][$k], CHARSET, 'UTF-8', true);
			}
			$data['watermarktext']['text'][$k] = bin2hex($data['watermarktext']['text'][$k]);
			if(file_exists('source/data/seccode/font/en/'.$data['watermarktext']['fontpath'][$k])) {
				$data['watermarktext']['fontpath'][$k] = 'source/data/seccode/font/en/'.$data['watermarktext']['fontpath'][$k];
			} elseif(file_exists('source/data/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k])) {
				$data['watermarktext']['fontpath'][$k] = 'source/data/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k];
			} else {
				$data['watermarktext']['fontpath'][$k] = 'source/data/seccode/font/'.$data['watermarktext']['fontpath'][$k];
			}
			$data['watermarktext']['color'][$k] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', 'build_cache_setting_callback_hexdec_123', $data['watermarktext']['color'][$k]);
			$data['watermarktext']['shadowcolor'][$k] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', 'build_cache_setting_callback_hexdec_123', $data['watermarktext']['shadowcolor'][$k]);
		} else {
			$data['watermarktext']['text'][$k] = '';
			$data['watermarktext']['fontpath'][$k] = '';
			$data['watermarktext']['color'][$k] = '';
			$data['watermarktext']['shadowcolor'][$k] = '';
		}
	}

	$data['styles'] = [];
	foreach(table_common_style::t()->fetch_all_data(false, 1) as $style) {
		$data['styles'][$style['styleid']] = dhtmlspecialchars($style['name']);
	}

	$exchcredits = [];
	$allowexchangein = $allowexchangeout = FALSE;
	foreach((array)$data['extcredits'] as $id => $credit) {
		$data['extcredits'][$id]['img'] = $credit['img'] ? '<img style="vertical-align:middle" src="'.$credit['img'].'" />' : '';
		if(!empty($credit['ratio'])) {
			$exchcredits[$id] = $credit;
			$credit['allowexchangein'] && $allowexchangein = TRUE;
			$credit['allowexchangeout'] && $allowexchangeout = TRUE;
		}
		$data['creditnotice'] && $data['creditnames'][] = str_replace("'", "\'", dhtmlspecialchars($id.'|'.$credit['title'].'|'.$credit['unit']));
	}
	$data['creditnames'] = $data['creditnotice'] ? @implode(',', $data['creditnames']) : '';

	$creditstranssi = explode(',', $data['creditstrans']);
	$data['creditstrans'] = $creditstranssi[0];
	unset($creditstranssi[0]);
	$data['creditstransextra'] = $creditstranssi;
	for($i = 1; $i < 14; $i++) {
		$data['creditstransextra'][$i] = $data['creditstrans'] ? (!$data['creditstransextra'][$i] ? $data['creditstrans'] : $data['creditstransextra'][$i]) : 0;
	}
	$data['exchangestatus'] = $allowexchangein && $allowexchangeout;
	$data['transferstatus'] = isset($data['extcredits'][$data['creditstrans']]);

	list($data['zoomstatus'], $data['imagemaxwidth']) = explode("\t", $data['zoomstatus']);
	$data['imagemaxwidth'] = intval($data['imagemaxwidth']);

	require_once DISCUZ_ROOT.'./config/config_ucenter.php';
	$data['ucenterurl'] = UC_STANDALONE ? '.' : UC_API;
	$data['avatarurl'] = empty(UC_AVTURL) ? $data['ucenterurl'].'/data/avatar' : UC_AVTURL;
	$data['avatarpath'] = UC_STANDALONE ? (UC_AVTPATH ? substr(realpath(DISCUZ_ROOT.str_replace('..', '', UC_AVTPATH)), strlen(DISCUZ_ROOT)).'/' : 'data/avatar/') : '';

	if($data['ftp']['on'] == 2 && $data['oss']['oss_avatar']) {
		$data['defaultavatar'] = $data['ftp']['attachurl'].'avatar/noavatar.'.(!empty($data['avatar_default']) ? $data['avatar_default'] : 'svg');
	} else {
		$data['defaultavatar'] = $data['avatarurl'].'/noavatar.'.(!empty($data['avatar_default']) ? $data['avatar_default'] : 'svg');
	}

	foreach(table_common_magic::t()->fetch_all_data(1) as $magic) {
		$magic['identifier'] = str_replace(':', '_', $magic['identifier']);
		$data['magics'][$magic['identifier']] = $magic['name'];
	}

	$data['tradeopen'] = table_common_usergroup_field::t()->count_by_field('allowposttrade', 1) ? 1 : 0;

	$focus = [];
	if($data['focus']['data']) {
		foreach($data['focus']['data'] as $k => $v) {
			if($v['available'] && $v['position']) {
				foreach($v['position'] as $position) {
					$focus[$position][$k] = $k;
				}
			}
		}
	}
	$data['focus'] = $focus;

	list($data['plugins'], $data['pluginlinks'], $data['hookscript'], $data['hookscriptmobile'], $data['threadplugins'], $data['specialicon']) = get_cachedata_setting_plugin();
	unset($data['newhook'], $data['hooksort']);

	if(empty($data['defaultindex'])) $data['defaultindex'] = '';
	list($data['navs'], $data['subnavs'], $data['menunavs'], $data['navmns'], $data['navmn'], $data['navdms'], $data['navlogos']) = get_cachedata_mainnav();

	$data['footernavs'] = get_cachedata_footernav();
	$data['spacenavs'] = get_cachedata_spacenavs();
	$data['mynavs'] = get_cachedata_mynavs();
	$data['topnavs'] = get_cachedata_topnav();
	$data['profilenode'] = get_cachedata_threadprofile();
	$data['mfindnavs'] = get_cachedata_mfindnav();
	$data['mnavs'] = get_cachedata_mnav();

	loaducenter();
	$ucapparray = uc_app_ls();
	$data['allowsynlogin'] = $ucapparray[UC_APPID]['synlogin'] ?? 1;
	$appnamearray = ['UCHOME', 'XSPACE', 'DISCUZ', 'SUPESITE', 'SUPEV', 'ECSHOP', 'ECMALL', 'OTHER'];
	$data['ucapp'] = $data['ucappopen'] = [];
	$data['uchomeurl'] = '';
	$data['discuzurl'] = $_G['siteurl'];
	$appsynlogins = 0;
	foreach($ucapparray as $apparray) {
		if($apparray['appid'] != UC_APPID) {
			if(!empty($apparray['synlogin'])) {
				$appsynlogins = 1;
			}
			if($data['uc']['navlist'][$apparray['appid']] && $data['uc']['navopen']) {
				$data['ucapp'][$apparray['appid']]['name'] = $apparray['name'];
				$data['ucapp'][$apparray['appid']]['url'] = $apparray['url'];
			}
		} else {
			$data['discuzurl'] = $apparray['url'];
		}
		if(!empty($apparray['viewprourl'])) {
			$data['ucapp'][$apparray['appid']]['viewprourl'] = $apparray['url'].$apparray['viewprourl'];
		}
		foreach($appnamearray as $name) {
			if($apparray['type'] == $name && $apparray['appid'] != UC_APPID) {
				$data['ucappopen'][$name] = 1;
				if($name == 'UCHOME') {
					$data['uchomeurl'] = $apparray['url'];
				} elseif($name == 'XSPACE') {
					$data['xspaceurl'] = $apparray['url'];
				}
			}
		}
	}
	$data['allowsynlogin'] = $data['allowsynlogin'] && $appsynlogins ? 1 : 0;
	$data['homeshow'] = $data['uchomeurl'] && $data['uchome']['homeshow'] ? $data['uchome']['homeshow'] : '0';

	unset($data['allowthreadplugin']);
	if($data['jspath'] == 'data/cache/') {
		writetojscache();
	} elseif(!$data['jspath']) {
		$data['jspath'] = 'static/js/';
	}

	if($data['ftp']['on'] == 2) {
		$data['csspathv'] = $data['jspath'] = $data['ftp']['attachurl'].'cache/';
		writetojscache();
	}

	if($data['pwa']['allow']) {
		pwa::updatecache();
	}

	if(!$data['csspathv']) {
		$data['csspathv'] = 'data/cache/';
	}
	$data['csspath'] = $data['csspathv'].'style_';

	if($data['cacheindexlife']) {
		$cachedir = DISCUZ_ROOT.'./'.$data['cachethreaddir'];
		$tidmd5 = substr(md5(0), 3);
		@unlink($cachedir.'/'.$tidmd5[0].'/'.$tidmd5[1].'/'.$tidmd5[2].'/0.htm');
	}

	$reginputbwords = ['username', 'password', 'password2', 'email'];
	if(in_array($data['reginput']['username'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['username'])) {
		$data['reginput']['username'] = random(6);
	}
	if(in_array($data['reginput']['password'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['password'])) {
		$data['reginput']['password'] = random(6);
	}
	if(in_array($data['reginput']['password2'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['password2'])) {
		$data['reginput']['password2'] = random(6);
	}
	if(in_array($data['reginput']['email'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['email'])) {
		$data['reginput']['email'] = random(6);
	}

	$defaultcurhost = empty($_G['setting']['domain']['app']['default']) ? '{CURHOST}' : $_G['setting']['domain']['app']['default'];
	$output = ['str' => [], 'preg' => []]; //str为二级域名的查找和替换，preg为rewrite和默认域名的查找和替换
	$_G['domain'] = [];
	if(is_array($_G['setting']['domain']['app'])) {
		$apps = $_G['setting']['domain']['app'];
		$repflag = $apps['portal'] || $apps['forum'] || $apps['group'] || $apps['home'] || $apps['default'];
		foreach($apps as $app => $domain) {
			if(in_array($app, ['default', 'mobile'])) {
				continue;
			}
			$appphp = "{$app}.php";
			if(!$domain) {
				$domain = $defaultcurhost;
			}
			if($domain != '{CURHOST}') {
				$domain = $_G['scheme'].'://'.$domain.$_G['siteport'].'/';
			}
			if($repflag) {
				$output['str']['search'][$app] = "<a href=\"{$app}.php";
				$output['str']['replace'][$app] = '<a href="'.$domain.$appphp;
				$_G['domain']['pregxprw'][$app] = '<a href\="('.preg_quote($domain, '/').')'.$appphp;
			} else {
				$_G['domain']['pregxprw'][$app] = '<a href\="()'.$appphp;
			}
		}
	}
	if($_G['setting']['rewritestatus'] || $output['str']['search']) {
		if($_G['setting']['rewritestatus']) {
			require_once libfile('function/admincp');
			$output['preg'] = rewritedata(0);
		}
		if($output['preg']) {
			foreach($data['footernavs'] as $id => $nav) {
				foreach($output['preg']['search'] as $key => $value) {
					$data['footernavs'][$id]['code'] = preg_replace_callback(
						$value,
						function($matches) use ($output, $key) {
							return eval('return '.$output['preg']['replace'][$key].';');
						},
						$nav['code']
					);
				}
			}
			foreach($data['spacenavs'] as $id => $nav) {
				foreach($output['preg']['search'] as $key => $value) {
					$data['spacenavs'][$id]['code'] = preg_replace_callback(
						$value,
						function($matches) use ($output, $key) {
							return eval('return '.$output['preg']['replace'][$key].';');
						},
						$nav['code']
					);
				}
			}
			foreach($data['mynavs'] as $id => $nav) {
				foreach($output['preg']['search'] as $key => $value) {
					$data['mynavs'][$id]['code'] = preg_replace_callback(
						$value,
						function($matches) use ($output, $key) {
							return eval('return '.$output['preg']['replace'][$key].';');
						},
						$nav['code']
					);
				}
			}
			foreach($data['topnavs'] as $id => $nav) {
				foreach($output['preg']['search'] as $key => $value) {
					$data['topnavs'][$id]['code'] = preg_replace_callback(
						$value,
						function($matches) use ($output, $key) {
							return eval('return '.$output['preg']['replace'][$key].';');
						},
						$nav['code']
					);
				}
			}
			foreach($data['plugins']['jsmenu'] as $id => $nav) {
				foreach($output['preg']['search'] as $key => $value) {
					$data['plugins']['jsmenu'][$id]['url'] = preg_replace_callback(
						$value,
						function($matches) use ($output, $key) {
							return eval('return '.$output['preg']['replace'][$key].';');
						},
						$nav['url']
					);
				}
			}
		}
	}

	$data['witframe_plugins'] = witframe_plugin::getSettingValue();

	$data['output'] = $output;

	$data['parseflv'] = get_cachedata_discuzcode_parseflv($data['mediasetting'], $data['plugins']);

	$data['mpsid'] = preg_replace('/[^0-9]+/', '', $data['mps']);

	$data['securesiteurl'] = $_G['siteurl'];

	$data['maxsubjectsize'] = empty($data['maxsubjectsize']) ? 80 : $data['maxsubjectsize'];

	$data['minsubjectsize'] = empty($data['minsubjectsize']) ? 1 : $data['minsubjectsize'];

	// 如果站点做过用户分表, 需要在更新缓存时判定一下用户分表是否存在, 不存在的话需要加上.
	// 修复因站点自身问题导致用户分表丢失导致程序出错的问题.
	if($data['membersplit']) {
		table_common_member_archive::t()->check_table();
	}

	$style = table_common_style::t()->fetch_by_styleid($data['styleid']);
	$files = cells::getCells(DISCUZ_TEMPLATE($style['directory']).'/cells');
	foreach($files as $file) {
		$cellId = cells::getClass($file);
		$c = cells::className($cellId);
		foreach([0, 1] as $type) {
			$template = $data['cells'][cells::getTplKey($type)][$data['styleid']][$cellId] ?: $c::getDefault($type);
			$data['cells'][cells::getTplKey($type)][$data['styleid']][$cellId] = $template;
			$data['cells'][cells::getUsedKey($type)][$data['styleid']][$cellId] = cells::getUsedSetting($cellId, $template);
		}
	}

	init_i18n($data);

	savecache('setting', $data);
	$_G['setting'] = $data;

	dmkdir(DISCUZ_DATA.'cache/');
	dmkdir(DISCUZ_DATA.'diy/');
	dmkdir(DISCUZ_DATA.'sysdata/');
	dmkdir(DISCUZ_DATA.'template/');
	dmkdir(DISCUZ_DATA.'attachment/');
}

function build_cache_setting_callback_hexdec_123($matches) {
	return hexdec($matches[1]).','.hexdec($matches[2]).','.hexdec($matches[3]);
}

function get_cachedata_setting_creditspolicy() {
	$data = [];
	foreach(table_common_credit_rule::t()->fetch_all_by_action(['promotion_visit', 'promotion_register']) as $creditrule) {
		$ruleexist = false;
		for($i = 1; $i <= 8; $i++) {
			if($creditrule['extcredits'.$i]) {
				$ruleexist = true;
			}
		}
		$data[$creditrule['action']] = $ruleexist;
	}
	return $data;
}

function child_data($pluginid, &$childSetting) {
	$dir = DISCUZ_PLUGIN($pluginid).'/child';
	if(!file_exists($dir)) {
		return false;
	}

	$childdir = dir($dir);
	while($filename = $childdir->read()) {
		if(!in_array($filename, ['.', '..']) && fileext($filename) == 'php') {
			$content = file_get_contents($dir.'/'.$filename);
			preg_match("/childfile\:(.+?)\n/", $content, $r);
			$childfile = trim($r[1]);
			if(!$childfile) {
				continue;
			}
			if(!empty($childSetting[$childfile])) {
				continue;
			}
			$childSetting[$childfile] = [
				'plugin' => $pluginid,
				'file' => str_replace('.php', '', $filename),
			];
		}
	}
}

function component_data($pluginid, &$componentSetting) {
	if($pluginid) {
		$dir = DISCUZ_PLUGIN($pluginid).'/admin/component';
		if(!file_exists($dir)) {
			return false;
		}
	} else {
		$dir = MITFRAME_APP('admin').'/component';
	}

	$compdir = dir($dir);
	while($filename = $compdir->read()) {
		if(!in_array($filename, ['.', '..']) && fileext($filename) == 'php') {
			$c = 'admin\\'.($_n = substr($filename, 0, -4));
			if(!class_exists($class = ($pluginid ? '\\'.$pluginid : '').'\\'.$c)) {
				continue;
			}

			$n = new $class();
			$name = property_exists($n, 'name') ? $n->name : ($pluginid ? $pluginid.'_' : '').$_n;
			$componentSetting[($pluginid ? $pluginid.':' : '').$_n] = [
				'name' => $name,
				'class' => $class,
			];
		}
	}
}

function perm_data($pluginid, &$permSetting, &$permTypeSetting) {
	$dir = DISCUZ_PLUGIN($pluginid).'/perm';
	if(!file_exists($dir)) {
		return false;
	}
	$pluginname = $_G['setting']['plugins']['name'][$pluginid] ?? $pluginid;
	$childdir = dir($dir);
	while($filename = $childdir->read()) {
		if(!in_array($filename, ['.', '..']) && fileext($filename) == 'php') {
			$c = substr($filename, 0, -4);
			if(!class_exists($class = '\\'.$pluginid.'\\'.$c)) {
				continue;
			}

			$n = new $class();
			if(property_exists($n, 'typename')) {
				$permTypeSetting[$n->typename] = [
					'pluginid' => $pluginid,
					'name' => $n->typename,
				];
			} else {
				$name = property_exists($n, 'name') ? $n->name : $pluginid.'_'.$c;
				$permSetting[$c] = [
					'pluginid' => $pluginid,
					'name' => $name,
					'class' => $class,
				];
			}
		}
	}
}

function get_cachedata_setting_plugin($method = '') {
	require_once libfile('function/admincp');
	global $_G;
	$hookfuncs = ['common', 'discuzcode', 'template', 'deletemember', 'deletethread', 'deletepost', 'avatar', 'savebanlog', 'cacheuserstats', 'undeletethreads', 'recyclebinpostundelete', 'threadpubsave', 'profile_node'];
	$data = $adminmenu = $adminlog = [];
	$data['plugins'] = $data['pluginlinks'] = $data['hookscript'] = $data['hookscriptmobile'] = $data['threadplugins'] = $data['specialicon'] = [];
	$data['plugins']['func'] = $data['plugins']['available'] = $_G['cache']['admin']['component'] = [];
	foreach(table_common_plugin::t()->fetch_all_data() as $plugin) {
		$available = !$method && $plugin['available'] || $method && ($plugin['available'] || $method == $plugin['identifier']);
		$addadminmenu = $plugin['available'] && table_common_pluginvar::t()->count_by_pluginid($plugin['pluginid']);
		$plugin['modules'] = dunserialize($plugin['modules']);
		if($available) {
			$data['plugins']['available'][] = $plugin['identifier'];
			$data['plugins']['version'][$plugin['identifier']] = $plugin['version'];
			$data['plugins']['name'][$plugin['identifier']] = $plugin['name'];
		}
		child_data($plugin['identifier'], $data['plugins']['child']);
		component_data($plugin['identifier'], $_G['cache']['admin']['component']);
		perm_data($plugin['identifier'], $data['plugins']['perm'], $data['plugins']['permtype']);
		$plugin['directory'] = $plugin['directory'].((!empty($plugin['directory']) && !str_ends_with($plugin['directory'], '/')) ? '/' : '');
		if(!isplugindir($plugin['directory'])) {
			if(ispluginkey($plugin['identifier'])) {
				$plugin['directory'] = $plugin['identifier'].'/';
				C::t('common_plugin')->update($plugin['pluginid'], ['directory' => $plugin['directory']]);
			} else {
				continue;
			}
		}
		if(is_array($plugin['modules'])) {
			unset($plugin['modules']['extra']);
			foreach($plugin['modules'] as $k => $module) {
				if($available && isset($module['name'])) {
					$module['displayorder'] = $plugin['modules']['system'] ? ($module['displayorder'] < 1000 ? (int)$module['displayorder'] : 999) : (int)$module['displayorder'] + 1000;
					$k = '';
					switch($module['type']) {
						case 1:
							$navtype = 0;
						case 23:
							if($module['type'] == 23) $navtype = 1;
						case 24:
							if($module['type'] == 24) $navtype = 2;
						case 25:
							if($module['type'] == 25) $navtype = 3;
						case 30:
							if($module['type'] == 30) $navtype = 5;
						case 27:
							if($module['type'] == 27) $navtype = 4;
							$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
							if(!(table_common_nav::t()->count_by_navtype_type_identifier($navtype, 3, $plugin['identifier']))) {
								table_common_nav::t()->insert([
									'name' => $module['menu'],
									'title' => $module['navtitle'],
									'url' => $module['url'],
									'type' => 3,
									'identifier' => $plugin['identifier'],
									'navtype' => $navtype,
									'available' => 1,
									'icon' => $module['navicon'],
									'subname' => $module['navsubname'],
									'suburl' => $module['navsuburl'],
								]);
							}
							break;
						case 5:
							$k = 'jsmenu';
							$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
							list($module['menu'], $module['title']) = explode('/', $module['menu']);
							$module['menu'] = $module['type'] == 1 ? ($module['menu'].($module['title'] ? '<span>'.$module['title'].'</span>' : '')) : $module['menu'];
							$data['plugins'][$k][] = ['displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'url' => "<a href=\"$module[url]\" id=\"mn_plink_$module[name]\">$module[menu]</a>"];
							break;
						case 14:
							$k = 'faq';
						case 15:
							$k = !$k ? 'modcp_base' : $k;
						case 16:
							$k = !$k ? 'modcp_tools' : $k;
						case 7:
							$k = !$k ? 'spacecp' : $k;
						case 17:
							$k = !$k ? 'spacecp_profile' : $k;
						case 19:
							$k = !$k ? 'spacecp_credit' : $k;
						case 21:
							$k = !$k ? 'portalcp' : $k;
						case 26:
							$k = !$k ? 'space_thread' : $k;
							$data['plugins'][$k][$plugin['identifier'].':'.$module['name']] = ['displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'name' => $module['menu'], 'url' => $module['url'], 'directory' => $plugin['directory']];
							break;
						case 3:
							$addadminmenu = TRUE;
							break;
						case 4:
							$data['plugins']['include'][$plugin['identifier']] = ['displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'script' => $plugin['directory'].$module['name']];
							break;
						case 11:
							$k = 'hookscript';
						case 28:
							$k = !$k ? 'hookscriptmobile' : $k;
							$script = $plugin['directory'].$module['name'];
							@include_once DISCUZ_PLUGIN($script).'.class.php';
							$classes = get_declared_classes();
							$classnames = [];
							$namekey = ($k == 'hookscriptmobile' ? 'mobile' : '').'plugin_'.$plugin['identifier'];
							$cnlen = strlen($namekey);
							foreach($classes as $classname) {
								if(substr($classname, 0, $cnlen) == $namekey) {
									$hscript = substr($classname, $cnlen + 1);
									$classnames[$hscript ? $hscript : 'global'] = $classname;
								}
							}
							foreach($classnames as $hscript => $classname) {
								$hookmethods = get_class_methods($classname);
								foreach($hookmethods as $funcname) {
									if($hscript == 'global' && in_array($funcname, $hookfuncs)) {
										$data['plugins']['func'][$k][$funcname] = true;
										if($funcname == 'profile_node') {
											$data['plugins']['profile_node'][$plugin['identifier']] = $script;
										}
									}
									$v = explode('_', $funcname);
									$curscript = $v[0];
									if(!$curscript || $classname == $funcname) {
										continue;
									}
									if($hscript == 'home' && in_array($curscript, ['space', 'spacecp'])) {
										$curscript .= '_'.$v[1];
									}
									// If $funcname include __ , then before __ is $curscript.
									if(str_contains($funcname, '__')) {
										$curscript = current(explode('__', $funcname));
									}
									if(!is_array($data[$k][$hscript][$curscript]['module']) || !in_array($script, $data[$k][$hscript][$curscript]['module'])) {
										$data[$k][$hscript][$curscript]['module'][$plugin['identifier']] = $script;
										$data[$k][$hscript][$curscript]['adminid'][$plugin['identifier']] = $module['adminid'];
									}
									if(preg_match('/\_output$/', $funcname)) {
										$varname = preg_replace('/\_output$/', '', $funcname);
										$data[$k][$hscript][$curscript]['outputfuncs'][$varname][] = ['displayorder' => $module['displayorder'], 'func' => [$plugin['identifier'], $funcname]];
									} elseif(preg_match('/\_message$/', $funcname)) {
										$varname = preg_replace('/\_message$/', '', $funcname);
										$data[$k][$hscript][$curscript]['messagefuncs'][$varname][] = ['displayorder' => $module['displayorder'], 'func' => [$plugin['identifier'], $funcname]];
									} else {
										$data[$k][$hscript][$curscript]['funcs'][$funcname][] = ['displayorder' => $module['displayorder'], 'func' => [$plugin['identifier'], $funcname]];
									}
								}
							}
							break;
						case 12:
							$script = $plugin['directory'].$module['name'];
							@include_once DISCUZ_PLUGIN($script).'.class.php';
							if(class_exists('threadplugin_'.$plugin['identifier'])) {
								$classname = 'threadplugin_'.$plugin['identifier'];
								$hookclass = new $classname;
								if($hookclass->name) {
									$data['threadplugins'][$plugin['identifier']]['name'] = $hookclass->name;
									$data['threadplugins'][$plugin['identifier']]['icon'] = $hookclass->iconfile;
									$data['threadplugins'][$plugin['identifier']]['module'] = $script;
								}
							}
							break;
					}
				}
			}
		}
		if($addadminmenu) {
			$adminmenu[$plugin['modules']['system'] ? 0 : 1][] = ['url' => "plugins&operation=config&do={$plugin['pluginid']}", 'action' => 'plugins_config_'.$plugin['pluginid'], 'name' => $plugin['name']];
		}

		$dir = DISCUZ_PLUGIN($plugin['identifier']).'/log';
		if(file_exists($dir)) {
			$logdir = dir($dir);
			while($filename = $logdir->read()) {
				if(!in_array($filename, ['.', '..']) && preg_match('/^log\_(\w+)\.php$/', $filename, $r)) {
					$adminlog[] = $plugin['identifier'].':'.$r[1];
				}
			}
		}
	}

	component_data('', $_G['cache']['admin']['component']);

	savecache('adminlog', $adminlog);
	savecache('admin', $_G['cache']['admin']);

	if(!$method) {
		$_G['setting']['plugins']['available'] = $data['plugins']['available'];
		if($adminmenu[0]) {
			$adminmenu[0] = array_merge(
				[['name' => 'plugins_system', 'sub' => 1]],
				$adminmenu[0],
				[['name' => 'plugins_system', 'sub' => 2]]
			);
		}
		savecache('adminmenu', array_merge((array)$adminmenu[0], (array)$adminmenu[1]));
	}

	$hookfuncTypes = ['funcs', 'outputfuncs', 'messagefuncs'];

	$setting = table_common_setting::t()->fetch_all_setting(['hooksort', 'newhook'], true);
	$hooksort = $setting['hooksort'] ?? [];
	$newhook = $setting['newhook'] ?? [];

	$data['pluginhooks'] = [];
	foreach(['hookscript', 'hookscriptmobile'] as $hooktype) {
		foreach($data[$hooktype] as $hscript => $hookscript) {
			foreach($hookscript as $curscript => $scriptdata) {
				foreach($hookfuncTypes as $functype) {
					if(!is_array($scriptdata[$functype])) {
						continue;
					}
					foreach($scriptdata[$functype] as $funcname => $funcs) {
						usort($funcs, 'pluginmodulecmp');
						$tmp = [];
						foreach($funcs as $k => $v) {
							$sk = md5($v['func'][0].':'.$v['func'][1]);
							if(isset($hooksort[$hooktype][$hscript][$curscript][$functype][$funcname][$sk])) {
								$v['func'][99] = $hooksort[$hooktype][$hscript][$curscript][$functype][$funcname][$sk];
							}
							$tmp[$k] = $v['func'];
						}
						$data[$hooktype][$hscript][$curscript][$functype][$funcname] = $tmp;
					}
				}
			}
		}

		if(!empty($newhook[$hooktype])) {
			foreach($newhook[$hooktype] as $hscript => $hookscript) {
				foreach($hookscript as $curscript => $scriptdata) {
					foreach($scriptdata as $functype => $funcnames) {
						foreach($funcnames as $funcname => $funcs) {
							foreach($funcs as $func) {
								$func[2] = true;
								$sk = md5($func[0].':'.$func[1]);
								if(isset($hooksort[$hooktype][$hscript][$curscript][$functype][$funcname][$sk])) {
									$func[99] = $hooksort[$hooktype][$hscript][$curscript][$functype][$funcname][$sk];
								}
								$data[$hooktype][$hscript][$curscript][$functype][$funcname][] = $func;
							}
						}
					}
				}
			}
		}

		foreach($data[$hooktype] as $hscript => $hookscript) {
			foreach($hookscript as $curscript => $scriptdata) {
				foreach($hookfuncTypes as $functype) {
					if(!is_array($scriptdata[$functype])) {
						continue;
					}
					foreach($scriptdata[$functype] as $funcname => $funcs) {
						usort($data[$hooktype][$hscript][$curscript][$functype][$funcname], 'hooksortcmp');
					}
				}
			}
		}
	}

	foreach(['links', 'spacecp', 'include', 'jsmenu', 'space', 'spacecp', 'spacecp_profile', 'spacecp_credit', 'faq', 'modcp_base', 'modcp_member', 'modcp_forum'] as $pluginkey) {
		if(is_array($data['plugins'][$pluginkey])) {
			if(in_array($pluginkey, ['space', 'spacecp', 'spacecp_profile', 'spacecp_credit', 'faq', 'modcp_base', 'modcp_tools'])) {
				uasort($data['plugins'][$pluginkey], 'pluginmodulecmp');
			} else {
				usort($data['plugins'][$pluginkey], 'pluginmodulecmp');
			}
		}
	}

	return [$data['plugins'], $data['pluginlinks'], $data['hookscript'], $data['hookscriptmobile'], $data['threadplugins'], $data['specialicon']];

}

function get_cachedata_mainnav() {
	global $_G;

	$data['navs'] = $data['subnavs'] = $data['menunavs'] = $data['navmns'] = $data['navmn'] = $data['navdms'] = $navids = [];
	foreach(table_common_nav::t()->fetch_all_mainnav() as $nav) {
		$_nav = $nav;
		if($nav['available'] < 0) {
			continue;
		}
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		if($nav['identifier'] == 1 && $nav['type'] == 0 && !helper_access::check_module('portal')) {
			$nav['available'] = 0;
		}
		if($nav['identifier'] == 3 && $nav['type'] == 0 && !helper_access::check_module('group')) {
			$nav['available'] = 0;
		}
		if($nav['identifier'] == 4 && $nav['type'] == 0 && !helper_access::check_module('feed')) {
			$nav['available'] = 0;
		}
		if($nav['type'] == 3) {
			if(!in_array($nav['identifier'], $_G['setting']['plugins']['available'])) {
				continue;
			}
		}
		if($nav['identifier'] == 8 && $nav['type'] == 0 && !helper_access::check_module('ranklist')) {
			$nav['available'] = 0;
		}
		$nav['style'] = parsehighlight($nav['highlight']);
		$data['navs'][$id]['navname'] = $nav['name'];
		$data['navs'][$id]['filename'] = $nav['url'];
		$data['navs'][$id]['available'] = $nav['available'];
		$nav['name'] = $nav['name'].($nav['title'] ? '<span>'.$nav['title'].'</span>' : '');
		$subnavs = '';
		foreach(table_common_nav::t()->fetch_all_subnav($nav['id']) as $subnav) {
			$item = "<a href=\"{$subnav['url']}\" hidefocus=\"true\" ".($subnav['title'] ? "title=\"{$subnav['title']}\" " : '').($subnav['target'] == 1 ? "target=\"_blank\" " : '').parsehighlight($subnav['highlight']).">{$subnav['name']}</a>";
			$liparam = !$nav['subtype'] || !$nav['subcols'] ? '' : ' style="width:'.sprintf('%1.1f', (1 / $nav['subcols']) * 100).'%"';
			$subnavs .= '<li'.$liparam.'>'.$item.'</li>';
		}
		list($navid) = explode('.', basename($nav['url']));
		if($nav['type'] || $navid == 'misc' || $nav['identifier'] == 6) {
			if($nav['type'] == 4) {
				$navid = 'P'.$nav['identifier'];
			} elseif($nav['type'] == 5) {
				$navid = 'F'.$nav['identifier'];
			} else {
				$navid = 'N'.substr(md5(($nav['url'] != '#' ? $nav['url'] : $nav['name'])), 0, 4);
			}
		}
		$navid = 'mn_'.$navid;
		if(in_array($navid, $navids)) {
			$navid .= '_'.$nav['identifier'];
		}
		$navids[] = $navid;
		$onmouseover = '';
		if($subnavs) {
			if($nav['subtype']) {
				$onmouseover = 'navShow(\''.substr($navid, 3).'\')';
				$data['subnavs'][$navid] = $subnavs;
			} else {
				$onmouseover = 'showMenu({\'ctrlid\':this.id,\'ctrlclass\':\'hover\',\'duration\':2})';
				$data['menunavs'][] = '<ul class="p_pop h_pop" id="'.$navid.'_menu" style="display: none">'.$subnavs.'</ul>';
			}
		}
		if($nav['identifier'] == 6 && $nav['type'] == 0) {
			if(!empty($_G['setting']['plugins']['jsmenu'])) {
				$onmouseover .= "showMenu({'ctrlid':this.id,'ctrlclass':'hover','menuid':'plugin_menu'})";
			} else {
				$data['navs'][$id]['available'] = 0;
				continue;
			}
		}
		if($nav['identifier'] == 5 && $nav['type'] == 0) {
			$onmouseover = 'delayShow(this, function () {showMenu({\'ctrlid\':\'mn_userapp\',\'pos\':\'43!\',\'ctrlclass\':\'a\',\'duration\':2});showUserApp();})';
			$data['menunavs'][] = '<div class="p_pop h_pop" id="'.$navid.'_menu" style="display: none"></div>';
			$data['subnavs'][$navid] = '';
		}

		if($nav['logo']) {
			$navlogo = admin\class_attach::getUrl($nav['logo']);
			$data['navlogos'][$navid] = '<a href="'.$nav['url'].'" title="'.$_G['setting']['bbname'].'"><img src="'.$navlogo.'" alt="'.$_G['setting']['bbname'].'" border="0" /></a>';
		}

		if($nav['icon']) {
			$data['navs'][$id]['icon'] = admin\class_attach::getUrl($nav['icon']);
		}

		$purl = parse_url($nav['url']);
		$getvars = [];
		parse_str($purl['query'] ?? '', $getvars);
		if($purl['path'] == 'index.php' && !empty($getvars['index'])) {
			$purl['path'] = $getvars['index'].'.php';
		}

		if($purl['query']) {
			$data['navmns'][$purl['path']][] = [$getvars, $navid];
		} elseif($purl['host']) {
			$data['navdms'][strtolower($purl['host'].$purl['path'])] = $navid;
		} elseif($purl['path']) {
			$data['navmn'][$purl['path']] = $navid;
		}
		if($nav['type'] == 0) {
			$domainkey = substr($purl['path'], 0, -strlen(strrchr($purl['path'], '.')));
			if(!empty($_G['setting']['domain']['app'][$domainkey]) && !in_array(strtolower($nav['title']), ['follow', 'guide', 'collection', 'blog', 'album', 'favorite', 'friend', 'share', 'doing'])) {
				$nav['url'] = $_G['scheme'].'://'.$_G['setting']['domain']['app'][$domainkey];
			}
		}

		$data['navs'][$id]['data'] = $_nav;
		$data['navs'][$id]['navid'] = $navid;
		$data['navs'][$id]['level'] = $nav['level'];
		$data['navs'][$id]['nav'] = "id=\"$navid\" ".($onmouseover ? 'onmouseover="'.$onmouseover.'"' : '')."><a href=\"$nav[url]\" hidefocus=\"true\" ".($nav['title'] ? "title=\"$nav[title]\" " : '').($nav['target'] == 1 ? "target=\"_blank\" " : '')." $nav[style]>$nav[name]".($nav['identifier'] == 5 && $nav['type'] == 0 ? '<b class="icon_down"></b>' : '').'</a';
	}
	$data['menunavs'] = implode('', $data['menunavs']);

	return [$data['navs'], $data['subnavs'], $data['menunavs'], $data['navmns'], $data['navmn'], $data['navdms'], $data['navlogos']];

}

function get_cachedata_footernav() {
	global $_G;

	$data['footernavs'] = [];
	foreach(table_common_nav::t()->fetch_all_by_navtype(1) as $nav) {
		$_nav = $nav;
		$nav['extra'] = '';
		if(!$nav['type']) {
			if($nav['identifier'] == 'report') {
				$nav['url'] = 'javascript:;';
				$nav['extra'] = ' onclick="showWindow(\'miscreport\', \'misc.php?mod=report&url=\'+REPORTURL);return false;"';
			} elseif($nav['identifier'] == 'archiver') {
				if(!$_G['setting']['archiver']) {
					continue;
				} else {
					$domain = $_G['setting']['domain']['app']['forum'] ? $_G['setting']['domain']['app']['forum'] : ($_G['setting']['domain']['app']['default'] ? $_G['setting']['domain']['app']['default'] : '');
					$nav['url'] = ($domain ? $_G['scheme'].'://'.$domain.'/' : '').$nav['url'];
				}
			}
		}
		$nav['code'] = '<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').' '.parsehighlight($nav['highlight']).$nav['extra'].'>'.$nav['name'].'</a>';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['footernavs'][$id] = ['data' => $_nav, 'available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'type' => $nav['type'], 'level' => $nav['level'], 'id' => $nav['identifier']];
	}
	return $data['footernavs'];
}

function get_cachedata_mfindnav() {
	global $_G;

	$data['mfindnavs'] = [];
	foreach(table_common_nav::t()->fetch_all_by_navtype(5) as $nav) {
		$_nav = $nav;
		$nav['extra'] = '';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['mfindnavs'][$id] = ['data' => $_nav, 'available' => $nav['available'], 'navname' => $nav['name'], 'url' => $nav['url'], 'name' => $nav['name'], 'type' => $nav['type'], 'level' => $nav['level'], 'id' => $nav['identifier']];
	}
	return $data['mfindnavs'];
}

function get_cachedata_spacenavs() {
	global $_G;
	$data['spacenavs'] = [];
	foreach(table_common_nav::t()->fetch_all_by_navtype(2) as $nav) {
		$_nav = $nav;
		if($nav['available'] < 0) {
			continue;
		}
		if($nav['icon']) {
			$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
			if(!preg_match('/^'.preg_quote(STATICURL, '/').'/i', $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
				$navicon = $nav['icon'].'?'.random(6);
			}
			$nav['icon'] = '<img src="'.$navicon.'" width="16" height="16" />';
		}
		$nav['allowsubnew'] = 1;
		if(!$nav['subname'] || !$nav['suburl'] || str_starts_with($nav['subname'], "\t")) {
			$nav['allowsubnew'] = 0;
			$nav['subname'] = substr($nav['subname'], 1);
		}
		$nav['extra'] = '';
		if(!$nav['type'] && ($nav['identifier'] == 'magic' && !$_G['setting']['magicstatus'] || $nav['identifier'] == 'medal' && !$_G['setting']['medalstatus'])) {
			continue;
		}
		if(!$nav['type'] && $nav['allowsubnew']) {
			if($nav['identifier'] == 'share') {
				$nav['extra'] = ' onclick="showWindow(\'share\', this.href, \'get\', 0);return false;"';
			} elseif($nav['identifier'] == 'thread') {
				$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
			} elseif($nav['identifier'] == 'thread') {
				$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
			} elseif($nav['identifier'] == 'activity') {
				if($_G['setting']['activityforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['activityforumid'].'&special=4';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'poll') {
				if($_G['setting']['pollforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['pollforumid'].'&special=1';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'reward') {
				if($_G['setting']['rewardforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['rewardforumid'].'&special=3';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'debate') {
				if($_G['setting']['debateforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['debateforumid'].'&special=5';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'trade') {
				if($_G['setting']['tradeforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['tradeforumid'].'&special=2';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'credit') {
				$nav['allowsubnew'] = $_G['setting']['ec_ratio'] && payment::enable();
			}
		}
		$nav['subcode'] = $nav['allowsubnew'] ? '<span><a href="'.$nav['suburl'].'"'.($nav['target'] == 1 ? ' target="_blank"' : '').$nav['extra'].'>'.$nav['subname'].'</a></span>' : '';
		if($nav['name'] != '{hr}') {
			$nav['code'] = '<li>'.$nav['subcode'].'<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').'>'.$nav['icon'].$nav['name'].'</a></li>';
		} else {
			$nav['code'] = '</ul><hr class="da" /><ul>';
		}
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['spacenavs'][$id] = ['data' => $_nav, 'available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'level' => $nav['level']];
	}
	return $data['spacenavs'];
}

function get_cachedata_mynavs() {
	global $_G;

	$data['mynavs'] = [];
	foreach(table_common_nav::t()->fetch_all_by_navtype(3) as $nav) {
		$_nav = $nav;
		if($nav['available'] < 0) {
			continue;
		}
		if($nav['icon']) {
			$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
			if(!preg_match('/^'.preg_quote(STATICURL, '/').'/i', $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
				$navicon = $nav['icon'].'?'.random(6);
			}
			$navicon = preg_match('/^(https?:)?\/\//i', $navicon) ? $navicon : $_G['siteurl'].$navicon;
			$nav['icon'] = ' style="background-image:url('.$navicon.') !important"';
		}
		$nav['code'] = '<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').$nav['icon'].'>'.$nav['name'].'</a>';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['mynavs'][$id] = ['data' => $_nav, 'available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'level' => $nav['level'], 'icon' => $nav['icon']];
	}
	return $data['mynavs'];
}

function get_cachedata_topnav() {
	global $_G;

	$data['topnavs'] = [];
	foreach(table_common_nav::t()->fetch_all_by_navtype(4) as $nav) {
		$_nav = $nav;
		$nav['extra'] = '';
		if(!$nav['type']) {
			if($nav['identifier'] == 'sethomepage') {
				$nav['url'] = 'javascript:;';
				$nav['extra'] = ' onclick="setHomepage(\''.$_G['siteurl'].'\');"';
			} elseif($nav['identifier'] == 'setfavorite') {
				$nav['url'] = $_G['siteurl'];
				$nav['extra'] = ' onclick="addFavorite(this.href, \''.addslashes($_G['setting']['bbname']).'\');return false;"';
			}
		}
		$nav['code'] = '<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').' '.parsehighlight($nav['highlight']).$nav['extra'].'>'.$nav['name'].'</a>';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['topnavs'][$nav['subtype']][$id] = ['data' => $_nav, 'available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'type' => $nav['type'], 'level' => $nav['level'], 'id' => $nav['identifier']];
	}
	return $data['topnavs'];
}

function get_cachedata_mnav() {
	global $_G;

	$data['mnavs'] = [];
	foreach(table_common_nav::t()->fetch_all_by_navtype(6) as $nav) {
		$_nav = $nav;
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		if(strpos($nav['icon'], 'http://') === 0 || strpos($nav['icon'], 'https://') === 0 || strpos($nav['icon'], './') === 0 || strpos($nav['icon'], '../') === 0 || strpos($nav['icon'], '/') === 0) {
			if(strpos($nav['icon'], 'http://') !== 0 && strpos($nav['icon'], 'https://') !== 0) {
				$nav['icon'] = $_G['siteurl'].$nav['icon'];
			}
			$nav['icon'] = '<img src="'.$nav['icon'].'" alt="'.$nav['name'].'" />';
		}
		$data['mnavs'][$id] = ['data' => $_nav, 'available' => $nav['available'], 'navname' => $nav['name'], 'url' => $nav['url'], 'name' => $nav['name'], 'type' => $nav['type'], 'level' => $nav['level'], 'id' => $nav['identifier'], 'icon' => $nav['icon'], 'is_post' => $nav['identifier'] == 'post'];
	}
	return $data['mnavs'];
}

function get_cachedata_threadprofile() {
	global $_G;
	if(!helper_dbtool::isexisttable('forum_threadprofile')) {
		return;
	}
	$threadprofiles = table_forum_threadprofile::t()->fetch_all_threadprofile();
	$threadprofile_group = table_forum_threadprofile_group::t()->fetch_all_threadprofile();
	$data = [];
	foreach($threadprofiles as $id => $threadprofile) {
		if($threadprofile['global']) {
			$data['template'][0] = dunserialize($threadprofile['template']);
		}
	}
	foreach($threadprofile_group as $group) {
		if($threadprofiles[$group['tpid']]) {
			$id = $threadprofiles[$group['tpid']]['global'] ? 0 : $group['tpid'];
			if(!isset($data['template'][$id])) {
				$data['template'][$id] = dunserialize($threadprofiles[$group['tpid']]['template']);
			}
			if($id) {
				$data['groupid'][$group['gid']] = $id;
			}
		}
	}
	foreach($data['template'] as $id => $template) {
		foreach($template as $type => $row) {
			$data['template'][$id][$type] = preg_replace_callback(
				'/\{([\w:]+)(=([^}]+?))?\}(([^}]+?)\{\*\}([^}]+?)\{\/\\1\})?/s',
				function($matches) use ($id, $type) {
					return get_cachedata_threadprofile_nodeparse(intval($id), ''.addslashes($type).'', $matches[1], $matches[5], $matches[6], $matches[3]);
				},
				$template[$type]
			);
		}
	}
	$data['code'] = $_G['cachedata_threadprofile_code'];
	return $data;
}

function get_cachedata_threadprofile_nodeparse($id, $type, $name, $s, $e, $extra) {
	$s = $s ? stripslashes($s) : '';
	$e = $e ? stripslashes($e) : '';
	$extra = $extra ? stripslashes($extra) : '';
	global $_G;
	$hash = random(8);
	$_G['cachedata_threadprofile_code'][$id][$type]['{'.$hash.'}'] = [$name, $s, $e, $extra];
	return '{'.$hash.'}';
}

function get_cachedata_discuzcode_parseflv($mediasetting, $plugins) {
	$mediadir = DISCUZ_ROOT.'./source/class/media';
	$parseflv = [];
	if(file_exists($mediadir)) {
		$mediadirhandle = dir($mediadir);
		while($entry = $mediadirhandle->read()) {
			if(!in_array($entry, ['.', '..']) && preg_match('/^media\_([\_\w]+)\.php$/', $entry, $entryr) && str_ends_with($entry, '.php') && is_file($mediadir.'/'.$entry)) {
				if(isset($mediasetting['system']) &&
					(empty($mediasetting['system']) || !in_array($entryr[1], $mediasetting['system']))) {
					continue;
				}
				$c = 'media_'.$entryr[1];
				if(class_exists($c) && property_exists($c, 'checkurl')) {
					$parseflv[$entryr[1]] = $c::$checkurl;
				}
			}
		}
	}
	if(!empty($mediasetting['plugin']) && !empty($plugins['available'])) {
		foreach($mediasetting['plugin'] as $v) {
			[$pluginid, $t] = explode(':', $v);
			if(!in_array($pluginid, $plugins['available'])) {
				continue;
			}
			$c = $pluginid.'\media_'.$t;
			if(class_exists($c) && property_exists($c, 'checkurl')) {
				$parseflv[$pluginid.':'.$t] = $c::$checkurl;
			}
		}
	}

	return $parseflv;
}

function init_i18n(&$data) {
	global $_G;

	$jslangs = [
		'default' => DISCUZ_ROOT.'./source/i18n/'.currentlang().'/lang_js.php',
	];

	foreach(glob(DISCUZ_ROOT.'./source/i18n/*') as $path) {
		if(!is_dir($path)) {
			continue;
		}
		$langkey = basename($path);
		$data['i18n'][$langkey] = $path;
		$jslangs[$langkey] = $path.'/lang_js.php';
	}
	$data['i18nLang'] = [];
	if(!empty($data['i18ns'])) {
		foreach($data['i18n'] as $langkey => $path) {
			if(!in_array($langkey, $data['i18ns'])) {
				continue;
			}
			$lang = i18n::getLang('lang.php', $langkey);
			$data['i18nLang'][$langkey] = !empty($lang['name']) ? $lang['name'] : $langkey;
			$jslangs[$langkey] = i18n::getLang('lang_js.php', $langkey);
		}
	}

	$cachedir = DISCUZ_DATA.'./cache/';
	if(!is_dir($cachedir)) {
		dmkdir($cachedir);
	}
	foreach($jslangs as $langkey => $file) {
		$lang_js = $lang = [];
		if(!is_array($file)) {
			if(!file_exists($file)) {
				continue;
			}

			require $file;
			$lang_js = $lang;
		} else {
			$lang_js = $file;
		}
		foreach(glob(MITFRAME_APP().'/*') as $appdir) {
			if(!is_dir($appdir)) {
				continue;
			}
			$f = $appdir.'/i18n/'.($langkey == 'default' ? currentlang() : $langkey).'/lang_js.php';
			if(!file_exists($f)) {
				continue;
			}
			$lang = [];
			require $f;
			$lang_js = array_merge($lang_js, $lang);
		}
		foreach($data['plugins']['available'] as $plugin) {
			$f = DISCUZ_PLUGIN($plugin).'/i18n/'.($langkey == 'default' ? currentlang() : $langkey).'/lang_js.php';
			if(!file_exists($f)) {
				continue;
			}
			$lang = [];
			require $f;
			$lang_js = array_merge($lang_js, $lang);
		}
		$tpldirs = [];
		foreach(table_common_style::t()->fetch_all_data(true) as $style) {
			$tpldirs[$style['directory']] = $style['directory'];
		}
		foreach($tpldirs as $tpldir) {
			$f = DISCUZ_TEMPLATE($tpldir).'/i18n/'.($langkey == 'default' ? currentlang() : $langkey).'/lang_js.php';
			if(!file_exists($f)) {
				continue;
			}
			$lang = [];
			require $f;
			$lang_js = array_merge($lang_js, $lang);
		}

		$entry = 'lang_'.$langkey.'.js';
		$content = 'var _JSLANG_ = '.json_encode($lang_js, JSON_UNESCAPED_UNICODE).';';
		if(file_put_contents($cachedir.$entry, $content, LOCK_EX) === false) {
			exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
		}
		if(defined('IN_UPDATECACHE')) {
			oss::writeCache($entry);
		}
		if(!empty($_G['config']['plugindeveloper'])) {
			file_put_contents(DISCUZ_ROOT.'./static/js/'.$entry, $content, LOCK_EX);
		}
	}
}

function writetojscache() {
	$dir = DISCUZ_ROOT.'static/js/';
	$dh = opendir($dir);
	$remove = [
		[
			'/(^|\r|\n)\/\*.+?\*\/(\r|\n)/is',
			"/([^\\\:]{1})\/\/.+?(\r|\n)/",
			'/\/\/note.+?(\r|\n)/i',
			'/\/\/debug.+?(\r|\n)/i',
			'/(^|\r|\n)(\s|\t)+/',
			'/(\r|\n)/',
		], [
			'',
			'\1',
			'',
			'',
			'',
			'',
		]];
	while(($entry = readdir($dh)) !== false) {
		if(fileext($entry) == 'js' && filesize($dir.$entry)) {
			$jsfile = $dir.$entry;
			$fp = fopen($jsfile, 'r');
			$jsdata = fread($fp, filesize($jsfile));
			fclose($fp);
			$jsdata = preg_replace($remove[0], $remove[1], $jsdata);
			if($entry == 'common.js') {
				$jsdata = _appendjsvar().$jsdata;
			}
			$cachedir = DISCUZ_DATA.'./cache/';
			if(!is_dir($cachedir)) {
				dmkdir($cachedir);
			}
			if(file_put_contents($cachedir.$entry, $jsdata, LOCK_EX) === false) {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
			if(defined('IN_UPDATECACHE')) {
				oss::writeCache($entry);
			}
		}
	}
}

function _appendjsvar() {
	global $_G;
	$s = [];
	$defaultavatar = $_G['setting']['defaultavatar'];
	$s[] = "DEFAULTAVATAR = '$defaultavatar'";
	$pwaon = !empty($_G['setting']['pwa']['allow']) ? 1 : 0;
	$s[] = "PWAON = $pwaon";
	return 'var '.implode(',', $s).";\n";
}

function pluginmodulecmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

function hooksortcmp($a, $b) {
	return $a[99] > $b[99] ? 1 : -1;
}

function parsehighlight($highlight) {
	if($highlight) {
		$colorarray = ['', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray'];
		$string = sprintf('%02d', $highlight);
		$stylestr = sprintf('%03b', $string[0]);

		$style = ' style="';
		$style .= $stylestr[0] ? 'font-weight: bold;' : '';
		$style .= $stylestr[1] ? 'font-style: italic;' : '';
		$style .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$style .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
		$style .= '"';
	} else {
		$style = '';
	}
	return $style;
}

