<?php exit('Access Denied');?>
<!--{eval $_G['home_tpl_titles'] = array('{lang trends_and_statistics}');}-->
<!--{eval $_GET['types'] = isset($_GET['types']) && is_array($_GET['types']) ? $_GET['types'] : array();}-->
<!--{eval
$qend = dgmdate(TIMESTAMP, 'Y-m-d');
$qbegin7 = dgmdate(TIMESTAMP - 604800, 'Y-m-d');
$qbegin30 = dgmdate(TIMESTAMP - 2592000, 'Y-m-d');
$qbegin90 = dgmdate(TIMESTAMP - 7776000, 'Y-m-d');
$qbeginmonth = dgmdate(TIMESTAMP, 'Y-m-01');
$qendlastmonth = dgmdate(strtotime($qbeginmonth) - 86400, 'Y-m-d');
$qbeginlastmonth = dgmdate(strtotime($qbeginmonth) - 86400, 'Y-m-01');
}-->
<!--{template common/header}-->

<div id="pt" class="bm cl">
	<div class="z">
		<a href="./" class="nvhm" title="{lang homepage}">$_G[setting][bbname]</a><em>&rsaquo;</em>
		<a href="misc.php?mod=stat">{lang stats}</a><em>&rsaquo;</em>
		{lang trends_and_statistics}
	</div>
</div>

<div id="ct" class="ct2_a wp cl">
	<div class="mn">
		<div class="bm bw0 stat-page">
			<h1 class="mt">{lang trends_and_statistics}</h1>

			<form method="get" action="misc.php">
				<div class="stat-filter">
					<p class="stat-card-sub pbm">{lang stat_message}</p>

					<div class="stat-filter-row">
						<span class="stat-filter-key">{lang basic_data}</span>
						<div class="stat-filter-val">
							<a href="misc.php?mod=stat&op=trend" class="stat-chip{if $type == 'all'} on{/if}">{lang comprehensive_overview}</a>
							<!--{loop $cols['login'] $value}-->
							<label class="stat-chip{if in_array($value, $_GET['types'])} on{/if}"><input type="checkbox" name="types[]" value="$value" class="pc"{if in_array($value, $_GET['types'])} checked="checked"{/if} /><!--{eval echo lang('spacecp', "do_stat_$value");}--></label>
							<!--{/loop}-->
						</div>
					</div>

					<div class="stat-filter-row">
						<span class="stat-filter-key">$_G[setting][navs][2][navname]</span>
						<div class="stat-filter-val">
							<!--{loop $cols['forum'] $value}-->
							<label class="stat-chip{if in_array($value, $_GET['types'])} on{/if}"><input type="checkbox" name="types[]" value="$value" class="pc"{if in_array($value, $_GET['types'])} checked="checked"{/if} /><!--{eval echo lang('spacecp', "do_stat_$value");}--></label>
							<!--{/loop}-->
						</div>
					</div>

					<div class="stat-filter-row">
						<span class="stat-filter-key">{lang tgroup}</span>
						<div class="stat-filter-val">
							<!--{loop $cols['tgroup'] $value}-->
							<label class="stat-chip{if in_array($value, $_GET['types'])} on{/if}"><input type="checkbox" name="types[]" value="$value" class="pc"{if in_array($value, $_GET['types'])} checked="checked"{/if} /><!--{eval echo lang('spacecp', "do_stat_$value");}--></label>
							<!--{/loop}-->
						</div>
					</div>

					<div class="stat-filter-row">
						<span class="stat-filter-key">{lang home}</span>
						<div class="stat-filter-val">
							<!--{loop $cols['home'] $value}-->
							<label class="stat-chip{if in_array($value, $_GET['types'])} on{/if}"><input type="checkbox" name="types[]" value="$value" class="pc"{if in_array($value, $_GET['types'])} checked="checked"{/if} /><!--{eval echo lang('spacecp', "do_stat_$value");}--></label>
							<!--{/loop}-->
						</div>
					</div>

					<div class="stat-filter-row">
						<span class="stat-filter-key">{lang member_interactive}</span>
						<div class="stat-filter-val">
							<!--{loop $cols['space'] $value}-->
							<label class="stat-chip{if in_array($value, $_GET['types'])} on{/if}"><input type="checkbox" name="types[]" value="$value" class="pc"{if in_array($value, $_GET['types'])} checked="checked"{/if} /><!--{eval echo lang('spacecp', "do_stat_$value");}--></label>
							<!--{/loop}-->
						</div>
					</div>

					<div class="stat-filter-row">
						<span class="stat-filter-key">{lang stat_date_range}</span>
						<div class="stat-filter-val">
							<div class="stat-range">
								<a href="misc.php?mod=stat&op=trend&primarybegin=$qbegin7&primaryend=$qend">近 7 天</a>
								<a href="misc.php?mod=stat&op=trend&primarybegin=$qbegin30&primaryend=$qend">近 30 天</a>
								<a href="misc.php?mod=stat&op=trend&primarybegin=$qbegin90&primaryend=$qend">近 90 天</a>
								<a href="misc.php?mod=stat&op=trend&primarybegin=$qbeginmonth&primaryend=$qend">本月</a>
								<a href="misc.php?mod=stat&op=trend&primarybegin=$qbeginlastmonth&primaryend=$qendlastmonth">上月</a>
							</div>
						</div>
					</div>

					<div class="stat-bar">
						<script type="text/javascript" src="{$_G[setting][jspath]}calendar.js?{VERHASH}"></script>
						<input type="text" name="primarybegin" class="px" value="$primarybegin" onclick="showcalendar(event, this, false)" />
						<span class="xg1">-</span>
						<input type="text" name="primaryend" class="px" value="$primaryend" onclick="showcalendar(event, this, false)" />
						<label class="stat-chip{if !empty($merge)} on{/if}"><input type="checkbox" name="merge" value="1" class="pc"{if !empty($merge)} checked="checked"{/if} />{lang stat_merge_statistic}</label>
						<button type="submit" class="pn pnc"><strong>{lang show}</strong></button>
					</div>
				</div>
				<input type="hidden" name="type" value="$_GET[type]" />
				<input type="hidden" name="mod" value="stat" />
				<input type="hidden" name="op" value="trend" />
			</form>

			<section class="stat-card">
				<header class="stat-card-hd">
					<h2><!--{if $type=='all'}-->{lang comprehensive_overview}<!--{else}--><!--{loop $_GET[types] $ktype}--><!--{eval echo lang('spacecp', "do_stat_$ktype");}--> <!--{/loop}--><!--{/if}--></h2>
					<p class="stat-card-sub"><!--{if $type=='all'}-->{lang comprehensive_overview_message}<!--{else}-->$primarybegin ~ $primaryend<!--{/if}--></p>
				</header>
				<!--{if $type=='all'}-->
					<ul class="stat-card-sub pbm">{lang interactive_help_message}</ul>
				<!--{/if}-->
				<div class="stat-chart stat-chart-h420" id="statchart"></div>
			</section>
		</div>
	</div>
	<!--{subtemplate common/stat}-->
</div>
<script src="{STATICURL}js/echarts/echarts.common.min.js"></script>
<script src="{$_G['setting']['jspath']}stat.js"></script>
<script type="text/javascript">drawstatchart('{$statuspara}', 420);</script>
<!--{template common/footer}-->
