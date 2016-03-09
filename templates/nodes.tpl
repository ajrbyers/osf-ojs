{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}

<h2>OSF Submission</h2>
<p>Select a project to make a submission from.</p>
<form method="POST">
{foreach item=item key=key from=$node_array}
	<input type="radio" name="node" value="{$key}"> {$item}</br />
{/foreach}
{file_data}
<br />
<input type="submit" name="node_submit" id="node_submit" class="button" value="Select Node"/>
</form>

{include file="common/footer.tpl"}