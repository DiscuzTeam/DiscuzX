<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang =
	[
	'System Message' => '站点信息',

	'config_notfound' => '配置文件 "config_global.php" 未找到或者无法访问， 请确认您已经正确安装了程序',
	'template_notfound' => '模版文件未找到或者无法访问',
	'directory_notfound' => '目录未找到或者无法访问',
	'request_tainting' => '您当前的访问请求当中含有非法字符，已经被系统拒绝',
	'db_help_link' => '点击这里寻求帮助',
	'db_error_message' => '错误信息',
	'db_error_sql' => '<b>SQL</b>: $sql<br />',
	'db_error_backtrace' => '<b>Backtrace</b>: $backtrace<br />',
	'db_error_no' => '错误代码',
	'db_notfound_config' => '配置文件 "config_global.php" 未找到或者无法访问。',
	'db_notconnect' => '无法连接到数据库服务器',
	'db_security_error' => '查询语句安全威胁',
	'db_query_sql' => '查询语句',
	'db_query_error' => '查询语句错误',
	'db_config_db_not_found' => '数据库配置错误，请仔细检查 config_global.php 文件',
	'system_init_ok' => '网站系统初始化完成，请<a href="index.php">点击这里</a>进入',
	'backtrace' => '运行信息',
	'error_end_message_user' => '本站已经将此出错信息详细记录，由此给您带来的访问不便我们深感歉意<br /><a href="http://{host}">{host}</a>',
	'error_end_message_admin' => '本站已经将此出错信息详细记录，并可在管理中心“操作日志>系统错误”中搜索 BackTraceID 快速定位<br /><a href="http://{host}">{host}</a>',
	'suggestion' => '建议您尝试刷新页面、关闭所有浏览器窗口重新进行操作',
	'suggestion_user' => '如果无法解决，请您携带 BackTraceID 向站点管理员反馈此问题，以便快速定位问题',
	'suggestion_plugin' => '建议您尝试在管理中心关闭 <span class="guess">{guess}</span> 插件。如关闭插件后问题解决，请您凭完整截图联系插件供应方获得帮助',
	'suggestion_admin' => '如果无法解决，请凭完整截图通过 <a href="https://www.dismall.com/?form=discuz_error" target="_blank">Discuz! 官方论坛</a>、 <a href="https://chat.discuz.vip/?group=pay" target="_blank">Discuz! 有償服務</a> 寻求帮助，或者在官方 Git 仓库 <a href="https://gitee.com/discuz/DiscuzX/issues" target="_blank">提交 Issue</a> 给我们',

	'file_upload_error_-101' => '上传失败！上传文件不存在或不合法，请返回。',
	'file_upload_error_-102' => '上传失败！非图片类型文件，请返回。',
	'file_upload_error_-103' => '上传失败！无法写入文件或写入失败，请返回。',
	'file_upload_error_-104' => '上传失败！无法识别的图像文件格式，请返回。',
	];

