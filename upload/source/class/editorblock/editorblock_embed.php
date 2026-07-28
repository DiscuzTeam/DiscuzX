<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_embed {

	var $version = '1.0.3';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 0; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'embed';
	var $description = 'description';
	var $filename = 'embed';
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
            "id": "bLe0VYSDy8",
            "data": {
                "embed": "//player.bilibili.com/player.html?bvid=BV1LC4y1e7Rj",
                "width": 600,
                "height": 300,
                "source": "https://www.bilibili.com/video/BV1LC4y1e7Rj/?spm_id_from=333.1073.channel.secondary_floor_video.click",
                "caption": "",
                "service": "bilibili"
            },
            "type": "embed",
            "tunes": {}
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
   tools_embed: {
      embed: {
         class: Embed,
         inlineToolbar: true,
         config: {
         services: {
                      youtube: true,
                      twitter: true,
                      coub: true,
                      codepen: true,
                      github: true,
                      bilibili: {
                              regex: /https?:\/\/www\.bilibili\.com\/video\/([^\/\?\&]*)\/?(.*)/,
                              embedUrl: '//player.bilibili.com/player.html?aid=&bvid=<%= remote_id %>&cid=&p=1',
                              html: "<iframe height='300' scrolling='no' frameborder='no' allowtransparency='true' allowfullscreen='true' style='width: 100%;'></iframe>",
                                                height: 500,
                                                id: (ids) => {
                                  return ids[0];
                                },
                        },
                        youku: {
                              regex: /https?:\/\/v\.youku\.com\/v_show\/id_([^\/\?\&]*).html(.*)/,
                              embedUrl: 'https://player.youku.com/embed/<%= remote_id %>',
                              html: "<iframe height='300' scrolling='no' frameborder='no' allowtransparency='true' allowfullscreen='true' style='width: 100%;'></iframe>",
                                                height: 500,
                                                id: (ids) => {
                                  return ids[0];
                                },
                         },
                         qq: {
                              regex: /https?:\/\/v\.qq\.com\/x\/cover\/([^\/\?\&]*)\/([^\/\?\&]*).html(.*)/,
                              embedUrl: 'https://v.qq.com/txp/iframe/player.html?vid=<%= remote_id %>',
                              html: "<iframe height='300' scrolling='no' frameborder='no' allowtransparency='true' allowfullscreen='true' style='width: 100%;'></iframe>",
                                                height: 500,
                                                id: (ids) => {
                                  return ids[1];
                                },
                          },
                          acfun: {
                              regex: /https?:\/\/(www.|)acfun.(cn|tv)\/v\/ac(\d+)/i,
                              embedUrl: 'https://www.acfun.cn/player/ac<%= remote_id %>',
                              html: "<iframe height='300' scrolling='no' frameborder='no' allowtransparency='true' allowfullscreen='true' style='width: 100%;'></iframe>",
                                                height: 500,
                                                id: (ids) => {
                                  return ids[2];
                                },
                          }
                          
            }
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
.embed-tool--loading .embed-tool__caption {
	display:none
}
.embed-tool--loading .embed-tool__preloader {
	display:block
}
.embed-tool--loading .embed-tool__content {
	display:none
}
.embed-tool__preloader {
	display:none;
	position:relative;
	height:200px;
	box-sizing:border-box;
	border-radius:5px;
	border:1px solid #e6e9eb
}
.embed-tool__preloader:before {
	content:"";
	position:absolute;
	z-index:3;
	left:50%;
	top:50%;
	width:30px;
	height:30px;
	margin-top:-25px;
	margin-left:-15px;
	border-radius:50%;
	border:2px solid #cdd1e0;
	border-top-color:#388ae5;
	box-sizing:border-box;
	animation:embed-preloader-spin 2s infinite linear
}
.embed-tool__url {
	position:absolute;
	bottom:20px;
	left:50%;
	transform:translate(-50%);
	max-width:250px;
	color:#7b7e89;
	font-size:11px;
	white-space:nowrap;
	overflow:hidden;
	text-overflow:ellipsis
}
.embed-tool__content {
	width:100%
}
.embed-tool__caption {
	margin-top:7px;
		text-align: center;
		font-size: 14px;
		color: #a3a3a3;
	}
.embed-tool__caption[contentEditable=true][data-placeholder]:before {
	position:absolute;
	content:attr(data-placeholder);
	color:#707684;
	font-weight:400;
	opacity:0
}
.embed-tool__caption[contentEditable=true][data-placeholder]:empty:before {
	opacity:1
}
.embed-tool__caption[contentEditable=true][data-placeholder]:empty:focus:before {
	opacity:0
}
@keyframes embed-preloader-spin {
	0% {
	transform:rotate(0)
}
to {
	transform:rotate(360deg)
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
        <div class="cdx-block embed-tool">
            <preloader class="embed-tool__preloader">
                <div class="embed-tool__url">
                    {data.source}
                </div>
            </preloader>
            <iframe height="{data.height}" scrolling="no" frameborder="no" allowtransparency="true" allowfullscreen="true"
                    style="width: {data.width};" src="{data.embed}"
                    class="embed-tool__content"></iframe>
            <div class="cdx-input embed-tool__caption" data-placeholder="{data.caption}">{data.caption}</div>
        </div>
    </div>
</div>
EOF;
	}

}