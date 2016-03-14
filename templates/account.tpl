{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}
<h2>New Account</h2>
<form method="POST">
	<p>An account has been generated for you. An email with a username and password has been created for you. Click the login button below to login to your account and continue with the submission process.</p>
	<a href="{$login_url}" class="button-secondary">Login with New Account</a>
</form>


{include file="common/footer.tpl"}