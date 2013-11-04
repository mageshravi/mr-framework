<?php
namespace com;

/**
 * @copyright (c) 2013, Magesh Ravi
 */
class Rest {
    
    private static $instance;
    private static $arr_httpStatus;
    
    private function __construct() {
        $arr_httpStatus = parse_ini_file('httpCodes.ini');
        
        if($arr_httpStatus === FALSE)
            die('Error: Could not read HTTP status codes!');
        
        self::$arr_httpStatus = $arr_httpStatus;
    }
    
    private function __clone() {
        ;
    }

    /**
     * 
     * @return Rest
     */
    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new Rest();
        return self::$instance;
    }

    /**
     * 
     * @param int $statusCode
     * @return string
     */
    public static function getStatusMessage($statusCode) {
        
        if(array_key_exists($statusCode, self::$arr_httpStatus))
            return self::$arr_httpStatus[$statusCode];
        else
            return '';
    }
    
    /**
     * 
     * @param array $_filterDefinition
     * @param boolean $strict If set to TRUE, will check if the request parameters are IDENTICAL to the filter definition
     * @return mixed associative array with filtered parameters on success.
     * @deprecated since version 1.4.1.2
     */
    public static function processRequest(array $_filterDefinition=NULL, $strict=FALSE) {
        
        $requestMethod = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
        
        $route = filter_input(INPUT_GET, 'rt');
        
        $arr_parts = explode('/', $route);
        $partsCount = count($arr_parts);
        $_rawSeoParams = array();  // seo friendly url params
        for($i=2; $i<$partsCount; $i=$i+2) {
            if(array_key_exists($i+1, $arr_parts))
                $_rawSeoParams[$arr_parts[$i]] = $arr_parts[($i+1)];
            else
                $_rawSeoParams[$arr_parts[$i]] = '';
        }
        $_seoParams = filter_var_array($_rawSeoParams);
        
        switch ($requestMethod) {
            case 'get':
                
                $_GET1 = filter_input_array(INPUT_GET);
                $_GETall = array_merge($_GET1, $_seoParams);
                
                if($strict && array_keys($_filterDefinition) !== array_keys($_GETall)) {
                    $_requestParams = FALSE;
                    break;
                }
                
                if(is_null($_filterDefinition))
                    $_requestParams = filter_var_array($_GETall);
                else
                    $_requestParams = filter_var_array($_GETall, $_filterDefinition);
                break;
                
            case 'post':
                
                if($strict && array_keys($_filterDefinition) !== array_keys($_POST)) {
                    $_requestParams = FALSE;
                    break;
                }
                
                if(is_null($_filterDefinition))
                    $_requestParams = filter_input_array(INPUT_POST);
                else
                    $_requestParams = filter_input_array(INPUT_POST, $_filterDefinition);
                break;
            
            case 'put':
                
                $_PUT = array();
                parse_str(file_get_contents('php://input'), $_PUT);
                
                if($strict && array_keys($_filterDefinition) !== array_keys($_PUT)) {
                    $_requestParams = FALSE;
                    break;
                }
                
                if(is_null($_filterDefinition))
                    $_requestParams = filter_var_array($_PUT);
                else
                    $_requestParams = filter_var_array($_PUT, $_filterDefinition);
                break;
            
            case 'delete':
                if($strict && array_keys($_filterDefinition) !== array_keys($_seoParams)) {
                    $_requestParams = FALSE;
                    break;
                }
                
                if(is_null($_filterDefinition))
                    $_requestParams = filter_var_array($_seoParams);
                else
                    $_requestParams = filter_var_array($_seoParams, $_filterDefinition);
                break;
        }
        
        return $_requestParams;
    }

    public static function sendResponse($statusCode=200, $body=NULL, $contentType='text/html') {
        
        $statusMessage = self::getStatusMessage($statusCode);
        
        header("HTTP/1.1 $statusCode $statusMessage");
        header("Content-type: $contentType");
        
        if(!is_null($body))
            echo $body;
        else {
            $signature = "{$_SERVER['SERVER_SOFTWARE']} server at {$_SERVER['SERVER_NAME']} Port {$_SERVER['SERVER_PORT']}";
            
            echo <<<genBody
<!DOCTYPE html>
    <html>
        <head>
            <title>$statusCode $statusMessage</title>
        </head>
        <body>
            <h1>$statusCode</h1>
            <p>$statusMessage</p>
            <hr>
            <address>$signature</address>
        </body>
    </html>
genBody;
        }
        
        exit();
    }
}