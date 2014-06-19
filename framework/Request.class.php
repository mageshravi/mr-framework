<?php
namespace com;

/**
 * For creating internal requests
 *
 * @author mageshravi
 */
class Request {
    
    private $method;
    /** @var array */
    private $_params;
    
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
    
    public function processParams() {
        $this->method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
        
        $_params = array();
        
        $_seoParams = self::getSEOQueryParams();
        
        // process GET query parameters by default
        // POST, PUT can happen to a url containing query parameters
        $_GET1 = filter_input_array(INPUT_GET);
        if(!empty($_GET1))
            $_params['get'] = array_merge($_GET1, $_seoParams);
        else
            $_params['get'] = $_seoParams;
        
        switch ($this->method) {
            case 'post':
                $_params['post'] = filter_input_array(INPUT_POST);
                break;
            
            case 'put':
                $_PUT = array();
                parse_str(file_get_contents('php://input'), $_PUT);
                
                $_params['put'] = filter_var_array($_PUT);
                break;
            
            case 'delete':
                $_params['delete'] = $_seoParams;
                break;
        }
        
        $this->_params = $_params;
    }
    
    public static function getSEOQueryParams() {
        $route = filter_input(INPUT_GET, 'rt');
        $arr_parts = explode('/', $route);
        $partsCount = count($arr_parts);
        
        $_rawSeoParams = array();
        for($i=2; $i<$partsCount; $i=$i+2) {
            if(array_key_exists($i+1, $arr_parts))
                $_rawSeoParams[$arr_parts[$i]] = $arr_parts[($i+1)];
            else
                $_rawSeoParams[$arr_parts[$i]] = '';
        }
        
        return filter_var_array($_rawSeoParams);
    }
    
    /**
     * 
     * @param string $key
     * @param string $method get|post|put|delete
     * @return string the value if key is found
     */
    public function param($key, $method='get') {

        if(array_key_exists($method, $this->_params) == FALSE)
            return;
        
        $_methodParams = $this->_params[$method];
        if(array_key_exists($key, $_methodParams))
            return $_methodParams[$key];
    }
    
    /**
     * 
     * @param string $method
     * @return array
     */
    public function paramsArray($method='get') {
        if(array_key_exists($method, $this->_params))
            return $this->_params[$method];
    }
    
    /**
     * 
     * @return string
     */
    public function method() {
        return $this->method;
    }
}