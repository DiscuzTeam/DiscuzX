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
        'custom_name' => 'Custom Ad',
        'custom_desc' => 'By adding ad code in templates and HTML files, you can add ads to any page of the site. Suitable for webmasters who know basic HTML knowledge.<br /><br />
                <a href="javascript:;" onclick="prompt(\'Please copy (CTRL+C) the following content and add it to the template to add this ad position\', \'<!--{ad/custom_'.$_GET['customid'].'}-->\')" />Internal Call</a>&nbsp;
                <a href="javascript:;" onclick="prompt(\'Please copy (CTRL+C) the following content and add it to the HTML file to add this ad position\', \'&lt;script type=\\\'text/javascript\\\' src=\\\''.$_G['siteurl'].'api.php?mod=ad&adid=custom_'.$_GET['customid'].'\\\'&gt;&lt;/script&gt;\')" />External Call</a>',
        'custom_id_notfound' => 'Custom ad not found',
        'custom_codelink' => 'Internal Call',
        'custom_text' => 'Custom Ad',
        ];

