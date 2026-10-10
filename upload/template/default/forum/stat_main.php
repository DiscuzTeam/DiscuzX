<?php exit('Access Denied');?>
<!--{eval $hotvals = array((int)$hotforum['threads'], (int)$hotforum['posts']);}-->
<!--{template common/header}-->
<div id="pt" class="bm cl">
	<div class="z">
		<a href="./" class="nvhm" title="{lang homepage}">$_G[setting][bbname]</a><em>&rsaquo;</em> {lang stats}
	</div>
</div>
<div id="ct" class="ct2_a wp cl">
	<div class="mn">
		<div class="bm bw0 stat-page">
			<h1 class="mt">{lang stats_main}</h1>

			<section class="stat-card">
				<header class="stat-card-hd"><h2>{lang stats_main_member}</h2></header>
				<ul class="stat-kpis">
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($members)}</span>
						<span class="stat-kpi-label">{lang stats_main_members}</span>
						<span class="stat-kpi-sub">{lang stats_main_new} +$membersaddtoday</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($mempost)}</span>
						<span class="stat-kpi-label">{lang stats_main_posters}</span>
						<span class="stat-kpi-sub">{lang stats_main_posters_percent} $mempostpercent%</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($memnonpost)}</span>
						<span class="stat-kpi-label">{lang stats_main_nonposters}</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($admins)}</span>
						<span class="stat-kpi-label">{lang stats_main_admins}</span>
					</li>
				</ul>
			</section>

			<section class="stat-card">
				<header class="stat-card-hd"><h2>{lang stats}</h2></header>
				<ul class="stat-kpis">
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($forums)}</span>
						<span class="stat-kpi-label">{lang stats_main_forums_count}</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($threads)}</span>
						<span class="stat-kpi-label">{lang stats_main_threads_count}</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($posts)}</span>
						<span class="stat-kpi-label">{lang stats_main_posts_count}</span>
						<span class="stat-kpi-sub">{lang stats_main_todays_newposts} +$postsaddtoday</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">$threadreplyavg</span>
						<span class="stat-kpi-label">{lang stats_main_rpt}</span>
					</li>
				</ul>
			</section>

			<section class="stat-card">
				<header class="stat-card-hd"><h2>{lang stats_main_nmpd} / {lang stats_main_nppd}</h2></header>
				<ul class="stat-kpis stat-kpis-sm">
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($membersaddavg)}</span>
						<span class="stat-kpi-label">{lang stats_main_nmpd}</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($postsaddavg)}</span>
						<span class="stat-kpi-label">{lang stats_main_nppd}</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">$mempostavg</span>
						<span class="stat-kpi-label">{lang stats_main_posts_avg}</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($activeindex)}</span>
						<span class="stat-kpi-label">{lang stats_main_board_activity}</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo number_format($bestmemposts)}</span>
						<span class="stat-kpi-label">{lang stats_main_topposter}</span>
						<span class="stat-kpi-sub"><!--{if $bestmemposts}-->$bestmem<!--{else}-->{lang none}<!--{/if}--></span>
					</li>
				</ul>
			</section>

			<div class="stat-cols">
				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang stats_main_posters_percent}</h2>
						<p class="stat-card-sub">{lang stats_main_posters} {echo number_format($mempost)} &nbsp;/&nbsp; {lang stats_main_nonposters} {echo number_format($memnonpost)}</p>
					</header>
					<div class="stat-chart stat-chart-h240" id="stat_pie_poster"></div>
				</section>

				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang stats_main_board_activity}</h2>
						<p class="stat-card-sub">{lang stats_main_nppd} {echo number_format($postsaddavg)} &nbsp;/&nbsp; {lang stats_main_rpt} $threadreplyavg</p>
					</header>
					<div class="stat-chart stat-chart-h240" id="stat_gauge_active"></div>
				</section>

				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang stats_main_hot_forum}</h2>
						<p class="stat-card-sub"><a href="forum.php?mod=forumdisplay&fid=$hotforum[fid]" target="_blank">$hotforum[name]</a></p>
					</header>
					<div class="stat-chart stat-chart-h240" id="stat_bar_hotforum"></div>
				</section>
			</div>

			<div class="notice">{lang stats_update}</div>
		</div>
	</div>
	<!--{subtemplate common/stat}-->
</div>
<script src="{STATICURL}js/echarts/echarts.common.min.js"></script>
<script src="{$_G['setting']['jspath']}stat.js?{VERHASH}"></script>
<script type="text/javascript">
statPie($('stat_pie_poster'), [
	{name: '{lang stats_main_posters}', value: $mempost},
	{name: '{lang stats_main_nonposters}', value: $memnonpost}
]);
statGauge($('stat_gauge_active'), $activeindex, {title: '{lang stats_main_board_activity}'});
statBarH($('stat_bar_hotforum'), ['{lang stats_main_threads_count}', '{lang stats_main_posts_count}'], {echo json_encode($hotvals)});
</script>
<!--{template common/footer}-->
