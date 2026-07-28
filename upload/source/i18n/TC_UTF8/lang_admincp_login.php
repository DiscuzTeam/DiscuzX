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
	'admincp_title' => '{bbname} 管理中心',
	'login_title' => '登入管理中心',
	'login_username' => '用戶名',
	'login_password' => '密碼',
	'login_dk_light_mode' => '亮色模式',
	'login_dk_by_system' => '跟隨系統',
	'login_dk_normal_mode' => '正常模式',
	'login_dk_dark_mode' => '夜間模式',

	'submit' => '提交',
	'forcesecques' => '必填項',
	'security_question' => '安全提問',
	'security_answer' => '回答',
	'security_question_0' => '無安全提問',
	'security_question_1' => '母親的名字',
	'security_question_2' => '爺爺的名字',
	'security_question_3' => '父親出生的城市',
	'security_question_4' => '您其中一位老師的名字',
	'security_question_5' => '您個人電腦的型號',
	'security_question_6' => '您最喜歡的餐館名稱',
	'security_question_7' => '駕駛執照最後四位數字',
	'other_loginname' => '其他用戶登入',

	'login_tips' => 'Discuz! 是一款以社區為基礎的專業建站平台，幫助網站實現一站式服務。',
	'login_nosecques' => '您還沒有使用安全登入，請在個人中心設定您的安全提問後，再訪問管理中心。您可以 <a href="forum.php?mod=memcp&action=profile&typeid=1" target="_blank">點擊這裡</a> 進入安全提問的設定。',
	'copyright' => '&copy; 2001-'.date('Y').' <a href="https://code.dismall.com/" target="_blank">Discuz! Team</a>.',

	'login_cp_guest' => '<h1>您尚未登入網站</h1><a href="member.php?mod=logging&action=login" class="btn">登入</a><p>站長需要強制登入時，修改 config/config_global.php 可關閉此功能。</p>',
	'login_cplock' => '您的管理面板已經鎖定！<br>請在<b> {ltime} </b>秒以後重新訪問管理中心。',
	'login_user_lock' => '由於您的登入密碼錯誤次數過多，本次登入請求已經被拒絕。請 15 分鐘後重新嘗試。',
	'login_cp_noaccess' => '<b>管理中心(或此項操作)未對當前賬號開放</b><br><br>請更換有權限的賬號重新登入',
	'login_ip_noaccess' => '<a href="https://www.dismall.com/thread-17514-1-1.html" target="_blank">IP變動可能導致登入失敗，查看解決辦法</a>',
	'noaccess' => '後台管理權限(或此項操作)尚未對您開放，請聯繫站點管理員',

	'qrcode_login' => '掃碼登入',
	'pwd_login' => '賬號登入',
	'qrcode_wechat_scan' => '請使用微信掃碼登入',

	];

