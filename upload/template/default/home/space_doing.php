<?php exit('Access Denied');?>
<!--{if $diymode}-->
	<!--{if $_G[setting][homepagestyle]}-->
		<!--{subtemplate home/space_header}-->
		<div id="ct" class="ct2 wp cl">
			<div class="mn">
				<div class="bm">
					<div class="bm_h">
						<h1 class="mt">{lang doing}</h1>
					</div>
				<div class="bm_c">
	<!--{else}-->
		<!--{template common/header}-->
		<div id="pt" class="bm cl">
			<div class="z">
				<a href="./" class="nvhm" title="{lang homepage}">$_G[setting][bbname]</a> <em>&rsaquo;</em>
				<a href="home.php?mod=space&uid=$space[uid]">{$space[username]}</a> <em>&rsaquo;</em>
				<a href="home.php?mod=space&uid=$space[uid]&do=doing&view=me&from=space">{lang doing}</a>
			</div>
		</div>
		<style id="diy_style" type="text/css"></style>
		<div class="wp">
			<!--[diy=diy1]--><div id="diy1" class="area"></div><!--[/diy]-->
		</div>
		<!--{template home/space_menu}-->
		<div id="ct" class="ct1 wp cl">
			<div class="mn">
				<!--[diy=diycontenttop]--><div id="diycontenttop" class="area"></div><!--[/diy]-->
				<div class="bm bw0">
					<div class="bm_c">
	<!--{/if}-->
	<!--{if $space[self] && helper_access::check_module('doing')}--><!--{template home/space_doing_form}--><!--{/if}-->
<!--{else}-->
	<!--{template common/header}-->
	<div id="pt" class="bm cl">
		<div class="z">
			<a href="./" class="nvhm" title="{lang homepage}">$_G[setting][bbname]</a> <em>&rsaquo;</em>
			<a href="home.php?mod=space&do=doing">{lang doing}</a>
		</div>
	</div>
	<style id="diy_style" type="text/css"></style>
	<div class="wp">
		<!--[diy=diy1]--><div id="diy1" class="area"></div><!--[/diy]-->
	</div>
	<div id="ct" class="ct2_a wp cl">
		<!--{if $_G[setting][homestyle]}-->
			<div class="appl">
				<!--{subtemplate common/userabout}-->
			</div>
			<div class="mn pbm">
				<!--{hook/space_doing_top}-->
				<!--[diy=diycontenttop]--><div id="diycontenttop" class="area"></div><!--[/diy]-->
				<div class="bm bw0">
					<!--{if $space[self] && helper_access::check_module('doing')}-->
					<!--{template home/space_doing_form}-->
					<!--{/if}-->
					<!--{hook/space_doing_bottom}-->
					<ul class="tb cl">
						<li$actives[all]><a href="home.php?mod=space&do=$do&view=all">{lang view_all}</a></li>
						<li$actives[me]><a href="home.php?mod=space&do=$do&view=me"{if !$_G['uid']} onclick="showWindow('login', 'member.php?mod=logging&action=login&guestmessage=yes&referer='+encodeURIComponent(this.href))"{/if}>{lang doing_view_me}</a></li>
						<!--{if helper_access::check_module('follower')}-->
						<li$actives[follow]><a href="home.php?mod=space&do=$do&view=follow"{if !$_G['uid']} onclick="showWindow('login', 'member.php?mod=logging&action=login&guestmessage=yes&referer='+encodeURIComponent(this.href))"{/if}>{lang me_follow_doing}</a></li>
						<!--{/if}-->
						<!--{if helper_access::check_module('friend')}-->
						<li$actives[we]><a href="home.php?mod=space&do=$do&view=we"{if !$_G['uid']} onclick="showWindow('login', 'member.php?mod=logging&action=login&guestmessage=yes&referer='+encodeURIComponent(this.href))"{/if}>{lang me_friend_doing}</a></li>
						<!--{/if}-->
					</ul>
				</div>
		<!--{else}-->
			<div class="appl">
				<div class="tbn">
					<h2 class="mt bbda">{lang doing}</h2>
					<ul>
						<li$actives[all]><a href="home.php?mod=space&do=$do&view=all">{lang view_all}</a></li>
						<li$actives[me]><a href="home.php?mod=space&do=$do&view=me"{if !$_G['uid']} onclick="showWindow('login', 'member.php?mod=logging&action=login&guestmessage=yes&referer='+encodeURIComponent(this.href))"{/if}>{lang doing_view_me}</a></li>
						<!--{if helper_access::check_module('follower')}-->
						<li$actives[follow]><a href="home.php?mod=space&do=$do&view=follow"{if !$_G['uid']} onclick="showWindow('login', 'member.php?mod=logging&action=login&guestmessage=yes&referer='+encodeURIComponent(this.href))"{/if}>{lang me_follow_doing}</a></li>
						<!--{/if}-->
						<!--{if helper_access::check_module('friend')}-->
						<li$actives[we]><a href="home.php?mod=space&do=$do&view=we"{if !$_G['uid']} onclick="showWindow('login', 'member.php?mod=logging&action=login&guestmessage=yes&referer='+encodeURIComponent(this.href))"{/if}>{lang me_friend_doing}</a></li>
						<!--{/if}-->
					</ul>
				</div>
			</div>
			<div class="mn pbm">
			<!--[diy=diycontenttop]--><div id="diycontenttop" class="area"></div><!--[/diy]-->
			<!--{if $space[self] && helper_access::check_module('doing')}--><!--{template home/space_doing_form}--><!--{/if}-->
		<!--{/if}-->
		
<!--{/if}-->
		<!--{if $tagname}-->
				<p class="tbmu">{lang doing_tag_record} <span style="color: red; font-weight: 700;">#{$tag['tagname']}# </span> {lang doing_record_list}</p>
		<!--{/if}-->
		<!--{if $searchkey}-->
			<p class="tbmu">{lang doing_search_record} <span style="color: red; font-weight: 700;">$searchkey</span> {lang doing_record_list}</p>
		<!--{/if}-->
		<!--{if $dolist}-->
			<div class="xld {if empty($diymode)}xlda{/if}">
			<!--{loop $dolist $dv}-->
			<!--{eval $doid = $dv['doid'];}-->
			<!--{eval $_GET['key'] = $key = random(8);}-->
				<div id="{$key}dl{$doid}" class="doing_list cl">
					<div class="doing_list_item">
						<div class="doing_avatar">
							<a href="home.php?mod=space&uid=$dv[uid]" c="1"><!--{avatar($dv['uid'], 'small')}--></a>
						</div>
						<div class="item_right">
							<div class="item_author_box">
								<!--{if empty($diymode)}--><a href="home.php?mod=space&uid=$dv[uid]" class="author_name">$dv[username]</a><!--{/if}-->
							</div>
							<div class="item_info_date">
								<span ><!--{date($dv['dateline'], 'u')}--></span>
								<!--{if $_G['setting']['showiplocation']}--><span>$dv['iplocation']</span><!--{/if}-->
								<!--{if $dv[status] == 1}--> <span style="font-weight: bold;">({lang moderate_need})</span><!--{/if}-->
								<!--{if checkperm('managedoing')}-->
								<span >IP: $dv[ip]:$dv[port]</span>
								<!--{/if}-->
							</div>
							<div class="item_content">
								<div class="mbm">
								$dv[message]
								</div>
								<!--{if $dv['attachments']}-->
									<div class="doing_images">
										<!--{loop $dv['attachments'] $attach}-->
										<!--{if $attach['isimage']}-->
										<div class="doing_image_item">
											<a href="javascript:;" class="doing_image_link ">
												<img src="{$attach['thumb']}"
													zoomfile="{if $attach['remote']}{$_G['setting']['ftp']['attachurl']}{else}{$_G['setting']['attachurl']}{/if}doing/{$attach['attachment']}"
													file="{if $attach['remote']}{$_G['setting']['ftp']['attachurl']}{else}{$_G['setting']['attachurl']}{/if}doing/{$attach['attachment']}"
													onclick="zoom(this, this.getAttribute('zoomfile'), 0, 0, 0)"
													alt=""
													class="doing_image zoom"
													id="aimg_$attach[aid]"
													aid="$attach[aid]" />
											</a>
										</div>
										<!--{/if}-->
										<!--{/loop}-->
									</div>
									<!--{loop $dv['attachments'] $attach}-->
									<!--{if !$attach['isimage']}-->
									<!--{eval $doing_videoext = strtolower(pathinfo($attach['attachment'], PATHINFO_EXTENSION));}-->
									<div class="doing_video_item mbm">
										<div id="doingvideo_$attach[aid]" class="doingvideo_box"><div id="doingvideo_$attach[aid]_container"></div></div>
									</div>
									<script type="text/javascript">html5Player('doingvideo_$attach[aid]', '$doing_videoext', '{if $attach['remote']}{$_G['setting']['ftp']['attachurl']}{else}{$_G['setting']['attachurl']}{/if}doing/$attach[attachment]', '100%', '405px');</script>
									<!--{/if}-->
									<!--{/loop}-->
								<!--{/if}-->
								<!--{if $dv['body_template']}-->
									<div class="share_card_box mbm {$dv['type']} cl">
										<!--{if $dv['image']}-->
										<div class="share_card_img">
											<a href="$dv[image_link]" target="_blank" style="background: url($dv[image]) 50% 50%;background-size: cover;" ></a>
										</div>
										<!--{/if}-->
										<div class="share_card_imnfo">
											$dv[body_template]
										</div>
									</div>
								<!--{/if}-->
								<!--{if $dv['hotcomments']}-->
								<div class="doing_hotcomments">
									<div class="doing_hotcomments_list">
										<!--{loop $dv['hotcomments'] $cm}-->
										<div class="doing_hotcomment cl">
											<div class="doing_hotcomment_avt"><a href="home.php?mod=space&uid=$cm['uid']" target="_blank"><!--{avatar($cm['uid'],'small')}--></a></div>
											<div class="doing_hotcomment_body">
												<p class="doing_hotcomment_text">
													<a href="home.php?mod=space&uid=$cm['uid']" class="doing_hotcomment_author" target="_blank">$cm['username']</a>
													<span class="doing_hotcomment_msg">：$cm['message']</span>
												</p>
												<p class="doing_hotcomment_meta"><span class="xg1"><!--{date($cm['dateline'], 'u')}--></span></p>
											</div>
										</div>
										<!--{/loop}-->
									</div>
									<!--{if $hotcomment_count[$doid] > count($dv['hotcomments'])}-->
									<a href="home.php?mod=space&do=doing&doid=$doid" class="doing_hotcomments_more"><!--{eval echo str_replace('@num@', $hotcomment_count[$doid], lang('home/template', 'doing_hotcomment_more'));}--><i class="fico-right"></i></a>
									<!--{/if}-->
								</div>
								<!--{/if}-->
							</div>
							<div class="item_info">
								<div class="item_info_interactions">
									<div class="interactions_left">
										<!--{if $count == 1}-->
										<a href="javascript:;" class="doing_comment_btn icon_box" onclick="docomment_get($doid, '$key', 1)" data-doid="$doid" data-key="$key" title="{lang reply}"><i class="fico-comment"></i><span><!--{if $dv['replynum']}-->$dv['replynum']<!--{else}-->{lang reply}<!--{/if}--></span></a>
										<!--{else}-->
										<!-- 列表页采用微博式逻辑：热门评论已外透，点击评论数直接进入单条详情页查看/参与全部评论 -->
										<a href="home.php?mod=space&do=doing&doid=$doid" class="doing_comment_btn icon_box" data-doid="$doid" title="{lang reply}"><i class="fico-comment"></i><span><!--{if $dv['replynum']}-->$dv['replynum']<!--{else}-->{lang reply}<!--{/if}--></span></a>
										<!--{/if}-->
										<a href="javascript:;" class="doing_recommend_btn icon_box" data-doid="{$doid}" data-status="<!--{if $dv['recommendstatus']}-->1<!--{else}-->0<!--{/if}-->">
											<i class="<!--{if $dv['recommendstatus']}-->fico-thumbup fc-i<!--{else}-->fico-thumbup fc-s<!--{/if}-->"></i> 
											<span class="recommend_count"><!--{if $dv['recomends']}-->$dv['recomends']<!--{else}-->{$_G['setting']['recommendthread']['addtext']}<!--{/if}--></span>
										</a>
										<a href="javascript:;" class="doing_share_btn icon_box" data-doid="$doid" onclick="showWindow('sharedoing', 'home.php?mod=spacecp&ac=doing&type=doing&id={$doid}', 'get', 0);" title="{lang share}">
											<i class="fico-launch fc-s"></i>
											<span><!--{if $dv['sharetimes']}-->$dv['sharetimes']<!--{else}-->{lang share}<!--{/if}--></span>
										</a>
										<!--{if $dv[uid]==$_G[uid] || checkperm('managedoing')}--><a href="home.php?mod=spacecp&ac=doing&op=delete&doid={$doid}&handlekey=doinghk_{$doid}" id="{$key}_doing_delete_{$doid}" onclick="showWindow(this.id, this.href, 'get', 0);" class="icon_box" ><i class="fico-delete"></i>{lang delete}</a><!--{/if}-->
										<!--{hook/space_doing_content_toolbar $doid}-->
									</div>
									<div class="interactions_right"></div>
								</div>
								<!--{hook/space_doing_content_bottom $doid}-->
								<dd class=" brm" id="{$key}_$doid" style="display:none;"></dd>
							</div>
						</div>
					</div>
				</div>
			<!--{/loop}-->
			<!--{if $pricount}-->
				<p class="mtm">{lang hide_doing}</p>
			<!--{/if}-->
			</div>
			<!--{if $multi}-->
			<div class="pgs cl mtm">$multi</div>
			<!--{elseif $_GET[highlight]}-->
			<div class="pgs cl mtm"><div class="pg"><a href="home.php?mod=space&do=doing&view=me">{lang viewmore}</a></div></div>
			<!--{/if}-->
		<!--{else}-->
			<p class="emp">{lang doing_no_replay}<!--{if $space[self]}-->{lang doing_now}<!--{/if}--></p>
		<!--{/if}-->
		
		<!--{if !$_G[setting][homepagestyle]}--><!--[diy=diycontentbottom]--><div id="diycontentbottom" class="area"></div><!--[/diy]--><!--{/if}-->

		<!--{if $diymode}-->
					</div>
				</div>
			<!--{if $_G[setting][homepagestyle]}-->
			</div>
			<div class="sd">
				<!--{subtemplate home/space_userabout}-->
			<!--{/if}-->
		<!--{/if}-->
		</div>
	</div>

<!--{if !$_G[setting][homepagestyle]}-->
	<div class="wp mtn">
		<!--[diy=diy3]--><div id="diy3" class="area"></div><!--[/diy]-->
	</div>
<!--{/if}-->
<script type="text/javascript">
    window.onload = function() {
        var doingList = document.querySelectorAll('.doing_images');
        for (var i = 0; i < doingList.length; i++) {
            var images = doingList[i].querySelectorAll('.doing_image');
            if (images.length > 1) {
                var groupId = 'doing_group_' + i;
                if (typeof zoomgroup === 'undefined') {
                    zoomgroup = {};
                }
                if (typeof aimgcount === 'undefined') {
                    aimgcount = {};
                }
                aimgcount[groupId] = [];
                for (var j = 0; j < images.length; j++) {
                    var imgId = images[j].id;
                    if (imgId) {
                        zoomgroup[imgId] = groupId;
                        aimgcount[groupId].push(imgId.replace('aimg_', ''));
                    }
                }
            }
        }
        
        var recommendBtns = document.querySelectorAll('.doing_recommend_btn');
        for (let k = 0; k < recommendBtns.length; k++) {
            recommendBtns[k].addEventListener('click', function() {
                if (this.classList.contains('disabled')) {
                    return;
                }
                
                var doid = this.getAttribute('data-doid');
                var btn = this;
                var countElem = this.querySelector('.recommend_count');
                var iconElem = this.querySelector('i');
                btn.classList.add('disabled');
                
                fetch('home.php?mod=spacecp&ac=doing&op=recommend&doid=' + doid)
                    .then(response => response.json())
                    .then(data => {
                        btn.classList.remove('disabled');
                        
                        if (data && data.message === 'doing_recommend_success') {
                            btn.setAttribute('data-status', data.status);
                            countElem.innerHTML = data.count;
                            if (data.status === 1) {
                                iconElem.className = 'fico-thumbup fc-i';
                            } else {
                                iconElem.className = 'fico-thumbup fc-s';
                            }
                        } else {
                            console.error('error:', data);
                        }
                    })
                    .catch(error => {
                        btn.classList.remove('disabled');
                        console.error('error:', error);
                    });
            });
        }
        // 单条动态详情页自动加载评论和评论发布框
        <!--{if $count === 1}-->
        var commentLinks = document.querySelectorAll('.doing_comment_btn');
        if (commentLinks.length > 0) {
            // 获取第一个评论按钮的点击事件中的参数
                var doid = {$doid};
                var key = '{$key}';
                // 自动加载评论列表
                docomment_get(doid, key);
                // 自动加载评论发布框
                setTimeout(function() {
                    docomment_form(doid, 0, key);
                }, 100);
        }
        <!--{/if}-->
    };
</script>
<!--{template common/footer}-->
