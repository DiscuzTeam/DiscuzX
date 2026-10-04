<?php exit('Access Denied');?>
<!-- 图表数据预计算：Discuz 模板的 eval 标签正则为非贪婪，块内不可出现花括号，故统一用单语句数组函数 -->
<!--{eval $mposts = array_map('intval', array_column((array)$memberlist, 'posts'));}-->
<!--{eval $mnames = array_column((array)$memberlist, 'username');}-->
<!--{eval array_multisort($mposts, SORT_DESC, SORT_NUMERIC, $mnames);}-->
<!--{eval $mcats = array_map('strip_tags', array_slice($mnames, 0, 10));}-->
<!--{eval $mvals = array_slice($mposts, 0, 10);}-->
<!--{template common/header}-->
<div id="pt" class="bm cl">
	<div class="z">
		<a href="./" class="nvhm" title="{lang homepage}">$_G[setting][bbname]</a><em>&rsaquo;</em>
		<a href="misc.php?mod=stat">{lang stats}</a><em>&rsaquo;</em>
		{lang member_list}
	</div>
</div>
<div id="ct" class="ct2_a wp cl">
	<div class="mn">
		<div class="bm bw0 stat-page">

			<h1 class="mt">{lang member_list}</h1>

			<section class="stat-card">
				<header class="stat-card-hd stat-card-hd-bar">
					<div class="stat-card-hd-txt">
						<h2>{lang member_list}</h2>
						<p class="stat-card-sub">本页 {echo count($memberlist)} 位会员<!--{if !empty($_GET[srchmem])}--> · {lang search}: $_GET[srchmem]<!--{/if}--></p>
					</div>
					<form method="post" action="misc.php?mod=stat&op=memberlist" class="stat-search">
						<input type="hidden" name="formhash" value="{FORMHASH}" />
						<input type="text" name="srchmem" class="px vm" size="15" value="$_GET[srchmem]" />
						<button type="submit" class="pn vm"><em>{lang search}</em></button>
					</form>
				</header>
				<!--{eval $sortbase = 'misc.php?mod=stat&op=memberlist';}-->
				<!--{eval $sortq = !empty($_GET['srchmem']) ? '&srchmem='.rawurlencode($_GET['srchmem']) : '';}-->
				<!--{eval $so = isset($_GET['order']) ? $_GET['order'] : '';}-->
				<!--{eval $sa = !empty($_GET['asc']);}-->
				<!-- 排序并入表头：点击列头切换排序，箭头指示当前方向，替代独立的排序 chips 行 -->
				<div class="stat-list">
					<div class="stat-row stat-row-hd">
						<span class="stat-cell"><a href="$sortbase&order=username{if $so == 'username'}&asc={if $sa}0{else}1{/if}{else}&asc=0{/if}$sortq" class="stat-cell-hd stat-sort{if $so == 'username'} on{if $sa} asc{/if}{/if}">{lang username}</a></span>
						<span class="stat-cell-w1"><a href="$sortbase&order=gender{if $so == 'gender'}&asc={if $sa}0{else}1{/if}{else}&asc=0{/if}$sortq" class="stat-cell-hd stat-sort{if $so == 'gender'} on{if $sa} asc{/if}{/if}">{lang gender}</a></span>
						<span class="stat-cell-w3"><a href="$sortbase&order=regdate{if $so == 'regdate'}&asc={if $sa}0{else}1{/if}{else}&asc=0{/if}$sortq" class="stat-cell-hd stat-sort{if $so == 'regdate'} on{if $sa} asc{/if}{/if}">{lang regdate}</a></span>
						<span class="stat-cell-w3"><a href="$sortbase&order=lastvisit{if $so == 'lastvisit'}&asc={if $sa}0{else}1{/if}{else}&asc=0{/if}$sortq" class="stat-cell-hd stat-sort{if $so == 'lastvisit'} on{if $sa} asc{/if}{/if}">{lang lastvisit}</a></span>
						<span class="stat-cell-num"><a href="$sortbase&order=posts{if $so == 'posts'}&asc={if $sa}0{else}1{/if}{else}&asc=0{/if}$sortq" class="stat-cell-hd stat-sort{if $so == 'posts'} on{if $sa} asc{/if}{/if}">{lang posts}</a></span>
						<span class="stat-cell-num"><a href="$sortbase&order=credits{if $so == 'credits'}&asc={if $sa}0{else}1{/if}{else}&asc=0{/if}$sortq" class="stat-cell-hd stat-sort{if $so == 'credits'} on{if $sa} asc{/if}{/if}">{lang credits}</a></span>
					</div>
					<!--{loop $memberlist $member}-->
						<div class="stat-row">
							<span class="stat-cell stat-cell-user">{avatar($member['uid'], 'small')}<span class="stat-ava-txt"><a href="home.php?mod=space&uid=$member[uid]" target="_blank">$member[username]</a><em class="stat-ava-uid">UID $member[uid]</em></span></span>
							<span class="stat-cell-w1"><!--{if $member['gender'] == 1}-->{lang male}<!--{elseif $member['gender'] == 2}-->{lang female}<!--{else}--><span class="xg1">-</span><!--{/if}--></span>
							<span class="stat-cell-w3 xg1">$member[regdate]</span>
							<span class="stat-cell-w3 xg1">$member[lastvisit]</span>
							<span class="stat-cell-num">$member[posts]</span>
							<span class="stat-cell-num">$member[credits]</span>
						</div>
					<!--{else}-->
						<div class="stat-empty">{lang none}</div>
					<!--{/loop}-->
				</div>
				<!--{if !empty($multipage)}--><div class="pgs cl stat-pgs">$multipage</div><!--{/if}-->
			</section>

			<!--{if $mcats}-->
				<section class="stat-card">
					<header class="stat-card-hd">
						<h2>{lang posts} TOP 10</h2>
						<p class="stat-card-sub">{lang stats_main_topposter}（本页统计）</p>
					</header>
					<div class="stat-chart stat-chart-h320" id="stat_bar_toppost"></div>
				</section>
			<!--{/if}-->
		</div>
	</div>
	<!--{subtemplate common/stat}-->
</div>
<script src="{STATICURL}js/echarts/echarts.common.min.js"></script>
<script src="{$_G['setting']['jspath']}stat.js?{VERHASH}"></script>
<script type="text/javascript">
<!--{if $mcats}-->statBarH($('stat_bar_toppost'), {echo json_encode($mcats)}, {echo json_encode($mvals)}, {labelWidth: 96});<!--{/if}-->
</script>
<!--{template common/footer}-->
