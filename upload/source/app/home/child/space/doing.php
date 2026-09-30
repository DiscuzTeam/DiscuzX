<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['doingstatus']) {
	showmessage('doing_status_off');
}



require_once libfile('function/doing');
$perpage = 20;
$perpage = mob_perpage($perpage);

$page = empty($_GET['page']) ? 0 : intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page - 1) * $perpage;

// 评论分页参数
$comment_perpage = 10;
$comment_page = empty($_GET['page_c']) ? 0 : intval($_GET['page_c']);
if($comment_page < 1) $comment_page = 1;
$comment_start = ($comment_page - 1) * $comment_perpage;

ckstart($start, $perpage);

$dolist = [];
$count = 0;

$_GET['view'] = in_array($_GET['view'], ['follow', 'we', 'me', 'all']) ? $_GET['view'] : 'all';

$gets = [
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'doing',
	'view' => $_GET['view'],
	'searchkey' => $_GET['searchkey'],
	'from' => $_GET['from'],
	'tagid' => $_GET['tagid'],
];
$theurl = 'home.php?'.url_implode($gets);

$f_index = '';
$diymode = 0;
if($_GET['view'] == 'all') {

	$f_index = 'dateline';

} elseif($_GET['view'] == 'we') {

	space_merge($space, 'field_home');
	if($space['feedfriend']) {
		$uids = array_merge(explode(',', $space['feedfriend']), [$space['uid']]);
		$f_index = 'dateline';
	} else {
		$uids = [$space['uid']];
	}
} elseif($_GET['view'] == 'follow') {
	$uids[] = $space['uid'];
	if($_GET['viewtype'] == 'special') {
		$followusers = table_home_follow::t()->fetch_all_following_by_uid($space['uid'], 1);
	} else {
		$followusers = table_home_follow::t()->fetch_all_following_by_uid($space['uid']);
	}
	foreach($followusers as $followuser) {
		$uids[] = $followuser['followuid'];
	}
} else {

	if($_GET['from'] == 'space') $diymode = 1;

	$uids = $_GET['highlight'] ? [] : [$space['uid']];
}
$actives = [$_GET['view'] => ' class="a"'];

$tagid = empty($_GET['tagid']) ? 0 : intval($_GET['tagid']);
$doid = empty($_GET['doid']) ? 0 : intval($_GET['doid']);
$all_doids = $clist = $newdoids = [];
$pricount = 0;
if($doid) {
	$op = 'view';
	$count = 1;
	$f_index = '';
	$theurl .= "&doid=$doid";
}
if($tagid) {
	$doid = [];
	$tag = table_common_tag::t()->fetch_info($tagid);
	if(empty($tag['tagid'])) {
		showmessage('article_does_not_exist');
	}
	if($tag['status'] == 1) {
		showmessage('tag_closed');
	}
	$tagname = $tag['tagname'];
	$count = table_common_tagitem::t()->select($tagid, 0, 'doid', '', '', 0, 0, 0, 1);
	$query = table_common_tagitem::t()->select($tagid, 0, 'doid', '', '', $start, $perpage);
	foreach($query as $result) {
		$doid[] = $result['itemid'];
	}
}

if($searchkey = stripsearchkey($_GET['searchkey'])) {
	$searchkey = dhtmlspecialchars($searchkey);
}

if(empty($count)) {
	$count = table_home_doing::t()->fetch_all_search($start, $perpage, 3, $uids, '', $searchkey, '', '', '', 1, $doid, $f_index);
}

if($count) {
	$query = table_home_doing::t()->fetch_all_search($start, $perpage, 1, $uids, '', $searchkey, '', '', '', 1, $doid, $f_index);
	// 收集所有动态ID
	foreach($query as $value) {
		if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
			$all_doids[] = $value['doid'];
		} else {
			$pricount++;
		}
	}
	// 批量查询点赞状态
	$recommend_status = array();
	if($_G['uid'] && !empty($all_doids)) {
		$recommend_status = table_home_doing_recomend_log::t()->fetch_all_by_doids_uid($all_doids, $_G['uid']);
	}
	foreach($query as $value) {
		$value = domessageformat($value, $recommend_status);
		if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
			$dolist[] = $value;
		}
	}
}
if($doid && is_numeric($doid)) {
	$dovalue = empty($dolist) ? [] : $dolist[0];
	if($dovalue) {
		if($dovalue['uid'] == $_G['uid']) {
			$actives = ['me' => ' class="a"'];
		} else {
			$actives = ['all' => ' class="a"'];
		}
	}
}


// 查询记录对应的附件信息
$attachments = [];
if (!empty($all_doids)) {
	$attach_list = table_home_doing_attachment::t()->fetch_all_by_id(0, 'doid', $all_doids);
	foreach ($attach_list as $attach) {
		// 视频等非图片附件不生成缩略图
		$attach['thumb'] = $attach['isimage'] ? getdiscuzimg('doing', $attach['aid'], 0, 140, 140) : '';
		$attachments[$attach['doid']][] = $attach;
	}
}
// 将附件信息添加到记录数据中
foreach ($dolist as &$dv) {
	$dv['attachments'] = isset($attachments[$dv['doid']]) ? $attachments[$dv['doid']] : [];
}
unset($dv);

// 列表页外透每条记录最热门的前3条一级评论（单条详情页无需外透，本身展示完整评论）
// 注意：列表模式下 $doid 为 0（empty 默认值），标签页为数组，单条查看为正整数
$hotcomment_count = [];
if (empty($doid) && !empty($all_doids)) {
	$hotcomments = table_home_docomment::t()->fetch_hot_top_by_doids($all_doids, 3);
	foreach ($hotcomments as $cm) {
		$dv_hot[$cm['doid']][] = $cm;
	}
	foreach (table_home_docomment::t()->count_top_by_doids($all_doids) as $row) {
		$hotcomment_count[$row['doid']] = intval($row['cnt']);
	}
	foreach ($dolist as &$dv) {
		$dv['hotcomments'] = isset($dv_hot[$dv['doid']]) ? $dv_hot[$dv['doid']] : [];
	}
	unset($dv);
}

// 处理评论
$clist = [];
$showdoinglist = [];
$comment_multi = [];

// 无论是单条动态还是多条动态列表，都初始化空的评论数组，点击按钮时异步加载
$clist = [];
$showdoinglist = [];
$comment_multi = [];

// 单条动态查看时，为分页链接做准备
if($doid && is_numeric($doid)) {
	// 只获取评论总数用于生成分页链接，评论数据通过ajax异步加载
	$top_comment_count = intval(table_home_docomment::t()->count_top_by_doid($doid));
	
	// 生成评论分页链接
	if($top_comment_count > $comment_perpage) {
		$comment_url = "home.php?mod=space&do=doing&doid=$doid&page_c={page}";
		$comment_multi[$doid] = multi($top_comment_count, $comment_perpage, $comment_page, $comment_url);
	}
}

$multi = multi($count, $perpage, $page, $theurl);

dsetcookie('home_diymode', $diymode);
if($_G['uid']) {
	if($_GET['view'] == 'all') {
		$navtitle = lang('core', 'title_view_all').lang('core', 'title_doing');
	} elseif($_GET['view'] == 'me') {
		$navtitle = lang('core', 'title_doing_view_me');
	} else {
		$navtitle = lang('core', 'title_me_friend_doing');
	}
	$defaultstr = getdefaultdoing();
} else {
	$navtitle = lang('core', 'title_newest_doing');
}
if($tagid){
	$navtitle = $tagname.' - '.$navtitle;
}

if($_G['uid'] != $space['uid'] && $space['username']) {
	$navtitle = lang('space', 'sb_doing', ['who' => $space['username']]);
}
$metakeywords = $navtitle;
$metadescription = $navtitle;

// 移动端列表页顶部内嵌发布框（微博式）所需的模板变量初始化
$type = '';
$id = 0;
$topicid = 0;
$commentcable = [];

include_once template('diy:home/space_doing');