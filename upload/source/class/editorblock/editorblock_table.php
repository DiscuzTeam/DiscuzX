<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_table {

	var $version = '1.0.3';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'table';
	var $description = 'description';
	var $filename = 'table';
	var $copyright = '<a href="https://addon.dismall.com/developer-32563.html" target="_blank">云诺</a>';
	var $type = '0'; // 0:数据类型 1:图片类型 2:附件类型 3:视频类型 4:音频类型 5:文件类型

	function __construct() {

	}

	function getsetting() {
		global $_G;
		$settings = [];
		return $settings;
	}

	function setsetting(&$blocknew, &$parameters) {
	}

	function getParameter() {
		return <<<EOF
{
	  "data" : {
		"withHeadings": true,
		"content" : [ [ "Kine", "Pigs", "Chicken" ], [ "1 pcs", "3 pcs", "12 pcs" ], [ "100$", "200$", "150$" ] ]
	  },
      "id": "ZT8S70Q34G", // 区块id
      "type": "table" // 区块类型
}
EOF;
	}

	/*
	 * 结构(左顶头)：
	 * 	{
	 * 		tools_$identifier: {
	 * 			$identifier: {
	 * 				...
	 * 			}
	 * 		}
	 * 	}
	 */
	function getConfig() {
		return <<<EOF
{
   tools_table: {
      table: {
		  class: Table,
		  inlineToolbar: true,
		  config: {
			rows: 2,
			cols: 3,
		  },
          tunes: ['anchorTune', 'hideTune']
	  },
   },
   i18n: {
       messages: {
          tools: {
            'table': {
                  'With headings': '带标题',
                  'Without headings': '不带标题',
        	  'Stretch': '拉伸表格',
        	  'Collapse': '取消拉伸',
            }
          }
        },
    },
}
EOF;
	}


	function getI18n() {
		return <<<EOF

EOF;
	}

	function getStyle() {
		return <<<EOF
<style type="text/css">
.ce-block {
    margin-bottom: 20px;
}
.ce-block__content,.ce-toolbar__content {
	/* max-width:calc(100% - 50px) */
	margin-left: auto;
    margin-right: auto;
}
.tc-wrap {
	--color-background:#f9f9fb;
	--color-text-secondary:#7b7e89;
	--color-border:#e8e8eb;
	--cell-size:0px;
	--toolbox-icon-size:18px;
	--toolbox-padding:6px;
	--toolbox-aiming-field-size:calc(var(--toolbox-icon-size) + var(--toolbox-padding)*2);
	border:1px solid var(--color-border);
	position:relative;
	height:100%;
	width:100%;
	margin-top:var(--toolbox-icon-size);
	box-sizing:border-box;
	display:grid;
	grid-template-columns:calc(100% - var(--cell-size)) var(--cell-size);
}
.tc-wrap--readonly {
	grid-template-columns:100% var(--cell-size)
}
.tc-wrap svg {
	vertical-align:top
}
@media print {
	.tc-wrap {
	border-left-color:var(--color-border);
	border-left-style:solid;
	border-left-width:1px;
	grid-template-columns:100% var(--cell-size)
}
}@media print {
	.tc-wrap .tc-row:after {
	display:none
}
}.tc-table {
	position:relative;
	width:100%;
	height:100%;
	display:grid;
	font-size:14px;
	line-height:1.4;
}
.tc-table:after {
	width:calc(var(--cell-size));
	height:100%;
	left:calc(var(--cell-size)*-1);
	top:0
}
.tc-table:after,.tc-table:before {
	position:absolute;
	content:""
}
.tc-table:before {
	width:100%;
	height:var(--toolbox-aiming-field-size);
	top:calc(var(--toolbox-aiming-field-size)*-1);
	left:0
}
.tc-table--heading .tc-row:first-child {
	font-weight:600;
	border-bottom:2px solid var(--color-border);
}
.tc-table--heading .tc-row:first-child [contenteditable]:empty:before {
	content:attr(heading);
	color:var(--color-text-secondary)
}
.tc-table--heading .tc-row:first-child:after {
	bottom:-2px;
	border-bottom:2px solid var(--color-border)
}
.tc-add-column,.tc-add-row {
	display:flex;
	color:var(--color-text-secondary)
}
@media print {
	.tc-add {
	display:none
}
}.tc-add-column {
	padding:4px 0;
	justify-content:center;
	border-top:1px solid var(--color-border);
}
@media print {
	.tc-add-column {
	display:none
}
}.tc-add-row {
	height:var(--cell-size);
	align-items:center;
	padding-left:4px;
	position:relative;
}
.tc-add-row:before {
	content:"";
	position:absolute;
	right:calc(var(--cell-size)*-1);
	width:var(--cell-size);
	height:100%
}
@media print {
	.tc-add-row {
	display:none
}
}.tc-add-column,.tc-add-row {
	transition:0s;
	cursor:pointer;
	will-change:background-color;
}
.tc-add-column:hover,.tc-add-row:hover {
	transition:background-color .1s ease;
	background-color:var(--color-background)
}
.tc-add-row {
	margin-top:1px;
}
.tc-add-row:hover:before {
	transition:.1s;
	background-color:var(--color-background)
}
.tc-row {
	display:grid;
	grid-template-columns:repeat(auto-fit,minmax(10px,1fr));
	position:relative;
	border-bottom:1px solid var(--color-border);
}
.tc-row:after {
	content:"";
	pointer-events:none;
	position:absolute;
	width:var(--cell-size);
	height:100%;
	bottom:-1px;
	right:calc(var(--cell-size)*-1);
	border-bottom:1px solid var(--color-border)
}
.tc-row--selected {
	background:var(--color-background)
}
.tc-row--selected:after {
	background:var(--color-background)
}
.tc-cell {
	border-right:1px solid var(--color-border);
	padding:6px 12px;
	overflow:hidden;
	outline:none;
	line-break:normal;
}
.tc-cell--selected {
	background:var(--color-background)
}
.tc-wrap--readonly .tc-row:after {
	display:none
}
.tc-toolbox {
	--toolbox-padding:6px;
	--popover-margin:30px;
	--toggler-click-zone-size:30px;
	--toggler-dots-color:#7b7e89;
	--toggler-dots-color-hovered:#1d202b;
	position:absolute;
	cursor:pointer;
	z-index:1;
	opacity:0;
	transition:opacity .1s;
	will-change:left,opacity;
}
.tc-toolbox--column {
	top:calc(var(--toggler-click-zone-size)*-1);
	transform:translateX(calc(var(--toggler-click-zone-size)*-1/2));
	will-change:left,opacity
}
.tc-toolbox--row {
	left:calc(var(--popover-margin)*-1);
	transform:translateY(calc(var(--toggler-click-zone-size)*-1/2));
	margin-top:-1px;
	will-change:top,opacity
}
.tc-toolbox--showed {
	opacity:1
}
.tc-toolbox .tc-popover {
	position:absolute;
	top:0;
	left:var(--popover-margin)
}
.tc-toolbox__toggler {
	display:flex;
	align-items:center;
	justify-content:center;
	width:var(--toggler-click-zone-size);
	height:var(--toggler-click-zone-size);
	color:var(--toggler-dots-color);
	opacity:0;
	transition:opacity .15s ease;
	will-change:opacity;
}
.tc-toolbox__toggler:hover {
	color:var(--toggler-dots-color-hovered)
}
.tc-toolbox__toggler svg {
	fill:currentColor
}
.tc-wrap:hover .tc-toolbox__toggler {
	opacity:1
}
.tc-settings .cdx-settings-button {
	width:50%;
	margin:0
}
.tc-popover {
	--color-border:#eaeaea;
	--color-background:#fff;
	--color-background-hover:rgba(232,232,235,0.49);
	--color-background-confirm:#e24a4a;
	--color-background-confirm-hover:#d54040;
	--color-text-confirm:#fff;
	background:var(--color-background);
	border:1px solid var(--color-border);
	box-shadow:0 3px 15px -3px rgba(13,20,33,.13);
	border-radius:6px;
	padding:6px;
	display:none;
	will-change:opacity,transform;
}
.tc-popover--opened {
	display:block;
	animation:menuShowing .1s cubic-bezier(.215,.61,.355,1) forwards
}
.tc-popover__item {
	display:flex;
	align-items:center;
	padding:2px 14px 2px 2px;
	border-radius:5px;
	cursor:pointer;
	white-space:nowrap;
	-webkit-user-select:none;
	-moz-user-select:none;
	user-select:none;
}
.tc-popover__item:hover {
	background:var(--color-background-hover)
}
.tc-popover__item:not(:last-of-type) {
	margin-bottom:2px
}
.tc-popover__item-icon {
	display:inline-flex;
	width:26px;
	height:26px;
	align-items:center;
	justify-content:center;
	background:var(--color-background);
	border-radius:5px;
	border:1px solid var(--color-border);
	margin-right:8px
}
.tc-popover__item-label {
	line-height:22px;
	font-size:14px;
	font-weight:500
}
.tc-popover__item--confirm {
	background:var(--color-background-confirm);
	color:var(--color-text-confirm);
}
.tc-popover__item--confirm:hover {
	background-color:var(--color-background-confirm-hover)
}
.tc-popover__item--confirm .tc-popover__item-icon {
	background:var(--color-background-confirm);
	border-color:rgba(0,0,0,.1);
}
.tc-popover__item--confirm .tc-popover__item-icon svg {
	transition:transform .2s ease-in;
	transform:rotate(90deg) scale(1.2)
}
.tc-popover__item--hidden {
	display:none
}
@keyframes menuShowing {
	0% {
	opacity:0;
	transform:translateY(-8px) scale(.9)
}
70% {
	opacity:1;
	transform:translateY(2px)
}
to {
	transform:translateY(0)
}
}
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
    <div class="ce-block__content">
        <div class="cdx-block">
            <div class="tc-wrap">
                <div class="tc-table">
                [loop data.content]
                    <div class="tc-row">
                    	[loopobject null]
                        <div class="tc-cell">{loopobjectdata}</div>
                        [/loopobject]
                    </div>
                [/loop]
                </div>
            </div>
        </div>
    </div>
</div>
EOF;
	}

}