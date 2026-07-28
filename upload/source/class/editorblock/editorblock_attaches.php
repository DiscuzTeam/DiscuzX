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

	var $version = '1.3.3';
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

  .cdx-attaches--with-file {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    border: 1px solid #EFF0F1;
    border-radius: 7px;
    background: #fff;
  }

  .cdx-attaches--with-file .cdx-attaches__file-info {
      display: grid;
      grid-gap: 4px;
      max-width: calc(100% - 80px);
      margin: auto 0;
      flex-grow: 2;
    }

  .cdx-attaches--with-file .cdx-attaches__download-button {
      display: flex;
      align-items: center;
      background: #F8F8F8;
      padding: 6px;
      border-radius: 6px;
      margin: auto 0 auto auto;
    }

  .cdx-attaches--with-file .cdx-attaches__download-button:hover {
        background: #f2f2f2;
      }

  .cdx-attaches--with-file .cdx-attaches__download-button svg {
        width: 20px;
        height: 20px;
        fill: none;
      }

  .cdx-attaches--with-file .cdx-attaches__file-icon {
      position: relative;
    }

  .cdx-attaches--with-file .cdx-attaches__file-icon-background {
        background-color: #333;

        width: 27px;
        height: 30px;
        margin-right: 12px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
      }

  @supports(-webkit-mask-box-image: url('')){

  .cdx-attaches--with-file .cdx-attaches__file-icon-background {
          border-radius: 0;
          -webkit-mask-box-image: url("data:image/svg+xml,%3Csvg width=%2724%27 height=%2724%27 viewBox=%270 0 24 24%27 fill=%27none%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cpath d=%27M0 10.3872C0 1.83334 1.83334 0 10.3872 0H13.6128C22.1667 0 24 1.83334 24 10.3872V13.6128C24 22.1667 22.1667 24 13.6128 24H10.3872C1.83334 24 0 22.1667 0 13.6128V10.3872Z%27 fill=%27black%27/%3E%3C/svg%3E%0A") 48% 41% 37.9% 53.3%
      };
        }

  .cdx-attaches--with-file .cdx-attaches__file-icon-label {
        position: absolute;
        left: 3px;
        top: 11px;
        background: inherit;
        text-transform: uppercase;
        line-height: 1em;
        color: #fff;
        padding: 1px 2px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: bold;
        /* box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.22); */
        font-family: ui-monospace,SFMono-Regular,SF Mono,Menlo,Consolas,Liberation Mono,monospace;
        letter-spacing: 0.02em;
      }

  .cdx-attaches--with-file .cdx-attaches__file-icon svg {
        width: 20px;
        height: 20px;
      }

  .cdx-attaches--with-file .cdx-attaches__file-icon path {
        stroke: #fff;
      }

  .cdx-attaches--with-file .cdx-attaches__size {
      color: #707684;
      font-size: 12px;
      line-height: 1em;
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
      max-width: 90%;
      font-size: 14px;
      font-weight: 500;
      line-height: 1em;
    }

  .cdx-attaches--with-file .cdx-attaches__title:empty::before {
      content: attr(data-placeholder);
      color: #7b7e89;
    }

  .cdx-attaches--loading .cdx-attaches__title,
    .cdx-attaches--loading .cdx-attaches__file-icon,
    .cdx-attaches--loading .cdx-attaches__size,
    .cdx-attaches--loading .cdx-attaches__download-button,
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
				<div class="cdx-attaches__title" data-placeholder="{data.title}" data-empty="false">{data.title}</div>
			</div>
			<a class="cdx-attaches__download-button" href="forum.php?mod=attachment&aid=[attach data.file.aid]" target="_self">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M7 10L11.8586 14.8586C11.9367 14.9367 12.0633 14.9367 12.1414 14.8586L17 10"></path></svg>
			</a>
		</div>
	</div>
    </div>
</div>

EOF;
	}

}