<?php exit('Access Denied');?>
<!-- 移动端评论表单片段：仅供单条详情页内联加载（op=docomment&fragment=1），独立于弹窗 UI -->
<!--{if helper_access::check_module('doing')}-->
	<div class="doing_inline_reply" id="{$_GET['key']}_form_inner_{$doid}_{$docid}">
		<form id="{$_GET['key']}_docommform_{$doid}_{$docid}" method="post" autocomplete="off" action="home.php?mod=spacecp&ac=doing&op=comment&doid=$doid&docid=$docid">
			<input type="hidden" name="commentsubmit" value="true" />
			<input type="hidden" name="formhash" value="{FORMHASH}" />
			<textarea name="message" id="{$_GET['key']}_form_{$doid}_{$docid}_t" rows="3" maxlength="200" placeholder="{lang spacecp_doing_message1} 200 {lang spacecp_doing_message2}"></textarea>
			<div class="dire_bar">
				<button type="submit" name="do_button" id="{$_GET['key']}_replybtn_{$doid}_{$docid}" value="true" class="dire_submit">{lang publish}</button>
			</div>
		</form>
		<span id="return_$_GET['handlekey']"></span>
	</div>
<!--{/if}-->
