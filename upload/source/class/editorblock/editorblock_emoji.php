<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_emoji {

	var $version = '1.1.5';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 0; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'emoji';
	var $description = 'description';
	var $filename = 'emoji';
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
	 * 		emojis_$identifier: {
	 * 			$identifier: {
	 * 				...
	 * 			}
	 * 		}
	 * 	}
	 */
	function getConfig() {
		return <<<EOF
{
   tools_emoji: {
	emoji: {
		class: EmojiInlineTool,
		config: {
		    'editorid': 'editorjs',
		    'title': '插入Emoji',
		    'defaultLocale': 'zh-CN',
		    'locales': {
		      'zh-CN': { name: '简体中文', status: true },
		      'zh-TW': { name: '繁體中文', status: true },
		      'en': { name: 'English', status: true },
		      'demo': { name: 'Demo语言', status: false }
		    },
		    i18n: {
		       messages: {
		          'demo': {
			        categories: {
			          'smileys-emotion': 'Demo 表情与情感',
			          'people-body': 'Demo 人物与身体',
			          'animals-nature': 'Demo 动物与自然',
			          'food-drink': 'Demo 食物与饮料',
			          'travel-places': 'Demo 旅行与地点',
			          'activities': 'Demo 活动',
			          'objects': 'Demo 物品',
			          'symbols': 'Demo 符号',
			          'flags': 'Demo 旗帜'
			        },
			        skinTones: {
			          'default': 'Demo 默认',
			          'light': 'Demo 浅色',
			          'medium-light': 'Demo 中浅色',
			          'medium': 'Demo 中等',
			          'medium-dark': 'Demo 中深色',
			          'dark': 'Demo 深色'
			        },
			        statusMessages: {
			          loading: 'Demo 加载中...',
			          noEmoji: 'Demo 该分类下没有表情'
			        }
			  },
		       },
		    },
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
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
EOF;
	}

}