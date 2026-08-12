var _J$ = jQuery.noConflict();
window.$ = function () {
	if (arguments.length === 1 && typeof arguments[0] === 'string') {
		if (arguments[0] === '') {
			return null;
		}
		if (arguments[0].match(/^[a-zA-Z_][a-zA-Z0-9-_]*$/) &&
		    !isNativeHtmlTag(arguments[0])) {
			return _D$.apply(null, arguments);
		}
	}
	return _J$.apply(null, arguments);
};

function jqueryProperty() {
	for (var _prop in _J$) {
		if (_J$.hasOwnProperty(_prop) && typeof _J$[_prop] === 'function') {
			$[_prop] = _J$[_prop];
		}
	}
}

function isNativeHtmlTag(tag) {
	if (!tag || typeof tag !== 'string') return false;
	const t = tag.trim().toLowerCase();
	const el = document.createElement(t);
	return el instanceof window.HTMLUnknownElement === false;
}

jqueryProperty();

var platform = navigator.platform;
var ua = navigator.userAgent;
var ios = /iPhone|iPad|iPod/.test(platform) && ua.indexOf( "AppleWebKit" ) > -1;
var andriod = ua.indexOf( "Android" ) > -1;

var page = {
	converthtml : function() {
		var prevpage = qSel('div.pg .prev') ? qSel('div.pg .prev').href : undefined;
		var nextpage = qSel('div.pg .nxt') ? qSel('div.pg .nxt').href : undefined;
		var lastpage = qSel('div.pg label span') ? (qSel('div.pg label span').innerText.replace(/[^\d]/g, '') || 0) : 0;
		var curpage = qSel('div.pg input') ? qSel('div.pg input').value : 1;
		var multipage_url = getID('multipage_url') ? getID('multipage_url').value : undefined;

		if(!lastpage) {
			prevpage = qSel('div.pg .pgb a') ? qSel('div.pg .pgb a').href : undefined;
		}

		var prevpagehref = nextpagehref = '';
		if(prevpage == undefined) {
			prevpagehref = 'javascript:;" class="grey';
		} else {
			prevpagehref = prevpage;
		}
		if(nextpage == undefined) {
			nextpagehref = 'javascript:;" class="grey';
		} else {
			nextpagehref = nextpage;
		}

		var selector = '';
		if(lastpage) {
			selector += '<a id="select_a">';
			selector += '<select id="dumppage">';
			for(var i=1; i<=lastpage; i++) {
				selector += '<option value="' + i + '" ' + (i == curpage ? 'selected' : '') + '>' + $L('page_number', [i]) + '</option>';
			}
			selector += '</select>';
			selector += '<span>' + $L('page_number', [curpage]) + '</span>';
		}

		var pgobj = qSel('div.pg');
		pgobj.classList.remove('pg');
		pgobj.classList.add('page');
		pgobj.innerHTML = '<a href="'+ prevpagehref +'">' + $L('page_prev') + '</a>'+ selector +'<a href="'+ nextpagehref +'">' + $L('page_next') + '</a>';
		qSel('#dumppage').addEventListener('change', function() {
			window.location.href = multipage_url + 'page=' + this.value;
		});
	},
};

var scrolltop = {
	obj : null,
	init : function(obj) {
		scrolltop.obj = obj;
		var pageHeight = Math.max(document.body.scrollHeight, document.body.offsetHeight);
		var screenHeight = window.innerHeight;
		var scrollType = 'bottom';
		var scrollToPos = function() {
			if(scrollType == 'bottom') {
				window.scrollTo(0, pageHeight);
			} else {
				window.scrollTo(0, 0);
			}
			scrollfunc();
		};
		var scrollfunc = function() {
			var newType;
			if(document.documentElement.scrollTop >= 50) {
				newType = 'top';
			} else {
				newType = 'bottom';
			}
			if(newType != scrollType) {
				scrollType = newType;
				if(newType == 'top') {
					obj.classList.remove('bottom');
				} else {
					obj.classList.add('bottom');
				}
			}
		};
		if(pageHeight - screenHeight < 100) {
			obj.style.display = 'none';
		} else {
			obj.addEventListener('click', scrollToPos);
			document.addEventListener('scroll', scrollfunc);
			scrollfunc();
		}
	},
};

var img = {
	init : function(is_err_t) {
		var errhandle = this.errorhandle;
		$('img').on('load', function() {
			var obj = $(this);
			obj.attr('zsrc', obj.attr('src'));
			if(obj.width() < 5 && obj.height() < 10 && obj.css('display') != 'none') {
				return errhandle(obj, is_err_t);
			}
			obj.css('display', 'inline');
			obj.css('visibility', 'visible');
			if(obj.width() > window.innerWidth) {
				obj.css('width', window.innerWidth);
			}
			obj.parent().find('.loading').remove();
			obj.parent().find('.error_text').remove();
		})
		.on('error', function() {
			var obj = $(this);
			obj.attr('zsrc', obj.attr('src'));
			errhandle(obj, is_err_t);
		});
	},
	errorhandle : function(obj, is_err_t) {
		if(obj.attr('noerror') == 'true') {
			return;
		}
		obj.css('visibility', 'hidden');
		obj.css('display', 'none');
		var parentnode = obj.parent();
		parentnode.find('.loading').remove();
		parentnode.append('<div class="loading" style="background:url('+ IMGDIR +'/imageloading.gif) no-repeat center center;width:'+parentnode.width()+'px;height:'+parentnode.height()+'px"></div>');
		var loadnums = parseInt(obj.attr('load')) || 0;
		if(loadnums < 3) {
			obj.attr('src', obj.attr('zsrc'));
			obj.attr('load', ++loadnums);
			return false;
		}
		if(is_err_t) {
			var parentnode = obj.parent();
			parentnode.find('.loading').remove();
			parentnode.append('<div class="error_text">' + $L('click_reload') + '</div>');
			parentnode.find('.error_text').one('click', function() {
				obj.attr('load', 0).find('.error_text').remove();
				parentnode.append('<div class="loading" style="background:url('+ IMGDIR +'/imageloading.gif) no-repeat center center;width:'+parentnode.width()+'px;height:'+parentnode.height()+'px"></div>');
				obj.attr('src', obj.attr('zsrc'));
			});
		}
		return false;
	}
};

var POPMENU = new Object;
var popup = {
	init : function() {
		var $this = this;
		$('.popup').each(function(index, obj) {
			obj = $(obj);
			var pop = $(obj.attr('href'));
			if(pop && pop.attr('popup')) {
				pop.css({'display':'none'});
				obj.on('click', function(e) {
					$this.open(pop);
					return false;
				});
			}
		});
		this.maskinit();
	},
	maskinit : function() {
		var $this = this;
		$('#mask').off().on('click', function() {
			$this.close();
		});
	},

	open : function(pop, type, url) {
		this.close();
		this.maskinit();
		if(typeof pop == 'string') {
			$('#ntcmsg').remove();
			if(type == 'alert') {
				pop = '<div class="tip"><dt>'+ pop +'</dt><dd><input class="button2" type="button" value="' + $L('confirm') + '" onclick="popup.close();"></dd></div>'
			} else if(type == 'confirm') {
				pop = '<div class="tip"><dt>'+ pop +'</dt><dd><a class="button" href="'+ url +'">' + $L('confirm') + '</a> <button onclick="popup.close();" class="button">' + $L('cancel') + '</a></dd></div>'
			}
			$('body').append('<div id="ntcmsg" style="display:none;">'+ pop +'</div>');
			pop = $('#ntcmsg');
		}
		if(POPMENU[pop.attr('id')]) {
			$('#' + pop.attr('id') + '_popmenu').html(pop.html()).css({'height':pop.height()+'px', 'width':pop.width()+'px'});
		} else {
			pop.parent().append('<div class="dialogbox" id="'+ pop.attr('id') +'_popmenu" style="height:'+ pop.height() +'px;width:'+ pop.width() +'px;">'+ pop.html() +'</div>');
		}
		var popupobj = $('#' + pop.attr('id') + '_popmenu');
		var left = (window.innerWidth - popupobj.width()) / 2;
		var top = (document.documentElement.clientHeight - popupobj.height()) / 2;
		popupobj.css({'display':'block','position':'fixed','left':left,'top':top,'z-index':120,'opacity':1});
		$('#mask').css({'display':'block','width':'100%','height':'100%','position':'fixed','top':'0','left':'0','background':'black','opacity':'0.2','z-index':'100'});
		POPMENU[pop.attr('id')] = pop;
	},
	close : function() {
		$('#mask').css('display', 'none');
		$.each(POPMENU, function(index, obj) {
			$('#' + index + '_popmenu').css('display','none');
		});
	}
};

var dialog = {
	init : function() {
		$(document).on('click', '.dialog', function() {
			var obj = $(this);
			popup.open('<img src="' + IMGDIR + '/imageloading.gif">');
			$.ajax({
				type : 'GET',
				url : obj.attr('href') + '&inajax=1',
				dataType : 'xml'
			})
			.success(function(s) {
				popup.open(s.lastChild.firstChild.nodeValue);
				evalscript(s.lastChild.firstChild.nodeValue);
				// 初始化弹窗中的 sort_sel 选择器
				if (typeof window.initAllSortSel === 'function') {
					setTimeout(function() {
						window.initAllSortSel();
					}, 300);
				}
			})
			.error(function() {
				window.location.href = obj.attr('href');
				popup.close();
			});
			return false;
		});
	},

};

var formdialog = {
	init : function() {
		$(document).on('click', '.formdialog', function() {
			popup.open('<img src="' + IMGDIR + '/imageloading.gif">');
			var obj = $(this);
			var formobj = $(this.form);
			var isFormData = formobj.find("input[type='file']").length > 0;
			$.ajax({
				type:'POST',
				url:formobj.attr('action') + '&handlekey='+ formobj.attr('id') +'&inajax=1',
				data:isFormData ? new FormData(formobj[0]) : formobj.serialize(),
				dataType:'xml',
				processData:isFormData ? false : true,
				contentType:isFormData ? false : 'application/x-www-form-urlencoded; charset=UTF-8'
			})
			.success(function(s) {
				popup.open(s.lastChild.firstChild.nodeValue);
				evalscript(s.lastChild.firstChild.nodeValue);
				// 初始化弹窗中的 sort_sel 选择器
				if (typeof window.initAllSortSel === 'function') {
					setTimeout(function() {
						window.initAllSortSel();
					}, 300);
				}
			})
			.error(function() {
				popup.open($L('forum_submit_error'), 'alert');
			});
			return false;
		});
	}
};

var DISMENU = new Object;
var display = {
	init : function() {
		var $this = this;
		$('.display').each(function(index, obj) {
			obj = $(obj);
			var dis = $(obj.attr('href'));
			if(dis && dis.attr('display')) {
				dis.css({'display':'none'});
				dis.css({'z-index':'102'});
				DISMENU[dis.attr('id')] = dis;
				obj.on('click', function(e) {
					if(in_array(e.target.tagName, ['A', 'IMG', 'INPUT'])) return;
					$this.maskinit();
					if(dis.attr('display') == 'true') {
						dis.css('display', 'block');
						dis.attr('display', 'false');
						$('#mask').css({'display':'block','width':'100%','height':'100%','position':'fixed','top':'0','left':'0','background':'transparent','z-index':'100'});
					}
					return false;
				});
			}
		});
	},
	maskinit : function() {
		var $this = this;
		$('#mask').off().on('click', function() {
			$this.hide();
		});
	},
	hide : function() {
		$('#mask').css('display', 'none');
		$.each(DISMENU, function(index, obj) {
			obj.css('display', 'none');
			obj.attr('display', 'true');
		});
	}
};

function qSel(sel) {
	return document.querySelector(sel);
}

function qSelA(sel) {
	return document.querySelectorAll(sel);
}

function mygetnativeevent(event) {
	while(event && typeof event.originalEvent !== "undefined") {
		event = event.originalEvent;
	}
	return event;
}

function getFirstFrame(file, callback) {
	const video = document.createElement('video');
	video.preload = 'metadata';
	video.muted = true;
	video.playsInline = true;
	video.crossOrigin = 'anonymous';

	const canvas = document.createElement('canvas');
	const ctx = canvas.getContext('2d');

	video.onloadedmetadata = () => {
		video.currentTime = 0;
	};

	video.onseeked = () => {
		canvas.width = video.videoWidth;
		canvas.height = video.videoHeight;
		ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
		const dataURL = canvas.toDataURL('image/png');
		callback(dataURL);
	};

	video.src = URL.createObjectURL(file);
}

function addImageZoomStyles() {
	if(document.getElementById('imgzoom_styles')) return;

	var style = document.createElement('style');
	style.id = 'imgzoom_styles';
	style.textContent = `
	.imgzoom_pop {
		display: none;
	}
	.imgzoom_dialog {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, 0.98);
		z-index: 999999;
		/* 防止页面缩放 */
		touch-action: none;
	}
	/* 确保自动创建的弹窗容器也有足够高的z-index */
	#imgzoom_pop_popmenu {
		z-index: 999999 !important;
	}
	.imgzoom_content {
		position: absolute;
		top: 0;
		bottom: 104px;
		left: 0;
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: auto;
		padding: 20px;
		box-sizing: border-box;
	}
	.imgzoom_footer {
		position: absolute;
		bottom: 60px;
		left: 0;
		width: 100%;
		height: 44px;
		background: rgba(0, 0, 0, 0.8);
		color: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 20px;
	}
	.imgzoom_rotate, .imgzoom_opennew, .imgzoom_closebtn {
		color: #fff;
		font-size: 14px;
		cursor: pointer;
		padding: 8px 16px;
		background: rgba(255, 255, 255, 0.2);
		border-radius: 4px;
		text-decoration: none;
	}
	.imgzoom_rotate:active, .imgzoom_opennew:active, .imgzoom_closebtn:active {
		background: rgba(255, 255, 255, 0.3);
	}
	#imgzoom_img {
		max-width: 100%;
		max-height: 100%;
		transition: transform 0.1s ease;
	}
	#mask {
		cursor: pointer;
		z-index: 999998;
		background: rgba(0, 0, 0, 0.98);
	}
	`;
	document.head.appendChild(style);
}

var currentZoomImgUrl = '';

function openImageInNewWindow() {
	window.open(currentZoomImgUrl, '_blank');
	popup.close();
}

function zoom(imgObj, zoomfile, nocover, pn, showexif) {
	addImageZoomStyles();

	var imgUrl = zoomfile || imgObj.getAttribute('zoomfile') || imgObj.src;
	if(!imgUrl) return;

	currentZoomImgUrl = imgUrl;

	var zoomHtml = '<div id="imgzoom_pop" class="imgzoom_pop" popup="true" style="display:none;">'
		+ '<div class="imgzoom_dialog">'
		+ '<div class="imgzoom_content">'
		+ '<img id="imgzoom_img" src="' + imgUrl + '" style="transform-origin: center center; max-width: 100%; max-height: 100%; transform: scale(1) rotate(0deg);" />'
		+ '</div>'
		+ '<div class="imgzoom_footer f_f">'
		+ '<span class="imgzoom_rotate" ontouchend="rotateImage(); return false;">'+$L('img_roate')+'</span>'
		+ '<span class="imgzoom_opennew" ontouchend="openImageInNewWindow(); return false;">'+$L('open_newwindow')+'</span>'
		+ '<span class="imgzoom_closebtn" ontouchend="closeImageZoom();">'+$L('close')+'</span>'
		+ '</div>'
		+ '</div>'
		+ '</div>';

	var zoomContainer = document.getElementById('imgzoom_pop');
	if(zoomContainer) {
		zoomContainer.parentNode.removeChild(zoomContainer);
	}
	document.body.insertAdjacentHTML('beforeend', zoomHtml);
	popup.open($('#imgzoom_pop'));

	setTimeout(function() {
		var actualImg = document.querySelector('#imgzoom_pop_popmenu #imgzoom_img');
		if(actualImg) {
			if(!actualImg.style.transform) {
				actualImg.style.transform = 'scale(1) rotate(0deg)';
			}

			initImageZoomRotate();
		}
	}, 0);
}

function closeImageZoom() {
	var e = window.event || arguments.callee.caller.arguments[0];
	if(e) {
		e.stopPropagation();
		e.preventDefault();
	}

	popup.close();

	setTimeout(function() {
	}, 100);

	return false;
}

function initImageZoomRotate() {
	var img = document.querySelector('#imgzoom_pop_popmenu #imgzoom_img') || document.getElementById('imgzoom_img');
	if(!img) return;

	if(!img.style.transform) {
		img.style.transform = 'scale(1) rotate(0deg)';
	}

	var scale = 1;
	var rotate = 0;
	var startScale = 1;
	var startRotate = 0;

	var newImg = img.cloneNode(true);
	img.parentNode.replaceChild(newImg, img);
	img = newImg;

	img.addEventListener('click', function(e) {
		if(e.touches.length === 2) {
			startScale = scale;
			startRotate = rotate;
		}
	}, { passive: true });

	img.addEventListener('touchmove', function(e) {
		if(e.touches.length === 2) {
			var dist1 = Math.hypot(
				e.touches[0].clientX - e.touches[1].clientX,
				e.touches[0].clientY - e.touches[1].clientY
			);
			var dist2 = Math.hypot(
				e.touches[0].pageX - e.touches[1].pageX,
				e.touches[0].pageY - e.touches[1].pageY
			);
			scale = startScale * (dist2 / dist1);

			var angle1 = Math.atan2(
				e.touches[0].clientY - e.touches[1].clientY,
				e.touches[0].clientX - e.touches[1].clientX
			);
			var angle2 = Math.atan2(
				e.touches[0].pageY - e.touches[1].pageY,
				e.touches[0].pageX - e.touches[1].pageX
			);
			rotate = startRotate + (angle2 - angle1) * (180 / Math.PI);

			img.style.transform = 'scale(' + scale + ') rotate(' + rotate + 'deg)';
		}
	}, { passive: false });
}

function rotateImage() {
	var img = document.querySelector('#imgzoom_pop_popmenu #imgzoom_img') || document.getElementById('imgzoom_img');
	if(!img) return;

	var currentTransform = img.style.transform || 'scale(1) rotate(0deg)';

	var scaleMatch = currentTransform.match(/scale\(([\d.]+)\)/);
	var rotateMatch = currentTransform.match(/rotate\(([\d.]+)deg\)/);

	var scale = scaleMatch ? parseFloat(scaleMatch[1]) : 1;
	var currentRotate = rotateMatch ? parseFloat(rotateMatch[1]) : 0;

	var newRotate = currentRotate + 90;

	img.style.transform = 'scale(' + scale + ') rotate(' + newRotate + 'deg)';
}

$(document).ready(function() {
	if(qSel('div.pg')) {
		page.converthtml();
	}
	if(qSel('.scrolltop')) {
		scrolltop.init(qSel('.scrolltop'));
	}
	if($('img').length > 0) {
		img.init(1);
	}
	if($('.popup').length > 0) {
		popup.init();
	}
	if($('.display').length > 0) {
		display.init();
	}
	dialog.init();
	formdialog.init();

	$(document).on('click', 'img[zoomfile]', function() {
		zoom(this);
		return false;
	});
});

function portal_flowlazyload() {
	var obj = this;
	var times = 0;
	var processing = false;
	this.getOffset = function (el, isLeft) {
		var retValue = 0 ;
		while (el != null) {
			retValue += el["offset" + (isLeft ? "Left" : "Top" )];
			el = el.offsetParent;
		}
		return retValue;
	};
	this.attachEvent = function (obj, evt, func, eventobj) {
		eventobj = !eventobj ? obj : eventobj;
		if(obj.addEventListener) {
			obj.addEventListener(evt, func, false);
		} else if(eventobj.attachEvent) {
			obj.attachEvent('on' + evt, func);
		}
	};
	this.removeElement = function (_element) {
		var _parentElement = _element.parentNode;
		if(_parentElement) {
			_parentElement.removeChild(_element);
		}
	};
	this.showNextPage = function() {
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var offsetTop = this.getOffset(document.getElementsByClassName('page')[0]);
		if (!processing && times <= 9 && offsetTop > document.documentElement.clientHeight && (offsetTop - scrollTop < document.documentElement.clientHeight)) {
			processing = true;
			times++;
			var x = new Ajax();
			x.get('portal.php?mod=index&page=' + ++flowpage + '&inajax=1', function(s) {
				if(s.indexOf(mobnodata) !== -1) {
					var infoli = s.match(/<li>([\w\W]+)<\/li>/g);
					var pgdiv = s.match(/<div class="pg">([\w\W]+)<\/div>/g);
					if (infoli !== null && pgdiv !== null) {
						document.getElementsByClassName('wzlist')[0].insertAdjacentHTML('beforeend', infoli);
						document.getElementsByClassName('page')[0].insertAdjacentHTML('afterend', pgdiv);
						obj.removeElement(document.getElementsByClassName('page')[0]);
						page.converthtml();
						processing = false;
					}
				}
			});
		}
	};
	this.attachEvent(window, 'scroll', function(){obj.showNextPage();});
}

function explode(sep, string) {
	return string.split(sep);
}

function setCopy(text, msg) {
	var cp = document.createElement('textarea');
	cp.style.fontSize = '12pt';
	cp.style.border = '0';
	cp.style.padding = '0';
	cp.style.margin = '0';
	cp.style.position = 'absolute';
	cp.style.left = '-9999px';
	var yPosition = window.pageYOffset || document.documentElement.scrollTop;
	cp.style.top = yPosition + 'px';
	cp.setAttribute('readonly', '');
	text = text.replace(/[\xA0]/g, ' ');
	cp.value = text;
	document.getElementById('append_parent').appendChild(cp);
	cp.select();
	cp.setSelectionRange(0, cp.value.length);
	try {
		var success = document.execCommand('copy', false, null);
	} catch(e) {
		var success = false;
	}
	document.getElementById('append_parent').removeChild(cp);

	if (success) {
		if (msg) {
			popup.open(msg, 'alert');
		}
	} else {
		popup.open($L('copy_failed2'), 'alert');
	}
}

function copycode(obj) {
	setCopy(obj.textContent, $L('copy_clipboard_success'));
}

function submitpostpw(pid, tid) {
	var obj = document.getElementById('postpw_' + pid);
	setcookie('postpw_' + pid, hex_md5(obj.value));
	if(!tid) {
		location.href = location.href;
	} else {
		location.href = 'forum.php?mod=viewthread&tid='+tid;
	}
}

var mobileDiy = {
    setPos: function () {
        var len = this.moveableArea.length;
        var cssStr = '';
        for (var i = 0; i < len; i++) {
            var el = this.moveableArea[i];
            if (el == null || typeof el == 'undefined') return false;
            var id = el.id;
            var s = parent.$(id).innerHTML;
            s = s.replace(/<div class="edit.+?<\/div>/gi, '');
            s = s.replace(/<div class="block-name.+?<\/div>/gi, '');
            el.innerHTML = s;
            if(parent.spaceDiy) {
                cssStr += parent.spaceDiy.getSpacecssStr('#' + parent.$(id).childNodes[0].id);
            }
        }
        if(cssStr) {
            document.getElementById('diy_style').innerHTML = cssStr;
        }
    },
    init: function (tpldir, tplfile, diysign) {
        this.moveableArea = $C('area', document.body, 'div');
        var divs = "";
        var len = this.moveableArea.length;
        for (var i = 0; i < len; i++) {
            var el = this.moveableArea[i];
            if (el == null || typeof el == 'undefined') return false;
            divs += el.outerHTML;
            el.innerHTML = '';
            var id = el.id;
            setInterval(function () {
                mobileDiy.setPos();
            }, 2000);
        }
        parent.$('panel').innerHTML = divs;

        if(parent.$('diy_style') && document.getElementById('diy_style')) {
            parent.$('diy_style').innerHTML = document.getElementById('diy_style').innerHTML;
        }
        if(parent.$('diyform')) {
            parent.$('diyform').template.value = tplfile;
            parent.$('diyform').tpldirectory.value = tpldir;
            parent.$('diyform').diysign.value = diysign;
            parent.$('preview_title').innerHTML = document.title;
        }
        parent.start_diy();
    },

}

function filterTextNode(list) {
	var newlist = [];
	for(var i=0; i<list.length; i++) {
		if (list[i].nodeType == 1) {
			newlist.push(list[i]);
		}
	}
	return newlist;
}

function footlink() {
	var mfootlink = document.querySelectorAll("#mfoot a");
	for (var i = 0; i < mfootlink.length; i++) {
		mfootlink[i].setAttribute("i", i);
		mfootlink[i].onclick = function() {
			setcookie('mfootlink', this.getAttribute("i"));
		}
		if(mlast !== '' && mlast != i && mfootlink[i].classList.contains('mon')) {
			mfootlink[i].classList.remove('mon');
		}
	};
	if(mlast !== '' && mfootlink[mlast]) {
		mfootlink[mlast].classList.add("mon");
	}

	if(ios) {
		document.querySelectorAll('.foot a.mon span.foot-ico img').forEach(function (obj) {
			obj.style.transform = 'translateX(-200px) translateZ(0px)';
		});
		document.querySelectorAll('.foot a.foot-post span.foot-ico img').forEach(function (obj) {
			obj.style.transform = 'translateX(-200px) translateZ(0px)';
		});
	}
}

function initdhnav(containerSelector = '#dhnavs_li', activeClass = 'mon', customOptions = {}) {
    const container = document.querySelector(containerSelector);
    if (!container) {
        console.warn('Swiper容器不存在:', containerSelector);
        return null;
    }

    const activeElement = container.querySelector('.' + activeClass);
    let initialSlide = 0;

    if (activeElement) {
        const rect = activeElement.getBoundingClientRect();
        const elementLeft = rect.left;
        const elementWidth = activeElement.offsetWidth;
        const windowWidth = window.innerWidth;

        const siblings = Array.from(container.getElementsByClassName(activeClass));
        const elementIndex = siblings.indexOf(activeElement);

        initialSlide = (elementLeft + elementWidth >= windowWidth) ? elementIndex : 0;
    }

    const swiperOptions = {
        freeMode: true,
        slidesPerView: 'auto',
        initialSlide: initialSlide,
        onTouchMove: () => { Discuz_Touch_on = 0; },
        onTouchEnd: () => { Discuz_Touch_on = 1; },
        ...customOptions
    };

    return new Swiper(containerSelector, swiperOptions);
}

function home_passwordShow(value) {
    const spanPassword = document.getElementById('span_password');
    const tbSelectgroup = document.getElementById('tb_selectgroup');
    if(value == 4) {
        spanPassword.style.display= '';
        tbSelectgroup.style.display = 'none';
    } else if(value == 2) {
        spanPassword.style.display = 'none';
        tbSelectgroup.style.display = '';
    } else {
        spanPassword.style.display = 'none';
        tbSelectgroup.style.display = 'none';
    }
}

function home_getgroup(gid) {
    if (gid) {
        const url = `home.php?mod=spacecp&ac=privacy&inajax=1&op=getgroup&gid=${encodeURIComponent(gid)}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(s => {
                const targetNames = document.getElementById('target_names');
                if (targetNames) {
                    targetNames.innerHTML += s + ',';
                } else {
                    console.warn('未找到ID为target_names的元素');
                }
            })
            .catch(error => {
                console.error('请求失败:', error);
            });
    }
}

_attachEvent(window, 'load', footlink, document);

var mlast = getcookie('mfootlink');

function showmobilecalendar(event, controlid1, addtime1, startdate1, enddate1, halfhour1, recall) {
	event && event.preventDefault();

	const controlid = controlid1;
	const addtime = addtime1 ? true : false;
	const startdate = startdate1 ? parsedate(startdate1) : false;
	const enddate = enddate1 ? parsedate(enddate1) : false;
	const today = new Date();
	const currday = controlid.value ? parsedate(controlid.value) : today;

	let selectedYear = currday.getFullYear();
	let selectedMonth = currday.getMonth();
	let selectedDay = currday.getDate();
	let selectedHour = currday.getHours();
	let selectedMinute = currday.getMinutes();
	let activeYear = selectedYear;
	let activeMonth = selectedMonth;
	let activeDay = selectedDay;
	const halfhour = halfhour1 ? true : false;
	const calendarrecall = recall ? recall : null;

	let mask = document.querySelector('.discuz-calendar-mask');
	let popup = document.querySelector('.discuz-calendar-popup');
	if (!mask) {
		const dom = createCalendarDom();
		mask = dom.mask;
		popup = dom.popup;
	}

	const title = popup.querySelector('.title');
	if (title) title.textContent = $L('select_datetime') || '选择日期时间';

	const content = popup.querySelector('.discuz-calendar-content');
	content.innerHTML = '';

	// 日期区域
	const dateSection = document.createElement('div');
	dateSection.style.marginBottom = '16px';

	// 年份导航
	const yearRow = document.createElement('div');
	yearRow.className = 'discuz-calendar-nav';

	const prevYear = document.createElement('a');
	prevYear.href = 'javascript:;';
	prevYear.className = 'nav-btn';
	prevYear.innerHTML = '&lsaquo;';
	prevYear.onclick = function() {
		selectedYear--;
		updateCalendar();
	};

	const yearDisplay = document.createElement('span');
	yearDisplay.className = 'nav-label';
	yearDisplay.textContent = selectedYear + ($L('year') || '年');

	const nextYear = document.createElement('a');
	nextYear.href = 'javascript:;';
	nextYear.className = 'nav-btn';
	nextYear.innerHTML = '&rsaquo;';
	nextYear.onclick = function() {
		selectedYear++;
		updateCalendar();
	};

	yearRow.appendChild(prevYear);
	yearRow.appendChild(yearDisplay);
	yearRow.appendChild(nextYear);
	dateSection.appendChild(yearRow);

	// 月份导航
	const monthRow = document.createElement('div');
	monthRow.className = 'discuz-calendar-nav';

	const prevMonth = document.createElement('a');
	prevMonth.href = 'javascript:;';
	prevMonth.className = 'nav-btn';
	prevMonth.innerHTML = '&lsaquo;';
	prevMonth.onclick = function() {
		selectedMonth--;
		if (selectedMonth < 0) {
			selectedMonth = 11;
			selectedYear--;
		}
		updateCalendar();
	};

	const monthDisplay = document.createElement('span');
	monthDisplay.className = 'nav-label';
	monthDisplay.textContent = (selectedMonth + 1) + ($L('month') || '月');

	const nextMonth = document.createElement('a');
	nextMonth.href = 'javascript:;';
	nextMonth.className = 'nav-btn';
	nextMonth.innerHTML = '&rsaquo;';
	nextMonth.onclick = function() {
		selectedMonth++;
		if (selectedMonth > 11) {
			selectedMonth = 0;
			selectedYear++;
		}
		updateCalendar();
	};

	monthRow.appendChild(prevMonth);
	monthRow.appendChild(monthDisplay);
	monthRow.appendChild(nextMonth);
	dateSection.appendChild(monthRow);

	// 星期标题
	const weekHeader = document.createElement('div');
	weekHeader.className = 'discuz-calendar-week';
	const weekDays = [$L('sun')||'日', $L('mon')||'一', $L('tue')||'二', $L('wed')||'三', $L('thu')||'四', $L('fri')||'五', $L('sat')||'六'];
	for (let i = 0; i < 7; i++) {
		const wd = document.createElement('span');
		wd.textContent = weekDays[i];
		weekHeader.appendChild(wd);
	}
	dateSection.appendChild(weekHeader);

	// 日期网格
	const daysGrid = document.createElement('div');
	daysGrid.className = 'discuz-calendar-days';
	dateSection.appendChild(daysGrid);
	content.appendChild(dateSection);

	// 时间选择
	if (addtime) {
		const timeSection = document.createElement('div');
		timeSection.className = 'discuz-calendar-time';

		const timeLabel = document.createElement('div');
		timeLabel.className = 'discuz-calendar-time-label';
		timeLabel.textContent = $L('select_time') || '选择时间';
		timeSection.appendChild(timeLabel);

		const timeRow = document.createElement('div');
		timeRow.className = 'discuz-calendar-time-row';

		const hourSelect = document.createElement('select');
		for (let h = 0; h < 24; h++) {
			const opt = document.createElement('option');
			opt.value = h;
			opt.textContent = zerofill(h) + ($L('hour') || '时');
			if (h == selectedHour) opt.selected = true;
			hourSelect.appendChild(opt);
		}
		hourSelect.onchange = function() {
			selectedHour = parseInt(this.value);
		};
		timeRow.appendChild(hourSelect);

		const minuteSelect = document.createElement('select');
		for (let m = 0; m < 60; m += (halfhour ? 30 : 1)) {
			const opt = document.createElement('option');
			opt.value = m;
			opt.textContent = zerofill(m) + ($L('min') || '分');
			if (m == selectedMinute) opt.selected = true;
			minuteSelect.appendChild(opt);
		}
		minuteSelect.onchange = function() {
			selectedMinute = parseInt(this.value);
		};
		timeRow.appendChild(minuteSelect);

		timeSection.appendChild(timeRow);
		content.appendChild(timeSection);
	}

	mask.style.display = 'block';
	popup.classList.add('show');
	lockBodyScroll(true);

	// 绑定按钮事件
	const cancelBtn = popup.querySelector('.btn-cancel');
	const confirmBtn = popup.querySelector('.btn-confirm');

	cancelBtn.onclick = function() {
		closeCalendar();
	};

	confirmBtn.onclick = function() {
		let result = selectedYear + '-' + zerofill(selectedMonth + 1) + '-' + zerofill(selectedDay);
		if (addtime) {
			result += ' ' + zerofill(selectedHour) + ':' + zerofill(selectedMinute);
		}
		controlid.value = result;
		if (typeof calendarrecall == 'function') {
			calendarrecall();
		} else if (calendarrecall) {
			eval(calendarrecall);
		}
		closeCalendar();
	};

	function closeCalendar() {
		popup.classList.remove('show');
		lockBodyScroll(false);
		setTimeout(() => {
			mask.style.display = 'none';
			content.innerHTML = '';
		}, 300);
	}

	function updateCalendar() {
		yearDisplay.textContent = selectedYear + ($L('year') || '年');
		monthDisplay.textContent = (selectedMonth + 1) + ($L('month') || '月');

		daysGrid.innerHTML = '';

		const firstDay = new Date(selectedYear, selectedMonth, 1);
		const startDay = firstDay.getDay();
		const daysInMonth = new Date(selectedYear, selectedMonth + 1, 0).getDate();

		for (let i = 0; i < startDay; i++) {
			const empty = document.createElement('div');
			empty.style.height = '36px';
			daysGrid.appendChild(empty);
		}

		for (let d = 1; d <= daysInMonth; d++) {
			const dayCell = document.createElement('a');
			dayCell.href = 'javascript:;';
			dayCell.className = 'discuz-calendar-day';
			dayCell.textContent = d;

			const currentDate = new Date(selectedYear, selectedMonth, d);
			const isToday = (currentDate.getFullYear() === today.getFullYear() &&
				currentDate.getMonth() === today.getMonth() &&
				currentDate.getDate() === today.getDate());
			const isSelected = (d === activeDay && selectedMonth === activeMonth && selectedYear === activeYear);
			const isExpired = ((enddate && currentDate.getTime() > enddate.getTime()) ||
				(startdate && currentDate.getTime() < startdate.getTime()));

			if (isSelected) {
				dayCell.classList.add('selected');
			} else if (isToday) {
				dayCell.classList.add('today');
			} else if (isExpired) {
				dayCell.classList.add('expired');
			}

			if (!isExpired || isSelected) {
				dayCell.onclick = (function(dayVal) {
					return function() {
						selectedDay = dayVal;
						activeYear = selectedYear;
						activeMonth = selectedMonth;
						activeDay = dayVal;
						updateCalendar();
					};
				})(d);
			}

			daysGrid.appendChild(dayCell);
		}
	}

	updateCalendar();
}

function createCalendarDom() {
	const mask = document.createElement('div');
	mask.className = 'discuz-calendar-mask';

	const popup = document.createElement('div');
	popup.className = 'discuz-calendar-popup';
	popup.innerHTML = `
		<div class="discuz-calendar-header">
			<button class="btn-cancel">${$L('cancel') || '取消'}</button>
			<div class="title">${$L('select_datetime') || '选择日期时间'}</div>
			<button class="btn-confirm">${$L('confirm') || '确定'}</button>
		</div>
		<div class="discuz-calendar-content"></div>
	`;

	document.body.appendChild(mask);
	document.body.appendChild(popup);

	mask.addEventListener('click', function() {
		popup.classList.remove('show');
		lockBodyScroll(false);
		setTimeout(() => {
			mask.style.display = 'none';
			const content = popup.querySelector('.discuz-calendar-content');
			if (content) content.innerHTML = '';
		}, 300);
	});

	popup.addEventListener('touchmove', function(e) {
		e.stopPropagation();
	}, { passive: false });

	return { mask, popup };
}

function parsedate(s) {
	/(\d+)\-(\d+)\-(\d+)\s*(\d*):?(\d*)/.exec(s);
	var m1 = (RegExp.$1 && RegExp.$1 > 1899 && RegExp.$1 < 2101) ? parseFloat(RegExp.$1) : (new Date()).getFullYear();
	var m2 = (RegExp.$2 && (RegExp.$2 > 0 && RegExp.$2 < 13)) ? parseFloat(RegExp.$2) : (new Date()).getMonth() + 1;
	var m3 = (RegExp.$3 && (RegExp.$3 > 0 && RegExp.$3 < 32)) ? parseFloat(RegExp.$3) : (new Date()).getDate();
	var m4 = (RegExp.$4 && (RegExp.$4 > -1 && RegExp.$4 < 24)) ? parseFloat(RegExp.$4) : 0;
	var m5 = (RegExp.$5 && (RegExp.$5 > -1 && RegExp.$5 < 60)) ? parseFloat(RegExp.$5) : 0;
	/(\d+)\-(\d+)\-(\d+)\s*(\d*):?(\d*)/.exec("0000-00-00 00\:00");
	return new Date(m1, m2 - 1, m3, m4, m5);
}

function zerofill(s) {
	var s = parseFloat(s.toString().replace(/(^[\s0]+)|(\s+$)/g, ''));
	s = isNaN(s) ? 0 : s;
	return (s < 10 ? '0' : '') + s.toString();
}

(function () {
	const config = {
		currentSelEl: null,
		selectedVal: '',
		selectedText: '',
		popupOpenTime: 0
	};

	function initAllSortSel(root) {
		root = root || document;
		const selList = root.querySelectorAll('select.sort_sel');
		selList.forEach(sel => {
			if (sel.closest('.sort-sel-wrap')) return;
			if (!sel.parentNode) return;
			wrapSelect(sel);
		});
	}

	function wrapSelect(sel) {
		const wrap = document.createElement('div');
		wrap.className = 'sort-sel-wrap';
		const showInput = document.createElement('div');
		showInput.className = 'sort-sel-show';
		showInput.textContent = getSelectDefaultText(sel);
		showInput.style.position = 'relative';
		showInput.style.zIndex = '10';
		showInput.style.pointerEvents = 'auto';

		showInput.addEventListener('click', function(e) {
			openWxSelect(sel);
		});

		if (sel.parentNode) {
			sel.parentNode.insertBefore(wrap, sel);
			wrap.appendChild(showInput);
			wrap.appendChild(sel);
		}
	}

	function getSelectDefaultText(sel) {
		const idx = sel.selectedIndex;
		if (idx < 0) return $L('select');
		const text = sel.options[idx].text;
		return text.trim() || $L('select');
	}

	function openWxSelect(sel) {
		config.currentSelEl = sel;
		config.selectedVal = sel.value;
		config.selectedText = getSelectDefaultText(sel);

		let mask = document.querySelector('.discuz-select-mask');
		let popup = document.querySelector('.discuz-select-popup');
		if (!mask) {
			createPopupDom();
			mask = document.querySelector('.discuz-select-mask');
			popup = document.querySelector('.discuz-select-popup');
		}

		const title = sel.getAttribute('data-title') || $L('select');
		popup.querySelector('.discuz-select-header .title').textContent = title;

		renderOptions(sel);
		mask.style.display = 'block';
		popup.classList.add('show');
		lockBodyScroll(true);
		config.popupOpenTime = Date.now();

		// 滚动到选中项（等弹窗动画完成 300ms）
		setTimeout(() => {
			scrollToSelected();
		}, 350);
	}

	function scrollToSelected() {
		const optBox = document.querySelector('.discuz-select-options');
		if (!optBox) return;
		const activeItem = optBox.querySelector('.discuz-select-option.active');
		if (activeItem) {
			const wrap = optBox.parentElement;
			const wrapRect = wrap.getBoundingClientRect();
			const itemRect = activeItem.getBoundingClientRect();
			let scrollTop = optBox.scrollTop + (itemRect.top - wrapRect.top) - (wrapRect.height / 2) + (itemRect.height / 2);
			// 限制在合法滚动范围内
			const maxScroll = optBox.scrollHeight - optBox.clientHeight;
			scrollTop = Math.max(0, Math.min(scrollTop, maxScroll));
			optBox.scrollTop = scrollTop;
		}
		updateOptionOpacity();
	}

	function createPopupDom() {
		const mask = document.createElement('div');
		mask.className = 'discuz-select-mask';
		const popup = document.createElement('div');
		popup.className = 'discuz-select-popup';
		popup.innerHTML = `
            <div class="discuz-select-header">
                <button class="btn-cancel">${$L('cancel')}</button>
                <div class="title">${$L('select')}</div>
                <button class="btn-confirm">${$L('confirm')}</button>
            </div>
            <div class="discuz-select-options-wrap">
                <div class="discuz-select-indicator"></div>
                <div class="discuz-select-options"></div>
            </div>
        `;
		document.body.appendChild(mask);
		document.body.appendChild(popup);

		mask.addEventListener('click', function(e) {
			if (popup.classList.contains('show')) {
				closePopup();
			}
		});
		popup.querySelector('.btn-cancel').addEventListener('click', closePopup);
		popup.querySelector('.btn-confirm').addEventListener('click', confirmSelect);
		popup.addEventListener('touchmove', function (e) {
			e.stopPropagation();
		}, { passive: false });

		const optBox = popup.querySelector('.discuz-select-options');
		let scrollTimer = null;
		optBox.addEventListener('scroll', function () {
			updateOptionOpacity();
			clearTimeout(scrollTimer);
			scrollTimer = setTimeout(() => {
				snapToCenter();
			}, 150);
		}, { passive: true });
	}

	function snapToCenter() {
		const optBox = document.querySelector('.discuz-select-options');
		if (!optBox) return;
		const wrap = optBox.parentElement;
		const wrapRect = wrap.getBoundingClientRect();
		const centerY = wrapRect.top + wrapRect.height / 2;
		const items = optBox.querySelectorAll('.discuz-select-option');
		let closestItem = null;
		let minDistance = Infinity;
		items.forEach(item => {
			const itemRect = item.getBoundingClientRect();
			const itemCenterY = itemRect.top + itemRect.height / 2;
			const distance = Math.abs(itemCenterY - centerY);
			if (distance < minDistance) {
				minDistance = distance;
				closestItem = item;
			}
		});
		if (closestItem) {
			config.selectedVal = closestItem.dataset.value;
			config.selectedText = closestItem.textContent.trim() || $L('select');
			const itemRect = closestItem.getBoundingClientRect();
			let scrollTop = optBox.scrollTop + (itemRect.top - wrapRect.top) - (wrapRect.height / 2) + (itemRect.height / 2);
			const maxScroll = optBox.scrollHeight - optBox.clientHeight;
			scrollTop = Math.max(0, Math.min(scrollTop, maxScroll));
			optBox.scrollTo({ top: scrollTop, behavior: 'smooth' });
			updateOptionOpacity();
		}
	}

	function renderOptions(sel) {
		const optBox = document.querySelector('.discuz-select-options');
		optBox.innerHTML = '';

		const topSpacer = document.createElement('div');
		topSpacer.className = 'discuz-select-spacer';
		optBox.appendChild(topSpacer);

		Array.from(sel.options).forEach((opt, index) => {
			const item = document.createElement('div');
			item.className = 'discuz-select-option';
			item.dataset.value = opt.value;
			item.dataset.index = index;
			item.textContent = opt.text || ' ';
			if (!item.textContent.trim()) {
				item.classList.add('empty-option');
			}
			if (opt.value === config.selectedVal) {
				item.classList.add('active');
			}
			item.addEventListener('click', function () {
				config.selectedVal = this.dataset.value;
				config.selectedText = this.textContent.trim() || $L('select');
				const wrap = optBox.parentElement;
				const wrapRect = wrap.getBoundingClientRect();
				const itemRect = this.getBoundingClientRect();
				let scrollTop = optBox.scrollTop + (itemRect.top - wrapRect.top) - (wrapRect.height / 2) + (itemRect.height / 2);
				const maxScroll = optBox.scrollHeight - optBox.clientHeight;
				scrollTop = Math.max(0, Math.min(scrollTop, maxScroll));
				optBox.scrollTo({ top: scrollTop, behavior: 'smooth' });
				setTimeout(updateOptionOpacity, 300);
			});
			optBox.appendChild(item);
		});

		const bottomSpacer = document.createElement('div');
		bottomSpacer.className = 'discuz-select-spacer';
		optBox.appendChild(bottomSpacer);

		setTimeout(updateOptionOpacity, 50);
	}

	function updateOptionOpacity() {
		const optBox = document.querySelector('.discuz-select-options');
		if (!optBox) return;
		const wrap = optBox.parentElement;
		const wrapRect = wrap.getBoundingClientRect();
		const centerY = wrapRect.top + wrapRect.height / 2;
		const items = optBox.querySelectorAll('.discuz-select-option');
		let closestItem = null;
		let minDistance = Infinity;
		items.forEach(item => {
			const itemRect = item.getBoundingClientRect();
			const itemCenterY = itemRect.top + itemRect.height / 2;
			const distance = Math.abs(itemCenterY - centerY);
			if (distance < minDistance) {
				minDistance = distance;
				closestItem = item;
			}
		});
		items.forEach(item => {
			item.classList.remove('active');
			item.classList.remove('near-active');
			item.classList.remove('far-active');
			if (item === closestItem) {
				item.classList.add('active');
			} else {
				const itemRect = item.getBoundingClientRect();
				const itemCenterY = itemRect.top + itemRect.height / 2;
				const distance = Math.abs(itemCenterY - centerY);
				if (distance < 70) {
					item.classList.add('near-active');
				} else {
					item.classList.add('far-active');
				}
			}
		});
	}

	function confirmSelect() {
		const sel = config.currentSelEl;
		if (!sel) return;
		sel.value = config.selectedVal;
		const wrap = sel.closest('.sort-sel-wrap');
		if (wrap) {
			const showInput = wrap.querySelector('.sort-sel-show');
			if (showInput) showInput.textContent = config.selectedText || $L('select');
		}
		const changeEvent = document.createEvent('HTMLEvents');
		changeEvent.initEvent('change', true, false);
		sel.dispatchEvent(changeEvent);
		closePopup();
	}

	function closePopup() {
		if (Date.now() - config.popupOpenTime < 500) {
			return;
		}
		const mask = document.querySelector('.discuz-select-mask');
		const popup = document.querySelector('.discuz-select-popup');
		if (!popup || !mask) return;
		popup.classList.remove('show');
		lockBodyScroll(false);
		setTimeout(() => {
			mask.style.display = 'none';
		}, 300);
	}

	function lockBodyScroll(lock) {
		if (lock) {
			document.body.style.overflow = 'hidden';
		} else {
			document.body.style.overflow = '';
		}
	}
	window.lockBodyScroll = lockBodyScroll;

	function onDomReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	onDomReady(function () {
		initAllSortSel();
	});

	window.initAllSortSel = initAllSortSel;
})();