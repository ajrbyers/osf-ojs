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
		return Config::getVar('files', 'files_dir') . '/journals/' . $journalId .  '/articles/' . $articleId . '/submission/original/';
	}

	function download_file($url, $path) {
		$access_token = $_SESSION['token'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
		$result = curl_exec($ch);
		curl_close($ch);

		// the following lines write the contents to a file in the same directory (provided permissions etc)
		$fp = fopen($path, 'w');
		fwrite($fp, $result);
		fclose($fp);
	}

	function login_required($request) {
		$user =& $request->getUser();
		$journal =& $request->getJournal();
		if ($user == NULL){

			$url = $journal->getUrl() . '/login/signIn?source=' . $_SERVER['REQUEST_URI'];

			redirect($url); 
		} else {
			return True;
		}
	}

	function handle_contributor_form($contributors) {
		$required_fields = array('firstName', 'lastName', 'email');
		$errors = array();

		foreach ($contributors as $contributor) {
			foreach ($required_fields as $field) {
				if (empty($contributor[$field])) {
					$errors[$field] = 'is required.';
				}
			}
		}
		if (empty($errors)) {
			$check = True;
		} else {
			$check = False;
		}

		return array('check' => $check, 'errors' => $errors);
	}

	function handle_adding_authors($contributors, $authors, $article_id, $request){
		$i = 1;
		foreach($contributors as $contributor) {
    		$author = $authors[$contributor];
    		if ($author['primary']) {
    			$primary = 1;
    		} else {
    			$primary = 0;
    		}

    		$author_params = array(
    			'submission_id' => $article_id,
    			'primary_contact' => $primary,
    			'seq' => $i,
    			'first_name' => $author['firstName'],
    			'middle_name' => $author['middleName'],
    			'last_name' => $author['lastName'],
    			'country' => $author['country'],
    			'email' => $author['email'],
    			'url' => $author['url'],
    		);

    		$author_settings = array(
    			'biography' => $author['biography'],
    			'affiliation' => $author['affiliation'],
    		);

    		$author_id = $this->dao->add_author($author_params);

    		$this->dao->add_author_settings($author_id, AppLocale::getLocale(), $author_settings);
       		
       		$i++;
   		}
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
		$this->login_required($request);
	
		$context = array(
			"page_title" => "OSF Submission",
			"journal" => $request->getJournal(),
		);
		$this->display('index.tpl', $context);
	}

	/* handles requests to:
		/osf/get_token/
	*/
	function get_token($args, &$request) {
		$this->login_required($request);
		$journal =& $request->getJournal();
		$url = 'https://test-accounts.osf.io/oauth2/authorize?scope=osf.full_read&client_id=abb1368d3b124148899ff5fd5b07976f&redirect_uri=' . $journal->getUrl() . '/osf/callback/';
		redirect($url);
	}

	/* handles requests to:
		/osf/callback/
	*/
	function callback($args, &$request) {
		$this->login_required($request);
		$journal =& $request->getJournal();

		$oauth2_client_id = 'abb1368d3b124148899ff5fd5b07976f';
		$oauth2_secret = '3mmXzLqWOmNmtKNAu6VooOdqmqpBfCd9QqfZLRkC';
		$oauth2_redirect = $journal->getUrl() . '/osf/callback/';

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
	    
	    redirect($journal->getUrl() . '/osf/nodes/');
	}

	/* handles requests to:
		/osf/nodes/
	*/
	function nodes($args, &$request) {
		$this->login_required($request);
		$journal =& $request->getJournal();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$selected_node = $_POST["node"];
			redirect($journal->getUrl() . '/osf/node/?id=' . $selected_node);
		} else {
			$path = 'users/me/';
			$user_response = $this -> api_call($path, False);
			$node_url = $user_response->data->relationships->nodes->links->related->href;

			$node_response = $this -> api_call(false, $node_url);
			$node_array = array();
			foreach ($node_response->data as $data) {
				echo $data->id;
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
		$this->login_required($request);
		$node_id = $_GET['id'];
		$provider = $_GET['provider'];
		$file_path = $_GET['path'];

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			$journal =& $request->getJournal();
			$user =& $request->getUser();

			$label = $_POST["label"];
			$descr = $_POST["description"];

			$params = array(
				'locale' => AppLocale::getLocale(), 
				'user_id' => $user->getId(), 
				'journal_id'=> $journal->getId(), 
				'language' => 'en', 
				'current_round' => 1);

			$article_id = $this->dao->create_article($params);
			$articleDao =& DAORegistry::getDAO('ArticleDAO');
			$article =& $articleDao->getArticle($article_id);

			$path = '/files/' . $file_path . '/';
			$file_response = $this->api_call($path, False);

			$file_params = array(
				'revision' => 1, 
				'article_id' => $article_id, 
				'original_filename' => $file_response->data->attributes->name, 
				'file_stage' => 1, 
				'date_uploaded' => date("Y-m-d H:i:s"),
				'date_modified' => date("Y-m-d H:i:s"), 
				'round' => 1,
				'file_name' => 'temp',
				'file_type' => 'temp',
				'file_size' => 1);

			$file_id = $this->dao->create_file($file_params);

			$download_link = $file_response->data->links->download;
			$download_path = $this->file_path($article_id, $file_response->data->attributes->name);

			mkdir($download_path, 0775, true);

			$file_ext = pathinfo($file_response->data->attributes->name, PATHINFO_EXTENSION);
			$ojs_file_name = $article_id . '-' . $file_id . '-1.' . $file_ext;
			$download = $this->download_file($download_link, $download_path . $ojs_file_name);

			$update_file_params = $arrayName = array(
				'file_name' => $ojs_file_name, 
				'file_type' => mime_content_type($download_path . $ojs_file_name),
				'file_size' => filesize($download_path . $ojs_file_name),
				'file_id' => $file_id);

			$submission_file_params = array(
				'submission_file_id' => $file_id,
				'article_id' => $article_id);

			$update = $this->dao->update_file($update_file_params);
			$update = $this->dao->update_article_submission_file($submission_file_params);

			$url = $journal->getUrl() . '/osf/contributors/?id=' . $node_id . '&article_id=' . $article_id;

			redirect($url); 

		} else {

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

	function contributors($args, &$request){
		$this->login_required($request);
		$node_id = $_GET['id'];
		$article_id = $_GET['article_id'];

		$path = 'nodes/' . $node_id . '/?embed=contributors';
		$node_response = $this->api_call($path, False);
		$contrib_data = $node_response->data->embeds->contributors->data;

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if( isset($_POST['contributors']) && is_array($_POST['contributors']) ) {

				$contributors = array();

				foreach($_POST['contributors'] as $contributor) {
			       array_push($contributors, $_POST['authors'][$contributor]);
			    }

			    $check = $this->handle_contributor_form($contributors);

				if ($check['check'] == True) {
					// handle saving and redirect
					$this->handle_adding_authors($_POST['contributors'], $_POST['authors'], $article_id, $request);
					$journal =& $request->getJournal();
					redirect($journal->getUrl() . '/osf/metadata/?id=' . $node_id . '&article_id=' . $article_id);
				} else {
					// handle erorrs
					$errors = $check['errors'];
				}
			} else {
				$errors = array('You must select at least one contributor');
			}
			$authors = array();
			foreach($_POST['contributors'] as $contributor) {
		       $authors[$contributor] = $_POST['authors'][$contributor];
		    }	
		}

		$user_array = array();
		foreach ($contrib_data as $user) {
			$user_array[$user->id] = $user->embeds->users->data->attributes->full_name;
		}

		$countryDao =& DAORegistry::getDAO('CountryDAO');

		$context = array(
			'user_array' => $user_array,
			'errors' => $errors,
			'authors' => $authors,
			'countries' => $countryDao->getCountries(),
			'contributors' => $_POST['contributors'],
		);
		$this->display('contributors.tpl', $context);
	}

	function metadata($args, &$request){
		$this->login_required($request);
		$node_id = $_GET['id'];
		$article_id = $_GET['article_id'];

		$journal =& $request->getJournal();
		$sectionDao =& DAORegistry::getDAO('SectionDAO');

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$required_fields = array('sectionId', 'title', 'abstract');
			$errors = array();
			foreach ($required_fields as $field) {
				if (empty($_POST[$field])) {
					$errors[$field] = 'is required.';
				}
			}

			$submission_checklist = sizeof($journal->getLocalizedSetting('submissionChecklist'));
			$checked_elements = sizeof($_POST['checklist']);

			if ($submission_checklist == $checked_elements) {
				//pass
			} else {
				$errors['checklist'] = '- you must check all items in the submission checklist.';
			}

			$selected_check_items = array();
			foreach ($_POST['checklist'] as $key => $value) {
				array_push($selected_check_items, $key + 1);
			}

			if (empty($errors)) {
				$article_settings_params = array(
					'title' => $_POST['title'],
					'abstract' => $_POST['abstract'],
				);

				$this->dao->add_article_settings($article_id, AppLocale::getLocale(), $article_settings_params);

				$article_settings = array(
					'section_id' => $_POST['sectionId'],
					'submission_progress' => 0,
					'date_uploaded' => date("Y-m-d H:i:s"),
					'article_id' => $article_id,
				);

				$this->dao->complete_article($article_settings);

				redirect($journal->getUrl() . '/osf/complete/?article_id=' . $article_id);
			}
		}

		$context = array(
			'sectionOptions' => $sectionDao->getSectionTitles($journal->getId(), !$isEditor),
			'currentJournal' => $journal,
			'errors' => $errors,
			'post' => $_POST,
			'selected_check_items' => $selected_check_items,
		);
		$this->display('metadata.tpl', $context);
	}

	function complete($args, &$request) {
		$article_id = $_GET['article_id'];
		$journal =& $request->getJournal();

		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($article_id);

		$context = array(
			'article' => $article,
			'journal' => $journal,
		);
		$this->display('complete.tpl', $context);
	}
	
}

?>