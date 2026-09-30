<?php exit('Access Denied'); ?>
<!--{if $_G[inajax] && $type}-->
<h3 class="flb">
	<em id="return_$_GET[handlekey]">{lang share}</em>
	<!--{if $_G[inajax]}--><span><a href="javascript:;" onclick="hideWindow('$_GET[handlekey]');" class="flbc" title="{lang close}">{lang close}</a></span><!--{/if}-->
</h3>
<!--{/if}-->

<!--{eval $doing_imgmaxnum = max(1, intval($_G['setting']['doingimgmaxnum'] ? $_G['setting']['doingimgmaxnum'] : 9));}-->
<!--{eval $doing_imgmaxsize = max(1, intval($_G['setting']['doingimgmaxsize'] ? $_G['setting']['doingimgmaxsize'] : 2048));}-->
<!--{eval $doing_videoallow = intval($_G['setting']['doingvideoallow'] ? $_G['setting']['doingvideoallow'] : 1);}-->
<!--{eval $doing_videomaxsize = max(1, intval($_G['setting']['doingvideomaxsize'] ? $_G['setting']['doingvideomaxsize'] : 50));}-->
<!--{eval $doing_videoext = !empty($_G['setting']['doingvideoext']) ? strtolower($_G['setting']['doingvideoext']) : 'mp4,webm,mov';}-->
<!--{eval $doing_videoaccept = '.'.implode(',.', array_filter(array_map('trim', explode(',', $doing_videoext)))).',video/*';}-->

<script type="text/javascript">
	var msgstr = '$defaultstr';

	function handlePrompt(type) {
		var msgObj = $('message');
		if (type) {
			if (msgObj.value == msgstr) {
				<!--{if $tag['tagname']}-->
				msgObj.value = '#{$tag['tagname']}# ';
				<!--{else}-->
				msgObj.value = '';
				<!--{/if}-->
				msgObj.className = 'xg2';
			}
			if ($('message_menu')) {
				if ($('message_menu').style.display == 'block') {
					//showFace('message', 'message', msgstr);
				}
			}
			if (BROWSER.firefox || BROWSER.chrome) {
				//showFace('message', 'message', msgstr);
			}
		} else {
			if (msgObj.value == '') {
				msgObj.value = msgstr;
				msgObj.className = 'xg1';
			}
		}
	}
</script>

<div id="moodfm">
	<form method="post" autocomplete="off" id="mood_addform" action="home.php?mod=spacecp&ac=doing&view=$_GET['view']" onsubmit="if($('message').value == msgstr){showError('{lang content_isnull}');return false;} return check_submit();" enctype="multipart/form-data">
		<div class="moodfm_container">
			<!--{if $type}-->
			<p class="mbn cl">
				<span class="y xg1">{lang share_count}&nbsp;&nbsp;</span>
				{lang share_description}:
			</p>
			<!--{/if}-->
			<div class="moodfm_row">
				<div id="mood_statusinput" class="moodfm_input">
					<textarea name="message" id="message" class="xg1" onfocus="handlePrompt(1);" onblur="handlePrompt(0);" onkeyup="dstrLenCalc(this, 'maxlimit')" onkeydown="ctrlEnter(event, 'add');" rows="4">$defaultstr</textarea>
					<div class="moodfm_f">
						<div id="return_doing" class="xi1 xw1"></div>
						<span class="y">{lang doing_maxlimit_char}</span>
					</div>
				</div>
			</div>
			<!--{if !$type}-->
			<div class="image-main" id="MultiPicList" style="display:none;">
				<div id="mp_counter"></div>
				<div id="multipic_img"></div>
				<div id="multipic_btn" class="image-list image-upload image-upload-mp">
					<div class="file_pic"></div>
					<input name="photos[]" type="file" class="file" id="multipic_sel" multiple="multiple"
						accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,image/jpeg,image/png">
				</div>
			</div>
			<!--{if $doing_videoallow}-->
			<input name="video" type="file" id="video_sel" style="display:none;" accept="$doing_videoaccept">
			<!--{/if}-->
			<!--{else}-->
			<ul id="share_preview" class="el mtm cl 1">
				<!--{eval $value = $arr;}-->
				<!--{template home/space_share_li}-->
			</ul>
			<!--{/if}-->
			<div class="moodfm_div">
				<div class="specialpost s_clear">
					<!--{if !$type}-->
						<a href="javascript:;" id="moodfm_emoji" onclick="showFace('moodfm_emoji', 'message', msgstr); return false;" title="{lang insert_emoticons}"><i class="fico-emojifill fic8 fc-s fnmr vm" ></i></a>
						<a href="javascript:;" id="moodfm_pic" title="{lang upload_new_pic}"><i class="fico-image fic8 fc-s fnmr vm" ></i></a>
						<!--{if $doing_videoallow}-->
						<a href="javascript:;" id="moodfm_video" title="{lang doing_upload_video}"><i class="fico-camera fic8 fc-s fnmr vm" ></i></a>
						<!--{/if}-->
						<a href="javascript:;" id="moodfm_thread" title="{lang follow_new_thread}" onclick="<!--{if $_G['setting']['defaultforumid']}-->showWindow('newthread', 'forum.php?mod=post&action=newthread&fid={$_G['setting']['defaultforumid']}&adddynamic_doing=1');<!--{else}-->showWindow('nav', 'forum.php?mod=misc&action=nav', 'get', 0);<!--{/if}-->"><i class="fico-thread fic8 fc-s fnmr vm" ></i></a>
						<!--{if $_G['setting']['pollforumid']}-->
						<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['pollforumid']}&special=1&adddynamic_doing=1" id="moodfm_poll" title="{lang create_new_poll}"><i class="fico-assessment fic8 fc-s fnmr vm" ></i></a>
						<!--{/if}-->
						<!--{if $_G['setting']['tradeforumid']}-->
						<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['tradeforumid']}&special=2&adddynamic_doing=1" id="moodfm_trade" title="{lang create_new_trade}"><i class="fico-cart fic8 fc-s fnmr vm" ></i></a>
						<!--{/if}-->
						<!--{if $_G['setting']['rewardforumid']}-->
						<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['rewardforumid']}&special=3&adddynamic_doing=1" id="moodfm_reward" title="{lang publish_new_reward}"><i class="fico-help fic8 fc-s fnmr vm" ></i></a>
						<!--{/if}-->
						<!--{if $_G['setting']['activityforumid']}-->
						<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['activityforumid']}&special=4&adddynamic_doing=1" id="moodfm_activity" title="{lang create_new_activity}"><i class="fico-interactive fic8 fc-s fnmr vm" ></i></a>
						<!--{/if}-->
						<!--{if $_G['setting']['debateforumid']}-->
						<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['debateforumid']}&special=5&adddynamic_doing=1" id="moodfm_debate" title="{lang create_new_debate}"><i class="fico-vs fic8 fc-s fnmr vm" ></i></a>
						<!--{/if}-->
					<!--{/if}-->
					{hook/space_doing_toolbar}
				</div>
				<div class="moodfm_btn">
					<!--{if $commentcable[$type]}-->
					<label><input type="checkbox" class="pc z" name="iscomment" value="1"/><!--{if $type == 'thread'}-->{lang post_add_inonetime}<!--{else}-->{lang comment_add_inonetime}<!--{/if}--></label>
					<!--{/if}-->
					<button type="submit" name="add" id="add" class="pgsbtn" onsubmit="check_submit();"><strong>{lang publish}</strong></button>
				</div>
			</div>
		</div>
		<!--{if $type}--><input type="hidden" name="type" value="$type" /><!--{/if}-->
		<!--{if $id}--><input type="hidden" name="id" value="$id" /><!--{/if}-->
		<input type="hidden" name="addsubmit" value="true" />
		<input type="hidden" name="videoposter" id="videoposter" value="" />
		<input type="hidden" name="refer" value="$theurl" />
		<input type="hidden" name="topicid" value="$topicid" />
		<input type="hidden" name="formhash" value="{FORMHASH}" />
	</form>
</div>

<script type="text/javascript" reload="1">
	getID('maxlimit').innerHTML = 200;

	var mpimgmax = {$doing_imgmaxnum};
	var mpimgmax_low = mpimgmax - 1;
	var mpimgmaxsize = {$doing_imgmaxsize} * 1024;
	var mpvideomaxsize = {$doing_videomaxsize} * 1048576;
	var mpvideoexts = '{$doing_videoext}';
	var MultiPicUploaded = 0;

	listenup();
	function listenup() {
		if (typeof FileReader === 'undefined') {
		} else {
			var moodPicBtn = document.getElementById('moodfm_pic');
			var fileInput = document.getElementById('multipic_sel');
			var multiPicList = document.getElementById('MultiPicList');
			if (moodPicBtn && fileInput) {
				moodPicBtn.addEventListener('click', function(e) {
					e.preventDefault();
					fileInput.click();
				});
			}
			var moodVideoBtn = document.getElementById('moodfm_video');
			var videoInput = document.getElementById('video_sel');
			if (moodVideoBtn && videoInput) {
				moodVideoBtn.addEventListener('click', function(e) {
					e.preventDefault();
					videoInput.click();
				});
				videoInput.addEventListener('change', function(event) {
					const file = event.target.files && event.target.files[0];
					if (!file) {
						return;
					}
					const ext = (file.name.split('.').pop() || '').toLowerCase();
					if ((',' + mpvideoexts + ',').indexOf(',' + ext + ',') === -1) {
						var extmsg = '{lang doing_upload_video_ext_exceed}';
						showError(extmsg.replace('@ext@', mpvideoexts));
						resetVideoInput();
						return;
					}
					if (file.size > mpvideomaxsize) {
						var sizemsg = '{lang doing_upload_video_size_exceed}';
						showError(sizemsg.replace('@size@', {$doing_videomaxsize}));
						resetVideoInput();
						return;
					}
					// 视频限一个：重复选择时替换旧预览；与图片缩略图同尺寸，始终排在图片列表最前面
					const oldPreview = document.getElementById('video_preview_item');
					if (oldPreview) {
						oldPreview.remove();
					}
					const multiPicList = document.getElementById('MultiPicList');
					if (multiPicList && multiPicList.style.display === 'none') {
						multiPicList.style.display = '';
					}
					const wrapper = document.createElement('div');
					wrapper.className = 'previewvideo z';
					wrapper.id = 'video_preview_item';
					const vthumb = document.createElement('video');
					vthumb.muted = true;
					vthumb.playsInline = true;
					vthumb.preload = 'metadata';
					vthumb.src = URL.createObjectURL(file);
					vthumb.style.width = '100%';
					vthumb.style.height = '100%';
					vthumb.style.objectFit = 'cover';
					// 截取视频首帧作为封面，提交时随表单传给服务端保存为「附件名.thumb.jpg」
					vthumb.addEventListener('loadedmetadata', function() {
						try {
							vthumb.currentTime = Math.min(1, (vthumb.duration || 2) * 0.1);
						} catch (e) {}
					});
					vthumb.addEventListener('seeked', function() {
						try {
							const cv = document.createElement('canvas');
							const vw = vthumb.videoWidth || 1280;
							const vh = vthumb.videoHeight || 720;
							const scale = Math.min(1, 720 / vw);
							cv.width = Math.round(vw * scale);
							cv.height = Math.round(vh * scale);
							cv.getContext('2d').drawImage(vthumb, 0, 0, cv.width, cv.height);
							const posterInput = document.getElementById('videoposter');
							if (posterInput) {
								posterInput.value = cv.toDataURL('image/jpeg', 0.72);
							}
						} catch (e) {}
					}, {once: true});
					const badge = document.createElement('div');
					badge.className = 'video_play_badge';
					const removeDiv = document.createElement('div');
					removeDiv.className = 'flbc';
					removeDiv.onclick = function(e) {
						e.stopPropagation();
						resetVideoInput();
					};
					wrapper.onclick = function() {
						if (vthumb.paused) {
							vthumb.muted = false;
							vthumb.setAttribute('controls', 'controls');
							vthumb.play();
							wrapper.classList.add('playing');
						} else {
							vthumb.pause();
						}
					};
					wrapper.append(vthumb);
					wrapper.append(badge);
					wrapper.append(removeDiv);
					const imgContainer = document.getElementById('multipic_img');
					imgContainer.insertBefore(wrapper, imgContainer.firstChild);
				});
			}
			document.getElementById('multipic_sel').addEventListener('change', function(event) {
				const imgContainer = document.getElementById('multipic_img');
				const input = event.target;
				const files = input.files;
				if (files.length > 0 && multiPicList.style.display === 'none') {
					multiPicList.style.display = '';
				}
				// 先整体校验本次选择的文件：数量是否超限、单张是否超尺寸，未通过的立即提示并告知已选数量
				const accepted = [];
				let rejected = false;
				for (let i = 0; i < files.length; i++) {
					const file = files[i];
					if (!file) {
						continue;
					}
					if (MultiPicUploaded + accepted.length >= mpimgmax) {
						var nummsg = '{lang doing_upload_pic_num_exceed}';
						showError(nummsg.replace('@num@', mpimgmax).replace('@cur@', MultiPicUploaded + accepted.length));
						rejected = true;
						break;
					}
					if (file.size > mpimgmaxsize) {
						var picsizemsg = '{lang doing_upload_pic_size_exceed}';
						showError(picsizemsg.replace('@size@', {$doing_imgmaxsize}));
						rejected = true;
						continue;
					}
					accepted.push(file);
				}
				if (rejected && typeof DataTransfer !== 'undefined') {
					// 把未通过校验的文件从本次选择中剔除，避免随表单提交后由服务端拦截
					const dt = new DataTransfer();
					for (let j = 0; j < accepted.length; j++) {
						dt.items.add(accepted[j]);
					}
					input.files = dt.files;
				}
				for (let k = 0; k < accepted.length; k++) {
					(function(currentFile, index) {
						const reader = new FileReader();
						reader.onload = function(e) {
							const img = new Image();
							img.src = e.target.result;
							const imgWrapper = document.createElement('div');
							imgWrapper.className = 'previewbigpic z';
							imgWrapper.setAttribute('data-file-index', index);
							const removeDiv = document.createElement('div');
							removeDiv.className = 'flbc';
							removeDiv.onclick = function() {
								MultiPicDel(this);
							};
							imgWrapper.append(img);
							imgWrapper.append(removeDiv);
							img.style.width = '100%';
							img.style.objectFit = 'cover';
							imgContainer.append(imgWrapper);
							MultiPicUploaded++;
							updateMpCounter();
						};
						reader.onerror = function() {
						};
						reader.readAsDataURL(currentFile);
					})(accepted[k], k);
				}
				document.getElementById("multipic_sel").style.display = "none";
				document.getElementById("multipic_sel").removeAttribute("id");
				const newbtn = document.createElement('input');
				newbtn.type = 'file';
				newbtn.name = 'photos[]';
				newbtn.id = 'multipic_sel';
				newbtn.className = "file";
				newbtn.multiple = "multiple";
				newbtn.accept = ".jpg,.jpeg,.png,.gif,.webp,.bmp,image/jpeg,image/png";
				document.getElementById('multipic_btn').append(newbtn);
				listenup();
			});
		}
	}

	function resetVideoInput() {
		const item = document.getElementById('video_preview_item');
		if (item) {
			item.remove();
		}
		const posterInput = document.getElementById('videoposter');
		if (posterInput) {
			posterInput.value = '';
		}
		// 视频移除且没有图片时，隐藏整个预览区
		if (MultiPicUploaded <= 0) {
			const multiPicList = document.getElementById('MultiPicList');
			if (multiPicList) {
				multiPicList.style.display = 'none';
			}
		}
		const oldInput = document.getElementById('video_sel');
		if (oldInput) {
			const newInput = oldInput.cloneNode(true);
			newInput.value = '';
			oldInput.parentNode.replaceChild(newInput, oldInput);
			listenup();
		}
	}

	function check_submit() {
		if (MultiPicUploaded > mpimgmax) {
			var submsg = '{lang doing_upload_pic_num_exceed}';
			showError(submsg.replace('@num@', mpimgmax).replace('@cur@', MultiPicUploaded));
			return false;
		}
		return true;
	}

	function updateMpCounter() {
		const counter = document.getElementById('mp_counter');
		if (counter) {
			var countmsg = '{lang doing_upload_pic_count}';
			counter.innerHTML = countmsg
				.replace('@cur@', '<span class="mp_count_num">' + MultiPicUploaded + '</span>')
				.replace('@num@', mpimgmax);
			if (MultiPicUploaded >= mpimgmax) {
				counter.classList.add('mp_count_full');
			} else {
				counter.classList.remove('mp_count_full');
			}
		}
	}

	if (MultiPicUploaded >= mpimgmax_low) {
		document.querySelector('.image-upload-mp').style.display = 'none';
	}

	function MultiPicDel(obj) {
		var oldAid = obj.getAttribute('dataid');
		console.log(oldAid);
		MultiPicUploaded--;
		obj.parentNode.remove();
		updateMpCounter();

		if (MultiPicUploaded < mpimgmax) {
			document.querySelector('.image-upload-mp').style.display = '';
		}
		if (MultiPicUploaded <= 0) {
			MultiPicUploaded = 0;
			// 图片删光且无视频预览时，隐藏整个预览区
			if (!document.getElementById('video_preview_item')) {
				document.getElementById('MultiPicList').style.display = 'none';
			}
		}
	}

	function preview_pic(obj) {
		var hlthumb = obj.parentNode.childNodes[0];
		zoom(hlthumb, hlthumb.src);
	}
</script>