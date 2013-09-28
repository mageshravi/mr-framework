<?php
namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
class Registry {
    
    private $vars = array();
    private $requestType;

    public function __set($index, $value) {
        $this->vars[$index] = $value;
    }

    public function __get($index) {
        return $this->vars[$index];
    }
    
    public function getRequestType() {
        return $this->requestType;
    }

    public function setRequestType($requestType) {
        $this->requestType = $requestType;
    }

}

?>
