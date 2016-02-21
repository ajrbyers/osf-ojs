<?php

/**
 *
 * Plugin for submitting additional files to Osf
 * Written by Andy Byers, Ubiquity Press
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');
require_once('OsfDAO.inc.php');

class OsfPlugin extends GenericPlugin {
	function register($category, $path) {
		if(!parent::register($category, $path)) {
			//debug("failed to register!");
			return false;
		}
		if($this->getEnabled()) {
			HookRegistry::register("LoadHandler", array(&$this, "handleRequest"));
			$tm =& TemplateManager::getManager();
			$tm->assign("osfEnabled", true);
			define('OSF_PLUGIN_NAME', $this->getName());
		}
		return true;
	}

	function handleRequest($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];
		$sourceFile =& $args[2];

		if ($page == 'osf') {
			$this->import('OsfHandler');
			Registry::set('plugin', $this);
			define('HANDLER_CLASS', 'OsfHandler');
			return true;
		}
		return false;
	}

	function getName() {
		return "OSF Submission";
	}

	function getDisplayName() {
		return "OSF Submission";
	}
	
	function getDescription() {
		return "Allows OSF users to submit articles directly to OJS.";
	}
	
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}

}
