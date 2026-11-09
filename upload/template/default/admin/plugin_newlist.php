<?php exit('Access Denied');?>
<!--{block col1}-->
<img src="$logo" onerror="this.src='{STATICURL}image/admincp/plugin_logo.png';this.onerror=null" width="80" height="80" align="left" style="margin-right:5px" />
<!--{/block}-->
<!--{block col2}-->
<h3 class="light" style="font-size:16px">{$entrytitle} {$entryversion}
	<!--{if $filemtime > TIMESTAMP - 86400}-->
		<font color="red">New!</font>
	<!--{/if}-->
	<span class="smallfont light">
		(<a href="{ADMINSCRIPT}?action=cloudaddons&frame=no&id={$entry}.plugin" style="color: #555;" target="_blank" title="{$lang['cloudaddons_linkto']}">$entry</a>)
	</span>
</h3>
<p>
	<span class="light"><!--{if $entrycopyright}-->{$lang['author']}: {$entrycopyright} | <!--{/if}-->
	<a href="{ADMINSCRIPT}?action=cloudaddons&frame=no&id={$entry}.plugin&from=comment" target="_blank" title="{$lang['cloudaddons_linkto']}">{$lang['plugins_visit']}</a>
	</span>
</p>
<!--{/block}-->
<!--{block col3}-->
<div class="control_btns">
	<a href="{ADMINSCRIPT}?action=plugins&operation=import&dir={$entry}&formhash={FORMHASH}" class="control_btn"><img src="./static/image/admincp/svg/install.svg">{$lang['plugins_config_install']}</a>
</div>
<!--{/block}-->