<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], ['add', 'list', 'detail', 'search', 'manage', 'set']) ? $_GET['op'] : '';

if(!$op || in_array($op, ['list', 'detail'])) {
	$id = intval($_GET['id']);
	$type = trim($_GET['type']);
	$name = trim($_GET['name']);
	$page = intval($_GET['page']);
	if($type == 'countitem') {
		$num = 0;
		if($id) {
			$num = table_common_tagitem::t()->count_by_tagid($id);
		}
		include_once template('tag/tag');
		exit();
	}
	$taglang = lang('tag/template', 'tag');
	if($id || $name) {

		$tpp = 20;
		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;
		if($id) {
			$tag = table_common_tag::t()->fetch_info($id);
		} else {
			if(!preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $name) || strlen($name) > 20) {
				showmessage('parameters_error');
			}
			$name = addslashes($name);
			$tag = table_common_tag::t()->fetch_info(0, $name);
		}

		if($tag['tagid'] && TIMESTAMP - $tag['updated_at'] > 86400) {
			tag::update_tag_hot_score($tag['tagid']);
		}
		if($tag['status'] == 1) {
			showmessage('tag_closed');
		}
		$tagname = $tag['tagname'];
		$id = $tag['tagid'];
		$searchtagname = $name;
		$navtitle = $tagname ? $taglang.' - '.$tagname : $taglang;
		$metakeywords = $tagname ? $taglang.' - '.$tagname : $taglang;
		$metadescription = $tagname ? $taglang.' - '.$tagname : $taglang;

		$showtype = '';
		$count = '';
		$summarylen = 300;

		if($type == 'thread') {
			$showtype = 'thread';
			$tidarray = $threadlist = [];
			$count = table_common_tagitem::t()->select($id, 0, 'tid', '', '', 0, 0, 0, 1);
			if($count) {
				$query = table_common_tagitem::t()->select($id, 0, 'tid', '', '', $start_limit, $tpp);
				foreach($query as $result) {
					$tidarray[$result['itemid']] = $result['itemid'];
				}
				$threadlist = tag::getthreadsbytids($tidarray);
				$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id={$tag['tagid']}&type=thread");
			}
		} elseif($type == 'article') {
			$showtype = 'article';
			if(helper_access::check_module('portal')) {
				$articleidarray = $articlelist = [];
				$count = table_common_tagitem::t()->select($id, 0, 'articleid', '', '', 0, 0, 0, 1);
				if($count) {
					$query = table_common_tagitem::t()->select($id, 0, 'articleid', '', '', $start_limit, $tpp);
					foreach($query as $result) {
						$articleidarray[$result['itemid']] = $result['itemid'];
					}
					$articlelist = tag::getarticlebyid($articleidarray);

					$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id={$tag['tagid']}&type=article");
				}
			}else{
				showmessage('portal_status_off');
			}
		} elseif($type == 'blog') {
			$showtype = 'blog';
			if(helper_access::check_module('blog')) {
				$blogidarray = $bloglist = [];
				$count = table_common_tagitem::t()->select($id, 0, 'blogid', '', '', 0, 0, 0, 1);
				if($count) {
					$query = table_common_tagitem::t()->select($id, 0, 'blogid', '', '', $start_limit, $tpp);
					foreach($query as $result) {
						$blogidarray[$result['itemid']] = $result['itemid'];
					}
					$bloglist = tag::getblogbyid($blogidarray);

					$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id={$tag['tagid']}&type=blog");
				}
			} else {
				showmessage('blog_status_off');
			}
		} elseif($type == 'doing') {
			$showtype = 'doing';
			require_once libfile('function/doing');
			if(helper_access::check_module('doing')) {
				$doidarray = $dolist = [];
				$count = table_common_tagitem::t()->select($id, 0, 'doid', '', '', 0, 0, 0, 1);
				if($count) {
					$query = table_common_tagitem::t()->select($id, 0, 'doid', '', '', $start_limit, $tpp);
					foreach($query as $result) {
						$doidarray[$result['itemid']] = $result['itemid'];
					}
					$dolists = tag::getdoingbyid($doidarray);

					$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id={$tag['tagid']}&type=doing");
				}
			}else{
				showmessage('doing_status_off');
			}
		} else {
			$shownum = 20;

			$tidarray = $threadlist = [];
			$query = table_common_tagitem::t()->select($id, 0, 'tid', '', '', $shownum);
			foreach($query as $result) {
				$tidarray[$result['itemid']] = $result['itemid'];
			}
			$threadlist = tag::getthreadsbytids($tidarray);

			if(helper_access::check_module('portal')) {
				$articleidarray = $articlelist = [];
				$query = table_common_tagitem::t()->select($id, 0, 'articleid', '', '', $shownum);
				foreach($query as $result) {
					$articleidarray[$result['itemid']] = $result['itemid'];
				}
				$articlelist = tag::getarticlebyid($articleidarray);
			}

			if(helper_access::check_module('blog')) {
				$blogidarray = $bloglist = [];
				$query = table_common_tagitem::t()->select($id, 0, 'blogid', '', '', $shownum);
				foreach($query as $result) {
					$blogidarray[$result['itemid']] = $result['itemid'];
				}
				$bloglist = tag::getblogbyid($blogidarray);
			}

			if(helper_access::check_module('doing')) {
				require_once libfile('function/doing');
				$doidarray = $dolist = [];
				$query = table_common_tagitem::t()->select($id, 0, 'doid', '', '', $shownum);
				foreach($query as $result) {
					$doidarray[$result['itemid']] = $result['itemid'];
				}
				$dolist = tag::getdoingbyid($doidarray);
			}

		}
		include_once template('tag/tagitem');
	} else {
		$navtitle = $metakeywords = $metadescription = $taglang;
		$viewthreadtags = 100;
		$tagarray = [];
		$query = table_common_tag::t()->fetch_all_by_status(0, '', $viewthreadtags, 0, 0, 'DESC');
		foreach($query as $result) {
			$tagarray[] = $result;
		}
		include_once template('tag/tag');
	}
}elseif(in_array($_GET['op'], ['add', 'search', 'manage', 'set'])) {
	global $_G;
	$taglist = [];
	$_GET['tid'] = intval(getgpc('tid'));
	if($_GET['tid'] > 0) {
		$postinfo = table_forum_post::t()->fetch_threadpost_by_tid_invisible($_GET['tid']);
		if($postinfo['tags']) {
			$tagarray_all = $array_temp = $threadtag_array = [];
			$tagarray_all = explode("\t", $postinfo['tags']);
			if($tagarray_all) {
				foreach($tagarray_all as $var) {
					if($var) {
						$array_temp = explode(',', $var);
						$threadtag_array[] = $array_temp['1'];
					}
				}
			}
			$tags = implode(',', $threadtag_array);
		}
	}
	$file = childfile($op);
	if(file_exists($file)) {
		require_once $file;
	}

	include_once template('tag/tagmanage');
}
