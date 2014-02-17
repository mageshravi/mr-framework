<?php
namespace com\appname\model\exceptions;

class InvalidFormSubmissionException extends MRException {
    
    public function __construct($message="Invalid Form submission!") {
        parent::__construct($message, self::INVALID_FORM_SUBMISSION);
    }
    
    public function __toString() {
        return __CLASS__." [{$this->code}]: {$this->message}";
    }
}