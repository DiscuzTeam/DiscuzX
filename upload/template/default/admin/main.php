<?php exit('Access Denied');?>
<!DOCTYPE html>
<html><head>
	<meta charset="$charset">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="color-scheme" content="light dark">
	<title>{$menuData['title']}</title>
	<meta content="Discuz! Team" name="Copyright" />
	{template admin/rootcolor}
	<link rel="stylesheet" href="{$staticurl}image/admincp/minireset.css?{$_G['style']['verhash']}" type="text/css" media="all" />
	<link rel="stylesheet" href="{$staticurl}image/admincp/admincpframe.css?{$_G['style']['verhash']}" type="text/css" media="all" />
	$framecss
	<script type="text/JavaScript">
		var VERHASH = '$VERHASH', IN_ADMINCP = true, SITEURL = '{$_G['siteurl']}', JSPATH = '{$_G['setting']['jspath']}', JSCACHEPATH = '{$_G['setting']['jscachepath']}', cookiepre = '{$_G['config']['cookie']['cookiepre']}', cookiedomain = '{$_G['config']['cookie']['cookiedomain']}', cookiepath = '{$_G['config']['cookie']['cookiepath']}', DEFAULTAVATAR = '$_G[setting][defaultavatar]';
		var headers = new Array($headers), admincpfilename = '$basescript', admincpextra = '$extra';
		var ADMIN_COLOR_VARS = ['--root-base-value'];
	</script>
	<script src="{$_G['setting']['jspath']}common.js?{$_G['style']['verhash']}" type="text/javascript"></script>
</head>
<body>
<div id="append_parent"></div><div id="ajaxwaitid" style="display:none"></div>
$shownotice
<div id="bdcontainer"$oldlayout>
	<div id="navcontainer" class="navcontainer">
		<nav>
			{$menuData['logo']}
			<!--{if !empty($_ENV['menuupdate'])}-->
				{eval $p = http_build_query($_GET);}
				<div id="menuupdate">
					<a class="close" href="{ADMINSCRIPT}?$p&closemenunotice=yes" title="{lang menu_update_close}"></a>
					<a href="{ADMINSCRIPT}?$p&resetmenu=yes" title="{lang menu_update_tips}">{lang menu_update}</a>
				</div>
			<!--{/if}-->
			<a id="cpsetting"><span></span></a>
			<ul id="leftmenu">
				$leftmenus
			</ul>
			<div id="copyright"><img src="{$staticurl}image/mitframe/mitframe.svg"/></div>
		</nav>
	</div>
	<div class="ifmcontainer">
		<div class="mainhd">
			<div id="navbtn"><div></div></div>
			<div class="currentloca" id="admincpnav"{if !$oldlayout} style="display: none"{/if}></div>
			<div id="tabs"></div>
			{$menuData['navbar']}
			<div class="uinfo">
				<ul id="topmenu">
					$topmenus
				</ul>
				<div class="frameuinfo" >
					<!--{if $sitevip}-->
						<div id="_siteVip">&nbsp;</div>
						<script>
							_attachEvent(window, 'load', function () {
								var _as = document.createElement("script");
								_as.src = "https://addon.dismall.com/api/sitevip/?$sitevip";
								var _s = document.getElementsByTagName("script")[0];
								_s.parentNode.insertBefore(_as, _s);
							}, document);
						</script>
					<!--{/if}-->
					<p class="coloricon" id="adminColor" title="{lang admincolor}" onclick="showMenu({'ctrlid':this.id, 'pos':'00', 'duration': 3, 'mtype': 'win'});"></p>
					<div id="frameuinfo" onmouseover="showMenu({'ctrlid':this.id, 'pos':'34'});" >
						<p class="greet"><em>{$_G['member']['username']}</em><br />$cpadmingroup</p>
						{$useravt}
						<p class="btnlink">
							<div class="darkmode" title="$light_mode">
								<div>
									<div class="dk-light">
										<!--{subtemplate admin/svg/light}-->
									</div>
									<div class="dk-dark">
										<!--{subtemplate admin/svg/dark}-->
									</div>
								</div>
								<ul id="dkm_menu" style="display: none;"><li class="current">$by_system</li><li>$normal_mode</li><li>$dark_mode</li></ul>
							</div>
						</p>
					</div>
				</div>

				<div id="adminColor_menu" style="display: none">
					<a onclick="display('adminColor_menu')" class="close">Χ</a>
					{template admin/admincolor}
					<iframe id="adminColor_target" name="adminColor_target" style="display: none"></iframe>
				</div>

				<div id="frameuinfo_menu" class="frameuinfo_menu" disautofocus="1" style="display: none">
					<div class="btnlink">
						<div class="cl">
							<a href="index.php" target="_blank"  class="z"><i class="dzicon index"></i> $header_bbs</a>
							<a href="$basescript?action=logout" class="dzlogout y" target="_top"><i class="dzicon logout"></i> $header_logout</a>
						</div>
						<!--{if !empty($_G['cache']['admin']['platform']) && count($_G['cache']['admin']['platform']) > 1}-->
						<div class="platform_box">
						<!--{loop $_G['cache']['admin']['platform'] $platform $platform_data}-->
						<a href="javascript:;" platform="$platform" title="{$platform_data['name']}" onclick="switchplatform('$platform', <!--{if !empty($platform_data['location'])}-->1<!--{else}-->0<!--{/if}-->)"><i class="dzicon platform"></i><span class="platform_name">{$platform_data['name']}</span></a>
						<!--{/loop}-->
						</div>
						<!--{/if}-->
						<!--{if PLATFORM == 'system'}-->
						<div class="platform_box">
							<a href="javascript:;" id="mitframeapps" target="main">{echo cplang('mitframe_apps');}</a>
						</div>
						<!--{/if}-->
						{$menuData['menubar']}
					</div>
				</div>
			</div>
		</div>
		<div id="footerbars">
			<span>
				<a href="https://code.dismall.com/" class="lightlink2" target="_blank">$lang['discuz_git']</a>
				<i>|</i>
				<a href="https://www.discuz.vip/" class="lightlink2" target="_blank">$lang['discussion_area']</a>
				<i>|</i>
				<a href="https://www.dismall.com/" class="lightlink2" target="_blank">$lang['app_discussion']</a>
				<i>|</i>
				<a href="javascript:;" onmouseover="showMenu({'ctrlid':this.id});" id="qr">$lang['discuz_qrcode']</a>
			</span>
			<p>Powered by <a href="https://www.discuz.vip/" target="_blank" class="lightlink2">Discuz! $_G['setting']['version']</a> {lang copyright}</p>
		</div>
		<div id="qr_menu" style="display: none;"><img src="static/image/admincp/discuz_qr.jpg" width="120" /></div>
		<div id="favbars" style="display: none">
			<a id="op_back" title="{lang back}"></a><a id="op_refresh" title="{lang refresh}"></a><span>|</span>
			<div id="favbar_list">
				<!--{loop get_custommenu() $row}-->
				<a href="{ADMINSCRIPT}?action={$row[1]}">{$row[0]}</a>
				<!--{/loop}-->
			</div>
			<div id="favbar_mgr"><span>|</span><a href="{ADMINSCRIPT}?action=misc&operation=custommenu">{lang favmgr}</a></div>
		</div>
		<iframe src="{if !empty($_GET['frames'])}{ADMINSCRIPT}?$extra{else}{if !$oldlayout}{ADMINSCRIPT}?action=index&blank{/if}{/if}" id="main" name="main" class="mainframe"></iframe>
	</div>
</div>
<script>
	{if empty($_GET['frames'])}
	var defaultTab = 'submn_{if $_G['defaultTab']}$_G['defaultTab']{else}{$menuData['defaultId']}{/if}';
	{/if}
	var defaultUrl = '{ADMINSCRIPT}?action=index&blank';
</script>
<script src="{$_G['setting']['jspath']}admincp_frame.js?{$_G['style']['verhash']}" type="text/javascript"></script>
<script>
	init_darkmode();
</script>
</body></html>