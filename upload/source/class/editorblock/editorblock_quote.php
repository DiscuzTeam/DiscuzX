<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_quote {

	var $version = '1.0.5';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'quote';
	var $description = 'description';
	var $filename = 'quote';
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
        "text" : "引言内容.",
        "caption" : "引言描述",
        "alignment" : "left"
    }
    "id": "ZT8S70Q34G", // 区块id
    "type": "quote" // 区块类型
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
   tools_quote: {
        quote: {
		  class: Quote,
		  inlineToolbar: true,
		  shortcut: 'CMD+SHIFT+O',
		  config: {
			quotePlaceholder: '请输入引言内容',
			captionPlaceholder: '请输入引言说明',
		  },
                  tunes: ['anchorTune', 'hideTune']
	},
   },
   i18n: {
       messages: {
          tools: {
            'quote': {
                  'Align Left': '引言说明 居左 显示',
                  'Align Center': '引言说明 居中 显示',
                  'Align Right': '引言说明 居右 显示',
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

.ce-block__content, .ce-toolbar__content {
    margin-left: auto;
    margin-right: auto;
}

.cdx-quote {
    position: relative;
    background-color: #f8f8f8;
    padding: 24px!important;
    margin: 0px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.quote-background-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0.2;
    z-index: 1;
}

.cdx-quote__text {
    min-height: 30px;
    margin-bottom: 10px;
    font-size: 16px;
    line-height: 1.5;
    color: #333;
}

.cdx-quote__caption {
    padding: 10px 20px 0px 20px;
    font-size: 14px;
    color: #777;
}

.cdx-quote__caption-left {
    text-align: left;
}

.cdx-quote__caption-center {
    text-align: center;
}

.cdx-quote__caption-right {
    text-align: right;
}

.cdx-quote [contentEditable=true][data-placeholder]::before {
    position: absolute;
    content: attr(data-placeholder);
    color: #bbb;
    font-weight: normal;
    opacity: 0;
}

.cdx-quote [contentEditable=true][data-placeholder]:empty::before {
    opacity: 1;
}

.cdx-quote [contentEditable=true][data-placeholder]:empty:focus::before {
    opacity: 0;
}

.cdx-quote-settings {
    display: flex;
}

.cdx-quote-settings .cdx-settings-button {
    width: 50%;
}
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block ce-block--focused" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
    <div class="ce-block__content">
        <blockquote class="cdx-block cdx-quote">
            <div class="cdx-input cdx-quote__text">
                {data.text}
            </div>
            <div class="cdx-input cdx-quote__caption cdx-quote__caption-{data.alignment}">
                {data.caption}
            </div>
            <div class="quote-background-icon">
                <svg t="1747277238703" class="icon" viewBox="0 0 1126 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6647" width="60" height="60"><path d="M809.07988966 473.917355c33.85124 8.46280999 50.77686 25.38843 50.77685999 50.77686 0 42.31404999-42.31405 126.942149-135.404959 262.347107-33.85124 50.77686-50.77686 93.090909-50.77686 135.404959 0 67.70247899 25.38843 101.553719 84.6281 101.553719 33.85124001 0 67.702479-25.38843 110.016529-76.165289 169.256198-211.570248 253.884298-406.214876 253.88429699-600.859504 0-93.090909-25.38843-177.719008-76.16528899-236.95867799-50.77686-67.702479-118.479339-93.090909-194.64462799-93.09090901-59.23966901 0-118.479339 25.38843-160.79338901 67.702479S622.89807165 186.18181799 622.89807164 253.884298c8.46281 110.016529 67.702479 186.18181801 186.18181802 220.033057m-609.32231401 0c33.85124 8.46280999 50.77686 25.38843 50.77686 50.77686 0 42.31404999-42.31405 126.942149-135.404959 262.347107-33.85124 50.77686-50.77686 93.090909-50.77686 135.404959 0 67.70247899 25.38843 101.553719 84.6281 101.553719 33.85124001 0 67.702479-25.38843 110.016529-76.165289 169.256198-211.570248 253.884298-414.677686 253.88429701-600.859504 0-93.090909-25.38843-177.719008-76.16528901-236.958678-50.77686-67.702479-118.479339-93.090909-194.644628-93.090909-59.23966901 0-118.479339 25.38843-160.793389 67.702479s-67.70247899 101.553719-67.702479 169.256199c16.92562 110.016529 76.165289 186.181818 186.181818 220.033057" fill="#bfbfbf" p-id="6648"></path></svg>
            </div>
        </blockquote>
    </div>
</div>
EOF;
	}

}