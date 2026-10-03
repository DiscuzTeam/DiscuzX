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
var STAT_PALETTE = ['#2B7ACD', '#5B9BD5', '#7FB2E5', '#F2994A', '#F0A020', '#7CBE00', '#4CB0A8', '#EB5757', '#9B51E0', '#56CCF2'];

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
			itemStyle: { borderColor: '#FFF', borderWidth: 2, borderRadius: 6 },
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
		color: [opt.color || STAT_PALETTE[0]],
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
			progress: { show: true, width: 12, roundCap: true, itemStyle: { color: opt.color || STAT_PALETTE[0] } },
			axisLine: { roundCap: true, lineStyle: { width: 12, color: [[0.3, '#EB5757'], [0.6, '#F2994A'], [1, '#7CBE00']] } },
			pointer: { width: 4, length: '62%', itemStyle: { color: opt.color || STAT_PALETTE[0] } },
			axisTick: { distance: -18, splitNumber: 5, lineStyle: { width: 1, color: '#999' } },
			splitLine: { distance: -20, length: 8, lineStyle: { width: 1, color: '#999' } },
			axisLabel: { distance: -6, fontSize: 11, color: '#999' },
			anchor: { show: true, size: 10, itemStyle: { color: opt.color || STAT_PALETTE[0] } },
			title: { show: !!opt.title, offsetCenter: [0, '72%'], fontSize: 12, color: '#999' },
			detail: {
				valueAnimation: true, offsetCenter: [0, '42%'], fontSize: 26, fontWeight: 700,
				color: opt.color || STAT_PALETTE[0], formatter: function(v) { return statNum(v); }
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
			textStyle: { fontSize: 14, fontWeight: 400, color: '#999' }
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
				axisPointer: { type: 'cross', label: { backgroundColor: '#6a7985' } },
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
					fillerColor: 'rgba(43,122,205,.12)', handleSize: 16,
					dataBackground: { lineStyle: { color: '#C2D5E3' }, areaStyle: { color: '#E8F0F7' } }
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
				left: '50%', top: '50%', backgroundColor: '#e8f0f7'
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
