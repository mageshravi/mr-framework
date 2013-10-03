<?php
namespace com\appname\model\exceptions;

class InvalidFormSubmissionException extends \Exception {
    
    public function __construct($message="Invalid Form submission!") {
        parent::__construct($message, INVALID_FORM_SUBMISSION);
    }
    
    public function __toString() {
        return __CLASS__." [{$this->code}]: {$this->message}";
    }
}

?>
