<?php exit('Access Denied');?>
<!--{template common/header}-->
<div id="pt" class="bm cl">
	<div class="y" style="margin-top: 5px; margin-right: 10px;">
		<form id="darkroomSearchForm" method="get" action="misc.php">
			<input type="hidden" name="mod" value="darkroom" />
			<!--{if $username}-->
			<input type="text" id="searchUsername" name="username" value="$username" class="px vm"/>
			<!--{else}-->
			<input type="text" id="searchUsername" name="username" placeholder="{lang darkroom_search_placeholder}" class="px vm"/>
			<!--{/if}-->
			<button type="submit" class="pn pnc" style="width: 40px">{lang darkroom_search}</button>
		</form>
	</div>
	<div class="z">
		<a href="./" class="nvhm" title="{lang homepage}">$_G['setting']['bbname']</a> <em>&rsaquo;</em>
		<a href="misc.php?mod=darkroom">{lang darkroom}</a>
	</div>
</div>

<div style="margin:10px 0;">
<table id="darkroomtable" summary="{lang darkroom}" cellspacing="0" cellpadding="0" class="bm dt">
	<tr>
		<th class="xw1" style="width:105px;">{lang username}</th>
		<th class="xw1" style="width:135px;">{lang crime_action}</th>
		<th class="xw1" style="width:155px;">{lang expire_dateline}</th>
		<th class="xw1" style="width:155px;">{lang crime_dateline}</th>
		<th class="xw1">{lang crime_reason}</th>
	</tr>
<!--{if $crimelist}-->
	<!--{eval $i = 0;}-->
	<!--{loop $crimelist $crime}-->
	<!--{eval $i++;}-->
	<tr id="darkroomuid_$crime['uid']" {if $i % 2 == 0} class="alt"{/if}>
		<td><a href="home.php?mod=space&uid=$crime['uid']" target="_blank">$crime['username']</a></td>
		<td>$crime['action']</td>
		<td>$crime['groupexpiry']</td>
		<td>$crime['dateline']</td>
		<td>$crime['reason']</td>
	</tr>
	<!--{/loop}-->
</table>
<!--{if $dataexist == 1}-->
<div class="bm bw0 pgs cl">
	<span class="pgb y"><div class="pg"><a href="javascript:;" class="nxt" id="darkroommore" cid="$cid">{lang more}</a></div></span>
</div>
<!--{/if}-->
<!--{else}-->
<!--{if $username}-->
	<tr>
		<td colspan="6" align="center">{lang darkroom_no_search_result}</td>
	</tr>
<!--{else}-->
	<tr>
		<td colspan="6" align="center">{lang darkroom_no_users}</td>
	</tr>
<!--{/if}-->
</table>
<!--{/if}-->
</div>

<script type="text/javascript">
	(function() {
		const darkroommore = document.getElementById('darkroommore');

		if (darkroommore) {
			darkroommore.onclick = async function() {
				const obj = this;
				const cid = parseInt(obj.getAttribute('cid'), 10);

				// 构建请求URL
				const timestamp = Math.floor(Date.now() / 1000);
				const random = Math.random() * 1000;
				const url = 'misc.php?mod=darkroom&cid='+cid+'&ajaxdata=json&t='+parseInt((+new Date()/1000)/(Math.random()*1000));

				const table = document.getElementById('darkroomtable');
				let tablerows = table.rows.length;

				obj.style.display = 'none';

				try {
					// 使用Fetch API发送请求
					const response = await fetch(url);

					// 检查HTTP响应状态
					if (!response.ok) {
						throw new Error('error: '+response.status);
					}

					// 解析JSON响应
					const s = await response.json();

					if (s && s.message) {
						const smsg = s.message.split('|');
						if (smsg[0] === '1') {
							obj.setAttribute('cid', smsg[1]);
							obj.style.display = 'block';
						}

						const list = s.data ? Object.values(s.data) : [];
						if (list && list.length) {
							for (let i = 0; i < list.length; i++) {
								const item = list[i];
								if (document.getElementById('darkroomuid_'+item.uid)) {
									continue;
								}

								const newtr = table.insertRow(tablerows);
								if (tablerows % 2 === 0) {
									newtr.className = 'alt';
								}

								newtr.insertCell(0).innerHTML = '<a href="home.php?mod=space&uid='+item.uid+'" target="_blank">'+item.username+'</a>';
								newtr.insertCell(1).innerHTML = item.action;
								newtr.insertCell(2).innerHTML = item.groupexpiry;
								newtr.insertCell(3).innerHTML = item.dateline;
								newtr.insertCell(4).innerHTML = item.reason;

								tablerows++;
							}
						}
					}
				} catch (error) {
					// 处理所有可能的错误（网络错误、JSON解析错误等）
					console.error('error:', error);
					// 出错时恢复按钮显示，允许用户重试
					obj.style.display = 'block';
				}
			};
		}
	})();
</script>

<!--{template common/footer}-->