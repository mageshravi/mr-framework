<?php

namespace com;

class db {

    private static $user = 'root';
    private static $pass = 'root';
    private static $readPools = array(
        'primary' => 
            array('mysql:dbname=test;host=localhost'),
        'pool1' =>
            array('mysql:dbname=test;host=localhost'),
        'pool2' =>
            array('mysql:dbname=test;host=localhost')        
        );
    
    /** @var \PDO */
    private static $writeConn = NULL;

    /**
    *
    * the constructor is set to private so
    * so nobody can create a new instance using new
    *
    */
    private function __construct() {
        /*** maybe set the db name here later ***/
    }

    /**
     * 
     * @return \PDO
     */
    public static function getWriteConnection() {

        if (!self::$writeConn) {
            try {
                self::$writeConn = new \PDO('mysql:dbname=test;host=localhost', self::$user, self::$pass);
                self::$writeConn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            catch(\PDOException $e) {
                Log::debug($e->getMessage());
                die('Connection error!');
            }
        }
        return self::$writeConn;
    }
    
    /**
     * 
     * @param string $pool
     * @return \PDO
     * @throws Exception
     */
    public static function getReadConnection($pool='primary') {
        
        $servers = self::$readPools[$pool];
        $connection = false;

        while(!$connection && count($servers)) {
            $key = array_rand($servers);
            try {
                $connection = new \PDO($servers[$key], self::$user, self::$pass);
                $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            catch(\PDOException $e) {
                Log::debug($e->getMessage());
            }

            if(!$connection) {
                // Couldn't connect to this server. So remove it.
                Log::debug("Could not connect to server {$servers[$key]}...");
                unset($servers[$key]);
            }
        }
        
        if(!$connection) {
            throw new \Exception("Failed pool $pool");
        }
        
        return $connection;
    }

    /**
    *
    * Like the constructor, we make __clone private
    * so nobody can clone the instance
    *
    */
    private function __clone(){
        
    }

}

/*** end of class ***/

?>
