{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}

<h2>Article Metadata</h2>
<p>Complete your submission by providing a title and an abstract.</p>
{if $errors}
<div style="width: 100%; color: #a94442; background-color: #f2dede; padding: 5px; margin-bottom: 10px;">
	<p>There are some errors on the form:</p>
	<ul>
		{foreach item=item key=key from=$errors}
		<li>{$key} {$item}</li>
		{/foreach}
	</ul>
</div>
{/if}
<form method="POST">
<div id="titleAndAbstract">
<h3>Title and Abstract</h3>

<table width="100%" class="data">
<tbody>
<tr valign="top">
	<td width="20%" class="label">
		<label for="title">Section *</label>
	</td>
	<td width="80%" class="value">
		<select name="sectionId" id="sectionId" size="1" class="selectMenu"><option label="Please select a section..." value="0">Please select a section...</option>
			{html_options options=$sectionOptions selected=$post.sectionId}
		</select>
	</td>
</tr>

<tr valign="top">
	<td width="20%" class="label">
<label for="title">
	Title *</label>
</td>
	<td width="80%" class="value"><input type="text" class="textField" name="title" id="title" value="{$post.title}" size="60"></td>
</tr>

<tr valign="top">
	<td width="20%" class="label">
<label for="abstract">
	Abstract *</label>
</td>
	<td width="80%" class="value"><textarea name="abstract" id="abstract" class="textArea" rows="15" cols="60" aria-hidden="true">{$post.abstract}</textarea></td>
</tr>
</tbody></table>
<script type="text/javascript">
{literal}
<!--
function checkSubmissionChecklist() {
	var elements = document.getElementById('submit').elements;
	for (var i=0; i < elements.length; i++) {
		if (elements[i].type == 'checkbox' && !elements[i].checked) {
			if (elements[i].name.match('^checklist')) {
				alert({/literal}'{translate|escape:"jsparam" key="author.submit.verifyChecklist"}'{literal});
				return false;
			} else if (elements[i].name == 'copyrightNoticeAgree') {
				alert({/literal}'{translate|escape:"jsparam" key="author.submit.copyrightNoticeAgreeRequired"}'{literal});
				return false;
			}
		}
	}
	return true;
}
// -->
{/literal}
</script>
<div class="separator"></div>
{if $currentJournal->getLocalizedSetting('submissionChecklist')}
{foreach name=checklist from=$currentJournal->getLocalizedSetting('submissionChecklist') key=checklistId item=checklistItem}
	{if $checklistItem.content}
		{if !$notFirstChecklistItem}
			{assign var=notFirstChecklistItem value=1}
			<div id="checklist">
			<h3>Submission Checklist</h3>
			<p>You must agree to the below items.</p>
			<table width="100%" class="data">
		{/if}
		<tr valign="top">
			<td width="5%"><input type="checkbox" id="checklist-{$smarty.foreach.checklist.iteration}" name="checklist[]" value="{$checklistId|escape}" {if $smarty.foreach.checklist.iteration|in_array:$selected_check_items}checked="checked"{/if} /></td>
			<td width="95%"><label for="checklist-{$smarty.foreach.checklist.iteration}">{$checklistItem.content|nl2br}</label></td>
		</tr>
	{/if}
{/foreach}
{/if}
</table>
</div>
<br />
<div class="separator"></div>
<div id="privacyStatement">
<h3>Privacy Statement</h3>
<br />
{$currentJournal->getLocalizedSetting('privacyStatement')|nl2br}
</div>
<div class="separator"></div>
<br />
<input type="submit" name="node_submit" id="node_submit" class="button" value="Complete Submission"/>
</form>

{include file="common/footer.tpl"}