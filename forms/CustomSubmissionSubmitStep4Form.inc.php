<?php

import('plugins.generic.customSubmissionWizard.forms.CustomSubmissionSubmitForm');

class CustomSubmissionSubmitStep4Form extends CustomSubmissionSubmitForm {
    
    function __construct($context, $submission) {
		parent::__construct($context, $submission, 4);
	}
}