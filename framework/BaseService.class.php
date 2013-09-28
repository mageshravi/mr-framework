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
        $db = APP_NAMESPACE.'\\model\\db';
        $this->writeConn = $db::getWriteConnection();
    }

}

?>
