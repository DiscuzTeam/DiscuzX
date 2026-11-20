<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowdownremoteimg']) {
	dexit();
}
$_GET['message'] = str_replace(["\r", "\n"], [$_GET['wysiwyg'] ? '<br />' : '', "\\n"], $_GET['message']);
preg_match_all("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[img=\d{1,4}[x|\,]\d{1,4}\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", $_GET['message'], $image1, PREG_SET_ORDER);
preg_match_all("/\<img.+\bsrc\b\s*=('|\"|)(.*)('|\"|)([\s].*)?\>/ismU", $_GET['message'], $image2, PREG_SET_ORDER);
$temp = $aids = $existentimg = [];
if(is_array($image1) && !empty($image1)) {
	foreach($image1 as $value) {
		$temp[] = [
			'0' => $value[0],
			'1' => trim(!empty($value[1]) ? $value[1] : $value[2])
		];
	}
}
if(is_array($image2) && !empty($image2)) {
	foreach($image2 as $value) {
		$temp[] = [
			'0' => $value[0],
			'1' => trim($value[2])
		];
	}
}
require_once libfile('class/image');
if(is_array($temp) && !empty($temp)) {
	$upload = new discuz_upload();
	$attachaids = [];

	foreach($temp as $value) {
		$imageurl = $value[1];
		$hash = md5($imageurl);
		if(strlen($imageurl)) {
			$imagereplace['oldimageurl'][] = $value[0];
			if(!isset($existentimg[$hash])) {
				$existentimg[$hash] = $imageurl;
				$attach['ext'] = $upload->fileext($imageurl);
				if(!$upload->is_image_ext($attach['ext'])) {
					continue;
				}
				$content = '';
				if(preg_match('/^(http(s?):\/\/|\.)/i', $imageurl)) {
					$content = dfsockopen($imageurl);
				} elseif(preg_match('/^('.preg_quote(getglobal('setting/attachurl'), '/').')/i', $imageurl)) {
					$imagereplace['newimageurl'][] = $value[0];
				}
				if(empty($content)) continue;
				$patharr = explode('/', $imageurl);
				$attach['name'] = trim($patharr[count($patharr) - 1]);
				$attach['thumb'] = '';

				$attach['isimage'] = $upload->is_image_ext($attach['ext']);
				$attach['extension'] = $upload->get_target_extension($attach['ext']);
				$attach['attachdir'] = $upload->get_target_dir('forum');
				$attach['attachment'] = $attach['attachdir'].$upload->get_target_filename('forum').'.'.$attach['extension'];
				$attach['target'] = getglobal('setting/attachdir').'./forum/'.$attach['attachment'];

				if(!@$fp = fopen($attach['target'], 'wb')) {
					continue;
				} else {
					flock($fp, 2);
					fwrite($fp, $content);
					fclose($fp);
				}

				if(!$upload->get_image_info($attach['target'])) {
					@unlink($attach['target']);
					continue;
				}
				$attach['size'] = filesize($attach['target']);

				$remote = 0;
				if(ftpperm($attach['extension'], $attach['size'])) {
					ftpcmd('upload', 'forum/'.$attach['attachment']);
					@unlink($attach['target']);
					$remote = 1;
				}

				$upload->attach = $attach;
				$thumb = $width = $height = 0;
				if($upload->attach['isimage']) {
					if($_G['setting']['thumbsource'] && $_G['setting']['sourcewidth'] && $_G['setting']['sourceheight']) {
						$image = new image();
						$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['sourcewidth'], $_G['setting']['sourceheight'], 1, 1) ? 1 : 0;
						$width = $image->imginfo['width'];
						$height = $image->imginfo['height'];
						$upload->attach['size'] = $image->imginfo['size'];
					}
					if($_G['setting']['thumbstatus']) {
						$image = new image();
						$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], 0) ? 1 : 0;
						$width = $image->imginfo['width'];
						$height = $image->imginfo['height'];
					}
					if($_G['setting']['thumbsource'] || !$_G['setting']['thumbstatus']) {
						list($width, $height) = @getimagesize($upload->attach['target']);
					}
					if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
						$image = new image();
						$image->Watermark($attach['target'], '', 'forum');
						$upload->attach['size'] = $image->imginfo['size'];
					}
					if($thumb && $remote && $_G['setting']['ftp']['on'] == 2) {
						ftpcmd('upload', 'forum/'.getimgthumbname($upload->attach['attachment']));
						@unlink(getglobal('setting/attachdir').'forum/'.getimgthumbname($upload->attach['attachment']));
					}
				}
				$aids[] = $aid = getattachnewaid();
				$setarr = [
					'aid' => $aid,
					'dateline' => $_G['timestamp'],
					'filename' => strip_tags(str_replace('"', '', $upload->attach['name'])),
					'filesize' => $upload->attach['size'],
					'attachment' => $upload->attach['attachment'],
					'isimage' => $upload->attach['isimage'],
					'uid' => $_G['uid'],
					'thumb' => $thumb,
					'remote' => $remote,
					'width' => $width,
					'height' => $height
				];
				table_forum_attachment_unused::t()->insert($setarr);
				$attachaids[$hash] = $imagereplace['newimageurl'][] = '[attachimg]'.$aid.'[/attachimg]';

			} else {
				$imagereplace['newimageurl'][] = $attachaids[$hash];
			}
		}
	}
	if(!empty($aids)) {
		require_once libfile('function/post');
	}
	$_GET['message'] = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $_GET['message']);
}
$_GET['message'] = dhtmlspecialchars(addcslashes($_GET['message'], '/"\''));
print <<<EOF
		<script type="text/javascript">
			parent.ATTACHORIMAGE = 1;
			parent.updateDownImageList('{$_GET['message']}');
		</script>
EOF;
dexit();
	