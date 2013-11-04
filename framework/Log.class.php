<?php
namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
class Log {
    
    /** @var resource $fileHande */
    private static $fileHandle = NULL;
    
    /**
     * Setting to private 
     * so that no one can create an instance using new 
     */
    private function __construct() {
        
    }
    
    /**
     *
     * @return \Log 
     */
    public static function getInstance() {
        
        if(!self::$fileHandle) {
            
            if(is_dir(APPLICATION_PATH.'/temp/debug')) {
                
                $filename = APPLICATION_PATH.'/temp/debug/'.date("d-M-Y").'-debug.log';

                //OPEN FILE
                self::$fileHandle = fopen ($filename, 'a');
            }
        }
        
        return new Log();
    }

    function __destruct() {
        
        if(is_resource(self::$fileHandle)) {
            fwrite(self::$fileHandle, "- close -\n");
            fclose (self::$fileHandle);
            self::$fileHandle = NULL;
        }
    }
    
    /**
     *
     * @param string $message 
     */
    public static function debug($message) {
        
        if(is_resource(self::$fileHandle)) {
            fwrite(self::$fileHandle, date("H:i:s")." $message\n");
        }
    }
}