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
	'thread_name' => 'Forum/Groups In-thread Ad',
	'thread_desc' => 'Display Mode: In-thread ads are displayed above, below or to the right of the post content. Ads above and below the post content usually use text form, and ads to the right of the post content usually use image form. When there are multiple in-thread ads on the current page, the system will randomly select the same number of entries as the number of posts per page for display. You can modify the number of ads displayed per post in other settings in the global settings.<br />Value Analysis: Because threads are the core component of forums, in-thread ads embedded within the post content can be naturally accepted when users browse the post content. Combined with the feature of random display, they are suitable for effective promotion of specific content, and can also be used for the forum\'s own promotion and announcements. It is recommended to set multiple in-thread ads to achieve differentiation of ad content, thereby attracting more visitors\' attention.',
	'thread_fids' => 'Target Forums',
	'thread_fids_comment' => 'Set the forum boards for ad placement, effective when the ad scope includes "Forum"',
	'thread_groups' => 'Target Group Categories',
	'thread_groups_comment' => 'Set the group categories for ad placement, effective when the ad scope includes "Groups"',
	'thread_position' => 'Placement Position',
	'thread_position_comment' => 'Ads above and below the post content are suitable for text form, while ads on the right side of the post are suitable for image or Flash form. Multiple text ads can also be displayed at the same time',
	'thread_position_bottom' => 'Below Post',
	'thread_position_top' => 'Above Post',
	'thread_position_right' => 'Right Side of Post',
	'thread_pnumber' => 'Ad Display Floors',
	'thread_pnumber_comment' => 'Options #1 #2 #3 ... represent post floors. You can hold CTRL to select multiple',
	'thread_pnumber_all' => 'All',
	];

