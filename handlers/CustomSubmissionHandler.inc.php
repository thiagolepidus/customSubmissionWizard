<?php

import('pages.submission.SubmissionHandler');

class CustomSubmissionHandler extends SubmissionHandler {

	function verifyAuthorCanPublish($step, $request) {
		if ($step == $this->getStepCount()) {
			$templateMgr = TemplateManager::getManager($request);
			$context = $request->getContext();
			$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

			import('classes.core.Services');
			if (Services::get('publication')->canAuthorPublish($submission->getId())){

				$primaryLocale = $context->getPrimaryLocale();
				$allowedLocales = $context->getSupportedLocales();
				$errors = Services::get('publication')->validatePublish($submission->getLatestPublication(), $submission, $allowedLocales, $primaryLocale);

				if (!empty($errors)){
					$msg = '<ul class="plain">';
					foreach ($errors as $error) {
						$msg .= '<li>' . $error . '</li>';
					}
					$msg .= '</ul>';
					$templateMgr->assign('errors', $msg);
				}
			}
			else {
				$templateMgr->assign('authorCanNotPublish', true);
			}
		}
	}

	function step($args, $request) {
		$step = isset($args[0]) ? (int) $args[0] : 1;

		$context = $request->getContext();
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

		$this->verifyAuthorCanPublish($step, $request);
		$this->setupTemplate($request);

		if ( $step < $this->getStepCount() ) {
			$formClass = "CustomSubmissionSubmitStep{$step}Form";
			import("plugins.generic.customSubmissionWizard.forms.$formClass");

			$submitForm = new $formClass($context, $submission);
			$submitForm->initData();
			return new JSONMessage(true, $submitForm->fetch($request));
		} elseif($step == $this->getStepCount()) {
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('context', $context);

			// Retrieve the correct url for author review his submission.
			import('classes.core.Services');
			$reviewSubmissionUrl = Services::get('submission')->getWorkflowUrlByUserRoles($submission);
			$router = $request->getRouter();
			$dispatcher = $router->getDispatcher();

			$templateMgr->assign(array(
				'reviewSubmissionUrl' => $reviewSubmissionUrl,
				'submissionId' => $submission->getId(),
				'submitStep' => $step,
				'submissionProgress' => $submission->getSubmissionProgress(),
			));

			return new JSONMessage(true, $templateMgr->fetch('submission/form/complete.tpl'));
		}
	}

	function saveStep($args, $request) {
		$step = isset($args[0]) ? (int) $args[0] : 1;

		$router = $request->getRouter();
		$context = $router->getContext($request);
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

		$this->setupTemplate($request);

		$formClass = "CustomSubmissionSubmitStep{$step}Form";
		import("plugins.generic.customSubmissionWizard.forms.$formClass");

		$submitForm = new $formClass($context, $submission);
		$submitForm->readInputData();

		if (!HookRegistry::call('SubmissionHandler::saveSubmit', array($step, &$submission, &$submitForm))) {
			if ($submitForm->validate()) {
				$submissionId = $submitForm->execute();
				if (!$submission) {
					return $request->redirectUrlJson($router->url($request, null, null, 'wizard', $step+1, array('submissionId' => $submissionId), 'step-2'));
				}
				$json = new JSONMessage(true);
				$json->setEvent('setStep', max($step+1, $submission->getSubmissionProgress()));
				return $json;
			} else {
				// Provide entered tagit fields values
				$tagitKeywords = $submitForm->getData('keywords');
				if (is_array($tagitKeywords)) {
					$tagitFieldNames = $submitForm->_metadataFormImplem->getTagitFieldNames();
					$locales = array_keys($submitForm->supportedLocales);
					$formTagitData = array();
					foreach ($tagitFieldNames as $tagitFieldName) {
						foreach ($locales as $locale) {
							$formTagitData[$locale] = array_key_exists($locale . "-$tagitFieldName", $tagitKeywords) ? $tagitKeywords[$locale . "-$tagitFieldName"] : array();
						}
						$submitForm->setData($tagitFieldName, $formTagitData);
					}
				}
				return new JSONMessage(true, $submitForm->fetch($request));
			}
		}
	}
    
	function getStepsNumberAndLocaleKeys() {
		return array(
			1 => 'author.submit.start',
			2 => 'author.submit.upload',
			3 => 'author.submit.metadata',
            4 => 'plugins.generic.customSubmissionWizard.submit.uploadDataset',
			5 => 'author.submit.confirmation',
			6 => 'author.submit.nextSteps',
		);
	}

	function getStepCount() {
		return 6;
	}
}
