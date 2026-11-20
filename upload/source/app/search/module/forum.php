<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
const NOROBOT = true;

if(!$_G['setting']['search']['forum']['status']) {
	showmessage('search_forum_closed');
}

if(in_array($_G['adminid'], [0, -1]) && !($_G['group']['allowsearch'] & 2)) {
	showmessage('group_nopermission', NULL, ['grouptitle' => $_G['group']['grouptitle']], ['login' => 1]);
}

$_G['setting']['search']['forum']['searchctrl'] = intval($_G['setting']['search']['forum']['searchctrl']);

require_once libfile('function/forumlist');
require_once libfile('function/forum');
require_once libfile('function/post');
loadcache(['forums', 'posttable_info']);
$posttableselect = '';
if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
	$posttableselect = '<select name="seltableid" id="seltableid" class="ps" style="display:none">';
	foreach($_G['cache']['posttable_info'] as $posttableid => $data) {
		$posttableselect .= '<option value="'.$posttableid.'"'.($_GET['posttableid'] == $posttableid ? ' selected="selected"' : '').'>'.($data['memo'] ? $data['memo'] : 'post_'.$posttableid).'</option>';
	}
	$posttableselect .= '</select>';
}

$srchmod = 2;

$cachelife_time = 300;                // Life span for cache of searching in specified range of time
$cachelife_text = 3600;                // Life span for cache of text searching

$srchtype = empty($_GET['srchtype']) ? '' : trim($_GET['srchtype']);
$searchid = isset($_GET['searchid']) ? intval($_GET['searchid']) : 0;
$seltableid = intval(getgpc('seltableid'));

if($srchtype != 'title' && $srchtype != 'fulltext') {
	$srchtype = '';
}

$srchtxt = trim(getgpc('srchtxt'));
$srchuid = intval(getgpc('srchuid'));
$srchuname = isset($_GET['srchuname']) ? trim(str_replace('|', '', $_GET['srchuname'])) : '';;
$srchfrom = intval(getgpc('srchfrom'));
$before = intval(getgpc('before'));
$srchfid = getgpc('srchfid');
$srhfid = intval($_GET['srhfid']);

$keyword = isset($srchtxt) ? dhtmlspecialchars(trim($srchtxt)) : '';

$forumselect = forumselect();

if(!submitcheck('searchsubmit', 1)) {

	if(getgpc('adv')) {
		include template('search/forum_adv');
	} else {
		include template('search/forum');
	}

} else {
	$orderby = in_array(getgpc('orderby'), ['dateline', 'replies', 'views']) ? $_GET['orderby'] : 'lastpost';
	$ascdesc = isset($_GET['ascdesc']) && $_GET['ascdesc'] == 'asc' ? 'asc' : 'desc';
	$orderbyselected = [$orderby => 'selected="selected"'];
	$ascchecked = [$ascdesc => 'checked="checked""'];

	if(!empty($searchid)) {

		require_once libfile('function/misc');

		$page = max(1, intval(getgpc('page')));
		$start_limit = ($page - 1) * $_G['tpp'];

		$index = table_common_searchindex::t()->fetch_by_searchid_srchmod($searchid, $srchmod);
		if(!$index) {
			showmessage('search_id_invalid');
		}

		$keyword = dhtmlspecialchars($index['keywords']);
		$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

		$index['keywords'] = rawurlencode($index['keywords']);
		$searchstring = explode('|', $index['searchstring']);
		$index['searchtype'] = $searchstring[0];//preg_replace("/^([a-z]+)\|.*/", "\\1", $index['searchstring']);
		$searchstring[2] = base64_decode($searchstring[2]);
		$srchuname = dhtmlspecialchars($searchstring[4]);
		$modfid = 0;
		if($keyword) {
			$modkeyword = str_replace(' ', ',', $keyword);
			$fids = explode(',', str_replace('\\\'', '', $searchstring[5]));
			foreach($fids as $srchfid) {
				if(!empty($srchfid)) {
					$forumselect = str_replace('<option value="'.$srchfid.'">', '<option value="'.$srchfid.'" selected="selected">', $forumselect);
				}
			}
			if(count($fids) == 1 && in_array($_G['adminid'], [1, 2, 3])) {
				$modfid = $fids[0];
				if($_G['adminid'] == 3 && !table_forum_moderator::t()->fetch_uid_by_fid_uid($modfid, $_G['uid'])) {
					$modfid = 0;
				}
			}
		}
		$threadlist = $posttables = [];
		foreach(table_forum_thread::t()->fetch_all_by_tid_fid_displayorder(explode(',', $index['ids']), null, 0, $orderby, $start_limit, $_G['tpp'], '>=', $ascdesc) as $thread) {
			$thread['subject'] = bat_highlight($thread['subject'], $keyword);
			$thread['realtid'] = $thread['isgroup'] == 1 ? $thread['closed'] : $thread['tid'];
			$threadlist[$thread['tid']] = procthread($thread, 'dt');
			$posttables[$thread['posttableid']][] = $thread['tid'];
		}
		if($threadlist) {
			foreach($posttables as $tableid => $tids) {
				foreach(table_forum_post::t()->fetch_all_by_tid($tableid, $tids, true, '', 0, 0, 1) as $post) {
					if($post['status'] & 1) {
						$threadlist[$post['tid']]['message'] = lang('forum/template', 'message_single_banned');
					} else {
						$threadlist[$post['tid']]['message'] = bat_highlight(threadmessagecutstr($threadlist[$post['tid']], $post['message'], 200), $keyword);
					}
				}
			}

		}
		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=forum&'.$_SERVER['QUERY_STRING'];

		$fulltextchecked = $searchstring[1] == 'fulltext' ? 'checked="checked"' : '';

		$specials = explode(',', $searchstring[9]);
		$srchfilter = $searchstring[8];
		$before = $searchstring[7];
		$srchfrom = $searchstring[6];
		foreach($specials as $key) {
			$specialchecked[$key] = 'checked="checked""';
		}
		$srchfilterchecked[$srchfilter] = 'checked="checked""';
		$beforechecked = [$before => 'checked="checked""'];
		$srchfromselected = [$srchfrom => 'selected="selected"'];
		$advextra = '&orderby='.$orderby.'&ascdesc='.$ascdesc.'&searchid='.$searchid.'&searchsubmit=yes';
		if($_GET['adv']) {
			include template('search/forum_adv');
		} else {
			include template('search/forum');
		}

	} else {


		if($_G['group']['allowsearch'] & 32 && $srchtype == 'fulltext') {
			periodscheck('searchbanperiods');
		} elseif($srchtype != 'title') {
			$srchtype = 'title';
		}

		$forumsarray = [];
		if(!empty($srchfid)) {
			foreach((is_array($srchfid) ? $srchfid : explode('_', $srchfid)) as $forum) {
				if($forum = intval(trim($forum))) {
					$forumsarray[] = $forum;
				}
			}
		}

		$fids = $comma = '';
		foreach($_G['cache']['forums'] as $fid => $forum) {
			if($forum['type'] != 'group' && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
				if(!$forumsarray || in_array($fid, $forumsarray)) {
					$fids .= "$comma'$fid'";
					$comma = ',';
				}
			}
		}

		$specialpluginstr = '';
		$special = getgpc('special');
		$specials = $special ? implode(',', dintval($special, true)) : '';
		$srchfilter = in_array(getgpc('srchfilter'), ['all', 'digest', 'top']) ? $_GET['srchfilter'] : 'all';

		$searchstring = 'forum|'.$srchtype.'|'.base64_encode($srchtxt).'|'.intval($srchuid).'|'.$srchuname.'|'.addslashes($fids).'|'.intval($srchfrom).'|'.intval($before).'|'.$srchfilter.'|'.$specials.'|'.$specialpluginstr.'|'.$seltableid;
		$searchindex = ['id' => 0, 'dateline' => '0'];

		foreach(table_common_searchindex::t()->fetch_all_search($_G['setting']['search']['forum']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring, $srchmod) as $index) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = ['id' => $index['searchid'], 'dateline' => $index['dateline']];
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=forum', ['searchctrl' => $_G['setting']['search']['forum']['searchctrl']]);
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname && !$srchfrom && !in_array($srchfilter, ['digest', 'top']) && !is_array($special)) {
				dheader('Location: search.php?mod=forum');
			} elseif(isset($srchfid) && !empty($srchfid) && $srchfid != 'all' && !(is_array($srchfid) && in_array('all', $srchfid)) && empty($forumsarray)) {
				showmessage('search_forum_invalid', 'search.php?mod=forum');
			} elseif(!$fids) {
				showmessage('group_nopermission', NULL, ['grouptitle' => $_G['group']['grouptitle']], ['login' => 1]);
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['forum']['maxspm']) {
				if(table_common_searchindex::t()->count_by_dateline($_G['timestamp'], $srchmod) >= $_G['setting']['search']['forum']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=forum', ['maxspm' => $_G['setting']['search']['forum']['maxspm']]);
				}
			}

			if($srchtype == 'fulltext' && $_G['setting']['sphinxon']) {
				require_once libfile('class/sphinx');

				$s = new SphinxClient();
				$s->SetServer($_G['setting']['sphinxhost'], intval($_G['setting']['sphinxport']));
				$s->SetMaxQueryTime(intval($_G['setting']['sphinxmaxquerytime']));
				$s->SetRankingMode($_G['setting']['sphinxrank']);
				$s->SetLimits(0, intval($_G['setting']['sphinxlimit']), intval($_G['setting']['sphinxlimit']));
				$s->SetGroupBy('tid', SPH_GROUPBY_ATTR);

				if($srchfilter == 'digest') {
					$s->SetFilterRange('digest', 1, 3, false);
				}
				if($srchfilter == 'top') {
					$s->SetFilterRange('displayorder', 1, 2, false);
				} else {
					$s->SetFilterRange('displayorder', 0, 2, false);
				}

				if(!empty($srchfrom) && empty($srchtxt) && empty($srchuid) && empty($srchuname)) {
					$expiration = TIMESTAMP + $cachelife_time;
					$keywords = '';
					if($before) {
						$spx_timemix = 0;
						$spx_timemax = TIMESTAMP - $srchfrom;
					} else {
						$spx_timemix = TIMESTAMP - $srchfrom;
						$spx_timemax = TIMESTAMP;
					}
				} else {
					$uids = [];
					if($srchuname) {
						$uids = array_keys(table_common_member::t()->fetch_all_by_like_username($srchuname, 0, 50));
						if(count($uids) == 0) {
							$uids = [0];
						}
					} elseif($srchuid) {
						$uids = [$srchuid];
					}
					if(is_array($uids) && count($uids) > 0) {
						$s->SetFilter('authorid', $uids, false);
					}

					if($srchtxt) {
						if(preg_match("/\".*\"/", $srchtxt)) {
							$spx_matchmode = 'PHRASE';
							$s->SetMatchMode(SPH_MATCH_PHRASE);
						} elseif(preg_match('(AND|\+|&|\s)', $srchtxt) && !preg_match('(OR|\|)', $srchtxt)) {
							$srchtxt = preg_replace('/( AND |&| )/is', '+', $srchtxt);
							$spx_matchmode = 'ALL';
							$s->SetMatchMode(SPH_MATCH_ALL);
						} else {
							$srchtxt = preg_replace('/( OR |\|)/is', '+', $srchtxt);
							$spx_matchmode = 'ANY';
							$s->SetMatchMode(SPH_MATCH_ANY);
						}
						$srchtxt = str_replace('*', '%', addcslashes($srchtxt, '%_'));
						foreach(explode('+', $srchtxt) as $text) {
							$text = trim(daddslashes($text));
							if($text) {
								$sqltxtsrch .= $andor;
								$sqltxtsrch .= $srchtype == 'fulltext' ? "(p.message LIKE '%".str_replace('_', '\_', $text)."%' OR p.subject LIKE '%$text%')" : "t.subject LIKE '%$text%'";
							}
						}
						$sqlsrch .= " AND ($sqltxtsrch)";
					}

					if(!empty($srchfrom)) {
						if($before) {
							$spx_timemix = 0;
							$spx_timemax = TIMESTAMP - $srchfrom;
						} else {
							$spx_timemix = TIMESTAMP - $srchfrom;
							$spx_timemax = TIMESTAMP;
						}
						$s->SetFilterRange('lastpost', $spx_timemix, $spx_timemax, false);
					}
					if(!empty($specials)) {
						$s->SetFilter('special', explode(',', $special), false);
					}

					$keywords = str_replace('%', '+', $srchtxt).(trim($srchuname) ? '+'.str_replace('%', '+', $srchuname) : '');
					$expiration = TIMESTAMP + $cachelife_text;

				}
				if($srchtype == 'fulltext') {
					$result = $s->Query("'".$srchtxt."'", $_G['setting']['sphinxmsgindex']);
				} else {
					$result = $s->Query($srchtxt, $_G['setting']['sphinxsubindex']);
				}
				$tids = [];
				if($result) {
					if(is_array($result['matches'])) {
						foreach($result['matches'] as $value) {
							if($value['attrs']['tid']) {
								$tids[$value['attrs']['tid']] = $value['attrs']['tid'];
							}
						}
					}
				}
				if(count($tids) == 0) {
					$ids = 0;
					$num = 0;
				} else {
					$ids = implode(',', $tids);
					$num = $result['total_found'];
				}
			} else {
				$digestltd = $srchfilter == 'digest' ? "t.digest>'0' AND" : '';
				$topltd = $srchfilter == 'top' ? "AND t.displayorder>'0'" : "AND t.displayorder>='0'";

				if(!empty($srchfrom) && empty($srchtxt) && empty($srchuid) && empty($srchuname)) {

					$searchfrom = $before ? '<=' : '>=';
					$searchfrom .= TIMESTAMP - $srchfrom;
					$sqlsrch = 'FROM '.DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd AND t.lastpost$searchfrom";
					$expiration = TIMESTAMP + $cachelife_time;
					$keywords = '';

				} else {
					$sqlsrch = $srchtype == 'fulltext' ?
						'FROM '.DB::table(getposttable($seltableid)).' p, '.DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd AND p.tid=t.tid AND p.invisible='0'" :
						'FROM '.DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd";
					if($srchuname) {
						$srchuid = array_keys(table_common_member::t()->fetch_all_by_like_username($srchuname, 0, 50));
						if(!$srchuid) {
							$sqlsrch .= ' AND 0';
						}
					}/* elseif($srchuid) {
						$srchuid = "'$srchuid'";
					}*/

					if($srchtxt) {
						$srcharr = $srchtype == 'fulltext' ? searchkey($keyword, "(p.message LIKE '%{text}%' OR p.subject LIKE '%{text}%')", true) : searchkey($keyword, "t.subject LIKE '%{text}%'", true);
						$srchtxt = $srcharr[0];
						$sqlsrch .= $srcharr[1];
					}

					if($srchuid) {
						$sqlsrch .= ' AND '.($srchtype == 'fulltext' ? 'p' : 't').'.authorid IN ('.dimplode((array)$srchuid).')';
					}

					if(!empty($srchfrom)) {
						$searchfrom = ($before ? '<=' : '>=').(TIMESTAMP - $srchfrom);
						$sqlsrch .= " AND t.lastpost$searchfrom";
					}

					if(!empty($specials)) {
						$sqlsrch .= ' AND special IN ('.dimplode($special).')';
					}

					$keywords = str_replace('%', '+', $srchtxt);
					$expiration = TIMESTAMP + $cachelife_text;

				}

				$num = $ids = 0;
				$_G['setting']['search']['forum']['maxsearchresults'] = $_G['setting']['search']['forum']['maxsearchresults'] ? intval($_G['setting']['search']['forum']['maxsearchresults']) : 500;
				$query = DB::query('SELECT '.($srchtype == 'fulltext' ? 'DISTINCT' : '')." t.tid, t.closed, t.author, t.authorid $sqlsrch ORDER BY tid DESC LIMIT ".$_G['setting']['search']['forum']['maxsearchresults']);
				while($thread = DB::fetch($query)) {
					$ids .= ','.$thread['tid'];
					$num++;
				}
				DB::free_result($query);
			}

			$searchid = table_common_searchindex::t()->insert([
				'srchmod' => $srchmod,
				'keywords' => $keywords,
				'searchstring' => $searchstring,
				'useip' => $_G['clientip'],
				'uid' => $_G['uid'],
				'dateline' => $_G['timestamp'],
				'expiration' => $expiration,
				'num' => $num,
				'ids' => $ids
			], true);

			!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

