<?php

include APPLICATION_PATH . '/framework/BaseController.class.php';

include APPLICATION_PATH . '/framework/BaseService.class.php';

include APPLICATION_PATH . '/framework/Log.class.php';

include APPLICATION_PATH . '/framework/Registry.class.php';

include APPLICATION_PATH . '/framework/Rest.class.php';

include APPLICATION_PATH . '/framework/Router.class.php';

include APPLICATION_PATH . '/framework/Template.class.php';

include APPLICATION_PATH . '/framework/Cache.class.php';

spl_autoload_register('myAutoload');

function myAutoload($fullyQualifiedClassName) {
    
    $arr_parts = explode('\\', $fullyQualifiedClassName);
    $className = end($arr_parts);
    
    $file = APPLICATION_PATH."/model/$className.class.php";
    if(file_exists($file) == FALSE)
        return FALSE;
    else
        include_once $file;
}

?>
