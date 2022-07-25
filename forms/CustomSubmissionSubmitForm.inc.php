<?php

import('lib.pkp.classes.form.Form');

class CustomSubmissionSubmitForm extends Form {
	var $context;

	var $submissionId;

	var $submission;

	var $step;

	function __construct($context, $submission, $step) {
		$plugin = PluginRegistry::getPlugin('generic', 'tutorialexampleplugin');
		$template = $plugin->getTemplateResource(sprintf('submission/form/step%d.tpl', $step));
		parent::__construct(
			$template,
			true,
			$submission ? $submission->getLocale() : null,
			$context->getSupportedSubmissionLocaleNames()
		);
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
		$this->step = (int) $step;
		$this->submission = $submission;
		$this->submissionId = $submission ? $submission->getId() : null;
		$this->context = $context;
	}

	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);

		$templateMgr->assign('submissionId', $this->submissionId);
		$templateMgr->assign('submitStep', $this->step);

		if (isset($this->submission)) {
			$submissionProgress = $this->submission->getSubmissionProgress();
		} else {
			$submissionProgress = 1;
		}
		$templateMgr->assign('submissionProgress', $submissionProgress);
		return parent::fetch($request, $template, $display);
	}
}

