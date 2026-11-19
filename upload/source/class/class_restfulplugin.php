<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') && !defined('IN_RESTFUL')) {
	exit('Access Denied');
}

class restfulplugin {

	public static function discuzcode($type) {
		global $_G;

		$p = func_get_args();
		if(in_array($type, ['audio', 'video', 'flv'])) {
			$url = self::_f_siteurl($p[1]);
		} else {
			$attach = &$p[1];
		}

		$s = '';
		switch($type) {
			case 'audio':
				$s = '<audio controls ><source src="'.$url.'"></audio>';
				break;
			case 'video':
				$s = '<video controls width="100%" height="auto"><source src="'.$url.'"></video>';
				break;
			case 'flv':
				$s = '<a href="'.$url.'" target="_blank">'.$url.'</a>';
				break;
			case 'attach':
				$s = '<a href="'.$_G['siteurl'].'forum.php?mod=attachment&aid='.packaids($attach).'" target="_blank">'.($attach['filename']).'</a>';
				break;
			case 'img':
				$s = '<img src="'.self::_f_getImageUrl($attach).'" '.($attach['width'] ? 'width="'.$attach['width'].'" ' : '').($attach['height'] ? 'height="'.$attach['height'].'" ' : '').' />';
				break;
		}
		return $s;
	}

	public static function avatar(&$data, $param) {
		global $_G;
		if(empty($_POST['avatar']) || !$_G['uid']) {
			return;
		}
		if(!preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['avatar'], $_r)) {
			return;
		}
		$content = base64_decode(str_replace($_r[1], '', $_POST['avatar']));
		dmkdir(DISCUZ_DATA.'./avatar/');
		$tmpFile = DISCUZ_DATA.'./avatar/'.TIMESTAMP.random(6);
		file_put_contents($tmpFile, $content);

		if(is_file($tmpFile)) {
			$account = new account_base();

			if($account->set_avatar($_G['uid'], $tmpFile)) {
				table_common_member::t()->update($_G['uid'], [
					'avatarstatus' => '1'
				]);
			}
			unlink($tmpFile);
		}
	}

	public static function avatarUrl(&$data, $param) {
		if(empty($data[$param[0]])) {
			return;
		}
		$keys = [];
		foreach(explode('|', $param[1]) as $key) {
			[$fk, $nk] = explode('/', $key);
			$keys[$fk] = $nk;
		}
		foreach($keys as $fk => $nk) {
			if(isset($data[$param[0]][$fk])) {
				$data[$param[0]][$nk] = self::_f_siteurl(avatar($data[$param[0]][$fk], 'middle', 1));
			}
		}
		foreach($data[$param[0]] as $k => $v) {
			foreach($keys as $fk => $nk) {
				if(isset($v[$fk])) {
					$data[$param[0]][$k][$nk] = self::_f_siteurl(avatar($v[$fk], 'middle', 1));
				}
			}
		}
	}


	public static function searchList(&$data, $param) {
		if(empty($data[$param[0]])) {
			return;
		}
		foreach($data[$param[0]] as $k => $v) {
			$data[$param[0]][$k]['subject'] = strip_tags($data[$param[0]][$k]['subject']);
			$data[$param[0]][$k]['message'] = strip_tags($data[$param[0]][$k]['message']);
		}
	}

	public static function getAttach(&$data, $param) {
		if(empty($data[$param[0]])) {
			return;
		}
		global $_G;
		$attachs = get_attach($data[$param[0]]);
		foreach($data[$param[0]] as $k => $v) {
			if(!empty($attachs[$v['tid']]['attachment'])) {
				foreach($attachs[$v['tid']]['attachment'] as $_k => $_v) {
					$attachs[$v['tid']]['images'][$_k] = $_G['siteurl'].$_v;
				}
			}
			$data[$param[0]][$k] = array_merge($data[$param[0]][$k], $attachs[$v['tid']]);
		}
	}

	public static function userField(&$data, $param) {
		if(empty($data[$param[0]])) {
			return;
		}
		$keys = [];
		foreach(explode('|', $param[1]) as $key) {
			$keys[] = $key;
		}
		$return = [];
		foreach($data[$param[0]] as $k => $v) {
			foreach($keys as $key) {
				if($key == 'avatar') {
					$return[$k][$key] = self::_f_siteurl(avatar($v['uid'], 'middle', 1));
					continue;
				}
				$return[$k][$key] = $v[$key];
			}
		}
		$data[$param[0]] = $return;
	}

	public static function userInfo(&$data) {
		global $_G;

		if(empty($_G) || !$_G['uid'] || empty($data) || empty($data['user'])) {
			return;
		}
		$data['user']['adminid'] = $_G['member']['adminid'];
		$data['user']['credits'] = $_G['member']['credits'];
		$data['user']['secmobicc'] = $_G['member']['secmobicc'];
		$data['user']['secmobile'] = $_G['member']['secmobile'];
		$data['user']['groupname'] = $_G['group']['grouptitle'];
		$data['user']['allowadmincp'] = $_G['member']['allowadmincp'];
		$data['user']['freeze'] = $_G['member']['freeze'];
		$data['user']['regdate'] = $_G['member']['regdate'];
		$data['user']['lastvisit'] = $_G['member']['lastvisit'];
		$data['user']['avatar'] = self::_f_siteurl(avatar($_G['uid'], 'middle', 1));
	}

	public static function addDomain(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key]) || empty($param[1])) {
			return;
		}

		foreach($data[$key] as $k => $v) {
			if(empty($v[$param[1]])) {
				continue;
			}
			$data[$key][$k][$param[1]] = preg_replace_callback('/href="(\w+)\.php/i', function($m) {
				global $_G;
				return 'href="'.$_G['siteurl'].$m[1].'.php';
			}, $v[$param[1]]);
		}
	}

	public static function getCredit(&$data) {
		for($i = 1; $i <= 8; $i++) {
			$k = 'extcredits'.$i;
			$data[$k] = dintval(getuserprofile($k));
		}
	}

	public static function threadList(&$data, $param) {
		global $_G;

		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $thread) {
			if($thread['authorid'] > 0 && $thread['author'] === '') {
				$data[$key][$k]['author'] = $_G['setting']['anonymoustext'];
				$data[$key][$k]['authorid'] = $thread['authorid'] = 0;
			}
			$memberinfo = table_common_member::t()->fetch($thread['authorid']);
			space_merge($memberinfo, 'profile');
			$groupinfo = table_common_usergroup::t()->fetch($memberinfo['groupid']);
			$data[$key][$k]['memberinfo'] = $memberinfo;
			unset($data[$key][$k]['memberinfo']['password']);
			$data[$key][$k]['groupinfo'] = $groupinfo;
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($thread['authorid'], 'middle', 1));
			$data[$key][$k]['dateline'] = self::_f_gmdate($thread['dateline']);
			$data[$key][$k]['lastpost'] = self::_f_gmdate($thread['lastpost']);
			if(!empty($thread['images'])) {
				foreach($thread['images'] as $_k => $_v) {
					$data[$key][$k]['images'][$_k] = $_G['siteurl'].$_v;
				}
			}
		}
	}

	public static function postList(&$data, $param) {
		global $_G;
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $post) {
			if(!empty($post['anonymous'])) {
				$data[$key][$k]['username'] = $data[$key][$k]['usernameenc'] = $data[$key][$k]['author'] = $_G['setting']['anonymoustext'];
				$data[$key][$k]['authorid'] = $post['authorid'] = 0;
			}
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($post['authorid'], 'middle', 1));
			$data[$key][$k]['dateline'] = self::_f_gmdate($post['dateline']);
			if(!empty($post['message'])) {
				$data[$key][$k]['message'] = preg_replace_callback(
					'/<a href="(data\/attachment\/forum\/[^"]+\.mp4)"[^>]*>[^<]+<\/a>/i',
					function($matches) {
						return self::discuzcode('video', $matches[1]);
					},
					$data[$key][$k]['message']
				);
			}

			if(empty($post['attachments'])) {
				continue;
			}
			$listaids = array_merge($data[$key][$k]['imagelist'], $data[$key][$k]['attachlist']);
			$data[$key][$k]['imagelist'] = $data[$key][$k]['attachlist'] = $data[$key][$k]['attachments'] = [];
			foreach($post['attachments'] as $_k => $attach) {
				if(!in_array($attach['aid'], $listaids)) {
					continue;
				}
				$data[$key][$k]['attachments'][$_k]['downurl'] = $_G['siteurl'].'forum.php?mod=attachment&aid='.packaids($attach);
				if($attach['isimage']) {
					$data[$key][$k]['imagelist'][$attach['aid']] = $_G['siteurl'].getforumimg($attach['aid'], 0, 300, 300);
				} else {
					$data[$key][$k]['attachlist'][$attach['aid']] = [
						'attachsize' => $attach['attachsize'],
						'filename' => $attach['filename'],
					];
				}
			}
		}
	}

	public static function myPostList(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		$list = &$data[$key];
		$key = $param[1];
		if(empty($data[$key])) {
			return;
		}
		$post = &$data[$key];
		$posts = [];
		foreach($post as $row) {
			$posts[$row['tid']][] = [
				'pid' => dintval($row['pid']),
				'dateline' => self::_f_gmdate($row['dateline']),
				'message' => $row['message'],
			];
		}
		foreach($list as $k => $v) {
			$list[$k]['posts'] = $posts[$v['tid']];
		}
	}

	public static function forumList(&$data, $param) {
		global $_G;

		$catKey = $param[0];
		$forumKey = $param[1];
		if(empty($data[$catKey]) || empty($data[$forumKey])) {
			return;
		}

		$forumlist = $data[$catKey];

		foreach($data[$forumKey] as $k => $forum) {
			if(!empty($forum['iconUri'])) {
				$data[$forumKey][$k]['icon'] = self::_f_forumimg($forum['iconUri']);
			}
			unset($data[$forumKey][$k]['iconUri']);

			$data[$forumKey][$k]['lastpost'] = $forum['lastpost'] ? [
				'tid' => $forum['lastpost']['tid'],
				'subject' => $forum['lastpost']['subject'],
				'dateline' => $forum['lastpost']['dateline'],
				'authorusername' => $forum['lastpost']['authorusername'],
			] : [];

			if($forum['type'] == 'forum' && !empty($forum['subforums'])) {
				foreach($forum['subforums'] as $k_sub => $forum_sub) {
					if(!empty($forum_sub['iconUri'])) {
						$data[$forumKey][$k]['subforums'][$k_sub]['icon'] = self::_f_forumimg($forum_sub['iconUri']);
					}
					unset($data[$forumKey][$k]['subforums'][$k_sub]['iconUri']);
				}
			}
			unset($forumlist[$forum['fup']]['forums']);
			$forumlist[$forum['fup']]['subforums'][] = $data[$forumKey][$k];
		}
		$forumlist = array_values($forumlist);
		unset($data[$catKey]);
		$data[$forumKey] = $forumlist;
	}

	public static function foruminfo(&$data, $param) {
		global $_G;
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		// unset($data[$key]['password']);
		$forum_favorite = table_home_favorite::t()->fetch_by_id_idtype($_G['forum']['fid'], 'fid', $_G['uid']);
		if($forum_favorite['favid'] > 0) {
			$data[$key]['isfavorite'] = 1;
		}
		$data[$key]['icon'] = self::_f_forumimg($data[$key]['icon']);
		$data[$key]['banner'] = self::_f_siteurl($data[$key]['banner']);
	}

	public static function groupforumInfo(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		// unset($data[$key]['password']);
		$data[$key]['icon'] = self::_f_siteurl($data[$key]['icon']);
		$data[$key]['banner'] = self::_f_siteurl($data[$key]['banner']);
	}

	public static function thread(&$data, $param) {
		global $_G;

		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		if($data[$key]['authorid'] > 0 && $data[$key]['author'] === '') {
			$data[$key]['author'] = $_G['setting']['anonymoustext'];
			$data[$key]['authorid'] = 0;
		}
		$data[$key]['dateline'] = self::_f_gmdate($data[$key]['dateline']);
		$data[$key]['lastpost'] = self::_f_gmdate($data[$key]['lastpost']);
		$data[$key]['isfavorite'] = $data[$key]['isrecommendav'] = 0;
		$recommendav_add = table_forum_memberrecommend::t()->fetch_by_recommenduid_tid($_G['uid'], $_G['tid']);
		$thread_favorite = table_home_favorite::t()->fetch_by_id_idtype($_G['tid'], 'tid', $_G['uid']);
		if($thread_favorite['favid'] > 0) {
			$data[$key]['isfavorite'] = 1;
		}
		if($recommendav_add['recommenduid'] > 0) {
			$data[$key]['isrecommendav'] = 1;
		}
		//投票帖
		if($data[$key]['special'] == 1 && !empty($data['polloptions'])) {
			$options = $data['polloptions'];
			foreach($data['polloptions'] as $k => $v) {
				if($v['votes'] == 0) {
					$options[$k]['width'] = '';
				}
				if(!$v['imginfo']) {
					unset($options[$k]['imginfo']);
					continue;
				}
				$options[$k]['imginfo']['small'] = self::_f_siteurl($v['imginfo']['small']);
				$options[$k]['imginfo']['big'] = self::_f_siteurl($v['imginfo']['big']);
			}
			$data['polloptions'] = [
				'options' => $options,
				'isimagepoll' => $data['isimagepoll'],
				'voterscount' => $data['voterscount'],
				'maxchoices' => $data['maxchoices'],
				'multiple' => $data['multiple'],
			];
			unset($data['isimagepoll'], $data['voterscount'], $data['maxchoices'], $data['multiple']);
		}

		//悬赏帖
		if($data[$key]['special'] == 3) {
			$data['bestpost']['authoravatar'] = self::_f_siteurl(avatar($data['bestpost']['authorid'], 'middle', 1));
			$data['bestpost']['dateline'] = self::_f_gmdate($data['bestpost']['dateline']);
			$data['rewardoptions'] = [
				'rewardprice' => $data['rewardprice'],
				'credits' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]],
				'price' => $_G['forum_thread']['price'],
				'bestpost' => $data['bestpost'],
			];
			unset($data['rewardprice'], $data['bestpost']);
		}

		//活动帖
		if($data[$key]['special'] == 4) {
			$data['activityoptions'] = $GLOBALS['activity'];
			$data['activityoptions']['thumb'] = $data['activityoptions']['thumb'] ? self::_f_siteurl($data['activityoptions']['thumb']) : '';
			$data['activityoptions']['attachurl'] = $data['activityoptions']['attachurl'] ? self::_f_siteurl($data['activityoptions']['attachurl']) : '';
		}

		//辩论帖
		if($data[$key]['special'] == 5) {
			$data['debateoptions'] = $GLOBALS['debate'];
		}
	}

	public static function portalBefore(&$data) {
		$_ENV['cells']['forum_portal_threadlist'] = ['message' => true, 'image' => true];
	}

	public static function setUploadHash(&$data, $param) {
		global $_G;

		$data['uploadhash'] = md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid']);
	}

	public static function registerBefore(&$data) {
		if(empty($_POST['reginput'])) {
			return;
		}
		$reginput = json_decode($_POST['reginput'], true);
		if(!is_array($reginput)) {
			return;
		}
		foreach($reginput as $k => $v) {
			$_POST[$k] = $v;
		}
	}

	public static function arrayAssoc2List(&$data, $param) {
		foreach($param as $key) {
			if(!isset($data[$key]) || !is_array($data[$key])) {
				continue;
			}
			$new = [];
			foreach($data[$key] as $k => $v) {
				$new[] = ['key' => $k, 'value' => $v];
			}
			$data[$key] = $new;
		}

		if(!empty($data[$key])) {
			$data[$key] = array_values($data[$key]);
		}
	}

	public static function arrayValues(&$data, $param) {
		global $_G;

		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		if(!empty($data[$key])) {
			$data[$key] = array_values($data[$key]);
		}
	}

	private static function _f_getImageUrl($attach) {
		global $_G;

		$refcheck = (!$attach['remote'] && $_G['setting']['attachrefcheck']) || ($attach['remote'] && ($_G['setting']['ftp']['hideurl'] || ($attach['isimage'] && $_G['setting']['attachimgpost'] && strtolower(substr($_G['setting']['ftp']['attachurl'], 0, 3)) == 'ftp')));
		if($refcheck) {
			$aidencode = packaids($attach);
			$url = $_G['siteurl'].'forum.php?mod=attachment&aid='.$aidencode.'&noupdate=yes&nothumb=yes';
		} else {
			$url = $attach['url'].$attach['attachment'];
		}
		return self::_f_siteurl($url);
	}

	private static function _f_siteurl($url) {
		global $_G;

		if(!str_starts_with($url, 'http') && $url) {
			$url = $_G['siteurl'].$url;
		}
		return $url;
	}

	private static function _f_gmdate($v) {
		if(is_numeric($v)) {
			$v = dgmdate($v);
		}
		return str_replace('&nbsp;', ' ', strip_tags($v));
	}

	private static function _f_forumimg($v) {
		return $v ? self::_f_siteurl(get_forumimg($v)) : '';
	}

	public static function portalBlockItem(&$data) {
		global $_G;
		$CachekeyPre = 'blockApi_';
		$TTL = 300;
		$data = ['bid' => 0, 'itemlist' => []];
		$bid = intval($_GET['bid']);
		if(empty($bid)) {
			$name = daddslashes($_GET['name']);
			if(empty($name)) {
				return;
			}
			$block = table_common_block::t()->fetch_by_name($name);
			if(empty($block)) {
				return;
			}
			$bid = $block['bid'];
		}
		if(empty($bid)) {
			return;
		}
		$data['bid'] = $bid;
		$m_data = memory('get', $CachekeyPre.$bid);
		if(!empty($m_data['v'])) {
			$data['itemlist'] = $m_data['v'];
			return;
		}

		include_once libfile('function/block');
		block_get_batch($bid);
		block_updatecache($bid);

		$itemlist = [];
		foreach($_G['block'][$bid]['itemlist'] as $item) {
			$item['fields'] = dunserialize($item['fields']);
			$item['pic'] = self::_f_siteurl((!str_starts_with($item['pic'], 'http') ? $_G['setting']['attachurl'] : '').$item['pic']);
			$item['thumbpath'] = self::_f_siteurl((!str_starts_with($item['thumbpath'], 'http') ? $_G['setting']['attachurl'] : '').$item['thumbpath']);
			$itemlist[] = $item;
		}

		memory('set', $CachekeyPre.$bid, ['v' => $itemlist], $TTL);
		$data['itemlist'] = $itemlist;
	}

	public static function siteSetting(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		if (!empty($data[$key]['myrepeats']['usergroups'])) {
			$data[$key]['myrepeats']['usergroups'] = (array)dunserialize($data[$key]['myrepeats']['usergroups']);
		}

	}

	public static function portalList(&$data, $param) {
		$wheresql = category_get_wheresql($GLOBALS['cat']);
		$tmp = category_get_list($GLOBALS['cat'], $wheresql, $GLOBALS['page']);
		$data['list'] = $tmp['list'];
		foreach($data['list'] as $k => $item) {
			$data['list'][$k]['pic'] = self::_f_siteurl($item['pic']);
		}
	}

	public static function portalView(&$data, $param) {
		$data['article']['pic'] = self::_f_siteurl($data['article']['pic']);
		$data['content']['content'] = preg_replace_callback('/"(data\/.+?)"/', function($matches) {
			return '"'.self::_f_siteurl($matches[1]).'"';
		}, $data['content']['content']);
	}

	public static function applylist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['uid'], 'middle', 1));
		}
	}

	public static function tasklist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['icon'] = self::_f_siteurl($v['icon']);
		}
	}

	public static function taskView(&$data, $param) {

		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		if(!empty($data[$key]['icon'])) {
			$data[$key]['icon'] = self::_f_siteurl($data[$key]['icon']);
		}
	}

	public static function bloglist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['pic'] = self::_f_siteurl($v['pic']);
		}
	}

	public static function blogView(&$data, $param) {
		global $_G;
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		$blog_favorite = table_home_favorite::t()->fetch_by_id_idtype($data[$key]['blogid'], 'blogid', $_G['uid']);
		if($blog_favorite['favid'] > 0) {
			$data[$key]['isfavorite'] = 1;
		}
		if($data[$key]['uid']) {
			$data[$key]['authoravatar'] = self::_f_siteurl(avatar($data[$key]['uid'], 'middle', 1));
		}
		if($data[$key]['pic']) {
			$data[$key]['pic'] = self::_f_siteurl($data[$key]['pic']);
		}
	}

	public static function blogCommentlist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['authorid'], 'middle', 1));
		}
	}

	public static function albumlist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['pic'] = self::_f_siteurl($v['pic']);
		}
	}

	public static function albumView(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		if($data[$key]['uid']) {
			$data[$key]['authoravatar'] = self::_f_siteurl(avatar($data[$key]['uid'], 'middle', 1));
		}
	}

	public static function albumViewlist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['pic'] = self::_f_siteurl($v['pic']);
		}
	}

	public static function medallist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		global $medalcredits, $mymedals;
		foreach($data[$key] as $k => $v) {
			if(is_array($mymedals) && in_array($v['medalid'], $mymedals)) {
				if($v['price'] > 0) {
					$data[$key][$k]['havemedal'] = 1;
				} else {
					if($v['type'] == 2) {
						$data[$key][$k]['havemedal'] = 2;
					} else {
						$data[$key][$k]['havemedal'] = 3;
					}
				}
			}
			$data[$key][$k]['image'] = self::_f_siteurl($v['image']);
		}

		foreach($medalcredits as $id) {
			$data['mycredits'][$id] = getuserprofile('extcredits'.$id);
		}
	}

	public static function lastmedals(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		global $medallist, $lastmedalusers;
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['image'] = self::_f_siteurl(avatar($v['image'], 'middle', 1));
			$data[$key][$k]['medalname'] = $medallist[$v['medalid']]['name'];
			$data[$key][$k]['username'] = $lastmedalusers[$v['uid']]['username'];
		}
	}

	public static function medallogs(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		global $medallist, $lastmedalusers;
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['image'] = self::_f_siteurl(avatar($v['image'], 'middle', 1));
			$data[$key][$k]['medalname'] = $medallist[$v['medalid']]['name'];
			$data[$key][$k]['username'] = $lastmedalusers[$v['uid']]['username'];
		}
	}

	public static function magiclist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		// 按照模板逻辑处理 magiccredits
		if(!empty($data['magiccredits']) && is_array($data['magiccredits'])) {
			$magiccredits = [];
			foreach($data['magiccredits'] as $id => $value) {
				// 获取用户对应积分的余额
				$magiccredits[$id] = getuserprofile('extcredits'.$id);
			}
			$data['magiccredits'] = $magiccredits;
		}
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['pic'] = self::_f_siteurl($v['pic']);
		}
	}

	public static function rankmemberlist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['uid'], 'middle', 1));
		}
	}

	public static function rankactivitylist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['attachurl'] = self::_f_siteurl(($v['attachurl']));
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['authorid'], 'middle', 1));
			$data[$key][$k]['dateline'] = self::_f_gmdate($k['dateline']);
		}
	}

	public static function rankpolllist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['authorid'], 'middle', 1));
		}
	}

	public static function lastupdategroup(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['iconurl'] = self::_f_siteurl(($v['icon']));
		}
	}

	public static function group_recommend(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		$data[$key] = dunserialize($data[$key]);
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['iconurl'] = self::_f_siteurl(($v['icon']));
		}
	}

	public static function groupmemberlist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['uid'], 'middle', 1));
		}
		if(!empty($data[$key])) {
			$data[$key] = array_values($data[$key]);
		}
	}
	public static function collection(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		if($data[$key]['cover']) {
			$data[$key]['cover'] = self::_f_siteurl($data[$key]['cover']);
		}
		if($data[$key]['icon']) {
			$data[$key]['icon'] = self::_f_siteurl($data[$key]['icon']);
		}
		$data[$key]['dateline'] = self::_f_gmdate($data[$key]['dateline']);
	}
	public static function collectiontids(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['authorid'], 'middle', 1));
		}
	}
	public static function dolist(&$data, $param) {
		global $_G;
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			if(!empty($v['message'])) {
				$data[$key][$k]['message'] = preg_replace_callback('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function($matches) {
					$src = $matches[1];
					// 如果不是完整的 URL，则添加站点 URL 前缀
					if (!preg_match('/^(http|https):\/\//', $src)) {
						$src = self::_f_siteurl($src);
					}
					return str_replace($matches[1], $src, $matches[0]);
				}, $v['message']);
			}
			
			if (!empty($v['uid'])) {
				$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['uid'], 'middle', 1));
			}
			if (!empty($v['body_data'])) {
				$data[$key][$k]['body_data']['image'] = self::_f_siteurl($v['body_data']['image']);
			}
			if (!empty($v['attachments'])) {
				foreach ($v['attachments'] as $kk => $attachment) {
					if (!empty($attachment['thumb']) && $attachment['isimage']) {
						$data[$key][$k]['attachments'][$kk]['thumb'] = self::_f_siteurl($attachment['thumb']);
						if ($attach['remote']) {
							$data[$key][$k]['attachments'][$kk]['attachment'] = $_G['setting']['ftp']['attachurl']. $attachment['attachment'];
						}else {
							$data[$key][$k]['attachments'][$kk]['attachment'] = self::_f_siteurl($_G['setting']['attachurl'].'doing/'.$attachment['attachment']);
						}
						
					}
				}
			}
		}
	}
	public static function doingUpload(&$data, $param) {
		global $_G;
		
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		if (!empty($data[$key]['image']) && is_array($data[$key]['image'])) {
			$data[$key]['image']['url'] = self::_f_siteurl($data[$key]['image']['url']);
		}
	}
	public static function shareinfo(&$data, $param) {
		global $_G;
		
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		if (!empty($data[$key]['body_data']) && is_array($data[$key]['body_data'])) {
			$data[$key]['body_data']['image'] = self::_f_siteurl($data[$key]['body_data']['image']);
		}
		if (!empty($data[$key]['image']) && is_array($data[$key]['image'])) {
			$data[$key]['image'] = self::_f_siteurl($data[$key]['image']);
		}
		
	}
	public static function clist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $parentKey => $comments) {
			if (!is_array($comments)) continue;
			
			foreach($comments as $k => $v) {
				// 处理消息中的图片 URL
				if(!empty($v['message'])) {
					$data[$key][$parentKey][$k]['message'] = preg_replace_callback('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function($matches) {
						$src = $matches[1];
						// 如果不是完整的 URL，则添加站点 URL 前缀
						if (!preg_match('/^(http|https):\/\//', $src)) {
							$src = self::_f_siteurl($src);
						}
						return str_replace($matches[1], $src, $matches[0]);
					}, $v['message']);
				}
				
				// 如果有作者头像，也处理一下
				if (!empty($v['uid'])) {
					$data[$key][$parentKey][$k]['authoravatar'] = self::_f_siteurl(avatar($v['uid'], 'middle', 1));
				}
			}
		}
	}
	public static function walllist(&$data, $param) {
		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}
		foreach($data[$key] as $k => $v) {
			if(!empty($v['message'])) {
				$data[$key][$k]['message'] = preg_replace_callback('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function($matches) {
					$src = $matches[1];
					// 如果不是完整的 URL，则添加站点 URL 前缀
					if (!preg_match('/^(http|https):\/\//', $src)) {
						$src = self::_f_siteurl($src);
					}
					return str_replace($matches[1], $src, $matches[0]);
				}, $v['message']);
			}
			
			if (!empty($v['authorid'])) {
				$data[$key][$k]['authoravatar'] = self::_f_siteurl(avatar($v['authorid'], 'middle', 1));
			}
		}
	}
	public static function guideThreadList(&$data, $param) {
		global $_G;

		$key = $param[0];
		if(empty($data[$key])) {
			return;
		}

		// 处理 new.threadlist 数据
		if(!empty($data[$key]['new']['threadlist']) && is_array($data[$key]['new']['threadlist'])) {
			foreach($data[$key]['new']['threadlist'] as $k => $thread) {
				// 处理匿名用户
				if($thread['authorid'] > 0 && $thread['author'] === '') {
					$data[$key]['new']['threadlist'][$k]['author'] = $_G['setting']['anonymoustext'];
					$data[$key]['new']['threadlist'][$k]['authorid'] = $thread['authorid'] = 0;
				}

				// 添加用户头像
				$data[$key]['new']['threadlist'][$k]['authoravatar'] = self::_f_siteurl(avatar($thread['authorid'], 'middle', 1));

				// 格式化时间
				$data[$key]['new']['threadlist'][$k]['dateline'] = self::_f_gmdate($thread['dateline']);
				$data[$key]['new']['threadlist'][$k]['lastpost'] = self::_f_gmdate($thread['lastpost']);

				// 处理图片附件
				if(!empty($thread['images'])) {
					foreach($thread['images'] as $_k => $_v) {
						$data[$key]['new']['threadlist'][$k]['images'][$_k] = $_G['siteurl'].$_v;
					}
				}
			}
		}

		// 处理 hot.threadlist 数据
		if(!empty($data[$key]['hot']['threadlist']) && is_array($data[$key]['hot']['threadlist'])) {
			foreach($data[$key]['hot']['threadlist'] as $k => $thread) {
				// 处理匿名用户
				if($thread['authorid'] > 0 && $thread['author'] === '') {
					$data[$key]['hot']['threadlist'][$k]['author'] = $_G['setting']['anonymoustext'];
					$data[$key]['hot']['threadlist'][$k]['authorid'] = $thread['authorid'] = 0;
				}

				// 添加用户头像
				$data[$key]['hot']['threadlist'][$k]['authoravatar'] = self::_f_siteurl(avatar($thread['authorid'], 'middle', 1));

				// 格式化时间
				$data[$key]['hot']['threadlist'][$k]['dateline'] = self::_f_gmdate($thread['dateline']);
				$data[$key]['hot']['threadlist'][$k]['lastpost'] = self::_f_gmdate($thread['lastpost']);

				// 处理图片附件
				if(!empty($thread['images'])) {
					foreach($thread['images'] as $_k => $_v) {
						$data[$key]['hot']['threadlist'][$k]['images'][$_k] = $_G['siteurl'].$_v;
					}
				}
			}
		}

		// 处理 digest.threadlist 数据（如果存在）
		if(!empty($data[$key]['digest']['threadlist']) && is_array($data[$key]['digest']['threadlist'])) {
			foreach($data[$key]['digest']['threadlist'] as $k => $thread) {
				// 处理匿名用户
				if($thread['authorid'] > 0 && $thread['author'] === '') {
					$data[$key]['digest']['threadlist'][$k]['author'] = $_G['setting']['anonymoustext'];
					$data[$key]['digest']['threadlist'][$k]['authorid'] = $thread['authorid'] = 0;
				}

				// 添加用户头像
				$data[$key]['digest']['threadlist'][$k]['authoravatar'] = self::_f_siteurl(avatar($thread['authorid'], 'middle', 1));

				// 格式化时间
				$data[$key]['digest']['threadlist'][$k]['dateline'] = self::_f_gmdate($thread['dateline']);
				$data[$key]['digest']['threadlist'][$k]['lastpost'] = self::_f_gmdate($thread['lastpost']);

				// 处理图片附件
				if(!empty($thread['images'])) {
					foreach($thread['images'] as $_k => $_v) {
						$data[$key]['digest']['threadlist'][$k]['images'][$_k] = $_G['siteurl'].$_v;
					}
				}
			}
		}
		// 处理 sofa.threadlist 数据（如果存在）
		if(!empty($data[$key]['sofa']['threadlist']) && is_array($data[$key]['sofa']['threadlist'])) {
			foreach($data[$key]['sofa']['threadlist'] as $k => $thread) {
				// 处理匿名用户
				if($thread['authorid'] > 0 && $thread['author'] === '') {
					$data[$key]['sofa']['threadlist'][$k]['author'] = $_G['setting']['anonymoustext'];
					$data[$key]['sofa']['threadlist'][$k]['authorid'] = $thread['authorid'] = 0;
				}

				// 添加用户头像
				$data[$key]['sofa']['threadlist'][$k]['authoravatar'] = self::_f_siteurl(avatar($thread['authorid'], 'middle', 1));

				// 格式化时间
				$data[$key]['sofa']['threadlist'][$k]['dateline'] = self::_f_gmdate($thread['dateline']);
				$data[$key]['sofa']['threadlist'][$k]['lastpost'] = self::_f_gmdate($thread['lastpost']);

				// 处理图片附件
				if(!empty($thread['images'])) {
					foreach($thread['images'] as $_k => $_v) {
						$data[$key]['sofa']['threadlist'][$k]['images'][$_k] = $_G['siteurl'].$_v;
					}
				}
			}
		}
		// 处理 newthread.threadlist 数据（如果存在）
		if(!empty($data[$key]['newthread']['threadlist']) && is_array($data[$key]['newthread']['threadlist'])) {
			foreach($data[$key]['newthread']['threadlist'] as $k => $thread) {
				// 处理匿名用户
				if($thread['authorid'] > 0 && $thread['author'] === '') {
					$data[$key]['newthread']['threadlist'][$k]['author'] = $_G['setting']['anonymoustext'];
					$data[$key]['newthread']['threadlist'][$k]['authorid'] = $thread['authorid'] = 0;
				}

				// 添加用户头像
				$data[$key]['newthread']['threadlist'][$k]['authoravatar'] = self::_f_siteurl(avatar($thread['authorid'], 'middle', 1));

				// 格式化时间
				$data[$key]['newthread']['threadlist'][$k]['dateline'] = self::_f_gmdate($thread['dateline']);
				$data[$key]['newthread']['threadlist'][$k]['lastpost'] = self::_f_gmdate($thread['lastpost']);

				// 处理图片附件
				if(!empty($thread['images'])) {
					foreach($thread['images'] as $_k => $_v) {
						$data[$key]['newthread']['threadlist'][$k]['images'][$_k] = $_G['siteurl'].$_v;
					}
				}
			}
		}

	}

	public static function attachNew(&$data, $param) {
		global $_G;
		if(!empty($_POST['attachnew']) && is_string($_POST['attachnew'])) {
			$aids = [];
			$attachnew = explode(',', trim($_POST['attachnew']));
			if($attachnew) {
				foreach($attachnew as $attach) {
					$aids[$attach] = ['description' => '', 'readperm' => '', 'price' => 0];
				}
			}
			if($aids) {
				$_POST['attachnew'] = $aids;
			}
		}
	}

}