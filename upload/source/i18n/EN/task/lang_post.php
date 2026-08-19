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
	'post_name' => 'Forum Post Task',
	'post_desc' => 'Complete the task by posting threads and replies, liven up the forum atmosphere',
	'post_complete_var_act' => 'Action',
	'post_complete_var_act_newthread' => 'Post New Thread',
	'post_complete_var_act_newreply' => 'Post New Reply',
	'post_complete_var_act_newpost' => 'Post New Thread/Reply',
	'post_complate_var_forumid' => 'Forum Restriction',
	'post_complate_var_forumid_comment' => 'Set that members can only complete the task in a specific forum',
	'post_complate_var_threadid' => 'Reply to Specified Thread',
	'post_complate_var_threadid_comment' => 'Set members to only complete the task by replying to this thread, please fill in the thread TID',
	'post_complate_var_author' => 'Reply to Specified Author',
	'post_complate_var_author_comment' => 'Set that members can only complete the task by replying to threads posted by this author. Please fill in the author\'s username',
	'post_complete_var_num' => 'Minimum Action Count',
	'post_complete_var_num_comment' => 'The minimum number of times members need to perform the corresponding action',
	'post_complete_var_time' => 'Time Limit (hours)',
	'post_complete_var_time_comment' => 'Set the time limit for members from applying to completing the task. If members fail to complete the task within this time, they cannot claim the reward and the task is marked as failed. 0 or leave blank for no limit',

	'task_complete_forumid' => 'In forum {value} ',
	'task_complete_act_newthread' => 'Post new threads {num} times',
	'task_complete_act_newpost' => 'Post new threads/replies {num} times',
	'task_complete_act_newreply_thread' => 'Reply to threads in "{value}" {num} times',
	'task_complete_act_newreply_author' => 'Reply to threads by author "{value}" {num} times',
	];

