{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}

<h2>OSF Submission</h2>
{if $active == 'file' }
<p>Great! You've selected a file!</p>
<form method="POST">
	<ul>
	{foreach key=key item=item from=$file_array}
		<li>{$item}</li>
	{/foreach}
	</ul>
	<h4>File Details</h4>
	<div class="separator"></div>
	<p><strong>Label</strong>:<br /><input type="text" name="label" class="textField" style="width: 200px;"></p>
	<p><strong>Description</strong>:<br /><textarea name="description" class="textArea" cols="34" rows="5"></textarea>
	<div class="separator"></div>
	<input type="submit" name="node_submit" id="node_submit" class="button" value="Start Submission with File"/>
</form>
{else}
<p>Browse your files until you find the one you wish to use for your submission.</p>
<ul>
{foreach key=key item=item from=$file_array}
	<li><a href="?id={$node_id}{if $provider}&amp;provider={$provider}{/if}&amp;{$active}={$key}">{$item}</a></li>
{/foreach}
</ul>
{/if}

{include file="common/footer.tpl"}