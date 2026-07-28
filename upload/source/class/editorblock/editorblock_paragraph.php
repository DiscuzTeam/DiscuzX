<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_paragraph {

	var $version = '1.1.8';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'paragraph';
	var $description = 'description';
	var $filename = 'paragraph';
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
    "data": {
        "alignment": "left", // 对齐方式
        "text": "content" // 内容
    },
    "id": "ZT8S70Q34G", // 区块id
    "type": "paragraph" // 区块类型
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
	// 历史配置项（废弃）：clickToShowInlineToolbar: true,
	function getConfig() {
		return <<<EOF
{
   tools_paragraph: {
      paragraph: {
         class: Paragraph,
         inlineToolbar: true,
         config: {
            placeholder: "请输入正文内容, 或点击加号添加功能区块",
            enableClearFormattingBtn: true,
            markdown: false,
            markdownRules: [
	            { 
	                regex: /\*\*(.+?)\*/g, 
	                type: 'bold', 
	                data: {}, 
	                skip: true,
	                replacement: '<b>$1</b>' 
	            },
	            { 
	                regex: /__(.+?)_/g, 
	                type: 'bold', 
	                data: {}, 
	                skip: true,
	                replacement: '<b>$1</b>' 
	            },
	            { 
	                regex: /~~(.+?)~/g, 
	                type: 'del', 
	                data: {}, 
	                skip: true,
	                replacement: '<del>$1</del>' 
	            },
	            { 
	                regex: /\*\*(.+?)\*\*/g, 
	                type: 'bold', 
	                data: {}, 
	                skip: false,
	                replacement: '<b>$1</b>' 
	            },
	            { 
	                regex: /__(.+?)__/g, 
	                type: 'bold', 
	                data: {}, 
	                skip: false,
	                replacement: '<b>$1</b>' 
	            },
	            { 
	                regex: /\*(.+?)\*/g, 
	                type: 'italic', 
	                data: {}, 
	                skip: false,
	                replacement: '<i>$1</i>' 
	            },
	            { 
	                regex: /_(.+?)_/g, 
	                type: 'italic', 
	                data: {}, 
	                skip: false,
	                replacement: '<i>$1</i>' 
	            },
	            { 
	                regex: /~~(.+?)~~/g, 
	                type: 'del', 
	                data: {}, 
	                skip: false,
	                replacement: '<del>$1</del>' 
	            },
	            { 
	                regex: /==(.+?)==/g, 
	                type: 'mark', 
	                data: {}, 
	                skip: false,
	                replacement: '<mark style="background-color: #ffe500;">$1</mark>' 
	            },
	            { 
	              regex: /`(.+?)`/g, 
	              type: 'inline-code', 
	              data: {}, 
	              skip: false,
	              replacement: '<code class="inline-code">$1</code>' 
	            },
	            { 
	                regex: /___(.+?)___/g, 
	                type: 'underline', 
	                data: {}, 
	                skip: false,
	                replacement: '<u class="cdx-underline">$1</u>' 
	            }
	    ],
            markdownSetting: [
                    {
	                trigger: '#',
	                type: 'header',
	                data: { level: 1 },
	                settoblock: true,
	                pattern: /^#{1}$/
	            },
	            {
	                trigger: '##',
	                type: 'header',
	                data: { level: 2 },
	                settoblock: true,
	                pattern: /^#{2}$/
	            },
	            {
	                trigger: '###',
	                type: 'header',
	                data: { level: 3 },
	                settoblock: true,
	                pattern: /^#{3}$/
	            },
	            {
	                trigger: '####',
	                type: 'header',
	                data: { level: 4 },
	                settoblock: true,
	                pattern: /^#{4}$/
	            },
	            {
	                trigger: '#####',
	                type: 'header',
	                data: { level: 5 },
	                settoblock: true,
	                pattern: /^#{5}$/
	            },
	            {
	                trigger: '######',
	                type: 'header',
	                data: { level: 6 },
	                settoblock: true,
	                pattern: /^#{6}$/
	            },
	            {
	                trigger: '>',
	                type: 'quote',
	                data: { caption: '' },
	                settoblock: true,
	                pattern: /^>\s*$/
	            },
	            {
	                trigger: '*',
	                type: 'list',
	                data: { style: 'unordered', items: [] },
	                settoblock: true,
	                pattern: /^\*$/
	            },
	            {
	                trigger: '1.',
	                type: 'list',
	                data: { style: 'ordered', items: [] },
	                settoblock: true,
	                pattern: /^1\.$/
	            },
	            {
	                trigger: '``',
	                type: 'codeflask',
	                data: { code: '' },
	                settoblock: true,
	                pattern: /^``$/
	            },
	            {
	                trigger: '|',
	                type: 'table',
	                data: { rows: 2, cols: 3 },
	                settoblock: true,
	                pattern: /^\|$/
	            },
	            {
	                trigger: '---',
	                type: 'delimiter',
	                data: { style: 'star', lineWidth: 100, lineThickness: 1 },
	                settoblock: false,
	                pattern: /^\-{3}$/
	            },
	            {
	                trigger: '!',
	                type: 'image',
	                data: { },
	                settoblock: true,
	                pattern: /^\!$/
	            },
	            {
	                trigger: 'ttt',
	                type: 'alert',
	                data: { message: '', type: 'primary' },
	                settoblock: true,
	                pattern: /^ttt\s*$/
	            }
            ]
         },
         tunes: ['anchorTune', 'hideTune']
      },
   }
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
.ce-paragraph {
    line-height: 1.6em;
    outline: none;
    text-indent: 2em;
    font-size: 16px;
}
.ce-paragraph--right {
    text-align: right;
}
.ce-paragraph--center {
    text-align: center;
}
.ce-paragraph--left {
    text-align: left;
}

.ce-paragraph--justify {
    text-align: justify;
}

.ce-paragraph-text-indent {
    text-align: justify;
}
.ce-paragraph[data-placeholder]:empty::before{
  content: attr(data-placeholder);
  color: #707684;
  font-weight: normal;
  opacity: 0;
}

/** Show placeholder at the first paragraph if Editor is empty */
.codex-editor--empty .ce-block:first-child .ce-paragraph[data-placeholder]:empty::before {
  opacity: 1;
}

.codex-editor--toolbox-opened .ce-block:first-child .ce-paragraph[data-placeholder]:empty::before,
.codex-editor--empty .ce-block:first-child .ce-paragraph[data-placeholder]:empty:focus::before {
  opacity: 0;
}

.ce-paragraph p:first-of-type{
    margin-top: 0;
}

.ce-paragraph p:last-of-type{
    margin-bottom: 0;
}


.svg-icon {
    width: 1em;
    height: 1em;
}

.svg-icon path,
.svg-icon polygon,
.svg-icon rect {
    fill: #4691f6;
}

.svg-icon circle {
    stroke: #4691f6;
    stroke-width: 1;
}
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block ce-block--focused" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
	<div class="ce-block__content">
		<div class="ce-paragraph cdx-block ce-paragraph--{data.alignment}">{data.text}</div>
	</div>
</div>
EOF;
	}

}