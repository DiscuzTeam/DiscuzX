<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_list {

	var $version = '1.0.7';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'list';
	var $description = 'description';
	var $filename = 'list';
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
        "style" : "unordered",
        "items" : [
            "This is a block-styled editor",
            "Clean output data",
            "unordered"
        ]
    },
    "id": "ZT8S70Q34G", // 区块id
    "type": "list" // 区块类型
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
   tools_list: {
      list: {
		  class: EditorjsList,
		  inlineToolbar: true,
		  config: {
			defaultStyle: 'unordered'
		  },
          tunes: ['anchorTune', 'hideTune']
	  },
   },
   i18n: {
       messages: {
          "toolNames": {
                "Ordered List": "有序列表",
                "Unordered List": "无序列表",
                "Checklist": "可勾选列表",
          },
          tools: {
            'list': {
                  'Start with': '从指定字符开始',
        	  'Counter type': '计数器类型',
        	  'Numeric': '数字',
        	  'Lower Roman': '小写罗马数字',
        	  'Upper Roman': '大写罗马数字',
        	  'Lower Alpha': '小写字母 ',
        	  'Upper Alpha': '大写字母'
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
.cdx-list {
	margin:0;
	outline:none;
	display:block;
	counter-reset:item;
	padding:6px;
}
.cdx-list__item {
	line-height:1.45em;
	display:block;
	padding-top:8px;
	margin-left: 2em;
}
.cdx-list__item-children {
	display:block;
}
.cdx-list__item [contenteditable] {
	outline:none
}
.cdx-list__item-content {
	word-break:break-word;
	white-space:pre-wrap;
	grid-area:content;
	padding-left:8px
}
.cdx-list__item:before {
	counter-increment:item;
	white-space:nowrap
}
.cdx-list-li-container {
  display: flex;
}
.cdx-list-ordered .cdx-list__item:before {
	/* content:counters(item,".",numeric) "." */
}
.cdx-list-ordered {
	list-style-type: none;
  	margin-left: -15px;
	counter-reset:item;
	font-size: 16px;
}
.cdx-list-unordered {
	font-size: 16px;
}
.cdx-list-unordered .cdx-list__item:before {
	content:"•"
}
.cdx-list-checklist .cdx-list__item:before {
	content:""
}
.cdx-list__settings .cdx-settings-button {
	width:50%
}
.cdx-list__checkbox {
	padding-top:calc((1.45em - 1.2em) / 2);
	grid-area:checkbox;
	width:1.2em;
	height:1.2em;
	display:flex;
	cursor:pointer;
	font-size: 16px;
}
.cdx-list__checkbox svg {
	opacity:0;
	height:1.2em;
	width:1.2em;
	left:-1px;
	top:-1px;
	position:absolute
}
@media (hover:hover) {
	.cdx-list__checkbox:not(.cdx-list__checkbox--no-hover):hover .cdx-list__checkbox-check svg {
	opacity:1
}
}.cdx-list__checkbox--checked-1 {
	line-height:1.45em
}
@media (hover:hover) {
	.cdx-list__checkbox--checked-1:not(.cdx-list__checkbox--checked-1--no-hover):hover .cdx-checklist__checkbox-check {
	background:#0059AB;
	border-color:#0059AB
}
}.cdx-list__checkbox--checked-1 .cdx-list__checkbox-check {
	background:#369FFF;
	border-color:#369FFF
}
.cdx-list__checkbox--checked-1 .cdx-list__checkbox-check svg {
	opacity:1
}
.cdx-list__checkbox--checked-1 .cdx-list__checkbox-check svg path {
	stroke:#fff
}
.cdx-list__checkbox--checked-1 .cdx-list__checkbox-check:before {
	opacity:0;
	visibility:visible;
	transform:scale(2.5)
}
.cdx-list__checkbox-check {
	cursor:pointer;
	display:inline-block;
	position:relative;
	margin:0 auto;
	width:1.2em;
	height:1.2em;
	box-sizing:border-box;
	border-radius:5px;
	border:1px solid #C9C9C9;
	background:#fff
}
.cdx-list__checkbox-check:before {
	content:"";
	position:absolute;
	top:0;
	right:0;
	bottom:0;
	left:0;
	border-radius:100%;
	background-color:#369FFF;
	visibility:hidden;
	pointer-events:none;
	transform:scale(1);
	transition:transform .4s ease-out,opacity .4s
}
.cdx-list__checkbox-check--disabled {
	pointer-events:none
}
.cdx-list-start-with-field {
	background:#F8F8F8;
	border:1px solid rgba(226,226,229,.2);
	border-radius:6px;
	padding:2px;
	display:grid;
	grid-template-columns:auto auto 1fr;
	grid-template-rows:auto
}
.cdx-list-start-with-field--invalid {
	background:#FFECED;
	border:1px solid #E13F3F
}
.cdx-list-start-with-field--invalid .cdx-list-start-with-field__input {
	color:#e13f3f
}
.cdx-list-start-with-field__input {
	font-size:16px;
	outline:none;
	font-weight:500;
	font-family:inherit;
	border:0;
	background:transparent;
	margin:0;
	padding:0;
	line-height:22px;
	min-width:calc(100% - 10px)
}
.cdx-list-start-with-field__input::placeholder {
	color:#797979;
	font-weight:500
}

</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block ce-block--focused" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
    <div class="ce-block__content">
    	[if data.style=ordered]
        [recursive data.items, "{\"itemTemplate\":\"<li class=\\\"cdx-list__item\\\">{levelNumber}. {content}{items}</li>\",\"containerTemplate\":\"<ol class=\\\"cdx-list-ordered\\\" style=\\\"list-style-type:none; margin-left: -15px;\\\">{items}</ol>\",\"childrenClass\":\"cdx-list__item-children\",\"counterType\":\"{data.meta.counterType}\",\"startNumber\":\"{data.meta.start}\"}"]
        [/if]
        [if data.style=unordered]
        [recursive data.items, "{\"itemTemplate\":\"<li class=\\\"cdx-list__item\\\">{content}{items}</li>\",\"containerTemplate\":\"<ul class=\\\"cdx-list-unordered\\\">{items}</ul>\",\"childrenClass\":\"cdx-list__item-children\"}"]
        [/if]
        [if data.style=checklist]
        [recursive data.items, "{\"itemTemplate\":\"<li class=\\\"cdx-list__item\\\"><div class=\\\"cdx-list-li-container\\\"><div class=\\\"cdx-list__checkbox cdx-list__checkbox--checked-{meta.checked}\\\"><span class=\\\"cdx-list__checkbox-check\\\"><svg xmlns=\\\"http://www.w3.org/2000/svg\\\" width=\\\"24\\\" height=\\\"24\\\" fill=\\\"none\\\" viewBox=\\\"0 0 24 24\\\"><path stroke=\\\"currentColor\\\" stroke-linecap=\\\"round\\\" stroke-width=\\\"2\\\" d=\\\"M7 12L10.4884 15.8372C10.5677 15.9245 10.705 15.9245 10.7844 15.8372L17 9\\\"></path></svg></span></div><div class=\\\"cdx-list__item-content\\\" contenteditable=\\\"true\\\" data-empty=\\\"false\\\">{content}</div></div>{items}</li>\",\"containerTemplate\":\"<ul class=\\\"cdx-list cdx-list-checklist\\\">{items}</ul>\",\"childrenClass\":\"cdx-list__item-children\"}"]
        [/if]
    </div>
</div>
EOF;
	}

}