<?php
namespace com\appname\model\exceptions;

/**
 * Description of ObjectNotFoundException
 *
 * @author mageshravi
 */
class ObjectNotFoundException extends MRException {
    
    public function __construct($message='Object not found!') {
        parent::__construct($message, self::OBJECT_NOT_FOUND);
    }
    
    public function __toString() {
        return __CLASS__." [{$this->code}]: [{$this->message}]";
    }
}

?>
