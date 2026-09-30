<?php exit('Access Denied'); ?>
<!--{template common/header}-->

<div class="header cl">
	<div class="mz"><a href="javascript:history.back();"><i class="dm-c-left"></i></a></div>
	<h2>{lang doing}</h2>
	<div class="my"><a href="home.php?mod=spacecp&ac=doing"><i class="dm-edit"></i></a></div>
</div>
<div class="dhnav_box">
	<div class="dhnv cl">
		<div id="dhnav_li">
			<ul class="swiper-wrapper">
				<li class="swiper-slide{if $_GET['view'] == 'all'} mon{/if}"><a href="home.php?mod=space&do=$do&view=all">{lang view_all}</a></li>
				<li class="swiper-slide{if $_GET['view'] == 'me'} mon{/if}"><a href="home.php?mod=space&do=$do&view=me">{lang doing_view_me}</a></li>
				<!--{if helper_access::check_module('follower')}-->
				<li class="swiper-slide{if $_GET['view'] == 'follow'} mon{/if}"><a href="home.php?mod=space&do=$do&view=follow">{lang me_follow_doing}</a></li>
				<!--{/if}-->
				<!--{if helper_access::check_module('friend')}-->
				<li class="swiper-slide{if $_GET['view'] == 'we'} mon{/if}"><a href="home.php?mod=space&do=$do&view=we">{lang me_friend_doing}</a></li>
				<!--{/if}-->
			</ul>
		</div>
	</div>
</div>
<script type="text/javascript">
	initdhnav("#dhnav_li");
</script>
<!--{if $_G['uid'] && helper_access::check_module('doing')}-->
<!-- 微博式顶部内嵌发布框：登录用户直接在当前页发布记录 -->
<div class="doing_quickpost">
	<!--{template home/space_doing_form}-->
</div>
<!--{/if}-->
<script type="text/javascript">
// DPlayer 资源加载状态（移动端 common.js 无 html5Player，需自行加载）
// 注意：必须定义在记录列表之前，视频项的内联脚本在解析时就会调用
var doingDpLoaded = false;
var doingDpLoading = false;
var doingDpCallbacks = [];
function doingVideoPlayer(id, src) {
	var box = document.getElementById(id);
	if (!box) {
		return;
	}
	var start = function() {
		box.innerHTML = '';
		new DPlayer({
			container: box,
			autoplay: false,
			preload: 'none',
			mutex: true,
			video: {
				url: src,
				pic: src + '.thumb.jpg'
			}
		});
	};
	if (typeof DPlayer !== 'undefined') {
		start();
		return;
	}
	doingDpCallbacks.push(start);
	if (!doingDpLoaded && !doingDpLoading) {
		doingDpLoading = true;
		var css = document.createElement('link');
		css.rel = 'stylesheet';
		css.href = STATICURL + 'js/player/dplayer.min.css';
		document.head.appendChild(css);
		var js = document.createElement('script');
		js.src = STATICURL + 'js/player/dplayer.min.js';
		js.onload = function() {
			doingDpLoaded = true;
			var cbs = doingDpCallbacks;
			doingDpCallbacks = [];
			for (var i = 0; i < cbs.length; i++) {
				cbs[i]();
			}
		};
		document.body.appendChild(js);
	}
	var timer = setInterval(function() {
		if (typeof DPlayer !== 'undefined') {
			clearInterval(timer);
			var cbs = doingDpCallbacks;
			doingDpCallbacks = [];
			for (var i = 0; i < cbs.length; i++) {
				cbs[i]();
			}
		}
	}, 100);
	setTimeout(function() {
		clearInterval(timer);
	}, 10000);
}
</script>
<div class="doing_list threadlist_box cl">
	<div class="doing_list_box threadlist cl">
		<!--{if $tagname}-->
		<p class="p10 tbmu cl">{lang doing_tag_record} <span style="color: red; font-weight: 700;">#{$tag['tagname']}#</span> {lang doing_record_list}</p>
		<!--{/if}-->
		<!--{if $dolist}-->
		<!--{loop $dolist $dv}-->
		<!--{eval $doid = $dv['doid'];}-->
		<!--{eval $_GET['key'] = $key = random(8);}-->
		<div class="doing_card">
			<div class="doing_card_content">
				<div class="doing_card_top">
					<div class="doing_card_top_left">
						<div class="hl-author-icon">
							<span class="mio-avatar-badge">
								<a href="home.php?mod=space&uid=$dv['uid']&do=profile" class="mio-avatar">
									<!--{avatar($dv['uid'],'small')}-->
								</a>
							</span>
						</div>
						<div class="doing_card_info">
							<div class="doing_card_info_top">
								<a href="home.php?mod=space&uid=$dv['uid']&do=profile" id="author_$value['cid']" class="doing_card_info_name">$dv['username']</a>
							</div>
							<div class="doing_card_info_top">
								<span class="xg1"><!--{date($dv['dateline'], 'u')}--></span>
								<!--{if $_G['setting']['showiplocation']}--><span class="pl15 xg1">$dv['iplocation']</span><!--{/if}-->
							</div>
						</div>

					</div>
				</div>
				<div class="doing_card_text">
					<div class="doing_card_text_textcontent">
						<div id="comment_$doid" class="newmessage{if $value['magicflicker']} magicflicker{/if}">$dv[message]<!--{if $dv['status'] == 1}--> <span style="font-weight: bold;">({lang moderate_need})</span><!--{/if}--></div>
						<!--{if $dv['attachments']}-->
						<div class="doing_card_piclistbox">
							<div class="doing_card_piclist">
								<!--{loop $dv['attachments'] $attach}-->
								<!--{if $attach['isimage']}-->
								<div class="doing_card_piclist_item "><img lass="hl_noloadimage lazy lazy-fade-in" width="300" height="300" src="{$attach['thumb']}" data-src="{if $attach['remote']}{$_G['setting']['ftp']['attachurl']}{else}{$_G['setting']['attachurl']}{/if}doing/{$attach['attachment']}" zoomfile="{if $attach['remote']}{$_G['setting']['ftp']['attachurl']}{else}{$_G['setting']['attachurl']}{/if}doing/{$attach['attachment']}"></div>
								<!--{else}-->
								<div class="doing_card_video_item"><div id="doingvideo_$attach[aid]"></div></div>
								<script type="text/javascript">doingVideoPlayer('doingvideo_$attach[aid]', '{if $attach['remote']}{$_G['setting']['ftp']['attachurl']}{else}{$_G['setting']['attachurl']}{/if}doing/$attach[attachment]');</script>
								<!--{/if}-->
								<!--{/loop}-->
							</div>
						</div>
						<!--{/if}-->
					</div>
				</div>
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
					<!--{loop $dv['hotcomments'] $cm}-->
					<div class="doing_hotcomment">
						<div class="doing_hotcomment_avt"><a href="home.php?mod=space&uid=$cm['uid']"><!--{avatar($cm['uid'],'small')}--></a></div>
						<div class="doing_hotcomment_body">
							<p class="doing_hotcomment_text">
								<a href="home.php?mod=space&uid=$cm['uid']" class="doing_hotcomment_author">$cm['username']</a>
								<span class="doing_hotcomment_msg">：$cm['message']</span>
							</p>
							<p class="doing_hotcomment_meta"><span><!--{date($cm['dateline'], 'u')}--></span></p>
						</div>
					</div>
					<!--{/loop}-->
					<!--{if $hotcomment_count[$doid] > count($dv['hotcomments'])}-->
					<a href="home.php?mod=space&do=doing&doid=$doid" class="doing_hotcomments_more"><!--{eval echo str_replace('@num@', $hotcomment_count[$doid], lang('home/template', 'doing_hotcomment_more'));}--><i class="dm-c-right"></i></a>
					<!--{/if}-->
				</div>
				<!--{/if}-->
				<div class="doing_card_bottom">
					<div class="doing_card_bottom_left">
						<span class="doing_card_info-bottom-info"></span>
					</div>
					<div class="doing_card_bottom_right">
						<!--{if helper_access::check_module('doing')}-->
						<!--{if count($dolist) == 1}-->
						<!-- 单条记录页面：弹出评论框 -->
						<a href="home.php?mod=spacecp&ac=doing&op=docomment&handlekey=msg_0&doid=$doid&docid=0&key=$key" class="recommend{if $_G['uid']} dialog{/if}" data-doid="$doid">
							<i class="dm-chat-s"></i>
							<span class="bottom_num"><!--{if $dv['replynum']}-->$dv['replynum']<!--{else}-->{lang reply}<!--{/if}--></span>
						</a>
						<!--{else}-->
						<!-- 列表页面：跳转详情页 -->
						<a href="home.php?mod=space&do=doing&doid=$doid" class="recommend doing_comment_btn" data-doid="$doid">
							<i class="dm-chat-s"></i>
							<span class="bottom_num"><!--{if $dv['replynum']}-->$dv['replynum']<!--{else}-->{lang reply}<!--{/if}--></span>
						</a>
						<!--{/if}-->
						<!--{/if}-->
						<a href="javascript:;" class="recommend doing_recommend_btn" data-doid="$doid" data-status="<!--{if $dv['recommendstatus']}-->1<!--{else}-->0<!--{/if}-->">
							<i class="<!--{if $dv['recommendstatus']}-->fico-thumbup fc-i<!--{else}-->fico-thumbup fc-s<!--{/if}-->"></i>
							<span class="bottom_num recommend_count"><!--{if $dv['recomends']}-->$dv['recomends']<!--{else}-->{$_G['setting']['recommendthread']['addtext']}<!--{/if}--></span>
						</a>
						<a href="home.php?mod=spacecp&ac=doing&type=doing&id={$doid}" class="doing_share_btn {if $_G['uid']} dialog{/if}" data-doid="$doid" title="{lang share}">
							<i class="fico-launch fc-s"></i>
							<span class="bottom_num"><!--{if $dv['sharetimes']}-->$dv['sharetimes']<!--{else}-->{lang share}<!--{/if}--></span>
						</a>
						<!--{if $dv['uid']==$_G['uid'] || checkperm('managedoing')}-->
						<a href="home.php?mod=spacecp&ac=doing&op=delete&doid=$doid&docid=$dv['id']&handlekey=doinghk_{$doid}_$dv['id']" id="{$key}_doing_delete_{$doid}_{$dv['id']}" class="recommend{if $_G['uid']} dialog{/if}">
							<i class="dm-delete"></i>
							<span class="bottom_num">{lang delete}</span>
						</a>
						<!--{/if}-->
					</div>
				</div>
			</div>
			<div id="{$key}dl{$doid}" class="doing_card_comment">
				<!--{eval $list = $clist[$doid];}-->
				<div class="doing_card_quote" id="{$key}_$doid" {if empty($list) || !$showdoinglist[$doid]} style="display:none;" {/if}>
				<span id="{$key}_form_{$doid}_0"></span>
				<!--{template home/space_doing_li}-->
			</div>
		</div>
	</div>
	<!--{/loop}-->

	<!--{if $multi}-->
	<div class="pgs cl mtm">$multi</div>
	<!--{/if}-->

	<!--{else}-->
	<div class="threadlist_box mt10 cl">
		<h4>{lang doing_no_replay}</h4>
	</div>
	<!--{/if}-->
</div>
</div>
<script type="html" id="commentItemTemplate">
	<!-- 完整的评论项模板 -->
	<div id="comment_{:id:}_li" class="{:comment_class:}" data-root-id="{:root_id:}">
		<!--{:avatar_html:}-->
		<div class="comment-content">
			<div class="comment-header">
				<a href="home.php?mod=space&uid={:uid:}" class="comment-author">{:username:}</a>
				<!--{:reply_to_user:}-->
			</div>
			<div class="comment-body">{:message:}</div>
			<div class="comment-footer">
				<span class="comment-time">{:dateline_formatted:}</span>
				<div class="comment-actions">
					<a href="home.php?mod=spacecp&ac=doing&op=docomment&handlekey=msg_{:doid:}&doid={:doid:}&docid={:docid:}&key={:key:}" class="comment-action comment-action-reply dialog" onclick="return false;">回复</a>
					<!--{:delete_btn:}-->
				</div>
			</div>
			<div id="{:key:}_form_{:doid:}_{:docid:}" class="comment-form-container"></div>
		</div>
	</div>
</script>

<script type="html" id="commentToggleTemplate">
	<!-- 评论展开/折叠按钮模板 -->
	<div class="comment-toggle" data-root-id="{:root_id:}">
		<a href="javascript:;" class="toggle-comment-btn" onclick="toggle_child_comments({:root_id:});">
			<span class="toggle-icon">+</span> 查看更多{:hide_count:}条评论
		</a>
	</div>
</script>

<script type="html" id="commentListTemplate">
	<!-- 评论列表模板 -->
	<span id="{:key:}_form_{:doid:}_0"></span>
	<div class="doing-card-comments">
		{:comment_items:}
	</div>
	<!--{:load_more_html:}-->
	<div class="comment-triangle"></div>
</script>

<script type="html" id="noCommentsTemplate">
	<!-- 简化的无评论模板 -->
	<span id="{:key:}_form_{:doid:}_0"></span>
	<div class="no-comments">暂无评论，快来抢沙发吧！</div>
	<div class="comment-triangle"></div>
</script>

<script type="html" id="toggleOpenTemplate">
	<!-- 评论展开按钮模板 -->
	<span class="toggle-icon">-</span> 收起评论
</script>

<script type="html" id="toggleCloseTemplate">
	<!-- 评论折叠按钮模板 -->
	<span class="toggle-icon">+</span> 查看更多{:hide_count:}条评论
</script>

<script type="html" id="loadMoreBtnTemplate">
	<!-- 加载更多按钮模板 -->
	<a href="javascript:;" onclick="loadMoreComments({:doid:}, '{:key:}', {:next_page:});">加载更多评论...</a>
</script>

<script type="text/javascript">
// 保存当前页码的全局变量，与电脑版保持一致
var current_comment_pages = {};

// 生成单个评论项HTML - 使用<script type="html">模板
function generateCommentItemHTML(comment, doid, key) {
	// 如果是展开/折叠按钮，使用toggle模板
	if (comment.is_toggle) {
		var template = document.getElementById('commentToggleTemplate').innerHTML;
		var data = {
			root_id: comment.root_id || 0,
			hide_count: comment.hide_count || 0
		};
		return template.replace(/{:root_id:}/g, data.root_id)
			.replace(/{:hide_count:}/g, data.hide_count);
	}
	
	// 普通评论，使用commentItem模板
	var template = document.getElementById('commentItemTemplate').innerHTML;
	var commentClass = 'comment-item';
	if (comment.layer > 0) commentClass += ' comment-item-child';
	if (comment.is_hidden) commentClass += ' comment-item-hidden';
	
	// 生成头像HTML（一级、二级评论都显示头像，二级评论头像更小）
	var avatarHtml = '';
	{
		var avatarSize = comment.layer == 0 ? 34 : 24;
		avatarHtml = '<div class="comment-avatar"><a href="home.php?mod=space&uid=' + comment.uid + '"><img src="' + comment.avatar + '" alt="' + comment.username + '" class="avatar-small" style="width:' + avatarSize + 'px;height:' + avatarSize + 'px;"></a></div>';
	}
	
	// 生成回复信息HTML
	var replyToUserHtml = '';
	if (comment.reply_to_user) {
		replyToUserHtml = '<span class="comment-reply-to">回复 <a href="home.php?mod=space&uid=' + comment.reply_uid + '">' + comment.reply_to_user + '</a></span>';
	}
	
	// 生成删除按钮HTML
	var deleteBtnHtml = '';
	if (comment.can_delete) {
		deleteBtnHtml = '<a href="home.php?mod=spacecp&ac=doing&op=delete&doid=' + comment.doid + '&docid=' + comment.id + '&handlekey=doinghk_' + comment.doid + '_' + comment.id + '" id="' + key + '_doing_delete_' + comment.doid + '_' + comment.id + '" class="comment-action comment-action-delete dialog">删除</a>';
	}
	
	// 替换模板变量
	var html = template;
	html = html.replace(/{:id:}/g, comment.id);
	html = html.replace(/{:uid:}/g, comment.uid);
	html = html.replace(/{:username:}/g, comment.username);
	html = html.replace(/{:avatar:}/g, comment.avatar);
	html = html.replace(/{:message:}/g, comment.message);
	html = html.replace(/{:dateline_formatted:}/g, comment.dateline_formatted);
	html = html.replace(/{:doid:}/g, comment.doid);
	html = html.replace(/{:docid:}/g, comment.id);
	html = html.replace(/{:key:}/g, key);
	html = html.replace(/{:root_id:}/g, comment.root_id || 0);
	html = html.replace(/{:layer:}/g, comment.layer);
	html = html.replace(/{:comment_class:}/g, commentClass);
	html = html.replace(/{:avatar_html:}/g, avatarHtml);
	html = html.replace(/{:reply_to_user:}/g, replyToUserHtml);
	html = html.replace(/{:delete_btn:}/g, deleteBtnHtml);
	
	// 移除所有没有内容的注释标签
	html = html.replace(/<!--\s*-->\s*/g, '');
	
	return html;
}

// 生成评论列表HTML - 使用<script type="html">模板
function generateCommentHTML(data, doid, key) {
	// 如果没有评论，使用noComments模板
	if (!data.list || data.list.length === 0) {
		var template = document.getElementById('noCommentsTemplate').innerHTML;
		return template.replace(/{:key:}/g, key)
			.replace(/{:doid:}/g, doid);
	}
	
	// 有评论，使用commentList模板
	var template = document.getElementById('commentListTemplate').innerHTML;
	
	// 生成所有评论项的HTML
	var commentItemsHTML = '';
	for (var i = 0; i < data.list.length; i++) {
		commentItemsHTML += generateCommentItemHTML(data.list[i], doid, key);
	}
	
	// 生成加载更多按钮HTML
	var loadMoreHtml = '';
	var hasMore = data.total_pages > 1 && data.page < data.total_pages;
	if (hasMore) {
		var nextPage = data.page + 1;
		loadMoreHtml = '<div class="comment-load-more" data-doid="' + doid + '" data-key="' + key + '" data-next-page="' + nextPage + '">';
		loadMoreHtml += '<a href="javascript:;" onclick="loadMoreComments(' + doid + ', \'' + key + '\', ' + nextPage + ');">加载更多评论...</a>';
		loadMoreHtml += '</div>';
	}
	
	// 替换模板变量
	var html = template;
	html = html.replace(/{:key:}/g, key);
	html = html.replace(/{:doid:}/g, doid);
	html = html.replace(/{:comment_items:}/g, commentItemsHTML);
	html = html.replace(/{:load_more_html:}/g, loadMoreHtml);
	
	// 移除所有没有内容的注释标签
	html = html.replace(/<!--\s*-->/g, '');
	
	return html;
}

	function toggle_child_comments(root_id) {
	var toggleComment = document.querySelector('.comment-toggle[data-root-id="' + root_id + '"]');
	var toggleBtn = toggleComment.querySelector('.toggle-comment-btn');
	var toggleIcon = toggleBtn.querySelector('.toggle-icon');
	var hiddenComments = document.querySelectorAll('.comment-item-hidden[data-root-id="' + root_id + '"]');
	
	if (toggleIcon.textContent === '+') {
		// 展开评论
		var openTemplate = document.getElementById('toggleOpenTemplate').innerHTML;
		toggleBtn.innerHTML = openTemplate;
		for (var i = 0; i < hiddenComments.length; i++) {
			hiddenComments[i].style.display = '';
			hiddenComments[i].classList.remove('comment-item-hidden');
		}
	} else {
		// 折叠评论
		var closeTemplate = document.getElementById('toggleCloseTemplate').innerHTML;
		var data = {
			hide_count: hiddenComments.length
		};
		var html = closeTemplate.replace(/{:hide_count:}/g, data.hide_count);
		toggleBtn.innerHTML = html;
		for (var i = 0; i < hiddenComments.length; i++) {
			hiddenComments[i].style.display = 'none';
			hiddenComments[i].classList.add('comment-item-hidden');
		}
	}
}

	function docomment_form(doid, docid, key) {
		var formId = key + '_form_' + doid + '_' + docid;
		var formContainer = document.getElementById(formId);
		
		if (formContainer && formContainer.innerHTML === '') {
			// 如果表单不存在，加载表单
			var url = 'home.php?mod=spacecp&ac=doing&op=docomment&handlekey=msg_' + doid + '&doid=' + doid + '&docid=' + docid + '&key=' + key + '&fragment=1';
			var xhr = new XMLHttpRequest();
			xhr.onreadystatechange = function() {
				if (xhr.readyState === 4 && xhr.status === 200) {
					formContainer.innerHTML = xhr.responseText;
				}
			};
			xhr.open('GET', url, true);
			xhr.send();
		} else if (formContainer) {
			// 如果表单已存在，切换显示/隐藏
			if (formContainer.style.display === 'none') {
				formContainer.style.display = '';
			} else {
				formContainer.style.display = 'none';
			}
		}
	}

	// 移动端专用的评论加载函数，请求JSON数据并解析插入 - 定义在全局作用域
	function docomment_get(doid, key, page, append, callback) {
		var showid = key + '_' + doid;
		var opid = key + '_do_a_op_' + doid;
		var commentContainerId = key + 'dl' + doid;
		
		// 构建请求URL，Discuz!会自动判断移动端并返回JSON数据
		var url = 'home.php?mod=spacecp&ac=doing&op=getcomment&handlekey=msg_' + doid + '&doid=' + doid + '&key=' + key;
		
		// 处理页码逻辑
		var current_page = page || current_comment_pages[doid] || 1;
		current_comment_pages[doid] = current_page;
		
		// 检查当前页面URL是否包含doid参数（单条动态详情页）或是否已经保存了页码
		var currentUrl = window.location.href;
		if (currentUrl.indexOf('doid=') > -1 || typeof current_comment_pages[doid] !== 'undefined') {
			// 单条动态详情页或已经保存过页码，添加page_c参数
			url += '&page_c=' + current_page;
		}
		
		// 异步请求评论列表（JSON格式）
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				try {
					// 解析JSON数据
					var response = JSON.parse(xhr.responseText);
					
					// 将结果插入到 {$key}dl{$doid} 容器中
					var commentContainer = document.getElementById(commentContainerId);
					if (commentContainer) {
						if (append && response.list.length > 0) {
							// 追加评论到现有列表
							var commentsHtml = '';
							for (var i = 0; i < response.list.length; i++) {
								commentsHtml += generateCommentItemHTML(response.list[i], doid, key);
							}
							
							// 找到评论列表容器
							var commentList = commentContainer.querySelector('.doing-card-comments');
							if (commentList) {
								// 找到加载更多按钮，插入到其前面
								var loadMoreBtn = commentContainer.querySelector('.comment-load-more');
								if (loadMoreBtn) {
									loadMoreBtn.insertAdjacentHTML('beforebegin', commentsHtml);
								} else {
									// 如果没有加载更多按钮，直接添加到列表末尾
									commentList.insertAdjacentHTML('beforeend', commentsHtml);
								}
								
								// 更新加载更多按钮
			if (loadMoreBtn) {
				if (response.page < response.total_pages) {
					var nextPage = response.page + 1;
					loadMoreBtn.setAttribute('data-next-page', nextPage);
					var template = document.getElementById('loadMoreBtnTemplate').innerHTML;
					var data = {
						doid: doid,
						key: key,
						next_page: nextPage
					};
					var html = template.replace(/{:doid:}/g, data.doid)
						.replace(/{:key:}/g, data.key)
						.replace(/{:next_page:}/g, data.next_page);
					loadMoreBtn.innerHTML = html;
				} else {
					// 没有更多评论，移除加载更多按钮
					loadMoreBtn.remove();
				}
			}
							}
						} else {
							// 生成评论列表HTML并替换现有内容
							var html = generateCommentHTML(response, doid, key);
							commentContainer.innerHTML = html;
							// 显示评论容器
							commentContainer.style.display = '';
						}
					}
					// 评论渲染完成后触发回调（如单条详情页自动拉起回复框）
					if (typeof callback === 'function') {
						try {
							callback(response);
						} catch (e) {
							console.error('docomment_get callback error:', e);
						}
					}
				} catch (error) {
					console.error('JSON parse error:', error);
				}
			}
		};
		xhr.open('GET', url, true);
		xhr.send();
	}

	// 加载更多评论函数
	function loadMoreComments(doid, key, page) {
		docomment_get(doid, key, page, true);
	}

	// 使用Discuz原生样式，几乎不使用自定义CSS
var style = document.createElement('style');
style.textContent = `		/* 评论列表容器 */
		.doing-card-comments {
			padding: 2px 2px 0;
		}

		/* 一级评论：微博式 头像 + 昵称 + 内容 + 底部操作栏 */
		.comment-item {
			display: flex;
			padding: 14px 0;
			border-bottom: 1px solid var(--dz-BOR-ed);
		}
		.comment-item:last-child {
			border-bottom: none;
		}
		.comment-avatar {
			flex-shrink: 0;
			margin-right: 10px;
		}
		.comment-avatar img {
			width: 34px;
			height: 34px;
			border-radius: 50%;
			display: block;
		}
		.comment-content {
			flex: 1;
			min-width: 0;
		}
		.comment-header {
			display: flex;
			align-items: baseline;
			gap: 6px;
			margin-bottom: 5px;
		}
		.comment-author {
			font-size: 14px;
			font-weight: 600;
			color: var(--dz-BG-color);
			text-decoration: none;
		}
		.comment-reply-to {
			font-size: 12px;
			color: var(--dz-FC-999);
		}
		.comment-reply-to a {
			color: var(--dz-BG-color);
		}
		.comment-body {
			font-size: 14px;
			line-height: 21px;
			color: var(--dz-FC-333);
			word-break: break-all;
			overflow-wrap: break-word;
		}
		.comment-footer {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-top: 8px;
		}
		.comment-time {
			font-size: 12px;
			color: var(--dz-FC-999);
		}
		.comment-actions {
			display: flex;
			gap: 16px;
			font-size: 12px;
		}
		.comment-action {
			color: var(--dz-FC-999);
			text-decoration: none;
		}
		.comment-action:active {
			color: var(--dz-BG-color);
		}

		/* 二级评论：灰底圆角卡片，紧凑排版，无头像 */
		.comment-item-child {
			display: flex;
			margin: 8px 0 0 44px;
			padding: 8px 10px;
			background: var(--dz-BG-5);
			border-radius: 8px;
			border-bottom: none;
		}
		.comment-item-child .comment-avatar {
			flex-shrink: 0;
			margin-right: 8px;
		}
		.comment-item-child .comment-header {
			margin-bottom: 2px;
		}
		.comment-item-child .comment-author {
			font-size: 13px;
		}
		.comment-item-child .comment-body {
			font-size: 13px;
			line-height: 19px;
		}
		.comment-item-child .comment-footer {
			margin-top: 4px;
		}

		/* 隐藏的评论 */
		.comment-item-hidden {
			display: none;
		}

		/* 展开/收起更多二级评论 */
		.comment-toggle {
			text-align: center;
			margin: 8px 0 0 44px;
			padding: 6px 0;
			font-size: 12px;
			color: var(--dz-FC-999);
		}
		.comment-toggle a {
			color: inherit;
			text-decoration: none;
		}
		.comment-toggle .toggle-icon {
			display: inline-block;
			margin-right: 4px;
			font-weight: 700;
		}

		/* 加载更多评论：整块灰色按钮 */
		.comment-load-more {
			margin-top: 12px;
		}
		.comment-load-more a {
			display: block;
			text-align: center;
			padding: 10px 0;
			font-size: 13px;
			color: var(--dz-FC-999);
			background: var(--dz-BG-5);
			border-radius: 8px;
			text-decoration: none;
		}

		/* 无评论提示 */
		.no-comments {
			text-align: center;
			color: var(--dz-FC-999);
			padding: 20px;
			font-size: 13px;
		}
	`;
document.head.appendChild(style);
	document.addEventListener('DOMContentLoaded', function() {
		// 点赞功能
		var recommendBtns = document.querySelectorAll('.doing_recommend_btn');
		for (let i = 0; i < recommendBtns.length; i++) {
			recommendBtns[i].addEventListener('click', function() {
				if (this.classList.contains('disabled')) {
					return;
				}

				var doid = this.getAttribute('data-doid');
				var btn = this;
				var countElem = this.querySelector('.recommend_count');
				var iconElem = this.querySelector('i');

				btn.classList.add('disabled');
				fetch('home.php?mod=spacecp&ac=doing&op=recommend&doid=' + doid)
					.then(function(response) {
						return response.json();
					})
					.then(function(data) {
						btn.classList.remove('disabled');

						if (data && data.message === 'doing_recommend_success') {
							btn.setAttribute('data-status', data.status);
							countElem.innerHTML = data.count;

							if (parseInt(data.status) === 1) {
								iconElem.className = 'fico-thumbup fc-i';
							} else {
								iconElem.className = 'fico-thumbup fc-s';
							}
						} else {
							console.error('error:', data);
						}
					})
					.catch(function(error) {
								btn.classList.remove('disabled');
								console.error('error:', error);
						});
			});
		}

		// 单条记录页面异步加载评论列表
		// 使用PHP模板变量判断是否为单条记录页面
		<!--{if count($dolist) == 1}-->
		// 单条记录页面，执行异步加载评论列表
		var doid = '<!--{$doid}-->';
		var key = '<!--{$key}-->';

		// 调用与电脑版一致的docomment_get函数
		// 评论列表渲染完成后自动拉起顶部回复框，对齐电脑端单条详情页交互
		docomment_get(doid, key, 1, false, function() {
			docomment_form(doid, 0, key);
		});

		// 自动加载更多评论功能
		var autoLoadEnabled = true;
		var isLoading = false;

		// 监听滚动事件，实现滑动到底部自动加载
		window.addEventListener('scroll', function() {
			if (!autoLoadEnabled || isLoading) {
				return;
			}

			// 计算滚动位置
			var scrollTop = window.scrollY || document.documentElement.scrollTop;
			var scrollHeight = document.body.scrollHeight || document.documentElement.scrollHeight;
			var clientHeight = window.innerHeight || document.documentElement.clientHeight;
			var distanceToBottom = scrollHeight - (scrollTop + clientHeight);

			// 检查是否滚动到底部（距离底部100px以内）
			if (distanceToBottom <= 100) {
				// 获取所有加载更多按钮
				var loadMoreBtns = document.querySelectorAll('.comment-load-more');
				if (loadMoreBtns.length > 0) {
					isLoading = true;
					// 触发最后一个加载更多按钮的点击事件（处理可能存在的多个动态）
					var lastLoadMoreBtn = loadMoreBtns[loadMoreBtns.length - 1];
					// 直接调用loadMoreComments函数，而不是依赖按钮点击
					var doid = lastLoadMoreBtn.getAttribute('data-doid');
					var key = lastLoadMoreBtn.getAttribute('data-key');
					var nextPage = lastLoadMoreBtn.getAttribute('data-next-page');
					if (doid && key && nextPage) {
						loadMoreComments(doid, key, nextPage);
					}
					// 设置加载状态，防止重复触发
					setTimeout(function() {
						isLoading = false;
					}, 1500); // 1.5秒后恢复加载状态，给足够的时间完成请求
				} else {
					// 没有更多评论，禁用自动加载
					autoLoadEnabled = false;
				}
			}
		});
		<!--{/if}-->
	});
</script>
<!--{template common/footer}-->