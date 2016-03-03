<?php

/**
 *
 * Plugin for submitting an article from OSF.io
 * Written by Andy Byers, Ubiquity Press
 *
 */

import('classes.handler.Handler');
require_once('OsfDAO.inc.php');
require_once('utils/HttpPost.class.php');

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

	function api_call($path, $url) {
		$access_token = $_SESSION['token'];

		if ($url) {
			$url = $url;
		} else {
			$url = 'https://test-api.osf.io/v2/' . $path;
		}

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));

		$json_response = curl_exec($curl);

		return json_decode($json_response);
	}

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
		header ('Location: https://test-accounts.osf.io/oauth2/authorize?scope=osf.full_read&client_id=abb1368d3b124148899ff5fd5b07976f&redirect_uri=http://localhost:8001/index.php/test/osf/callback/');
	}

	/* handles requests to:
		/osf/callback/
	*/
	function callback($args, &$request) {
		$oauth2_client_id = 'abb1368d3b124148899ff5fd5b07976f';
		$oauth2_secret = '3mmXzLqWOmNmtKNAu6VooOdqmqpBfCd9QqfZLRkC';
		$oauth2_redirect = 'http://localhost:8001/index.php/test/osf/callback/';

		try {
			$code = $_GET['code'];
		} catch (Exception $err) {
			echo 'No code';
		}

		$url = 'https://test-accounts.osf.io/oauth2/token';
	    // this will be our POST data to send back to the OAuth server in exchange
		// for an access token
	    $params = array(
	        "code" => $code,
	        "client_id" => $oauth2_client_id,
	        "client_secret" => $oauth2_secret,
	        "redirect_uri" => $oauth2_redirect,
	        "grant_type" => "authorization_code"
	    );

		// build a new HTTP POST request
	    $request = new HttpPost($url);
	    $request->setPostData($params);
	    $request->send();

		// decode the incoming string as JSON
	    $responseObj = json_decode($request->getHttpResponse());

		// Tada: we have an access token!
	    $_SESSION['token'] = $responseObj->access_token;
	    
	    redirect('http://localhost:8001/index.php/test/osf/nodes/');
	}

	/* handles requests to:
		/osf/nodes/
	*/
	function nodes($args, &$request) {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$selected_node = $_POST["node"];
			redirect('http://localhost:8001/index.php/test/osf/node/?id=' . $selected_node);
		} else {
			$path = 'users/me/';
			$user_response = $this -> api_call($path, False);
			$node_url = $user_response->data->relationships->nodes->links->related->href;

			$node_response = $this -> api_call(false, $node_url);
			$node_array = array();
			foreach ($node_response->data as $data) {
			    $node_array[$data->id] = $data->attributes->title;
			}

			$context = array(
				"page_title" => "Select OSF Node",
				"node_array" => $node_array,
			);
			$this->display('nodes.tpl', $context);
		}
	}

	/* handles requests to:
		/osf/node/?id&provider&path
	*/
	function node($args, &$request) {
		$node_id = $_GET['id'];
		$provider = $_GET['provider'];
		$file_path = $_GET['path'];

		if ($provider && ! $file_path) {
			$active = 'path';
			$path = 'nodes/' . $node_id . '/files/' . $provider . '/';
			$file_response = $this->api_call($path, False);

			$file_array = array();

			foreach ($file_response->data as $file) {
				$file_array[$file->id] = $file->attributes->name;
			}

		} elseif ($file_path) {
			$active = 'path';
			$path = '/files/' . $file_path . '/';
			$file_response = $this->api_call($path, False);

			if ($file_response->data->attributes->kind == 'folder') {
				$path = 'nodes/' . $node_id . '/files/' . $provider . $file_response->data->attributes->path;
				$file_response = $this->api_call($path, False);
			} elseif ($file_response->data->attributes->kind == 'file') {
				$download_link = $file_response->data->links->download;
				$active = 'file';
			}

			$file_array = array();

			if (is_array($file_response->data)) {
				foreach ($file_response->data as $file) {
					$file_array[$file->id] = $file->attributes->name;
				}
			} else {
				$file_array[$file_response->id] = $file_response->data->attributes->name;
			}
			

		} else {
			$active = "provider";
			$path = 'nodes/' . $node_id . '/?embed=files';
			$node_response = $this->api_call($path, False);

			$file_data = $node_response->data->embeds->files->data;

			$file_array = array();

			foreach ($file_data as $file) {
				$file_array[$file->attributes->provider] = $file->attributes->name;
			}
		}

		$context = array(
			"page_title" => "OSF Node - " . $node_id,
			"file_array" => $file_array,
			"node_id" => $node_id,
			"file_path" => $file_path,
			"active" => $active,
			"provider" => $provider,
		);

		$this->display('node.tpl', $context);

	}
	
}

?>