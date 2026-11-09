<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_error {

	public static function system_error($message, $show = false, $save = true, $halt = true) {
		if(!empty($message)) {
			$message = lang('error', $message);
		} else {
			$message = lang('error', 'error_unknow');
		}

		$backtrace = debug_backtrace();
		krsort($backtrace);
		$logmsg = '';
		$phpmsg = [];
		foreach($backtrace as $error) {
			if(!empty($error['function'])) {
				self::format_function($error);
			}
			$phpmsg[] = [
				'file' => str_replace([DISCUZ_ROOT, '\\'], ['', '/'], $error['file']),
				'line' => $error['line'],
				'function' => $error['function'],
			];
			if($save) {
				$file = str_replace([DISCUZ_ROOT, '\\'], ['', '/'], $error['file']);
				$func = $error['class'] ?? '';
				$func .= $error['type'] ?? '';
				$func .= $error['function'] ?? '';
				$line = sprintf('%04d', $error['line']);
				$logmsg .= (!empty($logmsg) ? ' -> ' : '').$file.'#'.$func.':'.$line;
			}
		}
		$backtraceid = '';
		if($save) {
			$messagesave = '<b>'.$message.'</b><br><b>PHP:</b>'.$logmsg;
			$backtraceid = discuz_error::write_error_log($messagesave);
		}

		if(!empty($GLOBALS['_G']['config']['security']['error']['showerror']) && $halt) {
			$show = true;
		}

		if($show) {
			$backtraceid = !empty($backtraceid) ? $backtraceid : md5(discuz_error::clear($messagesave));
			discuz_error::show_error('system', "<li>$message</li>", $phpmsg, '', $backtraceid);
		}

		if($halt) {
			header('HTTP/1.1 503 Service Unavailable');
			exit();
		} else {
			return $message;
		}
	}

	public static function template_error($message, $tplname) {
		$message = lang('error', $message);
		$tplname = str_replace(DISCUZ_ROOT, '', $tplname);
		$message = $message.': '.$tplname;
		discuz_error::system_error($message);
	}

	public static function exception_error($exception) {

		if($exception instanceof DbException) {
			$type = 'db';
		} else {
			$type = 'system';
		}

		if($type == 'db') {
			$errormsg = '('.$exception->getCode().') ';
			$errormsg .= self::sql_clear($exception->getMessage());
			if($exception->getSql()) {
				$errormsg .= '<div class="sql">';
				$errormsg .= self::sql_clear($exception->getSql());
				$errormsg .= '</div>';
			}
		} else {
			$errormsg = $exception->getMessage();
		}

		$trace = $exception->getTrace();
		krsort($trace);

		$trace[] = ['file' => $exception->getFile(), 'line' => $exception->getLine(), 'function' => 'break'];
		$logmsg = '';
		$phpmsg = [];
		foreach($trace as $error) {
			if(!empty($error['function'])) {
				self::format_function($error);
			}
			$phpmsg[] = [
				'file' => str_replace([DISCUZ_ROOT, '\\'], ['', '/'], $error['file']),
				'line' => $error['line'],
				'function' => $error['function'],
			];
			$file = str_replace([DISCUZ_ROOT, '\\'], ['', '/'], $error['file']);
			$func = $error['class'] ?? '';
			$func .= $error['type'] ?? '';
			$func .= $error['function'] ?? '';
			$line = sprintf('%04d', $error['line']);
			$logmsg .= (!empty($logmsg) ? ' -> ' : '').$file.'#'.$func.':'.$line;
		}

		$messagesave = '<b>'.$errormsg.'</b><br><b>PHP:</b>'.$logmsg;
		$backtraceid = self::write_error_log($messagesave);

		self::show_error($type, $errormsg, $phpmsg, '', $backtraceid);
		exit();

	}

	private static function format_function(&$error) {
		$fun = '';
		if(!empty($error['class'])) {
			$fun .= $error['class'].$error['type'];
		}
		$fun .= $error['function'].'(';
		if(!empty($error['args'])) {
			$mark = '';
			foreach($error['args'] as $arg) {
				$fun .= $mark;
				if(is_array($arg)) {
					$fun .= 'Array';
				} elseif(is_bool($arg)) {
					$fun .= $arg ? 'true' : 'false';
				} elseif(is_int($arg)) {
					$fun .= (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? $arg : '%d';
				} elseif(is_float($arg)) {
					$fun .= (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? $arg : '%f';
				} elseif(is_resource($arg)) {
					$fun .= (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? 'Resource' : '%f';
				} elseif(is_object($arg)) {
					$fun .= (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? 'Object' : '%f';
				} else {
					$arg = (string)$arg;
					$fun .= (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? '\''.dhtmlspecialchars(mb_substr(self::clear($arg), 0, 5)).(mb_strlen($arg) > 5 ? ' ...' : '').'\'' : '%s';
				}
				$mark = ', ';
			}
		}

		$fun .= ')';
		$error['function'] = $fun;
	}

	public static function show_error($type, $errormsg, $phpmsg = '', $typemsg = '', $backtraceid = '') {
		global $_G;

		ob_end_clean();
		$gzip = $_G['gzipcompress'];
		ob_start($gzip ? 'ob_gzhandler' : null);

		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 3600');

		$host = $_SERVER['HTTP_HOST'];
		$title = (!isset($_G['config']['security']['error']['showerror']) || !empty($_G['config']['security']['error']['showerror'])) ? ($type == 'db' ? 'Database' : 'System') : 'General';
		echo <<<EOT
<!DOCTYPE html>
<html>
<head>
	<title>$host - $title Error</title>
	<meta charset="{$_G['config']['output']['charset']}" />
	<meta name="renderer" content="webkit" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style type="text/css">
	<!--
	body { background-color: white; color: black; font: 12px verdana, arial, sans-serif;}
	#container { max-width: 1024px; margin: auto; }
	#message   { max-width: 1024px; color: black; }

	.red  {color: red;}
	a:link,a:visited  { font: 12px verdana, arial, sans-serif; color: red; }
	span.guess { font: 12px verdana, arial, sans-serif; color: blue; }
	h1 { color: #FF0000; font: 18px "Verdana"; margin-bottom: 1px; }
	.bg1{ background-color: #F5D9D9;}
	.bg2{ background-color: #EEEEEE;}
	.bg3{ background-color: #FFA66C; font-weight: bold;}
	.table {background: #AAAAAA; font: 12px Menlo,Consolas,"Lucida Console";}
	.table tbody{word-break: break-all;}
	.info {
	    background: none repeat scroll 0 0 #F3F3F3;
	    border: 0px solid #aaaaaa;
	    border-radius: 10px;
	    color: #000000;
	    font-size: 12px;
	    line-height: 160%;
	    margin-bottom: 10px;
	    padding: 4px 8px;	  
	}
	.info svg { width: 40%; min-width: 200px; display: block; margin: auto; margin-bottom: 30px; fill: #999; }
	.info svg .xicon { fill: #d31f0d; }

	.help {
	    border-radius: 10px 10px 10px 10px;
	    font: 12px verdana, arial, sans-serif;
	    text-align: center;
	    line-height: 160%;
	    padding: 4px;
	    margin: 2px 0;
	}

	.sql {
	    background: none repeat scroll 0 0 #F5D9D9;
	    border: 1px solid #aaaaaa;
	    border-radius: 5px;
	    color: #000000;
	    font: arial, sans-serif;
	    font-size: 12px;
	    line-height: 160%;
	    margin-top: 2px;
	    padding: 4px;
	}
	-->
	</style>
</head>
<body>
<div id="container">
<h1>Discuz! $title Error</h1>
EOT;

		echo '<p>Time: '.date('Y-m-d H:i:s O').' IP: '.$_G['clientip'].' BackTraceID: '.$backtraceid.'</p>';

		if(!empty($errormsg) && (!isset($_G['config']['security']['error']['showerror']) || !empty($_G['config']['security']['error']['showerror']))) {
			echo '<div class="info">'.$errormsg.'</div>';
		}
		if(isset($_G['config']['security']['error']['showerror']) && empty($_G['config']['security']['error']['showerror'])) {
			echo '<div class="info"><svg viewBox="0 0 16 16"><path d="M2.5 5a.5.5 0 100-1 .5.5 0 000 1zM4 5a.5.5 0 100-1 .5.5 0 000 1zm2-.5a.5.5 0 11-1 0 .5.5 0 011 0zM0 4a2 2 0 012-2h11a2 2 0 012 2v4a.5.5 0 01-1 0V7H1v5a1 1 0 001 1h5.5a.5.5 0 010 1H2a2 2 0 01-2-2V4zm1 2h13V4a1 1 0 00-1-1H2a1 1 0 00-1 1v2z"/><path d="M16 12.5a3.5 3.5 0 11-7 0 3.5 3.5 0 017 0zm-4.854-1.354a.5.5 0 000 .708l.647.646-.647.646a.5.5 0 00.708.708l.646-.647.646.647a.5.5 0 00.708-.708l-.647-.646.647-.646a.5.5 0 00-.708-.708l-.646.647-.646-.647a.5.5 0 00-.708 0z" class="xicon"/></svg></div>';
		}

		if(!empty($phpmsg) && (!isset($_G['config']['security']['error']['showerror']) || $_G['config']['security']['error']['showerror'] == '1')) {
			echo "\n".'<div class="info">';
			echo '<p><strong>PHP Debug</strong></p>';
			echo '<table cellpadding="5" cellspacing="1" width="100%" class="table">';
			if(is_array($phpmsg)) {
				echo '<tr class="bg2"><td width="20">No.</td><td>File</td><td>Code</td></tr>';
				foreach($phpmsg as $k => $msg) {
					$k++;
					$explode = explode('/', $msg['file']);
					if(isset($explode['1']) && $explode['1'] == 'plugin') {
						$guess = $explode['2'];
						$bg = 'bg3';
					} else {
						$bg = 'bg1';
					}
					echo "\n".'<tr class="'.$bg.'">';
					echo '<td>'.$k.'</td>';
					echo '<td>'.($msg['file'] ? $msg['file'].':'.$msg['line'] : '').'</td>';
					echo '<td>'.$msg['function'].'</td>';
					echo '</tr>';
				}
			} else {
				echo '<tr><td><ul>'.$phpmsg.'</ul></td></tr>';
			}
			echo '</table></div>'."\n";
			if($_G['adminid']) {
				echo '<div class="info">';
				echo '<p><strong>System Info</strong></p>';
				echo '<table cellpadding="2" cellspacing="1" width="100%" class="table">';
				if(defined('DISCUZ_ROOT')) {
					include_once DISCUZ_ROOT.'./source/discuz_version.php';
				}
				if(defined('DISCUZ_VERSION') && defined('DISCUZ_RELEASE')) {
					echo '<tr class="bg2"><td width="50">Version</td><td>'.DISCUZ_VERSION.DISCUZ_SUBVERSION.' Release '.DISCUZ_RELEASE.'</td></tr>';
				}
				if(defined('PHP_OS') && function_exists('php_uname')) {
					echo '<tr class="bg2"><td width="50">OS</td><td>'.PHP_OS.' / '.php_uname().'</td></tr>';
				}
				if(defined('PHP_VERSION') && defined('PHP_SAPI')) {
					echo '<tr class="bg2"><td width="50">PHP</td><td>'.PHP_VERSION.' '.PHP_SAPI.(!empty($_SERVER['SERVER_SOFTWARE']) ? ' on '.$_SERVER['SERVER_SOFTWARE'] : '').'</td></tr>';
				}
				if(method_exists('helper_dbtool', 'dbversion') && class_exists('DB')) {
					echo '<tr class="bg2"><td width="50">MySQL</td><td>'.helper_dbtool::dbversion().'</td></tr>';
				}
				if(function_exists('memory') && ($v = memory('check'))) {
					echo '<tr class="bg2"><td width="50">Memory</td><td>'.$v.'</td></tr>';
				}
				echo '</table></div>';
			}
		}

		if(function_exists('lang')) {
			echo '<div class="info">';
			echo '<div class="help">'.lang('error', 'suggestion');

			if(!isset($_G['config']['security']['error']['guessplugin']) || !empty($_G['config']['security']['error']['guessplugin'])) {
				if(!empty($guess) && $_G['adminid']) {
					$suggestion = lang('error', 'suggestion_plugin', ['guess' => $guess]);
				} else {
					$suggestion = lang('error', $_G['adminid'] ? 'suggestion_admin' : 'suggestion_user');
				}
				echo '<br />'.$suggestion;
			}

			echo '</div>';
			$endmsg = lang('error', $_G['adminid'] ? 'error_end_message_admin' : 'error_end_message_user', ['host' => $host]);
			echo '<div class="help">'.$endmsg.'</div></div>';
		}

		echo '</div></body></html>';
	}

	public static function clear($message) {
		return str_replace(["\t", "\r", "\n"], ' ', $message);
	}

	public static function sql_clear($message) {
		$message = self::clear($message);
		$message = str_replace(DB::object()->tablepre, '', $message);
		return dhtmlspecialchars($message);
	}

	public static function write_error_log($message) {
		global $_G;
		$message = discuz_error::clear($message);
		//$file =  DISCUZ_DATA.'./log/'.date("Ym").'_errorlog.php';
		$hash = md5($message);

		$uid = $_G['uid'] ?? 0;
		$ip = $_G['clientip'] ?? '';

		$user = '<b>User:</b> uid='.intval($uid).'; IP='.$ip.'; RIP:'.$_SERVER['REMOTE_ADDR'];
		$uri = 'Request: '.htmlspecialchars(discuz_error::clear($_SERVER['REQUEST_URI']));
		/*
		$message = "<?PHP exit;?>\t{$time}\t$message\t$hash\t$user $uri\n";
		*/
		// logger start
		if(!empty($_G['setting']['log']['error'])) {
			$errorlog = [
				'timestamp' => TIMESTAMP,
				'message' => $message,
				'hash' => $hash,
				'clientip' => $_G['clientip'],
				'user' => $user,
				'uri' => $uri,
			];
			$member_log = getuserbyuid($uid);
			logger('error', $member_log, $uid, $errorlog);
		}
		// logger end
		/*
		if($fp = @fopen($file, 'rb')) {
			$lastlen = 50000;
			$maxtime = 60 * 10;
			$offset = filesize($file) - $lastlen;
			if($offset > 0) {
				fseek($fp, $offset);
			}
			if($data = fread($fp, $lastlen)) {
				$array = explode("\n", $data);
				if(is_array($array)) foreach($array as $key => $val) {
					$row = explode("\t", $val);
					if($row[0] != '<?PHP exit;?>') continue;
					if($row[3] == $hash && ($row[1] > $time - $maxtime)) {
						return;
					}
				}
			}
		}
		error_log($message, 3, $file);
		*/
		return $hash;
	}

}