<?php

namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
class Cache {
    
    /** @var string */
    private $salt;
    
    /** @var string */
    private $cacheName;
    
    /** @var string */
    private $content;
    
    /** @param boolean $isUserSpecific */
    function __construct($isUserSpecific = false) {
        
        if($isUserSpecific)
            $this->salt = session_id();
        else
            $this->salt = 'common';
        
        $path = CACHE.'/'.$this->salt;
        
        if(!is_dir($path)) {
            mkdir($path, 0750);
        }
    }
    
    /**
     * 
     * @param string $cacheName
     * @return string Encrypted cache file location
     */
    private function getCacheLocation($cacheName) {
        return CACHE.'/'.$this->salt.'/'.md5($this->salt.$cacheName).'.cache';
    }
    
    /**
     * 
     * @param string $cacheName
     * @param int $expiryTime
     * @return boolean
     */
    public function exists($cacheName, $expiryTime=3600) {
        
        $cacheName = $this->getCacheLocation($cacheName);
        
	
	if ( file_exists($cacheName) && filemtime($cacheName) > (time() - $expiryTime) )
            return true;
	else
            return false;
    }
    
    /**
     * 
     * @param string $cacheName
     * @param string $content
     */
    public function createCache($cacheName, $content) {
        
        Log::debug("Creating cache... ".$cacheName);
        
        $cacheName = $this->getCacheLocation($cacheName);
        
	$handle = fopen( $cacheName , 'w' );
	fwrite($handle , $content );
	fclose($handle);
        
        unset($handle);
    }
    
    /**
     * 
     * @param string $cacheName
     * @param int $expiryTime
     * @return mixed Contents on success, FALSE on failure
     */
    public function getCache($cacheName, $expiryTime=3600) {
        
        Log::debug('Fetching from cache... '.$cacheName);
        
        $cacheName = $this->getCacheLocation($cacheName);
        	
	if(file_exists ($cacheName) && filemtime($cacheName) >( time() - $expiryTime )) {
            return file_get_contents( $cacheName );
	}
	
	return false;
    }
    
    /**
     * 
     * @param string $cacheName
     */
    public function start($cacheName) {
        $this->cacheName = $cacheName;
        ob_start();
    }
    
    /**
     * 
     * @param string $cacheName
     */
    public function stop($cacheName) {
        
        if($this->cacheName == $cacheName) {
            
            $this->content = ob_get_contents();
            ob_clean();
            $this->createCache($cacheName, $this->content);
            
            ob_end_clean();
            
            echo $this->content;
        }
    }
}

?>
