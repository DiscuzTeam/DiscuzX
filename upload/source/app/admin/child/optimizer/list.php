<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$checkrecordtime = table_common_optimizer::t()->fetch_optimizer($check_record_time_key);

if(!$_GET['checking']) {
	showtips('optimizer_'.$_GET['anchor'].'_tips');
}

showboxheader();

echo '<div class="drow" style="margin-bottom: 0px;"><div class="dcol">';
echo $_GET['checking'] ? '<a href="javascript:;" id="checking" class="btn_big">'.$lang['founder_optimizer_checking'].'</a></div>' :
	'<a href="'.ADMINSCRIPT.'?action=optimizer&checking=1&anchor='.$_GET['anchor'].'" id="checking" class="btn_big">'.$lang['founder_optimizer_start_check'].'</a></div>';
if($_GET['checking']) {
	echo '<div class="dcol"><div class="pbg" id="processid">';
	echo '<div class="pbr" style="width: 0;" id="percentprocess"></div>';
	echo '<div class="xs0" id="percent">0%</div>';
	echo '</div></div>';
}
echo '<div id="checkstatus" class="dcol">';
if(!$checkrecordtime) {
	echo $lang['founder_optimizer_first_use'];
} else {
	$num = 0;
	$checkrecordkey = [];
	foreach($optimizer_option as $option) {
		$checkrecordkey[] = $option.'_checkrecord';
	}
	foreach(table_common_optimizer::t()->fetch_all($checkrecordkey) as $checkrecordvalue) {
		if($checkrecordvalue['v'] == 1) {
			$num++;
		}
	}
	if(!$_GET['checking']) {
		echo $lang['founder_optimizer_lastcheck'].dgmdate($checkrecordtime).$lang['founder_optimizer_findnum'].$num.$lang['founder_optimizer_neednum'];
	}
}
echo '</div>';
echo '</div>';
if($_GET['checking']) {
	$inc_unit = ceil(100 / count($optimizer_option));
	$adminscipt = ADMINSCRIPT;
	$C = '$C';
	$formhash = FORMHASH;
	print <<<END
			<script type="text/javascript">
				var checkpercent = 0;
				var checknum = 0;
				var optimize_num = 0;
				var security_num = 0;
				var tip_num = 0;
				var securitygrade = '';
				function updatecheckpercent() {
					checkpercent += {$inc_unit};
					checknum++;
					$('percent').innerHTML = parseInt(checkpercent) + '%';
					$('percentprocess').style.width = parseInt(checkpercent) * 2 + 'px';
				}
				function updatecheckstatus(id, msg, status, type, extraurl) {
					var optimize_table = $('optimizerable');
					var optimize_tablerows = optimize_table.rows.length;
					var security_table = $('securityoption');
					var security_tablerows = security_table.rows.length;
					var tip_table = $('tipoption');
					var tip_tablerows = tip_table.rows.length;

					if(id == 'optimizer_upgrade' || id == 'optimizer_patch') {
						securitygrade = '{$lang['founder_optimizer_low']}';
					}

					var optiontype = id;
					id = 'progress_' + id;
					$(id + '_tr').style.display = 'none';
					var color = 'green';
					if(status == 1) {
						color = 'red';
						optimize_num++;
						$('optimizerablenum').innerHTML = optimize_num;
						optimize_table.style.display = 'block';
						var newtr = optimize_table.insertRow(optimize_tablerows);
						newtr.className = 'ooclass';
					} else if(status == 2) {
						color = 'blue';
						tip_num++;
						$('tipoptionnum').innerHTML = tip_num;
						tip_table.style.display = 'block';
						var newtr = tip_table.insertRow(tip_tablerows);
						newtr.className = 'toclass';
						newtr.style.display = 'none';
					} else {
						color = 'green';
						security_num++;
						$('securityoptionnum').innerHTML = security_num;
						security_table.style.display = 'block';
						var newtr = security_table.insertRow(security_tablerows);
						newtr.className = 'soclass';
						newtr.style.display = 'none';
					}
					var statusstr = '';
					if(status != 0) {
						if(type == 'header') {
							statusstr = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&anchor={$_GET['anchor']}&type='+ optiontype + extraurl + '&formhash={$formhash}' + '" target="_blank">{$lang['founder_optimizer_optimizer']}</a>';
						} else if(type == 'view') {
							statusstr = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&anchor={$_GET['anchor']}&type='+ optiontype + extraurl + '&formhash={$formhash}' + '" target="_blank">{$lang['founder_optimizer_view']}</a>';
						} else if(type == 'scan') {
							statusstr = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&anchor={$_GET['anchor']}&type='+ optiontype + extraurl + '&formhash={$formhash}' + '" target="_blank">{$lang['founder_optimizer_scan']}</a>';
						}
					}
					newtr.insertCell(0).innerHTML = $(id + '_unit').innerHTML;
					newtr.insertCell(1).innerHTML = msg;
					newtr.insertCell(2).innerHTML = statusstr;

					if(parseInt(checkpercent) >= 100) {
						$('checking').innerHTML = '{$lang['founder_optimizer_recheck_js']}';
						$('checking').href = '{$adminscipt}?action=optimizer&checking=1&anchor={$_GET['anchor']}';
						$('processid').style.display = 'none';
						if('{$_GET['anchor']}' == 'security') {
							if(securitygrade == '') {
								if(optimize_num <= 1) {
									securitygrade = '{$lang['founder_optimizer_high']}';
								} else if(optimize_num >=2 && optimize_num <=4) {
									securitygrade = '{$lang['founder_optimizer_middle']}';
								} else {
									securitygrade = '{$lang['founder_optimizer_low']}';
								}
							}
							$('checkstatus').innerHTML = '{$lang['founder_optimizer_check_complete_js']}' + checknum + '{$lang['founder_optimizer_findnum']}' +  optimize_num + '{$lang['founder_optimizer_neednum']}' + ' {$lang['founder_optimizer_level']}: <span style="color:green;font-size:16px;font-weight:700;">' + securitygrade + '</span>';
						} else {
							$('checkstatus').innerHTML = '{$lang['founder_optimizer_check_complete_js']}' + checknum + '{$lang['founder_optimizer_findnum']}' +  optimize_num + '{$lang['founder_optimizer_neednum']}';
						}
					}
				}
				function showoptions(obj, option) {
					var o = $C(option);
					var isopen = 0;
					if(obj.innerHTML == '[-]') {
						isoepn = 0;
						obj.innerHTML = '[+]';
					} else {
						isopen = 1;
						obj.innerHTML = '[-]';
					}
					for(var i=0; i<o.length; i++) {
						if(isopen == 1) {
							o[i].style.display = '';
						} else {
							o[i].style.display = 'none';
						}
					}
				}
				function showlistmore(btnid, classname) {
					var btn = $(btnid);
					var o = $C(classname);
					btn.style.display = 'none';
					for(var i=0; i<o.length; i++) {
						o[i].style.display = 'block';
					}
				}
			</script>
END;
	echo '<table class="tb tb2" style="margin-top:0;">';
	foreach($optimizer_option as $option) {
		echo '<tr class="hover" id="progress_'.$option.'_tr">';
		echo '<td width="200"><div id="progress_'.$option.'_unit">'.$lang['optimizer_check_unit_'.$option].'</td>';
		echo '<td width="350"><div id="progress_'.$option.'">'.$lang['founder_optimizer_checking'].'...</div></td><script type="text/javascript">ajaxget(\''.ADMINSCRIPT.'?action=optimizer&operation=check_unit&type='.$option.'&anchor='.$_GET['anchor'].'\', \'progress_'.$option.'\', \'\', \'\', \'\', updatecheckpercent)</script>';
		echo '<td><div id="progress_'.$option.'_status"></div></td>';
		echo '</tr>';
	}
	echo '</table>';

	echo '<table id="optimizerable" class="tb tb2" style="margin-top:0;display:none;">';
	echo '<tr><td width="300" style="color:red;font-weight:700;"><a href="javascript:;" onclick="showoptions(this, \'ooclass\')">[-]</a> '.$lang['founder_optimizer_needopti'].'(<span id="optimizerablenum"></span>)</td><td width="350"></td><td width="100"></td></tr>';
	echo '</table>';
	echo '<table id="securityoption" class="tb tb2" style="margin-top:0;display:none;">';
	echo '<tr><td width="300" style="color:green;font-weight:700;"><a href="javascript:;" onclick="showoptions(this, \'soclass\')">[+]</a> '.$lang['founder_optimizer_safe'].'(<span id="securityoptionnum"></span>)</td><td width="350"></td><td width="100"></td></tr>';
	echo '</table>';
	echo '<table id="tipoption" class="tb tb2" style="margin-top:0;display:none;">';
	echo '<tr><td width="300" style="color:blue;font-weight:700;"><a href="javascript:;" onclick="showoptions(this, \'toclass\')">[+]</a> '.$lang['founder_optimizer_notice'].'(<span id="tipoptionnum"></span>)</td><td width="350"></td><td width="100"></td></tr>';
	echo '</table>';
}

showboxfooter();
	