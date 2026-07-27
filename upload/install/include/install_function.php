<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function show_msg($error_no, $error_msg = 'ok', $success = 1, $quit = TRUE) {
	if(VIEW_OFF) {
		$error_code = $success ? 0 : constant(strtoupper($error_no));
		$error_msg = empty($error_msg) ? $error_no : $error_msg;
		$error_msg = str_replace('"', '\"', $error_msg);
		$str = "<root>\n";
		$str .= "\t<error errorCode=\"$error_code\" errorMessage=\"$error_msg\" />\n";
		$str .= "</root>";
		send_mime_type_header();
		echo $str;
		exit;
	} else {
		show_header();
		global $step;

		$title = lang($error_no);
		$comment = lang($error_no.'_comment', false);
		$errormsg = '';

		if($error_msg) {
			if(!empty($error_msg)) {
				foreach((array)$error_msg as $k => $v) {
					if(is_numeric($k)) {
						$comment .= "<li><em class=\"red\">".lang($v)."</em></li>";
					}
				}
			}
		}

		if($step > 0) {
			echo "<div class=\"box warnbox\"><h3>$title</h3><ul>$comment</ul>";
		} else {
			echo "</div><div class=\"main\"><div class=\"box warnbox\"><h3>$title</h3><ul>$comment</ul>";
		}

		if($quit) {
			echo '<br /><span class="red">'.lang('error_quit_msg').'</span><br /><br /><br />';
		}

		echo '<input type="button" class="btn oldbtn" onclick="history.back()" value="'.lang('click_to_back').'" />';

		echo '</div>';

		$quit && show_footer();
	}
}

function check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre) {
	if(!function_exists('mysqli_connect')) {
		show_msg('undefine_func', 'mysqli_connect', 0);
	}

	mysqli_report(MYSQLI_REPORT_OFF);

	$link = @new mysqli($dbhost, $dbuser, $dbpw);
	if($link->connect_errno) {
		$errno = $link->connect_errno;
		$error = $link->connect_error;
		if($errno == 1045) {
			show_msg('database_errno_1045', $error, 0);
		} elseif($errno == 2003) {
			show_msg('database_errno_2003', $error, 0);
		} else {
			show_msg('database_connect_error', $error, 0);
		}
		return false;
	} else {
		if($query = $link->query("SHOW TABLES FROM $dbname")) {
			if(!$query) {
				return false;
			}
			while($row = $query->fetch_row()) {
				if(preg_match("/^$tablepre/", $row[0])) {
					return false;
				}
			}
		}
	}
	return true;
}

function dirfile_check(&$dirfile_items) {
	foreach($dirfile_items as $key => $item) {
		$item_path = $item['path'];
		if($item['type'] == 'dir') {
			if(!dir_writeable(ROOT_PATH.$item_path)) {
				if(is_dir(ROOT_PATH.$item_path)) {
					$dirfile_items[$key]['status'] = 0;
					$dirfile_items[$key]['current'] = '+r';
				} else {
					$dirfile_items[$key]['status'] = -1;
					$dirfile_items[$key]['current'] = 'nodir';
				}
			} else {
				$dirfile_items[$key]['status'] = 1;
				$dirfile_items[$key]['current'] = '+r+w';
			}
		} else {
			if(file_exists(ROOT_PATH.$item_path)) {
				if(is_writable(ROOT_PATH.$item_path)) {
					$dirfile_items[$key]['status'] = 1;
					$dirfile_items[$key]['current'] = '+r+w';
				} else {
					$dirfile_items[$key]['status'] = 0;
					$dirfile_items[$key]['current'] = '+r';
				}
			} else {
				if(dir_writeable(dirname(ROOT_PATH.$item_path))) {
					$dirfile_items[$key]['status'] = 1;
					$dirfile_items[$key]['current'] = '+r+w';
				} else {
					$dirfile_items[$key]['status'] = -1;
					$dirfile_items[$key]['current'] = 'nofile';
				}
			}
		}
	}
}

function env_check(&$env_items) {
	foreach($env_items as $key => $item) {
		if($key == 'php') {
			$env_items[$key]['current'] = PHP_VERSION;
		}
		if($key == 'mysql') {
			$env_items[$key]['current'] = class_exists('mysqli') ? 'mysql_enable' : 'disable';
		} elseif($key == 'attachmentupload') {
			$env_items[$key]['current'] = @ini_get('file_uploads') ? getmaxupload() : 'unknow';
		} elseif($key == 'diskspace') {
			if(function_exists('disk_free_space')) {
				$env_items[$key]['current'] = disk_free_space(ROOT_PATH);
			} else {
				$env_items[$key]['current'] = 'unknow';
			}
		} elseif(isset($item['c'])) {
			$env_items[$key]['current'] = constant($item['c']);
		} elseif(isset($item['f'])) {
			$env_items[$key]['current'] = function_exists($item['f']) ? 'enable' : 'disable';
		} elseif($key == 'extensions') {
			$extensions = check::extensions();
			if(!empty($extensions)) {
				$msg = [];
				!empty($extensions['extension']) && $msg[lang('ext_missing')] .= implode(', ', $extensions['extension']);
				!empty($extensions['function']) && $msg[lang('func_missing')] .= implode(', ', $extensions['function']);
				$env_items[$key]['current'] = $msg;
			}
			continue;
		}

		$env_items[$key]['status'] = 1;
		if($item['r'] != 'notset' && strcmp($env_items[$key]['current'], $item['r']) < 0) {
			$env_items[$key]['status'] = 0;
		}
	}
}

function function_check(&$func_items) {
	foreach($func_items as $item) {
		function_exists($item) or show_msg('undefine_func', $item, 0);
	}
}

function dfloatval($int, $allowarray = false) {
	$ret = floatval($int);
	if($int == $ret || !$allowarray && is_array($int)) return $ret;
	if($allowarray && is_array($int)) {
		foreach($int as &$v) {
			$v = dfloatval($v, true);
		}
		return $int;
	} elseif($int <= 0xffffffff) {
		$l = strlen($int);
		$m = substr($int, 0, 1) == '-' ? 1 : 0;
		if(($l - $m) === strspn($int, '0987654321', $m)) {
			return $int;
		}
	}
	return $ret;
}

function show_env_result(&$env_items, &$dirfile_items, &$func_items, &$filesock_items) {

	$env_str = $file_str = $dir_str = $func_str = '';
	$error_code = 0;

	foreach($env_items as $key => $item) {
		if($key == 'php' && strcmp($item['current'], $item['r']) < 0) {
			show_msg(sprintf(lang('php_version_too_low'), $item['r']), $item['current'], 0);
		}
		$status = 1;
		if($item['r'] != 'notset') {
			if(dfloatval($item['current']) && dfloatval($item['r'])) {
				if(dfloatval($item['current']) < dfloatval($item['r'])) {
					$status = 0;
					$error_code = ENV_CHECK_ERROR;
				}
			} else {
				if(strcmp($item['current'], $item['r']) < 0) {
					$status = 0;
					$error_code = ENV_CHECK_ERROR;
				}
			}
		}
		if($key == 'diskspace') {
			$item['current'] = format_space($item['current']);
			$item['r'] = format_space($item['r']);
		}
		if(VIEW_OFF) {
			$env_str .= "\t\t<runCondition name=\"$key\" status=\"$status\" Require=\"{$item['r']}\" Best=\"{$item['b']}\" Current=\"{$item['current']}\"/>\n";
		} elseif($key == 'extensions') {
			foreach($item['current'] as $name => $current) {
				$env_str .= "<tr class=\"nwbg\">\n";
				$env_str .= "<td>".$name."</td>\n";
				$env_str .= "<td class=\"nw padleft\" colspan=\"3\">".$current."</td>\n";
				$env_str .= "</tr>\n";
				$error_code = ENV_CHECK_ERROR;
			}
		} else {
			$env_str .= '<tr'.($status ? '' : ' class="nwbg"').">\n";
			$env_str .= "<td>".lang($key)."</td>\n";
			$env_str .= "<td class=\"padleft\">".lang($item['r'])."</td>\n";
			$env_str .= "<td class=\"padleft\">".lang($item['b'])."</td>\n";
			$env_str .= ($status ? "<td class=\"w pdleft1\">" : "<td class=\"nw pdleft1\">").lang($item['current'])."</td>\n";
			$env_str .= "</tr>\n";
		}
	}

	foreach($dirfile_items as $key => $item) {
		$tagname = $item['type'] == 'file' ? 'File' : 'Dir';
		$variable = $item['type'].'_str';

		if(VIEW_OFF) {
			if($item['status'] == 0) {
				$error_code = ENV_CHECK_ERROR;
			}
			$$variable .= "\t\t\t<File name=\"{$item['path']}\" status=\"{$item['status']}\" requirePermisson=\"+r+w\" currentPermisson=\"{$item['current']}\" />\n";
		} else {
			$$variable .= '<tr'.($item['status'] == 1 ? '' : ' class="nwbg"').">\n";
			$$variable .= "<td>{$item['path']}</td><td class=\"w pdleft1\">".lang('writeable')."</td>\n";
			if($item['status'] == 1) {
				$$variable .= "<td class=\"w pdleft1\">".lang('writeable')."</td>\n";
			} elseif($item['status'] == -1) {
				$error_code = ENV_CHECK_ERROR;
				$$variable .= "<td class=\"nw pdleft1\">".lang('nodir')."</td>\n";
			} else {
				$error_code = ENV_CHECK_ERROR;
				$$variable .= "<td class=\"nw pdleft1\">".lang('unwriteable')."</td>\n";
			}
			$$variable .= "</tr>\n";
		}
	}

	if(VIEW_OFF) {

		$str = "<root>\n";
		$str .= "\t<runConditions>\n";
		$str .= $env_str;
		$str .= "\t</runConditions>\n";
		$str .= "\t<FileDirs>\n";
		$str .= "\t\t<Dirs>\n";
		$str .= $dir_str;
		$str .= "\t\t</Dirs>\n";
		$str .= "\t\t<Files>\n";
		$str .= $file_str;
		$str .= "\t\t</Files>\n";
		$str .= "\t</FileDirs>\n";
		$str .= "\t<error errorCode=\"$error_code\" errorMessage=\"\" />\n";
		$str .= "</root>";
		send_mime_type_header();
		echo $str;
		exit;

	} else {

		show_header();

		echo "<div class=\"box\"><h2 class=\"title\" id=\"_env_check\">".lang('env_check')."</h2>\n";
		echo "<table class=\"tb\">\n";
		echo "<tr>\n";
		echo "\t<th>".lang('project')."</th>\n";
		echo "\t<th class=\"padleft\">".lang('ucenter_required')."</th>\n";
		echo "\t<th class=\"padleft\">".lang('ucenter_best')."</th>\n";
		echo "\t<th class=\"padleft\">".lang('curr_server')."</th>\n";
		echo "</tr>\n";
		echo $env_str;
		echo "</table></div>\n";

		echo "<div class=\"box\"><h2 class=\"title\">".lang('priv_check')."</h2>\n";
		echo "<table class=\"tb\">\n";
		echo "\t<tr>\n";
		echo "\t<th>".lang('step1_file')."</th>\n";
		echo "\t<th class=\"padleft\">".lang('step1_need_status')."</th>\n";
		echo "\t<th class=\"padleft\">".lang('step1_status')."</th>\n";
		echo "</tr>\n";
		echo $file_str;
		echo $dir_str;
		echo "</table></div>\n";

		foreach($func_items as $item) {
			$status = function_exists($item);
			$func_str .= '<tr'.($status ? '' : ' class="nwbg"').">\n";
			$func_str .= "<td>$item()</td>\n";
			if($status) {
				$func_str .= "<td class=\"w pdleft1\">".lang('supportted')."</td>\n";
				$func_str .= "<td class=\"padleft\">".lang('none')."</td>\n";
			} else {
				$error_code = ENV_CHECK_ERROR;
				$func_str .= "<td class=\"nw pdleft1\">".lang('unsupportted')."</td>\n";
				$func_str .= "<td><font color=\"red\">".lang('advice_'.$item)."</font></td>\n";
			}
		}
		$func_strextra = '';
		$filesock_disabled = 0;
		foreach($filesock_items as $item) {
			$status = function_exists($item);
			$func_strextra .= '<tr'.($status ? '' : ' class="nwbg"').">\n";
			$func_strextra .= "<td>$item()</td>\n";
			if($status) {
				$func_strextra .= "<td class=\"w pdleft1\">".lang('supportted')."</td>\n";
				$func_strextra .= "<td class=\"padleft\">".lang('none')."</td>\n";
				break;
			} else {
				$filesock_disabled++;
				$func_strextra .= "<td class=\"nw pdleft1\">".lang('unsupportted')."</td>\n";
				$func_strextra .= "<td><font color=\"red\">".lang('advice_'.$item)."</font></td>\n";
			}
		}
		if($filesock_disabled == count($filesock_items)) {
			$error_code = ENV_CHECK_ERROR;
		}
		echo "<div class=\"box\"><h2 class=\"title\">".lang('func_depend')."</h2>\n";
		echo "<table class=\"tb\">\n";
		echo "<tr>\n";
		echo "\t<th>".lang('func_name')."</th>\n";
		echo "\t<th class=\"padleft\">".lang('check_result')."</th>\n";
		echo "\t<th class=\"padleft\">".lang('suggestion')."</th>\n";
		echo "</tr>\n";
		echo $func_str.$func_strextra;
		echo "</table></div>\n";

		echo <<<EOT
<script>
	document.querySelectorAll('.box').forEach(function(elem){
		if(!elem.querySelector('.nw')) {
			elem.classList.add('valid','collapse');
			elem.addEventListener('click',function(){
				this.classList.contains('collapse') ? this.classList.remove('collapse') : this.classList.add('collapse');
			});
		}
	});
	document.getElementById('_env_check').click();
</script>
EOT;

		show_next_step(2, $error_code);

		show_footer();

	}

}

function show_next_step($step, $error_code) {
	global $uchidden;

	if(!empty($uchidden)) {
		$uc_info_transfer = unserialize(urldecode($uchidden));
		if(!isset($uc_info_transfer['ucapi']) && !isset($uc_info_transfer['ucfounderpw'])) {
			$uchidden = '';
		} else {
			$uchidden = dhtmlspecialchars($uchidden);
		}
	}

	echo "<form action=\"index.php\" method=\"post\">\n";
	echo "<input type=\"hidden\" name=\"step\" value=\"$step\" />";
	if(isset($GLOBALS['hidden'])) {
		echo $GLOBALS['hidden'];
	}
	echo "<input type=\"hidden\" name=\"uchidden\" value=\"$uchidden\" />";
	if($uchidden) {
		echo "<input type=\"hidden\" name=\"install_ucenter\" value=\"no\" />";
	}
	if($error_code == 0) {
		$nextstep = "<input type=\"button\" class=\"btn oldbtn\" onclick=\"history.back();\" value=\"".lang('old_step')."\"><input type=\"submit\" class=\"btn\" value=\"".lang('new_step')."\">\n";
	} else {
		$nextstep = "<input type=\"button\" class=\"btn\" disabled=\"disabled\" value=\"".lang('not_continue')."\">\n";
	}
	echo "<div class=\"btnbox\"><div class=\"inputbox\">".$nextstep."</div></div>\n";
	echo "</form>\n";
}

function show_form(&$form_items, $error_msg) {

	global $step, $uchidden;

	if(empty($form_items) || !is_array($form_items)) {
		return;
	}

	show_header();
	show_setting('start');
	show_setting('hidden', 'step', $step);
	if($step == 2) {
		echo '<div class="box">';
		show_tips('install_dzstandalone');
		show_tips('install_dzonly');
		show_tips('upgrade_upgrade');
		echo '</div>';
	} else {
		show_setting('hidden', 'install_ucenter', getgpc('install_ucenter'));
	}
	$is_first = 1;
	if(!empty($uchidden)) {
		$uc_info_transfer = unserialize(urldecode($uchidden));
	}
	echo '<div id="form_items_'.$step.'" '.($step == 2 && !getgpc('install_ucenter') ? 'style="display:none"' : '').'>';
	foreach($form_items as $key => $items) {
		global ${'error_'.$key};
		if($is_first == 0) {
			echo '</div>';
		}

		if(!${'error_'.$key}) {
			show_tips('tips_'.$key);
		} else {
			show_error('tips_admin_config', ${'error_'.$key});
		}

		echo '<div class="box">';
		foreach($items as $k => $v) {
			$value = '';
			if(!empty($error_msg)) {
				$value = isset($_POST[$key][$k]) ? $_POST[$key][$k] : '';
			}
			if(empty($value)) {
				if(isset($v['value']) && is_array($v['value'])) {
					if($v['value']['type'] == 'constant') {
						$value = defined($v['value']['var']) ? constant($v['value']['var']) : $v['value']['var'];
					} else {
						$value = $GLOBALS[$v['value']['var']];
					}
				} else {
					$value = '';
				}
			}

			if($k == 'ucurl' && isset($uc_info_transfer['ucapi'])) {
				$value = $uc_info_transfer['ucapi'];
			} elseif($k == 'ucpw' && isset($uc_info_transfer['ucfounderpw'])) {
				$value = $uc_info_transfer['ucfounderpw'];
			} elseif($k == 'ucip') {
				$value = '';
			}

			show_setting($k, $key.'['.$k.']', $value, $v['type'], isset($error_msg[$key][$k]) ? $key.'_'.$k.'_invalid' : '');
		}

		if($is_first) {
			$is_first = 0;
		}
	}
	echo '</div>';
	echo '</div>';
	echo '<div class="btnbox">';
	show_setting('', 'submitname', 'new_step', ($step == 2 ? 'submit|oldbtn' : 'submit'));
	echo '</div>';
	show_setting('end');
	show_footer();
}

function dunserialize($data) {
	if(($ret = unserialize($data)) === false) {
		$ret = unserialize(stripslashes($data));
	}
	return $ret;
}

function show_license() {
	global $self, $uchidden, $step;
	$next = $step + 1;
	if(VIEW_OFF) {

		show_msg('license_contents', lang('license'), 1);

	} else {

		show_header();

		$license = str_replace('  ', '&nbsp; ', lang('license'));
		$lang_agreement_yes = lang('agreement_yes');
		$lang_agreement_no = lang('agreement_no');
		$lang_agreement_notice = lang('agreement_notice');

		$arraw = '<svg class="arrow" viewBox="0 0 24 24"><path d="M12 16.5c-.3 0-.5-.1-.7-.3l-6-6a1 1 0 0 1 1.4-1.4L12 14.1l5.3-5.3a1 1 0 1 1 1.4 1.4l-6 6c-.2.2-.5.3-.7.3z" /></svg>';

		echo <<<EOT
</div>
<div class="main">
	<div class="scroll-arrows" id="scrollArrows">
            $arraw$arraw$arraw$arraw$arraw$arraw
        </div>
        <div class="licenseblock" id="license">$license
	<div onmouseover="agreeUnlock()" style="width: 100%">&nbsp;</div>
	</div>
	
	<div class="btnbox">
		<em>$lang_agreement_notice</em>
		<form method="get" autocomplete="off" action="index.php" class="inputbox">
		<input type="hidden" name="step" value="$next">
		<input type="hidden" name="uchidden" value="$uchidden">
		<input type="hidden" name="agree" value="yes">
		<input type="button" class="btn oldbtn" name="exit" value="{$lang_agreement_no}"  onclick="location.href='https://www.discuz.vip'">
		<input type="submit" id="agree" class="btn" name="submit" disabled value="{$lang_agreement_yes}(10)">
		</form>
	</div>
	<script type="text/javascript">
		var currentSeconds = 10;
		var t = setInterval(function(){
			if(currentSeconds == 0) {
				agreeUnlock();
				return;
			}
			document.getElementById('agree').value = '{$lang_agreement_yes}(' + currentSeconds + ')';
			currentSeconds--;
		}, 1000);
		const licenseBox = document.getElementById('license');
	        const scrollArrows = document.getElementById('scrollArrows');
	        licenseBox.addEventListener('scroll', () => {
	            const { scrollTop, scrollHeight, clientHeight } = licenseBox;
	            if (scrollTop + clientHeight >= scrollHeight - 10) {
	                scrollArrows.classList.add('hidden');
	            } else {
	                scrollArrows.classList.remove('hidden');
	            }
	        });
		function agreeUnlock() {
			document.getElementById('agree').disabled = false;
			document.getElementById('agree').value = '{$lang_agreement_yes}';
			clearInterval(t);
		}
	</script>
EOT;

		show_footer();

	}
}

function get_langs() {
	$lang_items = [];
	foreach(glob(ROOT_PATH.'./source/i18n/*') as $langdir) {
		if(is_dir($langdir) && file_exists($langdir.'/lang.php') && file_exists($langdir.'/install/lang_install.php')) {
			require $langdir.'/lang.php';
			$lang_items[basename($langdir)] = $lang['name'];
		}
	}
	return $lang_items;
}

function set_lang() {
	if(!empty($_GET['lang'])) {
		if($_GET['lang'] != '_') {
			$lang_items = get_langs();
			$v = $lang_items[$_GET['lang']] ? $_GET['lang'] : 'SC_UTF8';
			setcookie('LANG', $v, time() + 86400);
			$_COOKIE['LANG'] = $v;
		} else {
			setcookie('LANG', '', -1);
			$_COOKIE['LANG'] = '';
		}
	}

	define('INSTALL_LANG', !empty($_COOKIE['LANG']) ? $_COOKIE['LANG'] : (!empty($_config['lang']) ? $_config['lang'] : 'EN'));
}

function show_select_lang() {

	$version = DISCUZ_VERSION;
	$version_title = lang('version_title');

	show_header();

	$langs = '';
	$select = lang('select');
	$lang_items = get_langs();
	foreach($lang_items as $k => $v) {
		$langs .= '<input type="button" class="btn" onclick="location.href=\'?lang='.$k.'\'" value="'.$v.'" style="margin: 0 10px;">';
	}

	echo <<<EOT
</div>
<div class="main" id="startdiv">
	<div class="startblock">
		<div class="start">
			<h1>$select</h1>
		</div>
	</div>
	<div class="btnbox">
		$langs
	</div>
EOT;

	show_footer();
	exit;

}

function show_version_notice() {
	global $self, $uchidden, $step, $instid;
	$next = $step;
	if(VIEW_OFF) {

		show_msg('license_contents', lang('version_notice'), 1);

	} else {

		$version = DISCUZ_VERSION;
		$version_title = lang('version_title');

		show_header();

		$back = lang('click_to_back');
		$notice = str_replace('  ', '&nbsp; ', lang('version_notice'));
		$start_install = lang('start_install');

		echo <<<EOT
</div>
<div class="main" id="startdiv">
	<div class="startblock">
		<div class="start">
			<h1>Discuz! $version $version_title</h1>
			$notice
		</div>
	</div>
	<div class="btnbox">
		<form method="get" autocomplete="off" action="index.php" class="inputbox">
		<input type="hidden" name="step" value="$next">
		<input type="hidden" name="start" value="yes">
		<input type="hidden" name="uchidden" value="$uchidden">
		<input type="button" class="btn oldbtn" name="exit" value="$back" onclick="location.href='?lang=_'">
		<input type="submit" class="btn" name="submit" value="{$start_install}">
		</form>
	</div>
EOT;

		show_footer();

	}
}

function transfer_ucinfo(&$post) {
	global $uchidden;
	if(isset($post['ucapi']) && isset($post['ucfounderpw'])) {
		$arr = array(
			'ucapi' => $post['ucapi'],
			'ucfounderpw' => $post['ucfounderpw']
		);
		$uchidden = urlencode(serialize($arr));
	} else {
		$uchidden = '';
	}
}

function createtable($sql, $dbver) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('INNODB', 'MYISAM', 'HEAP', 'MEMORY')) ? $type : 'INNODB';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		" ENGINE=$type DEFAULT CHARSET=".DBCHARSET.
		(DBCHARSET === 'utf8mb4' ? " COLLATE=utf8mb4_unicode_ci" : "");
}

function dir_writeable($dir) {
	$writeable = 0;
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function dir_clear($dir) {
	global $lang;
	showjsmessage($lang['clear_dir'].' '.str_replace(ROOT_PATH, '', $dir)."\n");
	if($directory = @dir($dir)) {
		while($entry = $directory->read()) {
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			}
		}
		$directory->close();
		@touch($dir.'/index.htm');
	}
}

function show_header() {
	define('SHOW_HEADER', TRUE);
	global $step;
	$version_name = DISCUZ_VERSION_NAME;
	$version = DISCUZ_VERSION;
	$mversion_name = MITFRAME_VERSION_NAME;
	$mversion = MITFRAME_VERSION;
	$subversion = DISCUZ_SUBVERSION;
	$release = DISCUZ_RELEASE;
	$install_lang = lang('name');
	$title = RUN_MODE == 'install' ? lang('title_install') : lang('title_tool');
	$titlehtml = '
<svg width="127.78282px" height="22.5px" viewBox="0 0 127.78282 22.5" version="1.1" xmlns="http://www.w3.org/2000/svg"
     xmlns:xlink="http://www.w3.org/1999/xlink">
	<defs>
		<linearGradient x1="54.9591121%" y1="0%" x2="54.959052%" y2="100%" id="linearGradient-ose6cjh84e-1">
			<stop stop-color="#E8A833" offset="0%"></stop>
			<stop stop-color="#EBC874" offset="51.5905084%"></stop>
			<stop stop-color="#AE7222" offset="100%"></stop>
		</linearGradient>
	</defs>
	<g id="Discuz" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
		<g fill="url(#linearGradient-ose6cjh84e-1)" fill-rule="nonzero">
			<path d="M8.98281976,3.2 L5.28281976,20.7 L7.98281976,20.7 C10.5828198,20.7 12.7828198,20.1 14.4828198,18.9 C16.1828198,17.7 17.6828198,14 18.1828198,11.9 C18.6828198,9.7 18.6828198,6 17.4828198,4.9 C16.2828198,3.7 14.2828198,3.2 11.5828198,3.2 L8.98281976,3.2 Z M3.88281976,1.7 L11.1828198,1.7 C13.2828198,1.7 15.0828198,1.8 16.5828198,2.1 C18.0828198,2.3 19.1828198,2.7 20.0828198,3.2 C21.6828198,4.1 22.7828198,5.3 23.3828198,6.8 C23.9828198,8.3 24.0828198,10 23.6828198,12 C23.1828198,14 22.3828198,15.8 21.1828198,17.2 C19.9828198,18.7 18.2828198,19.9 16.2828198,20.8 C15.0828198,21.3 13.6828198,21.7 12.0828198,22 C10.4828198,22.2 8.58281976,22.4 6.28281976,22.4 L2.28281976,22.4 C0.782819756,22.4 -0.317180244,21.2 0.0828197562,19.5 L3.88281976,1.7 Z M28.5828198,2.4 L33.6828198,2.4 C32.2828198,9.2 30.8828198,15.6 29.4828198,22.3 L26.5828198,22.3 C24.7828198,22.3 24.5828198,21.2 24.8828198,19.9 L28.5828198,2.4 Z M31.8828198,20.6 C34.2828198,20.6 39.9828198,20.7 42.6828198,20.7 C46.0828198,20.5 46.8828198,19.3 47.2828198,16.9 C47.5828198,15.5 44.9828198,14 42.2828198,13 C35.7828198,10.6 34.8828198,5.5 40.3828198,2.9 C42.4828198,1.9 44.8828198,1.6 47.8828198,1.6 C48.6828198,1.6 49.4828198,1.7 50.3828198,1.9 C51.3828198,2.1 52.6828198,2.3 54.3828198,2.8 L53.7828198,4.5 C50.5828198,4 48.9828198,3.6 46.4828198,3.6 C45.3828198,3.6 44.4828198,3.9 43.7828198,4.1 C40.9828198,5.2 42.3828198,8 44.7828198,9.3 C47.6828198,10.8 50.9828198,11.6 51.9828198,13.2 C52.5828198,14.2 53.0828198,15.2 52.7828198,16.6 C52.3828198,18.5 51.3828198,19.6 49.2828198,20.7 C47.1828198,21.8 44.5828198,22.4 41.3828198,22.4 L31.3828198,22.4 L31.8828198,20.6 L31.8828198,20.6 Z M80.6828198,3.6 L78.7828198,13.6 C78.3828198,15.5 77.8828198,17.5 78.5828198,18.3 C79.8828198,20.2 84.8828198,20 86.2828198,18.5 C87.4828198,17.3 88.3828198,15.3 88.7828198,13 L90.5828198,3.4 L95.9828198,3.4 L92.6828198,22 L87.7828198,22 L88.1828198,19.9 C86.0828198,21.7 83.6828198,22.4 80.0828198,22.4 C77.1828198,22.4 75.4828198,21.7 74.2828198,20.2 C73.0828198,18.7 72.7828198,16.6 73.2828198,13.8 L75.2828198,3.7 C73.8828198,3.5 71.0828198,3.6 69.8828198,3.6 C67.4828198,3.6 64.8828198,4.3 63.2828198,5.5 C58.5828198,8.8 57.4828198,20 66.9828198,20.3 C67.7828198,20.4 69.9828198,19.9 70.8828198,19.7 L71.0828198,19.7 L70.7828198,21.7 C70.2828198,21.8 68.5828198,22.2 68.2828198,22.3 C67.2828198,22.5 66.3828198,22.5 65.4828198,22.5 C61.4828198,22.5 58.4828198,21.6 56.4828198,19.6 C54.4828198,17.7 53.7828198,15.1 54.3828198,11.9 C54.8828198,8.9 56.6828198,6.4 59.4828198,4.5 C62.2828198,2.6 65.2828198,1.5 69.6828198,1.5 C73.2828198,1.5 75.3828198,1.5 78.5828198,1.6 C81.1828198,1.7 80.8828198,2.9 80.6828198,3.6 M98.6828198,3.2 L114.98282,3.2 C115.58282,5.4 114.88282,7.4 112.78282,9.2 L100.48282,20.5 L113.58282,20.5 L113.58282,22.4 L95.4828198,22.4 C95.6828198,19.5 95.7828198,18.5 98.4828198,15.9 L109.88282,5.3 L98.4828198,5.3 L98.6828198,3.2 L98.6828198,3.2 Z M122.78282,22.2 L121.18282,22.1 L121.08282,18.8 L122.98282,19 L124.58282,19.1 L124.28282,22.3 L122.78282,22.2 L122.78282,22.2 Z M124.38282,16.4 L121.68282,16.1 C121.78282,14.6 121.28282,3.5 121.18282,2.2 L121.08282,8.8817842e-16 C121.78282,8.8817842e-16 121.68282,8.8817842e-16 124.88282,0.4 C126.88282,0.6 127.18282,0.7 127.78282,0.7 L126.98282,3.5 C126.28282,5.7 124.58282,15.6 124.38282,16.4"></path>
		</g>
	</g>
</svg>
'.(RUN_MODE == 'install' ? lang('install_wizard') : lang('tool_wizard'));
	$mitframehtml = '
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="51" viewBox="0 -2 62.8641357421875 16.128326416015625">
	<defs>
		<linearGradient x1="54.9591121%" y1="0%" x2="54.959052%" y2="100%" id="linearGradient-dksicudr-1">
			<stop stop-color="#E8A833" offset="0%"></stop>
			<stop stop-color="#EBC874" offset="51.5905084%"></stop>
			<stop stop-color="#AE7222" offset="100%"></stop>
		</linearGradient>
	</defs>
	<g id="MitFrame" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
		<g fill="url(#linearGradient-dksicudr-1)" fill-rule="nonzero">
			<path d="M12.5477 14.233L10.0516 14.233L11.1005 9.28766L11.3759 8.02789L11.329 8.02789L10.8954 9.07086L8.5282 14.233L6.20203 14.233L5.9032 9.36383C5.86804 8.87164 5.86804 8.02789 5.86804 8.02789L5.81531 8.02789C5.64539 9.32281 5.42273 10.3951 5.42273 10.3951L4.60828 14.233L2.36414 14.233L4.13953 5.83063L7.69031 5.83063L7.98328 10.8521C7.98328 11.233 7.95398 11.6431 7.95398 11.6431L7.98914 11.6431C8.08875 11.3619 8.19421 11.0543 8.19421 11.0543C8.29968 10.7466 8.41101 10.5005 8.41101 10.5005L10.4618 5.83063L14.329 5.83063L12.5477 14.233Z"></path>
			<path d="M18.079 6.55133C18.079 7.10797 17.6718 7.45367 17.6718 7.45367C17.2645 7.79938 16.6024 7.79938 16.6024 7.79938C16.0165 7.79938 15.6473 7.49176 15.6473 7.49176C15.2782 7.18414 15.2782 6.72125 15.2782 6.72125C15.2782 6.15875 15.6884 5.81012 15.6884 5.81012C16.0985 5.46148 16.7665 5.46148 16.7665 5.46148C17.3407 5.46148 17.7098 5.76324 17.7098 5.76324C18.079 6.065 18.079 6.55133 18.079 6.55133ZM16.1571 14.3795C15.2782 14.3795 14.7684 13.9166 14.7684 13.9166C14.2587 13.4537 14.2587 12.6861 14.2587 12.6861C14.2587 12.3111 14.3817 11.772 14.3817 11.772L15.1493 8.23297L17.6454 8.23297L16.913 11.69C16.8544 11.9361 16.8544 12.147 16.8544 12.147C16.8544 12.481 17.2587 12.481 17.2587 12.481C17.5868 12.481 17.9208 12.3345 17.9208 12.3345L17.5341 14.1509C17.3641 14.233 17.0009 14.3062 17.0009 14.3062C16.6376 14.3795 16.1571 14.3795 16.1571 14.3795Z"></path>
			<path d="M22.6317 9.96148L21.2841 9.96148L20.9384 11.5904C20.8563 11.9713 20.8563 12.1939 20.8563 12.1939C20.8563 12.5513 21.2723 12.5513 21.2723 12.5513C21.6239 12.5513 22.0751 12.3873 22.0751 12.3873L21.7177 14.1158C21.4423 14.2213 20.9354 14.3004 20.9354 14.3004C20.4286 14.3795 20.0243 14.3795 20.0243 14.3795C18.2958 14.3795 18.2958 12.7447 18.2958 12.7447C18.2958 12.5338 18.3749 12.1177 18.3749 12.1177C18.454 11.7017 18.8231 9.96148 18.8231 9.96148L17.9149 9.96148L18.3309 8.23297L19.1864 8.23297L19.4735 6.85016L22.0751 6.2525L21.6591 8.23297L23.0477 8.23297L22.6317 9.96148Z"></path>
			<path d="M29.4579 7.81109L26.7216 7.81109L26.411 9.32281L28.9071 9.32281L28.4735 11.3033L25.9774 11.3033L25.368 14.233L22.8368 14.233L24.6122 5.83063L29.8798 5.83063L29.4579 7.81109Z"></path>
			<path d="M34.3153 10.3716L34.3036 10.3716C34.0106 10.2252 33.6649 10.2252 33.6649 10.2252C33.0555 10.2252 32.6776 10.5767 32.6776 10.5767C32.2997 10.9283 32.1063 11.7838 32.1063 11.7838L31.5907 14.233L29.0946 14.233L29.9091 10.4068C30.1962 9.07086 30.2841 8.23297 30.2841 8.23297L32.7391 8.23297C32.6747 8.80133 32.5868 9.38727 32.5868 9.38727L32.6102 9.38727C32.9559 8.7193 33.3573 8.40289 33.3573 8.40289C33.7587 8.08648 34.2919 8.08648 34.2919 8.08648C34.5145 8.08648 34.8192 8.16266 34.8192 8.16266L34.3153 10.3716Z"></path>
			<path d="M40.8895 14.233L38.4169 14.233C38.4169 14.0455 38.5048 13.1666 38.5048 13.1666L38.4813 13.1666C37.5907 14.3795 36.4774 14.3795 36.4774 14.3795C35.5927 14.3795 35.1093 13.7642 35.1093 13.7642C34.6259 13.149 34.6259 12.0357 34.6259 12.0357C34.6259 11.2388 34.9335 10.4654 34.9335 10.4654C35.2411 9.69195 35.7831 9.17633 35.7831 9.17633C36.3251 8.6607 37.1132 8.37359 37.1132 8.37359C37.9012 8.08648 38.9325 8.08648 38.9325 8.08648C40.3446 8.08648 41.8973 8.44977 41.8973 8.44977L41.118 12.1236C41.0243 12.5396 40.9569 13.1783 40.9569 13.1783C40.8895 13.817 40.8895 14.233 40.8895 14.233ZM39.1493 9.70953C39.0087 9.67438 38.7919 9.67438 38.7919 9.67438C38.3231 9.67438 37.9481 9.94391 37.9481 9.94391C37.5731 10.2134 37.3417 10.7525 37.3417 10.7525C37.1102 11.2916 37.1102 11.8306 37.1102 11.8306C37.1102 12.1822 37.2626 12.3668 37.2626 12.3668C37.4149 12.5513 37.6376 12.5513 37.6376 12.5513C38.0594 12.5513 38.3934 12.0738 38.3934 12.0738C38.7274 11.5963 38.9208 10.7056 38.9208 10.7056L39.1493 9.70953Z"></path>
			<path d="M52.995 9.56305C52.995 10.0025 52.8661 10.5826 52.8661 10.5826L52.0985 14.233L49.6024 14.233L50.3114 10.9107C50.4052 10.5357 50.4052 10.3072 50.4052 10.3072C50.4052 9.91461 50.0067 9.91461 50.0067 9.91461C49.8134 9.91461 49.5585 10.1109 49.5585 10.1109C49.3036 10.3072 49.1102 10.6265 49.1102 10.6265C48.9169 10.9459 48.8466 11.2798 48.8466 11.2798L48.243 14.233L45.7352 14.233L46.4442 10.9107C46.5438 10.5123 46.5438 10.3072 46.5438 10.3072C46.5438 9.91461 46.1454 9.91461 46.1454 9.91461C45.829 9.91461 45.4598 10.3336 45.4598 10.3336C45.0907 10.7525 44.9794 11.2798 44.9794 11.2798L44.3583 14.233L41.8505 14.233L42.7001 10.2486C42.9052 9.27594 43.0516 8.23297 43.0516 8.23297L45.5067 8.23297C45.4657 8.93609 45.3895 9.26422 45.3895 9.26422L45.413 9.26422C45.8934 8.7193 46.5175 8.40289 46.5175 8.40289C47.1415 8.08648 47.6923 8.08648 47.6923 8.08648C49.0341 8.08648 49.1219 9.4107 49.1219 9.4107C49.579 8.80719 50.2382 8.44684 50.2382 8.44684C50.8973 8.08648 51.5594 8.08648 51.5594 8.08648C52.995 8.08648 52.995 9.56305 52.995 9.56305Z"></path>
			<path d="M59.827 9.87945C59.827 10.9107 58.8807 11.4146 58.8807 11.4146C57.9344 11.9185 55.9657 11.9771 55.9657 11.9771L55.9657 12.024C55.9657 12.3638 56.2528 12.5543 56.2528 12.5543C56.5399 12.7447 57.0204 12.7447 57.0204 12.7447C57.4481 12.7447 58.0487 12.5982 58.0487 12.5982C58.6493 12.4517 59.036 12.2525 59.036 12.2525L58.6903 13.8873C58.2333 14.1041 57.58 14.2418 57.58 14.2418C56.9266 14.3795 56.2762 14.3795 56.2762 14.3795C55.0165 14.3795 54.2958 13.7115 54.2958 13.7115C53.5751 13.0435 53.5751 11.8834 53.5751 11.8834C53.5751 10.8287 54.0702 9.93805 54.0702 9.93805C54.5653 9.04742 55.4354 8.56695 55.4354 8.56695C56.3055 8.08648 57.4423 8.08648 57.4423 8.08648C58.579 8.08648 59.203 8.54938 59.203 8.54938C59.827 9.01227 59.827 9.87945 59.827 9.87945ZM57.5243 9.86773C57.5243 9.73883 57.4452 9.63336 57.4452 9.63336C57.3661 9.52789 57.1669 9.52789 57.1669 9.52789C56.7743 9.52789 56.4901 9.82672 56.4901 9.82672C56.2059 10.1255 56.1063 10.6705 56.1063 10.6705C56.7684 10.6705 57.1464 10.4713 57.1464 10.4713C57.5243 10.272 57.5243 9.86773 57.5243 9.86773Z"></path>
		</g>
	</g>
</svg>
	';
	$nostep = $step > 0 ? '' : ' nostep';
	$charset = CHARSET;
	$reldisp = is_numeric(DISCUZ_RELEASE) ? ('Release '.DISCUZ_RELEASE) : DISCUZ_RELEASE;
	echo <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta charset="$charset" />
<meta name="renderer" content="webkit" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>$title</title>
<link rel="stylesheet" href="static/style.css" type="text/css" media="all" />
<script type="text/javascript">
	function $(id) {
		return document.getElementById(id);
	}

	function showmessage(message) {
		document.getElementById('notice').innerHTML += message + '<br />';
	}
</script>
<meta content="Discuz! Team" name="Copyright" />
</head>
<body>
<div class="gear"></div>
<div class="container{$nostep}">
	<div class="header">
		<h1>$titlehtml</h1>
		<div>
		<p>$version_name $version $install_lang</p>
		<p><em>$version$subversion $reldisp / $mitframehtml <b>$mversion</b></em></p>
		</div>
EOT;

	$step > 0 && show_step($step);
	echo "\r\n";
	echo str_repeat('  ', 1024 * 4);
	echo "\r\n";
	flush();
	ob_flush();
}

function show_footer($quit = true) {

	$copy = lang('copyright');

	echo <<<EOT
		<div class="footer">$copy</div>
	</div>
</div>
</body>
</html>
EOT;
	$quit && exit();
}

function sse_exception_handler($exception) {
	sse_output(lang('failed').' '.nl2br(strip_tags($exception->getMessage())));
}

function showjsmessage($message) {
	if(VIEW_OFF) return;
	sse_output($message);
}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	if($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length--;
	}
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

function secrandom($length, $numeric = 0, $strong = false) {
	// Thank you @popcorner for your strong support for the enhanced security of the function.
	$chars = $numeric ? array('A', 'B', '+', '/', '=') : array('+', '/', '=');
	$num_find = str_split('CDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
	$num_repl = str_split('01234567890123456789012345678901234567890123456789');
	$isstrong = false;
	if(function_exists('random_bytes')) {
		$isstrong = true;
		$random_bytes = function($length) {
			return random_bytes($length);
		};
	} elseif(extension_loaded('mcrypt') && function_exists('mcrypt_create_iv')) {
		// for lower than PHP 7.0, Please Upgrade ASAP.
		$isstrong = true;
		$random_bytes = function($length) {
			$rand = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
			if($rand !== false && strlen($rand) === $length) {
				return $rand;
			} else {
				return false;
			}
		};
	} elseif(extension_loaded('openssl') && function_exists('openssl_random_pseudo_bytes')) {
		// for lower than PHP 7.0, Please Upgrade ASAP.
		// openssl_random_pseudo_bytes() does not appear to cryptographically secure
		// https://github.com/paragonie/random_compat/issues/5
		$isstrong = true;
		$random_bytes = function($length) {
			$rand = openssl_random_pseudo_bytes($length, $secure);
			if($secure === true) {
				return $rand;
			} else {
				return false;
			}
		};
	}
	if(!$isstrong) {
		return $strong ? false : random($length, $numeric);
	}
	$retry_times = 0;
	$return = '';
	while($retry_times < 128) {
		$getlen = $length - strlen($return); // 33% extra bytes
		$bytes = $random_bytes(max($getlen, 12));
		if($bytes === false) {
			return false;
		}
		$bytes = str_replace($chars, '', base64_encode($bytes));
		$return .= substr($bytes, 0, $getlen);
		if(strlen($return) == $length) {
			return $numeric ? str_replace($num_find, $num_repl, $return) : $return;
		}
		$retry_times++;
	}
}

function redirect($url) {

	echo "<script>".
		"function redirect() {window.location.replace('$url');}\n".
		"setTimeout('redirect();', 0);\n".
		"</script>";
	exit();

}

function validate_ip($ip) {
	return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

function get_onlineip() {
	$onlineip = $_SERVER['REMOTE_ADDR'];
	if(isset($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
		$onlineip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ",") > 0) {
			$exp = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
			$onlineip = validate_ip(trim($exp[0])) ? $exp[0] : $onlineip;
		} else {
			$onlineip = validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $onlineip;
		}
	}
	return $onlineip;
}

function timezone_set($timeoffset = 8) {
	if(function_exists('date_default_timezone_set')) {
		@date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
	}
}

function save_config_file($filename, $config, $default) {
	$config = setdefault($config, $default);
	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$content = <<<EOT
<?php


\$_config = array();

EOT;
	$content .= getvars(array('_config' => $config));
	$content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)." //\r\n\r\n?>";
	file_put_contents($filename, $content);
}

function setdefault($var, $default) {
	foreach($default as $k => $v) {
		if(!isset($var[$k])) {
			$var[$k] = $default[$k];
		} elseif(is_array($v)) {
			$var[$k] = setdefault($var[$k], $default[$k]);
		}
	}
	return $var;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	// 动态密钥长度, 通过动态密钥可以让相同的 string 和 key 生成不同的密文, 提高安全性
	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	// a参与加解密, b参与数据验证, c进行密文随机变换
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

	// 参与运算的密钥组
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	// 前 10 位用于保存时间戳验证数据有效性, 10 - 26位保存 $keyb , 解密时通过其验证数据完整性
	// 如果是解码的话会从第 $ckey_length 位开始, 因为密文前 $ckey_length 位保存动态密匙以保证解密正确
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	// 产生密钥簿
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	// 打乱密钥簿, 增加随机性
	// 类似 AES 算法中的 SubBytes 步骤
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	// 从密钥簿得出密钥进行异或，再转成字符
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		// 这里按照算法对数据进行验证, 保证数据有效性和完整性
		// $result 01 - 10 位是时间, 如果小于当前时间或为 0 则通过
		// $result 10 - 26 位是加密时的 $keyb , 需要和入参的 $keyb 做比对
		if(((int)substr($result, 0, 10) == 0 || (int)substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) === substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		// 把动态密钥保存在密文里, 并用 base64 编码保证传输时不被破坏
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function show_db_install($upgrade = false) {
	if(VIEW_OFF) return;
	global $dbhost, $dbuser, $dbpw, $dbname, $tablepre, $username, $password, $email, $uid, $myisam2innodb, $_config;
	$dzucfull = DZUCFULL;
	$dzucstl = DZUCSTL ? 1 : 0;
	$succlang = 'initdbdataresult_succ';
	$allinfo = base64_encode(serialize(compact('dbhost', 'dbuser', 'dbpw', 'dbname', 'tablepre', 'username', 'password', 'email', 'dzucfull', 'dzucstl', 'uid', 'myisam2innodb')));
	?>

	<div class="box">
		<div class="desc" id="lastmsg"><?= lang('install_in_processed') ?></div>
		<div class="progress">
			<div class="move" id="pgb"></div>
		</div>
		<div id="notice"></div>
	</div>
	<div class="btnbox">
		<input type="button" class="btn" name="submit" value="<?php echo lang('install_in_processed'); ?>"
		       disabled="disabled" id="laststep" onclick="initinput()">
	</div>
	<script type="text/javascript">
		var eventSource = null;
		var count = 0;

		function sse_start(url, cb) {
			eventSource = new EventSource(url);
			eventSource.onmessage = function (e) {
				cb(e.data);
			};
			eventSource.onerror = function (e) {
				add_instfail();
				append_notice('<p class="red">Connection error</p>');
			};
		}

		function append_notice(str) {
			document.getElementById('notice').innerHTML += str;
			document.getElementById('notice').scrollTop = 100000000;
		}

		function refresh_lastmsg() {
			document.getElementById('lastmsg').innerHTML = document.querySelector('#notice>p:last-child').outerHTML;
		}

		function add_instfail() {
			document.querySelector('.box').classList.add('instfail');
			document.getElementById('notice').scrollTop = 100000000;
			eventSource.close();
		}

		function refresh_progress() {
			// 进度条的总数，需要跟进实际安装情况修改
			var total = <?php echo !$upgrade ? 338 : 107; ?>;
			var percent = document.querySelectorAll('#notice>p').length * 95 / total;
			percent = (percent > 95) ? 95 : percent;
			document.getElementById('pgb').style.width = percent + '%';
		}


		sse_start('index.php?<?= http_build_query(array('method' => !$upgrade ? 'do_db_init' : 'do_db_upgrade', 'allinfo' => $allinfo)) ?>', function (data) {
			if (data) {
				append_notice(
					data.trim().split("\n").map(function (l) {
						if (l.indexOf('<?= lang('failed') ?>') !== -1) {
							return '<p class="red">' + l + '</p>';
						} else if (!l) {
							return '';
						} else {
							return '<p>' + l + '</p>';
						}
					}).join('')
				);
				count++;
				console.log(count);
				refresh_lastmsg();
				refresh_progress();
				if (data.indexOf('<?= lang('failed') ?>') !== -1) {
					append_notice('<p class="red"><?= lang('error_quit_msg') ?></p>');
					add_instfail();
					return;
				}
				if (data.indexOf('<?= lang($succlang) ?>') !== -1) {
					eventSource.close();

					sse_start('../misc.php?mod=initsys<?php if($upgrade) {
						echo '&force='.rawurlencode(authcode(time(), 'ENCODE', $_config['security']['authkey']));
					} ?>', function (data) {
						if (data === 'Done') {
							append_notice('<p><?= lang('initsys').lang('succeed') ?></p>');
							document.getElementById('pgb').style.width = '100%';
							document.getElementById('pgb').className = '';
							document.getElementById('laststep').value = '<?= lang('succeed') ?>';
							document.getElementById('laststep').disabled = false;
							window.setTimeout(function () {
								window.location = 'index.php?method=ext_info';
							}, 1000);
						} else {
							append_notice('<p class="red">' + data + '<br /><?= lang('error_quit_msg') ?></p>');
							add_instfail();
						}
						eventSource.close();
					});
				}
			}

		})
	</script>
	<?php
}

function runquery($sql, $upgrade = false) {
	global $lang, $tablepre, $db;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(' '.ORIG_TABLEPRE, ' '.$tablepre, $sql));
	$sql = str_replace("\r", "\n", str_replace(' `'.ORIG_TABLEPRE, ' `'.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	$oldtablename = '';
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			if(str_starts_with($query, 'CREATE TABLE')) {
				$name = preg_replace("/CREATE TABLE\s+[\`]?([a-z0-9_]+)[\`]?\s*\(.*/is", "\\1", $query);
				if($db->query(createtable($query, $db->version()), 'SILENT')) {
					showjsmessage(lang('create_table').' '.$name.'  ... '.lang('succeed')."\n");
				} else {
					showjsmessage(lang('create_table').' '.$name.'  ... '.lang('failed').': '.$err."\n");
					if(!$upgrade) {
						return false;
					}
				}
			} elseif(str_starts_with($query, 'INSERT')) {
				$name = preg_replace("/INSERT\s+INTO\s+[\`]?([a-z0-9_]+)[\`]? .*/is", "\\1", $query);
				if($db->query($query, 'SILENT')) {
					if($oldtablename != $name) {
						showjsmessage(lang('init_table_data').' '.$name.'  ... '.lang('succeed')."\n");
						$oldtablename = $name;
					}
				} else {
					$err = $db->error();
					if($upgrade && str_contains($err, 'Duplicate')) {
						continue;
					}
					showjsmessage(lang('init_table_data').' '.$name.'  ... '.lang('failed').': '.$err."\n");
					if(!$upgrade) {
						return false;
					}
				}
			} else {
				if(!$db->query($query, 'SILENT')) {
					$err = $db->error();
					if($upgrade && (str_contains($err, 'Duplicate') || str_contains($err, 'doesn\'t exist'))) {
						continue;
					}
					showjsmessage(lang('failed').': '.$err."\n");
					if(!$upgrade) {
						return false;
					}
				}
				if(substr($query, 0, 11) == 'ALTER TABLE') {
					$name = preg_match("/ALTER\s+TABLE\s+[\`]?([a-z0-9_]+)[\`]?/is", $query, $r);
					if($r) {
						showjsmessage(lang('alter_table_data').' '.$r[1].'  ... '.lang('succeed')."\n");
					}
				}
			}
		}
	}
	return true;
}

function runucquery($sql, $tablepre) {
	global $lang, $db;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(' uc_', ' '.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE\s+[\`]?([a-z0-9_]+)[\`]?\s*\(.*/is", "\\1", $query);
				if($db->query(createtable($query, $db->version()))) {
					showjsmessage(lang('create_table').' '.$name.' ... '.lang('succeed')."\n");
				} else {
					showjsmessage(lang('create_table').' '.$name.' ... '.lang('failed')."\n");
					return false;
				}
			} else {
				if(!$db->query($query)) {
					showjsmessage(lang('failed')."\n");
					return false;
				}
			}

		}
	}
	return true;
}

function rundatasql($f, $upgrade = false) {
	global $tablepre, $db;

	$db->query('START TRANSACTION', 'SILENT');

	$dir = ROOT_PATH.'./source/i18n/'.INSTALL_LANG.'/install/'.$f;
	$oldtablename = '';
	foreach(glob($dir.'/*.php') as $file) {
		$table = basename($file, '.php');
		$table = str_replace('table_', '', $table);
		$name = $tablepre.$table;
		$sql = 'INSERT INTO `'.$name.'` SET ';
		$data = [];
		require_once $file;
		foreach($data as $row) {
			$fields = [];
			foreach($row as $field => $value) {
				if(is_array($value)) {
					$value = serialize($value);
				}
				$value = addcslashes($value, '\'');
				$fields[] = "`{$field}`='{$value}'";
			}
			$sqlline = $sql.implode(', ', $fields);

			if($db->query($sqlline, 'SILENT')) {
				if($oldtablename != $name) {
					showjsmessage(lang('init_table_data').' '.$name.'  ... '.lang('succeed')."\n");
					$oldtablename = $name;
				}
			} else {
				$err = $db->error();
				if($upgrade && str_contains($err, 'Duplicate')) {
					continue;
				}
				showjsmessage(lang('init_table_data').' '.$name.'  ... '.lang('failed').': '.$err."\n");
			}
		}
	}

	$db->query('COMMIT', 'SILENT');
}

function charcovert($string) {
	return str_replace('\'', '\\\'', $string);
}

function insertconfig($s, $find, $replace) {
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		$s .= "\r\n".$replace;
	}
	return $s;
}

function getgpc($k, $t = 'GP') {
	$t = strtoupper($t);
	switch($t) {
		case 'GP' :
			isset($_POST[$k]) ? $var = &$_POST : $var = &$_GET;
			break;
		case 'G':
			$var = &$_GET;
			break;
		case 'P':
			$var = &$_POST;
			break;
		case 'C':
			$var = &$_COOKIE;
			break;
		case 'R':
			$var = &$_REQUEST;
			break;
	}
	return isset($var[$k]) ? $var[$k] : null;
}

function var_to_hidden($k, $v) {
	return "<input type=\"hidden\" name=\"$k\" value=\"$v\" />\n";
}

function fsocketopen($hostname, $port = 80, &$errno = null, &$errstr = null, $timeout = 15) {
	$fp = '';
	if(function_exists('fsockopen')) {
		$fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('pfsockopen')) {
		$fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($hostname.':'.$port, $errno, $errstr, $timeout);
	}
	return $fp;
}

function sse_header() {
	@set_time_limit(0);
	@ignore_user_abort(true);
	ini_set('max_execution_time', 0);
	ini_set('mysql.connect_timeout', 0);

	header_remove();
	ob_end_clean();
	ob_implicit_flush();
	header('X-Accel-Buffering: no');
	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');
	header('Connection: keep-alive');
	header('Access-Control-Allow-Origin: *');
	ob_start();
}

function sse_output($message, $close = false) {
	echo "data:{$message}\n\n";
	ob_flush();
	flush();
}

function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype = 'URLENCODE', $allowcurl = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$scheme = strtolower($matches['scheme']);
	$host = $matches['host'];
	$path = !empty($matches['path']) ? $matches['path'].(!empty($matches['query']) ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : ($scheme == 'https' ? 443 : 80);

	if(function_exists('curl_init') && function_exists('curl_exec') && $allowcurl) {
		$ch = curl_init();
		$ip && curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: ".$host));
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		// 在提供 IP 地址的同时, 当请求主机名并非一个合法 IP 地址, 且 PHP 版本 >= 5.5.0 时, 使用 CURLOPT_RESOLVE 设置固定的 IP 地址与域名关系
		// 在不支持的 PHP 版本下, 继续采用原有不支持 SNI 的流程
		if(!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP) && version_compare(PHP_VERSION, '5.5.0', 'ge')) {
			curl_setopt($ch, CURLOPT_RESOLVE, array("$host:$port:$ip"));
			curl_setopt($ch, CURLOPT_URL, $scheme.'://'.$host.':'.$port.$path);
		} else {
			curl_setopt($ch, CURLOPT_URL, $scheme.'://'.($ip ? $ip : $host).':'.$port.$path);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($post) {
			curl_setopt($ch, CURLOPT_POST, 1);
			if($encodetype == 'URLENCODE') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			} else {
				parse_str($post, $postarray);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
			}
		}
		if($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		if($errno || $status['http_code'] != 200) {
			return;
		} else {
			return !$limit ? $data : substr($data, 0, $limit);
		}
	}

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$header = "Accept: */*\r\n";
		$header .= "Accept-Language: zh-cn\r\n";
		if($allowcurl) {
			$encodetype = 'URLENCODE';
		}
		$boundary = $encodetype == 'URLENCODE' ? '' : '; boundary='.trim(substr(trim($post), 2, strpos(trim($post), "\n") - 2));
		$header .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data$boundary\r\n";
		$header .= "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
		$header .= "Host: $host:$port\r\n";
		$header .= 'Content-Length: '.strlen($post)."\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Cache-Control: no-cache\r\n";
		$header .= "Cookie: $cookie\r\n\r\n";
		$out .= $header.$post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$header = "Accept: */*\r\n";
		$header .= "Accept-Language: zh-cn\r\n";
		$header .= "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
		$header .= "Host: $host:$port\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Cookie: $cookie\r\n\r\n";
		$out .= $header;
	}

	$fpflag = 0;
	$context = array();
	if($scheme == 'https') {
		$context['ssl'] = array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'peer_name' => $host
		);
	}
	if(ini_get('allow_url_fopen')) {
		$context['http'] = array(
			'method' => $post ? 'POST' : 'GET',
			'header' => $header,
			'timeout' => $timeout
		);
		if($post) {
			$context['http']['content'] = $post;
		}
		$context = stream_context_create($context);
		$fp = @fopen($scheme.'://'.($ip ? $ip : $host).':'.$port.$path, 'b', false, $context);
		$fpflag = 1;
	} elseif(function_exists('stream_socket_client')) {
		$context = stream_context_create($context);
		$fp = @stream_socket_client(($scheme == 'https' ? 'ssl://' : '').($ip ? $ip : $host).':'.$port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
	} else {
		$fp = @fsocketopen(($scheme == 'https' ? 'ssl://' : '').($scheme == 'https' ? $host : ($ip ? $ip : $host)), $port, $errno, $errstr, $timeout);
	}

	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		if(!$fpflag) {
			@fwrite($fp, $out);
		}
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while(!feof($fp) && !$fpflag) {
				if(($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

function show_error($type, $errors = '', $quit = false) {

	global $lang, $step;

	$title = lang($type);
	$comment = lang($type.'_comment', false);
	$errormsg = '';
	if($errors) {
		if(!empty($errors)) {
			foreach((array)$errors as $k => $v) {
				if(is_numeric($k)) {
					$comment .= "<li><em class=\"red\">".lang($v)."</em></li>";
				}
			}
		}
	}

	if($step > 0) {
		echo "<div class=\"desc\"><b>$title</b><ul>$comment</ul>";
	} else {
		echo "<div><b>$title</b><ul style=\"line-height: 200%; margin-left: 30px;\">$comment</ul>";
	}

	if($quit) {
		echo '<br /><span class="red">'.$lang['error_quit_msg'].'</span><br /><br /><br /><br /><br /><br />';
	}

	echo '</div>';

	$quit && show_footer();
}

function show_tips($tip, $title = '', $comment = '', $style = 1) {
	global $lang;
	$title = empty($title) ? lang($tip) : $title;
	$comment = empty($comment) ? lang($tip.'_comment', FALSE) : $comment;
	if($style) {
		echo "<div class=\"desc\">$title";
	} else {
		echo "</div><div class=\"main\">$title<div class=\"desc1 marginbot\"><ul>";
	}
	$comment && print('<div class="comm">'.$comment.'</div>');
	echo "</div>";
}

function show_setting($setname, $varname = '', $value = '', $type = 'text|password|checkbox', $error = '') {
	if($setname == 'start') {
		echo "<form method=\"post\" action=\""._FILE_."\">\n";
		return;
	} elseif($setname == 'end') {
		echo "\n</form>\n";
		return;
	} elseif($setname == 'hidden') {
		echo "<input type=\"hidden\" name=\"$varname\" value=\"$value\">\n";
		return;
	}

	echo "\n".'<div class="inputbox'.($error ? ' red' : '').'">';
	if($type == 'text' || $type == 'password') {
		echo "\n".'<label class="tbopt" for="inst_'.$varname.'">'.(empty($setname) ? '' : lang($setname).':')."</label>\n";
		$value = dhtmlspecialchars($value);
		echo "<input type=\"$type\" id=\"inst_{$varname}\" name=\"$varname\" value=\"$value\" class=\"txt\">";
	} elseif(strpos($type, 'submit') !== FALSE) {
		if(strpos($type, 'oldbtn') !== FALSE) {
			echo "<input type=\"button\" name=\"oldbtn\" value=\"".lang('old_step')."\" class=\"btn oldbtn\" onclick=\"history.back();\">\n";
		}
		$value = empty($value) ? 'new_step' : $value;
		echo "<input type=\"submit\" name=\"$varname\" value=\"".lang($value)."\" class=\"btn\">\n";
	} elseif($type == 'checkbox') {
		if(!is_array($varname) && !is_array($value)) {
			echo "<input type=\"checkbox\" class=\"ckb\" id=\"$varname\" name=\"$varname\" value=\"1\"".($value ? 'checked="checked"' : '')."><label for=\"$varname\">".lang($setname.'_check_label')."</label>\n";
		}
	} else {
		echo $value;
	}

	if($error) {
		$comment = '<div class="comm red">'.(is_string($error) ? lang($error) : lang($setname.'_error')).'</div>';
	} else {
		$comment = lang($setname.'_comment', false);
		if($comment) {
			$comment = '<div class="comm">'.$comment.'</div>';
		}
	}
	echo "$comment\n</div>\n";
	return true;
}

function show_step($step) {

	global $method;

	$laststep = 4;
	$title = lang('step_'.$method.'_title');
	$comment = lang('step_'.$method.'_desc');
	$step_title_1 = lang('step_title_1');
	$step_title_2 = lang('step_title_2');
	if($step < 10) {
		$step_title_3 = lang('step_title_3');
		$step_title_4 = lang('step_title_4');
	} else {
		$step_title_3 = lang('step_title_3u');
		$step_title_4 = lang('step_title_4u');
		$step = $step - 7;
	}

	$stepclass = array();
	for($i = 1; $i <= $laststep; $i++) {
		$stepclass[$i] = $i == $step ? 'current' : ($i < $step ? '' : 'unactivated');
	}
	$stepclass[$laststep] .= ' last';

	echo <<<EOT
</div>
<div class="setup">
	<div>
		<div class="step step{$step}">
			<div class="stepnum">{$step}</div>
			<div>
				<h2>$title</h2>
				<p>$comment</p>
			</div>
		</div>
		<div class="stepstat">
			<div class="stepstattxt">
				<div class="$stepclass[1]">$step_title_1</div>
				<div class="$stepclass[2]">$step_title_2</div>
				<div class="$stepclass[3]">$step_title_3</div>
				<div class="$stepclass[4]">$step_title_4</div>
			</div>
			<div class="stepstatbg stepstat{$step}"></div>
		</div>
	</div>
</div>
<div class="main">
EOT;

}

function lang($lang_key, $force = true) {
	return isset($GLOBALS['lang'][$lang_key]) ? $GLOBALS['lang'][$lang_key] : ($force ? $lang_key : '');
}

function check_adminuser($username, $password, $email) {

	include ROOT_PATH.CONFIG_UC;
	include ROOT_PATH.'./source/class/uc/client.php';

	$error = '';
	$ucresult = uc_user_login($username, $password);
	list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email']) = uc_addslashes($ucresult);
	$ucresult = $tmp;
	if($ucresult['uid'] <= 0) {
		$uid = uc_user_register($username, $password, $email);
		if($uid == -1 || $uid == -2) {
			$error = 'admin_username_invalid';
		} elseif($uid == -4 || $uid == -5 || $uid == -6) {
			$error = 'admin_email_invalid';
		} elseif($uid == -3) {
			$error = 'admin_exist_password_error';
		}
	} else {
		$uid = $ucresult['uid'];
		$email = $ucresult['email'];
		$password = $ucresult['password'];
	}

	if(!$error && $uid > 0) {
		$password = md5($password);
		uc_user_addprotected($username, '');
	} else {
		$uid = 0;
		$error = empty($error) ? 'error_unknow_type' : $error;
	}
	return array('uid' => $uid, 'username' => $username, 'password' => $password, 'email' => $email, 'error' => $error);
}

function save_uc_config($config, $file) {

	list($appauthkey, $appid, $ucdbhost, $ucdbname, $ucdbuser, $ucdbpw, $ucdbcharset, $uctablepre, $uccharset, $ucapi, $ucip, $dzucstl) = $config;
	mysqli_report(MYSQLI_REPORT_OFF);

	$link = new mysqli($ucdbhost, $ucdbuser, $ucdbpw, $ucdbname);
	$uc_connnect = $link ? 'mysql' : '';

	if($dzucstl) {
		$dbconfig = '
require \'config_global.php\';		
define(\'UC_DBHOST\', $_config[\'db\'][1][\'dbhost\']);
define(\'UC_DBUSER\', $_config[\'db\'][1][\'dbuser\']);
define(\'UC_DBPW\', $_config[\'db\'][1][\'dbpw\']);
define(\'UC_DBNAME\', $_config[\'db\'][1][\'dbname\']);
define(\'UC_DBTABLEPRE\', \'`\'.$_config[\'db\'][1][\'dbname\'].\'`.\'.$_config[\'db\'][1][\'tablepre\'].\'ucenter_\');
define(\'UC_KEY\', $_config[\'security\'][\'authkey\']);
';
	} else {
		$dbconfig = <<<EOT
define('UC_DBHOST', '$ucdbhost');
define('UC_DBUSER', '$ucdbuser');
define('UC_DBPW', '$ucdbpw');
define('UC_DBNAME', '$ucdbname');
define('UC_DBTABLEPRE', '`$ucdbname`.$uctablepre');
define('UC_KEY', '$appauthkey');
EOT;
	}

	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$year = date('Y');
	$config = <<<EOT
<?php

define('UC_CONNECT', '$uc_connnect');
define('UC_STANDALONE', $dzucstl);

$dbconfig
define('UC_DBCHARSET', '$ucdbcharset');
define('UC_DBCONNECT', 0);

define('UC_AVTURL', '');
define('UC_AVTPATH', '');

define('UC_CHARSET', '$uccharset');
define('UC_API', '$ucapi');
define('UC_APPID', '$appid');
define('UC_IP', '$ucip');
define('UC_PPP', 20);
?>
EOT;

	if(file_put_contents($file, $config) !== false) {
		return true;
	}

	return false;
}

function _generate_key($length = 32) {
	$random = secrandom($length);
	$info = md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_NAME'].(isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '').(isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '').$_SERVER['HTTP_USER_AGENT'].time());
	$return = '';
	for($i = 0; $i < $length; $i++) {
		$return .= $random[$i].$info[$i];
	}
	return $return;
}

function install_uc_sql() {
	global $db, $dbhost, $dbuser, $dbpw, $dbname, $tablepre, $username, $password, $email, $dzucstl, $myisam2innodb;

	$ucsql = read_sql('sql_uc');
	$uctablepre = $tablepre.'ucenter_';
	$ucsql = str_replace(' uc_', ' '.$uctablepre, $ucsql);
	if($ucsql) {
		if(!runucquery($ucsql, $uctablepre)) {
			exit();
		}
	}
	$appauthkey = _generate_key();
	$ucdbhost = $dbhost;
	$ucdbname = $dbname;
	$ucdbuser = $dbuser;
	$ucdbpw = $dbpw;
	$ucdbcharset = DBCHARSET;

	$uccharset = CHARSET;

	$pathinfo = pathinfo($_SERVER['PHP_SELF']);
	$pathinfo['dirname'] = substr($pathinfo['dirname'], 0, -8);
	$isHTTPS = is_https();
	$appurl = 'http'.($isHTTPS ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$pathinfo['dirname'];
	$ucapi = '';
	$ucip = '';

	$db->query("INSERT INTO {$uctablepre}applications SET name='Discuz! Board', url='$appurl', ip='$ucip', authkey='$appauthkey', synlogin='1', charset='$uccharset', dbcharset='$ucdbcharset', type='DISCUZX', recvnote='1', tagtemplates=''");
	$appid = $db->insert_id();
	$db->query("ALTER TABLE {$uctablepre}notelist ADD COLUMN app$appid tinyint NOT NULL");

	$config = array($appauthkey, $appid, $ucdbhost, $ucdbname, $ucdbuser, $ucdbpw, $ucdbcharset, $uctablepre, $uccharset, $ucapi, $ucip, $dzucstl);
	save_uc_config($config, ROOT_PATH.'./config/config_ucenter.php');

	$salt = '';
	$passwordhash = password_hash($password, PASSWORD_BCRYPT);
	$db->query("INSERT INTO {$uctablepre}members SET username='$username', password='$passwordhash', email='$email', regip='hidden', regdate='".time()."', salt='$salt'");
	$uid = $db->insert_id();
	$db->query("INSERT INTO {$uctablepre}memberfields SET uid='$uid'");

	$db->query("INSERT INTO {$uctablepre}admins SET
		uid='$uid',
		username='$username',
		allowadminsetting='1',
		allowadminapp='1',
		allowadminuser='1',
		allowadminbadword='1',
		allowadmincredits='1',
		allowadmintag='1',
		allowadminpm='1',
		allowadmindomain='1',
		allowadmindb='1',
		allowadminnote='1',
		allowadmincache='1',
		allowadminlog='1'");
}

function install_data($username, $uid) {
	global $_G, $db, $tablepre;
	showjsmessage(lang('install_data')." ... "."\n");

	$_G = array('db' => $db, 'tablepre' => $tablepre, 'uid' => $uid, 'username' => $username);

	showjsmessage(lang('install_data').lang('succeed')."\n");
}

function all_done() {
	@unlink(ROOT_PATH.'./install/'._FILE_);
	show_header();
	echo '</div><div class="main">';
	echo '<div class="box">';
	if(file_exists(ROOT_PATH.'./install/'._FILE_)) {
		show_tips('all_done_exists');
	} else {
		show_tips('all_done_noexists');
	}
	echo '</div>';
	show_footer();
	exit;
}

function getvars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
			continue;
		}
		if(is_array($val)) {
			$evaluate .= buildarray($val, 0, "\${$key}")."\r\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}

function buildarray($array, $level = 0, $pre = '$_config') {
	static $ks;
	$return = '';

	if($level == 0) {
		$ks = array();
	}

	foreach($array as $key => $val) {
		if(!preg_match("/^[a-zA-Z0-9_\x7f-\xff]+$/", $key)) {
			continue;
		}

		if($level == 0) {
			$newline = str_pad('  CONFIG '.strtoupper($key).'  ', 70, '-', STR_PAD_BOTH);
			$return .= "\r\n// $newline //\r\n";
			if($key == 'admincp') {
				$newline = str_pad(' Founders: $_config[\'admincp\'][\'founder\'] = \'1,2,3\'; ', 70, '-', STR_PAD_BOTH);
				$return .= "// $newline //\r\n";
			}
		}
		$ks[$level] = $level ? $ks[$level - 1] : '';
		if(is_int($key)) {
			$ks[$level] .= '['.$key.']';
		} else {
			$ks[$level] .= "['$key']";
		}
		if(is_array($val)) {
			$return .= buildarray($val, $level + 1, $pre);
		} else {
			$val = is_string($val) || strlen($val) > 12 || ($val !== 0 && !preg_match("/^\-?[1-9]\d*$/", $val)) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			$return .= $pre.$ks[$level]." = $val;\r\n";
		}
	}
	return $return;
}

function save_diy_data($primaltplname, $targettplname, $data, $database = false) {
	global $_G;
	if(empty($data) || !is_array($data)) return false;

	$_G['curtplbid'] = array();
	$_G['curtplframe'] = array();

	$tpldirectory = './template/default';
	$file = '.'.$tpldirectory.'/'.$primaltplname.'.php';
	$content = file_get_contents(realpath($file));
	foreach($data['layoutdata'] as $key => $value) {
		$html = '';
		$html .= '<div id="'.$key.'" class="area">';
		$html .= getframehtml($value);
		$html .= '</div>';
		$content = preg_replace("/(\<\!\-\-\[diy\=$key\]\-\-\>).+?(\<\!\-\-\[\/diy\]\-\-\>)/is", "\\1".$html."\\2", $content);
	}
	$content = preg_replace("/(\<style id\=\"diy_style\" type\=\"text\/css\"\>).*(\<\/style\>)/is", "\\1".$data['spacecss']."\\2", $content);
	if(!empty($data['style'])) {
		$content = preg_replace("/(\<link id\=\"style_css\" rel\=\"stylesheet\" type\=\"text\/css\" href\=\").+?(\"\>)/is", "\\1".$data['style']."\\2", $content);
	}

	$tplfile = ROOT_PATH.'./data/diy/'.$tpldirectory.'/'.$targettplname.'.htm';

	$tplpath = dirname($tplfile);
	if(!is_dir($tplpath)) dmkdir($tplpath);
	$r = file_put_contents($tplfile, $content);

	if($r && $database) {
		$_G['db']->query('DELETE FROM '.$_G['tablepre'].'common_template_block WHERE targettplname="'.$targettplname.'"');
		if(!empty($_G['curtplbid'])) {
			$values = array();
			foreach($_G['curtplbid'] as $bid) {
				$values[] = "('$targettplname', '$tpldirectory', '$bid')";
			}
			if(!empty($values)) {
				$_G['db']->query("INSERT INTO ".$_G['tablepre']."common_template_block (targettplname, tpldirectory, bid) VALUES ".implode(',', $values));
			}
		}

		$tpldata = daddslashes(serialize($data));
		$_G['db']->query("REPLACE INTO ".$_G['tablepre']."common_diy_data (targettplname, tpldirectory, primaltplname, diycontent) VALUES ('$targettplname', '$tpldirectory', '$primaltplname', '$tpldata')");
	}

	return $r;
}

function getframehtml($data = array()) {
	global $_G;
	$html = $style = '';
	foreach((array)$data as $id => $content) {
		list($flag, $name) = explode('`', $id.'`');
		if($flag == 'frame') {
			$fattr = $content['attr'];
			$moveable = $fattr['moveable'] == 'true' ? ' move-span' : '';
			$html .= '<div id="'.$fattr['name'].'" class="'.$fattr['className'].'">';
			if(checkhastitle($fattr['titles'])) {
				$style = gettitlestyle($fattr['titles']);
				$html .= '<div class="'.implode(' ', $fattr['titles']['className']).'"'.$style.'>'.gettitlehtml($fattr['titles'], 'frame').'</div>';
			}
			foreach((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid.'`');
				if($colflag == 'column') {
					$html .= '<div id="'.$colname.'" class="'.$coldata['attr']['className'].'">';
					$html .= '<div id="'.$colname.'_temp" class="move-span temp"></div>';
					$html .= getframehtml($coldata);
					$html .= '</div>';
				}
			}
			$html .= '</div>';
		} elseif($flag == 'tab') {
			$fattr = $content['attr'];
			$moveable = $fattr['moveable'] == 'true' ? ' move-span' : '';
			$html .= '<div id="'.$fattr['name'].'" class="'.$fattr['className'].'">';
			$switchtype = 'click';
			foreach((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				if($colflag == 'column') {
					if(checkhastitle($fattr['titles'])) {
						$style = gettitlestyle($fattr['titles']);
						$title = gettitlehtml($fattr['titles'], 'tab');
					}
					$switchtype = is_array($fattr['titles']['switchType']) && !empty($fattr['titles']['switchType'][0]) ? $fattr['titles']['switchType'][0] : 'click';
					$html .= '<div id="'.$colname.'" class="'.$coldata['attr']['className'].'"'.$style.' switchtype="'.$switchtype.'">'.$title;
					$html .= getframehtml($coldata);
					$html .= '</div>';
				}
			}
			$html .= '<div id="'.$fattr['name'].'_content" class="tb-c"></div>';
			$html .= '<script type="text/javascript">initTab("'.$fattr['name'].'","'.$switchtype.'");</script>';
			$html .= '</div>';
		} elseif($flag == 'block') {
			$battr = $content['attr'];
			$bid = intval(str_replace('portal_block_', '', $battr['name']));
			if(!empty($bid)) {
				$html .= "<!--{block/{$bid}}-->";
				$_G['curtplbid'][$bid] = $bid;
			}
		}
	}

	return $html;
}

function gettitlestyle($title) {
	$style = '';
	if(is_array($title['style']) && count($title['style'])) {
		foreach($title['style'] as $k => $v) {
			$style .= $k.':'.$v.';';
		}
	}
	$style = $style ? ' style=\''.$style.'\'' : '';
	return $style;
}

function checkhastitle($title) {
	if(!is_array($title)) return false;
	foreach($title as $k => $v) {
		if(strval($k) == 'className') continue;
		if(!empty($v['text'])) return true;
	}
	return false;
}

function gettitlehtml($title, $type) {
	global $_G;
	if(!is_array($title)) return '';
	$html = $one = $style = $color = '';
	foreach($title as $k => $v) {
		if(in_array(strval($k), array('className', 'style'))) continue;
		if(empty($v['src']) && empty($v['text'])) continue;
		$one = "<span class=\"{$v['className']}\"";
		$style = $color = "";
		$style .= empty($v['font-size']) ? '' : "font-size:{$v['font-size']}px;";
		$style .= empty($v['float']) ? '' : "float:{$v['float']};";
		$margin_ = empty($v['float']) ? 'left' : $v['float'];
		$style .= empty($v['margin']) ? '' : "margin-{$margin_}:{$v['margin']}px;";
		$color = empty($v['color']) ? '' : "color:{$v['color']};";
		$img = !empty($v['src']) ? '<img src="'.$v['src'].'" class="vm" alt="'.$v['text'].'"/>' : '';
		if(empty($v['href'])) {
			$style = empty($style) && empty($color) ? '' : ' style="'.$style.$color.'"';
			$one .= $style.">$img{$v['text']}";
		} else {
			$style = empty($style) ? '' : ' style="'.$style.'"';
			$colorstyle = empty($color) ? '' : ' style="'.$color.'"';
			$one .= $style.'><a href="'.$v['href'].'"'.$colorstyle.'>'.$img.$v['text'].'</a>';
		}
		$one .= '</span>';

		$siteurl = str_replace(array('/', '.'), array('\/', '\.'), $_G['siteurl']);
		$one = preg_replace('/\"'.$siteurl.'(.*?)\"/', '"$1"', $one);

		$html = $k === 'first' ? $one.$html : $html.$one;
	}
	return $html;
}

function block_import($data) {
	global $_G;
	if(!is_array($data['block'])) {
		return;
	}
	$data = daddslashes($data);
	$stylemapping = array();
	if($data['style']) {
		$hashes = $styles = array();
		foreach($data['style'] as $value) {
			$hashes[] = $value['hash'];
			$styles[$value['hash']] = $value['styleid'];
		}
		$query = $_G['db']->query('SELECT styleid, hash FROM '.$_G['tablepre']."common_block_style WHERE hash IN (".dimplode($hashes).')');
		while($value = $_G['db']->fetch_array($query)) {
			$id = $styles[$value['hash']];
			$stylemapping[$id] = intval($value['styleid']);
			unset($styles[$value['hash']]);
		}
		foreach($styles as $id) {
			$style = $data['style'][$id];
			$style['styleid'] = '';
			if(is_array($style['template'])) {
				$style['template'] = dstripslashes($style['template']);
				$style['template'] = addslashes(serialize($style['template']));
			}
			$sql = implode_field_value($style);
			$_G['db']->query('INSERT INTO '.$_G['tablepre'].'common_block_style SET '.$sql);
			$newid = $_G['db']->insert_id();
			$stylemapping[$id] = $newid;
		}
	}

	$blockmapping = array();
	foreach($data['block'] as $block) {
		$oid = $block['bid'];
		if(!empty($block['styleid'])) {
			$block['styleid'] = intval($stylemapping[$block['styleid']]);
		}
		$block['bid'] = '';
		$block['uid'] = $_G['uid'];
		$block['username'] = $_G['username'];
		$block['dateline'] = 0;
		if(is_array($block['param'])) {
			$block['param'] = dstripslashes($block['param']);
			$block['param'] = addslashes(serialize($block['param']));
		}
		$sql = implode_field_value($block);
		$_G['db']->query('INSERT INTO '.$_G['tablepre'].'common_block SET '.$sql);
		$newid = $_G['db']->insert_id();
		$blockmapping[$oid] = $newid;
	}
	return $blockmapping;
}

function getframeblock($data) {
	global $_G;

	if(!isset($_G['curtplbid'])) $_G['curtplbid'] = array();
	if(!isset($_G['curtplframe'])) $_G['curtplframe'] = array();

	foreach((array)$data as $id => $content) {
		list($flag, $name) = explode('`', $id.'`');
		if($flag == 'frame' || $flag == 'tab') {
			foreach((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid.'`');
				if($colflag == 'column') {
					getframeblock($coldata);
				}
			}
			$_G['curtplframe'][$name] = array('type' => $flag, 'name' => $name);
		} elseif($flag == 'block') {
			$battr = $content['attr'];
			$bid = intval(str_replace('portal_block_', '', $battr['name']));
			if(!empty($bid)) {
				$_G['curtplbid'][$bid] = $bid;
			}
		}
	}
}

function import_diy($importfile, $primaltplname, $targettplname) {
	global $_G;

	$css = $html = '';
	$arr = array();

	$content = file_get_contents(realpath($importfile));
	require_once ROOT_PATH.'./source/class/class_xml.php';
	if(empty($content)) return $arr;
	$diycontent = xml2array($content);
	$diycontent = is_array($diycontent) ? $diycontent : array();

	if($diycontent) {

		foreach($diycontent['layoutdata'] as $key => $value) {
			if(!empty($value)) getframeblock($value);
		}
		$newframe = array();
		foreach($_G['curtplframe'] as $value) {
			$newframe[] = $value['type'].random(6);
		}

		$mapping = array();
		if(!empty($diycontent['blockdata'])) {
			$mapping = block_import($diycontent['blockdata']);
			unset($diycontent['blockdata']);
		}

		$oldbids = $newbids = array();
		if(!empty($mapping)) {
			foreach($mapping as $obid => $nbid) {
				$oldbids[] = 'portal_block_'.$obid;
				$newbids[] = 'portal_block_'.$nbid;
			}
		}

		require_once ROOT_PATH.'./source/class/class_xml.php';
		$xml = array2xml($diycontent['layoutdata'], true);
		$xml = str_replace($oldbids, $newbids, $xml);
		$xml = str_replace((array)array_keys($_G['curtplframe']), $newframe, $xml);
		$diycontent['layoutdata'] = xml2array($xml);

		$css = str_replace($oldbids, $newbids, $diycontent['spacecss']);
		$css = str_replace((array)array_keys($_G['curtplframe']), $newframe, $css);

		$arr['spacecss'] = $css;
		$arr['layoutdata'] = $diycontent['layoutdata'];
		$arr['style'] = $diycontent['style'];
		save_diy_data($primaltplname, $targettplname, $arr, true);
	}
	return $arr;
}

function dimplode($array) {
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return '';
	}
}

function implode_field_value($array, $glue = ',') {
	$sql = $comma = '';
	foreach($array as $k => $v) {
		$sql .= $comma."`$k`='".(is_string($v) ? $v : '')."'";
		$comma = $glue;
	}
	return $sql;
}

function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function dstripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

function dmkdir($dir, $mode = 0777) {
	if(!is_dir($dir)) {
		dmkdir(dirname($dir), $mode);
		@mkdir($dir, $mode);
		@touch($dir.'/index.htm');
		@chmod($dir.'/index.htm', 0777);
	}
	return true;
}

function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
		if(strpos($string, '&amp;#') !== false) {
			$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
		}
	}
	return $string;
}

function install_extra_setting() {
	global $db, $tablepre, $lang;
	include ROOT_PATH.'./install/include/install_extvar.php';
	foreach($settings as $key => $val) {
		$db->query("REPLACE INTO {$tablepre}common_setting SET skey='$key', svalue='".addslashes(serialize($val))."'");
	}
}

function format_space($space) {
	if($space > 1048576) {
		if($space > 1073741824) {
			return floor($space / 1073741824).'GB';
		} else {
			return floor($space / 1048576).'MB';
		}
	}
	return $space;
}

function read_sql($f) {
	if(!file_exists($file = ROOT_PATH.'./install/sql/'.$f.'.php')) {
		return '';
	}
	$s = file_get_contents($file);
	return str_replace("\r\n", "\n", substr($s, 30));
}

function read_install_log_file() {
	if(file_exists(INST_LOG_PATH)) {
		$offset = intval(getgpc('offset'));
		echo sprintf('%05d', filesize(INST_LOG_PATH));
		if($offset) {
			$fp = fopen(INST_LOG_PATH, 'rb');
			fseek($fp, $offset);
			fpassthru($fp);
		} else {
			readfile(INST_LOG_PATH);
		}
	} else {
		echo '00000';
	}
}

function send_mime_type_header($type = 'application/xml') {
	header("Content-Type: ".$type);
}

function is_https() {
	// PHP 标准服务器变量
	if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
		return true;
	}
	// X-Forwarded-Proto 事实标准头部, 用于反代透传 HTTPS 状态
	if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
		return true;
	}
	// 阿里云全站加速私有 HTTPS 状态头部
	// Git 意见反馈 https://gitee.com/Discuz/DiscuzX/issues/I3W5GP
	if(isset($_SERVER['HTTP_X_CLIENT_SCHEME']) && strtolower($_SERVER['HTTP_X_CLIENT_SCHEME']) == 'https') {
		return true;
	}
	// 西部数码建站助手私有 HTTPS 状态头部
	// 官网意见反馈 https://discuz.dismall.com/thread-3849819-1-1.html
	if(isset($_SERVER['HTTP_FROM_HTTPS']) && strtolower($_SERVER['HTTP_FROM_HTTPS']) != 'off') {
		return true;
	}
	// 服务器端口号兜底判断
	if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
		return true;
	}
	return false;
}

function getmaxupload() {
	$sizeconv = array('B' => 1, 'KB' => 1024, 'MB' => 1048576, 'GB' => 1073741824);
	$sizes = array();
	$sizes[] = ini_get('upload_max_filesize');
	$sizes[] = ini_get('post_max_size');
	$sizes[] = ini_get('memory_limit');
	if(intval($sizes[1]) === 0) {
		unset($sizes[1]);
	}
	if(intval($sizes[2]) === -1) {
		unset($sizes[2]);
	}
	$sizes = preg_replace_callback(
		'/^(\-?\d+)([KMG]?)$/i',
		function($arg) use ($sizeconv) {
			return (intval($arg[1]) * $sizeconv[strtoupper($arg[2]).'B']).'|'.strtoupper($arg[0]);
		},
		$sizes
	);
	natsort($sizes);
	$output = explode('|', current($sizes));
	if(!empty($output[1])) {
		return $output[1];
	} else {
		return ini_get('upload_max_filesize');
	}
}

function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
	global $md5data;
	$dir = @opendir(ROOT_PATH.$currentdir);
	$exts = '/('.$ext.')$/i';
	$skips = explode(',', $skip);

	if(!$dir) {
		return;
	}

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && (($ext && preg_match($exts, $entry) || !$ext) || $sub && is_dir(ROOT_PATH.$file)) && !in_array($entry, $skips)) {
			if($sub && is_dir(ROOT_PATH.$file)) {
				checkfiles($file.'/', $ext, $sub, $skip);
			} else {
				if(is_dir(ROOT_PATH.$file)) {
					$md5data[$file] = md5($file);
				} else {
					$md5data[$file] = md5_file(ROOT_PATH.$file);
				}
			}
		}
	}
}

function checkcachefiles($currentdir) {
	global $_config;
	$dir = opendir(ROOT_PATH.'./'.$currentdir);
	$exts = '/\.php$/i';
	$showlist = $modifylist = $addlist = [];
	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && preg_match($exts, $entry)) {
			$fp = fopen(ROOT_PATH.'./'.$file, 'rb');
			$cachedata = fread($fp, filesize(ROOT_PATH.'./'.$file));
			fclose($fp);

			if(preg_match("/^<\?php\n\/\/Discuz! cache file, DO NOT modify me!\n\/\/Identify: (\w+)\n\n(.+?)\?>$/s", $cachedata, $match)) {
				$showlist[$file] = $md5 = $match[1];
				$cachedata = $match[2];

				if(md5($entry.$cachedata.$_config['security']['authkey']) != $md5) {
					$modifylist[$file] = $md5;
				}
			} else {
				$showlist[$file] = '';
			}
		}
	}

	return [$showlist, $modifylist, $addlist];
}

function unmark_system_plugin() {
	global $tablepre, $db;

	$result = [];
	$db->fetch_all("SELECT * FROM {$tablepre}common_plugin WHERE modules LIKE '%s:6:\"system\";i:2;%'", $result);
	foreach($result as $row) {
		$modules = dunserialize($row['modules']);
		unset($modules['system']);
		$db->query("UPDATE {$tablepre}common_plugin SET modules='".addslashes(serialize($modules))."' WHERE pluginid='".$row['pluginid']."'");
	}
}