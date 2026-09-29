<?PHP exit('Access Denied');?>
	</div>

<!--{if empty($topic) || ($topic['usefooter'])}-->
	<!--{eval $focusid = getfocus_rand($_G['basescript']);}-->
	<!--{if $focusid !== null}-->
		<!--{eval $focus = $_G['cache']['focus']['data'][$focusid];}-->
		<!--{eval $focusnum = count($_G['setting']['focus'][$_G['basescript']]);}-->
		<div class="focus" id="sitefocus">
			<div class="bm">
				<div class="bm_h cl">
					<a href="javascript:;" onclick="setcookie('nofocus_$_G['basescript']', 1, $_G['cache']['focus']['cookie']*3600);$('sitefocus').style.display='none'" class="y" title="{lang close}">{lang close}</a>
					<h2>
						<!--{if $_G['cache']['focus']['title']}-->{$_G['cache']['focus']['title']}<!--{else}-->{lang focus_hottopics}<!--{/if}-->
						<span id="focus_ctrl" class="fctrl"><img src="{IMGDIR}/pic_nv_prev.gif" alt="{lang footer_previous}" title="{lang footer_previous}" id="focusprev" class="cur1" onclick="showfocus('prev');" /> <em><span id="focuscur"></span>/$focusnum</em> <img src="{IMGDIR}/pic_nv_next.gif" alt="{lang footer_next}" title="{lang footer_next}" id="focusnext" class="cur1" onclick="showfocus('next')" /></span>
					</h2>
				</div>
				<div class="bm_c" id="focus_con">
				</div>
			</div>
		</div>
		<!--{eval $focusi = 0;}-->
		<!--{loop $_G['setting']['focus'][$_G['basescript']] $id}-->
				<div class="bm_c" style="display:none" id="focus_$focusi">
					<dl class="xld cl bbda">
						<dt><a href="{$_G['cache']['focus']['data'][$id]['url']}" class="xi2" target="_blank">$_G['cache']['focus']['data'][$id]['subject']</a></dt>
						<!--{if $_G['cache']['focus']['data'][$id]['image']}-->
						<dd class="m"><a href="{$_G['cache']['focus']['data'][$id]['url']}" target="_blank"><img src="{$_G['cache']['focus']['data'][$id]['image']}" alt="$_G['cache']['focus']['data'][$id]['subject']" /></a></dd>
						<!--{/if}-->
						<dd>$_G['cache']['focus']['data'][$id]['summary']</dd>
					</dl>
					<p class="ptn cl"><a href="{$_G['cache']['focus']['data'][$id]['url']}" class="xi2 y" target="_blank">{lang focus_show} &raquo;</a></p>
				</div>
		<!--{eval $focusi ++;}-->
		<!--{/loop}-->
		<script type="text/javascript">
			var focusnum = $focusnum;
			if(focusnum < 2) {
				$('focus_ctrl').style.display = 'none';
			}
			if(!$('focuscur').innerHTML) {
				var randomnum = parseInt(Math.round(Math.random() * focusnum));
				$('focuscur').innerHTML = Math.max(1, randomnum);
			}
			showfocus();
			var focusautoshow = window.setInterval('showfocus(\'next\', 1);', 5000);
		</script>
	<!--{/if}-->
	<!--{ad/footerbanner/wp a_f/1}--><!--{ad/footerbanner/wp a_f/2}--><!--{ad/footerbanner/wp a_f/3}-->
	<!--{ad/float/a_fl/1}--><!--{ad/float/a_fr/2}-->
	<!--{ad/couplebanner/a_fl a_cb/1}--><!--{ad/couplebanner/a_fr a_cb/2}-->
	<!--{ad/cornerbanner/a_cn}-->
	<!--{hook/global_footer}-->
	<div class="dz_footc cl">
		<div id="ft" class="wp dz_footc_bottom cl">
			<div class="dz_footc_dico">
				<!--{if $_G['style']['bottom_qrcode'] || $_G['style']['bottom_qrcodetxt']}-->
					<div class="ewmimg">
						<!--{if $_G['style']['bottom_qrcode']}-->
							<img src="{$_G['style']['bottom_qrcode']}">   
						<!--{else}-->
							<img src="{STYLEIMGDIR}/img/ewm_b.jpg">
						<!--{/if}-->
						<!--{if $_G['style']['bottom_qrcodetxt']}-->
							{$_G['style']['bottom_qrcodetxt']}
						<!--{else}-->
							{lang fllowwechat}
						<!--{/if}-->
					</div>
				<!--{/if}-->
				
			</div>
			<div class="dz_footc_nav">
				<!--{loop $_G['setting']['footernavs'] $nav}--><!--{if is_array($nav) && $nav['available'] && ($nav['type'] && (!$nav['level'] || ($nav['level'] == 1 && $_G['uid']) || ($nav['level'] == 2 && $_G['adminid'] > 0) || ($nav['level'] == 3 && $_G['adminid'] == 1)) ||
						!$nav['type'] && ($nav['id'] == 'stat' && $_G['group']['allowstatdata'] || $nav['id'] == 'report' && $_G['uid'] || $nav['id'] == 'archiver' || $nav['id'] == 'mobile' || $nav['id'] == 'darkroom'))}-->$nav['code']<span class="pipe">|</span><!--{/if}--><!--{/loop}-->
						<a href="$_G['setting']['siteurl']" rel="nofollow" target="_blank">$_G['setting']['sitename']</a>
						<!--{hook/global_footerlink}-->
						<!--{if $_G['setting']['statcode']}-->$_G['setting']['statcode']<!--{/if}-->
			</div>
			<div class="dz_footc_copy">
				<!--{if $_G['style']['bottom_txt']}-->
				<p>{$_G['style']['bottom_txt']}</p>
				<!--{/if}-->
				<p>{cells common/footer/copyright} {lang copyright}<!--{if $_G['setting']['icp'] || !empty($_G['setting']['mps'])}--><!--{if $_G['setting']['icp']}--><span class="pipe">|</span><a href="https://beian.miit.gov.cn/" target="_blank">$_G['setting']['icp']</a><!--{/if}--><!--{if !empty($_G['setting']['mps'])}--><!--{if $_G['setting']['icp']}--><span class="pipe">|</span><!--{/if}--><a href="https://beian.mps.gov.cn/#/query/webSearch?code=$_G['setting']['mpsid']" target="_blank"><img width="14" height="14" src="{STYLEIMGDIR}/img/ico_mps.png" />$_G['setting']['mps']</a><!--{/if}--><!--{/if}--></p>
			</div>
		</div>
	</div>
<!--{/if}-->

<div class="dz_rnav" id="dz_rnav">

	<!--{if $_G['style']['sider_fastpost']}-->
	<!--{if $_G['basescript'] == 'forum' || ($_G['basescript'] == 'group' && $_G['fid']) || ($_G['basescript'] == 'portal' && CURMODULE == 'index')}--><a {if $_G['fid']}href="forum.php?mod=post&action=newthread&fid={$_G['fid']}"{else}href="forum.php?mod=misc&action=nav" onclick="showWindow('nav', this.href, 'get', 0)"{/if} class="dz_rpost" rel="nofollow"><span class="dz_tip_text">{if $_G['fid']}{lang tmp104}{if $_G['basescript'] == 'group'}{lang tmp118}{else}{lang tmp105}{/if}{lang tmp066}{else}{lang tmp103}{lang tmp066}{/if}</span></a><!--{/if}-->
	<!--{/if}-->
	<!--{if $_G['style']['sider_wechat']}-->
	<a href="javascript:;" class="dz_rwx" rel="nofollow">
		<div class="dz_rewm_box">
			<div class="dz_rewm">
				<!--{if $_G['style']['sider_wechat']}-->
					<img src="{$_G['style']['sider_wechat_qrcode']}">
				<!--{else}-->
					<img src="{STYLEIMGDIR}/img/dz_wx.jpg">
				<!--{/if}-->
				<!--{if $_G['style']['sider_wechat']}-->
					<span>{$_G['style']['sider_wechat_txt']}</span>
				<!--{else}-->
					<span>{lang addservice}</span>
				<!--{/if}-->
			</div>
		</div>
	</a>
	<!--{/if}-->
	<!--{if $_G['setting']['site_qq']}--><a href="//wpa.qq.com/msgrd?v=3&uin=$_G['setting']['site_qq']&site=$_G['setting']['bbname']&menu=yes&from=discuz" class="dz_rqq" rel="nofollow" target="_blank"><span class="dz_tip_text">QQ{lang tmp097}</span></a><!--{/if}-->
	<a href="javascript:;" onclick="jQuery('body,html').animate({scrollTop:0}, 600);" title="{lang scrolltop}" id="dz_rtop" class="dz_rtop" rel="nofollow"><span class="dz_tip_text">{lang tmp096}</span></a>
</div>

<div id="scrolltop" style="display: none;">
	<!--{if $_G['fid'] && $_G['mod'] == 'viewthread'}-->
	<span><a href="forum.php?mod=post&action=reply&fid=$_G['fid']&tid=$_G['tid']&extra=$_GET['extra']&page=$page{if $_GET['from']}&from=$_GET['from']{/if}" onclick="showWindow('reply', this.href)" class="replyfast" title="{lang fastreply}"><b>{lang fastreply}</b></a></span>
	<!--{/if}-->
	<span hidefocus="true"><a title="{lang scrolltop}" onclick="window.scrollTo('0','0')" class="scrolltopa" ><b>{lang scrolltop}</b></a></span>
	<!--{if $_G['fid']}-->
	<span>
		<!--{if $_G['mod'] == 'viewthread'}-->
		<a href="forum.php?mod=forumdisplay&fid=$_G['fid']" hidefocus="true" class="returnlist" title="{lang return_list}"><b>{lang return_list}</b></a>
		<!--{else}-->
		<a href="forum.php" hidefocus="true" class="returnboard" title="{lang return_forum}"><b>{lang return_forum}</b></a>
		<!--{/if}-->
	</span>
	<!--{/if}-->
</div>
<script type="text/javascript">_attachEvent(window, 'scroll', function () { new_showTopLink(); });
_attachEvent(window, 'resize', function(){ new_showTopLink(); });checkBlind();</script>
<script type="text/javascript">
	function new_showTopLink() {
		var ft = $('ft');
		if(ft){
			var dzscrolltop = $('dz_rnav');
			var viewPortHeight = parseInt(document.documentElement.clientHeight);
			var dzscrollHeight = parseInt(document.body.getBoundingClientRect().top);
			var dzbasew = parseInt(ft.clientWidth);
			var dzsw = dzscrolltop.clientWidth;
			if (dzbasew < 1500) {
				var left = parseInt(fetchOffset(ft)['left']);
				left = left < dzsw ? left * 2 - dzsw : left;
				dzscrolltop.style.left = ( dzbasew + left ) + 'px';
			} else {
				dzscrolltop.style.left = 'auto';
				dzscrolltop.style.right = 0;
			}
			console.log(dzscrollHeight);
			
			if (dzscrollHeight < -100) {
				jQuery(".dz_rtop").slideDown();
			} else {
				jQuery(".dz_rtop").slideUp();
			}
		}
	}
	new_showTopLink();
	function show_win(on) {
		showMenu({
			'ctrlid': "win_"+on+"_div",
			'menuid': "win_"+on+"_box",
			'duration': 3,
			'pos': '00',
			'cover': 1,
			'drag': "win_"+on+"_move",
			'maxh': ''
		});
	}

</script>

{cells common/footer/js}

<!--{eval output();}-->
</body>
</html>
