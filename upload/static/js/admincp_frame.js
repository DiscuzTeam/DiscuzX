function init_darkmode() {
	var dmcookie = getcookie('darkmode');
	var dmdark = 0, dmauto = 1;
	document.querySelector('.darkmode').addEventListener('click', toggledarkmode);
	if (dmcookie && dmcookie.indexOf('a') == -1) {
		dmauto = 0;
		if (dmcookie.indexOf('d') != -1) {
			dmdark = 1;
		}
		switchdmvalue(dmdark, dmauto);
	} else {
		var colormedia = window.matchMedia('(prefers-color-scheme: dark)');
		switchdmvalue(colormedia.matches, dmauto);
		colormedia.addEventListener('change', function () {
			var dmlcookie = getcookie('darkmode');
			if (dmlcookie && dmlcookie.indexOf('a') != -1) {
				switchdmvalue(this.matches, 1);
			}
		});
	}
}

function toggledarkmode() {
	var dmcookie = getcookie('darkmode');
	var dmdark = 0, dmauto = 1;
	var colormedia = window.matchMedia('(prefers-color-scheme: dark)');
	if (dmcookie && dmcookie.indexOf('a') == -1) {
		dmauto = 0;
		if (dmcookie.indexOf('d') != -1) {
			dmdark = 1;
		}
	} else {
		dmdark = colormedia.matches ? 1 : 0;
	}
	if (dmauto) {
		dmauto = dmauto ? 0 : 1;
		dmdark = dmdark ? 0 : 1;
	} else if (colormedia.matches == dmdark) {
		dmauto = 1;
	} else {
		dmdark = dmdark ? 0 : 1;
	}
	switchdmvalue(dmdark, dmauto);
}

var _ifdark, _ifauto;

function switchdmvalue(ifdark, ifauto) {
	var dmcookie = '';
	var dmmeta = '';
	if (ifdark) {
		document.body.classList.add('st-d');
		document.body.classList.remove('st-l');
		dmcookie = 'd';
		dmmeta = 'dark';
	} else {
		document.body.classList.add('st-l');
		document.body.classList.remove('st-d');
		dmcookie = 'l';
		dmmeta = 'light';
	}
	if (ifauto) {
		document.body.classList.add('st-a');
		dmcookie += 'a';
		dmmeta = 'light dark';
	} else {
		document.body.classList.remove('st-a');
	}
	let iframe = document.getElementById('main');
	iframe.onload = function () {
		switchframedmvalue(ifdark, ifauto);
	}
	switchframedmvalue(ifdark, ifauto)
	if (getcookie('darkmode') != dmcookie) {
		setcookie('darkmode', dmcookie);
	}
	if (document.querySelector('meta[name="color-scheme"]').content != dmmeta) {
		document.querySelector('meta[name="color-scheme"]').content = dmmeta;
	}
	_ifdark = ifdark;
	_ifauto = ifauto;
}

function switchframedmvalue(ifdark, ifauto) {
	document.querySelectorAll('.ifmcontainer .mainframe').forEach(function (iframe) {
		if (!iframe.contentWindow.document.body.classList) {
			return;
		}
		if (ifdark) {
			iframe.contentWindow.document.body.classList.add('st-d');
			iframe.contentWindow.document.body.classList.remove('st-l');
		} else {
			iframe.contentWindow.document.body.classList.add('st-l');
			iframe.contentWindow.document.body.classList.remove('st-d');
		}
		if (ifauto) {
			iframe.contentWindow.document.body.classList.add('st-a');
		} else {
			iframe.contentWindow.document.body.classList.remove('st-a');
		}
	});
}

var nolocation_tmp = false;

function setnavmsg(id, cpurl) {
}

function switchplatform(platform, newwindow) {
	var currentUrl = window.location.href;
	var url = new URL(currentUrl);
	url.searchParams.set('platform', platform);
	url.searchParams.set('frames', 'yes');
	if (newwindow) {
		window.open(url.href)
		$('cpplatform').value = currentPlatform;
	} else {
		window.location.href = url.href;
	}
}

function addplatform(platform, name) {
	showMenu({'ctrlid': 'frameuinfo', 'pos': '34'});
	var safename = htmlspecialchars(name);
	document.querySelector('div.platform_box').insertAdjacentHTML('beforeend',
	    '<a href="javascript:;" platform="' + platform + '" title="' + safename + '" onclick="switchplatform(\'' + platform + '\', 0)"><i class="dzicon platform"></i><span class="platform_name">' + safename + '</span></a>')
}

function removeplatform(platform) {
	var obj = document.querySelector('div.platform_box [platform=' + platform + ']');
	obj.parentNode.removeChild(obj);
}

var _framemenu = (function () {
	var prevnav = prevtab = menunav = navt = navkey = headerST = null;

	function isleftmenu() {
		return !getcookie('admincp_oldlayout') || parseInt(document.documentElement.clientWidth) < 1200;
	}

	function switchnav(key, nolocation = false, switchheader = true) {
		if (!key || !$('header_' + key)) {
			return;
		}
		if (nolocation_tmp) {
			nolocation = nolocation_tmp;
		}
		if (prevnav && $('header_' + prevnav) && key != 'cloudaddons' && key != 'uc') {
			document.querySelectorAll('#topmenu button').forEach(function (nav) {
				navkey = nav.id.substring(7);
				if (navkey && $('header_' + navkey)) {
					if (switchheader) {
						$('header_' + navkey).className = '';
					}
					$('lm_' + navkey).className = '';
				}
			});
		}
		href = $('lm_' + key).childNodes[1].childNodes[0].childNodes[0].href;
		if (key == 'cloudaddons' || key == 'uc') {
			if (!nolocation) {
				window.open(href);
				doane();
			}
		} else {
			if (prevnav == key && isleftmenu()) {
				$('header_' + prevnav).className = '';
				$('lm_' + prevnav).className = '';
				prevnav = null;
			} else {
				$('lm_' + key).className = 'active';
				if (switchheader) {
					$('header_' + key).className = 'active';
					prevnav = key;
				}
				if (!nolocation) {
					switchtab($('lm_' + key).childNodes[1].childNodes[0].childNodes[0]);
					parent.main.location = href;
				}

				document.querySelectorAll('#leftmenu > li > ul > li > a.active').forEach(function (tab) {
					let customTitle = tab.childNodes[1].innerHTML;
					let cpurl = tab.getAttribute('href');
					$('admincpnav').innerHTML = customTitle
					    + '&nbsp;&nbsp;<a target="main" class="custommenu_addto" href="' + admincpfilename
					    + '?action=misc&operation=custommenu&do=add&title=' + customTitle + '&url=' + escape(cpurl) + '">[+]</a>';
				});
			}
		}
		if (nolocation_tmp) {
			nolocation_tmp = false;
		}
	}

	function switchtab(key) {
		if (!key || !key.href) {
			return;
		}
		if (prevtab) {
			prevtab.className = '';
		}
		key.className = 'active';
		prevtab = key;
		$('navcontainer').classList.remove('show');
	}

	function openinnewwindow(obj) {
		var href = obj.parentNode.href;
		if (obj.parentNode.href.indexOf(admincpfilename + '?') != -1) {
			href += '&frames=yes';
		}
		window.open(href);
		doane();
	}

	document.querySelectorAll('#leftmenu > li > a').forEach(function (nav) {
		nav.addEventListener('click', function () {
			nolocation = true;
			id = this.id.substring(7);
			if (id == 'cloudaddons' || id == 'uc') {
				nolocation = false;
			}
			switchnav(id, nolocation);
		});
	});
	document.querySelectorAll('#topmenu > li > a').forEach(function (nav) {
		nav.addEventListener('click', function () {
			switchnav(this.id.substring(7));
		});
	});
	document.querySelectorAll('#topmenu button').forEach(function (nav) {
		nav.addEventListener('click', function () {
			if (isleftmenu()) {
				return;
			}
			switchnav(this.id.substring(7));
		});
		nav.addEventListener('mouseover', function () {
			id = this.id.substring(7);
			headerST = setTimeout(function () {
				switchnav(id, true, false);
			}, 1000);
		});
		nav.addEventListener('mouseout', function () {
			clearTimeout(headerST);
		});
	});
	document.querySelectorAll('nav ul ul a').forEach(function (tab) {
		tab.addEventListener('click', function () {
			tabNew(tab);
		});
	});
	document.querySelectorAll('#favbar_list a').forEach(function (tab) {
		tab.addEventListener('click', function () {
			tabNew(tab);
			doane();
		});
	});
	document.querySelectorAll('#favbar_mgr a').forEach(function (tab) {
		tab.addEventListener('click', function () {
			tabNew(tab);
			doane();
		});
	});

	function tabNew(tab, tabHref) {
		var tabName = '', tabId = '', customMode = false;
		if(typeof tabHref == 'undefined') {
			tabHref = tab.href;
			tabName = tab.childNodes[1] ? tab.childNodes[1].innerHTML : tab.innerHTML;
			tabId = tab.id;
		} else {
			customMode = true;
			tabName = tab;
			tabId = 't' + Math.random(4);
		}
		switchtab(tab);
		if (!isleftmenu() || document.body.clientWidth < 575) {
			if(customMode) {
				window.open(tabHref);
				return;
			}
			let customTitle = tabName;
			let cpurl = tabHref;
			$('admincpnav').innerHTML = customTitle
			    + '&nbsp;&nbsp;<a target="main" class="custommenu_addto" href="' + admincpfilename
			    + '?action=misc&operation=custommenu&do=add&title=' + customTitle + '&url=' + escape(cpurl) + '">[+]</a>';
			$('favbars').style.display = 'none';
			$('main').src = cpurl;
			return;
		}
		$('favbars').style.display = '';
		document.querySelectorAll('.ifmcontainer .mainframe').forEach(function (tab) {
			tab.style.display = 'none';
		});
		document.querySelectorAll('.ifmcontainer #tabs a').forEach(function (a) {
			a.className = 'dragObjTab';
		});
		let frameid = tabId + '_frame';
		if ($(frameid)) {
			$(frameid).style.display = '';
			$(frameid + '_tab').className = 'dragObjTab current';
			$(frameid).src = tabHref;
			nav_middle('#tabs', 'a.current');
		} else {
			var iframeNode = document.createElement("iframe");
			iframeNode.id = frameid;
			iframeNode.className = 'mainframe';
			iframeNode.src = tabHref;
			iframeNode.onload = function () {
				switchframedmvalue(_ifdark, _ifauto);
			}
			$('main').parentNode.appendChild(iframeNode);
			var aNode = document.createElement("a");
			var xNode = document.createElement("span");
			var autoLeft = 0;
			aNode.addEventListener("contextmenu", function (e) {
				e.preventDefault();
				showContextMenu(e);
			})
			xNode.addEventListener('mousedown', function (e) {
				let newFrameid = '';
				if (xNode.parentNode.className != '') {
					if (document.querySelectorAll('[id="' + frameid + '_tab"].current').length > 0) {
						if (xNode.parentNode.nextSibling && xNode.parentNode.nextSibling.id != '') {
							newFrameid = xNode.parentNode.nextSibling.id.replace('_tab', '');
						} else if (xNode.parentNode.previousSibling) {
							newFrameid = xNode.parentNode.previousSibling.id.replace('_tab', '');
						} else {
							newFrameid = 'main';
						}
					}
				}
				if ($(frameid)) {
					$(frameid).remove();
				}
				if ($(frameid + '_tab')) {
					$(frameid + '_tab').remove();
				}
				if (newFrameid) {
					$(newFrameid).style.display = '';
					if ($(newFrameid + '_tab')) {
						$(newFrameid + '_tab').className = 'dragObjTab current';
					}
				}
				if (!document.querySelectorAll('.ifmcontainer #tabs a').length) {
					$('favbars').style.display = 'none';
				}
				doane();
			});
			aNode.innerHTML = tabName;
			aNode.appendChild(xNode);
			aNode.className = 'dragObjTab current';
			aNode.addEventListener('mousedown', function (e) {
				if (e.button == 1) {
					let event = new MouseEvent('mousedown', {
						'view': window,
						'bubbles': true,
						'cancelable': true
					});
					xNode.dispatchEvent(event);
					if (!document.querySelectorAll('.ifmcontainer #tabs a').length) {
						$('favbars').style.display = 'none';
					}
				} else if (e.button == 2) {
				} else {
					document.querySelectorAll('.ifmcontainer .mainframe').forEach(function (tab) {
						tab.style.display = 'none';
					});
					document.querySelectorAll('.ifmcontainer #tabs a').forEach(function (a) {
						a.className = 'dragObjTab';
					});
					$(frameid).style.display = '';
					$(frameid + '_tab').className = 'dragObjTab current';
				}
			});
			aNode.id = frameid + '_tab';
			aNode.draggable = 'true';
			aNode.addEventListener('dragstart', dragStartTab);
			aNode.addEventListener('dragend', dragEndTab);
			$('tabs').appendChild(aNode);

			document.querySelector('#tabs').scrollTo({
				left: document.querySelector('#tabs').scrollWidth,
				behavior: 'smooth'
			});
		}
		doane();
	}

	// 定义菜单结构
	function createContextMenu() {
		var menu = document.createElement('ul');
		menu.classList.add('context-menu');
		menu.innerHTML = `
			<li class='item' id='refresh'>` + $L('admincp_tab_refresh') + `</li>
			<li class='item' id='closeThis'>` + $L('admincp_tab_closeThis') + `</li>
			<li class='item' id='closeOther'>` + $L('admincp_tab_closeOther') + `</li>
			<li class='item' id='closeAll'>` + $L('admincp_tab_closeAll') + `</li>
			<li class='item' id='addCustom'>` + $L('admincp_tab_addCustom') + `</li>
			<li class='item' id='newWindow'>` + $L('admincp_tab_newWindow') + `</li>
		`;
		return menu;
	}

	// 显示菜单
	function showContextMenu(e) {
		var frameid = e.target.id.replace('_tab', '');
		var menu = createContextMenu();
		var top = e.clientY - 5;
		var left = e.clientX - 5;
		var menuWidth = 200;
		var leftmenuWidth = $('navcontainer').offsetWidth;
		if ((left + menuWidth) > window.innerWidth) {
			left = window.innerWidth - menuWidth - leftmenuWidth;
		}

		$('append_parent').appendChild(menu);
		menu.style.top = `${top}px`;
		menu.style.left = `${left}px`;
		menu.addEventListener('click', function (event) {
			var target = event.target;
			if (target.tagName === 'LI') {
				handleMenuClick(frameid, target);
			}
		});

		menu.addEventListener('mouseleave', hideContextMenu);
		document.addEventListener('click', hideContextMenu);
	}

	function handleMenuClick(frameid, target) {
		if (target.id == 'refresh') {
			$(frameid).setAttribute('src', $(frameid).getAttribute('src'));
		} else if (target.id == 'addCustom') {
			let customTitle = $(frameid + '_tab').innerText;
			let cpurl = $(frameid).getAttribute('src');
			ajaxget(admincpfilename + '?action=misc&operation=custommenu&do=add&title=' + customTitle + '&url=' + escape(cpurl) + '&fromFavbars=yes',
			    'favbar_list', null, '', '', function () {
				    document.querySelectorAll('#favbar_list a').forEach(function (tab) {
					    tab.addEventListener('click', function () {
						    tabNew(tab);
						    doane();
					    });
				    });
			    });
		} else if (target.id == 'closeThis') {
			xNode = $(frameid + '_tab').querySelector('span');
			let event = new MouseEvent('mousedown', {
				'view': window,
				'bubbles': true,
				'cancelable': true
			});
			xNode.dispatchEvent(event);
			if (!document.querySelectorAll('.ifmcontainer #tabs a').length) {
				$('favbars').style.display = 'none';
			}
		} else if (target.id == 'closeAll' || target.id == 'closeOther') {
			let curTabid = '';
			if (target.id == 'closeOther') {
				curTabid = frameid + '_tab';
			}
			document.querySelectorAll('.ifmcontainer #tabs a').forEach(function (a) {
				if (curTabid != '' && a.id == curTabid) {
					return;
				}
				let frameid = a.id.replace('_tab', '');
				if ($(frameid)) {
					$(frameid).remove();
				}
				if ($(frameid + '_tab')) {
					$(frameid + '_tab').remove();
				}
			});
			if (target.id == 'closeAll') {
				$('main').style.display = '';
				$('favbars').style.display = 'none';
			} else {
				aNode = $(curTabid).parentNode.querySelector('a');
				let event = new MouseEvent('mousedown', {
					'view': window,
					'bubbles': true,
					'cancelable': true
				});
				aNode.dispatchEvent(event);
			}
		} else if (target.id == 'newWindow') {
			let cpurl = $(frameid).getAttribute('src');
			window.open(cpurl);
		}
	}

	function hideContextMenu() {
		var menu = document.querySelector('.context-menu');
		if (menu) {
			$('append_parent').removeChild(menu);
			document.removeEventListener('click', hideContextMenu);
		}
	}

	function rollPage(d, obj) {
		let tabTitle = document.querySelector(obj);
		let left = tabTitle.scrollLeft;
		if (d === 'left') {
			tabTitle.scrollTo({
				left: left - 50,
				behavior: 'smooth'
			});
		} else {
			tabTitle.scrollTo({
				left: left + 50,
				behavior: 'smooth'
			});
		}
	}

	function nav_middle(nav_dom_name, cur_dom) {
		const nav_dom = document.querySelector(nav_dom_name);
		var prev_dom = Array.prototype.slice.call(nav_dom.querySelectorAll(cur_dom)).shift().previousElementSibling;
		if (prev_dom) {
			var offset_width = 0;
			var sibling = prev_dom;
			while (sibling) {
				offset_width += sibling.offsetWidth;
				sibling = sibling.previousElementSibling;
			}
			var windows_half = window.innerWidth / 3;
			var cur_half = nav_dom.querySelector(cur_dom).offsetWidth / 3;
			offset_width = offset_width - windows_half + cur_half;
			nav_dom.scrollLeft = offset_width;
		}
	}

	// 监听滚轮事件
	// 获取 tabs 容器
	var tabsContainer = document.querySelector('.ifmcontainer #tabs');

	// 绑定滚动事件
	tabsContainer.addEventListener('wheel', function (event) {
		event.preventDefault(); // 阻止默认滚动行为

		// 判断滚动方向
		if (event.deltaY > 0) {
			// 向下滚动
			rollPage("right", '#tabs');
		} else if (event.deltaY < 0) {
			// 向上滚动
			rollPage("left", '#tabs');
		}
	});
	$('favbar_list').addEventListener('wheel', function (event) {
		event.preventDefault(); // 阻止默认滚动行为

		// 判断滚动方向
		if (event.deltaY > 0) {
			// 向下滚动
			rollPage("right", '#favbar_list');
		} else if (event.deltaY < 0) {
			// 向上滚动
			rollPage("left", '#favbar_list');
		}
	});

	document.querySelectorAll('nav ul ul a > em').forEach(function (tabem) {
		tabem.addEventListener('click', function () {
			openinnewwindow(this);
		});
	});

	if (document.querySelector("form[name=search]")) {
		document.querySelector("form[name=search]").addEventListener('submit', function (event) {
			tabOnMain();
		});
	}

	$("op_refresh").addEventListener('click', function (event) {
		let curTab = document.querySelector('.ifmcontainer #tabs a.current');
		let frameid = curTab.id.replace('_tab', '');
		$(frameid).setAttribute('src', $(frameid).getAttribute('src'));
	});

	$("op_back").addEventListener('click', function (event) {
		let curTab = document.querySelector('.ifmcontainer #tabs a.current');
		let frameid = curTab.id.replace('_tab', '');
		$(frameid).contentWindow.history.back();
	});

	if ($("mitframeapps")) {
		$("mitframeapps").addEventListener('click', function (event) {
			tabOnMain();
		});
	}

	switchnav(typeof defaultNav != 'undefined' ? defaultNav : 'index', 1);
	switchtab(document.querySelector('nav ul li.active ul a.active') != null ? document.querySelector('nav ul li.active ul a.active') : document.querySelector('nav ul ul a'));
	$('cpsetting').addEventListener('click', function () {
		$('bdcontainer').classList.toggle('oldlayout');
		if (getcookie('admincp_oldlayout')) {
			$('main').src = defaultUrl;
			setcookie('admincp_oldlayout', 1, -2592000);
			$('admincpnav').style.display = 'none';
			$('tabs').style.display = '';
		} else {
			setcookie('admincp_oldlayout', 1, 2592000);
			$('admincpnav').style.display = '';
			$('tabs').style.display = 'none';

			document.querySelectorAll('.ifmcontainer #tabs a').forEach(function (a) {
				let frameid = a.id.replace('_tab', '');
				if ($(frameid)) {
					$(frameid).remove();
				}
				if ($(frameid + '_tab')) {
					$(frameid + '_tab').remove();
				}
			});
			$('main').style.display = '';
			$('favbars').style.display = 'none';
			$('admincpnav').innerHTML = '';
		}
	});
	document.querySelector('#frameuinfo > img').addEventListener('click', function () {
		document.querySelector('.mainhd').classList.toggle('toggle');
	});
	$('navbtn').addEventListener('click', function () {
		$('navcontainer').classList.add('show');
	});
	$('navcontainer').addEventListener('click', function (e) {
		if (e.target === this) {
			this.classList.remove('show')
		}
	});

	// tab拖拽部分 开始
	document.getElementById('tabs').addEventListener('dragover', dragOverTab);
	document.getElementById('tabs').addEventListener('drop', dropTab);

	function dragStartTab(e) {
		e.dataTransfer.effectAllowed = 'move';
		e.dataTransfer.setData('text/html', this.innerHTML);
		this.classList.add('draggingTab');
	}

	function dragEndTab(e) {
		this.classList.remove('draggingTab');
	}

	function dragOverTab(e) {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'move';
	}

	function dropTab(e) {
		e.preventDefault();
		const source = document.querySelector('.draggingTab');
		const target = e.target.closest('.dragObjTab');

		if (target && source !== target) {
			const sourceIndex = Array.from(source.parentNode.children).indexOf(source);
			const targetIndex = Array.from(target.parentNode.children).indexOf(target);

			if (sourceIndex < targetIndex) {
				target.parentNode.insertBefore(source, target.nextSibling);
			} else {
				target.parentNode.insertBefore(source, target);
			}
		}
	}

	// tab拖拽部分 结束

	function tabOnMain() {
		if (!isleftmenu() || document.body.clientWidth < 575) {
			return;
		}
		document.querySelectorAll('.ifmcontainer .mainframe').forEach(function (tab) {
			tab.style.display = 'none';
		});
		document.querySelectorAll('.ifmcontainer #tabs a').forEach(function (a) {
			a.classList.remove('current');
		});
		$('main').style.display = '';
		$('favbars').style.display = 'none';
	}

	if ('undefined' !== typeof defaultTab) {
		tabNew($(defaultTab));
	}

	if ($("mitframeapps")) {
		$('mitframeapps').addEventListener('click', function (e) {
			tabNew($('submn_mitframe'));
		});
	}

	return {
		tabNew: function (tab, tabHref) {
			tabNew(tab, tabHref);
		}
	}
})();

function reloadmenu(selector) {
	document.querySelectorAll(selector).forEach(function (tab) {
		tab.addEventListener('click', function () {
			parent._framemenu.tabNew(tab);
		});
	});
};

function switchColor() {
	const form = new FormData(document.querySelector('#adminColor_form'));
	const value = Object.fromEntries(new FormData(document.querySelector('#adminColor_form')));
	if(!ADMIN_COLOR_VARS) {
		return;
	}
	for(var v in ADMIN_COLOR_VARS) {
		var _v = ADMIN_COLOR_VARS[v];
		if(!value[_v]) {
			continue;
		}
		document.documentElement.style.setProperty(_v, value[_v]);
		document.querySelectorAll('.ifmcontainer .mainframe').forEach(function (iframe) {
			iframe.contentWindow.document.documentElement.style.setProperty(_v, value[_v]);
		});
	}
}

_framemenu;