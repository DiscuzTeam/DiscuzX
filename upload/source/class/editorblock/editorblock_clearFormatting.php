<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_clearFormatting {

	var $version = '1.0.0';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'clearFormatting';
	var $description = 'description';
	var $filename = 'clear-formatting';
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
   tools_clearFormatting: {
        clearFormatting:{
	      class: ClearFormatting,
	      config: {
	        shortcut: null,
	        closeOnClick: false,
	        icon: `<svg t="1747118184839" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="11763" width="30" height="30"><path d="M382.805333 910.222222H285.696L429.226667 234.154667H184.888889l17.92-85.504h585.841778l-17.92 85.504H526.336L382.862222 910.222222z m315.164445-159.971555l-110.819556 110.136889-46.990222-49.834667 110.535111-110.478222-110.535111-110.535111 46.990222-49.834667 110.819556 110.136889 110.535111-110.136889 46.933333 49.834667-110.535111 110.535111 110.535111 110.478222-46.933333 49.834667-110.535111-110.136889z" fill="#2c2c2c" p-id="11764"></path></svg>`
	      }
	}
   },
   i18n: {
       messages: {
          toolNames: {
                'ClearFormatting': '清除格式',
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

</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
EOF;
	}

}