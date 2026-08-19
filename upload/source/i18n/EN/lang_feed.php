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

	'feed_blog_password' => '{actor} published a new password-protected blog {subject}',
	'feed_blog_title' => '{actor} published a new blog',
	'feed_blog_body' => '<b>{subject}</b><br />{summary}',
	'feed_album_title' => '{actor} updated the album',
	'feed_album_body' => '<b>{album}</b><br />{picnum} photos total',
	'feed_pic_title' => '{actor} uploaded new pictures',
	'feed_pic_body' => '{title}',


	'feed_poll' => '{actor} created a new poll',

	'feed_comment_space' => '{actor} left a message on {touser}\'s wall',
	'feed_comment_image' => '{actor} commented on {touser}\'s picture',
	'feed_comment_blog' => '{actor} commented on {touser}\'s blog {blog}',
	'feed_comment_poll' => '{actor} commented on {touser}\'s poll {poll}',
	'feed_comment_event' => '{actor} left a message on the activity {event} organized by {touser}',
	'feed_comment_share' => '{actor} commented on the share {share} by {touser}',

	'feed_showcredit' => '{actor} gifted {fusername} {credit} bid credits, helping a friend improve their ranking on the <a href="misc.php?mod=ranklist&type=member" target="_blank">Bid Ranking</a>',
	'feed_showcredit_self' => '{actor} gained {credit} bid credits, improving their ranking on the <a href="misc.php?mod=ranklist&type=member" target="_blank">Bid Ranking</a>',
	'feed_doing_title' => '{actor}：{message}',
	'feed_friend_title' => '{actor} and {touser} became friends',


	'feed_click_blog' => '{actor} gave a "{click}" to {touser}\'s blog {subject}',
	'feed_click_thread' => '{actor} gave a "{click}" to {touser}\'s thread {subject}',
	'feed_click_pic' => '{actor} gave a "{click}" to {touser}\'s picture',
	'feed_click_article' => '{actor} gave a "{click}" to {touser}\'s article {subject}',


	'feed_task' => '{actor} completed the reward task {task}',
	'feed_task_credit' => '{actor} completed the reward task {task} and received {credit} reward credits',

	'feed_profile_update_base' => '{actor} updated their basic profile',
	'feed_profile_update_contact' => '{actor} updated their contact info',
	'feed_profile_update_edu' => '{actor} updated their education info',
	'feed_profile_update_work' => '{actor} updated their work info',
	'feed_profile_update_info' => '{actor} updated their personal info',
	'feed_profile_update_bbs' => '{actor} updated their forum info',
	'feed_profile_update_verify' => '{actor} updated their verification info',

	'feed_add_attachsize' => '{actor} exchanged {credit} credits for {size} attachment space, can upload more pictures now (<a href="home.php?mod=spacecp&ac=credit&op=addsize">I want to exchange too</a>)',

	'feed_invite' => '{actor} sent an invitation and became friends with {username}',

	'magicuse_thunder_announce_title' => '<strong>{username} made a "Thunder Announcement"</strong>',
	'magicuse_thunder_announce_body' => 'Hello everyone, I\'m online now<br /><a href="home.php?mod=space&uid={uid}" target="_blank">Welcome to visit my space</a>',


	'feed_thread_title' => '{actor} published a new thread',
	'feed_thread_message' => '<b>{subject}</b><br />{message}',

	'feed_reply_title' => '{actor} replied to {author}\'s thread {subject}',
	'feed_reply_title_anonymous' => '{actor} replied to the thread {subject}',
	'feed_reply_message' => '',

	'feed_thread_poll_title' => '{actor} created a new poll',
	'feed_thread_poll_message' => '<b>{subject}</b><br />{message}',

	'feed_thread_votepoll_title' => '{actor} voted on the poll about {subject}',
	'feed_thread_votepoll_message' => '',

	'feed_thread_goods_title' => '{actor} listed a new item for sale',
	'feed_thread_goods_message_1' => '<b>{itemname}</b><br />Price: {itemprice} Yuan plus {itemcredit}{creditunit}',
	'feed_thread_goods_message_2' => '<b>{itemname}</b><br />Price: {itemprice} Yuan',
	'feed_thread_goods_message_3' => '<b>{itemname}</b><br />Price: {itemcredit}{creditunit}',

	'feed_thread_reward_title' => '{actor} posted a new reward',
	'feed_thread_reward_message' => '<b>{subject}</b><br />Reward: {rewardprice}{extcredits}',

	'feed_reply_reward_title' => '{actor} replied to the reward about {subject}',
	'feed_reply_reward_message' => '',

	'feed_thread_activity_title' => '{actor} created a new activity',
	'feed_thread_activity_message' => '<b>{subject}</b><br />Start Time: {starttimefrom}<br />Location: {activityplace}<br />{message}',

	'feed_reply_activity_title' => '{actor} registered for the activity {subject}',
	'feed_reply_activity_message' => '',

	'feed_thread_debate_title' => '{actor} started a new debate',
	'feed_thread_debate_message' => '<b>{subject}</b><br />Pro: {affirmpoint}<br />Con: {negapoint}<br />{message}',

	'feed_thread_debatevote_title_1' => '{actor} participated in the debate on {subject} as the pro side',
	'feed_thread_debatevote_title_2' => '{actor} participated in the debate on {subject} as the con side',
	'feed_thread_debatevote_title_3' => '{actor} participated in the debate on {subject} as neutral',
	'feed_thread_debatevote_message_1' => '',
	'feed_thread_debatevote_message_2' => '',
	'feed_thread_debatevote_message_3' => '',

	];

