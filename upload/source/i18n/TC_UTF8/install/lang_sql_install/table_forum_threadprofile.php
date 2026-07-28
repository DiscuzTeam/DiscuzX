<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

$data = [[
    'id' => '1',
    'name' => '預設方案',
    'template' => 
    [
      'left' => '{numbercard}
{groupicon}<p>{*}</p>{/groupicon}
{authortitle}<p><em>{*}</em></p>{/authortitle}
{customstatus}<p class="xg1">{*}</p>{/customstatus}
{star}<p>{*}</p>{/star}
{upgradeprogress}<p>{*}</p>{/upgradeprogress}
<dl class="pil cl">
	<dt>{baseinfo=credits,1}</dt><dd>{baseinfo=credits,0}</dd>
</dl>
{medal}<p class="md_ctrl">{*}</p>{/medal}
<dl class="pil cl">{baseinfo=field_qq,0}</dl>',
      'top' => '<dl class="cl">
<dt>{baseinfo=credits,1}</dt><dd>{baseinfo=credits,0}</dd>
</dl>',
    ],
    'global' => '1',
  ],
];
