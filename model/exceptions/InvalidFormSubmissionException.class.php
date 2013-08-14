<?php

namespace com;

class InvalidFormSubmissionException extends \Exception {
    
    public function __construct($message="Invalid Form submission!") {
        parent::__construct($message, 99);
    }
    
    public function __toString() {
        return __CLASS__." [{$this->code}]: {$this->message}";
    }
}

?>
