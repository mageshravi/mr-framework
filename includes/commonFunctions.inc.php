<?php

/**
 *
 * @deprecated use $this->registry->log->debug() instead
 * @param string $msg 
 */
function logToFile($msg)
{
    $filename = APPLICATION_PATH . "/temp/debug/debugLog-".date("d.m.Y").".log";
     // open file
    $fd = fopen($filename, "a");
    // write string
    fwrite($fd, date("F j, Y, g:i:s a")."\t" . $msg . "\n");
    // close file
    fclose($fd);
}


function loadClass($class_name) {
    require_once APPLICATION_PATH."/model/$class_name.class.php";
}

function loadException($class_name) {
    require_once APPLICATION_PATH."/model/exceptions/$class_name.class.php";
}

function loadComponent($componentName) {
    include_once COMPONENTS."/$componentName.php";
}

function checkAuth($ajax=FALSE)
{
    if(!isset($_SESSION['profileId'])) {
        
        if($ajax)
            die('98_Login to continue!');
        else {
            header("Location: login");
            exit();
        }
    }
}

function checkForCache( $cacheFile , $expireTime )
{
	//ADDING LOCATION AND FILE EXTENSION
	$cacheFile = APPLICATION_PATH . '/temp/cache/'.$cacheFile.'.txt';
	
	if ( file_exists ( $cacheFile ) && filemtime ( $cacheFile ) >( time() - $expireTime ) )
		return true;
	else
		return false;
}

//GET CACHE IF EXISTS
function getCache ( $cacheFile ,  $expireTime ) {
	
	//ADDING LOCATION AND FILE EXTENSION
	$cacheFile = APPLICATION_PATH . '/temp/cache/'.$cacheFile.'.txt';
	
	if ( file_exists ( $cacheFile ) && filemtime ( $cacheFile ) >( time() - $expireTime ) ) {
		logToFile("Fetching from cache... ".$cacheFile);
		return file_get_contents( $cacheFile );
	}
	
	return false;
}

//CREATE CACHE
function createCache ( $content ,  $cacheFile ) {
	
	//ADDING LOCATION AND FILE EXTENSION
	$cacheFile = APPLICATION_PATH . '/temp/cache/'.$cacheFile.'.txt';
	logToFile("Creating cache... ".$cacheFile);
	
	$fp = fopen( $cacheFile , 'w' );
	fwrite( $fp , $content );
	fclose( $fp);
}

?>