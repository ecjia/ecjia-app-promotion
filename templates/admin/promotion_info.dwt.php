<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.promotion_info.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link}
		<a class="btn data-pjax" href="{$action_link.href}" id="sticky_a" style="float:right;margin-top:-3px;"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		{/if}
	</h3>
</div>

<div class="row-fluid edit-page">
	<div class="span12">
		<form method="post" class="form-horizontal" action="{$form_action}" name="theForm" enctype="multipart/form-data">
			<fieldset>
				<div class="control-group formSep">
					<label class="control-label">{t}商品关键字：{/t}</label>
					<div class="controls">
						<input type="text" name="keywords" />
						<input type="button" value="{t}搜索{/t}" class="btn searchGoods" data-url='{url path="promotion/admin/search_goods"}'>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{t}选择活动商品：{/t}</label>
					<div class="controls">
						<select name="goods_id" class='goods_list'>
							<!-- {if !$promotion_info.goods_name} -->
								<option value='-1'>{t}请选择...{/t}</option>
							<!-- {else} -->
								<option value="{$promotion_info.goods_id}">{$promotion_info.goods_name}</option>
							<!-- {/if} -->
						</select>
						<span class="help-block">{t}需要先搜索商品，生成商品列表，然后再选择{/t}</span>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{t}活动开始时间：{/t}</label>
					<div class="controls">
						<input name="start_time" class="date" type="text" placeholder="{t}请选择活动开始时间{/t}" value="{$promotion_info.promote_start_date}"/>
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{t}活动结束时间：{/t}</label>
					<div class="controls">
						<input name="end_time" class="date" type="text" placeholder="{t}请选择活动开始时间{/t}" value="{$promotion_info.promote_end_date}"/>
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{t}活动价格：{/t}</label>
					<div class="controls">
						<input name="price" type="text" value="{$promotion_info.promote_price}"/>
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<input type="submit" value="{t}确定{/t}" class="btn btn-gebo" />
						<input type="hidden" name='act_id' value="{$mobilebuy.act_id}">
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<!-- {/block} -->