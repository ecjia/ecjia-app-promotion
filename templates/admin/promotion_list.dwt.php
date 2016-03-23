<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.promotion_list.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}"  id="sticky_a"><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<ul class="nav nav-pills">
	<li class="{if $type eq ''}active{/if}"><a class="data-pjax" href='{url path="promotion/admin/init"}'>全部 <span class="badge badge-info">{$type_count.count}</span> </a></li>
	<li class="{if $type eq 'on_sale'}active{/if}"><a class="data-pjax" href='{url path="promotion/admin/init" args="type=on_sale"}'>正在进行中<span class="badge badge-info">{$type_count.on_sale}</span> </a></li>
	<li class="{if $type eq 'coming'}active{/if}"><a class="data-pjax" href='{url path="promotion/admin/init" args="type=coming"}'>即将开始<span class="badge badge-info">{$type_count.coming}</span> </a></li>
	<li class="{if $type eq 'finished'}active{/if}"><a class="data-pjax" href='{url path="promotion/admin/init" args="type=finished"}'>已结束<span class="badge badge-info">{$type_count.finished}</span> </a></li>
	
	<li class="ecjiaf-fn">
		<form name="searchForm" method="post" action="{$form_search}">
			<div class="f_r form-inline">
				<!-- 关键字 -->
				<input type="text" name="keywords" value="{$list.filter.keywords}" placeholder="请输入留言标题或内容"/> 
				<button class="btn" type="submit">{t}搜索{/t}</button>
			</div>
		</form>
	</li>
</ul>
<div class="row-fluid list-page">
	<div class="span12">
		<div class="tab-content">
			<!-- system start -->
			<div class="tab-pane active">
				<table class="table table-striped table-hide-edit">
					<thead>
						<tr>
							<th class="w180">{t}缩略图{/t}</th>
							<th>{t}商品名称{/t}</th>
							<th>{t}活动开始时间{/t}</th>
							<th>{t}活动结束时间{/t}</th>
							<th>{t}活动价格{/t}</th>
						</tr>
					</thead>
					<!-- {foreach from=$promotion_list item=item key=key} -->
					<tr class="big">
						<td>
							<a href="{url path='promotion/admin/edit' args="goods_id={$item.goods_id}"}" title="Image 10" >
								<img class="thumbnail" alt="{$item.goods_name}" src="{$item.goods_thumb}">
							</a>
						</td>
						<td class="hide-edit-area">
							<span>{$item.goods_name}</span><br>
							<div class="edit-list">
								<a class="data-pjax" href="{RC_Uri::url('promotion/admin/edit',"id={$item.goods_id}")}" title="{t}编辑{/t}">{t}编辑{/t}</a>&nbsp;|&nbsp;
								<a data-toggle="ajaxremove" class="ajaxremove ecjiafc-red" data-msg="{t}您确定要删除该促销活动吗？{/t}" href="{RC_Uri::url('promotion/admin/remove',"id={$item.goods_id}")}" title="{t}移除{/t}">{t}删除{/t}</a>
						    </div>
						</td>
						<td>{$item.start_time}</td>
						<td>{$item.end_time}</td>
						<td>{$item.promote_price}</td>
					</tr>
					<!-- {foreachelse} -->
					   <tr><td class="no-records" colspan="10">{t}没有找到任何记录{/t}</td></tr>
					<!-- {/foreach} -->
				</table>
			</div>
			<!-- system end -->
		</div>
	</div>
</div>
{$page}
<!-- {/block} -->