{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}
<h2>Start Submission</h2>
<form method="POST">
	<p>Thanks for your interest in submitting an article to {$journal->getLocalizedTitle()}. You will now be asked to permit this journal access to your OSF account.</p>
	<p>If you accept, you will be asked to select contributors and files to create a new article.</p>
	{if $logged_in eq 1 }
	<button type="submit" class="button-secondary">Start Submission Process</button>
	{else}
	<p>Unfortunately, the OSF API does not allow us to collect your email address, so please enter it below so we can generate you an account with this journal. If you already have an account with this journal, you should <a href="{$login_url}">login first</a>.</p>
	<div class="separator"></div>
	<br />
	{if $errors}
	<div style="width: 100%; color: #a94442; background-color: #f2dede; padding: 5px; margin-bottom: 10px;">
		<p>There are some errors on the form:</p>
		<ul>
			{foreach item=item key=key from=$errors}
			<li>{$key} {$item}</li>
			{/foreach}
		</ul>
	</div>
	<br />
	{/if}
	<label for="email">Email Address:</label>
	<input name="email" type="email" class="textField" value="" /><br /><br />
	<button type="submit" class="button-secondary">Start Submission Process</button>
	{/if}
</form>


{include file="common/footer.tpl"}