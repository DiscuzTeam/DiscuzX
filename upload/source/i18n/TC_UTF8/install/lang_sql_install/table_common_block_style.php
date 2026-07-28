<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

$data = [[
    'styleid' => '1',
    'blockclass' => 'html_html',
    'name' => '[內置]空模板',
    'template' =>
    [
      'raw' => '',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' =>
      [
      ],
    ],
    'hash' => 'ee3e718a',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '0',
    'fields' =>
    [
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '2',
    'blockclass' => 'forum_forum',
    'name' => '[內置]版塊名稱列表',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'c6c48ef5',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '3',
    'blockclass' => 'forum_forum',
    'name' => '[內置]版塊名稱＋總帖數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{posts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{posts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '91c25611',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'posts',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '4',
    'blockclass' => 'forum_forum',
    'name' => '[內置]版塊名稱+總帖數（有序）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ol>
[loop]
<li><em>{posts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ol>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{posts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '951323a8',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'posts',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '5',
    'blockclass' => 'forum_forum',
    'name' => '[內置]版塊名稱+今日發貼數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{todayposts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{todayposts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'e08c8a30',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'todayposts',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '6',
    'blockclass' => 'forum_forum',
    'name' => '[內置]版塊名稱+今日發貼數（有序）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ol>
[loop]
<li><em>{todayposts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ol>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{todayposts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '12516b2d',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'todayposts',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '7',
    'blockclass' => 'forum_forum',
    'name' => '[內置]版塊名稱（兩列）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl2">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '0e51a193',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '8',
    'blockclass' => 'forum_forum',
    'name' => '[內置]版塊名稱＋介紹',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '2bf344ae',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '9',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '079cd140',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '10',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題+回覆數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{replies}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{replies}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '0cc45858',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'replies',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '11',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題+查看數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{views}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{views}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'c5361e32',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'views',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '12',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題+熱度',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{heats}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{heats}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'dfac2b4f',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'heats',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '13',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題+發帖時間',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{dateline}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{dateline}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '37a3603a',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'dateline',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '14',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題+最後回覆時間',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{lastpost}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{lastpost}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '1ae9c85b',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'lastpost',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '15',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題+作者',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '30def87f',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'authorid',
      1 => 'author',
      2 => 'url',
      3 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '16',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題+作者+摘要',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dt><em class="y xg1 xw0"><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><em class="y xg1 xw0"><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '8ebc8d5f',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'authorid',
      1 => 'author',
      2 => 'url',
      3 => 'title',
      4 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '17',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題+摘要',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '1107d2bd',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '18',
    'blockclass' => 'forum_thread',
    'name' => '[內置]焦點模式',
    'template' =>
    [
      'raw' => '<div class="module cl xld fcs">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'b6337920',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '19',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子標題（第一條帶摘要）',
    'template' =>
    [
      'raw' => '<div class="module cl xl">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
[order=1]
<li>
	<dl class="cl xld">
		<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
		<dd>{summary}</dd>
	</dl> 
	<hr class="da" />
</li>
[/order]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
        1 => '<li>
	<dl class="cl xld">
		<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
		<dd>{summary}</dd>
	</dl> 
	<hr class="da" />
</li>',
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '2e06f8b5',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '24',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '176fcc68',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '25',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題+回覆數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{replies}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{replies}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '8baa57ad',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'replies',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '26',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題+查看數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{views}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{views}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '8f012db4',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'views',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '27',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題+熱度',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{heats}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{heats}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '7f002523',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'heats',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '28',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題+發帖時間',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{dateline}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{dateline}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '23ba8554',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'dateline',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '29',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題+最後回覆時間',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{lastpost}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{lastpost}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'a6fbd13d',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'lastpost',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '30',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題+作者',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '49245e40',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'authorid',
      1 => 'author',
      2 => 'url',
      3 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '31',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題+作者+摘要',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dt><em class="y"><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><em class="y"><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'd9c23f31',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'authorid',
      1 => 'author',
      2 => 'url',
      3 => 'title',
      4 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '32',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題+摘要',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '9e90211d',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '33',
    'blockclass' => 'group_thread',
    'name' => '[內置]焦點模式',
    'template' =>
    [
      'raw' => '<div class="module cl xld fcs">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '9670c626',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '34',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子標題（第一條帶摘要）',
    'template' =>
    [
      'raw' => '<div class="module cl xl">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
[order=1]
<li>
	<dl class="cl xld">
		<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
		<dd>{summary}</dd>
	</dl> 
	<hr class="da" />
</li>
[/order]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
        1 => '<li>
	<dl class="cl xld">
		<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
		<dd>{summary}</dd>
	</dl> 
	<hr class="da" />
</li>',
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '9355f559',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '39',
    'blockclass' => 'group_group',
    'name' => '[內置]圈子名稱',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '9872d550',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '40',
    'blockclass' => 'group_group',
    'name' => '[內置]圈子名稱+成員數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{membernum}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{membernum}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '20a09ec8',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'membernum',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '41',
    'blockclass' => 'group_group',
    'name' => '[內置]圈子名稱+成員數（有序）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ol>
[loop]
<li><em>{membernum}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ol>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{membernum}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'af166b44',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'membernum',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '42',
    'blockclass' => 'group_group',
    'name' => '[內置]圈子名稱+總帖數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{posts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{posts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '43ed1e7c',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'posts',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '43',
    'blockclass' => 'group_group',
    'name' => '[內置]圈子名稱+今日發貼數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{todayposts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{todayposts}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '3c59217b',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'todayposts',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '44',
    'blockclass' => 'group_group',
    'name' => '[內置]圈子圖標+名稱+介紹',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{icon}" width="48" height="48" /></a></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{icon}" width="48" height="48" /></a></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '6f470107',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'icon',
      2 => 'title',
      3 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '45',
    'blockclass' => 'group_group',
    'name' => '[內置]圈子圖標列表',
    'template' =>
    [
      'raw' => '<div class="module cl ml mls">
<ul>
[loop]
<li><a href="{url}"{target}><img src="{icon}" width="48" height="48" /></a><p><a href="{url}" title="{title}"{target}>{title}</a></p></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}"{target}><img src="{icon}" width="48" height="48" /></a><p><a href="{url}" title="{title}"{target}>{title}</a></p></li>',
    ],
    'hash' => 'f3646b2a',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'icon',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '46',
    'blockclass' => 'group_group',
    'name' => '[內置]圈子名稱（兩列）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl2">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '5279d89d',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '47',
    'blockclass' => 'portal_article',
    'name' => '[內置]文章標題',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '527a563d',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '48',
    'blockclass' => 'portal_article',
    'name' => '[內置]文章標題+時間',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{dateline}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{dateline}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '6e4be436',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'dateline',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '49',
    'blockclass' => 'portal_article',
    'name' => '[內置]文章標題+時間（帶欄目）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{dateline}</em><label>[<a href="{caturl}"{target}>{catname}</a>]</label><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{dateline}</em><label>[<a href="{caturl}"{target}>{catname}</a>]</label><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'c3b98a2f',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'dateline',
      1 => 'caturl',
      2 => 'catname',
      3 => 'url',
      4 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '50',
    'blockclass' => 'portal_article',
    'name' => '[內置]文章標題+摘要+縮略圖',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'a5b550ee',
    'getpic' => '1',
    'getsummary' => '1',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '51',
    'blockclass' => 'portal_article',
    'name' => '[內置]文章標題+摘要',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'e57dbe5a',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '52',
    'blockclass' => 'portal_article',
    'name' => '[內置]焦點模式',
    'template' =>
    [
      'raw' => '<div class="module cl xld fcs">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '3b234c9c',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '53',
    'blockclass' => 'portal_article',
    'name' => '[內置]文章圖片幻燈',
    'template' =>
    [
      'raw' => '<div class="module cl slidebox">
<ul class="slideshow">
[loop]
<li style="width: {picwidth}px; height: {picheight}px;"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></a><span class="title">{title}</span></li>
[/loop]
</ul>
</div>
<script type="text/javascript">
runslideshow();
</script>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px; height: {picheight}px;"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></a><span class="title">{title}</span></li>',
    ],
    'hash' => '8ff81e35',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '54',
    'blockclass' => 'portal_article',
    'name' => '[內置]文章圖文幻燈',
    'template' =>
    [
      'raw' => '<div class="module cl xld slideshow">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>
<script type="text/javascript">
runslideshow();
</script>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'd88aded4',
    'getpic' => '1',
    'getsummary' => '1',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '55',
    'blockclass' => 'portal_category',
    'name' => '[內置]欄目名稱',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '6846b818',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '56',
    'blockclass' => 'portal_category',
    'name' => '[內置]欄目名稱（兩列）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl2">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'fa5b40c1',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '57',
    'blockclass' => 'portal_topic',
    'name' => '[內置]專題名稱',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '268501b8',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '58',
    'blockclass' => 'portal_topic',
    'name' => '[內置]專題名稱（兩列）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl2">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'b21a9795',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '59',
    'blockclass' => 'portal_topic',
    'name' => '[內置]專題名稱+介紹+縮略圖',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'e07e6128',
    'getpic' => '1',
    'getsummary' => '1',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '60',
    'blockclass' => 'portal_topic',
    'name' => '[內置]專題名稱+介紹',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '573d0170',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '61',
    'blockclass' => 'portal_topic',
    'name' => '[內置]焦點模式',
    'template' =>
    [
      'raw' => '<div class="module cl xld fcs">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '7cc2ab53',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '62',
    'blockclass' => 'space_doing',
    'name' => '[內置]作者+內容',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="home.php?mod=space&uid={uid}" title="{username}" c="1"{target}>{username}</a>: <a href="{url}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="home.php?mod=space&uid={uid}" title="{username}" c="1"{target}>{username}</a>: <a href="{url}"{target}>{title}</a></li>',
    ],
    'hash' => 'd0ca1426',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'uid',
      1 => 'username',
      2 => 'url',
      3 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '63',
    'blockclass' => 'space_doing',
    'name' => '[內置]頭像+作者+內容',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="home.php?mod=space&uid={uid}" c="1"{target}><img src="{avatar}" width="48" height="48" alt="{username}" /></a></dd>
	<dt><a href="home.php?mod=space&uid={uid}" title="{username}"{target}>{username}</a> <em class="xg1 xw0">{dateline}</em></dt>
	<dd><a href="{url}"{target}>{title}</a></dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="home.php?mod=space&uid={uid}" c="1"{target}><img src="{avatar}" width="48" height="48" alt="{username}" /></a></dd>
	<dt><a href="home.php?mod=space&uid={uid}" title="{username}"{target}>{username}</a> <em class="xg1 xw0">{dateline}</em></dt>
	<dd><a href="{url}"{target}>{title}</a></dd>
</dl>',
    ],
    'hash' => '13f43cab',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'uid',
      1 => 'avatar',
      2 => 'username',
      3 => 'dateline',
      4 => 'url',
      5 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '64',
    'blockclass' => 'space_doing',
    'name' => '[內置]作者+內容（多行）+時間',
    'template' =>
    [
      'raw' => '<div class="module cl xl">
<ul>
[loop]
<li><a href="home.php?mod=space&uid={uid}" title="{username}" c="1"{target}>{username}</a>: <a href="{url}"{target}>{title}</a> <span class="xg1">({dateline})</span></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="home.php?mod=space&uid={uid}" title="{username}" c="1"{target}>{username}</a>: <a href="{url}"{target}>{title}</a> <span class="xg1">({dateline})</span></li>',
    ],
    'hash' => '927ed021',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'uid',
      1 => 'username',
      2 => 'url',
      3 => 'title',
      4 => 'dateline',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '65',
    'blockclass' => 'space_blog',
    'name' => '[內置]日誌標題',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '9349072a',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '66',
    'blockclass' => 'space_blog',
    'name' => '[內置]日誌標題+作者',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em><a href="home.php?mod=space&uid={uid}"{target}>{username}</a></em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em><a href="home.php?mod=space&uid={uid}"{target}>{username}</a></em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'd2a5c82a',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'uid',
      1 => 'username',
      2 => 'url',
      3 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '67',
    'blockclass' => 'space_blog',
    'name' => '[內置]日誌標題+發佈時間',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{dateline}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{dateline}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'c68ceade',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'dateline',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '68',
    'blockclass' => 'space_blog',
    'name' => '[內置]日誌標題+評論數',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><em>{replynum}</em><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{replynum}</em><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '0345faa7',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'replynum',
      1 => 'url',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '69',
    'blockclass' => 'space_blog',
    'name' => '[內置]日誌標題+作者+簡介',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dt><em class="y xg1 xw0"><a href="home.php?mod=space&uid={uid}"{target}>{username}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><em class="y xg1 xw0"><a href="home.php?mod=space&uid={uid}"{target}>{username}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'cd5e700c',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'uid',
      1 => 'username',
      2 => 'url',
      3 => 'title',
      4 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '70',
    'blockclass' => 'space_blog',
    'name' => '[內置]日誌縮略圖+標題+簡介',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><em class="y xg1 xw0"><a href="home.php?uid={uid}"{target}>{username}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><em class="y xg1 xw0"><a href="home.php?uid={uid}"{target}>{username}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '323bc8e0',
    'getpic' => '1',
    'getsummary' => '1',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'uid',
      4 => 'username',
      5 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '71',
    'blockclass' => 'space_blog',
    'name' => '[內置]日誌圖片幻燈',
    'template' =>
    [
      'raw' => '<div class="module cl slidebox">
<ul class="slideshow">
[loop]
<li style="width: {picwidth}px; height: {picheight}px;"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></a><span class="title">{title}</span></li>
[/loop]
</ul>
</div>
<script type="text/javascript">
runslideshow();
</script>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px; height: {picheight}px;"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></a><span class="title">{title}</span></li>',
    ],
    'hash' => 'c23cc347',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '72',
    'blockclass' => 'space_blog',
    'name' => '[內置]焦點模式',
    'template' =>
    [
      'raw' => '<div class="module cl xld fcs">
[loop]
<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '3bb0bf67',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '73',
    'blockclass' => 'space_album',
    'name' => '[內置]相簿列表',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li>
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a> ({picnum})</p>
</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li>
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a> ({picnum})</p>
</li>',
    ],
    'hash' => '73e0a54f',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'picnum',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '74',
    'blockclass' => 'space_album',
    'name' => '[內置]相簿列表+名稱+用戶',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li>
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a> ({picnum})</p>
	<span><a href="home.php?uid={uid}"{target}>{username}</a></span>
</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li>
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a> ({picnum})</p>
	<span><a href="home.php?uid={uid}"{target}>{username}</a></span>
</li>',
    ],
    'hash' => 'cc34db30',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'picnum',
      4 => 'uid',
      5 => 'username',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '75',
    'blockclass' => 'space_pic',
    'name' => '[內置]圖片列表',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li style="width: {picwidth}px;">
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px;">
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>',
    ],
    'hash' => '9e9201a8',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '76',
    'blockclass' => 'space_pic',
    'name' => '[內置]圖片幻燈',
    'template' =>
    [
      'raw' => '<div class="module cl slidebox">
<ul class="slideshow">
[loop]
<li style="width: {picwidth}px; height: {picheight}px;"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></a><span class="title">{title}</span></li>
[/loop]
</ul>
</div>
<script type="text/javascript">
runslideshow();
</script>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px; height: {picheight}px;"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></a><span class="title">{title}</span></li>',
    ],
    'hash' => 'c5d88e6d',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '77',
    'blockclass' => 'member_member',
    'name' => '[內置]會員頭像列表',
    'template' =>
    [
      'raw' => '<div class="module cl ml mls">
<ul>
[loop]
<li>
	<a href="{url}" c="1"{target}><img src="{avatar}" width="48" height="48" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li>
	<a href="{url}" c="1"{target}><img src="{avatar}" width="48" height="48" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>',
    ],
    'hash' => '2ef16e64',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'avatar',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '78',
    'blockclass' => 'member_member',
    'name' => '[內置]用戶名列表',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}" c="1"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}" c="1"{target}>{title}</a></li>',
    ],
    'hash' => 'ed36c3b0',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '79',
    'blockclass' => 'member_member',
    'name' => '[內置]頭像+用戶名+發貼數（有序）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ol>
[loop]
<li><em>{posts}</em><img class="vm" src="{avatar}" width="16" height="16" alt="{title}" /> <a href="{url}" title="{title}" c="1"{target}>{title}</a></li>
[/loop]
</ol>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{posts}</em><img class="vm" src="{avatar}" width="16" height="16" alt="{title}" /> <a href="{url}" title="{title}" c="1"{target}>{title}</a></li>',
    ],
    'hash' => 'b185afb9',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'posts',
      1 => 'avatar',
      2 => 'title',
      3 => 'url',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '80',
    'blockclass' => 'member_member',
    'name' => '[內置]頭像+用戶名+積分數（有序）',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ol>
[loop]
<li><em>{credits}</em><img class="vm" src="{avatar}" width="16" height="16" alt="{title}" /> <a href="{url}" title="{title}" c="1"{target}>{title}</a></li>
[/loop]
</ol>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><em>{credits}</em><img class="vm" src="{avatar}" width="16" height="16" alt="{title}" /> <a href="{url}" title="{title}" c="1"{target}>{title}</a></li>',
    ],
    'hash' => '8431f4e1',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'credits',
      1 => 'avatar',
      2 => 'title',
      3 => 'url',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '81',
    'blockclass' => 'forum_trade',
    'name' => '[內置]商品列表',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li style="padding: 0 12px 10px; width: {picwidth}px;">
<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" style="padding: 1px; border: 1px solid #CCC; background: #FFF;" /></a>
<p class="xs2"><a href="{url}"{target} class="xi1">{price}</a></p>
<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="padding: 0 12px 10px; width: {picwidth}px;">
<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" style="padding: 1px; border: 1px solid #CCC; background: #FFF;" /></a>
<p class="xs2"><a href="{url}"{target} class="xi1">{price}</a></p>
<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>',
    ],
    'hash' => '4fd3ffc9',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'price',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '82',
    'blockclass' => 'forum_activity',
    'name' => '[內置]活動列表',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{time} {place}</dd>
	<dd> 已有 {applynumber} 人報名</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{time} {place}</dd>
	<dd> 已有 {applynumber} 人報名</dd>
</dl>',
    ],
    'hash' => '3d04a558',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'time',
      4 => 'place',
      5 => 'applynumber',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '83',
    'blockclass' => 'group_trade',
    'name' => '[內置]商品列表',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li style="width: {picwidth}px;">
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
	<p>{price}</p>
</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px;">
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
	<p>{price}</p>
</li>',
    ],
    'hash' => 'edd331a7',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'price',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '84',
    'blockclass' => 'group_activity',
    'name' => '[內置]活動列表',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{time} {place}</dd>
	<dd> 已有 {applynumber} 人報名</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{time} {place}</dd>
	<dd> 已有 {applynumber} 人報名</dd>
</dl>',
    ],
    'hash' => '502cc3f6',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'time',
      4 => 'place',
      5 => 'applynumber',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '85',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子作者＋標題+摘要（帶頭像）',
    'template' =>
    [
      'raw' => '<div class="module cl xld xlda">
[loop]
<dl class="cl">
<dd class="m"><a href="home.php?mod=space&uid={authorid}" c="1"{target}><img src="{avatar}" width="48" height="48" alt="{author}" /></a></dd>
<dt style="padding-bottom: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd style="margin-bottom: 0;">{summary}</dd>
<dd style="margin-bottom: 0;">作者: <a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
<dd class="m"><a href="home.php?mod=space&uid={authorid}" c="1"{target}><img src="{avatar}" width="48" height="48" alt="{author}" /></a></dd>
<dt style="padding-bottom: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd style="margin-bottom: 0;">{summary}</dd>
<dd style="margin-bottom: 0;">作者: <a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></dd>
</dl>',
    ],
    'hash' => '87d533ea',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'authorid',
      1 => 'avatar',
      2 => 'author',
      3 => 'url',
      4 => 'title',
      5 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '86',
    'blockclass' => 'portal_article',
    'name' => '[內置]頻道欄目+標題',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><label>[<a href="{caturl}" title="{catname}"{target}>{catname}</a>]</label><a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><label>[<a href="{caturl}" title="{catname}"{target}>{catname}</a>]</label><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '7720f457',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'caturl',
      1 => 'catname',
      2 => 'url',
      3 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '87',
    'blockclass' => 'forum_thread',
    'name' => '[內置]懸賞主題專用樣式',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a>{summary}</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a>{summary}</li>',
    ],
    'hash' => '56bffda0',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '88',
    'blockclass' => 'forum_thread',
    'name' => '[內置]首頁熱議-帖子',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl>
	<dd style="margin-bottom: 0; font-size: 12px; color: #369">{author} &#8250;</dd>
	<dt style="padding: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd style="margin-bottom: 0;">{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl>
	<dd style="margin-bottom: 0; font-size: 12px; color: #369">{author} &#8250;</dd>
	<dt style="padding: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd style="margin-bottom: 0;">{summary}</dd>
</dl>',
    ],
    'hash' => '08596517',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'author',
      1 => 'url',
      2 => 'title',
      3 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '89',
    'blockclass' => 'group_thread',
    'name' => '[內置]首頁熱議-圈子帖子',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl>
	<dd style="margin-bottom: 0; font-size: 12px; color: #369">{author} &#8250;</dd>
	<dt style="padding: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd style="margin-bottom: 0;">{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl>
	<dd style="margin-bottom: 0; font-size: 12px; color: #369">{author} &#8250;</dd>
	<dt style="padding: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd style="margin-bottom: 0;">{summary}</dd>
</dl>',
    ],
    'hash' => 'a75db897',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'author',
      1 => 'url',
      2 => 'title',
      3 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '90',
    'blockclass' => 'space_blog',
    'name' => '[內置]首頁熱議-日誌',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl>
	<dd style="margin-bottom: 0; font-size: 12px; color: #369">{username} &#8250;</dd>
	<dt style="padding: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd style="margin-bottom: 0;">{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl>
	<dd style="margin-bottom: 0; font-size: 12px; color: #369">{username} &#8250;</dd>
	<dt style="padding: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd style="margin-bottom: 0;">{summary}</dd>
</dl>',
    ],
    'hash' => '9e68bc9b',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'username',
      1 => 'url',
      2 => 'title',
      3 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '91',
    'blockclass' => 'forum_thread',
    'name' => '[內置]投票主題專用樣式',
    'template' =>
    [
      'raw' => '<div class="module cl xld b_poll">
[loop]
<dl>
<dt class="xs2"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl>
<dt class="xs2"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'fa07a66f',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '92',
    'blockclass' => 'forum_thread',
    'name' => '[內置]辯論主題專用樣式',
    'template' =>
    [
      'raw' => '<div class="module cl xld b_debate">
[loop]
<dl>
<dt class="xs2"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl>
<dt class="xs2"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '6a480986',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '93',
    'blockclass' => 'group_activity',
    'name' => '[內置]圈子活動:大圖＋摘要',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl>
<dd class="m"><a href="{url}"{target}><img src="{pic}" width="120" height="140" alt="{title}" /></a></dd>
<dt class="xs2"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd>
<p class="pbn">{summary}</p>
<p>{place} {class}</p>
<p>時間: {time}</p>
<p>{applynumber} 人關注</p>
</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl>
<dd class="m"><a href="{url}"{target}><img src="{pic}" width="120" height="140" alt="{title}" /></a></dd>
<dt class="xs2"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd>
<p class="pbn">{summary}</p>
<p>{place} {class}</p>
<p>時間: {time}</p>
<p>{applynumber} 人關注</p>
</dd>
</dl>',
    ],
    'hash' => '11d4011e',
    'getpic' => '1',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'summary',
      4 => 'place',
      5 => 'class',
      6 => 'time',
      7 => 'applynumber',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '94',
    'blockclass' => 'group_activity',
    'name' => '[內置]圈子活動:小圖＋標題',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
<dd class="m"><a href="{url}"{target}><img src="{pic}" width="48" height="48“ alt="{title}" /></a></dd>
<dt style="padding-bottom: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd style="margin: 0;"> {time} {place}</dd>
<dd class="xg1" style="margin: 0;">{applynumber} 人關注</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
<dd class="m"><a href="{url}"{target}><img src="{pic}" width="48" height="48“ alt="{title}" /></a></dd>
<dt style="padding-bottom: 0;"><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd style="margin: 0;"> {time} {place}</dd>
<dd class="xg1" style="margin: 0;">{applynumber} 人關注</dd>
</dl>',
    ],
    'hash' => '51658dfa',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'time',
      4 => 'place',
      5 => 'applynumber',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '95',
    'blockclass' => 'space_album',
    'name' => '[內置]相簿列表（豎線分隔）',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li style="width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}" title="{title}"{target}>{title}</a> ({picnum})</p>
</li>
[/loop]
[order=odd]
<li style="margin-right: 18px; padding-right: 24px; border-right: 1px solid #CCC; width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a> ({picnum})</p>
</li>
[/order]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
        'odd' => '<li style="margin-right: 18px; padding-right: 24px; border-right: 1px solid #CCC; width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a> ({picnum})</p>
</li>',
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}" title="{title}"{target}>{title}</a> ({picnum})</p>
</li>',
    ],
    'hash' => '771549b7',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'picnum',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '96',
    'blockclass' => 'space_pic',
    'name' => '[內置]圖片列表（豎線分隔）',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li style="width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>
[/loop]
[order=odd]
<li style="margin-right: 18px; padding-right: 24px; border-right: 1px solid #EEE; width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>
[/order]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
        'odd' => '<li style="margin-right: 18px; padding-right: 24px; border-right: 1px solid #EEE; width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>',
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>',
    ],
    'hash' => 'ab23af19',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '97',
    'blockclass' => 'portal_article',
    'name' => '[內置]碎片式文章標題列表',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li><a href="{url}" title="{title}"{target}>{title}</a>
[/loop]
[order=even]
<a href="{url}" title="{title}"{target} class="lit" style="margin-left: 5px; font-size: 12px">{title}</a></li>
[/order]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
        'even' => '<a href="{url}" title="{title}"{target} class="lit" style="margin-left: 5px; font-size: 12px">{title}</a></li>',
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a>',
    ],
    'hash' => 'bc85eab4',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '98',
    'blockclass' => 'portal_article',
    'name' => '[內置]文章封面列表（豎線分隔）',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li style="width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>
[/loop]
[order=odd]
<li style="margin-right: 18px; padding-right: 24px; border-right: 1px solid #EEE; width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>
[/order]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
        'odd' => '<li style="margin-right: 18px; padding-right: 24px; border-right: 1px solid #EEE; width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>',
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px;">
	<a href="{url}"><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>',
    ],
    'hash' => '6b653acb',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '99',
    'blockclass' => 'html_announcement',
    'name' => '[內置]站點公告',
    'template' =>
    [
      'raw' => '<div class="module cl">
<ul>
[loop]
<li><img alt="公告" src="static/image/common/ann_icon.gif"><a href="{url}" title="{title}"{target}>{title}（{starttime}）</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><img alt="公告" src="static/image/common/ann_icon.gif"><a href="{url}" title="{title}"{target}>{title}（{starttime}）</a></li>',
    ],
    'hash' => '1f88cc82',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'starttime',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '100',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子圖文展示',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><em class="y xg1 xw0"><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><em class="y xg1 xw0"><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => '881ee4a3',
    'getpic' => '1',
    'getsummary' => '1',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'authorid',
      4 => 'author',
      5 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '101',
    'blockclass' => 'group_thread',
    'name' => '[內置]帖子圖文列表',
    'template' =>
    [
      'raw' => '<div class="module cl xld">
[loop]
<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><em class="y xg1 xw0"><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>
[/loop]
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<dl class="cl">
	<dd class="m"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a></dd>
	<dt><em class="y xg1 xw0"><a href="home.php?mod=space&uid={authorid}"{target}>{author}</a></em><a href="{url}" title="{title}"{target}>{title}</a></dt>
	<dd>{summary}</dd>
</dl>',
    ],
    'hash' => 'b67132d6',
    'getpic' => '1',
    'getsummary' => '1',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
      3 => 'authorid',
      4 => 'author',
      5 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '102',
    'blockclass' => 'group_thread',
    'name' => '[內置][圈子名]+圈子帖子標題',
    'template' =>
    [
      'raw' => '<div class="module cl xl xl1">
<ul>
[loop]
<li>[<a href="{groupurl}"{target}>{groupname}</a>] <a href="{url}" title="{title}"{target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li>[<a href="{groupurl}"{target}>{groupname}</a>] <a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => 'a2f9089e',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'groupurl',
      1 => 'groupname',
      2 => 'url',
      3 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '103',
    'blockclass' => 'other_otherfriendlink',
    'name' => '[內置]友情鏈接圖文',
    'template' =>
    [
      'raw' => '<div class="bn lk">
<ul class="m cl">
[loop]
<li class="cl">
<div class="forumlogo"><a href="{url}" {target}><img border="0" alt="{title}" src="{pic}"></a></div>
<div class="forumcontent"><h5><a target="_blank" href="{url}">{title}</a></h5><p>{summary}</p></div>
</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li class="cl">
<div class="forumlogo"><a href="{url}" {target}><img border="0" alt="{title}" src="{pic}"></a></div>
<div class="forumcontent"><h5><a target="_blank" href="{url}">{title}</a></h5><p>{summary}</p></div>
</li>',
    ],
    'hash' => 'b921ea24',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'pic',
      3 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '104',
    'blockclass' => 'other_otherfriendlink',
    'name' => '[內置]友情鏈接僅圖片',
    'template' =>
    [
      'raw' => '<div class="bn lk">
<div class="cl mbm">
[loop]
<a href="{url}" {target}><img border="0" alt="{title}" src="{pic}"></a>
[/loop]
</div>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<a href="{url}" {target}><img border="0" alt="{title}" src="{pic}"></a>',
    ],
    'hash' => 'c8d00338',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'pic',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '105',
    'blockclass' => 'other_otherfriendlink',
    'name' => '[內置]友情鏈接僅文字',
    'template' =>
    [
      'raw' => '<div class="x cl">
<ul class="cl mbm">
[loop]
<li><a href="{url}" {target}>{title}</a></li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" {target}>{title}</a></li>',
    ],
    'hash' => 'b615e0d0',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '106',
    'blockclass' => 'other_otherstat',
    'name' => '[內置]全部統計資訊',
    'template' =>
    [
      'raw' => '[loop]<div class="tns">
<ul>
<li>{posts_title}:<em>{posts}</em></li>
<li>{groups_title}:<em>{groups}</em></li>
<li>{members_title}:<em>{members}</em></li>
<li>{groupnewposts_title}:<em>{groupnewposts}</em></li>
<li>{bbsnewposts_title}:<em>{bbsnewposts}</em></li>
<li>{bbslastposts_title}:<em>{bbslastposts}</em></li>
<li>{onlinemembers_title}:<em>{onlinemembers}</em></li>
<li>{maxmembers_title}:<em>{maxmembers}</em></li>
<li>{doings_title}:<em>{doings}</em></li>
<li>{blogs_title}:<em>{blogs}</em></li>
<li>{albums_title}:<em>{albums}</em></li>
<li>{pics_title}:<em>{pics}</em></li>
<li>{shares_title}:<em>{shares}</em></li>
</ul>
</div>
[/loop]',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<div class="tns">
<ul>
<li>{posts_title}:<em>{posts}</em></li>
<li>{groups_title}:<em>{groups}</em></li>
<li>{members_title}:<em>{members}</em></li>
<li>{groupnewposts_title}:<em>{groupnewposts}</em></li>
<li>{bbsnewposts_title}:<em>{bbsnewposts}</em></li>
<li>{bbslastposts_title}:<em>{bbslastposts}</em></li>
<li>{onlinemembers_title}:<em>{onlinemembers}</em></li>
<li>{maxmembers_title}:<em>{maxmembers}</em></li>
<li>{doings_title}:<em>{doings}</em></li>
<li>{blogs_title}:<em>{blogs}</em></li>
<li>{albums_title}:<em>{albums}</em></li>
<li>{pics_title}:<em>{pics}</em></li>
<li>{shares_title}:<em>{shares}</em></li>
</ul>
</div>',
    ],
    'hash' => '027d3e60',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '0',
    'fields' =>
    [
      0 => 'posts_title',
      1 => 'posts',
      2 => 'groups_title',
      3 => 'groups',
      4 => 'members_title',
      5 => 'members',
      6 => 'groupnewposts_title',
      7 => 'groupnewposts',
      8 => 'bbsnewposts_title',
      9 => 'bbsnewposts',
      10 => 'bbslastposts_title',
      11 => 'bbslastposts',
      12 => 'onlinemembers_title',
      13 => 'onlinemembers',
      14 => 'maxmembers_title',
      15 => 'maxmembers',
      16 => 'doings_title',
      17 => 'doings',
      18 => 'blogs_title',
      19 => 'blogs',
      20 => 'albums_title',
      21 => 'albums',
      22 => 'pics_title',
      23 => 'pics',
      24 => 'shares_title',
      25 => 'shares',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '107',
    'blockclass' => 'forum_thread',
    'name' => '[內置]一簡介+兩列標題',
    'template' =>
    [
      'raw' => '<div class="bm bw0">
[index=1]
<dl class="cl xld">
<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd>{summary}</dd>
</dl>
<hr class="da" />
[/index]
<ul class="xl xl2 cl">
[loop]<li><a href="{url}" title="{title}"{target}>{title}</a></li>[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
        1 => '<dl class="cl xld">
<dt><a href="{url}" title="{title}"{target}>{title}</a></dt>
<dd>{summary}</dd>
</dl>
<hr class="da" />',
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li><a href="{url}" title="{title}"{target}>{title}</a></li>',
    ],
    'hash' => '9e2ea31f',
    'getpic' => '0',
    'getsummary' => '1',
    'makethumb' => '0',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'summary',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '108',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子圖片幻燈片',
    'template' =>
    [
      'raw' => '<div class="module cl slidebox">
<ul class="slideshow">
[loop]
<li style="width: {picwidth}px; height: {picheight}px;"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></a><span class="title">{title}</span></li>
[/loop]
</ul>
</div>
<script type="text/javascript">
runslideshow();
</script>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px; height: {picheight}px;"><a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" /></a><span class="title">{title}</span></li>',
    ],
    'hash' => 'cba1f109',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '109',
    'blockclass' => 'forum_thread',
    'name' => '[內置]帖子圖片列表',
    'template' =>
    [
      'raw' => '<div class="module cl ml">
<ul>
[loop]
<li style="width: {picwidth}px;">
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>
[/loop]
</ul>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<li style="width: {picwidth}px;">
	<a href="{url}"{target}><img src="{pic}" width="{picwidth}" height="{picheight}" alt="{title}" /></a>
	<p><a href="{url}" title="{title}"{target}>{title}</a></p>
</li>',
    ],
    'hash' => '0ab2e307',
    'getpic' => '1',
    'getsummary' => '0',
    'makethumb' => '1',
    'settarget' => '1',
    'fields' =>
    [
      0 => 'url',
      1 => 'pic',
      2 => 'title',
    ],
    'moreurl' => '0',
  ],[
    'styleid' => '110',
    'blockclass' => 'html_misctag',
    'name' => '[內置]標籤模版',
    'template' =>
    [
      'raw' => '<!-- 熱門標籤模組 -->
<div class="tag-cloud-module">
	<div class="tag-cloud-container">
		[loop]
		<a href="{url}"
		   title="{title} ({related_count}篇內容)"
		   class="tag-cloud-item tag-size-{size_level} tag-color-{color_level}"
		   data-count="{related_count}"
		   data-hot="{hot_score}">
			{title}
		</a>
		[/loop]
	</div>
</div>',
      'footer' => '',
      'header' => '',
      'indexplus' =>
      [
      ],
      'index' =>
      [
      ],
      'orderplus' =>
      [
      ],
      'order' =>
      [
      ],
      'loopplus' =>
      [
      ],
      'loop' => '<a href="{url}"
		   title="{title} ({related_count}篇內容)"
		   class="tag-cloud-item tag-size-{size_level} tag-color-{color_level}"
		   data-count="{related_count}"
		   data-hot="{hot_score}">
			{title}
		</a>',
    ],
    'hash' => '391cb72a',
    'getpic' => '0',
    'getsummary' => '0',
    'makethumb' => '0',
    'settarget' => '0',
    'fields' =>
    [
      0 => 'url',
      1 => 'title',
      2 => 'related_count',
      3 => 'size_level',
      4 => 'color_level',
      5 => 'hot_score',
    ],
    'moreurl' => '0',
  ],
];
