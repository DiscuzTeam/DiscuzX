<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

class menu_default {

	public static function getMenu() {

		$logo = menu::defaultLogo;

		$navbar = menu::defaultNavbar;

		$menu = [];

		/**
		 * array(
		 *      'menu_setting_optimize',            // 菜单标题文本
		 *      'setting_cachethread',              // 菜单 Key，系统: [action]_[operation]_[do]，插件: plugin_[identifier]_[pmod]，URL: 跳转链接
		 *      0,                                  // 0: 无，1: 分割区域开始，2: 分割区域结束，_blank: 新窗口打开
		 *      '',                                 // $_G['setting']['xxx']、menu_loader::xxx、plugin::xxx 返回为真时显示菜单
		 *      '',                                 // 将 menu_loader::xxx、plugin::xxx 返回的菜单列表替换此条菜单
		 *      array('setting_serveropti')         // 此菜单项目包含的其他菜单 Key，用于权限
		 * )
		 */

		$menu['index'] = [
			['menu_home', 'index'],
			['mitframe_apps', 'mitframe'],
			['menu_home_qrcodelogin', 'qrcodelogin_list'],
			['menu_custommenu_manage', 'misc_custommenu'],
			['', '', 0, '', 'customMenuList'],
		];

		$menu['global'] = [
			['menu_setting_basic', 'setting_basic'],
			['menu_setting_login', 'account'],
			['menu_setting_tpfunctions', 'tpfunctions_index'],
			['menu_setting_access', 'setting_access'],
			['menu_setting_functions', 'setting_functions'],
			['menu_setting_optimize', 'setting_cachethread', 0, '', '', ['setting_serveropti', 'setting_memory', 'setting_memorydata']],
			['menu_setting_seo', 'setting_seo'],
			['menu_setting_domain', 'domain'],
			['menu_setting_follow', 'setting_follow', 0, 'followstatus'],
			['menu_setting_home', 'setting_home'],
			['menu_setting_user', 'setting_permissions'],
			['menu_setting_credits', 'setting_credits', 0, '', '', ['credits_list']],
			['menu_setting_datetime', 'setting_datetime'],
			['menu_setting_attachments', 'setting_attach'],
			['menu_setting_imgwater', 'setting_imgwater', 0, '', '', ['checktools_imagepreview']],
			['menu_posting_attachtypes', 'misc_attachtype'],
			['menu_setting_search', 'setting_search'],
			['menu_setting_district', 'district'],
			['menu_setting_ranklist', 'setting_ranklist', 0, 'rankliststatus'],
			['menu_setting_mobile', 'setting_mobile'],
			['menu_setting_antitheft', 'setting_antitheft'],
		];

		$menu['style'] = [
			['menu_setting_customnav', 'nav'],
			['menu_setting_styles', 'setting_styles', 0, '', '', ['setting_threadprofile']],
			['menu_posting_smilies', 'smilies'],
			['menu_click', 'click'],
			['menu_thread_stamp', 'misc_stamp'],
			['menu_posting_editor', 'setting_editor', 0, '', '', ['misc_bbcode', 'editorblock']],
			['menu_misc_onlinelist', 'misc_onlinelist'],
		];

		$menu['moderate'] = [
			['menu_moderate_posts', 'moderate'],
			['menu_remoderate', 'remoderate'],
			['menu_posting_censors', 'misc_censor'],
			['menu_moderate_modmembers', 'moderate_members'],
			['menu_maint_report', 'report'],
			['menu_group_mod', 'group_mod'],
		];

		$menu['user'] = [
			['menu_members_edit', 'members_search', 0, '', '', ['members_clean', 'members_repeat', 'members_add']],
			['menu_members_profile', 'members_profile', 0, '', '', ['setting_profile']],
			['menu_members_stat', 'members_stat'],
			['menu_usertag', 'usertag'],
			['menu_members_edit_ban_user', 'members_ban'],
			['menu_members_ipban', 'members_ipban'],
			['menu_members_credits', 'members_reward'],
			['menu_follow', 'specialuser_follow'],
			['menu_defaultuser', 'specialuser_defaultuser'],
			['menu_members_verify_profile', 'verify_verify'],
			['menu_members_verify_setting', 'verify'],
			['', '', 0, '', 'verifyList'],
			['menu_usergroups', 'usergroups'],
			['menu_admingroups', 'admingroup'],
		];

		$menu['forum'] = [
			['menu_forums', 'forums'],
			['menu_forums_merge', 'forums_merge'],
			['menu_forums_infotypes', 'threadtypes'],
			['menu_grid', 'grid'],
			['menu_forums_portal', 'forumportal'],
			['menu_maint_threads', 'threads'],
			['menu_maint_prune', 'prune'],
			['menu_maint_attaches', 'attach'],
			['menu_setting_tag', 'tag'],
			['menu_setting_collection', 'collection', 0, 'collectionstatus'],
			['menu_moderate_recyclebin', 'recyclebin'],
			['menu_moderate_recyclebinpost', 'recyclebinpost'],
			['menu_threads_forumstick', 'threads_forumstick'],
			['menu_postcomment', 'postcomment'],
		];

		$menu['group'] = [
			['menu_group_setting', 'group_setting', 0, 'groupstatus'],
			['menu_group_type', 'group_type', 0, 'groupstatus', '', ['group_mergetype', 'group_editgroup']],
			['menu_group_manage', 'group_manage', 0, 'groupstatus'],
			['menu_group_userperm', 'group_userperm', 0, 'groupstatus'],
			['menu_group_level', 'group_level', 0, 'groupstatus'],
			['menu_maint_threads_group', 'threads_group', 0, 'groupstatus'],
			['menu_maint_prune_group', 'prune_group', 0, 'groupstatus'],
			['menu_maint_attaches_group', 'attach_group', 0, 'groupstatus'],
		];

		$menu['home'] = [
			['menu_blogcategory', 'blogcategory', 0, 'blogstatus'],
			['menu_maint_blog', 'blog', 0, 'blogstatus'],
			['menu_maint_blog_recycle_bin', 'blogrecyclebin', 0, 'blogstatus'],
			['menu_maint_doing', 'doing', 0, 'doingstatus'],
			['menu_maint_feed', 'feed', 0, 'feedstatus'],
			['menu_albumcategory', 'albumcategory', 0, 'albumstatus'],
			['menu_maint_album', 'album', 0, 'albumstatus'],
			['menu_maint_pic', 'pic', 0, 'albumstatus'],
			['menu_maint_comment', 'comment', 0, 'wallstatus'],
			['menu_maint_share', 'share', 0, 'sharestatus'],
		];

		$menu['portal'] = [
			['menu_portalcategory', 'portalcategory'],
			['menu_article', 'article'],
			['menu_topic', 'topic'],
			['menu_html', 'makehtml'],
			['menu_diytemplate', 'diytemplate'],
			['menu_block', 'block'],
			['menu_blockstyle', 'blockstyle'],
			['menu_blockxml', 'blockxml'],
			['menu_portalpermission', 'portalpermission'],
		];

		$menu['safe'] = [
			['menu_safe_setting', 'setting_sec'],
			['menu_safe_account', 'setting_account'],
			['menu_smsgw', 'smsgw', 0, 'isfounder'],
			['menu_setting_mail', 'setting_mail', 0, 'isfounder'],
			['menu_safe_seccheck', 'setting_seccheck'],
			['menu_security', 'optimizer_security', 0, 'isfounder'],
			['menu_serversec', 'optimizer_serversec', 0, 'isfounder'],
			['menu_safe_accountguard', 'setting_accountguard'],
		];

		$menu['extended'] = [
			['menu_misc_announce', 'announce'],
			['menu_adv_custom', 'adv'],
			['menu_tasks', 'tasks', 0, 'taskstatus'],
			['menu_magics', 'magics', 0, 'magicstatus', '', ['members_confermagic']],
			['menu_medals', 'medals', 0, 'medalstatus', '', ['members_confermedal']],
			['menu_misc_help', 'faq'],
			['menu_ec', 'ec_base', 0, '', '', ['ec']],
			['menu_misc_link', 'misc_link'],
			['memu_focus_topic', 'misc_focus'],
			['menu_misc_relatedlink', 'misc_relatedlink'],
			['menu_card', 'card'],
			['menu_members_newsletter', 'members_newsletter', 0, '', '', ['members_grouppmlist']],
			['menu_members_sms', 'members_newsletter_sms'],
		];

		$menu['plugin'] = [
			['menu_plugins', 'plugins', 0, 'isfounder'],
			['menu_plugins_add', 'plugins_add', 0, 'isdeveloper'],
			['', '', 0, '', 'pluginList'],
		];

		$menu['template'] = [
			['menu_styles', 'styles', 0, '', '', ['cells']],
			['menu_templates_add', 'templates_add', 0, 'isdeveloper'],
			['menu_lang', 'lang', 0, 'isfounder'],
		];

		$menu['tools'] = [
			['menu_tools_updatecaches', 'tools_updatecache'],
			['menu_tools_updatecounters', 'counter'],
			['menu_logs', 'logs'],
			['menu_misc_cron', 'misc_cron'],
			['menu_tools_iconfont', 'misc_iconfont', 0, 'isfounder'],
			['menu_tools_fileperms', 'tools_fileperms', 0, 'isfounder'],
			['menu_tools_filecheck', 'checktools_filecheck', 0, 'isfounder'],
			['menu_tools_hookcheck', 'checktools_hookcheck', 0, 'isfounder'],
			['menu_tools_replacekey', 'checktools_replacekey', 0, 'isfounder'],
		];

		$menu['founder'] = [
			['menu_founder_perm', 'founder_perm', 0, 'isfounder'],
			['menu_platform', 'founder_platform', 0, 'isfounder'],
			['menu_db', 'db_export', 0, 'isfounder'],
			['menu_membersplit', 'membersplit_check', 0, 'isfounder'],
			['menu_postsplit', 'postsplit_manage', 0, 'isfounder'],
			['menu_threadsplit', 'threadsplit_manage', 0, 'isfounder'],
			['menu_optimizer', 'optimizer_performance', 0, 'isfounder'],
			['menu_founder_restful', 'restful', 0, 'isfounder'],
			['menu_upgrade', 'founder_upgrade', 0, 'isfounder'],
		];

		$menu['cloudaddons'] = [
			['menu_addons', 'cloudaddons&frame=no', '_blank', 'isfounder'],
		];

		$return = [
			'system' => [
				'name' => cplang('home_welcome', ['bbname' => getglobal('setting/bbname')]),
				'title' => cplang('admincp_title'),
				'framecss' => '',
				'pagecss' => '',
				'logo' => $logo,
				'navbar' => $navbar,
				'defaultId' => 'index',
				'menu' => $menu,
			],
		];

		if($GLOBALS['isfounder'] && !UC_STANDALONE) {
			loaducenter();

			$return['ucenter'] = [
				'name' => 'UCenter',
				'title' => 'UCenter',
				'location' => UC_API,
			];
		}
		return $return;
	}

}