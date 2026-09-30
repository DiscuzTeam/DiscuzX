<?php exit('Access Denied');?>
<!--{if $type}-->
<dt>
	<span class="y xg1">{lang share_count}&nbsp;&nbsp;</span>
	<span class="z">{lang share_description}:</span>
</dt>
<!--{/if}-->
<!--{eval $doing_imgmaxnum = max(1, intval($_G['setting']['doingimgmaxnum'] ? $_G['setting']['doingimgmaxnum'] : 9));}-->
<!--{eval $doing_imgmaxsize = max(1, intval($_G['setting']['doingimgmaxsize'] ? $_G['setting']['doingimgmaxsize'] : 2048));}-->
<!--{eval $doing_videoallow = intval($_G['setting']['doingvideoallow'] ? $_G['setting']['doingvideoallow'] : 1);}-->
<!--{eval $doing_videomaxsize = max(1, intval($_G['setting']['doingvideomaxsize'] ? $_G['setting']['doingvideomaxsize'] : 50));}-->
<!--{eval $doing_videoext = !empty($_G['setting']['doingvideoext']) ? strtolower($_G['setting']['doingvideoext']) : 'mp4,webm,mov';}-->
<!--{eval $doing_videoaccept = '.'.implode(',.', array_filter(array_map('trim', explode(',', $doing_videoext)))).',video/*';}-->
<div id="moodfm" class="moodfm">
	<form method="post" autocomplete="off" id="mood_addform" action="home.php?mod=spacecp&ac=doing&view=$_GET['view']" enctype="multipart/form-data">
		<div class="moodfm_post">
			<div class="moodfm_text">
				<textarea name="message" id="message" class="xg1" placeholder="{$defaultstr}" rows="3"></textarea>
			</div>
			<!--{if !$type}-->
			<div class="specialpost s_clear">
				<li class="doing-toolrow">
					<a href="javascript:;" class="doing-tool-item doing-tool-media" id="moodfm_pic" title="{lang upload_new_pic}"><i class="fico-image"></i></a>
					<!--{if $doing_videoallow}-->
					<a href="javascript:;" class="doing-tool-item doing-tool-media" id="moodfm_video" title="{lang doing_upload_video}"><i class="fico-camera"></i></a>
					<!--{/if}-->
					<a href="<!--{if $_G['setting']['defaultforumid']}-->forum.php?mod=post&action=newthread&fid={$_G['setting']['defaultforumid']}&adddynamic_doing=1<!--{else}-->forum.php?mod=misc&action=nav<!--{/if}-->" class="doing-tool-item" id="moodfm_thread" title="{lang follow_new_thread}"><i class="fico-thread"></i></a>
					<!--{if $_G['setting']['pollforumid']}-->
					<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['pollforumid']}&special=1&adddynamic_doing=1" class="doing-tool-item" id="moodfm_poll" title="{lang create_new_poll}"><i class="fico-assessment"></i></a>
					<!--{/if}-->
					<!--{if $_G['setting']['tradeforumid']}-->
					<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['tradeforumid']}&special=2&adddynamic_doing=1" class="doing-tool-item" id="moodfm_trade" title="{lang create_new_trade}"><i class="fico-cart"></i></a>
					<!--{/if}-->
					<!--{if $_G['setting']['rewardforumid']}-->
					<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['rewardforumid']}&special=3&adddynamic_doing=1" class="doing-tool-item" id="moodfm_reward" title="{lang publish_new_reward}"><i class="fico-help"></i></a>
					<!--{/if}-->
					<!--{if $_G['setting']['activityforumid']}-->
					<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['activityforumid']}&special=4&adddynamic_doing=1" class="doing-tool-item" id="moodfm_activity" title="{lang create_new_activity}"><i class="fico-interactive"></i></a>
					<!--{/if}-->
					<!--{if $_G['setting']['debateforumid']}-->
					<a href="forum.php?mod=post&action=newthread&fid={$_G['setting']['debateforumid']}&special=5&adddynamic_doing=1" class="doing-tool-item" id="moodfm_debate" title="{lang create_new_debate}"><i class="fico-vs"></i></a>
					<!--{/if}-->
				</li>
				<!-- 已选媒体预览区：选中图片/视频后显示 -->
				<li class="upload-main" id="MultiPicList" style="display:none;">
					<div class="image-main">
						<div id="mp_counter"></div>
						<div id="multipic_img"></div>
					</div>
				</li>
				<!-- 隐藏的文件选择框：由工具行中的图片/视频入口触发 -->
				<li id="mp_hidden_inputs" style="display:none;">
					<input name="photos[]" type="file" class="file" id="multipic_sel" multiple="multiple"
					       accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,image/jpeg,image/png">
					<!--{if $doing_videoallow}-->
					<input name="video" type="file" class="file" id="video_sel" accept="$doing_videoaccept">
					<!--{/if}-->
				</li>
			</div>
			<!--{else}-->
			<ul class="post_box cl">
				<!--{if $commentcable[$type]}-->
				<label><li class="flex-box b0"><div class="flex tit"><!--{if $type == 'thread'}-->{lang post_add_inonetime}<!--{else}-->{lang comment_add_inonetime}<!--{/if}</div><div class="flex"></div><div class="flex y"><input type="checkbox" class="pc" name="iscomment" value="1"/></div></li></label>
				<!--{/if}-->
			</ul>
			<!--{/if}-->
			<div class="moodfm_f">
				<div class="moodfm_btn">
					<button type="submit" name="add" id="add" class="pgsbtn button">{lang publish}</button>
				</div>
			</div>
		</div>
		<!--{if $type}--><input type="hidden" name="type" value="$type" /><!--{/if}-->
		<!--{if $id}--><input type="hidden" name="id" value="$id" /><!--{/if}-->
		<!--{if !$type}--><input type="hidden" name="referer" value="home.php?mod=space&do=doing" /><!--{/if}-->
		<input type="hidden" name="addsubmit" value="true" />
		<input type="hidden" name="videoposter" id="videoposter" value="" />
		<input type="hidden" name="refer" value="$theurl" />
		<input type="hidden" name="topicid" value="$topicid" />
		<input type="hidden" name="formhash" value="{FORMHASH}" />
	</form>
</div>

<script type="text/javascript" reload="1">
	<!--{if !$type}-->
	var mpimgmax = {$doing_imgmaxnum};
	var mpimgmaxsize = {$doing_imgmaxsize} * 1024;
	var mpvideomaxsize = {$doing_videomaxsize} * 1048576;
	var mpvideoexts = '{$doing_videoext}';
	var MultiPicUploaded = 0;

	function mpShowError(msg) {
		if (typeof showError === 'function') {
			showError(msg);
		} else {
			alert(msg);
		}
	}

	listenup();
	function listenup() {
		if (typeof FileReader === 'undefined') {
		} else {
			// 工具行入口：点击图片/视频按钮触发对应的隐藏文件选择框（对齐电脑端交互）
			var moodPicBtn = document.getElementById('moodfm_pic');
			var fileInput = document.getElementById('multipic_sel');
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
						mpShowError(extmsg.replace('@ext@', mpvideoexts));
						resetVideoInput();
						return;
					}
					if (file.size > mpvideomaxsize) {
						var sizemsg = '{lang doing_upload_video_size_exceed}';
						mpShowError(sizemsg.replace('@size@', {$doing_videomaxsize}));
						resetVideoInput();
						return;
					}
					// 视频限一个：重复选择时替换旧预览
					const oldPreview = document.getElementById('video_preview_item');
					if (oldPreview) {
						oldPreview.remove();
					}
					const multiPicList = document.getElementById('MultiPicList');
					if (multiPicList) {
						multiPicList.style.display = '';
					}
					const preview = document.createElement('div');
					preview.className = 'previewvideo z';
					preview.id = 'video_preview_item';
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
					const removeDiv = document.createElement('i');
					removeDiv.className = 'fico-error';
					removeDiv.onclick = function(e) {
						e.stopPropagation();
						resetVideoInput();
					};
					preview.onclick = function() {
						if (vthumb.paused) {
							vthumb.muted = false;
							vthumb.setAttribute('controls', 'controls');
							vthumb.play();
							preview.classList.add('playing');
						} else {
							vthumb.pause();
						}
					};
					preview.append(vthumb);
					preview.append(badge);
					preview.append(removeDiv);
					// 视频预览与图片缩略图同尺寸，且始终排在图片列表最前面
					const imgContainer = document.getElementById('multipic_img');
					imgContainer.insertBefore(preview, imgContainer.firstChild);
				});
			}
			document.getElementById('multipic_sel').addEventListener('change', function(event) {
				const imgContainer = document.getElementById('multipic_img');
				const multiPicList = document.getElementById('MultiPicList');
				const input = event.target;
				const files = input.files;

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
						mpShowError(nummsg.replace('@num@', mpimgmax).replace('@cur@', MultiPicUploaded + accepted.length));
						rejected = true;
						break;
					}
					if (file.size > mpimgmaxsize) {
						var picsizemsg = '{lang doing_upload_pic_size_exceed}';
						mpShowError(picsizemsg.replace('@size@', {$doing_imgmaxsize}));
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
				if (accepted.length > 0 && multiPicList) {
					multiPicList.style.display = '';
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
							const removeDiv = document.createElement('i');
							removeDiv.className = 'fico-error';
							removeDiv.onclick = function() {
								MultiPicDel(this);
							};
							imgWrapper.append(img);
							imgWrapper.append(removeDiv);
							img.style.width = '100%';
							img.style.height = '100%';
							img.style.objectFit = 'cover';
							imgContainer.prepend(imgWrapper);
							MultiPicUploaded++;
							updateMpCounter();
						};
						reader.onerror = function() {
						};
						reader.readAsDataURL(currentFile);
					})(accepted[k], k);
				}
				// 选择完成后立刻替换一个新的空 file input（隐藏容器内），以便继续选择
				const oldInput = document.getElementById('multipic_sel');
				if (oldInput) {
					const newInput = oldInput.cloneNode(true);
					newInput.value = '';
					oldInput.parentNode.replaceChild(newInput, oldInput);
				}
				listenup();
			});
		}
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

	function MultiPicDel(obj) {
		var oldAid = obj.getAttribute('dataid');
		console.log(oldAid);
		MultiPicUploaded--;
		obj.parentNode.remove();
		updateMpCounter();

		if (MultiPicUploaded <= 0) {
			MultiPicUploaded = 0;
			// 图片删光且无视频预览时，隐藏整个预览区
			const videoItem = document.getElementById('video_preview_item');
			const multiPicList = document.getElementById('MultiPicList');
			if (!videoItem && multiPicList) {
				multiPicList.style.display = 'none';
			}
		}
	}

	function preview_pic(obj) {
		var hlthumb = obj.parentNode.childNodes[0];
		zoom(hlthumb, hlthumb.src);
	}
<!--{/if}-->
</script>
