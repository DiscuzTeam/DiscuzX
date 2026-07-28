<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_delimiter {

	var $version = '1.0.5';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'delimiter';
	var $description = 'description';
	var $filename = 'delimiter';
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
        "style": "line",
        "lineWidth": 25,
        "lineThickness": 2
    },
    "id": "ZT8S70Q34G", // 区块id
    "type": "delimiter" // 区块类型
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
   tools_delimiter: {
      delimiter: {
        class: Delimiter,
        config: {
                styleOptions: ['star', 'dash', 'line'],
                defaultStyle: 'star',
                lineWidthOptions: [8, 15, 25, 35, 50, 60, 100],
                defaultLineWidth: 25,
                lineThicknessOptions: [1, 2, 3, 4, 5, 6],
                defaultLineThickness: 2,
        }
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
    margin-bottom: 20px;
}
.ce-block__content,.ce-toolbar__content {
	/* max-width:calc(100% - 50px) */
	margin-left: auto;
    margin-right: auto;
}
.ce-delimiter {
	line-height: 1.6em;
	width: 100%;
	text-align: center;
	color: black;
}

/* Delimiter styles */
.ce-delimiter-star span {
	font-size: 30px;
	line-height: 65px;
	display: inline-block;
	height: 30px;
	letter-spacing: 0.2em;
	font-weight: 900;
}
.ce-delimiter-dash span {
	margin: 10px;
	display: inline-block;
	height: 30px;
	letter-spacing: 0.6em;
	font-weight: 900;
}
.ce-delimiter-line hr {
	font-size: 48px;
	border-style: solid;
	border-color: black;
	border-radius: 3px;
	margin: 0px auto;
}

/* Thickness */
.ce-delimiter-thickness-1 {
	border-width: 0.5px;
}
.ce-delimiter-thickness-2 {
	border-width: 1px;
}
.ce-delimiter-thickness-3 {
	border-width: 1.5px;
}
.ce-delimiter-thickness-4 {
	border-width: 2px;
}
.ce-delimiter-thickness-5 {
	border-width: 2.5px;
}
.ce-delimiter-thickness-6 {
	border-width: 3px;
}
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
	<div class="ce-block__content">
		<div class="ce-delimiter cdx-block ce-delimiter-{data.style}">
			[if data.style=star]
			<span>***</span>
			[/if]
			[if data.style=dash]
			<span>———</span>
			[/if]
			[if data.style=line]
			<hr class="ce-delimiter-thickness-{data.lineThickness}" style="width: {data.lineWidth}%;">
			[/if]
		</div>
	</div>
</div>
EOF;
	}

}