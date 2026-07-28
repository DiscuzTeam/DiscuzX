<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editorblock_codeflask {

	var $version = '1.0.8';
	var $name = 'name';
	var $available = 1; // 默认启用状态 0:不启用 1:启用
	var $columns = 1; //  默认是否支持多列 0:不支持 1:支持
	var $identifier = 'codeflask';
	var $description = 'description';
	var $filename = 'codeflask';
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
            "id": "mAPnw-xzCD",
            "type": "codeflask",
            "data": {
                "code": "// Hello World\n{\"aa\": \"bb\"}",
                "language": "json",
                "showlinenumbers": true,
                "length": 2,
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
   tools_codeflask: {
      codeflask: {
         class : editorjsCodeflask,
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
	position: relative;
}

/* 主容器 */
.editorjs-codeFlask_Wrapper {
    border: 1px solid #dcdfe6;
    border-radius: 5px;
    background-color: #f6f8fa;
    margin-bottom: 10px;
    position: relative;
    transition: all 0.3s ease;
    width: 100%;
    min-height: 100px;
    overflow: hidden;
}

/* 标题栏 */
.editorjs-codeFlask_Header {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 8px 12px;
    background-color: #e9ecef;
    border-bottom: 1px solid #dcdfe6;
    position: relative;
    z-index: 0; /* 从10降低到2 */
}

/* 语言显示 */
.editorjs-codeFlask_LangDisplay {
    padding: 2px 8px;
    background-color: #409eff;
    color: white;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
}

/* 内容容器 - 禁止纵向滚动 */
.editorjs-codeFlask_ContentContainer {
    position: relative;
    min-height: 100px;
    overflow-x: hidden; /* 禁止横向滚动 */
    overflow-y: hidden;
    transition: height 0.3s ease;
}

/* 编辑器容器 - 禁止纵向滚动 */
.editorjs-codeFlask_Editor {
    position: relative;
    min-height: 100px;
    overflow-x: hidden; /* 禁止横向滚动 */
    overflow-y: hidden;
    transition: height 0.3s ease;
}

/* 底部按钮容器 - 优化为更简洁的样式 */
.editorjs-codeFlask_BottomButtonContainer {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 4px;
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    position: relative;
    z-index: 0;
}

/* 底部横向折叠/展开按钮 - 优化样式，使其更融入设计 */
.editorjs-codeFlask_BottomToggle {
    width: 100%;
    background-color: transparent;
    color: #606266;
    border: none;
    border-radius: 4px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 400;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    text-align: center;
    position: relative;
}

.editorjs-codeFlask_BottomToggle:hover {
    background-color: #e9ecef;
    color: #409eff;
}

.editorjs-codeFlask_BottomToggle:active {
    background-color: #dee2e6;
}

.editorjs-codeFlask_BottomToggle .toggle-icon {
    font-size: 11px;
    transition: transform 0.3s ease;
}

/* 展开按钮（显示"展开"文本）上边缘添加渐变虚化效果 */
.editorjs-codeFlask_BottomToggle:has(span.toggle-icon:contains("▲"))::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(to bottom, rgba(248, 249, 250, 0) 0%, rgba(248, 249, 250, 1) 100%);
    pointer-events: none;
}

/* 展开按钮（显示"展开"文本）上边缘添加渐变虚化效果 */
/* 删除不被广泛支持的:contains()选择器 */
.editorjs-codeFlask_BottomToggle.expand-mode::before {
    content: '';
    position: absolute;
    top: -60px;
    left: 0;
    right: 0;
    height: 60px;
    background: linear-gradient(to bottom, rgb(255 255 255 / 0%) 0%, rgba(248, 249, 250, 1) 100%);
    pointer-events: none;
}

/* CodeFlask 主容器样式 */
.editorjs-codeFlask_Editor .codeflask {
    position: relative;
    background: #fafafa;
    border-radius: 0 0 4px 4px;
    min-height: 100px;
    overflow-x: hidden; /* 禁止横向滚动 */
    overflow-y: hidden;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
}

/* 行号容器 */
.editorjs-codeFlask_Editor .codeflask.codeflask--has-line-numbers:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 40px;
    background-color: #f5f5f5;
    border-right: 1px solid #e0e0e0;
    z-index: 0;
}

/* 行号 */
.editorjs-codeFlask_Editor .codeflask__lines {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 40px;
    padding: 10px 0;
    background-color: #f5f5f5;
    border-right: 1px solid #e0e0e0;
    z-index: 0;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 14px;
    line-height: 21px;
    color: #666;
    text-align: right;
    user-select: none;
    overflow: hidden;
}

.editorjs-codeFlask_Editor .codeflask__lines__line {
    padding-right: 8px;
}

/* 文本区域 - 输入框 */
.editorjs-codeFlask_Editor .codeflask__textarea {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    padding: 10px;
    border: none;
    background: transparent;
    color: transparent;
    caret-color: #333;
    resize: none;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 14px;
    line-height: 21px;
    z-index: 1;
    overflow-x: hidden; /* 禁止横向滚动 */
    overflow-y: hidden;
    white-space: pre-wrap; /* 自动换行 */
    tab-size: 4;
    outline: none;
}

/* 添加选中文本样式 */
.editorjs-codeFlask_Editor .codeflask__textarea::selection {
    background-color: #b3d4fc;
    color: #333;
}

.editorjs-codeFlask_Editor .codeflask__textarea::-moz-selection {
    background-color: #b3d4fc;
    color: #333;
}

/* 代码预览区域 */
.editorjs-codeFlask_Editor .codeflask__pre {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%; /* 确保宽度不超出容器 */
    height: 100%;
    padding: 10px;
    margin: 0;
    border: none;
    background: transparent;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 14px;
    line-height: 21px;
    z-index: 0;
    overflow-x: hidden; /* 禁止横向滚动 */
    overflow-y: hidden;
    white-space: pre-wrap; /* 自动换行 */
    pointer-events: none;
}

/* 代码高亮区域 */
.editorjs-codeFlask_Editor .codeflask__code {
    display: block;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 14px;
    line-height: 21px;
    color: #333;
    white-space: pre-wrap; /* 自动换行 */
    tab-size: 4;
    overflow: visible;
}

/* 语法高亮样式 - 确保Prism.js样式正确应用 */
.editorjs-codeFlask_Editor .codeflask__code[class*="language-"] {
    background: transparent !important;
}

/* 通用语法高亮token样式 */
.editorjs-codeFlask_Editor .token.comment,
.editorjs-codeFlask_Editor .token.prolog,
.editorjs-codeFlask_Editor .token.doctype,
.editorjs-codeFlask_Editor .token.cdata {
    color: #708090;
}

.editorjs-codeFlask_Editor .token.punctuation {
    color: #999;
}

.editorjs-codeFlask_Editor .token.namespace {
    opacity: 0.7;
}

.editorjs-codeFlask_Editor .token.property,
.editorjs-codeFlask_Editor .token.tag,
.editorjs-codeFlask_Editor .token.boolean,
.editorjs-codeFlask_Editor .token.number,
.editorjs-codeFlask_Editor .token.constant,
.editorjs-codeFlask_Editor .token.symbol,
.editorjs-codeFlask_Editor .token.deleted {
    color: #905;
}

.editorjs-codeFlask_Editor .token.selector,
.editorjs-codeFlask_Editor .token.attr-name,
.editorjs-codeFlask_Editor .token.string,
.editorjs-codeFlask_Editor .token.char,
.editorjs-codeFlask_Editor .token.builtin,
.editorjs-codeFlask_Editor .token.inserted {
    color: #690;
}

.editorjs-codeFlask_Editor .token.operator,
.editorjs-codeFlask_Editor .token.entity,
.editorjs-codeFlask_Editor .token.url,
.editorjs-codeFlask_Editor .language-css .token.string,
.editorjs-codeFlask_Editor .style .token.string {
    color: #9a6e3a;
    background: hsla(0, 0%, 100%, 0.5);
}

.editorjs-codeFlask_Editor .token.atrule,
.editorjs-codeFlask_Editor .token.attr-value,
.editorjs-codeFlask_Editor .token.keyword {
    color: #07a;
}

.editorjs-codeFlask_Editor .token.function,
.editorjs-codeFlask_Editor .token.class-name {
    color: #dd4a68;
}

.editorjs-codeFlask_Editor .token.regex,
.editorjs-codeFlask_Editor .token.important,
.editorjs-codeFlask_Editor .token.variable {
    color: #e90;
}

/* 复制按钮样式 */
.editorjs-codeFlask_CopyButton {
    background-color: #409eff;
    color: white;
    border: none;
    border-radius: 3px;
    padding: 6px 12px;
    margin-right: 8px;
    font-size: 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
}

.editorjs-codeFlask_CopyButton:hover {
    background-color: #66b1ff;
}

.editorjs-codeFlask_CopyButton.copied {
    background-color: #67c23a;
}

/* 折叠/展开按钮样式 */
.editorjs-codeFlask_Toggle {
    background-color: #909399;
    color: white;
    border: none;
    border-radius: 3px;
    padding: 0 8px;
    font-size: 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    font-weight: bold;
}

.editorjs-codeFlask_Toggle:hover {
    background-color: #a6a9ad;
}

/* 响应式调整 */
@media (max-width: 768px) {
    .editorjs-codeFlask_Header {
        padding: 6px 8px;
    }
    
    .editorjs-codeFlask_Editor .codeflask__textarea,
    .editorjs-codeFlask_Editor .codeflask__pre,
    .editorjs-codeFlask_Editor .codeflask__code {
        font-size: 13px;
        line-height: 19px;
        padding: 8px;
    }
    
    .editorjs-codeFlask_Editor .codeflask.codeflask--has-line-numbers:before {
        width: 35px;
    }
    
    .editorjs-codeFlask_Editor .codeflask__lines {
        width: 35px;
        font-size: 12px;
    }
}

/* 滚动条样式 - 完全隐藏纵向滚动条 */
.editorjs-codeFlask_Wrapper ::-webkit-scrollbar {
    width: 0;  /* 纵向滚动条宽度为0 */
    height: 6px;  /* 横向滚动条保持6px宽度 */
}

.editorjs-codeFlask_Wrapper ::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 0;
}

.editorjs-codeFlask_Wrapper ::-webkit-scrollbar-thumb {
    background: transparent;
    border-radius: 0;
}

/* Firefox 滚动条隐藏 */
.editorjs-codeFlask_Wrapper {
    scrollbar-width: none;  /* Firefox 隐藏纵向滚动条 */
}

.editorjs-codeFlask_Wrapper ::-moz-scrollbar {
    width: 0;
    height: 6px;
}

/* 焦点状态 */
.editorjs-codeFlask_Wrapper:focus-within {
    border-color: #409eff;
    box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.2);
}

/* 只读模式样式 */
.editorjs-codeFlask_Wrapper.readonly .editorjs-codeFlask_Header {
    background-color: #f5f7fa;
}

.editorjs-codeFlask_Wrapper.readonly .editorjs-codeFlask_Editor .codeflask__textarea {
    cursor: default;
}

/* 确保所有元素正确显示 */
.editorjs-codeFlask_Wrapper * {
    box-sizing: border-box;
}

/* 修复行号对齐 */
.editorjs-codeFlask_Editor .codeflask--has-line-numbers .codeflask__textarea,
.editorjs-codeFlask_Editor .codeflask--has-line-numbers .codeflask__pre {
    padding-left: 50px;
}

.editorjs-codeFlask_Editor .codeflask--has-line-numbers .codeflask__lines {
    padding-top: 10px;
}



/* 语言显示元素 - 添加点击样式 */
.editorjs-codeFlask_LangDisplay {
    padding: 2px 8px;
    background-color: #409eff;
    color: white;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    transition: all 0.2s ease;
}

.editorjs-codeFlask_LangDisplay:hover {
    background-color: #66b1ff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* 语言选择弹窗 */
.editorjs-codeFlask_LanguagePopup {
    background-color: white;
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
    width: 250px;
    max-height: 300px;
    z-index: 1000;
    overflow: hidden;
}

/* 搜索框 */
.editorjs-codeFlask_LanguageSearch {
    width: 100%;
    padding: 8px 12px;
    border: none;
    border-bottom: 1px solid #ebeef5;
    font-size: 12px;
    outline: none;
    box-sizing: border-box;
}

/* 语言列表 */
.editorjs-codeFlask_LanguagesList {
    max-height: 250px;
    overflow-y: auto;
}

/* 语言选项 */
.editorjs-codeFlask_LanguageItem {
    padding: 8px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.editorjs-codeFlask_LanguageItem:hover {
    background-color: #f5f7fa;
}

.editorjs-codeFlask_LanguageItem.selected {
    background-color: #ecf5ff;
    color: #409eff;
    font-weight: 500;
}

/* 滚动条样式 */
.editorjs-codeFlask_LanguagesList::-webkit-scrollbar {
    width: 6px;
}

.editorjs-codeFlask_LanguagesList::-webkit-scrollbar-track {
    background-color: #f5f7fa;
}

.editorjs-codeFlask_LanguagesList::-webkit-scrollbar-thumb {
    background-color: #c0c4cc;
    border-radius: 3px;
}

.editorjs-codeFlask_LanguagesList::-webkit-scrollbar-thumb:hover {
    background-color: #909399;
}
</style>
EOF;

	}

	function getParser($block = []) {
		global $_G;
		return <<<EOF
<div class="ce-block ce-block--focused" data-id="{id}" [if tunes.anchorTune.anchor=notnull]id="{tunes.anchorTune.anchor}"[/if]>
    <div class="ce-block__content">
        <div class="editorjs-codeFlask_Wrapper">
		<div class="editorjs-codeFlask_Header">
			<div class="editorjs-codeFlask_LangDisplay">{data.language}</div>
			<button class="editorjs-codeFlask_CopyButton" title="复制代码" id="codeflask-copy-{id}">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
					<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
				</svg>
			</button>
		        <button class="editorjs-codeFlask_Toggle" id="codeflask-Toggle-{id}">
		                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
		                <polyline points="6 9 12 15 18 9"></polyline>
		                </svg>
		        </button>
		</div>
		<div class="editorjs-codeFlask_ContentContainer">
			<div class="editorjs-codeFlask_Editor" id="codeflask-{id}" >
			
			</div>
		</div>
		<div class="editorjs-codeFlask_BottomButtonContainer" id="codeflask-bottomBtn-{id}">
			<button class="editorjs-codeFlask_BottomToggle expand-mode" title="展开代码" data-empty="false"><span class="toggle-icon">▼</span> 展开</button>
		</div>
	</div>
    </div>
</div>
[jsfile codeflask150.min.js]
[codeflask id,data.language,data.length,data.code]
EOF;
	}

}