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
    'explanation' => '使內容橫向滾動，這個效果類似 HTML 的 marquee 標籤，注意：這個效果只在 Internet Explorer 瀏覽器下有效。',
    'params' => '1',
    'prompt' => '請輸入滾動顯示的文字:',
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
    'explanation' => '顯示 QQ 在線狀態，點這個圖標可以和他（她）聊天',
    'params' => '1',
    'prompt' => '請輸入 QQ 號碼:<a href="" class="xi2" onclick="this.href=\'https://wp.qq.com/set.html?from=discuz&uin=\'+$(\'e_cst1_qq_param_1\').value" target="_blank" style="float:right;">設定QQ在線狀態&nbsp;&nbsp;</a>',
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
    'explanation' => '上標',
    'params' => '1',
    'prompt' => '請輸入上標文字：',
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
    'explanation' => '下標',
    'params' => '1',
    'prompt' => '請輸入下標文字：',
    'nest' => '1',
    'displayorder' => '23',
    'perm' => '1	2	3	12	13	14	15	16	17	18	19',
  ],
];
