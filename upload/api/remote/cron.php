<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!preg_match('/cli/i', php_sapi_name())) {
	exit;
}

// 添加系统计划任务，每隔1分钟执行一次
// */1 * * * * [php_path]php [discuz_path]api/remote/cron.php
// [php_path] 和 [discuz_path] 自行补充
//
// 修改 config_global.php
// $_config['remote']['on'] = 1;
// $_config['remote']['cron'] = 1;

const IN_API = true;
const CURSCRIPT = 'phpcli_cron';
const APPTYPEID = 200;
const DISABLEDEFENSE = true;

$_ENV['remote'] = new discuz_remote();
$_ENV['remote']->init();

class discuz_remote {

	var $core;

	function init() {

		$path = dirname(__DIR__, 2);
		$_config = [];
		@include $path.'/config/config_global.php';
		if(empty($_config)) {
			exit('config_global.php is missing');
		}

		if(!$_config['remote']['on']) {
			$this->error(1, 'remote service is down');
		}

		if(!$_config['remote']['cron']) {
			$this->error(100, 'cron service is off. Please check "config.global.php" on your webserver folder.');
		}

		require_once($path.'/source/class/class_core.php');

		$this->core = C::app();
		$this->core->init_setting = true;
		$this->core->init_cron = true;
		$this->core->init_user = false;
		$this->core->init_session = false;
		$this->core->init_misc = false;
		$this->core->init_mobile = false;
		$this->core->init();

		$this->success('Cron work is done');
	}

	function error($code, $msg) {
		$code = sprintf('%04d', $code);
		echo $code.':'.ucfirst($msg);
		exit();
	}

	function success($msg) {
		$this->error(0, $msg);
	}

}
