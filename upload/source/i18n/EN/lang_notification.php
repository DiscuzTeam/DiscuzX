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

	'type_wall' => 'Message',
	'type_piccomment' => 'Picture Comment',
	'type_blogcomment' => 'Blog Comment',
	'type_clickblog' => 'Blog Reaction',
	'type_clickarticle' => 'Article Rating',
	'type_clickpic' => 'Picture Reaction',
	'type_sharecomment' => 'Share Comment',
	'type_doing' => 'Records',
	'type_friend' => 'Friend',
	'type_credit' => 'Credit',
	'type_bbs' => 'Forum',
	'type_system' => 'System',
	'type_thread' => 'Thread',
	'type_task' => 'Task',
	'type_group' => 'Groups',

	'mail_to_user' => 'You have new notifications',
	'showcredit' => '{actor} gifted you {credit} bidding credits to help improve your ranking in the <a href="misc.php?mod=ranklist&type=member" target="_blank">Bidding Ranklist</a>',
	'share_space' => '{actor} shared your space',
	'share_blog' => '{actor} shared your blog <a href="{url}" target="_blank">{subject}</a>',
	'share_album' => '{actor} shared your album <a href="{url}" target="_blank">{albumname}</a>',
	'share_pic' => '{actor} shared the <a href="{url}" target="_blank">picture</a> from your album {albumname}',
	'share_thread' => '{actor} shared your thread <a href="{url}" target="_blank">{subject}</a>',
	'share_article' => '{actor} shared your article <a href="{url}" target="_blank">{subject}</a>',
	'magic_present_note' => '{actor} sent you a magic item <a href="{url}" target="_blank">{name}</a>',
	'friend_add' => '{actor} became friends with you',
	'friend_request' => '{actor} sent you a friend request{note}&nbsp;&nbsp;<a onclick="showWindow(this.id, this.href, \'get\', 0);" class="xw1" id="afr_{uid}" href="{url}">Approve</a>',
	'doing_reply' => '{actor} replied to your doing <a href="{url}" target="_blank">{summery}</a> &nbsp; <a href="{url}" target="_blank" class="lit">View</a>',
	'wall_reply' => '{actor} replied to your <a href="{url}" target="_blank">wall message</a>',
	'pic_comment_reply' => '{actor} replied to your <a href="{url}" target="_blank">picture comment</a>',
	'blog_comment_reply' => '{actor} replied to your <a href="{url}" target="_blank">blog comment</a>',
	'share_comment_reply' => '{actor} replied to your <a href="{url}" target="_blank">share comment</a>',
	'wall' => '{actor} left a <a href="{url}" target="_blank">message</a> on your wall',
	'pic_comment' => '{actor} commented on your <a href="{url}" target="_blank">picture</a>',
	'blog_comment' => '{actor} commented on your blog <a href="{url}" target="_blank">{subject}</a>',
	'share_comment' => '{actor} commented on your <a href="{url}" target="_blank">share</a>',
	'click_blog' => '{actor} rated your blog <a href="{url}" target="_blank">{subject}</a>',
	'click_pic' => '{actor} rated your <a href="{url}" target="_blank">picture</a>',
	'click_article' => '{actor} rated your article <a href="{url}" target="_blank">{subject}</a>',
	'show_out' => 'After {actor} visited your homepage, your last credit in the bidding ranklist has been consumed',
	'puse_article' => 'Congratulations, your <a href="{url}" target="_blank">{subject}</a> has been added to the article list, <a href="{newurl}" target="_blank">click here to view</a>',

	'group_member_join' => '{actor} wants to join your group <a href="forum.php?mod=group&fid={fid}" target="_blank">{groupname}</a> and needs moderation. Please go to the group <a href="{url}" target="_blank">Management Center</a> for review',
	'group_member_invite' => '{actor} invited you to join the group <a href="forum.php?mod=group&fid={fid}" target="_blank">{groupname}</a>, <a href="{url}" target="_blank">click here to join now</a>',
	'group_member_check' => 'You have passed the moderation for the group <a href="{url}" target="_blank">{groupname}</a>, please <a href="{url}" target="_blank">click here to visit</a>',
	'group_member_check_failed' => 'You did not pass the moderation for the group <a href="{url}" target="_blank">{groupname}</a>.',
	'group_mod_check' => 'Your created group <a href="{url}" target="_blank">{groupname}</a> has been approved, please <a href="{url}" target="_blank">click here to visit</a>',

	'reason_moderate' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was {modaction} by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_merge' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was {modaction} by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_delete_post' => 'Your post in <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was deleted by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_delete_comment' => 'Your comment on <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> was deleted by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_ban_post' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was {modaction} by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_warn_post' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was {modaction} by {actor}<br />
After accumulating {warninglimit} warnings within {warningexpiration} consecutive days, you will be automatically banned from posting for {warningexpiration} days.<br />
So far, you have received {authorwarnings} warnings, please pay attention!<div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_move' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was moved by {actor} to <a href="forum.php?mod=forumdisplay&fid={tofid}" target="_blank">{toname}</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_copy' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was copied by {actor} as <a href="forum.php?mod=viewthread&tid={threadid}" target="_blank">{subject}</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_remove_reward' => 'Your reward thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was cancelled by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamp_update' => 'A stamp {stamp} was added to your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamp_delete' => 'The stamp was removed from your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamplist_update' => 'An icon {stamp} was added to your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamplist_delete' => 'The icon was removed from your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stickreply' => 'Your reply in the thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was stickied by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stickdeletereply' => 'Your reply in the thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was unstickied by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_quickclear' => 'Your {cleartype} was cleared by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_live_update' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was set as a live thread by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',
	'reason_live_cancle' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was cancelled from live streaming by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'modthreads_delete' => 'The thread {threadsubject} you posted has been rejected by the management team {modusername} and has been deleted!',

	'modthreads_delete_reason' => 'The thread {threadsubject} you posted has been rejected by the management team {modusername} and has been deleted!<div class="quote"><blockquote>{reason}</blockquote></div>',
	'modthreads_dismiss' => 'The thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{threadsubject}</a> you posted has been rejected by the management team {modusername} and saved to draft box. Please modify and resubmit!',

	'modthreads_dismiss_reason' => 'The thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{threadsubject}</a> you posted has been rejected by the management team {modusername} and saved to draft box. Please modify and resubmit!<div class="quote"><blockquote>{reason}</blockquote></div>',
	'modthreads_validate' => 'The thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{threadsubject}</a> you posted has been approved by the management team {modusername}! &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a>',

	'modreplies_delete' => 'The reply you posted has been rejected by the management team {modusername} and has been deleted! <p class="summary">Reply content: <span>{post}</span></p>',

	'modreplies_delete_reason' => 'The reply you posted has been rejected by the management team {modusername} and has been deleted! <p class="summary">Reply content: <span>{post}</span></p><div class="quote"><blockquote>{reason}</blockquote></div>',

	'modreplies_dismiss' => 'The reply you posted has been rejected by the management team {modusername}! &nbsp; <a href="forum.php?mod=post&action=edit&fid={fid}&pid={pid}&tid={tid}" target="_blank" class="lit">Re-edit &rsaquo;</a> <p class="summary">Reply content: <span>{post}</span></p>',

	'modreplies_dismiss_reason' => 'The reply you posted has been rejected by the management team {modusername}! &nbsp; <a href="forum.php?mod=post&action=edit&fid={fid}&pid={pid}&tid={tid}" target="_blank" class="lit">Re-edit &rsaquo;</a> <p class="summary">Reply content: <span>{post}</span></p><div class="quote"><blockquote>{reason}</blockquote></div>',

	'modreplies_validate' => 'The reply you posted has been approved by the management team {modusername}! &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="lit">View &rsaquo;</a> <p class="summary">Reply content: <span>{post}</span></p>',

	'transfer' => 'You received a credit transfer of {credit} from {actor} &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=log&suboperation=creditslog" target="_blank" class="lit">View &rsaquo;</a>
<p class="summary">{actor} said: <span>{transfermessage}</span></p>',

	'addfunds' => 'Your credit recharge request has been completed, the corresponding credits have been deposited into your credit account &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=base" target="_blank" class="lit">View &rsaquo;</a>
<p class="summary">Order ID: <span>{orderid}</span></p><p class="summary">Expense: <span>RMB {price} yuan</span></p><p class="summary">Income: <span>{value}</span></p>',

	'rate_reason' => 'Your post in the thread <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> was rated {ratescore} by {actor} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'recommend_note_post' => 'Congratulations, your post <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> has been adopted by the editor',

	'rate_removereason' => 'The rating {ratescore} on your post in the thread <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> <div class="quote"><blockquote>{reason}</blockquote></div> was revoked by {actor}',

	'trade_seller_send' => '<a href="home.php?mod=space&uid={buyerid}" target="_blank">{buyer}</a> purchased your goods <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a>, the buyer has paid, waiting for you to ship &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">View &rsaquo;</a>',

	'trade_buyer_confirm' => 'The goods <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> you purchased, <a href="home.php?mod=space&uid={sellerid}" target="_blank">{seller}</a> has shipped, waiting for your confirmation &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">View &rsaquo;</a>',

	'trade_fefund_success' => 'The goods <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> has been refunded successfully &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">Rate &rsaquo;</a>',

	'trade_success' => 'The goods <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> has been traded successfully &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">Rate &rsaquo;</a>',

	'trade_order_update_sellerid' => 'The seller <a href="home.php?mod=space&uid={sellerid}" target="_blank">{seller}</a> modified the trade order for goods <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a>, please confirm &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">View &rsaquo;</a>',

	'trade_order_update_buyerid' => 'The buyer <a href="home.php?mod=space&uid={buyerid}" target="_blank">{buyer}</a> modified the trade order for goods <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a>, please confirm &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">View &rsaquo;</a>',

	'eccredit' => '{actor} you traded with has rated you &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">Rate Back &rsaquo;</a>',

	'activity_notice' => '{actor} applied to join the activity you organized <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a>, please review &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a>',

	'activity_apply' => 'The organizer {actor} of the activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> has approved your participation &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_replenish' => 'The organizer {actor} of the activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> notifies you that you need to complete the activity registration information &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_delete' => 'The organizer {actor} of the activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> rejected your participation &nbsp; <a href="forum.php?mod=viewthread&tid={tid}"  target="_blank" class="lit">View &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_cancel' => '{actor} cancelled participation in the activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=viewthread&tid={tid}"  target="_blank" class="lit">View &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_notification' => 'The organizer {actor} of the activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> sent a notification&nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View Activity &rsaquo;</a> <div class="quote"><blockquote>{msg}</blockquote></div>',

	'reward_question' => 'A best answer was set by {actor} for your reward thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a>',

	'reward_bestanswer' => 'Your reply was selected as the best answer by the author {actor} of the reward thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a>',

	'reward_bestanswer_moderator' => 'Your reply in the reward thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> was selected as the best answer &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a>',

	'comment_add' => '{actor} commented on the post you made in the thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="lit">View &rsaquo;</a>',

	'reppost_noticeauthor' => '{actor} replied to your post <a href="forum.php?mod=redirect&goto=findpost&ptid={tid}&pid={pid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="lit">View</a>',

	'task_reward_credit' => 'Congratulations on completing the task: <a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>, you earned {creditbonus} credits &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=base" target="_blank" class="lit">View My Credits &rsaquo;</a></p>',

	'task_reward_magic' => 'Congratulations on completing the task: <a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>, you earned the magic item <a href="home.php?mod=magic&action=mybox" target="_blank">{rewardtext}</a> x {bonus}',

	'task_reward_medal' => 'Congratulations on completing the task: <a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>, you earned the medal <a href="home.php?mod=medal" target="_blank">{rewardtext}</a> valid for {bonus} days',

	'task_reward_medal_forever' => 'Congratulations on completing the task: <a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>, you earned the medal <a href="home.php?mod=medal" target="_blank">{rewardtext}</a> permanently valid',

	'task_reward_invite' => 'Congratulations on completing the task: <a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>, you earned <a href="home.php?mod=spacecp&ac=invite" target="_blank">{rewardtext} invite code(s)</a> valid for {bonus} days',

	'task_reward_group' => 'Congratulations on completing the task: <a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>, you earned user group {rewardtext} valid for {bonus} days &nbsp; <a href="home.php?mod=spacecp&ac=usergroup" target="_blank" class="lit">See what I can do &rsaquo;</a>',

	'user_usergroup' => 'Your user group has been upgraded to {usergroup} &nbsp; <a href="home.php?mod=spacecp&ac=usergroup" target="_blank" class="lit">See what I can do &rsaquo;</a>',

	'grouplevel_update' => 'Congratulations, your group {groupname} has been upgraded to {newlevel}.',

	'thread_invite' => '{actor} invited you to {invitename} <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a>',
	'blog_invite' => '{actor} invited you to view the blog <a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank">{subject}</a> &nbsp; <a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank" class="lit">View &rsaquo;</a>',
	'article_invite' => '{actor} invited you to view the article <a href="{url}" target="_blank">{subject}</a> &nbsp; <a href="{url}" target="_blank" class="lit">View &rsaquo;</a>',
	'invite_friend' => 'Congratulations, you successfully invited {actor} and they became your friend',

	'poke_request' => '<a href="{fromurl}" class="xi2">{fromusername}</a>: <span class="xw0">{pokemsg}&nbsp;</span><a href="home.php?mod=spacecp&ac=poke&op=reply&uid={fromuid}&from=notice" id="a_p_r_{fromuid}" class="xw1" onclick="showWindow(this.id, this.href, \'get\', 0);">Poke Back</a><span class="pipe">|</span><a href="home.php?mod=spacecp&ac=poke&op=ignore&uid={fromuid}&from=notice" id="a_p_i_{fromuid}" onclick="showWindow(\'pokeignore\', this.href, \'get\', 0);">Ignore</a>',

	'profile_verify_error' => '{verify} profile verification was rejected, the following fields need to be re-filled:<br/>{profile}<br/>Rejection reason: {reason}',
	'profile_verify_pass' => 'Congratulations, your {verify} profile verification has been approved',
	'profile_verify_pass_refusal' => 'Sorry, your {verify} profile verification has been rejected',
	'member_ban_speak' => 'You have been banned from posting by {user}, duration: {day} days (0 means permanent ban), ban reason: {reason}',
	'member_ban_visit' => 'You have been banned from accessing by {user}, duration: {day} days (0 means permanent ban), ban reason: {reason}',
	'member_ban_status' => 'Your account has been locked by {user}, lock reason: {reason}',
	'member_change_usergroup' => 'Your user group has been changed to {groupname} by {user}, duration: {day} (0 means permanent), extended group info: {extgroupinfo}. As requested by the operator, you are informed of the changes, operation reason: {reason}',
	'member_change_credits' => 'Your credits have been adjusted by {user}, credit types and adjustment values: {extcredits}. As requested by the operator, you are informed of the changes, operation reason: {reason}',

	'member_follow' => 'There are {count} new feeds from people you follow.<a href="home.php?mod=follow">Click to view</a>',
	'member_follow_add' => '{actor} followed you.<a href="home.php?mod=follow&do=follower">Click to view</a>',

	'member_moderate_invalidate' => 'Your account failed to pass the administrator review, please <a href="home.php?mod=spacecp&ac=profile">resubmit registration information</a>.<br />Administrator message: <b>{remark}</b>',
	'member_moderate_validate' => 'Your account has been approved.<br />Administrator message: <b>{remark}</b>',
	'member_moderate_invalidate_no_remark' => 'Your account failed to pass the administrator review, please <a href="home.php?mod=spacecp&ac=profile">resubmit registration information</a>.',
	'member_moderate_validate_no_remark' => 'Your account has been approved.',
	'manage_verifythread' => 'There are new threads pending moderation.<a href="admin.php?action=moderate&operation=threads&dateline=all">Moderate now</a>',
	'manage_verifypost' => 'There are new replies pending moderation.<a href="admin.php?action=moderate&operation=replies&dateline=all">Moderate now</a>',
	'manage_verifyuser' => 'There are new members pending moderation.<a href="admin.php?action=moderate&operation=members">Moderate now</a>',
	'manage_verifyblog' => 'There are new blogs pending moderation.<a href="admin.php?action=moderate&operation=blogs">Moderate now</a>',
	'manage_verifydoing' => 'There are new doings pending moderation.<a href="admin.php?action=moderate&operation=doings">Moderate now</a>',
	'manage_verifypic' => 'There are new pictures pending moderation.<a href="admin.php?action=moderate&operation=pictures">Moderate now</a>',
	'manage_verifyshare' => 'There are new shares pending moderation.<a href="admin.php?action=moderate&operation=shares">Moderate now</a>',
	'manage_verifycommontes' => 'There are new wall messages/comments pending moderation.<a href="admin.php?action=moderate&operation=comments">Moderate now</a>',
	'manage_verifyrecycle' => 'There are new threads in the recycle bin to process.<a href="admin.php?action=recyclebin">Process now</a>',
	'manage_verifyrecyclepost' => 'There are new replies in the reply recycle bin to process.<a href="admin.php?action=recyclebinpost">Process now</a>',
	'manage_verifyarticle' => 'There are new articles pending moderation.<a href="admin.php?action=moderate&operation=articles">Moderate now</a>',
	'manage_verifymedal' => 'There are new medal applications pending moderation.<a href="admin.php?action=medals&operation=mod">Moderate now</a>',
	'manage_verifyacommont' => 'There are new article comments pending moderation.<a href="admin.php?action=moderate&operation=articlecomments">Moderate now</a>',
	'manage_verifytopiccommont' => 'There are new topic comments pending moderation.<a href="admin.php?action=moderate&operation=topiccomments">Moderate now</a>',
	'manage_verify_field' => 'There are new {verifyname} pending processing.<a href="admin.php?action=verify&operation=verify&do={doid}">Process now</a>',
	'system_notice' => '{subject}<p class="summary">{message}</p>',
	'system_adv_expiration' => 'The following advertisements on your site will expire in {day} days, please handle them promptly:<br />{advs}',
	'report_change_credits' => '{actor} processed your report {creditchange} {msg}',
	'at_message' => '<a href="home.php?mod=space&uid={buyerid}" target="_blank">{buyer}</a> mentioned you in the thread <a href="forum.php?mod=redirect&goto=findpost&ptid={tid}&pid={pid}" target="_blank">{subject}</a><div class="quote"><blockquote>{message}</blockquote></div><a href="forum.php?mod=redirect&goto=findpost&ptid={tid}&pid={pid}" target="_blank">Go check it out</a>.',
	'at_doing' => '<a href="home.php?mod=space&uid={buyerid}" target="_blank">{buyer}</a> mentioned you in a doing<a href="home.php?mod=space&do=doing&doid={doid}" target="_blank">Go check it out</a>.',
	'new_report' => 'There is a new report from {username} waiting to be processed, <a href="admin.php?action=report" target="_blank">click here to enter the management center</a>.',
	'new_post_report' => 'There is a new report from {username} waiting to be processed, <a href="forum.php?mod=modcp&action=report&fid={fid}" target="_blank">click here to enter the management panel</a>.',
	'magics_receive' => 'You received a magic item {magicname} from {actor}
<p class="summary">{actor} said: <span>{msg}</span></p>
<p class="mbn"><a href="home.php?mod=magic" target="_blank">Gift Back</a><span class="pipe">|</span><a href="home.php?mod=magic&action=mybox" target="_blank">View My Magic Box</a></p>',
	'invite_collection' => '{actor} invited you to participate in maintaining the collection <a href="forum.php?mod=collection&action=view&ctid={ctid}">{collectionname}</a>.<br /> <a href="forum.php?mod=collection&action=edit&op=acceptinvite&ctid={ctid}&dateline={dateline}">Accept Invitation</a>',
	'collection_removed' => 'The collection <a href="forum.php?mod=collection&action=view&ctid={ctid}">{collectionname}</a> you participated in maintaining has been closed by {actor}.',
	'exit_collection' => 'You have exited maintaining the collection <a href="forum.php?mod=collection&action=view&ctid={ctid}">{collectionname}</a>.',
	'collection_becommented' => 'Your collection <a href="forum.php?mod=collection&action=view&ctid={ctid}">{collectionname}</a> has received a new comment.',
	'collection_befollowed' => 'A new user has subscribed to your collection <a href="forum.php?mod=collection&action=view&ctid={ctid}">{collectionname}</a>!',
	'collection_becollected' => 'Congratulations, your thread <a href="forum.php?mod=viewthread&tid={tid}">{threadname}</a> has been included in the collection <a href="forum.php?mod=collection&action=view&ctid={ctid}">{collectionname}</a>!',

	'pmreportcontent' => '{pmreportcontent}',

	'thread_hidden' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> has been identified as spam by multiple users and has been hidden &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View &rsaquo;</a>',

	'forum_member_new' => '{actor} applied to join <a href="forum.php?mod=forumdisplay&fid={fid}" target="_blank">{forumname}</a> and needs moderation, please go to the <a href="{url}" target="_blank">management panel</a> for review',
	'forum_member_check' => 'You have passed the moderation for <a href="{url}" target="_blank">{forumname}</a>, please <a href="{url}" target="_blank">click here to visit</a>',
	'forum_member_check_failed' => 'You did not pass the moderation for <a href="{url}" target="_blank">{forumname}</a>.',

	];

