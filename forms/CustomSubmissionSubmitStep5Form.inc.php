<?php

import('classes.submission.form.SubmissionSubmitStep4Form');

class CustomSubmissionSubmitStep5Form extends SubmissionSubmitStep4Form {

    function __construct($context, $submission) {
		parent::__construct($context, $submission, 5);
	}
}