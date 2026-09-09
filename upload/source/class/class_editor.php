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
			$editorblock_static = empty($editorblock['plugin']) ? 'static/js/editorjs/tools/' . $type . '/' : 'source/plugin/' . $editorblock['plugin'] . '/editorblock/tools/' . $type . '/';
		} else {
			$editorblock = table_common_editorblock::t()->fetch_by_block_class($type);
			$editorblock_parser = $editorblock['parser'];
			$editorblock_style = $editorblock['style'];
			$editorblock_static = empty($editorblock['plugin']) ? 'static/js/editorjs/tools/' . $type . '/' : 'source/plugin/' . $editorblock['plugin'] . '/editorblock/tools/' . $type . '/';
			memory('set', 'editorblock_'.$type, $editorblock);
		}
		return [$editorblock_parser, $editorblock_style, $editorblock_static];
	}

	public static function getBlockParser($tpl, $block) {
		$parser = $tpl;
		return $parser;
	}

	public static function parser($content) {
		global $_G;
		$blocksData = self::toArray($content);
		$parserData = '';
		$styleData = ['types' => [], 'style' => ''];
		foreach($blocksData['blocks'] as $key => $value) {
			list($editorblock_parser, $editorblock_style, $editorblock_static) = self::getBlockTemplate($value['type']);
			// 是否用户回帖可见
			$allowParser = true;
			$isHideTuneBlock = false;
			if($value['tunes']['hideTune']['hide']) {
				$isHideTuneBlock = true;
				$authorreplyexist = null;
				if($_G['uid']) {
					$authorreplyexist = table_forum_post::t()->fetch_pid_by_tid_authorid($_G['tid'], $_G['uid']);
				}
				if(!$authorreplyexist) {
					$allowParser = false;
				}
				if($_G['adminid'] > 0) {
					$allowParser = true;
				}
			}
			if(!$allowParser) {
				$parser = '<div class="locked">'.($_G['uid'] ? $_G['username'] : lang('forum/template', 'guest')).lang('forum/template', 'post_hide_reply_hidden_text').'<a href="forum.php?mod=post&action=reply&fid='.$_G['fid'].'&tid='.$_G['tid'].'" onclick="showWindow(\'reply\', this.href)">'.lang('forum/template', 'reply').'</a></div>';
			} else {
				$parser = (new editorBlock($editorblock_parser, $value, $editorblock_static))->replace();
				if($isHideTuneBlock) {
					$parser = '<div class="showhide"><h4>'.lang('forum/template', 'post_hide').'</h4>'.$parser.'</div>';
				}
			}

			// 锚点解析
			$anchorparse = explode(',', getglobal('setting/anchorparse')) ?? [];
			if(in_array($value['type'], $anchorparse)) {
				$parser = preg_replace_callback('/<a\s+href=[\'"]#([^\'"]+)[\'"](.*?)>/s', function($m) {
					$href = $m[1];
					// 在<a>标签中添加onclick属性
					$onclick = "javascript: document.getElementById('".$href."').scrollIntoView();return false;";
					return '<a href="#'.$href.'" onclick="'.$onclick.'"'.$m[2].'>';
				}, $parser);
			}
			// 追加
			$parserData .= $parser;
			if(!in_array($value['type'], $styleData['types'])) {
				$styleData['types'][] = $value['type'];
				$styleData['style'] .= $editorblock_style;
			}
		}
		return [$parserData, $styleData['style']];
	}

}

class editorBlock {
	var $html = '';
	var $block = [];
	var $static = '';

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
	 * [url data.url]
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
	}

	public function replace() {
		//$this->html = preg_replace_callback(self::rScript, array($this, '_script'), $this->html);
		$this->html = preg_replace_callback(self::rJsFile, [$this, '_jsfile'], $this->html);
		$this->html = preg_replace_callback(self::rCssFile, [$this, '_cssfile'], $this->html);
		$this->html = preg_replace_callback(self::rCodeflask, [$this, '_codeflask'], $this->html);
		$this->html = preg_replace_callback(self::rLoop, [$this, '_loop'], $this->html);
		$this->html = preg_replace_callback(self::rLoopObject, [$this, '_object'], $this->html);
		$this->html = preg_replace_callback(self::rVar, [$this, '_var'], $this->html);
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
			// flask.updateCode('');
			// This will also trigger .onUpdate()
			flask$rand.updateCode(code$rand);

			const currentCode$rand = flask$rand.getCode();
            
		            var coderow$rand = parseInt('$n_count');
			    if (coderow$rand === undefined || coderow$rand !== coderow$rand || coderow$rand === 0) {
				    coderow$rand = flask$rand.lineNumber;
			    }
			    
		            if (coderow$rand < 20) {
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

		// 验证URL合法性
		$url = addslashes($url);
		if(!in_array(strtolower(substr($url, 0, 6)), ['http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://']) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
			return dhtmlspecialchars($url);
		}

		// 获取文件类型
		$cleanUrl = parse_url($url, PHP_URL_PATH) ?: $url;
		$type = fileext($cleanUrl);
		$randomid = $id;


		// 定义常见的图片扩展名
		$imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico', 'svg'];
		if ($thumbnail) {
			// 从thumbnail URL中提取扩展名
			$thumbExt = fileext(parse_url($thumbnail, PHP_URL_PATH) ?: $thumbnail);
			// 如果不是图片扩展名，则设置为空字符串
			if (!in_array($thumbExt, $imageExtensions)) {
				$thumbnail = '';
			}
		}


		// 定义音频和视频扩展名数组
		$audio = ['aac', 'flac', 'ogg', 'mp3', 'm4a', 'weba', 'wma', 'mid', 'wav', 'ra', 'ram'];
		$video = ['rm', 'rmvb', 'flv', 'swf', 'asf', 'asx', 'wmv', 'avi', 'mpg', 'mpeg', 'mp4', 'm4v', '3gp', 'ogv', 'webm', 'mov'];

		// 根据文件类型生成不同的播放器
		if(in_array($type, $audio)) {
			// 音频类型，高度使用固定值66
			// 如果有缩略图，将其传递给 detectPlayer 函数
			$thumbParam = $thumbnail ? addslashes($thumbnail) : '';
			return '<ignore_js_op><div id="'.$type.'_'.$randomid.'" class="media" style="margin-left: auto;margin-right: auto;"><div id="'.$type.'_'.$randomid.'_container" class="media_container"></div><div id="'.$type.'_'.$randomid.'_tips" class="media_tips"><a href="'.$url.'" target="_blank">'.lang('template', 'parse_av_tips').'</a></div></div><script type="text/javascript">detectPlayer("'.$type.'_'.$randomid.'", "'.$type.'", "'.$url.'", "'.$width.'", "66", "'.$thumbParam.'");</script></ignore_js_op>';
		} else if(in_array($type, $video)) {
			// 视频类型，计算适当的高度
			$height = intval($width * 0.75); // 保持4:3的宽高比
			// 如果有缩略图，将其传递给 detectPlayer 函数
			$thumbParam = $thumbnail ? addslashes($thumbnail) : '';
			return '<ignore_js_op><div id="'.$type.'_'.$randomid.'" class="media" style="margin-left: auto;margin-right: auto;"><div id="'.$type.'_'.$randomid.'_container" class="media_container"></div><div id="'.$type.'_'.$randomid.'_tips" class="media_tips"><a href="'.$url.'" target="_blank">'.lang('template', 'parse_av_tips').'</a></div></div><script type="text/javascript">detectPlayer("'.$type.'_'.$randomid.'", "'.$type.'", "'.$url.'", "'.$width.'", "'.$height.'", "'.$thumbParam.'");</script></ignore_js_op>';
		} else {
			// 未知类型，返回链接
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
			$t2 .= preg_replace(self::rLoopObjectData, $ad, $m[2]);
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
			// 兼容旧版url格式
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

		$path = $this->static . $filename . '?' . getglobal('style/verhash');

		return '<script type="text/javascript" src="' . $path . '"></script>';
	}

	private function _cssfile($m, $value = null) {
		$filename = $m[1];
		$blockType = $this->block['type'] ?? '';
		if(empty($blockType) || empty($this->static) || strpos($this->static, $blockType) === false) {
			return '';
		}

		$path = $this->static . $filename . '?' . getglobal('style/verhash');

		return '<link rel="stylesheet" type="text/css" href="' . $path . '" />';
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
				list($parserData, $styleData) = editor::parser(json_encode($data));
				return $parserData.$styleData;
			}, $t);
		}
		return $s;
	}


	/**
	 * 完全参数化的无限级递归渲染方法
	 * @param array $data 要处理的数据数组
	 * @param array $config 配置参数数组
	 * @param array $context 上下文数据，用于递归传递
	 * @return string 生成的HTML内容
	 *
	 */
	private function _recursiveRender($data, $config, $context = []) {
		// 验证数据
		if(!is_array($data) || empty($data)) {
			return isset($config['emptyTemplate']) ? $config['emptyTemplate'] : '';
		}

		// 获取配置参数，确保所有必要参数都有默认值
		$config = $this->_normalizeConfig($config);

		// 转换数字格式的辅助函数
		$formatNumber = function($number, $type) {
			switch($type) {
				case 'lower-roman':
					return strtolower($this->_intToRoman($number));
				case 'upper-roman':
					return $this->_intToRoman($number);
				case 'lower-alpha':
					return chr(96 + $number); // 'a' 是 97
				case 'upper-alpha':
					return chr(64 + $number); // 'A' 是 65
				case 'numeric':
				default:
					return (string)$number;
			}
		};

		// 渲染所有子项目
		$itemsHtml = '';
		foreach($data as $index => $item) {
			// 设置当前项的上下文
			$itemContext = array_merge($context, [
				'index' => $index,
				'depth' => isset($context['depth']) ? $context['depth'] + 1 : 0,
				'parent' => $context
			]);

			// 获取起始数字
			$startNumber = isset($config['startNumber']) && intval($config['startNumber']) > 0 ? intval($config['startNumber']) : 1;
			$currentNumber = $index + $startNumber;

			// 生成层级编号
			if(isset($context['levelNumber'])) {
				// 对于多级编号，只转换最后一部分
				$parentParts = explode('.', $context['levelNumber']);
				$currentPart = $formatNumber($currentNumber, $config['counterType']);
				$levelNumber = implode('.', $parentParts) . '.' . $currentPart;
			} else {
				// 对于第一级，直接转换整个编号
				$levelNumber = $formatNumber($currentNumber, $config['counterType']);
			}
			$itemContext['levelNumber'] = $levelNumber;

			// 替换项目模板中的占位符
			$itemHtml = $this->_replacePlaceholders($config['itemTemplate'], $item, $itemContext, $config);

			// 如果有子项，递归处理
			if(isset($item[$config['childrenKey']]) && !empty($item[$config['childrenKey']])) {
				// 递归调用自身处理子项
				$childrenHtml = $this->_recursiveRender($item[$config['childrenKey']], $config, $itemContext);

				// 如果配置了childrenClass，则为子项容器添加这个类
				if(isset($config['childrenClass']) && !empty($config['childrenClass'])) {
					// 查找容器的开始标签并添加class
					$childrenHtml = preg_replace('/^<([a-z][a-z0-9]*)\s*/i', '<$1 class="' . $config['childrenClass'] . '" ', $childrenHtml);
				}

				// 替换子项占位符
				$itemHtml = str_replace('{' . $config['childrenKey'] . '}', $childrenHtml, $itemHtml);
			} else {
				// 如果没有子项，清空子项占位符
				$itemHtml = str_replace('{' . $config['childrenKey'] . '}', '', $itemHtml);
			}

			$itemsHtml .= $itemHtml;
		}

		// 组装完整的容器HTML
		$containerHtml = $this->_replacePlaceholders($config['containerTemplate'], ['items' => $itemsHtml], $context, $config);

		return $containerHtml;
	}

	/**
	 * 将整数转换为罗马数字
	 * @param int $num 要转换的整数
	 * @return string 罗马数字字符串
	 */
	private function _intToRoman($num) {
		// 确保输入是正整数
		$num = intval($num);
		if($num <= 0) return '0';

		// 罗马数字映射
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

	/**
	 * 替换模板中的占位符
	 * @param string $template HTML模板
	 * @param array $data 用于替换的数据
	 * @param array $context 上下文数据
	 * @param array $config 配置参数
	 * @return string 替换后的HTML
	 */
	private function _replacePlaceholders($template, $data, $context, $config) {
		$html = $template;

		// 替换数据占位符
		foreach($data as $key => $val) {
			if(!is_array($val) && !is_object($val)) {
				$html = str_replace('{' . $key . '}', $val, $html);
			}
		}

		// 替换上下文占位符
		foreach($context as $key => $val) {
			if(!is_array($val) && !is_object($val)) {
				$html = str_replace('{context.' . $key . '}', $val, $html);
			}
		}

		// 替换特殊占位符
		if(isset($context['depth'])) {
			$html = str_replace('{depth}', $context['depth'], $html);
		} else {
			$html = str_replace('{depth}', '', $html);
		}
		if(isset($context['index'])) {
			$html = str_replace('{index}', $context['index'], $html);
		} else {
			$html = str_replace('{index}', '', $html);
		}
		// 添加层级编号占位符替换
		if(isset($context['levelNumber'])) {
			$html = str_replace('{levelNumber}', $context['levelNumber'], $html);
		} else {
			$html = str_replace('{levelNumber}', '', $html);
		}

		// 添加对任意嵌套数据的支持，利用现有的_value方法
		// 使用正则表达式查找所有包含点分隔符的占位符（但排除context.开头的）
		preg_match_all('/\{(?!context\.)([\w]+)\.([\w\.]+)\}/', $html, $matches);

		if (!empty($matches[1])) {
			// matches[1]包含根键名，matches[2]包含剩余的路径，matches[0]包含完整的占位符
			foreach (array_keys($matches[1]) as $index) {
				$rootKey = $matches[1][$index];
				$nestedPath = $matches[2][$index];
				$fullPlaceholder = $matches[0][$index];

				// 初始化为空字符串
				$value = '';

				// 检查根键是否存在于data中
				if (isset($data[$rootKey])) {
					// 使用_value方法获取嵌套值
					$nestedValue = $this->_value($nestedPath, $data[$rootKey]);

					// 如果值存在且不是数组或对象，则使用该值
					if ($nestedValue !== false && !is_array($nestedValue) && !is_object($nestedValue)) {
						$value = $nestedValue;
					}
				}

				// 无论值是否存在，都进行替换（不存在时替换为空字符串）
				$html = str_replace($fullPlaceholder, $value, $html);
			}
		}

		return $html;
	}

	/**
	 * 规范化配置参数
	 * @param array $config 原始配置
	 * @return array 规范化后的配置
	 */
	private function _normalizeConfig($config) {
		// 确保所有必要的配置项都存在
		$defaultConfig = [
			'childrenKey' => 'items',
			'itemTemplate' => '{content}',
			'containerTemplate' => '{items}',
			'emptyTemplate' => '',
			'separator' => '',
			'childrenClass' => 'cdx-list__item-children', // 默认的children类名
			'counterType' => 'numeric', // 默认的计数器类型
			'start' => 1 // 默认的起始编号
		];

		return array_merge($defaultConfig, $config);
	}

	/**
	 * 用于在模板中调用完全参数化递归渲染的方法
	 * 用法: [recursive dataPath,configJson]
	 * configJson格式: {"childrenKey":"items","itemTemplate":"<li>{content}</li>","containerTemplate":"<ul>{items}</ul>"}
	 * 基本使用（使用默认配置）：[recursive data.data.items]
	 * 自定义所有参数（匹配您提供的HTML格式）：[recursive data.data.items,"{\"childrenKey\":\"items\",\"itemTemplate\":\"<li class=\\\"cdx-list__item\\\"><div class=\\\"cdx-list__item-content\\\" contenteditable=\\\"true\\\" data-empty=\\\"false\\\">{content}</div>{items}</li>\",\"containerTemplate\":\"<ol class=\\\"cdx-list cdx-list-ordered\\\" style=\\\"--list-counter-type: numeric;\\\">{items}</ol>\"}"]
	 * 使用不同的数据结构和键名：[recursive data.categories,"{\"childrenKey\":\"subcategories\",\"itemTemplate\":\"<option value=\\\"{id}\\\">{name}</option>{subcategories}\",\"containerTemplate\":\"<select>{items}</select>\"}"]
	 * 使用上下文变量（如索引、深度等）：[recursive data.items,"{\"itemTemplate\":\"<div class=\\\"level-{depth}\\\">{index}: {content}</div>{items}\",\"containerTemplate\":\"<div>{items}</div>\"}"]
	 */
	private function _recursive($m, $value = null) {
		// 解析参数 - 使用正则捕获组直接获取数据
		$dataPath = isset($m[1]) ? trim($m[1]) : '';

		// 获取JSON配置，只使用m[2]捕获组（与正则表达式匹配）
		$configJson = isset($m[2]) ? trim($m[2]) : '{}';

		// 移除配置字符串两端可能存在的引号
		if ((strpos($configJson, '"') === 0 && strrpos($configJson, '"') === strlen($configJson) - 1) ||
			(strpos($configJson, "'") === 0 && strrpos($configJson, "'") === strlen($configJson) - 1)) {
			$configJson = substr($configJson, 1, -1);
		}

		// 处理JSON中的转义字符，确保HTML标签正确解析
		$configJson = stripslashes($configJson);

		// 解析配置JSON，并添加错误处理
		$config = json_decode($configJson, true);
		// 检查JSON解析是否成功
		if(json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {
			// 如果解析失败，使用默认配置
			$config = [];
		}

		// 获取数据
		$data = [];
		if($dataPath != 'null' && $dataPath != null) {
			$data = $this->_value($dataPath, $value);
		}

		// 调用递归渲染方法
		return $this->_recursiveRender($data, $config);
	}

	private function _var($m, $value = null) {
		$v = $this->_value($m[1], $value);
		//return $v !== false ? $v : '';
		return $v !== false ? $v : $m[0];
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