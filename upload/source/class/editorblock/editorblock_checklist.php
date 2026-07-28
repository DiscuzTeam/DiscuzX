<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_checklist {

	var $version = '1.5.6';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'checklist';
	var $description = 'description';
	var $filename = 'checklist';
	var $copyright = '<a href="https://addon.dismall.com/developer-32563.html" target="_blank">云诺</a>';
	var $type = '0'; // 0:数据类型 1:图片类型 2:附件类型 3:视频类型 4:音频类型 5:文件类型

	function __construct() {

	}

	function getsetting() {
		global $_G;
		$settings = [];
		return $settings;
	}

	function setsetting(&$smsgwnew, &$parameters) {
	}

	function getParameter() {
		return <<<EOF
{
    "data": {
        "items": [
            {
                "text": "选项1",
                "checked": false
            },
            {
                "text": "选项2",
                "checked": false
            }
        ]
    },
    "id": "UbEUIk82tj", // 区块id
    "type": "checklist" // 区块类型
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
   tools_checklist: {
      checklist: {
         class: Checklist,
         inlineToolbar: true,
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
.cdx-checklist {
    gap: 6px;
    display: flex;
    flex-direction: column;
}

    .cdx-checklist__item {
        display: flex;
        box-sizing: content-box;
        align-items: flex-start;
    }

    .cdx-checklist__item-text {
            outline: none;
            flex-grow: 1;
            line-height: 1.57em;
        }

    .cdx-checklist__item-checkbox {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            margin-right: 8px;
            margin-top: calc(1.57em/2 - 22px/2);
            cursor: pointer;
        }

    .cdx-checklist__item-checkbox svg {
                opacity: 0;
                height: 20px;
                width: 20px;
                position: absolute;
                left: -1px;
                top: -1px;
                max-height: 20px;
            }

    @media (hover: hover) {
                        .cdx-checklist__item-checkbox:not(.cdx-checklist__item-checkbox--no-hover):hover .cdx-checklist__item-checkbox-check svg {
                            opacity: 1;
                        }
            }

    .cdx-checklist__item-checkbox-check {
                cursor: pointer;
                display: inline-block;
                flex-shrink: 0;
                position: relative;
                width: 20px;
                height: 20px;
                box-sizing: border-box;
                margin-left: 0;
                border-radius: 5px;
                border: 1px solid #C9C9C9;
                background: #fff;
            }

    .cdx-checklist__item-checkbox-check::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    border-radius: 100%;
                    background-color: #369FFF;
                    visibility: hidden;
                    pointer-events: none;
                    transform: scale(1);
                    transition: transform 400ms ease-out, opacity 400ms;
                }

    @media (hover: hover) {
                        .cdx-checklist__item--checked .cdx-checklist__item-checkbox:not(.cdx-checklist__item--checked .cdx-checklist__item-checkbox--no-hover):hover .cdx-checklist__item-checkbox-check {
                            background: #0059AB;
                            border-color: #0059AB;
                        }
                }

    .cdx-checklist__item--checked .cdx-checklist__item-checkbox-check {
                    background: #369FFF;
                    border-color: #369FFF;
                }

    .cdx-checklist__item--checked .cdx-checklist__item-checkbox-check svg {
                        opacity: 1;
                    }

    .cdx-checklist__item--checked .cdx-checklist__item-checkbox-check svg path {
                            stroke: #fff;
                        }

    .cdx-checklist__item--checked .cdx-checklist__item-checkbox-check::before {
                        opacity: 0;
                        visibility: visible;
                        transform: scale(2.5);
                    }
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block ce-block--focused" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
    <div class="ce-block__content">
        <div class="cdx-block cdx-checklist">
            [loop data.items]
               <div class="cdx-checklist__item [if checked=1]cdx-checklist__item--checked[/if]">
                    <div class="cdx-checklist__item-checkbox"><span class="cdx-checklist__item-checkbox-check"><svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path
                            stroke="currentColor" stroke-linecap="round" stroke-width="2"
                            d="M7 12L10.4884 15.8372C10.5677 15.9245 10.705 15.9245 10.7844 15.8372L17 9"></path></svg></span>
                    </div>
                    <div class="cdx-checklist__item-text" contenteditable="true">{text}</div>
               </div>
            [/loop]
        </div>
    </div>
</div>
EOF;
	}

}