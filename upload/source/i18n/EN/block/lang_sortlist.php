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
        'sortlist_fids' => 'Target Forums',
        'sortlist_fids_comment' => 'Set the forums allowed to participate in new thread calls. You can hold CTRL to select multiple. Selecting all or none means no restriction',
        'sortlist_startrow' => 'Start Data Row',
        'sortlist_startrow_comment' => 'If you need to set the starting data row, please enter a specific value. 0 means starting from the first row, and so on',
        'sortlist_showitems' => 'Display Data Count',
        'sortlist_showitems_comment' => 'Set the number of thread items displayed at one time. Please set it to an integer greater than 0',
        'sortlist_titlelength' => 'Max Title Bytes',
        'sortlist_titlelength_comment' => 'Set whether to automatically reduce the title to the number of bytes in this setting when the title length exceeds this setting. 0 means no automatic reduction',
        'sortlist_fnamelength' => 'Max Title Bytes Includes Forum Name',
        'sortlist_fnamelength_comment' => 'Set whether the title length includes the length of the forum name it is in',
        'sortlist_summarylength' => 'Thread Brief Content Text Count',
        'sortlist_summarylength_comment' => 'Set the text count of the thread brief content. 0 means using the default value 255',
        'sortlist_tids' => 'Specified Threads',
        'sortlist_tids_comment' => 'Set the thread tids to be displayed. Multiple tids separated by half-width comma ",". Note: Leave blank for no filtering',
        'sortlist_keyword' => 'Title Keyword',
        'sortlist_keyword_comment' => 'Set keywords contained in the title. Note: Leave blank for no filtering; Wildcard * can be used in keywords; To match all of multiple keywords, use space or AND connection. E.g. win32 AND unix; To match part of multiple keywords, use | or OR connection. E.g. win32 OR unix',
        'sortlist_typeids' => 'Thread Types',
        'sortlist_typeids_comment' => 'Set threads of specific types. Note: Selecting all or none means no filtering',
        'sortlist_typeids_all' => 'All Thread Types',
        'sortlist_sortids' => 'Category Info',
        'sortlist_sortids_comment' => 'Set threads with specific category info. Note: Selecting all or none means no filtering',
        'sortlist_sortids_all' => 'All Category Info',
        'sortlist_digest' => 'Digest Thread Filter',
        'sortlist_digest_comment' => 'Set specific thread range. Note: Selecting all or none means no filtering',
        'sortlist_digest_0' => 'Normal Thread',
        'sortlist_digest_1' => 'Digest I',
        'sortlist_digest_2' => 'Digest II',
        'sortlist_digest_3' => 'Digest III',
        'sortlist_stick' => 'Sticky Thread Filter',
        'sortlist_stick_comment' => 'Set specific thread range. Note: Selecting all or none means no filtering',
        'sortlist_stick_0' => 'Normal Thread',
        'sortlist_stick_1' => 'Sticky I',
        'sortlist_stick_2' => 'Sticky II',
        'sortlist_stick_3' => 'Sticky III',
        'sortlist_special' => 'Special Thread Filter',
        'sortlist_special_comment' => 'Set specific thread range. Note: Selecting all or none means no filtering',
        'sortlist_special_1' => 'Poll Thread',
        'sortlist_special_2' => 'Trade Thread',
        'sortlist_special_3' => 'Reward Thread',
        'sortlist_special_4' => 'Activity Thread',
        'sortlist_special_5' => 'Debate Thread',
        'sortlist_special_0' => 'Normal Thread',
        'sortlist_special_reward' => 'Reward Thread Filter',
        'sortlist_special_reward_comment' => 'Set specific types of reward threads',
        'sortlist_special_reward_0' => 'All',
        'sortlist_special_reward_1' => 'Solved',
        'sortlist_special_reward_2' => 'Unsolved',
        'sortlist_recommend' => 'Recommended Thread Filter',
        'sortlist_recommend_comment' => 'Set whether to display only recommended threads',
        'sortlist_orderby' => 'Thread Sorting Method',
        'sortlist_orderby_comment' => 'Set by which field or method to sort threads',
        'sortlist_orderby_lastpost' => 'Sort by last reply time in descending order',
        'sortlist_orderby_dateline' => 'Sort by publication time in descending order',
        'sortlist_orderby_replies' => 'Sort by number of replies in descending order',
        'sortlist_orderby_views' => 'Sort by number of views in descending order',
        'sortlist_orderby_heats' => 'Sort by popularity in descending order',
        'sortlist_orderby_recommends' => 'Sort by thread rating in descending order',
        'sortlist_lastpost' => 'Thread Publication Time',
        'sortlist_lastpost_nolimit' => 'No Limit',
        'sortlist_lastpost_hour' => 'Within 1 Hour',
        'sortlist_lastpost_day' => 'Within 1 Day',
        'sortlist_lastpost_week' => 'Within 1 Week',
        'sortlist_lastpost_month' => 'Within 1 Month',
        'sortlist_orderby_hours_comment' => 'Time value for sorting by views within specified time in descending order',
        ];

