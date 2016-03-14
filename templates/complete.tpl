{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}

<h2>Submission Complete</h2>
<p>Thanks for submitting {$article->getArticleTitle()} to {$journal->getLocalizedTitle()}. It will now be reviewed by an Editor.</p>

{include file="common/footer.tpl"}