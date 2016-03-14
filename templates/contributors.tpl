{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
</style>
{/literal}

<h2>Contributors</h2>
<p>Select contributors to add as authors to this paper.</p>
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
{foreach item=item key=key from=$user_array}
	<input type="checkbox" name="contributors[]" value="{$key}" {if $authors.$key}checked="checked"{/if}> Include {$item} as an author</br /></br />
	<table width="100%" class="data">
<tbody><tr valign="top">
	<td width="20%" class="label">
<label for="authors-{$key}-firstName">
	First Name *</label>
</td>
	 {$authors.$key.firstName}
	<td width="80%" class="value"><input type="text" class="textField" name="authors[{$key}][firstName]" id="authors-{$key}-firstName" value="{$authors.$key.firstName}" size="20" maxlength="40"></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">
<label for="authors-{$key}-middleName">
	Middle Name </label>
</td>
	<td width="80%" class="value"><input type="text" class="textField" name="authors[{$key}][middleName]" id="authors-{$key}-middleName" value="{$authors.$key.middleName}" size="20" maxlength="40"></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">
<label for="authors-{$key}-lastName">
	Last Name *</label>
</td>
	<td width="80%" class="value"><input type="text" class="textField" name="authors[{$key}][lastName]" id="authors-{$key}-lastName" value="{$authors.$key.lastName}" size="20" maxlength="90"></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">
<label for="authors-{$key}-email">
	Email *</label>
</td>
	<td width="80%" class="value"><input type="text" class="textField" name="authors[{$key}][email]" id="authors-{$key}-email" value="{$authors.$key.email}" size="30" maxlength="90"></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">
<label for="authors-{$key}-orcid">
	ORCID iD </label>
</td>
	<td width="80%" class="value"><input type="text" class="textField" name="authors[{$key}][orcid]" id="authors-{$key}-orcid" value="{$authors.$key.orcid}" size="30" maxlength="90"><br>ORCID iDs can only be assigned by <a href="http://orcid.org/" target="_blank">the ORCID Registry</a>. You must conform to their standards for expressing ORCID iDs, and include the full URI (eg. <em>http://orcid.org/0000-0002-1825-0097</em>).</td>
</tr>
<tr valign="top">
	<td class="label">
<label for="authors-{$key}-url">
	URL </label>
</td>
	<td class="value"><input type="text" name="authors[{$key}][url]" id="authors-{$key}-url" value="{$authors.$key.url}" size="30" maxlength="255" class="textField"></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">
<label for="authors-{$key}-affiliation">
	Affiliation </label>
</td>
	<td width="80%" class="value">
		<textarea name="authors[{$key}][affiliation]" class="textArea" id="authors-{$key}-affiliation" rows="5" cols="40">{$authors.$key.affiliation}</textarea><br>
		<span class="instruct">(Your institution, e.g. "Simon Fraser University")</span>
	</td>
</tr>
<tr valign="top">
	<td width="20%" class="label">
<label for="authors-{$key}-country">
	Country </label>
</td>
	<td width="80%" class="value">
		<select name="authors[{$key}][country]" id="authors-{$key}-country" class="selectMenu" selected="{$authors.$key.country}">
			<option value=""></option>
				{html_options options=$countries selected=$authors.$key.country}
		</select>
	</td>
</tr>
<tr valign="top">
	<td width="20%" class="label">
<label for="authors-{$key}-biography">
	Bio Statement </label>
<br>(E.g., department and rank)</td>
	<td width="80%" class="value"><textarea name="authors[{$key}][biography]" class="textArea" id="authors-{$key}-biography" rows="5" cols="40" aria-hidden="true">{$authors.$key.biography}</textarea></td>
</tr>

</tbody></table>
{$authors.$key.primary}
<input type="checkbox" name="authors[{$key}][primary]" value="{$key}" {if $authors.$key.primary}checked="checked"{/if}> Mark {$item} as the primary author</br /></br />
<br />
<div class="separator"></div>
<br />
{/foreach}
<br />
<input type="submit" name="node_submit" id="node_submit" class="button" value="Select Contributors"/>
</form>

{include file="common/footer.tpl"}