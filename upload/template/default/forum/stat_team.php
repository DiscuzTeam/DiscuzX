<?php exit('Access Denied');?>
<!--{template common/header}-->
<div id="pt" class="bm cl">
	<div class="z">
		<a href="./" class="nvhm" title="{lang homepage}">$_G[setting][bbname]</a><em>&rsaquo;</em>
		<a href="misc.php?mod=stat">{lang stats}</a><em>&rsaquo;</em>
		{lang stats_team}
	</div>
</div>
<div id="ct" class="ct2_a wp cl">
	<div class="mn">
		<div class="bm bw0 stat-page">
			<h1 class="mt">{lang stats_team}</h1>

			<section class="stat-card">
				<header class="stat-card-hd"><h2>{lang stats_team} · {lang stats_main_admins}</h2></header>
				<ul class="stat-kpis stat-kpis-sm">
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo count($team['members'])}</span>
						<span class="stat-kpi-label">{lang manage_team}</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo round($team['avgoffdays'], 1)}</span>
						<span class="stat-kpi-label">{lang stats_team_offdays}（{lang stats_main_posts_avg}）</span>
					</li>
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo round($team['avgthismonthposts'], 1)}</span>
						<span class="stat-kpi-label">{lang stats_posts_thismonth}（{lang stats_main_posts_avg}）</span>
					</li>
					<!--{if $_G[setting][modworkstatus]}-->
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo round($team['avgmodactions'], 1)}</span>
						<span class="stat-kpi-label">{lang stats_modworks_thismonth}（{lang stats_main_posts_avg}）</span>
					</li>
					<!--{/if}-->
					<!--{if $_G[setting][oltimespan]}-->
					<li class="stat-kpi">
						<span class="stat-kpi-num">{echo round($team['avgtotalol'], 1)}</span>
						<span class="stat-kpi-label">{lang onlinetime_total}（{lang hours}）</span>
					</li>
					<!--{/if}-->
				</ul>
			</section>

			<!--{if $team['admins']}-->
				<div id="team_admins_c" class="umh">
					<h3 onclick="toggle_collapse('team_admins', 1, 1);">{lang stats_team_admins}</h3>
					<div class="umh_act">
						<p class="umh_cb" onclick="toggle_collapse('team_admins', 1, 1);">[ {lang open} ]</p>
					</div>
				</div>
				<section class="stat-card" id="team_admins">
					<!--{loop $team['admins'] $uid}-->
						<div class="stat-member">
							<div class="stat-member-hd">
								<a href="home.php?mod=space&uid=$uid" class="stat-member-name" target="_blank">$team[members][$uid][username]</a>
								<span class="stat-tag">$team[members][$uid][grouptitle]</span>
								<!--{if $team['members'][$uid]['adminid'] == 1}--><span class="stat-tag stat-tag-act">{lang admin}</span><!--{elseif $team['members'][$uid]['adminid'] == 2}--><span class="stat-tag stat-tag-act">{lang supermod}<!--{/if}-->
							</div>
							<ul class="stat-metrics">
								<li class="stat-metric"><b>$team[members][$uid][credits]</b><span>{lang credits}</span></li>
								<li class="stat-metric"><b>$team[members][$uid][posts]</b><span>{lang posts}</span></li>
								<li class="stat-metric"><b>$team[members][$uid][thismonthposts]</b><span>{lang stats_posts_thismonth}</span></li>
								<li class="stat-metric"><b>$team[members][$uid][offdays]</b><span>{lang stats_team_offdays}</span></li>
								<li class="stat-metric"><b>$team[members][$uid][lastactivity]</b><span>{lang lastvisit}</span></li>
								<!--{if $_G[setting][modworkstatus]}-->
									<li class="stat-metric"><a href="misc.php?mod=stat&op=modworks&uid=$uid">$team[members][$uid][modactions]</a><span>{lang stats_modworks_thismonth}</span></li>
								<!--{/if}-->
								<!--{if $_G[setting][oltimespan]}-->
									<li class="stat-metric"><b>$team[members][$uid][totalol]</b><span>{lang onlinetime_total}</span></li>
									<li class="stat-metric"><b>$team[members][$uid][thismonthol]</b><span>{lang onlinetime_thismonth}</span></li>
								<!--{/if}-->
							</ul>
						</div>
					<!--{/loop}-->
				</section>
			<!--{/if}-->

			<!--{loop $team['categories'] $category}-->
				<div id="category_$category[fid]_c" class="umh">
					<h3 onclick="toggle_collapse('category_$category[fid]', 1, 1);">$category[name]</h3>
					<div class="umh_act">
						<p class="umh_cb" onclick="toggle_collapse('category_$category[fid]', 1, 1);">[ {lang open} ]</p>
					</div>
				</div>
				<section class="stat-card" id="category_$category[fid]">
					<!--{loop $team['forums'][$category['fid']] $fid $forum}-->
						<!--{if $forum[moderators]}-->
							<div class="stat-group">
								<div class="stat-group-hd">
									<!--{if $forum[type] == 'group'}--><a href="forum.php?gid=$fid" target="_blank"><!--{else}--><a href="forum.php?mod=forumdisplay&fid=$fid" target="_blank"><!--{/if}-->$forum[name]</a>
									<span class="stat-tag">$forum[moderators] 位版主</span>
									<!--{if $forum['inheritedmod']}--><span class="stat-tag">继承版主</span><!--{/if}-->
								</div>
								<!--{loop $team['moderators'][$fid] $key $uid}-->
									<div class="stat-member">
										<div class="stat-member-hd">
											<a href="home.php?mod=space&uid=$uid" class="stat-member-name" target="_blank"><!--{if $forum['inheritedmod']}--><b>$team[members][$uid][username]</b><!--{else}-->$team[members][$uid][username]<!--{/if}--></a>
											<span class="stat-tag">$team[members][$uid][grouptitle]</span>
											<!--{if $team['members'][$uid]['adminid'] == 3}--><span class="stat-tag stat-tag-act">{lang moderator}<!--{/if}-->
										</div>
										<ul class="stat-metrics">
											<li class="stat-metric"><b>$team[members][$uid][credits]</b><span>{lang credits}</span></li>
											<li class="stat-metric"><b>$team[members][$uid][posts]</b><span>{lang posts}</span></li>
											<li class="stat-metric"><b>$team[members][$uid][thismonthposts]</b><span>{lang stats_posts_thismonth}</span></li>
											<li class="stat-metric"><b>$team[members][$uid][offdays]</b><span>{lang stats_team_offdays}</span></li>
											<li class="stat-metric"><b>$team[members][$uid][lastactivity]</b><span>{lang lastvisit}</span></li>
											<!--{if $_G[setting][modworkstatus]}-->
												<li class="stat-metric"><a href="misc.php?mod=stat&op=modworks&uid=$uid">$team[members][$uid][modactions]</a><span>{lang stats_modworks_thismonth}</span></li>
											<!--{/if}-->
											<!--{if $_G[setting][oltimespan]}-->
												<li class="stat-metric"><b>$team[members][$uid][totalol]</b><span>{lang onlinetime_total}</span></li>
												<li class="stat-metric"><b>$team[members][$uid][thismonthol]</b><span>{lang onlinetime_thismonth}</span></li>
											<!--{/if}-->
										</ul>
									</div>
								<!--{/loop}-->
							</div>
						<!--{/if}-->
					<!--{/loop}-->
				</section>
			<!--{/loop}-->

			<div class="notice">{lang stats_update}</div>
		</div>
	</div>
	<!--{subtemplate common/stat}-->
</div>
<!--{template common/footer}-->
