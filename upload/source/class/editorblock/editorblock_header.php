<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_header {

	var $version = '2.7.7';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'header';
	var $description = 'description';
	var $filename = 'editorjs-header-with-alignment';
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
        "level": 5, // h1、h2...h6
        "text": "content" // 内容
    },
    "id": "0co08uxJK4", // 区块id
    "type": "header" // 区块类型
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
   tools_header: {
      header: {
         class: Header,
         config: {
            placeholder: '请输入标题...',
            levels: [1, 2, 3, 4, 5, 6],
            defaultLevel: 3,
            defaultAlignment: 'left'
         },
         tunes: ['anchorTune', 'hideTune']
      }
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
	margin-top: 20px;
    margin-bottom: 20px;
}
.ce-block__content,.ce-toolbar__content {
	/* max-width:calc(100% - 50px) */
	margin-left: auto;
    margin-right: auto;
}
/**
 * Plugin styles
 */
.ce-header {
  position: relative;
  padding: 1px 0px 1px 15px;
  margin: 0;
  line-height: 1.25em;
  outline: none;
  margin-bottom: 10px;
}

.ce-header p,
.ce-header div {
  padding: 0 !important;
  margin: 0 !important;
}
.ce-header::before {
	content: "";
	background-color: #3e8fe3;
	width: 6px;
	height: 100%;
	position: absolute;
	left: 0;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
}
/**
 * Styles for Plugin icon in Toolbar
 */
.ce-header__icon {
}

.ce-header[contentEditable="true"][data-placeholder]::before {
  position: absolute;
  content: attr(data-placeholder);
  color: #707684;
  font-weight: normal;
  display: none;
  cursor: text;
}

.ce-header[contentEditable="true"][data-placeholder]:empty::before {
  display: block;
}

.ce-header[contentEditable="true"][data-placeholder]:empty:focus::before {
  display: none;
}
/* FontSize */
h1.ce-header {
    font-size: 2.0em;
}
h2.ce-header {
    font-size: 1.7em;
}
h3.ce-header {
    font-size: 1.4em;
}
h4.ce-header {
    font-size: 1.15em;
}
h5.ce-header {
    font-size: 0.95em;
}
h6.ce-header {
    font-size: 0.8em;
}
/* Alignment*/
.ce-header--right {
  text-align: right;
}
.ce-header--center {
  text-align: center;
}
.ce-header--left {
  text-align: left;
}
.ce-header--justify {
  text-align: justify;
}
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
	<div class="ce-block__content" style="">
		<h{data.level} class="ce-header ce-header--{data.alignment}">{data.text}</h{data.level}>
	</div>
</div>
EOF;
	}

}