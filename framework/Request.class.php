<?php
namespace com;

/**
 * For creating internal requests
 *
 * @author mageshravi
 */
class Request {
    
    /**
     * TODO: handle request params
     * @param string $controller
     * @param string $action
     * @param array $_params
     * @param string $method GET|POST
     * @return string Response
     */
    public static function factory($controller, $action, $_params=array(), $method='GET') {
        
        $registry = new Registry();
        $registry->setRequestType('internal');
        
        $router = new Router();
        $router->setPath(APPLICATION_PATH . '/controller');
        $router->controller = ucfirst(preg_replace('/Controller/', '', $controller));
        $router->action = $action;
        
        ob_start();
        $router->loader($registry);
        $internalResponse = ob_get_contents();
        ob_clean();
        ob_end_clean();
        
        return $internalResponse;
    }
}

?>
