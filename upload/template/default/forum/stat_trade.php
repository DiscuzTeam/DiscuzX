<?php exit('Access Denied');?>
<!-- 图表数据预计算：Discuz 模板的 eval 标签正则为非贪婪，块内不可出现花括号，故统一用单语句数组函数 -->
<!--{eval $sumTop = array_slice((array)$tradesums, 0, 8);}-->
<!--{eval $sumCats = array_column($sumTop, 'subject');}-->
<!--{eval $sumVals = array_map('floatval', array_column($sumTop, 'tradesum'));}-->
<!--{eval $itemTop = array_slice((array)$totalitems, 0, 8);}-->
<!--{eval $itemCats = array_column($itemTop, 'subject');}-->
<!--{eval $itemVals = array_map('intval', array_column($itemTop, 'totalitems'));}-->
<!--{template common/header}-->
<div id="pt" class="bm cl">
	<div class="z">
		<a href="./" class="nvhm" title="{lang homepage}">$_G[setting][bbname]</a><em>&rsaquo;</em>
		<a href="misc.php?mod=stat">{lang stats}</a><em>&rsaquo;</em>
		{lang stats_trade_rank}
	</div>
</div>
<div id="ct" class="ct2_a wp cl">
	<div class="mn">
		<div class="bm bw0 stat-page">
			<h1 class="mt">{lang stats_trade_rank}</h1>

			<!--{if $tradesums}-->
				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang trade_price_sort}</h2>
						<p class="stat-card-sub">{lang trade_totalprice}（{lang payment_unit}）· TOP {echo min(8, count($tradesums))}</p>
					</header>
					<div class="stat-chart stat-chart-h320" id="stat_bar_tradesum"></div>
					<div class="stat-group">
						<ul class="stat-rank">
							<!--{loop $tradesums $key $tradesum}-->
								<li>
									<span class="stat-rank-no">{echo $key + 1}</span>
									<a class="stat-rank-t" target="_blank" href="forum.php?mod=viewthread&do=tradeinfo&tid=$tradesum[tid]&pid=$tradesum[pid]" title="$tradesum[subject]">$tradesum[subject]</a>
									<span class="stat-rank-t xg1" style="flex:0 0 120px;text-align:right">$tradesum[seller]</span>
									<span class="stat-rank-v">$tradesum[tradesum]</span>
								</li>
							<!--{/loop}-->
						</ul>
					</div>
				</section>
			<!--{else}-->
				<section class="stat-card"><div class="stat-empty">{lang trade_price_sort} — {lang none}</div></section>
			<!--{/if}-->

			<!--{if $totalitems}-->
				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang trace_number_sort}</h2>
						<p class="stat-card-sub">{lang trace_sell_number} · TOP {echo min(8, count($totalitems))}</p>
					</header>
					<div class="stat-chart stat-chart-h320" id="stat_bar_totalitems"></div>
					<div class="stat-group">
						<ul class="stat-rank">
							<!--{loop $totalitems $key $totalitem}-->
								<li>
									<span class="stat-rank-no">{echo $key + 1}</span>
									<a class="stat-rank-t" target="_blank" href="forum.php?mod=viewthread&do=tradeinfo&tid=$totalitem[tid]&pid=$totalitem[pid]" title="$totalitem[subject]">$totalitem[subject]</a>
									<span class="stat-rank-t xg1" style="flex:0 0 120px;text-align:right">$totalitem[seller]</span>
									<span class="stat-rank-v">$totalitem[totalitems]</span>
								</li>
							<!--{/loop}-->
						</ul>
					</div>
				</section>
			<!--{else}-->
				<section class="stat-card"><div class="stat-empty">{lang trace_number_sort} — {lang none}</div></section>
			<!--{/if}-->

			<!--{if !empty($credittradesums)}-->
				<section class="stat-card">
					<header class="stat-card-hd"><h2>{lang trade_credit_sort}</h2></header>
					<ul class="stat-rank">
						<!--{loop $credittradesums $key $credittradesum}-->
							<li>
								<span class="stat-rank-no">{echo $key + 1}</span>
								<a class="stat-rank-t" target="_blank" href="forum.php?mod=viewthread&do=tradeinfo&tid=$credittradesum[tid]&pid=$credittradesum[pid]" title="$credittradesum[subject]">$credittradesum[subject]</a>
								<span class="stat-rank-t xg1" style="flex:0 0 120px;text-align:right">$credittradesum[seller]</span>
								<span class="stat-rank-v">$credittradesum[credittradesum]</span>
							</li>
						<!--{/loop}-->
					</ul>
				</section>
			<!--{/if}-->

			<div class="notice">{lang stats_update}</div>
		</div>
	</div>
	<div class="sd">
		<!--{subtemplate common/stat}-->
	</div>
</div>
<script src="{STATICURL}js/echarts/echarts.common.min.js"></script>
<script src="{$_G['setting']['jspath']}stat.js?{VERHASH}"></script>
<script type="text/javascript">
<!--{if $tradesums}-->statBarH($('stat_bar_tradesum'), {echo json_encode($sumCats)}, {echo json_encode($sumVals)}, {color: 3});<!--{/if}-->
<!--{if $totalitems}-->statBarH($('stat_bar_totalitems'), {echo json_encode($itemCats)}, {echo json_encode($itemVals)}, {color: 0});<!--{/if}-->
</script>
<!--{template common/footer}-->
