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

?>