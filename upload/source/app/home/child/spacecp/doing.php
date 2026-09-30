<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

use table_common_member;
use table_home_follow;
use table_home_friend;

if(!$_G['setting']['doingstatus']) {
	showmessage('doing_status_off');
}

/**
 * 读取记录上传限制设置（含默认值）
 * 设置项保存在 common_setting，可在 后台->设置->空间设置->记录设置 中修改
 */
if(!function_exists('doing_upload_limits')) {
	function doing_upload_limits() {
		$setting = getglobal('setting');
		$videoexts = !empty($setting['doingvideoext']) ? strtolower($setting['doingvideoext']) : 'mp4,webm,mov';
		$videoexts = array_unique(array_filter(array_map('trim', explode(',', $videoexts))));
		return [
			'imgmaxnum' => max(1, intval(isset($setting['doingimgmaxnum']) ? $setting['doingimgmaxnum'] : 9)),
			'imgmaxsize' => max(1, intval(isset($setting['doingimgmaxsize']) ? $setting['doingimgmaxsize'] : 2048)) * 1024,
			'imgexts' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'],
			'videoallow' => intval(isset($setting['doingvideoallow']) ? $setting['doingvideoallow'] : 1),
			'videomaxsize' => max(1, intval(isset($setting['doingvideomaxsize']) ? $setting['doingvideomaxsize'] : 50)) * 1024 * 1024,
			'videoexts' => $videoexts,
		];
	}
}

/**
 * 校验单张记录图片是否合法，合法返回空字符串，否则返回错误语言键
 */
if(!function_exists('doing_check_image_file')) {
	function doing_check_image_file($file, $limits) {
		if(!is_array($file) || empty($file['name']) || !is_string($file['name'])) {
			return 'doing_upload_image_invalid';
		}
		if(!empty($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
			return 'doing_upload_image_invalid';
		}
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if(!in_array($ext, $limits['imgexts'])) {
			return 'doing_upload_image_ext_invalid';
		}
		if($file['size'] > $limits['imgmaxsize']) {
			return 'doing_upload_image_too_large';
		}
		return '';
	}
}

$doid = empty($_GET['doid']) ? 0 : intval($_GET['doid']);
$docid = empty($_GET['docid']) ? 0 : intval($_GET['docid']);

if($_GET['op'] == 'delete') {
	if(submitcheck('deletesubmit')) {
		if($docid) {
			$allowmanage = checkperm('managedoing');
			if($value = table_home_docomment::t()->fetch($docid)) {
				$home_doing = table_home_doing::t()->fetch($value['doid']);
				$value['duid'] = $home_doing['uid'];
				if($allowmanage || $value['uid'] == $_G['uid'] || $value['duid'] == $_G['uid']) {
					table_home_docomment::t()->update($docid, ['uid' => 0, 'username' => '', 'message' => '']);
					if($value['uid'] != $_G['uid'] && $value['duid'] != $_G['uid']) {
						batchupdatecredit('comment', $value['uid'], [], -1);
					}
					table_home_doing::t()->update_replynum_by_doid(-1, $updo['doid']);
				}
			}
		} else {
			require_once libfile('function/delete');
			deletedoings([$doid]);
		}

		if(defined('IN_RESTFUL')) {
			showmessage('doing_delete_success');
			exit();
		}else{
			dheader('location: '.dreferer());
			exit();
		}
	}
} elseif($_GET['op'] == 'docomment') {
	$doid = intval($_GET['doid']);
	$docid = intval($_GET['docid']);
	$key = empty($_GET['key']) ? random(8) : $_GET['key'];
	$_GET['key'] = $key;

	$updo = [];
	if($docid) {
		$updo = table_home_docomment::t()->fetch($docid);
	} elseif($doid) {
		$updo = table_home_doing::t()->fetch($doid);
	}
	
	if(empty($updo)) {
		showmessage('docomment_error');
	}

	// 移动端单条详情页内联加载时只返回表单片段（不含整页头尾）
	if(defined('IN_MOBILE') && !empty($_GET['fragment'])) {
		include template('home/spacecp_doing_formfragment');
	} else {
		include template('home/spacecp_doing');
	}
	dexit();
} elseif($_GET['op'] == 'getcomment') {
	$key = empty($_GET['key']) ? random(8) : $_GET['key'];
	$_GET['key'] = $key;
	$doid = empty($_GET['doid']) ? 0 : intval($_GET['doid']);
	$doing_info = table_home_doing::t()->fetch($doid);

	$comment_perpage = intval(10);
	$comment_page = empty($_GET['page_c']) ? 0 : intval($_GET['page_c']);
	if($comment_page < 1) $comment_page = 1;
	$comment_start = intval(($comment_page - 1) * $comment_perpage);

	$list = [];
	$highlight = 0;
	$count = 0;
	$has_more = false;

	if(empty($_GET['close'])) {
		// 获取顶级评论总数
		$top_comment_count = intval(table_home_docomment::t()->count_top_by_doid($doid));
		
		// 1. 获取分页的顶级评论
		$top_comments = table_home_docomment::t()->fetch_all_top_by_doid($doid, $comment_start, $comment_perpage);
		$top_ids = array_column($top_comments, 'id');
		
		// 检查是否有更多评论（只有在列表页通过AJAX加载时才显示更多链接）
		// 在单条动态详情页，通过URL参数判断，应该显示分页而不是更多链接
		// 列表页AJAX请求：不包含page_c参数，显示"更多"链接
		// 单条动态详情页AJAX请求：包含page_c参数，显示分页，不显示"更多"链接
		$is_ajax_list = isset($_GET['inlist']);
		$has_more = $is_ajax_list && $top_comment_count > $comment_perpage;
		
		// 2. 获取所有子评论
		$child_comments = table_home_docomment::t()->fetch_all_child_by_doid($doid);
		
		// 3. 合并评论
		$all_comments = array_merge($top_comments, $child_comments);
		
		// 4. 构建评论映射表
		$comment_map = [];
		$root_comments = [];
		
		foreach($all_comments as $comment) {
			if(!empty($comment['ip'])) {
				$comment['ip'] = ip::to_display($comment['ip']);
				$comment['iplocation'] = $_G['setting']['showiplocation'] ? ip::convert($comment['ip'], true) : '';
			}
			$comment_map[$comment['id']] = $comment;
			
			if($comment['upid'] == 0) {
				$root_comments[] = $comment;
			}
			
			$count++;
		}
		
		// 5. 构建评论树结构
		$comment_tree = [];
		foreach($comment_map as $comment_id => $comment) {
			$parent_id = $comment['upid'];
			if($parent_id == 0) {
				// 根评论
				$comment_tree[$comment_id] = $comment;
				$comment_tree[$comment_id]['children'] = [];
			} else {
				// 子评论，添加到顶级评论的children数组中，形成扁平的二级结构
				if(isset($comment_map[$parent_id])) {
					$parent_comment = $comment_map[$parent_id];
					
					// 找到顶级父评论
					$top_parent_id = $parent_id;
					while($top_parent_id > 0 && isset($comment_map[$top_parent_id]) && $comment_map[$top_parent_id]['upid'] > 0) {
						$top_parent_id = $comment_map[$top_parent_id]['upid'];
					}
					
					// 只有当顶级父评论是前10条根评论时，才显示当前子评论
					if(in_array($top_parent_id, $top_ids)) {
						$comment_tree[$top_parent_id]['children'][] = $comment;
					}
				}
			}
		}
		
		// 6. 遍历评论树，生成评论列表
		foreach($comment_tree as $root_id => $root_comment) {
			
			// 显示根评论
			$root_comment['layer'] = 0;
			$root_comment['style'] = "padding-left:0em;";
			$root_comment['class'] = '';
			$root_comment['is_child'] = false;
			$root_comment['reply_to_user'] = '';
			$root_comment['reply_uid'] = 0;
			$root_comment['avatar'] = avatar($root_comment['uid'], 'small', true);
			$root_comment['dateline_formatted'] = dgmdate($root_comment['dateline'], 'u');
			// 判断是否可以删除评论
			$root_comment['can_delete'] = ($root_comment['uid'] == $_G['uid'] || $doing_info['uid'] == $_G['uid'] || checkperm('managedoing'));
			
			// 移除高亮逻辑，因为$highlight和$space变量未初始化
			// if($root_comment['id'] == $highlight && $root_comment['uid'] == $space['uid']) {
			// 	$root_comment['style'] .= 'color:#F60;';
			// }
			
			$list[] = $root_comment;
			
			// 显示子评论，只显示前3条，超过则提供展开/折叠功能
			if(!empty($root_comment['children'])) {
				$child_count = count($root_comment['children']);
				$show_child_count = 3;
				$show_children = array_slice($root_comment['children'], 0, $show_child_count);
				$hide_children = array_slice($root_comment['children'], $show_child_count);
				
				// 显示前3条子评论
				foreach($show_children as $child_comment) {
					$child_comment['layer'] = 2;
					$child_comment['style'] = "padding-left:4em;";
					$child_comment['class'] = ' dtll';
					$child_comment['is_child'] = true;
					$child_comment['is_hidden'] = false;
					$child_comment['avatar'] = avatar($child_comment['uid'], 'small', true);
					$child_comment['dateline_formatted'] = dgmdate($child_comment['dateline'], 'u');
					// 判断是否可以删除评论
					$child_comment['can_delete'] = ($child_comment['uid'] == $_G['uid'] || $doing_info['uid'] == $_G['uid'] || checkperm('managedoing'));
					
					// 设置回复信息：只有当回复的是其他子评论时，才显示回复信息
					$child_comment['reply_to_user'] = '';
					$child_comment['reply_uid'] = 0;
					
					if($child_comment['upid'] > 0 && isset($comment_map[$child_comment['upid']])) {
						$parent_comment = $comment_map[$child_comment['upid']];
						// 只有当父评论也是子评论时，才显示回复信息
						if($parent_comment['upid'] > 0) {
							$child_comment['reply_to_user'] = $parent_comment['username'];
							$child_comment['reply_uid'] = $parent_comment['uid'];
						}
					}
					
					$list[] = $child_comment;
				}
				
				// 如果有隐藏的子评论，显示展开按钮
				if(!empty($hide_children)) {
					// 添加展开/折叠按钮
					$toggle_comment = array(
						'id' => "toggle_{$root_id}",
						'doid' => $root_comment['doid'],
						'layer' => 2,
						'style' => "padding-left:2em;",
						'class' => ' dtll toggle-comment',
						'is_child' => true,
						'is_toggle' => true,
						'hide_count' => count($hide_children),
						'root_id' => $root_id,
						'username' => '',
						'message' => "",
						'reply_to_user' => '',
						'reply_uid' => 0,
						'is_hidden' => false,
						'can_delete' => false
					);
					$list[] = $toggle_comment;
					
					// 添加隐藏的子评论
					foreach($hide_children as $child_comment) {
						$child_comment['layer'] = 2;
						$child_comment['style'] = "padding-left:2em; display: none;";
						$child_comment['class'] = ' dtll hidden-comment';
						$child_comment['is_child'] = true;
						$child_comment['is_hidden'] = true;
						$child_comment['root_id'] = $root_id;
						$child_comment['avatar'] = avatar($child_comment['uid'], 'small', true);
						$child_comment['dateline_formatted'] = dgmdate($child_comment['dateline'], 'u');
						// 判断是否可以删除评论
						$child_comment['can_delete'] = ($child_comment['uid'] == $_G['uid'] || $doing_info['uid'] == $_G['uid'] || checkperm('managedoing'));
						
						// 设置回复信息：只有当回复的是其他子评论时，才显示回复信息
						$child_comment['reply_to_user'] = '';
						$child_comment['reply_uid'] = 0;
						
						if($child_comment['upid'] > 0 && isset($comment_map[$child_comment['upid']])) {
							$parent_comment = $comment_map[$child_comment['upid']];
							// 只有当父评论也是子评论时，才显示回复信息
							if($parent_comment['upid'] > 0) {
								$child_comment['reply_to_user'] = $parent_comment['username'];
								$child_comment['reply_uid'] = $parent_comment['uid'];
							}
						}
						
						$list[] = $child_comment;
					}
				}
			}
		}
	}
	
	// 生成评论分页链接（只有在单条动态详情页AJAX请求时才显示）
	$comment_multi = array();
	if (isset($_GET['page_c'])) {
		if($top_comment_count > $comment_perpage) {
			// 生成AJAX分页链接，指向当前的getcomment接口
			$comment_url = "home.php?mod=spacecp&ac=doing&op=getcomment&doid=$doid&key=$key&page_c={page}";
			$comment_multi[$doid] = multi($top_comment_count, $comment_perpage, $comment_page, $comment_url);
			
			// 修改分页链接的onclick事件，使其异步加载
			// 使用正则表达式匹配并替换每个分页链接，处理包含ajaxtarget属性的情况
			// 匹配所有分页链接，包括page_c=数字、page_c=空和没有page_c参数的情况
			preg_match_all('/<a\s+href="([^"]*?)"[^>]*>/', $comment_multi[$doid], $matches);
			if(!empty($matches[0])) {
				foreach($matches[0] as $index => $full_match) {
					$href = $matches[1][$index];
					// 解析页码，处理不同情况：page_c=数字、page_c=空、没有page_c参数
					$page_num = 1; // 默认第一页
					if(preg_match('/page_c=(\d+)/', $href, $page_match)) {
						// 有明确的页码，如page_c=2
						$page_num = $page_match[1];
					} elseif(strpos($href, 'page_c=') !== false) {
						// page_c后面为空，如page_c=
						$page_num = 1;
					}
					// 生成新的链接，使用javascript:;和onclick事件，移除原有的href和ajaxtarget属性
					$new_link = '<a href="javascript:;" onclick="docomment_get_page('.$doid.', \''.$key.'\', '.$page_num.');" data-page="'.$page_num.'">';
					$comment_multi[$doid] = str_replace($full_match, $new_link, $comment_multi[$doid]);
				}
			}
			
			// 处理当前页的链接（通常是<span>标签）
			$comment_multi[$doid] = preg_replace('/<span[^>]*>(\d+)<\/span>/', '<span data-page="$1">$1</span>', $comment_multi[$doid]);
		}
	}
	
	// 检查是否是移动端请求，返回JSON数据
	if (defined('IN_MOBILE')) {
		// 返回JSON数据，包含分页信息而非HTML
		$response = array(
			'list' => $list,
			'count' => $count,
			'has_more' => $has_more,
			'page' => $comment_page,
			'perpage' => $comment_perpage,
			'total_pages' => ceil($top_comment_count / $comment_perpage),
			'total_comments' => $top_comment_count
		);

		// 设置响应头为JSON
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($response);
		dexit();
	} else {
		// 输出评论列表
		include template('home/space_doing_comment_li');
		dexit();
	}
} elseif($_GET['op'] == 'recommend') {
	// 处理点赞请求
	$doid = intval($_GET['doid']);
	$uid = $_G['uid'];

	if(!$doid || !$uid) {
		showmessage('to_login', '', array(), array('login' => 1));
	}

	$doing = table_home_doing::t()->fetch($doid);
	if(!$doing) {
		showmessage('doing_not_exists');
	}

	$exists = table_home_doing_recomend_log::t()->fetch_by_doid_uid($doid, $uid);

	if($exists) {
		// 取消点赞
		table_home_doing::t()->update_recommendnum_by_doid(-1, $doid);
		table_home_doing_recomend_log::t()->delete_by_doid_uid($doid, $uid);
		$status = 0;
	} else {
		// 添加点赞
		table_home_doing::t()->update_recommendnum_by_doid(1, $doid);
		table_home_doing_recomend_log::t()->insert(array(
			'doid' => $doid,
			'uid' => $uid,
			'dateline' => TIMESTAMP
		));
		$status = 1;
	}

	// 重新获取点赞数
	$doing = table_home_doing::t()->fetch($doid);
	$recomends = $doing['recomends'];

	if(defined('IN_RESTFUL')) {
		showmessage('doing_recommend_success', '', array(), array('status' => $status,'count' => $recomends));
		exit();
	}else{
		// 直接返回JSON格式数据
		header('Content-Type: application/json');
		echo json_encode(array(
			'message' => 'doing_recommend_success',
			'status' => $status,
			'count' => $recomends
		));
		exit;
	}
	exit;
} elseif($_GET['op'] == 'spacenote') {
	space_merge($space, 'field_home');
} elseif($_GET['op'] == 'upload_image') {
	if(!checkperm('allowdoing')) {
		showmessage('no_privilege_doing', '', array(), array('login' => 1));
	}

	// 服务端强制校验：图片数量、单张尺寸、图片类型
	$limits = doing_upload_limits();
	$errkey = '';
	if(empty($_FILES['Filedata']) || !is_array($_FILES['Filedata']) || is_array($_FILES['Filedata']['name'])) {
		$errkey = 'doing_upload_image_invalid';
	} else {
		$errkey = doing_check_image_file($_FILES['Filedata'], $limits);
		if(!$errkey) {
			$tempcount = DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d AND doid=0 AND isimage IN (1, -1)', ['home_doing_attachment', $_G['uid']]);
			if($tempcount >= $limits['imgmaxnum']) {
				$errkey = 'doing_upload_image_too_many';
			}
		}
	}
	if($errkey) {
		$errvars = [
			'num' => $limits['imgmaxnum'],
			'size' => intval($limits['imgmaxsize'] / 1024),
		];
		if(defined('IN_RESTFUL')) {
			showmessage($errkey, '', $errvars);
		}
		header('Content-Type: application/json');
		echo json_encode([
			'status' => 'error',
			'message' => lang('message', $errkey, $errvars),
		]);
		exit;
	}

	if (!empty($_FILES)) {
		$upload = new upload('doing');
		$f = $upload->upload();
		$result = [
			'status' => 'success',
			'images' => []
		];

		$image = null;

		if (!empty($f['Filedata']) && is_array($f['Filedata'])) {
			$value = $f['Filedata'];
			if (is_array($value) && $value['attachment']) {
				if (isset($value['isimage']) && $value['isimage'] == 1) {
					$temp_doid = 0; 
					$aid = table_home_doing_attachment::t()->insert_attachment([
						'doid' => $temp_doid,
						'uid' => $_G['uid'],
						'dateline' => TIMESTAMP,
						'filename' => $value['name'],
						'filesize' => $value['size'],
						'attachment' => $value['attachment'],
						'remote' => $value['remote'] ?? 0,
						'isimage' => $value['isimage'],
						'width' => $value['imageinfo'][0] ?? 0,
						'height' => $value['imageinfo'][1] ?? 0,
						'displayorder' => 0
					], true);
					$image_url = getglobal('setting/attachurl').'doing/'.$value['attachment'];
					if($value['remote']) {
						$image_url = getglobal('setting/ftp/attachurl').'doing/'.$value['attachment'];
					}
					
					$image = [
						'aid' => $aid,
						'filename' => $value['name'],
						'url' => $image_url,
						'width' => $value['imageinfo'][0] ?? 0,
						'height' => $value['imageinfo'][1] ?? 0,
						'filesize' => $value['size'],
						'extension' => $value['ext'] ?? '',
						'mime_type' => $value['type'] ?? ''
					];
				}
			}
		}

		// 构建结果
		if ($image) {
			$result = [
				'status' => 'success',
				'image' => $image
			];
		} else {
			$result = [
				'status' => 'error',
				'message' => 'Upload failed or no valid image found',
				'debug_info' => $f // 仅用于调试，实际部署时应移除
			];
			
			if(defined('IN_RESTFUL')) {
				showmessage('upload_failed', '', array(), array('result' => $result));
			} else {
				header('Content-Type: application/json');
				echo json_encode($result);
				exit;
			}
		}

		if(defined('IN_RESTFUL')) {
			echo $result;
		} else {
			header('Content-Type: application/json');
			echo json_encode($result);
			exit;
		}
	} else {
		$result = [
			'status' => 'error',
			'message' => 'No files uploaded'
		];

		if(defined('IN_RESTFUL')) {
			echo $result;
		} else {
			header('Content-Type: application/json');
			echo json_encode($result);
			exit;
		}
	}
} elseif($_GET['op'] == 'delete_image') {
	$count = 0;

	if(isset($_GET['aids'])) {
		$_GET['aids'] = (array)$_GET['aids'];
		foreach($_GET['aids'] as $aid) {
			$attach = table_home_doing_attachment::t()->fetch_attachment('aid:'.$aid, $aid);
			if($attach && $attach['uid'] == $_G['uid']) {

				table_home_doing_attachment::t()->delete($aid);
				pic_delete($attach['attachment'], 'doing', empty($attach['isimage']) ? 1 : 0, $attach['remote']);
				if($_G['setting']['ftp']['on'] == 2) {
					ftpcmd('delete', 'doing/'.$attach['attachment']);
					ftpcmd('delete', 'doing/'.getimgthumbname($attach['attachment']));
				}

				$count++;
			}
		}
	}

	if(defined('IN_RESTFUL')) {
		echo $count;
		exit();
	}

	include template('common/header_ajax');
	echo $count;
	include template('common/footer_ajax');
	dexit();
}else{
	$type = empty($_GET['type']) ? '' : $_GET['type'];
	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
	$note_uid = 0;
	$note_message = '';
	$note_values = [];

	$arr = [];
	$feed_hash_data = '';

	switch($type) {
		case 'space':

			$feed_hash_data = "uid{$id}";

			$tospace = getuserbyuid($id);
			if(empty($tospace)) {
				showmessage('space_does_not_exist');
			}
			if(isblacklist($tospace['uid'])) {
				showmessage('is_blacklist');
			}

			$arr['itemid'] = $id;
			$arr['body_template'] = '<h5><a href="home.php?mod=space&uid={uid}" target="_blank">{username}</a></h5><p><a href="home.php?mod=space&uid={uid}" target="_blank">'.lang('spacecp', 'homepage').'</a></p>';
			$arr['body_data'] = [
				'uid' => $id,
				'username' => $tospace['username'],
				'reside' => $tospace['residecountry'].$tospace['resideprovince'].$tospace['residecity'],
				'spacenote' => $tospace['spacenote']
			];

			loaducenter();
			$isavatar = uc_check_avatar($id);
			$arr['image'] = $arr['body_data']['image'] = $isavatar ? avatar($id, 'middle', true) : $_G['setting']['avatarurl'].'/noavatar.svg';
			$arr['image_link'] = $arr['body_data']['image_link'] = "home.php?mod=space&uid=$id";

			$note_uid = $id;
			$note_message = 'share_space';

			break;
		case 'blog':

			$feed_hash_data = "blogid{$id}";

			$blog = array_merge(
				table_home_blog::t()->fetch($id),
				table_home_blogfield::t()->fetch($id)
			);
			if(!$blog) {
				showmessage('blog_does_not_exist');
			}
			if(in_array($blog['status'], [1, 2])) {
				showmessage('moderate_blog_not_share');
			}
			if($blog['friend']) {
				showmessage('logs_can_not_share');
			}
			if(isblacklist($blog['uid'])) {
				showmessage('is_blacklist');
			}
			$arr['itemid'] = $id;
			$arr['body_template'] = '<h5><a href="{url}" target="_blank">{subject}</a></h5><p><a href="home.php?mod=space&uid={uid}">{username}</a>: {dateline} </p>';
			$arr['body_data'] = [
				'url' => "home.php?mod=space&uid=".$blog['uid']."&do=blog&id=".$blog['blogid'],
				'subject' => $blog['subject'],
				'uid' => $blog['uid'],
				'blog' => $blog['blogid'],
				'username' => $blog['username'],
				'dateline' => dgmdate($blog['dateline']),
				'message' => getstr($blog['message'], 150, 0, 0, 0, -1)
			];
			if($blog['pic']) {
				$arr['image'] = $arr['body_data']['image'] = pic_cover_get($blog['pic'], $blog['picflag']);
				$arr['image_link'] = $arr['body_data']['image_link'] = "home.php?mod=space&uid={$blog['uid']}&do=blog&id={$blog['blogid']}";
			}
			$note_uid = $blog['uid'];
			$note_message = 'share_blog';
			$note_values = ['url' => "home.php?mod=space&uid={$blog['uid']}&do=blog&id={$blog['blogid']}", 'subject' => $blog['subject'], 'from_id' => $id, 'from_idtype' => 'blogid'];

			$hotarr = ['blogid', $blog['blogid'], $blog['hotuser']];

			break;
		case 'album':

			$feed_hash_data = "albumid{$id}";

			if(!$album = table_home_album::t()->fetch_album($id)) {
				showmessage('album_does_not_exist');
			}
			if($album['friend']) {
				showmessage('album_can_not_share');
			}
			if(isblacklist($album['uid'])) {
				showmessage('is_blacklist');
			}

			$arr['itemid'] = $id;
			$arr['body_template'] = '<h5><a href="{url}" target="_blank">{albumname}</a></h5><p><a href="home.php?mod=space&uid={uid}">{username}</a>: {dateline} </p>';
			$arr['body_data'] = [
				'url' => "home.php?mod=space&uid={$album['uid']}&do=album&id={$album['albumid']}",
				'albumname' => $album['albumname'],
				'uid' => $album['uid'],
				'albumid' => $album['albumid'],
				'username' => $album['username'],
				'dateline' => dgmdate($album['dateline'])
			];
			$arr['image'] = $arr['body_data']['image'] = pic_cover_get($album['pic'], $album['picflag']);
			$arr['image_link'] = $arr['body_data']['image_link'] = "home.php?mod=space&uid={$album['uid']}&do=album&id={$album['albumid']}";
			$note_uid = $album['uid'];
			$note_message = 'share_album';
			$note_values = ['url' => "home.php?mod=space&uid={$album['uid']}&do=album&id={$album['albumid']}", 'albumname' => $album['albumname'], 'from_id' => $id, 'from_idtype' => 'albumid'];

			break;
		case 'pic':

			$feed_hash_data = "picid{$id}";
			$pic = table_home_pic::t()->fetch($id);
			if(!$pic) {
				showmessage('image_does_not_exist');
			}
			$picfield = table_home_picfield::t()->fetch($id);
			$album = table_home_album::t()->fetch_album($pic['albumid']);
			$pic = array_merge($pic, $picfield, $album);
			if(in_array($pic['status'], [1, 2])) {
				showmessage('moderate_pic_not_share');
			}
			if($pic['friend']) {
				showmessage('image_can_not_share');
			}
			if(isblacklist($pic['uid'])) {
				showmessage('is_blacklist');
			}
			if(empty($pic['albumid'])) $pic['albumid'] = 0;
			if(empty($pic['albumname'])) $pic['albumname'] = lang('spacecp', 'default_albumname');

			$arr['itemid'] = $id;
			$arr['body_template'] = '<h5>'.lang('spacecp', 'album').'<a href="{url}" target="_blank">{albumname}</a></h5><p><a href="home.php?mod=space&uid={uid}">{username}</a>: {dateline} </p>';
			$arr['body_data'] = [
				'url' => "home.php?mod=space&uid={$pic['uid']}&do=album&picid={$pic['picid']}",
				'albumname' => $pic['albumname'],
				'uid' => $pic['uid'],
				'picid' => $pic['picid'],
				'username' => $pic['username'],
				'dateline' => dgmdate($pic['dateline']),
				'title' => getstr($pic['title'], 100, 0, 0, 0, -1)
			];
			$arr['image'] = $arr['body_data']['image'] = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
			$arr['image_link'] = $arr['body_data']['image_link'] = "home.php?mod=space&uid={$pic['uid']}&do=album&picid={$pic['picid']}";
			$note_uid = $pic['uid'];
			$note_message = 'share_pic';
			$note_values = ['url' => "home.php?mod=space&uid={$pic['uid']}&do=album&picid={$pic['picid']}", 'albumname' => $pic['albumname'], 'from_id' => $id, 'from_idtype' => 'picid'];

			$hotarr = ['picid', $pic['picid'], $pic['hotuser']];

			break;

		case 'thread':
			//此段如果修改，须对应修改source/app/forum/extend/extend_thread_doing.php
			$feed_hash_data = "tid{$id}";

			$thread = table_forum_thread::t()->fetch_thread($id);
			if(in_array($thread['displayorder'], [-2, -3])) {
				showmessage('moderate_thread_not_share');
			}
			require_once libfile('function/post');
			$post = table_forum_post::t()->fetch_threadpost_by_tid_invisible($id);
			$arr['body_template'] = '<h5><a href="{url}" target="_blank">{subject}</a></h5><p><a href="home.php?mod=space&uid={authorid}">{author}</a>: {dateline} </p>';
			$post['message'] = threadmessagecutstr($thread, $post['message']);
			$arr['body_data'] = [
				'url' => "forum.php?mod=viewthread&tid=$id",
				'subject' => $thread['subject'],
				'authorid' => $thread['authorid'],
				'author' => $thread['author'],
				'tid' => $id,
				'dateline' => dgmdate($thread['dateline']),
				'message' => getstr($post['message'], 150, 0, 0, 0, -1)
			];
			$arr['itemid'] = $id;
			$attachment = table_forum_attachment_n::t()->fetch_max_image('tid:'.$id, 'tid', $id);
			if($attachment) {
				$arr['image'] = $arr['body_data']['image'] = pic_get($attachment['attachment'], 'forum', $attachment['thumb'], $attachment['remote'], 1);
				$arr['image_link'] = $arr['body_data']['image_link'] = "forum.php?mod=viewthread&tid=$id";
			}

			$note_uid = $thread['authorid'];
			$note_message = 'share_thread';
			$note_values = ['url' => "forum.php?mod=viewthread&tid=$id", 'subject' => $thread['subject'], 'from_id' => $id, 'from_idtype' => 'tid'];
			break;

		case 'article':

			$feed_hash_data = "articleid{$id}";

			$article = table_portal_article_title::t()->fetch($id);
			if(!$article) {
				showmessage('article_does_not_exist');
			}
			if(in_array($article['status'], [1, 2])) {
				showmessage('moderate_article_not_share');
			}

			require_once libfile('function/portal');
			$article_url = fetch_article_url($article);
			$arr['itemid'] = $id;
			$arr['body_template'] = '<h5><a href="{url}" target="_blank">{title}</a></h5><p><a href="home.php?mod=space&uid={uid}">{username}</a>: {dateline} </p>';
			$arr['body_data'] = [
				'url' => $article_url,
				'title' => $article['title'],
				'uid' => $article['uid'],
				'articleid' => $id,
				'username' => $article['username'],
				'dateline' => dgmdate($article['dateline']),
				'summary' => getstr($article['summary'], 150, 0, 0, 0, -1)
			];
			if($article['pic']) {
				$arr['image'] = $arr['body_data']['image'] = pic_get($article['pic'], 'portal', $article['thumb'], $article['remote'], 1, 1);
				$arr['image_link'] = $arr['body_data']['image_link'] = $article_url;
			}
			$note_uid = $article['uid'];
			$note_message = 'share_article';
			$note_values = ['url' => $article_url, 'subject' => $article['title'], 'from_id' => $id, 'from_idtype' => 'aid'];

			break;
		case 'doing':
			$feed_hash_data = "doingid{$id}";
			$sdoing = table_home_doing::t()->fetch($id);
			if(!$sdoing) {
				showmessage('doing_does_not_exist');
			}
			$arr['itemid'] = $id;
			$arr['body_template'] = '<p><a href="{url}" target="_blank">{message}</a></p><p><a href="home.php?mod=space&uid={uid}">{username}</a> - {dateline} </p>';
			$arr['body_data'] = [
				'url' => "home.php?mod=space&do=doing&doid={$sdoing['doid']}",
				'message' => getstr($sdoing['message'], 150, 0, 0, 0, -1),
				'uid' => $sdoing['uid'],
				'doid' => $sdoing['doid'],
				'username' => $sdoing['username'],
				'dateline' => dgmdate($sdoing['dateline']),
			];
			// 查询记录对应的附件信息
			$attachment = table_home_doing_attachment::t()->fetch_max_image(0, 'doid', $id);
			if($attachment) {
				$arr['image'] = $arr['body_data']['image'] = getdiscuzimg('doing', $attachment['aid'], 0, 140, 140);
				$arr['image_link'] = $arr['body_data']['image_link'] = "home.php?mod=space&do=doing&doid={$sdoing['doid']}";
			}
			break;
		default:
			$arr['itemid'] = 0;
			$arr['body_template'] = '';
			break;
	}
	$commentcable = ['blog' => 'blogid', 'pic' => 'picid', 'thread' => 'thread', 'article' => 'article', 'doing' => 'doid'];

	if(submitcheck('addsubmit')) {

		if(!checkperm('allowdoing')) {
			showmessage('no_privilege_doing');
		}

		cknewuser();

		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast', '', ['waittime' => $waittime]);
		}

		$message = getstr($_POST['message'], 200, 0, 0, 1);
		$message = preg_replace('/\<br.*?\>/i', ' ', $message);
		if(strlen($message) < 1) {
			showmessage('should_write_that');
		}

		$message = censor($message, NULL, TRUE, false);
		if(is_array($message) && $message['message']) {
			showmessage($message['message'], dreferer(), ['message' => $message['message']]);
		}

		if(censormod($message) || $_G['group']['allowdoingmod']) {
			$doing_status = 1;
		} else {
			$doing_status = 0;
		}

		// AT功能处理
		if($_G['group']['allowat']) {
			$atlist = parse_at_user($message);
			if($atlist) {
				// 处理AT标签，生成链接
				$atsearch = [];
				$atreplace = [];
				foreach($atlist as $atuid => $atusername) {
					$atsearch[] = '/@'.preg_quote($atusername, '/').' /i';
					$atreplace[] = '<a href="home.php?mod=space&uid='.$atuid.'" class="atuser" target="_blank" c="1">@'.$atusername.'</a> ';

					// 匹配新格式：@[用户名]（带方括号）
					$atsearch[] = '/@\['.preg_quote($atusername, '/').'\]/i';
					$atreplace[] = '<a href="home.php?mod=space&uid='.$atuid.'" class="atuser" target="_blank" c="1">@'.$atusername.'</a> ';
				}
				$updated_message = preg_replace($atsearch, $atreplace, $message.' ', 1);
				$updated_message = substr($updated_message, 0, strlen($updated_message) - 1);
				
				// 存储AT信息到fields字段
				$updatefields['at'] = $atlist;
				$message = $updated_message;
			}
		}

		$arr['body_data'] = serialize($arr['body_data']);
		$setarr = [
			'itemid' => $arr['itemid'],
			'type' => $type,
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'body_template' => $arr['body_template'],
			'body_data' => $arr['body_data'],
			'message' => $message,
			'ip' => $_G['clientip'],
			'port' => $_G['remoteport'],
			'recomends' => 0,
			'status' => $doing_status,
		];
		$newdoid = table_home_doing::t()->insert($setarr, 1);

		// 标签提取和处理
		$class_tag = new tag();
		$tags = '';
		// 从消息中提取标签（假设标签格式为#标签名#）
		preg_match_all('/#([^#]+)#/', $message, $matches);
		if (!empty($matches[1])) {
			$tags = implode(',', $matches[1]) . ',';
			// 添加标签关联
			if($tags) {
				$tagsarr = $class_tag->add_tag($tags, $newdoid, 'doid',true);
			}
		}
		if($tagsarr) {
			$updatefields['tags'] = $tagsarr;
		}
		if($atlist) {
			// 发送通知给被AT的用户
			foreach($atlist as $atuid => $atusername) {
				notification_add($atuid, 'at', 'at_doing', array('from_id' => $newdoid, 'from_idtype' => 'at', 'buyerid' => $_G['uid'], 'buyer' => $_G['username'], 'doid' => $newdoid));
			}
		}
		$updatearr['fields'] = !empty($updatefields) ? json_encode($updatefields) : '{}';
		table_home_doing::t()->update($newdoid, $updatearr);

		$setarr = ['recentnote' => $message, 'spacenote' => $message];
		$credit = $experience = 0;
		$extrasql = ['doings' => 1];

		updatecreditbyaction('doing', 0, $extrasql);

		table_common_member_field_home::t()->update($_G['uid'], $setarr);
		$imageaids = [];
		if (!empty($_POST['imageaids'])) {
			$imageaids = explode(',', $_POST['imageaids']);
			$imageaids = array_map('intval', $imageaids);
			$imageaids = array_filter($imageaids);

			if (!empty($imageaids)) {
				// 仅允许绑定当前用户自己上传的、尚未关联记录的临时图片
				$validaids = [];
				foreach ($imageaids as $aid) {
					$attach = table_home_doing_attachment::t()->fetch_attachment('aid:'.$aid, $aid);
					if ($attach && $attach['uid'] == $_G['uid'] && !$attach['doid']) {
						$validaids[] = $aid;
					}
				}
				$imageaids = $validaids;
				if (!empty($imageaids)) {
					table_home_doing_attachment::t()->update_by_aid($imageaids, ['doid' => $newdoid]);
				}
			}
		}
		if (!empty($_FILES)) {
			$limits = doing_upload_limits();

			// 规范化本次提交的图片文件列表（$_FILES['photos'] 为多文件结构）
			$photofiles = [];
			if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
				foreach ($_FILES['photos']['name'] as $k => $v) {
					if ($_FILES['photos']['error'][$k] === UPLOAD_ERR_NO_FILE) {
						continue;
					}
					$photofiles[$k] = [
						'name' => $v,
						'type' => $_FILES['photos']['type'][$k],
						'tmp_name' => $_FILES['photos']['tmp_name'][$k],
						'error' => $_FILES['photos']['error'][$k],
						'size' => $_FILES['photos']['size'][$k],
					];
				}
			}

			// 已绑定临时图片数 + 本次上传图片数 不得超过数量上限
			if (count($photofiles) + count($imageaids) > $limits['imgmaxnum']) {
				showmessage('doing_upload_image_too_many', '', ['num' => $limits['imgmaxnum']]);
			}

			// 单张图片类型与尺寸校验
			foreach ($photofiles as $p) {
				$errkey = doing_check_image_file($p, $limits);
				if ($errkey) {
					showmessage($errkey, '', [
						'num' => $limits['imgmaxnum'],
						'size' => intval($limits['imgmaxsize'] / 1024),
					]);
				}
			}

			// 视频校验：仅允许 1 个，且需在后台开启
			$hasvideo = false;
			if (!empty($_FILES['video']) && is_array($_FILES['video']) && !is_array($_FILES['video']['name'])
				&& $_FILES['video']['name'] !== '' && $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE) {
				if (empty($limits['videoallow'])) {
					showmessage('doing_upload_video_disabled');
				}
				$vext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
				if (!in_array($vext, $limits['videoexts'])) {
					showmessage('doing_upload_video_ext_invalid', '', ['ext' => implode(',', $limits['videoexts'])]);
				}
				if ($_FILES['video']['size'] > $limits['videomaxsize']) {
					showmessage('doing_upload_video_too_large', '', ['size' => intval($limits['videomaxsize'] / 1048576)]);
				}
				$hasvideo = true;
			}

			// 仅将通过校验的文件交给上传类处理
			$files = [];
			if (!empty($photofiles)) {
				foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $field) {
					$files['photos'][$field] = array_column($photofiles, $field);
				}
			}
			if ($hasvideo) {
				$files['video'] = $_FILES['video'];
			}
			$_FILES = $files;

			$upload = new upload('doing');
			$f = $upload->upload();

			// 检查上传结果
			$photos = [];
			if (!empty($f['photos'])) {
				// 处理多图上传情况
				if (isset($f['photos'][0])) {
					// 多图数组
					$photos = $f['photos'];
				} else {
					// 单图情况
					$photos[] = $f['photos'];
				}
			}

			// 保存图片信息到数据库
			if (!empty($photos)) {
				foreach ($photos as $key => $value) {
					if (!$value['attachment']) continue;

					// 保存图片信息
					table_home_doing_attachment::t()->insert_attachment([
						'doid' => $newdoid,
						'uid' => $_G['uid'],
						'dateline' => TIMESTAMP,
						'filename' => $value['name'],
						'filesize' => $value['size'],
						'attachment' => $value['attachment'],
						'remote' => $value['remote'],
						'isimage' => $value['isimage'],
						'width' => $value['imageinfo'][0],
						'height' => $value['imageinfo'][1],
						'displayorder' => $key
					], true);
				}
			}

			// 保存视频附件信息到数据库（isimage=0）
			if ($hasvideo && !empty($f['video']) && is_array($f['video']) && !empty($f['video']['attachment']) && empty($f['video']['error'])) {
				$value = $f['video'];
				table_home_doing_attachment::t()->insert_attachment([
					'doid' => $newdoid,
					'uid' => $_G['uid'],
					'dateline' => TIMESTAMP,
					'filename' => $value['name'],
					'filesize' => $value['size'],
					'attachment' => $value['attachment'],
					'remote' => $value['remote'] ?? 0,
					'isimage' => 0,
					'width' => 0,
					'height' => 0,
					'displayorder' => 0
				], true);

				// 保存客户端截取的视频封面（DPlayer 默认读取「附件名.thumb.jpg」作为封面）
				if (!empty($_POST['videoposter']) && preg_match('/^data:image\/(?:jpeg|jpg|png);base64,([A-Za-z0-9+\/=]+)$/', trim($_POST['videoposter']), $pmatch)) {
					$posterdata = base64_decode($pmatch[1], true);
					if ($posterdata && strlen($posterdata) < 5 * 1024 * 1024) {
						$thumbname = getimgthumbname($value['attachment']);
						$thumbpath = $_G['setting']['attachdir'].'/doing/'.$thumbname;
						if (!is_dir(dirname($thumbpath))) {
							dmkdir(dirname($thumbpath));
						}
						if (@file_put_contents($thumbpath, $posterdata)) {
							// 远程附件时同步封面到 FTP
							if (!empty($value['remote']) && $_G['setting']['ftp']['on']) {
								ftpcmd('upload', 'doing/'.$thumbname);
							}
						}
					}
				}
			}
		}
		if($_GET['iscomment'] && $message && $commentcable[$type] && $id) {

			$message = censor($message);

			$currenttype = $commentcable[$type];
			$currentid = $id;

			if($currenttype == 'article') {
				$article = table_portal_article_title::t()->fetch($currentid);
				include_once libfile('function/portal');
				loadcache('portalcategory');
				$cat = $_G['cache']['portalcategory'][$article['catid']];
				$article['allowcomment'] = !empty($cat['allowcomment']) && !empty($article['allowcomment']) ? 1 : 0;
				if(!$article['allowcomment']) {
					showmessage('no_privilege_commentadd', '', [], ['return' => true]);
				}
				if($article['idtype'] == 'blogid') {
					$currentid = $article['id'];
					$currenttype = 'blogid';
				} elseif($article['idtype'] == 'tid') {
					$currentid = $article['id'];
					$currenttype = 'thread';
				}
			}

			if($currenttype == 'thread') {
				if($commentcable[$type] == 'article') {
					$_POST['portal_referer'] = $article_url ? $article_url : 'portal.php?mod=view&aid='.$id;
				}

				$_G['setting']['seccodestatus'] = 0;
				$_G['setting']['secqaa']['status'] = 0;

				$_POST['replysubmit'] = true;
				$_GET['tid'] = $currentid;
				$_GET['action'] = 'reply';
				$_GET['message'] = $message;
				include_once libfile('function/forum');
				require_once libfile('function/post');
				loadforum();

				$inspacecpshare = 1;
				include_once appfile('module/post', 'forum');

				if($_POST['portal_referer']) {
					$redirecturl = $_POST['portal_referer'];
				} else {
					if($modnewreplies) {
						$redirecturl = 'forum.php?mod=viewthread&tid='.$currentid;
					} else {
						$redirecturl = 'forum.php?mod=viewthread&tid='.$currentid.'&pid='.$modpost->pid.'&page='.$modpost->param('page').'&extra='.$extra.'#pid'.$modpost->pid;
					}
				}
				$showmessagecontent = ($modnewreplies && $commentcable[$type] != 'article') ? 'do_success_thread_share_mod' : '';

			} elseif($currenttype == 'article') {

				if(!checkperm('allowcommentarticle')) {
					showmessage('group_nopermission', NULL, ['grouptitle' => $_G['group']['grouptitle']], ['login' => 1]);
				}

				include_once libfile('function/spacecp');
				include_once libfile('function/portalcp');

				cknewuser();

				$waittime = interval_check('post');
				if($waittime > 0) {
					showmessage('operating_too_fast', '', ['waittime' => $waittime], ['return' => true]);
				}

				$aid = intval($currentid);

				$retmessage = addportalarticlecomment($aid, $message);
				if($retmessage != 'do_success') {
					showmessage($retmessage);
				}

			} elseif($currenttype == 'picid' || $currenttype == 'blogid') {

				if(!checkperm('allowcomment')) {
					showmessage('no_privilege_comment', '', [], ['return' => true]);
				}
				cknewuser();
				$waittime = interval_check('post');
				if($waittime > 0) {
					showmessage('operating_too_fast', '', ['waittime' => $waittime], ['return' => true]);
				}
				$message = getstr($message, 0, 0, 0, 2);
				if(strlen($message) < 2) {
					showmessage('content_is_too_short', '', [], ['return' => true]);
				}
				include_once libfile('class/bbcode');
				$bbcode = &bbcode::instance();

				require_once libfile('function/comment');
				$cidarr = add_comment($message, $currentid, $currenttype, 0);
				if($cidarr['cid']) {
					$magvalues['cid'] = $cidarr['cid'];
					$magvalues['id'] = $currentid;
				}
			} elseif($currenttype == 'doid') {
				$commentdo = table_home_doing::t()->fetch($currentid);
				$message = getstr($message, 200, 0, 0, 1);
				$message = preg_replace('/\<br.*?\>/i', ' ', $message);

				$setarr = [
					'doid' => $currentid,
					'upid' => 0,
					'uid' => $_G['uid'],
					'username' => $_G['username'],
					'dateline' => $_G['timestamp'],
					'message' => $message,
					'ip' => $_G['clientip'],
					'port' => $_G['remoteport'],
					'grade' => 1
				];

				$newid = table_home_docomment::t()->insert($setarr, true);

				table_home_doing::t()->update_replynum_by_doid(1, $currentid);

				if($commentdo['uid'] != $_G['uid']) {
					notification_add($commentdo['uid'], 'comment', 'doing_reply', [
						'url' => "home.php?mod=space&uid={$commentdo['uid']}&do=doing&view=me&doid={$commentdo['doid']}&highlight=$newid",
						'from_id' => $commentdo['doid'],
						'from_idtype' => 'doid'
					]);
					updatecreditbyaction('comment', 0, [], 'doing'.$commentdo['doid']);
				}

				include_once libfile('function/stat');
				updatestat('docomment');
			}
			$magvalues['type'] = $commentcable[$type];
		}
		
		if(helper_access::check_module('feed') && ckprivacy('doing', 'feed') && $doing_status == '0') {
			$feedarr = [
				'icon' => 'doing',
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => $_G['timestamp'],
				'title_template' => lang('feed', 'feed_doing_title'),
				'title_data' => serialize(['message' => $message]),
				'body_template' => $arr['body_template'],
				'body_data' => $arr['body_data'],
				'id' => $newdoid,
				'idtype' => 'doid'
			];
			table_home_feed::t()->insert($feedarr);
		}
		if($doing_status == '1') {
			updatemoderate('doid', $newdoid);
			manage_addnotify('verifydoing');
		}
		require_once libfile('function/stat');
		updatestat('doing');
		table_common_member_status::t()->update($_G['uid'], ['lastpost' => TIMESTAMP], 'UNBUFFERED');
		if($type) {
			switch($type) {
				case 'space':
					table_common_member_status::t()->increase($id, ['sharetimes' => 1]);
					break;
				case 'blog':
					table_home_blog::t()->increase($id, null, ['sharetimes' => 1]);
					break;
				case 'album':
					table_home_album::t()->update_num_by_albumid($id, 1, 'sharetimes');
					break;
				case 'pic':
					table_home_pic::t()->update_sharetimes($id);
					break;
				case 'thread':
					table_forum_thread::t()->increase($id, ['sharetimes' => 1]);
					require_once libfile('function/forum');
					update_threadpartake($id);
					break;
				case 'article':
					table_portal_article_count::t()->increase($id, ['sharetimes' => 1]);
					break;
				case 'doing':
					table_home_doing::t()->increase($id, ['sharetimes' => 1]);
					break;
			}
			updatestat('share');
			if($note_uid && $note_uid != $_G['uid']) {
				notification_add($note_uid, 'sharenotice', $note_message, $note_values);
			}
		}
		if(!empty($_GET['fromcard'])) {
			showmessage($message.lang('spacecp', 'card_update_doing'));
		} else {
			showmessage('do_success', dreferer(), ['doid' => $newdoid], $_GET['spacenote'] ? ['showmsg' => false] : ['header' => true]);
		}

	} elseif(submitcheck('commentsubmit')) {

		if(!checkperm('allowdoing')) {
			showmessage('no_privilege_doing_comment');
		}
		cknewuser();

		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast', '', ['waittime' => $waittime]);
		}

		$message = getstr($_POST['message'], 200, 0, 0, 1);
		$message = preg_replace('/\<br.*?\>/i', ' ', $message);
		if(strlen($message) < 1) {
			showmessage('should_write_that');
		}
		$message = censor($message);


		$updo = [];
		if($docid) {
			$updo = table_home_docomment::t()->fetch($docid);
		}
		if(empty($updo) && $doid) {
			$updo = table_home_doing::t()->fetch($doid);
		}
		if(empty($updo)) {
			showmessage('docomment_error');
		} else {
			if(isblacklist($updo['uid'])) {
				showmessage('is_blacklist');
			}
		}

		$updo['id'] = intval($updo['id']);
		$updo['grade'] = intval($updo['grade']);

		$replyUid = $updo['uid'];
		$replyUsername = $updo['username'];
		
		$setarr = [
			'doid' => $updo['doid'],
			'upid' => $updo['id'],
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'message' => $message,
			'ip' => $_G['clientip'],
			'port' => $_G['remoteport'],
			'grade' => $updo['upid'] == 0 ? 2 : 2
		];

		$newid = table_home_docomment::t()->insert($setarr, true);

		table_home_doing::t()->update_replynum_by_doid(1, $updo['doid']);

		$notifyUid = $updo['uid'];
		$notifyMessage = getstr($updo['message'], 120, 0, 0, 0);

		if($notifyUid != $_G['uid']) {
			notification_add($notifyUid, 'comment', 'doing_reply', [
				'url' => "home.php?mod=space&uid={$notifyUid}&do=doing&view=me&doid={$updo['doid']}&highlight=$newid",
				'summery' => $notifyMessage,
				'from_id' => $updo['doid'],
				'from_idtype' => 'doid',
			]);
			updatecreditbyaction('comment', 0, [], 'doing'.$updo['doid']);
		}

		include_once libfile('function/stat');
		updatestat('docomment');
		table_common_member_status::t()->update($_G['uid'], ['lastpost' => TIMESTAMP], 'UNBUFFERED');
		showmessage('do_success', dreferer(), ['doid' => $updo['doid']]);
	}
}
require_once libfile('function/share');
$arr = mkshare($arr);
$arr['dateline'] = $_G['timestamp'];
$share_count = table_home_doing::t()->count_by_uid_itemid_type(null, $id ? $id : '', $type ? $type : '');
include template('home/spacecp_doing');