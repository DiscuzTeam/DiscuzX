/**
 * [Discuz!] 统计页图表助手（echarts 5.4.3）
 *
 * 兼容约定：drawstatchart(url, height, titleOption, obj) 的签名与行为保持不变，
 * stat_misc.php（版块统计 / 管理统计）仍在调用它，不可破坏。
 * 本文件在原有 XML 折线图之上，补充：
 *   - 长时段缩放（dataZoom inside + slider）、图表类型切换（magicType）、导出图片、还原
 *   - 千分位数值格式化、空数据中文态、大数据抽稀（lttb）
 *   - 统计页通用图表：statPie / statBarH / statGauge / statLine
 *   - 统一实例登记，窗口缩放时批量 resize
 */

var STAT_CHARTS = [];
var STAT_RESIZE_TIMER = 0;
var STAT_FALLBACK = ['#2B7ACD', '#5B9BD5', '#7FB2E5', '#F2994A', '#F0A020', '#7CBE00', '#4CB0A8', '#EB5757', '#9B51E0', '#56CCF2'];
var STAT_PALETTE = [];
var STAT_INK = {};

/* 配色统一从 CSS 自定义属性读取（module.css 的 .stat-page 用主题变量定义），
   切换模板/配色后图表自动跟随；取不到时回退到 STAT_FALLBACK */
function statTheme() {
	var probe = document.querySelector('.stat-page') || document.documentElement;
	var cs = window.getComputedStyle(probe);
	function v(name, fb) {
		var val = cs.getPropertyValue(name);
		return (val && val.trim()) ? val.trim() : fb;
	}
	STAT_INK = {
		grid: v('--stat-grid', '#E8F0F7'),
		axis: v('--stat-axis', '#CDCDCD'),
		label: v('--stat-label', '#666'),
		pointer: v('--stat-pointer', '#6a7985'),
		surface: v('--stat-surface', '#FFF'),
		warn: v('--stat-warn', '#F0A020'),
		good: v('--stat-good', '#7CBE00')
	};
	STAT_PALETTE = [
		v('--stat-c1', STAT_FALLBACK[0]),
		v('--stat-c2', STAT_FALLBACK[1]),
		v('--stat-c3', STAT_FALLBACK[2]),
		v('--stat-c4', STAT_FALLBACK[3]),
		v('--stat-c5', STAT_FALLBACK[4]),
		v('--stat-c6', STAT_FALLBACK[5])
	].concat(STAT_FALLBACK.slice(6));
}
statTheme();
window.addEventListener('load', statTheme);

/* 颜色参数支持传色值或调色板序号（0 起），序号随主题自动变化 */
function statColor(c) {
	if(typeof c === 'number') {
		return STAT_PALETTE[c] || STAT_PALETTE[0];
	}
	return c || STAT_PALETTE[0];
}

/* 把主题色转成带透明度的 rgba（CSS 变量只有十六进制值，图表里又需要半透明） */
function statAlpha(c, a) {
	var m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(c);
	return m ? 'rgba(' + parseInt(m[1], 16) + ',' + parseInt(m[2], 16) + ',' + parseInt(m[3], 16) + ',' + a + ')' : c;
}

function statNum(n) {
	n = Number(n);
	if(isNaN(n)) {
		return '-';
	}
	return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/* 初始化图表并登记，供窗口缩放时统一 resize */
function statInit(el, option) {
	var chart;
	if(typeof echarts == 'undefined' || !el) {
		return null;
	}
	try {
		chart = echarts.getInstanceByDom(el) || echarts.init(el);
		chart.setOption(option, true);
	} catch(e) {
		return null;
	}
	if(STAT_CHARTS.indexOf(chart) < 0) {
		STAT_CHARTS.push(chart);
	}
	return chart;
}

/* 折线 / 面积 */
function statLine(el, xdata, series, opt) {
	opt = opt || {};
	statInit(el, {
		color: STAT_PALETTE,
		textStyle: { fontSize: 12 },
		grid: { left: 8, right: 16, top: 16, bottom: 8, containLabel: true },
		tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, valueFormatter: function(v) { return statNum(v); }, textStyle: { fontSize: 12 } },
		xAxis: { type: 'category', data: xdata, boundaryGap: !opt.line, axisLabel: { fontSize: 11 } },
		yAxis: { type: 'value', axisLabel: { fontSize: 11, formatter: function(v) { return statNum(v); } }, splitLine: { lineStyle: { type: 'dashed' } } },
		series: series
	});
}

/* 环形饼图（占比类） */
function statPie(el, data, opt) {
	opt = opt || {};
	statInit(el, {
		color: STAT_PALETTE,
		textStyle: { fontSize: 12 },
		tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)', textStyle: { fontSize: 12 } },
		legend: { bottom: 0, left: 'center', itemWidth: 10, itemHeight: 10, textStyle: { fontSize: 12 } },
		series: [{
			type: 'pie',
			radius: opt.radius || ['48%', '70%'],
			center: opt.center || ['50%', '45%'],
			avoidLabelOverlap: true,
			itemStyle: { borderColor: STAT_INK.surface, borderWidth: 2, borderRadius: 6 },
			label: { fontSize: 12, formatter: '{b}\n{d}%' },
			labelLine: { length: 8, length2: 8 },
			data: data
		}]
	});
}

/* 横向条形排行（TOP 榜） */
function statBarH(el, cats, vals, opt) {
	opt = opt || {};
	statInit(el, {
		color: [statColor(opt.color)],
		textStyle: { fontSize: 12 },
		grid: { left: 8, right: 40, top: 8, bottom: 8, containLabel: true },
		tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, valueFormatter: function(v) { return statNum(v); }, textStyle: { fontSize: 12 } },
		xAxis: { type: 'value', axisLabel: { fontSize: 11, formatter: function(v) { return statNum(v); } }, splitLine: { lineStyle: { type: 'dashed' } } },
		yAxis: {
			type: 'category', data: cats, inverse: true,
			axisTick: { show: false }, axisLine: { show: false },
			axisLabel: { fontSize: 11, width: opt.labelWidth || 96, overflow: 'truncate' }
		},
		series: [{
			type: 'bar', data: vals, barMaxWidth: 18,
			itemStyle: { borderRadius: [0, 5, 5, 0] },
			label: { show: true, position: 'right', fontSize: 11, formatter: function(p) { return statNum(p.value); } }
		}]
	});
}

/* 仪表盘（指数类） */
function statGauge(el, value, opt) {
	opt = opt || {};
	statInit(el, {
		textStyle: { fontSize: 12 },
		series: [{
			type: 'gauge', min: 0, max: opt.max || 100,
			startAngle: 210, endAngle: -30,
			radius: '92%', center: ['50%', '58%'],
			progress: { show: true, width: 12, roundCap: true, itemStyle: { color: statColor(opt.color) } },
			axisLine: { roundCap: true, lineStyle: { width: 12, color: [[0.3, STAT_PALETTE[3]], [0.6, STAT_INK.warn], [1, STAT_INK.good]] } },
			pointer: { width: 4, length: '62%', itemStyle: { color: statColor(opt.color) } },
			axisTick: { distance: -18, splitNumber: 5, lineStyle: { width: 1, color: STAT_INK.label } },
			splitLine: { distance: -20, length: 8, lineStyle: { width: 1, color: STAT_INK.label } },
			axisLabel: { distance: -6, fontSize: 11, color: STAT_INK.label },
			anchor: { show: true, size: 10, itemStyle: { color: statColor(opt.color) } },
			title: { show: !!opt.title, offsetCenter: [0, '72%'], fontSize: 12, color: STAT_INK.label },
			detail: {
				valueAnimation: true, offsetCenter: [0, '42%'], fontSize: 26, fontWeight: 700,
				color: statColor(opt.color), formatter: function(v) { return statNum(v); }
			},
			data: [{ value: Number(value) || 0, name: opt.title || '' }]
		}]
	});
}

/* 空数据态（中文，替代原英文硬编码） */
function statNoData(el, text) {
	statInit(el, {
		title: {
			text: text || '所选时间段内没有数据',
			left: 'center', top: 'middle',
			textStyle: { fontSize: 14, fontWeight: 400, color: STAT_INK.label }
		}
	});
}

/**
 * 趋势图（原函数增强版）
 * 签名不变：drawstatchart(url, height, titleOption, obj)
 */
function drawstatchart(url, height, titleOption, obj) {
	obj = obj || $('statchart');
	height = height || 400;

	var x = new Ajax();
	x.recvType = 'HTML';
	obj.style.width = '100%';
	obj.style.height = height + 'px';
	x.get(url, function (s, x) {
		var myChart = echarts.init(obj);
		option = {
			color: STAT_PALETTE,
			textStyle: { fontSize: 12 },
			grid: { left: 60, right: 20, top: 20, bottom: 52 },
			xAxis: { type: 'category', data: [], boundaryGap: false, axisLabel: { fontSize: 11 } },
			yAxis: {
				type: 'value',
				axisLabel: { fontSize: 11, formatter: function(v) { return statNum(v); } },
				splitLine: { lineStyle: { type: 'dashed' } }
			},
			tooltip: {
				trigger: 'axis',
				axisPointer: { type: 'cross', label: { backgroundColor: STAT_INK.pointer } },
				valueFormatter: function(v) { return statNum(v); },
				textStyle: { fontSize: 12 }
			},
			legend: {
				type: 'scroll', data: [], left: 60, bottom: 4,
				itemWidth: 12, itemHeight: 8, textStyle: { fontSize: 12 }
			},
			dataZoom: [
				{ type: 'inside', throttle: 50 },
				{
					type: 'slider', height: 16, bottom: 28,
					borderColor: 'transparent', backgroundColor: 'rgba(0,0,0,.03)',
					fillerColor: statAlpha(STAT_PALETTE[0], .12), handleSize: 16,
					dataBackground: { lineStyle: { color: STAT_INK.axis }, areaStyle: { color: STAT_INK.grid } }
				}
			],
			toolbox: {
				right: 16, top: 0, itemSize: 14,
				feature: {
					magicType: { type: ['line', 'bar', 'stack'], title: { line: '折线图', bar: '柱状图', stack: '堆叠' } },
					saveAsImage: { title: '保存为图片', pixelRatio: 2 },
					restore: { title: '还原' }
				}
			},
			series: [],
		};
		if(titleOption) {
			option.title = titleOption;
		}
		var rex = x.XMLHttpRequest.responseXML, reax = rex.getElementsByTagName('xaxis')[0].childNodes;
		if (!reax.length) {
			option['title'] = {
				text: '所选时间段内没有数据，请调整统计项或时间范围', padding: [10, 50],
				textAlign: 'center', textVerticalAlign: 'center',
				left: '50%', top: '50%', backgroundColor: STAT_INK.grid
			};
			delete option.dataZoom;
			delete option.toolbox;
		}
		for (var i = 0; i < reax.length; i++) {
			option.xAxis.data.push(reax[i].firstChild.nodeValue);
		}
		for (var i = 0, q = rex.getElementsByTagName('graphs')[0].childNodes; i < q.length; i++) {
			qttl = q[i].getAttribute('title');
			option.legend.data.push(qttl);
			qdata = {
				type: 'line',
				smooth: true,
				sampling: 'lttb',
				showSymbol: false,
				name: qttl,
				data: []
			};
			for (var j = 0; j < q[i].childNodes.length; j++) {
				qdata.data.push(parseInt(q[i].childNodes[j].firstChild.nodeValue));
			}
			option.series.push(qdata);

		}
		myChart.setOption(option);
		if(STAT_CHARTS.indexOf(myChart) < 0) {
			STAT_CHARTS.push(myChart);
		}
	});
}

/* 窗口缩放时统一 resize（去抖） */
if(window.addEventListener) {
	window.addEventListener('resize', function() {
		if(STAT_RESIZE_TIMER) {
			clearTimeout(STAT_RESIZE_TIMER);
		}
		STAT_RESIZE_TIMER = setTimeout(function() {
			for(var i = 0; i < STAT_CHARTS.length; i++) {
				try {
					STAT_CHARTS[i].resize();
				} catch(e) {}
			}
		}, 150);
	}, false);
}
