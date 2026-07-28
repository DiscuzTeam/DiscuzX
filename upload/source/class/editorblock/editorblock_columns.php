<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_columns {

	var $version = '1.0.5';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 0; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'columns';
	var $description = 'description';
	var $filename = 'editorjs-columns';
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
            "id": "s-FgPhJacf",
            "type": "columns",
            "data": {
                "cols": [
                    {
                        "time": 1703647833755,
                        "blocks": [
                            {
                                "id": "Gi91gbORbV",
                                "type": "paragraph",
                                "data": {
                                    "text": "1列",
                                    "alignment": "left"
                                }
                            }
                        ],
                        "version": "2.28.2"
                    },
                    {
                        "time": 1703647833755,
                        "blocks": [
                            {
                                "id": "-E6VBirxsd",
                                "type": "paragraph",
                                "data": {
                                    "text": "2列",
                                    "alignment": "left"
                                }
                            }
                        ],
                        "version": "2.28.2"
                    },
                    {
                        "time": 1703647833755,
                        "blocks": [
                            {
                                "id": "nhF5HL0xeU",
                                "type": "paragraph",
                                "data": {
                                    "text": "3列",
                                    "alignment": "left"
                                }
                            }
                        ],
                        "version": "2.28.2"
                    }
                ]
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
.ce-editorjsColumns_col {
	flex:50%
}
.ce-editorjsColumns_wrapper {
	display:flex;
	width:auto;
	gap:10px;
	margin-bottom:10px;
	flex-direction:row
}
.ce-editorjsColumns_wrapper .ce-toolbar__actions {
	z-index:0
}
.ce-editorjsColumns_wrapper .ce-toolbar {
	z-index:4
}
.ce-editorjsColumns_wrapper .ce-popover {
	z-index:4000
}
@media(max-width:800px) {
	.ce-editorjsColumns_wrapper {
	flex-direction:column;
	padding:10px;
	border:1px solid #ccc;
	border-radius:4px
}
}.ce-inline-toolbar {
	z-index:1000
}
.ce-toolbar__actions {
	right:calc(100% + 30px);
	background-color:rgba(255,255,255,.5);
	border-radius:4px
}
.codex-editor--narrow .codex-editor__redactor {
	margin:0
}
.ce-toolbar {
	z-index:4
}
.codex-editor {
	z-index:auto !important
}

</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block ce-block--focused" data-id="{id}">
    <div class="ce-block__content">
        <div class="ce-editorjsColumns_wrapper">
        	[loop data.cols]
            <div class="ce-editorjsColumns_col editorjs_col_[loopindex]">
                <div class="codex-editor codex-editor--narrow">
                    <div class="codex-editor__redactor" style="padding-bottom: 50px;">
                        [column blocks]
                    </div>
                </div>
            </div>
            [/loop]
        </div>
    </div>
</div>
EOF;
	}

}