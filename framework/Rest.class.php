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
     * @return array
     */
    public static function processRequest() {
        
        $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $arr_requestParams = array();
        
        switch ($requestMethod) {
            case 'get':
                $arr_requestParams = $_GET;
                break;
            case 'post':
                $arr_requestParams = $_POST;
                break;
            case 'put':
                parse_str(file_get_contents('php://input'), $arr_requestParams);
                break;
            case 'delete':
                $arr_requestParams = $_GET;
                break;
        }
        
        unset($arr_requestParams['rt']);
        return $arr_requestParams;
    }


    public static function sendResponse($statusCode = 200, $body=NULL, $contentType='text/html') {
        
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

?>