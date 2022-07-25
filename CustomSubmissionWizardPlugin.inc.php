<?php

import('lib.pkp.classes.plugins.GenericPlugin');

class CustomSubmissionWizardPlugin extends GenericPlugin {

	public function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if ($success && $this->getEnabled()) {
			HookRegistry::register('LoadHandler', array($this, 'setPageHandler'));
		}
		return $success;
    }

    public function setPageHandler($hookName, $params) {
		$page = $params[0];
		$op = $params[1];
		if ($page === 'submission') {
			switch ($op) {
				case 'wizard':
				case 'step':
				case 'saveStep':
				case 'index':
					$this->import('handlers.CustomSubmissionHandler');
					define('HANDLER_CLASS', 'CustomSubmissionHandler');
					break;
			}
			return true;
		}
		return false;
	}

    public function getDisplayName() {
		return __('plugins.generic.customSubmissionWizard.name');
	}

	public function getDescription() {
		return __('plugins.generic.customSubmissionWizard.description');
	}
}

