<div class="col-sm-4">
{if $value && $value != '0000-00-00 00:00:00'}
 	<input type="text" class="form-control" id="i{$item_id}" placeholder="{$item_id}" name="{$form_id}_{$item_id}" value="{$value}" {if $readonly}readonly{/if}>
{else}
	<input type="text" class="form-control" id="i{$item_id}" placeholder="{$item_id}" name="{$form_id}_{$item_id}" value='{$smarty.now|date_format:"%Y-%m-%d %H:%I:%S"}' {if $readonly}readonly{/if}>
{/if}
</div>