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
	'groupattachment_name' => 'Forum Attachment List',
	'groupattachment_desc' => 'Forum attachment list call',
	'groupattachment_fids' => 'Specified Groups',
	'groupattachment_fids_comment' => 'Specified groups, multiple IDs separated by half-width comma ","',
	'groupattachment_tids' => 'Specified Threads',
	'groupattachment_tids_comment' => 'Specified thread IDs, multiple IDs separated by comma',
	'groupattachment_gtids' => 'Group Categories',
	'groupattachment_gtids_comment' => 'Set the category where the groups are located. You can hold CTRL to select multiple. Selecting all or none means no restriction',
	'groupattachment_startrow' => 'Start Data Row',
	'groupattachment_startrow_comment' => 'If you need to set the starting data row, please enter a specific value. 0 means starting from the first row, and so on',
	'groupattachment_items' => 'Display Data Count',
	'groupattachment_items_comment' => 'Set the number of image thread items displayed at one time. Please set it to an integer greater than 0',
	'groupattachment_titlelength' => 'Title Length',
	'groupattachment_titlelength_comment' => 'Set the maximum display length of attachment name/post title',
	'groupattachment_summarylength' => 'Content Length',
	'groupattachment_summarylength_comment' => 'Set the maximum display length of attachment description/post content',
	'groupattachment_maxwidth' => 'Max Image Width (pixels)',
	'groupattachment_maxwidth_comment' => 'Set whether to automatically reduce or enlarge the image size to this set width. 0 means no automatic scaling',
	'groupattachment_maxheight' => 'Max Image Height (pixels)',
	'groupattachment_maxheight_comment' => 'Set whether to automatically reduce or enlarge the image size to this set height. 0 means no automatic scaling',
	'groupattachment_threadmethod' => 'Thread Mode Call',
	'groupattachment_threadmethod_comment' => 'Select "Yes" to call attachments by thread mode, one attachment per thread; Select "No" to call by attachment mode',
	'groupattachment_digest' => 'Digest Thread Filter',
	'groupattachment_digest_comment' => 'Set specific thread range. Note: Selecting all or none means no filtering',
	'groupattachment_digest_0' => 'Normal Thread',
	'groupattachment_digest_1' => 'Digest I',
	'groupattachment_digest_2' => 'Digest II',
	'groupattachment_digest_3' => 'Digest III',
	'groupattachment_special' => 'Special Thread Filter',
	'groupattachment_special_comment' => 'Set specific thread range. Note: Selecting all or none means no filtering',
	'groupattachment_special_1' => 'Poll Thread',
	'groupattachment_special_2' => 'Trade Thread',
	'groupattachment_special_3' => 'Reward Thread',
	'groupattachment_special_4' => 'Activity Thread',
	'groupattachment_special_5' => 'Debate Thread',
	'groupattachment_special_0' => 'Normal Thread',
	'groupattachment_special_reward' => 'Reward Thread Filter',
	'groupattachment_special_reward_comment' => 'Set specific types of reward threads',
	'groupattachment_special_reward_0' => 'All',
	'groupattachment_special_reward_1' => 'Solved',
	'groupattachment_special_reward_2' => 'Unsolved',
	'groupattachment_dateline' => 'Attachment Upload Time',
	'groupattachment_dateline_nolimit' => 'No limit',
	'groupattachment_dateline_hour' => 'Last 1 hour',
	'groupattachment_dateline_day' => 'Last 24 hours',
	'groupattachment_dateline_week' => 'Last 1 week',
	'groupattachment_dateline_month' => 'Last 1 month',
	'groupattachment_gviewperm' => 'Group View Permission',
	'groupattachment_gviewperm_nolimit' => 'No limit',
	'groupattachment_gviewperm_only_member' => 'Members only',
	'groupattachment_gviewperm_all_member' => 'Everyone',
	'groupattachment_highlight' => 'Get Highlight Value',
	];

