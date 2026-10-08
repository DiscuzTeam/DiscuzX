<?php exit('Access Denied');?>
<!--{template common/header}-->
<div id="pt" class="bm cl">
	<div class="z">
		<a href="./" class="nvhm" title="{lang homepage}">$_G[setting][bbname]</a><em>&rsaquo;</em>
		<a href="misc.php?mod=stat">{lang stats}</a><em>&rsaquo;</em>
	<!--{if $op == 'views'}-->
		{lang stats_views}
	<!--{elseif $op == 'agent'}-->
		{lang stats_agent}
	<!--{elseif $op == 'posts'}-->
		{lang stats_posthist}
	<!--{elseif $op == 'modworks'}-->
		{lang stats_modworks}
	<!--{elseif $op == 'forumstat'}-->
		{lang stats_forums_stat}
	<!--{/if}-->
	</div>
</div>

<div id="ct" class="ct2_a wp cl">
	<div class="mn">
		<!--{if $op == 'views'}-->
			<div class="bm bw0">
				<h1 class="mt">{lang stats_views}</h1>
				<table summary="{lang stats_week}" class="dt bm">
					<caption><h2 class="mbn">{lang stats_week}</h2></caption>
					$statsbar_week
				</table>

				<table summary="{lang stats_hour}" class="dt bm">
					<caption><h2 class="mbn">{lang stats_hour}</h2></caption>
					$statsbar_hour
				</table>
			</div>
		<!--{elseif $op == 'agent'}-->
			<div class="bm bw0">
				<h1 class="mt">{lang stats_agent}</h1>
				<table summary="{lang stats_agent}" class="dt bm">
					<caption><h2 class="mbn">{lang stats_os}</h2></caption>
					$statsbar_os
				</table>

				<table summary="{lang stats_browser}" class="dt bm">
					<caption><h2 class="mbn">{lang stats_browser}</h2></caption>
					$statsbar_browser
				</table>
			</div>
		<!--{elseif $op == 'posts'}-->
			<div class="bm bw0">
				<h1 class="mt">{lang stats_posthist}</h1>
				<table summary="{lang stats_month_posts}" class="dt bm">
					<caption><h2 class="mbn">{lang stats_month_posts}</h2></caption>
					$statsbar_monthposts
				</table>

				<table summary="{lang stats_day_posts}" class="dt bm">
					<caption><h2 class="mbn">{lang stats_day_posts}</h2></caption>
					$statsbar_dayposts
				</table>
		<!--{elseif $op == 'forumstat' && !$_GET['fid']}-->
			<!-- 图表数据预计算：eval 块内不可出现花括号，故统一用单语句数组函数 -->
			<!--{eval $fposts = array_map('intval', array_column((array)$forums, 'posts'));}-->
			<!--{eval $fnames = array_column((array)$forums, 'name');}-->
			<!--{eval $fids = array_column((array)$forums, 'fid');}-->
			<!--{eval $ftypes = array_column((array)$forums, 'type');}-->
			<!--{eval array_multisort($fposts, SORT_DESC, SORT_NUMERIC, $fnames, $fids, $ftypes);}-->
			<!--{eval $fCats = array_slice($fnames, 0, 15);}-->
			<!--{eval $fVals = array_slice($fposts, 0, 15);}-->
			<div class="bm bw0 stat-page">
				<h1 class="mt">{lang stats_forums_stat}</h1>
				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang stats_main_posts_count} · 版块排行</h2>
						<p class="stat-card-sub">发帖量前 {echo min(15, count($fCats))} 的版块，点击版块名查看逐日走势</p>
					</header>
					<div class="stat-chart stat-chart-h420" id="stat_bar_forums"></div>
				</section>
				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang stats_forums_stat}</h2>
						<p class="stat-card-sub">共 {echo count($forums)} 个版块</p>
					</header>
					<ul class="stat-rank">
						<!--{loop $fnames $fkey $fname}-->
							<li>
								<span class="stat-rank-no">{echo $fkey + 1}</span>
								<a class="stat-rank-t" href="misc.php?mod=stat&op=forumstat&fid=$fids[$fkey]">$fname<!--{if $ftypes[$fkey] == 'sub'}--><span class="xg1">（{lang stats_forums_sub}）</span><!--{/if}--></a>
								<span class="stat-rank-v">$fposts[$fkey]</span>
							</li>
						<!--{/loop}-->
					</ul>
				</section>
			</div>
		<!--{elseif $op == 'forumstat' && $_GET['fid']}-->
			<div class="bm bw0 stat-page">
				<h1 class="mt">{lang stats_forum_stat_log} - $foruminfo[name] - $month</h1>

				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>$foruminfo[name] · $month</h2>
						<p class="stat-card-sub">{lang stats_forum_stat_log}</p>
					</header>
					<div class="stat-chart stat-chart-h420" id="statchart"></div>
				</section>

				<!--{if $logs}-->
					<section class="stat-card">
						<header class="stat-card-hd">
							<h2>{lang stats_date} · {lang stats_main_total_posted}</h2>
							<p class="stat-card-sub">本月 {echo count($logs)} 天</p>
						</header>
						<div class="stat-list">
							<div class="stat-row">
								<span class="stat-cell-w2 stat-cell-hd">{lang stats_date}</span>
								<span class="stat-cell-num stat-cell-hd">{lang stats_main_total_posted}</span>
							</div>
							<!--{loop $logs $log}-->
								<div class="stat-row">
									<span class="stat-cell-w2">$log[logdate]</span>
									<span class="stat-cell-num">$log[value]</span>
								</div>
							<!--{/loop}-->
						</div>
					</section>
				<!--{/if}-->

				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang stats_history} - $foruminfo[name]</h2>
						<p class="stat-card-sub">点击月份查看该月逐日发帖走势</p>
					</header>
					<ul class="stat-rank">
						<!--{loop $monthlist $mkey $mname}-->
							<li>
								<span class="stat-rank-no">{echo $mkey + 1}</span>
								<a class="stat-rank-t" href="misc.php?mod=stat&op=forumstat&fid=$_GET[fid]&month=$mname">$mname</a>
								<span class="stat-rank-v">$monthposts[$mname]</span>
							</li>
						<!--{/loop}-->
					</ul>
				</section>
			</div>
		<!--{elseif $op == 'modworks' && $uid}-->
			<!--{eval $cntAll = array_combine(array_keys(array_diff_key((array)$totalactions, array('total' => 1))), array_map('intval', array_column((array)$totalactions, 'count')));}-->
			<!--{eval $mwPos = array_filter(array_replace(array_fill_keys(array_keys((array)$modactioncode), 0), $cntAll));}-->
			<!--{eval arsort($mwPos);}-->
			<!--{eval $mwTop = array_slice($mwPos, 0, 15, true);}-->
			<!--{eval $mwCats = array_slice(array_values(array_replace(array_flip(array_keys($mwTop)), (array)$modactioncode)), 0, count($mwTop));}-->
			<!--{eval $mwVals = array_values($mwTop);}-->
			<!--{if count($mwPos) > 15}-->
				<!--{eval $mwVals[] = array_sum(array_slice($mwPos, 15));}-->
				<!--{eval $mwCats[] = 'other';}-->
			<!--{/if}-->
			<!--{eval $mwDays = array_keys((array)$modactions);}-->
			<!--{eval $mwTotals = array_map('intval', array_column((array)$modactions, 'total'));}-->
			<div class="bm bw0 stat-page">
				<h1 class="mt">{lang stats_modworks} - $username</h1>

				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>$username · $starttime ~ $endtime</h2>
						<p class="stat-card-sub"><a href="misc.php?mod=stat&op=modworks&exportexcel=1&uid=$uid&modworks_starttime=$starttime&modworks_endtime=$endtime">{lang stats_modworks_export}</a></p>
					</header>
					<ul class="stat-range">
						<!--{loop $monthlinks $link}-->
							$link
						<!--{/loop}-->
						<li>
							<script src="{$_G[setting][jspath]}calendar.js?{VERHASH}" type="text/javascript"></script>
							<form action="misc.php?mod=stat&op=modworks&uid=$uid" method="post">
								<input type="hidden" name="formhash" value="{FORMHASH}" />
								{lang stats_modworks_timerange}: <input type="text" name="modworks_starttime" class="px vm" size="10" onclick="showcalendar(event, this, false)" autocomplete="off" /> {lang stats_modworks_to} <input type="text" name="modworks_endtime" class="px vm" size="10" onclick="showcalendar(event, this, false)" autocomplete="off" />
								<button type="submit" class="pn vm" name="modworkssubmit" value="true"><em>{lang focus_show}</em></button>
							</form>
						</li>
					</ul>
				</section>

				<div class="stat-cols">
					<section class="stat-card">
						<header class="stat-card-hd"><h2>操作类型分布</h2><p class="stat-card-sub">按次数统计 · 最多显示 15 项，其余归入"其他"</p></header>
						<!--{if count($mwCats) > 8}--><div class="stat-chart stat-chart-h420" id="stat_bar_modaction"></div><!--{else}--><div class="stat-chart stat-chart-h320" id="stat_bar_modaction"></div><!--{/if}-->
					</section>
					<section class="stat-card">
						<header class="stat-card-hd"><h2>每日操作量</h2><p class="stat-card-sub">$starttime ~ $endtime</p></header>
						<div class="stat-chart stat-chart-h320" id="stat_line_modday"></div>
					</section>
				</div>

				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang stats_modworks} 明细</h2>
						<p class="stat-card-sub">横向滚动查看全部操作类型，表头与日期列已固定</p>
					</header>
					<div class="stat-matrix">
						<table class="stat-mx">
							<thead>
								<tr>
									<th class="stat-mx-corner">{lang time}</th>
									<!--{loop $modactioncode $key $val}-->
										<th>$val</th>
									<!--{/loop}-->
									<th class="stat-mx-total">{lang stats_modworks_total}</th>
									</tr>
									</thead>
									<tbody>
									<!--{loop $modactions $day $modaction}-->
									<tr>
										<th class="stat-mx-row">$day</th>
										<!--{loop $modactioncode $key $val}-->
											<!--{if isset($modaction[$key]['posts']) && $modaction[$key]['posts']}-->
												<td title="{lang posts}: $modaction[$key][posts]">$modaction[$key][count]</td>
											<!--{else}-->
												<td class="stat-mx-empty">&nbsp;</td>
											<!--{/if}-->
										<!--{/loop}-->
										<td class="stat-mx-total">$modaction[total]</td>
									</tr>
								<!--{/loop}-->
								<tr class="stat-mx-sum">
									<th class="stat-mx-row">{lang stats_modworks_total}</th>
									<!--{loop $modactioncode $key $val}-->
										<!--{if isset($totalactions[$key]['posts']) && $totalactions[$key]['posts']}-->
											<td title="{lang posts}: $totalactions[$key][posts]">$totalactions[$key][count]</td>
										<!--{else}-->
											<td class="stat-mx-empty">&nbsp;</td>
										<!--{/if}-->
									<!--{/loop}-->
									<td class="stat-mx-total">$totalactions[total]</td>
								</tr>
							</tbody>
						</table>
					</div>
				</section>
			</div>

		<!--{elseif $op == 'modworks'}-->
			<!--{eval $cntAll2 = array_combine(array_keys(array_diff_key((array)$total, array('total' => 1))), array_map('intval', array_column((array)$total, 'count')));}-->
			<!-- 同上：只保留有记录的操作类型；total 汇总项是标量而非数组，需先从键里剔除 -->
			<!--{eval $maPos = array_filter(array_replace(array_fill_keys(array_keys((array)$modactioncode), 0), $cntAll2));}-->
			<!--{eval arsort($maPos);}-->
			<!--{eval $maTop = array_slice($maPos, 0, 15, true);}-->
			<!--{eval $maCats = array_slice(array_values(array_replace(array_flip(array_keys($maTop)), (array)$modactioncode)), 0, count($maTop));}-->
			<!--{eval $maVals = array_values($maTop);}-->
			<!--{if count($maPos) > 15}-->
				<!--{eval $maVals[] = array_sum(array_slice($maPos, 15));}-->
				<!--{eval $maCats[] = '其他';}-->
			<!--{/if}-->
			<!--{eval $mmNames = array_column((array)$members, 'username', 'uid');}-->
			<!--{eval $mmTotals = array_replace(array_fill_keys(array_keys($mmNames), 0), array_map('intval', array_column((array)$members, 'total', 'uid')));}-->
			<!--{eval arsort($mmTotals);}-->
			<!--{eval $mmTop = array_slice($mmTotals, 0, 10, true);}-->
			<!--{eval $mmCats = array_map('strip_tags', array_slice(array_values(array_replace(array_flip(array_keys($mmTop)), $mmNames)), 0, count($mmTop)));}-->
			<!--{eval $mmVals = array_values($mmTop);}-->
			<div class="bm bw0 stat-page">
				<h1 class="mt">{lang stats_modworks} - {lang stats_modworks_all}</h1>

				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>$starttime ~ $endtime</h2>
						<p class="stat-card-sub">{echo count($members)} 位管理人员 · <a href="misc.php?mod=stat&op=modworks&exportexcel=1&modworks_starttime=$starttime&modworks_endtime=$endtime">{lang stats_modworks_export}</a></p>
					</header>
					<ul class="stat-range">
						<!--{loop $monthlinks $link}-->
							$link
						<!--{/loop}-->
						<li>
							<script src="{$_G[setting][jspath]}calendar.js?{VERHASH}" type="text/javascript"></script>
							<form action="misc.php?mod=stat&op=modworks" method="post">
								<input type="hidden" name="formhash" value="{FORMHASH}" />
								{lang stats_modworks_timerange}: <input type="text" name="modworks_starttime" class="px vm" size="10" onclick="showcalendar(event, this, false)" autocomplete="off" /> {lang stats_modworks_to} <input type="text" name="modworks_endtime" class="px vm" size="10" onclick="showcalendar(event, this, false)" autocomplete="off" />
								<button type="submit" class="pn vm" name="modworkssubmit" value="true"><em>{lang focus_show}</em></button>
							</form>
						</li>
					</ul>
				</section>

				<div class="stat-cols">
					<section class="stat-card">
						<header class="stat-card-hd"><h2>操作类型分布</h2><p class="stat-card-sub">全部管理人员合计 · 最多显示 15 项，其余归入"其他"</p></header>
						<!--{if count($maCats) > 8}--><div class="stat-chart stat-chart-h420" id="stat_bar_modaction"></div><!--{else}--><div class="stat-chart stat-chart-h320" id="stat_bar_modaction"></div><!--{/if}-->
					</section>
					<section class="stat-card">
						<header class="stat-card-hd"><h2>管理人员排行</h2><p class="stat-card-sub">操作量 TOP {echo min(10, count($mmCats))}</p></header>
						<div class="stat-chart stat-chart-h320" id="stat_bar_modmember"></div>
					</section>
				</div>

				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang stats_modworks} 明细</h2>
						<p class="stat-card-sub">横向滚动查看全部操作类型，表头与人员列已固定；点击人员名查看逐日明细</p>
					</header>
					<div class="stat-matrix">
						<table class="stat-mx">
							<thead>
								<tr>
									<th class="stat-mx-corner">{lang username}</th>
									<!--{loop $modactioncode $key $val}-->
									<th>$val</th>
								<!--{/loop}-->
									<th class="stat-mx-total">{lang stats_modworks_total}</th>
								</tr>
							</thead>
							<tbody>
								<!--{loop $members $muid $member}-->
									<tr>
										<th class="stat-mx-row"><a href="misc.php?mod=stat&op=modworks{if isset($_GET[before])}&before=$_GET[before]{/if}&uid=$muid{if $starttime || $endtime}&modworks_starttime=$starttime&modworks_endtime=$endtime{/if}" title="{lang stats_modworks_details}">{$member[username]}</a></th>
										<!--{loop $modactioncode $key $val}-->
											<!--{if isset($member[$key]['posts']) && $member[$key]['posts']}-->
												<td title="{lang posts}: {$member[$key]['posts']}">{$member[$key][count]}</td>
											<!--{else}-->
												<td class="stat-mx-empty">&nbsp;</td>
											<!--{/if}-->
										<!--{/loop}-->
										<td class="stat-mx-total">$member[total]</td>
									</tr>
								<!--{/loop}-->
								<!--{if $members}-->
									<tr class="stat-mx-sum">
										<th class="stat-mx-row">{lang stats_modworks_total}</th>
										<!--{loop $modactioncode $key $val}-->
											<!--{if isset($total[$key]['posts']) && $total[$key]['posts']}-->
												<td title="{lang posts}: {$total[$key]['posts']}">{$total[$key][count]}</td>
											<!--{else}-->
												<td class="stat-mx-empty">&nbsp;</td>
											<!--{/if}-->
										<!--{/loop}-->
										<td class="stat-mx-total">$total[total]</td>
									</tr>
								<!--{/if}-->
							</tbody>
						</table>
					</div>
				</section>
			</div>
		<!--{/if}-->

	</div>
	<!--{subtemplate common/stat}-->
</div>

<!--{if $op == 'forumstat' && $_GET['fid']}-->
	<script src="{STATICURL}js/echarts/echarts.common.min.js"></script>
	<script src="{$_G['setting']['jspath']}stat.js?{VERHASH}"></script>
	<script type="text/javascript">
	statLine($('statchart'), {echo json_encode(array_column((array)$logs, 'logdate'))}, [{name: '{lang stats_main_total_posted}', type: 'line', smooth: true, showSymbol: true, data: {echo json_encode(array_map('intval', array_column((array)$logs, 'value')))}}]);
	</script>
<!--{/if}-->
<!--{if $op == 'modworks'}-->
	<script src="{STATICURL}js/echarts/echarts.common.min.js"></script>
	<script src="{$_G['setting']['jspath']}stat.js?{VERHASH}"></script>
	<script type="text/javascript">
	<!--{if $op == 'modworks' && $uid}-->
		<!--{if $mwCats}-->statBarH($('stat_bar_modaction'), {echo json_encode($mwCats)}, {echo json_encode($mwVals)}, {color: 3});<!--{else}-->statNoData($('stat_bar_modaction'));<!--{/if}-->
		statLine($('stat_line_modday'), {echo json_encode($mwDays)}, [{name: '{lang stats_modworks}', type: 'line', smooth: true, showSymbol: true, data: {echo json_encode($mwTotals)}}]);
	<!--{else}-->
		<!--{if $maCats}-->statBarH($('stat_bar_modaction'), {echo json_encode($maCats)}, {echo json_encode($maVals)}, {color: 3});<!--{else}-->statNoData($('stat_bar_modaction'));<!--{/if}-->
		statBarH($('stat_bar_modmember'), {echo json_encode($mmCats)}, {echo json_encode($mmVals)}, {color: 0, labelWidth: 72});
	<!--{/if}-->
	</script>
<!--{/if}-->
<!--{if $op == 'forumstat' && !$_GET['fid']}-->
	<script src="{STATICURL}js/echarts/echarts.common.min.js"></script>
	<script src="{$_G['setting']['jspath']}stat.js?{VERHASH}"></script>
	<script type="text/javascript">
	<!--{if $fCats}-->statBarH($('stat_bar_forums'), {echo json_encode($fCats)}, {echo json_encode($fVals)}, {labelWidth: 110});<!--{/if}-->
	</script>
<!--{/if}-->

<!--{template common/footer}-->
