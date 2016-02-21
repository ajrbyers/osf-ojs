<?php

/**
 *
 * Plugin for submitting an article from OSF.io
 * Written by Andy Byers, Ubiquity Press
 *
 */

import('classes.handler.Handler');
require_once('OsfDAO.inc.php');

function redirect($url) {
	header("Location: ". $url); // http://www.example.com/"); /* Redirect browser */
	/* Make sure that code below does not get executed when we redirect. */
	exit;
}

function raise404($msg='404 Not Found') {
	header("HTTP/1.0 404 Not Found");
	fatalError($msg);
	return;
}

function clean_string($v) {
	// strips non-alpha-numeric characters from $v	
	return preg_replace('/[^\-a-zA-Z0-9]+/', '',$v);
}

class OsfHandler extends Handler {

	public $dao = null;

	function OsfHandler() {
		parent::Handler();
		$this->dao = new OsfDAO();
	}
	
	// utils

	function file_path($articleId, $file_name) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($articleId);
		$journalId = $article->getJournalId();
		return Config::getVar('files', 'files_dir') . '/journals/' . $journalId .  '/articles/' . $articleId . '/supp/' . $file_name;
	}
	
	/* sets up the template to be rendered */
	function display($fname, $page_context=array()) {
		// setup template
		AppLocale::requireComponents(LOCALE_COMPONENT_OJS_MANAGER, LOCALE_COMPONENT_PKP_MANAGER);
		parent::setupTemplate();
		
		// setup template manager
		$templateMgr =& TemplateManager::getManager();
		
		// default page values
		$context = array(
			"page_title" => "OSF Submission"
		);
		foreach($page_context as $key => $val) {
			$context[$key] = $val;
		}

		$plugin =& PluginRegistry::getPlugin('generic', OSF_PLUGIN_NAME);
		$tp = $plugin->getTemplatePath();
		$context["template_path"] = $tp;
		$context["article_select_template"] = $tp . "article_select_snippet.tpl";
		$context["article_pagination_template"] = $tp . "article_pagination_snippet.tpl";
		$context["disableBreadCrumbs"] = true;
		$templateMgr->assign($context); // http://www.smarty.net/docsv2/en/api.assign.tpl

		// render the page
		$templateMgr->display($tp . $fname);
	}

	//
	// views
	//
	
	/* handles requests to:
		/osf/
		/osf/index/
	*/
	function index($args, &$request) {
	
		$context = array(
			"page_title" => "OSF Submission",
		);
		$this->display('index.tpl', $context);
	}

	/* handles requests to:
		/osf/get_token/
	*/
	function get_token($args, &$request) {
		header ('Location: https://accounts.osf.io/oauth2/authorize?scope=osf.full_read&client_id=149ea618e57a4331acd8115360096aa0&redirect_uri=http://localhost:8000/index.php/test/osf/callback/');
	}

	/* handles requests to:
		/osf/callback/
	*/
	function callback($args, &$request) {
		echo 'hello';
	}
	
}

?>