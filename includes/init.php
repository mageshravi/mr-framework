<?php

include APPLICATION_PATH . '/framework/BaseController.class.php';

include APPLICATION_PATH . '/framework/BaseService.class.php';

include APPLICATION_PATH . '/framework/Log.class.php';

include APPLICATION_PATH . '/framework/Registry.class.php';

include APPLICATION_PATH . '/framework/Request.class.php';

include APPLICATION_PATH . '/framework/Rest.class.php';

include APPLICATION_PATH . '/framework/Router.class.php';

include APPLICATION_PATH . '/framework/Template.class.php';

include APPLICATION_PATH . '/framework/Cache.class.php';

spl_autoload_register('myAutoload');

function myAutoload($fullyQualifiedClassName) {
    
    if(strpos($fullyQualifiedClassName, APP_NAMESPACE) !== false) {
        /* com\appname\model\User => model/User.class.php */
        $namespacePath = str_replace(APP_NAMESPACE, '', $fullyQualifiedClassName);
    } else
        throw new Exception ("Class $fullyQualifiedClassName not found!");

    $arr_parts = explode('\\', $namespacePath);

    $file = APPLICATION_PATH;
    foreach ($arr_parts as $dir) {
        $file .= "/$dir";
    }
    $file .= ".class.php";

    if(file_exists($file) == FALSE)
       throw new Exception ("Class $fullyQualifiedClassName not found!");
    
    include_once $file;
}

set_exception_handler('myExceptionHandler');

function myExceptionHandler(Exception $e) {
    // For all uncaught exceptions
    header("HTTP/1.1 500 Internal Server Error");
    echo '<h1>Internal Server Error</h1>';
    
    if(error_reporting() !== 0) {
        // print trace when error reporting is ON
        echo '<p>', $e->getMessage(), '</p>';
        echo '<pre>', $e->getTraceAsString(), '</pre>';
    }
}

set_error_handler("myErrorHandler");

function myErrorHandler($errno, $errstr, $errfile, $errline) {
    
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
        case E_USER_ERROR:
            header("HTTP/1.1 500 Internal Server Error");
            echo "<b>USER ERROR</b> [$errno] $errstr<br>";
            echo "Fatal error in file $errfile, line $errline";
            exit(1);
            break;

        case E_USER_WARNING:
            echo "<b>USER WARNING</b> [$errno] $errstr<br>";
            break;

        case E_USER_NOTICE:
            echo "<b>USER NOTICE</b> [$errno] $errstr<br>";
            break;
        
        case E_ERROR:
            header("HTTP/1.1 500 Internal Server Error");
            exit(1);
            break;
        
        default:
            echo "Unknown error type: [$errno] $errstr<br>";
            break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}