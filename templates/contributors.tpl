{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}

<h2>OSF Submission</h2>
<p>Select contributors to add as authors to this paper.</p>
<form method="POST">
{foreach item=item key=key from=$user_array}
	<input type="checkbox" name="contributors[]" value="{$key}"> {$item}</br />
{/foreach}
{file_data}
<br />
<input type="submit" name="node_submit" id="node_submit" class="button" value="Select Contributors"/>
</form>

{include file="common/footer.tpl"}