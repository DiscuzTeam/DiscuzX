<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_video {

	var $version = '1.1.7';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'video';
	var $description = 'description';
	var $filename = 'video';
	var $copyright = '<a href="https://addon.dismall.com/developer-32563.html" target="_blank">云诺</a>';
	var $type = '3'; // 0:数据类型 1:图片类型 2:附件类型 3:视频类型 4:音频类型 5:文件类型

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
            "id": "ls9YCBJ7V7",
            "type": "video",
            "data": {
                "file": {
                    "aid": 1,
                    "remote": 0,
                    "directory" => "forum",
                    "url": "202312/26/151439rv17ot1mgatw1121.mp4"
                },
                "caption": "desc",
                "withBorder": false,
                "stretched": false,
                "withBackground": false
            }
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
   tools_video: {
        video: {
            class: VideoTool,
            config: {
                endpoints: {
                    byFile: 'misc.php?mod=swfupload&action=swfupload&operation=jsoneditorupload&fid='+editor_fid, // Your backend file uploader endpoint
                    byUrl: 'misc.php?mod=swfupload&action=swfupload&operation=jsoneditorupload&fid='+editor_fid, // Your endpoint that provides uploading by Url
                },
                field: 'Filedata',
                types: 'video/*',
                additionalRequestData: {
                    'uid': editor_uid,
                    'hash': editor_hash,
                },
                remote_attachurl: editor_remote_attachurl,
                attachurl: editor_attachurl,
                captionPlaceholder: '描述信息',
                buttonContent: '请选择需要上传的视频（MP4）',
            },
            tunes: ['anchorTune', 'hideTune']
        },
   },
   i18n: {
       messages: {
          tools: {
            'video': {
                  'Add border': '添加边框',
        	  'Stretch': '横向平铺',
        	  'Add background': '添加背景色',
        	  'Autoplay': '自动播放',
        	  'Mute': '静音播放',
        	  'Controls': '视频控制',
        	  'Loop': '循环播放',
        	  'Unsupported file type': '不支持的文件类型',
        	  'File has exceptions': '文件存在异常',
        	  'File size cannot exceed ': '文件大小不可超过 ',
        	  'User group does not support uploading this type of file': '用户组不支持上传该类型的文件',
        	  'Couldn’t upload video. Please try another.': '无法上传视频，请尝试另一个。',
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
.video-tool {
  --bg-color: #cdd1e0;
  --front-color: #388ae5;
  --border-color: #e8e8eb;

}

  .video-tool__video {
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 10px;
  }

  .video-tool__video-picture {
      max-width: 100%;
      vertical-align: bottom;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

  .video-tool__video-preloader {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-size: cover;
      margin: auto;
      position: relative;
      background-color: #cdd1e0;
      background-position: center center;
    }

  .video-tool__video-preloader::after {
        content: "";
        position: absolute;
        z-index: 3;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid #cdd1e0;
        border-top-color: #388ae5;
        left: 50%;
        top: 50%;
        margin-top: -30px;
        margin-left: -30px;
        animation: video-preloader-spin 2s infinite linear;
        box-sizing: border-box;
      }

  .video-tool__caption[contentEditable="true"][data-placeholder]::before {
      position: absolute !important;
      content: attr(data-placeholder);
      color: #707684;
      font-weight: normal;
      display: none;
    }

  .video-tool__caption[contentEditable="true"][data-placeholder]:empty::before {
        display: block;
      }

  .video-tool__caption[contentEditable="true"][data-placeholder]:empty:focus::before {
        display: none;
      }

  .video-tool--empty .video-tool__video {
      display: none;
    }

  .video-tool--empty .video-tool__caption, .video-tool--loading .video-tool__caption {
      display: none;
    }

  .video-tool .cdx-button {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .video-tool .cdx-button svg {
      height: auto;
      margin: 0 6px 0 0;
    }

  .video-tool--filled .cdx-button {
      display: none;
    }

  .video-tool--filled .video-tool__video-preloader {
        display: none;
      }

  .video-tool--loading .video-tool__video {
      min-height: 200px;
      display: flex;
      border: 1px solid #e8e8eb;
      background-color: #fff;
    }

  .video-tool--loading .video-tool__video-picture {
        display: none;
      }

  .video-tool--loading .cdx-button {
      display: none;
    }

  /**
   * Tunes
   * ----------------
   */

  .video-tool--withBorder .video-tool__video {
      border: 1px solid #e8e8eb;
    }

  .video-tool--withBackground .video-tool__video {
      padding: 15px;
      background: #cdd1e0;
    }

  .video-tool--withBackground .video-tool__video-picture {
        max-width: 60%;
        margin: 0 auto;
      }

  .video-tool--stretched .video-tool__video-picture {
        width: 100%;
      }

  .video-tool__caption {
		text-align: center;
		font-size: 14px;
		color: #a3a3a3;
	}
@keyframes video-preloader-spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.video-tool__video .media_tips {
	margin: 1px 0;
}
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block ce-block--focused" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
    <div class="ce-block__content">
        <div class="cdx-block video-tool video-tool--filled [if data.withBorder=1]video-tool--withBorder[/if] [if data.stretched=1]video-tool--stretched[/if] [if data.withBackground=1]video-tool--withBackground[/if]">
            <div class="video-tool__video">
                <div class="video-tool__video-preloader" style=""></div>
                <video class="video-tool__video-picture" src="[url data.file.url,data.file.remote,data.file.directory]" type="video/mp4" [if data.autoplay=1]autoplay[/if] [if data.loop=1]loop[/if] [if data.muted=1]muted[/if] [if data.controls=1]controls[/if] title="{data.caption}" alt="{data.caption}" />
            </div>
            <div class="cdx-input video-tool__caption" data-placeholder="{data.caption}">{data.caption}</div>
        </div>
    </div>
</div>
EOF;
	}

}