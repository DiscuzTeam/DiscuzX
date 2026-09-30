<?php exit('Access Denied');?>
<!--{block row}-->
<div class="template_box">
	<div class="template_top">
		<h3 class="template_name">
			<!--{if $addonids[$id]}-->
				<a href="{ADMINSCRIPT}?action=cloudaddons&frame=no&id={$identifier}.template" target="_blank" title="{$style['copyright']}">$style[tplname]</a>
			<!--{else}-->
				$style['tplname']
			<!--{/if}-->
		</h3>
		<!--{if $style['version']}--><span class="template_version"> $style['version']</span><!--{/if}-->
		{$updatestring[$addonids[$style['styleid']]]}
	</div>
	<!--{if $id > 0}-->
		<div class="template_center">
			<div class="template_preview">
				<img <!--{if $previewlarge}--> style="cursor:pointer" title="{$lang['preview_large']}" onclick="zoom(this, '{$previewlarge}', 1)"<!--{/if}--> src="{$preview}" alt="{$lang['preview']}" onerror="this.onerror=null;this.src='./static/image/admincp/stylepreview.gif'" alt="">
			</div>
			<div class="template_switch">
				<div class="switch_top">
					<p>
						<label><input type="radio" class="radio" name="defaultnew" value="$id" $isdefault /> {$lang['styles_default0']}</label>
					</p>
					<!--{if $d2exists}-->
						<p><label><input type="radio" class="radio" name="defaultnew2" value="$id" {$isdefault2} /> {$lang['styles_default2']}</label></p>
					<!--{else}-->
						<p class="lightfont"><label><input type="radio" class="radio" disabled readonly /> {$lang['styles_default2']}</label></p>
					<!--{/if}-->
					<!--{if $d3exists}-->
						<p><label><input type="radio" class="radio" name="defaultnew3" value="$id" {$isdefault3} /> {$lang['styles_default3']}</label></p>
					<!--{else}-->
						<p class="lightfont"><label><input type="radio" class="radio" disabled readonly /> {$lang['styles_default3']}</label></p>
					<!--{/if}-->
					<p>
						<label>
							<!--{if $isdefault || $isdefault1 || $isdefault2 || $isdefault3}-->
							<input class="checkbox" type="checkbox" disabled="disabled" />
							<!--{else}-->
							<input class="checkbox" type="checkbox" name="delete[]" value="{$id}" />
							<!--{/if}-->
							{$lang['styles_uninstall']}
						</label>
					</p>
				</div>

			</div>
			<div class="template_menu">
				<div class="template_menu_top">
					<div class="template_control">
						<a href="{ADMINSCRIPT}?action=styles&operation=upgrade&id=$id" class="control_btn" ><img src="./static/image/admincp/svg/upgrade.svg">{$lang['plugins_config_upgrade']}</a>
						<!--{if $isfounder && $addonids[$id]}-->
						<a href="{ADMINSCRIPT}?action=cloudaddons&frame=no&id={$identifier}.template&from=comment" target="_blank" class="control_btn"><img src="./static/image/admincp/svg/comment.svg">{$lang['plugins_visit']}</a>
						<!--{/if}-->
					</div>
					<div class="template_control">
						<a href="{ADMINSCRIPT}?action=styles&operation=edit&id={$id}" class="control_btn" ><img src="./static/image/admincp/svg/edit.svg">{$lang['edit']}</a>
						<a href="{ADMINSCRIPT}?action=cells&id={$id}" class="control_btn"><img src="./static/image/admincp/svg/cell.svg">{$lang['cells']}</a>
					</div>
					<div class="template_control">
						<a href="{ADMINSCRIPT}?action=styles&operation=import&dir={$identifier}&restore={$id}" class="control_btn" ><img src="./static/image/admincp/svg/restore.svg">{$lang['restore']}</a>
						<a href="{ADMINSCRIPT}?action=styles&operation=copy&id=$id" class="control_btn"><img src="./static/image/admincp/svg/copy.svg"> {$lang['copy']}</a>
					</div>
					<div class="template_control">
						<!--{if $isplugindeveloper && $isfounder}-->
						<a href="{ADMINSCRIPT}?action=styles&operation=editvar&id=$id" class="control_btn"><img src="./static/image/admincp/svg/design.svg">{$lang['plugins_editlink']}</a>
						<!--{/if}-->
						<!--{if ($isplugindeveloper && $isfounder) || !$addonids[$style['styleid']] || !cloudaddons_getmd5($addonids[$style['styleid']])}-->
						<a href="{ADMINSCRIPT}?action=styles&operation=export&id=$id" class="control_btn"><img src="./static/image/admincp/svg/export.svg">$lang['export']</a>
						<!--{/if}-->
					</div>
				</div>
			</div>
		</div>
		<div class="template_bottom"><input type="text" class="txt" name="namenew[{$id}]" value="{$style['name']}" /></div>
	<!--{else}-->
		<div class="template_center">
			<div class="template_preview">
				<img src="{$preview}"<!--{if $previewlarge}--> style="cursor:pointer" title="{$lang['preview_large']}" onclick="zoom(this, '{$previewlarge}', 1)"<!--{/if}--> onerror="this.onerror=null;this.src='./static/image/admincp/stylepreview.gif'">
			</div>
			<div class="template_menu">
				<div class="template_menu_top">
					<a href="{ADMINSCRIPT}?action=styles&operation=import&dir={$identifier}" class="control_btn"><img src="./static/image/admincp/svg/install.svg"> {$lang['styles_install']}</a>
				</div>
				<!--{if $style['stylecount'] > 0}-->
					<p style="margin: 2px 0">{$lang['styles_stylecount']}: {$style['stylecount']}</p>
				<!--{/if}-->
				<!--{if $style['filemtime'] > $timestamp - 86400}-->
					<p style="margin-bottom: 2px;"><font color="red">New!</font></p>
				<!--{/if}-->
			</div>
		</div>
		<input type="hidden" name="namenew[{$id}]" value="{$style['name']}" />
		<!--{if !empty($style['desc'])}--><div class="template_bottom xg1">{$style['desc']}</div><!--{/if}-->
	<!--{/if}-->
</div>
<!--{/block}-->