{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}

<h2>OSF Submission</h2>
<p>Thanks for your interest in submitting an article to {{ journal.name }}. You will now be asked to permit this journal access to your OSF account.</p>
<p>If you accept, you will be asked to select contributors and files to create a new article.</p>

<a href="get_token/" type="submit" class="button-secondary">Start Submission Process</a>


{include file="common/footer.tpl"}