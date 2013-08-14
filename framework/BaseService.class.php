<?php

namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
class BaseService {
    
    /** @var Log */
    protected $log;
    
    /** @var \PDO */
    protected $writeConn;
    
    /** @var \PDO */
    protected $readConn;
            
    function __construct() {        
        $this->log = Log::getInstance();
        $this->writeConn = db::getWriteConnection();
    }

}

?>
