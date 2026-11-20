<?php exit('Access Denied');?>
<!--{block col1}-->
<img src="$logo" onerror="this.src='{STATICURL}image/admincp/plugin_logo.png';this.onerror=null" width="80" height="80" align="left" />
<!--{/block}-->
<!--{block col2}-->
<h3 <!--{if !$plugin['available']}-->class="light"<!--{/if}--> style="font-size:16px">
	$name $version <span class="smallfont">(<a href="{ADMINSCRIPT}?action=cloudaddons&frame=no&id={$plugin['identifier']}.plugin" style="color: #555;" target="_blank" title="{$lang['cloudaddons_linkto']}">{$plugin['identifier']}</a>)</span>
	<!--{if $updateinfo}--><b>$updateinfo</b><!--{/if}-->
	<!--{if $plugin['description'] || $plugin['modules']['extra']['intro']}-->
		<a href="javascript:;" onclick="display('intro_{$plugin['pluginid']}')" class="memo w1000hide">{$lang['plugins_home']}</a>
	<!--{/if}-->
</h3>
<!--{if $plugin['description'] || $plugin['modules']['extra']['intro']}-->
	<div id="intro_{$plugin['pluginid']}" class="memo" style="display:none">{$plugin['description']}<br />{$plugin['modules']['extra']['intro']}</div>
<!--{/if}-->
<p>
	<span class="light">
	<!--{if $plugin['copyright']}-->
		{$lang['author']}: $copyright
	<!--{/if}-->
	<a href="{ADMINSCRIPT}?action=cloudaddons&frame=no&id={$plugin['identifier']}.plugin&from=comment" target="_blank" title="{$lang['cloudaddons_linkto']}">{$lang['plugins_visit']}</a>
	</span>
</p>
<p>$submenuitems</p>
<!--{/block}-->
<!--{block col3}-->
<div class="control_btns">
<!--{if $isplugindeveloper && !$plugin['modules']['system']}-->
	<a href="{ADMINSCRIPT}?action=plugins&operation=edit&pluginid={$plugin['pluginid']}" class="control_btn"><img src="./static/image/admincp/svg/design.svg">{$lang['plugins_editlink']}</a>&nbsp;&nbsp;
<!--{/if}-->
<!--{if !$plugin['modules']['system']}-->
	<a href="{ADMINSCRIPT}?action=plugins&operation=delete&pluginid={$plugin['pluginid']}&formhash={FORMHASH}" onclick="return confirm('$uninstalltips');" class="control_btn"><img src="./static/image/admincp/svg/uninstall.svg">{$lang['plugins_config_uninstall']}</a>&nbsp;&nbsp;
<!--{/if}-->
<!--{if !$plugin['available']}-->
	<a href="{ADMINSCRIPT}?action=plugins&operation=enable&pluginid={$plugin['pluginid']}&formhash={FORMHASH}<!--{if !empty($_GET['system'])}-->&system=1<!--{/if}-->" class="control_btn"><img src="./static/image/admincp/svg/switch.svg">{$lang['enable']}</a>&nbsp;&nbsp;
<!--{else}-->
	<a href="{ADMINSCRIPT}?action=plugins&operation=disable&pluginid={$plugin['pluginid']}&formhash={FORMHASH}<!--{if !empty($_GET['system'])}-->&system=1<!--{/if}-->" class="control_btn"><img src="./static/image/admincp/svg/switch.svg">{$lang['closed']}</a>&nbsp;&nbsp;
<!--{/if}-->
<a href="{ADMINSCRIPT}?action=plugins&operation=upgrade&pluginid={$plugin['pluginid']}" class="control_btn"><img src="./static/image/admincp/svg/upgrade.svg">{$lang['plugins_config_upgrade']}</a>&nbsp;&nbsp;
</div>
<!--{if $hookexists !== FALSE && $plugin['available']}-->
<div style="margin-top: 10px">
{$lang['display_order']}: <input class="txt num" type="text" id="displayorder_{$plugin['pluginid']}" name="displayordernew[{$plugin['pluginid']}][$hookexists]" value="$hookorder" /><br /><br />
</div>
<!--{/if}-->
<!--{/block}-->