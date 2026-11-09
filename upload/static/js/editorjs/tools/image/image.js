/**
 * Image Tool for the Editor.js —— 明文纯 JS 版（System 内置区块）
 * ============================================================================
 * 文件：static/js/editorjs/tools/image/image.js
 * 上传：直接使用原生 XHR 整文件上传，不使用 window.editorSendUpload 分片。
 *
 * 数据结构（与 editorblock_image.php 的 getParameter/getConfig 保持一致）：
 *   { caption, withBorder, withBackground, stretched,
 *     file: { aid, remote, directory, url, thumb } }
 *
 * 与「图集（images）」的关系：
 *   本区块是 System 内置区块，没有 getStaticResource()，前台脚本不是由
 *   viewthread_postbottom 挂钩注入，而是[在 getParser() 里用 [jsfile xxx.js]
 *   就地输出]。评分后放大不了的问题，跟图集一致，同样由 imageview.js 用
 *   事件委托接管点击解决。
 * ============================================================================
 */
(function (root, factory) {
	if (typeof exports === 'object' && typeof module !== 'undefined') {
		module.exports = factory();
	} else if (typeof define === 'function' && define.amd) {
		define(factory);
	} else {
		root.ImageTool = factory();
	}
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
	'use strict';

	var VERSION = '1.0.0';

	/* ------------------------------------------------------------------ *
	 * 图标（来源 @codexteam/icons，MIT）
	 * ------------------------------------------------------------------ */
	var IconAddBackground = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19V19C9.13623 19 8.20435 19 7.46927 18.6955C6.48915 18.2895 5.71046 17.5108 5.30448 16.5307C5 15.7956 5 14.8638 5 13V12C5 9.19108 5 7.78661 5.67412 6.77772C5.96596 6.34096 6.34096 5.96596 6.77772 5.67412C7.78661 5 9.19108 5 12 5H13.5C14.8956 5 15.5933 5 16.1611 5.17224C17.4395 5.56004 18.44 6.56046 18.8278 7.83886C19 8.40666 19 9.10444 19 10.5V10.5"/><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M16 13V16M16 19V16M19 16M13"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.5 17.5L17.5 6.5"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.9919 10.5H19.0015"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.9919 19H11.0015"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13L13 5"/></svg>';
	var IconAddBorder = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.9919 9.5H19.0015"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.5 5H14.5096"/><path stroke="currentColor" stroke-width="2" d="M14.625 5H15C17.2091 5 19 6.79086 19 9V9.375"/><path stroke="currentColor" stroke-width="2" d="M9.375 5L9 5C6.79086 5 5 6.79086 5 9V9.375"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.3725 5H9.38207"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 9.5H5.00957"/><path stroke="currentColor" stroke-width="2" d="M9.375 19H9C6.79086 19 5 17.2091 5 15V14.625"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.3725 19H9.38207"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 14.55H5.00957"/><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M16 13V16M16 19V16M19 16H16M16 13H13"/></svg>';
	var IconStretch = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9L20 12L17 15"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 12H20"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 9L4 12L7 15"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12H10"/></svg>';
	var IconPicture = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="14" height="14" x="5" y="5" stroke="currentColor" stroke-width="2" rx="4"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.13968 15.32L8.69058 11.5661C9.02934 11.2036 9.48873 11 9.96774 11C10.4467 11 10.9061 11.2036 11.2449 11.5661L15.3871 16M13.5806 14.0664L15.0132 12.533C15.3519 12.1705 15.8113 11.9668 16.2903 11.9668C16.7693 11.9668 17.2287 12.1705 17.5675 12.533L18.841 13.9634"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.7778 9.33331H13.7867"/></svg>';
	var IconText = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 9V7.2C8 7.08954 8.08954 7 8.2 7L12 7M16 9V7.2C16 7.08954 15.9105 7 15.8 7L12 7M12 7L12 17M12 17H10M12 17H14"/></svg>';

	/* ------------------------------------------------------------------ *
	 * 样式（与编译版一致，只注入一次，id 标记防重复）
	 * ------------------------------------------------------------------ */
	var STYLE_ID = 'image-tool-inline-style';
	var TOOL_CSS = [
		'.image-tool{--bg-color:#cdd1e0;--front-color:#388ae5;--border-color:#e8e8eb}',
		'.image-tool__image{border-radius:3px;overflow:hidden;margin-bottom:10px;padding-bottom:0}',
		'.image-tool__image-picture{max-width:100%;vertical-align:bottom;display:block;margin:0 auto}',
		'.image-tool__image-preloader{width:50px;height:50px;border-radius:50%;background-size:cover;margin:auto;position:relative;background-color:var(--bg-color);background-position:center center}',
		'.image-tool__image-preloader:after{content:"";position:absolute;z-index:3;width:60px;height:60px;border-radius:50%;border:2px solid var(--bg-color);border-top-color:var(--front-color);left:50%;top:50%;margin-top:-30px;margin-left:-30px;animation:image-preloader-spin 2s infinite linear;box-sizing:border-box}',
		'.image-tool__caption{bottom:0;left:0;margin-bottom:10px}',
		'.image-tool__caption[contentEditable=true][data-placeholder]:before{position:absolute!important;content:attr(data-placeholder);color:#707684;font-weight:400;display:none}',
		'.image-tool__caption[contentEditable=true][data-placeholder]:empty:before{display:block}',
		'.image-tool__caption[contentEditable=true][data-placeholder]:empty:focus:before{display:none}',
		'.image-tool--empty .image-tool__image,.image-tool--empty .image-tool__image-preloader{display:none}',
		'.image-tool--empty .image-tool__caption,.image-tool--uploading .image-tool__caption{visibility:hidden!important}',
		'.image-tool .cdx-button{display:flex;align-items:center;justify-content:center}',
		'.image-tool .cdx-button svg{height:auto;margin:0 6px 0 0}',
		'.image-tool--filled .cdx-button,.image-tool--filled .image-tool__image-preloader{display:none}',
		'.image-tool--uploading .image-tool__image{min-height:200px;display:flex;border:1px solid var(--border-color);background-color:#fff}',
		'.image-tool--uploading .image-tool__image-picture,.image-tool--uploading .cdx-button{display:none}',
		'.image-tool--withBorder .image-tool__image{border:1px solid var(--border-color)}',
		'.image-tool--withBackground .image-tool__image{padding:15px;background:var(--bg-color)}',
		'.image-tool--withBackground .image-tool__image-picture{max-width:60%;margin:0 auto}',
		'.image-tool--stretched .image-tool__image-picture{width:100%}',
		'.image-tool--caption .image-tool__caption{visibility:visible}',
		'.image-tool--caption{padding-bottom:50px}',
		'@keyframes image-preloader-spin{0%{transform:rotate(0)}to{transform:rotate(360deg)}}'
	].join('');

	function injectStyle() {
		if (typeof document === 'undefined' || !document.head) {
			return;
		}
		if (document.getElementById(STYLE_ID)) {
			return;
		}
		var style = document.createElement('style');
		style.id = STYLE_ID;
		style.appendChild(document.createTextNode(TOOL_CSS));
		document.head.appendChild(style);
	}

	/* ------------------------------------------------------------------ *
	 * 通用小工具
	 * ------------------------------------------------------------------ */

	/**
	 * 创建元素。className 允许 'a b'（含空格）或数组。
	 * @param {string} tagName
	 * @param {string|string[]|null} className
	 * @param {Object} [properties] 直接挂到元素上的属性/字段
	 */
	function make(tagName, className, properties) {
		var el = document.createElement(tagName);
		var tokens = [];
		if (Array.isArray(className)) {
			for (var i = 0; i < className.length; i++) {
				if (className[i]) {
					tokens = tokens.concat(String(className[i]).split(/\s+/));
				}
			}
		} else if (className) {
			tokens = String(className).split(/\s+/);
		}
		for (var j = 0; j < tokens.length; j++) {
			if (tokens[j]) {
				el.classList.add(tokens[j]);
			}
		}
		properties = properties || {};
		for (var key in properties) {
			if (Object.prototype.hasOwnProperty.call(properties, key)) {
				el[key] = properties[key];
			}
		}
		return el;
	}

	function isPromise(value) {
		return value !== undefined && typeof value.then === 'function';
	}

	/** 解析响应头 */
	function parseHeaders(rawHeaders) {
		var result = {};
		if (!rawHeaders) {
			return result;
		}
		rawHeaders.trim().split(/[\r\n]+/).forEach(function (line) {
			var parts = line.split(': ');
			var name = parts.shift();
			var value = parts.join(': ');
			if (name) {
				result[name] = value;
			}
		});
		return result;
	}

	/* ------------------------------------------------------------------ *
	 * 请求层：直接 XHR 整文件上传（不用分片）
	 * ------------------------------------------------------------------ */

	function createProgressReporter(progress) {
		var last = 0;
		return function (value) {
			var next = Math.min(100, Math.max(last, Math.round(Number(value) || 0)));
			if (next === last) {
				return;
			}
			last = next;
			progress(next);
		};
	}

	/**
	 * 原生 XHR POST。整文件上传：FormData 直接发出，不做分片。
	 * @param {Object} options { url, data, headers, progress, beforeSend }
	 * @returns {Promise<{body:*, code:number, headers:Object}>}
	 */
	function request(options) {
		return new Promise(function (resolve, reject) {
			var url = options.url || '';
			var data = options.data;
			var headers = Object.assign({}, options.headers || {});
			var progress = createProgressReporter(
				typeof options.progress === 'function' ? options.progress : function () {}
			);
			var beforeSend = typeof options.beforeSend === 'function' ? options.beforeSend : function () {};

			var xhr = new XMLHttpRequest();
			xhr.open('POST', url);
			headers['X-Requested-With'] = 'XMLHttpRequest';
			Object.keys(headers).forEach(function (name) {
				var value = headers[name];
				if (value !== undefined && value !== null && value !== '') {
					xhr.setRequestHeader(name, value);
				}
			});

			xhr.upload.addEventListener('progress', function (event) {
				if (!event.lengthComputable || !event.total) {
					return;
				}
				progress((event.loaded / event.total) * 100);
			}, false);

			xhr.onreadystatechange = function () {
				if (xhr.readyState !== 4) {
					return;
				}
				var body = xhr.responseText;
				try {
					body = JSON.parse(body);
				} catch (e) {}
				var rawHeaders = '';
				try {
					rawHeaders = xhr.getAllResponseHeaders();
				} catch (e2) {}
				var response = { body: body, code: xhr.status, headers: parseHeaders(rawHeaders) };
				if (xhr.status >= 200 && xhr.status < 300) {
					progress(100);
					resolve(response);
				} else {
					reject(response);
				}
			};

			try {
				beforeSend();
			} catch (e) {}

			/* 整文件直发，不使用 window.editorSendUpload 分片 */
			xhr.send(data);
		});
	}

	/* ------------------------------------------------------------------ *
	 * Ui 状态
	 * ------------------------------------------------------------------ */
	var UiState = { Empty: 'empty', Uploading: 'uploading', Filled: 'filled' };

	function Ui(params) {
		this.api = params.api;
		this.config = params.config;
		this.onSelectFile = params.onSelectFile;
		this.readOnly = params.readOnly;

		this.nodes = {
			wrapper: make('div', [this.CSS.baseClass, this.CSS.wrapper]),
			imageContainer: make('div', [this.CSS.imageContainer]),
			fileButton: this.createFileButton(),
			imageEl: undefined,
			imagePreloader: make('div', this.CSS.imagePreloader),
			caption: make('div', [this.CSS.input, this.CSS.caption], {
				contentEditable: !this.readOnly
			})
		};

		this.nodes.caption.dataset.placeholder = this.config.captionPlaceholder;
		this.nodes.imageContainer.appendChild(this.nodes.imagePreloader);
		this.nodes.wrapper.appendChild(this.nodes.imageContainer);
		this.nodes.wrapper.appendChild(this.nodes.caption);
		this.nodes.wrapper.appendChild(this.nodes.fileButton);
	}

	Ui.prototype.applyTune = function (tuneName, status) {
		this.nodes.wrapper.classList.toggle(this.CSS.wrapper + '--' + tuneName, status);
	};

	Ui.prototype.render = function () {
		this.toggleStatus(UiState.Empty);
		return this.nodes.wrapper;
	};

	Ui.prototype.showPreloader = function (src) {
		this.nodes.imagePreloader.style.backgroundImage = 'url(' + src + ')';
		this.toggleStatus(UiState.Uploading);
	};

	Ui.prototype.hidePreloader = function () {
		this.nodes.imagePreloader.style.backgroundImage = '';
		this.toggleStatus(UiState.Empty);
	};

	/** 填充图片（上传成功后）。mp4 用 video 自动播放看作动图。 */
	Ui.prototype.fillImage = function (url, remote, directory) {
		var isVideo = /\.mp4$/.test(url);
		var attributes = {
			src: remote !== undefined
				? (remote > 0
					? this.config.remote_attachurl + directory + '/' + url
					: this.config.attachurl + directory + '/' + url)
				: url
		};
		var eventName = 'load';
		if (isVideo) {
			attributes.autoplay = true;
			attributes.loop = true;
			attributes.muted = true;
			attributes.playsinline = true;
			eventName = 'loadeddata';
		}
		this.nodes.imageEl = make(isVideo ? 'VIDEO' : 'IMG', this.CSS.imageEl, attributes);
		var self = this;
		this.nodes.imageEl.addEventListener(eventName, function () {
			self.toggleStatus(UiState.Filled);
			if (self.nodes.imagePreloader !== undefined) {
				self.nodes.imagePreloader.style.backgroundImage = '';
			}
		});
		this.nodes.imageContainer.appendChild(this.nodes.imageEl);
	};

	Ui.prototype.fillCaption = function (text) {
		if (this.nodes.caption !== undefined) {
			this.nodes.caption.innerHTML = text;
		}
	};

	Ui.prototype.toggleStatus = function (status) {
		for (var key in UiState) {
			if (Object.prototype.hasOwnProperty.call(UiState, key)) {
				var state = UiState[key];
				this.nodes.wrapper.classList.toggle(this.CSS.wrapper + '--' + state, state === status);
			}
		}
	};

	Object.defineProperty(Ui.prototype, 'CSS', {
		get: function () {
			return {
				baseClass: this.api.styles.block,
				loading: this.api.styles.loader,
				input: this.api.styles.input,
				button: this.api.styles.button,
				wrapper: 'image-tool',
				imageContainer: 'image-tool__image',
				imagePreloader: 'image-tool__image-preloader',
				imageEl: 'image-tool__image-picture',
				caption: 'image-tool__caption'
			};
		}
	});

	Ui.prototype.createFileButton = function () {
		var self = this;
		var button = make('div', [this.api.styles.button]);
		button.innerHTML = this.config.buttonContent
			|| (IconPicture + ' ' + this.api.i18n.t('Select an Image'));
		button.addEventListener('click', function () {
			self.onSelectFile();
		});
		return button;
	};

	/* ------------------------------------------------------------------ *
	 * 上传层
	 * ------------------------------------------------------------------ */

	function Uploader(params) {
		this.config = params.config;
		this.onUpload = params.onUpload;
		this.onError = params.onError;
	}

	/** 弹出文件选择框 */
	Uploader.prototype.selectFiles = function (accept) {
		return new Promise(function (resolve) {
			var input = document.createElement('input');
			input.type = 'file';
			if (accept) {
				input.setAttribute('accept', accept);
			}
			input.style.display = 'none';
			document.body.appendChild(input);
			input.addEventListener('change', function (event) {
				var files = event.target.files;
				resolve(files);
				document.body.removeChild(input);
			}, false);
			input.click();
		});
	};

	/** 追加 additionalRequestData（uid / hash 等）到表单 */
	Uploader.prototype.appendExtraData = function (formData) {
		var extra = this.config.additionalRequestData;
		if (!extra || !Object.keys(extra).length) {
			return;
		}
		Object.keys(extra).forEach(function (name) {
			formData.append(name, extra[name]);
		});
	};

	/** 本地 preview */
	function preparePreview(file, onPreview) {
		var reader = new FileReader();
		reader.readAsDataURL(file);
		reader.onload = function (event) {
			onPreview(event.target.result);
		};
	}

	/** 点按钮选择图片后上传 */
	Uploader.prototype.uploadSelectedFile = function (options) {
		var self = this;
		var onPreview = options.onPreview;
		var onProgress = options.onProgress;
		var accept = this.config.types || 'image/*';
		var upload;

		if (this.config.uploader && typeof this.config.uploader.uploadByFile === 'function') {
			var uploadByFile = this.config.uploader.uploadByFile;
			upload = this.selectFiles(accept).then(function (files) {
				preparePreview(files[0], onPreview);
				var customUpload = uploadByFile(files[0]);
				if (!isPromise(customUpload)) {
					console.warn('Custom uploader method uploadByFile should return a Promise');
				}
				return customUpload;
			});
		} else {
			upload = this.selectFiles(accept).then(function (files) {
				var file = files[0];
				preparePreview(file, onPreview);
				var formData = new FormData();
				formData.append(self.config.field || 'image', file, file.name);
				self.appendExtraData(formData);
				return request({
					url: self.config.endpoints.byFile,
					data: formData,
					headers: self.config.additionalRequestHeaders,
					progress: onProgress
				}).then(function (response) {
					return response.body;
				});
			});
		}

		upload.then(function (response) {
			self.onUpload(response);
		}).catch(function (error) {
			self.onError(error);
		});
	};

	/** 拖拽 / 粘贴得到的 Blob 直接上传 */
	Uploader.prototype.uploadByFile = function (file, options) {
		var self = this;
		var onPreview = options.onPreview;
		var onProgress = options.onProgress;
		var upload;

		preparePreview(file, onPreview);

		if (this.config.uploader && typeof this.config.uploader.uploadByFile === 'function') {
			upload = this.config.uploader.uploadByFile(file);
			if (!isPromise(upload)) {
				console.warn('Custom uploader method uploadByFile should return a Promise');
			}
		} else {
			var formData = new FormData();
			formData.append(this.config.field || 'image', file, file.name || 'image.png');
			this.appendExtraData(formData);
			upload = request({
				url: this.config.endpoints.byFile,
				data: formData,
				headers: this.config.additionalRequestHeaders,
				progress: onProgress
			}).then(function (response) {
				return response.body;
			});
		}

		upload.then(function (response) {
			self.onUpload(response);
		}).catch(function (error) {
			self.onError(error);
		});
	};

	/** 粘贴图片地址上传 */
	Uploader.prototype.uploadByUrl = function (url) {
		var self = this;
		var upload;

		if (this.config.uploader && typeof this.config.uploader.uploadByUrl === 'function') {
			upload = this.config.uploader.uploadByUrl(url);
			if (!isPromise(upload)) {
				console.warn('Custom uploader method uploadByUrl should return a Promise');
			}
		} else {
			var payload = Object.assign({ url: url }, this.config.additionalRequestData || {});
			upload = request({
				url: this.config.endpoints.byUrl,
				data: JSON.stringify(payload),
				headers: Object.assign({
					'Content-Type': 'application/json; charset=utf-8'
				}, this.config.additionalRequestHeaders || {})
			}).then(function (response) {
				return response.body;
			});
		}

		upload.then(function (response) {
			self.onUpload(response);
		}).catch(function (error) {
			self.onError(error);
		});
	};

	/* ------------------------------------------------------------------ *
	 * 工具主体
	 * ------------------------------------------------------------------ */

	function ImageTool(params) {
		var data = params.data;
		var config = params.config || {};
		var api = params.api;
		var readOnly = params.readOnly;
		var block = params.block;

		this.isCaptionEnabled = null;
		this.api = api;
		this.block = block;

		this.config = {
			endpoints: config.endpoints || {},
			additionalRequestData: config.additionalRequestData,
			additionalRequestHeaders: config.additionalRequestHeaders,
			field: config.field,
			types: config.types,
			remote_attachurl: config.remote_attachurl,
			attachurl: config.attachurl,
			captionPlaceholder: api.i18n.t(config.captionPlaceholder || 'Caption'),
			buttonContent: config.buttonContent,
			uploader: config.uploader,
			actions: config.actions,
			features: config.features || {}
		};

		this.uploader = new Uploader({
			config: this.config,
			onUpload: this.onUpload.bind(this),
			onError: this.uploadingFailed.bind(this)
		});

		this.ui = new Ui({
			api: api,
			config: this.config,
			onSelectFile: function () {
				this.uploader.uploadSelectedFile({
					onPreview: function (src) {
						this.ui.showPreloader(src);
					}.bind(this)
				});
			}.bind(this),
			readOnly: readOnly
		});

		this._data = {
			caption: '',
			withBorder: false,
			withBackground: false,
			stretched: false,
			file: {
				remote: 0,
				directory: '',
				url: ''
			}
		};

		this.data = data;
	}

	ImageTool.isReadOnlySupported = true;

	ImageTool.toolbox = {
		icon: IconPicture,
		title: 'Image'
	};

	/** 内置 tune：带边框 / 拉伸 / 带背景色 */
	ImageTool.tunes = [
		{ name: 'withBorder', icon: IconAddBorder, title: 'With border', toggle: true },
		{ name: 'stretched', icon: IconStretch, title: 'Stretch image', toggle: true },
		{ name: 'withBackground', icon: IconAddBackground, title: 'With background', toggle: true }
	];

	ImageTool.pasteConfig = {
		tags: [{ img: { src: true } }],
		patterns: {
			image: /https?:\/\/\S+\.(gif|jpe?g|tiff|png|svg|webp)(\?[a-z0-9=]*)?$/i
		},
		files: {
			mimeTypes: ['image/*']
		}
	};

	ImageTool.conversionConfig = {
		export: function (data) {
			return data;
		},
		import: function () {
			return {
				caption: '',
				withBorder: false,
				withBackground: false,
				stretched: false,
				file: {
					remote: 0,
					directory: '',
					url: ''
				}
			};
		}
	};

	ImageTool.prototype.render = function () {
		var captionFeature = this.config.features ? this.config.features.caption : undefined;
		if (captionFeature === true || captionFeature === undefined
			|| (captionFeature === 'optional' && this.data.caption)) {
			this.isCaptionEnabled = true;
			this.ui.applyTune('caption', true);
		}
		return this.ui.render();
	};

	/** 没 url 视为无效块（未上传成功） */
	ImageTool.prototype.validate = function (savedData) {
		return !!savedData.file && !!savedData.file.url;
	};

	ImageTool.prototype.save = function () {
		this._data.caption = this.ui.nodes.caption.innerHTML;
		return this.data;
	};

	/** 区块设置菜单（tunes） */
	ImageTool.prototype.renderSettings = function () {
		var tunes = ImageTool.tunes.concat(this.config.actions || []);
		var featureTuneMap = {
			border: 'withBorder',
			background: 'withBackground',
			stretch: 'stretched',
			caption: 'caption'
		};
		var features = this.config.features || {};

		if (features.caption === 'optional') {
			tunes.push({ name: 'caption', icon: IconText, title: 'With caption', toggle: true });
		}

		var self = this;
		var availableTunes = tunes.filter(function (tune) {
			var featureKey = Object.keys(featureTuneMap).filter(function (key) {
				return featureTuneMap[key] === tune.name;
			})[0];
			if (featureKey === 'caption') {
				return features.caption !== false;
			}
			return featureKey === undefined || features[featureKey] !== false;
		});

		var isActive = function (tune) {
			var currentState = self.data[tune.name];
			if (tune.name === 'caption') {
				currentState = self.isCaptionEnabled !== null ? self.isCaptionEnabled : currentState;
			}
			return currentState;
		};

		return availableTunes.map(function (tune) {
			return {
				icon: tune.icon,
				label: self.api.i18n.t(tune.title),
				name: tune.name,
				toggle: tune.toggle,
				isActive: isActive(tune),
				onActivate: function () {
					if (typeof tune.action === 'function') {
						tune.action(tune.name);
						return;
					}
					var newState = !isActive(tune);
					if (tune.name === 'caption') {
						self.isCaptionEnabled = !(self.isCaptionEnabled || false);
						newState = self.isCaptionEnabled;
					}
					self.tuneToggled(tune.name, newState);
				}
			};
		});
	};

	/** 从工具栏点「图片」时直接弹出文件选择 */
	ImageTool.prototype.appendCallback = function () {
		this.ui.nodes.fileButton.click();
	};

	ImageTool.prototype.onPaste = function (event) {
		var self = this;
		switch (event.type) {
			case 'tag': {
				var image = event.detail.data;
				if (/^blob:/.test(image.src)) {
					fetch(image.src).then(function (response) {
						return response.blob();
					}).then(function (file) {
						self.uploadFile(file);
					});
					break;
				}
				this.uploadUrl(image.src);
				break;
			}
			case 'pattern': {
				this.uploadUrl(event.detail.data);
				break;
			}
			case 'file': {
				this.uploadFile(event.detail.file);
				break;
			}
		}
	};

	Object.defineProperty(ImageTool.prototype, 'data', {
		get: function () {
			return this._data;
		},
		set: function (data) {
			data = data || {};
			this.image = data.file;

			this._data.caption = data.caption || '';
			this.ui.fillCaption(this._data.caption);

			ImageTool.tunes.forEach(function (tune) {
				var raw = data[tune.name];
				var value = typeof raw !== 'undefined' ? (raw === true || raw === 'true') : false;
				this.setTune(tune.name, value);
			}, this);

			if (data.caption) {
				this.setTune('caption', true);
			} else if (this.config.features && this.config.features.caption === true) {
				this.setTune('caption', true);
			}
		}
	});

	Object.defineProperty(ImageTool.prototype, 'image', {
		set: function (file) {
			this._data.file = file || { remote: 0, directory: '', url: '' };
			if (file && file.url) {
				this.ui.fillImage(file.url, file.remote, file.directory);
			}
		}
	});

	/** 上传成功回调：拿到服务端返回的 file 对象 */
	ImageTool.prototype.onUpload = function (response) {
		if (response && response.success && response.file) {
			this.image = response.file;
		} else {
			this.uploadingFailed('incorrect response: ' + JSON.stringify(response), response);
		}
	};

	/** 上传失败：按 statusid 给提示 */
	ImageTool.prototype.uploadingFailed = function (errorText, response) {
		console.log('Image Tool: uploading failed because of', errorText);
		var statusid = response ? response.statusid : undefined;
		var message;

		switch (statusid) {
			case -1:
				message = this.api.i18n.t('Unsupported file type');
				break;
			case -2:
				message = this.api.i18n.t('File has exceptions');
				break;
			case -3:
			case -4:
			case -5:
				message = this.api.i18n.t('File size cannot exceed ') + formatSize(parseInt(response.sizelimit || '0', 10));
				break;
			case -6:
				message = this.api.i18n.t('User group does not support uploading this type of file');
				break;
			default:
				message = this.api.i18n.t('Couldn’t upload image. Please try another.');
				break;
		}

		this.api.notifier.show({ message: message, style: 'error' });
		this.ui.hidePreloader();
	};

	function formatSize(sizeLimit) {
		if (sizeLimit >= 1073741824) {
			return (sizeLimit / 1073741824).toFixed(2) + ' GB';
		}
		if (sizeLimit >= 1048576) {
			return (sizeLimit / 1048576).toFixed(2) + ' MB';
		}
		if (sizeLimit >= 1024) {
			return (sizeLimit / 1024).toFixed(2) + ' KB';
		}
		return sizeLimit + ' B';
	}

	ImageTool.prototype.tuneToggled = function (tuneName, state) {
		if (tuneName === 'caption') {
			this.ui.applyTune(tuneName, state);
			if (state === false) {
				this._data.caption = '';
				this.ui.fillCaption('');
			}
		} else {
			this.setTune(tuneName, state);
		}
	};

	ImageTool.prototype.setTune = function (tuneName, value) {
		this._data[tuneName] = value;
		this.ui.applyTune(tuneName, value);
		if (tuneName === 'stretched') {
			var self = this;
			Promise.resolve().then(function () {
				self.block.stretched = value;
			}).catch(function (error) {
				console.error(error);
			});
		}
	};

	ImageTool.prototype.uploadFile = function (file) {
		this.uploader.uploadByFile(file, {
			onPreview: function (src) {
				this.ui.showPreloader(src);
			}.bind(this)
		});
	};

	ImageTool.prototype.uploadUrl = function (url) {
		this.ui.showPreloader(url);
		this.uploader.uploadByUrl(url);
	};

	injectStyle();

	return ImageTool;
});
