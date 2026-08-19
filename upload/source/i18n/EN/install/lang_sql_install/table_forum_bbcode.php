<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

$data = [[
    'id' => '1',
    'available' => '0',
    'tag' => 'fly',
    'icon' => 'bb_fly.gif',
    'replacement' => '<marquee width="90%" scrollamount="3">{1}</marquee>',
    'example' => '[fly]This is sample text[/fly]',
    'explanation' => 'Scrolls content horizontally. This effect is similar to the HTML marquee tag. Note: This effect only works in Internet Explorer.',
    'params' => '1',
    'prompt' => 'Please enter the scrolling text:',
    'nest' => '1',
    'displayorder' => '19',
    'perm' => '1	2	3	12	13	14	15	16	17	18	19',
  ],[
    'id' => '2',
    'available' => '2',
    'tag' => 'qq',
    'icon' => 'bb_qq.gif',
    'replacement' => '<a href="https://wpa.qq.com/msgrd?v=3&uin={1}&amp;site=[Discuz!]&amp;from=discuz&amp;menu=yes" target="_blank"><img src="static/image/common/qq_big.gif" border="0"></a>',
    'example' => '[qq]688888[/qq]',
    'explanation' => 'Show QQ online status. Click this icon to chat with him/her',
    'params' => '1',
    'prompt' => 'Please enter QQ number:<a href="" class="xi2" onclick="this.href=\'https://wp.qq.com/set.html?from=discuz&uin=\'+$(\'e_cst1_qq_param_1\').value" target="_blank" style="float:right;">Set QQ online status&nbsp;&nbsp;</a>',
    'nest' => '1',
    'displayorder' => '21',
    'perm' => '1	2	3	10	11	12	13	14	15	16	17	18	19',
  ],[
    'id' => '3',
    'available' => '0',
    'tag' => 'sup',
    'icon' => 'bb_sup.gif',
    'replacement' => '<sup>{1}</sup>',
    'example' => 'X[sup]2[/sup]',
    'explanation' => 'Superscript',
    'params' => '1',
    'prompt' => 'Please enter the superscript text:',
    'nest' => '1',
    'displayorder' => '22',
    'perm' => '1	2	3	12	13	14	15	16	17	18	19',
  ],[
    'id' => '4',
    'available' => '0',
    'tag' => 'sub',
    'icon' => 'bb_sub.gif',
    'replacement' => '<sub>{1}</sub>',
    'example' => 'X[sub]2[/sub]',
    'explanation' => 'Subscript',
    'params' => '1',
    'prompt' => 'Please enter the subscript text:',
    'nest' => '1',
    'displayorder' => '23',
    'perm' => '1	2	3	12	13	14	15	16	17	18	19',
  ],
];
