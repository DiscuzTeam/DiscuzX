<?php exit('Access Denied');?>
<!--{template common/header}-->
<!--{if $comments}-->
	<h3 class="psth cm">{lang comments}</h3>
	<!--{if $totalcomment}--><div class="pstl">$totalcomment</div><!--{/if}-->
	<!--{loop $comments $comment}-->
		<div class="pstl cl">
			<div class="psta">
				$comment[avatar]&nbsp;
				<!--{if $comment['authorid']}-->
				<a href="home.php?mod=space&uid=$comment[authorid]" class="xi2 xw1">$comment[author]</a>
				<!--{else}-->
				{lang guest}
				<!--{/if}-->
			</div>
			<div class="psti">
				<!--{if !empty($comment['reply_author'])}-->
					<span class="cm-to">{lang reply} <a href="home.php?mod=space&uid=$comment[reply_authorid]" title="{$comment[reply_author]}">@$comment[reply_author]</a></span>
				<!--{/if}-->
				&nbsp;$comment[comment]&nbsp;
				<!--{if $comment[rpid] && empty($comment['reply_author'])}-->
					<a href="forum.php?mod=redirect&goto=findpost&pid=$comment[rpid]&ptid=$_G[tid]" class="xi2">{lang detail}</a>
					<a href="forum.php?mod=post&action=reply&fid=$_G[fid]&tid=$_G[tid]&reppost=$comment[rpid]&extra=$_GET[extra]&page=$page{if $_GET[from]}&from=$_GET[from]{/if}" class="xi2" onclick="showWindow('reply', this.href)">{lang reply}</a>
				<!--{/if}-->
				<span class="xg1">
					{lang poston} $comment[dateline]
					<!--{if $comment['useip'] && $_G['group']['allowviewip']}-->&nbsp;IP:$comment[useip]{if $comment[port]}:$comment[port]{/if}<!--{/if}-->
					<a href="forum.php?mod=misc&action=comment&tid=$comment[tid]&pid=$comment[pid]&rpid=$comment[id]&extra=$_GET[extra]&page=$page{if $_GET[from]}&from=$_GET[from]{/if}" class="xi2 cm-act" onclick="showWindow('comment', this.href)">{lang reply}</a>
					<!--{if $_G['forum']['ismoderator'] && $_G['group']['allowdelpost']}-->&nbsp;<a href="javascript:;" class="cm-act" onclick="modaction('delcomment', $comment[id])">{lang delete}</a><!--{/if}-->
				</span>
			</div>
		</div>
	<!--{/loop}-->
<!--{/if}-->
<div class="pgs mbm cl">$multi</div>
<!--{template common/footer}-->