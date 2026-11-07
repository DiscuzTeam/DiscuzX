/*!
 * 云诺 · 单图区块前台脚本（imageview.js）
 * ============================================================================
 * 职责：接管帖子正文里「图片（单图）区块」的「点击查看大图」。
 *
 * 为什么需要它：
 *   Discuz 的 initZoom()（static/js/common.js:2496）只在 DOMContentLoaded
 *   时遍历一次 .zoom / ._zoom 并绑定 click。评分成功后 rate.php 用 ajaxget
 *   把整个楼层 #post_pid 的 DOM 整块替换（ajaxinnerhtml），随后的
 *   _ajaxupdateevents() 只重绑 <a ajaxtarget>，**不碰 img**。于是替换后新图
 *   没有 click 绑定，点图没反应 —— 普通帖子附件图 / 图集 / 单图同样如此。
 *
 * 修复方式：
 *   在 document 的捕获阶段用事件委托监听点击，命中前台正文里的
 *   img.image-tool__image-picture 时调用 Discuz 原生 zoom() 弹层。
 *   DOM 无论怎么换（评分/点赞/翻页）都照常生效。
 *
 * 与 System 区块的关系：
 *   系统区块没有 getStaticResource()，本脚本不是由 viewthread_postbottom 挂钩
 *   注入；它由 editorblock_image.php 的 getParser() 通过 [jsfile imageview.js]
 *   就地输出 —— 帖子里只要含单图区块就会带上这个脚本，评分替换回来时也会
 *   带上（因为楼层 HTML 是服务端重渲染的，ajaxget 回来执行 reload=1 的脚本时
 *   appendscript 会去重用 evalscripts，重复插入不会重复绑定）。
 *
 * 与 Discuz 原生 zoom 的关系：
 *   本脚本不改任何样式与布局，前台依然是纯 CSS 排版；
 *   只在 initZoom 漏绑时补一次 binding，且在捕获阶段 stopImmediatePropagation
 *   拦截，避免双重触发同一个 zoom()。
 * ============================================================================
 */
(function (global) {
	'use strict';

	var VERSION = '1.0.0';

	/* 已绑过就不再重复绑（防 [jsfile] 同一页面多次输出） */
	if (global.__CDX_IMAGE_VIEW_BOUND__) {
		return;
	}
	global.__CDX_IMAGE_VIEW_BOUND__ = true;

	/* 是否前台正文里的单图图片（编辑器内不接管） */
	function isFrontImage(target) {
		if (!target || !target.tagName || target.tagName.toUpperCase() !== 'IMG') {
			return false;
		}
		if (!target.classList) {
			return false;
		}
		/* 单图区块的图片类名（与 editorblock_image.php 的 parser 一致） */
		if (!target.classList.contains('image-tool__image-picture')) {
			return false;
		}
		if (target.closest && target.closest('#editorjs')) {
			return false;
		}
		return true;
	}

	/* 调 Discuz 原生 zoom() 弹层（保持整站一致） */
	function openViewer(img) {
		var src = img.getAttribute('data-full') || img.currentSrc || img.src;
		if (!src) {
			return;
		}
		if (typeof global.zoom === 'function') {
			/* zoom(obj, zimg, nocover, pn, showexif) */
			global.zoom(img, src, 0, 0, 0);
			return;
		}
		try { window.open(src, '_blank'); } catch (e) {}
	}

	/* 捕获阶段接管点击 */
	document.addEventListener('click', function (e) {
		var target = e.target;
		if (!isFrontImage(target)) {
			return;
		}
		/* 外层包真实链接就尊重链接 */
		var link = target.closest ? target.closest('a') : null;
		if (link && link.getAttribute('href') && link.getAttribute('href') !== 'javascript:;') {
			return;
		}
		e.preventDefault();
		e.stopPropagation();
		if (e.stopImmediatePropagation) {
			e.stopImmediatePropagation();
		}
		openViewer(target);
	}, true);

	global.CDXImageView = { version: VERSION };

})(typeof window !== 'undefined' ? window : this);
