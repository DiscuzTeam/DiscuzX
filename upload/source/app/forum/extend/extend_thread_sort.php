<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

namespace forum;

use table_forum_attachment;
use table_forum_attachment_n;
use table_forum_optionvalue;
use table_forum_typeoptionvar;

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_sort extends extend_thread_base {

	public function before_newthread($parameters) {
		global $_G;

		if($_GET['typeoption']) {
			$this->param['extramessage'] = "\t".implode("\t", $_GET['typeoption']);
		}

		$_G['forum_optiondata'] = [];
		if($this->forum['threadsorts']['types'][$parameters['sortid']] && !$this->forum['allowspecialonly']) {
			$_G['forum_optiondata'] = threadsort_validator($_GET['typeoption'], 0);
		}
	}

	public function after_newthread() {
		global $_G;

		$sortid = $this->param['sortid'];
		$pid = $this->pid;

		if($this->forum['threadsorts']['types'][$sortid] && !empty($_G['forum_optiondata']) && is_array($_G['forum_optiondata'])) {
			$sortaids = [];
			$filedname = $valuelist = $separator = '';
			$fid = $this->forum['fid'];
			$tid = $this->tid;
			foreach($_G['forum_optiondata'] as $optionid => $value) {
				if($value) {
					$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
					$valuelist .= $separator."'".daddslashes($value)."'";
					$separator = ' ,';
				}

				if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
					$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
					$sortaids[] = intval($_GET['typeoption'][$identifier]['aid']);
				}
				if($_G['forum_optionlist'][$optionid]['type'] == 'plugin') {
					pluginthreadtype_submit($_G['forum_optionlist'][$optionid], $value);
				}
				$typeexpiration = intval($_GET['typeexpiration']);

				table_forum_typeoptionvar::t()->insert([
					'sortid' => $sortid,
					'tid' => $tid,
					'fid' => $fid,
					'optionid' => $optionid,
					'value' => $value,
					'expiration' => ($typeexpiration ? $this->param['publishdate'] + $typeexpiration : 0),
				]);
			}

			if($filedname && $valuelist) {
				table_forum_optionvalue::t()->insert_optionvalue($sortid, "($filedname, tid, fid) VALUES ($valuelist, '{$tid}', '$fid')");
			}

			if($sortaids) {
				foreach($sortaids as $sortaid) {
					convertunusedattach($sortaid, $tid, $pid);
				}
			}
		}

	}

	public function before_editpost($parameters) {
		global $_G;
		$sortid = $parameters['sortid'];
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {

			$parameters['typeid'] = isset($this->forum['threadtypes']['types'][$parameters['typeid']]) ? $parameters['typeid'] : 0;
			if(!$this->forum['ismoderator'] && !empty($this->forum['threadtypes']['moderators'][$this->thread['typeid']])) {
				$parameters['typeid'] = $this->thread['typeid'];
			}
			$parameters['sortid'] = isset($this->forum['threadsorts']['types'][$parameters['sortid']]) ? $parameters['sortid'] : 0;
			$typeexpiration = intval($_GET['typeexpiration']);

			if(!$parameters['typeid'] && $this->forum['threadtypes']['required'] && !$this->thread['special']) {
				showmessage('post_type_isnull');
			}


			if($this->forum['threadsorts']['types'][$sortid] && $_G['forum_checkoption']) {
				$_G['forum_optiondata'] = threadsort_validator($_GET['typeoption'], $this->post['pid']);
			}

			$this->param['threadimageaid'] = 0;
			$this->param['threadimage'] = [];

			if($this->forum['threadsorts']['types'][$parameters['sortid']] && $_G['forum_optiondata'] && is_array($_G['forum_optiondata'])) {
				$sql = $separator = $filedname = $valuelist = '';
				foreach($_G['forum_optiondata'] as $optionid => $value) {
					$value = censor(daddslashes($value));
					if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
						$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
						$newsortaid = intval($_GET['typeoption'][$identifier]['aid']);
						if($newsortaid && $_GET['oldsortaid'][$identifier] && $newsortaid != $_GET['oldsortaid'][$identifier]) {
							$attach = table_forum_attachment_n::t()->fetch_attachment('tid:'.$this->thread['tid'], $_GET['oldsortaid'][$identifier]);
							if(!empty($attach) && $attach['tid'] == $this->thread['tid']) {
								table_forum_attachment::t()->delete($_GET['oldsortaid'][$identifier]);
								table_forum_attachment_n::t()->delete_attachment('tid:'.$this->thread['tid'], $_GET['oldsortaid'][$identifier]);
								dunlink($attach);
							}
							$this->param['threadimageaid'] = $newsortaid;
							convertunusedattach($newsortaid, $this->thread['tid'], $this->post['pid']);
						}
					}
					if($_G['forum_optionlist'][$optionid]['type'] == 'plugin') {
						pluginthreadtype_submit($_G['forum_optionlist'][$optionid], $value);
					}
					if($_G['forum_optionlist'][$optionid]['unchangeable']) {
						continue;
					}
					if(($_G['forum_optionlist'][$optionid]['search'] || in_array($_G['forum_optionlist'][$optionid]['type'], ['radio', 'select', 'number'])) && $value) {
						$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
						$valuelist .= $separator."'$value'";
						$sql .= $separator.$_G['forum_optionlist'][$optionid]['identifier']."='$value'";
						$separator = ' ,';
					}
					table_forum_typeoptionvar::t()->update_by_tid($this->thread['tid'], ['value' => $value, 'sortid' => $parameters['sortid']], false, false, $optionid);
				}

				if($typeexpiration) {
					table_forum_typeoptionvar::t()->update_by_tid($this->thread['tid'], ['expiration' => (TIMESTAMP + $typeexpiration)], false, false, null, $parameters['sortid']);
				}

				if($sql || ($filedname && $valuelist)) {
					if(table_forum_optionvalue::t()->fetch_all_tid($parameters['sortid'], "WHERE tid='".$this->thread['tid']."'")) {
						if($sql) {
							table_forum_optionvalue::t()->update_optionvalue($parameters['sortid'], $this->thread['tid'], $this->forum['fid'], $sql);
						}
					} elseif($filedname && $valuelist) {
						table_forum_optionvalue::t()->insert_optionvalue($parameters['sortid'], "($filedname, tid, fid) VALUES ($valuelist, '".$this->thread['tid']."', '".$this->forum['fid']."')");
					}
				}
			}
		}
	}

	public function after_deletepost() {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {
			table_forum_typeoptionvar::t()->delete_by_tid($this->thread['tid']);
		}
	}
}

