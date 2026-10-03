<?php exit('Access Denied');?><!--{eval echo "\xEF\xBB\xBF";}--><!--{if $op == 'modworks' && $uid}-->{lang stats_modworks} - $username
{$starttime} {lang stats_modworks_to} {$endtime} {lang stats_modworks_data}
{lang time},<!--{loop $modactioncode $key $val}-->$val,<!--{/loop}-->{lang stats_modworks_total}
<!--{loop $modactions $day $modaction}-->$day,<!--{loop $modactioncode $key $val}--><!--{if $modaction[$key]['posts']}-->$modaction[$key][count],<!--{else}-->,<!--{/if}--><!--{/loop}-->$modaction[total]
<!--{/loop}-->{lang stats_modworks_total},<!--{loop $modactioncode $key $val}--><!--{if $totalactions[$key]['posts']}-->$totalactions[$key][count],<!--{else}-->,<!--{/if}--><!--{/loop}-->$totalactions[total]
<!--{elseif $op == 'modworks'}-->{lang stats_modworks} - {lang stats_modworks_all}
{$starttime} {lang stats_modworks_to} {$endtime} {lang stats_modworks_data}
{lang username},<!--{loop $modactioncode $key $val}-->$val,<!--{/loop}-->{lang stats_modworks_total}
<!--{loop $members $uid $member}--><!--{eval echo strip_tags($member['username']);}-->,<!--{loop $modactioncode $key $val}--><!--{if $member[$key]['posts']}-->{$member[$key][count]},<!--{else}-->,<!--{/if}--><!--{/loop}-->$member[total]
<!--{/loop}--><!--{if $members}-->{lang stats_modworks_total},<!--{loop $modactioncode $key $val}--><!--{if $total[$key]['posts']}-->{$total[$key][count]},<!--{else}-->,<!--{/if}--><!--{/loop}-->$total[total]<!--{/if}--><!--{/if}-->
