<?php exit('Access Denied');?>
<!DOCTYPE html>
<html>
<head>
	<title>$title</title>
	<meta charset="$charset">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="color-scheme" content="light dark">
	<link href="{$staticurl}image/admincp/minireset.css?{$_G['style']['verhash']}" rel="stylesheet" />
	<link href="{$staticurl}image/admincp/admincppage.css?{$_G['style']['verhash']}" rel="stylesheet" />
	<!--{if $_G['style']['admin_color']}-->
	<style>
	:root {
  		--root-base-value: {echo $_G['adminColor']['--root-base-value'] ?? '21,91,213'};
  		--root-base: rgb(var(--root-base-value));
		--admincp-color: $_G['style']['admin_color'];
		--admincp-ahover: var(--admincp-color);
		--admincp-ahot: $_G['style']['admin_color'];
	}
	.btn:hover { text-decoration: none; background-color:$_G['style']['admin_color'];}
	</style>	
	<!--{/if}-->
	$pagecss
</head>
<body>
<script type="text/JavaScript">
	var admincpfilename = '$basescript', IMGDIR = '$IMGDIR', STYLEID = '$STYLEID', VERHASH = '$VERHASH', IN_ADMINCP = true, ISFRAME = $frame, STATICURL='static/', SITEURL = '{$_G['siteurl']}', JSCACHEPATH = '{$_G['setting']['jscachepath']}', JSPATH = '{$_G['setting']['jspath']}', cookiepre = '{$_G['config']['cookie']['cookiepre']}', cookiedomain = '{$_G['config']['cookie']['cookiedomain']}', cookiepath = '{$_G['config']['cookie']['cookiepath']}';
</script>
<script src="{$_G['setting']['jspath']}common.js?{$_G['style']['verhash']}" type="text/javascript"></script>
<script src="{$_G['setting']['jspath']}admincp.js?{$_G['style']['verhash']}" type="text/javascript"></script>
<script type="text/javascript">showretheader('$title', '{ADMINSCRIPT}?frames=yes&action=index&js=yes');</script>
<div id="append_parent"></div><div id="ajaxwaitid"></div>
<div class="container" id="cpcontainer">
