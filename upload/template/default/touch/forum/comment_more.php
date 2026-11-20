<?php exit('Access Denied');?>
<!--{template common/header}-->
<!--{if $comments}-->
	<h3 class="psth xs1"><span class="icon_ring vm"></span>{lang comments}</h3>
	<!--{if $totalcomment}--><div class="pstl">$totalcomment</div><!--{/if}-->
	<!--{loop $comments $comment}-->
				<div class="plc p0 cl" id="commentdetail_{$comment['id']}">
					<div class="avatar l0">$comment['avatar']</div>
						<div class="display pi">
						<ul class="authi">
							<li class="mtit">
								<span class="y"><!--{if $comment['useip'] && $_G['group']['allowviewip']}-->IP:$comment['useip']{if $comment['port']}:$comment['port']{/if}<!--{/if}--></span>
								<span class="z">
								<!--{if $comment['authorid']}-->
									<a href="home.php?mod=space&uid=$comment['authorid']" class="xi2 xw1">$comment['author']</a>
								<!--{else}-->
									{lang guest}
								<!--{/if}-->
								</span>
							</li>
							<li class="mtime">
								<em class="mgl"><!--{if $_G['forum']['ismoderator'] && $_G['group']['allowdelpost']}--><a href="forum.php?mod=topicadmin&action=delcomment&fid={$_G['fid']}&tid={$_G['tid']}&moderate[]={$_G['tid']}&topiclist[]={$comment['id']}&page={$_G['page']}" class="dialog cm-act">{lang delete}</a><!--{/if}--></em>
								{lang poston} $comment['dateline']
							</li>
							<li class="mtxt mt5">
								<!--{if !empty($comment['reply_author'])}-->
								<span class="cm-to">{lang reply} <a href="home.php?mod=space&uid=$comment['reply_authorid']" title="{$comment['reply_author']}">@$comment['reply_author']</a></span>
								<!--{/if}-->
								$comment['comment']
								<a href="forum.php?mod=misc&action=comment&tid=$comment[tid]&pid=$comment[pid]&rpid=$comment[id]&extra=$_GET[extra]&page=$page{if $_GET[from]}&from=$_GET[from]{/if}" class="xi2 dialog mgl cm-act">{lang reply}</a>
							</li>
						</ul>
					</div>
				</div>
	<!--{/loop}-->
<!--{/if}-->
<div class="pgs page mpage mbm cl">$multi</div>
<!--{template common/footer}-->