<?php exit('Access Denied');?>
<!--{if $list}-->
<ul class="doing_cm_list">
<!--{loop $list $value}-->
	<li id="comment_{$value['id']}_li" class="doing_cm_item cl{$value['class']}" style="$value['style']">
		<div class="doing_cm_row{if $value['layer'] > 0} doing_cm_child{/if}">
			<!--{if $value['layer'] == 0}-->
			<div class="doing_cm_avt"><a href="home.php?mod=space&uid={$value['uid']}" c="1"><!--{avatar($value['uid'], 'small')}--></a></div>
			<!--{/if}-->
			<div class="doing_cm_body">
				<p class="doing_cm_text">
					<a href="home.php?mod=space&uid={$value['uid']}" class="doing_cm_author">{$value['username']}</a>
					<!--{if $value['reply_to_user']}-->
					<span class="doing_cm_replyto">{lang reply} <a href="home.php?mod=space&uid={$value['reply_uid']}">{$value['reply_to_user']}</a></span>
					<!--{/if}-->
					<span class="doing_cm_msg">：{$value['message']}</span>
				</p>
				<p class="doing_cm_meta">
					<span class="xg1"><!--{date($value['dateline'], 'u')}--></span>
					<span class="doing_cm_ops">
					<!--{if $_G['uid'] && helper_access::check_module('doing')}-->
					<a href="javascript:;" onclick="docomment_form({$value['doid']}, {$value['id']}, '{$_GET['key']}');">{lang reply}</a>
					<!--{/if}-->
					<!--{if $value['uid'] == $_G['uid'] || $dv['uid'] == $_G['uid'] || checkperm('managedoing')}-->
					<a href="home.php?mod=spacecp&ac=doing&op=delete&doid={$value['doid']}&docid={$value['id']}&handlekey=doinghk_{$value['doid']}_{$value['id']}" id="{$_GET['key']}_doing_delete_{$value['doid']}_{$value['id']}" onclick="showWindow(this.id, this.href, 'get', 0);">{lang delete}</a>
					<!--{/if}-->
					</span>
				</p>
				<div id="{$_GET['key']}_form_{$value['doid']}_{$value['id']}"></div>
			</div>
		</div>
	</li>
<!--{/loop}-->
</ul>
<!--{else}-->
<p>没有评论数据</p>
<!--{/if}-->
<div class="tri"></div>
