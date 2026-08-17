<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_attaches {

	var $version = '1.3.8';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'attaches';
	var $description = 'description';
	var $filename = 'attaches';
	var $copyright = '<a href="https://addon.dismall.com/developer-32563.html" target="_blank">云诺</a>';
	var $type = '2'; // 0:数据类型 1:图片类型 2:附件类型 3:视频类型 4:音频类型 5:文件类型

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
    "type" : "attaches",
    "data" : {
        "file": {
                "aid": 1,
                "remote": 0,
                "directory" => "forum",
                "url": "202312/26/151439rv17ot1mgatw1121.png",
                "size": 91,
                "name": "hero.jpg",
                "extension": "jpg"
        },
        "title": "Hero"
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
   tools_attaches: {
        attaches: {
            class: AttachesTool,
            config: {
                endpoint: 'misc.php?mod=swfupload&action=swfupload&operation=jsoneditorupload&fid='+editor_fid, // Your backend file uploader endpoint,
                field: 'Filedata',
                types: '*',
                additionalRequestData: {
                    'uid': editor_uid,
                    'hash': editor_hash,
                },
                remote_attachurl: editor_remote_attachurl,
                attachurl: editor_attachurl,
                additionalRequestHeaders: {},
                errorMessage: '文件上传失败，请重试',
                buttonText: '请选择需要上传的文件',
            },
            tunes: ['anchorTune', 'hideTune']
        },
   },
   i18n: {
       messages: {
          tools: {
            'attaches': {
        	  'Unsupported file type': '不支持的文件类型',
        	  'File has exceptions': '文件存在异常',
        	  'File size cannot exceed ': '文件大小不可超过 ',
        	  'User group does not support uploading this type of file': '用户组不支持上传该类型的文件',
        	  'Couldn’t upload attachment. Please try another.': '无法上传附件，请尝试另一个。',
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
.cdx-attaches {
  --color-line: #EFF0F1;
  --color-bg: #fff;
  --color-bg-secondary: #F8F8F8;
  --color-bg-secondary--hover: #f2f2f2;
  --color-text-secondary: #707684;
}

.cdx-attaches a {
  color: inherit;
  text-decoration: none;
  outline: none;
  cursor: pointer;
}

.cdx-attaches a:link,
.cdx-attaches a:visited,
.cdx-attaches a:hover,
.cdx-attaches a:active {
  color: inherit;
  text-decoration: none;
  outline: none;
}

  .cdx-attaches--with-file {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border: 1px solid #EFF0F1;
    border-radius: 12px;
    background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
  }

  .cdx-attaches--with-file:hover {
      border-color: #c4c8d0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

  .cdx-attaches--with-file .cdx-attaches__file-info {
      display: flex;
      flex-direction: column;
      gap: 6px;
      min-width: 0;
      flex: 1;
    }

  .cdx-attaches--with-file .cdx-attaches__file-icon {
      position: relative;
      flex-shrink: 0;
    }

  .cdx-attaches--with-file .cdx-attaches__file-icon-background {
        background-color: #333;
        width: 44px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
      }

  @supports(-webkit-mask-box-image: url('')){

  .cdx-attaches--with-file .cdx-attaches__file-icon-background {
          border-radius: 0;
          -webkit-mask-box-image: url("data:image/svg+xml,%3Csvg width=%2724%27 height=%2724%27 viewBox=%270 0 24 24%27 fill=%27none%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cpath d=%27M0 10.3872C0 1.83334 1.83334 0 10.3872 0H13.6128C22.1667 0 24 1.83334 24 10.3872V13.6128C24 22.1667 22.1667 24 13.6128 24H10.3872C1.83334 24 0 22.1667 0 13.6128V10.3872Z%27 fill=%27black%27/%3E%3C/svg%3E%0A") 48% 41% 37.9% 53.3%
      };
        }

  .cdx-attaches--with-file .cdx-attaches__file-icon-label {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background: inherit;
        text-transform: uppercase;
        line-height: 1em;
        color: #fff;
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
        font-family: ui-monospace,SFMono-Regular,SF Mono,Menlo,Consolas,Liberation Mono,monospace;
        letter-spacing: 0.03em;
      }

  .cdx-attaches--with-file .cdx-attaches__file-icon svg {
        width: 22px;
        height: 22px;
      }

  .cdx-attaches--with-file .cdx-attaches__file-icon path {
        stroke: #fff;
      }

  .cdx-attaches--with-file .cdx-attaches__size {
      color: #F8F8F8;
      font-size: 12px;
      line-height: 1.2;
    }

  .cdx-attaches--with-file .cdx-attaches__size::after {
        content: attr(data-size);
        margin-left: 0.2em;
      }

  .cdx-attaches--with-file .cdx-attaches__title {
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
      outline: none;
      font-size: 15px;
      font-weight: 500;
      line-height: 1.3;
      color: #1b1b1b;
      padding: 2px 4px;
      border-radius: 4px;
      transition: background 0.15s ease;
    }

  .cdx-attaches--with-file .cdx-attaches__title:focus {
        background: rgba(35, 131, 226, 0.08);
      }

  .cdx-attaches--with-file .cdx-attaches__title:empty::before {
      content: attr(data-placeholder);
      color: #a8aeb8;
    }

  .cdx-attaches--loading .cdx-attaches__title,
    .cdx-attaches--loading .cdx-attaches__file-icon,
    .cdx-attaches--loading .cdx-attaches__size,
    .cdx-attaches--loading .cdx-attaches__button {
      opacity: 0;
      font-size: 0;
    }

  .cdx-attaches__button {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    border-radius: 7px;
    font-weight: 500;
  }

  .cdx-attaches__button svg {
      margin-top: 0;
    }
    
    .cdx-attaches__download-button svg, .cdx-attaches__download-button .cdx-attaches__download-button-text {
        vertical-align: middle;
    }
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block ce-block--focused" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
    <div class="ce-block__content">
        <div class="cdx-block">
		<div class="cdx-attaches cdx-attaches--with-file">
			<div class="cdx-attaches__file-icon">
				<div class="cdx-attaches__file-icon-background">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.3236 8.43554L9.49533 12.1908C9.13119 12.5505 8.93118 13.043 8.9393 13.5598C8.94741 14.0767 9.163 14.5757 9.53862 14.947C9.91424 15.3182 10.4191 15.5314 10.9422 15.5397C11.4653 15.5479 11.9637 15.3504 12.3279 14.9908L16.1562 11.2355C16.8845 10.5161 17.2845 9.53123 17.2682 8.4975C17.252 7.46376 16.8208 6.46583 16.0696 5.72324C15.3184 4.98066 14.3086 4.55425 13.2624 4.53782C12.2162 4.52138 11.2193 4.91627 10.4911 5.63562L6.66277 9.39093C5.57035 10.4699 4.97032 11.9473 4.99467 13.4979C5.01903 15.0485 5.66578 16.5454 6.79264 17.6592C7.9195 18.7731 9.43417 19.4127 11.0034 19.4374C12.5727 19.462 14.068 18.8697 15.1604 17.7907L18.9887 14.0354"></path></svg>
				</div>
			</div>
			<div class="cdx-attaches__file-info">
				<div class="cdx-attaches__title" data-placeholder="{data.title}" data-empty="false">
					<a href="forum.php?mod=attachment&aid=[attach data.file.aid]" target="_self">{data.title}</a>
				</div>
			</div>
			<a class="cdx-attaches__download-button" href="forum.php?mod=attachment&aid=[attach data.file.aid]" target="_self">
				<svg t="1786936813963" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2992" width="25" height="25"><path d="M505.7 661c3.2 4.1 9.4 4.1 12.6 0l112-141.7c4.1-5.2 0.4-12.9-6.3-12.9h-74.1V168c0-4.4-3.6-8-8-8h-60c-4.4 0-8 3.6-8 8v338.3H400c-6.7 0-10.4 7.7-6.3 12.9l112 141.8z" p-id="2993"></path><path d="M878 626h-60c-4.4 0-8 3.6-8 8v154H214V634c0-4.4-3.6-8-8-8h-60c-4.4 0-8 3.6-8 8v198c0 17.7 14.3 32 32 32h684c17.7 0 32-14.3 32-32V634c0-4.4-3.6-8-8-8z" p-id="2994"></path></svg>
				<span class="cdx-attaches__download-button-text">下载附件</span>
			</a>
		</div>
	</div>
    </div>
</div>

EOF;
	}

}