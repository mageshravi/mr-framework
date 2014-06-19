<?php

/**
 * 
 * @param string $message
 */
function log_debug($message) {
    $log = \com\Log::getInstance();
    $log->debug($message);
}

function loadClass($class_name) {
    require_once APPLICATION_PATH."/model/$class_name.class.php";
}

function loadException($class_name) {
    require_once APPLICATION_PATH."/model/exceptions/$class_name.class.php";
}

function loadComponent($componentName) {
    include_once COMPONENTS."/$componentName.php";
}