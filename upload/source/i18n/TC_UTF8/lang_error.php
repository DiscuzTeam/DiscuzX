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
	'System Message' => '站點資訊',

	'config_notfound' => '配置檔案 "config_global.php" 未找到或者無法訪問， 請確認您已經正確安裝了程式',
	'template_notfound' => '模版檔案未找到或者無法訪問',
	'directory_notfound' => '目錄未找到或者無法訪問',
	'request_tainting' => '您當前的訪問請求當中含有非法字符，已經被系統拒絕',
	'db_help_link' => '點擊這裡尋求幫助',
	'db_error_message' => '錯誤資訊',
	'db_error_sql' => '<b>SQL</b>: $sql<br />',
	'db_error_backtrace' => '<b>Backtrace</b>: $backtrace<br />',
	'db_error_no' => '錯誤代碼',
	'db_notfound_config' => '配置檔案 "config_global.php" 未找到或者無法訪問。',
	'db_notconnect' => '無法連接到數據庫伺服器',
	'db_security_error' => '查詢語句安全威脅',
	'db_query_sql' => '查詢語句',
	'db_query_error' => '查詢語句錯誤',
	'db_config_db_not_found' => '數據庫配置錯誤，請仔細檢查 config_global.php 檔案',
	'system_init_ok' => '網站系統初始化完成，請<a href="index.php">點擊這裡</a>進入',
	'backtrace' => '運行資訊',
	'error_end_message_user' => '本站已經將此出錯資訊詳細記錄，由此給您帶來的訪問不便我們深感歉意<br /><a href="http://{host}">{host}</a>',
	'error_end_message_admin' => '本站已經將此出錯資訊詳細記錄，並可在管理中心“操作日誌>系統錯誤”中搜尋 BackTraceID 快速定位<br /><a href="http://{host}">{host}</a>',
	'suggestion' => '建議您嘗試刷新頁面、關閉所有瀏覽器窗口重新進行操作',
	'suggestion_user' => '如果無法解決，請您攜帶 BackTraceID 向站點管理員回饋此問題，以便快速定位問題',
	'suggestion_plugin' => '建議您嘗試在管理中心關閉 <span class="guess">{guess}</span> 插件。如關閉插件後問題解決，請您憑完整截圖聯繫插件供應方獲得幫助',
	'suggestion_admin' => '如果無法解決，請憑完整截圖通過 <a href="https://www.dismall.com/" target="_blank">Discuz! 官方論壇</a> 尋求幫助、或者在官方 Git 倉庫 <a href="https://gitee.com/discuz/DiscuzX/issues" target="_blank">提交 Issue</a> 給我們',

	'file_upload_error_-101' => '上傳失敗！上傳檔案不存在或不合法，請返回。',
	'file_upload_error_-102' => '上傳失敗！非圖片類型檔案，請返回。',
	'file_upload_error_-103' => '上傳失敗！無法寫入檔案或寫入失敗，請返回。',
	'file_upload_error_-104' => '上傳失敗！無法識別的圖像檔案格式，請返回。',
	];

