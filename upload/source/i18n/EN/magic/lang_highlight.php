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
	'highlight_name' => 'Color Change Card',
	'highlight_desc' => 'Can highlight the title of a post or blog and change its color',
	'highlight_expiration' => 'Highlight duration',
	'highlight_expiration_comment' => 'Set how long the title can be highlighted, default 24 hours. No duration when applied to blogs.',
	'highlight_forum' => 'Forums allowed to use this magic item',
	'highlight_info_tid' => 'Highlight the thread title for {expiration} hours',
	'highlight_info_blogid' => 'Can highlight the title of a blog or post and change its color',
	'highlight_color' => 'Color',
	'highlight_info_nonexistence_tid' => 'Please specify the post to highlight',
	'highlight_info_nonexistence_blogid' => 'Please specify the blog to highlight',
	'highlight_succeed_tid' => 'The post you operated on has been highlighted',
	'highlight_succeed_blogid' => 'The blog you operated on has been highlighted',
	'highlight_info_noperm' => 'Sorry, this magic item is not allowed in the forum where this thread is located',
	'highlight_info_notype' => 'Parameter error, no operation type specified.',

	'highlight_notification' => 'Your thread {subject} was used {magicname} by {actor}, <a href="forum.php?mod=viewthread&tid={tid}">go check it out!</a>',
	'highlight_notification_blogid' => 'Your blog {subject} was used {magicname} by {actor}, <a href="home.php?mod=space&do=blog&id={blogid}">go check it out!</a>',
	];
