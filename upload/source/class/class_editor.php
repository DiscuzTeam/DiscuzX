<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class editor {

	private static $r_dzTpl = [
		"/[\n\r\t]*\{block\/(\d+?)\}[\n\r\t]*/i",
		"/[\n\r\t]*\{blockdata\/(\d+?)\}[\n\r\t]*/i",
		"/[\n\r\t]*\{ad\/(.+?)\}[\n\r\t]*/i",
		"/[\n\r\t]*\{ad\s+([a-zA-Z0-9_\[\]]+)\/(.+?)\}[\n\r\t]*/i",
		"/[\n\r\t]*\{date\((.+?)\)\}[\n\r\t]*/i",
		"/[\n\r\t]*\{avatar\((.+?)\)\}[\n\r\t]*/i",
		"/[\n\r\t]*\{eval\}\s*(\<\!\-\-)*(.+?)(\-\-\>)*\s*\{\/eval\}[\n\r\t]*/is",
		"/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/is",
		"/[\n\r\t]*\{csstemplate\}[\n\r\t]*/is",
		"/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\s(or|\?\?)\s([a-zA-Z0-9\']+)\}/s",
		"/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s",
		'/\{hook\/(\w+?)(\s+(.+?))?\}/i',
		"/((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)/s",
		"/\<\?\=\<\?\=((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)\?\>\?\>/s",
		"/[\n\r\t]*\{template\s+([a-z0-9_:\/]+)\}[\n\r\t]*/is",
		"/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is",
		"/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/is",
		"/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/is",
		"/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/is",
		'/\{else\}/i',
		'/\{\/if\}/i',
		"/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/is",
		"/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/is",
		'/\{\/loop\}/i',
		"/[\n\r\t]*\{block\s+([a-zA-Z0-9_\[\]']+)\}(.+?)\{\/block\}/is",
	];
	private static $r_php = [
		'/<\?/',
		'/\?>/',
	];
	private static $r_js = [
		'/javascrit:/i',
		'/<script(.*?)>/i',
		'/\s+on(?!ly\b)([a-zA-Z]+)/i',
	];

	public static function checkTemplate($template) {
		$template = preg_replace('/\<\!\-\-\{(.+?)\}\-\-\>/s', "{\\1}", $template);
		foreach(self::$r_dzTpl as $r) {
			if(preg_match($r, $template)) {
				return false;
			}
		}
		foreach(self::$r_php as $r) {
			if(preg_match($r, $template)) {
				return false;
			}
		}
		foreach(self::$r_js as $r) {
			if(preg_match($r, $template)) {
				return false;
			}
		}
		return true;
	}

	public static function toArray($content) {
		$content = json_decode($content, true);
		return $content;
	}

	public static function getBlockTemplate($type) {
		$editorblock_parser = $editorblock_style = '';
		$editorblock = memory('get', 'editorblock_'.$type);
		if($editorblock && $editorblock['parser'] && $editorblock['style']) {
			$editorblock_parser = $editorblock['parser'];
			$editorblock_style = $editorblock['style'];
			$editorblock_static = empty($editorblock['plugin']) ? 'static/js/editorjs/tools/'.$type.'/' : 'source/plugin/'.$editorblock['plugin'].'/editorblock/tools/'.$type.'/';
		} else {
			$editorblock = table_common_editorblock::t()->fetch_by_block_class($type);
			$editorblock_parser = $editorblock['parser'];
			$editorblock_style = $editorblock['style'];
			$editorblock_static = empty($editorblock['plugin']) ? 'static/js/editorjs/tools/'.$type.'/' : 'source/plugin/'.$editorblock['plugin'].'/editorblock/tools/'.$type.'/';
			memory('set', 'editorblock_'.$type, $editorblock);
		}
		return [$editorblock_parser, $editorblock_style, $editorblock_static];
	}

	public static function getBlockParser($tpl, $block) {
		$parser = $tpl;
		return $parser;
	}

	public static function parser($content, $context = []) {
		global $_G;
		$blocksData = self::toArray($content);

		$itemid = isset($context['itemid']) ? intval($context['itemid']) : intval($_G['tid'] ?? 0);
		$authorid = isset($context['authorid']) ? intval($context['authorid']) : intval($_G['forum_thread']['authorid'] ?? 0);
		$parserData = '';
		$styleData = ['types' => [], 'style' => ''];
		foreach($blocksData['blocks'] as $key => $value) {
			[$editorblock_parser, $editorblock_style, $editorblock_static] = self::getBlockTemplate($value['type']);

			$allowParser = true;
			$isHideTuneBlock = false;
			if($value['tunes']['hideTune']['hide']) {
				$isHideTuneBlock = true;
				$authorreplyexist = null;
				if($_G['uid']) {
					$authorreplyexist = table_forum_post::t()->fetch_pid_by_tid_authorid($itemid, $_G['uid']);
				}
				if(!$authorreplyexist) {
					$allowParser = false;
				}
				if($_G['adminid'] > 0) {
					$allowParser = true;
				}
			}

			$payLocked = false;
			$payCredits = 0;
			$payBlockId = '';
			$payNeedLogin = false;
			if(!empty($value['tunes']['payTune']['pay']) && !empty($value['tunes']['payTune']['credits'])) {
				$payCredits = intval($value['tunes']['payTune']['credits']);
				if($payCredits > 0) {
					$payBlockId = self::getBlockId($value);

					if($allowParser) {
						if(!$_G['uid']) {
							$allowParser = false;
							$payLocked = true;
							$payNeedLogin = true;
						} elseif($_G['adminid'] <= 0 && $authorid != $_G['uid']) {
							if(!self::isBlockPaid($_G['uid'], $itemid, $payBlockId)) {
								$allowParser = false;
								$payLocked = true;
							}
						}
					}
				}
			}
			if(!$allowParser) {
				if($payLocked) {

					$creditid = intval($_G['setting']['creditstransextra'][1]);
					$credititle = !empty($_G['setting']['extcredits'][$creditid]['title']) ? $_G['setting']['extcredits'][$creditid]['title'] : 'credits';
					$creditunit = !empty($_G['setting']['extcredits'][$creditid]['unit']) ? $_G['setting']['extcredits'][$creditid]['unit'] : '';

					$credit_display = $credititle.($creditunit ? ' '.$creditunit : '');
					if($payNeedLogin) {
						$parser = '<div class="locked payblock-locked"><span class="pb-text">'.lang('forum/template', 'post_payblock_login_text').'</span> <a href="member.php?mod=logging&action=login" onclick="showWindow(\'login\', this.href)">'.lang('template', 'login').'</a></div>';
					} else {
						$payurl = 'forum.php?mod=misc&action=payblock&tid='.$itemid.'&block='.$payBlockId;
						$parser = '<div class="locked payblock-locked">'
							.'<span class="pb-text">'.lang('forum/template', 'post_payblock_hidden_text', ['credits' => $payCredits, 'credit_display' => $credit_display]).'</span> '
							.'<a href="'.$payurl.'" onclick="showWindow(\'payblock\', this.href)" class="pb-btn">'.lang('forum/template', 'post_payblock_pay_button', ['credits' => $payCredits, 'credit_display' => $credit_display]).'</a>'
							.'</div>';
					}
				} else {
					$parser = '<div class="locked">'.($_G['uid'] ? $_G['username'] : lang('forum/template', 'guest')).lang('forum/template', 'post_hide_reply_hidden_text').'<a href="forum.php?mod=post&action=reply&fid='.$_G['fid'].'&tid='.$itemid.'" onclick="showWindow(\'reply\', this.href)">'.lang('forum/template', 'reply').'</a></div>';
				}
			} else {
				$parser = (new editorBlock($editorblock_parser, $value, $editorblock_static))->replace();
				if($isHideTuneBlock) {
					$parser = '<div class="showhide"><h4>'.lang('forum/template', 'post_hide').'</h4>'.$parser.'</div>';
				}
			}

			$anchorparse = explode(',', getglobal('setting/anchorparse')) ?? [];
			if(in_array($value['type'], $anchorparse)) {
				$parser = preg_replace_callback('/<a\s+href=[\'"]#([^\'"]+)[\'"](.*?)>/s', function($m) {
					$href = $m[1];

					$clean = preg_replace('/[^a-zA-Z0-9_\-]/', '', $href);
					if($clean === '' || $clean !== $href) {
						return '<a href="#'.$href.'"'.$m[2].'>';
					}

					$onclick = "javascript: document.getElementById('".$clean."').scrollIntoView();return false;";
					return '<a href="#'.$clean.'" onclick="'.$onclick.'"'.$m[2].'>';
				}, $parser);
			}

			$parserData .= $parser;
			if(!in_array($value['type'], $styleData['types'])) {
				$styleData['types'][] = $value['type'];
				$styleData['style'] .= $editorblock_style;
			}
		}
		return [$parserData, $styleData['style']];
	}

	public static function getBlockId($block) {
		if(!empty($block['id'])) {
			$id = preg_replace('/[^a-zA-Z0-9_\-]/', '', strval($block['id']));
			if($id !== '') {
				return substr($id, 0, 64);
			}
		}
		return 'md5'.substr(md5((isset($block['type']) ? $block['type'] : '').'#'.(isset($block['data']) ? json_encode($block['data']) : '')), 0, 28);
	}

	public static function isBlockPaid($uid, $tid, $blockid) {
		static $cache = [];
		$key = $uid.'_'.$tid.'_'.$blockid;
		if(isset($cache[$key])) {
			return $cache[$key];
		}
		$count = table_common_credit_log::t()->count_by_uid_operation_relatedid_memo($uid, 'BPB', $tid, 'block:'.$blockid);
		$cache[$key] = $count > 0;
		return $cache[$key];
	}

}

class editorBlock {
	var $html = '';
	var $block = [];
	var $static = '';

	private static $urlAttrs = ['src', 'href', 'action', 'formaction', 'poster', 'xlink:href', 'data', 'cite', 'longdesc', 'background', 'srcset', 'dynsrc', 'lowsrc', 'usemap'];
	var $ctxMap = null;
	var $ctxOcc = [];

	private function buildContextMap($template) {

		$occ = [];
		$map = [];
		$propose = function($offset, $length, $ctx) use (&$occ) {

			foreach($occ as &$o) {
				if($o['start'] === $offset && $o['end'] === $offset + $length) {
					if($this->ctxSeverity($ctx) < $this->ctxSeverity($o['ctx'])) {
						$o['ctx'] = $ctx;
					}
					return;
				}
			}
			$occ[] = ['start' => $offset, 'end' => $offset + $length, 'ctx' => $ctx];
		};

		if(preg_match_all('/<textarea\b[^>]*>.*?<\/textarea\s*>/is', $template, $ta, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
			foreach($ta as $seg) {
				$segStart = $seg[0][1];
				foreach($this->extractPlaceholders($seg[0][0], $segStart) as $ph) {
					$propose($ph['start'], $ph['end'] - $ph['start'], 'textarea');

					if(preg_match('/^\{([a-zA-Z\.]+)\}$/', substr($seg[0][0], $ph['start'] - $segStart, $ph['end'] - $ph['start']), $pm)) {
						$map[$pm[1]] = 'textarea';
					}
				}
			}
		}

		if(preg_match_all('/<\s*\{[a-zA-Z\.]+\}/', $template, $tg, PREG_OFFSET_CAPTURE)) {
			foreach($tg[0] as $t) {
				foreach($this->extractPlaceholders($t[0], $t[1]) as $ph) {
					$propose($ph['start'], $ph['end'] - $ph['start'], 'tag');
					if(preg_match('/^\{([a-zA-Z\.]+)\}$/', substr($t[0], $ph['start'] - $t[1], $ph['end'] - $ph['start']), $pm)) {
						$map[$pm[1]] = 'tag';
					}
				}
			}
		}

		if(preg_match_all('/<([a-zA-Z][a-zA-Z0-9]*)(\s[^<>]*)?>/', $template, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
			foreach($tags as $t) {
				$attrs = isset($t[2]) ? $t[2][0] : '';
				if($attrs === '' || strpos($attrs, '{') === false) {
					continue;
				}
				$attrsOffset = $t[2][1];

				$attrsScan = preg_replace_callback('/\[\/?if\b[^\]]*\]/i', function($mm) {
					return str_repeat(' ', strlen($mm[0]));
				}, $attrs);
				if($attrsScan === null || strpos($attrsScan, '{') === false) {
					continue;
				}
				if(preg_match_all('/([a-zA-Z_][a-zA-Z0-9_:.\-]*)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))/', $attrsScan, $am, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
					foreach($am as $a) {
						$attrName = strtolower($a[1][0]);
						$rawVal = $a[2][0];
						if(strpos($rawVal, '{') === false) {
							continue;
						}
						$ctx = 'attr';
						if(in_array($attrName, self::$urlAttrs)) {
							$ctx = 'urlattr';
						} elseif($attrName === 'style') {
							$ctx = 'styleattr';
						}

						if(isset($a[5]) && $a[5][0] !== null && $a[5][0] !== '') {
							$ctx = 'unq:'.$ctx;
						}

						$valAbsOffset = $attrsOffset + $a[2][1];
						foreach($this->extractPlaceholders($rawVal, $valAbsOffset) as $ph) {
							$propose($ph['start'], $ph['end'] - $ph['start'], $ctx);

							if(preg_match('/^\{([a-zA-Z\.]+)\}$/', substr($rawVal, $ph['start'] - $valAbsOffset, $ph['end'] - $ph['start']), $pm)) {
								$p = $pm[1];
								if(!isset($map[$p]) || $this->ctxSeverity($ctx) < $this->ctxSeverity($map[$p])) {
									$map[$p] = $ctx;
								}
							}
						}
					}
				}
			}
		}

		if(preg_match_all('/\{[a-zA-Z\.]+\}/', $template, $all, PREG_OFFSET_CAPTURE)) {

			$covered = [];
			foreach($occ as $o) {
				$covered[$o['start'].':'.$o['end']] = true;
			}
			foreach($all[0] as $m) {
				$start = $m[1];
				$end = $start + strlen($m[0]);
				if(!isset($covered[$start.':'.$end])) {
					$occ[] = ['start' => $start, 'end' => $end, 'ctx' => 'body'];
				}
			}
		}

		usort($occ, function($a, $b) {
			return $a['start'] - $b['start'];
		});
		$this->ctxOcc = $occ;
		$this->ctxMap = $map;
		return $occ;
	}

	private function extractPlaceholders($subject, $baseOffset) {
		$out = [];
		if(preg_match_all('/\{[a-zA-Z\.]+\}/', $subject, $ms, PREG_OFFSET_CAPTURE)) {
			foreach($ms[0] as $m) {
				$out[] = ['start' => $baseOffset + $m[1], 'end' => $baseOffset + $m[1] + strlen($m[0])];
			}
		}
		return $out;
	}

	private function resolveContext($m, $path) {

		$pos = is_array($m[0]) ? ($m[0][1] ?? null) : null;
		if($pos !== null && $this->ctxOcc) {

			$lo = 0;
			$hi = count($this->ctxOcc) - 1;
			while($lo <= $hi) {
				$mid = intval(($lo + $hi) / 2);
				$o = $this->ctxOcc[$mid];
				if($pos < $o['start']) {
					$hi = $mid - 1;
				} elseif($pos >= $o['end']) {
					$lo = $mid + 1;
				} else {
					return $o['ctx'];
				}
			}
		}

		if(isset($this->ctxMap[$path])) {
			return $this->ctxMap[$path];
		}

		if($path === 'tunes.anchorTune.anchor' || $path === 'id') {
			return 'tag';
		}
		return 'body';
	}

	private function ctxSeverity($ctx) {
		switch($ctx) {
			case 'textarea':
				return 1;
			case 'tag':
				return 2;
			case 'urlattr':
				return 3;
			case 'styleattr':
				return 4;
			case 'attr':
				return 5;
			case 'body':
				return 6;
		}
		return 6;
	}

	private function filterValueCtx($value, $ctx) {
		if(is_array($value)) {
			return $value;
		}
		$value = is_string($value) ? $value : (string)$value;

		if(strpos($ctx, 'unq:') === 0) {
			$value = preg_replace('/[\s"\'=<>`]/', '', $value);
			$ctx = substr($ctx, 4);
		}
		switch($ctx) {
			case 'textarea':
				return dhtmlspecialchars($value);
			case 'tag':

				return preg_replace('/[^a-zA-Z0-9_\-]/', '', $value);
			case 'urlattr':
				return $this->sanitizeUrl($value);
			case 'styleattr':

				$cleaned = preg_replace_callback('/(expression|behavior|-moz-binding|javascript|vbscript)\s*(\(|:)/is', function($m) {
					return 'blocked'.$m[2];
				}, $value);
				return dhtmlspecialchars($cleaned);
			case 'attr':

				return dhtmlspecialchars(self::sanitizeRich($value));
			case 'body':
			default:
				return self::sanitizeRich($value);
		}
	}

	private static $allowedTags = [
		'a', 'abbr', 'b', 'blockquote', 'br', 'caption', 'cite', 'code', 'col', 'colgroup',
		'dd', 'del', 'details', 'div', 'dl', 'dt', 'em', 'figcaption', 'figure', 'font',
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'kbd', 'li', 'mark',
		'ol', 'p', 'pre', 'q', 's', 'samp', 'small', 'span', 'strike', 'strong', 'sub',
		'summary', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'u', 'ul', 'var',
	];
	private static $allowedAttrs = [
		'align', 'alt', 'border', 'cellpadding', 'cellspacing', 'class', 'color', 'colspan',
		'face', 'height', 'id', 'lang', 'rel', 'rowspan', 'size', 'span', 'start', 'style',
		'target', 'title', 'type', 'valign', 'value', 'width', 'checked', 'disabled', 'open',
	];

	public static function sanitizeRich($value) {
		if(!is_string($value) || $value === '' || strpos($value, '<') === false) {
			return $value;
		}

		$value = preg_replace('/<!--.*?-->/s', '', $value);
		$value = preg_replace('/<\s*(script|style)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $value);

		$value = preg_replace_callback(
			'/<(\/?)\s*([a-zA-Z][a-zA-Z0-9]*)((?:[^>"\']|"[^"]*"|\'[^\']*\')*)>/s',
			function($m) {
				$closing = $m[1] === '/';
				$tag = strtolower($m[2]);
				if(!in_array($tag, self::$allowedTags, true)) {
					return '';
				}
				if($closing) {
					return '</'.$tag.'>';
				}
				return '<'.$tag.self::rebuildAttrs($m[3]).'>';
			},
			$value
		);

		$value = preg_replace_callback('/<(?![a-zA-Z\/!])/', function() {
			return '&lt;';
		}, $value);

		$value = preg_replace_callback('/<[a-zA-Z][^<>]*$/', function($m) {
			return dhtmlspecialchars($m[0]);
		}, $value);
		return $value;
	}

	private static function rebuildAttrs($attrStr) {
		$out = '';
		if($attrStr === '' || trim($attrStr) === '' || trim($attrStr) === '/') {
			return $out;
		}

		if(!preg_match_all('/([a-zA-Z_:][a-zA-Z0-9_:.\-]*)(?:\s*=\s*("[^"]*"|\'[^\']*\'|[^\s"\'`=<>]+))?/', $attrStr, $am, PREG_SET_ORDER)) {
			return $out;
		}
		foreach($am as $a) {
			$name = strtolower($a[1]);

			if(strpos($name, 'on') === 0) {
				continue;
			}
			$isUrl = in_array($name, self::$urlAttrs, true);
			$isData = strpos($name, 'data-') === 0 || strpos($name, 'aria-') === 0;
			if(!in_array($name, self::$allowedAttrs, true) && !$isData && !$isUrl) {
				continue;
			}
			if(!isset($a[2]) || $a[2] === '') {
				$out .= ' '.$name;
				continue;
			}
			$val = $a[2];
			$quote = $val[0];
			$raw = ($quote === '"' || $quote === "'") ? substr($val, 1, -1) : $val;

			$raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			if($isUrl) {
				$raw = self::sanitizeUrlStatic($raw);
				if($raw === '') {
					continue;
				}
			} elseif($name === 'style') {
				$raw = preg_replace('/(expression|behavior|-moz-binding|javascript|vbscript)\s*(\(|:)/is', 'blocked$2', $raw);
			} elseif($name === 'class' || $name === 'id') {
				$raw = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $raw);
			}
			$out .= ' '.$name.'="'.dhtmlspecialchars($raw).'"';
		}
		return $out;
	}

	private static function sanitizeUrlStatic($url) {
		if(!is_string($url)) {
			return '';
		}

		$check = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$check = preg_replace('/[\x00-\x20\x7f]+/', '', $check);
		$check = str_replace('\\', '/', $check);
		if($check === '') {
			return '';
		}

		if(preg_match('/^(?:javascript|vbscript|livescript|mocha|jscript|ecmascript|about|view-source):/i', $check)) {
			return '';
		}
		if(preg_match('/^data:/i', $check) && !preg_match('/^data:image\/(png|jpe?g|gif|webp|bmp|avif|heic|x-icon);base64/i', $check)) {
			return '';
		}
		return $url;
	}

	private function sanitizeUrl($url) {
		$url = self::sanitizeUrlStatic($url);
		return $url === '' ? '' : dhtmlspecialchars($url);
	}

	/*
	 * [loop data.items]
	 *   <li>{text}</li>
	 * [/loop]
	 */
	const rLoop = '/\[loop ([a-zA-Z\.]+)\](.+?)\[\/loop\]/s';
	const rLoopIndex = '/\[loopindex\]/s';
	const rLoopObject = '/\[loopobject ([a-zA-Z\.]+)\](.+?)\[\/loopobject\]/s';
	const rLoopObjectData = '/\{loopobjectdata\}/';
	const rVar = '/\{([a-zA-Z\.]+)\}/';

	/*
	 * [if data.ok]
	 *   {text} or string
	 * [/if]
	 */
	const rIf = '/\[if ([a-zA-Z\.]+)=(.+?)\](.*?)\[\/if\]/s';

	/*
	 * [url data.url]
	 */
	const rUrl = '/\[url ([a-zA-Z\.]+),([a-zA-Z\.]+),([a-zA-Z\.]+)\]/s';

	/*
	 * [attach data.url]
	 */
	const rAttach = '/\[attach ([a-zA-Z\.]+)\]/s';

	/*
	* [column blocks]
	*/
	const rColumn = '/\[column ([a-zA-Z\.]+)\]/s';

	/*
	 * [script path]
	 */
	const rScript = '/\[script (.+?)\]/s';

	/*
	* [codeflask id,language,code]
	*/
	const rCodeflask = '/\[codeflask ([a-zA-Z\.]+),([a-zA-Z\.]+),([a-zA-Z\.]+),([a-zA-Z\.]+)\]/s';

	/*
	 * [media id,url,width]
	 * 支持audio、video解析，用法：[media id,[url data.file.url,data.file.remote,data.file.directory],300]
	 */
	const rMedia = '/\[media ([a-zA-Z\.]+),([^,\]]+),([a-zA-Z0-9%\.]+)(?:,([^,\]]+))?\]/s';

	const rJsFile = '/\[jsfile ([a-zA-Z0-9_\.]+\.js)\]/s';
	const rCssFile = '/\[cssfile ([a-zA-Z0-9_\.]+\.css)\]/s';
	const rRecursive = '/\[recursive ([a-zA-Z\.]+),([^\]]+)\]/s';

	public function __construct($html, $block, $static) {
		$this->html = $html;
		$this->block = $block;
		$this->static = $static;

		$this->buildContextMap($this->html);
	}

	public function replace() {

		$replaced = 0;
		$n = 0;
		$this->html = preg_replace_callback(self::rJsFile, [$this, '_jsfile'], $this->html, -1, $n);
		$replaced += $n;
		$this->html = preg_replace_callback(self::rCssFile, [$this, '_cssfile'], $this->html, -1, $n);
		$replaced += $n;
		$this->html = preg_replace_callback(self::rCodeflask, [$this, '_codeflask'], $this->html, -1, $n);
		$replaced += $n;
		$this->html = preg_replace_callback(self::rLoop, [$this, '_loop'], $this->html, -1, $n);
		$replaced += $n;
		$this->html = preg_replace_callback(self::rLoopObject, [$this, '_object'], $this->html, -1, $n);
		$replaced += $n;

		if($replaced > 0) {
			$this->buildContextMap($this->html);
		}

		$this->html = preg_replace_callback(self::rVar, [$this, '_var'], $this->html, -1, $cnt, PREG_OFFSET_CAPTURE);
		$this->html = preg_replace_callback(self::rRecursive, [$this, '_recursive'], $this->html);
		$this->html = preg_replace_callback(self::rUrl, [$this, '_url'], $this->html);
		$this->html = preg_replace_callback(self::rAttach, [$this, '_attach'], $this->html);
		$this->html = preg_replace_callback(self::rIf, [$this, '_if'], $this->html);
		$this->html = preg_replace_callback(self::rMedia, [$this, '_media'], $this->html);
		return $this->html;
	}

	private function _codeflask($m, $value = null) {
		$id = $this->_value($m[1], $value);
		$language = $this->_value($m[2], $value);
		$n_count = $this->_value($m[3], $value) ?? 0;
		$code = $this->_value($m[4], $value);

		$id = preg_replace('/[^a-zA-Z0-9_\-]/', '', is_string($id) ? $id : (string)$id);
		$language = preg_replace('/[^a-zA-Z0-9_\-]/', '', is_string($language) ? $language : (string)$language);
		$n_count = intval($n_count);
		$code = str_replace(
			['`', '${'],
			['\`', '\${'],
			$code
		);
		$code = dhtmlspecialchars($code);
		$rand = time().random(5);
		$script = <<<EOF
<script type="application/javascript">
			const editorElem$rand = document.getElementById('codeflask-$id');
			var isCollapsed$rand = true;
			const flask$rand = new CodeFlask(editorElem$rand, {
				language: '$language',
				lineNumbers: true,
				styleParent: this.shadowRoot,
				rtl: false,
				readonly: true
			});
            		var code$rand = `$code`;
            		code$rand = code$rand.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, '"').replace(/&#039;/g, "'");
			flask$rand.addLanguage('$language', Prism.languages['$language']);
			flask$rand.onUpdate((code) => {
				// do something with code here.
				// this will trigger whenever the code
				// in the editor changes.
			    // console.log(code)
			});
			// flask.updateCode('云诺');
			// This will also trigger .onUpdate()
			flask$rand.updateCode(code$rand);

			const currentCode$rand = flask$rand.getCode();

		            var coderow$rand = parseInt('$n_count');
			    if (coderow$rand === undefined || coderow$rand !== coderow$rand || coderow$rand === 0) {
				    coderow$rand = flask$rand.lineNumber;
			    }

		            if (coderow$rand < 10) {
		                editorElem$rand.parentElement.style.height = '100px';
				editorElem$rand.style.height = '100px';
		            } else if (coderow$rand >= 10 && coderow$rand < 20) {
		                editorElem$rand.parentElement.style.height = '300px';
				editorElem$rand.style.height = '300px';
		            } else if (coderow$rand >= 20) {
		                editorElem$rand.parentElement.style.height = '500px';
		                editorElem$rand.style.height = '500px';
		            }

			//console.log({currentCode$rand})

			const copyBtn$rand = document.getElementById('codeflask-copy-{id}');
			copyBtn$rand.addEventListener('click', function() {
			    try {
			        // 先检查 Clipboard API 是否可用
			        if (navigator.clipboard && navigator.clipboard.writeText) {
			            // 使用 Clipboard API 复制代码
			            navigator.clipboard.writeText(code$rand).then(function() {
			                // 复制成功提示
			                const originalText = copyBtn$rand.innerHTML;
			                copyBtn$rand.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> 已复制';

			                // 一段时间后恢复原文本
			                setTimeout(function() {
			                    copyBtn$rand.innerHTML = originalText;
			                }, 2000);
			            }).catch(function(err) {
			                // 复制失败处理
			                console.error('复制失败:', err);

			                // 降级方案：使用传统的复制方法
			                fallbackCopyTextToClipboard(code$rand);
			            });
			        } else {
			            // Clipboard API 不可用时直接使用降级方案
			            fallbackCopyTextToClipboard(code$rand);
			        }

			        // 提取降级方案为独立函数
			        function fallbackCopyTextToClipboard(text) {
			            const textArea = document.createElement('textarea');
			            textArea.value = text;
			            textArea.style.position = 'fixed';
			            textArea.style.opacity = '0';
			            document.body.appendChild(textArea);
			            textArea.select();

			            try {
			                document.execCommand('copy');
			                const originalText = copyBtn$rand.innerHTML;
			                copyBtn$rand.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> 已复制';

			                setTimeout(function() {
			                    copyBtn$rand.innerHTML = originalText;
			                }, 2000);
			            } catch (copyErr) {
			                console.error('传统复制方法也失败了:', copyErr);
			            } finally {
			                document.body.removeChild(textArea);
			            }
			        }
			    } catch (err) {
			        console.error('复制功能不可用:', err);
			    }
			});

			const bottomBtn$rand = document.getElementById('codeflask-bottomBtn-{id}');
			const Toggle$rand = document.getElementById('codeflask-Toggle-{id}');
			bottomBtn$rand.addEventListener('click', function() {
			        if (isCollapsed$rand) {
			            // 展开代码
			            isCollapsed$rand = false;
				    const codeElement$rand = editorElem$rand.querySelector('.codeflask__code');
			            if (codeElement$rand) {
				            const actualHeight$rand = codeElement$rand.scrollHeight + 20;

				            editorElem$rand.parentElement.style.height = actualHeight$rand + 'px';
				            editorElem$rand.style.height = actualHeight$rand + 'px';
			            }
			            bottomBtn$rand.innerHTML = '<button class="editorjs-codeFlask_BottomToggle" title="折叠代码"><span class="toggle-icon">▲</span> 折叠</button>';
			            Toggle$rand.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
				} else {
			            // 折叠代码
			            isCollapsed$rand = true;
				    if (coderow$rand < 20) {
			                editorElem$rand.parentElement.style.height = '300px';
					editorElem$rand.style.height = '300px';
			            } else if (coderow$rand >= 20) {
			                editorElem$rand.parentElement.style.height = '500px';
			                editorElem$rand.style.height = '500px';
			            }
			            bottomBtn$rand.innerHTML = '<button class="editorjs-codeFlask_BottomToggle expand-mode" title="展开代码" data-empty="false"><span class="toggle-icon">▼</span> 展开</button>';
			            Toggle$rand.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';
				}
			});

			Toggle$rand.addEventListener('click', function() {
				bottomBtn$rand.click();
			});
</script>
EOF;
		return $script;
	}

	/*
	 * 调用示例：[media id,[url data.file.url,data.file.remote,data.file.directory],300,[url data.coverImage.url,data.coverImage.remote,data.coverImage.directory]]
	 */
	private function _media($m, $value = null) {
		$id = $this->_value($m[1], $value);
		$url = $m[2];
		$width = intval($m[3]) ?? 300;
		$thumbnail = isset($m[4]) ? $m[4] : null;

		$id = preg_replace('/[^a-zA-Z0-9_\-]/', '', is_string($id) ? $id : (string)$id);

		$url = addslashes($url);

		$url = str_replace(['"', "'", '\\'], '', dhtmlspecialchars($url));
		if(!in_array(strtolower(substr($url, 0, 6)), ['http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://']) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
			return dhtmlspecialchars($url);
		}

		$cleanUrl = parse_url($url, PHP_URL_PATH) ?: $url;
		$type = fileext($cleanUrl);
		$randomid = $id;

		$imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heif', 'heic', 'bmp', 'ico', 'svg'];
		if($thumbnail) {

			$thumbExt = fileext(parse_url($thumbnail, PHP_URL_PATH) ?: $thumbnail);

			if(!in_array($thumbExt, $imageExtensions)) {
				$thumbnail = '';
			}
		}

		$audio = ['aac', 'flac', 'ogg', 'mp3', 'm4a', 'weba', 'wma', 'mid', 'wav', 'ra', 'ram'];
		$video = ['rm', 'rmvb', 'flv', 'swf', 'asf', 'asx', 'wmv', 'avi', 'mpg', 'mpeg', 'mp4', 'm4v', '3gp', 'ogv', 'webm', 'mov'];

		$thumbParam = $thumbnail ? str_replace(['"', "'", '\\'], '', dhtmlspecialchars(addslashes($thumbnail))) : '';
		if(in_array($type, $audio)) {

			return '<ignore_js_op><div id="'.$type.'_'.$randomid.'" class="media" style="margin-left: auto;margin-right: auto;"><div id="'.$type.'_'.$randomid.'_container" class="media_container"></div><div id="'.$type.'_'.$randomid.'_tips" class="media_tips"><a href="'.$url.'" target="_blank">'.lang('template', 'parse_av_tips').'</a></div></div><script type="text/javascript">detectPlayer("'.$type.'_'.$randomid.'", "'.$type.'", "'.$url.'", "'.$width.'", "66", "'.$thumbParam.'");</script></ignore_js_op>';
		} else if(in_array($type, $video)) {

			$height = intval($width * 0.75);

			return '<ignore_js_op><div id="'.$type.'_'.$randomid.'" class="media" style="margin-left: auto;margin-right: auto;"><div id="'.$type.'_'.$randomid.'_container" class="media_container"></div><div id="'.$type.'_'.$randomid.'_tips" class="media_tips"><a href="'.$url.'" target="_blank">'.lang('template', 'parse_av_tips').'</a></div></div><script type="text/javascript">detectPlayer("'.$type.'_'.$randomid.'", "'.$type.'", "'.$url.'", "'.$width.'", "'.$height.'", "'.$thumbParam.'");</script></ignore_js_op>';
		} else {

			return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		}
	}

	private function _object($m, $value = null) {
		$t2 = '';
		if($m[1] != 'null' && $m[1] != null) {
			$value = $this->_value($m[1], $value);
		}
		if(is_object($value)) {
			$jsonString = json_encode($value);
			$array = json_decode($jsonString, true);
		} elseif(is_array($value)) {
			$array = $value;
		} else {
			return $m[2];
		}
		foreach($array as $ad) {

			if(is_array($ad) || is_object($ad)) {
				$ad = json_encode($ad, JSON_UNESCAPED_UNICODE);
			}
			$ad = $this->filterValueCtx($ad, 'body');
			$t2 .= str_replace('{loopobjectdata}', $ad, $m[2]);
		}
		return $t2;
	}

	private function _if($m, $value = null) {
		$result = $this->_value($m[1], $value);
		if(!$result) {
			return '';
		} else {
			if((string)$m[2] == 'notnull') {
				$content = preg_replace_callback(self::rVar, function($m) {
					return $this->_var($m);
				}, $m[3]);
				return !empty($content) ? $content : $m[3];
			} elseif((string)$result != (string)$m[2]) {
				return '';
			} else {
				$content = preg_replace_callback(self::rVar, function($m) {
					return $this->_var($m);
				}, $m[3]);
				return !empty($content) ? $content : $m[3];
			}
		}
	}

	private function _url($m, $value = null) {
		$url = $this->_value($m[1], $value);
		$remoteParam = $this->_value($m[2], $value);
		$directoryParam = $this->_value($m[3], $value);

		if(!$url) {
			return '';
		} else {

			$url = preg_replace('/["\'\\\\]/', '', is_string($url) ? $url : (string)$url);
			$directoryParam = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', is_string($directoryParam) ? $directoryParam : (string)$directoryParam);

			$url = isset($remoteParam) && $directoryParam ?
				(($remoteParam ? getglobal('setting/ftp/attachurl') : getglobal('setting/attachurl')).$directoryParam.'/'.$url)
				: (str_contains($url, 'http') ? $url : getglobal('siteurl').$url);
			if(str_contains($url, 'http')) {
				return $url;
			} else {
				return getglobal('siteurl').$url;
			}
		}
	}

	private function _attach($m, $value = null) {
		global $_G;
		$aid = $this->_value($m[1], $value);
		if(!$aid) {
			return '';
		} else {
			return aidencode($aid, 0, $_G['tid']);
		}
	}

	private function _jsfile($m, $value = null) {
		$filename = $m[1];
		$blockType = $this->block['type'] ?? '';
		if(empty($blockType) || empty($this->static) || strpos($this->static, $blockType) === false) {
			return '';
		}

		$path = $this->static.$filename.'?'.getglobal('style/verhash');

		return '<script type="text/javascript" src="'.$path.'"></script>';
	}

	private function _cssfile($m, $value = null) {
		$filename = $m[1];
		$blockType = $this->block['type'] ?? '';
		if(empty($blockType) || empty($this->static) || strpos($this->static, $blockType) === false) {
			return '';
		}

		$path = $this->static.$filename.'?'.getglobal('style/verhash');

		return '<link rel="stylesheet" type="text/css" href="'.$path.'" />';
	}

	private function _script($m, $value = null) {
		$path = $m[1];
		if(str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'ftp://')) {
			return '';
		}
		$path = str_replace('{STATICURL}', getglobal('staticurl'), $path);
		$path = str_replace('{VERHASH}', getglobal('style/verhash'), $path);

		return '<script src="'.$path.'"></script>';
	}

	private function _column($m, $value = null) {
		$result = $this->_value($m[1], $value);
		if(!$result) {
			return '';
		} else {
			return $m[2];
		}
	}

	private function _loop($m) {
		$nodes = $this->_value($m[1]);
		if(!is_array($nodes)) {
			return $m[0];
		}
		$s = '';
		foreach($nodes as $index => $node) {
			$t = preg_replace(self::rLoopIndex, $index, $m[2]);
			$t = preg_replace_callback(self::rLoopObject, function($m_sub) use ($node) {
				return $this->_object($m_sub, $node);
			}, $t);
			$t = preg_replace_callback(self::rVar, function($m_sub) use ($node) {
				return $this->_var($m_sub, $node);
			}, $t);
			$t = preg_replace_callback(self::rUrl, function($m_sub) use ($node) {
				return $this->_url($m_sub, $node);
			}, $t);
			$t = preg_replace_callback(self::rIf, function($m_sub) use ($node) {
				return $this->_if($m_sub, $node);
			}, $t);
			$s .= preg_replace_callback(self::rColumn, function($m_sub) use ($node) {
				$data = ['blocks' => []];
				$data['blocks'] = $this->_var($m_sub, $node);
				[$parserData, $styleData] = editor::parser(json_encode($data));
				return $parserData.$styleData;
			}, $t);
		}
		return $s;
	}

	private function _recursiveRender($data, $config, $context = []) {

		if(!is_array($data) || empty($data)) {
			return isset($config['emptyTemplate']) ? $config['emptyTemplate'] : '';
		}

		$config = $this->_normalizeConfig($config);

		$formatNumber = function($number, $type) {
			switch($type) {
				case 'lower-roman':
					return strtolower($this->_intToRoman($number));
				case 'upper-roman':
					return $this->_intToRoman($number);
				case 'lower-alpha':
					return chr(96 + $number);
				case 'upper-alpha':
					return chr(64 + $number);
				case 'numeric':
				default:
					return (string)$number;
			}
		};

		$itemsHtml = '';
		foreach($data as $index => $item) {

			$itemContext = array_merge($context, [
				'index' => $index,
				'depth' => isset($context['depth']) ? $context['depth'] + 1 : 0,
				'parent' => $context
			]);

			$startNumber = isset($config['startNumber']) && intval($config['startNumber']) > 0 ? intval($config['startNumber']) : 1;
			$currentNumber = $index + $startNumber;

			if(isset($context['levelNumber'])) {

				$parentParts = explode('.', $context['levelNumber']);
				$currentPart = $formatNumber($currentNumber, $config['counterType']);
				$levelNumber = implode('.', $parentParts).'.'.$currentPart;
			} else {

				$levelNumber = $formatNumber($currentNumber, $config['counterType']);
			}
			$itemContext['levelNumber'] = $levelNumber;

			$itemHtml = $this->_replacePlaceholders($config['itemTemplate'], $item, $itemContext, $config);

			if(isset($item[$config['childrenKey']]) && !empty($item[$config['childrenKey']])) {

				$childrenHtml = $this->_recursiveRender($item[$config['childrenKey']], $config, $itemContext);

				if(isset($config['childrenClass']) && !empty($config['childrenClass'])) {

					$childrenHtml = preg_replace('/^<([a-z][a-z0-9]*)\s*/i', '<$1 class="'.$config['childrenClass'].'" ', $childrenHtml);
				}

				$itemHtml = str_replace('{'.$config['childrenKey'].'}', $childrenHtml, $itemHtml);
			} else {

				$itemHtml = str_replace('{'.$config['childrenKey'].'}', '', $itemHtml);
			}

			$itemsHtml .= $itemHtml;
		}

		$containerHtml = $this->_replacePlaceholders($config['containerTemplate'], ['items' => $itemsHtml], $context, $config);

		return $containerHtml;
	}

	private function _intToRoman($num) {

		$num = intval($num);
		if($num <= 0) return '0';

		$romanMap = [
			'M' => 1000,
			'CM' => 900,
			'D' => 500,
			'CD' => 400,
			'C' => 100,
			'XC' => 90,
			'L' => 50,
			'XL' => 40,
			'X' => 10,
			'IX' => 9,
			'V' => 5,
			'IV' => 4,
			'I' => 1
		];

		$result = '';
		foreach($romanMap as $roman => $value) {
			while($num >= $value) {
				$result .= $roman;
				$num -= $value;
			}
		}
		return $result;
	}

	private function _replacePlaceholders($template, $data, $context, $config) {
		$html = $template;

		foreach($data as $key => $val) {
			if(!is_array($val) && !is_object($val)) {
				$html = str_replace('{'.$key.'}', $this->filterValueCtx($val, 'body'), $html);
			}
		}

		foreach($context as $key => $val) {
			if(!is_array($val) && !is_object($val)) {
				$html = str_replace('{context.'.$key.'}', self::sanitizeRich(is_string($val) ? $val : (string)$val), $html);
			}
		}

		if(isset($context['depth'])) {
			$html = str_replace('{depth}', intval($context['depth']), $html);
		} else {
			$html = str_replace('{depth}', '', $html);
		}
		if(isset($context['index'])) {
			$html = str_replace('{index}', strval($context['index']), $html);
		} else {
			$html = str_replace('{index}', '', $html);
		}

		if(isset($context['levelNumber'])) {

			$html = str_replace('{levelNumber}', preg_replace('/[^a-zA-Z0-9.\-]/', '', is_string($context['levelNumber']) ? $context['levelNumber'] : (string)$context['levelNumber']), $html);
		} else {
			$html = str_replace('{levelNumber}', '', $html);
		}

		preg_match_all('/\{(?!context\.)([\w]+)\.([\w\.]+)\}/', $html, $matches);

		if(!empty($matches[1])) {

			foreach(array_keys($matches[1]) as $index) {
				$rootKey = $matches[1][$index];
				$nestedPath = $matches[2][$index];
				$fullPlaceholder = $matches[0][$index];

				$value = '';

				if(isset($data[$rootKey])) {

					$nestedValue = $this->_value($nestedPath, $data[$rootKey]);

					if($nestedValue !== false && !is_array($nestedValue) && !is_object($nestedValue)) {
						$value = $nestedValue;
					}
				}

				$html = str_replace($fullPlaceholder, $this->filterValueCtx($value, 'body'), $html);
			}
		}

		return $html;
	}

	private function _normalizeConfig($config) {

		$defaultConfig = [
			'childrenKey' => 'items',
			'itemTemplate' => '{content}',
			'containerTemplate' => '{items}',
			'emptyTemplate' => '',
			'separator' => '',
			'childrenClass' => 'cdx-list__item-children',
			'counterType' => 'numeric',
			'start' => 1
		];

		return array_merge($defaultConfig, $config);
	}

	private function _recursive($m, $value = null) {

		$dataPath = isset($m[1]) ? trim($m[1]) : '';

		$configJson = isset($m[2]) ? trim($m[2]) : '{}';

		if((strpos($configJson, '"') === 0 && strrpos($configJson, '"') === strlen($configJson) - 1) ||
			(strpos($configJson, "'") === 0 && strrpos($configJson, "'") === strlen($configJson) - 1)) {
			$configJson = substr($configJson, 1, -1);
		}

		$configJson = stripslashes($configJson);

		$config = json_decode($configJson, true);

		if(json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {

			$config = [];
		}

		$data = [];
		if($dataPath != 'null' && $dataPath != null) {
			$data = $this->_value($dataPath, $value);
		}

		return $this->_recursiveRender($data, $config);
	}

	private function _var($m, $value = null) {

		$isOffset = is_array($m[0]);
		$text = $isOffset ? $m[0][0] : $m[0];
		$path = $isOffset ? $m[1][0] : $m[1];
		$v = $this->_value($path, $value);
		if($v !== false) {

			$ctx = $this->resolveContext($m, $path);
			$v = $this->filterValueCtx($v, $ctx);
		}

		return $v !== false ? $v : $text;
	}

	private function _value($var, $value = null) {
		$e = explode('.', $var);
		$value = $value ?? $this->block;
		foreach($e as $_v) {
			if(!isset($value[$_v])) {
				return false;
			}
			$value = $value[$_v];
		}
		return $value;
	}
}
